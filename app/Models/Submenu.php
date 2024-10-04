<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Submenu extends Model
{
    use HasFactory;
    protected $table = "submenus";
    protected $primaryKey = "id_submenu";
    private $logs;

    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_submenus_x_menu_general_livewire($id,$search,$pagination,$order = 'asc'){
        try {
            $query = DB::table('submenus')
                ->where('id_menu', '=', $id)
                ->where(function($q) use ($search) {
                    $q->where('submenu_name', 'like', '%' . $search . '%')
                        ->orWhere('submenu_function', 'like', '%' . $search . '%')
                        ->orWhereNull('submenu_name')
                        ->orWhereNull('submenu_function');
                })->orderBy('id_submenu', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
    public function listar_submenus_x_menu($id){
        try {

            $result = DB::table('submenus')
                ->where([['id_menu','=',$id],['submenu_status','=',1],['submenu_show','=',1]])
                ->orderBy('submenu_order','asc')->get();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_datos_x_id($id){
        try {

            $result = DB::table('submenus')
                ->where('id_submenu','=',$id)->first();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
