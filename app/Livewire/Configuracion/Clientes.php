<?php

namespace App\Livewire\Configuracion;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Cliente;
use App\Models\Clientedireccion;
use App\Models\Clientecontacto;
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
     private $clientedireccion;
     private $clientecontacto;
    public function __construct(){
        $this->logs = new Logs();
        $this->cliente = new Cliente();
        $this->server = new Server();
        $this->clientedireccion = new Clientedireccion();
        $this->clientecontacto = new Clientecontacto();
    }
    public $search_cliente;
    public $pagination_cliente = 10;
    public $listar_clientes = [];

    // DIRECCIONES
    public $listar_direccion_cliente = [];
    public $obtener_direccion_cliente = [];
    public $cliente_nombre_cliente = '';

    // CONTACTOS
    public $listar_contacto_cliente = [];
    public $obtener_contacto_cliente_new = [];

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

        if (!Gate::allows('obtener_direccion_entrega_cliente')) {
            session()->flash('error_modal', 'No tiene permisos para ver las direcciones del cliente.');
            return;
        }

        try {
            DB::beginTransaction();

            $cliente = DB::table('clientes')
                ->where('cliente_codigo_cliente', $codigo)
                ->where('cliente_estado_registro', 1)
                ->first();

            if (!$cliente) {
                session()->flash('error_modal', 'No se encontró el cliente.');
                DB::rollBack();
                return;
            }

            $this->cliente_nombre_cliente = $cliente->cliente_nombre_cliente;
            $id_cliente = $cliente->id_cliente;

            $resultado_direccion_cliente = $this->server->obtener_direccion_cliente($codigo);
            $this->obtener_direccion_cliente = $resultado_direccion_cliente;

            $contadorActualizados = 0;
            $contadorCreados = 0;
            $contadorIgnorados = 0;

            // Verificar si hay datos para procesar
            if (!empty($this->obtener_direccion_cliente)) {
                foreach ($this->obtener_direccion_cliente as $lc) {
                    // Validar que los campos necesarios existan
                    if (empty($lc->CODIGO_CLIENTE)) {
                        $contadorIgnorados++;
                        continue;
                    }

                    // Buscar si ya existe un registro con este id_stock Y lote específico
                    $direccionClienteExistente = Clientedireccion::where('cliente_direccion_codigo_cliente', $lc->CODIGO_CLIENTE)->first();

                    if ($direccionClienteExistente) {
                        // Si el registro existe pero tiene estado 0, lo ignoramos
                        if ($direccionClienteExistente->cliente_direccion_estado == 0) {
                            $contadorIgnorados++;
                            continue;
                        }

                        $microtime = microtime(true);

                        // Actualizar registro existente
                        $direccionClienteExistente->id_cliente = $id_cliente;
                        $direccionClienteExistente->cliente_direccion_codigo_direccion = $lc->CODIGO_DIRECCION ?? null;
                        $direccionClienteExistente->cliente_direccion_codigo_cliente = $lc->CODIGO_CLIENTE ?? null;
                        $direccionClienteExistente->cliente_direccion_ruc_cliente = $lc->RUC_CLIENTE ?? null;
                        $direccionClienteExistente->cliente_direccion_nombre_cliente = $lc->NOMBRE_CLIENTE ?? null;
                        $direccionClienteExistente->cliente_direccion_direccion_entrega = $lc->DIRECCION_DE_ENTREGA ?? null;
                        $direccionClienteExistente->cliente_direccion_ubigeo = $lc->UBIGEO ?? null;
                        $direccionClienteExistente->cliente_direccion_departamento = $lc->DEPARTAMENTO ?? null;
                        $direccionClienteExistente->cliente_direccion_provincia = $lc->PROVINCIA ?? null;
                        $direccionClienteExistente->cliente_direccion_distrito = $lc->DISTRITO ?? null;
                        $direccionClienteExistente->cliente_direccion_microtime = $microtime;

                        $direccionClienteExistente->save();
                        $contadorActualizados++;
                    } else {
                        // Crear nuevo registro
                        $microtime = microtime(true);
                        $direccion_cliente = new Clientedireccion();
                        $direccion_cliente->id_users = Auth::id();
                        $direccion_cliente->id_cliente = $id_cliente;
                        $direccion_cliente->cliente_direccion_codigo_direccion = $lc->CODIGO_DIRECCION ?? null;
                        $direccion_cliente->cliente_direccion_codigo_cliente = $lc->CODIGO_CLIENTE ?? null;
                        $direccion_cliente->cliente_direccion_ruc_cliente = $lc->RUC_CLIENTE ?? null;
                        $direccion_cliente->cliente_direccion_nombre_cliente = $lc->NOMBRE_CLIENTE ?? null;
                        $direccion_cliente->cliente_direccion_direccion_entrega = $lc->DIRECCION_DE_ENTREGA ?? null;
                        $direccion_cliente->cliente_direccion_ubigeo = $lc->UBIGEO ?? null;
                        $direccion_cliente->cliente_direccion_departamento = $lc->DEPARTAMENTO ?? null;
                        $direccion_cliente->cliente_direccion_provincia = $lc->PROVINCIA ?? null;
                        $direccion_cliente->cliente_direccion_distrito = $lc->DISTRITO ?? null;
                        $direccion_cliente->cliente_direccion_microtime = $microtime;
                        $direccion_cliente->cliente_direccion_estado = 1;

                        $direccion_cliente->save();
                        $contadorCreados++;
                    }
                }
            }

            DB::commit();

            // Después de actualizar/crear, obtener los datos para mostrar en el modal
            $this->listar_direccion_cliente = Clientedireccion::where('id_cliente', $id_cliente)
                ->where('cliente_direccion_estado', 1)
                ->orderBy('id_cliente')
                ->get();

            // Mostrar mensaje de éxito
            session()->flash('success_modal', "Sincronización completada: {$contadorActualizados} registros actualizados, {$contadorCreados} nuevos registros creados.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_modal', 'Ocurrió un error al actualizar los stock: ' . $e->getMessage());
        }
    }

    public function obtener_contacto_cliente($codigo){

        if (!Gate::allows('obtener_contacto_cliente')) {
            session()->flash('error_modal_cont', 'No tiene permisos para ver los contactos del cliente.');
            return;
        }

        try {
            DB::beginTransaction();

            $cliente_c = DB::table('clientes')
                ->where('cliente_codigo_cliente', $codigo)
                ->where('cliente_estado_registro', 1)
                ->first();

            if (!$cliente_c) {
                session()->flash('error_modal_cont', 'No se encontró el cliente.');
                DB::rollBack();
                return;
            }

            $this->cliente_nombre_cliente = $cliente_c->cliente_nombre_cliente;
            $id_cliente = $cliente_c->id_cliente;

            $resultado_contacto_cliente = $this->server->obtener_contacto_cliente($codigo);
            $this->obtener_contacto_cliente_new = $resultado_contacto_cliente;

            $contadorActualizados = 0;
            $contadorCreados = 0;
            $contadorIgnorados = 0;

            // Verificar si hay datos para procesar
            if (!empty($this->obtener_contacto_cliente_new)) {
                foreach ($this->obtener_contacto_cliente_new as $lc) {
                    // Validar que los campos necesarios existan
                    if (empty($lc->CODIGO_CLIENTE)) {
                        $contadorIgnorados++;
                        continue;
                    }

                    // Buscar si ya existe un registro con este id_stock Y lote específico
                    $contacto_cliente_existente = Clientecontacto::where('cliente_contacto_codigo_cliente', $lc->CODIGO_CLIENTE)->first();

                    if ($contacto_cliente_existente) {
                        // Si el registro existe pero tiene estado 0, lo ignoramos
                        if ($contacto_cliente_existente->cliente_contacto_estado == 0) {
                            $contadorIgnorados++;
                            continue;
                        }

                        $microtime = microtime(true);

                        // Actualizar registro existente
                        $contacto_cliente_existente->id_cliente = $id_cliente;
                        $contacto_cliente_existente->cliente_contacto_codigo_contacto = $lc->CODIGO_CONTACTO ?? null;
                        $contacto_cliente_existente->cliente_contacto_codigo_cliente = $lc->CODIGO_CLIENTE ?? null;
                        $contacto_cliente_existente->cliente_contacto_ruc_cliente = $lc->RUC_CLIENTE ?? null;
                        $contacto_cliente_existente->cliente_contacto_nombre_cliente = $lc->NOMBRE_CLIENTE ?? null;
                        $contacto_cliente_existente->cliente_contacto_nombre = $lc->NOMBRE ?? null;
                        $contacto_cliente_existente->cliente_contacto_telefono = $lc->TELEFONO ?? null;
                        $contacto_cliente_existente->cliente_contacto_correo = $lc->CORREO ?? null;
                        $contacto_cliente_existente->cliente_contacto_area = $lc->AREA ?? null;
                        $contacto_cliente_existente->cliente_contacto_cargo = $lc->CARGO ?? null;
                        $contacto_cliente_existente->cliente_contacto_celular = $lc->CELULAR ?? null;
                        $contacto_cliente_existente->cliente_contacto_microtime = $microtime;

                        $contacto_cliente_existente->save();
                        $contadorActualizados++;
                    } else {
                        // Crear nuevo registro
                        $microtime = microtime(true);
                        $contacto_cliente = new Clientecontacto();
                        $contacto_cliente->id_users = Auth::id();
                        $contacto_cliente->id_cliente = $id_cliente;
                        $contacto_cliente->cliente_contacto_codigo_contacto = $lc->CODIGO_CONTACTO ?? null;
                        $contacto_cliente->cliente_contacto_codigo_cliente = $lc->CODIGO_CLIENTE ?? null;
                        $contacto_cliente->cliente_contacto_ruc_cliente = $lc->RUC_CLIENTE ?? null;
                        $contacto_cliente->cliente_contacto_nombre_cliente = $lc->NOMBRE_CLIENTE ?? null;
                        $contacto_cliente->cliente_contacto_nombre = $lc->NOMBRE ?? null;
                        $contacto_cliente->cliente_contacto_telefono = $lc->TELEFONO ?? null;
                        $contacto_cliente->cliente_contacto_correo = $lc->CORREO ?? null;
                        $contacto_cliente->cliente_contacto_area = $lc->AREA ?? null;
                        $contacto_cliente->cliente_contacto_cargo = $lc->CARGO ?? null;
                        $contacto_cliente->cliente_contacto_celular = $lc->CELULAR ?? null;
                        $contacto_cliente->cliente_contacto_microtime = $microtime;
                        $contacto_cliente->cliente_contacto_estado = 1;

                        $contacto_cliente->save();
                        $contadorCreados++;
                    }
                }
            }

            DB::commit();

            // Después de actualizar/crear, obtener los datos para mostrar en el modal
            $this->listar_contacto_cliente = Clientecontacto::where('id_cliente', $id_cliente)
                ->where('cliente_contacto_estado', 1)
                ->orderBy('id_cliente')
                ->get();

            // Mostrar mensaje de éxito
            session()->flash('success_modal_cont', "Sincronización completada: {$contadorActualizados} registros actualizados, {$contadorCreados} nuevos registros creados.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logs->insertarLog($e);
            session()->flash('error_modal_cont', 'Ocurrió un error al actualizar los stock: ' . $e->getMessage());
        }
    }
}
