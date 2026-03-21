<?php
$pdo_sidebar = get_db_connection();
$stmt_sidebar = $pdo_sidebar->prepare("SELECT * FROM empresas WHERE id = ?");
$stmt_sidebar->execute([$_SESSION['empresa_id']]);
$e_sidebar = $stmt_sidebar->fetch();
?>
<aside class="sidebar" id="appSidebar">
    <!-- Toggle Button -->
    <button id="sidebarToggle" style="position: absolute; right: 1rem; top: 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; width: 35px; height: 35px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; z-index: 1000; transition: all 0.3s ease;">
        <i class="fa-solid fa-chevron-left" id="toggleIcon"></i>
    </button>

    <a href="<?php echo BASE_URL; ?>" class="sidebar-brand">
        <div style="background: var(--gradient-primary); width: 45px; height: 45px; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 8px 16px rgba(99, 102, 241, 0.3);">
            <i class="fa-solid fa-briefcase" style="font-size: 1.2rem;"></i>
        </div>
        <span style="font-size: 1.3rem; font-weight: 800; color: white; letter-spacing: -0.5px;"><?php echo get_site_setting('nombre_sitio', 'TalentFlow'); ?></span>
    </a>

    <div class="user-sidebar-card">
        <div style="width: 50px; height: 50px; border-radius: 16px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
            <?php if ($e_sidebar['foto_perfil']): ?>
                <img src="../<?php echo $e_sidebar['foto_perfil']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <div style="width: 100%; height: 100%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-weight: 800; color: white; font-size: 1.2rem;">
                    <?php echo substr($e_sidebar['nombre_comercial'], 0, 1); ?>
                </div>
            <?php endif; ?>
        </div>
        <div style="min-width: 0;">
            <p style="font-size: 0.95rem; font-weight: 700; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 0.1rem;"><?php echo $e_sidebar['nombre_comercial']; ?></p>
            <p style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Portal Empresa</p>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="sidebar-item">
            <a href="dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-gauge-high"></i> <span>Panel de Control</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="publicar_vacante.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'publicar_vacante.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-bullhorn"></i> <span>Nueva Vacante</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="mis_vacantes.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'mis_vacantes.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-list-check"></i> <span>Mis Vacantes</span>
            </a>
        </li>
        
        <div class="sidebar-divider"></div>

        <li class="sidebar-item">
            <a href="postulantes_filtro.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'postulantes_filtro.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-magnifying-glass-chart"></i> <span>Filtrar Talento</span>
            </a>
        </li>
        
        <li class="sidebar-item">
            <a href="postulaciones.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'postulaciones.php' || basename($_SERVER['PHP_SELF']) == 'ver_candidato.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i> <span>Postulantes</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="perfil.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-building-circle-gear"></i> <span>Perfil Empresa</span>
            </a>
        </li>

        <li class="sidebar-item" style="margin-top: 2rem;">
            <a href="logout.php" class="sidebar-link" style="color: #fb7185; background: rgba(251, 113, 133, 0.05);">
                <i class="fa-solid fa-share-from-square"></i> <span>Cerrar Sesión</span>
            </a>
        </li>
    </ul>
</aside>

<script>
document.getElementById('sidebarToggle').addEventListener('click', function() {
    const sidebar = document.getElementById('appSidebar');
    const mainContent = document.querySelector('.main-content');
    const icon = document.getElementById('toggleIcon');
    
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
    
    if (sidebar.classList.contains('collapsed')) {
        icon.classList.replace('fa-chevron-left', 'fa-chevron-right');
        this.style.left = '50%';
        this.style.transform = 'translateX(-50%)';
        this.style.right = 'auto';
    } else {
        icon.classList.replace('fa-chevron-right', 'fa-chevron-left');
        this.style.left = 'auto';
        this.style.right = '1rem';
        this.style.transform = 'none';
    }
});
</script>
