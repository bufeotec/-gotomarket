<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facturamovimientoarea extends Model
{
    use HasFactory;
    protected $table = "facturas_movimientos_areas";
    protected $primaryKey = "id_fac_mov_area";

    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
