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
use App\Models\Campania;
use App\Models\Campaniadocumento;
use App\Models\General;
use Illuminate\Support\Facades\Storage;

class Gestioncampanias extends Component{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads;
    private $logs;
    private $campania;
    private $campaniadocumento;
    private $general;

    public function __construct(){
        $this->logs = new Logs();
        $this->campania = new Campania();
        $this->campaniadocumento = new Campaniadocumento();
        $this->general = new General();
    }
    public $search_campania;
    public $pagination_campania = 10;
    public $messageDelete = "";
    public $id_campania = "";
    public $campania_nombre = "";
    public $campania_fecha_inicio = "";
    public $campania_fecha_fin = "";
    public $campania_fecha_fin_canje = "";
    public $campania_nombre_admin = "";
    public $campania_celular = "";
    public $campania_estado_ejecucion = "";
    public $campania_estado = "";
    public $archivos = [];
    public $archivosNuevos = [];
    public $archivosAEliminar = [];
    public $desde;
    public $hasta;
    public $buscar_estado;
    public function mount(){
        $this->desde =  date('Y-m-d');
        $this->hasta =  date('Y-m-d');
    }

    public function render(){
        $listar_campanias = $this->campania->listar_campanias($this->desde, $this->hasta, $this->buscar_estado, $this->search_campania, $this->pagination_campania);
        return view('livewire.crm.gestioncampanias', compact('listar_campanias'));
    }

    public function updatedArchivosNuevos(){
        // (Opcional) evitar duplicados por nombre+size
        $existentes = array_map(function($f){
            if (is_object($f) && method_exists($f, 'getClientOriginalName')) {
                // Archivo nuevo (objeto UploadedFile)
                return ($f->getClientOriginalName() ?? '') . '|' . ($f->getSize() ?? 0);
            } else {
                // Archivo existente (string - ruta)
                return basename($f) . '|0'; // Para archivos existentes usamos 0 como size
            }
        }, $this->archivos);

        foreach ($this->archivosNuevos as $nuevo) {
            if (is_object($nuevo) && method_exists($nuevo, 'getClientOriginalName')) {
                $clave = ($nuevo->getClientOriginalName() ?? '') . '|' . ($nuevo->getSize() ?? 0);
                if (!in_array($clave, $existentes, true)) {
                    $this->archivos[] = $nuevo;
                    $existentes[] = $clave;
                }
            }
        }

        $this->archivosNuevos = [];
    }

    public function removeArchivo($index): void{
        if (isset($this->archivos[$index])) {
            // Si es un archivo existente (string), agregarlo a la lista de eliminación
            if (is_string($this->archivos[$index])) {
                $this->archivosAEliminar[] = $this->archivos[$index];
            }

            unset($this->archivos[$index]);
            $this->archivos = array_values($this->archivos); // Reindexar
        }
    }

    public function clear_form(){
        $this->id_campania = "";
        $this->campania_nombre = "";
        $this->campania_fecha_inicio = "";
        $this->campania_fecha_fin = "";
        $this->campania_fecha_fin_canje = "";
        $this->campania_nombre_admin = "";
        $this->campania_celular = "";
        $this->campania_estado_ejecucion = "";
        $this->campania_estado = "";
        $this->archivos = [];
        $this->archivosNuevos = [];
    }

    public function edit_data($id){
        $campania_edit = Campania::find(base64_decode($id));
        if ($campania_edit) {
            $this->campania_nombre = $campania_edit->campania_nombre;
            $this->campania_fecha_inicio = $campania_edit->campania_fecha_inicio;
            $this->campania_fecha_fin = $campania_edit->campania_fecha_fin;
            $this->campania_fecha_fin_canje = $campania_edit->campania_fecha_fin_canje;
            $this->campania_nombre_admin = $campania_edit->campania_nombre_admin;
            $this->campania_celular = $campania_edit->campania_celular;
            $this->campania_estado_ejecucion = $campania_edit->campania_estado_ejecucion;
            $this->id_campania = $campania_edit->id_campania;

            // Cargar archivos existentes como array de strings (rutas)
            $this->archivos = Campaniadocumento::where('id_campania', $this->id_campania)
                ->where('campania_documento_estado', 1)
                ->pluck('campania_documento_adjunto')
                ->toArray();
        }
    }

    public function btn_disable($id_cam,$esta){
        $id = base64_decode($id_cam);
        $status = $esta;
        if ($id){
            $this->id_campania = $id;
            $this->campania_estado = $status;
            if ($status == 0){
                $this->messageDelete = "¿Está seguro que desea deshabilitar esta campaña?";
            }else{
                $this->messageDelete = "¿Está seguro que desea habilitar esta campaña?";
            }
        }
    }

    public function disable_campania(){
        try {
            if (!Gate::allows('disable_campania')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados de la campaña.');
                return;
            }

            $this->validate([
                'id_campania' => 'required|integer',
                'campania_estado' => 'required|integer',
            ], [
                'id_campania.required' => 'El identificador es obligatorio.',
                'id_campania.integer' => 'El identificador debe ser un número entero.',

                'campania_estado.required' => 'El estado es obligatorio.',
                'campania_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $campania_delete = Campania::find($this->id_campania);
            $campania_delete->campania_estado = $this->campania_estado;
            if ($campania_delete->save()) {
                DB::commit();
                $this->dispatch('hide_modal_delete_campania');
                if ($this->campania_estado == 0){
                    session()->flash('success', 'Registro deshabilitado correctamente.');
                }else{
                    session()->flash('success', 'Registro habilitado correctamente.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo cambiar el estado de la campaña.');
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

    public function save_campania(){
        try {
            $this->validate([
                'campania_nombre' => 'required|string',
                'campania_estado_ejecucion' => 'required|integer',
                'campania_fecha_inicio' => 'required|date',
                'campania_fecha_fin' => 'required|date|after_or_equal:campania_fecha_inicio',
                'campania_fecha_fin_canje' => 'required|date|after:campania_fecha_fin',
                'campania_nombre_admin' => 'required|string',
                'campania_celular' => 'required|numeric|digits:9',
                'archivos' => 'required|array|min:1',
            ], [
                'campania_nombre.required' => 'El nombre de la campaña es obligatorio.',
                'campania_nombre.string' => 'El nombre debe ser texto válido.',

                'campania_estado_ejecucion.required' => 'El estado de ejecución es obligatorio.',
                'campania_estado_ejecucion.integer' => 'El estado debe ser un valor numérico.',

                'campania_fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
                'campania_fecha_inicio.date' => 'La fecha de inicio debe ser válida.',

                'campania_fecha_fin.required' => 'La fecha de fin es obligatoria.',
                'campania_fecha_fin.date' => 'La fecha de fin debe ser válida.',
                'campania_fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',

                'campania_fecha_fin_canje.required' => 'La fecha de fin del canje es obligatoria.',
                'campania_fecha_fin_canje.date' => 'La fecha de fin del canje debe ser válida.',
                'campania_fecha_fin_canje.after' => 'La fecha de fin del canje debe ser posterior a la fecha de fin de la campaña.',

                'campania_nombre_admin.required' => 'El nombre del administrador es obligatorio.',
                'campania_nombre_admin.string' => 'El nombre del administrador debe ser texto válido.',

                'campania_celular.required' => 'El celular del administrador es obligatorio.',
                'campania_celular.numeric' => 'El Celular del administrador debe contener solo números.',
                'campania_celular.digits' => 'El Celular del administrador debe tener exactamente 9 dígitos.',

                'archivos.required' => 'Debe adjuntar al menos un archivo.',
                'archivos.array' => 'Los archivos deben ser enviados correctamente.',
                'archivos.min' => 'Debe adjuntar al menos un archivo.',
            ]);

            foreach ($this->archivos as $index => $archivo) {
                if (is_object($archivo) && method_exists($archivo, 'getClientOriginalName')) {
                    $validator = validator(['archivo' => $archivo], [
                        'archivo' => 'file|max:10240'
                    ], [
                        'archivo.file' => 'El archivo adjunto no es válido.',
                        'archivo.max' => 'Cada archivo no debe exceder los 10MB de tamaño.',
                    ]);

                    if ($validator->fails()) {
                        $this->addError("archivos.{$index}", $validator->errors()->first('archivo'));
                        return;
                    }
                }
            }

            $microtime = microtime(true);

            DB::beginTransaction();

            if (empty($this->archivos)) {
                session()->flash('error_modal', 'Debes seleccionar al menos un archivo adjunto.');
                return;
            }

            if (!$this->id_campania) { // INSERT
                if (!Gate::allows('create_campania')) {
                    session()->flash('error', 'No tiene permisos para crear la campaña.');
                    return;
                }

                $save_campania = new Campania();
                $save_campania->id_users = Auth::id();
                $save_campania->campania_nombre = $this->campania_nombre;
                $save_campania->campania_fecha_inicio = $this->campania_fecha_inicio;
                $save_campania->campania_fecha_fin = $this->campania_fecha_fin;
                $save_campania->campania_fecha_fin_canje = $this->campania_fecha_fin_canje;
                $save_campania->campania_nombre_admin = $this->campania_nombre_admin;
                $save_campania->campania_celular = $this->campania_celular;
                $save_campania->campania_estado_ejecucion = $this->campania_estado_ejecucion;
                $save_campania->campania_microtime = $microtime;
                $save_campania->campania_estado = 1;

                if ($save_campania->save()) {
                    // Guardar los archivos adjuntos
                    foreach ($this->archivos as $archivo) {
                        $documento = new Campaniadocumento();
                        $documento->id_users = Auth::id();
                        $documento->id_campania = $save_campania->id_campania;
                        // Guardar el archivo usando la función del helper
                        $documento->campania_documento_adjunto = $this->general->save_files_campanha($archivo, 'campanias/documentos');
                        $documento->campania_documento_microtime = $microtime;
                        $documento->campania_documento_estado = 1;

                        if (!$documento->save()) {
                            session()->flash('error', 'Error al guardar documento adjunto.');
                            return;
                        }
                    }

                    DB::commit();
                    $this->dispatch('hide_modal_campania');
                    session()->flash('success', 'Campania y documentos guardados correctamente.');
                    $this->clear_form();
                } else {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar la campaña.');
                }
            } else { // UPDATE
                if (!Gate::allows('update_campania')) {
                    session()->flash('error', 'No tiene permisos para actualizar campañas.');
                    return;
                }

                $update_campania = Campania::findOrFail($this->id_campania);
                $update_campania->campania_nombre = $this->campania_nombre;
                $update_campania->campania_fecha_inicio = $this->campania_fecha_inicio;
                $update_campania->campania_fecha_fin = $this->campania_fecha_fin;
                $update_campania->campania_fecha_fin_canje = $this->campania_fecha_fin_canje;
                $update_campania->campania_nombre_admin = $this->campania_nombre_admin;
                $update_campania->campania_celular = $this->campania_celular;
                $update_campania->campania_estado_ejecucion = $this->campania_estado_ejecucion;

                if ($update_campania->save()) {
                    // Eliminar archivos marcados (soft delete)
                    if (!empty($this->archivosAEliminar)) {
                        Campaniadocumento::where('id_campania', $this->id_campania)
                            ->whereIn('campania_documento_adjunto', $this->archivosAEliminar)
                            ->update(['campania_documento_estado' => 0]);
                    }

                    // Guardar nuevos archivos (solo los que son objetos UploadedFile)
                    foreach ($this->archivos as $archivo) {
                        if (is_object($archivo) && method_exists($archivo, 'getClientOriginalName')) {
                            $documento = new Campaniadocumento();
                            $documento->id_users = Auth::id();
                            $documento->id_campania = $this->id_campania;
                            $documento->campania_documento_adjunto = $this->general->save_files_campanha($archivo, 'campanias/documentos');
                            $documento->campania_documento_microtime = microtime(true);
                            $documento->campania_documento_estado = 1;
                            $documento->save();
                        }
                    }
                    DB::commit();
                    $this->dispatch('hide_modal_campania');
                    session()->flash('success', 'Campaña actualizada correctamente.');
                    $this->clear_form();
                } else {
                    DB::rollBack();
                    session()->flash('error', 'Error al actualizar la campaña.');
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
}
