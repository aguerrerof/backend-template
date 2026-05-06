<!DOCTYPE html>
<html>
<head>
    <title>Desafío de Pago</title>
    @vite(['resources/js/app.js','resources/css/app.css'])
</head>
<body>
<div class="challenge-container">
    <h1>Redireccionando al Banco...</h1>
    <p>Por favor, espere mientras lo redirigimos para completar el desafío de seguridad.</p>

    <form id="idForm3DS" method="POST" style="display: none;" action="{{$urlCallback}}">
        @csrf
    </form>
    <script id="parameters-data" type="application/json">
        @json($parameters)
    </script>
</div>
</body>
</html>
