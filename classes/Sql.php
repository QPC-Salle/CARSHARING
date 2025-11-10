<?php
// classes/Sql.php
// Clase simple para encapsular operaciones con la base de datos usando PDO

class Sql
{
    private $pdo;

    public function __construct(array $config)
    {
        $host = $config['db_host'] ?? '127.0.0.1';
        $db   = $config['db_name'] ?? 'carsharing';
        $user = $config['db_user'] ?? 'root';
        $pass = $config['db_pass'] ?? '';
        $charset = $config['db_charset'] ?? 'utf8mb4';

        // Validación clara si falta el nombre de la base de datos
        if (empty($db)) {
            throw new RuntimeException("Falta la configuración de la base de datos: añade 'db_name' en config.php (ej. 'db_name' => 'mi_base_datos').");
        }

        $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Mensaje amigable para depuración
            throw new RuntimeException("Error al conectar con la base de datos: " . $e->getMessage());
        }
    }

    // Devuelve usuario por email o null
    public function getUserByEmail(string $email)
    {
        $stmt = $this->pdo->prepare('SELECT  nom, correu, contrasenya FROM usuaris WHERE correu = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // Crea un usuario y devuelve su id (int) o false
    public function createUser(string $name, string $email, string $passwordHash)
    {
        $stmt = $this->pdo->prepare('INSERT INTO usuaris (nom, correu, contrasenya) VALUES (:name, :email, :password)');
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
        if (password_verify($password, $user['contrasenya'])) {
            // No devolver la contraseña
            unset($user['contrasenya']);
            return $user;
        }
        return false;
    }

    /**
     * Ejecuta una SELECT y devuelve todas las filas.
     */
    public function select(string $sql, array $params = []): array {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecuta una INSERT y devuelve el lastInsertId.
     */
    public function insert(string $sql, array $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    /**
     * Ejecuta una consulta (UPDATE/DELETE u otra) y devuelve true/false.
     */
    public function execute(string $sql, array $params = []): bool {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Ejecuta una SELECT y devuelve la primera fila.
     */
    public function fetch(string $sql, array $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}
