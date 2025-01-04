<?php

namespace App\Livewire\Configuracion;

use App\Livewire\Intranet\Navegation;
use App\Models\Empresa;
use App\Models\General;
use App\Models\Logs;
use App\Models\Menu;
use App\Models\Ubigeo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Empresas extends Component
{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads; // Usa el trait aquí

    private $logs;
    private $ubigeos;
    private $general;
    private $empresa;
    public function __construct()
    {
        $this->logs = new Logs();
        $this->ubigeos = new Ubigeo();
        $this->general = new General();
        $this->empresa = new Empresa();
    }
    /* DATOS DEL FORMULARIO */
    public $ruc;
    public $trade_name; // NOMBRE COMERCIAL
    public $company_name; // RAZON SOCIAL
    public $ubigeo;
    public $tax_address; // DIRECCION FISCAL
    public $phone1;
    public $phone2;
    public $email1;
    public $email2;
    public $description;
    public $secondary_sun_user; // USUARIOS SECUNDARIO
    public $treble_clef; // CLAVO DEL USUARTIOS SECUNDARIO
    public $certificate;
    public $certificate_key;
    public $logo;
    public $id_empresa;
    public $produccion = false;
    public $typeBoleta = 1;
    public $statusEmpresas = "";
    public $messageDeleteEmpresas = "";
    public $messageConsulta = "";
    public $id_empresa_delete = "";
    /* DATOS DE LA TABLA */
    public $search_empresas;
    public $pagination_empresas = 10;

    #[On('refresh_sidebar_menu')]
    public function render()
    {
        $ubigeos_ = $this->ubigeos->listar_ubigeos();
        $empresas = $this->empresa->listar_empresa_livewire($this->search_empresas,$this->pagination_empresas);
        return view('livewire.configuracion.empresas',compact('ubigeos_','empresas'));
    }

    public function clear_form_empresa(){
        try {
            $this->reset('ruc',
                'trade_name',
                'company_name',
                'ubigeo',
                'tax_address',
                'phone1',
                'phone2',
                'email1',
                'email2',
                'description',
                'secondary_sun_user',
                'treble_clef',
                'certificate',
                'certificate_key',
                'logo',
                'id_empresa',
                'produccion',
                'typeBoleta'
            );
            $this->dispatch('updateUserImagePreview', ['image' => asset('assets/images/faces/1.jpg')]);
            $this->dispatch('select_ubigeo',['text' => null]);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
        }
    }
    public function saveEmpresas(){
        try {
            $this->validate([
                'ruc' => 'required|integer|digits:11', // Debe ser número y tener solo 11 dígitos
                'ubigeo' => 'required|integer',
                'company_name' => 'required|string', // Razón social
                'tax_address' => 'nullable|string', // Domicilio fiscal
                'trade_name' => 'required|string', // Nombre comercial
                'phone1' => 'nullable|digits:9', // Teléfono 1, debe tener 11 dígitos
                'phone2' => 'nullable|digits:9', // Teléfono 2, debe tener 11 dígitos
                'email1' => 'nullable|email', // Email 1, debe ser un correo válido
                'email2' => 'nullable|email', // Email 2, debe ser un correo válido
                'description' => 'nullable|string', // Descripción
                'secondary_sun_user' => 'nullable|string', // Usuario SOL secundario
                'treble_clef' => 'nullable|string', // Clave SOL
                'certificate' => 'nullable|mimes:pfx', // La extensión del archivo debe ser .pfx
                'logo' => 'nullable|mimes:png,jpg,jpeg', // El logo debe ser PNG, JPG o JPEG
                'certificate_key' => 'nullable|string', // Clave del Certificado
                'produccion' => 'nullable|boolean', // Producción
                'typeBoleta' => 'nullable|in:1,2', // Tipo de envío de boleta, solo admite 1 o 2
            ], [
                // Mensajes personalizados
                'ruc.required' => 'El campo RUC es obligatorio.',
                'ruc.integer' => 'El RUC debe ser un número.',
                'ruc.digits' => 'El RUC debe tener exactamente 11 dígitos.',
                'ubigeo.required' => 'El campo ubigeo es obligatorio.',
                'ubigeo.integer' => 'El ubigeo debe ser un número válido.',
                'company_name.required' => 'La razón social es obligatoria.',
                'company_name.string' => 'La razón social debe ser una cadena de texto.',
                'trade_name.required' => 'El nombre comercial es obligatoria.',
                'trade_name.string' => 'La nombre comercial debe ser una cadena de texto.',
                'tax_address.string' => 'El domicilio fiscal debe ser una cadena de texto.',
                'description.string' => 'La descripción debe ser una cadena de texto.',
                'secondary_sun_user.string' => 'La usuarios SOL secundario debe ser una cadena de texto.',
                'treble_clef.string' => 'La clave SOL  debe ser una cadena de texto.',
                'phone1.digits' => 'El teléfono 1 debe tener exactamente 11 dígitos.',
                'phone2.digits' => 'El teléfono 2 debe tener exactamente 11 dígitos.',
                'email1.email' => 'El campo email 1 debe ser una dirección de correo válida.',
                'email2.email' => 'El campo email 2 debe ser una dirección de correo válida.',
                'certificate.mimes' => 'El certificado debe ser un archivo con extensión .pfx.',
                'certificate_key.string' => 'La clave del certificado  debe ser una cadena de texto.',
                'logo.mimes' => 'El logo debe ser un archivo de tipo PNG, JPG o JPEG.',
                'typeBoleta.in' => 'El tipo de envío de boleta debe ser Resumen Diario o Envío Directo.',
                'produccion.boolean' => 'El campo producción debe ser verdadero o falso.'
            ]);


            if (!$this->id_empresa) { // INSERT
                if (!Gate::allows('create_company')) {
                    session()->flash('error', 'No tiene permisos para crear empresas.');
                    return;
                }
                // VALIDAR QUE NO EXISTA UNA EMPRESA CON EL MISMO RUC
                $validarRuc = DB::table('empresas')->where([['empresa_estado','=',1],['empresa_ruc','=',$this->ruc]])->exists();
                if (!$validarRuc){
                    // VALIDAR QUE NO EXISTA UNA EMPRESA CON EL MISMO RUC
                    DB::beginTransaction();
                    $company = new Empresa();
                    $company->empresa_ruc = $this->ruc;
                    $company->id_ubigeo = $this->ubigeo;
                    $company->empresa_razon_social = $this->company_name;
                    $company->empresa_domicilio_fiscal = $this->tax_address;
                    $company->empresa_nombre_comercial = $this->trade_name;
                    $company->empresa_telefono_uno = $this->phone1;
                    $company->empresa_telefono_dos = $this->phone2;
                    $company->empresa_email_uno = $this->email1;
                    $company->empresa_email_dos = $this->email2;
                    $company->empresa_descricion = $this->description;
                    $company->empresa_usuario = $this->secondary_sun_user;
                    $company->empresa_clave = $this->treble_clef;

                    if ($this->certificate) {
                        $company->empresa_archivo = $this->general->save_files($this->certificate, 'configuration/empresas/certificado');
                    }

                    $company->empresa_clave_certificado = $this->certificate_key;

                    if ($this->logo) {
                        $company->empresa_logo = $this->general->save_files($this->logo, 'configuration/empresas/logo');
                    }
                    $company->empresa_estado_produccion = $this->produccion ? 1 : 0;
                    $company->empresa_estado_boleta = $this->typeBoleta;
                    $company->empresa_estado = 1;
                    if ($company->save()) {
                        DB::commit();
                        $this->dispatch('hideModal');
                        session()->flash('success', 'Registro guardado correctamente.');
                    } else {
                        DB::rollBack();
                        session()->flash('error', 'Ocurrió un error al guardar la empresa.');
                        return;
                    }
                }else{
                    session()->flash('error', 'Ya existe una empresa registrada con el número de RUC');
                    return;
                }
            } else {

                if (!Gate::allows('update_company')) {
                    session()->flash('error', 'No tiene permisos para actualizar la empresa.');
                    return;
                }

                DB::beginTransaction();
                // Actualizar los datos del menú
                $companyUpdate = Empresa::findOrFail($this->id_empresa);
                if ($companyUpdate){
                    $validarRuc = DB::table('empresas')
                        ->where([['empresa_estado','=',1],['empresa_ruc','=',$this->ruc],['id_empresa','<>',$this->id_empresa]])->exists();
                    if (!$validarRuc){

                        $companyUpdate->empresa_ruc = $this->ruc;
                        $companyUpdate->id_ubigeo = $this->ubigeo;
                        $companyUpdate->empresa_razon_social = $this->company_name;
                        $companyUpdate->empresa_domicilio_fiscal = $this->tax_address;
                        $companyUpdate->empresa_nombre_comercial = $this->trade_name;
                        $companyUpdate->empresa_telefono_uno = $this->phone1;
                        $companyUpdate->empresa_telefono_dos = $this->phone2;
                        $companyUpdate->empresa_email_uno = $this->email1;
                        $companyUpdate->empresa_email_dos = $this->email2;
                        $companyUpdate->empresa_descricion = $this->description;
                        $companyUpdate->empresa_usuario = $this->secondary_sun_user;
                        $companyUpdate->empresa_clave = $this->treble_clef;

                        if ($this->certificate) {
                            try{
                                unlink($companyUpdate->empresa_archivo);
                            }catch  (\Exception $e){
                            }
                            $companyUpdate->empresa_archivo = $this->general->save_files($this->certificate, 'configuration/empresas/certificado');
                        }
                        $companyUpdate->empresa_clave_certificado = $this->certificate_key;

                        if ($this->logo) {
                            try{
                                unlink($companyUpdate->empresa_logo);
                            }catch  (\Exception $e){
                            }
                            $companyUpdate->empresa_logo = $this->general->save_files($this->logo, 'configuration/empresas/logo');
                        }
                        $companyUpdate->empresa_estado_produccion = $this->produccion ? 1 : 0;
                        $companyUpdate->empresa_estado_boleta = $this->typeBoleta;

                        if ($companyUpdate->save()) {
                            DB::commit();
                            $this->dispatch('hideModal');
                            session()->flash('success', 'La empresa se ha actualizado con éxito.');
                        } else {
                            DB::rollBack();
                            session()->flash('error', 'No se pudo actualizar la empresa. Por favor, inténtalo nuevamente.');
                            return;
                        }
                    }else{
                        DB::rollBack();
                        session()->flash('error', 'Ya existe una empresa registrada con el mismo número de RUC.');
                        return;
                    }
                }
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
        }
    }
    public function consultDocument(){
        try {
            $this->messageConsulta = "";
            $this->company_name = "";
            $this->tax_address = "";
            $resultado = $this->general->consultar_documento(4,$this->ruc);
            if ($resultado['result']['tipo'] == 'success'){
                $this->company_name = $resultado['result']['name'];
                $this->tax_address = $resultado['result']['direccion'];
            }
            $this->messageConsulta = array('mensaje'=>$resultado['result']['mensaje'],'type'=>$resultado['result']['tipo']);
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el registro. Por favor, inténtelo nuevamente.');
            return;
        }
    }
    public function  edit_data_empresa($id)
    {
        $id_empresa = base64_decode($id);
        if ($id_empresa){
            $empresa = Empresa::find($id_empresa);
            $this->ruc = $empresa->empresa_ruc;
            $this->company_name = $empresa->empresa_razon_social;
            $this->tax_address = $empresa->empresa_domicilio_fiscal;
            $this->ubigeo = $empresa->id_ubigeo;
            $this->trade_name = $empresa->empresa_nombre_comercial;
            $this->phone1 = $empresa->empresa_telefono_uno;
            $this->phone2 = $empresa->empresa_telefono_dos;
            $this->email1 = $empresa->empresa_email_uno;
            $this->email2 = $empresa->empresa_email_dos;
            $this->description = $empresa->empresa_descricion;
            $this->secondary_sun_user = $empresa->empresa_usuario;
            $this->treble_clef = $empresa->empresa_clave;
            $this->certificate_key = $empresa->empresa_clave_certificado;
            $rutaimagen = $empresa->empresa_logo ? $empresa->empresa_logo : asset('assets/images/faces/1.jpg');
            if ($empresa->empresa_estado_produccion == 1){
                $this->produccion = true;
            }else{
                $this->produccion = false;
            }
            $this->typeBoleta = $empresa->empresa_estado_boleta;
            $this->id_empresa = $id_empresa;
            $opcionSelect = "";
            if ($empresa->id_ubigeo){
                $ubi = Ubigeo::find($empresa->id_ubigeo);
                $opcionSelect = $ubi->ubigeo_departamento." - ".$ubi->ubigeo_provincia." - ".$ubi->ubigeo_distrito;
            }
            $this->dispatch('updateUserImagePreview', ['image' => $rutaimagen]);
            $this->dispatch('select_ubigeo',['text' => $opcionSelect]);
        }
    }


    public function btn_disable($id_empresa){
        $id = base64_decode($id_empresa);
        if ($id){
            $this->id_empresa_delete = $id;
            $this->messageDeleteEmpresas = "¿Está seguro que desea eliminar esta empresa?";
        }
    }

    public function disable_company(){
        try {

            if (!Gate::allows('disable_company')) {
                session()->flash('error_delete', 'No tiene permisos para eliminar la empresa.');
                return;
            }


            $this->validate([
                'id_empresa_delete' => 'required|integer',
            ], [
                'id_empresa_delete.required' => 'El identificador es obligatorio.',
                'id_empresa_delete.integer' => 'El identificador debe ser un número entero.',
            ]);

            DB::beginTransaction();

            $empresa_delete = Empresa::find($this->id_empresa_delete);
            $empresa_delete->empresa_estado = 0;
            if ($empresa_delete->save()) {
                DB::commit();
                $this->dispatch('hideModalDelete');
                session()->flash('success', 'Registro eliminado correctamente.');
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo eliminar la empresa.');
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
}
