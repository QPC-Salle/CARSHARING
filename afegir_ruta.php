<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Sql.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$config = require __DIR__ . '/config.php';
$sql = new Sql($config);
$errors = [];

$user_email = $_SESSION['user']['correu'] ?? null;

if (!$user_email) {
    // Esto podría indicar un problema grave en el inicio de sesión o la sesión.
    // Redireccionamos a login para que se vuelva a autenticar.
    header('Location: login.php?error=no_user_email');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $seats = (int)($_POST['seats'] ?? 1);
    $description = trim($_POST['description'] ?? '');

    if (!$origin || !$destination || !$date || !$time) {
        $errors[] = 'Todos los campos son obligatorios excepto la descripción.';
    }

    if (empty($errors)) {
        $date_time = date('Y-m-d H:i:s', strtotime("$date $time"));
        
        try {
            $sql->insert(
                "INSERT INTO rutes (user_email, origin, destination, date_time, seats, description, available) 
                 VALUES (?, ?, ?, ?, ?, ?, 1)",
                [$user_email, $origin, $destination, $date_time, $seats, $description]
            );
            header('Location: rutes_disponibles.php');
            exit;
        } catch (Exception $e) {
            $errors[] = 'Error al guardar la ruta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Añadir Ruta - CarSharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_afegir_ruta.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <header class="text-center mb-5">
            <img src="img/Logo.png" alt="CarSharing Logo" class="logo mb-3" style="max-width: 150px;">
            <h1 class="display-4 fw-bold">Añadir Nueva Ruta</h1>
        </header>

        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="afegir_ruta.php">
                            <div class="mb-3">
                                <label class="form-label">Origen</label>
                                <input type="text" name="origin" class="form-control" required 
                                       value="<?= htmlspecialchars($_POST['origin'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Destino</label>
                                <input type="text" name="destination" class="form-control" required
                                       value="<?= htmlspecialchars($_POST['destination'] ?? '') ?>">
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Fecha</label>
                                    <input type="date" name="date" class="form-control" required
                                           min="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Hora</label>
                                    <input type="time" name="time" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Plazas disponibles</label>
                                <input type="number" name="seats" class="form-control" min="1" max="8" 
                                       value="<?= htmlspecialchars($_POST['seats'] ?? '1') ?>">
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Descripción (opcional)</label>
                                <textarea name="description" class="form-control" rows="3"
                                ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-plus-circle me-2"></i>Añadir Ruta
                                </button>
                                <a href="menu.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Volver al Menú
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

