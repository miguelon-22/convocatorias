<?php
require_once 'includes/functions.php';

if (is_admin_logged_in()) {
    header("Location: " . BASE_URL . "admin/dashboard.php");
    exit();
}
if (is_empresa_logged_in()) {
    header("Location: " . BASE_URL . "empresa/dashboard.php");
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_or_email = clean_input($_POST['user_or_email']);
    $password = $_POST['password'];

    $pdo = get_db_connection();

    // 1. Check if Admin
    $stmt = $pdo->prepare("SELECT * FROM administradores WHERE usuario = ?");
    $stmt->execute([$user_or_email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nombre'] = $admin['nombre'];
        redirect_with_message(BASE_URL . "admin/dashboard.php", "Bienvenido, Administrador.");
    }

    // 2. Check if Empresa
    $stmt = $pdo->prepare("SELECT * FROM empresas WHERE correo_contacto = ?");
    $stmt->execute([$user_or_email]);
    $empresa = $stmt->fetch();

    if ($empresa && password_verify($password, $empresa['password_hash'])) {
        if ($empresa['estado'] === 'bloqueado') {
            $error = "Tu cuenta de empresa ha sido BLOQUEADA por el administrador. Contacta con soporte.";
        } elseif ($empresa['estado'] !== 'activo') {
            $error = "Tu cuenta de empresa está en estado: " . strtoupper($empresa['estado']) . ". Debes esperar la aprobación.";
        } else {
            $_SESSION['empresa_id'] = $empresa['id'];
            $_SESSION['empresa_nombre'] = $empresa['nombre_comercial'];
            redirect_with_message(BASE_URL . "empresa/dashboard.php", "¡Bienvenido, " . $empresa['nombre_comercial'] . "!");
        }
    }

    if (!$admin && (!$empresa || !password_verify($password, $empresa['password_hash']))) {
        $error = "Credenciales incorrectas. Verifica tu usuario/correo y contraseña.";
    }
}

$page_title = "Acceso Unificado";
include 'includes/header.php';
?>

<div class="auth-page">
    <div class="glass-panel auth-card animate-fade-in" style="max-width: 500px;">
        <div style="margin-bottom: 2rem;">
            <div style="background: var(--gradient-primary); width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; color: white; margin: 0 auto 1.5rem; font-size: 1.5rem;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="gradient-text" style="font-size: 2.2rem; margin-bottom: 0.5rem;">Acceso al Sistema</h1>
            <p style="color: var(--text-muted);">Ingresa para gestionar tus vacantes o administrar la plataforma.</p>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(239, 68, 68, 0.2);">
                <i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Usuario o Email Corporativo</label>
                <div style="position: relative;">
                    <i class="fas fa-user" style="position: absolute; left: 1rem; top: 1rem; color: var(--text-muted);"></i>
                    <input type="text" name="user_or_email" class="form-control" required placeholder="admin o correo@empresa.com" style="padding-left: 2.5rem;">
                </div>
            </div>
            
            <div class="form-group">
                <label>Contraseña</label>
                <div style="position: relative;">
                    <i class="fas fa-lock" style="position: absolute; left: 1rem; top: 1rem; color: var(--text-muted);"></i>
                    <input type="password" name="password" class="form-control" required placeholder="Tu contraseña..." style="padding-left: 2.5rem;">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; padding: 1rem; font-size: 1rem;">
                Acceder ahora <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
            </button>

            <div style="margin-top: 2rem; display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--border-color);">
                    <span style="font-size: 0.85rem; color: var(--text-muted);">¿Eres una empresa nueva?</span>
                    <a href="empresa/registro.php" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Registrarme</a>
                </div>
                
                <div style="text-align: center;">
                    <a href="recuperar.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-key"></i> ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
