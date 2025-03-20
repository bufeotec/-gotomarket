<?php

namespace App\Livewire\Gestiontransporte;

use App\Models\TipoServicio;
use App\Models\Transportista;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Vehiculo;
use App\Models\Logs;
use App\Models\TarifaMovil;
use App\Models\TipoVehiculo;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class Vehiculos extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $tipovehiculo;
    private $transportistas;
    private $vehiculo;
    private $tarifamovil;
    public function __construct(){
        $this->logs = new Logs();
        $this->tipovehiculo = new Tipovehiculo();
        $this->transportistas = new Transportista();
        $this->vehiculo = new Vehiculo();
        $this->tarifamovil = new TarifaMovil;

    }

//    ATRIBUTOS PARA GUARDAR
    public $id_transportistas = "";
    public $search_vehiculos;
    public $pagination_vehiculos = 10;
    public $urlActual;
    public $id_vehiculo = "";
    public $id_tipo_vehiculo = "";
    public $vehiculo_placa = "";
    public $vehiculo_capacidad_peso = "";
    public $vehiculo_ancho = "";
    public $vehiculo_largo = "";
    public $vehiculo_alto = "";
    public $vehiculo_capacidad_volumen = "";
    public $vehiculo_estado = "";
    public $listar_tipo_vehiculo = array();
    public $messageDeleteVehiculo = "";
    public $id_tarifario = "";
    public $observaciones = "";
    public $isEditing = false;
    public $listar_tarifario = [];

//
    public function mount(){
        $this->listarTipoVehiculoSelect();

    }

    #[On('refresh_select_tipo_vehiculo')]
    public function listarTipoVehiculoSelect(){
        $this->listar_tipo_vehiculo = TipoVehiculo::where('tipo_vehiculo_estado', 1)->get();
    }

    public function calcularVolumen(){
        $ancho = floatval($this->vehiculo_ancho);
        $largo = floatval($this->vehiculo_largo);
        $alto = floatval($this->vehiculo_alto);
        $this->vehiculo_capacidad_volumen = $ancho * $largo * $alto;
    }
    public function render(){
        $listar_transportistas = $this->transportistas->listar_transportista_sin_id();
        $listar_vehiculos = $this->vehiculo->listar_vehiculos_por_transportistas($this->search_vehiculos, $this->pagination_vehiculos);
        return view('livewire.gestiontransporte.vehiculos', compact('listar_vehiculos', 'listar_transportistas'));
    }
    public function listar_tarifarios()
    {
        $valor = $this->id_tipo_vehiculo;
        if ($valor) {
            $this->listar_tarifario = DB::table('tarifarios')
                ->where('id_tipo_vehiculo', '=', $valor)
                ->get();
        } else {
            $this->listar_tarifario = [];
            $this->id_tarifario = "";
        }
    }

    public function tipoVehiculoChange()
    {
        $this->id_tarifario = ""; // Limpiar la selección de tarifa
//        $this->listar_tarifario = []; // Limpiar la lista de tarifas
        $this->listar_tarifarios(); // Cargar tarifas según el tipo de vehículo seleccionado
    }

    public function limpiar_campo_tipo_vehiculo(){
        $this->dispatch('limpiar_campo_tipo_vehiculo');
    }

    public function clear_form_vehiculos(){
        $this->id_vehiculo = "";
        $this->id_transportistas = "";
        $this->id_tipo_vehiculo = "";
        $this->vehiculo_placa = "";
        $this->vehiculo_capacidad_peso = "";
        $this->vehiculo_ancho = "";
        $this->id_tarifario = "";
        $this->vehiculo_largo = "";
        $this->vehiculo_alto = "";
        $this->vehiculo_capacidad_volumen = "";
        $this->observaciones = "";
        $this->isEditing = false;
    }
    public function edit_data($id) {
        $vehiculoEdit = Vehiculo::with('tarifasMovil')->find(base64_decode($id));

        if ($vehiculoEdit) {
            $this->id_transportistas = $vehiculoEdit->id_transportistas;
            $this->id_tipo_vehiculo = $vehiculoEdit->id_tipo_vehiculo;
            $this->vehiculo_placa = $vehiculoEdit->vehiculo_placa;
            $this->vehiculo_capacidad_peso = $vehiculoEdit->vehiculo_capacidad_peso;
            $this->vehiculo_ancho = $vehiculoEdit->vehiculo_ancho;
            $this->vehiculo_largo = $vehiculoEdit->vehiculo_largo;
            $this->vehiculo_alto = $vehiculoEdit->vehiculo_alto;
            $this->vehiculo_capacidad_volumen = $vehiculoEdit->vehiculo_capacidad_volumen;
            $this->id_vehiculo = $vehiculoEdit->id_vehiculo;

            $tarifa = $vehiculoEdit->tarifasMovil->first();
            $this->id_tarifario = $tarifa ? $tarifa->id_tarifario : null;
            $this->observaciones = $tarifa ? $tarifa->observaciones : '';
            $this->isEditing = true;
            $this->listar_tarifarios();

        }
    }

    public function saveVehiculo() {
        try {
            $this->validate([
                'id_transportistas' => 'required|integer',
                'id_tipo_vehiculo' => 'required|integer',
                'vehiculo_placa' => 'required|string',
                'vehiculo_capacidad_peso' => 'required|numeric',
                'vehiculo_ancho' => 'required|numeric',
                'vehiculo_largo' => 'required|numeric',
                'vehiculo_alto' => 'required|numeric',
                'vehiculo_capacidad_volumen' => 'required|numeric',
                'vehiculo_estado' => 'nullable|integer',
                'id_vehiculo' => 'nullable|integer',
                'id_tarifario' => 'required|integer',
            ], [
                'id_transportistas.required' => 'Debes seleccionar un transportista.',
                'id_transportistas.integer' => 'El transportista debe ser un número entero.',
                'id_tipo_vehiculo.required' => 'Debes seleccionar un tipo de vehículo.',
                'id_tipo_vehiculo.integer' => 'El tipo de vehículo debe ser un número entero.',
                'vehiculo_placa.required' => 'La placa es obligatoria.',
                'vehiculo_placa.string' => 'La placa debe ser una cadena de texto.',
                'vehiculo_capacidad_peso.required' => 'La capacidad de peso del vehículo es obligatoria.',
                'vehiculo_capacidad_peso.numeric' => 'La capacidad de peso del vehículo debe ser un valor numérico.',
                'vehiculo_ancho.required' => 'El ancho del vehículo es obligatorio.',
                'vehiculo_ancho.numeric' => 'El ancho del vehículo debe ser un valor numérico.',
                'vehiculo_largo.required' => 'El largo del vehículo es obligatorio.',
                'vehiculo_largo.numeric' => 'El largo del vehículo debe ser un valor numérico.',
                'vehiculo_alto.required' => 'La altura del vehículo es obligatoria.',
                'vehiculo_alto.numeric' => 'La altura del vehículo debe ser un valor numérico.',
                'vehiculo_capacidad_volumen.required' => 'La capacidad de volumen del vehículo es obligatoria.',
                'vehiculo_capacidad_volumen.numeric' => 'La capacidad de volumen del vehículo debe ser un valor numérico.',
                'vehiculo_estado.integer' => 'El estado debe ser un número entero.',
                'id_vehiculo.integer' => 'El identificador debe ser un número entero.',
                'id_tarifario.required' => 'Debes seleccionar una tarifa.',
                'id_tarifario.integer' => 'La tarifa debe ser un número entero.',
            ]);

            if (!$this->id_vehiculo) { // INSERT
                if (!Gate::allows('create_vehiculo')) {
                    session()->flash('error', 'No tiene permisos para crear.');
                    return;
                }

                $validar = DB::table('vehiculos')->where('vehiculo_placa', '=', $this->vehiculo_placa)->exists();
                if (!$validar) {
                    $microtime = microtime(true);
                    DB::beginTransaction();
                    $vehiculo_save = new Vehiculo();
                    $vehiculo_save->id_users = Auth::id();
                    $vehiculo_save->id_transportistas = $this->id_transportistas;
                    $vehiculo_save->id_tipo_vehiculo = $this->id_tipo_vehiculo;
                    $vehiculo_save->vehiculo_placa = $this->vehiculo_placa;
                    $vehiculo_save->vehiculo_capacidad_peso = $this->vehiculo_capacidad_peso;
                    $vehiculo_save->vehiculo_ancho = $this->vehiculo_ancho;
                    $vehiculo_save->vehiculo_largo = $this->vehiculo_largo;
                    $vehiculo_save->vehiculo_alto = $this->vehiculo_alto;
                    $vehiculo_save->vehiculo_capacidad_volumen = $this->vehiculo_capacidad_volumen;
                    $vehiculo_save->vehiculo_estado = 0;
                    $vehiculo_save->vehiculo_microtime = $microtime;

                    if ($vehiculo_save->save()) {
                        // Guardar en la tabla tarifas_movil
                        DB::table('tarifas_movil')->insert([
                            'id_vehiculo' => $vehiculo_save->id_vehiculo,
                            'id_tarifario' => $this->id_tarifario,
//                            'observaciones' => $this->observaciones,
                            'created_at' => now(),
                            'id_users' => Auth::id(),
                        ]);

                        DB::commit(); // Confirmar transacción
                        $this->dispatch('hideModal'); // Cerrar modal
                        session()->flash('success', 'Registro guardado correctamente.');
                    } else {
                        DB::rollBack();
                        session()->flash('error', 'Ocurrió un error al guardar el vehículo.');
                        return;
                    }
                } else {
                    session()->flash('error', 'La placa ingresada para el vehículo ya está registrada.');
                    return;
                }
            } else { // UPDATE
                if (!Gate::allows('update_vehiculos')) {
                    session()->flash('error', 'No tiene permisos para actualizar este registro.');
                    return;
                }

                // Validar si la placa ya está registrada en otro vehículo
                $validar_update = DB::table('vehiculos')
                    ->where('id_vehiculo', '<>', $this->id_vehiculo)
                    ->where('vehiculo_placa', '=', $this->vehiculo_placa)
                    ->exists();

                if (!$validar_update) {
                    DB::beginTransaction();
                    // Actualizar los datos del vehículo
                    $vehiculo_update = Vehiculo::findOrFail($this->id_vehiculo);
                    $vehiculo_update->id_transportistas = $this->id_transportistas;
                    $vehiculo_update->id_tipo_vehiculo = $this->id_tipo_vehiculo;
                    $vehiculo_update->vehiculo_placa = $this->vehiculo_placa;
                    $vehiculo_update->vehiculo_capacidad_peso = $this->vehiculo_capacidad_peso;
                    $vehiculo_update->vehiculo_ancho = $this->vehiculo_ancho;
                    $vehiculo_update->vehiculo_largo = $this->vehiculo_largo;
                    $vehiculo_update->vehiculo_alto = $this->vehiculo_alto;
                    $vehiculo_update->vehiculo_capacidad_volumen = $this->vehiculo_capacidad_volumen;
                    $vehiculo_update->vehiculo_estado = 0;

                    // Guardar cambios en el vehículo
                    if (!$vehiculo_update->save()) {
                        DB::rollBack();
                        session()->flash('error', 'No se pudo actualizar el registro del vehículo.');
                        return;
                    }

                    // Actualizar la tabla tarifas_movil
//                    DB::table('tarifas_movil')
//                        ->where('id_vehiculo', $this->id_vehiculo) // Asegúrate de que estás usando el ID correcto
//                        ->update([
//                            'id_tarifario' => $this->id_tarifario, // ID de la tarifa seleccionada
//                        ]);
//                    DB::table('tarifas_movil')->insert([
//                        'id_vehiculo' => $this->id_vehiculo,
//                        'id_tarifario' => $this->id_tarifario,
//                        'created_at' => now(),
//                        'id_users' => Auth::id(),
//                    ]);

                    // Verificar si el vehículo ya tiene una tarifa asignada
                    $tarifa = DB::table('tarifas_movil')
                        ->where('id_vehiculo', $this->id_vehiculo)
                        ->first();

                    if ($tarifa) {
                        // Si ya existe una tarifa, solo actualiza la tarifa
                        DB::table('tarifas_movil')
                            ->where('id_vehiculo', $this->id_vehiculo)
                            ->update(['id_tarifario' => $this->id_tarifario, 'observaciones' => $this->observaciones]); // Actualiza la tarifa
                    } else {
                        // Si no existe, crea una nueva entrada
                        DB::table('tarifas_movil')->insert([
                            'id_vehiculo' => $this->id_vehiculo,
                            'id_tarifario' => $this->id_tarifario,
                            'observaciones' => $this->observaciones,
                            'created_at' => now(),
                            'id_users' => Auth::id(),
                        ]);
                    }

                    DB::commit();
                    $this->dispatch('hideModal');
                    session()->flash('success', 'Registro actualizado correctamente.');
                } else {
                    session()->flash('error', 'La placa ingresada para el vehículo ya está registrada.');
                    return;
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro: ' . $e->getMessage());
        }
    }
    public function btn_disable($id_vehiculo,$estado){
        $id = base64_decode($id_vehiculo);
        $status = $estado;
        if ($id){
            $this->id_vehiculo = $id;
            $this->vehiculo_estado = $status;
            if ($status == 0){
                $this->messageDeleteVehiculo = "¿Está seguro que desea deshabilitar este registro?";
            }else{
                $this->messageDeleteVehiculo = "¿Está seguro que desea habilitar este registro?";
            }
        }
    }

    public function disable_vehiculo(){
        try {

            if (!Gate::allows('disable_vehiculo')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }

            $this->validate([
                'id_vehiculo' => 'required|integer',
                'vehiculo_estado' => 'required|integer',
            ], [
                'id_vehiculo.required' => 'El identificador es obligatorio.',
                'id_vehiculo.integer' => 'El identificador debe ser un número entero.',

                'vehiculo_estado.required' => 'El estado es obligatorio.',
                'vehiculo_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $vehiculoDelete = Vehiculo::find($this->id_vehiculo);
            $vehiculoDelete->vehiculo_estado = $this->vehiculo_estado;
            if ($vehiculoDelete->save()) {
                DB::commit();
                $this->dispatch('hideModalDelete');
                if ($this->vehiculo_estado == 0){
                    session()->flash('success', 'Registro deshabilitado correctamente.');
                }else{
                    session()->flash('success', 'Registro habilitado correctamente.');
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
