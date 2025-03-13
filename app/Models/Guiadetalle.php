<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guiadetalle extends Model
{
    use HasFactory;
    protected $table = "guias_detalles";
    protected $primaryKey = "id_guia_det";

    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
