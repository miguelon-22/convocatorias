<?php
require_once '../includes/functions.php';
require_admin();

$pdo = get_db_connection();

// Acciones: Aprobar, Rechazar, Bloquear, Desbloquear
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    $nuevo_estado = '';
    switch ($action) {
        case 'aprobar':   $nuevo_estado = 'activo'; break;
        case 'rechazar':  $nuevo_estado = 'rechazado'; break;
        case 'bloquear':  $nuevo_estado = 'bloqueado'; break;
        case 'activar':   $nuevo_estado = 'activo'; break;
    }
    
    if ($nuevo_estado) {
        $stmt = $pdo->prepare("UPDATE empresas SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $id]);
        $_SESSION['alert'] = ['message' => "Estado de la empresa actualizado a " . strtoupper($nuevo_estado), 'type' => 'success'];
    }
    header("Location: gestion_empresas.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM empresas ORDER BY creado_en DESC");
$empresas = $stmt->fetchAll();

$page_title = "Admin: Gestión de Empresas";
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
            <h1 class="gradient-text">Gestión de Empresas</h1>
            <p style="color: var(--text-muted);">Administra los accesos y registros de las compañías.</p>
        </div>
        <div style="display: flex; gap: 1rem;">
             <a href="dashboard.php" class="btn btn-outline"><i class="fas fa-chart-line"></i> Dashboard</a>
        </div>
    </header>

    <?php display_alert(); ?>

    <div class="glass-panel animate-fade-in">
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Perfil</th>
                        <th>Identificación</th>
                        <th>Sector/Contacto</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($empresas)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 3rem;">No hay registros disponibles.</td></tr>
                    <?php else: ?>
                        <?php foreach ($empresas as $e): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <div style="width: 45px; height: 45px; border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; background: rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center;">
                                            <?php if ($e['foto_perfil']): ?>
                                                <img src="../<?php echo $e['foto_perfil']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                            <?php else: ?>
                                                <i class="fas fa-building" style="color: var(--text-muted);"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <p style="font-weight: 700;"><?php echo $e['nombre_comercial']; ?></p>
                                            <p style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $e['direccion']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td><p style="font-family: monospace; font-weight: 600;"><?php echo $e['ruc']; ?></p></td>
                                <td>
                                    <span style="font-size: 0.85rem; display: block;"><?php echo $e['sector']; ?></span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $e['correo_contacto']; ?></span>
                                </td>
                                <td>
                                    <?php 
                                        $badge_class = '';
                                        switch ($e['estado']) {
                                            case 'activo':   $badge_class = 'badge-active'; break;
                                            case 'pendiente':$badge_class = 'badge-pending'; break;
                                            case 'rechazado':$badge_class = 'badge-rejected'; break;
                                            case 'bloqueado':$badge_class = 'badge-rejected'; break;
                                        }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo strtoupper($e['estado']); ?></span>
                                </td>
                                <td><?php echo format_date($e['creado_en']); ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <?php if ($e['estado'] == 'pendiente'): ?>
                                            <a href="?action=aprobar&id=<?php echo $e['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; background: var(--accent-color); font-size: 0.75rem;"><i class="fas fa-check"></i> Aprobar</a>
                                            <a href="?action=rechazar&id=<?php echo $e['id']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; border-color: #ef4444; color: #ef4444; font-size: 0.75rem;"><i class="fas fa-times"></i></a>
                                        <?php elseif($e['estado'] == 'activo'): ?>
                                            <a href="?action=bloquear&id=<?php echo $e['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; background: #ef4444; font-size: 0.75rem;"><i class="fas fa-ban"></i> Bloquear</a>
                                        <?php elseif($e['estado'] == 'bloqueado'): ?>
                                            <a href="?action=activar&id=<?php echo $e['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; background: #818cf8; font-size: 0.75rem;"><i class="fas fa-unlock"></i> Desbloquear</a>
                                        <?php endif; ?>
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
