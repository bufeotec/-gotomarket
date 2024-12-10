<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiquidacionGastos extends Model
{
    use HasFactory;
    protected $table = "liquidacion_gastos";
    protected $primaryKey = "id_liquidacion_gasto";
}
