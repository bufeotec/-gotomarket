<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header">
            <div class="d-flex justify-content-center">
                <div class="logo ">
                    <a href="{{route('intranet')}}" class="d-flex align-items-center">
                        <img src="{{asset('isologoCompleteGo.png')}}" style="height: 40px;width: 40px" alt="Logo" srcset="">
                        <h3 class="mb-0 font-weight-bold ms-2" style="font-weight: 800!important;">Go To Market</h3>
                    </a>
                </div>
                <div class="toggler">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            @livewire('intranet.navegation')
        </div>
        <button class="sidebar-toggler btn x"><i data-feather="x"></i></button>
    </div>
</div>
