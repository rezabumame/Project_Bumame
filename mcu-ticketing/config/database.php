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

        $env = function ($key) {
            $g = $GLOBALS['_APP_ENV'] ?? null;
            if (!empty($g[$key])) return $g[$key];
            $v = getenv($key);
            if ($v !== false && $v !== '') return $v;
            return $_ENV[$key] ?? null;
        };

        $port = null;
        if ($is_localhost) {
            $host = $env('DB_HOST') ?: $this->local_host;
            $db_name = $env('DB_NAME') ?: $this->local_db_name;
            $username = $env('DB_USER') ?: $this->local_username;
            $password = $env('DB_PASSWORD') ?: ($env('DB_PASS') ?: $this->local_password);
            $port = $env('DB_PORT') ?: null;
        } else {
            $host = $env('DB_HOST') ?: $this->live_host;
            $port = $env('DB_PORT') ?: null;
            $db_name = $env('DB_NAME') ?: $this->live_db_name;
            $username = $env('DB_USER') ?: $this->live_username;
            $password = $env('DB_PASSWORD') ?: ($env('DB_PASS') ?: $this->live_password);
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
