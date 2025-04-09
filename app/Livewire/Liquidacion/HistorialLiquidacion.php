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

    public function mount()
    {
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
    }

    public function render()
    {
        $resultado = $this->liquidacion->listar_liquidacion_aprobadas_new($this->search, $this->desde, $this->hasta);
        return view('livewire.liquidacion.historial-liquidacion', compact('resultado'));
    }

    public function listar_informacion_liquidacion($id)
    {
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
            $resultado = $this->liquidacion->listar_liquidacion_aprobadas_excel($this->search,$this->desde, $this->hasta);

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
            $totalGeneralComprobantes = 0;
            $primeraFilaIngresadaTra = null;
            $ultimoTransportista = null;
            $sumaPorTransportista = 0;

            foreach ($locales as $index => $re) {
                // Obtener los datos de programación y despacho
                $detallesDespacho = DB::table('liquidacion_detalles as ld')
                    ->join('despachos as d', 'd.id_despacho', '=', 'ld.id_despacho')
                    ->leftJoin('programaciones as p', 'p.id_programacion', '=', 'd.id_programacion')
                    ->select('d.*', 'p.programacion_fecha')
                    ->where('ld.id_liquidacion', '=', $re->id_liquidacion)
                    ->first();

                $programacionFecha = isset($detallesDespacho->programacion_fecha) ? date('d/m/Y', strtotime($detallesDespacho->programacion_fecha)) : '-';
                $despachoFechaAprobacion = isset($detallesDespacho->despacho_fecha_aprobacion) ? date('d/m/Y', strtotime($detallesDespacho->despacho_fecha_aprobacion)) : '-';

                $totalSinIGVExcel = $re->total_sin_igv;
                $totalConIVCExcel = $totalSinIGVExcel * 1.18;

                $totalSinIGVExcelFormatted = $this->general->formatoDecimal($totalSinIGVExcel);
                $totalConIVCExcelFormatted = $this->general->formatoDecimal($totalConIVCExcel);

                if ($ultimoTransportista !== null && $ultimoTransportista !== $re->id_transportistas) {
                    $Filahasta = $row - 1;
                    $sheet1->mergeCells('H'.$primeraFilaIngresadaTra.':H'.$Filahasta);
                    $sheet1->setCellValue('H'.$primeraFilaIngresadaTra, $this->general->formatoDecimal($sumaPorTransportista));
                    $sumaPorTransportista = 0;
                    $primeraFilaIngresadaTra = $row;
                }

                $sumaPorTransportista += $totalConIVCExcel;

                $sheet1->setCellValue('A'.$row, $programacionFecha);
                $sheet1->setCellValue('B'.$row, $despachoFechaAprobacion);
                $sheet1->setCellValue('C'.$row, 'LIMA');
                $sheet1->setCellValue('D'.$row, $re->transportista_nom_comercial);
                $sheet1->setCellValue('E'.$row, $re->liquidacion_serie.'-'.$re->liquidacion_correlativo);
                $sheet1->setCellValue('F'.$row, $totalSinIGVExcelFormatted);
                $sheet1->setCellValue('G'.$row, $totalConIVCExcelFormatted);

                $cellRange = 'A'.$row.':H'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                if ($ultimoTransportista === null || $ultimoTransportista !== $re->id_transportistas) {
                    $ultimoTransportista = $re->id_transportistas;
                    $primeraFilaIngresadaTra = $row;
                }

                if ($index == count($locales) - 1) {
                    $Filahasta = $row;
                    $sheet1->mergeCells('H'.$primeraFilaIngresadaTra.':H'.$Filahasta);
                    $sheet1->setCellValue('H'.$primeraFilaIngresadaTra, $this->general->formatoDecimal($sumaPorTransportista));
                }

                $row++;
                $totalGeneralComprobantes += $re->total_sin_igv;
            }

            // Totales LOCAL
            $sheet1->setCellValue('A'.$row, 'TOTAL LOCAL');
            $sheet1->mergeCells('A'.$row.':E'.$row);
            $toSinIGV = $this->general->formatoDecimal($totalGeneralComprobantes);
            $sheet1->setCellValue('F'.$row, $toSinIGV);
            $toConIGV = $this->general->formatoDecimal($totalGeneralComprobantes * 1.18);
            $sheet1->setCellValue('G'.$row, $toConIGV);
            $sheet1->setCellValue('H'.$row, $toConIGV);

            $cellRange = 'A'.$row.':H'.$row;
            $rowStyle = $sheet1->getStyle($cellRange);
            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ececec');
            $rowStyle->getFont()->setSize(10);
            $rowStyle->getFont()->setBold(true);
            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $nextRow = $row + 2; // Dejar un espacio entre tablas

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

            $row = 6;
            $totalGeneralProvincial = 0;
            $primeraFilaIngresadaTraProv = null;
            $ultimoTransportistaProv = null;
            $sumaPorTransportistaProv = 0;

            foreach ($provinciales as $index => $re) {
                // Obtener los datos de programación, despacho, departamento y provincia
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

                if ($ultimoTransportistaProv !== null && $ultimoTransportistaProv !== $re->id_transportistas) {
                    $Filahasta = $row - 1;
                    $sheet1->mergeCells('Q'.$primeraFilaIngresadaTraProv.':Q'.$Filahasta);
                    $sheet1->setCellValue('Q'.$primeraFilaIngresadaTraProv, $this->general->formatoDecimal($sumaPorTransportistaProv));
                    $sumaPorTransportistaProv = 0;
                    $primeraFilaIngresadaTraProv = $row;
                }

                $sumaPorTransportistaProv += $totalConIVCExcel;

                $sheet1->setCellValue('J'.$row, $programacionFecha);
                $sheet1->setCellValue('K'.$row, $despachoFechaAprobacion);
                $sheet1->setCellValue('L'.$row, $re->transportista_nom_comercial);
                $sheet1->setCellValue('M'.$row, $departamentoProvincia);
                $sheet1->setCellValue('N'.$row, $re->liquidacion_serie.'-'.$re->liquidacion_correlativo);
                $sheet1->setCellValue('O'.$row, $totalSinIGVExcelFormatted);
                $sheet1->setCellValue('P'.$row, $totalConIVCExcelFormatted);

                $cellRange = 'J'.$row.':Q'.$row;
                $rowStyle = $sheet1->getStyle($cellRange);
                $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                if ($ultimoTransportistaProv === null || $ultimoTransportistaProv !== $re->id_transportistas) {
                    $ultimoTransportistaProv = $re->id_transportistas;
                    $primeraFilaIngresadaTraProv = $row;
                }

                if ($index == count($provinciales) - 1) {
                    $Filahasta = $row;
                    $sheet1->mergeCells('Q'.$primeraFilaIngresadaTraProv.':Q'.$Filahasta);
                    $sheet1->setCellValue('Q'.$primeraFilaIngresadaTraProv, $this->general->formatoDecimal($sumaPorTransportistaProv));
                }

                $row++;
                $totalGeneralProvincial += $re->total_sin_igv;
            }

            // Totales PROVINCIAL
            $sheet1->setCellValue('J'.$row, 'TOTAL PROVINCIAL');
            $sheet1->mergeCells('J'.$row.':N'.$row);
            $toSinIGV = $this->general->formatoDecimal($totalGeneralProvincial);
            $sheet1->setCellValue('O'.$row, $toSinIGV);
            $toConIGV = $this->general->formatoDecimal($totalGeneralProvincial * 1.18);
            $sheet1->setCellValue('P'.$row, $toConIGV);
            $sheet1->setCellValue('Q'.$row, $toConIGV);

            $cellRange = 'J'.$row.':Q'.$row;
            $rowStyle = $sheet1->getStyle($cellRange);
            $rowStyle->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $rowStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ececec');
            $rowStyle->getFont()->setSize(10);
            $rowStyle->getFont()->setBold(true);
            $rowStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rowStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Ajustar anchos de columnas
            for ($col = 'A'; $col <= 'Q'; $col++) {
                $sheet1->getColumnDimension($col)->setWidth(15);
            }

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
