<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DespachoVenta extends Model
{
    use HasFactory;
    protected $table = "depacho_ventas";
    protected $primaryKey = "id_depacho_venta";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
}
