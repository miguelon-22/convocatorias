<?php
require_once '../includes/functions.php';
require_empresa();

$pdo = get_db_connection();
$empresa_id = $_SESSION['empresa_id'];

// Get company stats
$total_v = $pdo->prepare("SELECT COUNT(*) FROM vacantes WHERE empresa_id = ?");
$total_v->execute([$empresa_id]);
$total_vacantes = $total_v->fetchColumn();

// Get total applications
$total_p = $pdo->prepare("SELECT COUNT(*) FROM postulaciones p JOIN vacantes v ON p.vacante_id = v.id WHERE v.empresa_id = ?");
$total_p->execute([$empresa_id]);
$total_postulaciones = $total_p->fetchColumn();

// Data for Chart 1: Postulantes por Vacante
$stmt_char1 = $pdo->prepare("SELECT titulo_puesto, (SELECT COUNT(*) FROM postulaciones WHERE vacante_id = v.id) as count 
                            FROM vacantes v WHERE empresa_id = ? LIMIT 5");
$stmt_char1->execute([$empresa_id]);
$chart1_data = $stmt_char1->fetchAll();
$labels1 = array_column($chart1_data, 'titulo_puesto');
$values1 = array_column($chart1_data, 'count');

// Data for Chart 2: Distribución por Modalidad
$stmt_char2 = $pdo->prepare("SELECT modalidad, COUNT(*) as count FROM vacantes WHERE empresa_id = ? GROUP BY modalidad");
$stmt_char2->execute([$empresa_id]);
$chart2_data = $stmt_char2->fetchAll();
$labels2 = array_column($chart2_data, 'modalidad');
$values2 = array_column($chart2_data, 'count');

// Recent top candidates
$stmt = $pdo->prepare("SELECT p.*, v.titulo_puesto FROM postulaciones p 
                       JOIN vacantes v ON p.vacante_id = v.id 
                       WHERE v.empresa_id = ? 
                       ORDER BY p.match_porcentaje DESC LIMIT 5");
$stmt->execute([$empresa_id]);
$postulaciones = $stmt->fetchAll();

$page_title = "Dashboard Empresa: TalentFlow";
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
            <h1 class="gradient-text" style="font-size: 2rem; margin-bottom: 0.25rem;">Panel de Talento</h1>
            <p style="color: var(--text-muted);">Gestiona tus convocatorias y encuentra candidatos ideales.</p>
        </div>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <a href="publicar_vacante.php" class="btn btn-primary" style="padding: 1rem 1.5rem;"><i class="fas fa-plus"></i> Publicar Puesto</a>
        </div>
    </header>

    <?php display_alert(); ?>

    <div class="card-grid" style="margin-bottom: 3rem;">
        <div class="glass-panel" style="padding: 2rem; border-left: 5px solid var(--primary-color);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <span style="color: var(--text-muted); font-weight: 700; font-size: 0.8rem;">VACANTES</span>
                <i class="fas fa-file-invoice" style="color: var(--primary-color);"></i>
            </div>
            <h3 style="font-size: 2rem; margin-bottom: 0.25rem;"><?php echo $total_vacantes; ?></h3>
            <p style="font-size: 0.75rem; color: var(--accent-color); font-weight: 600;">Publicaciones activas</p>
        </div>
        
        <div class="glass-panel" style="padding: 2rem; border-left: 5px solid #f472b6;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <span style="color: var(--text-muted); font-weight: 700; font-size: 0.8rem;">POSTULANTES</span>
                <i class="fas fa-users-viewfinder" style="color: #f472b6;"></i>
            </div>
            <h3 style="font-size: 2rem; margin-bottom: 0.25rem;"><?php echo $total_postulaciones; ?></h3>
            <p style="font-size: 0.75rem; color: var(--accent-color); font-weight: 600;">Crecimiento exponencial</p>
        </div>

        <div class="glass-panel" style="padding: 2rem; border-left: 5px solid #10b981;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <span style="color: var(--text-muted); font-weight: 700; font-size: 0.8rem;">CALIDAD IA</span>
                <i class="fas fa-bolt" style="color: #10b981;"></i>
            </div>
            <h3 style="font-size: 2rem; margin-bottom: 0.25rem;">88%</h3>
            <p style="font-size: 0.75rem; color: #10b981; font-weight: 600;">Promedio de Match</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 3rem;">
        <div class="glass-panel" style="padding: 2.5rem;">
            <h3 style="margin-bottom: 2rem; font-size: 1.2rem;">Postulantes por Vacante</h3>
            <div style="height: 300px;">
                <canvas id="vacanteChart"></canvas>
            </div>
        </div>
        <div class="glass-panel" style="padding: 2.5rem;">
            <h3 style="margin-bottom: 2rem; font-size: 1.2rem;">Modalidad Puestos</h3>
            <div style="height: 300px;">
                <canvas id="modalidadChart"></canvas>
            </div>
        </div>
    </div>

    <div class="glass-panel animate-fade-in" style="padding: 2.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h3><i class="fas fa-crown" style="color: #f59e0b; margin-right: 0.5rem;"></i> Mejores Match Recientes</h3>
            <a href="mis_vacantes.php" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; font-weight: 700;">Gestionar todo <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i></a>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Candidato</th>
                        <th>Aplicación</th>
                        <th>IA Score</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($postulaciones)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 2rem;">Sin postulaciones de alto perfil aún.</td></tr>
                    <?php else: ?>
                        <?php foreach ($postulaciones as $p): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--bg-color); display: flex; align-items: center; justify-content: center; font-weight: 700; border: 1px solid var(--border-color);">
                                            <?php echo substr($p['nombre_completo'], 0, 1); ?>
                                        </div>
                                        <div>
                                            <p style="font-weight: 700; font-size: 0.9rem; color: white;"><?php echo $p['nombre_completo']; ?></p>
                                            <p style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $p['correo_estudiante']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td><strong><?php echo $p['titulo_puesto']; ?></strong></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="flex: 1; height: 6px; background: rgba(255,255,255,0.05); border-radius: 10px; min-width: 80px;">
                                            <div style="width: <?php echo $p['match_porcentaje']; ?>%; height: 100%; background: var(--gradient-primary); border-radius: 10px;"></div>
                                        </div>
                                        <span style="font-weight: 800; color: #818cf8; font-size: 0.85rem;"><?php echo (int)$p['match_porcentaje']; ?>%</span>
                                    </div>
                                </td>
                                <td><span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">DISPONIBLE</span></td>
                                <td><a href="ver_candidato.php?id=<?php echo $p['id']; ?>" class="btn btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;"><i class="fas fa-eye"></i> Perfil</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    // Chart 1: Bar Chart
    const ctx1 = document.getElementById('vacanteChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels1); ?>,
            datasets: [{
                label: 'Postulantes',
                data: <?php echo json_encode($values1); ?>,
                backgroundColor: 'rgba(99, 102, 241, 0.4)',
                borderColor: '#6366f1',
                borderWidth: 2,
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
            },
            plugins: { legend: { display: false } }
        }
    });

    // Chart 2: Pie Chart
    const ctx2 = document.getElementById('modalidadChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($labels2); ?>,
            datasets: [{
                data: <?php echo json_encode($values2); ?>,
                backgroundColor: ['#6366f1', '#f472b6', '#10b981', '#f59e0b'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#94a3b8', padding: 20, font: { weight: 'bold' } } }
            },
            cutout: '70%'
        }
    });
</script>

</body>
</html>
