<?php

namespace App\Livewire\Gestiontransporte;

use App\Models\General;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\Transportista;
use App\Models\Ubigeo;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Transportistas extends Component
{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $general;
    private $transportistas;
    private $ubigeo;
    private $vehiculo;
    /* ATRIBUTOS PARA DATATABLES */
    public $search_transportistas;
    public $pagination_transportistas = 10;

    /* FIN ATRIBUTOS PARA DATATABLES */

    /* ATRIBUTOS PARA GUARDAR TRANSPORTISTAS */
    public $id_transportistas = "";
//    public $id_tipo_servicios = "";
    public $transportista_ruc = "";
    public $transportista_razon_social = "";
    public $transportista_nom_comercial = "";
    public $transportista_direccion = "";
    public $transportista_estado = "";
    public $messageDeleteTranspor = "";
    public $messageConsulta = "";

    // CONTACTOS
    public $transportista_contacto_uno_comercial_operativo = "";
    public $transportista_contacto_uno_cargo = "";
    public $transportista_contacto_uno_correo = "";
    public $transportista_contacto_uno_telefono = "";
    public $transportista_contacto_dos_contabilidad_pago = "";
    public $transportista_contacto_dos_cargo = "";
    public $transportista_contacto_dos_correo = "";
    public $transportista_contacto_dos_telefono = "";
    public $transportista_contacto_tres = "";
    public $transportista_contacto_tres_cargo = "";
    public $transportista_contacto_tres_correo = "";
    public $transportista_contacto_tres_telefono = "";


    // ACUERDOS COMERCIALES BASE
    public $transportista_conformidad_factura = "";
    public $transportista_modo_pago_factura = "";
    public $transportista_dias_credito_factura = "";
    public $transportista_referencia_acuerdo_comercial = "";
    public $transportista_garantias_servicio = "";

//    public $urlActual;
    /* FIN  ATRIBUTOS PARA GUARDAR TRANSPORTISTAS */

    public function __construct(){
        $this->logs = new Logs();
        $this->transportistas = new Transportista();
        $this->ubigeo = new Ubigeo();
        $this->general = new General();
        $this->vehiculo = new Vehiculo();
    }
//
    public function render(){
        $transportistas = $this->transportistas->listar_transportistas_new($this->search_transportistas,$this->pagination_transportistas);
        return view('livewire.gestiontransporte.transportistas',compact('transportistas'));
    }

    public function clear_form_transportistas(){
        $this->id_transportistas = "";
        $this->transportista_ruc = "";
        $this->transportista_razon_social = "";
        $this->transportista_nom_comercial = "";
        $this->transportista_direccion = "";
        $this->transportista_contacto_uno_comercial_operativo = "";
        $this->transportista_contacto_uno_cargo = "";
        $this->transportista_contacto_uno_correo = "";
        $this->transportista_contacto_uno_telefono = "";
        $this->transportista_contacto_dos_contabilidad_pago = "";
        $this->transportista_contacto_dos_cargo = "";
        $this->transportista_contacto_dos_correo = "";
        $this->transportista_contacto_dos_telefono = "";
        $this->transportista_contacto_tres = "";
        $this->transportista_contacto_tres_cargo = "";
        $this->transportista_contacto_tres_correo = "";
        $this->transportista_contacto_tres_telefono = "";
        $this->transportista_conformidad_factura = "";
        $this->transportista_modo_pago_factura = "";
        $this->transportista_dias_credito_factura = "";
        $this->transportista_referencia_acuerdo_comercial = "";
        $this->transportista_garantias_servicio = "";
        $this->transportista_estado = "";
        $this->dispatch('select_ubigeo',['text' => null]);
    }
    public function consultDocument(){
        try {
            $this->messageConsulta = "";
            $this->transportista_razon_social = "";
            $this->transportista_direccion = "";
            $resultado = $this->general->consultar_documento(4,$this->transportista_ruc);
            if ($resultado['result']['tipo'] == 'success'){
                $this->transportista_razon_social = $resultado['result']['name'];
                $this->transportista_direccion = $resultado['result']['direccion'];
            }
            $this->messageConsulta = array('mensaje'=>$resultado['result']['mensaje'],'type'=>$resultado['result']['tipo']);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error_modal', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
            return;
        }
    }

    public function edit_data($id){
        $transportistasEdit = Transportista::find(base64_decode($id));
        if ($transportistasEdit){
            $this->transportista_ruc = $transportistasEdit->transportista_ruc;
            $this->transportista_razon_social = $transportistasEdit->transportista_razon_social;
            $this->transportista_nom_comercial = $transportistasEdit->transportista_nom_comercial;
            $this->transportista_direccion = $transportistasEdit->transportista_direccion;
            // CONTACTO
            $this->transportista_contacto_uno_comercial_operativo = $transportistasEdit->transportista_contacto_uno_comercial_operativo;
            $this->transportista_contacto_uno_cargo = $transportistasEdit->transportista_contacto_uno_cargo;
            $this->transportista_contacto_uno_correo = $transportistasEdit->transportista_contacto_uno_correo;
            $this->transportista_contacto_uno_telefono = $transportistasEdit->transportista_contacto_uno_telefono;
            // ACUERDO COMERCIAL
            $this->transportista_conformidad_factura = $transportistasEdit->transportista_conformidad_factura;
            $this->transportista_modo_pago_factura = $transportistasEdit->transportista_modo_pago_factura;
            $this->transportista_dias_credito_factura = $transportistasEdit->transportista_dias_credito_factura;
            $this->transportista_referencia_acuerdo_comercial = $transportistasEdit->transportista_referencia_acuerdo_comercial;
            $this->transportista_garantias_servicio = $transportistasEdit->transportista_garantias_servicio;
            $this->id_transportistas = $transportistasEdit->id_transportistas;
        }
    }

    public function saveTransportista(){
        try {
            $this->validate([
                'transportista_ruc' => 'required|size:11',
                'transportista_razon_social' => 'required|string',
                'transportista_nom_comercial' => 'required|string',
                'transportista_direccion' => 'required|string',

                // CONTACTO 1
                'transportista_contacto_uno_comercial_operativo' => 'required|string',
                'transportista_contacto_uno_cargo' => 'required|string',
                'transportista_contacto_uno_correo' => 'required|email|max:200',
                'transportista_contacto_uno_telefono' => 'required|digits:9',

                // CONTACTO 2
                'transportista_contacto_dos_contabilidad_pago' => 'nullable|string',
                'transportista_contacto_dos_cargo' => 'nullable|string',
                'transportista_contacto_dos_correo' => 'nullable|email|max:200',
                'transportista_contacto_dos_telefono' => 'nullable|digits:9',

                // CONTACTO 3
                'transportista_contacto_tres' => 'nullable|string',
                'transportista_contacto_tres_cargo' => 'nullable|string',
                'transportista_contacto_tres_correo' => 'nullable|email|max:200',
                'transportista_contacto_tres_telefono' => 'nullable|digits:9',

                //ACUERDO COMERCIAL
                'transportista_conformidad_factura' => 'required|integer',
                'transportista_modo_pago_factura' => 'required|integer',
                'transportista_dias_credito_factura' => 'nullable|integer|required_if:transportista_modo_pago_factura,2',
                'transportista_referencia_acuerdo_comercial' => 'required|string',
                'transportista_garantias_servicio' => 'required|string',

                'transportista_estado' => 'nullable|integer',
                'id_transportistas' => 'nullable|integer',
            ], [
                'transportista_ruc.required' => 'El RUC es obligatorio',
                'transportista_ruc.string' => 'El RUC debe ser una cadena de texto.',
                'transportista_ruc.size' => 'El número RUC debe tener exactamente 11 caracteres.',

                'transportista_razon_social.required' => 'La razon social es obligatorio.',
                'transportista_razon_social.string' => 'La razon social debe ser una cadena de texto.',

                'transportista_nom_comercial.required' => 'La dirección es obligatorio.',
                'transportista_nom_comercial.string' => 'La dirección debe ser una cadena de texto.',

                'transportista_direccion.required' => 'El nombre comercial es obligatorio.',
                'transportista_direccion.string' => 'El nombre comercial debe ser una cadena de texto.',

                // CONTACTO 1
                'transportista_contacto_uno_comercial_operativo.required' => 'El contacto 1 es obligatorio.',
                'transportista_contacto_uno_comercial_operativo.string' => 'El contacto 1 debe ser una cadena de texto.',

                'transportista_contacto_uno_cargo.required' => 'El cargo 1 es obligatorio.',
                'transportista_contacto_uno_cargo.string' => 'El cargo 1 debe ser una cadena de texto.',

                'transportista_contacto_uno_correo.required' => 'El correo electrónico 1 es obligatorio.',
                'transportista_contacto_uno_correo.email' => 'El correo electrónico 1 debe ser un email válido.',

                'transportista_contacto_uno_telefono.required' => 'El número de teléfono 1 es obligatorio.',
                'transportista_contacto_uno_telefono.digits' => 'El número de teléfono 1 debe tener exactamente 9 caracteres.',

                // CONTACTO 2
                'transportista_contacto_dos_contabilidad_pago.string' => 'El contacto 2 debe ser una cadena de texto.',

                'transportista_contacto_dos_cargo.string' => 'El cargo 2 debe ser una cadena de texto.',

                'transportista_contacto_dos_correo.email' => 'El correo electrónico 2 debe ser un email válido.',

                'transportista_contacto_dos_telefono.digits' => 'El número de teléfono 2 debe tener exactamente 9 caracteres.',

                // CONTACTO 3
                'transportista_contacto_tres.string' => 'El contacto 3 debe ser una cadena de texto.',

                'transportista_contacto_tres_cargo.string' => 'El cargo 3 debe ser una cadena de texto.',

                'transportista_contacto_tres_correo.email' => 'El correo electrónico 3 debe ser un email válido.',

                'transportista_contacto_tres_telefono.digits' => 'El número de teléfono 3 debe tener exactamente 9 caracteres.',

                // ACUERDO COMERCIAL
                'transportista_conformidad_factura.required' => 'La conformidad de factura es obligatorio.',
                'transportista_conformidad_factura.integer' => 'La conformidad debe ser un número entero.',

                'transportista_modo_pago_factura.required' => 'El modo de pago es obligatorio.',
                'transportista_modo_pago_factura.integer' => 'El modo de pago debe ser un número entero.',

                'transportista_dias_credito_factura.required_if' => 'El campo días de crédito de factura es obligatorio cuando el modo de pago es crédito.',
                'transportista_dias_credito_factura.integer' => 'El crédito de factura debe ser un número entero.',

                'transportista_referencia_acuerdo_comercial.required' => 'La referencia de acuerdo comercial es obligatorio.',
                'transportista_referencia_acuerdo_comercial.string' => 'La referencia de acuerdo comercial debe ser una cadena de texto.',

                'transportista_garantias_servicio.required' => 'La garantía de servicio es obligatorio.',
                'transportista_garantias_servicio.string' => 'La garantía de servicio debe ser una cadena de texto.',

                'transportista_estado.integer' => 'El estado debe ser un número entero.',

                'id_transportistas.integer' => 'El identificador debe ser un número entero.',
            ]);

            if (!$this->id_transportistas) { // INSERT
                if (!Gate::allows('create_transportistas')) {
                    session()->flash('error_modal', 'No tiene permisos para crear.');
                    return;
                }

                $validar = DB::table('transportistas')->where('transportista_ruc', '=',$this->transportista_ruc)->exists();
                if (!$validar){
                    $microtime = microtime(true);
                    DB::beginTransaction();
                    $transportistas_save = new Transportista();
                    $transportistas_save->id_users = Auth::id();
                    $transportistas_save->transportista_ruc = $this->transportista_ruc;
                    $transportistas_save->transportista_razon_social = $this->transportista_razon_social;
                    $transportistas_save->transportista_nom_comercial = $this->transportista_nom_comercial;
                    $transportistas_save->transportista_direccion = $this->transportista_direccion;
                    // CONTACTO 1
                    $transportistas_save->transportista_contacto_uno_comercial_operativo = $this->transportista_contacto_uno_comercial_operativo;
                    $transportistas_save->transportista_contacto_uno_cargo = $this->transportista_contacto_uno_cargo;
                    $transportistas_save->transportista_contacto_uno_correo = $this->transportista_contacto_uno_correo;
                    $transportistas_save->transportista_contacto_uno_telefono = $this->transportista_contacto_uno_telefono;
                    // CONTACTO 2
                    $transportistas_save->transportista_contacto_dos_contabilidad_pago = $this->transportista_contacto_dos_contabilidad_pago ?? null;
                    $transportistas_save->transportista_contacto_dos_cargo = $this->transportista_contacto_dos_cargo ?? null;
                    $transportistas_save->transportista_contacto_dos_correo = $this->transportista_contacto_dos_correo ?? null;
                    $transportistas_save->transportista_contacto_dos_telefono = $this->transportista_contacto_dos_telefono ?? null;
                    // CONTACTO 3
                    $transportistas_save->transportista_contacto_tres = $this->transportista_contacto_tres ?? null;
                    $transportistas_save->transportista_contacto_tres_cargo = $this->transportista_contacto_tres_cargo ?? null;
                    $transportistas_save->transportista_contacto_tres_correo = $this->transportista_contacto_tres_correo ?? null;
                    $transportistas_save->transportista_contacto_tres_telefono = $this->transportista_contacto_tres_telefono ?? null;
                    // ACUERDO COMERCIAL
                    $transportistas_save->transportista_conformidad_factura = $this->transportista_conformidad_factura;
                    $transportistas_save->transportista_modo_pago_factura = $this->transportista_modo_pago_factura;
                    $transportistas_save->transportista_dias_credito_factura = $this->transportista_dias_credito_factura ?? null;
                    $transportistas_save->transportista_referencia_acuerdo_comercial = $this->transportista_referencia_acuerdo_comercial;
                    $transportistas_save->transportista_garantias_servicio = $this->transportista_garantias_servicio;

                    $transportistas_save->transportista_estado = 1;
                    $transportistas_save->transportista_microtime = $microtime;

                    if ($transportistas_save->save()) {
                        DB::commit();
                        $this->dispatch('hideModal');
                        session()->flash('success', 'Registro guardado correctamente.');

                    } else {
                        DB::rollBack();
                        session()->flash('error_modal', 'Ocurrió un error al guardar el registro.');
                        return;
                    }
                } else{
                    session()->flash('error_modal', 'El RUC ingresado ya está registrado. Por favor, verifica los datos o ingresa un RUC diferente.');
                    return;
                }
            } else {
                if (!Gate::allows('update_transportistas')) {
                    session()->flash('error_modal', 'No tiene permisos para actualizar este registro.');
                    return;
                }

                $validar_update = DB::table('transportistas')
                    ->where('id_transportistas', '<>',$this->id_transportistas)
                    ->where('transportista_ruc', '=',$this->transportista_ruc)
                    ->exists();
                if (!$validar_update){
                    DB::beginTransaction();
                    $transportistas_update = Transportista::findOrFail($this->id_transportistas);
                    $transportistas_update->transportista_ruc = $this->transportista_ruc;
                    $transportistas_update->transportista_razon_social = $this->transportista_razon_social;
                    $transportistas_update->transportista_nom_comercial = $this->transportista_nom_comercial;
                    $transportistas_update->transportista_direccion = $this->transportista_direccion;
                    // CONTACTO 1
                    $transportistas_update->transportista_contacto_uno_comercial_operativo = $this->transportista_contacto_uno_comercial_operativo;
                    $transportistas_update->transportista_contacto_uno_cargo = $this->transportista_contacto_uno_cargo;
                    $transportistas_update->transportista_contacto_uno_correo = $this->transportista_contacto_uno_correo;
                    $transportistas_update->transportista_contacto_uno_telefono = $this->transportista_contacto_uno_telefono;
                    // CONTACTO 2
                    $transportistas_update->transportista_contacto_dos_contabilidad_pago = $this->transportista_contacto_dos_contabilidad_pago ?? null;
                    $transportistas_update->transportista_contacto_dos_cargo = $this->transportista_contacto_dos_cargo ?? null;
                    $transportistas_update->transportista_contacto_dos_correo = $this->transportista_contacto_dos_correo ?? null;
                    $transportistas_update->transportista_contacto_dos_telefono = $this->transportista_contacto_dos_telefono ?? null;
                    // CONTACTO 3
                    $transportistas_update->transportista_contacto_tres = $this->transportista_contacto_tres ?? null;
                    $transportistas_update->transportista_contacto_tres_cargo = $this->transportista_contacto_tres_cargo ?? null;
                    $transportistas_update->transportista_contacto_tres_correo = $this->transportista_contacto_tres_correo ?? null;
                    $transportistas_update->transportista_contacto_tres_telefono = $this->transportista_contacto_tres_telefono ?? null;
                    // ACUERDO COMERCIAL
                    $transportistas_update->transportista_conformidad_factura = $this->transportista_conformidad_factura;
                    $transportistas_update->transportista_modo_pago_factura = $this->transportista_modo_pago_factura;
                    $transportistas_update->transportista_dias_credito_factura = $this->transportista_dias_credito_factura  ?? null;
                    $transportistas_update->transportista_referencia_acuerdo_comercial = $this->transportista_referencia_acuerdo_comercial;
                    $transportistas_update->transportista_garantias_servicio = $this->transportista_garantias_servicio;


                    if (!$transportistas_update->save()) {
                        session()->flash('error_modal', 'No se pudo actualizar el registro.');
                        return;
                    }
                    DB::commit();
                    $this->dispatch('hideModal');
                    session()->flash('success', 'Registro actualizado correctamente.');
                } else{
                    session()->flash('error_modal', 'El RUC ingresado ya está registrado. Por favor, verifica los datos o ingresa un RUC diferente.');
                    return;
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_modal', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }

    public function btn_disable($id_transpor,$esta){
        $id = base64_decode($id_transpor);
        $status = $esta;
        if ($id){
            $this->id_transportistas = $id;
            $this->transportista_estado = $status;
            if ($status == 0){
                $this->messageDeleteTranspor = "¿Está seguro que desea deshabilitar este registro?";
            }else{
                $this->messageDeleteTranspor = "¿Está seguro que desea habilitar este registro?";
            }
        }
    }

    public function disable_transportistas(){
        try {

            if (!Gate::allows('disable_transportistas')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados de este registro.');
                return;
            }


            $this->validate([
                'id_transportistas' => 'required|integer',
                'transportista_estado' => 'required|integer',
            ], [
                'id_transportistas.required' => 'El identificador es obligatorio.',
                'id_transportistas.integer' => 'El identificador debe ser un número entero.',

                'transportista_estado.required' => 'El estado es obligatorio.',
                'transportista_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $transportistasDelete = Transportista::find($this->id_transportistas);
            $transportistasDelete->transportista_estado = $this->transportista_estado;
            if ($transportistasDelete->save()) {
                DB::commit();
                $this->dispatch('hideModalDelete');
                if ($this->transportista_estado == 0){
                    session()->flash('success', 'Registro deshabilitado correctamente.');
                }else{
                    session()->flash('success', 'Registro habilitado correctamente.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo cambiar el estado del registro.');
                return;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }

    public function limpiar_nombre_convenio(){
        $this->dispatch('limpiar_nombre_convenio');
    }

    public function descargar_transportistas_excel(){
        try {
            if (!Gate::allows('descargar_transportistas_excel')) {
                session()->flash('error', 'No tiene permisos para descargar.');
                return;
            }

            $transportistas_resultado = $this->transportistas->obtener_transportistas_excel();

            $spreadsheet = new Spreadsheet();
            $sheet1 = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Transportistas');

            $row = 1;

            // Título principal 'TRANSPORTISTAS REGISTRADOS' desde la columna A hasta T
            $sheet1->mergeCells('A'.$row.':U'.$row);
            $sheet1->setCellValue('A'.$row, 'TRANSPORTISTAS REGISTRADOS');

            // Estilo para el título
            $sheet1->getStyle('A'.$row)->getFont()->setBold(true)->setSize(14);
            $sheet1->getStyle('A'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $row++; // Incrementar fila para los encabezados

            // Encabezados de las columnas
            $sheet1->setCellValue('A'.$row, 'N° ');
            $sheet1->setCellValue('B'.$row, 'RAZÓN SOCIAL');
            $sheet1->setCellValue('C'.$row, 'NOMBRE COMERCIAL');
            $sheet1->setCellValue('D'.$row, 'DIRECCIÓN');
            //CONTACTO 1
            $sheet1->setCellValue('E'.$row, 'CONTACTO 1');
            $sheet1->setCellValue('F'.$row, 'CONTACTO 1 CARGO');
            $sheet1->setCellValue('G'.$row, 'CONTACTO 1 CORREO');
            $sheet1->setCellValue('H'.$row, 'CONTACTO 1 TELÉFONO');
            //CONTACTO 2
            $sheet1->setCellValue('I'.$row, 'CONTACTO 2');
            $sheet1->setCellValue('J'.$row, 'CONTACTO 2 CARGO');
            $sheet1->setCellValue('K'.$row, 'CONTACTO 2 CORREO');
            $sheet1->setCellValue('L'.$row, 'CONTACTO 2 TELÉFONO');
            //CONTACTO 3
            $sheet1->setCellValue('M'.$row, 'CONTACTO 3');
            $sheet1->setCellValue('N'.$row, 'CONTACTO 3 CARGO');
            $sheet1->setCellValue('O'.$row, 'CONTACTO 3 CORREO');
            $sheet1->setCellValue('P'.$row, 'CONTACTO 3 TELÉFONO');
            // ACUERDO COMERCIAL
            $sheet1->setCellValue('Q'.$row, 'CONFORMIDAD DE FACTURA');
            $sheet1->setCellValue('R'.$row, 'MODO DE PAGO');
            $sheet1->setCellValue('S'.$row, 'DÍAS DE CRÉDITO');
            $sheet1->setCellValue('T'.$row, 'REFERENCIA DE ACUERDO');
            $sheet1->setCellValue('U'.$row, 'GARANTÍAS DE SERVICIO');

            // Configurar ancho de columnas fijas
            $sheet1->getColumnDimension('A')->setWidth(5);
            $sheet1->getColumnDimension('B')->setWidth(30);
            $sheet1->getColumnDimension('C')->setWidth(25);
            $sheet1->getColumnDimension('D')->setWidth(70);
            //CONTACTO 1
            $sheet1->getColumnDimension('E')->setWidth(25);
            $sheet1->getColumnDimension('F')->setWidth(15);
            $sheet1->getColumnDimension('G')->setWidth(25);
            $sheet1->getColumnDimension('H')->setWidth(15);
            //CONTACTO 2
            $sheet1->getColumnDimension('I')->setWidth(25);
            $sheet1->getColumnDimension('J')->setWidth(15);
            $sheet1->getColumnDimension('K')->setWidth(25);
            $sheet1->getColumnDimension('L')->setWidth(15);
            //CONTACTO 3
            $sheet1->getColumnDimension('M')->setWidth(25);
            $sheet1->getColumnDimension('N')->setWidth(15);
            $sheet1->getColumnDimension('O')->setWidth(25);
            $sheet1->getColumnDimension('P')->setWidth(15);
            // ACUERDO COMERCIAL
            $sheet1->getColumnDimension('Q')->setWidth(22);
            $sheet1->getColumnDimension('R')->setWidth(22);
            $sheet1->getColumnDimension('S')->setWidth(18);
            $sheet1->getColumnDimension('T')->setWidth(30);
            $sheet1->getColumnDimension('U')->setWidth(30);

            // Estilo para los encabezados
            $sheet1->getStyle('A'.$row.':U'.$row)->getFont()->setBold(true);
            $sheet1->getStyle('A'.$row.':U'.$row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E6E6FA');

            $row++; // Incrementar fila para comenzar con los datos
            $conteo = 1;

            // Llenar datos de los transportistas
            foreach ($transportistas_resultado as $transportista) {
                $sheet1->setCellValue('A'.$row, $conteo);
                $sheet1->setCellValue('B'.$row, $transportista->transportista_razon_social ?? '-');
                $sheet1->setCellValue('C'.$row, $transportista->transportista_nom_comercial ?? '-');
                $sheet1->setCellValue('D'.$row, $transportista->transportista_direccion ?? '-');
                //CONTACTO 1
                $sheet1->setCellValue('E'.$row, $transportista->transportista_contacto_uno_comercial_operativo ?? '-');
                $sheet1->setCellValue('F'.$row, $transportista->transportista_contacto_uno_cargo ?? '-');
                $sheet1->setCellValue('G'.$row, $transportista->transportista_contacto_uno_correo ?? '-');
                $sheet1->setCellValue('H'.$row, $transportista->transportista_contacto_uno_telefono ?? '-');
                //CONTACTO 2
                $sheet1->setCellValue('I'.$row, $transportista->transportista_contacto_dos_contabilidad_pago ?? '-');
                $sheet1->setCellValue('J'.$row, $transportista->transportista_contacto_dos_cargo ?? '-');
                $sheet1->setCellValue('K'.$row, $transportista->transportista_contacto_dos_correo ?? '-');
                $sheet1->setCellValue('L'.$row, $transportista->transportista_contacto_dos_telefono ?? '-');
                //CONTACTO 3
                $sheet1->setCellValue('M'.$row, $transportista->transportista_contacto_tres ?? '-');
                $sheet1->setCellValue('N'.$row, $transportista->transportista_contacto_tres_cargo ?? '-');
                $sheet1->setCellValue('O'.$row, $transportista->transportista_contacto_tres_correo ?? '-');
                $sheet1->setCellValue('P'.$row, $transportista->transportista_contacto_tres_telefono ?? '-');
                // ACUERDO COMERCIAL
                $conformidad_factura = "";
                if ($transportista->transportista_conformidad_factura == 1){
                    $conformidad_factura = "Anticipado";
                } elseif ($transportista->transportista_conformidad_factura == 2){
                    $conformidad_factura = "Después de Entrega";
                } else{
                    $conformidad_factura = "-";
                }

                $pago_factura = "";
                if ($transportista->transportista_modo_pago_factura == 1){
                    $pago_factura = "Contado";
                } elseif ($transportista->transportista_modo_pago_factura == 2){
                    $pago_factura = "Crédito";
                } else {
                    $pago_factura = "-";
                }

                $dias_credito = "";
                if ($transportista->transportista_dias_credito_factura == 1){
                    $dias_credito = "7 Días";
                } elseif ($transportista->transportista_dias_credito_factura == 2){
                    $dias_credito = "15 Días";
                }  elseif ($transportista->transportista_dias_credito_factura == 3){
                    $dias_credito = "30 Días";
                }  elseif ($transportista->transportista_dias_credito_factura == 4){
                    $dias_credito = "45 Días";
                } else {
                    $dias_credito = "-";
                }


                $sheet1->setCellValue('Q'.$row, $conformidad_factura);
                $sheet1->setCellValue('R'.$row, $pago_factura);
                $sheet1->setCellValue('S'.$row, $dias_credito);
                $sheet1->setCellValue('T'.$row, $transportista->transportista_referencia_acuerdo_comercial ?? '-');
                $sheet1->setCellValue('U'.$row, $transportista->transportista_garantias_servicio ?? '-');

                $conteo++;
                $row++;
            }

            // Generar nombre del archivo
            $fecha_actual = date('Y-m-d_H-i-s');
            $nombre_excel = 'transportistas_registrados_' . $fecha_actual . '.xlsx';

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
        }
    }

}
