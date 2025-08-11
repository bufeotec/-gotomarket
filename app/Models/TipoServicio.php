<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TipoServicio extends Model
{
    use HasFactory;

    protected $table = "tipo_servicios";
    protected $primaryKey = "id_tipo_servicios";
    private $logs;

    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_tipo_servicios(){
        try {

            $result = TipoServicio::get();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_tipo_servicio_local_provincial(){
        try {
            $result = DB::table('tipo_servicios')
                ->whereIn('id_tipo_servicios', [1, 2])
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_tipo_servicios_os(){
        try {
            $result = DB::table('tipo_servicios')
                ->where('tipo_servicio_estado', '=', 1)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
