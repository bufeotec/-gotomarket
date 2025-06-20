<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\Transportista;
use Illuminate\Http\Request;

class VendedorController extends Controller
{
    private $logs;
    private $transportista;
    public function __construct(){
        $this->logs = new Logs();
        $this->transportista = new Transportista();
    }
}
