<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Sharing - Menú Principal</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php
    session_start();
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
    
    $user_name = $_SESSION['user_name'] ?? 'Usuario';
    ?>
    
    <div class="container menu-container">
        <h1>🚗 Car Sharing</h1>
        <div class="welcome">
            Bienvenido, <strong><?php echo htmlspecialchars($user_name); ?></strong>
        </div>
        
        <h2>Menú Principal</h2>
        
        <div class="menu-grid">
            <div class="menu-item">
                <h3>🚙 Buscar Vehículo</h3>
                <p>Encuentra el vehículo perfecto</p>
            </div>
            
            <div class="menu-item">
                <h3>📅 Mis Reservas</h3>
                <p>Gestiona tus reservas</p>
            </div>
            
            <div class="menu-item">
                <h3>👤 Mi Perfil</h3>
                <p>Edita tu información</p>
            </div>
            
            <div class="menu-item">
                <h3>💳 Pagos</h3>
                <p>Historial de pagos</p>
            </div>
            
            <div class="menu-item">
                <h3>⭐ Valoraciones</h3>
                <p>Califica tu experiencia</p>
            </div>
            
            <div class="menu-item">
                <h3>📞 Soporte</h3>
                <p>Contacta con nosotros</p>
            </div>
        </div>
        
        <form method="POST" action="logout.php">
            <button type="submit" class="logout-btn">Cerrar Sesión</button>
        </form>
        
        <div style="text-align: center; margin-top: 30px; color: white; font-size: 14px;">
            <p>Desarrollado por: <strong>GitHub Copilot Agent</strong></p>
        </div>
    </div>
</body>
</html>
