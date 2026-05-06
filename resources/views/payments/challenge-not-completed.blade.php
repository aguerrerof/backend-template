<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error en la Transacción</title>
    @vite(['resources/css/app.css'])
</head>
<body>

<div class="challenge-container">
    <div class="challenge-icon">
        &#x274C;
    </div>
    <h1 class="challenge-title">Transacci&oacute;n no pudo ser completada</h1>
    @if(isset($description) && $description)
        <p class="challenge-message">M&oacute;tivo: {{$description}}.</p>
    @endif
    @if(isset($showReturn) && $showReturn)
        <a href="{{ url('/') }}" class="back-link">Volver al inicio</a>
    @endif
</div>
</body>
</html>
