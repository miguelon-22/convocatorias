<?php
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo = get_db_connection();
    
    $nombre = clean_input($_POST['nombre_comercial']);
    $ruc = clean_input($_POST['ruc']);
    $sector = clean_input($_POST['sector']);
    $correo = clean_input($_POST['correo_contacto']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $telefono = clean_input($_POST['telefono']);
    $direccion = clean_input($_POST['direccion']);

    // Check if RUC or email already exists
    $check = $pdo->prepare("SELECT id FROM empresas WHERE ruc = ? OR correo_contacto = ?");
    $check->execute([$ruc, $correo]);
    
    if ($check->rowCount() > 0) {
        $error = "El RUC o correo ya están registrados.";
    } elseif (!str_ends_with($correo, '.edu.pe')) {
        $error = "El correo corporativo debe tener el dominio .edu.pe (Ej: contacto@u.edu.pe)";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO empresas (nombre_comercial, ruc, sector, correo_contacto, password_hash, telefono, direccion, estado) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, 'pendiente')");
            $stmt->execute([$nombre, $ruc, $sector, $correo, $password, $telefono, $direccion]);
            
            $_SESSION['alert'] = ['message' => "¡Registro exitoso! Por favor, espera a que el administrador apruebe tu cuenta.", 'type' => 'success'];
            header("Location: " . BASE_URL . "login.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error al registrar la empresa: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="auth-page" style="margin-top: 5rem;">
    <div class="glass-panel auth-card animate-fade-in" style="max-width: 600px;">
        <h1 class="gradient-text">Regístrate como Empresa</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Únete a nuestra red de talento y publica tus vacantes.</p>

        <?php if (isset($error)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.75rem; border-radius: 10px; margin-bottom: 1.5rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Nombre Comercial</label>
                    <input type="text" name="nombre_comercial" class="form-control" required placeholder="Nombre de la empresa">
                </div>
                <div class="form-group">
                    <label>RUC (11 dígitos)</label>
                    <input type="text" name="ruc" pattern="\d{11}" maxlength="11" class="form-control" required placeholder="20XXXXXXXXX">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Sector</label>
                    <input type="text" name="sector" class="form-control" required placeholder="Ej: Tecnología">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" class="form-control" required placeholder="Número de contacto">
                </div>
            </div>

            <div class="form-group">
                <label>Correo Electrónico Corporativo</label>
                <input type="email" name="correo_contacto" class="form-control" required placeholder="correo@empresa.com">
            </div>

            <div class="form-group">
                <label>Dirección Física</label>
                <input type="text" name="direccion" class="form-control" required placeholder="Av. Principal 123">
            </div>

            <div class="form-group">
                <label>Contraseña de Acceso</label>
                <input type="password" name="password" class="form-control" required placeholder="Crea tu password...">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Registrar mi Empresa</button>
            <div style="margin-top: 1.5rem;">
                <a href="<?php echo BASE_URL; ?>login.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">¿Ya tienes cuenta? Inicia sesión</a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
