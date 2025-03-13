<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notacreditodetalle extends Model
{
    use HasFactory;
    protected $table = "notas_creditos_detalles";
    protected $primaryKey = 'id_not_cred_det';

    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
