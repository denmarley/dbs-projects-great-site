<?php
// PR.01.projects.php - Projects List, Management & Drawer View
session_start();
$isAdmin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : true; 

include 'GN.01.db.php';

// Yeni Proje Ekleme İşlemi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
    $project_code = 'DBS-' . date('Y') . '-' . rand(1000, 9999);
    
    $project_name = $_POST['project_name'];
    $client       = $_POST['client'];
    $region       = $_POST['region'];
    $status       = $_POST['project_status'];
    $contract_no  = $_POST['contract_no'];
    $job_type     = $_POST['job_type'];
    $start_date   = $_POST['start_date'];
    $end_date     = $_POST['end_date'];

    $stmt = $conn->prepare("INSERT INTO projects (project_code, project_name, client, region, status, contract_no, job_type, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $project_code, $project_name, $client, $region, $status, $contract_no, $job_type, $start_date, $end_date);
    $stmt->execute();
    header("Location: PR.01.projects.php");
    exit();
}

// Projeleri Veritabanından Çekme
$result = $conn->query("SELECT * FROM projects ORDER BY id DESC");
$projects = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects & Contracts | DBS Project Management Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Proje Kartları - Duruma Göre Görünüm */
        .project-tile { 
            transition: all 0.25s ease-in-out; 
            border-left: 5px solid #0d6efd; 
            border-radius: 12px; 
            background: #fff; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.04); 
            cursor: pointer; 
        }
        .project-tile:hover { 
            transform: translateY(-4px); 
            box-shadow: 0 8px 25px rgba(0,0,0,0.1); 
        }
        
        /* Devam Eden Projeler (Parlak/Canlı) */
        .status-ongoing { border-left-color: #198754; background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%); }
        /* Tamamlanan / Biten Projeler (Hafif Soluk) */
        .status-completed { border-left-color: #6c757d; background-color: #f8f9fa; opacity: 0.75; }
        .status-completed:hover { opacity: 1.0; }

        /* Offcanvas / Sağ Panel Şık Tasarım */
        .offcanvas-custom { border-top-left-radius: 20px; border-bottom-left-radius: 20px; box-shadow: -10px 0 30px rgba(0,0,0,0.1); }
        .drawer-header-bg { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; padding: 25px; border-top-left-radius: 20px; }
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; margin-bottom: 12px; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="#"><i class="fa-solid fa-cube text-primary me-2"></i>DBS Project Portal</a>
            <div class="ms-auto">
                <a href="GN.03.home.php" class="btn btn-outline-light btn-sm"><i class="fa-solid fa-home me-1"></i> Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Projects & Contracts</h2>
                <p class="text-muted mb-0">Click any project card to open stylish overview drawer, or go to Engineering Details.</p>
            </div>
            <button class="btn btn-primary shadow-sm px-4 py-2" data-bs-toggle="modal" data-bs-target="#newProjectModal">
                <i class="fa-solid fa-plus me-2"></i>New Project
            </button>
        </div>

        <!-- Proje Kartları Listesi -->
        <div class="row g-4">
            <?php if (empty($projects)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No projects found. Click "New Project" to add one.</p>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $proj): 
                    $isCompleted = (strtolower($proj['status']) === 'completed' || strtolower($proj['status']) === 'biten');
                    $statusClass = $isCompleted ? 'status-completed' : 'status-ongoing';
                ?>
                    <div class="col-md-4">
                        <div class="card project-tile <?= $statusClass ?> p-4 h-100" onclick="openDrawer(<?= htmlspecialchars(json_encode($proj)) ?>)">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-secondary font-monospace"><?= htmlspecialchars($proj['project_code']) ?></span>
                                <span class="badge <?= $isCompleted ? 'bg-secondary' : 'bg-success' ?>">
                                    <?= htmlspecialchars($proj['status']) ?>
                                </span>
                            </div>
                            <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($proj['project_name']) ?></h5>
                            <p class="text-muted small mb-3"><i class="fa-solid fa-building me-1 text-primary"></i> <?= htmlspecialchars($proj['client'] ?? 'No Client') ?> | <i class="fa-solid fa-location-dot me-1 text-danger"></i> <?= htmlspecialchars($proj['region'] ?? 'N/A') ?></p>
                            
                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                <span class="text-muted small"><i class="fa-regular fa-calendar me-1"></i> <?= htmlspecialchars($proj['start_date'] ?? '-') ?></span>
                                <a href="PR.02.projectDetails.php?id=<?= $proj['id'] ?>" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();">
                                    Details <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- STYLISH SAĞ PANEL (OFFCANVAS) -->
    <div class="offcanvas offcanvas-end offcanvas-custom" tabindex="-1" id="projectDrawer" aria-labelledby="projectDrawerLabel" style="width: 480px;">
        <div class="drawer-header-bg position-relative">
            <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            <div class="mb-2">
                <span id="drawerStatusBadge" class="badge bg-success mb-2 px-2 py-1">Ongoing</span>
                <span id="drawerCode" class="badge bg-light text-dark font-monospace ms-1">DBS-0000</span>
            </div>
            <h3 id="drawerTitle" class="fw-bold text-white mb-1">Project Name</h3>
            <p id="drawerClient" class="text-white-50 small mb-0"><i class="fa-solid fa-building me-1"></i> Client Name</p>
        </div>
        
        <div class="offcanvas-body p-4 bg-light">
            <div class="info-card">
                <h6 class="text-uppercase text-muted fw-bold small mb-3"><i class="fa-solid fa-circle-info me-2 text-primary"></i>General Information</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Region / Location:</span>
                    <span id="drawerRegion" class="fw-semibold text-dark">-</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Job Type:</span>
                    <span id="drawerJobType" class="fw-semibold text-dark">-</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Contract No:</span>
                    <span id="drawerContractNo" class="fw-semibold text-dark">-</span>
                </div>
            </div>

            <div class="info-card">
                <h6 class="text-uppercase text-muted fw-bold small mb-3"><i class="fa-solid fa-calendar-days me-2 text-success"></i>Project Timeline</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Start Date:</span>
                    <span id="drawerStartDate" class="fw-semibold text-dark">-</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">End Date:</span>
                    <span id="drawerEndDate" class="fw-semibold text-dark">-</span>
                </div>
            </div>

            <!-- Aksiyon Butonları -->
            <div class="d-grid gap-2 mt-4">
                <a id="drawerDetailsBtn" href="#" class="btn btn-primary py-2 fw-semibold">
                    <i class="fa-solid fa-folder-open me-2"></i> Open Full Engineering Details
                </a>
                <button type="button" class="btn btn-outline-secondary py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#editProjectModal">
                    <i class="fa-solid fa-pen-to-square me-2"></i> Edit Project Info
                </button>
            </div>
        </div>
    </div>

    <!-- Yeni Proje Modal -->
    <div class="modal fade" id="newProjectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle me-2 text-primary"></i>Add New Project</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Project Name</label>
                                <input type="text" name="project_name" class="form-control" required placeholder="e.g. Office Renovation Project">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Client</label>
                                <input type="text" name="client" class="form-control" placeholder="e.g. The Lego Group">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Region / Location</label>
                                <input type="text" name="region" class="form-control" placeholder="e.g. Şişli, İstanbul">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="project_status" class="form-select">
                                    <option value="Ongoing" selected>Ongoing</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Planning">Planning</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contract No</label>
                                <input type="text" name="contract_no" class="form-control" placeholder="e.g. CTR-2026-001">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Job Type</label>
                                <input type="text" name="job_type" class="form-control" placeholder="e.g. Fit-Out & Renovation">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">End Date</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_project" class="btn btn-primary px-4">Save Project</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openDrawer(project) {
            // Başlık ve Üst Kısım Güncelleme
            document.getElementById('drawerTitle').innerText = project.project_name || '-';
            document.getElementById('drawerCode').innerText = project.project_code || 'DBS-0000';
            document.getElementById('drawerClient').innerHTML = '<i class="fa-solid fa-building me-1"></i> ' + (project.client || 'No Client');
            
            // Status Badge Renklendirme
            let badge = document.getElementById('drawerStatusBadge');
            badge.innerText = project.status || 'Ongoing';
            if(project.status === 'Completed' || project.status === 'Biten') {
                badge.className = 'badge bg-secondary mb-2 px-2 py-1';
            } else {
                badge.className = 'badge bg-success mb-2 px-2 py-1';
            }

            // İçerik Detayları
            document.getElementById('drawerRegion').innerText = project.region || '-';
            document.getElementById('drawerJobType').innerText = project.job_type || '-';
            document.getElementById('drawerContractNo').innerText = project.contract_no || '-';
            document.getElementById('drawerStartDate').innerText = project.start_date || '-';
            document.getElementById('drawerEndDate').innerText = project.end_date || '-';

            // Detay Butonu Linkini Güncelle
            document.getElementById('drawerDetailsBtn').href = 'PR.02.projectDetails.php?id=' + project.id;

            // Offcanvas'ı Aç
            var myOffcanvas = new bootstrap.Offcanvas(document.getElementById('projectDrawer'));
            myOffcanvas.show();
        }
    </script>
</body>
</html>