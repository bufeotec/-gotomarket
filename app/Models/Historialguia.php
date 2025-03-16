<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historialguia extends Model
{
    use HasFactory;
    protected $table = "historial_guias";
    protected $primaryKey = "id_historial_guia";

    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
