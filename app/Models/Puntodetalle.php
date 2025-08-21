<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puntodetalle extends Model{
    use HasFactory;
    protected $table = "puntos_detalles";
    protected $primaryKey = "id_punto_detalle";
    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
