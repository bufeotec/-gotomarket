<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\Notacredito;
use App\Models\Facturaspreprogramacion;
use PDF;
use Illuminate\Http\Request;

class GestiondocumentariaController extends Controller
{
    private $logs;
    private $notacredito;
    private $facturapreprogramacion;

    public function __construct()
    {
        $this->logs = new Logs();
        $this->notacredito = new Notacredito();
        $this->facturapreprogramacion = new Facturaspreprogramacion();
    }

    public function nota_credito(){
        try{
            return view('Gestiondocumentaria.nota_credito');
        }catch(Exception $e){
            $this->logs->insertarLog($e);
            return redirect()->route('intranet')->with('error', 'Ocurrió un error al mostrar el contenido');
        }
    }

    public function exportToPdf()
    {
        $listar_nota_credito = $this->notacredito->all();

        // Cargar la vista y pasar los datos
        $pdf = PDF::loadView('Gestiondocumentaria.nota_creditopdf', ['listar_nota_credito' => $listar_nota_credito]);

        // Descargar el PDF
        return $pdf->download('notas_credito.pdf');
    }

}
