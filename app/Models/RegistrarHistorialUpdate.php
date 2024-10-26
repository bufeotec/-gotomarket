<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RegistrarHistorialUpdate extends Model
{
    use HasFactory;
    protected $table = "registrar_historial_updates";
    protected $primaryKey = "id_registrar";
    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
}
