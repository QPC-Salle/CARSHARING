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

$user_email = strtolower(trim($_SESSION['user']['correu'] ?? ''));
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (!$nom) {
    $_SESSION['flash'] = 'El nom és obligatori.';
    header('Location: profile.php');
    exit;
}

try {
    if (!empty($password)) {
        // Actualizar amb nova contrasenya
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql->execute(
            "UPDATE usuaris SET nom = ?, contrasenya = ? WHERE LOWER(correu) = ?",
            [$nom, $hashed, $user_email]
        );
        $_SESSION['flash'] = 'Dades i contrasenya actualitzades correctament.';
    } else {
        // Actualizar només el nom
        $sql->execute(
            "UPDATE usuaris SET nom = ? WHERE LOWER(correu) = ?",
            [$nom, $user_email]
        );
        $_SESSION['flash'] = 'Dades actualitzades correctament.';
    }
    
    // Actualitzar sessió
    $_SESSION['user']['nom'] = $nom;

} catch (Exception $e) {
    $_SESSION['flash'] = 'Error al actualitzar les dades.';
}

header('Location: profile.php');
exit;
?>
