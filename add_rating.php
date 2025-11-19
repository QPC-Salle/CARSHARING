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

$rater = strtolower(trim($_SESSION['user']['correu'] ?? ''));
$route_id = isset($_POST['route_id']) ? (int)$_POST['route_id'] : 0;
$rated_user = isset($_POST['rated_user']) ? strtolower(trim($_POST['rated_user'])) : '';
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($route_id <= 0 || !$rated_user || $rating < 1 || $rating > 5 || $rater === $rated_user) {
    $_SESSION['flash'] = 'Paràmetres invàlids.';
    header("Location: route_details.php?id={$route_id}");
    exit;
}

// VALIDACIÓ: comprovar que l'usuari ha reservat aquesta ruta
$hasReservation = $sql->fetch(
    "SELECT id FROM reservas WHERE route_id = ? AND LOWER(user_email) = ?",
    [$route_id, $rater]
);

if (!$hasReservation) {
    $_SESSION['flash'] = 'No pots valorar una ruta si no l\'has reservat.';
    header("Location: route_details.php?id={$route_id}");
    exit;
}

try {
    // Comprovar si ja existeix valoració
    $exists = $sql->fetch(
        "SELECT id FROM valoracions WHERE route_id = ? AND LOWER(rated_user_email) = ? AND LOWER(rater_email) = ?",
        [$route_id, $rated_user, $rater]
    );

    if ($exists) {
        // Actualitzar
        $sql->execute(
            "UPDATE valoracions SET rating = ?, comment = ?, created_at = NOW() WHERE route_id = ? AND LOWER(rated_user_email) = ? AND LOWER(rater_email) = ?",
            [$rating, $comment, $route_id, $rated_user, $rater]
        );
        $_SESSION['flash'] = 'Valoració actualitzada correctament.';
    } else {
        // Inserir nova
        $sql->insert(
            "INSERT INTO valoracions (route_id, rated_user_email, rater_email, rating, comment) VALUES (?, ?, ?, ?, ?)",
            [$route_id, $rated_user, $rater, $rating, $comment]
        );
        $_SESSION['flash'] = 'Valoració afegida correctament.';
    }

    // Actualitzar valoració promig a usuaris
    updateUserRating($sql, $rated_user);

} catch (Exception $e) {
    $_SESSION['flash'] = 'Error al guardar la valoració.';
}

header("Location: route_details.php?id={$route_id}");
exit;

function updateUserRating($sql, $email) {
    $avg = $sql->fetch(
        "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM valoracions WHERE LOWER(rated_user_email) = ?",
        [$email]
    );
    if ($avg && $avg['count'] > 0) {
        $sql->execute(
            "UPDATE usuaris SET valoracio = ? WHERE LOWER(correu) = ?",
            [round($avg['avg_rating'], 2), $email]
        );
    }
}
?>
