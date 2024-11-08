<?php

namespace App\Livewire\Registroflete;

use App\Models\Transportista;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\Ubigeo;
use App\Models\TipoVehiculo;
use App\Models\Logs;
use App\Models\Tarifario;
use App\Models\RegistrarHistorialUpdate;
use App\Models\TipoServicio;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\Distrito;
use App\Models\Medida;

class Tarifarios extends Component
{
    use WithPagination, WithoutUrlPagination;
    public $tarifarios;
    public $departamento;
    public $provincia;
    public $distrito;
    public $medida;
    public $search_tarifario;
    public $pagination_tarifario = 10;
    public $id_tarifario = "";
    public $id_transportistas = "";
    public $id_tipo_vehiculo = "";
    public $id_tipo_servicio = "";
    public $id_ubigeo_salida = "";

    public $id_departamento = "";
    public $id_provincia = "";
    public $id_distrito = "";
    public $departamentos = [];
    public $provincias = [];
    public $distritos = [];


    public $id_ubigeo_llegada = "";
    public $id_medida = "";
    public $tarifa_cap_min = "";
    public $tarifa_cap_max = "";
    public $tarifa_monto = "";
    public $tarifa_estado = "";
    public $id_users = "";
    public $listar_servicios = array();
    public $messageDeleteTarifario = "";
    public $historial_registros = [];
    public $detalles = [];
    private $logs;
    private $ubigeo;
    private $tipovehiculo;
    private $tarifario;
    private $registrar_historial;
    public function __construct(){
        $this->logs = new Logs();
        $this->ubigeo = new Ubigeo();
        $this->tipovehiculo = new TipoVehiculo();
        $this->tarifario = new Tarifario();
        $this->departamento = new Departamento();
        $this->provincia = new Provincia();
        $this->distrito = new Distrito();
        $this->medida = new Medida();
        $this->registrar_historial = new RegistrarHistorialUpdate();
    }
    public function mount($id) {
        $this->id_transportistas = $id;
        $this->listarServiciosSelect();
    }
    #[On('refresh_select_servicios')]
    public function listarServiciosSelect(){
        $this->listar_servicios = TipoServicio::where('tipo_servicio_estado', 1)->get();
    }

    public function render()
    {
        $tarifario = $this->tarifario->listar_tarifarios($this->id_transportistas,$this->search_tarifario,$this->pagination_tarifario);
        $listar_ubigeos = $this->ubigeo->listar_ubigeos();
        $listar_tipovehiculo = $this->tipovehiculo->listar_tipo_vehiculo();
        $listar_departamento = $this->departamento->lista_departamento();
//        $resultados = DB::connection('sqlsrv_external')
//            ->table('NUM_DOCCOMPRAS')
//            ->select('*')
//            ->get();
        return view('livewire.registroflete.tarifarios', compact('listar_ubigeos', 'tarifario', 'listar_tipovehiculo', 'listar_departamento'));
    }

    public function limpiar_nombre_convenio(){
        $this->dispatch('limpiar_nombre_convenio');
    }

    public function listar_provincias(){
        $valor = $this->id_departamento;
        if ($valor){
            $this->provincias=DB::table('provincias')->where('id_departamento', '=', $valor)->get();
        }
    }

    public function listar_distritos(){
        $valor = $this->id_provincia;
        if ($valor){
            $this->distritos=DB::table('distritos')->where('id_provincia', '=', $valor)->get();
        }
    }

    public function clear_form_tarifario(){
        $this->id_tarifario = "";
        $this->id_tipo_servicio = "";
        $this->id_tipo_vehiculo = "";
        $this->id_ubigeo_salida = "";
        $this->id_departamento = "";
        $this->id_provincia = "";
        $this->id_distrito = "";
        $this->id_medida = 23;
        $this->tarifa_cap_min = "";
        $this->tarifa_cap_max = "";
        $this->tarifa_monto = "";
        $this->tarifa_estado = "";
        $this->provincias = [];
        $this->distritos = [];
        $this->dispatch('select_ubigeo_salida',['text' => null]);
        $this->dispatch('select_ubigeo_llegada',['text' => null]);

        $ubigeoLima = DB::table('ubigeos')
            ->where('ubigeo_departamento', 'LIMA')
            ->where('ubigeo_provincia', 'LIMA')
            ->where('ubigeo_distrito', 'LIMA')
            ->first();
        if ($ubigeoLima) {
            $this->id_ubigeo_salida = $ubigeoLima->id_ubigeo;
        }
    }

    public function edit_data($id){
        $tarifario_Edit = Tarifario::find(base64_decode($id));
        if ($tarifario_Edit){
            $this->id_transportistas = $tarifario_Edit->id_transportistas;
            $this->id_tipo_servicio = $tarifario_Edit->id_tipo_servicio;
            $this->id_tipo_vehiculo = $tarifario_Edit->id_tipo_vehiculo;
            $this->id_ubigeo_salida = $tarifario_Edit->id_ubigeo_salida;
            $this->id_departamento = $tarifario_Edit->id_departamento;
            $this->id_provincia = $tarifario_Edit->id_provincia;
            $this->id_distrito = $tarifario_Edit->id_distrito;
            $this->id_ubigeo_llegada = $tarifario_Edit->id_ubigeo_llegada;
            $this->id_medida = $tarifario_Edit->id_medida;
            $this->tarifa_cap_min = $tarifario_Edit->tarifa_cap_min;
            $this->tarifa_cap_max = $tarifario_Edit->tarifa_cap_max;
            $this->tarifa_monto = $tarifario_Edit->tarifa_monto;
            $this->id_tarifario = $tarifario_Edit->id_tarifario;

            $this->provincias = DB::table('provincias')->where('id_departamento', $this->id_departamento)->get();
            $this->distritos = DB::table('distritos')->where('id_provincia', $this->id_provincia)->get();
        }
    }

    public function ver_registro($id){
        $tarifario_id = base64_decode($id);
        // los where deben ir siempre al último. -
        $this->historial_registros = DB::table('registrar_historial_updates as r')
            ->join('users as u', 'r.id_users', '=', 'u.id_users')
            ->where('id_tarifario', $tarifario_id)
            ->orderBy('r.created_at', 'desc')
            ->get();
    }

    public function ver_detalles($id){
        $id_tarifario = base64_decode($id);
        $this->detalles = Tarifario::where('id_tarifario', $id_tarifario)->first();
    }

    public function saveTarifario() {
        try {
            $this->validate([
                'id_transportistas' => 'required|integer',
                'id_tipo_servicio' => 'required|integer',
                'id_tipo_vehiculo' => 'required_if:id_tipo_servicio,1|nullable|integer',
                'id_ubigeo_salida' => 'required_if:id_tipo_servicio,2|nullable|integer',
                'id_departamento' => 'required_if:id_tipo_servicio,2|nullable|integer',
                'id_provincia' => 'required_if:id_tipo_servicio,2|nullable|integer',
                'id_distrito' => 'nullable|integer',
                'id_medida' => 'required_if:id_tipo_servicio,2|nullable|integer|in:23,9',
                'tarifa_cap_min' => 'required|numeric',
                'tarifa_cap_max' => 'required|numeric',
                'tarifa_monto' => 'required|numeric',
                'tarifa_estado' => 'nullable|integer',
                'id_tarifario' => 'nullable|integer',
            ], [
                'id_transportistas.required' => 'Debes seleccionar un transportista.',
                'id_transportistas.integer' => 'El transportista debe ser un número entero.',

                'id_tipo_servicio.required' => 'Debes seleccionar un servicio.',
                'id_tipo_servicio.integer' => 'El servicio debe ser un número entero.',

                'id_tipo_vehiculo.required_if' => 'Debes seleccionar un tipo de vehículo.',
                'id_tipo_vehiculo.integer' => 'El tipo de vehículo debe ser un número entero.',

                'id_ubigeo_salida.required_if' => 'La salida es obligatoria para servicios provinciales.',
                'id_ubigeo_salida.integer' => 'La salida debe ser un número entero.',

                'id_departamento.required_if' => 'El departamento es obligatoria para servicios provinciales.',
                'id_departamento.integer' => 'El departamento debe ser un número entero.',

                'id_provincia.required_if' => 'La provincia es obligatoria para servicios provinciales.',
                'id_provincia.integer' => 'La provincia debe ser un número entero.',

                'id_distrito.integer' => 'El distrito debe ser un número entero.',

                'id_medida.required_if' => 'Debes seleccionar un tipo de unidad.',
                'id_medida.integer' => 'El tipo de unidad debe ser un número entero.',

                'tarifa_cap_min.required' => 'La capacidad minima es obligatorio.',
                'tarifa_cap_min.numeric' => 'La capacidad minima debe ser un valor numérico.',

                'tarifa_cap_max.required' => 'La capacidad maxima es obligatorio.',
                'tarifa_cap_max.numeric' => 'La capacidad maxima debe ser un valor numérico.',

                'tarifa_monto.required' => 'El monto de la tarifa es obligatorio.',
                'tarifa_monto.numeric' => 'El monto de la tarifa debe ser un valor numérico.',

                'tarifa_estado.integer' => 'El estado debe ser un número entero.',

                'id_tarifario.integer' => 'El identificador debe ser un número entero.',
            ]);

            if (!$this->id_tarifario) { // INSERTAR
                if (!Gate::allows('create_tarifario')) {
                    session()->flash('error', 'No tiene permisos para crear.');
                    return;
                }

                $validar = DB::table('tarifarios')
                    ->where('id_transportistas', '=', $this->id_transportistas)
                    ->where('id_tipo_servicio', '=', $this->id_tipo_servicio)
                    ->where('tarifa_cap_max', '<=', $this->tarifa_cap_max)
                    ->where('tarifa_cap_min', '>=', $this->tarifa_cap_min)
                    ->first();

                if (!$validar) {
                    $microtime = microtime(true);
                    DB::beginTransaction();

                    $tarifario_save = new Tarifario();
                    $tarifario_save->id_users = Auth::id();
                    $tarifario_save->id_transportistas = $this->id_transportistas;
                    $tarifario_save->id_tipo_servicio = $this->id_tipo_servicio;
                    $tarifario_save->id_tipo_vehiculo = $this->id_tipo_servicio == 1 ? $this->id_tipo_vehiculo : null;
                    $tarifario_save->id_ubigeo_salida = $this->id_tipo_servicio == 2 ? $this->id_ubigeo_salida : null;
                    $tarifario_save->id_departamento = $this->id_tipo_servicio == 2 ? $this->id_departamento : null;
                    $tarifario_save->id_provincia = $this->id_tipo_servicio == 2 ? $this->id_provincia : null;
                    $tarifario_save->id_distrito = $this->id_tipo_servicio == 2 ? (!empty($this->id_distrito) ? $this->id_distrito : null) : null;
                    $tarifario_save->id_medida = $this->id_tipo_servicio == 2 ? $this->id_medida : null;
                    $tarifario_save->tarifa_cap_min = $this->tarifa_cap_min;
                    $tarifario_save->tarifa_cap_max = $this->tarifa_cap_max;
                    $tarifario_save->tarifa_monto = $this->tarifa_monto;
                    $tarifario_save->tarifa_estado = 1;
                    $tarifario_save->tarifa_microtime = $microtime;
                    $tarifario_save->tarifa_estado_aprobacion = 0;

                    if ($tarifario_save->save()) {
                        DB::commit();
                        $this->dispatch('hideModal');
                        session()->flash('success', 'Registro guardado correctamente.');
                    } else {
                        DB::rollBack();
                        session()->flash('error', 'Ocurrió un error al guardar el registro.');
                    }
                } else{
                    session()->flash('error', 'El rango de capacidad se cruza con un registro existente.');
                    return;
                }
            } else {
                if (!Gate::allows('update_tarifario')) {
                    session()->flash('error', 'No tiene permisos para actualizar este registro.');
                    return;
                }

                $this->validate([
                    'id_transportistas' => 'required|integer',
                    'id_tipo_servicio' => 'required|integer',
                    'id_tipo_vehiculo' => 'required_if:id_tipo_servicio,1|nullable|integer',
                    'id_ubigeo_salida' => 'required_if:id_tipo_servicio,2|nullable|integer',
                    'id_departamento' => 'required_if:id_tipo_servicio,2|nullable|integer',
                    'id_provincia' => 'required_if:id_tipo_servicio,2|nullable|integer',
                    'id_distrito' => 'nullable|integer',
                    'id_medida' => 'required|integer',
                    'tarifa_cap_min' => 'required|numeric',
                    'tarifa_cap_max' => 'required|numeric',
                    'tarifa_monto' => 'required|numeric',
                    'tarifa_estado' => 'nullable|integer',
                    'id_tarifario' => 'nullable|integer',
                ], [
                    'id_transportistas.required' => 'Debes seleccionar un transportista.',
                    'id_transportistas.integer' => 'El transportista debe ser un número entero.',

                    'id_tipo_servicio.required' => 'Debes seleccionar un servicio.',
                    'id_tipo_servicio.integer' => 'El servicio debe ser un número entero.',

                    'id_tipo_vehiculo.required_if' => 'Debes seleccionar un tipo de vehículo.',
                    'id_tipo_vehiculo.integer' => 'El tipo de vehículo debe ser un número entero.',

                    'id_ubigeo_salida.required_if' => 'La salida es obligatoria para servicios provinciales.',
                    'id_ubigeo_salida.integer' => 'La salida debe ser un número entero.',

                    'id_departamento.required_if' => 'El departamento es obligatoria para servicios provinciales.',
                    'id_departamento.integer' => 'El departamento debe ser un número entero.',

                    'id_provincia.required_if' => 'La provincia es obligatoria para servicios provinciales.',
                    'id_provincia.integer' => 'La provincia debe ser un número entero.',

                    'id_distrito.integer' => 'El distrito debe ser un número entero.',

                    'id_medida.required' => 'Debes seleccionar un tipo de unidad.',
                    'id_medida.integer' => 'El tipo de unidad debe ser un número entero.',

                    'tarifa_cap_min.required' => 'La capacidad mínima es obligatoria.',
                    'tarifa_cap_min.numeric' => 'La capacidad mínima debe ser un valor numérico.',

                    'tarifa_cap_max.required' => 'La capacidad máxima es obligatoria.',
                    'tarifa_cap_max.numeric' => 'La capacidad máxima debe ser un valor numérico.',

                    'tarifa_monto.required' => 'El monto de la tarifa es obligatorio.',
                    'tarifa_monto.numeric' => 'El monto de la tarifa debe ser un valor numérico.',

                    'tarifa_estado.integer' => 'El estado debe ser un número entero.',

                    'id_tarifario.integer' => 'El identificador debe ser un número entero.',
                ]);

                $validar = DB::table('tarifarios')
                    ->where('id_tarifario', '<>', $this->id_tarifario)
                    ->where('id_transportistas', '=', $this->id_transportistas)
                    ->where('id_tipo_servicio', '=', $this->id_tipo_servicio)
                    ->where('tarifa_cap_max', '<=', $this->tarifa_cap_max)
                    ->where('tarifa_cap_min', '>=', $this->tarifa_cap_min)
                    ->first();

                if (!$validar) {
                    // Obtener el registro actual antes de realizar cambios
                    $tarifario_update = Tarifario::findOrFail($this->id_tarifario);
                    // Guardar los valores originales para verificar cambios
                    $originalValues = $tarifario_update->getOriginal();
                    // Actualizar los campos del registro
                    $tarifario_update->id_tipo_servicio = $this->id_tipo_servicio;
                    $tarifario_update->id_tipo_vehiculo = $this->id_tipo_servicio == 1 ? $this->id_tipo_vehiculo : null;
                    $tarifario_update->id_ubigeo_salida = $this->id_tipo_servicio == 2 ? $this->id_ubigeo_salida : null;
                    $tarifario_update->id_departamento = $this->id_tipo_servicio == 2 ? $this->id_departamento : null;
                    $tarifario_update->id_provincia = $this->id_tipo_servicio == 2 ? $this->id_provincia : null;
                    $tarifario_update->id_distrito = $this->id_tipo_servicio == 2 ? (!empty($this->id_distrito) ? $this->id_distrito : null) : null;
                    $tarifario_update->id_medida = $this->id_medida;
                    $tarifario_update->tarifa_cap_min = $this->tarifa_cap_min;
                    $tarifario_update->tarifa_cap_max = $this->tarifa_cap_max;
                    $tarifario_update->tarifa_monto = $this->tarifa_monto;

                    // Inicializar el mensaje de registro
                    $registro_concepto = [];
                    $usuario_actual = auth()->user()->name;

                    // Verificar si hubo algún cambio en los campos
                    // Suponiendo que ya tienes acceso a los modelos correspondientes
                    if (
                        $originalValues['id_tipo_servicio'] !== $this->id_tipo_servicio ||
                        $originalValues['id_tipo_vehiculo'] !== ($this->id_tipo_servicio == 1 ? $this->id_tipo_vehiculo : null) ||
                        $originalValues['id_ubigeo_salida'] !== ($this->id_tipo_servicio == 2 ? $this->id_ubigeo_salida : null) ||
                        $originalValues['id_departamento'] !== ($this->id_tipo_servicio == 2 ? $this->id_departamento : null) ||
                        $originalValues['id_provincia'] !== ($this->id_tipo_servicio == 2 ? $this->id_provincia : null) ||
                        $originalValues['id_distrito'] !== ($this->id_tipo_servicio == 2 ? (!empty($this->id_distrito) ? $this->id_distrito : null) : null) ||
                        $originalValues['id_medida'] !== $this->id_medida ||
                        $originalValues['tarifa_cap_min'] !== $this->tarifa_cap_min ||
                        $originalValues['tarifa_cap_max'] !== $this->tarifa_cap_max ||
                        $originalValues['tarifa_monto'] !== $this->tarifa_monto
                    ) {
                        // Cambiar tarifa_estado_aprobacion a 0 solo si hubo cambios en los campos
                        $tarifario_update->tarifa_estado_aprobacion = 0;

                        // Agregar los cambios al mensaje
                        $registro_concepto = [];

                        if ($originalValues['id_tipo_servicio'] !== $this->id_tipo_servicio) {
                            $tipoServicioNombreAnterior = DB::table('tipo_servicios')->where('id_tipo_servicios', $originalValues['id_tipo_servicio'])->value('tipo_servicio_concepto') ?? 'Desconocido';
                            $tipoServicioNombreNuevo = DB::table('tipo_servicios')->where('id_tipo_servicios', $this->id_tipo_servicio)->value('tipo_servicio_concepto') ?? 'Desconocido';
                            $registro_concepto[] = "Tipo de servicio de '{$tipoServicioNombreAnterior}' hasta '{$tipoServicioNombreNuevo}'";
                        }

                        if ($originalValues['id_tipo_vehiculo'] !== ($this->id_tipo_servicio == 1 ? $this->id_tipo_vehiculo : null)) {
                            $tipoVehiculoNombreAnterior = DB::table('tipo_vehiculos')->where('id_tipo_vehiculo', $originalValues['id_tipo_vehiculo'])->value('tipo_vehiculo_concepto') ?? 'Desconocido';
                            $tipoVehiculoNombreNuevo = DB::table('tipo_vehiculos')->where('id_tipo_vehiculo', $this->id_tipo_vehiculo)->value('tipo_vehiculo_concepto') ?? 'Desconocido';
                            $registro_concepto[] = "Tipo de vehículo de '{$tipoVehiculoNombreAnterior}' hasta '{$tipoVehiculoNombreNuevo}'";
                        }

                        if ($originalValues['id_ubigeo_salida'] !== ($this->id_tipo_servicio == 2 ? $this->id_ubigeo_salida : null)) {
                            $ubigeoSalidaAnterior = DB::table('ubigeos')->where('id_ubigeo', $originalValues['id_ubigeo_salida'])->first();
                            $ubigeoSalidaNombreAnterior = $ubigeoSalidaAnterior ? "{$ubigeoSalidaAnterior->ubigeo_departamento}, {$ubigeoSalidaAnterior->ubigeo_provincia}, {$ubigeoSalidaAnterior->ubigeo_distrito}" : 'Desconocido';

                            $ubigeoSalidaNuevo = DB::table('ubigeos')->where('id_ubigeo', $this->id_ubigeo_salida)->first();
                            $ubigeoSalidaNombreNuevo = $ubigeoSalidaNuevo ? "{$ubigeoSalidaNuevo->ubigeo_departamento}, {$ubigeoSalidaNuevo->ubigeo_provincia}, {$ubigeoSalidaNuevo->ubigeo_distrito}" : 'Desconocido';

                            $registro_concepto[] = "Ubigeo de salida de '{$ubigeoSalidaNombreAnterior}' hasta '{$ubigeoSalidaNombreNuevo}'";
                        }

                        if ($originalValues['id_departamento'] !== ($this->id_tipo_servicio == 2 ? $this->id_departamento : null)) {
                            $departamentoNombreAnterior = DB::table('departamentos')->where('id_departamento', $originalValues['id_departamento'])->value('departamento_nombre') ?? 'Desconocido';
                            $departamentoNombreNuevo = DB::table('departamentos')->where('id_departamento', $this->id_departamento)->value('departamento_nombre') ?? 'Desconocido';
                            $registro_concepto[] = "Departamento de '{$departamentoNombreAnterior}' por '{$departamentoNombreNuevo}'";
                        }

                        if ($originalValues['id_provincia'] !== ($this->id_tipo_servicio == 2 ? $this->id_provincia : null)) {
                            $provinciaNombreAnterior = DB::table('provincias')->where('id_provincia', $originalValues['id_provincia'])->value('provincia_nombre') ?? 'Desconocido';
                            $provinciaNombreNuevo = DB::table('provincias')->where('id_provincia', $this->id_provincia)->value('provincia_nombre') ?? 'Desconocido';
                            $registro_concepto[] = "Provincia de '{$provinciaNombreAnterior}' por '{$provinciaNombreNuevo}'";
                        }

                        if ($originalValues['id_distrito'] !== ($this->id_tipo_servicio == 2 ? (!empty($this->id_distrito) ? $this->id_distrito : null) : null)) {
                            $distritoNombreAnterior = DB::table('distritos')->where('id_distrito', $originalValues['id_distrito'])->value('distrito_nombre') ?? 'Desconocido';
                            $distritoNombreNuevo = DB::table('distritos')->where('id_distrito', $this->id_distrito)->value('distrito_nombre') ?? 'Desconocido';
                            $registro_concepto[] = "Distrito de '{$distritoNombreAnterior}' por '{$distritoNombreNuevo}'";
                        }

                        if ($originalValues['id_medida'] !== $this->id_medida) {
                            $medidaNombreAnterior = DB::table('medida')->where('id_medida', $originalValues['id_medida'])->value('medida_nombre') ?? 'Desconocido';
                            $medidaNombreNuevo = DB::table('medida')->where('id_medida', $this->id_medida)->value('medida_nombre') ?? 'Desconocido';
                            $registro_concepto[] = "Tipo de unidad de '{$medidaNombreAnterior}' por '{$medidaNombreNuevo}'";
                        }

                        if ($originalValues['tarifa_cap_min'] !== $this->tarifa_cap_min) {
                            $registro_concepto[] = "Capacidad mínima de '{$originalValues['tarifa_cap_min']}' hasta '{$this->tarifa_cap_min}'";
                        }

                        if ($originalValues['tarifa_cap_max'] !== $this->tarifa_cap_max) {
                            $registro_concepto[] = "Capacidad máxima de '{$originalValues['tarifa_cap_max']}' hasta '{$this->tarifa_cap_max}'";
                        }

                        if ($originalValues['tarifa_monto'] !== $this->tarifa_monto) {
                            $registro_concepto[] = "Monto de '{$originalValues['tarifa_monto']}' hasta '{$this->tarifa_monto}'";
                        }
                    }

                    // Guardar los cambios solo si se realizaron cambios
                    if (!empty($registro_concepto)) {
                        // Guardar el tarifario actualizado
                        if (!$tarifario_update->save()) {
                            session()->flash('error', 'No se pudo actualizar el registro.');
                            return;
                        }

                        // Crear el registro de historial
                        $this->registrar_historial = new RegistrarHistorialUpdate();
                        $this->registrar_historial->id_tarifario = $this->id_tarifario;
                        $this->registrar_historial->id_users = auth()->id();
                        $this->registrar_historial->registro_concepto = "Se realizaron cambios de: " . implode(", ", $registro_concepto); // Convertir el array en texto
                        $this->registrar_historial->registro_hora_fecha = now();
                        $this->registrar_historial->registro_estado = 1;
                        $this->registrar_historial->registro_microtime = microtime(true);
                        $this->registrar_historial->save();

                        DB::commit();
                        $this->dispatch('hideModal');
                        session()->flash('success', 'Registro actualizado correctamente.');
                    } else {
                        // Si no se realizaron cambios, también cerrar el modal
                        $this->dispatch('hideModal');
                        session()->flash('success', 'No se realizaron cambios en los registros.');
                    }
                } else {
                    session()->flash('error', 'El rango de capacidad se cruza con un registro existente.');
                    return;
                }
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }


    public function btn_disable($id_tarifa,$esta){
        $id = base64_decode($id_tarifa);
        $status = $esta;
        if ($id){
            $this->id_tarifario = $id;
            $this->tarifa_estado = $status;
            if ($status == 0){
                $this->messageDeleteTarifario = "¿Está seguro que desea eliminar este registro?";
            }else{
                $this->messageDeleteTarifario = "¿Está seguro que desea eliminar este registro?";
            }
        }
    }

    public function disable_tarifario(){
        try {

            if (!Gate::allows('disable_tarifario')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }


            $this->validate([
                'id_tarifario' => 'required|integer',
                'tarifa_estado' => 'required|integer',
            ], [
                'id_tarifario.required' => 'El identificador es obligatorio.',
                'id_tarifario.integer' => 'El identificador debe ser un número entero.',

                'tarifa_estado.required' => 'El estado es obligatorio.',
                'tarifa_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $tarifarioDelete = Tarifario::find($this->id_tarifario);
            $tarifarioDelete->tarifa_estado = $this->tarifa_estado;
            if ($tarifarioDelete->save()) {
                DB::commit();
                $this->dispatch('hideModalDelete');
                if ($this->tarifa_estado == 0){
                    session()->flash('success', 'Registro eliminado correctamente.');
                }else{
                    session()->flash('success', 'Registro eliminado correctamente.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo cambiar el estado del registro.');
                return;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }
}
