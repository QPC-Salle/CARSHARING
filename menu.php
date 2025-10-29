<?php
session_start();
// Página principal después de iniciar sesión
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Menú - CarSharing</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <main class="container">
        <h1>Menú principal</h1>
        <p>Bienvenido/a, <strong><?= htmlspecialchars($user['name']) ?></strong></p>
        <ul class="menu">
            <li><a href="#">Buscar coche</a></li>
            <li><a href="#">Mis reservas</a></li>
            <li><a href="#">Perfil</a></li>
        </ul>
        <p><a class="btn" href="logout.php">Cerrar sesión</a></p>
    </main>
</body>

</html>