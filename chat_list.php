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

// normalitzar correu de l'usuari
$me = strtolower(trim($_SESSION['user']['correu'] ?? ''));

// obtenir converses (route_id + other_email) amb últim missatge
$sqlQuery = "
SELECT t.route_id, t.other_email, m.message AS last_message, m.created_at AS last_at, u.nom AS other_name, r.origin, r.destination
FROM (
    SELECT route_id,
           CASE WHEN LOWER(sender_email)=? THEN LOWER(receiver_email) ELSE LOWER(sender_email) END AS other_email,
           MAX(created_at) AS last_at
    FROM messages
    WHERE LOWER(sender_email)=? OR LOWER(receiver_email)=?
    GROUP BY route_id, other_email
) t
JOIN messages m ON m.route_id = t.route_id AND m.created_at = t.last_at
LEFT JOIN usuaris u ON LOWER(u.correu) = t.other_email
LEFT JOIN rutes r ON r.id = t.route_id
ORDER BY t.last_at DESC
";

$conversations = $sql->select($sqlQuery, [$me, $me, $me]);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Les meves converses - CarSharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_chatlist.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <header class="mb-4">
        <h1 class="h4 mb-1">Les meves converses</h1>
        <p class="text-muted mb-0">Xats recents amb conductors i passatgers</p>
    </header>

    <?php if (empty($conversations)): ?>
        <div class="alert alert-info">No tens converses encara. Pots iniciar una des de la pàgina de rutes disponibles.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($conversations as $c): 
                $other = $c['other_email'] ?? '';
                $otherName = $c['other_name'] ?? $other;
                $routeLabel = (!empty($c['origin']) && !empty($c['destination'])) ? htmlspecialchars($c['origin'] . ' → ' . $c['destination']) : 'Ruta #' . (int)$c['route_id'];
                $chatUrl = 'chat.php?route_id=' . urlencode($c['route_id']) . '&participant=' . urlencode($other);
            ?>
                <a href="<?= htmlspecialchars($chatUrl) ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                    <div class="ms-2 me-auto">
                        <div class="fw-bold"><?= htmlspecialchars($otherName) ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($routeLabel) ?></div>
                        <div class="small text-truncate mt-1"><?= htmlspecialchars($c['last_message']) ?></div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted"><?= date('d/m H:i', strtotime($c['last_at'])) ?></div>
                        <div class="mt-2"><i class="bi bi-chat-dots text-primary"></i></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="menu.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Volver al menú</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
