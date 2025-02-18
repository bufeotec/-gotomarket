<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historialdespachoventa extends Model
{
    use HasFactory;
    protected $table = "historial_despachos_ventas";
    protected $primaryKey = "id_his_desp_vent";

    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
