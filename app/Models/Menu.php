<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Menu extends Model
{
    use HasFactory;
    protected $table = "menus";
    protected $primaryKey = "id_menu";
    private $logs;

    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_menus_livewire($search,$pagination,$order = 'asc'){
        try {

            $query = DB::table('menus')
//                ->where('menu_status', '=', 1)
                ->where(function($q) use ($search) {
                    $q->where('menu_name', 'like', '%' . $search . '%')
                        ->orWhere('menu_controller', 'like', '%' . $search . '%')
                        ->orWhereNull('menu_name')
                        ->orWhereNull('menu_controller');
                })->orderBy('id_menu', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_menus_sidebar(){
        try {

            $result = DB::table('menus')->where([['menu_status','=',1],['menu_show','=',1]])->orderBy('menu_order','asc')->get();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function validar_permiso($permission){
        try {
            $result = DB::table('permissions')
                ->where('permission_status','=',1)
                ->where('permissions.name','=',$permission)->first();
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function buscar_menu_x_microtime($microtime){
        try {
            $result = DB::table('menus')->where('menu_microtime','=',$microtime)->first();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_menu_x_id($id){
        try {
            $result = DB::table('menus')->where('id_menu','=',$id)->first();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function permisos_datos($nombre){
        try {
            $result = DB::table('permissions')->where('name','=',$nombre)->first();
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
