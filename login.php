<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Sql.php';

$config = require __DIR__ . '/config.php';
$sql = new Sql($config);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $errors[] = 'Rellena todos los campos.';
    } else {
        $user = $sql->verifyUser($email, $password);
        if ($user) {
            // guardar en session
            $_SESSION['user'] = $user;
            header('Location: menu.php');
            exit;
        } else {
            $errors[] = 'Email o contraseña incorrectos.';
        }
    }
}

$registered = isset($_GET['registered']);
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Iniciar sesión - CarSharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_global.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <main class="container form py-5">
        <h2>Iniciar sesión</h2>
        <?php if ($registered): ?>
            <div class="success">Cuenta creada correctamente. Puedes iniciar sesión.</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul><?php foreach ($errors as $e)
                    echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <label>Email
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </label>
            <label>Contraseña
                <input type="password" name="password" required>
            </label>
            <div class="actions">
                <button class="btn" type="submit">Entrar</button>
                <a class="link" href="register.php">Crear cuenta</a>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
