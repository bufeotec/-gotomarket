<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Empresa extends Model
{
    use HasFactory;
    protected $table = "empresas";
    protected $primaryKey = "id_empresa";

    private $logs;
    public function __construct()
    {
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_empresas(){
        try {
            $result = DB::table('empresas')->where('empresa_estado','=',1)->get();
        }catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_empresa_livewire($search,$pagination,$order = 'asc'){
        try {

            $query = DB::table('empresas')
                ->where('empresa_estado', '=', 1)
                ->where(function($q) use ($search) {
                    $q->where('empresa_ruc', 'like', '%' . $search . '%')
                        ->orWhere('empresa_razon_social', 'like', '%' . $search . '%')
                        ->orWhere('empresa_domicilio_fiscal', 'like', '%' . $search . '%')
                        ->orWhere('empresa_nombre_comercial', 'like', '%' . $search . '%')
                        ->orWhere('empresa_telefono_uno', 'like', '%' . $search . '%')
                        ->orWhere('empresa_telefono_dos', 'like', '%' . $search . '%')
                        ->orWhere('empresa_email_uno', 'like', '%' . $search . '%')
                        ->orWhere('empresa_email_dos', 'like', '%' . $search . '%')
                        ->orWhere('empresa_descricion', 'like', '%' . $search . '%')
                        ->orWhere('empresa_usuario', 'like', '%' . $search . '%')
                        ->orWhere('empresa_clave', 'like', '%' . $search . '%')
                    ;
                })->orderBy('id_empresa', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }


}
