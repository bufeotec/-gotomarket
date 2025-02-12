<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    use HasFactory;
    protected $table = "provincias";
    protected $primaryKey = "id_provincia";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }

    public function lista_provincia(){
        try {

            $result = Provincia::get();

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
