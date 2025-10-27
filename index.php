<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Sharing - Inicio de Sesión</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🚗 Car Sharing</h1>
        <h2>Iniciar Sesión</h2>
        
        <?php
        session_start();
        
        if (isset($_SESSION['message'])) {
            echo '<div class="message ' . $_SESSION['message_type'] . '">';
            echo $_SESSION['message'];
            echo '</div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once 'includes/Database.php';
            
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($email) || empty($password)) {
                echo '<div class="message error">Por favor, completa todos los campos.</div>';
            } else {
                $db = new Database();
                $conn = $db->getConnection();
                
                if ($conn) {
                    $users = $db->select('users', ['email' => $email]);
                    
                    if ($users && count($users) > 0) {
                        $user = $users[0];
                        
                        if (password_verify($password, $user['password'])) {
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_name'] = $user['name'];
                            $_SESSION['user_email'] = $user['email'];
                            
                            header('Location: menu.php');
                            exit;
                        } else {
                            echo '<div class="message error">Contraseña incorrecta.</div>';
                        }
                    } else {
                        echo '<div class="message error">Usuario no encontrado.</div>';
                    }
                } else {
                    echo '<div class="message error">Error de conexión a la base de datos.</div>';
                }
            }
        }
        ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <div class="link-container">
            <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
        </div>
    </div>
</body>
</html>
