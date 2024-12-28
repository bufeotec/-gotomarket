<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiquidacionDetalles extends Model
{
    use HasFactory;
    protected $table = "liquidacion_detalles";
    protected $primaryKey = "id_liquidacion_detalle";
    public function gastos()
    {
        return $this->hasMany(LiquidacionGastos::class, 'id_liquidacion_detalle');
    }
}
