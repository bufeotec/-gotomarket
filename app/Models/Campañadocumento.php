<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campañadocumento extends Model{
    use HasFactory;
    protected $table = "campanias_documentos";
    protected $primaryKey = "id_campania_documento";
    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
