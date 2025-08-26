<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaniapremio extends Model{
    use HasFactory;
    protected $table = "campanias_premios";
    protected $primaryKey = "id_campaña_premio";
    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
