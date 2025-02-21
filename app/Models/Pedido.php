<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pedido extends Model
{
    use HasFactory;
    protected $table = "facturas_pedidos";
    protected $primaryKey = 'id_factura_pedido';

    protected $fillable = [
        'id_users',
        'factura_ped_numser',
        'factura_ped_numdoc',
        'factura_ped_factura',
        'factura_femision',
        'factura_ped_nomcli',
        'factura_ped_codcli',
        'factura_ped_direccion',
        'factura_ped_departamento',
        'factura_ped_provincia',
        'factura_ped_distrito',
        'factura_ped_cfimporte',
        'factura_ped_estado',
    ];

    public function __construct(){
        parent::__construct();
        $this->logs = new Logs();
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id');
    }

    // Relación con DetallePedido
    public function detallePedido()
    {
        return $this->belongsTo(DetallePedido::class, 'id_detalle_pedido', 'id');
    }

    private $logs;
    public function listar_producto(){
        try{
            $result = DB::table('productos')
                ->whereIn('producto_estado', [1])
                ->get();
        }catch(\Exception $e){
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }

    public function lista_factura($search, $pagination, $order = 'asc')
    {
        try {
            $query = DB::table('facturas_pedidos as fp')
                ->select('fp.factura_ped_factura', 'fp.factura_ped_codcli', 'fp.factura_ped_nomcli', 'fp.factura_femision', 'fp.factura_ped_cfimporte')
                ->where(function ($q) use ($search) {
                    $q->where('fp.factura_ped_factura', 'like', '%' . $search . '%')
                        ->orWhere('fp.factura_ped_nomcli', 'like', '%' . $search . '%')
                        ->orWhere('fp.factura_ped_codcli', 'like', '%' . $search . '%');
                })->orderBy('fp.factura_ped_factura', $order); // Puedes ordenar por otra columna si lo deseas
            $result = $query->paginate($pagination);
        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            $result = [];
        }
        return $result;
    }
}
