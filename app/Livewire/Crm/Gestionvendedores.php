<?php

namespace App\Livewire\Crm;

use App\Livewire\Intranet\Navegation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Vendedorintranet;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\User;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Gestionvendedores extends Component{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads;
    private $logs;
    private $vendedorintranet;
    private $departamento;
    private $provincia;
    private $user;

    public function __construct(){
        $this->logs = new Logs();
        $this->vendedorintranet = new Vendedorintranet();
        $this->departamento = new Departamento();
        $this->provincia = new Provincia();
        $this->user = new User();
    }
    public $search_gestion_vendedor;
    public $pagination_gestion_vendedor = 10;
    public $messageDelete = "";
    public $id_vendedor_intranet = "";
    public $id_cliente = "";
    public $id_departamento = "";
    public $id_provincia = "";
    public $id_distrito = "";
    public $vendedor_intranet_dni= "";
    public $vendedor_intranet_nombre = "";
    public $vendedor_intranet_correo = "";
    public $vendedor_intranet_estado = "";
    public $provincias = [];
    public $distritos = [];
    public $id_rol;

    public function render(){
        $listar_vendedores = $this->vendedorintranet->listar_gestion_vendedores($this->search_gestion_vendedor, $this->pagination_gestion_vendedor);
        $listar_departamento = $this->departamento->lista_departamento();
        $roles = DB::table('roles')->where('roles_status','=',1)->get();
        $roleId = auth()->user()->roles->first()->id ?? null;
        return view('livewire.crm.gestionvendedores', compact('listar_vendedores', 'listar_departamento', 'roles', 'roleId'));
    }

    public function clear_form(){
        $this->id_vendedor_intranet = "";
        $this->id_departamento = "";
        $this->id_provincia = "";
        $this->id_distrito = "";
        $this->vendedor_intranet_dni = "";
        $this->vendedor_intranet_nombre = "";
        $this->vendedor_intranet_correo = "";
        $this->vendedor_intranet_estado = "";
        $this->id_cliente = "";
        $this->provincias = [];
        $this->distritos = [];
    }

    public function deparTari(){
        $this->id_provincia = "";
        $this->id_distrito = "";
        $this->provincias = [];
        $this->distritos = [];
        $this->listar_provincias();
    }

    public function proviTari(){
        $this->listar_distritos();
    }

    public function listar_provincias(){
        $valor = $this->id_departamento;
        if ($valor) {
            $this->provincias = DB::table('provincias')->where('id_departamento', '=', $valor)->get();
        } else {
            $this->provincias = [];
            $this->id_provincia = "";
            $this->distritos = [];
            $this->id_distrito = "";
        }
    }

    public function listar_distritos(){
        $valor = $this->id_provincia;
        if ($valor) {
            $this->distritos = DB::table('distritos')->where('id_provincia', '=', $valor)->get();
        } else {
            $this->distritos = [];
            $this->id_distrito = "";
        }
    }

    public function edit_data($id){
        $vendedor_edit = Vendedorintranet::find(base64_decode($id));
        if ($vendedor_edit){
            $this->id_rol = $vendedor_edit->id_perfil;
            $this->id_departamento = $vendedor_edit->id_departamento;
            $this->id_provincia = $vendedor_edit->id_provincia;
            $this->id_distrito = $vendedor_edit->id_distrito;
            $this->vendedor_intranet_dni = $vendedor_edit->vendedor_intranet_dni;
            $this->vendedor_intranet_nombre = $vendedor_edit->vendedor_intranet_nombre;
            $this->vendedor_intranet_correo = $vendedor_edit->vendedor_intranet_correo;
            // Cargar las provincias y distritos
            $this->provincias = DB::table('provincias')->where('id_departamento', $this->id_departamento)->get();
            $this->distritos = DB::table('distritos')->where('id_provincia', $this->id_provincia)->get();
        }
    }

    public function btn_disable($id_ven_int,$esta){
        $id = base64_decode($id_ven_int);
        $status = $esta;
        if ($id){
            $this->id_vendedor_intranet = $id;
            $this->vendedor_intranet_estado = $status;
            if ($status == 0){
                $this->messageDelete = "¿Está seguro que desea deshabilitar este vendedor?";
            }else{
                $this->messageDelete = "¿Está seguro que desea habilitar este vendedor?";
            }
        }
    }

    public function disable_vendedor(){
        try {
            if (!Gate::allows('disable_vendedor')) {
                session()->flash('error_delete', 'No tiene permisos para cambiar los estados.');
                return;
            }

            $this->validate([
                'id_vendedor_intranet' => 'required|integer',
                'vendedor_intranet_estado' => 'required|integer',
            ], [
                'id_vendedor_intranet.required' => 'El identificador es obligatorio.',
                'id_vendedor_intranet.integer' => 'El identificador debe ser un número entero.',

                'vendedor_intranet_estado.required' => 'El estado es obligatorio.',
                'vendedor_intranet_estado.integer' => 'El estado debe ser un número entero.',
            ]);

            DB::beginTransaction();
            $vendedot_delete = Vendedorintranet::find($this->id_vendedor_intranet);
            $vendedot_delete->vendedor_intranet_estado = $this->vendedor_intranet_estado;
            if ($vendedot_delete->save()) {
                DB::commit();
                $this->dispatch('hide_modal_detele_vendedor');
                if ($this->vendedor_intranet_estado == 0){
                    session()->flash('success', 'Registro deshabilitado correctamente.');
                }else{
                    session()->flash('success', 'Registro habilitado correctamente.');
                }
            } else {
                DB::rollBack();
                session()->flash('error_delete', 'No se pudo cambiar el estado del vendedor.');
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

    public function save_vendedor(){
        try {
            $this->validate([
                'id_departamento' => 'required|integer',
                'id_provincia' => 'required|integer',
                'id_distrito' => 'required|integer',
                'vendedor_intranet_dni' => 'required|numeric|digits:8',
                'vendedor_intranet_nombre' => 'required|string|max:255',
                'vendedor_intranet_correo' => 'required|email|max:255',
                'id_vendedor_intranet' => 'nullable|integer',
            ], [
                'id_departamento.required' => 'El departamento es obligatorio.',
                'id_departamento.integer' => 'El departamento seleccionado no es válido.',

                'id_provincia.required' => 'La provincia es obligatoria.',
                'id_provincia.integer' => 'La provincia seleccionada no es válida.',

                'id_distrito.required' => 'El distrito es obligatorio.',
                'id_distrito.integer' => 'El distrito seleccionado no es válido.',

                'vendedor_intranet_dni.required' => 'El DNI del vendedor es obligatorio.',
                'vendedor_intranet_dni.numeric' => 'El DNI debe contener solo números.',
                'vendedor_intranet_dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',

                'vendedor_intranet_nombre.required' => 'El nombre del vendedor es obligatorio.',
                'vendedor_intranet_nombre.string' => 'El nombre del vendedor debe ser texto válido.',
                'vendedor_intranet_nombre.max' => 'El nombre no debe exceder los 255 caracteres.',

                'vendedor_intranet_correo.required' => 'El correo electrónico es obligatorio.',
                'vendedor_intranet_correo.email' => 'Debe ingresar un correo electrónico válido.',
                'vendedor_intranet_correo.max' => 'El correo no debe exceder los 255 caracteres.',

                'id_vendedor_intranet.integer' => 'El ID del vendedor debe ser un número entero.'
            ]);

            if (!$this->id_vendedor_intranet) { // INSERT
                if (!Gate::allows('crear_vendedor')) {
                    session()->flash('error', 'No tiene permisos para crear vendedor.');
                    return;
                }

                $microtime = microtime(true);

                $role = Role::find($this->id_rol);

                DB::beginTransaction();
                // Primero crear el vendedor
                $save_vendedor = new Vendedorintranet();
                $save_vendedor->id_users = Auth::id();
                $save_vendedor->id_cliente = null;
                $save_vendedor->id_perfil = $this->id_rol;
                $save_vendedor->id_departamento = $this->id_departamento;
                $save_vendedor->id_provincia = $this->id_provincia;
                $save_vendedor->id_distrito = $this->id_distrito;
                $save_vendedor->vendedor_intranet_dni = $this->vendedor_intranet_dni;
                $save_vendedor->vendedor_intranet_nombre = $this->vendedor_intranet_nombre;
                $save_vendedor->vendedor_intranet_correo = $this->vendedor_intranet_correo;
                $save_vendedor->vendedor_intranet_punto = 0;
                $save_vendedor->vendedor_intranet_microtime = $microtime;
                $save_vendedor->vendedor_intranet_estado = 1;

                if ($save_vendedor->save()) {
                    // Después de guardar el vendedor, crear el usuario
                    $user = new User();
                    $user->id_vendedor_intranet = $save_vendedor->id_vendedor_intranet;
                    $user->name = $this->vendedor_intranet_nombre;
                    $user->email = $this->vendedor_intranet_correo;
                    $user->username = $this->vendedor_intranet_correo;
                    $user->password = bcrypt($this->vendedor_intranet_dni);

                    if ($user->save()) {
                        // Asignar rol al usuario
                        $user->assignRole($role->name);
                        DB::commit();
                        $this->dispatch('hide_modal_vendedor');
                        session()->flash('success', 'Registro guardado correctamente.');
                    } else {
                        DB::rollBack();
                        session()->flash('error', 'Ocurrió un error al crear el usuario.');
                    }
                } else {
                    DB::rollBack();
                    session()->flash('error', 'Ocurrió un error al guardar el vendedor.');
                    return;
                }
            } else {
                if (!Gate::allows('update_vendedor')) {
                    session()->flash('error', 'No tiene permisos para actualizar los vendedores.');
                    return;
                }
                DB::beginTransaction();

                // Actualizar los datos del menú
                $update_vendedor = Vendedorintranet::findOrFail($this->id_vendedor_intranet);
                $update_vendedor->vendedor_intranet_dni = $this->vendedor_intranet_dni;
                $update_vendedor->vendedor_intranet_nombre = $this->vendedor_intranet_nombre;
                $update_vendedor->vendedor_intranet_correo = $this->vendedor_intranet_correo;

                if (!$update_vendedor->save()) {
                    session()->flash('error', 'No se pudo actualizar el vendedor.');
                    return;
                }

                DB::commit();
                $this->dispatch('hide_modal_vendedor');
                session()->flash('success', 'Vendedor actualizado correctamente.');
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
