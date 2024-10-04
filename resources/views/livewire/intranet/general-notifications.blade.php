<div>
    <a class="nav-link nav-link22 active dropdown-toggle text-center" href="#" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell bi-sub fs-4 text-gray-600"></i>
        @if(count($notifications) > 0)
            <span class="notificationCount">{{count($notifications)}}</span>
        @endif
    </a>
    <ul class="dropdown-menu dropdown_menu_end_notifi dropdown-menu-end " aria-labelledby="dropdownMenuButton">
        <li>
            <h6 class="dropdown-header">Notifications</h6>
        </li>
        @forelse ($notifications as $notification)
            <li wire:key="{{ $notification->id }}"  class="dropdown-item {{ $notification->data['type'] }}" style="cursor: pointer">
                @php
                    $type = match ($notification->data['type']) {
                        'success' => '<i class="fa-solid fa-check"></i>',
                        'danger' => '<i class="fa-solid fa-x"></i>',
                        'warning' => '<i class="fa-solid fa-circle-exclamation"></i>',
                        default => '<i class="fa-solid fa-triangle-exclamation"></i>',
                    };
                @endphp
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center flex-grow-1" wire:click="notification_information('{{ $notification->id }}')"  data-bs-toggle="modal" data-bs-target="#modalInformationNotificacion">
                        <div class="avatar bg-{{$notification->data['type']}} me-2">
                            <span class="avatar-content">@php echo $type @endphp</span>
                        </div>
                        <div class="notification-title" style="flex-grow: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <strong>{{ $notification->data['title'] }}</strong>
                        </div>
                    </div>
                    <span wire:click="markAsRead('{{ $notification->id }}')" class="btn btn-sm btn-link text-end ms-2">
                        <i class="fa-solid fa-x"></i>
                    </span>
                </div>
                @php
                    // Establecer la configuración regional a español
                    setlocale(LC_TIME, 'es_ES.UTF-8');
                    // Crear un objeto DateTime a partir de la cadena de fecha
                    $date = new DateTime($notification->created_at);
                    // Formatear la fecha y hora con strftime
                    $formattedDateTime = strftime('%d %b, %Y %H:%M:%S', $date->getTimestamp());
                @endphp
                <small class="notification-date text-muted ms-3">{{ $formattedDateTime }}</small>

            </li>
        @empty
            <li><a class="dropdown-item">No hay notificaciones disponibles</a></li>
        @endforelse
    </ul>

    <x-modal-delete  wire:ignore.self >
        <x-slot name="id_modal">modalInformationNotificacion</x-slot>
        <x-slot name="modalContentDelete">
            @php
                $type = "";
                if ($info_notifications){
                  $type = match ($info_notifications->data['type']) {
                    'success' => '<i class="fa-solid fa-check"></i>',
                    'danger' => '<i class="fa-solid fa-x"></i>',
                    'warning' => '<i class="fa-solid fa-circle-exclamation"></i>',
                    default => '<i class="fa-solid fa-triangle-exclamation"></i>',
                  };
                }
            @endphp
            <div class="notification_general w-100 success">
                <div class="icon avatar bg-{{$info_notifications ? $info_notifications->data['type'] : ''}} me-2">
                    <span class="avatar-content">@php echo $type @endphp</span>
                </div>
                <div class="content">
                    <strong>{{$info_notifications ? $info_notifications->data['title'] : ''}}</strong>
                    <p>{{$info_notifications ? $info_notifications->data['message'] : ''}}</p>
                </div>
                <div class="close">
                    <i class="fa-solid fa-times"></i>
                </div>
            </div>

        </x-slot>
    </x-modal-delete>


</div>
@script

<script>
    $wire.on('readNoti', () => {
        Livewire.dispatch('refreshComponenteNotificaciones');
    });
</script>

@endscript
