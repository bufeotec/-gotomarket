<div>
    @php
        $rutaActual = explode('.',Request::route()->getName());
        $controlador = "";
        $funcion = "";
        if ($rutaActual[0]){
            $controlador =  \Illuminate\Support\Facades\DB::table('menus')->where('menu_controller','=',$rutaActual[0])->first();
        }
        if ($rutaActual[1]){
            $funcion =  \Illuminate\Support\Facades\DB::table('submenus')->where('submenu_function','=',$rutaActual[1])->first();
        }
    @endphp
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>{{$funcion ? $funcion->submenu_name : '' }}</h3>
                <p class="text-subtitle text-muted">{{ $text }}</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">{{$controlador ? $controlador->menu_name : '' }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{$funcion ? $funcion->submenu_name : '' }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
