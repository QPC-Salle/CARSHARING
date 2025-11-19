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
$action = $_POST['action'] ?? '';
$route_id = isset($_POST['route_id']) ? (int)$_POST['route_id'] : 0;

if ($route_id <= 0 || !in_array($action, ['reserve', 'cancel'])) {
    header('Location: rutes_disponibles.php');
    exit;
}

try {
    if ($action === 'reserve') {
        // Comprovar si ja està reservat
        $exists = $sql->fetch("SELECT id FROM reservas WHERE route_id = ? AND LOWER(user_email) = ?", [$route_id, $user_email]);
        if ($exists) {
            $_SESSION['flash'] = 'Ja tens una reserva per aquesta ruta.';
            header("Location: route_details.php?id={$route_id}");
            exit;
        }
        // Comprovar places disponibles i cost en tokens
        $route = $sql->fetch("SELECT seats, available, token_cost, user_email FROM rutes WHERE id = ?", [$route_id]);
        if (!$route || !$route['available'] || (int)$route['seats'] <= 0) {
            $_SESSION['flash'] = 'No hi ha places disponibles.';
            header("Location: route_details.php?id={$route_id}");
            exit;
        }
        $cost = (int)($route['token_cost'] ?? 0);
        if ($cost > 0) {
            // comprovar saldo usuari
            $userRow = $sql->fetch("SELECT tokens FROM usuaris WHERE LOWER(correu) = ?", [$user_email]);
            $userTokens = (int)($userRow['tokens'] ?? 0);
            if ($userTokens < $cost) {
                $_SESSION['flash'] = 'Saldo insuficient. Compra més tokens per reservar aquesta ruta.';
                header("Location: route_details.php?id={$route_id}");
                exit;
            }
            // descomptar tokens de l'usuari i afegir al conductor
            $sql->execute("UPDATE usuaris SET tokens = tokens - ? WHERE LOWER(correu) = ?", [$cost, $user_email]);
            $driverEmail = strtolower(trim($route['user_email'] ?? ''));
            if ($driverEmail) {
                $sql->execute("UPDATE usuaris SET tokens = tokens + ? WHERE LOWER(correu) = ?", [$cost, $driverEmail]);
            }
            // actualitzar sessió tokens
            $_SESSION['user']['tokens'] = max(0, $userTokens - $cost);
        }
        // Inserir reserva i disminuir places
        $sql->insert("INSERT INTO reservas (route_id, user_email) VALUES (?, ?)", [$route_id, $user_email]);
        $sql->execute("UPDATE rutes SET seats = seats - 1 WHERE id = ? AND seats > 0", [$route_id]);
        $_SESSION['flash'] = 'Reserva realitzada correctament.';
    } else { // cancel
        // Comprovar existencia
        $res = $sql->fetch("SELECT id FROM reservas WHERE route_id = ? AND LOWER(user_email) = ?", [$route_id, $user_email]);
        if (!$res) {
            $_SESSION['flash'] = 'No tens cap reserva per a aquesta ruta.';
            header("Location: route_details.php?id={$route_id}");
            exit;
        }
        // recuperar cost per retornar tokens
        $route = $sql->fetch("SELECT token_cost, user_email FROM rutes WHERE id = ?", [$route_id]);
        $cost = (int)($route['token_cost'] ?? 0);
        // Esborrar reserva i incrementar places
        $sql->execute("DELETE FROM reservas WHERE id = ?", [$res['id']]);
        $sql->execute("UPDATE rutes SET seats = seats + 1 WHERE id = ?", [$route_id]);
        if ($cost > 0) {
            // retornar tokens a l'usuari i treure del driver si escau
            $sql->execute("UPDATE usuaris SET tokens = tokens + ? WHERE LOWER(correu) = ?", [$cost, $user_email]);
            $driverEmail = strtolower(trim($route['user_email'] ?? ''));
            if ($driverEmail) {
                $sql->execute("UPDATE usuaris SET tokens = GREATEST(0, tokens - ?) WHERE LOWER(correu) = ?", [$cost, $driverEmail]);
            }
            // actualitzar sessió tokens (refrescar valor)
            $userRow = $sql->fetch("SELECT tokens FROM usuaris WHERE LOWER(correu) = ?", [$user_email]);
            $_SESSION['user']['tokens'] = (int)($userRow['tokens'] ?? 0);
        }
        $_SESSION['flash'] = 'Reserva cancel·lada correctament.';
    }
} catch (Exception $e) {
    $_SESSION['flash'] = 'Error processant la petició.';
}
header("Location: route_details.php?id={$route_id}");
exit;
