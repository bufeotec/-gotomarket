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
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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
    public $archivo_excel;
    public $archivo_pdf;
    public $listar_detalles = [];
    public $editando_registros = [];
    public $datos_edicion = [];
    public $listar_campanias = [];
    public $punto_codigo = "";
    public $id_campania_busqueda = "";
    public $id_cliente_busqueda = "";
    public $search_puntos;
    public $id_campania = "";
    public $id_cliente = "";
    public $abrirListasCliente = false;
    public $buscar_clientes = null;
    public $buscar_clientes_search = null;
    public $listaClientesFiltro = array();
    public $abrirListasClienteModal = false;

    // REGISTRAR PUNTOS MANUALMENTE
    public $punto_detalle_motivo = "";
    public $buscar_clientes_modal_rpm = null;
    public $abrir_modal_rpm = null;
    public $id_campania_rpm = "";
    public $id_cliente_rpm = "";
    public $listar_vendedor_cliente = [];
    public $vendedores_disponibles = [];
    public $vendedores_seleccionados = [];
    public $vendedor_seleccionado = "";



    public $manual_archivo_pdf = "";




    public function mount(){
        $this->listar_campanias = DB::table('campanias')
            ->where('campania_estado', 1)
            ->orderBy('campania_nombre')
            ->get();
    }

    public function render(){
        $listar_puntos = $this->punto->listar_puntos_registrados($this->id_campania_busqueda, $this->id_cliente_busqueda, $this->search_puntos);
        foreach ($listar_puntos as $lp){
            // Consulta con JOIN para obtener los detalles junto con el nombre del vendedor
            $lp->puntos_detalles = DB::table('puntos_detalles as pd')
                ->leftJoin('vendedores_intranet as v', 'pd.punto_detalle_vendedor', '=', 'v.vendedor_intranet_dni')
                ->select(
                    'pd.*',
                    'v.vendedor_intranet_nombre as vendedor_nombre'
                )
                ->where('pd.punto_detalle_estado', '=', 1)
                ->where('pd.id_punto', '=', $lp->id_punto)
                ->get();
        }

        $listar_campania_formulario = $this->campania->listar_campanias_activos();
        return view('livewire.crm.gestionpuntos', compact('listar_puntos', 'listar_campania_formulario'));
    }

    public function descargar_formato_excel_puntos(){
        try {
            $resultados_puntos = $this->punto->obtener_resultado_puntos($this->id_campania_busqueda, $this->id_cliente_busqueda);

            foreach ($resultados_puntos as $rp) {
                $rp->puntos_detalles = DB::table('puntos_detalles')
                    ->where('punto_detalle_estado', '=', 1)
                    ->where('id_punto', '=', $rp->id_punto)
                    ->get();
            }

            $spreadsheet = new Spreadsheet();
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Detalle Cliente');

            $row = 1;

            // ====== Título: nombre de campaña abarcando A:J ======
            // Si no hay campaña filtrada o no hay resultados, usamos un genérico
            $tituloCampania = 'REPORTE DE PUNTOS';
            if (!empty($resultados_puntos) && isset($resultados_puntos[0]->campania_nombre)) {
                $tituloCampania = 'CAMPAÑA: ' . $resultados_puntos[0]->campania_nombre;
            } elseif (empty($resultados_puntos) && $this->id_campania_busqueda) {
                $tituloCampania = 'CAMPAÑA: (sin resultados)';
            }

            $sheet1->mergeCells('A1:J1');
            $sheet1->setCellValue('A1', $tituloCampania);
            $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet1->getStyle('A1')->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet1->getRowDimension(1)->setRowHeight(22);

            $row++;

            // ====== Encabezados (SIN columna CAMPAÑA) ======
            // Fijas (A-D)
            $sheet1->setCellValue('A'.$row, 'N°');
            $sheet1->setCellValue('B'.$row, 'CÓDIGO');
            $sheet1->setCellValue('C'.$row, 'CLIENTE');
            $sheet1->setCellValue('D'.$row, 'FECHA REGISTRO');

            // Detalle (E-J) — Nota: todo se corrió una a la IZQ
            $sheet1->setCellValue('E'.$row, 'N°');
            $sheet1->setCellValue('F'.$row, 'MOTIVO');
            $sheet1->setCellValue('G'.$row, 'VENDEDOR');
            $sheet1->setCellValue('H'.$row, 'PUNTOS GANADOS');
            $sheet1->setCellValue('I'.$row, 'FECHA REGISTRO');
            $sheet1->setCellValue('J'.$row, 'FECHA MODIFICACIÓN');

            // ====== Anchos (manteniendo proporciones, ajustados al nuevo mapeo) ======
            $sheet1->getColumnDimension('A')->setWidth(5);   // N°
            $sheet1->getColumnDimension('B')->setWidth(10);  // CÓDIGO
            $sheet1->getColumnDimension('C')->setWidth(30);  // CLIENTE (antes D)
            $sheet1->getColumnDimension('D')->setWidth(17);  // FECHA REGISTRO (antes E)

            $sheet1->getColumnDimension('E')->setWidth(5);   // N° detalle (antes F)
            $sheet1->getColumnDimension('F')->setWidth(30);  // MOTIVO (antes G)
            $sheet1->getColumnDimension('G')->setWidth(15);  // VENDEDOR (antes H)
            $sheet1->getColumnDimension('H')->setWidth(20);  // PUNTOS GANADOS (antes I)
            $sheet1->getColumnDimension('I')->setWidth(17);  // FECHA REGISTRO (antes J)
            $sheet1->getColumnDimension('J')->setWidth(22);  // FECHA MODIFICACIÓN (antes K)

            // Estilo encabezados
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFCCCCCC']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ]
                ]
            ];
            $sheet1->getStyle('A2:J2')->applyFromArray($headerStyle);

            $row++;
            $numero_punto = 1;

            // ====== Datos ======
            foreach ($resultados_puntos as $rp) {
                $row_inicial = $row;

                // Datos principales del punto (A-D) — sin CAMPAÑA
                $sheet1->setCellValue('A'.$row, $numero_punto);
                $sheet1->setCellValue('B'.$row, $rp->punto_codigo);
                $sheet1->setCellValue('C'.$row, $rp->cliente_nombre_cliente);
                $sheet1->setCellValue('D'.$row, date('d/m/Y', strtotime($rp->created_at)));

                $primera_fila_detalle = true;
                $numero_detalle = 1;

                if (count($rp->puntos_detalles) > 0) {
                    foreach ($rp->puntos_detalles as $detalle) {
                        if (!$primera_fila_detalle) {
                            $row++;
                        }
                        // Detalles (E-J)
                        $sheet1->setCellValue('E'.$row, $numero_detalle);
                        $sheet1->setCellValue('F'.$row, $detalle->punto_detalle_motivo);
                        $sheet1->setCellValue('G'.$row, $detalle->punto_detalle_vendedor);
                        $sheet1->setCellValue('H'.$row, $detalle->punto_detalle_punto_ganado);
                        $sheet1->setCellValue('I'.$row, !empty($detalle->punto_detalle_fecha_registro) ? date('d/m/Y', strtotime($detalle->punto_detalle_fecha_registro)) : '-');
                        $sheet1->setCellValue('J'.$row, !empty($detalle->punto_detalle_fecha_modificacion) ? date('d/m/Y', strtotime($detalle->punto_detalle_fecha_modificacion)) : '-');

                        $primera_fila_detalle = false;
                        $numero_detalle++;
                    }
                } else {
                    // Sin detalles
                    $sheet1->setCellValue('E'.$row, '-');
                    $sheet1->setCellValue('F'.$row, 'Sin detalles');
                    $sheet1->setCellValue('G'.$row, '-');
                    $sheet1->setCellValue('H'.$row, '-');
                    $sheet1->setCellValue('I'.$row, '-');
                    $sheet1->setCellValue('J'.$row, '-');
                }

                // Si hay múltiples detalles, merge de A-D (antes era A-E)
                if (count($rp->puntos_detalles) > 1) {
                    $row_final = $row;
                    $sheet1->mergeCells('A'.$row_inicial.':A'.$row_final);
                    $sheet1->mergeCells('B'.$row_inicial.':B'.$row_final);
                    $sheet1->mergeCells('C'.$row_inicial.':C'.$row_final);
                    $sheet1->mergeCells('D'.$row_inicial.':D'.$row_final);

                    $sheet1->getStyle('A'.$row_inicial.':D'.$row_final)
                        ->getAlignment()
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                }

                // Bordes por bloque (A:J)
                $borderStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ]
                    ]
                ];
                $sheet1->getStyle('A'.$row_inicial.':J'.$row)->applyFromArray($borderStyle);

                $row++;
                $numero_punto++;
            }
            $row++;

            // ====== Descarga ======
            $fecha_actual = date('Y-m-d_H-i-s');
            $nombre_excel = "reporte_puntos_{$fecha_actual}.xlsx";

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
            session()->flash('error', 'Ocurrió un error al generar el Excel. Por favor, inténtelo nuevamente.');
            return redirect()->back();
        }
    }

    // MÉTODOS ESPECÍFICOS PARA EL MODAL
    public function buscarClientesFiltroModal(){
        try {
            $buscar = $this->buscar_clientes ?? '';

            $this->listaClientesFiltro = DB::table('clientes')
                ->where('cliente_estado_registro','=', 1)
                ->where(function($q) use ($buscar) {
                    $q->where('cliente_codigo_cliente', 'like', '%' . $buscar . '%')
                        ->orWhere('cliente_nombre_cliente', 'like', '%' . $buscar . '%');
                })
                ->limit(10)
                ->get();

            $this->abrirListasClienteModal = true;

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return;
        }
    }
    public function seleccionar_cliente_modal($id_cliente){
        try {
            $this->abrirListasClienteModal = false;
            $id_c = base64_decode($id_cliente);

            if ($id_c) {
                $data = DB::table('clientes')
                    ->where('id_cliente', '=', $id_c)
                    ->first();

                // Asignar siempre al modal
                $this->buscar_clientes = $data->cliente_codigo_cliente . ' - ' . $data->cliente_nombre_cliente;
                $this->id_cliente = $id_c;

            } else {
                session()->flash('error', 'Los parámetros del cliente no son válidos.');
                return;
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return;
        }
    }

    // MÉTODOS ESPECÍFICOS PARA LA VISTA PRINCIPAL
    public function buscarClientesFiltroVista(){
        try {
            $buscar = $this->buscar_clientes_search ?? '';

            $this->listaClientesFiltro = DB::table('clientes')
                ->where('cliente_estado_registro','=', 1)
                ->where(function($q) use ($buscar) {
                    $q->where('cliente_codigo_cliente', 'like', '%' . $buscar . '%')
                        ->orWhere('cliente_nombre_cliente', 'like', '%' . $buscar . '%');
                })
                ->limit(10)
                ->get();

            $this->abrirListasCliente = true;

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return;
        }
    }
    public function seleccionar_cliente_vista($id_cliente){
        try {
            $this->abrirListasCliente = false;
            $id_c = base64_decode($id_cliente);

            if ($id_c) {
                $data = DB::table('clientes')
                    ->where('id_cliente', '=', $id_c)
                    ->first();

                // Asignar siempre a la vista principal
                $this->buscar_clientes_search = $data->cliente_codigo_cliente . ' - ' . $data->cliente_nombre_cliente;
                $this->id_cliente_busqueda = $id_c;

            } else {
                session()->flash('error', 'Los parámetros del cliente no son válidos.');
                return;
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return;
        }
    }

    // MÉTODO ESPECÍFICOS PARA LE MODAL DE REGISTRAR PUNTOS MANUALMENTE
    public function buscar_cliente_modal_registrar_puntos_manulmente(){
        try {
            $buscar = $this->buscar_clientes_modal_rpm ?? '';

            $this->listaClientesFiltro = DB::table('clientes')
                ->where('cliente_estado_registro','=', 1)
                ->where(function($q) use ($buscar) {
                    $q->where('cliente_codigo_cliente', 'like', '%' . $buscar . '%')
                        ->orWhere('cliente_nombre_cliente', 'like', '%' . $buscar . '%');
                })
                ->limit(10)
                ->get();

            $this->abrir_modal_rpm = true;

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return;
        }
    }
    public function seleccionar_cliente_modal_registrar_puntos_manualmente($id_cliente){
        try {
            $this->abrir_modal_rpm = false;
            $id_c = base64_decode($id_cliente);

            if ($id_c) {
                $data = DB::table('clientes')
                    ->where('id_cliente', '=', $id_c)
                    ->first();

                // Asignar siempre al modal
                $this->buscar_clientes_modal_rpm = $data->cliente_codigo_cliente . ' - ' . $data->cliente_nombre_cliente;
                $this->id_cliente_rpm = $id_c;

                // Limpiar vendedores seleccionados cuando se cambia de cliente
                $this->vendedores_seleccionados = [];
                $this->vendedor_seleccionado = "";

                // Cargar vendedores del cliente seleccionado
                $this->cargar_vendedores_cliente($id_c);

            } else {
                session()->flash('error', 'Los parámetros del cliente no son válidos.');
                return;
            }
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return;
        }
    }

    public function cargar_vendedores_cliente($id_cliente){
        try {
            $this->vendedores_disponibles = DB::table('vendedores_intranet')
                ->where('id_cliente', '=', $id_cliente)
                ->where('vendedor_intranet_estado', '=', 1)
                ->select('id_vendedor_intranet', 'vendedor_intranet_dni', 'vendedor_intranet_nombre', 'vendedor_intranet_apellido')
                ->get();

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            $this->vendedores_disponibles = [];
            session()->flash('error', 'Error al cargar los vendedores del cliente.');
        }
    }

    public function agregar_vendedor(){
        try {
            if(empty($this->vendedor_seleccionado)){
                return;
            }

            // Verificar si el vendedor ya está seleccionado
            $yaExiste = collect($this->vendedores_seleccionados)->where('id', $this->vendedor_seleccionado)->first();

            if($yaExiste){
                session()->flash('error_modal', 'El vendedor ya está seleccionado.');
                return;
            }

            // Buscar datos del vendedor
            $vendedor = collect($this->vendedores_disponibles)->where('id_vendedor_intranet', $this->vendedor_seleccionado)->first();

            if($vendedor){
                // Agregar vendedor a la lista de seleccionados
                $this->vendedores_seleccionados[] = [
                    'id_vendedor_intranet' => $vendedor->id_vendedor_intranet,
                    'vendedor_intranet_nombre' => $vendedor->vendedor_intranet_nombre,
                    'vendedor_intranet_apellido' => $vendedor->vendedor_intranet_apellido,
                    'vendedor_intranet_dni' => $vendedor->vendedor_intranet_dni,
                    'puntos' => 0
                ];

                // Remover vendedor de la lista disponible
                $this->vendedores_disponibles = collect($this->vendedores_disponibles)
                    ->where('id_vendedor_intranet', '!=', $this->vendedor_seleccionado)
                    ->values()
                    ->toArray();

                // Limpiar selección
                $this->vendedor_seleccionado = "";
            }

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error_modal', 'Error al agregar el vendedor.');
        }
    }

    public function eliminar_vendedor($id_vendedor){
        try {
            // Buscar el vendedor en los seleccionados
            $vendedorEliminado = null;
            foreach($this->vendedores_seleccionados as $index => $vendedor){
                if($vendedor['id_vendedor_intranet'] == $id_vendedor){
                    $vendedorEliminado = $vendedor;
                    unset($this->vendedores_seleccionados[$index]);
                    break;
                }
            }

            // Reindexar el array
            $this->vendedores_seleccionados = array_values($this->vendedores_seleccionados);

            // Volver a agregar el vendedor a la lista disponible
            if($vendedorEliminado && $this->id_cliente_rpm){
                $vendedorBD = DB::table('vendedores_intranet')
                    ->where('id_vendedor_intranet', '=', $id_vendedor)
                    ->where('id_cliente', '=', $this->id_cliente_rpm)
                    ->select('id_vendedor_intranet', 'vendedor_intranet_dni', 'vendedor_intranet_nombre', 'vendedor_intranet_apellido')
                    ->first();

                if($vendedorBD){
                    $this->vendedores_disponibles[] = $vendedorBD;
                }
            }

        } catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error_modal', 'Error al eliminar el vendedor.');
        }
    }

    public function registrar_puntos_manualmente(){
        try {
            $this->validate([
                'id_campania_rpm' => 'required|integer',
                'id_cliente_rpm'  => 'required|integer',
                'punto_detalle_motivo'  => 'required|string',
            ], [
                'id_campania_rpm.required' => 'La campaña es un dato obligatorio.',
                'id_campania_rpm.integer'  => 'El identificador debe ser un número entero.',

                'id_cliente_rpm.required'  => 'El cliente es un dato obligatorio.',
                'id_cliente_rpm.integer'   => 'El identificador debe ser un número entero.',

                'punto_detalle_motivo.required'  => 'El motivo es un dato obligatorio.',
                'punto_detalle_motivo.string'   => 'El motivo debe ser una cadena de texto.',
            ]);

            if (!Gate::allows('registrar_puntos_manualmente')) {
                session()->flash('error_modal', 'No tiene permisos para crear.');
                return;
            }

            // Validar que haya al menos un vendedor seleccionado
            if (empty($this->vendedores_seleccionados) || count($this->vendedores_seleccionados) < 1) {
                session()->flash('error_modal', 'Debe seleccionar al menos un vendedor para registrar puntos.');
                return;
            }

            $microtime = microtime(true);
            DB::beginTransaction();

            // Reusar/crear cabecera "puntos"
            $punto = Punto::where('id_campania', $this->id_campania_rpm)
                ->where('id_cliente',  $this->id_cliente_rpm)
                ->where('punto_estado', 1)
                ->first();

            if (!$punto) {
                $ultimo = Punto::orderBy('id_punto', 'desc')->first();
                $codigo_nuevo = $ultimo ? $ultimo->id_punto + 1 : 1;

                $punto = new Punto();
                $punto->id_users   = Auth::id();
                $punto->id_campania = $this->id_campania_rpm;
                $punto->id_cliente  = $this->id_cliente_rpm;
                $punto->punto_codigo = 'P-000' . $codigo_nuevo;
                $punto->punto_documento_excel = null;

                if ($this->manual_archivo_pdf) {
                    $punto->punto_documento_pdf = $this->general->save_files_campanha($this->manual_archivo_pdf, 'puntos/pdf');
                }

                $punto->punto_microtime = $microtime;
                $punto->punto_estado    = 1;
                $punto->save();
            }

            $id_punto_a_usar = $punto->id_punto;

            // Procesar cada vendedor seleccionado manualmente
            foreach ($this->vendedores_seleccionados as $vendedor) {
                $dni    = $vendedor['vendedor_intranet_dni'] ?? null;
                $rawPts = $vendedor['puntos'] ?? null;

                if (!$dni || $rawPts === null || $rawPts === '') {
                    session()->flash('error_modal', 'Todos los vendedores deben tener puntos asignados.');
                    DB::rollBack();
                    return;
                }

                // ==== Normalización de puntos ingresados manualmente ====
                $s = trim((string)$rawPts);
                if ($s === '') {
                    session()->flash('error_modal', 'Los puntos del vendedor ' . $dni . ' no pueden estar vacíos.');
                    DB::rollBack();
                    return;
                }
                $s = preg_replace('/\s+/', '', $s);

                $hasComma = strpos($s, ',') !== false;
                $hasDot   = strpos($s, '.') !== false;

                if ($hasComma && $hasDot) {
                    $lastComma = strrpos($s, ',');
                    $lastDot   = strrpos($s, '.');
                    if ($lastComma > $lastDot) {
                        // coma como decimal -> quitar puntos (miles) y cambiar coma por punto
                        $s = str_replace('.', '', $s);
                        $s = str_replace(',', '.', $s);
                    } else {
                        // punto como decimal -> quitar comas (miles)
                        $s = str_replace(',', '', $s);
                    }
                } elseif ($hasComma) {
                    // solo comas: decidir si miles o decimal por longitud del último tramo
                    $parts = explode(',', $s);
                    $lastLen = strlen(end($parts));
                    if (count($parts) > 1 && $lastLen === 3) {
                        $s = str_replace(',', '', $s); // miles
                    } else {
                        $s = str_replace(',', '.', $s); // decimal
                    }
                } elseif ($hasDot) {
                    // solo puntos: decidir si miles o decimal
                    $parts = explode('.', $s);
                    $lastLen = strlen(end($parts));
                    if (count($parts) > 1 && $lastLen === 3) {
                        $s = str_replace('.', '', $s); // miles
                    }
                    // si es decimal ya está correcto
                }

                // Validar que quedó como número válido
                if (!preg_match('/^-?\d+(\.\d+)?$/', $s)) {
                    session()->flash('error_modal', 'El formato de puntos del vendedor ' . $dni . ' no es válido.');
                    DB::rollBack();
                    return;
                }

                // Convertir a float
                $puntos = (float)$s;

                // Validar que sea mayor a 0
                if ($puntos <= 0) {
                    session()->flash('error_modal', 'Los puntos del vendedor ' . $dni . ' deben ser mayor a 0.');
                    DB::rollBack();
                    return;
                }

                // Guardar detalle
                $detalle = new Puntodetalle();
                $detalle->id_users = Auth::id();
                $detalle->id_punto = $id_punto_a_usar;
                $detalle->punto_detalle_motivo = $this->punto_detalle_motivo;
                $detalle->punto_detalle_vendedor = $dni;
                $detalle->punto_detalle_punto_ganado = $puntos;
                $detalle->punto_detalle_fecha_registro = now('America/Lima')->toDateString();
                $detalle->punto_detalle_fecha_modificacion = null;
                $detalle->punto_detalle_microtime = $microtime;
                $detalle->punto_detalle_estado  = 1;
                $detalle->save();

                // Sumar puntos al vendedor si existe (incremento atómico)
                $vendedorExiste = DB::table('vendedores_intranet')
                    ->where('vendedor_intranet_dni', $dni)
                    ->where('vendedor_intranet_estado', 1)
                    ->exists();

                if ($vendedorExiste) {
                    DB::table('vendedores_intranet')
                        ->where('vendedor_intranet_dni', $dni)
                        ->where('vendedor_intranet_estado', 1)
                        ->increment('vendedor_intranet_punto', (float)$puntos, [
                            'updated_at' => now('America/Lima')
                        ]);
                }
            }

            DB::commit();
            $this->dispatch('hide_modal_registrar_puntos_manualmente');
            session()->flash('success', 'Puntos guardados correctamente.');
            $this->clear_form();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_modal', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function clear_form(){
        $this->id_punto = "";
        $this->archivo_excel = "";
        $this->archivo_pdf = "";
        $this->buscar_clientes = "";
        $this->id_cliente = "";
        $this->id_campania = "";

        $this->id_campania_rpm = "";
        $this->id_cliente_rpm = "";
        $this->punto_detalle_motivo = "";
        $this->listar_vendedor_cliente = [];
        $this->vendedores_disponibles = [];
        $this->vendedores_seleccionados = [];
        $this->vendedor_seleccionado = "";
        $this->buscar_clientes_modal_rpm = "";

        $this->manual_archivo_pdf = "";
    }

    public function save_carga_excel(){
        try {
            $this->validate([
                'id_campania' => 'required|integer',
                'id_cliente'  => 'required|integer',
            ], [
                'id_campania.required' => 'La campaña es un dato obligatorio.',
                'id_campania.integer'  => 'El identificador debe ser un número entero.',

                'id_cliente.required'  => 'El cliente es un dato obligatorio.',
                'id_cliente.integer'   => 'El identificador debe ser un número entero.',
            ]);

            if (!Gate::allows('save_carga_excel')) {
                session()->flash('error_modal', 'No tiene permisos para crear.');
                return;
            }

            if (!$this->archivo_excel) {
                session()->flash('error', 'El archivo Excel es obligatorio.');
                return;
            }

            $extension = strtolower($this->archivo_excel->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls'])) {
                session()->flash('error_modal', 'El archivo debe ser de tipo Excel (xlsx, xls).');
                return;
            }

            $microtime = microtime(true);
            DB::beginTransaction();

            // Reusar/crear cabecera "puntos"
            $punto = Punto::where('id_campania', $this->id_campania)
                ->where('id_cliente',  $this->id_cliente)
                ->where('punto_estado', 1)
                ->first();

            if (!$punto) {
                $ultimo = Punto::orderBy('id_punto', 'desc')->first();
                $codigo_nuevo = $ultimo ? $ultimo->id_punto + 1 : 1;

                $punto = new Punto();
                $punto->id_users   = Auth::id();
                $punto->id_campania = $this->id_campania;
                $punto->id_cliente  = $this->id_cliente;
                $punto->punto_codigo = 'P-000' . $codigo_nuevo;

                if ($this->archivo_excel) {
                    $punto->punto_documento_excel = $this->general->save_files_campanha($this->archivo_excel, 'puntos/excel');
                }
                if ($this->archivo_pdf) {
                    $punto->punto_documento_pdf = $this->general->save_files_campanha($this->archivo_pdf, 'puntos/pdf');
                }

                $punto->punto_microtime = $microtime;
                $punto->punto_estado    = 1;
                $punto->save();
            }

            $id_punto_a_usar = $punto->id_punto;

            // Procesar Excel
            $spreadsheet = IOFactory::load($this->archivo_excel->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $registros_procesados = 0;
            $registros_ignorados = 0;

            foreach (array_slice($rows, 1) as $row) {
                $dni    = $row[0] ?? null;
                $motivo = $row[1] ?? null;
                $rawPts = $row[2] ?? null;

                if (!$dni || !$motivo || $rawPts === null || $rawPts === '') {
                    continue;
                }

                // VALIDACIÓN DE VINCULACIÓN CON CLIENTE
                $vendedor = DB::table('vendedores_intranet')
                    ->where('vendedor_intranet_dni', $dni)
                    ->where('id_cliente', $this->id_cliente)
                    ->where('vendedor_intranet_estado', 1)
                    ->first();

                // Si no existe el vendedor o no está vinculado al cliente, ignorar registro
                if (!$vendedor) {
                    $registros_ignorados++;
                    continue;
                }

                // ==== Normalización inline de "puntos" ====
                $s = trim((string)$rawPts);
                if ($s === '') { continue; }
                $s = preg_replace('/\s+/', '', $s);

                $hasComma = strpos($s, ',') !== false;
                $hasDot   = strpos($s, '.') !== false;

                if ($hasComma && $hasDot) {
                    $lastComma = strrpos($s, ',');
                    $lastDot   = strrpos($s, '.');
                    if ($lastComma > $lastDot) {
                        // coma como decimal -> quitar puntos (miles) y cambiar coma por punto
                        $s = str_replace('.', '', $s);
                        $s = str_replace(',', '.', $s);
                    } else {
                        // punto como decimal -> quitar comas (miles)
                        $s = str_replace(',', '', $s);
                    }
                } elseif ($hasComma) {
                    // solo comas: decidir si miles o decimal por longitud del último tramo
                    $parts = explode(',', $s);
                    $lastLen = strlen(end($parts));
                    if (count($parts) > 1 && $lastLen === 3) {
                        $s = str_replace(',', '', $s); // miles
                    } else {
                        $s = str_replace(',', '.', $s); // decimal
                    }
                } elseif ($hasDot) {
                    // solo puntos: decidir si miles o decimal
                    $parts = explode('.', $s);
                    $lastLen = strlen(end($parts));
                    if (count($parts) > 1 && $lastLen === 3) {
                        $s = str_replace('.', '', $s); // miles
                    }
                    // si es decimal ya está correcto
                }

                // Validar que quedó como número válido
                if (!preg_match('/^-?\d+(\.\d+)?$/', $s)) {
                    // Si no es interpretable, saltamos la fila
                    continue;
                }

                // Convertir a float (puedes redondear si lo prefieres)
                $puntos = (float)$s;
                // $puntos = round((float)$s, 2); // <- si quieres limitar a 2 decimales

                // Guardar detalle (ya validamos que el vendedor está vinculado al cliente)
                $detalle = new Puntodetalle();
                $detalle->id_users = Auth::id();
                $detalle->id_punto = $id_punto_a_usar;
                $detalle->punto_detalle_motivo = $motivo;
                $detalle->punto_detalle_vendedor = $dni;
                $detalle->punto_detalle_punto_ganado = $puntos;
                $detalle->punto_detalle_fecha_registro = now('America/Lima')->toDateString();
                $detalle->punto_detalle_fecha_modificacion = null;
                $detalle->punto_detalle_microtime = $microtime;
                $detalle->punto_detalle_estado  = 1;
                $detalle->save();

                // Sumar puntos al vendedor (ya sabemos que existe y está vinculado)
                DB::table('vendedores_intranet')
                    ->where('vendedor_intranet_dni', $dni)
                    ->where('id_cliente', $this->id_cliente)
                    ->where('vendedor_intranet_estado', 1)
                    ->increment('vendedor_intranet_punto', (float)$puntos, [
                        'updated_at' => now('America/Lima')
                    ]);

                $registros_procesados++;
            }

            DB::commit();
            $this->dispatch('hide_modal_carga_excel');
            session()->flash('success', 'Archivo(s) procesado(s) correctamente.');
            $this->clear_form();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_modal', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function editar_punto($id_punto){
        $this->id_punto = base64_decode($id_punto);

        if ($this->id_punto) {
            // Obtener los detalles
            $this->listar_detalles = DB::table('puntos_detalles')
                ->where('punto_detalle_estado', '=', 1)
                ->where('id_punto', '=', $this->id_punto)
                ->get();

            // Obtener el registro de punto para saber la campaña y cliente
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

            // Establecer el cliente seleccionado si existe
            if ($punto && $punto->id_cliente) {
                $this->id_cliente = $punto->id_cliente;

                // Obtener datos del cliente para mostrar en el buscador
                $cliente = DB::table('clientes')
                    ->where('id_cliente', $punto->id_cliente)
                    ->where('cliente_estado_registro', 1)
                    ->first();

                if ($cliente) {
                    $this->buscar_clientes = $cliente->cliente_codigo_cliente . ' - ' . $cliente->cliente_nombre_cliente;
                } else {
                    $this->buscar_clientes = "";
                    $this->id_cliente = "";
                }
            } else {
                $this->buscar_clientes = "";
                $this->id_cliente = "";
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

            // Actualizar cabecera si cambió
            if ($this->id_punto && $this->id_campania) {
                DB::table('puntos')
                    ->where('id_punto', $this->id_punto)
                    ->update([
                        'id_campania' => $this->id_campania,
                        'id_cliente'  => $this->id_cliente,
                    ]);
            }

            // ========= Normalizar y validar "puntos" =========
            // Guardaremos los puntos ya normalizados (float) por id_punto_detalle
            $puntos_normalizados = [];

            foreach ($this->editando_registros as $id_registro) {
                // Campos obligatorios
                if (
                    empty($this->datos_edicion[$id_registro]['motivo']) ||
                    empty(trim((string)$this->datos_edicion[$id_registro]['vendedor'])) ||
                    !array_key_exists('puntos', $this->datos_edicion[$id_registro])
                ) {
                    session()->flash('error_moda_editar', 'Todos los campos son obligatorios.');
                    return;
                }

                // === Normalización de comas/puntos (idéntica lógica que en save_carga_excel) ===
                $s = (string)$this->datos_edicion[$id_registro]['puntos'];
                $s = trim($s);
                if ($s === '') {
                    session()->flash('error_moda_editar', 'El campo Puntos es obligatorio.');
                    return;
                }

                // quitar espacios internos
                $s = preg_replace('/\s+/', '', $s);

                $hasComma = strpos($s, ',') !== false;
                $hasDot   = strpos($s, '.') !== false;

                if ($hasComma && $hasDot) {
                    $lastComma = strrpos($s, ',');
                    $lastDot   = strrpos($s, '.');
                    if ($lastComma > $lastDot) {
                        // coma como decimal -> quitar puntos (miles) y cambiar coma por punto
                        $s = str_replace('.', '', $s);
                        $s = str_replace(',', '.', $s);
                    } else {
                        // punto como decimal -> quitar comas (miles)
                        $s = str_replace(',', '', $s);
                    }
                } elseif ($hasComma) {
                    // solo comas: decidir si miles o decimal por longitud del último tramo
                    $parts = explode(',', $s);
                    $lastLen = strlen(end($parts));
                    if (count($parts) > 1 && $lastLen === 3) {
                        $s = str_replace(',', '', $s); // miles
                    } else {
                        $s = str_replace(',', '.', $s); // decimal
                    }
                } elseif ($hasDot) {
                    // solo puntos: decidir si miles o decimal
                    $parts = explode('.', $s);
                    $lastLen = strlen(end($parts));
                    if (count($parts) > 1 && $lastLen === 3) {
                        $s = str_replace('.', '', $s); // miles
                    }
                    // si es decimal ya está correcto
                }

                // Validar número (permite negativos y decimales)
                if (!preg_match('/^-?\d+(\.\d+)?$/', $s)) {
                    session()->flash('error_moda_editar', "El valor de Puntos del registro {$id_registro} no es numérico válido.");
                    return;
                }

                // Convertir a float (si quieres limitar decimales, usa round)
                $puntos_normalizados[$id_registro] = (float)$s;
                // $puntos_normalizados[$id_registro] = round((float)$s, 2);
            }

            DB::beginTransaction();

            $registros_actualizados = 0;

            foreach ($this->editando_registros as $id_punto_detalle) {
                if (!isset($this->datos_edicion[$id_punto_detalle])) {
                    continue;
                }

                // Estado anterior del detalle (lock a nivel fila)
                $registro_anterior = DB::table('puntos_detalles')
                    ->where('id_punto_detalle', $id_punto_detalle)
                    ->where('punto_detalle_estado', 1)
                    ->lockForUpdate()
                    ->first();

                if (!$registro_anterior) {
                    continue;
                }

                $datos = $this->datos_edicion[$id_punto_detalle];
                $dni_anterior = trim($registro_anterior->punto_detalle_vendedor);
                $puntos_anteriores = (float) $registro_anterior->punto_detalle_punto_ganado;

                $dni_nuevo = trim((string)$datos['vendedor']);
                $puntos_nuevos = (float) $puntos_normalizados[$id_punto_detalle];

                // Actualizar el detalle
                $actualizado = DB::table('puntos_detalles')
                    ->where('id_punto_detalle', $id_punto_detalle)
                    ->where('punto_detalle_estado', 1)
                    ->update([
                        'punto_detalle_motivo' => $datos['motivo'],
                        'punto_detalle_vendedor' => $dni_nuevo,
                        'punto_detalle_punto_ganado' => $puntos_nuevos,
                        'punto_detalle_fecha_modificacion' => now('America/Lima')->toDateString(),
                        'updated_at' => now('America/Lima'),
                    ]);

                if (!$actualizado) {
                    continue;
                }

                $registros_actualizados++;

                // === AJUSTE DE PUNTAJE EN vendedores_intranet ===
                if ($dni_nuevo === $dni_anterior) {
                    // Mismo vendedor: ajustar diferencia
                    $diferencia = $puntos_nuevos - $puntos_anteriores;
                    if ($diferencia != 0) {
                        $existeVendedor = DB::table('vendedores_intranet')
                            ->where('vendedor_intranet_dni', $dni_anterior)
                            ->where('vendedor_intranet_estado', 1)
                            ->exists();

                        if ($existeVendedor) {
                            if ($diferencia > 0) {
                                DB::table('vendedores_intranet')
                                    ->where('vendedor_intranet_dni', $dni_anterior)
                                    ->where('vendedor_intranet_estado', 1)
                                    ->increment('vendedor_intranet_punto', $diferencia, [
                                        'updated_at' => now('America/Lima')
                                    ]);
                            } else {
                                DB::table('vendedores_intranet')
                                    ->where('vendedor_intranet_dni', $dni_anterior)
                                    ->where('vendedor_intranet_estado', 1)
                                    ->decrement('vendedor_intranet_punto', abs($diferencia), [
                                        'updated_at' => now('America/Lima')
                                    ]);
                            }
                        }
                    }
                } else {
                    // Cambió el DNI:
                    // 1) Restar al vendedor anterior (si existe)
                    $existeVendedorAnterior = DB::table('vendedores_intranet')
                        ->where('vendedor_intranet_dni', $dni_anterior)
                        ->where('vendedor_intranet_estado', 1)
                        ->exists();

                    if ($existeVendedorAnterior && $puntos_anteriores != 0) {
                        DB::table('vendedores_intranet')
                            ->where('vendedor_intranet_dni', $dni_anterior)
                            ->where('vendedor_intranet_estado', 1)
                            ->decrement('vendedor_intranet_punto', abs($puntos_anteriores), [
                                'updated_at' => now('America/Lima')
                            ]);
                    }

                    // 2) Sumar al vendedor nuevo SOLO si existe
                    $existeVendedorNuevo = DB::table('vendedores_intranet')
                        ->where('vendedor_intranet_dni', $dni_nuevo)
                        ->where('vendedor_intranet_estado', 1)
                        ->exists();

                    if ($existeVendedorNuevo && $puntos_nuevos != 0) {
                        DB::table('vendedores_intranet')
                            ->where('vendedor_intranet_dni', $dni_nuevo)
                            ->where('vendedor_intranet_estado', 1)
                            ->increment('vendedor_intranet_punto', $puntos_nuevos, [
                                'updated_at' => now('America/Lima')
                            ]);
                    }
                    // Si el nuevo DNI no existe, no se crea ni se suma (se guarda igual el detalle)
                }
            }

            DB::commit();

            // Limpiar estado de edición
            $this->editando_registros = [];
            $this->datos_edicion      = [];

            // Recargar tabla
            if ($this->id_punto) {
                $this->listar_detalles = DB::table('puntos_detalles')
                    ->where('punto_detalle_estado', 1)
                    ->where('id_punto', $this->id_punto)
                    ->get();
            }

            $this->dispatch('hide_modal_editar_punto');
            session()->flash('success_modal_editar', "Se actualizaron {$registros_actualizados} registro(s) correctamente.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
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

            // Primero obtener los datos del detalle antes de eliminarlo
            $detalle = DB::table('puntos_detalles')
                ->where('id_punto_detalle', $id_p_de)
                ->where('punto_detalle_estado', 1)
                ->first();

            if (!$detalle) {
                session()->flash('error_moda_editar', 'El registro no existe o ya fue eliminado.');
                return;
            }

            // Restar los puntos del vendedor
            $vendedor = DB::table('vendedores_intranet')
                ->where('vendedor_intranet_dni', $detalle->punto_detalle_vendedor)
                ->where('vendedor_intranet_estado', 1)
                ->first();

            if ($vendedor) {
                $nuevos_puntos = $vendedor->vendedor_intranet_punto - $detalle->punto_detalle_punto_ganado;

                // Asegurarse de que no queden puntos negativos
                $nuevos_puntos = max(0, $nuevos_puntos);

                DB::table('vendedores_intranet')
                    ->where('vendedor_intranet_dni', $detalle->punto_detalle_vendedor)
                    ->where('vendedor_intranet_estado', 1)
                    ->update([
                        'vendedor_intranet_punto' => $nuevos_puntos,
                        'updated_at' => now('America/Lima')
                    ]);
            }

            // Ahora marcar el detalle como eliminado
            $actualizar = DB::table('puntos_detalles')
                ->where('id_punto_detalle', $id_p_de)
                ->where('punto_detalle_estado', 1)
                ->update([
                    'punto_detalle_estado' => 0,
                    'updated_at' => now('America/Lima')
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
                session()->flash('success_moda_editar', 'Registro eliminado correctamente y puntos restados del vendedor.');
            } else {
                DB::rollBack();
                session()->flash('error_moda_editar', 'No se pudo eliminar el registro.');
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

            // Actualizar el estado del punto principal a 0
            $punto_delete = Punto::find($this->id_punto);
            if (!$punto_delete) {
                DB::rollBack();
                session()->flash('error_delete', 'No se encontró el punto especificado.');
                return;
            }

            $punto_delete->punto_estado = 0;
            if (!$punto_delete->save()) {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo actualizar el estado del punto.');
                return;
            }

            // Obtener todos los registros de puntos_detalles con el mismo id_punto
            $puntos_detalles = DB::table('puntos_detalles')
                ->where('id_punto', $this->id_punto)
                ->where('punto_detalle_estado', '=', 1)
                ->lockForUpdate()
                ->get();

            if ($puntos_detalles->isEmpty()) {
                // Si no hay detalles, solo confirmamos el cambio del punto principal
                DB::commit();
                $this->dispatch('hide_modal_delete_punto');
                session()->flash('success', 'Punto eliminado correctamente.');
                return;
            }

            $registros_procesados = 0;

            // Procesar cada registro de puntos_detalles
            foreach ($puntos_detalles as $detalle) {
                $dni_vendedor = trim($detalle->punto_detalle_vendedor);
                $puntos_ganados = (float) $detalle->punto_detalle_punto_ganado;

                // Verificar si existe el vendedor en vendedores_intranet
                $vendedor = DB::table('vendedores_intranet')
                    ->where('vendedor_intranet_dni', $dni_vendedor)
                    ->where('vendedor_intranet_estado', '=', 1)
                    ->lockForUpdate()
                    ->first();

                if ($vendedor && $puntos_ganados != 0) {
                    // Restar los puntos ganados del vendedor
                    $puntos_vendedor_actual = (float) $vendedor->vendedor_intranet_punto;
                    $nuevos_puntos = $puntos_vendedor_actual - $puntos_ganados;

                    $actualizado = DB::table('vendedores_intranet')
                        ->where('vendedor_intranet_dni', $dni_vendedor)
                        ->where('vendedor_intranet_estado', '=', 1)
                        ->update([
                            'vendedor_intranet_punto' => $nuevos_puntos,
                            'updated_at' => now('America/Lima')
                        ]);

                    if (!$actualizado) {
                        DB::rollBack();
                        session()->flash('error_delete', 'Error al actualizar los puntos del vendedor con DNI: ' . $dni_vendedor);
                        return;
                    }
                }

                // Cambiar el estado del detalle a 0
                $detalle_actualizado = DB::table('puntos_detalles')
                    ->where('id_punto_detalle', $detalle->id_punto_detalle)
                    ->where('punto_detalle_estado', '=', 1)
                    ->update([
                        'punto_detalle_estado' => 0,
                        'updated_at' => now('America/Lima')
                    ]);

                if ($detalle_actualizado) {
                    $registros_procesados++;
                }
            }

            DB::commit();
            $this->dispatch('hide_modal_delete_punto');
            session()->flash('success', "Punto eliminado correctamente. Se procesaron {$registros_procesados} detalle(s) y se actualizaron los puntos de los vendedores correspondientes.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al eliminar el registro. Por favor, inténtelo nuevamente.');
        }
    }

    public function generar_historial_puntos($id_cl){
        try {
            $id_cliente = $id_cl;

            $resultado_hp = $this->punto->obtener_historial_puntos($id_cliente, $this->id_campania_busqueda);





            $spreadsheet = new Spreadsheet();
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Historial Puntos');

            $row = 1;

            // Título de la campaña
            $sheet1->setCellValue('A'.$row, strtoupper('HISTORIAL DE REGISTRO DE PUNTOS'));
            $ultima_columna = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($total_columnas);
            $sheet1->mergeCells('A'.$row.':'.$ultima_columna.$row);
            $sheet1->getStyle('A'.$row)->getFont()->setBold(true);
            $sheet1->getStyle('A'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++;
            $row++; // Línea en blanco

            // Encabezados fijos
            $sheet1->setCellValue('A'.$row, 'ZONA');
            $sheet1->setCellValue('B'.$row, 'CÓDIGO CLIENTE');
            $sheet1->setCellValue('C'.$row, 'CLIENTE');
            $sheet1->setCellValue('D'.$row, 'CAMPAÑA');
            $sheet1->setCellValue('E'.$row, 'DNI DEL VENDEDOR DE CLIENTE');
            $sheet1->setCellValue('F'.$row, 'VENDEDOR DE CLIENTE');
            $sheet1->setCellValue('F'.$row, 'TOTAL PUNTOS GANADOS');






            $sheet1->getColumnDimension('A')->setWidth(15);
            $sheet1->getColumnDimension('B')->setWidth(20);
            $sheet1->getColumnDimension('C')->setWidth(50);
            $sheet1->getColumnDimension('D')->setWidth(30);
            $sheet1->getColumnDimension('E')->setWidth(22);
            $sheet1->getColumnDimension('F')->setWidth(22);



            // Formatear el nombre del archivo Excel
            $nombre_cliente = str_replace(' ', '_', $reporte_cliente->cliente_nombre_cliente ?: 'cliente');
            $nombre_excel = sprintf("detalle_cliente_%s.xlsx", $nombre_cliente);

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
            session()->flash('error', 'Ocurrió un error al generar el Excel. Por favor, inténtelo nuevamente.');
        }
    }

}
