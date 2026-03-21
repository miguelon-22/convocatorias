<?php
require_once '../includes/functions.php';
require_empresa();

$pdo = get_db_connection();
$empresa_id = $_SESSION['empresa_id'];

// Handle Image Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['foto_perfil'])) {
    $target_dir = "../uploads/logos/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $filename = "logo_" . $empresa_id . "_" . time() . "." . pathinfo($_FILES["foto_perfil"]["name"], PATHINFO_EXTENSION);
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($_FILES["foto_perfil"]["tmp_name"], $target_file)) {
        $db_path = "uploads/logos/" . $filename;
        $stmt = $pdo->prepare("UPDATE empresas SET foto_perfil = ? WHERE id = ?");
        $stmt->execute([$db_path, $empresa_id]);
        $_SESSION['alert'] = ['message' => "Logo corporativo actualizado.", 'type' => 'success'];
    }
}

// Handle Data Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre_comercial'])) {
    $nombre = clean_input($_POST['nombre_comercial']);
    $sector = clean_input($_POST['sector']);
    $telefono = clean_input($_POST['telefono']);
    $direccion = clean_input($_POST['direccion']);
    
    $stmt = $pdo->prepare("UPDATE empresas SET nombre_comercial = ?, sector = ?, telefono = ?, direccion = ? WHERE id = ?");
    $stmt->execute([$nombre, $sector, $telefono, $direccion, $empresa_id]);
    
    $_SESSION['empresa_nombre'] = $nombre;
    $_SESSION['alert'] = ['message' => "Datos de empresa actualizados.", 'type' => 'success'];
}

$stmt = $pdo->prepare("SELECT * FROM empresas WHERE id = ?");
$stmt->execute([$empresa_id]);
$e = $stmt->fetch();

$page_title = "Perfil Empresa";
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
        <h1 class="gradient-text">Perfil Empresarial</h1>
        <p style="color: var(--text-muted);">Actualiza tu identidad e información corporativa.</p>
    </header>

    <?php display_alert(); ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
        <div>
            <div class="glass-panel" style="text-align: center; padding: 3rem;">
                <div style="width: 140px; height: 140px; margin: 0 auto 2rem; border-radius: 25px; overflow: hidden; border: 3px solid var(--primary-color); background: rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center;">
                    <?php if ($e['foto_perfil']): ?>
                        <img src="../<?php echo $e['foto_perfil']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-building" style="font-size: 3.5rem; color: var(--text-muted);"></i>
                    <?php endif; ?>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <label class="btn btn-outline" style="cursor: pointer; width: 100%;">
                        <i class="fas fa-camera" style="margin-right: 0.5rem;"></i> Cambiar Logo
                        <input type="file" name="foto_perfil" hidden onchange="this.form.submit()">
                    </label>
                </form>
                
                <p style="margin-top: 2rem; font-size: 0.85rem; color: var(--text-muted);">RUC: <strong><?php echo $e['ruc']; ?></strong></p>
                <p style="font-size: 0.85rem; color: var(--text-muted);">Email: <strong><?php echo $e['correo_contacto']; ?></strong></p>
            </div>
        </div>

        <div>
            <div class="glass-panel" style="padding: 3rem;">
                <h3 style="margin-bottom: 2rem;">Editar Información Comercial</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Nombre Comercial</label>
                        <input type="text" name="nombre_comercial" class="form-control" value="<?php echo $e['nombre_comercial']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Sector Industrial</label>
                        <input type="text" name="sector" class="form-control" value="<?php echo $e['sector']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Teléfono de Contacto</label>
                        <input type="text" name="telefono" class="form-control" value="<?php echo $e['telefono']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Dirección Física</label>
                        <input type="text" name="direccion" class="form-control" value="<?php echo $e['direccion']; ?>" required>
                    </div>

                    <div style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                        <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem;">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>
