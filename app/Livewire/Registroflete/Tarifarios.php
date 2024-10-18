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
use App\Models\Logs;
use App\Models\Tarifario;
use App\Models\TipoServicio;

class Tarifarios extends Component
{
    use WithPagination, WithoutUrlPagination;
    public $tarifarios;
    public $search_tarifario;
    public $pagination_tarifario = 10;
    public $id_tarifario = "";
    public $id_transportistas = "";
    public $id_tipo_servicio = "";
    public $id_ubigeo_salida = "";
    public $id_ubigeo_llegada = "";
    public $tarifa_cap_min = "";
    public $tarifa_cap_max = "";
    public $tarifa_monto = "";
    public $tarifa_tipo_bulto = "";
    public $tarifa_estado = "";
    public $listar_servicios = array();
    public $messageDeleteTarifario = "";


    private $logs;
    private $ubigeo;
    private $tarifario;
    public function __construct(){
        $this->logs = new Logs();
        $this->ubigeo = new Ubigeo();
        $this->tarifario = new Tarifario();
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
        $listar_ubigeos = $this->ubigeo->listar_ubigeos();
        $tarifario = $this->tarifario->listar_tarifarios($this->id_transportistas,$this->search_tarifario,$this->pagination_tarifario);
        return view('livewire.registroflete.tarifarios', compact('listar_ubigeos', 'tarifario'));
    }

    public function limpiar_nombre_convenio(){
        $this->dispatch('limpiar_nombre_convenio');
    }

    public function clear_form_tarifario(){
        $this->id_tarifario = "";
        $this->id_tipo_servicio = "";
        $this->id_ubigeo_salida = "";
        $this->id_ubigeo_llegada = "";
        $this->tarifa_cap_min = "";
        $this->tarifa_cap_max = "";
        $this->tarifa_monto = "";
        $this->tarifa_tipo_bulto = "";
        $this->tarifa_estado = "";
        $this->dispatch('select_ubigeo_salida',['text' => null]);
        $this->dispatch('select_ubigeo_llegada',['text' => null]);
    }

    public function edit_data($id){
        $tarifario_Edit = Tarifario::find(base64_decode($id));
        if ($tarifario_Edit){
            $this->id_transportistas = $tarifario_Edit->id_transportistas;
            $this->id_tipo_servicio = $tarifario_Edit->id_tipo_servicio;
            $this->id_ubigeo_salida = $tarifario_Edit->id_ubigeo_salida;
            $this->id_ubigeo_llegada = $tarifario_Edit->id_ubigeo_llegada;
            $this->tarifa_cap_min = $tarifario_Edit->tarifa_cap_min;
            $this->tarifa_cap_max = $tarifario_Edit->tarifa_cap_max;
            $this->tarifa_monto = $tarifario_Edit->tarifa_monto;
            $this->tarifa_tipo_bulto = $tarifario_Edit->tarifa_tipo_bulto;
            $this->id_tarifario = $tarifario_Edit->id_tarifario;
            $opcionSelect = "";
            if ($tarifario_Edit->id_ubigeo_salida){
                $ubi = Ubigeo::find($tarifario_Edit->id_ubigeo_salida);
                $opcionSelect = $ubi->ubigeo_departamento." - ".$ubi->ubigeo_provincia." - ".$ubi->ubigeo_distrito;
            }
            $this->dispatch('select_ubigeo_salida',['text' => $opcionSelect]);
        }
    }

    public function saveTarifario() {
        try {
            $this->validate([
                'id_transportistas' => 'required|integer',
                'id_tipo_servicio' => 'required|integer',
                'id_ubigeo_salida' => 'required_if:id_tipo_servicio,2|nullable|integer',
                'id_ubigeo_llegada' => 'required_if:id_tipo_servicio,2|nullable|integer',
                'tarifa_cap_min' => 'required|numeric',
                'tarifa_cap_max' => 'required|numeric',
                'tarifa_monto' => 'required|numeric',
                'tarifa_tipo_bulto' => 'required|string',
                'tarifa_estado' => 'nullable|integer',
                'id_tarifario' => 'nullable|integer',
            ], [
                'id_transportistas.required' => 'Debes seleccionar un transportista.',
                'id_transportistas.integer' => 'El transportista debe ser un número entero.',

                'id_tipo_servicio.required' => 'Debes seleccionar un servicio.',
                'id_tipo_servicio.integer' => 'El servicio debe ser un número entero.',

                'id_ubigeo_salida.required_if' => 'La salida es obligatoria para servicios provinciales.',
                'id_ubigeo_salida.integer' => 'La salida debe ser un número entero.',

                'id_ubigeo_llegada.required_if' => 'La llegada es obligatoria para servicios provinciales.',
                'id_ubigeo_llegada.integer' => 'La llegada debe ser un número entero.',

                'tarifa_cap_min.required' => 'La capacidad minima es obligatorio.',
                'tarifa_cap_min.numeric' => 'La capacidad minima debe ser un valor numérico.',

                'tarifa_cap_max.required' => 'La capacidad maxima es obligatorio.',
                'tarifa_cap_max.numeric' => 'La capacidad maxima debe ser un valor numérico.',

                'tarifa_monto.required' => 'El monto de la tarifa es obligatorio.',
                'tarifa_monto.numeric' => 'El monto de la tarifa debe ser un valor numérico.',

                'tarifa_tipo_bulto.required' => 'El bulto es obligatoria.',
                'tarifa_tipo_bulto.string' => 'El bulto debe ser una cadena de texto.',

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
                    $tarifario_save->id_ubigeo_salida = !empty($this->id_ubigeo_salida) ? $this->id_ubigeo_salida : null;
                    $tarifario_save->id_ubigeo_llegada = !empty($this->id_ubigeo_llegada) ? $this->id_ubigeo_llegada : null;
                    $tarifario_save->tarifa_cap_min = $this->tarifa_cap_min;
                    $tarifario_save->tarifa_cap_max = $this->tarifa_cap_max;
                    $tarifario_save->tarifa_monto = $this->tarifa_monto;
                    $tarifario_save->tarifa_tipo_bulto = $this->tarifa_tipo_bulto;
                    $tarifario_save->tarifa_estado = 1;
                    $tarifario_save->tarifa_microtime = $microtime;

                    if ($tarifario_save->save()) {
                        DB::commit();
                        $this->dispatch('hideModal');
                        session()->flash('success', 'Registro guardado correctamente.');
                    } else {
                        DB::rollBack();
                        session()->flash('error', 'Ocurrió un error al guardar el registro.');
                    }
                } else{
                    session()->flash('error', 'El rango de capacidad se solapa con un registro existente.');
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
                    'id_ubigeo_salida' => 'required_if:id_tipo_servicio,2|nullable|integer',
                    'id_ubigeo_llegada' => 'required_if:id_tipo_servicio,2|nullable|integer',
                    'tarifa_cap_min' => 'required|numeric',
                    'tarifa_cap_max' => 'required|numeric',
                    'tarifa_monto' => 'required|numeric',
                    'tarifa_tipo_bulto' => 'required|string',
                    'tarifa_estado' => 'nullable|integer',
                    'id_tarifario' => 'nullable|integer',
                ], [
                    'id_transportistas.required' => 'Debes seleccionar un transportista.',
                    'id_transportistas.integer' => 'El transportista debe ser un número entero.',

                    'id_tipo_servicio.required' => 'Debes seleccionar un servicio.',
                    'id_tipo_servicio.integer' => 'El servicio debe ser un número entero.',

                    'id_ubigeo_salida.required_if' => 'La salida es obligatoria para servicios provinciales.',
                    'id_ubigeo_salida.integer' => 'La salida debe ser un número entero.',

                    'id_ubigeo_llegada.required_if' => 'La llegada es obligatoria para servicios provinciales.',
                    'id_ubigeo_llegada.integer' => 'La llegada debe ser un número entero.',

                    'tarifa_cap_min.required' => 'La capacidad mínima es obligatoria.',
                    'tarifa_cap_min.numeric' => 'La capacidad mínima debe ser un valor numérico.',

                    'tarifa_cap_max.required' => 'La capacidad máxima es obligatoria.',
                    'tarifa_cap_max.numeric' => 'La capacidad máxima debe ser un valor numérico.',

                    'tarifa_monto.required' => 'El monto de la tarifa es obligatorio.',
                    'tarifa_monto.numeric' => 'El monto de la tarifa debe ser un valor numérico.',

                    'tarifa_tipo_bulto.required' => 'El bulto es obligatorio.',
                    'tarifa_tipo_bulto.string' => 'El bulto debe ser una cadena de texto.',

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
                    // Actualizar los datos
                    $tarifario_update = Tarifario::findOrFail($this->id_tarifario);
                    $tarifario_update->id_tipo_servicio = $this->id_tipo_servicio;
                    $tarifario_update->id_ubigeo_salida = !empty($this->id_ubigeo_salida) ? $this->id_ubigeo_salida : null;
                    $tarifario_update->id_ubigeo_llegada = !empty($this->id_ubigeo_llegada) ? $this->id_ubigeo_llegada : null;
                    $tarifario_update->tarifa_cap_min = $this->tarifa_cap_min;
                    $tarifario_update->tarifa_cap_max = $this->tarifa_cap_max;
                    $tarifario_update->tarifa_monto = $this->tarifa_monto;
                    $tarifario_update->tarifa_tipo_bulto = $this->tarifa_tipo_bulto;

                    if (!$tarifario_update->save()) {
                        session()->flash('error', 'No se pudo actualizar el registro.');
                        return;
                    }
                    DB::commit();
                    $this->dispatch('hideModal');
                    session()->flash('success', 'Registro actualizado correctamente.');
                } else {
                    session()->flash('error', 'El rango de capacidad se solapa con un registro existente.');
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
                $this->messageDeleteTarifario = "¿Está seguro que desea deshabilitar este registro?";
            }else{
                $this->messageDeleteTarifario = "¿Está seguro que desea habilitar este registro?";
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
