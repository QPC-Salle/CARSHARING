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

// Normalize emails to avoid mismatches (trim + lowercase)
$me = strtolower(trim($_SESSION['user']['correu'] ?? ''));

// obtenir id
$route_id = isset($_GET['route_id']) ? (int)$_GET['route_id'] : 0;
if ($route_id <= 0) {
    header('Location: rutes_disponibles.php');
    exit;
}

// obtenir ruta i conductor
$route = $sql->fetch("SELECT r.*, u.nom AS driver_name, u.correu AS driver_email FROM rutes r JOIN usuaris u ON r.user_email = u.correu WHERE r.id = ?", [$route_id]);
if (!$route) {
    header('Location: rutes_disponibles.php');
    exit;
}

// normalitzar correu del driver
$driver_email = strtolower(trim($route['driver_email'] ?? ''));
$driver_name = $route['driver_name'] ?? '—';

// participant des de GET (opcional) i normalitzar
$participant = isset($_GET['participant']) ? strtolower(trim($_GET['participant'])) : null;

// Si l'usuari no és el conductor i no s'ha passat participant, el participant serà sempre el conductor
if ($me !== $driver_email && !$participant) {
    $participant = $driver_email;
}

// Si l'usuari és el conductor i no hi ha participant, mostrar llista de participants disponibles
$participants = [];
if ($me === $driver_email && !$participant) {
    // obtenir participants distincts normalitzats (excloure el conductor)
    $rows = $sql->select(
        "SELECT DISTINCT LOWER(email) AS participant FROM (
            SELECT sender_email AS email FROM messages WHERE route_id = ?
            UNION
            SELECT receiver_email AS email FROM messages WHERE route_id = ?
        ) t WHERE LOWER(email) != ?",
        [$route_id, $route_id, $driver_email]
    );
    foreach ($rows as $r) {
        if (!empty($r['participant'])) $participants[] = $r['participant'];
    }
}

// Proces envio de missatge
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $msg = trim($_POST['message']);
    $receiver = strtolower(trim($_POST['receiver'] ?? ''));

    if ($msg === '') {
        $errors[] = 'El mensaje no puede estar vacío.';
    } elseif ($receiver === '' ) {
        $errors[] = 'Destinatari invàlid.';
    } else {
        try {
            // Inserir correus normalitzats
            $sql->insert("INSERT INTO messages (route_id, sender_email, receiver_email, message) VALUES (?, ?, ?, ?)", [$route_id, $me, $receiver, $msg]);
            // redirigir per evitar reenvío del form; si hi ha participant mantenir-lo a la query
            $qs = "route_id={$route_id}";
            if (!empty($participant)) $qs .= "&participant=" . urlencode($participant);
            header("Location: chat.php?{$qs}");
            exit;
        } catch (Exception $e) {
            $errors[] = 'Error al enviar el mensaje.';
        }
    }
}

// Si tenim participant, carregar missatges entre me i participant
$messages = [];
if (!empty($participant)) {
    $other = $participant;
    $messages = $sql->select(
        "SELECT * FROM messages 
         WHERE route_id = ? 
           AND (
             (LOWER(sender_email) = ? AND LOWER(receiver_email) = ?)
             OR
             (LOWER(sender_email) = ? AND LOWER(receiver_email) = ?)
           )
         ORDER BY created_at ASC",
        [$route_id, $me, $other, $other, $me]
    );
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Chat - Ruta <?= htmlspecialchars($route['origin'] . ' → ' . $route['destination']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_chat.css">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0">Chat - <?= htmlspecialchars($route['origin'] . ' → ' . $route['destination']) ?></h5>
            <small class="text-muted">Conductor: <?= htmlspecialchars($driver_name) ?></small>
        </div>
        <div>
            <a href="route_details.php?id=<?= $route_id ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>
    </div>

    <?php if ($me === $driver_email && empty($participant)): ?>
        <!-- Llista de participants amb qui ha interaccionat per aquesta ruta -->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-3">Conversations</h6>
                <?php if (empty($participants)): ?>
                    <div class="text-muted">No hay conversaciones aún.</div>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($participants as $p): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div><?= htmlspecialchars($p) ?></div>
                                <a href="chat.php?route_id=<?= $route_id ?>&participant=<?= urlencode($p) ?>" class="btn btn-sm btn-primary">Abrir</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Conversa amb participant seleccionat -->
        <div class="card mb-3 chat-card">
            <div class="card-body chat-body">
                <?php if (empty($messages)): ?>
                    <div class="text-center text-muted">No hay mensajes todavía. Sé el primero en escribir.</div>
                <?php else: ?>
                    <?php foreach ($messages as $m): ?>
                        <?php $is_me = ($m['sender_email'] === $me); ?>
                        <div class="chat-message <?= $is_me ? 'me' : 'other' ?>">
                            <div class="msg-meta"><?= htmlspecialchars($is_me ? 'Tú' : $m['sender_email']) ?> · <span class="text-muted small"><?= date('d/m H:i', strtotime($m['created_at'])) ?></span></div>
                            <div class="msg-text"><?= nl2br(htmlspecialchars($m['message'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?php foreach ($errors as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
        <?php endif; ?>

        <form method="post" action="chat.php?route_id=<?= $route_id ?><?php if (!empty($participant)) echo '&participant=' . urlencode($participant); ?>">
            <input type="hidden" name="receiver" value="<?= htmlspecialchars(!empty($participant) ? $participant : $driver_email) ?>">
            <div class="input-group">
                <textarea name="message" class="form-control" rows="2" placeholder="Escribe un mensaje..." required></textarea>
                <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i></button>
            </div>
        </form>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
