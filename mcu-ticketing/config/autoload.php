<?php
spl_autoload_register(function ($class) {
    // Base directory (assuming this file is in config/, so project root is ../)
    $baseDir = __DIR__ . '/../';

    // Map specific classes or patterns
    if ($class === 'Database') {
        $file = $baseDir . 'config/database.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // Check Controllers
    if (strpos($class, 'Controller') !== false) {
        $file = $baseDir . 'controllers/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // Check Models
    $file = $baseDir . 'models/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // Check Helpers
    $file = $baseDir . 'helpers/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }
});
