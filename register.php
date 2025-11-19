<?php
session_start();
// register.php - formulario y registro
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Sql.php';

$config = require __DIR__ . '/config.php';
$sql = new Sql($config);

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'Rellena todos los campos.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email no válido.';
    }
    if ($password !== $password2) {
        $errors[] = 'Las contraseñas no coinciden.';
    }
    if (empty($errors)) {
        // comprobar si ya existe
        if ($sql->getUserByEmail($email)) {
            $errors[] = 'Ya existe un usuario con ese email.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $id = $sql->createUser($name, $email, $hash);
            if ($id) {
                $success = true;
                // redirigir al login
                header('Location: login.php?registered=1');
                exit;
            } else {
                $errors[] = 'Error al crear el usuario.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Registrarse - CarSharing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_global.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <main class="container form py-5">
        <h2>Registrarse</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php">
            <label>Nombre
                <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </label>
            <label>Email
                <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </label>
            <label>Contraseña
                <input type="password" name="password" required>
            </label>
            <label>Confirmar contraseña
                <input type="password" name="password2" required>
            </label>
            <div class="actions">
                <button class="btn" type="submit">Crear cuenta</button>
                <a class="link" href="login.php">¿Ya tienes cuenta?</a>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>