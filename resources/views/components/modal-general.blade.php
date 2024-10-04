<div {!! $attributes->merge(['class' => 'modal fade text-left modal-borderless']) !!} id="{{ $id_modal }}"
     tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
     aria-hidden="true">
    <div class="modal-dialog {{ $tama }} " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $titleModal }}</h5>
                <button type="button" class="close rounded-pill"
                        data-bs-dismiss="modal" aria-label="Close">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="modal-body">
                {{ $modalContent }}
            </div>
        </div>
    </div>
</div>
