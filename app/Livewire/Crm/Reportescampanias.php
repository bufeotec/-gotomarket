<?php

namespace App\Livewire\Crm;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Models\Logs;
use App\Models\Campania;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Reportescampanias extends Component{
    use WithPagination, WithoutUrlPagination;
    private $logs;
    private $campania;
    public function __construct(){
        $this->logs = new Logs();
        $this->campania = new Campania();
    }
    public $paginate_reporte = 10;
    public $id_campania = "";

    public function render(){
        $listar_campania = $this->campania->listar_campanias_ejecucion();
        $resultados = $this->campania->obtener_resultados_por_campania($this->id_campania, $this->paginate_reporte);

        // Obtener datos adicionales para cada resultado
        if(count($resultados) > 0 && $this->id_campania) {
            // Convertir la colección paginada a array para modificarla
            $resultadosArray = $resultados->items();

            foreach($resultadosArray as $key => $r) {
                // Obtener el usuario relacionado al vendedor
                $user = DB::table('users')
                    ->where('id_vendedor_intranet', '=', $r->id_vendedor_intranet)
                    ->first();

                $cant_premios_canjeados = 0;
                $puntos_canjeados_total = 0;

                if($user){
                    // Obtener todos los canjes de puntos para este usuario en esta campaña
                    $canjear_puntos = DB::table('canjear_puntos')
                        ->where('id_users', '=', $user->id_users)
                        ->where('id_campania', '=', $this->id_campania)
                        ->get();

                    // Calcular totales de premios y puntos canjeados
                    foreach($canjear_puntos as $cp) {
                        // Obtener detalles de cada canje
                        $detalles = DB::table('canjear_puntos_detalles')
                            ->where('id_canjear_punto', '=', $cp->id_canjear_punto)
                            ->get();

                        foreach($detalles as $detalle) {
                            $cant_premios_canjeados += (int)$detalle->canjear_punto_detalle_cantidad;
                            $puntos_canjeados_total += (int)$detalle->canjear_punto_detalle_total_puntos;
                        }
                    }
                }

                // Agregar datos al resultado como array
                $resultadosArray[$key] = (object) array_merge((array) $r, [
                    'cant_premios_canjeados' => $cant_premios_canjeados,
                    'puntos_canjeados_total' => $puntos_canjeados_total,
                    'puntos_ganados_total' => $r->vendedor_intranet_punto
                ]);
            }

            // Reemplazar los items de la paginación con nuestros datos modificados
            $resultados->setCollection(collect($resultadosArray));
        }

        return view('livewire.crm.reportescampanias', compact('listar_campania', 'resultados'));
    }

    public function generar_excel_detalle_cliente(){
        try {
            $reporte_cliente = $this->campania->reporte_por_cliente($this->id_campania);

            $spreadsheet = new Spreadsheet();
            $sheet1  = $spreadsheet->getActiveSheet();
            $sheet1->setTitle('Historial programación');


            // Formatear el nombre del archivo Excel
            $nombre_excel = sprintf("reporte_cliente.xlsx");

            $response = response()->stream(
                function () use ($spreadsheet) {
                    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename=' . $nombre_excel,
                ]
            );
            return $response;

        } catch (\Exception $e) {
            $this->logs->insertarLog($e);
            session()->flash('error', 'Ocurrió un error. Por favor, inténtelo nuevamente.');
        }
    }
}
