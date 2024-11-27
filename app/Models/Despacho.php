<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Despacho extends Model
{
    use HasFactory;
    protected $table = "despachos";
    protected $primaryKey = "id_despacho";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
    public function listar_despachos_por_programacion($id_program){
        try {
            $result =  DB::table('despachos as d')
                ->join('transportistas as t','t.id_transportistas','=','d.id_transportistas')
                ->where('d.id_programacion','=',$id_program)->get();
        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
