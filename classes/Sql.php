<?php
// classes/Sql.php
// Clase simple para encapsular operaciones con la base de datos usando PDO

class Sql
{
    private $pdo;

    public function __construct(array $config)
    {
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    // Devuelve usuario por email o null
    public function getUserByEmail(string $email)
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Crea un usuario y devuelve su id (int) o false
    public function createUser(string $name, string $email, string $passwordHash)
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        $ok = $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $passwordHash,
        ]);
        if ($ok) {
            return (int) $this->pdo->lastInsertId();
        }
        return false;
    }

    // Verifica credenciales. Devuelve usuario array o false.
    public function verifyUser(string $email, string $password)
    {
        $user = $this->getUserByEmail($email);
        if (!$user)
            return false;
        if (password_verify($password, $user['password'])) {
            // No devolver la contraseña
            unset($user['password']);
            return $user;
        }
        return false;
    }
}
