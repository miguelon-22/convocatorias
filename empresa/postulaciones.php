<?php
require_once '../includes/functions.php';
require_empresa();

$pdo = get_db_connection();
$empresa_id = $_SESSION['empresa_id'];
$vacante_id = isset($_GET['vacante_id']) ? (int)$_GET['vacante_id'] : 0;

// Fetch postulaciones list
$query = "SELECT p.*, v.titulo_puesto FROM postulaciones p 
          JOIN vacantes v ON p.vacante_id = v.id 
          WHERE v.empresa_id = ?";
$params = [$empresa_id];

if ($vacante_id > 0) {
    $query .= " AND p.vacante_id = ?";
    $params[] = $vacante_id;
}

$query .= " ORDER BY p.match_porcentaje DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$postulaciones = $stmt->fetchAll();

// Handle "Analyze All" action
if (isset($_GET['action']) && $_GET['action'] == 'analyze_all') {
    set_time_limit(300); // Allow more time for multiple analyses
    $count = 0;
    $errors = 0;
    
    foreach ($postulaciones as $p) {
        // Skip already analyzed if you want, but for "Analyze All" usually we refresh
        if (analyze_cv_with_n8n($p['id'])) {
            $count++;
        } else {
            $errors++;
        }
    }
    
    $msg = "Se completó el análisis masivo. Éxito: $count, Fallos: $errors.";
    $type = ($errors == 0) ? 'success' : ($count > 0 ? 'warning' : 'error');
    
    redirect_with_message("postulaciones.php?vacante_id=$vacante_id", $msg, $type);
}

$page_title = "Postulaciones Recibidas";
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
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <div>
            <h1 class="gradient-text">Listado de Postulantes</h1>
            <p style="color: var(--text-muted);">Candidatos analizados por IA para tus vacantes.</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
             <a href="?vacante_id=<?php echo $vacante_id; ?>&action=analyze_all" class="btn btn-primary" style="padding: 0.8rem 1.5rem; background: #6366f1; border: none; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Analizar Todo con n8n
             </a>
             <a href="mis_vacantes.php" class="btn btn-outline" style="padding: 0.8rem 1.5rem;"><i class="fa-solid fa-list-ul"></i> Regresar a Mis Vacantes</a>
        </div>
    </header>

    <?php display_alert(); ?>

    <div class="glass-panel animate-fade-in" style="padding: 2rem;">
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Candidato</th>
                        <th>Vacante</th>
                        <th>DNI</th>
                        <th style="width: 250px;">Match IA</th>
                        <th>Fecha Postulación</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($postulaciones)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 4rem; color: var(--text-muted);">No hay postulaciones registradas en esta vista.</td></tr>
                    <?php else: ?>
                        <?php foreach ($postulaciones as $p): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 45px; height: 45px; border-radius: 12px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                            <?php echo substr($p['nombre_completo'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <p style="font-weight: 700; font-size: 0.95rem; margin-bottom: 0.15rem; color: white;"><?php echo $p['nombre_completo']; ?></p>
                                            <p style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $p['correo_estudiante']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="background: rgba(255,255,255,0.03); padding: 0.4rem 0.8rem; border-radius: 8px; display: inline-block;">
                                        <span style="font-size: 0.85rem; font-weight: 600; color: #cbd5e1;"><?php echo $p['titulo_puesto']; ?></span>
                                    </div>
                                </td>
                                <td><span style="font-family: monospace; font-size: 0.9rem; color: #94a3b8;"><?php echo $p['dni']; ?></span></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="flex: 1; height: 8px; background: rgba(255,255,255,0.03); border-radius: 10px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                                            <?php 
                                            $color = $p['match_porcentaje'] >= 80 ? 'linear-gradient(90deg, #10b981, #34d399)' : ($p['match_porcentaje'] >= 50 ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #ef4444, #f87171)');
                                            ?>
                                            <div style="width: <?php echo $p['match_porcentaje']; ?>%; height: 100%; background: <?php echo $color; ?>; border-radius: 10px; box-shadow: 0 0 10px <?php echo $p['match_porcentaje'] >= 80 ? 'rgba(16, 185, 129, 0.4)' : 'transparent'; ?>;"></div>
                                        </div>
                                        <span style="font-weight: 800; color: <?php echo $p['match_porcentaje'] >= 50 ? 'white' : '#ef4444'; ?>; font-size: 0.9rem; min-width: 40px; text-align: right;"><?php echo (int)$p['match_porcentaje']; ?>%</span>
                                    </div>
                                </td>
                                <td><span style="font-size: 0.85rem; color: #64748b; font-weight: 500;"><?php echo format_date($p['fecha_postulacion']); ?></span></td>
                                <td>
                                    <a href="ver_candidato.php?id=<?php echo $p['id']; ?>" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem; border-radius: 10px;">
                                        <i class="fa-solid fa-user-check"></i> Revisar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>
