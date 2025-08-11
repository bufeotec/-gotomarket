<?php

namespace App\Http\Controllers;

use App\Helpers\PdfReporte;
use Illuminate\Http\Request;
use App\Livewire\Gestiontransporte\Vehiculos;
use App\Models\Logs;
use App\Models\Transportista;
use App\Models\Programacion;
use App\Models\Despacho;
use App\Models\DespachoVenta;
use App\Models\General;
use Illuminate\Support\Facades\DB;
use Codedge\Fpdf\Fpdf\Fpdf;

class DespachotransporteController extends Controller
{
    private $logs;
    private $transportista;
    private $programacion;
    private $despacho;
    private $despachoventa;
    private $general;

    public function __construct(){
        $this->logs = new Logs();
        $this->transportista = new Transportista();
        $this->programacion = new Programacion();
        $this->despacho = new Despacho();
        $this->despachoventa = new DespachoVenta();
        $this->general = new General();
    }

    public function registrar_transportista(){
        try {
            return view('gestiontransporte.transportistas');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function registrar_vehiculos(){
        try {
//            $id_transportistas = base64_decode($_GET['data']);
//            if ($id_transportistas){
//                $informacion_vehiculo = $this->transportista->listar_transportista_por_id($id_transportistas);
//
//                return view('gestiontransporte.vehiculos',compact('informacion_vehiculo'));
//            }
            return view('gestiontransporte.vehiculos');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function registrar_tarifas(){
        try {
            return view('registroflete.fletes');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function validar_tarifas(){
        try {
            return view('registroflete.validar_tarifa');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function validar_vehiculo(){
        try {
            return view('registroflete.tarifa_movil');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function guias_antiguas(){
        try {
            return view('registroflete.pase_guias_antiguas');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function programar_despachos(){
        try {
            return view('programacion_camiones.programar_camion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function registrar_servicio_transporte(){
        try {
            return view('despachotransporte.registrar_servicio_transporte');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function aprobar_programacion_despacho(){
        try {
            return view('programacion_camiones.programacion_pendientes');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function editar_programaciones(){
        try {
            $id_programacion = base64_decode($_GET['data']);
            if ($id_programacion){
                $programacion = $this->programacion->listar_informacion_x_id($id_programacion);
                $conteo = 0;
                $despacho = $this->despacho->listar_despachos_por_programacion($id_programacion);
                foreach ($despacho as $de){
                    $de->comprobantes = $this->despachoventa->listar_detalle_x_despacho($de->id_despacho);
                    $conteo+= $de->id_tipo_servicios;
                }
                // determinar que tipo de programacion es.

                return view('programacion_camiones.editar_programacion',compact('programacion','despacho','conteo','id_programacion'));
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function reporte_programacion_despacho(){
        try {
            return view('programacion_camiones.historial_programacion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function liquidar_fletes(){
        try {
            return view('liquidacion.liquidacion_flete');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function aprobar_fletes(){
        try {
            return view('liquidacion.liquidaciones_pendientes');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }
    public function editar_liquidaciones(){
        try {
            $id_liquidacion = base64_decode($_GET['data']);
            if ($id_liquidacion){

                return view('liquidacion.editar_liquidacion',compact('id_liquidacion'));
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function reporte_flete_aprobados(){
        try {
            return view('liquidacion.historial_liquidacion');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function reporte_despacho_transporte(){
        try {
            return view('despachotransporte.reporte_control_documentario');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function gestionar_orden_servicio(){
        try {
            return view('despachotransporte.gestionar_orden_servicio');
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function gestionar_os_detalle(){
        try {
            $id_despacho = base64_decode($_GET['id_despacho']);
            if ($id_despacho){
                $informacion_despacho = $this->despacho->listar_despacho_x_id($id_despacho);
                return view('despachotransporte.gestionar_os_detalle', compact('informacion_despacho'));
            }
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al intentar mostrar el contenido.');
        }
    }

    public function generar_pdf_os(Request $request) {
        $id_despacho = $request->input('id_despacho');
        $general = new General();
        $despachoModel = new Despacho();

        // Obtener los datos del despacho
        $listar_info = $despachoModel->listar_info_por_id($id_despacho);

        // Calcular datos adicionales como en el componente
        $totalVenta = 0;
        $guiasProcesadas = [];

        $guias = DB::table('despacho_ventas as dv')
            ->join('guias as g', 'g.id_guia', '=', 'dv.id_guia')
            ->where('dv.id_despacho', '=', $id_despacho)
            ->select('dv.*', 'g.*')
            ->get();

        foreach ($guias as $guia) {
            if (!in_array($guia->id_guia, $guiasProcesadas)) {
                $detalles = DB::table('guias_detalles')
                    ->where('id_guia', $guia->id_guia)
                    ->get();

                $pesoTotalGramos = $detalles->sum(function ($detalle) {
                    return $detalle->guia_det_peso_gramo * $detalle->guia_det_cantidad;
                });

                $guia->pesoTotalKilos = $pesoTotalGramos / 1000;
                $guia->volumenTotal = $detalles->sum(function ($detalle) {
                    return $detalle->guia_det_volumen * $detalle->guia_det_cantidad;
                });

                $totalVenta += round(floatval($guia->guia_importe_total_sin_igv), 2);
                $guiasProcesadas[] = $guia->id_guia;
            }
        }

        $guiasUnicas = $guias->whereIn('id_guia', $guiasProcesadas);
        $listar_info->guias = $guiasUnicas;
        $listar_info->totalVentaDespacho = $totalVenta;

        // Crear el PDF
        $pdf = new Fpdf();

        // Configuración inicial del PDF
        $pdf->SetCreator('Go To Market');
        $pdf->SetAuthor('Go To Market');
        $pdf->SetTitle('Orden de Servicio N° ' . ($listar_info->despacho_numero_correlativo ?? '-'));
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->AddPage();

        // Logo y cabecera
        $logoPath = public_path('isologoCompleteGo.png');
        $pdf->Image($logoPath, 10, 10, 20, 0, 'PNG');
        $pdf->SetFont('helvetica', 'B', 16);
//        $pdf->Cell(0, 10, 'Go To Market', 0, 1, 'L');
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode('ORDEN DE SERVICIO DE TRANSPORTE DE MERCADERÍA'), 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('N° ' . ($listar_info->despacho_numero_correlativo ?? '-')), 0, 1, 'R');
        $pdf->Ln(10);

        // Datos Solicitante - Datos de la OS
        $pdf->SetLeftMargin(16);
        $pdf->SetRightMargin(10);
        $marginL = 10;
        $gutter = 10;
        $usableW = $pdf->GetPageWidth() - 2*$marginL;
        $colW = ($usableW - $gutter) / 2;
        $yTop = $pdf->GetY();
        $headerH = 16;
        $padX = 6;
        $padY = 2; // un poco más de aire
        // Datos
        $solicitante_nombre = 'GO TO MARKET SAC';
        $solicitante_ruc    = 'RUC 20537638045';
        $solicitante_dir    = 'CAL.CALLE 1 MZA. X LOTE. 4V INT. C COO. LAS VERTIENTES DE TABLADA DE LURÍN LIMA - LIMA - VILLA EL SALVADOR';

        $fechaAprobacion = $listar_info->despacho_fecha_aprobacion
            ? $general->obtenerNombreFecha($listar_info->despacho_fecha_aprobacion, 'Date', 'Date') : '-';
        $fechaInicio = $listar_info->despacho_fecha_aprobacion
            ? $general->obtenerNombreFecha($listar_info->despacho_fecha_aprobacion, 'Date', 'Date') : '-';

        // Colores
        $celeste    = [231,239,252];
        $textoMuted = [91,105,120];
        $borde      = [225,231,239];

// ---------- Coordenadas base ----------
        $xL = $marginL;
        $yL = $yTop;
        $xR = $marginL + $colW + $gutter; $yR = $yTop;
// ---------- Cabeceras ----------
        $pdf->SetFillColor($celeste[0], $celeste[1], $celeste[2]);
        $pdf->Rect($xL, $yL, $colW, $headerH, 'F');
        $pdf->Rect($xR, $yR, $colW, $headerH, 'F');

        $pdf->SetFont('helvetica','B',12);
        $pdf->SetTextColor(0);
        $pdf->SetXY($xL + $padX, $yL + 5);
        $pdf->Cell($colW - 2*$padX, 6, utf8_decode('Datos Solicitante'), 0, 1, 'L');

        $pdf->SetXY($xR + $padX, $yR + 5);
        $pdf->Cell($colW - 2*$padX, 6, utf8_decode('Datos de la OS'), 0, 1, 'L');

// ---------- Panel izquierdo: contenido ----------
        $pdf->SetXY($xL + $padX, $yL + $headerH + $padY);
        $pdf->SetFont('helvetica','',9);
        $pdf->SetTextColor(0);

        $yStartBodyL = $pdf->GetY();
        $pdf->MultiCell($colW - 2*$padX, 7, utf8_decode($solicitante_nombre), 0, 'L');

        $pdf->SetTextColor(0);
        $pdf->MultiCell($colW - 2*$padX, 7, utf8_decode($solicitante_ruc), 0, 'L');

        $pdf->SetTextColor(0);
        $pdf->MultiCell($colW - 2*$padX, 6, utf8_decode($solicitante_dir), 0, 'L');

        $yEndBodyL = $pdf->GetY();
        $panelHL   = ($yEndBodyL - $yStartBodyL) + $headerH + 2*$padY;

// ---------- Panel derecho: contenido en dos columnas con wrap ----------
        $pdf->SetFont('helvetica','',9);
        $pdf->SetTextColor(0);

        $wInner = $colW - 2*$padX;
        $wHalf = $wInner / 2;
        $lineH = 6;

        $rows = [
            [utf8_decode('Fecha de Aprobación: ').$fechaAprobacion, utf8_decode('Fecha de Inicio de Servicio: ').$fechaInicio],
            [utf8_decode('Plazo de Entrega: 2 días hábiles'),       utf8_decode('Fecha de Entrega Esperada: ').$fechaInicio],
        ];

        $yBodyR = $yR + $headerH + $padY;
        $yCursor = $yBodyR;

        foreach ($rows as $row) {
            // Col 1
            $pdf->SetXY($xR + $padX, $yCursor);
            $pdf->MultiCell($wHalf - 1, $lineH, $row[0], 0, 'L'); // -2 para evitar tocar el borde
            $yAfterCol1 = $pdf->GetY();

            // Col 2 (misma fila, misma Y inicial)
            $pdf->SetXY($xR + $padX + $wHalf, $yCursor);
            $pdf->MultiCell($wHalf - 1, $lineH, $row[1], 0, 'L');
            $yAfterCol2 = $pdf->GetY();

            // Avanza a la mayor Y de ambas columnas
            $yCursor = max($yAfterCol1, $yAfterCol2);
        }

        $panelHR = ($yCursor - $yBodyR) + $headerH + 2*$padY;
// ---------- Bordes y normalización de alturas ----------
        $panelH = max($panelHL, $panelHR);
        $pdf->SetDrawColor($borde[0], $borde[1], $borde[2]);
        $pdf->Rect($xL, $yL, $colW, $panelH);
        $pdf->Rect($xR, $yR, $colW, $panelH);
        // Cursor debajo
        $pdf->SetY($yTop + $panelH + 8);
        // FIN Datos Solicitante - Datos de la OS


        $marginL = 10;
        $gutter = 10;
        $usableW = $pdf->GetPageWidth() - 2*$marginL;
        $colW = ($usableW - $gutter) / 2;
        $headerH = 16;
        $padX = 6;
        $padY = 6;

        $xL = $marginL;
        $xR = $marginL + $colW + $gutter;
        $yTop = $pdf->GetY();

        // Cabeceras
        $pdf->SetFillColor($celeste[0], $celeste[1], $celeste[2]);
        $pdf->Rect($xL, $yTop, $colW, $headerH, 'F');
        $pdf->Rect($xR, $yTop, $colW, $headerH, 'F');

        $pdf->SetFont('helvetica','B',12);
        $pdf->SetTextColor(0);
        $pdf->SetXY($xL + $padX, $yTop + 5);
        $pdf->Cell($colW - 2*$padX, 6, utf8_decode('Datos del Proveedor'), 0, 1, 'L');
        $pdf->SetXY($xR + $padX, $yTop + 5);
        $pdf->Cell($colW - 2*$padX, 6, utf8_decode('Acuerdos Comerciales de la OS'), 0, 1, 'L');

        // Panel izquierdo
        $prov_nombre = $listar_info->transportista_razon_social ?: '-';
        $prov_ruc    = 'RUC ' . ($listar_info->transportista_ruc ?: '-');
        $prov_dir    = 'CAL.CALLE 1 MZA. X LOTE. 4V INT. C COO. LAS VERTIENTES DE TABLADA DE LURÍN LIMA - LIMA - VILLA EL SALVADOR';

        $pdf->SetFont('helvetica','',10);
        $pdf->SetTextColor(0);
        $pdf->SetXY($xL + $padX, $yTop + $headerH + $padY);
        $yStartL = $pdf->GetY();
        $pdf->MultiCell($colW - 2*$padX, 6, utf8_decode($prov_nombre), 0, 'L');
        $pdf->SetTextColor(0);
        $pdf->MultiCell($colW - 2*$padX, 6, utf8_decode($prov_ruc), 0, 'L');
        $pdf->SetTextColor(0);
        $pdf->MultiCell($colW - 2*$padX, 6, utf8_decode($prov_dir), 0, 'L');
        $yEndL  = $pdf->GetY();
        $panelHL = ($yEndL - $yStartL) + $headerH + 2*$padY;

        // Panel derecho (FIX: reponer X antes de CADA MultiCell)
        $acuerdos = [
            'Referencia: Cotización N° 123456',
            'Presentado: Por Correo Electrónico e-mail absa@gmail.com',
            'Contacto Comercial: Josue Pomachua',
            'Conformidad de Factura: Después de Entrega',
            'Modo de Pago: Crédito a 15 días de presentación de factura',
            'Garantías del Servicio: 100% de pérdida. Retorno gratuito por deterioro',
        ];

        $pdf->SetFont('helvetica','',10);
        $pdf->SetTextColor(0);

        $yStartR = $yTop + $headerH + $padY;
        $pdf->SetXY($xR + $padX, $yStartR);

        foreach ($acuerdos as $linea) {
            // IMPORTANTÍSIMO: recolocar X en la columna derecha ANTES de cada MultiCell
            $pdf->SetX($xR + $padX);
            $pdf->MultiCell($colW - 2*$padX, 6, utf8_decode($linea), 0, 'L');
        }

        $yEndR   = $pdf->GetY();
        $panelHR = ($yEndR - $yStartR) + $headerH + 2*$padY;

        // Bordes y cursor final
        $panelH = max($panelHL, $panelHR);
        $pdf->SetDrawColor($borde[0], $borde[1], $borde[2]);
        $pdf->Rect($xL, $yTop, $colW, $panelH);
        $pdf->Rect($xR, $yTop, $colW, $panelH);
        $pdf->SetY($yTop + $panelH + 8);






        // Resumen General de la OS
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Resumen General de la OS', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        $pdf->Cell(40, 7, 'Tipo de Servicio:', 0, 0, 'L');
        $pdf->Cell(0, 7, $listar_info->tipo_servicio_concepto, 0, 1, 'L');

        if ($listar_info->id_tipo_servicios == 2) {
            $departamento = DB::table('departamentos')->where('id_departamento', $listar_info->id_departamento)->first();
            $provincia = DB::table('provincias')->where('id_provincia', $listar_info->id_provincia)->first();
            $distrito = DB::table('distritos')->where('id_distrito', $listar_info->id_distrito)->first();

            $pdf->Cell(40, 7, 'Ubigeo del Servicio:', 0, 0, 'L');
            $pdf->Cell(0, 7, ($departamento ? $departamento->departamento_nombre : '') . ' - ' .
                ($provincia ? $provincia->provincia_nombre : '') . ' - ' .
                ($distrito ? $distrito->distrito_nombre : 'TODOS LOS DISTRITOS'), 0, 1, 'L');
        }

        if ($listar_info->id_vehiculo) {
            $vehiculo = DB::table('vehiculos')->where('id_vehiculo', $listar_info->id_vehiculo)->first();
            $pdf->Cell(40, 7, 'Capacidad del Vehículo:', 0, 0, 'L');
            $pdf->Cell(0, 7, $general->formatoDecimal($vehiculo->vehiculo_capacidad_peso) . ' Kg', 0, 1, 'L');
        }

        if ($listar_info->id_tarifario) {
            $pdf->Cell(40, 7, 'Capacidad de la Tarifa:', 0, 0, 'L');
            $pdf->Cell(0, 7, 'Min: ' . $general->formatoDecimal($listar_info->despacho_cap_min) . ' Kg - Max: ' .
                $general->formatoDecimal($listar_info->despacho_cap_max) . ' Kg', 0, 1, 'L');
        }

        $pdf->Cell(40, 7, 'Peso Despacho:', 0, 0, 'L');
        $pdf->Cell(0, 7, $general->formatoDecimal($listar_info->despacho_peso) . ' Kg', 0, 1, 'L');

        $pdf->Cell(40, 7, 'Volumen Despacho:', 0, 0, 'L');
        $pdf->Cell(0, 7, $general->formatoDecimal($listar_info->despacho_volumen) . ' m³', 0, 1, 'L');

        $pdf->Cell(40, 7, 'Monto de la Tarifa:', 0, 0, 'L');
        $pdf->Cell(0, 7, 'S/ ' . $general->formatoDecimal($listar_info->despacho_flete), 0, 1, 'L');

        $pdf->Cell(40, 7, 'Otros Gastos:', 0, 0, 'L');
        $pdf->Cell(0, 7, 'S/ ' . $general->formatoDecimal($listar_info->despacho_gasto_otros), 0, 1, 'L');

        if ($listar_info->id_tipo_servicios == 1) {
            $pdf->Cell(40, 7, 'Mano de Obra:', 0, 0, 'L');
            $pdf->Cell(0, 7, 'S/ ' . $general->formatoDecimal($listar_info->despacho_ayudante), 0, 1, 'L');
        }

        $pdf->Cell(40, 7, 'Total del Servicio sin IGV:', 0, 0, 'L');
        $pdf->Cell(0, 7, 'S/ ' . $general->formatoDecimal($listar_info->despacho_costo_total), 0, 1, 'L');

        $pdf->Cell(40, 7, 'Total del Servicio con IGV:', 0, 0, 'L');
        $pdf->Cell(0, 7, 'S/ ' . $general->formatoDecimal($listar_info->despacho_costo_total), 0, 1, 'L');
        $pdf->Ln(10);

        // Detalle del Servicio (Tabla de guías)
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Detalle del Servicio', 0, 1, 'L');
        $pdf->SetFont('helvetica', 'B', 10);

        // Cabecera de la tabla
        $pdf->Cell(10, 7, 'N°', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Guía o Doc.', 1, 0, 'C');
        $pdf->Cell(25, 7, 'RUC Dest.', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Nombre Dest.', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Factura/Doc.', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Dirección Entrega', 1, 0, 'C');
        $pdf->Cell(30, 7, 'Ubigeo', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Peso (kg)', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Volumen (m³)', 1, 1, 'C');

        // Contenido de la tabla
        $pdf->SetFont('helvetica', '', 8);
        $conteo = 1;
        foreach ($listar_info->guias as $guia) {
            $pdf->Cell(10, 7, $conteo, 1, 0, 'C');
            $pdf->Cell(30, 7, $guia->guia_nro_doc, 1, 0, 'L');
            $pdf->Cell(25, 7, $guia->guia_ruc_cliente, 1, 0, 'L');
            $pdf->Cell(40, 7, $guia->guia_nombre_cliente, 1, 0, 'L');
            $pdf->Cell(30, 7, $guia->guia_nro_doc_ref, 1, 0, 'L');
            $pdf->Cell(40, 7, $guia->guia_direc_entrega, 1, 0, 'L');
            $pdf->Cell(30, 7, $guia->guia_departamento . ' - ' . $guia->guia_provincia . ' - ' . ($guia->guia_destrito ?? ''), 1, 0, 'L');
            $pdf->Cell(20, 7, $general->formatoDecimal($guia->pesoTotalKilos), 1, 0, 'R');
            $pdf->Cell(20, 7, $general->formatoDecimal($guia->volumenTotal), 1, 1, 'R');
            $conteo++;
        }

        // Notas y firmas
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Nota:', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(0, 7, '1. El Proveedor debe emitir su factura según lo especificado en la Orden de Servicio; donde cada ítem de la factura es una OS o, en su defecto, emitir una factura por OS', 0, 'L');
        $pdf->MultiCell(0, 7, '2. El Proveedor debe indicar el número de OS en el detalle de su Factura y las guías de remisión o documento solicitante', 0, 'L');
        $pdf->MultiCell(0, 7, '3. El Proveedor debe entregar los cargos de entrega en un plazo de 7 días hábiles', 0, 'L');
        $pdf->MultiCell(0, 7, '4. El Proveedor puede enviar su Factura electrónica al correo electrónico: operaciones@gotomarket.com.pe', 0, 'L');

        // Firmas
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(95, 10, 'Firma del Solicitante', 0, 0, 'L');
        $pdf->Cell(95, 10, 'Firma del Proveedor', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(95, 7, 'Autorizado con Usuario y contraseña:', 0, 0, 'L');
        $pdf->Cell(95, 7, '', 0, 1, 'L');
        $pdf->Cell(95, 7, 'Antonio Angulo Casanova', 0, 0, 'L');
        $pdf->Cell(95, 7, '', 0, 1, 'L');
        $pdf->Cell(95, 7, 'Gerente de Operaciones', 0, 0, 'L');
        $pdf->Cell(95, 7, '', 0, 1, 'L');

        // Generar el PDF
        $pdf->Output('I', utf8_decode('OrdenServicio_' . ($listar_info->despacho_numero_correlativo ?? '')) . '.pdf');
        exit;
    }
}
