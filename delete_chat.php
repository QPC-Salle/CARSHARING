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

$me = strtolower(trim($_SESSION['user']['correu'] ?? ''));
$route_id = isset($_POST['route_id']) ? (int)$_POST['route_id'] : 0;
$participant = isset($_POST['participant']) ? strtolower(trim($_POST['participant'])) : '';

if ($route_id <= 0 || !$participant) {
    $_SESSION['flash'] = 'Paràmetres invàlids.';
    header('Location: chat_list.php');
    exit;
}

try {
    // Eliminar tots els missatges entre l'usuari actual i el participant per aquesta ruta
    $sql->execute(
        "DELETE FROM messages 
         WHERE route_id = ? 
         AND (
            (LOWER(sender_email) = ? AND LOWER(receiver_email) = ?)
            OR
            (LOWER(sender_email) = ? AND LOWER(receiver_email) = ?)
         )",
        [$route_id, $me, $participant, $participant, $me]
    );
    $_SESSION['flash'] = 'Conversa eliminada correctament.';
} catch (Exception $e) {
    $_SESSION['flash'] = 'Error al eliminar la conversa.';
}

header('Location: chat_list.php');
exit;
?>
