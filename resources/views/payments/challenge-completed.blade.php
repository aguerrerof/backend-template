<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Transacci&oacute;n Exitosa</title>
    @vite(['resources/css/app.css'])
</head>
<body>
<div class="challenge-container">
    <div class="icon">&#10004;</div>
    <h1 class="challenge-title">¡Transacción Exitosa!</h1>
    <p class="challenge-message">Tu operación ha sido procesada correctamente</p>
    @if(isset($showReturn) && $showReturn)
        <a href="/" class="home-link">Volver a la página de inicio</a>
    @endif
</div>
</body>
</html>
