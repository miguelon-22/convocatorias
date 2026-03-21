<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . " | " : ""; echo get_site_setting('nombre_sitio', 'TalentFlow'); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="glass-panel" style="margin: 1rem; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; border-radius: 15px; position: sticky; top: 1rem; z-index: 1000;">
        <a href="<?php echo BASE_URL; ?>" style="text-decoration: none; display: flex; align-items: center; gap: 0.75rem;">
            <div style="background: var(--gradient-primary); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white;">
                <i class="fas fa-briefcase"></i>
            </div>
            <span style="font-size: 1.25rem; font-weight: 700; color: white;">Talent<span class="gradient-text">Flow</span></span>
        </a>
        
        <div style="display: flex; gap: 1.5rem; align-items: center;">
            <a href="<?php echo BASE_URL; ?>" style="color: var(--text-muted); text-decoration: none; font-weight: 500;">Vacantes</a>
            
            <?php if (is_admin_logged_in()): ?>
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="btn btn-outline" style="padding: 0.5rem 1rem;">Admin Dashboard</a>
            <?php elseif (is_empresa_logged_in()): ?>
                <a href="<?php echo BASE_URL; ?>empresa/dashboard.php" class="btn btn-outline" style="padding: 0.5rem 1rem;">Empresa Dashboard</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-primary" style="padding: 0.5rem 1.5rem;">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </nav>
    <main class="container">
        <?php display_alert(); ?>
