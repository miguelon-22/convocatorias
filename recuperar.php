<?php
require_once 'includes/functions.php';

if (is_admin_logged_in() || is_empresa_logged_in()) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_or_email = clean_input($_POST['user_or_email']);
    $pdo = get_db_connection();
    
    // Check if it's admin (by username)
    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE usuario = ?");
    $stmt->execute([$user_or_email]);
    $admin = $stmt->fetch();

    // Check if it's empresa (by email)
    $stmt = $pdo->prepare("SELECT * FROM empresas WHERE correo_contacto = ?");
    $stmt->execute([$user_or_email]);
    $empresa = $stmt->fetch();

    if ($admin || $empresa) {
        $token = generate_token();
        $email = $admin ? "admin@sistema.com" : $empresa['correo_contacto']; // In real admin might have email
        $reset_link = BASE_URL . "reset_password.php?token=" . $token . "&email=" . urlencode($email);
        
        $_SESSION['alert'] = [
            'message' => "Se ha enviado un enlace de recuperación a: <strong>" . ($admin ? "tu usuario administrador" : $email) . "</strong>.<br>(Simulado: <a href='{$reset_link}' style='color: #10b981; font-weight: 700;'>Restablecer ahora</a>)",
            'type' => 'success'
        ];
    } else {
        $_SESSION['alert'] = ['message' => "No se encontró ningún usuario o empresa con esos datos.", 'type' => 'error'];
    }
}

$page_title = "Recuperar Contraseña";
include 'includes/header.php';
?>

<div class="auth-page">
    <div class="glass-panel auth-card animate-fade-in" style="max-width: 450px;">
        <h1 class="gradient-text">Recuperar Acceso</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Ingresa tu usuario (admin) o correo (empresa) para restablecer tu contraseña.</p>

        <?php display_alert(); ?>

        <form method="POST">
            <div class="form-group">
                <label>Usuario o Correo Electrónico</label>
                <input type="text" name="user_or_email" class="form-control" required placeholder="admin o correo@empresa.com">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Enviar Instrucciones</button>
            <div style="margin-top: 1.5rem;">
                <a href="<?php echo BASE_URL; ?>login.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">
                    <i class="fas fa-arrow-left"></i> Volver al Login
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
