<?php
require_once '../includes/functions.php';
require_empresa();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo = get_db_connection();
    $empresa_id = $_SESSION['empresa_id'];
    
    $titulo = clean_input($_POST['titulo_puesto']);
    $descripcion = clean_input($_POST['descripcion_puesto']);
    $requisitos = clean_input($_POST['requisitos_raw']);
    $modalidad = clean_input($_POST['modalidad']);
    $ubicacion = clean_input($_POST['ubicacion']);
    $fecha_limite = $_POST['fecha_limite'];

    try {
        $stmt = $pdo->prepare("INSERT INTO vacantes (empresa_id, titulo_puesto, descripcion_puesto, requisitos_raw, modalidad, ubicacion, fecha_limite) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$empresa_id, $titulo, $descripcion, $requisitos, $modalidad, $ubicacion, $fecha_limite]);
        
        $_SESSION['alert'] = ['message' => "¡Vacante publicada con éxito!", 'type' => 'success'];
        header("Location: mis_vacantes.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error al publicar: " . $e->getMessage();
    }
}

$page_title = "Nueva Vacante: TalentFlow";
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
        <h1 class="gradient-text"><i class="fas fa-plus-circle"></i> Nueva Convocatoria</h1>
        <p style="color: var(--text-muted);">Completa los detalles para atraer al mejor talento académico.</p>
    </header>

    <?php display_alert(); ?>

    <div class="glass-panel animate-fade-in" style="max-width: 850px; padding: 3rem;">
        <?php if (isset($error)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 0.75rem; border-radius: 10px; margin-bottom: 1.5rem;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="color: var(--primary-color); font-weight: 700;">Título del Puesto</label>
                <input type="text" name="titulo_puesto" class="form-control" required placeholder="Ej: Practicante de Ingeniería de Sistemas" style="background: rgba(255,255,255,0.03);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label>Modalidad de Trabajo</label>
                    <select name="modalidad" class="form-control" required style="background: rgba(255,255,255,0.03);">
                        <option value="Presencial">Presencial</option>
                        <option value="Remoto">Remoto</option>
                        <option value="Híbrido">Híbrido</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ubicación Física / Ciudad</label>
                    <input type="text" name="ubicacion" class="form-control" placeholder="Ej: Lima, Perú" style="background: rgba(255,255,255,0.03);">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Descripción del Puesto</label>
                <textarea name="descripcion_puesto" class="form-control" rows="8" required placeholder="Detalla las funciones y lo que el estudiante aprenderá..." style="background: rgba(255,255,255,0.03);"></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Requisitos & Competencias (Skills)</label>
                <textarea name="requisitos_raw" class="form-control" rows="5" required placeholder="Inglés intermedio, conocimientos en SQL, Proactivo..." style="background: rgba(255,255,255,0.03);"></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label>Fecha Límite para Postular</label>
                <input type="date" name="fecha_limite" class="form-control" required min="<?php echo date('Y-m-d'); ?>" style="background: rgba(255,255,255,0.03); width: 100%;">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.25rem; font-size: 1rem; letter-spacing: 0.5px;">
                <i class="fas fa-rocket" style="margin-right: 0.75rem;"></i> Lanzar Convocatoria
            </button>
        </form>
    </div>
</main>

</body>
</html>
