<?php

namespace App\Livewire\Liquidacion;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Despacho;
use App\Models\Liquidacion;
use App\Models\General;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
class HistorialLiquidacion extends Component
{
    use WithFileUploads;

    private $logs;
    private $despacho;
    private $liquidacion;
    private $general;

    public function __construct()
    {
        $this->logs = new Logs();
        $this->despacho = new Despacho();
        $this->liquidacion = new Liquidacion();
        $this->general = new General();
    }

    public $desde;
    public $hasta;
    public $search;
    public $estado_liquidacion = '';
    public $listar_detalle_liquidacion = [];
    public $guiasAsociadasDespachos = [];
    public $listar_detalle_despacho = [];
    public $id_liquidacion = '';
    public $liquidacion_ruta_comprobante = '';
    public $tipo_reporte = '';
    public $locales = [];
    public $provinciales = [];

    public function mount()
    {
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
    }

    public function render(){
        return view('livewire.liquidacion.historial-liquidacion', [
            'locales' => $this->locales ?? [],
            'provinciales' => $this->provinciales ?? []
        ]);
    }

    public function buscar_historial_liquidacion(){

        if (!Gate::allows('buscar_historial_liquidacion')) {
            session()->flash('error', 'No tiene permisos para buscar el historial de liquidaciones.');
            return;
        }


        $resultado = $this->liquidacion->listar_liquidacion_aprobadas_new($this->search, $this->desde, $this->hasta, $this->tipo_reporte);

        // Separar y ordenar resultados por transportista
        $locales = collect();
        $provinciales = collect();

        foreach ($resultado as $item) {
            $detalles = DB::table('liquidacion_detalles as ld')
                ->join('despachos as d', 'd.id_despacho', '=', 'ld.id_despacho')
                ->where('ld.id_liquidacion', '=', $item->id_liquidacion)
                ->get();

            $esLocal = false;
            $esProvincial = false;

            foreach ($detalles as $detalle) {
                if ($detalle->id_tipo_servicios == 1) {
                    $esLocal = true;
                } else if ($detalle->id_tipo_servicios == 2) {
                    $esProvincial = true;
                }
            }

            if ($esLocal) {
                $locales->push($item);
            }
            if ($esProvincial) {
                $provinciales->push($item);
            }
        }

        // Ordenar por transportista
        $this->locales = $locales->sortBy('transportista_nom_comercial')->values()->all();
        $this->provinciales = $provinciales->sortBy('transportista_nom_comercial')->values()->all();
    }

    public function listar_informacion_liquidacion($id){
        try {
            $this->listar_detalle_liquidacion = DB::table('liquidaciones as l')
                ->join('users as u', 'l.id_users', '=', 'u.id_users')
                ->join('liquidacion_detalles as ld', 'ld.id_liquidacion', '=', 'l.id_liquidacion')
                ->join('despachos as d', 'ld.id_despacho', '=', 'd.id_despacho')
                ->where('l.id_liquidacion', '=', $id)
                ->get();
            // Asignar los gastos a cada detalle de liquidación
            $totalVenta = 0;
            foreach ($this->listar_detalle_liquidacion as $detalle) {
                $detalle->gastos = DB::table('liquidacion_detalles as ld')
                    ->join('liquidacion_gastos as lg', 'ld.id_liquidacion_detalle', '=', 'lg.id_liquidacion_detalle')
                    ->where('ld.id_liquidacion', '=', $id)
                    ->where('ld.id_despacho', '=', $detalle->id_despacho)
                    ->get();

                foreach ($detalle->gastos as $com) {
                    $precio = floatval($com->despacho_venta_cfimporte);
                    $totalVenta += round($precio, 2);
                }
                $detalle->totalVentaDespacho = $totalVenta;
            }

        } catch (\Exception $e) {
            // Registrar el error en los logs
            $this->logs->insertarLog($e);
        }
    }

    public function agregar_comprobante($id_liquidqcion)
    {
        try {
            if ($id_liquidqcion) {
                $id = $id_liquidqcion;
                $this->id_liquidacion = $id;
                $this->liquidacion_ruta_comprobante = '';
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return;
        }
    }

    public function guardar_comprobante()
    {
        if (!Gate::allows('guardar_comprobante_liquidacion')) {
            session()->flash('error', 'No tiene permisos para guardar el comprobante relacionado con la liquidación.');
            return;
        }

        $this->validate([
            'liquidacion_ruta_comprobante' => 'nullable|file|mimes:jpg,jpeg,pdf,png|max:2048',
        ], [
            'liquidacion_ruta_comprobante.file' => 'Debe cargar un archivo válido.',
            'liquidacion_ruta_comprobante.mimes' => 'El archivo debe ser JPG, JPEG, PNG o PDF.',
            'liquidacion_ruta_comprobante.max' => 'El archivo no puede exceder los 2MB.',
        ]);

        try {
            DB::beginTransaction();

            $liquidacion = Liquidacion::find($this->id_liquidacion);
            if ($liquidacion) {
                if ($this->liquidacion_ruta_comprobante) {
                    $liquidacion->liquidacion_ruta_comprobante = $this->general->save_files($this->liquidacion_ruta_comprobante, 'liquidacion/comprobantes');
                }

                if ($liquidacion->save()) {
                    DB::commit();
                    $this->dispatch('hideModal');
                    session()->flash('success', 'Comprobante agregado correctamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error', 'No se pudo agregar el comprobante.');
                }
            } else {
                DB::rollBack();
                session()->flash('error', 'Liquidación no encontrada.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error al guardar el comprobante: ' . $e->getMessage());
        }
    }
//    public function listar_informacion_despacho($id){
//        try {
//            $this->listar_detalle_liquidacion = DB::table('liquidaciones')->where('id_liquidacion','=',$id)->first();
//            if ($this->listar_detalle_liquidacion){
//                $this->listar_detalle_liquidacion->detalles = DB::table('liquidacion_detalles as ld')
//                ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
//                ->join('programaciones as p','p.id_programacion','=','d.id_programacion')
//                ->join('users as u','u.id_users','=','d.id_users')
//                ->where('ld.id_liquidacion','=',$id)->get();
//
//                foreach ($this->listar_detalle_liquidacion->detalles as $de){
//                    $de->comprobantes = DB::table('despacho_ventas')->where('id_despacho','=',$de->id_despacho)->get();
//
//                    $totalVenta = 0;
//                    $totalVentaRestar = 0;
//                    $totalPesoRestar = 0;
//                    foreach ($de->comprobantes as $com) {
//                        $precio = floatval($com->despacho_venta_cfimporte);
//                        $pesoMenos = $com->despacho_venta_total_kg;
//                        $totalVenta += $precio;
//                        if ($com->despacho_detalle_estado_entrega == 3){
//                            $totalVentaRestar += $precio;
//                            $totalPesoRestar += $pesoMenos;
//                        }
//                    }
//                    $de->totalVentaDespacho = $totalVenta;
//                    $de->totalVentaNoEntregado = $totalVentaRestar;
//                    $de->totalPesoNoEntregado = $totalPesoRestar;
//                }
//            }
//        }catch (\Exception $e){
//            $this->logs->insertarLog($e);
//        }
//    }
//    public function listar_informacion_despacho($id){
//        try {
//            $this->listar_detalle_despacho = DB::table('liquidacion_detalles as ld')
//                ->join('despachos as d','d.id_despacho','=','ld.id_despacho')
//                ->join('programaciones as p','p.id_programacion','=','d.id_programacion')
//                ->join('users as u','u.id_users','=','d.id_users')
//                ->where('d.id_despacho','=',$id)->first();
//            if ($this->listar_detalle_despacho){
//                $this->listar_detalle_despacho->comprobantes = DB::table('despacho_ventas')
//                    ->where('id_despacho','=',$id)->get();
//
//                $totalVenta = 0;
//                $totalVentaRestar = 0;
//                $totalPesoRestar = 0;
//                foreach ($this->listar_detalle_despacho->comprobantes as $com) {
//                    $precio = floatval($com->despacho_venta_cfimporte);
//                    $pesoMenos = $com->despacho_venta_total_kg;
//                    $totalVenta += $precio;
//                    if ($com->despacho_detalle_estado_entrega == 3){
//                        $totalVentaRestar += $precio;
//                        $totalPesoRestar += $pesoMenos;
//                    }
//                }
//                $this->listar_detalle_despacho->totalVentaDespacho = $totalVenta;
//                $this->listar_detalle_despacho->totalVentaNoEntregado = $totalVentaRestar;
//                $this->listar_detalle_despacho->totalPesoNoEntregado = $totalPesoRestar;
//
//            }
//        }catch (\Exception $e){
//            $this->logs->insertarLog($e);
//        }
//    }
    public function listar_informacion_despacho($id, $liquidacion)
    {
        try {
            $this->listar_detalle_despacho = DB::table('liquidacion_detalles as ld')
                ->join('despachos as d', 'd.id_despacho', '=', 'ld.id_despacho')
                ->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                ->join('users as u', 'u.id_users', '=', 'd.id_users')
                ->where('d.id_despacho', '=', $id)
                ->where('ld.id_liquidacion', '=', $liquidacion)
                ->first();
            if ($this->listar_detalle_despacho) {
                $this->listar_detalle_despacho->comprobantes = DB::table('despacho_ventas')
                    ->where('id_despacho', '=', $id)->get();

                $totalVenta = 0;
                $totalVentaRestar = 0;
                $totalPesoRestar = 0;
                foreach ($this->listar_detalle_despacho->comprobantes as $com) {
                    $precio = floatval($com->despacho_venta_cfimporte);
                    $pesoMenos = $com->despacho_venta_total_kg;
                    $totalVenta += $precio;
                    if ($com->despacho_detalle_estado_entrega == 3) {
                        $totalVentaRestar += $precio;
                        $totalPesoRestar += $pesoMenos;
                    }
                }
                $this->listar_detalle_despacho->totalVentaDespacho = $totalVenta;
                $this->listar_detalle_despacho->totalVentaNoEntregado = $totalVentaRestar;
                $this->listar_detalle_despacho->totalPesoNoEntregado = $totalPesoRestar;

            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
        }
    }

    public function listar_guias_despachos($id)
    {
        try {
            // Obtener las guías básicas primero
            $guias = DB::table('despacho_ventas as dv')
                ->select('dv.*', 'p.programacion_fecha', 'g.*')
                ->join('despachos as d', 'd.id_despacho', '=', 'dv.id_despacho')
                ->join('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                ->join('guias as g', 'dv.id_guia', '=', 'g.id_guia')
                ->where('dv.id_despacho', '=', $id)
                ->get();

            // Calcular peso y volumen para cada guía
            $guiasConPeso = $guias->map(function ($guia) {
                $detalles = DB::table('guias_detalles')
                    ->where('id_guia', $guia->id_guia)
                    ->get();

                // Calcular peso total en gramos y convertirlo a kilos
                $pesoTotalGramos = $detalles->sum(function ($detalle) {
                    return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                });
                $pesoTotalKilos = $pesoTotalGramos / 1000;

                // Calcular volumen total
                $volumenTotal = $detalles->sum(function ($detalle) {
                    return $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
                });

                // Agregar los nuevos campos al objeto guía
                $guia->peso_total = $pesoTotalKilos;
                $guia->volumen_total = $volumenTotal;

                return $guia;
            });

            $this->guiasAsociadasDespachos = $guiasConPeso;

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
        }
    }

    public function generar_excel_historial_liquidacion(){
        try {
            if (!Gate::allows('generar_excel_historial_liquidacion')) {
                session()->flash('error', 'No tiene permisos para generar el reporte en excel.');
                return;
            }
            $resultado = $this->liquidacion->listar_liquidacion_aprobadas_excel(
                $this->search,
                $this->desde,
                $this->hasta,
                $this->tipo_reporte
            );

            // Ordenar por nombre comercial de transportista
            $resultado = collect($resultado)->sortBy('transportista_nom_comercial')->values()->all();

            $spreadsheet = new Spreadsheet();
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('historial_liquidacion');

            $mensaje = "RESULTADO DE BÚSQUEDA: ";
            if ($this->search) {
                $mensaje .= "Criterio: \"" . $this->search . "\"";
            }
            if (isset($this->desde, $this->hasta)) {
                $mensaje .= " | Rango de fechas: " . date("d-m-Y", strtotime($this->desde)) . " al " . date("d-m-Y", strtotime($this->hasta));
            }

            // Configurar título
            $sheet1->setCellValue('A1', 'HISTORIAL DE APROBACIÓN DE LIQUIDACIONES');
            $titleStyle = $sheet1->getStyle('A1');
            $titleStyle->getFont()->setSize(16);
            $titleStyle->getFont()->setBold(true);
            $sheet1->mergeCells('A1:L1');
            $sheet1->setCellValue('A2', $mensaje);
            $titleStyle = $sheet1->getStyle('A2');
            $titleStyle->getFont()->setSize(12);
            $titleStyle->getFont()->setBold(true);
            $sheet1->mergeCells('A2:L2');
            $sheet1->setCellValue('A3', "");
            $sheet1->mergeCells('A3:L3');

            // Separar datos en locales y provinciales
            $locales = [];
            $provinciales = [];
            foreach ($resultado as $item) {
                $detalles = DB::table('liquidacion_detalles as ld')
                    ->join('despachos as d', 'd.id_despacho', '=', 'ld.id_despacho')
                    ->where('ld.id_liquidacion', '=', $item->id_liquidacion)
                    ->get();

                foreach ($detalles as $detalle) {
                    if ($detalle->id_tipo_servicios == 1) {
                        $locales[] = $item;
                        break;
                    } else if ($detalle->id_tipo_servicios == 2) {
                        $provinciales[] = $item;
                        break;
                    }
                }
            }

            // Generar tabla LOCAL (columna A-H)
            $sheet1->setCellValue('A4', 'LOCAL');
            $titleStyle = $sheet1->getStyle('A4:H4');
            $titleStyle->getFont()->setSize(14);
            $titleStyle->getFont()->setBold(true);
            $sheet1->mergeCells('A4:H4');
            $titleStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $titleStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $titleStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('E2EFDA');
            $titleStyle->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $titleStyle->getBorders()->getInside()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Encabezados LOCAL
            $sheet1->setCellValue('A5', 'FEC. DESPACHO');
            $sheet1->setCellValue('B5', 'FEC. APROB');
            $sheet1->setCellValue('C5', 'LOCAL');
            $sheet1->setCellValue('D5', 'PROVEEDOR');
            $sheet1->setCellValue('E5', 'FACT');
            $sheet1->setCellValue('F5', 'SIN IGV');
            $sheet1->setCellValue('G5', 'CON IGV');
            $sheet1->setCellValue('H5', 'TOTAL PROVEEDOR');

            $cellRange = 'A5:H5';
            $rowStyle = $sheet1->getStyle($cellRange);
            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowStyle->getFont()->setSize(10);
            $rowStyle->getFont()->setBold(true);
            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $row = 6;
            $transportistaGroups = [];
            $totalGeneralSinIGV = 0;
            $totalGeneralConIGV = 0;

            // Agrupar locales por nombre comercial de transportista
            foreach ($locales as $re) {
                $transportistaNombre = $re->transportista_nom_comercial ?? 'SIN TRANSPORTISTA';

                if (!isset($transportistaGroups[$transportistaNombre])) {
                    $transportistaGroups[$transportistaNombre] = [
                        'registros' => [],
                        'total_con_igv' => 0,
                        'total_sin_igv' => 0
                    ];
                }

                $conIGV = $re->total_sin_igv * 1.18;

                $transportistaGroups[$transportistaNombre]['registros'][] = [
                    'data' => $re,
                    'con_igv' => $conIGV,
                    'sin_igv' => $re->total_sin_igv
                ];

                $transportistaGroups[$transportistaNombre]['total_con_igv'] += $conIGV;
                $transportistaGroups[$transportistaNombre]['total_sin_igv'] += $re->total_sin_igv;

                // Sumar a totales generales
                $totalGeneralSinIGV += $re->total_sin_igv;
                $totalGeneralConIGV += $conIGV;
            }

            // Procesar cada grupo de transportista para locales
            foreach ($transportistaGroups as $transportistaNombre => $group) {
                $isFirstRow = true;

                foreach ($group['registros'] as $registro) {
                    $re = $registro['data'];
                    $conIGV = $registro['con_igv'];

                    $detallesDespacho = DB::table('liquidacion_detalles as ld')
                        ->join('despachos as d', 'd.id_despacho', '=', 'ld.id_despacho')
                        ->leftJoin('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                        ->select('d.*', 'p.programacion_fecha')
                        ->where('ld.id_liquidacion', '=', $re->id_liquidacion)
                        ->first();

                    $programacionFecha = isset($detallesDespacho->programacion_fecha) ? date('d/m/Y', strtotime($detallesDespacho->programacion_fecha)) : '-';
                    $despachoFechaAprobacion = isset($detallesDespacho->despacho_fecha_aprobacion) ? date('d/m/Y', strtotime($detallesDespacho->despacho_fecha_aprobacion)) : '-';

                    $totalSinIGVExcel = $re->total_sin_igv;

                    $totalSinIGVExcelFormatted = $this->general->formatoDecimal($totalSinIGVExcel);
                    $totalConIVCExcelFormatted = $this->general->formatoDecimal($conIGV);

                    $sheet1->setCellValue('A'.$row, $programacionFecha);
                    $sheet1->setCellValue('B'.$row, $despachoFechaAprobacion);
                    $sheet1->setCellValue('C'.$row, 'LIMA');
                    $sheet1->setCellValue('D'.$row, $transportistaNombre);
                    $sheet1->setCellValue('E'.$row, $re->liquidacion_serie.'-'.$re->liquidacion_correlativo);
                    $sheet1->setCellValue('F'.$row, 'S/ '. $totalSinIGVExcelFormatted);
                    $sheet1->setCellValue('G'.$row, 'S/ '. $totalConIVCExcelFormatted);

                    // Mostrar el total CON IGV solo en la primera fila del transportista
                    if ($isFirstRow) {
                        $sheet1->setCellValue('H'.$row, 'S/ '. $this->general->formatoDecimal($group['total_con_igv']));
                        $isFirstRow = false;
                    } else {
                        $sheet1->setCellValue('H'.$row, '');
                    }

                    $cellRange = 'A'.$row.':H'.$row;
                    $rowStyle = $sheet1->getStyle($cellRange);
                    $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    $row++;
                }
                // No agregamos espacio entre transportistas
            }

            // Agregar total general LOCAL
            $sheet1->setCellValue('A'.$row, 'TOTAL GENERAL LOCAL');
            $sheet1->mergeCells('A'.$row.':E'.$row);
            $sheet1->setCellValue('F'.$row, $this->general->formatoDecimal($totalGeneralSinIGV));
            $sheet1->setCellValue('G'.$row, $this->general->formatoDecimal($totalGeneralConIGV));
            $sheet1->setCellValue('H'.$row, $this->general->formatoDecimal($totalGeneralConIGV));

            $cellRange = 'A'.$row.':H'.$row;
            $rowStyle = $sheet1->getStyle($cellRange);
            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ececec');
            $rowStyle->getFont()->setSize(10);
            $rowStyle->getFont()->setBold(true);
            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $row += 2; // Espacio antes de la tabla provincial

            // Generar tabla PROVINCIAL (columna J-Q)
            $sheet1->setCellValue('J4', 'PROVINCIAL');
            $titleStyle = $sheet1->getStyle('J4:Q4');
            $titleStyle->getFont()->setSize(14);
            $titleStyle->getFont()->setBold(true);
            $sheet1->mergeCells('J4:Q4');
            $titleStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $titleStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $titleStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('E2EFDA');
            $titleStyle->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $titleStyle->getBorders()->getInside()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // Encabezados PROVINCIAL
            $sheet1->setCellValue('J5', 'FEC. DESPACHO');
            $sheet1->setCellValue('K5', 'FEC. APROB');
            $sheet1->setCellValue('L5', 'TRANSPORTE');
            $sheet1->setCellValue('M5', 'DEPARTAMENTO - PROVINCIA');
            $sheet1->setCellValue('N5', 'FACT');
            $sheet1->setCellValue('O5', 'SIN IGV');
            $sheet1->setCellValue('P5', 'CON IGV');
            $sheet1->setCellValue('Q5', 'TOTAL PROVEEDOR');

            $cellRange = 'J5:Q5';
            $rowStyle = $sheet1->getStyle($cellRange);
            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowStyle->getFont()->setSize(10);
            $rowStyle->getFont()->setBold(true);
            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $provincialRow = 6;
            $transportistaGroupsProv = [];
            $totalGeneralProvSinIGV = 0;
            $totalGeneralProvConIGV = 0;

            // Agrupar provinciales por nombre comercial de transportista
            foreach ($provinciales as $re) {
                $transportistaNombre = $re->transportista_nom_comercial ?? 'SIN TRANSPORTISTA';

                if (!isset($transportistaGroupsProv[$transportistaNombre])) {
                    $transportistaGroupsProv[$transportistaNombre] = [
                        'registros' => [],
                        'total_con_igv' => 0,
                        'total_sin_igv' => 0
                    ];
                }
                $totalConIGV = $re->total_sin_igv * 1.18;
                $transportistaGroupsProv[$transportistaNombre]['registros'][] = [
                    'data' => $re,
                    'con_igv' => $totalConIGV,
                    'sin_igv' => $re->total_sin_igv
                ];
                $transportistaGroupsProv[$transportistaNombre]['total_con_igv'] += $totalConIGV;
                $transportistaGroupsProv[$transportistaNombre]['total_sin_igv'] += $re->total_sin_igv;

                // Sumar a totales generales provinciales
                $totalGeneralProvSinIGV += $re->total_sin_igv;
                $totalGeneralProvConIGV += $totalConIGV;
            }

            // Procesar cada grupo de transportista para provinciales
            foreach ($transportistaGroupsProv as $transportistaNombre => $group) {
                $isFirstRow = true;

                foreach ($group['registros'] as $registro) {
                    $re = $registro['data'];
                    $conIGV = $registro['con_igv'];

                    $detallesDespacho = DB::table('liquidacion_detalles as ld')
                        ->join('despachos as d', 'd.id_despacho', '=', 'ld.id_despacho')
                        ->leftJoin('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                        ->leftJoin('departamentos as dep', 'dep.id_departamento', '=', 'd.id_departamento')
                        ->leftJoin('provincias as prov', 'prov.id_provincia', '=', 'd.id_provincia')
                        ->select('d.*', 'p.programacion_fecha', 'dep.departamento_nombre', 'prov.provincia_nombre')
                        ->where('ld.id_liquidacion', '=', $re->id_liquidacion)
                        ->first();

                    $programacionFecha = isset($detallesDespacho->programacion_fecha) ? date('d/m/Y', strtotime($detallesDespacho->programacion_fecha)) : '-';
                    $despachoFechaAprobacion = isset($detallesDespacho->despacho_fecha_aprobacion) ? date('d/m/Y', strtotime($detallesDespacho->despacho_fecha_aprobacion)) : '-';

                    $departamentoProvincia = '-';
                    if (isset($detallesDespacho->departamento_nombre) && isset($detallesDespacho->provincia_nombre)) {
                        $departamentoProvincia = $detallesDespacho->departamento_nombre . ' - ' . $detallesDespacho->provincia_nombre;
                    }

                    $totalSinIGVExcel = $re->total_sin_igv;
                    $totalConIVCExcel = $totalSinIGVExcel * 1.18;

                    $totalSinIGVExcelFormatted = $this->general->formatoDecimal($totalSinIGVExcel);
                    $totalConIVCExcelFormatted = $this->general->formatoDecimal($totalConIVCExcel);

                    $sheet1->setCellValue('J'.$provincialRow, $programacionFecha);
                    $sheet1->setCellValue('K'.$provincialRow, $despachoFechaAprobacion);
                    $sheet1->setCellValue('L'.$provincialRow, $transportistaNombre);
                    $sheet1->setCellValue('M'.$provincialRow, $departamentoProvincia);
                    $sheet1->setCellValue('N'.$provincialRow, $re->liquidacion_serie.'-'.$re->liquidacion_correlativo);
                    $sheet1->setCellValue('O'.$provincialRow, 'S/ ' . $totalSinIGVExcelFormatted);
                    $sheet1->setCellValue('P'.$provincialRow, 'S/ ' . $totalConIVCExcelFormatted);

                    // Mostrar el total CON IGV solo en la primera fila del transportista
                    if ($isFirstRow) {
                        $sheet1->setCellValue('Q'.$provincialRow, 'S/ ' . $this->general->formatoDecimal($group['total_con_igv']));
                        $isFirstRow = false;
                    } else {
                        $sheet1->setCellValue('Q'.$provincialRow, '');
                    }

                    $cellRange = 'J'.$provincialRow.':Q'.$provincialRow;
                    $rowStyle = $sheet1->getStyle($cellRange);
                    $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    $provincialRow++;
                }
                // No agregamos espacio entre transportistas
            }

            // Agregar total general PROVINCIAL
            $sheet1->setCellValue('J'.$provincialRow, 'TOTAL GENERAL PROVINCIAL');
            $sheet1->mergeCells('J'.$provincialRow.':N'.$provincialRow);
            $sheet1->setCellValue('O'.$provincialRow, $this->general->formatoDecimal($totalGeneralProvSinIGV));
            $sheet1->setCellValue('P'.$provincialRow, $this->general->formatoDecimal($totalGeneralProvConIGV));
            $sheet1->setCellValue('Q'.$provincialRow, $this->general->formatoDecimal($totalGeneralProvConIGV));

            $cellRange = 'J'.$provincialRow.':Q'.$provincialRow;
            $rowStyle = $sheet1->getStyle($cellRange);
            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ececec');
            $rowStyle->getFont()->setSize(10);
            $rowStyle->getFont()->setBold(true);
            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Ajustar anchos de columnas
            $sheet1->getColumnDimension('A')->setWidth(15);
            $sheet1->getColumnDimension('B')->setWidth(15);
            $sheet1->getColumnDimension('C')->setWidth(15);
            $sheet1->getColumnDimension('D')->setWidth(40);
            $sheet1->getColumnDimension('E')->setWidth(15);
            $sheet1->getColumnDimension('F')->setWidth(15);
            $sheet1->getColumnDimension('G')->setWidth(15);
            $sheet1->getColumnDimension('H')->setWidth(15);

            $sheet1->getColumnDimension('J')->setWidth(15);
            $sheet1->getColumnDimension('K')->setWidth(15);
            $sheet1->getColumnDimension('L')->setWidth(40);
            $sheet1->getColumnDimension('M')->setWidth(30);
            $sheet1->getColumnDimension('N')->setWidth(15);
            $sheet1->getColumnDimension('O')->setWidth(15);
            $sheet1->getColumnDimension('P')->setWidth(15);
            $sheet1->getColumnDimension('Q')->setWidth(15);

            $nombre_excel = "historial_de_liquidación" . date('d-m-Y') . '.xlsx';
            $response = response()->stream(
                function () use ($spreadsheet) {
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename=' . $nombre_excel,
                ]
            );
            return $response;
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
        }
    }
}
