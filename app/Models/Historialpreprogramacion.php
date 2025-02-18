<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historialpreprogramacion extends Model
{
    use HasFactory;
    protected $table = "historial_pre_programacion";
    protected $primaryKey = "id_his_pre_progr";

    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
