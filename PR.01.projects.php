<?php
// PR.01.projects.php - Projects List, Management & Dual Drawer View
session_start();
$isAdmin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : true;

include 'GN.01.db.php';

// Aktif filtre / tab parametresini korumak için URL yönlendirme yönetimi
$active_tab = isset($_REQUEST['tab']) ? $_REQUEST['tab'] : 'all';

// Yeni Proje Ekleme İşlemi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
    $project_code  = 'DBS-' . date('Y') . '-' . rand(1000, 9999);
    $project_name  = $_POST['project_name'];
    $client        = $_POST['client'];
    $region        = $_POST['region'];
    $status        = $_POST['project_status'];
    $contract_no   = $_POST['contract_no'];
    $job_type      = $_POST['job_type'];
    $start_date    = $_POST['start_date'];
    $end_date      = $_POST['end_date'];
    $active        = 1; 

    $stmt = $conn->prepare("INSERT INTO projects (project_code, project_name, client, region, contract_no, job_type, start_date, end_date, status, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssi", $project_code, $project_name, $client, $region, $contract_no, $job_type, $start_date, $end_date, $status, $active);
    $stmt->execute();
    header("Location: PR.01.projects.php?tab=" . urlencode($active_tab));
    exit();
}

// Proje Güncelleme İşlemi (POST - Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_project'])) {
    $id            = intval($_POST['project_id']);
    $project_name  = $_POST['project_name'];
    $client        = $_POST['client'];
    $region        = $_POST['region'];
    $status        = $_POST['project_status'];
    $contract_no   = $_POST['contract_no'];
    $job_type      = $_POST['job_type'];
    $start_date    = $_POST['start_date'];
    $end_date      = $_POST['end_date'];

    $stmt = $conn->prepare("UPDATE projects SET project_name = ?, client = ?, region = ?, contract_no = ?, job_type = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssssssi", $project_name, $client, $region, $contract_no, $job_type, $start_date, $end_date, $status, $id);
    $stmt->execute();
    header("Location: PR.01.projects.php?tab=" . urlencode($active_tab));
    exit();
}

// Proje Deactivate İşlemi (POST - Admin Only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivate_project']) && $isAdmin) {
    $id = intval($_POST['project_id']);
    $stmt = $conn->prepare("UPDATE projects SET active = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: PR.01.projects.php?tab=" . urlencode($active_tab));
    exit();
}

// Proje Reactivate İşlemi (POST - Admin Only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reactivate_project']) && $isAdmin) {
    $id = intval($_POST['project_id']);
    $stmt = $conn->prepare("UPDATE projects SET active = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: PR.01.projects.php?tab=" . urlencode($active_tab));
    exit();
}

// Projeleri Veritabanından Çekme (Aktif ve Pasifler Ayrı Ayrı)
$result = $conn->query("SELECT * FROM projects WHERE active = 1 OR active IS NULL ORDER BY id DESC");
$projects = [];
$completed_projects = [];
$regions_list = [];
$total_projects_count = 0;
$ongoing_count = 0;
$planning_count = 0;
$completed_count = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $total_projects_count++;
        $st = strtolower($row['status']);
        
        if (!empty($row['region']) && !in_array($row['region'], $regions_list)) {
            $regions_list[] = $row['region'];
        }
        
        if (strpos($st, 'completed') !== false || strpos($st, 'biten') !== false) {
            $completed_count++;
            $completed_projects[] = $row;
        } else {
            $projects[] = $row;
            if (strpos($st, 'ongoing') !== false || strpos($st, 'devam') !== false) {
                $ongoing_count++;
            } elseif (strpos($st, 'planning') !== false || strpos($st, 'planlanan') !== false) {
                $planning_count++;
            }
        }
    }
}

// Deactive Projeleri Çekme
$deactive_result = $conn->query("SELECT * FROM projects WHERE active = 0 ORDER BY id DESC");
$deactive_projects = [];
if ($deactive_result) {
    while ($row = $deactive_result->fetch_assoc()) {
        $deactive_projects[] = $row;
    }
}

sort($regions_list);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects | DBS Project Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dbs-primary: rgb(150, 200, 60);
            --dbs-primary-dark: rgb(130, 180, 50);
            --dbs-primary-light: rgba(150, 200, 60, 0.12);
            --dbs-bg-light: #f8f9fa;
        }
        body {
            background-color: var(--dbs-bg-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar-custom {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 0.5rem 1.5rem;
        }
        .btn-dbs {
            background-color: var(--dbs-primary);
            color: #fff;
            border: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-dbs:hover {
            background-color: var(--dbs-primary-dark);
            color: #fff;
        }
        /* Stylish Filter Pills */
        .filter-pill {
            background: #ffffff;
            border: 2px solid #e9ecef;
            color: #495057;
            font-weight: 600;
            padding: 8px 18px;
            border-radius: 30px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .filter-pill:hover {
            border-color: var(--dbs-primary);
            color: var(--dbs-primary-dark);
        }
        .filter-pill.active {
            background-color: var(--dbs-primary);
            border-color: var(--dbs-primary);
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(150, 200, 60, 0.3);
        }
        .project-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            background: #fff;
            border-left: 5px solid #ced4da;
            position: relative;
        }
        .project-card.ongoing { border-left-color: #ffc107; }
        .project-card.planning { border-left-color: #0dcaf0; }
        .project-card.completed { border-left-color: #6c757d; }
        .project-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        .fav-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: transparent;
            border: none;
            color: #cbd5e1;
            font-size: 1.1rem;
            transition: color 0.2s;
            z-index: 5;
        }
        .fav-btn.favorited {
            color: #f59e0b;
        }
        /* Stylish Left Sidebar Panel */
        .sidebar-panel {
            background: linear-gradient(135deg, #ffffff 0%, #fbfcfd 100%);
            border-radius: 14px;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.06);
            padding: 22px;
            border: 1px solid #e2e8f0;
            border-top: 4px solid var(--dbs-primary);
        }
        /* Compact Completed Stack Panel on Right */
        .completed-accordion-btn {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #334155;
            font-weight: 600;
            border-radius: 8px;
            text-align: left;
            padding: 10px 14px;
            width: 100%;
            transition: all 0.2s;
        }
        .completed-accordion-btn:hover {
            background: #e2e8f0;
        }
        .completed-stack-card {
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            border-left: 3px solid #64748b;
            transition: all 0.2s;
            cursor: pointer;
        }
        .completed-stack-card:hover {
            background: #f1f5f9;
            transform: translateX(-2px);
        }
        /* Ultra İnce Arşiv Tablosu Tasarımı */
        .archive-table th {
            padding: 8px 12px;
            font-size: 0.78rem;
            letter-spacing: 0.5px;
        }
        .archive-table td {
            padding: 6px 12px;
            vertical-align: middle;
            font-size: 0.85rem;
        }
        footer {
            margin-top: auto;
            background: #fff;
            border-top: 1px solid #dee2e6;
            padding: 1rem 0;
            text-align: center;
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body>

    <!-- Header Entegrasyonu -->
    <?php include 'AA.00.header.php'; ?>

    <div class="container-fluid py-4 px-4">
        <!-- Sayfa Başlığı ve Yeni Proje Butonu -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1" style="color: #2c3e50;"><i class="fa-solid fa-folder-open me-2" style="color: var(--dbs-primary);"></i>PROJECTS</h2>
                <p class="text-muted mb-0">Manage active company projects, review status filters, and access engineering details.</p>
            </div>
            <div>
                <button class="btn btn-dbs shadow-sm px-4 py-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#newProjectModal">
                    <i class="fa-solid fa-plus me-2"></i>New Project
                </button>
            </div>
        </div>

        <!-- Üst Stylish Filtre Sekmeleri -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="filter-pill active" id="btn-all" onclick="filterProjects('all', this)">
                        <i class="fa-solid fa-layer-group me-2"></i>All Active (<?php echo count($projects); ?>)
                    </button>
                    <button type="button" class="filter-pill" id="btn-ongoing" onclick="filterProjects('Ongoing', this)">
                        <i class="fa-solid fa-spinner me-2 text-warning"></i>Ongoing (<?php echo $ongoing_count; ?>)
                    </button>
                    <button type="button" class="filter-pill" id="btn-planning" onclick="filterProjects('Planning', this)">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-info"></i>Planning (<?php echo $planning_count; ?>)
                    </button>
                    <button type="button" class="filter-pill" id="btn-completed" onclick="filterProjects('Completed', this)">
                        <i class="fa-solid fa-circle-check me-2 text-secondary"></i>Completed (<?php echo $completed_count; ?>)
                    </button>
                    <button type="button" class="filter-pill" id="btn-favorites" onclick="filterProjects('Favorites', this)">
                        <i class="fa-solid fa-star me-2 text-warning"></i>Favorites (<span id="favCount">0</span>)
                    </button>
                </div>
            </div>
        </div>

        <!-- Ana İçerik Grid Yapısı -->
        <div class="row">
            <!-- SOL STYLISH PANEL: Lokasyon Filtresi, Özet Bilgiler ve Hızlı İşlemler -->
            <div class="col-lg-3 mb-4">
                <!-- Lokasyon Filtreleme Kutusu -->
                <div class="sidebar-panel mb-4">
                    <h5 class="fw-bold mb-3" style="color: #2c3e50;"><i class="fa-solid fa-filter me-2" style="color: var(--dbs-primary);"></i>Location Filter</h5>
                    <hr class="text-muted mt-1 mb-3">
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-muted">Select Region / Location</label>
                        <select class="form-select form-select-sm shadow-sm" id="locationFilter" onchange="filterByLocation()">
                            <option value="all">All Locations (Tümü)</option>
                            <?php foreach ($regions_list as $reg): ?>
                                <option value="<?php echo htmlspecialchars($reg); ?>"><?php echo htmlspecialchars($reg); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Portfolio Summary -->
                <div class="sidebar-panel mb-4">
                    <h5 class="fw-bold mb-3" style="color: #2c3e50;"><i class="fa-solid fa-chart-pie me-2" style="color: var(--dbs-primary);"></i>Portfolio Summary</h5>
                    <hr class="text-muted mt-1 mb-3">
                    <div class="mb-3">
                        <span class="text-muted small d-block">Total Projects Recorded</span>
                        <h4 class="fw-bold text-dark mb-0"><?php echo $total_projects_count; ?> Projects</h4>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small d-block">Active / Ongoing Projects</span>
                        <h5 class="fw-semibold text-warning mb-0"><?php echo $ongoing_count; ?> Projects</h5>
                    </div>
                    <div class="mb-3">
                        <span class="text-muted small d-block">Planning Stage</span>
                        <h5 class="fw-semibold text-info mb-0"><?php echo $planning_count; ?> Projects</h5>
                    </div>
                    <div class="mb-0">
                        <span class="text-muted small d-block">Completed Projects</span>
                        <h5 class="fw-semibold text-secondary mb-0"><?php echo $completed_count; ?> Projects</h5>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="sidebar-panel">
                    <h5 class="fw-bold mb-3" style="color: #2c3e50;"><i class="fa-solid fa-bolt me-2" style="color: var(--dbs-primary);"></i>Quick Actions</h5>
                    <hr class="text-muted mt-1 mb-3">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-dark btn-sm text-start py-2" data-bs-toggle="modal" data-bs-target="#newProjectModal">
                            <i class="fa-solid fa-circle-plus me-2" style="color: var(--dbs-primary);"></i> New Project Entry
                        </button>
                        <?php if ($isAdmin): ?>
                        <button class="btn btn-outline-secondary btn-sm text-start py-2" data-bs-toggle="modal" data-bs-target="#deactiveProjectsModal">
                            <i class="fa-solid fa-box-archive me-2 text-warning"></i> List Deactive Projects (<?php echo count($deactive_projects); ?>)
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ORTA ALAN: Proje Kartları -->
            <div class="col-lg-6 mb-4" id="middleGridContainer">
                <div class="row g-3" id="projectGrid">
                    <?php 
                    foreach ($projects as $proj): 
                        $statusClass = strtolower($proj['status']);
                        $projId = $proj['id'];
                    ?>
                        <div class="col-md-12 col-xl-6 project-item" data-status="<?php echo htmlspecialchars($proj['status']); ?>" data-region="<?php echo htmlspecialchars($proj['region']); ?>" data-id="<?php echo $projId; ?>">
                            <div class="card project-card <?php echo $statusClass; ?> h-100 p-3" onclick='openDrawer(<?php echo json_encode($proj); ?>)'>
                                <button type="button" class="fav-btn" onclick="toggleFavorite(event, <?php echo $projId; ?>)" title="Toggle Favorite">
                                    <i class="fa-solid fa-star"></i>
                                </button>

                                <div class="d-flex justify-content-between align-items-start mb-2 pe-4">
                                    <span class="badge bg-light text-dark font-monospace border"><?php echo htmlspecialchars($proj['project_code']); ?></span>
                                    <span class="badge 
                                        <?php 
                                            if(strpos($statusClass, 'ongoing') !== false || strpos($statusClass, 'devam') !== false) echo 'bg-warning text-dark';
                                            else echo 'bg-info text-dark';
                                        ?>">
                                        <?php echo htmlspecialchars($proj['status']); ?>
                                    </span>
                                </div>
                                <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($proj['project_name']); ?></h6>
                                <p class="text-muted small mb-3">
                                    <i class="fa-solid fa-building me-1 text-secondary"></i> <?php echo htmlspecialchars($proj['client']); ?> | 
                                    <i class="fa-solid fa-location-dot me-1 text-secondary"></i> <?php echo htmlspecialchars($proj['region']); ?>
                                </p>
                                <div class="mt-auto d-flex justify-content-between align-items-center pt-2 border-top">
                                    <small class="text-muted"><i class="fa-regular fa-calendar me-1"></i><?php echo htmlspecialchars($proj['start_date']); ?></small>
                                    <a href="PR.02.projectDetails.php?id=<?php echo $proj['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="event.stopPropagation();">Details <i class="fa-solid fa-arrow-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php foreach ($completed_projects as $comp): 
                        $projId = $comp['id'];
                    ?>
                        <div class="col-md-12 col-xl-6 project-item" data-status="<?php echo htmlspecialchars($comp['status']); ?>" data-region="<?php echo htmlspecialchars($comp['region']); ?>" data-id="<?php echo $projId; ?>" style="display: none;">
                            <div class="card project-card completed h-100 p-3" onclick='openDrawer(<?php echo json_encode($comp); ?>)'>
                                <button type="button" class="fav-btn" onclick="toggleFavorite(event, <?php echo $projId; ?>)" title="Toggle Favorite">
                                    <i class="fa-solid fa-star"></i>
                                </button>
                                <div class="d-flex justify-content-between align-items-start mb-2 pe-4">
                                    <span class="badge bg-light text-dark font-monospace border"><?php echo htmlspecialchars($comp['project_code']); ?></span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($comp['status']); ?></span>
                                </div>
                                <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($comp['project_name']); ?></h6>
                                <p class="text-muted small mb-3">
                                    <i class="fa-solid fa-building me-1 text-secondary"></i> <?php echo htmlspecialchars($comp['client']); ?> | 
                                    <i class="fa-solid fa-location-dot me-1 text-secondary"></i> <?php echo htmlspecialchars($comp['region']); ?>
                                </p>
                                <div class="mt-auto d-flex justify-content-between align-items-center pt-2 border-top">
                                    <small class="text-muted"><i class="fa-regular fa-calendar me-1"></i><?php echo htmlspecialchars($comp['start_date']); ?></small>
                                    <a href="PR.02.projectDetails.php?id=<?php echo $comp['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="event.stopPropagation();">Details <i class="fa-solid fa-arrow-right ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SAĞ ALAN: Tamamlanan Projeler Akordeon Paneli -->
            <div class="col-lg-3 mb-4" id="rightCompletedPanel">
                <div class="sidebar-panel">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold mb-0 text-secondary" style="font-size: 1.05rem;"><i class="fa-solid fa-circle-check me-2"></i>Completed Projects</h5>
                        <span class="badge bg-secondary rounded-pill"><?php echo count($completed_projects); ?></span>
                    </div>
                    <p class="text-muted small mb-3" style="font-size: 0.8rem;">Click below to expand archive list.</p>
                    <hr class="text-muted mt-0 mb-3">

                    <?php if(empty($completed_projects)): ?>
                        <p class="text-muted small text-center py-3 mb-0">No completed projects found.</p>
                    <?php else: ?>
                        <button class="completed-accordion-btn mb-2 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#completedCollapse" aria-expanded="false" aria-controls="completedCollapse">
                            <span><i class="fa-solid fa-box-archive me-2 text-muted"></i>View Archive</span>
                            <i class="fa-solid fa-chevron-down small text-muted"></i>
                        </button>

                        <div class="collapse" id="completedCollapse">
                            <div class="d-flex flex-column gap-2 pt-2">
                                <?php foreach ($completed_projects as $comp): ?>
                                    <div class="completed-stack-card p-2 shadow-sm" onclick='openDrawer(<?php echo json_encode($comp); ?>)'>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.7rem;"><?php echo htmlspecialchars($comp['project_code']); ?></span>
                                            <span class="text-muted" style="font-size: 0.7rem;"><i class="fa-regular fa-calendar me-1"></i><?php echo htmlspecialchars($comp['end_date'] ?: $comp['start_date']); ?></span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.9rem;"><?php echo htmlspecialchars($comp['project_name']); ?></h6>
                                        <div class="d-flex justify-content-between align-items-center text-muted" style="font-size: 0.75rem;">
                                            <span><i class="fa-solid fa-building me-1"></i> <?php echo htmlspecialchars($comp['client']); ?></span>
                                            <a href="PR.02.projectDetails.php?id=<?php echo $comp['id']; ?>" class="text-primary fw-bold text-decoration-none" onclick="event.stopPropagation();">Details <i class="fa-solid fa-arrow-right"></i></a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Deactive Projects Modal (Admin Archive Ultra-Thin Table View with Search) -->
    <div class="modal fade" id="deactiveProjectsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
                <div class="modal-header bg-dark text-white px-4 py-3">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-box-archive me-2 text-warning"></i>Deactive Projects Archive</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <!-- Canlı Arama Kutusu -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-search"></i></span>
                                <input type="text" class="form-control" id="deactiveSearchInput" onkeyup="filterDeactiveTable()" placeholder="Search by Project Name or Location...">
                            </div>
                        </div>
                    </div>

                    <?php if (empty($deactive_projects)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fa-solid fa-folder-open fa-2x mb-2 text-secondary"></i>
                            <p class="mb-0">No deactive projects found in archive.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive bg-white rounded-3 shadow-sm border">
                            <table class="table table-hover align-middle mb-0 archive-table" id="deactiveTable">
                                <thead class="table-light text-secondary text-uppercase">
                                    <tr>
                                        <th style="width: 15%;">Code</th>
                                        <th style="width: 32%;">Project Name</th>
                                        <th style="width: 23%;">Client</th>
                                        <th style="width: 20%;">Location (Region)</th>
                                        <th style="width: 10%; text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deactive_projects as $dproj): ?>
                                        <tr class="deactive-row">
                                            <td class="font-monospace fw-semibold text-secondary"><?php echo htmlspecialchars($dproj['project_code']); ?></td>
                                            <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($dproj['project_name']); ?></span></td>
                                            <td class="text-muted"><i class="fa-solid fa-building me-1 text-secondary"></i><?php echo htmlspecialchars($dproj['client']); ?></td>
                                            <td class="text-muted"><i class="fa-solid fa-location-dot me-1 text-secondary"></i><?php echo htmlspecialchars($dproj['region']); ?></td>
                                            <td class="text-end">
                                                <form action="PR.01.projects.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="project_id" value="<?php echo $dproj['id']; ?>">
                                                    <input type="hidden" name="tab" class="current-tab-input">
                                                    <button type="submit" name="reactivate_project" class="btn btn-sm btn-outline-success px-2 py-0 rounded-pill fw-semibold" style="font-size: 0.75rem; line-height: 1.5; white-space: nowrap;">
                                                        <i class="fa-solid fa-rotate-left me-1"></i> Reactivate
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sağ Özet Paneli (Offcanvas) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="projectDrawer" aria-labelledby="projectDrawerLabel" style="width: 450px;">
        <div class="offcanvas-header border-bottom bg-light">
            <div>
                <span class="badge bg-secondary mb-1" id="drawerStatusBadge">Status</span>
                <h5 class="offcanvas-title fw-bold" id="drawerTitle">Project Name</h5>
                <small class="text-muted font-monospace" id="drawerCode">DBS-0000</small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mb-4">
                <h6 class="text-uppercase text-muted fs-7 fw-bold mb-3"><i class="fa-solid fa-circle-info me-2" style="color: var(--dbs-primary);"></i>General Information</h6>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Client:</span>
                        <span class="fw-semibold" id="drawerClient">-</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Region / Location:</span>
                        <span class="fw-semibold" id="drawerRegion">-</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Job Type:</span>
                        <span class="fw-semibold" id="drawerJobType">-</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Contract No:</span>
                        <span class="fw-semibold font-monospace" id="drawerContractNo">-</span>
                    </li>
                </ul>
            </div>

            <div class="mb-4">
                <h6 class="text-uppercase text-muted fs-7 fw-bold mb-3"><i class="fa-solid fa-calendar-days me-2" style="color: var(--dbs-primary);"></i>Project Timeline</h6>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Start Date:</span>
                        <span class="fw-semibold" id="drawerStartDate">-</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">End Date:</span>
                        <span class="fw-semibold" id="drawerEndDate">-</span>
                    </li>
                </ul>
            </div>

            <div class="d-grid gap-2 mt-4">
                <a href="#" id="drawerDetailsBtn" class="btn btn-dbs py-2">
                    <i class="fa-solid fa-diagram-project me-2"></i>Open Full Engineering Details
                </a>
                <button type="button" class="btn btn-outline-secondary py-2" onclick="openEditModal()">
                    <i class="fa-solid fa-pen-to-square me-2"></i>Edit Project Info
                </button>

                <!-- Admin Only: Deactivate Project Button (Triggers Stylish Modal) -->
                <?php if ($isAdmin): ?>
                <button type="button" class="btn btn-outline-danger w-100 py-2 mt-2" onclick="confirmDeactivate()">
                    <i class="fa-solid fa-ban me-2"></i>Deactivate Project
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stylish Deactivate Confirmation Modal -->
    <div class="modal fade" id="deactivateConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-body text-center p-4">
                    <div class="mb-3 text-danger">
                        <i class="fa-solid fa-triangle-exclamation fa-3x"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Deactivate Project?</h5>
                    <p class="text-muted small mb-4">Are you sure you want to deactivate this project? You can restore it from deactive list.</p>
                    <form action="PR.01.projects.php" method="POST">
                        <input type="hidden" name="project_id" id="modal_deactivate_project_id">
                        <input type="hidden" name="tab" class="current-tab-input">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light w-50 py-2 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="deactivate_project" class="btn btn-danger w-50 py-2 fw-semibold">Deactivate</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- New Project Modal -->
    <div class="modal fade" id="newProjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white" style="background-color: var(--dbs-primary);">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-square-plus me-2"></i>Add New Project</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="PR.01.projects.php" method="POST">
                    <input type="hidden" name="tab" class="current-tab-input">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">Project Name</label>
                                <input type="text" class="form-control" name="project_name" required placeholder="e.g. Office Renovation Project">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Client</label>
                                <input type="text" class="form-control" name="client" placeholder="e.g. The Lego Group">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Region / Location</label>
                                <input type="text" class="form-control" name="region" placeholder="e.g. Şişli, İstanbul">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Status</label>
                                <select class="form-select" name="project_status">
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Planning">Planning</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Contract No</label>
                                <input type="text" class="form-control" name="contract_no" placeholder="e.g. CTR-2026-001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Job Type</label>
                                <input type="text" class="form-control" name="job_type" placeholder="e.g. Fit-Out & Renovation">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Start Date</label>
                                <input type="date" class="form-control" name="start_date">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">End Date</label>
                                <input type="date" class="form-control" name="end_date">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_project" class="btn btn-dbs px-4">Save Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Project Modal -->
    <div class="modal fade" id="editProjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white bg-dark">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Project Info</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="PR.01.projects.php" method="POST">
                    <input type="hidden" name="project_id" id="edit_project_id">
                    <input type="hidden" name="tab" class="current-tab-input">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">Project Name</label>
                                <input type="text" class="form-control" name="project_name" id="edit_project_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Client</label>
                                <input type="text" class="form-control" name="client" id="edit_client">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Region / Location</label>
                                <input type="text" class="form-control" name="region" id="edit_region">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Status</label>
                                <select class="form-select" name="project_status" id="edit_project_status">
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Planning">Planning</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Contract No</label>
                                <input type="text" class="form-control" name="contract_no" id="edit_contract_no">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Job Type</label>
                                <input type="text" class="form-control" name="job_type" id="edit_job_type">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Start Date</label>
                                <input type="date" class="form-control" name="start_date" id="edit_start_date">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">End Date</label>
                                <input type="date" class="form-control" name="end_date" id="edit_end_date">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_project" class="btn btn-dbs px-4">Update Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Alt Footer -->
    <footer>
        &copy; 2026 DBS Mimarlık Mühendislik İnşaat Taah. San. ve Tic. A.Ş. All rights reserved.
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let favorites = JSON.parse(localStorage.getItem('dbs_fav_projects')) || [];
        let currentActiveProject = null;
        let currentStatusFilter = 'all';

        document.addEventListener("DOMContentLoaded", function() {
            updateFavUI();
            
            // PHP'den gelen aktif tab bilgisini yakala ve filtreyi uygula
            let urlParams = new URLSearchParams(window.location.search);
            let tabParam = urlParams.get('tab');
            if (tabParam) {
                let btnId = 'btn-' + tabParam.toLowerCase();
                let targetBtn = document.getElementById(btnId);
                if (targetBtn) {
                    filterProjects(tabParam, targetBtn, false);
                } else {
                    filterProjects('all', document.getElementById('btn-all'), false);
                }
            } else {
                filterProjects('all', document.getElementById('btn-all'), false);
            }
        });

        function toggleFavorite(event, projectId) {
            event.stopPropagation();
            let index = favorites.indexOf(projectId);
            if (index > -1) {
                favorites.splice(index, 1);
            } else {
                favorites.push(projectId);
            }
            localStorage.setItem('dbs_fav_projects', JSON.stringify(favorites));
            updateFavUI();
        }

        function updateFavUI() {
            document.getElementById('favCount').innerText = favorites.length;
            document.querySelectorAll('.project-item').forEach(item => {
                let id = parseInt(item.getAttribute('data-id'));
                let btn = item.querySelector('.fav-btn');
                if (favorites.includes(id)) {
                    btn.classList.add('favorited');
                } else {
                    btn.classList.remove('favorited');
                }
            });
        }

        function openDrawer(project) {
            currentActiveProject = project;
            document.getElementById('drawerTitle').innerText = project.project_name || '-';
            document.getElementById('drawerCode').innerText = project.project_code || 'DBS-0000';
            document.getElementById('drawerClient').innerHTML = '<i class="fa-solid fa-building me-1 text-secondary"></i> ' + (project.client || 'No Client');
            
            let badge = document.getElementById('drawerStatusBadge');
            badge.innerText = project.status || 'Ongoing';
            let st = (project.status || '').toLowerCase();
            if (st.includes('completed') || st.includes('biten')) {
                badge.className = 'badge bg-secondary mb-1 px-2 py-1';
            } else if (st.includes('planning')) {
                badge.className = 'badge bg-info text-dark mb-1 px-2 py-1';
            } else {
                badge.className = 'badge bg-warning text-dark mb-1 px-2 py-1';
            }

            document.getElementById('drawerRegion').innerText = project.region || '-';
            document.getElementById('drawerJobType').innerText = project.job_type || '-';
            document.getElementById('drawerContractNo').innerText = project.contract_no || '-';
            document.getElementById('drawerStartDate').innerText = project.start_date || '-';
            document.getElementById('drawerEndDate').innerText = project.end_date || '-';
            
            document.getElementById('drawerDetailsBtn').href = 'PR.02.projectDetails.php?id=' + project.id;
            
            var myOffcanvas = new bootstrap.Offcanvas(document.getElementById('projectDrawer'));
            myOffcanvas.show();
        }

        function confirmDeactivate() {
            if (!currentActiveProject) return;
            
            var drawerEl = document.getElementById('projectDrawer');
            var offcanvas = bootstrap.Offcanvas.getInstance(drawerEl);
            if (offcanvas) offcanvas.hide();

            document.getElementById('modal_deactivate_project_id').value = currentActiveProject.id;
            document.querySelectorAll('.current-tab-input').forEach(el => el.value = currentStatusFilter);

            var confirmModal = new bootstrap.Modal(document.getElementById('deactivateConfirmModal'));
            confirmModal.show();
        }

        function openEditModal() {
            if (!currentActiveProject) return;
            
            var drawerEl = document.getElementById('projectDrawer');
            var offcanvas = bootstrap.Offcanvas.getInstance(drawerEl);
            if (offcanvas) offcanvas.hide();

            document.getElementById('edit_project_id').value = currentActiveProject.id;
            document.getElementById('edit_project_name').value = currentActiveProject.project_name || '';
            document.getElementById('edit_client').value = currentActiveProject.client || '';
            document.getElementById('edit_region').value = currentActiveProject.region || '';
            document.getElementById('edit_project_status').value = currentActiveProject.status || 'Ongoing';
            document.getElementById('edit_contract_no').value = currentActiveProject.contract_no || '';
            document.getElementById('edit_job_type').value = currentActiveProject.job_type || '';
            document.getElementById('edit_start_date').value = currentActiveProject.start_date || '';
            document.getElementById('edit_end_date').value = currentActiveProject.end_date || '';

            document.querySelectorAll('.current-tab-input').forEach(el => el.value = currentStatusFilter);

            var editModal = new bootstrap.Modal(document.getElementById('editProjectModal'));
            editModal.show();
        }

        function filterProjects(status, btnElement, updateHistory = true) {
            currentStatusFilter = status;
            let buttons = document.querySelectorAll('.filter-pill');
            if(btnElement) {
                buttons.forEach(btn => btn.classList.remove('active'));
                btnElement.classList.add('active');
            }

            if (updateHistory) {
                let newUrl = window.location.pathname + '?tab=' + encodeURIComponent(status);
                window.history.replaceState({}, '', newUrl);
            }

            document.querySelectorAll('.current-tab-input').forEach(el => el.value = currentStatusFilter);

            applyFilters();
        }

        function filterByLocation() {
            applyFilters();
        }

        function applyFilters() {
            let selectedLocation = document.getElementById('locationFilter').value;
            let items = document.querySelectorAll('.project-item');

            items.forEach(item => {
                let itemStatus = item.getAttribute('data-status').toLowerCase();
                let itemRegion = item.getAttribute('data-region') || '';
                let itemId = parseInt(item.getAttribute('data-id'));

                let matchLocation = (selectedLocation === 'all' || itemRegion === selectedLocation);

                let matchStatus = false;
                if (currentStatusFilter === 'all') {
                    matchStatus = !(itemStatus.includes('completed') || itemStatus.includes('biten'));
                } else if (currentStatusFilter === 'Favorites') {
                    matchStatus = favorites.includes(itemId);
                } else if (currentStatusFilter === 'Completed') {
                    matchStatus = (itemStatus.includes('completed') || itemStatus.includes('biten'));
                } else {
                    matchStatus = itemStatus.includes(currentStatusFilter.toLowerCase());
                }

                if (matchLocation && matchStatus) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Deactive Projeler Tablosu İçin Canlı Arama (Project Name & Location)
        function filterDeactiveTable() {
            let input = document.getElementById('deactiveSearchInput').value.toLowerCase();
            let rows = document.querySelectorAll('.deactive-row');

            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                if (text.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>