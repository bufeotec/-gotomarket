<div>
    <div class="row mt-3 mb-3 " id="pills-tab" role="tablist">
        <div class="col-lg-12" role="presentation">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                    <div class="d-flex align-items-center {{$estadoTabs == 1 ? 'bg-primary' : ''}}" style="cursor:pointer;" wire:click="$set('estadoTabs', 1)">
                        <img src="{{asset('local_new.png')}}" class="tamaIm me-2" alt="">
                        <h5 class="{{$estadoTabs == 1 ? 'text-white' : 'text-dark'}}">TRANSPORTE LOCAL</h5>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                    <div class="d-flex align-items-center {{$estadoTabs == 2 ? 'bg-primary' : ''}}" style="cursor:pointer;" wire:click="$set('estadoTabs', 2)">
                        <img src="{{asset('provi_new.png')}}" class="tamaIm me-2" alt="">
                        <h5 class="{{$estadoTabs == 2 ? 'text-white' : 'text-dark'}}">TRANSPORTE PROVINCIAL</h5>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
                    <button class="btn w-100 {{$estadoTabs == 3 ? 'bg-primary' : ''}}" style="cursor:pointer;" wire:click="$set('estadoTabs', 3)" >MIXTO</button>
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
            width: 100px;
        }
    </style>
</div>

