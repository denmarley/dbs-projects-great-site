<style>
/* Üst Bar Ortak Stilleri */
.navbar-custom {
    background-color: #ffffff;
    border-bottom: 1px solid #dee2e6;
    padding: 0.75rem 2rem;
}
.brand-box {
    background-color: #96c83c;
    color: white;
    font-weight: bold;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.nav-pill-btn {
    background: #f1f3f5;
    color: #495057;
    border: 1px solid #ced4da;
    padding: 6px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.nav-pill-btn:hover, .nav-pill-btn.active {
    background: #96c83c;
    color: white;
    border-color: #96c83c;
    box-shadow: 0 4px 6px rgba(25, 135, 84, 0.2);
}
</style>



<?php
// Sayfa adını yakalıyoruz (Örn: PR.01.projects.php)
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-custom d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-3">
        <a href="GN.03.home.php" class="brand-box">
            <i class="fa-solid fa-building"></i> dbs
        </a>
        <span class="fs-5 fw-semibold text-secondary">
            <?php 
                // Sayfaya göre başlık otomatik değişebilir veya sabit kalabilir
                if ($currentPage == 'PR.01.projects.php') echo 'Projects & Contracts';
                elseif ($currentPage == 'GN.04.usersPage.php') echo 'Team Management';
                elseif ($currentPage == 'DC.01.documents.php') echo 'Documents Portal';
                else echo 'Project Management Portal';
            ?>
        </span>
    </div>

    <!-- Navigasyon Düğmeleri (Aktif sayfaya göre otomatik active sınıfı eklenir) -->
    <div class="d-flex align-items-center gap-2">
        <a href="GN.03.home.php" class="nav-pill-btn <?= ($currentPage == 'GN.03.home.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i> HOME
        </a>
        <a href="PR.01.projects.php" class="nav-pill-btn <?= ($currentPage == 'PR.01.projects.php' || strpos($currentPage, 'PR.02') !== false) ? 'active' : '' ?>">
            <i class="fa-solid fa-folder-open"></i> PROJECTS
        </a>
        <a href="GN.04.usersPage.php" class="nav-pill-btn <?= ($currentPage == 'GN.04.usersPage.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i> TEAM
        </a>
        <a href="DC.01.documents.php" class="nav-pill-btn <?= ($currentPage == 'DC.01.documents.php') ? 'active' : '' ?>">
            <i class="fa-solid fa-file-lines"></i> DOCUMENTS
        </a>
    </div>

    <div>
        <a href="GN.02.login.php" class="btn btn-outline-danger btn-sm px-3 rounded-pill">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</nav>