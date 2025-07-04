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
    public $guia_nombre_cliente = "";

    public function mount($id, $numdoc, $nombre){
        $this->id_guia = $id;
        $this->guia_nro_doc = $numdoc;
        $this->guia_nombre_cliente = $nombre;
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
    public $id_guia_seleccionada = null;

    public function render(){
        $nombre = $this->guia_nombre_cliente;
        return view('livewire.gestionvendedor.vistatrackings', compact('nombre'));
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
            ->where('historial_guia_estado', '=', 1)
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
        $fechaEmision = $this->general->obtenerNombreFecha($preProg->guia_fecha_emision, 'DateTime', 'Date');
        $this->mensajeEtapa0 = $fechaEmision . '<br>' . 'Fecha de emisión de la guía.';

        // Procesar historial
        foreach ($historialGuia as $registro) {
            $fechaHora = $this->general->obtenerNombreFecha($registro->historial_guia_fecha_hora, 'DateTime', 'DateTime');
            $estado = $registro->historial_guia_estado_aprobacion;

            switch ($estado) {
                case 5: // En Créditos
                    $this->mensajeEtapa1 = $fechaHora . '<br>' . 'Guía en créditos.';
                    $this->etapaActual = max($this->etapaActual, 1);
                    break;
                case 3: // Por Programar
                    $this->mensajeEtapa2 = $fechaHora . '<br>' . 'Guía listo para despachar.';
                    $this->etapaActual = max($this->etapaActual, 2);
                    break;
                case 9: // Programado
                    $this->mensajeEtapa3 = $fechaHora . '<br>' . 'Guía despachada.';
                    $this->etapaActual = max($this->etapaActual, 3);
                    break;
                case 7: // En Ruta
                    $this->mensajeEtapa4 = $fechaHora . '<br>' . 'Guía en tránsito.';
                    $this->etapaActual = max($this->etapaActual, 4);
                    break;
                case 8: // Entregado
                    $this->mensajeEtapa5 = $fechaHora . '<br>' . 'Guía entregada.';
                    $this->etapaActual = 5;
                    break;
                case 11: // No Entregado
                    $this->mensajeEtapa5 = $fechaHora . '<br>' . 'Guía no entregada.';
                    $this->etapaActual = 5;
                    break;
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
                // Primero obtener el cliente de la guía actual
                $guiaActual = DB::table('guias')->where('id_guia', $preProg->id_guia)->first();
                $nombreClienteActual = $guiaActual->guia_nombre_cliente;

                // Obtener todas las guías con el mismo id_despacho Y del mismo cliente
                $despachosRelacionados = DB::table('despacho_ventas as dv')
                    ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                    ->where('dv.id_despacho', $despachoVentaActual->id_despacho)
                    ->where('g.id_guia', '!=', $preProg->id_guia)
                    ->where('g.guia_nombre_cliente', $nombreClienteActual) // Filtramos solo guías del mismo cliente
                    ->select('g.*')
                    ->get();

                if ($despachosRelacionados->isNotEmpty()) {
                    // Calcular peso y volumen para cada guía y agrupar por nombre de cliente
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
                    });

                    // Creamos la estructura que espera la vista
                    $this->facturasRelacionadas = [
                        [
                            'nombre_cliente' => $nombreClienteActual,
                            'guias' => $despachosRelacionados,
                            'total_peso_gramos' => $despachosRelacionados->sum('peso_total_gramos'),
                            'total_peso_kilogramos' => $despachosRelacionados->sum('peso_total_kilogramos'),
                            'total_volumen' => $despachosRelacionados->sum('volumen_total')
                        ]
                    ];
                } else {
                    $this->facturasRelacionadas = [];
                }
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
            $detalles = DB::table('guias_detalles as gd')
                ->join('guias as g', 'gd.id_guia', '=', 'g.id_guia')
                ->where('gd.id_guia', $guiaId)
                ->orderBy('gd.id_guia_det')
                ->get();

            if ($detalles->isEmpty()) {
                session()->flash('error', 'No hay detalles de facturas para esta guía.');
                return;
            }

            // Crear nuevo documento de Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar encabezados
            $sheet->setCellValue('A1', 'Fecha Emisión');
            $sheet->setCellValue('B1', 'Nro Documento');
            $sheet->setCellValue('C1', 'Código Producto');
            $sheet->setCellValue('D1', 'Descripción Producto');
            $sheet->setCellValue('E1', 'Unidad');
            $sheet->setCellValue('F1', 'Cantidad');
            $sheet->setCellValue('G1', 'Precio Unit. (Inc IGV)');
            $sheet->setCellValue('H1', 'Descuento Total (Sin IGV)');
            $sheet->setCellValue('I1', 'Importe Total (Inc IGV)');
            $sheet->setCellValue('J1', 'Moneda');
            $sheet->setCellValue('K1', 'Lote');
            $sheet->setCellValue('L1', 'Peso Total (g)');
            $sheet->setCellValue('M1', 'Volumen Total');
            $sheet->setCellValue('N1', 'ESTADO');
            $sheet->setCellValue('O1', 'VENDEDOR');

//            $sheet->setCellValue('A1', 'Almacén Salida');
//            $sheet->setCellValue('C1', 'Tipo Documento');
//            $sheet->setCellValue('E1', 'Nro Línea');
//            $sheet->setCellValue('M1', 'IGV Total');
//            $sheet->setCellValue('P1', 'Tipo Cambio');
//            $sheet->setCellValue('Q1', 'Peso Unit. (g)');
//            $sheet->setCellValue('R1', 'Volumen Unit.');


            // Estilo para encabezados
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']]
            ];
            $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);

            // Llenar datos
            $row = 2;
            foreach ($detalles as $detalle) {
                $sheet->setCellValue('A'.$row, date('d/m/Y', strtotime($detalle->guia_det_fecha_emision)));
                $sheet->setCellValue('B'.$row, $detalle->guia_det_nro_documento);
                $sheet->setCellValueExplicit(
                    'C'.$row,
                    $detalle->guia_det_cod_producto,
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                $sheet->setCellValue('D'.$row, $detalle->guia_det_descripcion_producto);
                $sheet->setCellValue('E'.$row, $detalle->guia_det_unidad);
                $sheet->setCellValue('F'.$row, $detalle->guia_det_cantidad);
                $sheet->setCellValue('G'.$row, $detalle->guia_det_precio_unit_final_inc_igv);
                $sheet->setCellValue('H'.$row, $detalle->guia_det_descuento_total_sin_igv);
                $sheet->setCellValue('I'.$row, $detalle->guia_det_importe_total_inc_igv);
                $sheet->setCellValue('J'.$row, $detalle->guia_det_moneda);
                $sheet->setCellValue('K'.$row, $detalle->guia_det_lote);
                $sheet->setCellValue('L'.$row, $detalle->guia_det_peso_total_gramo);
                $sheet->setCellValue('M'.$row, $detalle->guia_det_volumen_total);
                $sheet->setCellValue('N'.$row, $detalle->guia_det_estado);
                $sheet->setCellValue('O'.$row, $detalle->guia_vendedor);

//                $sheet->setCellValue('A'.$row, $detalle->guia_det_almacen_salida);
//                $sheet->setCellValue('C'.$row, $detalle->guia_det_tipo_documento);
//                $sheet->setCellValue('E'.$row, $detalle->guia_det_nro_linea);
//                $sheet->setCellValue('M'.$row, $detalle->guia_det_igv_total);
//                $sheet->setCellValue('P'.$row, $detalle->guia_det_tipo_cambio);
//                $sheet->setCellValue('Q'.$row, $detalle->guia_det_peso_gramo);
//                $sheet->setCellValue('R'.$row, $detalle->guia_det_volumen);

                $row++;
            }

            // Autoajustar columnas
            foreach (range('A', 'O') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Formato de números
            $numberColumns = ['F', 'G', 'H', 'I', 'L', 'M'];
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
