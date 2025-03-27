<?php

namespace App\Livewire\Gestionvendedor;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Despacho;
use App\Models\DespachoVenta;
use App\Models\Facturaspreprogramacion;
use App\Models\General;
use App\Models\Historialdespachoventa;
use App\Models\Historialpreprogramacion;
use App\Models\Guia;
use App\Models\Historialguia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Vistatrackings extends Component
{
    private $logs;
    private $despachoventa;
    private $facpreprogramacion;
    private $general;
    private $despacho;
    private $historialpreprogramacion;
    private $historialdespachoventa;
    private $guia;
    private $historialguia;
    public function __construct(){
        $this->logs = new Logs();
        $this->despachoventa = new DespachoVenta();
        $this->facpreprogramacion = new Facturaspreprogramacion();
        $this->general = new General();
        $this->despacho = new Despacho();
        $this->historialpreprogramacion = new Historialpreprogramacion();
        $this->historialdespachoventa = new Historialdespachoventa();
        $this->guia = new Guia();
        $this->historialguia = new Historialguia();
    }
    public $id_guia = "";
    public $guia_nro_doc = "";

    public function mount($id, $numdoc){
        $this->id_guia = $id;
        $this->guia_nro_doc = $numdoc;
        $this->buscar();
    }
    public $search_compro;
    public $mensaje;
    public $estadoMensaje = [];
    public $mensajeEtapa1;
    public $mensajeEtapa2;
    public $mensajeEtapa3;
    public $mensajeEtapa0 = '';
    public $mensajeEtapa4 = '';
    public $mensajeEtapa5 = '';
    public $mensajeEstadoEtapa1;
    public $mensajeEstadoEtapa2 = [];
    public $mensajeEstadoEtapa3 = [];
    public $mensajesCompletos = [];
    public $mensajeEstadoFacturaEtapa2;
    public $etapaActual;
    public $codigoEncontrado = false;
    public $botonDeshabilitado = false;
    public $botonSiguienteVisible = true;
    public $botonAnteriorVisible;
    public $etapaMostrada;
    public $facturas = [];
    public $facturasRelacionadas = [];
    public $guiainfo = [];
    public $guia_detalle = [];

    public function render(){
        return view('livewire.gestionvendedor.vistatrackings');
    }

    public function modal_guia_info($id_guia) {
        $this->guiainfo = $this->guia->listar_guia_x_id($id_guia);
    }

    public function listar_detalle_guia($id_guia) {
        $this->guia_detalle = $this->guia->listar_guia_detalle_x_id($id_guia);
    }

    public function actualizarVisibilidadBotones() {
        // Control del botón siguiente
        $this->botonSiguienteVisible = false;

        if ($this->etapaMostrada == 1) {
            // Verifica si existe información en la etapa 2
            if (!empty($this->mensajeEtapa2) || !empty($this->mensajeEstadoEtapa2)) {
                $this->botonSiguienteVisible = true;
            }
        } elseif ($this->etapaMostrada == 2) {
            // Verifica si existe información en la etapa 3
            if (!empty($this->mensajeEtapa3) || !empty($this->mensajeEstadoEtapa3)) {
                $this->botonSiguienteVisible = true;
            }
        }

        // Control del botón anterior
        $this->botonAnteriorVisible = $this->etapaMostrada > 1;
    }

    public function buscar() {
        $numdoc = $this->guia_nro_doc;

        // Buscar la guía principal (código existente)
        $preProg = Guia::where('guia_nro_doc', $numdoc)->first();

        if ($preProg) {
            // Obtener los detalles de la guía
            $detallesGuia = DB::table('guias_detalles')
                ->where('id_guia', $preProg->id_guia)
                ->get();

            // Calcular el peso y volumen total
            $pesoTotalGramos = 0; // Peso total en gramos
            $volumenTotal = 0;

            foreach ($detallesGuia as $detalle) {
                $pesoTotalGramos += $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                $volumenTotal += $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
            }

            // Convertir el peso total a kilogramos
            $pesoTotalKilogramos = $pesoTotalGramos / 1000;

            // Agregar los totales a la guía
            $preProg->peso_total_gramos = $pesoTotalGramos; // Peso en gramos (opcional)
            $preProg->peso_total_kilogramos = $pesoTotalKilogramos; // Peso en kilogramos
            $preProg->volumen_total = $volumenTotal;

            // Convertir en array dentro de otro array
            $this->facturas = [$preProg->toArray()];
        } else {
            $this->facturas = [];
            session()->flash('error', 'No hay registros para el comprobante ingresado.');
        }

        // Buscar en la tabla historialguias
        $historialGuia = Historialguia::where('guia_nro_doc', $numdoc)
            ->orderBy('historial_guia_fecha_hora', 'asc')
            ->get();

        if ($historialGuia->isEmpty()) {
            session()->flash('error', 'No hay registros para el comprobante ingresado.');
            return;
        }

        // Inicializar variables
        $this->codigoEncontrado = true;
        $this->etapaActual = 0;

        // Resetear mensajes
        $this->resetMensajes();

        // Mensaje de etapa 0 (Fecha de Emisión)
        $fechaEmision = $this->general->obtenerNombreFecha($preProg->guia_fecha_emision, 'DateTime', 'DateTime');
        $this->mensajeEtapa0 = $fechaEmision . ' | Fecha de emisión de la guía.';

        // Procesar historial
        foreach ($historialGuia as $registro) {
            $fechaHora = $this->general->obtenerNombreFecha($registro->historial_guia_fecha_hora, 'DateTime', 'DateTime');
            $estado = $registro->historial_guia_estado_aprobacion;

            if ($estado == 1) {
                $this->mensajeEtapa1 = $fechaHora . ' | Guía en créditos.';
                $this->etapaActual = 1;
            }
            elseif ($estado == 3) {
                $this->mensajeEtapa2 = $fechaHora . ' | Guía listo para despachar.';
                $this->etapaActual = 2;
            }
            elseif ($estado == 4) {
                $this->mensajeEtapa3 = $fechaHora . ' | Guía despachado.';
                $this->etapaActual = 3;
            }
            elseif ($estado == 7) {
                $this->mensajeEtapa4 = $fechaHora . ' | Guía en tránsito.';
                $this->etapaActual = 4;
            }
            elseif ($estado == 8) {
                $this->mensajeEtapa5 = $fechaHora . ' | Guía entregado.';
                $this->etapaActual = 5;
            }
            elseif ($estado == 11) {
                $this->mensajeEtapa5 = $fechaHora . ' | Guía no entregado.';
                $this->etapaActual = 5;
            }

            if ($estado == 10) {
                break;
            }
        }
        // Obtener las guías relacionadas (mismo id_despacho) con cálculo de peso y volumen
        if ($preProg) {
            // Obtener el id_despacho de la guía actual
            $despachoVentaActual = DespachoVenta::where('id_guia', $preProg->id_guia)->first();

            if ($despachoVentaActual) {
                // Obtener todas las guías con el mismo id_despacho
                $despachosRelacionados = DB::table('despacho_ventas as dv')
                    ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                    ->where('dv.id_despacho', $despachoVentaActual->id_despacho)
                    ->where('g.id_guia', '!=', $preProg->id_guia)
                    ->select('g.*') // Seleccionar todos los campos de guías
                    ->get();

                // Calcular peso y volumen para cada guía relacionada
                $this->facturasRelacionadas = $despachosRelacionados->map(function($guia) {
                    // Obtener detalles de la guía
                    $detallesGuia = DB::table('guias_detalles')
                        ->where('id_guia', $guia->id_guia)
                        ->get();

                    // Calcular el peso y volumen total
                    $pesoTotalGramos = 0;
                    $volumenTotal = 0;

                    foreach ($detallesGuia as $detalle) {
                        $pesoTotalGramos += $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                        $volumenTotal += $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
                    }

                    // Convertir el peso total a kilogramos
                    $pesoTotalKilogramos = $pesoTotalGramos / 1000;

                    // Agregar los totales calculados al objeto guía
                    $guia->peso_total_gramos = $pesoTotalGramos;
                    $guia->peso_total_kilogramos = $pesoTotalKilogramos;
                    $guia->volumen_total = $volumenTotal;

                    return $guia;
                })->toArray();
            } else {
                $this->facturasRelacionadas = [];
            }
        } else {
            $this->facturasRelacionadas = [];
        }
        session()->flash('success', 'Comprobante identificado en el sistema.');
    }

    protected function resetMensajes() {
        $this->mensajeEtapa0 = '';
        $this->mensajeEtapa1 = '';
        $this->mensajeEtapa2 = '';
        $this->mensajeEtapa3 = '';
        $this->mensajeEtapa4 = '';
        $this->mensajeEtapa5 = '';
    }

    // Agrega esta propiedad al componente
    public $id_guia_seleccionada = null;

// Método para establecer la guía seleccionada
    public function seleccionarGuia($id_guia) {
        $this->id_guia_seleccionada = $id_guia;
    }

    public function generar_excel_guia_factura($id_guia = null) {
        try {
            if (!Gate::allows('generar_excel_guia_factura')) {
                session()->flash('error', 'No tiene permisos para generar el reporte en excel.');
                return;
            }

            // Determinar qué ID de guía usar
            $guiaId = $id_guia ?? $this->id_guia_seleccionada ?? $this->id_guia;

            // Obtener información de la guía para el nombre del archivo
            $guia = Guia::find($guiaId);
            if (!$guia) {
                session()->flash('error', 'No se encontró la guía especificada.');
                return;
            }

            // Obtener los detalles de la guía (usando $guiaId en lugar de $this->id_guia)
            $detalles = DB::table('guias_detalles')
                ->where('id_guia', $guiaId)
                ->orderBy('id_guia_det')
                ->get();

            if ($detalles->isEmpty()) {
                session()->flash('error', 'No hay detalles de facturas para esta guía.');
                return;
            }

            // Crear nuevo documento de Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar encabezados
            $sheet->setCellValue('A1', 'Almacén Salida');
            $sheet->setCellValue('B1', 'Fecha Emisión');
            $sheet->setCellValue('C1', 'Tipo Documento');
            $sheet->setCellValue('D1', 'Nro Documento');
            $sheet->setCellValue('E1', 'Nro Línea');
            $sheet->setCellValue('F1', 'Código Producto');
            $sheet->setCellValue('G1', 'Descripción Producto');
            $sheet->setCellValue('H1', 'Lote');
            $sheet->setCellValue('I1', 'Unidad');
            $sheet->setCellValue('J1', 'Cantidad');
            $sheet->setCellValue('K1', 'Precio Unit. (Inc IGV)');
            $sheet->setCellValue('L1', 'Descuento Total (Sin IGV)');
            $sheet->setCellValue('M1', 'IGV Total');
            $sheet->setCellValue('N1', 'Importe Total (Inc IGV)');
            $sheet->setCellValue('O1', 'Moneda');
            $sheet->setCellValue('P1', 'Tipo Cambio');
            $sheet->setCellValue('Q1', 'Peso Unit. (g)');
            $sheet->setCellValue('R1', 'Volumen Unit.');
            $sheet->setCellValue('S1', 'Peso Total (g)');
            $sheet->setCellValue('T1', 'Volumen Total');

            // Estilo para encabezados
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']]
            ];
            $sheet->getStyle('A1:T1')->applyFromArray($headerStyle);

            // Llenar datos
            $row = 2;
            foreach ($detalles as $detalle) {
                $sheet->setCellValue('A'.$row, $detalle->guia_det_almacen_salida);
                $sheet->setCellValue('B'.$row, $detalle->guia_det_fecha_emision);
                $sheet->setCellValue('C'.$row, $detalle->guia_det_tipo_documento);
                $sheet->setCellValue('D'.$row, $detalle->guia_det_nro_documento);
                $sheet->setCellValue('E'.$row, $detalle->guia_det_nro_linea);
                $sheet->setCellValue('F'.$row, $detalle->guia_det_cod_producto);
                $sheet->setCellValue('G'.$row, $detalle->guia_det_descripcion_producto);
                $sheet->setCellValue('H'.$row, $detalle->guia_det_lote);
                $sheet->setCellValue('I'.$row, $detalle->guia_det_unidad);
                $sheet->setCellValue('J'.$row, $detalle->guia_det_cantidad);
                $sheet->setCellValue('K'.$row, $detalle->guia_det_precio_unit_final_inc_igv);
                $sheet->setCellValue('L'.$row, $detalle->guia_det_descuento_total_sin_igv);
                $sheet->setCellValue('M'.$row, $detalle->guia_det_igv_total);
                $sheet->setCellValue('N'.$row, $detalle->guia_det_importe_total_inc_igv);
                $sheet->setCellValue('O'.$row, $detalle->guia_det_moneda);
                $sheet->setCellValue('P'.$row, $detalle->guia_det_tipo_cambio);
                $sheet->setCellValue('Q'.$row, $detalle->guia_det_peso_gramo);
                $sheet->setCellValue('R'.$row, $detalle->guia_det_volumen);
                $sheet->setCellValue('S'.$row, $detalle->guia_det_peso_total_gramo);
                $sheet->setCellValue('T'.$row, $detalle->guia_det_volumen_total);

                $row++;
            }

            // Autoajustar columnas
            foreach (range('A', 'T') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Formato de números
            $numberColumns = ['J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T'];
            foreach ($numberColumns as $col) {
                $sheet->getStyle($col.'2:'.$col.$row)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }

            // Crear respuesta para descarga
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'detalle_facturas_guia_' . $guia->guia_nro_doc . '.xlsx';

            ob_start();
            $writer->save('php://output');
            $content = ob_get_clean();

            return response()->streamDownload(
                function () use ($content) {
                    echo $content;
                },
                $fileName,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            );

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al generar el Excel. Por favor, inténtelo nuevamente.');
        }
    }
}
