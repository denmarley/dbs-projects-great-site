<?php
// PR.02.projectDetails.php - Fitout & Architectural Project Management Details
session_start();
$isAdmin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : true; 
include 'GN.01.db.php';

$project_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';


// Proje ve Sözleşme Güncelleme İşlemi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_project'])) {
    $project_name   = $_POST['project_name'];
    $client         = $_POST['client'];
    $region         = $_POST['region'];
    $status         = $_POST['project_status'];
    $job_type       = $_POST['job_type'];
    
    // Sözleşme tablosu alanları
    $contract_no     = $_POST['contract_no'];
    $contract_amount = $_POST['contract_amount'];
    $currency        = $_POST['currency'];
    $fx_rate         = $_POST['fx_rate'];
    $start_date      = $_POST['start_date'];
    $end_date        = $_POST['end_date'];

    // 1. Projects tablosunu güncelle (working_area kaldırıldı)
    $stmt = $conn->prepare("UPDATE projects SET project_name=?, client=?, region=?, status=?, job_type=? WHERE id = ?");
    $stmt->bind_param("sssssi", $project_name, $client, $region, $status, $job_type, $project_id);
    $stmt->execute();

    // 2. Contracts tablosunu güncelle veya yoksa oluştur (UPSERT)
    $check_c = $conn->prepare("SELECT id FROM contracts WHERE project_id = ?");
    $check_c->bind_param("i", $project_id);
    $check_c->execute();
    $res_c = $check_c->get_result();

    if ($res_c->num_rows > 0) {
        $c_stmt = $conn->prepare("UPDATE contracts SET contract_no=?, contract_amount=?, currency=?, fx_rate=?, start_date=?, end_date=? WHERE project_id=?");
        $c_stmt->bind_param("sdssdsi", $contract_no, $contract_amount, $currency, $fx_rate, $start_date, $end_date, $project_id);
        $c_stmt->execute();
    } else {
        $c_stmt = $conn->prepare("INSERT INTO contracts (project_id, contract_no, contract_amount, currency, fx_rate, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $c_stmt->bind_param("isdssds", $project_id, $contract_no, $contract_amount, $currency, $fx_rate, $start_date, $end_date);
        $c_stmt->execute();
    }

    header("Location: PR.02.projectDetails.php?id=" . $project_id . "&tab=" . $active_tab);
    exit();
}


// Doküman Silme İşlemi (Admin Only)
if (isset($_GET['delete_doc']) && $isAdmin) {
    $doc_id_to_delete = intval($_GET['delete_doc']);
    $del_stmt = $conn->prepare("DELETE FROM documents WHERE id = ? AND project_id = ?");
    $del_stmt->bind_param("ii", $doc_id_to_delete, $project_id);
    $del_stmt->execute();
    header("Location: PR.02.projectDetails.php?id=" . $project_id . "&tab=contract");
    exit();
}

// Sözleşme / Ek Doküman Yükleme İşlemi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_contract_doc'])) {
    $doc_no          = $_POST['doc_no'];
    $doc_name        = $_POST['doc_name'];
    $doc_type        = $_POST['doc_type']; 
    $production_date = $_POST['doc_date'];
    
    $file_path = '';
    if (isset($_FILES['contract_file']) && $_FILES['contract_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = time() . '_' . basename($_FILES['contract_file']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['contract_file']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        }
    }

    $ins_stmt = $conn->prepare("INSERT INTO documents (project_id, doc_no, doc_name, doc_type, doc_date, file_path, status) VALUES (?, ?, ?, ?, ?, ?, 'Active')");
    $ins_stmt->bind_param("isssss", $project_id, $doc_no, $doc_name, $doc_type, $doc_date, $file_path);
    $ins_stmt->execute();

    header("Location: PR.02.projectDetails.php?id=" . $project_id . "&tab=contract");
    exit();
}

// Proje Verisini Çekme
$stmt = $conn->prepare("SELECT p.*, c.contract_no, c.contract_amount, c.currency, c.fx_rate, c.start_date, c.end_date FROM projects p LEFT JOIN contracts c ON p.id = c.project_id WHERE p.id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();


if (!$project) {
    die("Project not found!");
}

// Sözleşmesel dokümanlar (Raporlar hariç)
$contract_docs_stmt = $conn->prepare("SELECT id, doc_no, doc_name, doc_type, doc_date, file_path FROM documents WHERE project_id = ? AND doc_type NOT LIKE '%Report%'");
$contract_docs_stmt->bind_param("i", $project_id);
$contract_docs_stmt->execute();
$contract_docs_result = $contract_docs_stmt->get_result();
$contract_docs_count = $contract_docs_result->num_rows;

// Raporlar sekmesi için
$reports_docs_stmt = $conn->prepare("SELECT id, doc_no, doc_name, doc_type, doc_date, file_path FROM documents WHERE project_id = ? AND doc_type LIKE '%Report%'");
$reports_docs_stmt->bind_param("i", $project_id);
$reports_docs_stmt->execute();
$reports_docs_result = $reports_docs_stmt->get_result();
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
        
        .archive-sidebar { background: #ffffff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 15px; border-left: 5px solid #0d6efd; max-height: 85vh; overflow-y: auto; }
        .accordion-button { padding: 10px 12px; font-size: 0.88rem; font-weight: 600; color: #2d3748; background-color: #f8f9fa; border-radius: 8px !important; box-shadow: none !important; text-align: left; }
        .accordion-button:not(.collapsed) { background-color: #ebf8ff; color: #2b6cb0; }
        .accordion-button::after { background-size: 0.8rem; }
        .accordion-item { border: none; margin-bottom: 6px; background: transparent; }
        .accordion-body { padding: 6px 0 6px 15px; background: transparent; text-align: left; }
        .archive-sub-item { display: flex; align-items: center; justify-content: space-between; padding: 6px 10px; color: #4a5568; text-decoration: none; font-size: 0.85rem; font-weight: 500; border-radius: 6px; transition: all 0.2s ease; margin-bottom: 2px; text-align: left; }
        .archive-sub-item:hover, .archive-sub-item.active { background-color: #ebf8ff; color: #2b6cb0; padding-left: 14px; font-weight: 600; }
        .archive-sub-item i { width: 20px; color: #718096; }
        .archive-sub-item.active i { color: #2b6cb0; }
        .archive-badge { font-size: 0.65rem; background: #edf2f7; color: #4a5568; padding: 2px 5px; border-radius: 4px; font-weight: 600; }

        .discipline-tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #edf2f7; padding-bottom: 10px; }
        .discipline-tab-btn { background: #f8f9fa; border: 1px solid #cbd5e0; padding: 6px 15px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; color: #4a5568; text-decoration: none; transition: all 0.2s; }
        .discipline-tab-btn:hover, .discipline-tab-btn.active { background: #0d6efd; color: #fff; border-color: #0d6efd; }
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

        <div class="row">
            <!-- Sol Panel -->
            <div class="col-lg-3 mb-4">
                <div class="archive-sidebar">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="text-uppercase text-dark fw-bold m-0 fs-7"><i class="fa-solid fa-folder-tree me-2 text-primary"></i>Project Archive</h6>
                    </div>

                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=overview" class="archive-sub-item mb-2 <?= $active_tab == 'overview' ? 'active' : '' ?>">
                        <span><i class="fa-solid fa-chart-pie me-2"></i> General Overview</span>
                        <?php if ($active_tab == 'overview'): ?><span class="archive-badge bg-primary text-white">Active</span><?php endif; ?>
                    </a>

                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=contract" class="archive-sub-item mb-2 <?= $active_tab == 'contract' ? 'active' : '' ?>">
                        <span><i class="fa-solid fa-file-contract me-2 text-primary"></i> 1. Contractual</span>
                        <?php if ($active_tab == 'contract'): ?><span class="archive-badge bg-primary text-white">Active</span><?php else: ?><span class="archive-badge">D.A.</span><?php endif; ?>
                    </a>

                    <div class="accordion text-start" id="archiveAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingMaster">
                                <button class="accordion-button <?= in_array($active_tab, ['boq', 'quantities', 'workschedule', 'expenses', 'unitprice']) ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMaster">
                                    <i class="fa-solid fa-calendar-days me-2 text-success"></i> 2. Master Plan
                                </button>
                            </h2>
                            <div id="collapseMaster" class="accordion-collapse collapse <?= in_array($active_tab, ['boq', 'quantities', 'workschedule', 'expenses', 'unitprice']) ? 'show' : '' ?>" data-bs-parent="#archiveAccordion">
                                <div class="accordion-body">
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=boq" class="archive-sub-item <?= $active_tab == 'boq' ? 'active' : '' ?>"><span><i class="fa-solid fa-list-check me-2"></i> 2.1. BOQ</span></a>
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=quantities" class="archive-sub-item <?= $active_tab == 'quantities' ? 'active' : '' ?>"><span><i class="fa-solid fa-calculator me-2"></i> 2.2. Quantities</span></a>
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=workschedule" class="archive-sub-item <?= $active_tab == 'workschedule' ? 'active' : '' ?>"><span><i class="fa-solid fa-timeline me-2"></i> 2.3. Workschedule</span></a>
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=expenses" class="archive-sub-item <?= $active_tab == 'expenses' ? 'active' : '' ?>"><span><i class="fa-solid fa-receipt me-2"></i> 2.4. Expenses</span></a>
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=unitprice" class="archive-sub-item <?= $active_tab == 'unitprice' ? 'active' : '' ?>"><span><i class="fa-solid fa-tags me-2"></i> 2.5. Unit Price Analysis</span></a>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingDrawings">
                                <button class="accordion-button <?= str_starts_with($active_tab, 'drawings_') ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDrawings">
                                    <i class="fa-solid fa-pen-ruler me-2 text-success"></i> 3. Drawings (Arch.)
                                </button>
                            </h2>
                            <div id="collapseDrawings" class="accordion-collapse collapse <?= str_starts_with($active_tab, 'drawings_') ? 'show' : '' ?>" data-bs-parent="#archiveAccordion">
                                <div class="accordion-body">
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=drawings_conceptual" class="archive-sub-item <?= $active_tab == 'drawings_conceptual' ? 'active' : '' ?>"><span><i class="fa-solid fa-drafting-compass me-2"></i> 3.1 Conceptual</span> <span class="archive-badge">CAD</span></a>
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=drawings_application" class="archive-sub-item <?= $active_tab == 'drawings_application' ? 'active' : '' ?>"><span><i class="fa-solid fa-object-ungroup me-2"></i> 3.2 Application / Shop</span> <span class="archive-badge">BIM</span></a>
                                    <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=drawings_asbuilt" class="archive-sub-item <?= $active_tab == 'drawings_asbuilt' ? 'active' : '' ?>"><span><i class="fa-solid fa-check-double me-2"></i> 3.3 As-Built</span> <span class="archive-badge">Final</span></a>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingCorr">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCorr">
                                    <i class="fa-solid fa-comments me-2 text-warning"></i> 4. Correspondence
                                </button>
                            </h2>
                            <div id="collapseCorr" class="accordion-collapse collapse" data-bs-parent="#archiveAccordion">
                                <div class="accordion-body">
                                    <a href="#" class="archive-sub-item"><span><i class="fa-solid fa-envelope me-2"></i> 4.1 Letters</span></a>
                                    <a href="#" class="archive-sub-item"><span><i class="fa-solid fa-paper-plane me-2"></i> 4.2 Mails</span></a>
                                    <a href="#" class="archive-sub-item"><span><i class="fa-solid fa-users-rectangle me-2"></i> 4.3 Minutes of Meetings</span></a>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingReports">
                                <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=reports" class="accordion-button <?= $active_tab == 'reports' ? '' : 'collapsed' ?>" style="text-decoration: none;">
                                    <i class="fa-solid fa-clipboard-list me-2 text-info"></i> 5. Reports
                                </a>
                            </h2>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSub">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSub">
                                    <i class="fa-solid fa-users-gear me-2 text-secondary"></i> 6. Subcontractors
                                </button>
                            </h2>
                            <div id="collapseSub" class="accordion-collapse collapse" data-bs-parent="#archiveAccordion">
                                <div class="accordion-body">
                                    <a href="#" class="archive-sub-item"><span><i class="fa-solid fa-boxes-stacked me-2"></i> 6.1 Catalogs / Brochures</span></a>
                                    <a href="#" class="archive-sub-item"><span><i class="fa-solid fa-handshake me-2"></i> 6.2 Proposals & Tenders</span></a>
                                    <a href="#" class="archive-sub-item"><span><i class="fa-solid fa-file-signature me-2"></i> 6.3 Subcontract Contracts</span></a>
                                    <a href="#" class="archive-sub-item"><span><i class="fa-solid fa-file-lines me-2 text-primary"></i> 6.4 Subcontractor Reports</span></a>
                                    <a href="#" class="archive-sub-item"><span><i class="fa-solid fa-receipt me-2"></i> 6.5 Subcontract IPC</span></a>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFinance">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFinance">
                                    <i class="fa-solid fa-wallet me-2 text-success"></i> 7. Financial
                                </button>
                            </h2>
                            <div id="collapseFinance" class="accordion-collapse collapse" data-bs-parent="#archiveAccordion">
                                <div class="accordion-body">
                                    <a href="#" class="archive-sub-item"><span><i class="fa-solid fa-coins me-2"></i> Income</span></a>
                                    <a href="#" class="archive-sub-item"><span><i class="fa-solid fa-file-invoice-dollar me-2"></i> Account</span></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sağ İçerik Alanı -->
            <div class="col-lg-9">                    
                <?php if ($active_tab == 'overview'): ?>
                    <?php include 'PR.03.projectdet_overview.php'; ?>

                <?php elseif ($active_tab == 'contract'): ?>
                    <!-- 1. MAIN CONTRACT (Tam Boy Sağ Görüntüleyici ve Özet Sayaç) -->
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class="card card-custom">
                                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                                    <span><i class="fa-solid fa-file-contract me-2 text-primary"></i>Main Contract & Agreement Documents</span>
                                    <div>
                                        <button class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadContractModal">
                                            <i class="fa-solid fa-upload me-1"></i> Upload New Document
                                        </button>
                                        <span class="badge bg-secondary ms-2">Primary Agreement</span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Sol Taraf: Tablo -->
                                        <div class="col-lg-12" id="docTableColumn">
                                            <p class="text-muted small mb-3">Projeye ait sözleşmeler ve sözleşmesel ekler. İncelemek için göz ikonuna tıklayın.</p>
                                            
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 5%;">#</th>
                                                            <th style="width: 25%;">Doc No</th>
                                                            <th style="width: 45%;">Title</th>
                                                            <th style="width: 25%;" class="text-end">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                        $contract_docs_result->data_seek(0);
                                                        if ($contract_docs_result && $contract_docs_result->num_rows > 0) {
                                                            $counter = 1;
                                                            while ($doc = $contract_docs_result->fetch_assoc()) {
                                                                $fpath = !empty($doc['file_path']) ? htmlspecialchars($doc['file_path']) : '#';
                                                                echo '<tr>';
                                                                echo '<td class="fw-bold text-muted">' . $counter++ . '</td>';
                                                                echo '<td><span class="badge bg-light text-dark border">' . htmlspecialchars($doc['doc_no']) . '</span></td>';
                                                                echo '<td class="fw-semibold text-dark">' . htmlspecialchars($doc['doc_name']) . '</td>';
                                                                echo '<td class="text-end">';
                                                                echo '<a href="#" onclick="openViewer(\'' . $fpath . '\', \'' . htmlspecialchars($doc['doc_name']) . '\', \'' . htmlspecialchars($doc['doc_no']) . '\', \'' . htmlspecialchars($doc['doc_date']) . '\'); return false;" class="btn btn-outline-primary btn-sm me-1" title="View"><i class="fa-solid fa-eye"></i></a>';
                                                                echo '<a href="#" onclick="openEditModal(' . $doc['id'] . ', \'' . htmlspecialchars($doc['doc_no']) . '\', \'' . htmlspecialchars($doc['doc_name']) . '\'); return false;" class="btn btn-outline-warning btn-sm me-1 text-dark" title="Edit"><i class="fa-solid fa-pen"></i></a>';
                                                                if ($isAdmin) {
                                                                    echo '<button type="button" class="btn btn-outline-danger btn-sm" onclick="openDeleteModal(' . $doc['id'] . ');" title="Delete"><i class="fa-solid fa-trash"></i></button>';
                                                                }
                                                                echo '</td>';
                                                                echo '</tr>';
                                                            }
                                                        } else {
                                                            echo '<tr><td colspan="4" class="text-center text-muted py-3">No contract documents uploaded yet.</td></tr>';
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Contractual Works Özet Sayacı -->
                                            <div class="mt-4 p-3 bg-light border rounded d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="fw-bold text-dark"><i class="fa-solid fa-folder-open me-2 text-primary"></i>Contractual Works / Documents Archive Summary</span>
                                                    <p class="text-muted small mb-0">Toplam kayıtlı sözleşme ve ek doküman sayısı</p>
                                                </div>
                                                <div>
                                                    <span class="badge bg-primary fs-6 px-3 py-2"><?= $contract_docs_count ?> Documents</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sağ Taraf: Ekranın Üstünden Altına Kadar Tam Boy Görüntüleyici -->
                                        <div class="col-lg-7 border-start d-none" id="docViewerColumn">
                                            <div class="h-100 d-flex flex-column">
                                                <div class="d-flex justify-content-between align-items-center mb-2 bg-dark text-white p-2 rounded">
                                                    <h6 class="fw-bold m-0"><i class="fa-solid fa-file-pdf text-danger me-2"></i>Document Viewer & Contract Metadata</h6>
                                                    <div>
                                                        <span id="previewDocBadge" class="badge bg-light text-dark me-2">Doc</span>
                                                        <button class="btn btn-sm btn-outline-light py-0 px-2" onclick="closeViewer()"><i class="fa-solid fa-xmark"></i></button>
                                                    </div>
                                                </div>
                                                <div id="docPreviewContainer" class="border rounded bg-light p-2 flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center" style="min-height: 75vh;">
                                                    <!-- Dinamik içerik -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    function openViewer(filePath, docName, docNo, docDate) {
                        const tableCol = document.getElementById('docTableColumn');
                        const viewerCol = document.getElementById('docViewerColumn');
                        
                        tableCol.className = "col-lg-5";
                        viewerCol.classList.remove('d-none');

                        const container = document.getElementById('docPreviewContainer');
                        const badge = document.getElementById('previewDocBadge');
                        badge.textContent = docNo;
                        
                        if(filePath && filePath !== '#') {
                            container.innerHTML = `
                                <div class="w-100 text-start mb-2 px-1">
                                    <span class="fw-bold text-dark">${docName}</span> <span class="text-muted small ms-2">(Tarih: ${docDate})</span>
                                </div>
                                <iframe src="${filePath}" style="width:100%; height:70vh; border:1px solid #cbd5e0;" class="rounded bg-white shadow-sm"></iframe>
                                <div class="mt-2 text-end w-100">
                                    <a href="${filePath}" target="_blank" class="btn btn-sm btn-dark"><i class="fa-solid fa-external-link-alt me-1"></i> Open in Fullscreen Window</a>
                                </div>`;
                        } else {
                            container.innerHTML = `
                                <i class="fa-solid fa-triangle-exclamation fa-2x text-warning mb-2"></i>
                                <h6 class="fw-bold text-dark">${docName}</h6>
                                <p class="text-muted small">Fiziksel dosya yolu bulunamadı veya henüz sunucuya yüklenmemiş.</p>`;
                        }
                    }

                    function closeViewer() {
                        const tableCol = document.getElementById('docTableColumn');
                        const viewerCol = document.getElementById('docViewerColumn');
                        
                        viewerCol.classList.add('d-none');
                        tableCol.className = "col-lg-12";
                    }

                    function openEditModal(docId, docNo, docName) {
                        document.getElementById('editDocId').value = docId;
                        document.getElementById('editDocNo').value = docNo;
                        document.getElementById('editDocName').value = docName;
                        new bootstrap.Modal(document.getElementById('editDocumentModal')).show();
                    }

                    function openDeleteModal(docId) {
                        document.getElementById('confirmDeleteBtn').href = "PR.02.projectDetails.php?id=<?= $project_id ?>&tab=contract&delete_doc=" + docId;
                        new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
                    }
                    </script>

                <?php elseif ($active_tab == 'reports'): ?>
                    <!-- ================= 5. REPORTS SEKMESİ ================= -->
                    <div class="card card-custom">
                        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-clipboard-list me-2 text-info"></i>Project Reports Archive</span>
                            <span class="badge bg-info text-dark">Reports Section</span>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">Bu projeye ait yüklenen günlük, haftalık ve aylık raporlar aşağıda listelenmektedir.</p>
                            
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Doc No</th>
                                            <th>Report Title</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if ($reports_docs_result && $reports_docs_result->num_rows > 0) {$counter = 1;
                                            while ($rep = $reports_docs_result->fetch_assoc()) {$fpath = !empty($rep['file_path']) ? htmlspecialchars($rep['file_path']) : '#';
                                                echo '<tr>';
                                                echo '<td class="fw-bold text-muted">' . $counter++ . '</td>';
                                                echo '<td><span class="badge bg-light text-dark border">' . htmlspecialchars($rep['doc_no']) . '</span></td>';
                                                echo '<td class="fw-semibold text-dark">' . htmlspecialchars($rep['doc_name']) . '</td>';
                                                echo '<td><span class="badge bg-info text-dark">' . htmlspecialchars($rep['doc_type']) . '</span></td>';
                                                echo '<td class="text-muted small">' . htmlspecialchars($rep['doc_date'] ?? '-') . '</td>';
                                                echo '<td class="text-end">';
                                                echo '<a href="' . $fpath . '" target="_blank" class="btn btn-outline-primary btn-sm me-1" title="View"><i class="fa-solid fa-eye"></i></a>';
                                                if ($isAdmin) {
                                                    echo '<button type="button" class="btn btn-outline-danger btn-sm" onclick="openDeleteModal(' . $rep['id'] . ');" title="Delete"><i class="fa-solid fa-trash"></i></button>';
                                                }
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="6" class="text-center text-muted py-4">Henüz yüklenmiş bir rapor bulunmuyor. Raporları "Upload New Document" üzerinden ekleyebilirsiniz.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php elseif (str_starts_with($active_tab, 'drawings_')): ?>
                    <div class="card card-custom">
                        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-pen-ruler me-2 text-success"></i>Drawings Archive &raquo; <?= ucfirst(str_replace('drawings_', '', $active_tab)) ?></span>
                            <span class="badge bg-success">Active Section</span>
                        </div>
                        <div class="card-body">
                            <div class="discipline-tabs">
                                <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=drawings_conceptual" class="discipline-tab-btn <?= $active_tab == 'drawings_conceptual' ? 'active' : '' ?>"><i class="fa-solid fa-drafting-compass me-1"></i> 3.1 Conceptual</a>
                                <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=drawings_application" class="discipline-tab-btn <?= $active_tab == 'drawings_application' ? 'active' : '' ?>"><i class="fa-solid fa-object-ungroup me-1"></i> 3.2 Application / Shop</a>
                                <a href="PR.02.projectDetails.php?id=<?= $project_id ?>&tab=drawings_asbuilt" class="discipline-tab-btn <?= $active_tab == 'drawings_asbuilt' ? 'active' : '' ?>"><i class="fa-solid fa-check-double me-1"></i> 3.3 As-Built</a>
                            </div>

                            <div class="py-4 text-center">
                                <i class="fa-solid fa-compass-drafting fa-3x text-muted mb-3"></i>
                                <h4 class="fw-bold text-dark"><?= strtoupper(str_replace('drawings_', '', $active_tab)) ?> Proje Çizimleri</h4>
                                <p class="text-muted">Bu disipline ait CAD ve BIM çizimleri, revizyon takip tabloları burada listelenmektedir.</p>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="card card-custom">
                        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                            <span><i class="fa-solid fa-folder-tree me-2 text-success"></i>Project Module &raquo; <?= strtoupper($active_tab) ?></span>
                            <span class="badge bg-success">Active Section</span>
                        </div>
                        <div class="card-body py-5 text-center">
                            <i class="fa-solid fa-bars-progress fa-3x text-muted mb-3"></i>
                            <h4 class="fw-bold text-dark"><?= strtoupper($active_tab) ?> Modülü Aktif</h4>
                            <p class="text-muted">Seçtiğiniz <strong><?= htmlspecialchars($active_tab) ?></strong> sekmesine ait içerik ve yönetim araçları bu alanda sunulmaktadır.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Stylish Silme Onay Modal Penceresi -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-triangle-exclamation me-2"></i>Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fa-solid fa-trash-can fa-3x text-danger mb-3"></i>
                    <h5 class="fw-bold text-dark">Bu dokümanı silmek istediğinize emin misiniz?</h5>
                    <p class="text-muted small mb-0">Bu işlem geri alınamaz ve dosyaya ait tüm kayıtlar silinir.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">İptal</button>
                    <a id="confirmDeleteBtn" href="#" class="btn btn-danger btn-sm px-4">Evet, Sil</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stylish Düzenleme (Edit) Modal Penceresi -->
    <div class="modal fade" id="editDocumentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Document Parameters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <input type="hidden" name="edit_document" value="1">
                        <input type="hidden" id="editDocId" name="doc_id">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Document No</label>
                            <input type="text" class="form-control" id="editDocNo" name="doc_no" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Document Title / Name</label>
                            <input type="text" class="form-control" id="editDocName" name="doc_name" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning btn-sm px-4 fw-semibold">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sözleşme Yükleme Modal Penceresi -->
    <div class="modal fade" id="uploadContractModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-upload me-2"></i>Upload Contractual Document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="upload_contract_doc" value="1">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Document Type / Category</label>
                            <select class="form-select" name="doc_type" required>
                                <option value="Contract">Main Contract</option>
                                <option value="Contractual Documents">Contractual Documents & Appendices</option>
                                <option value="BOQ">Bill of Quantities (BOQ)</option>
                                <option value="NDA">Non-Disclosure Agreement (NDA)</option>
                                <option value="Daily Report">Daily Report (Rapor)</option>
                                <option value="Weekly Report">Weekly Report (Rapor)</option>
                                <option value="Monthly Report">Monthly Report (Rapor)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Document No</label>
                            <input type="text" class="form-control" name="doc_no" placeholder="Örn: DBS-2026-001" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Document Title / Name</label>
                            <input type="text" class="form-control" name="doc_name" placeholder="Örn: Günlük Rapor" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Document Date</label>
                            <input type="date" class="form-control" name="doc_date" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select File (PDF, Word, Excel)</label>
                            <input type="file" class="form-control" name="contract_file" required>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm px-4">Upload Document</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Project Modal -->
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
                                    <option value="Suspended" <?= $project['status'] == 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Client / Employer</label>
                                <input type="text" class="form-control" name="client" value="<?= htmlspecialchars($project['client'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Region / Location</label>
                                <input type="text" class="form-control" name="region" value="<?= htmlspecialchars($project['region'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Working Area (Net Area)</label>
                                <input type="text" class="form-control" name="working_area" value="<?= htmlspecialchars($project['working_area'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">  
                                <label class="form-label fw-semibold">Job Type</label>
                                <input type="text" class="form-control" name="job_type" value="<?= htmlspecialchars($project['job_type'] ?? '') ?>">
                            </div>
                        </div>

                        <h6 class="text-success fw-bold mb-3"><i class="fa-solid fa-wallet me-1"></i> Contract & Financial Details</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contract No</label>
                                <input type="text" class="form-control" name="contract_no" value="<?= htmlspecialchars($project['contract_no'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Contract Amount</label>
                                <input type="number" step="0.01" class="form-control" name="contract_amount" value="<?= htmlspecialchars($project['contract_amount'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Currency</label>
                                <select class="form-select" name="currency">
                                    <option value="EUR" <?= ($project['currency'] ?? '') == 'EUR' ? 'selected' : '' ?>>EUR</option>
                                    <option value="USD" <?= ($project['currency'] ?? '') == 'USD' ? 'selected' : '' ?>>USD</option>
                                    <option value="TL" <?= ($project['currency'] ?? '') == 'TL' ? 'selected' : '' ?>>TL</option>
                                    <option value="GBP" <?= ($project['currency'] ?? '') == 'GBP' ? 'selected' : '' ?>>GBP</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">FX Rate</label>
                                <input type="number" step="0.0001" class="form-control" name="fx_rate" value="<?= htmlspecialchars($project['fx_rate'] ?? '1.0000') ?>">
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

                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="update_project" class="btn btn-primary px-4">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>