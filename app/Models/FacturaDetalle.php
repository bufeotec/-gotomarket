<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaDetalle extends Model
{
    use HasFactory;
    protected $table = "facturas_detalles";
    protected $primaryKey = "id_factura_detalle";
}
