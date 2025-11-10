<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Sql.php';

$config = require __DIR__ . '/config.php';
$sql = new Sql($config);

// obtenir id
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: rutes_disponibles.php');
    exit;
}

// buscar ruta amb dades del conductor
$ruta = $sql->fetch(
    "SELECT r.*, u.nom AS driver_name, u.valoracio AS driver_valoracio, u.correu AS driver_email 
     FROM rutes r 
     JOIN usuaris u ON r.user_email = u.correu 
     WHERE r.id = ?",
    [$id]
);

?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Detall de la ruta - CarSharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_route_details.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <?php if (empty($ruta)): ?>
        <div class="alert alert-warning">Ruta no trobada. <a href="rutes_disponibles.php" class="link-primary">Tornar</a></div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <h3 class="me-3 mb-0"><?= htmlspecialchars($ruta['origin']) ?></h3>
                    <i class="bi bi-arrow-right mx-2 text-muted"></i>
                    <h3 class="ms-3 mb-0"><?= htmlspecialchars($ruta['destination']) ?></h3>
                </div>

                <div class="row mt-4">
                    <div class="col-md-8">
                        <ul class="list-unstyled mb-3">
                            <li><strong>Data i hora:</strong> <?= date('d/m/Y H:i', strtotime($ruta['date_time'])) ?></li>
                            <li><strong>Plazas disponibles:</strong> <?= (int)$ruta['seats'] ?></li>
                            <li><strong>Descripció:</strong><br><div class="mt-1"><?= nl2br(htmlspecialchars($ruta['description'] ?? '')) ?></div></li>
                            <li><strong>Disponible:</strong> <?= $ruta['available'] ? 'Sí' : 'No' ?></li>
                            <li><strong>Creada:</strong> <?= date('d/m/Y H:i', strtotime($ruta['created_at'])) ?></li>
                            <li><strong>Correu del conductor:</strong> <?= htmlspecialchars($ruta['user_email']) ?></li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <div class="border rounded p-3 bg-white">
                            <h6>Conductor</h6>
                            <p class="mb-1"><strong><?= htmlspecialchars($ruta['driver_name'] ?? '—') ?></strong></p>
                            <p class="mb-1 text-muted">Valoració: <?= isset($ruta['driver_valoracio']) ? htmlspecialchars($ruta['driver_valoracio']) : '—' ?></p>

                            <?php
                            // Preparar URLs de chat:
                            // - El propietari (conductor) ha de veure la llista de converses: chat.php?route_id=ID
                            // - Un usuari normal obrirà directament la conversa amb el driver: chat.php?route_id=ID&participant=driver_email
                            $chatUrlOwner = 'chat.php?route_id=' . urlencode($ruta['id']);
                            if (!empty($ruta['driver_email'])) {
                                $chatUrlUser = 'chat.php?route_id=' . urlencode($ruta['id']) . '&participant=' . urlencode($ruta['driver_email']);
                            } else {
                                $chatUrlUser = $chatUrlOwner;
                            }
                            ?>

                            <?php if (!empty($_SESSION['user']) && $_SESSION['user']['correu'] === ($ruta['driver_email'] ?? null)): ?>
                                <div class="mt-3">
                                    <a href="editar_ruta.php?id=<?= $ruta['id'] ?>" class="btn btn-sm btn-outline-primary w-100 mb-2">Editar</a>

                                    <form method="post" action="mis_rutes.php" onsubmit="return confirm('Segur que vols eliminar aquesta ruta?');">
                                        <input type="hidden" name="id" value="<?= $ruta['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100 mb-2">Eliminar</button>
                                    </form>

                                    <!-- Para el propietario: ver lista de conversaciones -->
                                    <a href="<?= htmlspecialchars($chatUrlOwner) ?>" class="btn btn-sm btn-primary w-100">Veure converses</a>
                                </div>
                            <?php else: ?>
                                <div class="mt-3">
                                    <?php if (!empty($_SESSION['user'])): ?>
                                        <!-- Para usuarios: abrir chat directo con el driver -->
                                        <a href="<?= htmlspecialchars($chatUrlUser) ?>" class="btn btn-sm btn-primary w-100 mb-2">
                                            <i class="bi bi-chat-dots me-2"></i>Chatear con el conductor
                                        </a>
                                    <?php endif; ?>
                                    <a href="mis_rutes.php" class="btn btn-sm btn-outline-secondary w-100 mt-1">Les meves rutes</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <a href="rutes_disponibles.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i>Tornar</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- bootstrap icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
