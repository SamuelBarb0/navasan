<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Orden {{ $factura->orden->numero_orden }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
        }

        h2 {
            color: #16509D;
            border-bottom: 2px solid #16509D;
            padding-bottom: 5px;
        }

        .section {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        table, th, td {
            border: 1px solid #ccc;
        }

        th {
            background-color: #e6f0fa;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }

        .total {
            font-weight: bold;
            background-color: #f0f8ff;
        }

        .info {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

    <h2>Factura - Orden #{{ $factura->orden->numero_orden }}</h2>

    <div class="section">
        <p class="info"><strong>Cliente:</strong> {{ $factura->orden->cliente->nombre }}</p>
        <p class="info"><strong>NIT:</strong> {{ $factura->orden->cliente->nit ?? 'N/A' }}</p>
        <p class="info"><strong>Teléfono:</strong> {{ $factura->orden->cliente->telefono ?? 'N/A' }}</p>
        <p class="info"><strong>Fecha de Orden:</strong> {{ \Carbon\Carbon::parse($factura->orden->fecha)->format('d/m/Y') }}</p>
    </div>

    <div class="section">
        <h4>Items Producidos</h4>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->orden->items as $item)
                    <tr>
                        <td>{{ $item->nombre }}</td>
                        <td>{{ $item->cantidad }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h4>Insumos Usados</h4>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Cant. Requerida</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->orden->insumos as $detalle)
                    <tr>
                        <td>{{ $detalle->insumo->nombre }}</td>
                        <td>{{ $detalle->cantidad_requerida }}</td>
                        <td>{{ ucfirst($detalle->estado) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h4>Impresiones</h4>
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Máquina</th>
                    <th>Pliegos</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->orden->impresiones as $imp)
                    <tr>
                        <td>{{ $imp->tipo_impresion }}</td>
                        <td>{{ $imp->maquina }}</td>
                        <td>{{ $imp->cantidad_pliegos }}</td>
                        <td>{{ ucfirst($imp->estado) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h4>Acabados</h4>
        <table>
            <thead>
                <tr>
                    <th>Proceso</th>
                    <th>Realizado por</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->orden->acabados as $acabado)
                    <tr>
                        <td>{{ ucwords(str_replace('_', ' ', $acabado->proceso)) }}</td>
                        <td>{{ $acabado->realizado_por }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h4>Revisiones</h4>
        <table>
            <thead>
                <tr>
                    <th>Revisado por</th>
                    <th>Cantidad</th>
                    <th>Tipo</th>
                    <th>Comentarios</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->orden->revisiones as $rev)
                    <tr>
                        <td>{{ $rev->revisado_por }}</td>
                        <td>{{ $rev->cantidad }}</td>
                        <td>{{ ucfirst($rev->tipo) }}</td>
                        <td>{{ $rev->comentarios ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h4>Resumen de Facturación</h4>
        <table>
            <tbody>
                <tr>
                    <td><strong>Cantidad Final Producida</strong></td>
                    <td>{{ $factura->cantidad_final }}</td>
                </tr>
                <tr>
                    <td><strong>Costo por unidad</strong></td>
                    <td>$ {{ number_format($factura->costo_unitario, 2) }}</td>
                </tr>
                <tr class="total">
                    <td><strong>Total Facturado</strong></td>
                    <td>$ {{ number_format($factura->total, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Estado Facturación</strong></td>
                    <td>{{ ucfirst($factura->estado_facturacion) }}</td>
                </tr>
                <tr>
                    <td><strong>Fecha de Entrega</strong></td>
                    <td>{{ \Carbon\Carbon::parse($factura->fecha_entrega)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Método de Entrega</strong></td>
                    <td>{{ $factura->metodo_entrega }}</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>
