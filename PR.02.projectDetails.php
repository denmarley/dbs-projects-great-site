<?php
// PR.02.projectDetails.php - Fitout & Architectural Project Management Details
session_start();
$isAdmin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : true; 
include 'GN.01.db.php';

$project_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Proje Güncelleme İşlemi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_project'])) {
    $project_name        = $_POST['project_name'];
    $client              = $_POST['client'];
    $region              = $_POST['region'];
    $status              = $_POST['project_status'];
    $contract_no         = $_POST['contract_no'];
    $job_type            = $_POST['job_type'];
    $start_date          = $_POST['start_date'];
    $end_date            = $_POST['end_date'];
    $contract_amount     = $_POST['contract_amount'];
    $currency            = $_POST['currency'];
    $fx_rate             = $_POST['fx_rate'];
    $working_area        = $_POST['working_area'];

    $stmt = $conn->prepare("UPDATE projects SET project_name=?, client=?, region=?, status=?, contract_no=?, job_type=?, start_date=?, end_date=?, contract_amount=?, currency=?, fx_rate=?, working_area=? WHERE id=?");
    $stmt->bind_param("ssssssssdsssi", $project_name, $client, $region, $status, $contract_no, $job_type, $start_date, $end_date, $contract_amount, $currency, $fx_rate, $working_area, $project_id);
    $stmt->execute();
    
    header("Location: PR.02.projectDetails.php?id=" . $project_id . "&tab=" . $active_tab);
    exit();
}

// Proje Verisini Veritabanından Çekme
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

if (!$project) {
    die("Project not found!");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($project['project_name']) ?> | Fitout & Professional Archive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 24px; }
        .card-header-custom { background: #fff; border-bottom: 1px solid #edf2f7; font-weight: 700; color: #2d3748; padding: 15px 20px; border-radius: 12px 12px 0 0 !important; }
        
        /* Compact Accordion Sidebar Styles */
        .archive-sidebar { background: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 15px; border-left: 5px solid #0d6efd; max-height: 85vh; overflow-y: auto; }
        .accordion-button { padding: 10px 12px; font-size: 0.88rem; font-weight: 600; color: #2d3748; background-color: #f8f9fa; border-radius: 8px !important; box-shadow: none !important; }
        .accordion-button:not(.collapsed) { background-color: #ebf8ff; color: #2b6cb0; }
        .accordion-button::after { background-size: 0.8rem; }
        .accordion-item { border: none; margin-bottom: 6px; background: transparent; }
        .accordion-body { padding: 6px 0 6px 15px; background: transparent; }
        .archive-sub-item { display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; color: #4a5568; text-decoration: none; font-size: 0.85rem; font-weight: 500; border-radius: 6px; transition: all 0.2s ease; margin-bottom: 2px; }
        .archive-sub-item:hover, .archive-sub-item.active { background-color: #ebf8ff; color: #2b6cb0; padding-left: 14px; font-weight: 600; }
        .archive-sub-item i { width: 20px; color: #718096; }
        .archive-sub-item.active i { color: #2b6cb0; }
        .archive-badge { font-size: 0.65rem; background: #edf2f7; color: #4a5568; padding: 2px 5px; border-radius: 4px; font-weight: 600; }

        /* Drag & Drop Zone Styles */
        .drop-zone { border: 2px dashed #cbd5e0; border-radius: 10px; padding: 30px; text-align: center; background: #fdfdfe; transition: all 0.2s ease; cursor: pointer; }
        .drop-zone:hover, .drop-zone.dragover { border-color: #0d6efd; background: #ebf8ff; }
        .file-item { display: flex; align-items: center; justify-content: space-between; background: #fff; border: 1px solid #edf2f7; border-radius: 8px; padding: 10px 15px; margin-bottom: 8px; transition: all 0.2s; }
        .file-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.05); border-color: #cbd5e0; }
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
                <a class="nav-link text-warning fw-semibold" href="PR.01.projects.php">Projects</a>
                <?php if ($isAdmin): ?>
                    <a class="nav-link text-info fw-semibold" href="GN.04.usersPage.php">Users</a>
                <?php endif; ?>
                <a class="nav-link text-danger" href="GN.02.login.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4 px-4">
        <!-- Üst Başlık ve Geri Dönüş -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="PR.01.projects.php" class="btn btn-outline-secondary btn-sm mb-2"><i class="fa-solid fa-arrow-left me-1"></i> Back to Projects</a>
                <h2 class="fw-bold text-dark mb-0"><?= htmlspecialchars($project['project_name']) ?></h2>
                <span class="text-muted small"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($project['region'] ?? '-') ?> | Client: <?= htmlspecialchars($project['client'] ?? '-') ?></span>
            </div>
            <div>
                <button class="btn btn-warning fw-semibold px-3 py-2 text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#editProjectModal">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Project Parameters
                </button>
            </div>
        </div>

        <!-- Ana Düzen: Sol Panel ve Sağ İçerik Alanı -->
        <div class="row">
            
            <!-- SOL PANEL: Kompakt Akordeon Arşiv Menüsü -->
            <div class="col-lg-3 mb-4">
                <div class="archive-sidebar">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="text-uppercase text-dark fw-bold m-0 fs-7"><i class="fa-solid fa-folder-tree me-2 text-primary"></i>Project Archive</h6>
                    </div>

                    <!-- General Overview Linki -->
                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=overview" class="archive-sub-item mb-2 <?= $active_tab == 'overview' ? 'active' : '' ?>">
                        <span><i class="fa-solid fa-chart-pie me-2"></i> General Overview</span>
                        <?php if ($active_tab == 'overview'): ?><span class="archive-badge bg-primary text-white">Active</span><?php endif; ?>
                    </a>

                    <!-- 1. Main Contract (Bağımsız Satır) -->
                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=contract" class="archive-sub-item mb-2 <?= $active_tab == 'contract' ? 'active' : '' ?>">
                        <span><i class="fa-solid fa-file-contract me-2 text-primary"></i> 1. Main Contract</span>
                        <?php if ($active_tab == 'contract'): ?><span class="archive-badge bg-primary text-white">Active</span><?php else: ?><span class="archive-badge">PDF</span><?php endif; ?>
                    </a>

                    <!-- Akordeon Grupları -->
                    <div class="accordion" id="archiveAccordion">
                        
                        <!-- 2. Master Plan & Details (Akordeon) -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingMaster">
                                <button class="accordion-button <?= in_array($active_tab, ['boq', 'quantities', 'workschedule', 'expenses', 'unitprice']) ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMaster">
                                    <i class="fa-solid fa-calendar-days me-2 text-success"></i> 2. Master Plan
                                </button>
                            </h2>
                            <div id="collapseMaster" class="accordion-collapse collapse <?= in_array($active_tab, ['boq', 'quantities', 'workschedule', 'expenses', 'unitprice']) ? 'show' : '' ?>" data-bs-parent="#archiveAccordion">
                                <div class="accordion-body">
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=boq" class="archive-sub-item <?= $active_tab == 'boq' ? 'active' : '' ?>">
                                        <span><i class="fa-solid fa-list-check me-2"></i> 2.1. BOQ</span>
                                    </a>
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=quantities" class="archive-sub-item <?= $active_tab == 'quantities' ? 'active' : '' ?>">
                                        <span><i class="fa-solid fa-calculator me-2"></i> 2.2. Quantities</span>
                                    </a>
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=workschedule" class="archive-sub-item <?= $active_tab == 'workschedule' ? 'active' : '' ?>">
                                        <span><i class="fa-solid fa-timeline me-2"></i> 2.3. Workschedule</span>
                                    </a>
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=expenses" class="archive-sub-item <?= $active_tab == 'expenses' ? 'active' : '' ?>">
                                        <span><i class="fa-solid fa-receipt me-2"></i> 2.4. Expenses</span>
                                    </a>
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=unitprice" class="archive-sub-item <?= $active_tab == 'unitprice' ? 'active' : '' ?>">
                                        <span><i class="fa-solid fa-tags me-2"></i> 2.5. Unit Price Analysis</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Drawings -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingDrawings">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDrawings">
                                    <i class="fa-solid fa-pen-ruler me-2 text-success"></i> 3. Drawings (Arch.)
                                </button>
                            </h2>
                            <div id="collapseDrawings" class="accordion-collapse collapse" data-bs-parent="#archiveAccordion">
                                <div class="accordion-body">
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-drafting-compass me-2"></i> 3.1 Conceptual <span class="archive-badge">CAD</span></a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-object-ungroup me-2"></i> 3.2 Application / Shop <span class="archive-badge">BIM</span></a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-check-double me-2"></i> 3.3 As-Built <span class="archive-badge">Final</span></a>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Correspondence -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingCorr">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCorr">
                                    <i class="fa-solid fa-comments me-2 text-warning"></i> 4. Correspondence
                                </button>
                            </h2>
                            <div id="collapseCorr" class="accordion-collapse collapse" data-bs-parent="#archiveAccordion">
                                <div class="accordion-body">
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-inbox me-2"></i> 4.1 Incoming (Client)</a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-paper-plane me-2"></i> 4.2 Outgoing (Client)</a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-users-rectangle me-2"></i> 4.4 MOM (Minutes)</a>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Financials & Income -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFinance">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFinance">
                                    <i class="fa-solid fa-wallet me-2 text-success"></i> 5. Financials & Income
                                </button>
                            </h2>
                            <div id="collapseFinance" class="accordion-collapse collapse" data-bs-parent="#archiveAccordion">
                                <div class="accordion-body">
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-file-invoice-dollar me-2"></i> 5.1 Payment Cert (IPC)</a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-coins me-2"></i> 5.2 Variation Orders <span class="archive-badge">VO</span></a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-scale-balanced me-2"></i> 5.3 Claims & FX Gains</a>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Reports -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingReports">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReports">
                                    <i class="fa-solid fa-clipboard-list me-2 text-info"></i> 6. Reports
                                </button>
                            </h2>
                            <div id="collapseReports" class="accordion-collapse collapse" data-bs-parent="#archiveAccordion">
                                <div class="accordion-body">
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-calendar-day me-2"></i> 6.1 Daily Reports</a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-calendar-week me-2"></i> 6.2 Weekly Reports</a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-file-lines me-2"></i> 6.3 Monthly Reports</a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-building me-2"></i> 6.5 Arch. Design Rep.</a>
                                </div>
                            </div>
                        </div>

                        <!-- 7. Subcontractors -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSub">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSub">
                                    <i class="fa-solid fa-users-gear me-2 text-secondary"></i> 7. Subcontractors
                                </button>
                            </h2>
                            <div id="collapseSub" class="accordion-collapse collapse" data-bs-parent="#archiveAccordion">
                                <div class="accordion-body">
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-boxes-stacked me-2"></i> 7.1 Catalogs / Brochures</a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-handshake me-2"></i> 7.2 Proposals & Tenders</a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-file-signature me-2"></i> 7.3 Subcontract Contracts</a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-receipt me-2"></i> 7.5 Subcontract IPC</a>
                                </div>
                            </div>
                        </div>

                        <!-- 8. Admin, Financial & Warehouse -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingAdmin">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdmin">
                                    <i class="fa-solid fa-calculator me-2 text-dark"></i> 8-11. Admin, HSE & QC
                                </button>
                            </h2>
                            <div id="collapseAdmin" class="accordion-collapse collapse" data-bs-parent="#archiveAccordion">
                                <div class="accordion-body">
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-calculator me-2"></i> 8.1 Accounting & Invoices</a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-warehouse me-2"></i> 8.2 Warehouse & Stock</a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-helmet-safety me-2"></i> 10. HSE Plans & Audits</a>
                                    <a href="#" class="archive-sub-item"><i class="fa-solid fa-award me-2"></i> 11. QA/QC & Material App.</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Sağ Taraf: Sekmeye Göre Değişen İçerik Alanı -->
            <div class="col-lg-9">
                <?php if ($active_tab == 'overview'): ?>
                    <!-- ================= GENERAL OVERVIEW SEKMESİ ================= -->
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card card-custom h-100">
                                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-info-circle me-2 text-primary"></i>General Overview</span>
                                    <span class="badge bg-success"><?= htmlspecialchars($project['status']) ?></span>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between px-0"><strong>Project Name:</strong> <span class="text-muted"><?= htmlspecialchars($project['project_name']) ?></span></li>
                                        <li class="list-group-item d-flex justify-content-between px-0"><strong>Client:</strong> <span class="text-muted"><?= htmlspecialchars($project['client'] ?? '-') ?></span></li>
                                        <li class="list-group-item d-flex justify-content-between px-0"><strong>Location / Region:</strong> <span class="text-muted"><?= htmlspecialchars($project['region'] ?? '-') ?></span></li>
                                        <li class="list-group-item d-flex justify-content-between px-0"><strong>Working Area (Net Area):</strong> <span class="fw-bold text-primary"><?= htmlspecialchars($project['working_area'] ?? '720 m²') ?></span></li>
                                        <li class="list-group-item d-flex justify-content-between px-0"><strong>Job Type:</strong> <span class="text-muted"><?= htmlspecialchars($project['job_type'] ?? '-') ?></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-custom h-100">
                                <div class="card-header card-header-custom">
                                    <i class="fa-solid fa-wallet me-2 text-success"></i>Financial & Multi-Currency Status
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between px-0"><strong>Contract Amount:</strong> <span class="fw-bold text-success"><?= number_format($project['contract_amount'] ?? 0, 2, ',', '.') ?> <?= htmlspecialchars($project['currency'] ?? 'TL') ?></span></li>
                                        <li class="list-group-item d-flex justify-content-between px-0"><strong>Currency:</strong> <span class="badge bg-secondary"><?= htmlspecialchars($project['currency'] ?? 'TL') ?></span></li>
                                        <li class="list-group-item d-flex justify-content-between px-0"><strong>Exchange Rate (FX Rate):</strong> <span class="fw-semibold text-dark"><?= number_format($project['fx_rate'] ?? 1.0000, 4, ',', '.') ?></span></li>
                                        <li class="list-group-item d-flex justify-content-between px-0"><strong>Contract No:</strong> <span class="text-muted"><?= htmlspecialchars($project['contract_no'] ?? '-') ?></span></li>
                                        <li class="list-group-item d-flex justify-content-between px-0"><strong>Duration / Dates:</strong> <span class="text-muted"><?= htmlspecialchars($project['start_date'] ?? '-') ?> / <?= htmlspecialchars($project['end_date'] ?? '-') ?></span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($active_tab == 'contract'): ?>
                    <!-- ================= 1. MAIN CONTRACT SEKMESİ ================= -->
                    <div class="row g-4">
                        <!-- Sözleşme Bilgileri Tablosu -->
                        <div class="col-md-12">
                            <div class="card card-custom">
                                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-file-contract me-2 text-primary"></i>Main Contract & Agreement Details</span>
                                    <span class="badge bg-primary">Primary Agreement</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between px-0"><strong>Contract No:</strong> <span class="text-muted"><?= htmlspecialchars($project['contract_no'] ?? '2026-73') ?></span></li>
                                                <li class="list-group-item d-flex justify-content-between px-0"><strong>Employer (Client):</strong> <span class="text-muted"><?= htmlspecialchars($project['client'] ?? '-') ?></span></li>
                                                <li class="list-group-item d-flex justify-content-between px-0"><strong>Contractor:</strong> <span class="fw-semibold text-dark">DBS Mimarlık Mühendislik A.Ş.</span></li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item d-flex justify-content-between px-0"><strong>Contract Value:</strong> <span class="fw-bold text-success"><?= number_format($project['contract_amount'] ?? 0, 2, ',', '.') ?> <?= htmlspecialchars($project['currency'] ?? 'EUR') ?></span></li>
                                                <li class="list-group-item d-flex justify-content-between px-0"><strong>Sign Date / Duration:</strong> <span class="text-muted"><?= htmlspecialchars($project['start_date'] ?? '-') ?></span></li>
                                                <li class="list-group-item d-flex justify-content-between px-0"><strong>Governing Framework:</strong> <span class="text-muted">Main Contract Conditions & Appendices</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- İlgili Dosyalar ve Sürükle-Bırak Alanı -->
                        <div class="col-md-12">
                            <div class="card card-custom">
                                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-folder-open me-2 text-warning"></i>Contract Files & Appendices (PDF, Word, Excel)</span>
                                    <span class="badge bg-secondary" id="fileCountBadge">0 Files</span>
                                </div>
                                <div class="card-body">
                                    <!-- Sürükle Bırak Alanı -->
                                    <div class="drop-zone mb-3" id="dropZone">
                                        <i class="fa-solid fa-cloud-arrow-up fa-3x text-primary mb-2"></i>
                                        <h5 class="fw-bold text-dark">Drag & Drop Contract Files Here</h5>
                                        <p class="text-muted small mb-2">Support for Signed PDF, Excel (BoQ), and Word agreements</p>
                                        <input type="file" id="fileInput" multiple style="display: none;">
                                        <button class="btn btn-outline-primary btn-sm px-3" onclick="document.getElementById('fileInput').click()">Browse Files</button>
                                    </div>

                                    <!-- Yüklenen Dosyaların Listeleneceği Alan -->
                                    <div id="fileListContainer">
                                        <div class="file-item d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-3">
                                                <i class="fa-solid fa-file-pdf fa-2x text-danger"></i>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold text-dark">Main_Contract_Signed_2026.pdf</h6>
                                                    <small class="text-muted">Signed Agreement & General Conditions (2.4 MB)</small>
                                                </div>
                                            </div>
                                            <a href="#" class="btn btn-outline-primary btn-sm" onclick="alert('Demo: Signed contract PDF preview/download.'); return false;"><i class="fa-solid fa-eye me-1"></i> Open / View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- ================= MASTER PLAN ALT SEKMELERİ (BOQ, Quantities vb.) ================= -->
                    <div class="card card-custom">
                        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-folder-tree me-2 text-success"></i>Master Plan &raquo; <?= ucfirst($active_tab) ?></span>
                            <span class="badge bg-success">Active Section</span>
                        </div>
                        <div class="card-body py-5 text-center">
                            <i class="fa-solid fa-hammer fa-3x text-muted mb-3"></i>
                            <h4 class="fw-bold text-dark"><?= strtoupper($active_tab) ?> modülü hazırlanıyor...</h4>
                            <p class="text-muted">Bu bölümde proje metrajları, keşif listeleri ve iş programı detayları yönetilecektir.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editProjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Project Parameters</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <input type="hidden" name="update_project" value="1">
                        
                        <h6 class="text-primary fw-bold mb-3"><i class="fa-solid fa-info-circle me-1"></i> General & Fitout Overview</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Project Name</label>
                                <input type="text" class="form-control" name="project_name" value="<?= htmlspecialchars($project['project_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Project Status</label>
                                <select class="form-select" name="project_status">
                                    <option value="Planning" <?= $project['status'] == 'Planning' ? 'selected' : '' ?>>Planning</option>
                                    <option value="Ongoing" <?= $project['status'] == 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                    <option value="Completed" <?= $project['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="On Hold / Suspended" <?= $project['status'] == 'On Hold / Suspended' ? 'selected' : '' ?>>On Hold / Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Client</label>
                                <input type="text" class="form-control" name="client" value="<?= htmlspecialchars($project['client'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Region / Location</label>
                                <input type="text" class="form-control" name="region" value="<?= htmlspecialchars($project['region'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Working Area (Net Area - e.g. 720 m²)</label>
                                <input type="text" class="form-control" name="working_area" value="<?= htmlspecialchars($project['working_area'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Job Type</label>
                                <input type="text" class="form-control" name="job_type" value="<?= htmlspecialchars($project['job_type'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Contract No</label>
                                <input type="text" class="form-control" name="contract_no" value="<?= htmlspecialchars($project['contract_no'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($project['start_date'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">End Date</label>
                                <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($project['end_date'] ?? '') ?>">
                            </div>
                        </div>

                        <h6 class="text-success fw-bold mb-3"><i class="fa-solid fa-wallet me-1"></i> Financials (Currency & FX Rate)</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contract Amount</label>
                                <input type="number" step="0.01" class="form-control" name="contract_amount" value="<?= htmlspecialchars($project['contract_amount'] ?? 0) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Currency</label>
                                <select class="form-select" name="currency">
                                    <option value="TL" <?= ($project['currency'] ?? 'TL') == 'TL' ? 'selected' : '' ?>>TL</option>
                                    <option value="USD" <?= ($project['currency'] ?? '') == 'USD' ? 'selected' : '' ?>>USD</option>
                                    <option value="EUR" <?= ($project['currency'] ?? '') == 'EUR' ? 'selected' : '' ?>>EUR</option>
                                    <option value="GBP" <?= ($project['currency'] ?? '') == 'GBP' ? 'selected' : '' ?>>GBP</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">FX Rate</label>
                                <input type="number" step="0.0001" class="form-control" name="fx_rate" value="<?= htmlspecialchars($project['fx_rate'] ?? 1.0000) ?>">
                            </div>
                        </div>

                        <div class="modal-footer mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Drag & Drop JavaScript -->
    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const fileListContainer = document.getElementById('fileListContainer');

        if (dropZone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropZone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('dragover');
                }, false);
            });

            dropZone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            });

            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });
        }

        function handleFiles(files) {
            ([...files]).forEach(file => {
                let iconClass = 'fa-file text-secondary';
                let badgeText = 'FILE';
                if (file.name.endsWith('.pdf')) { iconClass = 'fa-file-pdf text-danger'; badgeText = 'PDF'; }
                else if (file.name.endsWith('.xlsx') || file.name.endsWith('.xls')) { iconClass = 'fa-file-excel text-success'; badgeText = 'EXCEL'; }
                else if (file.name.endsWith('.docx') || file.name.endsWith('.doc')) { iconClass = 'fa-file-word text-primary'; badgeText = 'WORD'; }

                const fileHtml = `
                    <div class="file-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid ${iconClass} fa-2x"></i>
                            <div>
                                <h6 class="mb-0 fw-semibold text-dark">${file.name}</h6>
                                <small class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB • Uploaded just now</small>
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border">${badgeText}</span>
                    </div>
                `;
                fileListContainer.insertAdjacentHTML('beforeend', fileHtml);
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>