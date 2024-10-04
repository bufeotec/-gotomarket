{{--<div>--}}
{{--    @php--}}
{{--        $alertId = 'alert-' . uniqid();--}}
{{--    @endphp--}}
{{--    <div id="{{ $alertId }}" class="alert alert-light-{{ $type }} color-{{ $type }} alert-dismissible show fade">--}}
{{--        {{ $text }}--}}
{{--        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--    </div>--}}

{{--    <style>--}}
{{--        .fade {--}}
{{--            transition: opacity 0.5s linear;--}}
{{--        }--}}

{{--        .fade.show {--}}
{{--            opacity: 1;--}}
{{--        }--}}

{{--        .d-none {--}}
{{--            display: none;--}}
{{--        }--}}
{{--    </style>--}}
{{--</div>--}}

{{--<script>--}}
{{--    $(document).ready(function() {--}}
{{--        var $alertElement = $('#{{ $alertId }}');--}}

{{--        setTimeout(function() {--}}
{{--            if ($alertElement.length) {--}}
{{--                $alertElement.removeClass('show').addClass('fade');--}}
{{--            }--}}
{{--        }, {{ $duration * 1000 }});--}}

{{--        setTimeout(function() {--}}
{{--            if ($alertElement.length) {--}}
{{--                $alertElement.addClass('d-none');--}}
{{--            }--}}
{{--        }, {{ ($duration * 1000) + 500 }});--}}
{{--    });--}}
{{--</script>--}}

<div>
    @php
        $alertId = 'alert-' . uniqid();
    @endphp
    <div id="{{ $alertId }}" class="alert alert-light-{{ $type }} color-{{ $type }} alert-dismissible show fade">
        {{ $text }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <style>
        .fade {
            transition: opacity 0.5s linear;
        }

        .fade.show {
            opacity: 1;
        }

        .d-none {
            display: none;
        }
    </style>
</div>

<script>
    function handleAlertDismissal(alertId, duration) {
        var $alertElement = $('#' + alertId);

        setTimeout(function() {
            if ($alertElement.length) {
                $alertElement.removeClass('show').addClass('fade');
            }
        }, duration * 1000);

        setTimeout(function() {
            if ($alertElement.length) {
                $alertElement.addClass('d-none');
            }
        }, (duration * 1000) + 500);
    }

    $(document).ready(function() {
        handleAlertDismissal('{{ $alertId }}', {{ $duration }});
    });

    // Hook Livewire to reapply the alert handling after an update
    Livewire.hook('message.processed', (message, component) => {
        handleAlertDismissal('{{ $alertId }}', {{ $duration }});
    });
</script>
