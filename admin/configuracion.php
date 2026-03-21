<?php
require_once '../includes/functions.php';
require_admin();

$pdo = get_db_connection();

// Load settings from DB
function load_db_settings($pdo) {
    $stmt = $pdo->query("SELECT clave, valor FROM configuracion");
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

$settings = load_db_settings($pdo);

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_settings'])) {
    $nombre = clean_input($_POST['site_name']);
    $mantenimiento = $_POST['maintenance_mode'];
    
    // Perform updates individually
    $updates = [
        'nombre_sitio' => $nombre,
        'modo_mantenimiento' => $mantenimiento,
        'n8n_webhook' => clean_input($_POST['n8n_webhook'] ?? ''),
        'email_contacto' => clean_input($_POST['email_contacto'] ?? ''),
        'smtp_pass' => clean_input($_POST['smtp_pass'] ?? '') 
    ];
    
    foreach ($updates as $k => $v) {
        $stmt = $pdo->prepare("INSERT INTO configuracion (clave, valor) VALUES (?, ?) 
                               ON CONFLICT (clave) DO UPDATE SET valor = EXCLUDED.valor");
        $stmt->execute([$k, $v]);
    }
    
    $_SESSION['alert'] = ['message' => "La plataforma se ha actualizado correctamente en la Base de Datos.", 'type' => 'success'];
    $settings = load_db_settings($pdo);
}

$page_title = "Configuración Global";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-container">

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <header style="margin-bottom: 4rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="gradient-text" style="font-size: 2.5rem;">Ajustes Globales</h1>
            <p style="color: var(--text-muted);">Configura el nombre, estado y parámetros de comunicación del sitio.</p>
        </div>
        <div style="background: rgba(16, 185, 129, 0.1); padding: 1.25rem 2rem; border-radius: 16px; border: 1px solid rgba(16, 185, 129, 0.2); box-shadow: 0 5px 15px rgba(16, 185, 129, 0.1);">
             <span style="color: #10b981; font-weight: 800; font-size: 0.95rem; letter-spacing: 1px;"><i class="fas fa-microchip"></i> DB CONNECTED</span>
        </div>
    </header>

    <?php display_alert(); ?>

    <div style="max-width: 900px;">
        <form method="POST">
            <div class="glass-panel" style="padding: 4rem; margin-bottom: 3rem; position: relative; overflow: hidden;">
                <!-- Decorative element -->
                <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: var(--gradient-primary); filter: blur(80px); opacity: 0.15;"></div>

                <h3 style="margin-bottom: 3rem; display: flex; align-items: center; gap: 1rem; font-size: 1.5rem; color: white;">
                    <i class="fas fa-sliders-h" style="color: var(--primary-color);"></i> Configuración del Entorno
                </h3>
                
                <div class="form-group" style="margin-bottom: 2.5rem;">
                    <label style="font-weight: 800; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 1rem; display: block;">Nombre de la Plataforma / Sitio</label>
                    <input type="text" name="site_name" class="form-control" value="<?php echo $settings['nombre_sitio'] ?? 'TalentFlow'; ?>" placeholder="Ej: Mi Convocatoria UP" required 
                           style="background: rgba(255,255,255,0.02); padding: 1.5rem; font-size: 1.25rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); color: white; width: 100%;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem;">
                    <div class="form-group">
                        <label style="font-weight: 800; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 1rem; display: block;">Email del Sistema (Gmail)</label>
                        <input type="email" name="email_contacto" class="form-control" value="<?php echo $settings['email_contacto'] ?? ''; ?>" placeholder="ej: tu-email@gmail.com" 
                               style="background: rgba(255,255,255,0.02); padding: 1.5rem; font-size: 1.1rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); color: white; width: 100%;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 800; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 1rem; display: block;">Clave de Aplicación (SMTP)</label>
                        <input type="password" name="smtp_pass" class="form-control" value="<?php echo $settings['smtp_pass'] ?? ''; ?>" placeholder="••••••••••••••••" 
                               style="background: rgba(255,255,255,0.02); padding: 1.5rem; font-size: 1.1rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); color: white; width: 100%;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 2.5rem;">
                    <label style="font-weight: 800; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 1rem; display: block;">Webhook URL (CV Analizador n8n)</label>
                    <input type="url" name="n8n_webhook" class="form-control" value="<?php echo $settings['n8n_webhook'] ?? ''; ?>" placeholder="https://tu-instancia-n8n.com/webhooks/..." 
                           style="background: rgba(255,255,255,0.02); padding: 1.5rem; font-size: 1.1rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); color: white; width: 100%;">
                </div>

                <div class="form-group" style="margin-bottom: 4rem;">
                    <label style="font-weight: 800; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 1rem; display: block;">Modo Mantenimiento</label>
                    <select name="maintenance_mode" class="form-control" style="background: rgba(255,255,255,0.02); padding: 1.5rem; font-size: 1.1rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); color: white; width: 100%; cursor: pointer;">
                        <option value="off" <?php echo ($settings['modo_mantenimiento'] ?? 'off') == 'off' ? 'selected' : ''; ?> style="background: #0f172a;">
                            ✅ Apagado - Acceso Público Total
                        </option>
                        <option value="on" <?php echo ($settings['modo_mantenimiento'] ?? 'off') == 'on' ? 'selected' : ''; ?> style="background: #0f172a;">
                            🚧 Encendido - Solo Administradores
                        </option>
                    </select>
                </div>

                <div style="padding-top: 2.5rem;">
                    <button type="submit" name="save_settings" class="btn btn-primary" style="padding: 1.5rem; font-size: 1.1rem; width: 100%; border-radius: 22px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 15px 35px -10px rgba(99, 102, 241, 0.5);">
                        <i class="fas fa-rocket" style="margin-right: 1rem;"></i> Guardar Cambios en Base de Datos
                    </button>
                </div>
            </div>
        </form>

        <div class="glass-panel" style="padding: 2.5rem; border: 1px solid rgba(255,235,0,0.1); background: rgba(245, 158, 11, 0.02); border-radius: 24px; display: flex; align-items: center; gap: 1.5rem;">
            <div style="background: rgba(245, 158, 11, 0.1); width: 60px; height: 60px; border-radius: 18px; display: flex; align-items: center; justify-content: center; color: #f59e0b; font-size: 1.5rem;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h4 style="color: #f59e0b; margin-bottom: 0.25rem;">Configuración de Correo</h4>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    Para Gmail, debes usar una <strong>"Contraseña de Aplicación"</strong> generada en tu cuenta de Google. No uses tu contraseña personal.
                </p>
            </div>
        </div>
    </div>
</main>

</body>
</html>
