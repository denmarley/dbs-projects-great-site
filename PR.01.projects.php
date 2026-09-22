<?php
// PR.01.projects.php - Projects List, Management & Drawer View
session_start();
$isAdmin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : true; 

include 'GN.01.db.php';

// Yeni Proje Ekleme İşlemi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_project'])) {
    $project_name = $_POST['project_name'];
    $client       = $_POST['client'];
    $region       = $_POST['region'];
    $status       = $_POST['project_status'];
    $contract_no  = $_POST['contract_no'];
    $job_type     = $_POST['job_type'];
    $start_date   = $_POST['start_date'];
    $end_date     = $_POST['end_date'];

    $stmt = $conn->prepare("INSERT INTO projects (project_name, client, region, status, contract_no, job_type, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $project_name, $client, $region, $status, $contract_no, $job_type, $start_date, $end_date);
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
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .project-tile { transition: all 0.2s ease-in-out; border-left: 5px solid #0d6efd; border-radius: 10px; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.03); cursor: pointer; }
        .project-tile:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.08); background-color: #fafbfc; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="GN.03.home.php">
                <span class="badge bg-primary px-2 py-1">DBS</span> Project Management Portal
            </a>
            <div class="navbar-nav ms-auto gap-3 align-items-center">
                <a class="nav-link" href="GN.03.home.php">Home / Dashboard</a>
                <a class="nav-link active text-warning fw-semibold" href="PR.01.projects.php">Projects</a>
                <?php if ($isAdmin): ?>
                    <a class="nav-link text-info fw-semibold" href="GN.04.usersPage.php">Users</a>
                <?php endif; ?>
                <a class="nav-link text-danger" href="GN.02.login.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Projects & Contracts</h2>
                <p class="text-muted mb-0">Click any project card for a quick drawer overview, or go to Project Details for engineering disciplines.</p>
            </div>
            <?php if ($isAdmin): ?>
            <button class="btn btn-success px-4 py-2 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#newProjectModal">
                <i class="fa-solid fa-plus me-2"></i>New Project
            </button>
            <?php endif; ?>
        </div>

        <div class="row g-4">
            <?php if (empty($projects)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center py-4">No projects found in the database. Click <strong>New Project</strong> to add one.</div>
                </div>
            <?php else: ?>
                <?php foreach($projects as $p): ?>
                <div class="col-md-4">
                    <!-- Kartın kendisine tıklanınca Drawer açılır -->
                    <div class="card project-tile p-4 h-100 d-flex flex-column justify-content-between" 
                         onclick="openProjectDrawer(
                            '<?= htmlspecialchars($p['project_name'], ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($p['client'] ?? '-', ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($p['region'] ?? '-', ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($p['status'] ?? 'Planning', ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($p['contract_no'] ?? '-', ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($p['job_type'] ?? '-', ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($p['start_date'] ?? '-', ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($p['end_date'] ?? '-', ENT_QUOTES) ?>',
                            '<?= $p['id'] ?>'
                         )">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['contract_no'] ?? 'N/A') ?></span>
                                <?php 
                                    $st = $p['status'] ?? 'Planning';
                                    $badgeClass = 'bg-primary';
                                    if($st == 'Ongoing') $badgeClass = 'bg-success';
                                    elseif($st == 'Completed') $badgeClass = 'bg-info text-dark';
                                    elseif($st == 'On Hold / Suspended') $badgeClass = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $badgeClass ?> bg-opacity-75"><?= htmlspecialchars($st) ?></span>
                            </div>
                            <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($p['project_name']) ?></h4>
                            <p class="text-muted small mb-3">
                                <i class="fa-solid fa-building me-1"></i><?= htmlspecialchars($p['client'] ?? '-') ?> &nbsp;|&nbsp; 
                                <i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($p['region'] ?? '-') ?>
                            </p>
                        </div>
                        <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center" onclick="event.stopPropagation()">
                            <span class="text-muted small"><i class="fa-solid fa-coins me-1"></i>Multi-Currency</span>
                            <!-- Detay sayfasına geçiş butonu (Jeolojik, Yapısal vb. disiplinler için) -->
                            <a href="PR.02.projectDetails.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm fw-semibold px-3">
                                Project Details <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right Drawer (Offcanvas) for Quick Summary -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="projectDrawer" aria-labelledby="projectDrawerLabel">
        <div class="offcanvas-header bg-dark text-white">
            <h5 class="offcanvas-title" id="projectDrawerLabel"><i class="fa-solid fa-info-circle me-2"></i>Project Quick Overview</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <h3 id="drawer-title" class="fw-bold text-primary mb-3"></h3>
            <hr>
            <p class="mb-2"><strong>Client:</strong> <span id="drawer-client" class="text-muted"></span></p>
            <p class="mb-2"><strong>Region / Location:</strong> <span id="drawer-region" class="text-muted"></span></p>
            <p class="mb-2"><strong>Status:</strong> <span id="drawer-status" class="badge bg-success"></span></p>
            <p class="mb-2"><strong>Contract No:</strong> <span id="drawer-contract" class="text-muted"></span></p>
            <p class="mb-2"><strong>Job Type:</strong> <span id="drawer-job" class="text-muted"></span></p>
            <p class="mb-2"><strong>Start Date:</strong> <span id="drawer-start" class="text-muted"></span></p>
            <p class="mb-2"><strong>End Date:</strong> <span id="drawer-end" class="text-muted"></span></p>
            
            <div class="mt-4 pt-4 border-top">
                <a id="drawer-details-btn" href="#" class="btn btn-primary w-100 fw-semibold py-2">
                    <i class="fa-solid fa-folder-tree me-2"></i>Go to Full Engineering & Financial Details
                </a>
            </div>
        </div>
    </div>

    <!-- New Project Modal -->
    <div class="modal fade" id="newProjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-plus-circle me-2"></i>Create New Project</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <input type="hidden" name="add_project" value="1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Project Name</label>
                                <input type="text" class="form-control" name="project_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Client</label>
                                <input type="text" class="form-control" name="client">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Region / Location</label>
                                <input type="text" class="form-control" name="region">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Project Status</label>
                                <select class="form-select" name="project_status">
                                    <option value="Planning">Planning</option>
                                    <option value="Ongoing">Ongoing</option>
                                    <option value="Completed">Completed</option>
                                    <option value="On Hold / Suspended">On Hold / Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contract No</label>
                                <input type="text" class="form-control" name="contract_no">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Job Type</label>
                                <input type="text" class="form-control" name="job_type" placeholder="e.g. Turnkey / Retrofitting">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="date" class="form-control" name="start_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">End Date (Deadline)</label>
                                <input type="date" class="form-control" name="end_date">
                            </div>
                        </div>
                        <div class="modal-footer mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success px-4">Save Project</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openProjectDrawer(name, client, region, status, contract, job, start, end, id) {
            document.getElementById('drawer-title').innerText = name;
            document.getElementById('drawer-client').innerText = client;
            document.getElementById('drawer-region').innerText = region;
            document.getElementById('drawer-status').innerText = status;
            document.getElementById('drawer-contract').innerText = contract;
            document.getElementById('drawer-job').innerText = job;
            document.getElementById('drawer-start').innerText = start;
            document.getElementById('drawer-end').innerText = end;
            document.getElementById('drawer-details-btn').href = 'PR.02.projectDetails.php?id=' + id;

            var myOffcanvas = new bootstrap.Offcanvas(document.getElementById('projectDrawer'));
            myOffcanvas.show();
        }
    </script>
</body>
</html>