<?php
require_once 'includes/functions.php';

$dni = isset($_GET['dni']) ? clean_input($_GET['dni']) : '';
$vacante_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (empty($dni) || $vacante_id <= 0) {
    header("Location: index.php");
    exit();
}

$pdo = get_db_connection();
$stmt = $pdo->prepare("SELECT p.*, v.titulo_puesto FROM postulaciones p 
                       JOIN vacantes v ON p.vacante_id = v.id 
                       WHERE p.dni = ? AND p.vacante_id = ?");
$stmt->execute([$dni, $vacante_id]);
$p = $stmt->fetch();

include 'includes/header.php';
?>

<div class="animate-fade-in" style="max-width: 600px; margin: 4rem auto;">
    <?php if (!$p): ?>
        <div class="glass-panel" style="text-align: center; padding: 4rem;">
            <i class="fas fa-search-minus" style="font-size: 3.5rem; color: #ef4444; margin-bottom: 2rem;"></i>
            <h2>No se encontró postulación</h2>
            <p style="color: var(--text-muted); line-height: 1.8;">No existe un registro de postulación con el DNI: <strong><?php echo $dni; ?></strong> para esta vacante.</p>
            <div style="margin-top: 2rem;">
                <a href="vacante_detalle.php?id=<?php echo $vacante_id; ?>" class="btn btn-primary">Regresar e Intentar</a>
            </div>
        </div>
    <?php else: ?>
        <div class="glass-panel" style="text-align: center; padding: 4rem; border-top: 5px solid var(--primary-color);">
            <div style="background: var(--gradient-primary); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin: 0 auto 2rem; font-size: 2.5rem;">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h1 class="gradient-text" style="font-size: 2.25rem; margin-bottom: 0.5rem;"><?php echo (int)$p['match_porcentaje']; ?>% de Match</h1>
            <p style="color: var(--text-muted); font-size: 1.15rem; margin-bottom: 2.5rem;">Resultado del análisis IA para: <br><strong><?php echo $p['titulo_puesto']; ?></strong></p>
            
            <div style="background: rgba(255,255,255,0.03); border-radius: 20px; padding: 2rem; text-align: left; margin-bottom: 2.5rem; border: 1px solid var(--border-color);">
                <h4 style="margin-bottom: 1rem;"><i class="fas fa-brain" style="color: var(--primary-color);"></i> Retroalimentación IA:</h4>
                <p style="color: var(--text-color); font-style: italic; font-size: 0.95rem; line-height: 1.6;">"<?php echo $p['ia_analisis_descripcion']; ?>"</p>
            </div>

            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 2rem;">Postulante: <strong><?php echo $p['nombre_completo']; ?></strong></p>
            
            <a href="vacante_detalle.php?id=<?php echo $vacante_id; ?>" class="btn btn-outline" style="width: 100%;">Volver a la Vacante</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
