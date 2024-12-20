<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Liquidacion extends Model
{
    use HasFactory;
    protected $table = "liquidaciones";
    protected $primaryKey = "id_liquidacion";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_liquidacion($desde, $hasta)
    {
        try {
            $result = DB::table('liquidaciones as li')
                ->join('transportistas as tr', 'li.id_transportistas', '=', 'tr.id_transportistas')
                ->join('users as u', 'li.id_users', '=', 'u.id_users')
                ->whereBetween(DB::raw('DATE(li.created_at)'), [$desde, $hasta])
                ->where('li.liquidacion_estado', '=', 1)
                ->orderBy('li.id_liquidacion','desc')
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

}
