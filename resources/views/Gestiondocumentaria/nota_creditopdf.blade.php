<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notas de Crédito</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
<h1>Notas de Crédito</h1>
<p>Lista de las notas de crédito registrados en el sistema.
</p>
<table>
    <thead>
    <tr>
        <th>N°</th>
        <th>Fecha de Emisión</th>
        <th>RUC del Cliente</th>
        <th>Nombre del Cliente</th>
        <th>Motivo </th>
    </tr>
    </thead>
    <tbody>
    @if(count($listar_nota_credito) > 0)
        @foreach($listar_nota_credito as $index => $lnc)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($lnc->created_at)->format('d/m/Y') }}</td>
                <td>{{ $lnc->nota_credito_ruc_cliente }}</td>
                <td>{{ $lnc->nota_credito_nombre_cliente }}</td>
                <td>
                    @php
                        $motivos = [
                            1 => 'Deuda',
                            2 => 'Calidad',
                            3 => 'Cobranza',
                            4 => 'Error de facturación',
                            5 => 'Otros comercial'
                        ];
                        $motivo = $motivos[$lnc->nota_credito_motivo] ?? 'Desconocido';
                    @endphp
                    {{ $motivo }}
                </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="6">No hay registros disponibles.</td>
        </tr>
    @endif
    </tbody>
</table>
</body>
</html>
