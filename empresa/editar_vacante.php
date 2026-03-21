<?php
require_once '../includes/functions.php';
require_empresa();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$empresa_id = $_SESSION['empresa_id'];

$pdo = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['titulo_puesto'])) {
    $titulo = clean_input($_POST['titulo_puesto']);
    $descripcion = clean_input($_POST['descripcion_puesto']);
    $requisitos = clean_input($_POST['requisitos_raw']);
    $modalidad = clean_input($_POST['modalidad']);
    $ubicacion = clean_input($_POST['ubicacion']);
    $fecha_limite = $_POST['fecha_limite'];

    try {
        $stmt = $pdo->prepare("UPDATE vacantes SET titulo_puesto = ?, descripcion_puesto = ?, requisitos_raw = ?, modalidad = ?, ubicacion = ?, fecha_limite = ? 
                               WHERE id = ? AND empresa_id = ?");
        $stmt->execute([$titulo, $descripcion, $requisitos, $modalidad, $ubicacion, $fecha_limite, $id, $empresa_id]);
        
        $_SESSION['alert'] = ['message' => "Vacante actualizada con éxito.", 'type' => 'success'];
        header("Location: mis_vacantes.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error al actualizar la vacante: " . $e->getMessage();
    }
}

// Fetch current data
$stmt = $pdo->prepare("SELECT * FROM vacantes WHERE id = ? AND empresa_id = ?");
$stmt->execute([$id, $empresa_id]);
$v = $stmt->fetch();

if (!$v) {
    header("Location: mis_vacantes.php");
    exit();
}

$page_title = "Editar Vacante: " . $v['titulo_puesto'];
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
        <h1 class="gradient-text">Editar Convocatoria</h1>
        <p style="color: var(--text-muted);">Actualiza los requisitos o descripción de tu puesto.</p>
    </header>

    <?php display_alert(); ?>

    <div class="glass-panel animate-fade-in" style="max-width: 800px; padding: 3rem;">
        <?php if (isset($error)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.75rem; border-radius: 10px; margin-bottom: 1.5rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="color: var(--primary-color); font-weight: 700;">Título del Puesto</label>
                <input type="text" name="titulo_puesto" class="form-control" value="<?php echo $v['titulo_puesto']; ?>" required style="background: rgba(255,255,255,0.03);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label>Modalidad</label>
                    <select name="modalidad" class="form-control" required style="background: rgba(255,255,255,0.03);">
                        <option value="Presencial" <?php echo $v['modalidad'] == 'Presencial' ? 'selected' : ''; ?>>Presencial</option>
                        <option value="Remoto" <?php echo $v['modalidad'] == 'Remoto' ? 'selected' : ''; ?>>Remoto</option>
                        <option value="Híbrido" <?php echo $v['modalidad'] == 'Híbrido' ? 'selected' : ''; ?>>Híbrido</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ubicación</label>
                    <input type="text" name="ubicacion" class="form-control" value="<?php echo $v['ubicacion']; ?>" placeholder="Ej: Lima, Perú" style="background: rgba(255,255,255,0.03);">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Descripción Completa</label>
                <textarea name="descripcion_puesto" class="form-control" rows="8" required style="background: rgba(255,255,255,0.03);"><?php echo $v['descripcion_puesto']; ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Requisitos (Raw Text)</label>
                <textarea name="requisitos_raw" class="form-control" rows="5" required style="background: rgba(255,255,255,0.03);"><?php echo $v['requisitos_raw']; ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label>Fecha Límite</label>
                <input type="date" name="fecha_limite" class="form-control" value="<?php echo $v['fecha_limite']; ?>" required min="<?php echo date('Y-m-d'); ?>" style="background: rgba(255,255,255,0.03);">
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary" style="flex: 2; padding: 1.25rem;">Actualizar Vacante</button>
                <a href="mis_vacantes.php" class="btn btn-outline" style="flex: 1; padding: 1.25rem;">Cancelar</a>
            </div>
        </form>
    </div>
</main>

</body>
</html>
