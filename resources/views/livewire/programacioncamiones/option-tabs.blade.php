<div>
    <div class="row mt-3 mb-3 " id="pills-tab" role="tablist">
        <div class="col-lg-12" role="presentation">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                    <div class="d-flex align-items-center {{$estadoTabs == 1 ? 'activeType' : 'bg-white'}}" style="cursor:pointer;border-radius: 20px;" wire:click="$set('estadoTabs', 1)">
                        <img src="{{asset('local_n.png')}}" class="tamaIm me-2" alt="">
                        <h5 class="{{$estadoTabs == 1 ? 'text-white' : 'text-dark'}}">TRANSPORTE LOCAL</h5>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                    <div class="d-flex align-items-center {{$estadoTabs == 2 ? 'activeType' : 'bg-white'}}" style="cursor:pointer;border-radius: 20px;" wire:click="$set('estadoTabs', 2)">
                        <img src="{{asset('provi.svg')}}" class="tamaIm me-2" alt="">
                        <h5 class="{{$estadoTabs == 2 ? 'text-white' : 'text-dark'}}">TRANSPORTE PROVINCIAL</h5>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                    <div class="d-flex align-items-center {{$estadoTabs == 3 ? 'activeType' : 'bg-white'}}" style="cursor:pointer;border-radius: 20px;" wire:click="$set('estadoTabs', 3)">
                        <img src="{{asset('mix.svg')}}" class="tamaIm me-2" alt="">
                        <h5 class="{{$estadoTabs == 3 ? 'text-white' : 'text-dark'}}">TRANSPORTE MIXTO</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-content" id="pills-tabContent">
        @if($estadoTabs == 1)
            @livewire('programacioncamiones.local')
        @elseif($estadoTabs == 2)
            @livewire('programacioncamiones.provincial')
        @elseif($estadoTabs == 3)
            @livewire('programacioncamiones.mixto')
        @endif
    </div>
    <style>
        .tamaIm{
            width: 90px;
        }
        .activeType{
            background: #e51821!important;

        }
    </style>
</div>

