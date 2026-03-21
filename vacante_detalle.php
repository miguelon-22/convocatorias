<?php
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

$pdo = get_db_connection();
$stmt = $pdo->prepare("SELECT v.*, e.nombre_comercial, e.foto_perfil, e.sector as e_sector, e.correo_contacto FROM vacantes v 
                       JOIN empresas e ON v.empresa_id = e.id 
                       WHERE v.id = ? AND v.estado = 'abierta' AND e.estado = 'activo'");
$stmt->execute([$id]);
$v = $stmt->fetch();

if (!$v) {
    header("Location: index.php");
    exit();
}

include 'includes/header.php';
?>

<div class="animate-fade-in" style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
    <!-- Post Details -->
    <div class="glass-panel" style="padding: 3rem;">
        <div style="display: flex; align-items: start; gap: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 2rem; margin-bottom: 2rem;">
            <div style="width: 80px; height: 80px; border-radius: 20px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--border-color);">
                <?php if ($v['foto_perfil']): ?>
                    <img src="<?php echo $v['foto_perfil']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <i class="fas fa-building" style="font-size: 2rem; color: var(--text-muted);"></i>
                <?php endif; ?>
            </div>
            <div>
                <h1 style="font-size: 2rem; margin-bottom: 0.25rem;"><?php echo $v['titulo_puesto']; ?></h1>
                <p style="font-size: 1.15rem; color: var(--text-muted); font-weight: 500;"><?php echo $v['nombre_comercial']; ?></p>
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <span style="color: #818cf8; font-weight: 500;"><i class="fas fa-map-marker-alt" style="margin-right: 0.5rem;"></i> <?php echo $v['modalidad']; ?> (<?php echo $v['ubicacion']; ?>)</span>
                    <span style="color: #f472b6; font-weight: 500;"><i class="fas fa-industry" style="margin-right: 0.5rem;"></i> <?php echo $v['e_sector']; ?></span>
                </div>
            </div>
        </div>

        <div style="margin-bottom: 3rem;">
            <h3 style="color: white; border-left: 4px solid var(--primary-color); padding-left: 1rem; margin-bottom: 1.5rem;">Descripción del Puesto</h3>
            <div style="color: var(--text-muted); line-height: 1.8; font-size: 1.1rem; white-space: pre-line;">
                <?php echo $v['descripcion_puesto']; ?>
            </div>
        </div>

        <div>
            <h3 style="color: white; border-left: 4px solid var(--secondary-color); padding-left: 1rem; margin-bottom: 1.5rem;">Requisitos</h3>
            <div style="color: var(--text-muted); line-height: 1.8; font-size: 1.1rem; white-space: pre-line;">
                <?php echo $v['requisitos_raw']; ?>
            </div>
        </div>
    </div>

    <!-- Apply Sidebar -->
    <div style="position: sticky; top: 6rem;">
        <div class="glass-panel" style="padding: 2rem;">
            <h3>Postula Ahora</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem;">Completa tus datos para enviar tu CV directamente a la empresa.</p>
            
            <form action="procesar_postulacion.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="vacante_id" value="<?php echo $v['id']; ?>">
                
                <div class="form-group">
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre_completo" class="form-control" required placeholder="Tu nombre...">
                </div>
                
                <div class="form-group">
                    <label>DNI / Documento Identidad</label>
                    <input type="text" name="dni" class="form-control" required placeholder="Número...">
                </div>
                
                <div class="form-group">
                    <label>Email de Contacto</label>
                    <input type="email" name="correo_estudiante" class="form-control" required placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label>Celular</label>
                    <input type="text" name="celular" class="form-control" required placeholder="+51 9...">
                </div>

                <div class="form-group">
                    <label>Adjuntar CV (PDF)</label>
                    <div style="position: relative; overflow: hidden; display: inline-block; width: 100%;">
                        <input type="file" name="cv_pdf" accept=".pdf" required class="form-control" style="font-size: 0.8rem; padding: 0.5rem;">
                    </div>
                </div>

                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color); text-align: center;">
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1.5rem;">
                        <i class="fas fa-lock" style="margin-right: 0.5rem;"></i> Tus datos serán enviados de forma segura.
                    </p>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">
                        <i class="fas fa-paper-plane" style="margin-right: 0.5rem;"></i> Enviar Postulación
                    </button>
                </div>
            </form>
        </div>

        <div class="glass-panel" style="padding: 1.5rem; text-align: center; margin-top: 1.5rem;">
            <p style="font-size: 0.875rem; color: var(--text-muted);">Fecha límite: <br><strong style="color: white;"><?php echo format_date($v['fecha_limite']); ?></strong></p>
        </div>

        <div class="glass-panel score-check-form animate-fade-in" style="margin-top: 1.5rem; text-align: center;">
            <h4 style="font-size: 0.95rem; margin-bottom: 1rem;"><i class="fas fa-search" style="margin-right: 0.5rem;"></i> Consultar mi Puntaje</h4>
            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1.25rem;">Ingresa tu DNI para ver tu Match IA si ya postulaste.</p>
            
            <form method="GET" action="ver_puntaje.php">
                <input type="hidden" name="id" value="<?php echo $v['id']; ?>">
                <input type="text" name="dni" class="form-control" placeholder="Número de DNI" required style="font-size: 0.85rem; margin-bottom: 0.75rem; text-align: center;">
                <button type="submit" class="btn btn-outline" style="width: 100%; font-size: 0.85rem; padding: 0.6rem;">Consultar</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
