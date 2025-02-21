<?php

namespace App\Livewire\Pedido;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Pedido;
use App\Models\Logs;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\Distrito;
use Illuminate\Support\Facades\Gate;

class Pedidos extends Component
{
    private $pedido;
    private $logs;
    public $cantidad = 1;
    public $productosAgregados = [];
    public $subtotal = 0;
    public $searchs;
    public $paginations = 10;

    public $order;
    public $id_producto = "";
    public $factura_ped_nomcli = "";
    public $factura_ped_codcli = "";
    public $factura_ped_direccion = "";
    public $factura_ped_departamento = "";
    public $factura_ped_provincia = "";
    public $factura_ped_distrito = "";
    public $factura_ped_estado = 1; // Estado por defecto

    public function __construct()
    {
        $this->pedido = new Pedido();
        $this->logs = new Logs();
        $this->provincia = new Provincia();
        $this->departamento = new Departamento();
        $this->distrito = new Distrito();
    }

    public function render()
    {
        $listar_producto = $this->pedido->listar_producto();
        $listar_dep = $this->departamento->lista_departamento();
        $listar_prov = $this->provincia->lista_provincia();
        $listar_dis = $this->distrito->lista_distrito();
        $factura = $this->pedido->lista_factura($this->searchs, $this->paginations, 'desc');
        return view('livewire.pedido.pedidos', compact('listar_producto', 'listar_dep', 'listar_prov', 'listar_dis','factura'));
    }

    public function clear_form_rp()
    {
        $this->factura_ped_nomcli = "";
        $this->factura_ped_codcli = "";
        $this->factura_ped_direccion = "";
        $this->factura_ped_departamento = "";
        $this->factura_ped_provincia = "";
        $this->factura_ped_distrito = "";
        $this->id_producto = "";
        $this->productosAgregados = [];
        $this->subtotal = 0;
        $this->cantidad = 1; // Reiniciar cantidad
    }

    public function addProduct()
    {
        $this->validate([
            'id_producto' => 'required|integer',
            'cantidad' => 'required|integer|min:1',
        ], [
            'id_producto.required' => 'Debes seleccionar un producto.',
        ]);

        $producto = $this->pedido->listar_producto()->firstWhere('id_producto', $this->id_producto);
        if ($producto) {
            $this->productosAgregados[] = [
                'id' => $producto->id_producto,
                'nombre' => $producto->producto_nom,
                'precio' => $producto->producto_precio,
                'cantidad' => $this->cantidad,
            ];
            $this->calculateSubtotal();
            $this->reset('id_producto', 'cantidad'); // Resetear campos
        }
    }

    public function removeProduct($index)
    {
        if (isset($this->productosAgregados[$index])) {
            unset($this->productosAgregados[$index]);
            $this->productosAgregados = array_values($this->productosAgregados); // Reindexar el array
            $this->calculateSubtotal();
        }
    }

    public function calculateSubtotal()
    {
        $this->subtotal = 0;
        foreach ($this->productosAgregados as $item) {
            $this->subtotal += $item['precio'] * $item['cantidad'];
        }
    }


    public function savePedido()
    {
        $this->validate([
            'factura_ped_nomcli' => 'required|string',
            'factura_ped_codcli' => 'required|string',
            'factura_ped_direccion' => 'required|string',
            'factura_ped_departamento' => 'required|string',
            'factura_ped_provincia' => 'required|string',
            'factura_ped_distrito' => 'required|string',
        ], [
            'factura_ped_nomcli.required' => 'El nombre del cliente es obligatorio.',
            'factura_ped_codcli.required' => 'El número de documento es obligatorio.',
            'factura_ped_direccion.required' => 'La dirección es obligatoria.',
            'factura_ped_departamento.required' => 'El departamento es obligatorio.',
            'factura_ped_provincia.required' => 'La provincia es obligatoria.',
            'factura_ped_distrito.required' => 'El distrito es obligatorio.',
        ]);

        // Verificar que se hayan agregado productos
        if (empty($this->productosAgregados)) {
            session()->flash('error', 'Debe agregar al menos un producto.');
            return;
        }

        // Iniciar una transacción de base de datos
        DB::beginTransaction();

        try {
            // Crear un nuevo pedido
            $pedido = new Pedido();
            $pedido->id_users = Auth::id();
            $pedido->factura_ped_numser = $this->generateUniqueNumSer();
            $pedido->factura_ped_numdoc = $this->generateUniqueNumDoc();
            $pedido->factura_ped_factura = $pedido->factura_ped_numser . '-' . $pedido->factura_ped_numdoc;
            $pedido->factura_femision = now();
            $pedido->factura_ped_nomcli = $this->factura_ped_nomcli;
            $pedido->factura_ped_codcli = $this->factura_ped_codcli;
            $pedido->factura_ped_direccion = $this->factura_ped_direccion;
            $pedido->factura_ped_departamento = $this->factura_ped_departamento;
            $pedido->factura_ped_provincia = $this->factura_ped_provincia;
            $pedido->factura_ped_distrito = $this->factura_ped_distrito;
            $pedido->factura_ped_cfimporte = $this->subtotal; // Cambia el tipo de dato si es necesario
            $pedido->factura_ped_estado = $this->factura_ped_estado; // Estado por defecto

            // Guardar el pedido
            if ($pedido->save()) {
                // Recuperar el ID del pedido recién creado
                $id_factura_pedido = $pedido->id_factura_pedido;

                // Guardar los detalles del pedido
                foreach ($this->productosAgregados as $item) {
                    DB::table('detalles_pedido')->insert([
                        'id_factura_pedido' => $id_factura_pedido,
                        'detalle_pedido_cant' => $item['cantidad'],
                        'detalle_pedido_pre_uni' => $item['precio'],
                        'detalle_pedido_subtotal' => $item['precio'] * $item['cantidad'],
                        'id_producto' => $item['id'],
                    ]);
                }

                // Confirmar la transacción
                DB::commit();

                // Cerrar el modal y limpiar el formulario
                $this->dispatch('hideModal');
                session()->flash('success', 'Pedido guardado correctamente.');
                $this->clear_form_rp();
            } else {
                // Revertir la transacción en caso de error
                DB::rollBack();
                session()->flash('error', 'Ocurrió un error al guardar el pedido.');
            }
        } catch (\Exception $e) {
            // Revertir la transacción y mostrar el error
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al guardar el pedido: ' . $e->getMessage());
        }
    }
    private function generateUniqueNumSer()
    {
        // Generar un número de serie único (puedes personalizarlo)
        return 'F001';
    }

    private function generateUniqueNumDoc()
    {
        // Obtener el último número de documento utilizado
        $ultimoDoc = Pedido::orderBy('fac_pre_prog_cfnumdoc', 'desc')->first();

        // Si existe, incrementar el número; si no, comenzar en 1
        if ($ultimoDoc) {
            $nuevoNumDoc = intval($ultimoDoc->fac_pre_prog_cfnumdoc) + 1;
        } else {
            $nuevoNumDoc = 1; // Empezar desde 1 si no hay documentos
        }

        // Rellenar con ceros a la izquierda hasta llegar a 6 dígitos
        return str_pad($nuevoNumDoc, 6, '0', STR_PAD_LEFT);
    }
}
