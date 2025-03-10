<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notacreditoguia extends Model
{
    use HasFactory;
    protected $table = "notas_creditos_guias";
    protected $primaryKey = 'id_nota_credito_guia';

    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

}
