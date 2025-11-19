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

$user_email = $_SESSION['user']['correu'] ?? null;
if (!$user_email) {
    header('Location: login.php?error=no_user_email');
    exit;
}

// Gestionar eliminació (soft delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['id'])) {
    $id = (int)$_POST['id'];
    $sql->execute("UPDATE rutes SET available = 0 WHERE id = ? AND user_email = ?", [$id, $user_email]);
    header('Location: mis_rutes.php');
    exit;
}

// Obtenir rutes de l'usuari (mostrar només les disponibles)
$rutes = $sql->select("SELECT * FROM rutes WHERE user_email = ? AND available = 1 ORDER BY date_time DESC", [$user_email]);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Les meves rutes - CarSharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_global.css">
    <link rel="stylesheet" href="css/style_mis_rutes_professional.css">
</head>
<body class="bg-light">
    <?php require_once __DIR__ . '/includes/header.php'; ?>

    <main class="container py-5">
        <div class="mb-5">
            <h1 class="mb-1">Les meves rutes</h1>
            <p class="text-muted">Gestiona les rutes que has creat</p>
        </div>

        <?php if (empty($rutes)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h3 class="empty-state-title">No tens rutes publicades</h3>
                <p class="empty-state-text">Comença a compartir viatges creant una nova ruta</p>
                <a href="afegir_ruta.php" class="add-route-btn">
                    <i class="bi bi-plus-circle"></i>
                    Afegir nova ruta
                </a>
            </div>
        <?php else: ?>
            <div class="rutes-list">
                <?php foreach ($rutes as $rute): ?>
                    <div class="rute-card">
                        <div class="rute-card-body">
                            <!-- RUTE HEADER -->
                            <div class="rute-header">
                                <div class="rute-route">
                                    <i class="bi bi-geo-alt-fill rute-route-icon"></i>
                                    <div class="rute-route-info">
                                        <h5>
                                            <?= htmlspecialchars($rute['origin']) ?>
                                            <span class="route-arrow mx-2">→</span>
                                            <?= htmlspecialchars($rute['destination']) ?>
                                        </h5>
                                    </div>
                                </div>
                                <div class="rute-status">
                                    <span class="status-badge <?= $rute['available'] ? 'active' : 'inactive' ?>">
                                        <i class="bi <?= $rute['available'] ? 'bi-check-circle' : 'bi-x-circle' ?> me-1"></i>
                                        <?= $rute['available'] ? 'Activa' : 'Inactiva' ?>
                                    </span>
                                </div>
                            </div>

                            <!-- RUTE DETAILS -->
                            <div class="rute-details">
                                <div class="rute-detail">
                                    <div class="rute-detail-icon">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>
                                    <div class="rute-detail-info">
                                        <div class="rute-detail-label">Data i hora</div>
                                        <div class="rute-detail-value"><?= date('d/m/Y H:i', strtotime($rute['date_time'])) ?></div>
                                    </div>
                                </div>

                                <div class="rute-detail">
                                    <div class="rute-detail-icon">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="rute-detail-info">
                                        <div class="rute-detail-label">Plazas</div>
                                        <div class="rute-detail-value"><?= (int)$rute['seats'] ?> places</div>
                                    </div>
                                </div>

                                <div class="rute-detail">
                                    <div class="rute-detail-icon">
                                        <i class="bi bi-coin"></i>
                                    </div>
                                    <div class="rute-detail-info">
                                        <div class="rute-detail-label">Cost en tokens</div>
                                        <div class="rute-detail-value"><?= (int)($rute['token_cost'] ?? 0) ?> tokens</div>
                                    </div>
                                </div>

                                <div class="rute-detail">
                                    <div class="rute-detail-icon">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                    <div class="rute-detail-info">
                                        <div class="rute-detail-label">Publicada</div>
                                        <div class="rute-detail-value"><?= date('d/m/Y', strtotime($rute['created_at'])) ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- RUTE DESCRIPTION -->
                            <?php if (!empty($rute['description'])): ?>
                                <div class="rute-description show">
                                    <strong>Descripción:</strong><br>
                                    <?= htmlspecialchars($rute['description']) ?>
                                </div>
                            <?php endif; ?>

                            <!-- RUTE ACTIONS -->
                            <div class="rute-actions">
                                <a href="editar_ruta.php?id=<?= $rute['id'] ?>" class="btn-rute-action btn-edit">
                                    <i class="bi bi-pencil"></i>Editar
                                </a>

                                <form method="post" action="mis_rutes.php" style="display:contents;" onsubmit="return confirm('¿Seguro que deseas eliminar esta ruta?');">
                                    <input type="hidden" name="id" value="<?= $rute['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn-rute-action btn-delete">
                                        <i class="bi bi-trash"></i>Eliminar
                                    </button>
                                </form>

                                <a href="route_details.php?id=<?= $rute['id'] ?>" class="btn-rute-action btn-details">
                                    <i class="bi bi-eye"></i>Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-4 text-center">
                <a href="afegir_ruta.php" class="add-route-btn">
                    <i class="bi bi-plus-circle"></i>
                    Afegir nova ruta
                </a>
            </div>
        <?php endif; ?>

        <div class="mt-4 text-center">
            <a href="menu.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver al menú
            </a>
        </div>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
