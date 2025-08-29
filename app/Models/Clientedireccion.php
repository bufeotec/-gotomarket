<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientedireccion extends Model{
    use HasFactory;
    protected $table = "clientes_direcciones";
    protected $primaryKey = "id_cliente_direccion";
    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
