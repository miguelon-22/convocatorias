<?php
require_once '../includes/functions.php';
require_empresa();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$empresa_id = $_SESSION['empresa_id'];

$pdo = get_db_connection();
$stmt = $pdo->prepare("SELECT p.*, v.titulo_puesto, v.requisitos_raw FROM postulaciones p 
                       JOIN vacantes v ON p.vacante_id = v.id 
                       WHERE p.id = ? AND v.empresa_id = ?");
$stmt->execute([$id, $empresa_id]);
$p = $stmt->fetch();

if (!$p) {
    header("Location: postulaciones.php");
    exit();
}

// Trigger n8n analysis
if (isset($_GET['action']) && $_GET['action'] == 'reanalyze') {
    $success = analyze_cv_with_n8n($id);
    if ($success) {
        redirect_with_message("ver_candidato.php?id=$id", "¡Análisis con IA (n8n) completado con éxito!");
    } else {
        header("Location: ver_candidato.php?id=$id");
        exit();
    }
}

// Handle Approve Action via POST (now with additional message)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'approve') {
    $adicional = clean_input($_POST['mensaje_adicional'] ?? '');
    
    $stmt_up = $pdo->prepare("UPDATE postulaciones SET estado_postulacion = 'apto' WHERE id = ?");
    $stmt_up->execute([$id]);
    
    // Send email with additional content
    $student_email = $p['correo_estudiante'];
    $student_name = $p['nombre_completo'];
    $job_title = $p['titulo_puesto'];
    
    $mail_sent = send_selection_email($student_email, $student_name, $job_title, $adicional);
    
    if ($mail_sent) {
        $_SESSION['alert'] = [
            'message' => "¡Candidato seleccionado! Se ha marcado como APTO y se envió el correo a $student_email con el mensaje adicional.",
            'type' => 'success'
        ];
    } else {
        $error_detail = $_SESSION['last_smtp_error'] ?? 'Verifica que la extensión openssl esté activada.';
        $_SESSION['alert'] = [
            'message' => "Se marcó como APTO, pero hubo un problema SMTP: $error_detail",
            'type' => 'warning'
        ];
        unset($_SESSION['last_smtp_error']);
    }
    
    header("Location: ver_candidato.php?id=$id");
    exit();
}

$page_title = "Perfil Candidato: " . $p['nombre_completo'];

// Simulated Advanced AI Breakdown (inspired by cv_analizador_n8n)
$ai_score = (int)$p['match_porcentaje'];
$status_color = $p['estado_postulacion'] == 'apto' ? '#10b981' : ($ai_score >= 80 ? '#10b981' : ($ai_score >= 50 ? '#f59e0b' : '#ef4444'));
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
            <h1 class="gradient-text">Expediente del Candidato</h1>
            <p style="color: var(--text-muted);">Análisis detallado de postulación para: <strong><?php echo $p['titulo_puesto']; ?></strong></p>
        </div>
        <div style="display: flex; gap: 1rem;">
             <a href="postulaciones.php" class="btn btn-outline" style="padding: 0.8rem 1.25rem; border-radius: 14px;"><i class="fa-solid fa-arrow-left"></i> Volver al listado</a>
        </div>
    </header>

    <?php display_alert(); ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2.5rem;">
        <div>
            <!-- Candidato Profile Card -->
            <div class="glass-panel" style="padding: 3rem; margin-bottom: 2.5rem; display: flex; align-items: flex-start; gap: 3rem; border: 1px solid rgba(255,255,255,0.05); position: relative; overflow: hidden;">
                 <div style="position: absolute; top:0; left:0; width:5px; height:100%; background: <?php echo $status_color; ?>;"></div>
                 
                 <div style="width: 140px; height: 140px; border-radius: 30px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 3.5rem; flex-shrink: 0; box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);">
                    <?php echo substr($p['nombre_completo'], 0, 1); ?>
                 </div>
                 <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <h2 style="font-size: 2.5rem; color: white; margin: 0;"><?php echo $p['nombre_completo']; ?></h2>
                        <?php if ($p['estado_postulacion'] == 'apto'): ?>
                            <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 0.5rem 1rem; border-radius: 12px; font-weight: 800; font-size: 0.8rem; border: 1px solid rgba(16, 185, 129, 0.2);">
                                <i class="fa-solid fa-check-double"></i> SELECCIONADO / APTO
                            </span>
                        <?php endif; ?>
                    </div>
                    <p style="font-size: 1.15rem; color: #818cf8; margin-bottom: 2rem; font-weight: 600;"><?php echo $p['correo_estudiante']; ?></p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.05);">
                        <div>
                            <p style="font-size: 0.75rem; color: #64748b; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">DNI / ID</p>
                            <p style="font-weight: 700; color: white; font-family: monospace; font-size: 1.1rem;"><?php echo $p['dni']; ?></p>
                        </div>
                        <div>
                            <p style="font-size: 0.75rem; color: #64748b; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">TELÉFONO</p>
                            <p style="font-weight: 700; color: white; font-size: 1.1rem;"><?php echo $p['celular'] ?? 'No registrado'; ?></p>
                        </div>
                        <div>
                            <p style="font-size: 0.75rem; color: #64748b; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">POSTULADO EL</p>
                            <p style="font-weight: 700; color: white; font-size: 1.1rem;"><?php echo isset($p['fecha_postulacion']) ? format_date($p['fecha_postulacion']) : '-'; ?></p>
                        </div>
                    </div>
                 </div>
            </div>

            <!-- AI Intelligence Report (CV_ANALIZADOR_N8N Style) -->
            <div class="glass-panel" style="padding: 3.5rem; border: 1px solid rgba(99, 102, 241, 0.1); background: rgba(15, 23, 42, 0.4);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
                    <h3 style="display: flex; align-items: center; gap: 1rem; font-size: 1.5rem; color: white;">
                        <i class="fa-solid fa-microchip" style="color: #818cf8;"></i> Informe de Inteligencia Artificial
                    </h3>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <a href="?id=<?php echo $id; ?>&action=reanalyze" class="btn btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.8rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                            <i class="fa-solid fa-sync"></i> Regenerar con n8n
                        </a>
                        <div style="background: rgba(129, 140, 248, 0.1); padding: 0.5rem 1rem; border-radius: 10px; border: 1px solid rgba(129, 140, 248, 0.2);">
                            <span style="font-size: 0.8rem; font-weight: 700; color: #818cf8;">ALIMENTADO POR N8N + LLM</span>
                        </div>
                    </div>
                </div>

                <!-- Structured Report Area -->
                <div style="display: grid; grid-template-columns: 1fr; gap: 2.5rem;">
                    <div style="background: rgba(255,255,255,0.02); padding: 2.5rem; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05);">
                        <h4 style="color: #818cf8; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fa-solid fa-magnifying-glass-chart"></i> Resumen de Compatibilidad
                        </h4>
                        <p style="line-height: 1.8; color: #cbd5e1; font-size: 1.05rem;">
                            <?php echo $p['ia_analisis_descripcion'] ?: 'El análisis detallado no está disponible actualmente.'; ?>
                        </p>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div style="background: rgba(16, 185, 129, 0.03); padding: 2rem; border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.1);">
                            <h5 style="color: #10b981; margin-bottom: 1rem;"><i class="fa-solid fa-circle-check"></i> Fortalezas Detectadas</h5>
                            <ul style="color: #94a3b8; font-size: 0.9rem; padding-left: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                                <li>Alineación técnica con los requisitos clave.</li>
                                <li>Experiencia relevante en el sector del puesto.</li>
                                <li>Formación académica sólida.</li>
                            </ul>
                        </div>
                        <div style="background: rgba(239, 68, 68, 0.03); padding: 2rem; border-radius: 20px; border: 1px solid rgba(239, 68, 68, 0.1);">
                            <h5 style="color: #ef4444; margin-bottom: 1rem;"><i class="fa-solid fa-circle-xmark"></i> Áreas de Mejora</h5>
                            <ul style="color: #94a3b8; font-size: 0.9rem; padding-left: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                                <li>Falta profundizar en algunas skills específicas.</li>
                                <li>Brecha potencial en certificaciones internacionales.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <!-- Global Match Score Card -->
            <div class="glass-panel" style="text-align: center; padding: 4rem 2rem; margin-bottom: 2.5rem; border-top: 8px solid <?php echo $status_color; ?>; position: relative; overflow: hidden;">
                <div style="position: absolute; top:0; right:0; padding: 1rem;">
                    <i class="fa-solid fa-robot" style="font-size: 2rem; opacity: 0.1; color: white;"></i>
                </div>

                <p style="font-size: 0.85rem; color: #64748b; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 2.5rem;">INDICE DE MATCH IA</p>
                
                <div style="position: relative; width: 180px; height: 180px; margin: 0 auto 3rem;">
                     <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                        <path stroke="rgba(255,255,255,0.05)" stroke-width="2.5" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path stroke="<?php echo $status_color; ?>" stroke-width="2.5" stroke-dasharray="<?php echo $ai_score; ?>, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                     </svg>
                     <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                         <span style="font-size: 3.5rem; font-weight: 900; color: white; line-height: 1;"><?php echo $ai_score; ?><span style="font-size: 1.5rem; color: #64748b;">%</span></span>
                     </div>
                </div>

                <a href="../<?php echo $p['url_cv_pdf']; ?>" target="_blank" class="btn btn-primary" style="width: 100%; padding: 1.5rem; border-radius: 18px; font-weight: 800; letter-spacing: 0.5px; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);">
                    <i class="fa-solid fa-file-pdf" style="margin-right: 0.75rem;"></i> VER CV COMPLETO
                </a>
            </div>

            <!-- Recruitment Workflow -->
            <div class="glass-panel" style="padding: 2.5rem; border: 1px solid rgba(255,255,255,0.03);">
                <h4 style="margin-bottom: 2rem; color: white; display: flex; align-items: center; gap: 0.75rem; font-size: 1.1rem;">
                    <i class="fa-solid fa-envelope-circle-check" style="color: #64748b;"></i> Gestión de Selección
                </h4>
                
                <form method="POST">
                    <input type="hidden" name="action" value="approve">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-size: 0.8rem; color: #94a3b8; font-weight: 700; display: block; margin-bottom: 0.75rem;">MENSAJE ADICIONAL PARA EL ESTUDIANTE</label>
                        <textarea name="mensaje_adicional" rows="4" placeholder="Ej: Te esperamos el lunes a las 9am para tu inducción..." 
                                  style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 1rem; color: white; font-size: 0.9rem; resize: none;"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.25rem; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 1rem; background: #10b981; border: none; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);">
                        <i class="fa-solid fa-paper-plane"></i> Marcar como APTO / Enviar Correo
                    </button>
                    
                    <p style="margin-top: 1.5rem; font-size: 0.75rem; color: var(--text-muted); text-align: center;">
                        <i class="fas fa-info-circle"></i> Esto cerrará el proceso para el candidato y le enviará sus credenciales de selección.
                    </p>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
svg path {
    transition: stroke-dasharray 1s ease-in-out;
}
</style>

</body>
</html>
