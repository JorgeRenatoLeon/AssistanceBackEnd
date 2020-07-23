<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
</head>
<body>
<p>Bienvenido {{ $usuario_destino->apellidos }}, {{ $usuario_destino->nombre }} a Assistance.</p>
<p>Estos son los datos de tu usuario en el sistema:</p>
<ul>
    <li>Usuario: {{ $usuario_destino->correo }}</li>
    <li>Cotraseña: {{ $password }}</li>
</ul>
<p> Saludos cordiales,
    Assistance Soporte</p>
</body>
</html>
