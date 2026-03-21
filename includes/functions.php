<?php
session_start();

require_once dirname(__DIR__) . '/config.php';

// Check if user is logged in
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

function is_empresa_logged_in() {
    return isset($_SESSION['empresa_id']);
}

// Redirect if not logged in
function require_admin() {
    if (!is_admin_logged_in()) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

function require_empresa() {
    if (!is_empresa_logged_in()) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

// Clean input data
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Format date
function format_date($date) {
    if (!$date) return '-';
    $time = strtotime($date);
    if (!$time) return '-';
    return date('d/m/Y', $time);
}

// Generate a random token for password recovery
function generate_token($length = 32) {
    return bin2hex(random_bytes($length));
}

// Display alert messages
function display_alert() {
    if (isset($_SESSION['alert'])) {
        $type = $_SESSION['alert']['type'];
        $message = $_SESSION['alert']['message'];
        echo "<div class='alert alert-{$type} animate-fade-in' style='padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; background: rgba(0,0,0,0.3); border-left: 5px solid " . ($type == 'success' ? '#10b981' : '#ef4444') . "'>
                {$message}
              </div>";
        unset($_SESSION['alert']);
    }
}

// Simple redirect with message
function redirect_with_message($url, $message, $type = 'success') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
    header("Location: $url");
    exit();
}

/**
 * Gets a global configuration value from the DB
 */
function get_site_setting($clave, $default = '') {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = ?");
        $stmt->execute([$clave]);
        $valor = $stmt->fetchColumn();
        return $valor !== false ? $valor : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Sends CV data to n8n workflow for analysis
 */
function analyze_cv_with_n8n($postulacion_id) {
    try {
        $pdo = get_db_connection();
        $webhook_url = trim(get_site_setting('n8n_webhook'));
        
        if (empty($webhook_url)) {
            $_SESSION['alert'] = ['message' => "La URL del Webhook no está configurada.", 'type' => 'error'];
            return false;
        }

        // Fetch postulation and job data
        $stmt = $pdo->prepare("SELECT p.*, v.titulo_puesto, v.requisitos_raw FROM postulaciones p 
                               JOIN vacantes v ON p.vacante_id = v.id 
                               WHERE p.id = ?");
        $stmt->execute([$postulacion_id]);
        $data = $stmt->fetch();

        if (!$data) return false;

        // Prepare Multipart Form Data (matching React frontend logic)
        $cv_path = dirname(__DIR__) . '/' . $data['url_cv_pdf'];
        
        if (!file_exists($cv_path)) {
            $_SESSION['alert'] = ['message' => "El archivo del CV no existe en el servidor: " . $data['url_cv_pdf'], 'type' => 'error'];
            return false;
        }

        $payload = [
            'cv' => new CURLFile($cv_path, 'application/pdf', 'cv.pdf'),
            'requisitos' => $data['requisitos_raw']
        ];

        $ch = curl_init($webhook_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true); // Use CURLOPT_POST for standard multipart
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        $origin = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $referer = $origin . $_SERVER['REQUEST_URI'];

        $headers = [
            'Origin: ' . $origin,
            'Referer: ' . $referer,
            'Accept: application/json',
            'Accept-Language: es,en-US;q=0.9,en;q=0.8',
            'Connection: keep-alive',
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        
        // Deshabilitar verificación SSL para entornos locales/desarrollo
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        // Tiempo de espera para procesos de IA pesados
        curl_setopt($ch, CURLOPT_TIMEOUT, 90); 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            $_SESSION['alert'] = ['message' => "Error de Conexión (cURL): " . $error, 'type' => 'error'];
            return false;
        }

        if ($http_code >= 400) {
            $_SESSION['alert'] = ['message' => "El Webhook (n8n) devolvió error HTTP: " . $http_code . ". Asegúrate de que el webhook acepte peticiones POST.", 'type' => 'error'];
            return false;
        }

        if ($response) {
            // Handle possibility of n8n returning an array or a single object
            $result = json_decode($response, true);
            if (is_array($result) && isset($result[0])) $result = $result[0];
            
            // Check for 'puntaje' or 'match_score'
            $score = $result['puntaje'] ?? $result['match_score'] ?? null;
            $desc = $result['descripcion'] ?? $result['analysis'] ?? $result['ia_analisis_descripcion'] ?? '';

            if ($score !== null) {
                $stmt_up = $pdo->prepare("UPDATE postulaciones SET match_porcentaje = ?, ia_analisis_descripcion = ? WHERE id = ?");
                $stmt_up->execute([$score, $desc, $postulacion_id]);
                return true;
            } else {
                $_SESSION['alert'] = ['message' => "La IA respondió (200 OK) pero no devolvió el campo 'puntaje'.", 'type' => 'warning'];
                return false;
            }
        }
        
        $_SESSION['alert'] = ['message' => "No se recibió respuesta del Webhook de n8n.", 'type' => 'error'];
        return false;
    } catch (Exception $e) {
        $_SESSION['alert'] = ['message' => "Excepción: " . $e->getMessage(), 'type' => 'error'];
        return false;
    }
}

/**
 * Sends a notification email to selected candidate
 */
/**
 * Final sending logic using manual SMTP (no libraries needed)
 */
function send_selection_email($email, $name, $job, $additional_msg = '') {
    $smtp_user = get_site_setting('email_contacto', 'miguelangel01032001@gmail.com');
    $smtp_pass = get_site_setting('smtp_pass', 'jytf osny ckkx qtrd');
    $site_name = get_site_setting('nombre_sitio', 'TalentFlow');

    $subject = "¡Felicidades! Has sido seleccionado para el puesto: $job";
    
    // HTML Message Body
    $message = "
    <html>
    <body style='font-family: sans-serif; padding: 20px; color: #333;'>
        <div style='background: #6366f1; color: white; padding: 20px; border-radius: 10px 10px 0 0;'>
            <h2>¡Buenas noticias, $name!</h2>
        </div>
        <div style='padding: 30px; border: 1px solid #eee; border-radius: 0 0 10px 10px;'>
            <p>Nos complace informarte que has sido marcado como <strong>APTO</strong> para la vacante de <strong>$job</strong>.</p>
            " . (!empty($additional_msg) ? "<div style='background:#f8fafc; padding:20px; border-left:4px solid #6366f1; margin:20px 0;'><strong>Mensaje de la empresa:</strong><br>$additional_msg</div>" : "") . "
            <p>El equipo de $site_name se pondrá en contacto contigo pronto.</p>
        </div>
    </body>
    </html>";

    $headers = [
        "From: $site_name <$smtp_user>",
        "To: $email",
        "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
        "MIME-Version: 1.0",
        "Content-type: text/html; charset=UTF-8"
    ];

    try {
        $ch = curl_init("smtps://smtp.gmail.com:465");
        curl_setopt($ch, CURLOPT_MAIL_FROM, "<$smtp_user>");
        curl_setopt($ch, CURLOPT_MAIL_RCPT, ["<$email>"]);
        curl_setopt($ch, CURLOPT_USERNAME, $smtp_user);
        curl_setopt($ch, CURLOPT_PASSWORD, $smtp_pass);
        curl_setopt($ch, CURLOPT_USE_SSL, CURLUSESSL_ALL);
        
        $headers_str = implode("\r\n", $headers) . "\r\n\r\n" . $message;
        curl_setopt($ch, CURLOPT_READFUNCTION, function($ch, $fd, $length) use ($headers_str) {
            static $pos = 0;
            $chunk = substr($headers_str, $pos, $length);
            $pos += strlen($chunk);
            return $chunk;
        });
        
        curl_setopt($ch, CURLOPT_UPLOAD, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $success = curl_exec($ch);
        if (!$success) {
            $_SESSION['last_smtp_error'] = "Error cURL SMTP: " . curl_error($ch);
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        return true;
    } catch (Exception $e) {
        $_SESSION['last_smtp_error'] = "Excepción cURL SMTP: " . $e->getMessage();
        return false;
    }
}
?>
