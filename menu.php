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
    <link rel="stylesheet" href="css/style_menu.css">
</head>

<body>
    <main class="container">
        <h1>Menú principal</h1>
        <p>Bienvenido/a, <strong><?= htmlspecialchars($user['nom']) ?></strong></p>
        <ul class="menu">
            <li><a href="rutes_disponibles.php">Rutes Disponibles</a></li>
            <li><a href="afegir_ruta.php">Afegir ruta</a></li>
            <li><a href="mis_rutes.php">Modificar rutes</a></li>
            <li><a href="#">Mapa</a></li>
            <li><a href="chat_list.php">Veure Converses</a></li>
        </ul>
        <p><a class="btn" href="logout.php">Cerrar sesión</a></p>
    </main>
</body>

</html>