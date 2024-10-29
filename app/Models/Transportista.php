<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Transportista extends Model
{
    use HasFactory;

    protected $table = "transportistas";
    protected $primaryKey = "id_transportistas";
    private $logs;

    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_transportistas($search,$pagination,$order = 'asc'){
        try {

            $query = DB::table('transportistas as t')
                ->where('t.transportista_estado', '=', 1)
                ->where(function($q) use ($search) {
                    $q->where('t.transportista_ruc', 'like', '%' . $search . '%')
                        ->orWhere('t.transportista_razon_social', 'like', '%' . $search . '%')
                        ->orWhereNull('t.transportista_ruc')
                        ->orWhereNull('t.transportista_razon_social');
                })->orderBy('t.id_transportistas', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_transportista_por_id($id){
        try {
            $result = DB::table('transportistas')->where('id_transportistas','=',$id)->first();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_transportista_sin_id(){
        try {
            $result = DB::table('transportistas')
                ->where('transportista_estado','=',1)
                ->get();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
