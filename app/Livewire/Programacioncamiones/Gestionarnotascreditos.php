<?php

namespace App\Livewire\Programacioncamiones;

use App\Models\Guia;
use App\Models\Historialguia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\Logs;
use App\Models\General;
use App\Models\Notacredito;
use App\Models\Notacreditodetalle;
use App\Models\Server;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Gestionarnotascreditos extends Component{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $notacredito;
    private $general;
    private $notacreditodetalle;
    private $server;
    public function __construct(){
        $this->logs = new Logs();
        $this->notacredito = new Notacredito();
        $this->general = new General();
        $this->notacreditodetalle = new Notacreditodetalle();
        $this->server = new Server();
    }
    public $fecha_desde = "";
    public $fecha_hasta = "";
    public $buscar_ruc_nombre = "";
    public $buscar_numero_nc = "";
    public $buscar_estado = "";
    public $listar_nc = [];
    public $nota_credito_detalle = [];
    public $estado_nota_credito = "";
    public $id_not_cred = "";
    public function mount(){
        $this->fecha_desde = date('Y-m-01');
        $this->fecha_hasta = date('Y-m-d');
    }

    public function render(){
        return view('livewire.programacioncamiones.gestionarnotascreditos');
    }

    public function buscar_nc(){
        if (!Gate::allows('buscar_nc')) {
            session()->flash('error', 'No tiene permisos para buscar una nota de crédito.');
            return;
        }

        $query = DB::table('notas_creditos');
//            ->whereIn('not_cred_estado_aprobacion', '=', [1,2,3]);

        // Aplicar filtro por nombre de cliente si existe
        if (!empty($this->buscar_ruc_nombre)) {
            $busqueda = trim($this->buscar_ruc_nombre);

            // Verificar si tiene el formato "RUC - Nombre"
            if (preg_match('/^(\d+)\s*-\s*(.+)$/', $busqueda, $matches)) {
                $ruc = trim($matches[1]);
                $nombre = trim($matches[2]);

                $query->where(function($q) use ($ruc, $nombre) {
                    $q->where('not_cred_ruc_cliente', 'LIKE', '%' . $ruc . '%')
                        ->where('not_cred_nombre_cliente', 'LIKE', '%' . $nombre . '%');
                });
            } else {
                // Búsqueda normal (RUC o Nombre)
                $query->where(function($q) use ($busqueda) {
                    $q->where('not_cred_ruc_cliente', 'LIKE', '%' . $busqueda . '%')
                        ->orWhere('not_cred_nombre_cliente', 'LIKE', '%' . $busqueda . '%');
                });
            }
        }

        // Aplicar filtro por rango de fechas si existen
        if (!empty($this->buscar_numero_nc)) {
            $query->where('not_cred_nro_doc', 'LIKE', '%' . $this->buscar_numero_nc . '%');
        } else {
            // Aplicar filtros de fecha
            if ($this->fecha_desde) {
                $query->whereDate('not_cred_fecha_emision', '>=', $this->fecha_desde);
            }
            if ($this->fecha_hasta) {
                $query->whereDate('not_cred_fecha_emision', '<=', $this->fecha_hasta);
            }

            // Filtro por estado de aprobación
            if (!empty($this->buscar_estado)) {
                $query->where('not_cred_estado', $this->buscar_estado);
            }
        }
        $this->listar_nc = $query->get();
    }

    public function modal_info_nota_credito($id_not_cred) {
        $this->id_not_cred = base64_decode($id_not_cred);
        $this->nota_credito_detalle = $this->notacredito->listar_nota_credito_detalle($this->id_not_cred);
    }

    public function exportar_excel_x_id_nota_credito($id_not_cred){
        try {
            // Obtener los detalles de la nota de crédito
            $detalles = Notacreditodetalle::where('id_not_cred', $id_not_cred)->get();

            if ($detalles->isEmpty()) {
                session()->flash('error', 'No hay datos para exportar.');
                return;
            }

            // Crear el spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Detalle Nota de Crédito');

            // Encabezados
            $row = 1;
            $sheet->setCellValue('A'.$row, 'N°');
            $sheet->setCellValue('B'.$row, 'Almacén de Entrada');
            $sheet->setCellValue('C'.$row, 'Fecha Emisión');
            $sheet->setCellValue('D'.$row, 'Estado');
            $sheet->setCellValue('E'.$row, 'Tipo Documento');
            $sheet->setCellValue('F'.$row, 'Nro. Documento');
            $sheet->setCellValue('G'.$row, 'Nro. Línea');
            $sheet->setCellValue('H'.$row, 'Cód. Producto');
            $sheet->setCellValue('I'.$row, 'Descripción Producto');
            $sheet->setCellValue('J'.$row, 'Lote');
            $sheet->setCellValue('K'.$row, 'Unidad');
            $sheet->setCellValue('L'.$row, 'Cantidad');
            $sheet->setCellValue('M'.$row, 'Precio Unitario');
            $sheet->setCellValue('N'.$row, 'Texto');
            $sheet->setCellValue('O'.$row, 'IGV Total');
            $sheet->setCellValue('P'.$row, 'Importe Total');
            $sheet->setCellValue('Q'.$row, 'Moneda');
            $sheet->setCellValue('R'.$row, 'Tipo Cambio');
            $sheet->setCellValue('S'.$row, 'Peso (g)');
            $sheet->setCellValue('T'.$row, 'Volumen (cm³)');
            $sheet->setCellValue('U'.$row, 'Peso Total (g)');
            $sheet->setCellValue('V'.$row, 'Volumen Total (cm³)');

            // Configuración de anchos de columna (exactamente como lo proporcionaste)
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(15);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(18);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(13);
            $sheet->getColumnDimension('H')->setWidth(18);
            $sheet->getColumnDimension('I')->setWidth(60);
            $sheet->getColumnDimension('J')->setWidth(15);
            $sheet->getColumnDimension('K')->setWidth(15);
            $sheet->getColumnDimension('L')->setWidth(15);
            $sheet->getColumnDimension('M')->setWidth(15);
            $sheet->getColumnDimension('N')->setWidth(15);
            $sheet->getColumnDimension('O')->setWidth(12);
            $sheet->getColumnDimension('P')->setWidth(20);
            $sheet->getColumnDimension('Q')->setWidth(18);
            $sheet->getColumnDimension('R')->setWidth(12);
            $sheet->getColumnDimension('S')->setWidth(15);
            $sheet->getColumnDimension('T')->setWidth(15);
            $sheet->getColumnDimension('U')->setWidth(26);
            $sheet->getColumnDimension('V')->setWidth(18);

            // Estilo para los encabezados
            $headerRange = 'A1:V1';
            $headerStyle = $sheet->getStyle($headerRange);
            $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('C4D79B'); // Fondo verde claro
            $headerStyle->getFont()->setBold(true); // Texto en negrita

            // Datos
            $row = 2;
            $contador = 1;
            foreach ($detalles as $detalle) {
                $sheet->setCellValue('A'.$row, $contador);
                $sheet->setCellValue('B'.$row, $detalle->not_cred_det_almacen_entrada);
                $sheet->setCellValue('C'.$row, $this->general->obtenerNombreFecha($detalle->not_cred_det_fecha_emision, 'DateTime', 'Date'));
                $sheet->setCellValue('D'.$row, $detalle->not_cred_det_estado);
                $sheet->setCellValue('E'.$row, $detalle->not_cred_det_tipo_doc);
                $sheet->setCellValue('F'.$row, $detalle->not_cred_det_nro_doc);
                $sheet->setCellValue('G'.$row, $detalle->not_cred_det_nro_linea);
                $sheet->setCellValue('H'.$row, $detalle->not_cred_det_cod_producto);
                $sheet->setCellValue('I'.$row, $detalle->not_cred_det_descripcion_procd ?? '-');
                $sheet->setCellValue('J'.$row, $detalle->not_cred_det_lote ?? '-');
                $sheet->setCellValue('K'.$row, $detalle->not_cred_det_unidad);
                $sheet->setCellValue('L'.$row, $detalle->not_cred_det_cantidad);
                $sheet->setCellValue('M'.$row, $this->general->formatoDecimal($detalle->not_cred_det_precio_unit_final_inc_igv));
                $sheet->setCellValue('N'.$row, $detalle->not_cred_det_texto ?? '-');
                $sheet->setCellValue('O'.$row, $this->general->formatoDecimal($detalle->not_cred_det_igv_total));
                $sheet->setCellValue('P'.$row, $this->general->formatoDecimal($detalle->not_cred_det_importe_total_inc_igv));
                $sheet->setCellValue('Q'.$row, $detalle->not_cred_det_moneda);
                $sheet->setCellValue('R'.$row, $this->general->formatoDecimal($detalle->not_cred_det_tipo_cambio));
                $sheet->setCellValue('S'.$row, $this->general->formatoDecimal($detalle->not_cred_det_peso_gramos));
                $sheet->setCellValue('T'.$row, $this->general->formatoDecimal($detalle->not_cred_det_volumen));
                $sheet->setCellValue('U'.$row, $this->general->formatoDecimal($detalle->not_cred_det_peso_toal_gramos));
                $sheet->setCellValue('V'.$row, $this->general->formatoDecimal($detalle->not_cred_det_volumen_total));

                $row++;
                $contador++;
            }

            // Nota: He eliminado el autoajuste de columnas ya que estás definiendo anchos fijos

            // Formatear el nombre del archivo Excel
            $fecha_emision = $this->general->obtenerNombreFecha($detalles[0]->not_cred_det_fecha_emision, 'DateTime', 'Date');
            $nombre_excel = "detalle_nota_credito_{$detalles[0]->not_cred_det_nro_doc}_{$fecha_emision}.xlsx";

            // Descargar el archivo
            return response()->stream(
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

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al generar el Excel. Por favor, inténtelo nuevamente.');
            return;
        }
    }

    public function cambiar_estado_nota_credito(){
        try {
            // Validar que se haya seleccionado un estado
//            if (empty($this->estado_seleccionado)) {
//                session()->flash('error', 'Debe seleccionar un estado (Emitido o Anulada).');
//                return;
//            }

            DB::beginTransaction();

            $nota_credito = Notacredito::find($this->id_not_cred);

            if ($nota_credito) {
                // Solo actualizar si el estado es diferente
                if ($nota_credito->not_cred_estado_aprobacion != $this->estado_nota_credito) {
                    $nota_credito->not_cred_estado_aprobacion = $this->estado_nota_credito;

                    if ($nota_credito->save()) {
                        DB::commit();
                        session()->flash('success', "Estado de la nota de crédito actualizado correctamente.");
                        $this->dispatch('hideModalConfirmacionNota');
                        $this->buscar_nc();
                        $this->modal_info_nota_credito(base64_encode($nota_credito->id_not_cred));
                        return;
                    }
                } else {
                    session()->flash('info', 'No se realizaron cambios en el estado.');
                    return;
                }
            }

            DB::rollBack();
            session()->flash('error', 'No se encontró la nota de crédito especificada.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al actualizar la nota de crédito: ' . $e->getMessage());
        }
    }

    public function generar_excel_nota_credito(){
        try {
            // Aplicar los mismos filtros que en buscar_nc()
            $query = DB::table('notas_creditos');

            // Filtro por RUC/Nombre
            if (!empty($this->buscar_ruc_nombre)) {
                $busqueda = trim($this->buscar_ruc_nombre);
                if (preg_match('/^(\d+)\s*-\s*(.+)$/', $busqueda, $matches)) {
                    $ruc = trim($matches[1]);
                    $nombre = trim($matches[2]);
                    $query->where(function($q) use ($ruc, $nombre) {
                        $q->where('not_cred_ruc_cliente', 'LIKE', '%' . $ruc . '%')
                            ->where('not_cred_nombre_cliente', 'LIKE', '%' . $nombre . '%');
                    });
                } else {
                    $query->where(function($q) use ($busqueda) {
                        $q->where('not_cred_ruc_cliente', 'LIKE', '%' . $busqueda . '%')
                            ->orWhere('not_cred_nombre_cliente', 'LIKE', '%' . $busqueda . '%');
                    });
                }
            }

            // Filtro por número de NC
            if (!empty($this->buscar_numero_nc)) {
                $query->where('not_cred_nro_doc', 'LIKE', '%' . $this->buscar_numero_nc . '%');
            } else {
                // Filtros de fecha
                if ($this->fecha_desde) {
                    $query->whereDate('not_cred_fecha_emision', '>=', $this->fecha_desde);
                }
                if ($this->fecha_hasta) {
                    $query->whereDate('not_cred_fecha_emision', '<=', $this->fecha_hasta);
                }

                // Filtro por estado
                if (!empty($this->buscar_estado)) {
                    $query->where('not_cred_estado', $this->buscar_estado);
                }
            }

            $notas_credito = $query->get();

            if ($notas_credito->isEmpty()) {
                session()->flash('error', 'No hay datos para exportar con los filtros aplicados.');
                return;
            }

            // Crear el spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Notas de Crédito');

            // Encabezados (según lo que muestras en la vista)
            $row = 1;
            $sheet->setCellValue('A'.$row, 'N°');
            $sheet->setCellValue('B'.$row, 'Fecha Emisión NC');
            $sheet->setCellValue('C'.$row, 'Código de Motivo');
            $sheet->setCellValue('D'.$row, 'Factura Vinculada');
            $sheet->setCellValue('E'.$row, '¿Factura Registrada en Intranet?');
            $sheet->setCellValue('F'.$row, 'Importe sin IGV');
            $sheet->setCellValue('G'.$row, 'Nombre Cliente');
            $sheet->setCellValue('H'.$row, 'Estado NC Sistema Facturación');
            $sheet->setCellValue('I'.$row, 'Estado NC en Intranet');

            // Configuración de anchos de columna
            $sheet->getColumnDimension('A')->setWidth(8);
            $sheet->getColumnDimension('B')->setWidth(15);
            $sheet->getColumnDimension('C')->setWidth(20);
            $sheet->getColumnDimension('D')->setWidth(18);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(40);
            $sheet->getColumnDimension('H')->setWidth(25);
            $sheet->getColumnDimension('I')->setWidth(20);

            // Estilo para los encabezados
            $headerRange = 'A1:I1';
            $headerStyle = $sheet->getStyle($headerRange);
            $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('C4D79B');
            $headerStyle->getFont()->setBold(true);

            // Datos
            $row = 2;
            $contador = 1;

            foreach ($notas_credito as $nc) {
                // Verificar si la factura está registrada en intranet
                $facturaRegistrada = DB::table('guias')
                    ->where('guia_nro_doc_ref', '=', $nc->not_cred_nro_doc_ref)
                    ->exists();

                // Calcular importe sin IGV
                $importeSinIgv = $nc->not_cred_importe_total / 1.18;

                $sheet->setCellValue('A'.$row, $contador);
                $sheet->setCellValue('B'.$row, $this->general->obtenerNombreFecha($nc->not_cred_fecha_emision, 'DateTime', 'Date'));

                // Código de motivo
                switch($nc->not_cred_motivo) {
                    case 1: $motivo = 'Devolución'; break;
                    case 2: $motivo = 'Calidad'; break;
                    case 3: $motivo = 'Cobranza'; break;
                    case 4: $motivo = 'Error de facturación'; break;
                    case 5: $motivo = 'Otros comercial'; break;
                    default: $motivo = 'Código no reconocido';
                }
                $sheet->setCellValue('C'.$row, $motivo);

                $sheet->setCellValue('D'.$row, $nc->not_cred_nro_doc_ref);
                $sheet->setCellValue('E'.$row, $facturaRegistrada ? 'SI' : 'NO');
                $sheet->setCellValue('F'.$row, number_format($importeSinIgv, 2));
                $sheet->setCellValue('G'.$row, $nc->not_cred_nombre_cliente);
                $sheet->setCellValue('H'.$row, $nc->not_cred_estado);

                // Estado en intranet
                switch($nc->not_cred_estado_aprobacion) {
                    case 1: $estadoIntranet = 'Registrado'; break;
                    case 2: $estadoIntranet = 'Emitido'; break;
                    case 3: $estadoIntranet = 'Anulada'; break;
                    default: $estadoIntranet = 'Estado desconocido';
                }
                $sheet->setCellValue('I'.$row, $estadoIntranet);

                $row++;
                $contador++;
            }

            // Formatear celdas numéricas
            $sheet->getStyle('F2:F'.$row)
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');

            // Formatear el nombre del archivo Excel
            $nombre_excel = "notas_credito_filtradas_".date('Ymd_His').".xlsx";

            // Descargar el archivo
            return response()->stream(
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

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al generar el Excel. Por favor, inténtelo nuevamente.');
            return;
        }
    }

    // ACTUALIZAR EL ESTADO DE LA NOTA DE CRÉDITO:
    public function actualizar_estado_nc($num_doc, $id){
        try {
            if (!Gate::allows('actualizar_estado_nc')) {
                session()->flash('error_nc_guia', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }

            $id_ = base64_decode($id);

            // Obtener la guía actual de la base de datos local
            $nc_local = $this->notacredito->listar_nc_x_num_doc($num_doc);

            // Obtener los datos actualizados del servidor externo (devuelve una colección)
            $nc_servidor = $this->server->obtenerNCxNumDoc($num_doc);

            if (!$nc_local) {
                session()->flash('error_nc_guia', 'No se encontró la nota de crédito en la base de datos local.');
                return;
            }

            if ($nc_servidor->isEmpty()) {
                session()->flash('error_nc_guia', 'No se pudo obtener información actualizada del servidor.');
                return;
            }

            // Tomar el primer elemento de la colección (asumiendo que solo hay una guía)
            $nc_servidor = $nc_servidor->first();

            if (!is_object($nc_servidor)) {
                session()->flash('error_nc_guia', 'La información del servidor no tiene el formato esperado.');
                return;
            }

            // Verificar si el estado es diferente
            if ($nc_local->not_cred_estado != $nc_servidor->ESTADO) {
                DB::beginTransaction();

                // Actualizar solo el campo guia_estado
                $actualizado = DB::table('notas_creditos')
                    ->where('id_not_cred', $id_)
                    ->update(['not_cred_estado' => $nc_servidor->ESTADO]);

                if ($actualizado) {
                    DB::commit();
                    session()->flash('success', 'El estado de la nota de crédito se actualizó correctamente.');
                } else {
                    DB::rollBack();
                    session()->flash('error_nc_guia', 'No se pudo actualizar el estado de la guía.');
                }
            } else {
                session()->flash('success', 'El estado de la nota de crédito ya está actualizado.');
            }

            $this->dispatch('hideModalActualizarNC');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_nc_guia', 'Ocurrió un error al actualizar el estado: ' . $e->getMessage());
        }
    }
}
