<?php
/**
 * Configuration de la base de données pour ElvyMade
 * Site de prospection d'articles - Cameroun
 */

// Paramètres de connexion à la base de données
define('DB_HOST', 'dpg-d6c312h5pdvs73d7bb3g-a');
define('DB_NAME', 'elvymade_db');
define('DB_USER', 'elvymade_db_user');
define('DB_PASS', 'yZ6WESza24mNyFG6LcF3LsvzCc1iEAUv');
define('DB_CHARSET', 'utf8mb4');

/**
 * Classe de connexion à la base de données
 */
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $pdo;

    /**
     * Établit la connexion à la base de données
     * @return PDO|null
     */
    public function connect() {
        $this->pdo = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $e) {
            echo "Erreur de connexion : " . $e->getMessage();
        }
        
        return $this->pdo;
    }

    /**
     * Ferme la connexion à la base de données
     */
    public function disconnect() {
        $this->pdo = null;
    }
}

/**
 * Fonction globale pour obtenir une connexion à la base de données
 * @return PDO|null
 */
function getDBConnection() {
    $database = new Database();
    return $database->connect();
}
?>
