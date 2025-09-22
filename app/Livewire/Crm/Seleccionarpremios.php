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
    public $campania_cerrada = false;
    public $premios_canjeados_deseleccionados = [];
    public $campania_fecha_fin = "";
    public function mount(){
        $this->id_users = Auth::id();
        $this->puntos_ganados   = 0;
        $this->puntos_canjeados = 0;
        $this->puntos_restantes = 0;
    }

    public function render(){
        $this->id_users = $this->id_users ?: Auth::id();
        $this->listar_premios_disponibles = [];

        if (!empty($this->id_campania)) {
            $this->campania_seleccionada = Campania::find($this->id_campania);

            // Campaña cerrada por fecha
            $this->campania_cerrada = false;
            if ($this->campania_seleccionada && $this->campania_seleccionada->campania_fecha_fin_canje) {
                $hoy = now('America/Lima')->startOfDay();
                $fin = \Carbon\Carbon::parse($this->campania_seleccionada->campania_fecha_fin_canje)->endOfDay();
                $this->campania_cerrada = $hoy->gt($fin);
            }

            $fecha_finc = DB::table('campanias')->where('id_campania', '=', $this->id_campania)->first();
            $this->campania_fecha_fin = $fecha_finc->campania_fecha_fin_canje;

            $this->listar_premios_disponibles = $this->premio->listar_campanias_activos($this->id_campania);
            $this->obtenerPremiosCanjeados();
            $this->actualizarPremiosSeleccionados();
        }
        // Calcular puntos ganados de la campaña actual
        $this->obtenerPuntosVendedor();
        // Mantener tu cálculo
        $this->calcularPuntos();
        // Campañas filtradas por id_users
        $listar_campania = $this->campania->listar_campanias_activos_new($this->id_users);

        return view('livewire.crm.seleccionarpremios', compact('listar_campania'));
    }

    public function updatedIdCampania($value){
        $this->resetSeleccionLocal();
        $this->puntos_ganados   = 0;
        $this->puntos_canjeados = 0;
        $this->puntos_restantes = 0;
        // vigentes en BD (y baseline de canjeados)
        $this->obtenerPremiosCanjeados();

        // autoselección visual (sigue igual, usa baseline)
        $this->actualizarPremiosSeleccionados();

        // ganados por campaña (como implementaste por puntos_detalles)
        $this->obtenerPuntosVendedor();

        // restantes = ganados - canjeados_vigentes - nuevos
        $this->calcularPuntos();
    }

    public function resetSeleccionLocal(){
        $this->select_premios = [];
        $this->premios_seleccionados = [];
        $this->cantidades = [];
        $this->premios_canjeados_deseleccionados = [];
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
        $this->puntos_canjeados = 0;

        if (!empty($this->id_campania) && !empty($this->id_users)) {
            $canjeExistente = $this->canjearpunto
                ->where('id_campania', $this->id_campania)
                ->where('id_users', $this->id_users)
                ->where('canjear_punto_estado', 1)
                ->first();

            if ($canjeExistente) {
                $this->id_canjear_punto_actual = $canjeExistente->id_canjear_punto;

                $detallesCanje = $this->canjearpuntodetalle
                    ->where('id_canjear_punto', $this->id_canjear_punto_actual)
                    ->where('canjear_punto_detalle_estado', 1)
                    ->get();

                $totalCanjeadoVigente = 0;

                foreach ($detallesCanje as $detalle) {
                    $this->premios_ya_canjeados[$detalle->id_premio] = [
                        'id_premio' => $detalle->id_premio,
                        'cantidad' => (int)$detalle->canjear_punto_detalle_cantidad,
                        'puntos_unitarios' => (float)$detalle->canjear_punto_detalle_pts_unitario,
                    ];
                    $totalCanjeadoVigente +=
                        (float)$detalle->canjear_punto_detalle_pts_unitario *
                        (int)$detalle->canjear_punto_detalle_cantidad;
                }

                // ahora sí: canjeados = vigentes en BD
                $this->puntos_canjeados = (float) $totalCanjeadoVigente;
            }
        }
    }

    public function calcularPuntos(){
        $puntosNuevos = 0;

        foreach ($this->premios_seleccionados as $id_premio => $premio) {
            if (($premio['id_campania'] ?? null) !== $this->id_campania) continue;

            $unit    = (float)($premio['campania_premio_puntaje'] ?? 0);
            $cantSel = (int)($premio['cantidad'] ?? 0);

            if (isset($this->premios_ya_canjeados[$id_premio])
                && !in_array($id_premio, $this->premios_canjeados_deseleccionados ?? [], true)) {
                $base = (int)($this->premios_ya_canjeados[$id_premio]['cantidad'] ?? 0);
                $puntosNuevos += max(0, $cantSel - $base) * $unit;
            } else {
                $puntosNuevos += $unit * $cantSel;
            }
        }

        $this->puntos_restantes = $this->puntos_ganados - $puntosNuevos - $this->puntos_canjeados;

        if ($this->puntos_restantes < 0) {
            session()->flash('error_modal', 'Los puntos seleccionados exceden tu saldo disponible.');
        }
    }

    public function obtenerPuntosVendedor(){
        try {
            if (empty($this->id_campania)) {
                $this->puntos_ganados   = 0;
                $this->puntos_canjeados = 0;
                $this->puntos_restantes = 0;
                return;
            }

            // User -> id_vendedor_intranet
            $user = DB::table('users')
                ->where('id_users', $this->id_users ?: Auth::id())
                ->select('id_vendedor_intranet')
                ->first();

            if (!$user || empty($user->id_vendedor_intranet)) {
                $this->puntos_ganados = 0;
                return;
            }

            $vend = DB::table('vendedores_intranet')
                ->where('id_vendedor_intranet', $user->id_vendedor_intranet)
                ->select('id_cliente', 'vendedor_intranet_dni')
                ->first();

            if (!$vend) {
                $this->puntos_ganados = 0;
                return;
            }

            // IDs de puntos de la campaña para el mismo cliente
            $puntosIds = DB::table('puntos')
                ->where('id_campania', $this->id_campania)
                ->where('id_cliente',  $vend->id_cliente)
                ->pluck('id_punto');

            if ($puntosIds->isEmpty()) {
                $this->puntos_ganados = 0;
                return;
            }

            // Total ganado (bruto) por DNI en puntos_detalles
            $totalGanadoBruto = DB::table('puntos_detalles')
                ->whereIn('id_punto', $puntosIds)
                ->where('punto_detalle_vendedor', $vend->vendedor_intranet_dni)
                ->sum('punto_detalle_punto_ganado');

            $neto = (float)$totalGanadoBruto;
            $this->puntos_ganados = max(0, $neto);

        } catch (\Throwable $e) {
            \Log::error('Error obteniendo puntos del vendedor por campaña: ' . $e->getMessage());
            $this->puntos_ganados = 0;
        }
    }

    public function seleccionar_premio($id_premio, $isChecked){
        if ($this->campania_cerrada) return;

        if ($isChecked) {
            $wasDeseleccionado = in_array($id_premio, $this->premios_canjeados_deseleccionados, true);

            // Al volver a seleccionar, sácalo de la lista de deseleccionados de sesión
            if ($wasDeseleccionado) {
                $this->premios_canjeados_deseleccionados = array_values(array_diff(
                    $this->premios_canjeados_deseleccionados, [$id_premio]
                ));
            }

            $premio = $this->premio->find($id_premio);
            if ($premio) {
                $campania_premio = $this->campaniapremio
                    ->where('id_campania', $this->id_campania)
                    ->where('id_premio', $id_premio)
                    ->first();

                // Si fue deseleccionado (era canjeado), vuelve como NUEVO
                if ($wasDeseleccionado) {
                    $cantidad_inicial = 1;
                } else {
                    $cantidad_inicial = isset($this->premios_ya_canjeados[$id_premio])
                        ? (int)$this->premios_ya_canjeados[$id_premio]['cantidad']
                        : 1;
                }

                $this->premios_seleccionados[$id_premio] = [
                    'id_campania' => $this->id_campania,
                    'id_premio' => $premio->id_premio,
                    'premio_codigo' => $premio->premio_codigo,
                    'premio_descripcion' => $premio->premio_descripcion,
                    'premio_documento' => $premio->premio_documento,
                    'campania_premio_puntaje' => $campania_premio ? (float)$campania_premio->campania_premio_puntaje : 0,
                    'cantidad' => $cantidad_inicial
                ];
                $this->cantidades[$id_premio] = $cantidad_inicial;

                if (!in_array($id_premio, $this->select_premios, true)) {
                    $this->select_premios[] = $id_premio;
                }
            }
        } else {
            if (isset($this->premios_ya_canjeados[$id_premio])) {
                $ok = $this->revertirCanjeEnBD($id_premio);
                if ($ok) {
                    // Actualiza baseline y vigentes desde BD
                    $this->obtenerPremiosCanjeados();
                    // Limpia selección visual
                    unset($this->premios_ya_canjeados[$id_premio]);
                    if (!in_array($id_premio, $this->premios_canjeados_deseleccionados, true)) {
                        $this->premios_canjeados_deseleccionados[] = $id_premio;
                    }
                } else {
                    $this->calcularPuntos();
                    return;
                }
            }

            unset($this->premios_seleccionados[$id_premio], $this->cantidades[$id_premio]);
            $this->select_premios = array_values(array_diff($this->select_premios, [$id_premio]));

            // Recalcular usando vigentes actualizados
            $this->obtenerPuntosVendedor();
            $this->calcularPuntos();
        }
        // refrescar puntos ganados desde BD (pueden haber cambiado por devolución)
        $this->obtenerPuntosVendedor();
        $this->calcularPuntos();
    }

    public function actualizarCantidad($id_premio, $cantidad){
        if (!isset($this->premios_seleccionados[$id_premio])) return;
        if ($this->campania_cerrada) return;

        $cantidad = max(0, (int)$cantidad);
        $this->premios_seleccionados[$id_premio]['cantidad'] = $cantidad;
        $this->cantidades[$id_premio] = $cantidad;

        // Si cantidad llega a 0, manejar según si era un premio ya canjeado o no
        if ($cantidad === 0) {
            // Si era un premio ya canjeado en BD, mantenerlo para procesarlo en save_canjear_puntos
            if (isset($this->premios_ya_canjeados[$id_premio])) {
                // Mantener en premios_seleccionados pero con cantidad 0 para procesarlo después
                // Desmarcar el checkbox visualmente
                $this->select_premios = array_values(array_diff($this->select_premios, [$id_premio]));

                // Registrar que fue deseleccionado en esta sesión
                if (!in_array($id_premio, $this->premios_canjeados_deseleccionados, true)) {
                    $this->premios_canjeados_deseleccionados[] = $id_premio;
                }
            } else {
                // Si no era canjeado, eliminarlo completamente
                $this->select_premios = array_values(array_diff($this->select_premios, [$id_premio]));
                unset($this->premios_seleccionados[$id_premio], $this->cantidades[$id_premio]);
            }
        } else {
            // Si la cantidad es mayor a 0, asegurarse de que esté seleccionado
            if (!in_array($id_premio, $this->select_premios, true)) {
                $this->select_premios[] = $id_premio;
            }

            // Si estaba deseleccionado y ahora tiene cantidad > 0, quitarlo de deseleccionados
            $this->premios_canjeados_deseleccionados = array_values(array_diff(
                $this->premios_canjeados_deseleccionados, [$id_premio]
            ));
        }

        $this->calcularPuntos();
    }

    public function actualizarPremiosSeleccionados(){
        $nuevos_premios_seleccionados = [];
        $nuevas_cantidades = [];
        $nuevos_select_premios = [];

        // Autoseleccionar los que ya estaban canjeados (salvo que el usuario los quitó en esta sesión)
        foreach ($this->premios_ya_canjeados as $id_premio => $premio_canjeado) {
            if (in_array($id_premio, $this->premios_canjeados_deseleccionados, true)) {
                // usuario los quitó en esta sesión NO autoseleccionar
                continue;
            }

            $premio = $this->premio->find($id_premio);
            if ($premio) {
                $campania_premio = $this->campaniapremio
                    ->where('id_campania', $this->id_campania)
                    ->where('id_premio', $id_premio)
                    ->first();

                // Si ya estaba en selección, mantener la mayor cantidad (por si el user la subió)
                $cantidad_actual = $premio_canjeado['cantidad'];
                if (isset($this->premios_seleccionados[$id_premio])) {
                    $cantidad_actual = max($cantidad_actual, (int)$this->premios_seleccionados[$id_premio]['cantidad']);
                }

                $nuevos_premios_seleccionados[$id_premio] = [
                    'id_campania' => $this->id_campania,
                    'id_premio' => $premio->id_premio,
                    'premio_codigo' => $premio->premio_codigo,
                    'premio_descripcion' => $premio->premio_descripcion,
                    'premio_documento' => $premio->premio_documento,
                    'campania_premio_puntaje' => $campania_premio ? (float)$campania_premio->campania_premio_puntaje : 0,
                    'cantidad' => (int)$cantidad_actual
                ];

                $nuevas_cantidades[$id_premio] = (int)$cantidad_actual;
                $nuevos_select_premios[] = $id_premio;
            }
        }

        // Mantener los que el usuario seleccionó manualmente y que pertenecen a esta campaña
        foreach ($this->premios_seleccionados as $id_premio => $premio) {
            $existe_en_campania = $this->campaniapremio
                ->where('id_campania', $this->id_campania)
                ->where('id_premio', $id_premio)
                ->exists();

            if ($existe_en_campania) {
                // Si el user lo había quitado siendo canjeado, y ahora vuelve a estar, será "nuevo"
                $nuevos_premios_seleccionados[$id_premio] = $premio;
                $nuevas_cantidades[$id_premio] = (int)($this->cantidades[$id_premio] ?? $premio['cantidad'] ?? 1);

                if (!in_array($id_premio, $nuevos_select_premios, true)) {
                    $nuevos_select_premios[] = $id_premio;
                }
            }
        }

        $this->premios_seleccionados = $nuevos_premios_seleccionados;
        $this->cantidades = $nuevas_cantidades;
        $this->select_premios = $nuevos_select_premios;

        $this->calcularPuntos();
    }

    public function revertirCanjeEnBD($id_premio): bool{
        // Busca user->id_vendedor_intranet
        $user = DB::table('users')
            ->where('id_users', $this->id_users)
            ->select('id_vendedor_intranet')
            ->first();

        if (!$user || empty($user->id_vendedor_intranet)) {
            session()->flash('error_modal', 'vendedor no encontrado');
            return false;
        }

        // Busca canje cabecera vigente
        $canje = $this->canjearpunto
            ->where('id_campania', $this->id_campania)
            ->where('id_users', $this->id_users)
            ->where('canjear_punto_estado', 1)
            ->first();

        if (!$canje) {
            session()->flash('error_modal', 'No se encontró el registro de canje activo.');
            return false;
        }

        // Busca el detalle canjeado (estado=1)
        $detalle = $this->canjearpuntodetalle
            ->where('id_canjear_punto', $canje->id_canjear_punto)
            ->where('id_premio', $id_premio)
            ->where('canjear_punto_detalle_estado', 1)
            ->first();

        if (!$detalle) {
            session()->flash('error_modal', 'No se encontró el detalle canjeado para este premio.');
            return false;
        }

        // Puntos a devolver
        $puntosDevolver = (float)$detalle->canjear_punto_detalle_pts_unitario * (int)$detalle->canjear_punto_detalle_cantidad;

        DB::beginTransaction();
        try {
            // devolver puntos al vendedor
            DB::table('vendedores_intranet')
                ->where('id_vendedor_intranet', $user->id_vendedor_intranet)
                ->update([
                    'vendedor_intranet_punto' => DB::raw("vendedor_intranet_punto + {$puntosDevolver}")
                ]);

            // desactivar detalle
            DB::table('canjear_puntos_detalles')
                ->where('id_canjear_punto', $canje->id_canjear_punto)
                ->where('id_premio', $id_premio)
                ->where('canjear_punto_detalle_estado', 1)
                ->update([
                    'canjear_punto_detalle_estado' => 0,
                    'canjear_punto_detalle_microtime' => microtime(true),
                ]);

            DB::commit();
            session()->flash('success_modal', 'Premio eliminado correctamente.');
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_modal', 'No se pudo revertir el canje. Inténtelo nuevamente.');
            return false;
        }
    }

    public function eliminarPremio($id_premio){
        if ($this->campania_cerrada) return;

        if (isset($this->premios_ya_canjeados[$id_premio])) {
            $ok = $this->revertirCanjeEnBD($id_premio);
            if ($ok) {
                // Refrescar vigentes en BD
                $this->obtenerPremiosCanjeados();
                unset($this->premios_ya_canjeados[$id_premio]);
                if (!in_array($id_premio, $this->premios_canjeados_deseleccionados, true)) {
                    $this->premios_canjeados_deseleccionados[] = $id_premio;
                }
            } else {
                $this->calcularPuntos();
                return;
            }
        }

        unset($this->premios_seleccionados[$id_premio], $this->cantidades[$id_premio]);
        $this->select_premios = array_values(array_diff($this->select_premios, [$id_premio]));

        $this->obtenerPuntosVendedor();
        $this->calcularPuntos();
    }

    public function save_canjear_puntos(){
        try {
            if (!Gate::allows('save_canjear_puntos')) {
                session()->flash('error_modal', 'No tiene permisos para canjear.');
                return;
            }

            if ($this->puntos_restantes < 0) {
                session()->flash('error_modal', 'No puede canjear más puntos de los que tiene disponibles.');
                return;
            }

            // Verificar si hay premios seleccionados O premios canjeados para procesar
            $hayPremiosParaProcesar = count($this->premios_seleccionados) > 0 ||
                count($this->premios_canjeados_deseleccionados) > 0;

            if (!$hayPremiosParaProcesar) {
                $canjeExistente = $this->canjearpunto
                    ->where('id_campania', $this->id_campania)
                    ->where('id_users', $this->id_users)
                    ->where('canjear_punto_estado', 1)
                    ->first();

                if (!$canjeExistente) {
                    session()->flash('error_modal', 'Debe seleccionar al menos un premio para canjear.');
                    return;
                }
            }

            if (empty($this->id_campania)) {
                session()->flash('error_modal', 'Debe seleccionar una campaña.');
                return;
            }

            DB::beginTransaction();

            $microtime = microtime(true);
            $id_canjear_punto = null;

            $canjeExistente = $this->canjearpunto
                ->where('id_campania', $this->id_campania)
                ->where('id_users', $this->id_users)
                ->where('canjear_punto_estado', 1)
                ->first();

            if ($canjeExistente) {
                $canjeExistente->canjear_punto_microtime = $microtime;
                $canjeExistente->save();
                $id_canjear_punto = $canjeExistente->id_canjear_punto;
            } else {
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

            // Obtener vendedor para devolver puntos si es necesario
            $user = DB::table('users')
                ->where('id_users', $this->id_users)
                ->select('id_vendedor_intranet')
                ->first();

            // NUEVA VALIDACIÓN: Verificar premios con cantidad 0 o deseleccionados
            if ($canjeExistente) {
                $detallesExistentes = $this->canjearpuntodetalle
                    ->where('id_canjear_punto', $id_canjear_punto)
                    ->where('canjear_punto_detalle_estado', 1)
                    ->get();

                foreach ($detallesExistentes as $detalleExistente) {
                    $id_premio_existente = $detalleExistente->id_premio;

                    // Verificar si el premio fue deseleccionado o tiene cantidad 0
                    $premioDeseleccionado = in_array($id_premio_existente, $this->premios_canjeados_deseleccionados, true);
                    $premioEnSeleccion = collect($this->premios_seleccionados)->firstWhere('id_premio', $id_premio_existente);
                    $cantidadNueva = $premioEnSeleccion ? (int)$premioEnSeleccion['cantidad'] : 0;

                    if ($premioDeseleccionado || $cantidadNueva === 0) {
                        // Calcular puntos a devolver
                        $puntosDevolver = (float)$detalleExistente->canjear_punto_detalle_pts_unitario *
                            (int)$detalleExistente->canjear_punto_detalle_cantidad;

                        // Devolver puntos al vendedor
                        if ($user && !empty($user->id_vendedor_intranet)) {
                            DB::table('vendedores_intranet')
                                ->where('id_vendedor_intranet', $user->id_vendedor_intranet)
                                ->update([
                                    'vendedor_intranet_punto' => DB::raw("vendedor_intranet_punto + {$puntosDevolver}")
                                ]);
                        }

                        // Desactivar el detalle (estado = 0)
                        $detalleExistente->canjear_punto_detalle_estado = 0;
                        $detalleExistente->canjear_punto_detalle_microtime = $microtime;
                        $detalleExistente->save();
                    }
                }
            }

            // Procesar detalles de premios seleccionados (cantidad > 0)
            foreach ($this->premios_seleccionados as $premio) {
                $id_premio = $premio['id_premio'];
                $cantidad_nueva = (int)$premio['cantidad'];

                // Saltar si la cantidad es 0
                if ($cantidad_nueva === 0) {
                    continue;
                }

                $puntos_unitarios = (float)$premio['campania_premio_puntaje'];
                $total_puntos = $puntos_unitarios * $cantidad_nueva;

                $detalleExistente = $this->canjearpuntodetalle
                    ->where('id_canjear_punto', $id_canjear_punto)
                    ->where('id_premio', $id_premio)
                    ->where('canjear_punto_detalle_estado', 1)
                    ->first();

                if ($detalleExistente) {
                    if ($detalleExistente->canjear_punto_detalle_cantidad != $cantidad_nueva) {
                        $detalleExistente->canjear_punto_detalle_cantidad = $cantidad_nueva;
                        $detalleExistente->canjear_punto_detalle_total_puntos = $total_puntos;
                        $detalleExistente->canjear_punto_detalle_microtime = $microtime;
                        $detalleExistente->save();
                    }
                } else {
                    $nuevoDetalle = new Canjearpuntodetalle();
                    $nuevoDetalle->id_users = $this->id_users;
                    $nuevoDetalle->id_canjear_punto = $id_canjear_punto;
                    $nuevoDetalle->id_premio = $id_premio;
                    $nuevoDetalle->canjear_punto_detalle_pts_unitario= $puntos_unitarios;
                    $nuevoDetalle->canjear_punto_detalle_cantidad = $cantidad_nueva;
                    $nuevoDetalle->canjear_punto_detalle_total_puntos= $total_puntos;
                    $nuevoDetalle->canjear_punto_detalle_microtime = $microtime;
                    $nuevoDetalle->canjear_punto_detalle_estado = 1;
                    $nuevoDetalle->save();
                }
            }

            // Actualizar saldo del vendedor con los puntos restantes finales
            if ($user && !empty($user->id_vendedor_intranet)) {
                DB::table('vendedores_intranet')
                    ->where('id_vendedor_intranet', $user->id_vendedor_intranet)
                    ->update(['vendedor_intranet_punto' => $this->puntos_restantes]);
            }

            DB::commit();

            $mensaje = $canjeExistente ? 'Canje actualizado exitosamente.' : 'Puntos canjeados exitosamente.';
            session()->flash('success_modal', $mensaje);

            $this->obtenerPuntosVendedor();
            $this->premios_seleccionados = [];
            $this->select_premios = [];
            $this->cantidades = [];
            $this->premios_canjeados_deseleccionados = [];
            $this->calcularPuntos();
            $this->id_campania = "";
            $this->dispatch('hide_modal_ver_seleccion');

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
