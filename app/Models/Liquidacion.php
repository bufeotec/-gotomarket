<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Liquidacion extends Model
{
    use HasFactory;
    protected $table = "liquidaciones";
    protected $primaryKey = "id_liquidacion";
}
