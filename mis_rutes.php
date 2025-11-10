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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Les meves rutes - CarSharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_mis_rutes.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <header class="text-center mb-4">
        <h1 class="h3">Les meves rutes</h1>
        <p class="text-muted">Gestiona les rutes que has creat</p>
    </header>

    <?php if (empty($rutes)): ?>
        <div class="alert alert-info">No tens rutes publicades. <a href="afegir_ruta.php">Crear-ne una</a>.</div>
    <?php else: ?>
        <?php foreach ($rutes as $rute): ?>
            <div class="card mb-3">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start">
                    <div class="me-3">
                        <h5 class="mb-1"><?= htmlspecialchars($rute['origin']) ?> <small class="text-muted">→</small> <?= htmlspecialchars($rute['destination']) ?></h5>
                        <div class="small text-muted mb-2">
                            <?= date('d/m/Y H:i', strtotime($rute['date_time'])) ?> · <?= (int)$rute['seats'] ?> places
                        </div>
                        <?php if (!empty($rute['description'])): ?>
                            <p class="mb-0"><?= htmlspecialchars($rute['description']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="mt-3 mt-md-0 text-md-end">
                        <a href="editar_ruta.php?id=<?= (int)$rute['id'] ?>" class="btn btn-sm btn-outline-primary mb-2">
                            <i class="bi bi-pencil"></i> Editar
                        </a>

                        <form method="post" action="mis_rutes.php" style="display:inline-block;" onsubmit="return confirm('Segur que vols eliminar aquesta ruta?');">
                            <input type="hidden" name="id" value="<?= (int)$rute['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="mt-4">
        <a href="afegir_ruta.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Afegir nova ruta</a>
        <a href="menu.php" class="btn btn-outline-secondary ms-2">Tornar al menú</a>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
