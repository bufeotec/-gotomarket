<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stocklote extends Model{
    use HasFactory;
    protected $table = "stocks_lotes";
    protected $primaryKey = "id_stock_lote";
    private $logs;

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }
}
