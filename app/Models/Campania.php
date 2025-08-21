<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Campania extends Model{
    use HasFactory;
    protected $table = "campanias";
    protected $primaryKey = "id_campania";
    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_campanias($search,$pagination,$order = 'desc'){
        try {
            $query = DB::table('campanias')
                ->where(function($q) use ($search) {
                    $q->where('campania_nombre', 'like', '%' . $search . '%')
                        ->orWhereNull('campania_nombre');
                })->orderBy('id_campania', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_campanias_activos(){
        try {
            $result = DB::table('campanias')
                ->where('campania_estado', '=', 1)
                ->get();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
