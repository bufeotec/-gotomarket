<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientecontacto extends Model{
    use HasFactory;
    protected $table = "clientes_contactos";
    protected $primaryKey = "id_cliente_contacto";
    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
