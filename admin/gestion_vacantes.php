<?php
require_once '../includes/functions.php';
require_admin();

$pdo = get_db_connection();

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    if ($action == 'eliminar') {
        $stmt = $pdo->prepare("DELETE FROM vacantes WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['alert'] = ['message' => "Vacante eliminada con éxito.", 'type' => 'success'];
    }
    header("Location: gestion_vacantes.php");
    exit();
}

$stmt = $pdo->query("SELECT v.*, e.nombre_comercial FROM vacantes v 
                    JOIN empresas e ON v.empresa_id = e.id 
                    ORDER BY v.creado_en DESC");
$vacantes = $stmt->fetchAll();

$page_title = "Admin: Auditoría de Vacantes";
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
    <header style="margin-bottom: 3rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 class="gradient-text">Auditoría de Vacantes</h1>
            <p style="color: var(--text-muted);">Monitorea todas las ofertas publicadas por las empresas.</p>
        </div>
    </header>

    <?php display_alert(); ?>

    <div class="glass-panel animate-fade-in">
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Empresa</th>
                        <th>Modalidad</th>
                        <th>Estado</th>
                        <th>Postulantes</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vacantes)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 3rem;">No hay vacantes publicadas en el sistema.</td></tr>
                    <?php else: ?>
                        <?php foreach ($vacantes as $v): ?>
                            <tr>
                                <td><strong><?php echo $v['titulo_puesto']; ?></strong></td>
                                <td><?php echo $v['nombre_comercial']; ?></td>
                                <td><?php echo $v['modalidad']; ?> / <?php echo $v['ubicacion']; ?></td>
                                <td>
                                    <span class="badge" style="background: <?php echo $v['estado'] == 'abierta' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; color: <?php echo $v['estado'] == 'abierta' ? '#10b981' : '#ef4444'; ?>;">
                                        <?php echo strtoupper($v['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $num_post = $pdo->prepare("SELECT COUNT(*) FROM postulaciones WHERE vacante_id = ?");
                                        $num_post->execute([$v['id']]);
                                        echo $num_post->fetchColumn();
                                    ?> personas
                                </td>
                                <td>
                                    <a href="?action=eliminar&id=<?php echo $v['id']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; border-color: #ef4444; color: #ef4444; font-size: 0.75rem;" onclick="return confirm('¿Estás seguro de eliminar esta vacante?')"><i class="fas fa-trash"></i> Eliminar</a>
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
