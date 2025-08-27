<?php

namespace App\Livewire\Crm;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Logs;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\Premio;
use App\Models\Campania;
use App\Models\Campaniapremio;
use App\Models\User;
use App\Models\Vendedorintranet;
use App\Models\Canjearpunto;
use App\Models\Canjearpuntodetalle;
use App\Models\Campaniadocumento;

class Seleccionarpremios extends Component{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $premio;
    private $campania;
    private $campaniapremio;
    private $user;
    private $vendedorintranet;
    private $canjearpunto;
    private $canjearpuntodetalle;
    private $campaniadocumento;
    public function __construct(){
        $this->logs = new Logs();
        $this->premio = new Premio();
        $this->campania = new Campania();
        $this->campaniapremio = new Campaniapremio();
        $this->user = new User();
        $this->vendedorintranet = new Vendedorintranet();
        $this->canjearpunto = new Canjearpunto();
        $this->canjearpuntodetalle = new Canjearpuntodetalle();
        $this->campaniadocumento = new Campaniadocumento();
    }
    public $id_campania = "";
    public $select_premios = [];
    public $listar_premios_disponibles = [];
    public $premios_seleccionados = [];
    public $campania_seleccionada = null;
    public $cantidades = [];
    public $puntos_ganados = 0;
    public $puntos_canjeados = 0;
    public $puntos_restantes = 0;
    public $id_users = "";
    public $id_canjear_punto_actual = null;
    public $premios_ya_canjeados = [];
    public $documentos_campania = [];
    public $archivos_adjuntos = [];
    public $id_cliente_vendedor = "";
    public function mount(){
        $this->id_users = Auth::id();
    }

    public function render(){
        $this->id_users = $this->id_users ?: Auth::id();
        $this->listar_premios_disponibles = [];

        if (!empty($this->id_campania)) {
            $this->listar_premios_disponibles = $this->premio->listar_campanias_activos($this->id_campania);
            $this->campania_seleccionada = Campania::find($this->id_campania);
            // Obtener premios canjeados ANTES de actualizar selección
            $this->obtenerPremiosCanjeados();
            $this->actualizarPremiosSeleccionados();
        }

        // Obtener puntos del vendedor y calcular
        $this->obtenerPuntosVendedor();
        $this->calcularPuntos();

        // Pasar el id_users al modelo para filtrar campañas
        $listar_campania = $this->campania->listar_campanias_activos_new($this->id_users);

        return view('livewire.crm.seleccionarpremios', compact('listar_campania'));
    }

    public function cargarDocumentosCampania(){
        $this->documentos_campania = [];
        $this->archivos_adjuntos = [];

        if (!empty($this->id_campania)) {
            // Obtener documentos de la campaña con estado 1
            $documentos = CampaniaDocumento::where('id_campania', $this->id_campania)
                ->where('campania_documento_estado', 1)
                ->get();

            foreach ($documentos as $documento) {
                $this->documentos_campania[] = [
                    'id_campania_documento' => $documento->id_campania_documento,
                    'nombre' => $documento->campania_documento_nombre ?? basename($documento->campania_documento_adjunto),
                    'campania_documento_adjunto' => $documento->campania_documento_adjunto,
                    'extension' => pathinfo($documento->campania_documento_adjunto, PATHINFO_EXTENSION)
                ];

                $this->archivos_adjuntos[] = $documento->campania_documento_adjunto;
            }
        }
    }

    public function obtenerPremiosCanjeados(){
        $this->premios_ya_canjeados = [];
        $this->id_canjear_punto_actual = null;

        if (!empty($this->id_campania) && !empty($this->id_users)) {
            // Buscar si ya existe un registro de canje para esta campaña y usuario
            $canjeExistente = $this->canjearpunto
                ->where('id_campania', $this->id_campania)
                ->where('id_users', $this->id_users)
                ->where('canjear_punto_estado', 1)
                ->first();

            if ($canjeExistente) {
                $this->id_canjear_punto_actual = $canjeExistente->id_canjear_punto;

                // Obtener los detalles del canje
                $detallesCanje = $this->canjearpuntodetalle
                    ->where('id_canjear_punto', $this->id_canjear_punto_actual)
                    ->where('canjear_punto_detalle_estado', 1)
                    ->get();

                foreach ($detallesCanje as $detalle) {
                    $this->premios_ya_canjeados[$detalle->id_premio] = [
                        'id_premio' => $detalle->id_premio,
                        'cantidad' => $detalle->canjear_punto_detalle_cantidad,
                        'puntos_unitarios' => $detalle->canjear_punto_detalle_pts_unitario
                    ];
                }
            }
        }
    }

    public function calcularPuntos(){
        $this->puntos_canjeados = 0;

        foreach ($this->premios_seleccionados as $id_premio => $premio) {
            $unit = (float)($premio['campania_premio_puntaje'] ?? 0);
            $cant = (float)($premio['cantidad'] ?? 0);
            $base = (float)($this->premios_ya_canjeados[$id_premio]['cantidad'] ?? 0);

            // CORRECCIÓN: Solo calcular la diferencia (delta) para premios nuevos o incrementos
            if (isset($this->premios_ya_canjeados[$id_premio])) {
                // Para premios ya canjeados, solo contar el incremento
                $delta = max(0, $cant - $base);
            } else {
                // Para premios nuevos, contar toda la cantidad
                $delta = $cant;
            }

            $this->puntos_canjeados += $unit * $delta;
        }

        $this->puntos_restantes = $this->puntos_ganados - $this->puntos_canjeados;

        if ($this->puntos_restantes < 0) {
            session()->flash('error_modal', 'Los puntos canjeados no pueden superar los puntos ganados.');
        }
    }

    public function obtenerPuntosVendedor(){
        try {
            // Obtener el vendedor del usuario
            $user = DB::table('users')
                ->where('id_users', $this->id_users)
                ->select('id_vendedor_intranet')
                ->first();

            if ($user && !empty($user->id_vendedor_intranet)) {
                // Buscar en vendedores_intranet
                $vendedor = DB::table('vendedores_intranet')
                    ->where('id_vendedor_intranet', $user->id_vendedor_intranet)
                    ->select('vendedor_intranet_punto')
                    ->first();

                if ($vendedor) {
                    $this->puntos_ganados = (float)$vendedor->vendedor_intranet_punto;
                } else {
                    $this->puntos_ganados = 0;
                }
            } else {
                $this->puntos_ganados = 0;
            }
        } catch (\Exception $e) {
            $this->puntos_ganados = 0;
            \Log::error('Error obteniendo puntos del vendedor: ' . $e->getMessage());
        }
    }

    public function seleccionar_premio($id_premio, $isChecked){
        if ($isChecked) {
            // Agregar premio a la selección
            $premio = $this->premio->find($id_premio);
            if ($premio) {
                $campania_premio = $this->campaniapremio
                    ->where('id_campania', $this->id_campania)
                    ->where('id_premio', $id_premio)
                    ->first();

                // Si ya está canjeado, usar la cantidad canjeada, sino 1
                $cantidad_inicial = isset($this->premios_ya_canjeados[$id_premio]) ?
                    (int)$this->premios_ya_canjeados[$id_premio]['cantidad'] : 1;

                $this->premios_seleccionados[$id_premio] = [
                    'id_premio' => $premio->id_premio,
                    'premio_codigo' => $premio->premio_codigo,
                    'premio_descripcion' => $premio->premio_descripcion,
                    'premio_documento' => $premio->premio_documento,
                    'campania_premio_puntaje' => $campania_premio ? (float)$campania_premio->campania_premio_puntaje : 0,
                    'cantidad' => $cantidad_inicial
                ];

                $this->cantidades[$id_premio] = $cantidad_inicial;

                // Asegurarse de que el checkbox se mantenga seleccionado
                if (!in_array($id_premio, $this->select_premios)) {
                    $this->select_premios[] = $id_premio;
                }
            }
        } else {
            // No permitir deseleccionar premios ya canjeados
            if (!isset($this->premios_ya_canjeados[$id_premio])) {
                // Remover premio de la selección solo si no está ya canjeado
                unset($this->premios_seleccionados[$id_premio]);
                unset($this->cantidades[$id_premio]);

                // Remover de select_premios
                $this->select_premios = array_values(array_diff($this->select_premios, [$id_premio]));
            } else {
                // Si es un premio ya canjeado, forzar a que se mantenga seleccionado
                if (!in_array($id_premio, $this->select_premios)) {
                    $this->select_premios[] = $id_premio;
                }
            }
        }

        // Recalcular puntos después de cambiar la selección
        $this->calcularPuntos();
    }

    public function actualizarCantidad($id_premio, $cantidad){
        if (!isset($this->premios_seleccionados[$id_premio])) return;

        $cantidad = (int)$cantidad;
        $yaCanjeado = isset($this->premios_ya_canjeados[$id_premio]);
        $min = $yaCanjeado ? (int)$this->premios_ya_canjeados[$id_premio]['cantidad'] : 1;

        if ($cantidad < $min) {
            $cantidad = $min;
            session()->flash('error_modal', 'No puedes reducir por debajo de lo ya canjeado.');
        }

        // Actualizar la cantidad
        $this->premios_seleccionados[$id_premio]['cantidad'] = $cantidad;
        $this->cantidades[$id_premio] = $cantidad;

        // IMPORTANTE: Recalcular inmediatamente después de cambiar
        $this->calcularPuntos();

        // Si es un premio ya canjeado y se está aumentando la cantidad
        if ($yaCanjeado && $cantidad > $min) {
            $incremento = $cantidad - $min;
            $puntosUnitarios = (float)$this->premios_seleccionados[$id_premio]['campania_premio_puntaje'];
            $puntosAdicionales = $incremento * $puntosUnitarios;

            // Mensaje informativo
//            session()->flash('success_modal', "Se agregaron {$incremento} unidades adicionales ({$puntosAdicionales} puntos).");
        }
    }

    public function actualizarPremiosSeleccionados(){
        $nuevos_premios_seleccionados = [];
        $nuevas_cantidades = [];
        $nuevos_select_premios = [];

        // Agregar automáticamente los premios ya canjeados
        foreach ($this->premios_ya_canjeados as $id_premio => $premio_canjeado) {
            $premio = $this->premio->find($id_premio);
            if ($premio) {
                $campania_premio = $this->campaniapremio
                    ->where('id_campania', $this->id_campania)
                    ->where('id_premio', $id_premio)
                    ->first();

                $cantidad_actual = $premio_canjeado['cantidad'];

                // Si ya existe en premios_seleccionados, mantener la cantidad actual (puede ser mayor)
                if (isset($this->premios_seleccionados[$id_premio])) {
                    $cantidad_actual = max($cantidad_actual, $this->premios_seleccionados[$id_premio]['cantidad']);
                }

                $nuevos_premios_seleccionados[$id_premio] = [
                    'id_premio' => $premio->id_premio,
                    'premio_codigo' => $premio->premio_codigo,
                    'premio_descripcion' => $premio->premio_descripcion,
                    'premio_documento' => $premio->premio_documento,
                    'campania_premio_puntaje' => $campania_premio ? (float)$campania_premio->campania_premio_puntaje : 0,
                    'cantidad' => $cantidad_actual
                ];

                $nuevas_cantidades[$id_premio] = $cantidad_actual;
                $nuevos_select_premios[] = $id_premio;
            }
        }

        // Mantener los premios seleccionados manualmente que pertenecen a esta campaña
        foreach ($this->premios_seleccionados as $id_premio => $premio) {
            // Verificar si el premio pertenece a la campaña actual y no está ya canjeado
            $existe_en_campania = $this->campaniapremio
                ->where('id_campania', $this->id_campania)
                ->where('id_premio', $id_premio)
                ->exists();

            if ($existe_en_campania && !isset($this->premios_ya_canjeados[$id_premio])) {
                $nuevos_premios_seleccionados[$id_premio] = $premio;
                $nuevas_cantidades[$id_premio] = $this->cantidades[$id_premio] ?? 1;
                if (!in_array($id_premio, $nuevos_select_premios)) {
                    $nuevos_select_premios[] = $id_premio;
                }
            }
        }

        $this->premios_seleccionados = $nuevos_premios_seleccionados;
        $this->cantidades = $nuevas_cantidades;
        $this->select_premios = $nuevos_select_premios;

        // Recalcular puntos
        $this->calcularPuntos();
    }

    public function save_canjear_puntos(){
        try {
            // Validar permiso
            if (!Gate::allows('save_canjear_puntos')) {
                session()->flash('error_modal', 'No tiene permisos para canjear.');
                return;
            }

            // Validar que los puntos canjeados no superen los puntos ganados
            if ($this->puntos_restantes < 0) {
                session()->flash('error_modal', 'No puede canjear más puntos de los que tiene disponibles.');
                return;
            }

            // Validar que se haya seleccionado al menos un premio
            if (count($this->premios_seleccionados) === 0) {
                session()->flash('error_modal', 'Debe seleccionar al menos un premio para canjear.');
                return;
            }

            // Validar que haya una campaña seleccionada
            if (empty($this->id_campania)) {
                session()->flash('error_modal', 'Debe seleccionar una campaña.');
                return;
            }

            DB::beginTransaction();

            $microtime = microtime(true);
            $id_canjear_punto = null;

            // Verificar si ya existe un registro de canje para esta campaña y usuario
            $canjeExistente = $this->canjearpunto
                ->where('id_campania', $this->id_campania)
                ->where('id_users', $this->id_users)
                ->where('canjear_punto_estado', 1)
                ->first();

            if ($canjeExistente) {
                // ACTUALIZAR registro existente
//                $canjeExistente->canjear_punto_pts_ganado = $this->puntos_ganados;
//                $canjeExistente->canjear_punto_pts_canjeado = $this->puntos_canjeados;
//                $canjeExistente->canjear_punto_pts_restante = $this->puntos_restantes;
                $canjeExistente->canjear_punto_microtime = $microtime;
                $canjeExistente->save();

                $id_canjear_punto = $canjeExistente->id_canjear_punto;
            } else {
                // CREAR nuevo registro
                $save_canjear = new Canjearpunto();
                $save_canjear->id_users = $this->id_users;
                $save_canjear->id_campania = $this->id_campania;
                $save_canjear->canjear_punto_pts_ganado = $this->puntos_ganados;
                $save_canjear->canjear_punto_pts_canjeado = $this->puntos_canjeados;
                $save_canjear->canjear_punto_pts_restante = $this->puntos_restantes;
                $save_canjear->canjear_punto_microtime = $microtime;
                $save_canjear->canjear_punto_estado = 1;
                $save_canjear->save();

                $id_canjear_punto = $save_canjear->id_canjear_punto;
            }

            // Procesar cada premio seleccionado
            foreach ($this->premios_seleccionados as $premio) {
                $id_premio = $premio['id_premio'];
                $cantidad_nueva = (int)$premio['cantidad'];
                $puntos_unitarios = (float)$premio['campania_premio_puntaje'];
                $total_puntos = $puntos_unitarios * $cantidad_nueva;

                // Verificar si ya existe un detalle para este premio
                $detalleExistente = $this->canjearpuntodetalle
                    ->where('id_canjear_punto', $id_canjear_punto)
                    ->where('id_premio', $id_premio)
                    ->where('canjear_punto_detalle_estado', 1)
                    ->first();

                if ($detalleExistente) {
                    // ACTUALIZAR detalle existente solo si cambió la cantidad
                    if ($detalleExistente->canjear_punto_detalle_cantidad != $cantidad_nueva) {
                        $detalleExistente->canjear_punto_detalle_cantidad = $cantidad_nueva;
                        $detalleExistente->canjear_punto_detalle_total_puntos = $total_puntos;
                        $detalleExistente->canjear_punto_detalle_microtime = $microtime;
                        $detalleExistente->save();
                    }
                } else {
                    // CREAR nuevo detalle para premio que no existía antes
                    $nuevoDetalle = new Canjearpuntodetalle();
                    $nuevoDetalle->id_users = $this->id_users;
                    $nuevoDetalle->id_canjear_punto = $id_canjear_punto;
                    $nuevoDetalle->id_premio = $id_premio;
                    $nuevoDetalle->canjear_punto_detalle_pts_unitario = $puntos_unitarios;
                    $nuevoDetalle->canjear_punto_detalle_cantidad = $cantidad_nueva;
                    $nuevoDetalle->canjear_punto_detalle_total_puntos = $total_puntos;
                    $nuevoDetalle->canjear_punto_detalle_microtime = $microtime;
                    $nuevoDetalle->canjear_punto_detalle_estado = 1;
                    $nuevoDetalle->save();
                }
            }

            // Actualizar los puntos del vendedor en vendedores_intranet
            $user = DB::table('users')
                ->where('id_users', $this->id_users)
                ->select('id_vendedor_intranet')
                ->first();

            if ($user && !empty($user->id_vendedor_intranet)) {
                DB::table('vendedores_intranet')
                    ->where('id_vendedor_intranet', $user->id_vendedor_intranet)
                    ->update(['vendedor_intranet_punto' => $this->puntos_restantes]);
            }

            DB::commit();

            // Determinar mensaje según si fue actualización o creación
            $mensaje = $canjeExistente ? 'Canje actualizado exitosamente.' : 'Puntos canjeados exitosamente.';
            session()->flash('success_modal', $mensaje);

            $this->dispatch('hide_modal_ver_seleccion');

            // Actualizar datos después del guardado
            $this->obtenerPremiosCanjeados();
            $this->actualizarPremiosSeleccionados();
            $this->obtenerPuntosVendedor();
            $this->calcularPuntos();
            $this->id_campania = "";

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            $this->setErrorBag($e->validator->errors());
            session()->flash('error_modal', 'Error de validación: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_modal', 'Ocurrió un error al procesar el canje. Por favor, inténtelo nuevamente.');
        }
    }

}
