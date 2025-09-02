<div>
    <ul class="menu">
        <li class="sidebar-item">
            <a href="{{route('intranet')}}" class='sidebar-link'>
                <i class="bi bi-grid-fill"></i>
                <span>Inicio</span>
            </a>
        </li>
        @php $menutab = $urlactual_sidebar @endphp
        @foreach($list_menus as $list_men)
            @can($list_men->menu_controller)
                <li class="sidebar-item {{ ($menutab[0]==$list_men->menu_controller)? 'active' :''  }}  has-sub" wire:key="menu-{{ $list_men->id_menu }}">
                    <a href="#" class='sidebar-link'>
                        <i class="{{$list_men->menu_icons}} "></i> {{-- bi bi-grid-1x2-fill--}}
                        <span>{{$list_men->menu_name}}</span>
                    </a>
                    @if(count($list_men->submenu) > 0)
                        <ul class="submenu {{ ($menutab[0]==$list_men->menu_controller)? 'active' :''  }} ">
                            @foreach($list_men->submenu as $sub)
                                {{-- Condición para ocultar id_submenu 67 y 68 --}}
                                @if(!in_array($sub->id_submenu, [67, 68,69, 70, 71, 73]))
                                    @can($sub->submenu_function)
                                        <li class="submenu-item {{ isset($menutab[1]) && $sub->submenu_function == $menutab[1] ? 'active' : '' }}" wire:key="submenu-{{ $sub->id_submenu }}">
                                            <a href="{{ url($list_men->menu_controller . '/' . $sub->submenu_function) }}">
                                                {{ $sub->submenu_name }}
                                            </a>
                                        </li>
                                    @endcan
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endcan
        @endforeach
    </ul>
</div>
