<?php
// Paramètres de connexion à la base de données
define('DB_HOST', 'dpg-d6c312h5pdvs73d7bb3g-a');  
define('DB_PORT', '5432');
define('DB_NAME', 'elvymade_db');
define('DB_USER', 'elvymade_db_user');
define('DB_PASS', 'yZ6WESza24mNyFG6LcF3LsvzCc1iEAUv');
define('DB_CHARSET', 'utf8mb4');

class Database {
    private $host = DB_HOST;
    private $port = DB_PORT;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $charset = DB_CHARSET;
    private $pdo;

    public function connect() {
        $this->pdo = null;
        
        try {
      
            $dsn = "mysql:host=" . $this->host . 
                   ";port=" . $this->port . 
                   ";dbname=" . $this->db_name . 
                   ";charset=" . $this->charset;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5,  // Timeout de connexion
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $e) {
       
            error_log("Erreur de connexion BDD: " . $e->getMessage());
            error_log("Host: " . $this->host);
            error_log("Port: " . $this->port);
            error_log("Database: " . $this->db_name);
            error_log("User: " . $this->username);
            
            // Message utilisateur générique
            die("Désolé, une erreur technique est survenue. L'équipe a été prévenue.");
        }
        
        return $this->pdo;
    }
}

function getDBConnection() {
    $database = new Database();
    return $database->connect();
}
?>