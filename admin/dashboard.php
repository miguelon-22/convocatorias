<?php
require_once '../includes/functions.php';
require_admin();

$pdo = get_db_connection();

// Basic stats
$total_empresas = $pdo->query("SELECT COUNT(*) FROM empresas")->fetchColumn();
$total_vacantes = $pdo->query("SELECT COUNT(*) FROM vacantes")->fetchColumn();
$total_postulaciones = $pdo->query("SELECT COUNT(*) FROM postulaciones")->fetchColumn();
$pending_empresas = $pdo->query("SELECT COUNT(*) FROM empresas WHERE estado = 'pendiente'")->fetchColumn();

// Chart 1: Sales / Registries simulation
$sales_data = [200, 450, 300, 700, 850, 600, 950, 1100, 1050, 1300, 1450, 1600];
$months = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

// Chart 2: Empresas by status
$stmt_char2 = $pdo->query("SELECT estado, COUNT(*) as count FROM empresas GROUP BY estado");
$chart2_data = $stmt_char2->fetchAll();
$labels2 = array_column($chart2_data, 'estado');
$values2 = array_column($chart2_data, 'count');

$page_title = "Admin Dashboard: TalentFlow";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-container">

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <div>
            <h1 class="gradient-text" style="font-size: 2.25rem;">Panel de Control Admin</h1>
            <p style="color: var(--text-muted);">Administra la plataforma, empresas y postulaciones globales.</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div style="text-align: right; margin-right: 1.5rem;">
                 <p style="font-size: 0.9rem; font-weight: 700; color: white;"><?php echo date('d M, Y'); ?></p>
                 <p style="font-size: 0.75rem; color: #10b981; font-weight: 600;"><i class="fas fa-circle" style="font-size: 0.5rem; margin-right: 0.3rem;"></i> SISTEMA ACTIVO</p>
            </div>
            <a href="gestion_empresas.php" class="btn btn-primary" style="padding: 1rem 1.75rem;"><i class="fas fa-building"></i> Gestionar Empresas</a>
        </div>
    </header>

    <?php display_alert(); ?>

    <!-- Stats Grid -->
    <div class="card-grid" style="margin-bottom: 3rem;">
        <div class="glass-panel" style="padding: 2.5rem; border-left: 6px solid #818cf8;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <span style="color: var(--text-muted); font-weight: 700; font-size: 0.85rem;">TOTAL EMPRESAS</span>
                <i class="fas fa-building" style="color: #818cf8; font-size: 1.5rem;"></i>
            </div>
            <h3 style="font-size: 2.5rem; line-height: 1; margin-bottom: 0.5rem;"><?php echo $total_empresas; ?></h3>
            <p style="font-size: 0.85rem; color: #10b981; font-weight: 700;"><i class="fas fa-arrow-up"></i> +12% este mes</p>
        </div>
        
        <div class="glass-panel" style="padding: 2.5rem; border-left: 6px solid #f472b6;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <span style="color: var(--text-muted); font-weight: 700; font-size: 0.85rem;">POSTULACIONES</span>
                <i class="fas fa-users-viewfinder" style="color: #f472b6; font-size: 1.5rem;"></i>
            </div>
            <h3 style="font-size: 2.5rem; line-height: 1; margin-bottom: 0.5rem;"><?php echo $total_postulaciones; ?></h3>
            <p style="font-size: 0.85rem; color: #10b981; font-weight: 700;"><i class="fas fa-arrow-up"></i> +25% acumulado</p>
        </div>

        <div class="glass-panel" style="padding: 2.5rem; border-left: 6px solid #f59e0b;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <span style="color: var(--text-muted); font-weight: 700; font-size: 0.85rem;">PENDIENTES</span>
                <i class="fas fa-clock" style="color: #f59e0b; font-size: 1.5rem;"></i>
            </div>
            <h3 style="font-size: 2.5rem; line-height: 1; margin-bottom: 0.5rem;"><?php echo $pending_empresas; ?></h3>
            <p style="font-size: 0.85rem; color: #f59e0b; font-weight: 700;">Requieren atención Inmediata</p>
        </div>
    </div>

    <!-- Dual Charts Row -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 3rem;">
        <div class="glass-panel" style="padding: 3rem;">
            <h3 style="margin-bottom: 2.5rem; font-size: 1.5rem;">Crecimiento de la Plataforma</h3>
            <div style="height: 350px;">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        <div class="glass-panel" style="padding: 3rem;">
            <h3 style="margin-bottom: 2.5rem; font-size: 1.5rem;">Estado de Empresas</h3>
            <div style="height: 350px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 2rem;">
         <a href="gestion_empresas.php" class="glass-panel" style="padding: 2.5rem; text-decoration: none; border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s ease; text-align: center;">
             <i class="fas fa-tasks" style="font-size: 2.5rem; color: #818cf8; margin-bottom: 1.5rem;"></i>
             <h4 style="color: white; font-size: 1.25rem;">Auditar Empresas</h4>
             <p style="color: var(--text-muted); font-size: 0.9rem;">Aprobar, rechazar o bloquear entidades.</p>
         </a>
         <a href="gestion_vacantes.php" class="glass-panel" style="padding: 2.5rem; text-decoration: none; border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s ease; text-align: center;">
             <i class="fas fa-file-invoice" style="font-size: 2.5rem; color: #f472b6; margin-bottom: 1.5rem;"></i>
             <h4 style="color: white; font-size: 1.25rem;">Auditar Vacantes</h4>
             <p style="color: var(--text-muted); font-size: 0.9rem;">Monitorea las ofertas de empleo globales.</p>
         </a>
         <a href="configuracion.php" class="glass-panel" style="padding: 2.5rem; text-decoration: none; border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s ease; text-align: center;">
             <i class="fas fa-shield-alt" style="font-size: 2.5rem; color: #10b981; margin-bottom: 1.5rem;"></i>
             <h4 style="color: white; font-size: 1.25rem;">Seguridad</h4>
             <p style="color: var(--text-muted); font-size: 0.9rem;">Ajustes de logs y configuración del sistema.</p>
         </a>
    </div>
</main>

<script>
    // Line Chart
    const ctx1 = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Interacción de Usuarios',
                data: <?php echo json_encode($sales_data); ?>,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.15)',
                borderWidth: 5,
                tension: 0.45,
                fill: true,
                pointBackgroundColor: '#6366f1',
                pointRadius: 6,
                pointHoverRadius: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } }
            },
            plugins: { legend: { display: false } }
        }
    });

    // Donut Chart
    const ctx2 = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($labels2); ?>,
            datasets: [{
                data: <?php echo json_encode($values2); ?>,
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#818cf8'],
                borderWidth: 0,
                hoverOffset: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#94a3b8', font: { weight: 'bold' }, padding: 15 } }
            },
            cutout: '75%'
        }
    });
</script>

</body>
</html>
