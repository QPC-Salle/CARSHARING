<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Sharing - Registro</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🚗 Car Sharing</h1>
        <h2>Registro</h2>
        
        <?php
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once 'includes/Database.php';
            
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
                echo '<div class="message error">Por favor, completa todos los campos.</div>';
            } elseif ($password !== $confirm_password) {
                echo '<div class="message error">Las contraseñas no coinciden.</div>';
            } elseif (strlen($password) < 6) {
                echo '<div class="message error">La contraseña debe tener al menos 6 caracteres.</div>';
            } else {
                $db = new Database();
                $conn = $db->getConnection();
                
                if ($conn) {
                    // Check if email already exists
                    $existing_users = $db->select('users', ['email' => $email]);
                    
                    if ($existing_users && count($existing_users) > 0) {
                        echo '<div class="message error">Este email ya está registrado.</div>';
                    } else {
                        // Hash password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert new user
                        $user_data = [
                            'name' => $name,
                            'email' => $email,
                            'password' => $hashed_password,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        
                        $result = $db->insert('users', $user_data);
                        
                        if ($result) {
                            $_SESSION['message'] = 'Registro exitoso. Por favor, inicia sesión.';
                            $_SESSION['message_type'] = 'success';
                            header('Location: index.php');
                            exit;
                        } else {
                            echo '<div class="message error">Error al registrar usuario.</div>';
                        }
                    }
                } else {
                    echo '<div class="message error">Error de conexión a la base de datos.</div>';
                }
            }
        }
        ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Nombre:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit">Registrarse</button>
        </form>
        
        <div class="link-container">
            <p>¿Ya tienes cuenta? <a href="index.php">Inicia sesión aquí</a></p>
        </div>
    </div>
</body>
</html>
