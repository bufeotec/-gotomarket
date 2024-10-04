<div>
    <div class="table-responsive borderTable">
        <table {!! $attributes->merge(['class' => 'table product-table table-hover borderTable']) !!}>
            <thead class="thead-dark table_header_color">
            {{ $thead }}
            </thead>
            <tbody>
            {{ $tbody }}
            </tbody>
        </table>
    </div>
</div>
