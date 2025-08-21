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
    public $id_campania = "";
    public $archivo_excel;
    public $archivo_pdf;
    public $listar_detalles = [];
    public $editando_registros = [];
    public $datos_edicion = [];
    public $listar_campanias = [];
    public $punto_codigo = "";
    public function mount(){
        $this->listar_campanias = DB::table('campanias')
            ->where('campania_estado', 1)
            ->orderBy('campania_nombre')
            ->get();
    }

    public function render(){
        $listar_puntos = $this->punto->listar_puntos_registrados();
        foreach ($listar_puntos as $lp){
            $lp->puntos_detalles = DB::table('puntos_detalles')
                ->where('punto_detalle_estado', '=', 1)
                ->where('id_punto', '=', $lp->id_punto)
                ->get();
        }
        return view('livewire.crm.gestionpuntos', compact('listar_puntos'));
    }

    public function clear_form(){
        $this->id_punto = "";
        $this->archivo_excel = "";
        $this->archivo_pdf = "";
    }

    public function save_carga_excel(){
        try {
            if (!Gate::allows('save_carga_excel')) {
                session()->flash('error', 'No tiene permisos para crear.');
                return;
            }
            // Validar que el archivo Excel sea obligatorio
            if (!$this->archivo_excel) {
                session()->flash('error', 'El archivo Excel es obligatorio.');
                return;
            }
            // Validar que sea un archivo Excel válido
            $extension = strtolower($this->archivo_excel->getClientOriginalExtension());
            $allowedExtensions = ['xlsx', 'xls'];

            if (!in_array($extension, $allowedExtensions)) {
                session()->flash('error', 'El archivo debe ser de tipo Excel (xlsx, xls).');
                return;
            }

            $microtime = microtime(true);
            DB::beginTransaction();
            $ultimoPremio = Punto::orderBy('id_punto', 'desc')->first();
            $codigo_nuevo = $ultimoPremio ? $ultimoPremio->id_punto + 1 : 1;

            // Insertar registro en tabla puntos
            $punto = new Punto();
            $punto->id_users = Auth::id();
            $punto->id_campania = null;
            $punto->id_cliente = null;
            $punto->punto_codigo = 'P-000' . $codigo_nuevo;
            if ($this->archivo_excel) {
                $punto->punto_documento_excel = $this->general->save_files($this->archivo_excel, 'puntos/excel');
            }
            if ($this->archivo_pdf) {
                $punto->punto_documento_pdf = $this->general->save_files($this->archivo_pdf, 'puntos/pdf');
            }
            $punto->punto_microtime = $microtime;
            $punto->punto_estado = 1;
            $punto->save();

            // Generar correlativo P-000{id}
            $punto->save();

            // Leer datos del Excel
            $spreadsheet = IOFactory::load($this->archivo_excel->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Saltar cabecera (empieza en fila 2)
            foreach (array_slice($rows, 1) as $row) {
                $dni = $row[0] ?? null;
                $motivo = $row[1] ?? null;
                $puntos = $row[2] ?? null;

                if (!$dni || !$motivo || !$puntos) {
                    continue;
                }

                $detalle = new Puntodetalle();
                $detalle->id_users = Auth::id();
                $detalle->id_punto = $punto->id_punto;
                $detalle->punto_detalle_motivo = $motivo;
                $detalle->punto_detalle_vendedor = $dni;
                $detalle->punto_detalle_punto_ganado = $puntos;
                $detalle->punto_detalle_fecha_registro = now('Ameriaca')->toDateString();
                $detalle->punto_detalle_fecha_modificacion = null;
                $detalle->punto_detalle_microtime = $microtime;
                $detalle->punto_detalle_estado = 1;
                $detalle->save();
            }

            DB::commit();
            $this->dispatch('hide_modal_carga_excel');
            session()->flash('success', 'Archivo(s) procesado(s) correctamente.');
            $this->clear_form();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function editar_punto($id_punto){
        $this->id_punto = base64_decode($id_punto);

        if ($this->id_punto){
            // Obtener los detalles
            $this->listar_detalles = DB::table('puntos_detalles')
                ->where('punto_detalle_estado', '=', 1)
                ->where('id_punto', '=', $this->id_punto)
                ->get();

            // Obtener el registro de punto para saber la campaña
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

            // Validar que hay registros en edición
            if (empty($this->editando_registros)) {
                session()->flash('error_moda_editar', 'No hay registros en edición para guardar.');
                return;
            }

            // Actualizar la campaña del punto principal si cambió
            if ($this->id_punto && $this->id_campania) {
                DB::table('puntos')
                    ->where('id_punto', $this->id_punto)
                    ->update([
                        'id_campania' => $this->id_campania,
                    ]);
            }

            // Validar que todos los campos requeridos estén llenos
            foreach ($this->editando_registros as $id_registro) {
                if (empty($this->datos_edicion[$id_registro]['motivo']) ||
                    empty($this->datos_edicion[$id_registro]['vendedor']) ||
                    empty($this->datos_edicion[$id_registro]['puntos'])) {
                    session()->flash('error_moda_editar', 'Todos los campos son obligatorios.');
                    return;
                }
            }

            DB::beginTransaction();

            $registros_actualizados = 0;

            // Iterar solo sobre los registros que están en edición
            foreach ($this->editando_registros as $id_punto_detalle) {
                // Verificar que existen los datos de edición para este registro
                if (isset($this->datos_edicion[$id_punto_detalle])) {

                    $datos = $this->datos_edicion[$id_punto_detalle];

                    // Actualizar el registro en la base de datos
                    $actualizado = DB::table('puntos_detalles')
                        ->where('id_punto_detalle', $id_punto_detalle)
                        ->where('punto_detalle_estado', 1)
                        ->update([
                            'punto_detalle_motivo' => $datos['motivo'],
                            'punto_detalle_vendedor' => $datos['vendedor'],
                            'punto_detalle_punto_ganado' => $datos['puntos'],
                            'punto_detalle_fecha_modificacion' => now('America/Lima')->toDateString(),
                            'updated_at' => now('America/Lima')
                        ]);

                    if ($actualizado) {
                        $registros_actualizados++;
                    }
                }
            }

            DB::commit();

            // Limpiar los estados de edición después de guardar
            $this->editando_registros = [];
            $this->datos_edicion = [];

            // Recargar la lista de detalles para mostrar los cambios
            if ($this->id_punto) {
                $this->listar_detalles = DB::table('puntos_detalles')
                    ->where('punto_detalle_estado', '=', 1)
                    ->where('id_punto', '=', $this->id_punto)
                    ->get();
            }

            $this->dispatch('hide_modal_editar_punto');
            session()->flash('success', "Se actualizaron {$registros_actualizados} registro(s) correctamente.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
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

            $actualizar = DB::table('puntos_detalles')
                ->where('id_punto_detalle', $id_p_de)
                ->where('punto_detalle_estado', 1) // Solo si está activo
                ->update([
                    'punto_detalle_estado' => 0,
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
                session()->flash('success_moda_editar', 'Registro eliminado correctamente.');
            } else {
                DB::rollBack();
                session()->flash('error_moda_editar', 'No se pudo eliminar el registro o ya fue eliminado.');
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
            $punto_delete = Punto::find($this->id_punto);
            $punto_delete->punto_estado = 0;
            if ($punto_delete->save()) {
                DB::commit();
                $this->dispatch('hide_modal_delete_punto');
                session()->flash('success', 'Registro eliminado correctamente.');
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo eliminar el punto.');
                return;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al eliminar el registro. Por favor, inténtelo nuevamente.');
        }
    }

}
