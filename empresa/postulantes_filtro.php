<?php
require_once '../includes/functions.php';
require_empresa();

$pdo = get_db_connection();
$empresa_id = $_SESSION['empresa_id'];

// Get all vacancies for the filter dropdown
$stmt_v = $pdo->prepare("SELECT id, titulo_puesto, fecha_limite FROM vacantes WHERE empresa_id = ? ORDER BY creado_en DESC");
$stmt_v->execute([$empresa_id]);
$mis_puestos = $stmt_v->fetchAll();

// Filter params
$selected_puesto = isset($_GET['vacante_id']) ? (int)$_GET['vacante_id'] : 0;
$selected_fecha = isset($_GET['fecha_limite']) ? $_GET['fecha_limite'] : '';

// Query results
$query = "SELECT p.*, v.titulo_puesto, v.fecha_limite FROM postulaciones p 
          JOIN vacantes v ON p.vacante_id = v.id 
          WHERE v.empresa_id = ?";
$params = [$empresa_id];

if ($selected_puesto > 0) {
    $query .= " AND p.vacante_id = ?";
    $params[] = $selected_puesto;
}

if ($selected_fecha != '') {
    $query .= " AND v.fecha_limite = ?";
    $params[] = $selected_fecha;
}

$query .= " ORDER BY p.match_porcentaje DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$postulantes = $stmt->fetchAll();

$page_title = "Filtro Especial de Postulantes";
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
    <header style="margin-bottom: 3rem;">
        <h1 class="gradient-text">Postulantes por Puesto y Fecha</h1>
        <p style="color: var(--text-muted);">Filtra candidatos según el cierre de la convocatoria y el cargo específico.</p>
    </header>

    <!-- Filters Section -->
    <div class="glass-panel" style="padding: 2.5rem; margin-bottom: 3rem; border: 1px solid rgba(255,255,255,0.05);">
        <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1.5rem; align-items: flex-end;">
            <div class="form-group">
                <label style="color: var(--primary-color);">Seleccionar Puesto</label>
                <select name="vacante_id" class="form-control" style="background: rgba(255,255,255,0.03);">
                    <option value="0">--- Todos los puestos ---</option>
                    <?php foreach ($mis_puestos as $vp): ?>
                        <option value="<?php echo $vp['id']; ?>" <?php echo $selected_puesto == $vp['id'] ? 'selected' : ''; ?>>
                            <?php echo $vp['titulo_puesto']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label style="color: var(--primary-color);">Fecha de Cierre (Límite)</label>
                <input type="date" name="fecha_limite" class="form-control" value="<?php echo $selected_fecha; ?>" style="background: rgba(255,255,255,0.03);">
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary" style="flex: 1; padding: 0.85rem;"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="postulantes_filtro.php" class="btn btn-outline" style="padding: 0.85rem;"><i class="fas fa-sync"></i></a>
            </div>
        </form>
    </div>

    <!-- Results -->
    <div class="glass-panel animate-fade-in" style="padding: 2.5rem;">
        <h3 style="margin-bottom: 2rem;"><i class="fas fa-user-graduate" style="color: #818cf8; margin-right: 0.5rem;"></i> Candidatos Encontrados (<?php echo count($postulantes); ?>)</h3>
        
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Candidato (IA Score)</th>
                        <th>Puesto Solicitado</th>
                        <th>Fecha Cierre Puesto</th>
                        <th>Estado IA</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($postulantes)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                             <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 1.5rem; display: block; opacity: 0.3;"></i>
                             No hay postulaciones que coincidan con estos filtros.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($postulantes as $p): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 45px; height: 45px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; position: relative;">
                                             <span style="font-weight: 800; font-size: 0.8rem;"><?php echo (int)$p['match_porcentaje']; ?>%</span>
                                        </div>
                                        <div>
                                            <p style="font-weight: 700; color: white;"><?php echo $p['nombre_completo']; ?></p>
                                            <p style="font-size: 0.75rem; color: var(--text-muted);">DNI: <?php echo $p['dni']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td><p style="font-weight: 600; font-size: 0.95rem;"><?php echo $p['titulo_puesto']; ?></p></td>
                                <td>
                                    <span style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem;">
                                        <i class="fas fa-calendar-times" style="color: #f87171;"></i> <?php echo format_date($p['fecha_limite']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($p['match_porcentaje'] >= 80): ?>
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);">TOP TALENT</span>
                                    <?php elseif($p['match_porcentaje'] >= 60): ?>
                                        <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.2);">POTENCIAL</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(148, 163, 184, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2);">REVISIÓN</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="ver_candidato.php?id=<?php echo $p['id']; ?>" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem;"><i class="fas fa-eye"></i> Detalle</a>
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
