<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Guia extends Model
{
    use HasFactory;
    protected $table = "guias";
    protected $primaryKey = "id_guia";

    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function listar_comprobantes($search, $pagination, $order = 'desc'){
        try {
            // Mapeo de términos de búsqueda a valores de estado
            $estadoMapping = [
                'creditos' => 1,
                'programar' => 2,
                'programado' => 3,
                'ruta' => 4
            ];

            // Convertir el término de búsqueda a su valor correspondiente si existe en el mapeo
            $estadoValue = $estadoMapping[strtoupper($search)] ?? null;

            $query = DB::table('guias')
                ->where(function ($q) use ($search, $estadoValue) {
                    $q->where('guia_nombre_cliente', 'like', '%' . $search . '%')
                        ->orWhere('guia_ruc_cliente', 'like', '%' . $search . '%')
                        ->orWhere('guia_nro_doc', 'like', '%' . $search . '%');

                    // Si el término de búsqueda coincide con un estado, filtrar por el campo correspondiente
                    if (!is_null($estadoValue)) {
                        $q->orWhere('guia_estado_aprobacion', $estadoValue);
                    }
                })
                ->orderBy('id_guia', $order);

            $result = $query->paginate($pagination);

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }

        return $result;
    }

    public function listar_guia_x_id($id){
        try {
            $result = DB::table('guias')
                ->where('id_guia','=',$id)
                ->first();

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function listar_guia_detalle_x_id($id) {
        try {
            $result = DB::table('guias as g')
                ->join('guias_detalles as gd', 'g.id_guia', '=', 'gd.id_guia')
                ->where('gd.id_guia', '=', $id)
                ->get();
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
