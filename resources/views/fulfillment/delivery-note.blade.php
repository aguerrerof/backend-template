<!-- resources/views/guia_entrega.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guía de Entrega</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            width: 80mm;
            margin: 0;
            padding: 5px;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .logo {
            width: 100px;
            margin-bottom: 5px;
        }

        .section {
            margin-bottom: 5px;
        }

        .section strong {
            display: block;
            margin-bottom: 2px;
        }

        .barcode {
            text-align: center;
            font-size: 10px;
            border-top: 1px dashed #000;
            margin-top: 5px;
            padding-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 3px;
        }

        .signature {
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 5px;
            font-size: 10px;
        }
    </style>
</head>
<body>
<div class="header">
    <strong>{{ config('app.name', 'Application') }}</strong><br>
    <strong>Guía Nº:</strong> {{ $tracking_number ?? '________' }}<br>
    <strong>Fecha:</strong> {{ $dispatched_at ?? '____/____/______' }}
</div>

<div class="section">
    <strong>Remitente</strong>
    {{ $shipper_name ?? '________________' }}<br>
    Ciudad: {{ $shipper_address ?? '________________' }}<br>
    Tel: {{ $shipper_phone ?? '________________' }}
</div>

<div class="section">
    <strong>Destinatario</strong>
    {{ $recipient_name ?? '________________' }}<br>
    Dir: {{ ucwords($recipient_address ?? '________________') }}<br>
    Tel: {{ $recipient_phone ?? '________________' }}
</div>

<div class="section">
    <strong>Destino:</strong> {{ '________________' }} / {{  '________________' }}<br>
    <strong>Contenido:</strong>
    @if($line_items)
        @foreach($line_items as $lineItem)
            @if(in_array(['quantity','name'],$lineItem))
                {{ $lineItem['quantity'].' - '.$lineItem['name']  }}<br>
            @endif
        @endforeach
    @endif
    <br>
    <strong>Piezas:</strong>  ________________
</div>

<div class="section barcode">
    <strong>Guía:</strong> {{ $tracking_number ?? '________' }}
</div>

<div class="section">
    <strong>Observaciones:</strong><br>
    {{ $notes ?? '________________' }}
</div>

<div class="signature">
    <strong>Firma del Remitente:</strong> ___________________________<br>
    <strong>Firma del Destinatario:</strong> _________________________
</div>
</body>
</html>
