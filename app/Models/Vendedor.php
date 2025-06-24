<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Vendedor extends Model
{
    use HasFactory;
    private $logs;
    protected $table = "vendedores";
    protected $primaryKey = "id_vendedor";
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_vendedores_activos($pagination,$order = 'asc'){
        try {
            $query = DB::table('vendedores')
                ->where('vendedor_estado', '!=', 0)
                ->orderBy('id_vendedor', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listra_vendedores_activos(){
        try {
            $result = DB::table('vendedores')
                ->where('vendedor_estado','!=',0)
                ->whereNotNull('vendedor_codigo_intranet')
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listra_perfiles_activos(){
        try {
            $result = DB::table('roles')
                ->where('roles_status','=',1)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
