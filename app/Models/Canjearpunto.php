<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Canjearpunto extends Model{
    use HasFactory;
    protected $table = "canjear_puntos";
    protected $primaryKey = "id_canjear_punto";
    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
