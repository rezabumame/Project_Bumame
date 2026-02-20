<?php
date_default_timezone_set('Asia/Jakarta');

class Database {
    // KONFIGURASI OTOMATIS (AUTO-DETECT)
    // Script ini akan otomatis memilih database Local atau Hosting berdasarkan servernya.
    
    // 1. KREDENSIAL LOKAL (XAMPP Laptop)
    private $local_host = "127.0.0.1:3307";
    private $local_db_name = "mcu_ticketing";
    private $local_username = "root";
    private $local_password = "";

    // 2. KREDENSIAL HOSTING (InfinityFree)
    private $live_host = "sql103.infinityfree.com"; 
    private $live_db_name = "if0_40972680_mcu_ticketing"; 
    private $live_username = "if0_40972680"; 
    private $live_password = "KoCTmYYo0mGux"; 

    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        // Cek apakah script berjalan di localhost atau hosting
        $whitelist = array('127.0.0.1', '::1');
        $is_localhost = false;

        // Cek CLI mode
        if (php_sapi_name() === 'cli') {
            $is_localhost = true;
        } elseif (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
            $is_localhost = true;
        } elseif (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'localhost') {
            $is_localhost = true;
        }

        // Pilih kredensial yang sesuai
        $port = null;
        if ($is_localhost) {
            $host = getenv('DB_HOST') ?: $this->local_host;
            $db_name = getenv('DB_NAME') ?: $this->local_db_name;
            $username = getenv('DB_USER') ?: $this->local_username;
            $password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : $this->local_password;
        } else {
            $host = getenv('DB_HOST') ?: $this->live_host;
            $port = getenv('DB_PORT') ?: null;
            $db_name = getenv('DB_NAME') ?: $this->live_db_name;
            $username = getenv('DB_USER') ?: $this->live_username;
            $livePass = getenv('DB_PASSWORD');
            if ($livePass === false) {
                $livePass = getenv('DB_PASS');
            }
            $password = ($livePass !== false) ? $livePass : $this->live_password;
        }

        try {
            $dsn = "mysql:host=" . $host . ($port ? ";port=" . $port : "") . ";dbname=" . $db_name;
            $this->conn = new PDO($dsn, $username, $password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error (" . ($is_localhost ? "Local" : "Live") . "): " . $exception->getMessage();
        }
        return $this->conn;
    }
}
