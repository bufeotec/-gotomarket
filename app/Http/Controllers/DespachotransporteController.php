<?php

namespace App\Http\Controllers;

//use App\Helpers\PdfReporte;
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
        $pdf->SetTitle(utf8_decode('Orden de Servicio N° ' . ($listar_info->despacho_numero_correlativo ?? '-')));
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->AddPage();





        // Ancho total de la página (todo el ancho)
        $anchoPagina = $pdf->GetPageWidth();
        $logoPath = public_path('goto_new_logo.png');
        // Definir anchos relativos
        $anchoLogo = 30;
        $anchoNumero = 70;
        $anchoTitulo = $anchoPagina - $anchoLogo - $anchoNumero;

        $pdf->SetFont('helvetica', 'B', 16);

        $pdf->Cell($anchoLogo, 20, $pdf->Image($logoPath, $pdf->GetX(), $pdf->GetY(), 15), 0, 0, 'L');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell($anchoTitulo, 20, utf8_decode('ORDEN DE SERVICIO DE TRANSPORTE DE MERCADERÍA'), 0, 0, 'C');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell($anchoNumero, 20, utf8_decode('N° ' . ($listar_info->despacho_numero_correlativo ?? '-')), 0, 1, 'C');











        // Configuración inicial
        $pdf->SetFont('helvetica', '', 8);
        $startY = $pdf->GetY(); // Guardamos la posición Y inicial

        // --- ENCABEZADOS SUPERIORES ---
        $pdf->SetFont('helvetica', 'B', 8);
        // Encabezado izquierdo
        $pdf->SetXY(10, $startY);
        $pdf->Cell(90, 5, 'Datos Solicitante', 1, 1, 'C'); // Marco alrededor del título
        // Encabezado derecho
        $pdf->SetXY(110, $startY);
        $pdf->Cell(90, 5, 'Datos de la OS', 1, 1, 'C');

        // Ajustamos la posición Y para los cuadros de datos
        $dataStartY = $startY + 5;

        // --- PRIMER CUADRO: Datos Solicitante (izquierda) ---
        $pdf->Rect(10, $dataStartY, 90, 23); // Cuadro de datos

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(12, $dataStartY + 2); // Pequeño margen interno
        $pdf->MultiCell(86, 5, utf8_decode('GO TO MARKET SAC'), 0, 'L');
        $pdf->SetX(12);
        $pdf->MultiCell(86, 5, utf8_decode('RUC 20537638045'), 0, 'L');
        $pdf->SetX(12);
        $pdf->MultiCell(86, 5, utf8_decode('CAL.CALLE 1 MZA. X LOTE. 4V INT. C COO. LAS VERTIENTES DE TABLADA DE LURÍN LIMA - LIMA - VILLA EL SALVADOR'), 0, 'L');

        // --- SEGUNDO CUADRO: Datos de la OS (derecha) ---
        $pdf->Rect(110, $dataStartY, 90, 23); // Cuadro de datos

        $fechaAprobacion = $listar_info->despacho_fecha_aprobacion ? $general->obtenerNombreFecha($listar_info->despacho_fecha_aprobacion, 'Date', 'Date') : '-';
        $fechaInicio = $listar_info->despacho_fecha_aprobacion ? $general->obtenerNombreFecha($listar_info->despacho_fecha_aprobacion, 'Date', 'Date') : '-';

        $pdf->SetXY(112, $dataStartY + 2); // Pequeño margen interno
        $pdf->MultiCell(86, 5, utf8_decode('Fecha de Aprobación: ' . $fechaAprobacion), 0, 'L');
        $pdf->SetX(112);
        $pdf->MultiCell(86, 5, utf8_decode('Fecha de Inicio: ' . $fechaInicio), 0, 'L');
        $pdf->SetX(112);
        $pdf->MultiCell(86, 5, utf8_decode('Plazo de Entrega: - '), 0, 'L');
        $pdf->SetX(112);
        $pdf->MultiCell(86, 5, utf8_decode('Fecha Entrega Esperada: - '), 0, 'L');

        $pdf->Ln(3); // Espacio después de los cuadros
        // FIN Datos Solicitante - Datos de la OS












        // Configuración inicial
        $pdf->SetFont('helvetica', '', 10);
        $startY = $pdf->GetY(); // Guardamos la posición - inicial

        // --- ENCABEZADOS SUPERIORES ---
        $pdf->SetFont('helvetica', 'B', 8);
        // Encabezado izquierdo
        $pdf->SetXY(10, $startY);
        $pdf->Cell(90, 5, 'Datos del Proveedor', 1, 1, 'C', false);
        // Encabezado derecho
        $pdf->SetXY(110, $startY);
        $pdf->Cell(90, 5, 'Acuerdos Comerciales de la OS', 1, 1, 'C', false);

        // Ajustamos la posición Y para los cuadros de datos
        $dataStartY = $startY + 5;

        // --- PRIMER CUADRO: Datos del Proveedor (izquierda) ---
        $pdf->Rect(10, $dataStartY, 90, 18); // Cuadro de datos (ajustar altura según necesidad)

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetXY(12, $dataStartY + 3); // Margen interno
        $pdf->MultiCell(86, 5, utf8_decode($listar_info->transportista_razon_social), 0, 'L');
        $pdf->SetX(12);
        $pdf->MultiCell(86, 5, utf8_decode('RUC ' . $listar_info->transportista_ruc), 0, 'L');
        $pdf->SetX(12);
        $pdf->MultiCell(86, 5, utf8_decode($listar_info->transportista_direccion), 0, 'L');

        // --- SEGUNDO CUADRO: Acuerdos Comerciales (derecha) ---
        $pdf->Rect(110, $dataStartY, 90, 18); // Misma altura que el primero

//        $pdf->SetXY(112, $dataStartY + 2); // Margen interno
//        $pdf->MultiCell(86, 5, utf8_decode('Referencia: Cotización N° 123456'), 0, 'L');
//        $pdf->SetX(112);
//        $pdf->MultiCell(86, 5, utf8_decode('Presentado: Por Correo Electrónico e-mail absa@gmail.com'), 0, 'L');
//        $pdf->SetX(112);
//        $pdf->MultiCell(86, 5, utf8_decode('Contacto Comercial: Josue Pomachua'), 0, 'L');
//        $pdf->SetX(112);
//        $pdf->MultiCell(86, 5, utf8_decode('Conformidad de Factura: Después de Entrega'), 0, 'L');
//        $pdf->SetX(112);
//        $pdf->MultiCell(86, 5, utf8_decode('Modo de Pago: Crédito a 15 días de presentación de factura'), 0, 'L');
//        $pdf->SetX(112);
//        $pdf->MultiCell(86, 5, utf8_decode('Garantías del Servicio: 100% de pérdida. Retorno gratuito por deterioro'), 0, 'L');

        $pdf->Ln(3); // Espacio después de los cuadros












        // Resumen General de la OS - Estilo de cuadro con datos en 2 líneas de 5
        $pdf->SetFont('helvetica', '', 10);
        $startY = $pdf->GetY();

        $pdf->SetFont('helvetica', 'B', 8);
        // Encabezado izquierdo
        $pdf->SetXY(10, $startY);
        $pdf->Cell(190, 5, 'Resumen General de la OS', 1, 1, 'C', false);

        // Dibujar el cuadro contenedor principal
        $startY = $pdf->GetY();
        $pdf->Rect(10, $startY, 190, 13); // Ajusta la altura según necesidad

        $pdf->SetFont('helvetica', '', 7);

        // Primera línea horizontal (5 datos)
        $line1Y = $startY + 2;
        $colWidth = 20; // Ancho por columna (190mm/5)

        // Data 1: Tipo de Servicio
        $pdf->SetXY(10, $line1Y);
        $pdf->Cell(20, 2, utf8_decode('Tipo de Servicio:'), 0, 0, 'L');
        $pdf->Cell(15, 2, utf8_decode($listar_info->tipo_servicio_concepto), 0, 0, 'L');

        // Data 2: Capacidad Vehículo
        $vehiculo_capacidad = $listar_info->id_vehiculo ? DB::table('vehiculos')->where('id_vehiculo', $listar_info->id_vehiculo)->first()->vehiculo_capacidad_peso : 'N/A';
        $pdf->Cell(20, 2, utf8_decode('Capac. Vehículo:'), 0, 0, 'L');
        $pdf->Cell(18, 2, utf8_decode($general->formatoDecimal($vehiculo_capacidad).' Kg'), 0, 0, 'L');

        // Data 3: Capacidad Tarifa
        $pdf->Cell(17, 2, utf8_decode('Capac. Tarifa:'), 0, 0, 'L');
        $pdf->Cell(34, 2, utf8_decode('Min:'.$general->formatoDecimal($listar_info->despacho_cap_min).'Kg - Max:'.$general->formatoDecimal($listar_info->despacho_cap_max)), 0, 0, 'L');

        // Data 4: Peso Despacho
        $pdf->Cell(19, 2, utf8_decode('Peso Despacho:'), 0, 0, 'L');
        $pdf->Cell(18, 2, utf8_decode($general->formatoDecimal($listar_info->despacho_peso).' Kg'), 0, 1, 'L');




        // Segunda línea horizontal (5 datos)
        $line2Y = $line1Y + 6;
        $pdf->SetXY(10, $line2Y);


        // Data 5: Volumen Despacho
        $pdf->Cell(17, 2, utf8_decode('Vol. Despacho:'), 0, 0, 'L');
        $pdf->Cell(22, 2, utf8_decode($general->formatoDecimal($listar_info->despacho_volumen).' m³'), 0, 0, 'L');

        // Data 6: Monto Tarifa
        $pdf->Cell(15, 2, utf8_decode('Monto Tarifa:'), 0, 0, 'L');
        $pdf->Cell(15, 2, utf8_decode('S/'.$general->formatoDecimal($listar_info->despacho_flete)), 0, 0, 'L');

        // Data 7: Otros Gastos
        $pdf->Cell(16, 2, utf8_decode('Otros Gastos:'), 0, 0, 'L');
        $pdf->Cell(15, 2, utf8_decode('S/'.$general->formatoDecimal($listar_info->despacho_gasto_otros)), 0, 0, 'L');
        // Data 8: Mano de Obra
        $mano_obra = ($listar_info->id_tipo_servicios == 1) ? 'S/'.$general->formatoDecimal($listar_info->despacho_ayudante) : 'S/0';
        $pdf->Cell(17, 2, utf8_decode('Mano de Obra:'), 0, 0, 'L');
        $pdf->Cell(15, 2, utf8_decode($mano_obra), 0, 0, 'L');

        // Data 9: Total sin IGV
        $pdf->Cell(14, 2, utf8_decode('Total s/IGV:'), 0, 0, 'L');
        $pdf->Cell(15, 2, utf8_decode('S/'.$general->formatoDecimal($listar_info->despacho_costo_total)), 0, 0, 'L');

        // Data 9: Total con IGV
        $pdf->Cell(14, 2, utf8_decode('Total c/IGV:'), 0, 0, 'L');
        $pdf->Cell(20, 2, utf8_decode('S/'.$general->formatoDecimal($listar_info->despacho_costo_total)), 0, 1, 'L');

        $pdf->Ln(3); // Espacio después del cuadro








        // Márgenes y salto automático (ajusta 15 si quieres más pie de página)
        $margenInferior = 15;

        // ===== Título =====
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 10, 'Detalle del Servicio', 0, 1, 'L');

        // Anchos
        $anchos = [10,20,20,40,20,30,20,15,15];

        // ===== función inline de cabecera (SIN declarar función) =====
        $pdf->SetFont('helvetica', 'B', 8);
        $headers = ['N°','Guía o Doc.','RUC Dest.','Nombre Dest.','Factura/Doc.','Dirección Entrega','Ubigeo','Peso (kg)','Volumen (cm³)'];
        $hRow = 9;
        $x0 = $pdf->GetX(); $y0 = $pdf->GetY();
        // escribir textos de cabecera
        for ($i=0; $i<count($headers); $i++) {
            $x = $pdf->GetX(); $y = $pdf->GetY();
            $pdf->MultiCell($anchos[$i], 5, utf8_decode($headers[$i]), 0, 'C');
            $pdf->SetXY($x + $anchos[$i], $y);
        }
        // dibujar rectángulos
        $x = $x0;
        for ($i=0; $i<count($anchos); $i++) {
            $pdf->Rect($x, $y0, $anchos[$i], $hRow);
            $x += $anchos[$i];
        }
        $pdf->SetXY($x0, $y0 + $hRow);

        // ===== Contenido =====
        $pdf->SetFont('helvetica', '', 5);
        $conteo = 1;
        $altoLinea = 3;

        foreach ($listar_info->guias as $guia) {
            // Preparar textos
            $txtNro       = (string)$conteo;
            $txtGuia      = (string)$guia->guia_nro_doc;
            $txtRuc       = (string)$guia->guia_ruc_cliente;
            $txtNombre    = utf8_decode((string)$guia->guia_nombre_cliente);
            $txtFactura   = (string)$guia->guia_nro_doc_ref;
            $txtDireccion = utf8_decode((string)$guia->guia_direc_entrega);
            $txtUbigeo    = utf8_decode((string)$guia->guia_departamento).' - '
                . utf8_decode((string)$guia->guia_provincia).' - '
                . utf8_decode((string)($guia->guia_destrito ?? ''));
            $txtPeso      = $general->formatoDecimal($guia->pesoTotalKilos);
            $txtVolumen   = $general->formatoDecimal($guia->volumenTotal);

            // Estimar altura de la fila
            $lineasEstim = 1;
            $lineasEstim = max($lineasEstim, ceil($pdf->GetStringWidth($txtNombre)    / $anchos[3]));
            $lineasEstim = max($lineasEstim, ceil($pdf->GetStringWidth($txtDireccion) / $anchos[5]));
            $lineasEstim = max($lineasEstim, ceil($pdf->GetStringWidth($txtUbigeo)    / $anchos[6]));
            $lineasEstim = max($lineasEstim, ceil($pdf->GetStringWidth($txtGuia)      / $anchos[1]));
            $lineasEstim = max($lineasEstim, ceil($pdf->GetStringWidth($txtRuc)       / $anchos[2]));
            $lineasEstim = max($lineasEstim, ceil($pdf->GetStringWidth($txtFactura)   / $anchos[4]));

            $rowH_est = $altoLinea * $lineasEstim;
            $limITE   = $pdf->GetPageHeight() - $margenInferior;

            // Salto de página si no entra
            if ($pdf->GetY() + $rowH_est > $limITE) {
                $pdf->AddPage();

                // Redibujar cabecera
                $pdf->SetFont('helvetica', 'B', 9);
                $x0 = $pdf->GetX(); $y0 = $pdf->GetY();
                for ($i=0; $i<count($headers); $i++) {
                    $x = $pdf->GetX(); $y = $pdf->GetY();
                    $pdf->MultiCell($anchos[$i], 5, utf8_decode($headers[$i]), 0, 'C');
                    $pdf->SetXY($x + $anchos[$i], $y);
                }
                $x = $x0;
                for ($i=0; $i<count($anchos); $i++) {
                    $pdf->Rect($x, $y0, $anchos[$i], $hRow);
                    $x += $anchos[$i];
                }
                $pdf->SetXY($x0, $y0 + $hRow);
                $pdf->SetFont('helvetica', '', 8);
            }

            // --- Escribir fila (igual que ya tienes) ---
            $xFila = $pdf->GetX(); $yFila = $pdf->GetY(); $yMax = $yFila;

            // N°
            $x = $pdf->GetX(); $y = $pdf->GetY();
            $pdf->MultiCell($anchos[0], $altoLinea, $txtNro, 0, 'C');
            $yMax = max($yMax, $pdf->GetY()); $pdf->SetXY($x + $anchos[0], $y);

            // Guía
            $x = $pdf->GetX(); $y = $pdf->GetY();
            $pdf->MultiCell($anchos[1], $altoLinea, $txtGuia, 0, 'L');
            $yMax = max($yMax, $pdf->GetY()); $pdf->SetXY($x + $anchos[1], $y);

            // RUC
            $x = $pdf->GetX(); $y = $pdf->GetY();
            $pdf->MultiCell($anchos[2], $altoLinea, $txtRuc, 0, 'L');
            $yMax = max($yMax, $pdf->GetY()); $pdf->SetXY($x + $anchos[2], $y);

            // Nombre
            $x = $pdf->GetX(); $y = $pdf->GetY();
            $pdf->MultiCell($anchos[3], $altoLinea, $txtNombre, 0, 'L');
            $yMax = max($yMax, $pdf->GetY()); $pdf->SetXY($x + $anchos[3], $y);

            // Factura
            $x = $pdf->GetX(); $y = $pdf->GetY();
            $pdf->MultiCell($anchos[4], $altoLinea, $txtFactura, 0, 'L');
            $yMax = max($yMax, $pdf->GetY()); $pdf->SetXY($x + $anchos[4], $y);

            // Dirección
            $x = $pdf->GetX(); $y = $pdf->GetY();
            $pdf->MultiCell($anchos[5], $altoLinea, $txtDireccion, 0, 'L');
            $yMax = max($yMax, $pdf->GetY()); $pdf->SetXY($x + $anchos[5], $y);

            // Ubigeo
            $x = $pdf->GetX(); $y = $pdf->GetY();
            $pdf->MultiCell($anchos[6], $altoLinea, $txtUbigeo, 0, 'L');
            $yMax = max($yMax, $pdf->GetY()); $pdf->SetXY($x + $anchos[6], $y);

            // Peso
            $x = $pdf->GetX(); $y = $pdf->GetY();
            $pdf->MultiCell($anchos[7], $altoLinea, $txtPeso, 0, 'R');
            $yMax = max($yMax, $pdf->GetY()); $pdf->SetXY($x + $anchos[7], $y);

            // Volumen
            $x = $pdf->GetX(); $y = $pdf->GetY();
            $pdf->MultiCell($anchos[8], $altoLinea, $txtVolumen, 0, 'R');
            $yMax = max($yMax, $pdf->GetY()); $pdf->SetXY($x + $anchos[8], $y);

            // Bordes
            $rowH = $yMax - $yFila;
            $x = $xFila;
            for ($i=0; $i<count($anchos); $i++) {
                $pdf->Rect($x, $yFila, $anchos[$i], $rowH);
                $x += $anchos[$i];
            }
            $pdf->SetXY($xFila, $yFila + $rowH);

            $conteo++;
        }







        // Notas y firmas
        $pdf->Ln(5);

        // === Config de columnas (dos cuadros) ===
        $col1_width = 115; // Notas
        $col2_width = 75;  // Firma solicitante
        $row_height = 5;

// === MARCAR INICIO DEL CUADRO (antes de los títulos) ===
        $boxX = $pdf->GetX();
        $boxY = $pdf->GetY();

// Títulos (sin bordes)
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell($col1_width, 7, utf8_decode('Notas:'), 0, 0, 'L');
        $pdf->Cell($col2_width, 7, utf8_decode('Firma del Solicitante'), 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 7);

// Guarda la posición inicial del CONTENIDO (después de los títulos)
        $xStart = $pdf->GetX(); // margen izquierdo
        $yStart = $pdf->GetY();

// Contenidos
        $txtNotas =
            "1. El Proveedor debe emitir su factura según lo especificado en la Orden de Servicio; donde cada ítem de la factura es una OS o, en su defecto, emitir una factura por OS
2. El Proveedor debe indicar el número de OS en el detalle de su Factura y las guías de remisión o documento solicitante
3. El Proveedor debe entregar los cargos de entrega en un plazo de 7 días hábiles
4. El Proveedor puede enviar su Factura electrónica al correo electrónico: operaciones@gotomarket.com.pe";

        $txtSolicitante =
            "  Autorizado con Usuario y contraseña:
  Antonio Angulo Casanova
  Gerente de Operaciones
_____________________________________________________

                            _____________________________
                                         Firma del Proveedor";

// Helper para imprimir MultiCell en posición fija y devolver la Y final
        $printCol = function($x, $y, $w, $text) use ($pdf, $row_height) {
            $pdf->SetXY($x, $y);
            $pdf->MultiCell($w, $row_height, utf8_decode($text), 0, 'L');
            return $pdf->GetY();
        };

// Imprime las dos columnas arrancando al mismo Y
        $yEnd1 = $printCol($boxX, $yStart, $col1_width, $txtNotas);
        $yEnd2 = $printCol($boxX + $col1_width, $yStart, $col2_width, $txtSolicitante);

// Altura total del cuadro (desde antes de los títulos)
        $paddingBottom = 2;
        $yEndMax = max($yEnd1, $yEnd2);
        $boxW = $col1_width + $col2_width;
        $boxH = ($yEndMax - $boxY) + $paddingBottom;

// === DIBUJO DEL CUADRO Y SEPARADOR ===
        $pdf->Rect($boxX, $boxY, $boxW, $boxH); // borde exterior
        $pdf->Line($boxX + $col1_width, $boxY, $boxX + $col1_width, $boxY + $boxH); // separador entre las dos

// Cursor al final del bloque
        $pdf->SetXY($boxX, $yEndMax + $paddingBottom);










        // Generar el PDF
        $pdf->Output('I', utf8_decode('OrdenServicio_' . utf8_decode($listar_info->despacho_numero_correlativo ?? ''))) . '.pdf';
        exit;
    }
}
