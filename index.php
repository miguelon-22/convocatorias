<?php
require_once 'includes/functions.php';

$pdo = get_db_connection();

$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$sector = isset($_GET['sector']) ? clean_input($_GET['sector']) : '';
$modalidad = isset($_GET['modalidad']) ? clean_input($_GET['modalidad']) : '';

// Build query
$query = "SELECT v.*, e.nombre_comercial, e.foto_perfil, e.sector as e_sector FROM vacantes v 
          JOIN empresas e ON v.empresa_id = e.id 
          WHERE v.estado = 'abierta' AND e.estado = 'activo'";

$params = [];
if (!empty($search)) {
    $query .= " AND (v.titulo_puesto ILIKE ? OR e.nombre_comercial ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if (!empty($sector)) {
    $query .= " AND e.sector = ?";
    $params[] = $sector;
}
if (!empty($modalidad)) {
    $query .= " AND v.modalidad = ?";
    $params[] = $modalidad;
}

$query .= " ORDER BY v.creado_en DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$vacantes = $stmt->fetchAll();

// Get unique sectors for filter
$sectors_stmt = $pdo->query("SELECT DISTINCT sector FROM empresas WHERE sector IS NOT NULL AND estado = 'activo'");
$sectors = $sectors_stmt->fetchAll(PDO::FETCH_COLUMN);

include 'includes/header.php';
?>

<section class="animate-fade-in">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem;">Encuentra tu próximo <span class="gradient-text">Gran Paso</span></h1>
        <p style="color: var(--text-muted); font-size: 1.25rem;">La plataforma donde el talento se encuentran con las oportunidades.</p>
    </div>

    <form method="GET" action="" class="glass-panel filter-bar">
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="search" value="<?php echo $search; ?>" class="form-control" placeholder="Puesto o empresa...">
        </div>
        <div style="min-width: 150px;">
            <select name="sector" class="form-control">
                <option value="">Todos los sectores</option>
                <?php foreach ($sectors as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $sector == $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="min-width: 150px;">
            <select name="modalidad" class="form-control">
                <option value="">Todas las modalidades</option>
                <option value="Presencial" <?php echo $modalidad == 'Presencial' ? 'selected' : ''; ?>>Presencial</option>
                <option value="Remoto" <?php echo $modalidad == 'Remoto' ? 'selected' : ''; ?>>Remoto</option>
                <option value="Híbrido" <?php echo $modalidad == 'Híbrido' ? 'selected' : ''; ?>>Híbrido</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
    </form>

    <div class="card-grid">
        <?php if (empty($vacantes)): ?>
            <div class="glass-panel" style="grid-column: 1 / -1; text-align: center; padding: 4rem;">
                <i class="fas fa-search-minus" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                <h3>No se encontraron vacantes</h3>
                <p>Prueba ajustando los filtros o vuelve más tarde.</p>
            </div>
        <?php else: ?>
            <?php foreach ($vacantes as $v): ?>
                <div class="glass-panel" style="display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: start; gap: 1rem; margin-bottom: 1.5rem;">
                        <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--border-color);">
                            <?php if ($v['foto_perfil']): ?>
                                <img src="<?php echo $v['foto_perfil']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-building" style="color: var(--text-muted);"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h3 style="margin-bottom: 0.25rem; font-size: 1.15rem;"><?php echo $v['titulo_puesto']; ?></h3>
                            <p style="font-size: 0.875rem; color: var(--text-muted); font-weight: 500;"><?php echo $v['nombre_comercial']; ?></p>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                        <span class="badge" style="background: rgba(99, 102, 241, 0.1); color: #818cf8;"><?php echo $v['modalidad']; ?></span>
                        <span class="badge" style="background: rgba(236, 72, 153, 0.1); color: #f472b6;"><?php echo $v['e_sector']; ?></span>
                        <span class="badge" style="background: rgba(148, 163, 184, 0.1); color: #94a3b8;"><?php echo $v['ubicacion']; ?></span>
                    </div>

                    <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Publicado: <?php echo format_date($v['creado_en']); ?></span>
                        <a href="vacante_detalle.php?id=<?php echo $v['id']; ?>" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.875rem;">Ver Detalles</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
