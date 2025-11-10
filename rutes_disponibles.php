<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Sql.php';

$config = require __DIR__ . '/config.php';
$sql = new Sql($config);

// Obtener rutas disponibles usando prepared statement
$rutes = $sql->select(
    "SELECT r.*, u.nom as username 
     FROM rutes r 
     JOIN usuaris u ON r.user_email = u.correu 
     WHERE r.available = ? 
     ORDER BY r.date_time DESC",
    [1]
);

// Si no hay rutas, crear una ruta de ejemplo
if (empty($rutes)) {
    $rutes = [[
        'id' => 1,
        'user_email' => 'ejemplo@email.com',
        'origin' => 'Lleida',
        'destination' => 'Barcelona',
        'username' => 'Juan García',
        'date_time' => '2024-02-20 08:00:00',
        'seats' => 3,
        'description' => 'Viaje directo por la A-2. Salida desde la estación de tren.',
        'available' => true,
        'created_at' => '2024-02-19 10:00:00'
    ]];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rutas Disponibles - CarSharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_rutesdisponibles.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <header class="text-center mb-5">
            <img src="img/Logo.png" alt="CarSharing Logo" class="logo mb-3" style="max-width: 150px;">
            <h1 class="display-4 fw-bold">Rutas Disponibles</h1>
        </header>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <?php if (empty($rutes)): ?>
                    <div class="alert alert-info shadow-sm">
                        <i class="bi bi-info-circle me-2"></i>No hay rutas disponibles en este momento.
                    </div>
                <?php else: ?>
                    <?php foreach ($rutes as $rute): ?>
                        <div class="card mb-4 shadow-sm hover-shadow">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h5 class="card-title mb-3">
                                            <i class="bi bi-geo-alt text-primary me-2"></i>
                                            <?= htmlspecialchars($rute['origin']) ?> 
                                            <i class="bi bi-arrow-right mx-2"></i> 
                                            <?= htmlspecialchars($rute['destination']) ?>
                                        </h5>
                                        <div class="card-text text-muted small">
                                            <p class="mb-1">
                                                <i class="bi bi-person-circle me-2"></i>
                                                <?= htmlspecialchars($rute['username']) ?>
                                            </p>
                                            <p class="mb-1">
                                                <i class="bi bi-calendar-event me-2"></i>
                                                <?= date('d/m/Y H:i', strtotime($rute['date_time'])) ?>
                                            </p>
                                            <p class="mb-1">
                                                <i class="bi bi-people me-2"></i>
                                                <?= $rute['seats'] ?> plazas disponibles
                                            </p>
                                            <?php if (!empty($rute['description'])): ?>
                                                <p class="mb-0 mt-2">
                                                    <i class="bi bi-info-circle me-2"></i>
                                                    <?= htmlspecialchars($rute['description']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <div class="d-flex flex-column align-items-md-end">
                                            <a href="route_details.php?id=<?= $rute['id'] ?>" 
                                               class="btn btn-outline-primary">
                                                <i class="bi bi-eye me-2"></i>Ver detalles
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="text-center mt-5">
                    <a href="menu.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver al menú
                    </a>
                    <a href="logout.php" class="btn btn-outline-danger ms-2">
                        <i class="bi bi-box-arrow-right me-2"></i>Salir
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
