<?php
// Configuration for the database connection
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'prueba');
define('DB_USER', 'postgres');
define('DB_PASS', 'root'); // Assuming default or common password

// Database connection function
function get_db_connection()
{
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    }
    catch (PDOException $e) {
        die("Error connecting to the database: " . $e->getMessage());
    }
}

// Mailer configuration (Placeholders for Gmail SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@gmail.com');
define('SMTP_PASS', 'your_app_password'); // Use an App Password if using 2FA
define('FROM_EMAIL', 'your_email@gmail.com');
define('FROM_NAME', 'Sisteme de Vacantes');

// Define base URL
define('BASE_URL', '/conv/');
?>
