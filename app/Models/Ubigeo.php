<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ubigeo extends Model
{
    use HasFactory;
    protected $table = "ubigeos";
    protected $primaryKey = "id_ubigeo";
    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_ubigeos(){
        try {
            $result =  DB::table('ubigeos')->get();
        }catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
