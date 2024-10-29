<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Medida extends Model
{
    use HasFactory;
    protected $table = "medida";
    protected $primaryKey = "id_medida";
    private $logs;

    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }
}
