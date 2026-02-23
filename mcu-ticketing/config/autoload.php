<?php
$GLOBALS['_APP_ENV'] = [];
$envCandidates = [__DIR__ . '/../.env', __DIR__ . '/.env'];
if (function_exists('getcwd')) {
    $envCandidates[] = getcwd() . '/.env';
    $envCandidates[] = getcwd() . '/../.env';
}
foreach ($envCandidates as $envFile) {
    $envFile = realpath($envFile) ?: $envFile;
    if ($envFile && file_exists($envFile) && is_readable($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line !== '' && strpos($line, '#') !== 0 && strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value, " \t\"'");
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $GLOBALS['_APP_ENV'][$key] = $value;
            }
        }
        break;
    }
}
$vendorAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}
$fileHelper = __DIR__ . '/../helpers/file_helper.php';
if (file_exists($fileHelper)) {
    require_once $fileHelper;
}
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
