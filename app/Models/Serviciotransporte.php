<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Serviciotransporte extends Model
{
    use HasFactory;
    protected $table = "servicios_transportes";
    protected $primaryKey = "id_serv_transpt";

    private $logs;
    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function generar_codigo_servicio_transporte(){
        try {
            $añoActual = date('y'); // Obtiene los últimos dos dígitos del año (ej. 25 para 2025)

            // Buscar el último código registrado en el mismo año
            $ultimoRegistro = DB::table('servicios_transportes')
                ->where('serv_transpt_estado', '=', 1)
                ->orderBy('serv_transpt_codigo', 'desc')
                ->first();

            if ($ultimoRegistro) {
                // Extraer el número del último código registrado
                preg_match('/SS\d{2}-(\d+)/', $ultimoRegistro->serv_transpt_codigo, $matches);
                $ultimoNumero = isset($matches[1]) ? (int) $matches[1] : 0;

                // Incrementar el número
                $nuevoNumero = str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);
            } else {
                // Si no hay registros previos en el año, empezar desde 0001
                $nuevoNumero = "0001";
            }

            return "SS$añoActual-$nuevoNumero";
        } catch (\Exception $e) {
            \Log::error("Error al generar código de servicio: " . $e->getMessage());
            return null;
        }
    }

    public function listar_servicio_transporte($search,$pagination,$order = 'desc'){
        try {

            $query = DB::table('servicios_transportes')
                ->where('serv_transpt_estado', '=', 1)
                ->where(function($q) use ($search) {
                    $q->where('serv_transpt_motivo', 'like', '%' . $search . '%')
                        ->orWhere('serv_transpt_detalle_motivo', 'like', '%' . $search . '%')
                        ->orWhere('serv_transpt_remitente_ruc', 'like', '%' . $search . '%')
                        ->orWhere('serv_transpt_remitente_razon_social', 'like', '%' . $search . '%')
                        ->orWhere('serv_transpt_remitente_direccion', 'like', '%' . $search . '%')
                        ->orWhere('serv_transpt_destinatario_ruc', 'like', '%' . $search . '%')
                        ->orWhere('serv_transpt_destinatario_razon_social', 'like', '%' . $search . '%')
                        ->orWhere('serv_transpt_codigo', 'like', '%' . $search . '%')
                        ->orWhere('serv_transpt_destinatario_direccion', 'like', '%' . $search . '%');
                })->orderBy('id_serv_transpt', $order);

            $result = $query->paginate($pagination);

        }catch (\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

}
