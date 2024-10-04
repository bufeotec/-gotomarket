<div {!! $attributes->merge(['class' => 'modal fade text-left modal-borderless']) !!} id="{{ $id_modal }}"
     tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                {{ $modalContentDelete }}
            </div>
        </div>
    </div>
</div>
