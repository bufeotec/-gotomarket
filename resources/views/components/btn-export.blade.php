<button {!! $attributes->merge(['class' => 'btn  create-new ms-3']) !!} >
    <span>
        <i class="{{$icons}} me-sm-1"></i>
        <span class="d-none d-sm-inline-block">{{ $slot }}</span>
    </span>
</button>
