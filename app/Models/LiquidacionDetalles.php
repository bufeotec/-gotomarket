<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiquidacionDetalles extends Model
{
    use HasFactory;
    protected $table = "liquidacion_detalles";
    protected $primaryKey = "id_liquidacion_detalle";
}
