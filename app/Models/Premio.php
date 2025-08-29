<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Premio extends Model{
    use HasFactory;
    protected $table = "premios";
    protected $primaryKey = "id_premio";
    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_premios_activos($search, $order = 'desc'){
        try {
            $query = DB::table('premios')
//                ->where('premio_en_campania', '=', 0)
                ->where(function($q) use ($search) {
                    $q->where('premio_descripcion', 'like', '%' . $search . '%')
                        ->orWhere('premio_codigo', 'like', '%' . $search . '%')
                        ->orWhereNull('premio_descripcion')
                        ->orWhereNull('premio_codigo');
                })
                ->orderBy('id_premio', $order)
                ->get();

            return $query;

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [];
        }
    }

    public function listar_campanias_activos($id_campania = null){
        try {
           $result = DB::table('premios as p')
                ->join('campanias_premios as cp', 'p.id_premio', '=', 'cp.id_premio')
                ->select([
                    'p.*',
                    'cp.campania_premio_puntaje',
                    'cp.id_campania'
                ])
                ->where('p.premio_estado', 1)
                ->where('cp.campania_premio_estado', 1)
                ->where('cp.id_campania', $id_campania)
                ->orderBy('p.id_premio', 'desc');

            return $result->get();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            return [];
        }
    }

}
