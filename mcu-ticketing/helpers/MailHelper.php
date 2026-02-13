<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Note: Ensure PHPMailer files are included for this helper to work.
// If not using composer, you can manually include the files:
// require 'phpmailer/src/Exception.php';
// require 'phpmailer/src/PHPMailer.php';
// require 'phpmailer/src/SMTP.php';

class MailHelper {
    // Gmail Settings
    private static $host = 'smtp.gmail.com';
    private static $username = 'ticketingbumame@gmail.com';
    private static $password = 'dvyw yujz axxf vbtr'; // App Password
    private static $smtp_secure = 'tls'; 
    private static $port = 587;

    // Outlook / Office 365 Settings (Use these if using @bumame / Outlook)
    // private static $host = 'smtp.office365.com';
    // private static $username = 'your_email@bumame.com';
    // private static $password = 'your_app_password'; // App Password
    // private static $smtp_secure = 'tls';
    // private static $port = 587;

    public static function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        
        // Get the directory containing public/ (root of the app)
        // Since we are in helpers/, the root is one level up.
        // However, we want the URL that points to public/index.php
        $scriptName = $_SERVER['SCRIPT_NAME']; // e.g. /mcu-ticketing/public/index.php
        $dir = dirname($scriptName); // e.g. /mcu-ticketing/public
        
        return $protocol . $domainName . $dir . "/index.php";
    }

    public static function send($to, $subject, $message, $from_name = 'Bumame Ticketing') {
        if (empty($to)) {
            error_log("MailHelper: No recipients provided for subject: $subject");
            return false;
        }

        // We'll check if PHPMailer is available
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            // Fallback for when PHPMailer files are not yet included or available
            // In a real scenario, we should require them here or in a bootstrap file
            $phpmailer_path = __DIR__ . '/phpmailer/src/';
            if (file_exists($phpmailer_path . 'PHPMailer.php')) {
                require_once $phpmailer_path . 'Exception.php';
                require_once $phpmailer_path . 'PHPMailer.php';
                require_once $phpmailer_path . 'SMTP.php';
            } else {
                error_log("PHPMailer not found in $phpmailer_path");
                return false;
            }
        }

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = self::$host;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::$username;
            $mail->Password   = self::$password;
            $mail->SMTPSecure = self::$smtp_secure;
            $mail->Port       = self::$port;

            // Recipients
            $mail->setFrom(self::$username, $from_name);
            
            if (is_array($to)) {
                foreach ($to as $address) {
                    $mail->addAddress($address);
                }
            } else {
                $mail->addAddress($to);
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Helper to get a nicely formatted HTML template
     */
    public static function getTemplate($title, $content, $link = null, $link_text = "Lihat Detail") {
        $html = "
        <div style='font-family: sans-serif; max-width: 100%; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>
            <h2 style='color: #204EAB;'>Bumame Ticketing System</h2>
            <hr style='border: 0; border-top: 1px solid #eee;'>
            <h3 style='color: #333;'>$title</h3>
            <div style='color: #555; line-height: 1.5;'>
                $content
            </div>";
        
        if ($link) {
            $html .= "
            <div style='margin-top: 25px;'>
                <a href='$link' style='background-color: #204EAB; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>$link_text</a>
            </div>";
        }

        $html .= "
            <hr style='border: 0; border-top: 1px solid #eee; margin-top: 30px;'>
            <p style='font-size: 12px; color: #999;'>Ini adalah email otomatis, mohon tidak membalas email ini.</p>
        </div>";

        return $html;
    }
}
