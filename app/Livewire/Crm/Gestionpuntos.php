<?php

namespace App\Livewire\Crm;

use App\Livewire\Intranet\Navegation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\Logs;
use App\Models\Punto;
use App\Models\Puntodetalle;
use App\Models\General;
use App\Models\Campania;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Gestionpuntos extends Component{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads;
    private $logs;
    private $punto;
    private $puntodetalle;
    private $general;
    private $campania;

    public function __construct(){
        $this->logs = new Logs();
        $this->punto = new Punto();
        $this->puntodetalle = new Puntodetalle();
        $this->general = new General();
        $this->campania = new Campania();
    }
    public $id_punto = "";
    public $id_punto_detalle = "";
    public $archivo_excel;
    public $archivo_pdf;
    public $listar_detalles = [];
    public $editando_registros = [];
    public $datos_edicion = [];
    public $listar_campanias = [];
    public $punto_codigo = "";
    public $id_campania_busqueda = "";
    public $id_cliente_busqueda = "";
    public $search_puntos;
    public $id_campania = "";
    public $id_cliente = "";
    public $abrirListasCliente = false;
    public $buscar_clientes = null;
    public $buscar_clientes_search = null;
    public $listaClientesFiltro = array();
    public $abrirListasClienteModal = false;
    public function mount(){
        $this->listar_campanias = DB::table('campanias')
            ->where('campania_estado', 1)
            ->orderBy('campania_nombre')
            ->get();
    }

    public function render(){
        $listar_puntos = $this->punto->listar_puntos_registrados($this->id_campania_busqueda, $this->id_cliente_busqueda, $this->search_puntos);
        foreach ($listar_puntos as $lp){
            $lp->puntos_detalles = DB::table('puntos_detalles')
                ->where('punto_detalle_estado', '=', 1)
                ->where('id_punto', '=', $lp->id_punto)
                ->get();
        }

        $listar_campania_formulario = $this->campania->listar_campanias_activos();
        return view('livewire.crm.gestionpuntos', compact('listar_puntos', 'listar_campania_formulario'));
    }

    // MÉTODOS ESPECÍFICOS PARA EL MODAL
    public function buscarClientesFiltroModal(){
        try {
            $buscar = $this->buscar_clientes ?? '';

            $this->listaClientesFiltro = DB::table('clientes')
                ->where('cliente_estado_registro','=', 1)
                ->where(function($q) use ($buscar) {
                    $q->where('cliente_codigo_cliente', 'like', '%' . $buscar . '%')
                        ->orWhere('cliente_nombre_cliente', 'like', '%' . $buscar . '%');
                })
                ->limit(10)
                ->get();

            $this->abrirListasClienteModal = true;

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return;
        }
    }
    public function seleccionar_cliente_modal($id_cliente){
        try {
            $this->abrirListasClienteModal = false;
            $id_c = base64_decode($id_cliente);

            if ($id_c) {
                $data = DB::table('clientes')
                    ->where('id_cliente', '=', $id_c)
                    ->first();

                // Asignar siempre al modal
                $this->buscar_clientes = $data->cliente_codigo_cliente . ' - ' . $data->cliente_nombre_cliente;
                $this->id_cliente = $id_c;

            } else {
                session()->flash('error', 'Los parámetros del cliente no son válidos.');
                return;
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return;
        }
    }

    // MÉTODOS ESPECÍFICOS PARA LA VISTA PRINCIPAL
    public function buscarClientesFiltroVista(){
        try {
            $buscar = $this->buscar_clientes_search ?? '';

            $this->listaClientesFiltro = DB::table('clientes')
                ->where('cliente_estado_registro','=', 1)
                ->where(function($q) use ($buscar) {
                    $q->where('cliente_codigo_cliente', 'like', '%' . $buscar . '%')
                        ->orWhere('cliente_nombre_cliente', 'like', '%' . $buscar . '%');
                })
                ->limit(10)
                ->get();

            $this->abrirListasCliente = true;

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return;
        }
    }
    public function seleccionar_cliente_vista($id_cliente){
        try {
            $this->abrirListasCliente = false;
            $id_c = base64_decode($id_cliente);

            if ($id_c) {
                $data = DB::table('clientes')
                    ->where('id_cliente', '=', $id_c)
                    ->first();

                // Asignar siempre a la vista principal
                $this->buscar_clientes_search = $data->cliente_codigo_cliente . ' - ' . $data->cliente_nombre_cliente;
                $this->id_cliente_busqueda = $id_c;

            } else {
                session()->flash('error', 'Los parámetros del cliente no son válidos.');
                return;
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return;
        }
    }

    public function clear_form(){
        $this->id_punto = "";
        $this->archivo_excel = "";
        $this->archivo_pdf = "";
        $this->buscar_clientes = "";
        $this->id_cliente = "";
        $this->id_campania = "";
    }

    public function save_carga_excel(){
        try {
            $this->validate([
                'id_campania' => 'required|integer',
                'id_cliente'  => 'required|integer',
            ], [
                'id_campania.required' => 'La campaña es un dato obligatorio.',
                'id_campania.integer'  => 'El identificador debe ser un número entero.',
                'id_cliente.required'  => 'El cliente es un dato obligatorio.',
                'id_cliente.integer'   => 'El identificador debe ser un número entero.',
            ]);

            if (!Gate::allows('save_carga_excel')) {
                session()->flash('error_modal', 'No tiene permisos para crear.');
                return;
            }

            if (!$this->archivo_excel) {
                session()->flash('error', 'El archivo Excel es obligatorio.');
                return;
            }

            $extension = strtolower($this->archivo_excel->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls'])) {
                session()->flash('error_modal', 'El archivo debe ser de tipo Excel (xlsx, xls).');
                return;
            }

            $microtime = microtime(true);
            DB::beginTransaction();

            // Reusar/crear cabecera "puntos"
            $punto = Punto::where('id_campania', $this->id_campania)
                ->where('id_cliente',  $this->id_cliente)
                ->where('punto_estado', 1)
                ->first();

            if (!$punto) {
                $ultimo = Punto::orderBy('id_punto', 'desc')->first();
                $codigo_nuevo = $ultimo ? $ultimo->id_punto + 1 : 1;

                $punto = new Punto();
                $punto->id_users   = Auth::id();
                $punto->id_campania = $this->id_campania;
                $punto->id_cliente  = $this->id_cliente;
                $punto->punto_codigo = 'P-000' . $codigo_nuevo;

                if ($this->archivo_excel) {
                    $punto->punto_documento_excel = $this->general->save_files($this->archivo_excel, 'puntos/excel');
                }
                if ($this->archivo_pdf) {
                    $punto->punto_documento_pdf = $this->general->save_files($this->archivo_pdf, 'puntos/pdf');
                }

                $punto->punto_microtime = $microtime;
                $punto->punto_estado    = 1;
                $punto->save();
            }

            $id_punto_a_usar = $punto->id_punto;

            // Procesar Excel
            $spreadsheet = IOFactory::load($this->archivo_excel->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            foreach (array_slice($rows, 1) as $row) {
                $dni    = $row[0] ?? null;
                $motivo = $row[1] ?? null;
                $rawPts = $row[2] ?? null;

                if (!$dni || !$motivo || $rawPts === null || $rawPts === '') {
                    continue;
                }

                // ==== Normalización inline de "puntos" ====
                $s = trim((string)$rawPts);
                if ($s === '') { continue; }
                $s = preg_replace('/\s+/', '', $s);

                $hasComma = strpos($s, ',') !== false;
                $hasDot   = strpos($s, '.') !== false;

                if ($hasComma && $hasDot) {
                    $lastComma = strrpos($s, ',');
                    $lastDot   = strrpos($s, '.');
                    if ($lastComma > $lastDot) {
                        // coma como decimal -> quitar puntos (miles) y cambiar coma por punto
                        $s = str_replace('.', '', $s);
                        $s = str_replace(',', '.', $s);
                    } else {
                        // punto como decimal -> quitar comas (miles)
                        $s = str_replace(',', '', $s);
                    }
                } elseif ($hasComma) {
                    // solo comas: decidir si miles o decimal por longitud del último tramo
                    $parts = explode(',', $s);
                    $lastLen = strlen(end($parts));
                    if (count($parts) > 1 && $lastLen === 3) {
                        $s = str_replace(',', '', $s); // miles
                    } else {
                        $s = str_replace(',', '.', $s); // decimal
                    }
                } elseif ($hasDot) {
                    // solo puntos: decidir si miles o decimal
                    $parts = explode('.', $s);
                    $lastLen = strlen(end($parts));
                    if (count($parts) > 1 && $lastLen === 3) {
                        $s = str_replace('.', '', $s); // miles
                    }
                    // si es decimal ya está correcto
                }

                // Validar que quedó como número válido
                if (!preg_match('/^-?\d+(\.\d+)?$/', $s)) {
                    // Si no es interpretable, saltamos la fila
                    continue;
                }

                // Convertir a float (puedes redondear si lo prefieres)
                $puntos = (float)$s;
                // $puntos = round((float)$s, 2); // <- si quieres limitar a 2 decimales

                // Guardar detalle
                $detalle = new Puntodetalle();
                $detalle->id_users = Auth::id();
                $detalle->id_punto = $id_punto_a_usar;
                $detalle->punto_detalle_motivo = $motivo;
                $detalle->punto_detalle_vendedor = $dni;
                $detalle->punto_detalle_punto_ganado = $puntos;
                $detalle->punto_detalle_fecha_registro = now('America/Lima')->toDateString();
                $detalle->punto_detalle_fecha_modificacion = null;
                $detalle->punto_detalle_microtime = $microtime;
                $detalle->punto_detalle_estado  = 1;
                $detalle->save();

                // Sumar puntos al vendedor si existe (incremento atómico)
                $vendedorExiste = DB::table('vendedores_intranet')
                    ->where('vendedor_intranet_dni', $dni)
                    ->where('vendedor_intranet_estado', 1)
                    ->exists();

                if ($vendedorExiste) {
                    DB::table('vendedores_intranet')
                        ->where('vendedor_intranet_dni', $dni)
                        ->where('vendedor_intranet_estado', 1)
                        ->increment('vendedor_intranet_punto', (float)$puntos, [
                            'updated_at' => now('America/Lima')
                        ]);
                }
            }

            DB::commit();
            $this->dispatch('hide_modal_carga_excel');
            session()->flash('success', 'Archivo(s) procesado(s) correctamente.');
            $this->clear_form();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_modal', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function editar_punto($id_punto){
        $this->id_punto = base64_decode($id_punto);

        if ($this->id_punto) {
            // Obtener los detalles
            $this->listar_detalles = DB::table('puntos_detalles')
                ->where('punto_detalle_estado', '=', 1)
                ->where('id_punto', '=', $this->id_punto)
                ->get();

            // Obtener el registro de punto para saber la campaña y cliente
            $punto = DB::table('puntos')
                ->where('id_punto', $this->id_punto)
                ->first();

            $this->punto_codigo = $punto->punto_codigo;

            // Establecer la campaña seleccionada si existe
            if ($punto && $punto->id_campania) {
                $this->id_campania = $punto->id_campania;

                // Verificar que la campaña aún existe
                $campania_existe = DB::table('campanias')
                    ->where('id_campania', $punto->id_campania)
                    ->where('campania_estado', 1)
                    ->exists();

                if (!$campania_existe) {
                    $this->id_campania = "";
                }
            } else {
                $this->id_campania = "";
            }

            // Establecer el cliente seleccionado si existe
            if ($punto && $punto->id_cliente) {
                $this->id_cliente = $punto->id_cliente;

                // Obtener datos del cliente para mostrar en el buscador
                $cliente = DB::table('clientes')
                    ->where('id_cliente', $punto->id_cliente)
                    ->where('cliente_estado_registro', 1)
                    ->first();

                if ($cliente) {
                    $this->buscar_clientes = $cliente->cliente_codigo_cliente . ' - ' . $cliente->cliente_nombre_cliente;
                } else {
                    $this->buscar_clientes = "";
                    $this->id_cliente = "";
                }
            } else {
                $this->buscar_clientes = "";
                $this->id_cliente = "";
            }
        }

        // Limpiar ediciones al abrir modal
        $this->editando_registros = [];
        $this->datos_edicion = [];
    }

    public function activar_edicion($id_punto_detalle){
        try {
            // Buscar el registro que se va a editar
            $detalle = DB::table('puntos_detalles')
                ->where('id_punto_detalle', $id_punto_detalle)
                ->where('punto_detalle_estado', 1)
                ->first();

            if ($detalle) {
                // Agregar el ID al array de registros en edición
                if (!in_array($id_punto_detalle, $this->editando_registros)) {
                    $this->editando_registros[] = $id_punto_detalle;
                }

                // Cargar los valores actuales en el array de datos de edición
                $this->datos_edicion[$id_punto_detalle] = [
                    'motivo' => $detalle->punto_detalle_motivo,
                    'vendedor' => $detalle->punto_detalle_vendedor,
                    'puntos' => $detalle->punto_detalle_punto_ganado
                ];
            }

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error_moda_editar', 'Error al cargar los datos para edición.');
        }
    }

    public function cancelar_edicion_registro($id_punto_detalle){
        // Remover el ID del array de registros en edición
        $this->editando_registros = array_filter($this->editando_registros, function($id) use ($id_punto_detalle) {
            return $id != $id_punto_detalle;
        });

        // Remover los datos de edición de este registro
        unset($this->datos_edicion[$id_punto_detalle]);

        // Reindexar el array
        $this->editando_registros = array_values($this->editando_registros);
    }

    public function save_editar_punto(){
        try {
            if (!Gate::allows('save_editar_punto')) {
                session()->flash('error_moda_editar', 'No tiene permisos para actualizar los detalles.');
                return;
            }

            // Actualizar cabecera si cambió
            if ($this->id_punto && $this->id_campania) {
                DB::table('puntos')
                    ->where('id_punto', $this->id_punto)
                    ->update([
                        'id_campania' => $this->id_campania,
                        'id_cliente'  => $this->id_cliente,
                    ]);
            }

            // ========= Normalizar y validar "puntos" =========
            // Guardaremos los puntos ya normalizados (float) por id_punto_detalle
            $puntos_normalizados = [];

            foreach ($this->editando_registros as $id_registro) {
                // Campos obligatorios
                if (
                    empty($this->datos_edicion[$id_registro]['motivo']) ||
                    empty(trim((string)$this->datos_edicion[$id_registro]['vendedor'])) ||
                    !array_key_exists('puntos', $this->datos_edicion[$id_registro])
                ) {
                    session()->flash('error_moda_editar', 'Todos los campos son obligatorios.');
                    return;
                }

                // === Normalización de comas/puntos (idéntica lógica que en save_carga_excel) ===
                $s = (string)$this->datos_edicion[$id_registro]['puntos'];
                $s = trim($s);
                if ($s === '') {
                    session()->flash('error_moda_editar', 'El campo Puntos es obligatorio.');
                    return;
                }

                // quitar espacios internos
                $s = preg_replace('/\s+/', '', $s);

                $hasComma = strpos($s, ',') !== false;
                $hasDot   = strpos($s, '.') !== false;

                if ($hasComma && $hasDot) {
                    $lastComma = strrpos($s, ',');
                    $lastDot   = strrpos($s, '.');
                    if ($lastComma > $lastDot) {
                        // coma como decimal -> quitar puntos (miles) y cambiar coma por punto
                        $s = str_replace('.', '', $s);
                        $s = str_replace(',', '.', $s);
                    } else {
                        // punto como decimal -> quitar comas (miles)
                        $s = str_replace(',', '', $s);
                    }
                } elseif ($hasComma) {
                    // solo comas: decidir si miles o decimal por longitud del último tramo
                    $parts = explode(',', $s);
                    $lastLen = strlen(end($parts));
                    if (count($parts) > 1 && $lastLen === 3) {
                        $s = str_replace(',', '', $s); // miles
                    } else {
                        $s = str_replace(',', '.', $s); // decimal
                    }
                } elseif ($hasDot) {
                    // solo puntos: decidir si miles o decimal
                    $parts = explode('.', $s);
                    $lastLen = strlen(end($parts));
                    if (count($parts) > 1 && $lastLen === 3) {
                        $s = str_replace('.', '', $s); // miles
                    }
                    // si es decimal ya está correcto
                }

                // Validar número (permite negativos y decimales)
                if (!preg_match('/^-?\d+(\.\d+)?$/', $s)) {
                    session()->flash('error_moda_editar', "El valor de Puntos del registro {$id_registro} no es numérico válido.");
                    return;
                }

                // Convertir a float (si quieres limitar decimales, usa round)
                $puntos_normalizados[$id_registro] = (float)$s;
                // $puntos_normalizados[$id_registro] = round((float)$s, 2);
            }

            DB::beginTransaction();

            $registros_actualizados = 0;

            foreach ($this->editando_registros as $id_punto_detalle) {
                if (!isset($this->datos_edicion[$id_punto_detalle])) {
                    continue;
                }

                // Estado anterior del detalle (lock a nivel fila)
                $registro_anterior = DB::table('puntos_detalles')
                    ->where('id_punto_detalle', $id_punto_detalle)
                    ->where('punto_detalle_estado', 1)
                    ->lockForUpdate()
                    ->first();

                if (!$registro_anterior) {
                    continue;
                }

                $datos = $this->datos_edicion[$id_punto_detalle];
                $dni_anterior = trim($registro_anterior->punto_detalle_vendedor);
                $puntos_anteriores = (float) $registro_anterior->punto_detalle_punto_ganado;

                $dni_nuevo = trim((string)$datos['vendedor']);
                $puntos_nuevos = (float) $puntos_normalizados[$id_punto_detalle];

                // Actualizar el detalle
                $actualizado = DB::table('puntos_detalles')
                    ->where('id_punto_detalle', $id_punto_detalle)
                    ->where('punto_detalle_estado', 1)
                    ->update([
                        'punto_detalle_motivo' => $datos['motivo'],
                        'punto_detalle_vendedor' => $dni_nuevo,
                        'punto_detalle_punto_ganado' => $puntos_nuevos,
                        'punto_detalle_fecha_modificacion' => now('America/Lima')->toDateString(),
                        'updated_at' => now('America/Lima'),
                    ]);

                if (!$actualizado) {
                    continue;
                }

                $registros_actualizados++;

                // === AJUSTE DE PUNTAJE EN vendedores_intranet ===
                if ($dni_nuevo === $dni_anterior) {
                    // Mismo vendedor: ajustar diferencia
                    $diferencia = $puntos_nuevos - $puntos_anteriores;
                    if ($diferencia != 0) {
                        $existeVendedor = DB::table('vendedores_intranet')
                            ->where('vendedor_intranet_dni', $dni_anterior)
                            ->where('vendedor_intranet_estado', 1)
                            ->exists();

                        if ($existeVendedor) {
                            if ($diferencia > 0) {
                                DB::table('vendedores_intranet')
                                    ->where('vendedor_intranet_dni', $dni_anterior)
                                    ->where('vendedor_intranet_estado', 1)
                                    ->increment('vendedor_intranet_punto', $diferencia, [
                                        'updated_at' => now('America/Lima')
                                    ]);
                            } else {
                                DB::table('vendedores_intranet')
                                    ->where('vendedor_intranet_dni', $dni_anterior)
                                    ->where('vendedor_intranet_estado', 1)
                                    ->decrement('vendedor_intranet_punto', abs($diferencia), [
                                        'updated_at' => now('America/Lima')
                                    ]);
                            }
                        }
                    }
                } else {
                    // Cambió el DNI:
                    // 1) Restar al vendedor anterior (si existe)
                    $existeVendedorAnterior = DB::table('vendedores_intranet')
                        ->where('vendedor_intranet_dni', $dni_anterior)
                        ->where('vendedor_intranet_estado', 1)
                        ->exists();

                    if ($existeVendedorAnterior && $puntos_anteriores != 0) {
                        DB::table('vendedores_intranet')
                            ->where('vendedor_intranet_dni', $dni_anterior)
                            ->where('vendedor_intranet_estado', 1)
                            ->decrement('vendedor_intranet_punto', abs($puntos_anteriores), [
                                'updated_at' => now('America/Lima')
                            ]);
                    }

                    // 2) Sumar al vendedor nuevo SOLO si existe
                    $existeVendedorNuevo = DB::table('vendedores_intranet')
                        ->where('vendedor_intranet_dni', $dni_nuevo)
                        ->where('vendedor_intranet_estado', 1)
                        ->exists();

                    if ($existeVendedorNuevo && $puntos_nuevos != 0) {
                        DB::table('vendedores_intranet')
                            ->where('vendedor_intranet_dni', $dni_nuevo)
                            ->where('vendedor_intranet_estado', 1)
                            ->increment('vendedor_intranet_punto', $puntos_nuevos, [
                                'updated_at' => now('America/Lima')
                            ]);
                    }
                    // Si el nuevo DNI no existe, no se crea ni se suma (se guarda igual el detalle)
                }
            }

            DB::commit();

            // Limpiar estado de edición
            $this->editando_registros = [];
            $this->datos_edicion      = [];

            // Recargar tabla
            if ($this->id_punto) {
                $this->listar_detalles = DB::table('puntos_detalles')
                    ->where('punto_detalle_estado', 1)
                    ->where('id_punto', $this->id_punto)
                    ->get();
            }

            $this->dispatch('hide_modal_editar_punto');
            session()->flash('success_modal_editar', "Se actualizaron {$registros_actualizados} registro(s) correctamente.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_moda_editar', 'Ocurrió un error al guardar los registros. Por favor, inténtelo nuevamente.');
        }
    }

    public function eliminar_punto_detalle($id_p_de){
        try {
            if (!Gate::allows('eliminar_punto_detalle')) {
                session()->flash('error_moda_editar', 'No tiene permisos para eliminar este detalle.');
                return;
            }

            DB::beginTransaction();

            // Primero obtener los datos del detalle antes de eliminarlo
            $detalle = DB::table('puntos_detalles')
                ->where('id_punto_detalle', $id_p_de)
                ->where('punto_detalle_estado', 1)
                ->first();

            if (!$detalle) {
                session()->flash('error_moda_editar', 'El registro no existe o ya fue eliminado.');
                return;
            }

            // Restar los puntos del vendedor
            $vendedor = DB::table('vendedores_intranet')
                ->where('vendedor_intranet_dni', $detalle->punto_detalle_vendedor)
                ->where('vendedor_intranet_estado', 1)
                ->first();

            if ($vendedor) {
                $nuevos_puntos = $vendedor->vendedor_intranet_punto - $detalle->punto_detalle_punto_ganado;

                // Asegurarse de que no queden puntos negativos
                $nuevos_puntos = max(0, $nuevos_puntos);

                DB::table('vendedores_intranet')
                    ->where('vendedor_intranet_dni', $detalle->punto_detalle_vendedor)
                    ->where('vendedor_intranet_estado', 1)
                    ->update([
                        'vendedor_intranet_punto' => $nuevos_puntos,
                        'updated_at' => now('America/Lima')
                    ]);
            }

            // Ahora marcar el detalle como eliminado
            $actualizar = DB::table('puntos_detalles')
                ->where('id_punto_detalle', $id_p_de)
                ->where('punto_detalle_estado', 1)
                ->update([
                    'punto_detalle_estado' => 0,
                    'updated_at' => now('America/Lima')
                ]);

            if ($actualizar) {
                // Actualizar la lista de detalles para reflejar el cambio
                if ($this->id_punto) {
                    $this->listar_detalles = DB::table('puntos_detalles')
                        ->where('punto_detalle_estado', '=', 1)
                        ->where('id_punto', '=', $this->id_punto)
                        ->get();
                }

                DB::commit();
                session()->flash('success_moda_editar', 'Registro eliminado correctamente y puntos restados del vendedor.');
            } else {
                DB::rollBack();
                session()->flash('error_moda_editar', 'No se pudo eliminar el registro.');
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            $this->setErrorBag($e->validator->errors());
            session()->flash('error_moda_editar', 'Error de validación: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_moda_editar', 'Ocurrió un error al eliminar. Por favor, inténtelo nuevamente.');
        }
    }

    public function btn_punto($id_punto){
        $this->id_punto = base64_decode($id_punto);
    }

    public function delete_punto(){
        try {
            if (!Gate::allows('delete_punto')) {
                session()->flash('error_delete', 'No tiene permisos para eliminar el punto.');
                return;
            }

            $this->validate([
                'id_punto' => 'required|integer',
            ], [
                'id_punto.required' => 'El identificador es obligatorio.',
                'id_punto.integer' => 'El identificador debe ser un número entero.',
            ]);

            DB::beginTransaction();

            // Actualizar el estado del punto principal a 0
            $punto_delete = Punto::find($this->id_punto);
            if (!$punto_delete) {
                DB::rollBack();
                session()->flash('error_delete', 'No se encontró el punto especificado.');
                return;
            }

            $punto_delete->punto_estado = 0;
            if (!$punto_delete->save()) {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo actualizar el estado del punto.');
                return;
            }

            // Obtener todos los registros de puntos_detalles con el mismo id_punto
            $puntos_detalles = DB::table('puntos_detalles')
                ->where('id_punto', $this->id_punto)
                ->where('punto_detalle_estado', '=', 1)
                ->lockForUpdate()
                ->get();

            if ($puntos_detalles->isEmpty()) {
                // Si no hay detalles, solo confirmamos el cambio del punto principal
                DB::commit();
                $this->dispatch('hide_modal_delete_punto');
                session()->flash('success', 'Punto eliminado correctamente.');
                return;
            }

            $registros_procesados = 0;

            // Procesar cada registro de puntos_detalles
            foreach ($puntos_detalles as $detalle) {
                $dni_vendedor = trim($detalle->punto_detalle_vendedor);
                $puntos_ganados = (float) $detalle->punto_detalle_punto_ganado;

                // Verificar si existe el vendedor en vendedores_intranet
                $vendedor = DB::table('vendedores_intranet')
                    ->where('vendedor_intranet_dni', $dni_vendedor)
                    ->where('vendedor_intranet_estado', '=', 1)
                    ->lockForUpdate()
                    ->first();

                if ($vendedor && $puntos_ganados != 0) {
                    // Restar los puntos ganados del vendedor
                    $puntos_vendedor_actual = (float) $vendedor->vendedor_intranet_punto;
                    $nuevos_puntos = $puntos_vendedor_actual - $puntos_ganados;

                    $actualizado = DB::table('vendedores_intranet')
                        ->where('vendedor_intranet_dni', $dni_vendedor)
                        ->where('vendedor_intranet_estado', '=', 1)
                        ->update([
                            'vendedor_intranet_punto' => $nuevos_puntos,
                            'updated_at' => now('America/Lima')
                        ]);

                    if (!$actualizado) {
                        DB::rollBack();
                        session()->flash('error_delete', 'Error al actualizar los puntos del vendedor con DNI: ' . $dni_vendedor);
                        return;
                    }
                }

                // Cambiar el estado del detalle a 0
                $detalle_actualizado = DB::table('puntos_detalles')
                    ->where('id_punto_detalle', $detalle->id_punto_detalle)
                    ->where('punto_detalle_estado', '=', 1)
                    ->update([
                        'punto_detalle_estado' => 0,
                        'updated_at' => now('America/Lima')
                    ]);

                if ($detalle_actualizado) {
                    $registros_procesados++;
                }
            }

            DB::commit();
            $this->dispatch('hide_modal_delete_punto');
            session()->flash('success', "Punto eliminado correctamente. Se procesaron {$registros_procesados} detalle(s) y se actualizaron los puntos de los vendedores correspondientes.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al eliminar el registro. Por favor, inténtelo nuevamente.');
        }
    }

}
