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
use App\Models\Campania;
use App\Models\General;
use App\Models\Campaniapremio;

class Gestionarpremios extends Component{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads;
    private $logs;
    private $premio;
    private $campania;
    private $general;
    private $Campaniapremio;
    public function __construct(){
        $this->logs = new Logs();
        $this->premio = new Premio();
        $this->campania = new Campania();
        $this->general = new General();
        $this->Campaniapremio = new Campaniapremio();
    }
    // PREMIOS
    public $search_premios;
    public $id_premio = "";
    public $premio_codigo = "";
    public $premio_descripcion = "";
    public $premio_documento = "";
//    public $premio_en_campania = "";
    public $premio_estado = "";
    public $messageDelete = "";
    public $existingImage = null;
    public $listar_premios = [];

    // CAMPAÑAS - PREMIOS
    public $listar_campania_premios = [];
    public $id_campania = "";
    public $premios_seleccionados = [];
    public $puntajes_premios = [];

    public $premios_existentes_db = [];

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
//        $this->premio_en_campania = "";
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
//                $save_premio->premio_en_campania = 0;
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

        // Verificar si el premio está habilitado
        $premio = DB::table('premios')
            ->where('id_premio', $id)
            ->first();

        if (!$premio) {
            session()->flash('error', 'El premio seleccionado no existe.');
            return;
        }

        if ($premio->premio_estado != 1) {
            session()->flash('error', 'El premio "' . $premio->premio_descripcion . '" no está habilitado para su uso.');
            return;
        }

        if (!in_array($id, $this->premios_seleccionados)) {
            $this->premios_seleccionados[] = $id;
            // Si ya tuvo puntaje antes, conservarlo; si no, inicializar
            if (!isset($this->puntajes_premios[$id])) {
                $this->puntajes_premios[$id] = '';
            }
            $this->cargarPremiosCampania();
            session()->flash('success', 'Premio agregado correctamente.');
        } else {
            session()->flash('error', 'Este premio ya fue agregado anteriormente.');
        }
    }

    public function quitarPremio($id_premio){
        $id = base64_decode($id_premio);
        $key = array_search($id, $this->premios_seleccionados);
        if ($key !== false) {
            unset($this->premios_seleccionados[$key]);
            $this->premios_seleccionados = array_values($this->premios_seleccionados);
            // Puedes mantener el puntaje en memoria por si lo vuelven a agregar,
            // o borrarlo. Yo lo mantengo (no lo unset) por UX.
            $this->cargarPremiosCampania();
        }
    }

    public function cargarPremiosCampania() {
        if (!empty($this->premios_seleccionados)) {
            $this->listar_campania_premios = DB::table('premios')
                ->whereIn('id_premio', $this->premios_seleccionados)
                ->where('premio_estado', 1)
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

            // Validar puntajes
            foreach ($this->premios_seleccionados as $id_premio) {
                $val = $this->puntajes_premios[$id_premio] ?? null;
                if ($val === null || $val === '') {
                    session()->flash('error', 'Todos los premios deben tener un puntaje asignado.');
                    return;
                }
                if (!is_numeric($val)) {
                    session()->flash('error', 'El puntaje debe ser numérico.');
                    return;
                }
            }

            DB::beginTransaction();
            $microtime = microtime(true);

            // Premios actualmente en BD (snapshot al cargar la campaña)
            $existentes = $this->premios_existentes_db;

            // Calcular diferencias
            $toAdd = array_values(array_diff($this->premios_seleccionados, $existentes));
            $toRemove = array_values(array_diff($existentes, $this->premios_seleccionados));
            $toUpdate = array_values(array_intersect($this->premios_seleccionados, $existentes));

            // insertar en pivote y marcar premio_en_campania=1
            foreach ($toAdd as $id_premio) {
                $campaniaPremio = new Campaniapremio();
                $campaniaPremio->id_users = Auth::id();
                $campaniaPremio->id_campania = $this->id_campania;
                $campaniaPremio->id_premio = $id_premio;
                $campaniaPremio->campania_premio_puntaje = $this->puntajes_premios[$id_premio];
                $campaniaPremio->campania_premio_microtime = $microtime;
                $campaniaPremio->campania_premio_estado = 1;
                $campaniaPremio->save();

                // Si tu regla es 1 premio = 1 campaña, márcalo:
//                DB::table('premios')
//                    ->where('id_premio', $id_premio)
//                    ->update(['premio_en_campania' => 1]);
            }

            // actualizar puntajes si cambió
            foreach ($toUpdate as $id_premio) {
                DB::table('campanias_premios')
                    ->where('id_campania', $this->id_campania)
                    ->where('id_premio', $id_premio)
                    ->update([
                        'campania_premio_puntaje' => $this->puntajes_premios[$id_premio],
                    ]);
            }

            // desactivar del pivote y marcar premio_en_campania=0 (si aplica)
//            foreach ($toRemove as $id_premio) {
//                DB::table('campanias_premios')
//                    ->where('id_campania', $this->id_campania)
//                    ->where('id_premio', $id_premio)
//                    ->update(['campania_premio_estado' => 0]);
//
//                // Si tu política es exclusiva, libéralo:
//                DB::table('premios')
//                    ->where('id_premio', $id_premio)
//                    ->update(['premio_en_campania' => 0]);
//            }

            DB::commit();
            $this->dispatch('hide_modal_confirmar_premios');
            session()->flash('success', 'Premios de la campaña actualizados correctamente.');

            // Refrescar snapshot y data visible
            $this->updatedIdCampania($this->id_campania);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Error al guardar los premios de la campaña: '.$e->getMessage());
        }
    }



    public function updatedIdCampania($value){
        // Limpiar selección cuando cambie la campaña
        $this->premios_seleccionados = [];
        $this->puntajes_premios = [];
        $this->listar_campania_premios = [];
        $this->premios_existentes_db = [];

        if (empty($value)) return;

        // Trae lo que YA tiene la campaña desde la tabla pivote
        $premiosCampania = DB::table('campanias_premios as cp')
            ->join('premios as p', 'p.id_premio', '=', 'cp.id_premio')
            ->where('cp.id_campania', $value)
            ->where('cp.campania_premio_estado', 1)       // si manejas estado
            ->select([
                'p.id_premio',
                'p.premio_codigo',
                'p.premio_descripcion',
                'p.premio_documento',
                'cp.campania_premio_puntaje as puntaje'
            ])
            ->orderBy('p.id_premio', 'asc')
            ->get();

        // Popular estados en memoria para la tabla derecha
        foreach ($premiosCampania as $row) {
            $this->premios_seleccionados[] = $row->id_premio;
            $this->puntajes_premios[$row->id_premio] = $row->puntaje;
        }

        // Guardar “snapshot” de lo existente en BD para diffs al guardar
        $this->premios_existentes_db = collect($premiosCampania)->pluck('id_premio')->all();

        // Renderizar el detalle usando la misma función que ya tienes
        $this->cargarPremiosCampania();
    }


}
