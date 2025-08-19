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
                ->where('premio_en_campania', '=', 0)
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
}
