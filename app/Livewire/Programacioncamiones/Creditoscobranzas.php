<?php

namespace App\Livewire\Programacioncamiones;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Logs;
use App\Models\Facturaspreprogramacion;
use Illuminate\Support\Facades\Auth;
use App\Models\Facturamovimientoarea;
use App\Models\Historialguia;
use App\Models\Guia;
use Carbon\Carbon;

class Creditoscobranzas extends Component
{
    private $logs;
    private $facpreprog;
    private $facmovarea;
    private $historialguia;
    private $guia;
    public function __construct(){
        $this->logs = new Logs();
        $this->facpreprog = new Facturaspreprogramacion();
        $this->facmovarea = new Facturamovimientoarea();
        $this->historialguia = new Historialguia();
        $this->guia = new Guia();
    }
    public $desde;
    public $hasta;
    public $filteredFacturas = [];
    public $selectedFacturas = [];
    public $pesoTotal = 0;
    public $volumenTotal = 0;
    public $importeTotalVenta = 0;
    public $id_guia  = "";
    public $fac_pre_prog_estado_aprobacion = "";
    public $selectedGuiaIds = [];
    public $messageMotCre;
    public $facturasCreditoAprobadas;
    public $messageMotReCre;
    public $fac_mov_area_motivo_rechazo = "";
    public $messageFacApro;
    public $fechaHoraManual3 = "";
    public $selectedItems = [];
    public $selectedGuiaAcp = [];
    public $selectAll = false;
    public $select_guias_all = false;
    public $estadoGuia = "";

    public function mount(){
        $this->desde = date('Y-01-01');
        $this->hasta =  date('Y-m-d');
        $this->buscar_comprobantes();
        $this->messageFacApro = "¿Está seguro de enviar a despacho?";
        $this->facturasCreditoAprobadas = Guia::where('guia_estado_aprobacion', 1)->get();
    }
    public function render(){
        $this->facturasCreditoAprobadas = DB::table('guias')
            ->where('guia_estado_aprobacion', 5)
            ->get();

        return view('livewire.programacioncamiones.creditoscobranzas');
    }
    public function buscar_comprobantes(){
        // Construir la consulta base
        $query = DB::table('guias')
            ->where('guia_estado_aprobacion', 1);
//            ->where('guia_estado', 'EMITIDA',);
        // Aplicar filtros de fecha si están presentes
        if ($this->desde) {
            $query->whereDate('created_at', '>=', $this->desde);
        }
        if ($this->hasta) {
            $query->whereDate('created_at', '<=', $this->hasta);
        }
        // Obtener los resultados de la consulta
        $this->filteredFacturas = $query->get();
    }


    public function updatedSelectGuiasAll(){
        if ($this->select_guias_all) {
            // Si selectAll está marcado, seleccionar todas las guías filtradas
            $this->selectedGuiaIds = $this->filteredFacturas->pluck('id_guia')->toArray();
        } else {
            // Si selectAll está desmarcado, deseleccionar todas
            $this->selectedGuiaIds = [];
        }
    }

    public function updatedSelectedGuiaIds(){
        // Verificar si todas las guías están seleccionadas
        if (!empty($this->filteredFacturas)) {
            $allGuiaIds = $this->filteredFacturas->pluck('id_guia')->toArray();
            $this->select_guias_all = count(array_diff($allGuiaIds, $this->selectedGuiaIds)) === 0;
        } else {
            $this->select_guias_all = false;
        }
    }

    public function pre_mot_cre($id_fac = null){
        $this->fechaHoraManual3 = '';

        // Si se pasa un ID específico
        if ($id_fac) {
            $id = base64_decode($id_fac);

            // No cambiar la selección del checkbox cuando se abre desde el botón de acción
            if ($id) {
                $this->id_guia = $id;
            }
        }

        $fechaHoraActual3 = Carbon::now('America/Lima')->format('d/m/Y - h:i a');
        $this->messageMotCre = "¿Está seguro de aceptar estas Guías con fecha $fechaHoraActual3?";
    }
    public function aceptar_fac_credito() {
        try {
            // Verifica permisos
            if (!Gate::allows('aceptar_fac_credito')) {
                session()->flash('error', 'No tiene permisos para cambiar los estados del menú.');
                return;
            }

            if (count($this->selectedGuiaIds) > 0) {
                // Validar que al menos un checkbox esté seleccionado
                $this->validate([
                    'selectedGuiaIds' => 'required|array|min:1',
                ], [
                    'selectedGuiaIds.required' => 'Debe seleccionar al menos una opción.',
                    'selectedGuiaIds.array'    => 'La selección debe ser válida.',
                    'selectedGuiaIds.min'      => 'Debe seleccionar al menos una opción.',
                ]);
                DB::beginTransaction();
                foreach ($this->selectedGuiaIds as $select) {
                    $facturaPreprogramada = Guia::find($select);
                    $facturaPreprogramada->guia_estado_aprobacion = 5;
                    if ($facturaPreprogramada->save()) {
                        // Registrar en historial guias
                        $historial = new Historialguia();
                        $historial->id_users = Auth::id();
                        $historial->id_guia = $select;
                        $historial->guia_nro_doc = $facturaPreprogramada->guia_nro_doc;
                        $historial->historial_guia_estado_aprobacion = 5;
                        $historial->historial_guia_fecha_hora = $this->fechaHoraManual3 ?: Carbon::now('America/Lima');
                        $historial->historial_guia_estado = 1;
                        $historial->save();

                        // Registrar el movimiento en facturas_mov
                        $facturaMov = DB::table('facturas_mov')
                            ->where('id_guia', $select)
                            ->first();

                        $data = [
                            'fac_acept_valpago' => $this->fechaHoraManual3 ? Carbon::parse($this->fechaHoraManual3, 'America/Lima') : Carbon::now('America/Lima'),
                            'id_users_responsable' => Auth::id(),
                        ];

                        if ($facturaMov) {
                            // Si existe, actualizar los campos
                            DB::table('facturas_mov')->where('id_guia', $select)->update($data);
                        } else {
                            // Si no existe, crear un nuevo registro
                            DB::table('facturas_mov')->insert(array_merge(['id_guia' => $select], $data));
                        }
                    } else {
                        DB::rollBack();
                        session()->flash('error', 'No se pudo actualizar el estado de la factura preprogramada.');
                        return;
                    }
                }
                DB::commit();
                $this->selectedGuiaIds = [];
                $this->select_guias_all = false;
                $this->dispatch('hidemodalMotCre');
                session()->flash('success', 'Guías aprobadas correctamente.');
                $this->buscar_comprobantes();
            } else {
                $this->validate([
                    'id_guia' => 'required|integer',
                ], [
                    'id_guia.required' => 'El identificador es obligatorio.',
                    'id_guia.integer' => 'El identificador debe ser un número entero.',
                ]);

                DB::beginTransaction();
                $updateDespacho = Guia::find($this->id_guia);
                $updateDespacho->guia_estado_aprobacion = 5;
                if ($updateDespacho->save()) {
                    // Registrar en historial guias
                    $historial = new Historialguia();
                    $historial->id_users = Auth::id();
                    $historial->id_guia = $this->id_guia;
                    $historial->guia_nro_doc = $updateDespacho->guia_nro_doc;
                    $historial->historial_guia_estado_aprobacion = 5;
                    $historial->historial_guia_fecha_hora = $this->fechaHoraManual3 ?: Carbon::now('America/Lima');
                    $historial->historial_guia_estado = 1;
                    $historial->save();

                    // Registrar el movimiento en facturas_mov
                    $facturaMov = DB::table('facturas_mov')
                        ->where('id_guia', $this->id_guia)
                        ->first();

                    $data = [
                        'fac_acept_valpago' => $this->fechaHoraManual3 ? Carbon::parse($this->fechaHoraManual3, 'America/Lima') : Carbon::now('America/Lima'),
                        'id_users_responsable' => Auth::id(),
                    ];

                    if ($facturaMov) {
                        // Si existe, actualizar los campos
                        DB::table('facturas_mov')->where('id_guia', $this->id_guia)->update($data);
                    } else {
                        // Si no existe, crear un nuevo registro
                        DB::table('facturas_mov')->insert(array_merge(['id_guia' => $this->id_guia], $data));
                    }
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo actualizar el estado de la factura preprogramada.');
                    return;
                }
                DB::commit();
                $this->id_guia = "";
                $this->dispatch('hidemodalMotCre');
                session()->flash('success', 'Guia aprobada correctamente.');
                $this->buscar_comprobantes();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al aprobar las facturas preprogramadas: ' . $e->getMessage());
        }
    }
    public function actualizarMensaje2(){
        // Si hay una fecha y hora manual, usarla; de lo contrario, usar la fecha y hora actual
        $fhora = $this->fechaHoraManual3
            ? Carbon::parse($this->fechaHoraManual3, 'America/Lima')->format('d/m/Y - h:i a')
            : Carbon::now('America/Lima')->format('d/m/Y - h:i a');

        // Actualizar el mensaje con la nueva fecha y hora
        $this->messageMotCre = "¿Estás seguro de enviar con fecha $fhora?";
    }

//Documentos seleccionados
    public function enviar_fac_apro($id_fac = null){
        if ($id_fac) {
            $this->selectedItems = [base64_decode($id_fac)]; // Asigna solo la factura seleccionada
        }

    }
    public function confirmarEnvio(){
        try {
            if (empty($this->selectedItems)) {
                session()->flash('error', 'Debes seleccionar al menos una guía.');
                return;
            }

            DB::beginTransaction();
            foreach ($this->selectedItems as $id_guia) {
                $factura = Guia::find($id_guia);

                if ($factura) {
                    // Actualizar el estado de la factura
                    $factura->guia_estado_aprobacion = 2;
                    $factura->save();

                    // Registrar en historial guias
                    $historial = new Historialguia();
                    $historial->id_users = Auth::id();
                    $historial->id_guia = $id_guia;
                    $historial->guia_nro_doc = $factura->guia_nro_doc;
                    $historial->historial_guia_estado_aprobacion = 6;
                    $historial->historial_guia_fecha_hora = Carbon::now('America/Lima');
                    $historial->historial_guia_estado = 1;
                    $historial->save();

                    // Registrar en facturas_mov
                    DB::table('facturas_mov')->updateOrInsert(
                        ['id_guia' => $id_guia],
                        [
                            'fac_acept_val_rec' => Carbon::now('America/Lima'),
                            'fac_env_ges_fac' => Carbon::now('America/Lima'),
                            'id_users_responsable' => Auth::id(),
                        ]
                    );
                }
            }

            DB::commit();
            session()->flash('success', 'Guías enviadas a validar recibido por despachos.');
            $this->dispatch('hidemodalFacApro');
            $this->reset(['selectedItems', 'selectAll']); // Limpiar selecciones
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al enviar las facturas. Detalles: ' . $e->getMessage());
        }
    }
    public function updatedSelectAll($value){
        if ($value) {
            $this->selectedItems = $this->facturasCreditoAprobadas->pluck('id_guia')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedItems = [];
        }
    }
    public function updatedSelectedItems(){
        $this->selectAll = count($this->selectedItems) === $this->facturasCreditoAprobadas->count();
    }

}
