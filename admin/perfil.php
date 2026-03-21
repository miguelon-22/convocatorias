<?php
require_once '../includes/functions.php';
require_admin();

$pdo = get_db_connection();
$admin_id = $_SESSION['admin_id'];

// Handle Image Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto_perfil'])) {
    $target_dir = "../uploads/admin/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $filename = "admin_" . $admin_id . "_" . time() . "." . pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION);
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $target_file)) {
        $db_path = "uploads/admin/" . $filename;
        $stmt = $pdo->prepare("UPDATE administradores SET foto_perfil = ? WHERE id = ?");
        $stmt->execute([$db_path, $admin_id]);
        $_SESSION['alert'] = ['message' => "Foto de perfil actualizada.", 'type' => 'success'];
    }
}

// Handle Data Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre'])) {
    $nombre = clean_input($_POST['nombre']);
    $usuario = clean_input($_POST['usuario']);
    
    if (!empty($_POST['new_password'])) {
        $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE administradores SET nombre = ?, usuario = ?, password_hash = ? WHERE id = ?");
        $stmt->execute([$nombre, $usuario, $password, $admin_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE administradores SET nombre = ?, usuario = ? WHERE id = ?");
        $stmt->execute([$nombre, $usuario, $admin_id]);
    }
    
    $_SESSION['admin_nombre'] = $nombre;
    $_SESSION['alert'] = ['message' => "Datos de administrador actualizados.", 'type' => 'success'];
}

$stmt = $pdo->prepare("SELECT * FROM administradores WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

$page_title = "Mi Perfil: Administrador";
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
    <header style="margin-bottom: 2rem;">
        <h1 class="gradient-text">Mi Perfil</h1>
        <p style="color: var(--text-muted);">Gestiona tus datos de acceso y tu imagen de perfil.</p>
    </header>

    <?php display_alert(); ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <div>
            <div class="glass-panel" style="text-align: center; padding: 3rem;">
                <div style="width: 140px; height: 140px; margin: 0 auto 2rem; border-radius: 25px; overflow: hidden; border: 3px solid var(--primary-color); background: rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center;">
                    <?php if ($admin['foto_perfil']): ?>
                        <img src="../<?php echo $admin['foto_perfil']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user-shield" style="font-size: 3.5rem; color: var(--text-muted);"></i>
                    <?php endif; ?>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <label class="btn btn-outline" style="cursor: pointer; width: 100%;">
                        <i class="fas fa-camera" style="margin-right: 0.5rem;"></i> Cambiar Foto
                        <input type="file" name="foto_perfil" hidden onchange="this.form.submit()">
                    </label>
                </form>
                
                <p style="margin-top: 2rem; font-size: 0.9rem; color: var(--text-muted); font-weight: 500;">Administrador de TalentFlow</p>
                <p style="font-size: 0.8rem; color: var(--text-muted);"><?php echo $admin['usuario']; ?></p>
            </div>
        </div>

        <div>
            <div class="glass-panel" style="padding: 3rem;">
                <h3 style="margin-bottom: 2rem;">Actualizar Datos de Acceso</h3>
                <form method="POST">
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo $admin['nombre']; ?>" required style="background: rgba(255,255,255,0.03);">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Usuario (Nombre de Registro)</label>
                        <input type="text" name="usuario" class="form-control" value="<?php echo $admin['usuario']; ?>" required style="background: rgba(255,255,255,0.03);">
                    </div>

                    <div class="form-group" style="margin-bottom: 2rem;">
                        <label>Actualizar Contraseña (Opcional)</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Nueva password..." style="background: rgba(255,255,255,0.03);">
                    </div>

                    <div style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                        <button type="submit" class="btn btn-primary" style="padding: 1rem 2.5rem;">Guardar Información</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>
