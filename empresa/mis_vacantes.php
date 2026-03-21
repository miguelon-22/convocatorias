<?php
require_once '../includes/functions.php';
require_empresa();

$pdo = get_db_connection();
$empresa_id = $_SESSION['empresa_id'];

// Cerrar/Abrir vacante
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    $nuevo_estado = ($action == 'cerrar') ? 'cerrada' : 'abierta';
    
    $stmt = $pdo->prepare("UPDATE vacantes SET estado = ? WHERE id = ? AND empresa_id = ?");
    $stmt->execute([$nuevo_estado, $id, $empresa_id]);
    
    $_SESSION['alert'] = ['message' => "Estado de la vacante actualizado.", 'type' => 'success'];
    header("Location: mis_vacantes.php");
    exit();
}

// Get company vacancies
$stmt = $pdo->prepare("SELECT v.*, (SELECT COUNT(*) FROM postulaciones p WHERE p.vacante_id = v.id) as num_postulantes 
                       FROM vacantes v 
                       WHERE v.empresa_id = ? 
                       ORDER BY v.creado_en DESC");
$stmt->execute([$empresa_id]);
$vacantes = $stmt->fetchAll();

$page_title = "Mis Vacantes Publicadas";
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
            <h1 class="gradient-text">Mis Convocatorias</h1>
            <p style="color: var(--text-muted);">Gestiona las ofertas que has publicado en la plataforma.</p>
        </div>
        <a href="publicar_vacante.php" class="btn btn-primary" style="padding: 1rem 1.5rem;"><i class="fas fa-plus"></i> Nueva Vacante</a>
    </header>

    <?php display_alert(); ?>

    <div class="glass-panel animate-fade-in">
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Puesto</th>
                        <th>Postulantes</th>
                        <th>Modalidad</th>
                        <th>Estado</th>
                        <th>Publicado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vacantes)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">No tienes vacantes publicadas aún.</td></tr>
                    <?php else: ?>
                        <?php foreach ($vacantes as $v): ?>
                            <tr>
                                <td><strong style="font-size: 1rem; color: white;"><?php echo $v['titulo_puesto']; ?></strong></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></div>
                                        <span style="font-weight: 700; color: #10b981; font-size: 0.9rem;"><?php echo $v['num_postulantes']; ?></span>
                                    </div>
                                </td>
                                <td><span style="font-size: 0.85rem;"><?php echo $v['modalidad']; ?></span><br><span style="font-size: 0.7rem; color: var(--text-muted);"><?php echo $v['ubicacion']; ?></span></td>
                                <td>
                                    <span class="badge" style="background: <?php echo $v['estado'] == 'abierta' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(148, 163, 184, 0.1)'; ?>; color: <?php echo $v['estado'] == 'abierta' ? '#10b981' : '#94a3b8'; ?>; border: 1px solid <?php echo $v['estado'] == 'abierta' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(148, 163, 184, 0.2)'; ?>;">
                                        <?php echo strtoupper($v['estado']); ?>
                                    </span>
                                </td>
                                <td><p style="font-size: 0.85rem;"><?php echo format_date($v['creado_en']); ?></p></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <?php if ($v['estado'] == 'abierta'): ?>
                                            <a href="?action=cerrar&id=<?php echo $v['id']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; border-color: #ef4444; color: #ef4444; font-size: 0.8rem;" title="Pausar publicación"><i class="fas fa-pause"></i></a>
                                        <?php else: ?>
                                            <a href="?action=abrir&id=<?php echo $v['id']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; border-color: #10b981; color: #10b981; font-size: 0.8rem;" title="Reanudar publicación"><i class="fas fa-play"></i></a>
                                        <?php endif; ?>
                                        <a href="editar_vacante.php?id=<?php echo $v['id']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; color: var(--primary-color); border-color: var(--primary-color);" title="Editar vacante"><i class="fas fa-edit"></i></a>
                                        <a href="postulaciones.php?vacante_id=<?php echo $v['id']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; color: var(--accent-color); border-color: var(--accent-color);" title="Ver postulantes"><i class="fas fa-users"></i></a>
                                    </div>
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
