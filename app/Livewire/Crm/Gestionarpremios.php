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
use App\Models\Premio;
use App\Models\Campaña;
use App\Models\General;
use App\Models\Campañaprecio;

class Gestionarpremios extends Component{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads;
    private $logs;
    private $premio;
    private $campania;
    private $general;
    private $campañaprecio;
    public function __construct(){
        $this->logs = new Logs();
        $this->premio = new Premio();
        $this->campania = new Campaña();
        $this->general = new General();
        $this->campañaprecio = new Campañaprecio();
    }
    // PREMIOS
    public $search_premios;
    public $id_premio = "";
    public $premio_codigo = "";
    public $premio_descripcion = "";
    public $premio_documento = "";
    public $premio_en_campania = "";
    public $premio_estado = "";
    public $messageDelete = "";
    public $existingImage = null;
    public $listar_premios = [];

    // CAMPAÑAS - PREMIOS
    public $listar_campania_premios = [];
    public $id_campania = "";
    public $premios_seleccionados = [];
    public $puntajes_premios = [];

    public function render(){
        $this->listar_premios = $this->premio->listar_premios_activos($this->search_premios);
        $listar_campanias = $this->campania->listar_campanias_activos();
        return view('livewire.crm.gestionarpremios', compact('listar_campanias'));
    }

    // PREMIOS
    public function clear_form(){
        $this->id_premio = "";
        $this->premio_codigo = "";
        $this->premio_descripcion = "";
        $this->premio_documento = "";
        $this->existingImage = null; // Limpiar también la imagen existente
        $this->premio_en_campania = "";
        $this->premio_estado = "";
    }

    public function edit_data($id){
        $premio_editar = Premio::find(base64_decode($id));
        if ($premio_editar){
            $this->id_premio = $premio_editar->id_premio;
            $this->premio_descripcion = $premio_editar->premio_descripcion;
            // Guardamos la ruta de la imagen existente en una propiedad separada
            $this->existingImage = $premio_editar->premio_documento;
            // Limpiamos la propiedad de upload para evitar conflictos
            $this->premio_documento = null;
        }
    }

    public function btn_disable($id_premio,$esta){
        $id = base64_decode($id_premio);
        $status = $esta;
        if ($id){
            $this->id_premio = $id;
            $this->premio_estado = $status;
            if ($status == 0){
                $this->messageDelete = "¿Está seguro que desea deshabilitar este premio?";
            }else{
                $this->messageDelete = "¿Está seguro que desea habilitar este premio?";
            }
        }
    }

    public function disable_premio(){
        try {
            if (!Gate::allows('disable_premio')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados del premio.');
                return;
            }

            $this->validate([
                'id_premio' => 'required|integer',
                    'premio_estado' => 'required|integer',
            ], [
                'id_premio.required' => 'El identificador es obligatorio.',
                'id_premio.integer' => 'El identificador debe ser un número entero.',

                'premio_estado.required' => 'El estado es obligatorio.',
                'premio_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $premio_delete = Premio::find($this->id_premio);
            $premio_delete->premio_estado = $this->premio_estado;
            if ($premio_delete->save()) {
                DB::commit();
                $this->dispatch('hide_modal_detele_premio');
                if ($this->premio_estado == 0){
                    session()->flash('success', 'Registro deshabilitado correctamente.');
                }else{
                    session()->flash('success', 'Registro habilitado correctamente.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo cambiar el estado del premio.');
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

    public function save_premio(){
        try {
            $this->validate([
                'premio_descripcion' => 'required|string',
                'premio_documento' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                'id_premio' => 'nullable|integer',
            ], [
                'premio_descripcion.required' => 'El nombre del menú es obligatorio.',
                'premio_descripcion.string' => 'El nombre del menú debe ser una cadena de texto.',

                'premio_documento.file' => 'Debe cargar un archivo válido.',
                'premio_documento.mimes' => 'El archivo debe ser una imagen en formato JPG, JPEG o PNG.',
                'premio_documento.max' => 'La imagen no puede exceder los 2MB.',

                'id_premio.integer' => 'El identificador debe ser un número entero.',
            ]);

            if (!$this->id_premio) { // INSERT
                if (!Gate::allows('create_premio')) {
                    session()->flash('error', 'No tiene permisos para crear.');
                    return;
                }

                $microtime = microtime(true);

                DB::beginTransaction();

                // Obtener el último ID y calcular el siguiente
                $ultimoPremio = Premio::orderBy('id_premio', 'desc')->first();
                $nuevoId = $ultimoPremio ? $ultimoPremio->id_premio + 1 : 1;

                $save_premio = new Premio();
                $save_premio->id_users = Auth::id();
                $save_premio->premio_codigo = 'PR-0' . $nuevoId;
                $save_premio->premio_descripcion = $this->premio_descripcion;
                if ($this->premio_documento) {
                    $save_premio->premio_documento = $this->general->save_files($this->premio_documento, 'premios/img');
                }
                $save_premio->premio_en_campania = 0;
                $save_premio->premio_microtime = $microtime;
                $save_premio->premio_estado = 1;

                if ($save_premio->save()) {
                    $save_premio->premio_codigo = 'PR-0' . $save_premio->id_premio;

                    DB::commit();
                    $this->dispatch('hide_modal_premio');
                    session()->flash('success', 'Registro guardado correctamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar el premio.');
                    return;
                }
            } else {
                if (!Gate::allows('update_premio')) {
                    session()->flash('error', 'No tiene permisos para actualizar los premios.');
                    return;
                }
                DB::beginTransaction();

                // Actualizar los datos del premio
                $update_premio = Premio::findOrFail($this->id_premio);
                $update_premio->premio_descripcion = $this->premio_descripcion;

                if ($this->premio_documento) {
                    // Eliminar la imagen anterior si existe
                    if ($update_premio->premio_documento && file_exists($update_premio->premio_documento)) {
                        try {
                            unlink($update_premio->premio_documento);
                        } catch (\Exception $e) {
                            // Log del error si es necesario
                        }
                    }

                    // Guardar la nueva imagen
                    $update_premio->premio_documento = $this->general->save_files($this->premio_documento, 'premios/img');
                }

                if (!$update_premio->save()) {
                    session()->flash('error', 'No se pudo actualizar el premio.');
                    return;
                }

                DB::commit();
                $this->dispatch('hide_modal_premio');
                session()->flash('success', 'Premio actualizado correctamente.');
                $this->existingImage = null;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }

    //CAMPAÑAS -PREMIOS
    public function agregarPremio($id_premio){
        $id = base64_decode($id_premio);
        // Verificar si el premio ya está agregado
        if (!in_array($id, $this->premios_seleccionados)) {
            $this->premios_seleccionados[] = $id;
            $this->puntajes_premios[$id] = ''; // Inicializar puntaje vacío
            $this->cargarPremiosCampania();
        }
    }

    public function quitarPremio($id_premio){
        $id = base64_decode($id_premio);
        // Buscar y eliminar el premio de la lista
        $key = array_search($id, $this->premios_seleccionados);
        if ($key !== false) {
            unset($this->premios_seleccionados[$key]);
            unset($this->puntajes_premios[$id]);
            $this->premios_seleccionados = array_values($this->premios_seleccionados);
            $this->cargarPremiosCampania();
        }
    }

    public function cargarPremiosCampania(){
        if (!empty($this->premios_seleccionados)) {
            $this->listar_campania_premios = DB::table('premios')
                ->whereIn('id_premio', $this->premios_seleccionados)
                ->where('premio_en_campania', '=', 0)
                ->where('premio_estado', '=', 1)
                ->get();
        } else {
            $this->listar_campania_premios = [];
        }
    }

    public function confirmar_premios_campania(){
        try {
            if (!Gate::allows('confirmar_premios_campania')) {
                session()->flash('error', 'No tiene permisos para confirmar.');
                return;
            }

            if (empty($this->id_campania)) {
                session()->flash('error', 'Debe seleccionar una campaña.');
                return;
            }

            if (empty($this->premios_seleccionados)) {
                session()->flash('error', 'Debe agregar al menos un premio a la campaña.');
                return;
            }

            // Validar que todos los puntajes sean números
            foreach ($this->premios_seleccionados as $id_premio) {
                if (!isset($this->puntajes_premios[$id_premio]) || empty($this->puntajes_premios[$id_premio])) {
                    session()->flash('error', 'Todos los premios deben tener un puntaje asignado.');
                    return;
                }

                if (!is_numeric($this->puntajes_premios[$id_premio])) {
                    session()->flash('error', 'El puntaje debe ser un número válido para el premio.');
                    return;
                }
            }

            $microtime = microtime(true);
            DB::beginTransaction();

            // Actualizar el estado de los premios en la tabla premios
            foreach ($this->premios_seleccionados as $id_premio) {
                $premio = Premio::find($id_premio);
                if ($premio) {
                    $premio->premio_en_campania = 1;
                    $premio->save();
                }
            }

            // Agregar los premios a la tabla campaña_premio
            foreach ($this->premios_seleccionados as $id_premio) {
                $campaniaPremio = new Campañaprecio();
                $campaniaPremio->id_users = Auth::id();
                $campaniaPremio->id_campania = $this->id_campania;
                $campaniaPremio->id_premio = $id_premio;
                $campaniaPremio->campania_premio_puntaje = $this->puntajes_premios[$id_premio];
                $campaniaPremio->campania_premio_microtime = $microtime;
                $campaniaPremio->campania_premio_estado = 1;
                $campaniaPremio->save();
            }

            DB::commit();
            $this->dispatch('hide_modal_confirmar_premios');
            session()->flash('success', 'Premios asignados a la campaña correctamente.');

            // Limpiar selección después de guardar
            $this->premios_seleccionados = [];
            $this->puntajes_premios = [];
            $this->listar_campania_premios = [];
            $this->id_campania = "";

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar los premios de la campaña: ' . $e->getMessage());
        }
    }

}
