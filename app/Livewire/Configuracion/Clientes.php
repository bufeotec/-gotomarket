<?php

namespace App\Livewire\Configuracion;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Cliente;
use App\Models\Server;
use Livewire\WithFileUploads;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class Clientes extends Component{
    use WithPagination, WithoutUrlPagination;
    use WithFileUploads;
     private $logs;
     private $cliente;
     private $server;
    public function __construct(){
        $this->logs = new Logs();
        $this->cliente = new Cliente();
        $this->server = new Server();
    }
    public $search_cliente;
    public $pagination_cliente = 10;
    public $listar_clientes = [];
    public $obtener_direccion = [];
    public $obtener_contacto = [];

    public function render(){
        $listar_cliente = $this->cliente->listar_cliente_registrados($this->search_cliente, $this->pagination_cliente);
        return view('livewire.configuracion.clientes', compact('listar_cliente'));
    }

    public function actualizar_cliente(){
        try {

            if (!Gate::allows('actualizar_cliente')) {
                session()->flash('error', 'No tiene permisos para actualizar los clientes.');
                return;
            }

            DB::beginTransaction();

            $datosResult = $this->server->obtener_clientes();
            $this->listar_clientes = $datosResult;

            $contadorActualizados = 0;
            $contadorCreados = 0;
            $contadorIgnorados = 0;

            foreach ($this->listar_clientes as $lc) {
                // Buscar si ya existe un vendedor con este código
                $clienteExistente = Cliente::where('cliente_codigo_cliente', $lc->CODIGO_CLIENTE)->first();

                if ($clienteExistente) {
                    // Si el vendedor existe pero tiene estado 0, lo ignoramos
                    if ($clienteExistente->cliente_estado_registro == 0) {
                        $contadorIgnorados++;
                        continue;
                    }
                    $microtime = microtime(true);

                    // Actualizar registro existente
                    $clienteExistente->cliente_codigo_cliente = $lc->CODIGO_CLIENTE ?: null;
                    $clienteExistente->cliente_ruc_cliente = $lc->RUC_CLIENTE ?: null;
                    $clienteExistente->cliente_nombre_cliente = $lc->NOMBRE_CLIENTE ?: null;
                    $clienteExistente->cliente_estado = $lc->ESTADO ?: null;
                    $clienteExistente->cliente_pais = $lc->PAIS ?: null;
                    $clienteExistente->cliente_departamento_registrado = $lc->DEPARTAMENTO_REGISTRADO ?: null;
                    $clienteExistente->cliente_porvincia_registrado = $lc->PROVINCIA_REGISTRADA ?: null;
                    $clienteExistente->cliente_distrito_registrado = $lc->DISTRITO_REGISTRADO ?: null;
                    $clienteExistente->cliente_direccion_fiscal = $lc->DIRECCION_FISCAL ?: null;
                    $clienteExistente->cliente_direccion_entrega_principal = $lc->DIRECCION_ENTREGA_PRINCIPAL ?: null;
                    $clienteExistente->cliente_ubigeo = $lc->UBIGEO ?: null;
                    $clienteExistente->cliente_departamento = $lc->DEPARTAMENTO ?: null;
                    $clienteExistente->cliente_provincia = $lc->PROVINCIA ?: null;
                    $clienteExistente->cliente_distrito = $lc->DISTRITO ?: null;
                    $clienteExistente->cliente_codigo_tipo = $lc->CODIGO_TIPO ?: null;
                    $clienteExistente->cliente_tipo = $lc->TIPO ?: null;
                    $clienteExistente->cliente_codigo_giro = $lc->CODIGO_GIRO ?: null;
                    $clienteExistente->cliente_giro = $lc->GIRO ?: null;
                    $clienteExistente->cliente_codigo_forma_pago = $lc->CODIGO_FORMA_DE_PAGO ?: null;
                    $clienteExistente->cliente_forma_pago = $lc->FORMA_DE_PAGO ?: null;
                    $clienteExistente->cliente_codigo_zona = $lc->CODIGO_ZONA ?: null;
                    $clienteExistente->cliente_zona = $lc->ZONA ?: null;
                    $clienteExistente->cliente_codigo_lista_precio = $lc->CODIGO_LISTA_DE_PRECIO ?: null;
                    $clienteExistente->cliente_lista_precio = $lc->LISTA_DE_PRECIO ?: null;
                    $clienteExistente->cliente_codigo_vendedor = $lc->CODIGO_VENDEDOR ?: null;
                    $clienteExistente->cliente_vendedor = $lc->VENDEDOR ?: null;
                    $clienteExistente->cliente_telefono = $lc->TELEFONO ?: null;
                    $clienteExistente->cliente_email = $lc->EMAIL ?: null;
                    $clienteExistente->cliente_web = $lc->WEB ?: null;
                    $clienteExistente->cliente_codigo_transportacion = $lc->CODIGO_DE_TRANSPORTISTA ?: null;
                    $clienteExistente->cliente_transportista = $lc->TRANSPORTISTA ?: null;
                    $clienteExistente->cliente_moneda = $lc->MONEDA ?: null;
                    $clienteExistente->cliente_limite_credito = $lc->LIMITE_DE_CREDITO ?: null;
                    $clienteExistente->cliente_credito_dolares = $lc->CREDITO_EN_DOLARES ?: null;
                    $clienteExistente->cliente_credito_soles = $lc->CREDITO_EN_SOLES ?: null;
                    $clienteExistente->cliente_porcentaje_descuento = $lc->PORCENTAJE_DESCUENTO ?: null;
                    $clienteExistente->cliente_cliente_principal = $lc->CLIENTE_PRINCIPAL ?: null;
                    $clienteExistente->cliente_cod_grupo = $lc->COD_GRUPO ?: null;
                    $clienteExistente->cliente_ruc_grupo = $lc->RUC_GRUPO ?: null;
                    $clienteExistente->cliente_nombre_grupo = $lc->NOMBRE_GRUPO ?: null;
                    $clienteExistente->cliente_retencion = $lc->RETENCION ?: null;
                    $clienteExistente->cliente_microtime = $microtime;

                    $clienteExistente->save();
                    $contadorActualizados++;
                } else {
                    // Crear nuevo registro
                    $microtime = microtime(true);
                    $cliente = new Cliente();
                    $cliente->id_users = Auth::id();
                    $cliente->cliente_codigo_cliente = $lc->CODIGO_CLIENTE ?: null;
                    $cliente->cliente_ruc_cliente = $lc->RUC_CLIENTE ?: null;
                    $cliente->cliente_nombre_cliente = $lc->NOMBRE_CLIENTE ?: null;
                    $cliente->cliente_estado = $lc->ESTADO ?: null;
                    $cliente->cliente_pais = $lc->PAIS ?: null;
                    $cliente->cliente_departamento_registrado = $lc->DEPARTAMENTO_REGISTRADO ?: null;
                    $cliente->cliente_porvincia_registrado = $lc->PROVINCIA_REGISTRADA ?: null;
                    $cliente->cliente_distrito_registrado = $lc->DISTRITO_REGISTRADO ?: null;
                    $cliente->cliente_direccion_fiscal = $lc->DIRECCION_FISCAL ?: null;
                    $cliente->cliente_direccion_entrega_principal = $lc->DIRECCION_ENTREGA_PRINCIPAL ?: null;
                    $cliente->cliente_ubigeo = $lc->UBIGEO ?: null;
                    $cliente->cliente_departamento = $lc->DEPARTAMENTO ?: null;
                    $cliente->cliente_provincia = $lc->PROVINCIA ?: null;
                    $cliente->cliente_distrito = $lc->DISTRITO ?: null;
                    $cliente->cliente_codigo_tipo = $lc->CODIGO_TIPO ?: null;
                    $cliente->cliente_tipo = $lc->TIPO ?: null;
                    $cliente->cliente_codigo_giro = $lc->CODIGO_GIRO ?: null;
                    $cliente->cliente_giro = $lc->GIRO ?: null;
                    $cliente->cliente_codigo_forma_pago = $lc->CODIGO_FORMA_DE_PAGO ?: null;
                    $cliente->cliente_forma_pago = $lc->FORMA_DE_PAGO ?: null;
                    $cliente->cliente_codigo_zona = $lc->CODIGO_ZONA ?: null;
                    $cliente->cliente_zona = $lc->ZONA ?: null;
                    $cliente->cliente_codigo_lista_precio = $lc->CODIGO_LISTA_DE_PRECIO ?: null;
                    $cliente->cliente_lista_precio = $lc->LISTA_DE_PRECIO ?: null;
                    $cliente->cliente_codigo_vendedor = $lc->CODIGO_VENDEDOR ?: null;
                    $cliente->cliente_vendedor = $lc->VENDEDOR ?: null;
                    $cliente->cliente_telefono = $lc->TELEFONO ?: null;
                    $cliente->cliente_email = $lc->EMAIL ?: null;
                    $cliente->cliente_web = $lc->WEB ?: null;
                    $cliente->cliente_codigo_transportacion = $lc->CODIGO_DE_TRANSPORTISTA ?: null;
                    $cliente->cliente_transportista = $lc->TRANSPORTISTA ?: null;
                    $cliente->cliente_moneda = $lc->MONEDA ?: null;
                    $cliente->cliente_limite_credito = $lc->LIMITE_DE_CREDITO ?: null;
                    $cliente->cliente_credito_dolares = $lc->CREDITO_EN_DOLARES ?: null;
                    $cliente->cliente_credito_soles = $lc->CREDITO_EN_SOLES ?: null;
                    $cliente->cliente_porcentaje_descuento = $lc->PORCENTAJE_DESCUENTO ?: null;
                    $cliente->cliente_cliente_principal = $lc->CLIENTE_PRINCIPAL ?: null;
                    $cliente->cliente_cod_grupo = $lc->COD_GRUPO ?: null;
                    $cliente->cliente_ruc_grupo = $lc->RUC_GRUPO ?: null;
                    $cliente->cliente_nombre_grupo = $lc->NOMBRE_GRUPO ?: null;
                    $cliente->cliente_retencion = $lc->RETENCION ?: null;
                    $cliente->cliente_microtime = $microtime;
                    $cliente->cliente_estado_registro = 1;

                    $cliente->save();
                    $contadorCreados++;
                }
            }

            DB::commit();
            // Mostrar mensaje con todos los contadores
            session()->flash('success', "Sincronización completada: {$contadorActualizados} registros actualizados, {$contadorCreados} nuevos registros creados.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error al actualizar los vendedores: ' . $e->getMessage());
        }

        // Refrescar la vista
        $this->render();
    }

    public function obtener_direccion_entrega_cliente($codigo){
        $direccion = $this->server->obtener_direccion_cliente($codigo);
        $this->obtener_direccion = $direccion;
    }

    public function obtener_contacto_cliente($codigo){
        $contacto = $this->server->obtener_contacto_cliente($codigo);
        $this->obtener_contacto = $contacto;
    }
}
