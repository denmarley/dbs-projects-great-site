<?php
include 'GN.01.db.php';
session_start();

// Dosya Yükleme İşlemi (POST)
$upload_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_document'])) {
    $project_id = intval($_POST['project_id']);
    $doc_no = trim($_POST['doc_no']);
    $doc_name = trim($_POST['doc_name']);
    $current_revision = trim($_POST['current_revision']);
    $doc_type = trim($_POST['doc_type']);
    $status = trim($_POST['status']);
    $doc_date = !empty($_POST['doc_date']) ? $_POST['doc_date'] : NULL;

    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['document_file']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['document_file']['name']);
        $upload_dir = 'uploads/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_path = $upload_dir . $file_name;
        if (move_uploaded_file($file_tmp, $file_path)) {
            $stmt = $conn->prepare("INSERT INTO documents (project_id, doc_no, doc_name, current_revision, doc_type, status, doc_date, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssss", $project_id, $doc_no, $doc_name, $current_revision, $doc_type, $status, $doc_date, $file_path);
            if ($stmt->execute()) {
                $upload_msg = "<div class='alert alert-success alert-dismissible fade show' role='alert'>Document uploaded successfully!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            } else {
                $upload_msg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Database error: " . $conn->error . "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            }
        } else {
            $upload_msg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Failed to move uploaded file.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        }
    } else {
        $upload_msg = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Please select a valid file to upload.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}

// Filtreleme Parametreleri
$filter_project = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
$filter_type = isset($_GET['doc_type']) ? trim($_GET['doc_type']) : '';

// Projeleri Listeden Seçebilmek İçin Çekelim
$projects_result = $conn->query("SELECT id, project_name FROM projects ORDER BY project_name ASC");
$projects_list = [];
if ($projects_result) {
    while($p = $projects_result->fetch_assoc()) {
        $projects_list[$p['id']] = $p['project_name'];
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Management - DBS Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --dbs-green: #92C83E;
            --dbs-green-hover: #7eb032;
        }
        body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }
        .main-container {
            flex: 1;
        }
        .portal-header {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .portal-logo {
            background-color: var(--dbs-green);
            color: #111;
            font-weight: bold;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 1.1rem;
        }
        .btn-dbs-green {
            background-color: var(--dbs-green);
            border-color: var(--dbs-green);
            color: #111;
            font-weight: 500;
        }
        .btn-dbs-green:hover {
            background-color: var(--dbs-green-hover);
            border-color: var(--dbs-green-hover);
            color: #111;
        }
        footer {
            text-align: center;
            padding: 20px 0;
            color: #6c757d;
            font-size: 0.85rem;
            border-top: 1px solid #dee2e6;
            background-color: #ffffff;
        }
    </style>
</head>
<body>

    <!-- Üst Başlık Alanı -->
    <div class="portal-header">
        <div class="d-flex align-items-center">
            <span class="portal-logo me-3">dbs</span>
            <h5 class="mb-0 text-dark fw-semibold">Document Management Portal</h5>
        </div>
        <div>
            <a href="GN.03.home.php" class="btn btn-outline-secondary btn-sm me-2"><i class="fas fa-home me-1"></i> Home</a>
            <a href="GN.02.login.php?logout=true" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
        </div>
    </div>

    <!-- Ana İçerik -->
    <div class="container main-container py-4">
        <?= $upload_msg; ?>

        <div class="row mb-4 align-items-center">
            <div class="col-md-5">
                <h3 class="fw-bold text-dark mb-1"><i class="fas fa-folder-open me-2" style="color: var(--dbs-green);"></i> All Project Documents</h3>
                <p class="text-muted small mb-0">Central repository for all project drawings, correspondence, and technical metadata.</p>
            </div>
            <div class="col-md-7 text-end">
                <form method="GET" action="" class="d-inline-flex gap-2 align-items-center">
                    <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="0">-- All Projects --</option>
                        <?php foreach($projects_list as $pid => $pname): ?>
                            <option value="<?= $pid; ?>" <?= ($filter_project == $pid) ? 'selected' : ''; ?>><?= htmlspecialchars($pname); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="doc_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Types --</option>
                        <option value="contract" <?= ($filter_type == 'contract') ? 'selected' : ''; ?>>Contract</option>
                        <option value="drawing" <?= ($filter_type == 'drawing') ? 'selected' : ''; ?>>Drawing</option>
                        <option value="correspondence" <?= ($filter_type == 'correspondence') ? 'selected' : ''; ?>>Correspondence</option>
                        <option value="financial" <?= ($filter_type == 'financial') ? 'selected' : ''; ?>>Financial</option>
                        <option value="report" <?= ($filter_type == 'report') ? 'selected' : ''; ?>>Report</option>
                    </select>
                    <?php if($filter_project > 0 || !empty($filter_type)): ?>
                        <a href="DC.01.documents.php" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </form>
                <button class="btn btn-dbs-green btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fas fa-plus me-1"></i> Upload New Document
                </button>
            </div>
        </div>

        <!-- Doküman Listesi Tablosu -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Project</th>
                                <th>Doc No</th>
                                <th>Doc Name</th>
                                <th>Rev</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT d.*, p.project_name FROM documents d LEFT JOIN projects p ON d.project_id = p.id WHERE 1=1";
                            $params = [];
                            $types = "";

                            if ($filter_project > 0) {
                                $query .= " AND d.project_id = ?";
                                $params[] = $filter_project;
                                $types .= "i";
                            }
                            if (!empty($filter_type)) {
                                $query .= " AND d.doc_type = ?";
                                $params[] = $filter_type;
                                $types .= "s";
                            }

                            $query .= " ORDER BY d.id DESC";
                            
                            if (!empty($params)) {
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param($types, ...$params);
                                $stmt->execute();
                                $result = $stmt->get_result();
                            } else {
                                $result = $conn->query($query);
                            }

                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td class='ps-3'>" . $row['id'] . "</td>";
                                    echo "<td><span class='badge bg-secondary'>" . htmlspecialchars($row['project_name'] ?? 'Unknown Project') . "</span></td>";
                                    echo "<td><strong>" . htmlspecialchars($row['doc_no']) . "</strong></td>";
                                    echo "<td>" . htmlspecialchars($row['doc_name']) . "</td>";
                                    echo "<td><span class='badge bg-info text-dark'>" . htmlspecialchars($row['current_revision']) . "</span></td>";
                                    echo "<td><span class='text-capitalize'>" . htmlspecialchars($row['doc_type']) . "</span></td>";
                                    echo "<td><span class='badge bg-success'>" . htmlspecialchars($row['status']) . "</span></td>";
                                    echo "<td>" . htmlspecialchars($row['doc_date']) . "</td>";
                                    echo "<td class='text-end pe-3'>
                                            <a href='" . htmlspecialchars($row['file_path']) . "' target='_blank' class='btn btn-outline-primary btn-sm' title='Open / View'><i class='fas fa-eye'></i></a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center text-muted py-5'>No documents found matching the selected criteria.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadModalLabel"><i class="fas fa-upload me-2 text-success"></i> Upload New Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Select Project</label>
                            <select name="project_id" class="form-select" required>
                                <option value="">-- Choose Project --</option>
                                <?php foreach($projects_list as $pid => $pname): ?>
                                    <option value="<?= $pid; ?>"><?= htmlspecialchars($pname); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Document Type</label>
                            <select name="doc_type" class="form-select" required>
                                <option value="contract">Contract</option>
                                <option value="drawing">Drawing</option>
                                <option value="correspondence">Correspondence</option>
                                <option value="financial">Financial</option>
                                <option value="report">Report</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Document No</label>
                                <input type="text" name="doc_no" class="form-control" placeholder="e.g. 2026-73" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Revision</label>
                                <input type="text" name="current_revision" class="form-control" value="Rev 0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Document Name / Title</label>
                            <input type="text" name="doc_name" class="form-control" placeholder="e.g. Main Contract Signed" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Active">Active</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Archived">Archived</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Production / Sign Date</label>
                                <input type="date" name="doc_date" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Select File (PDF, Word, Excel, CAD)</label>
                            <input type="file" name="document_file" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="upload_document" class="btn btn-dbs-green btn-sm"><i class="fas fa-save me-1"></i> Save Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Alt Bilgi (Footer) -->
    <footer>
        &copy; 2026 DBS Mimarlık Mühendislik İnşaat Taah. San. ve Tic. A.Ş. All rights reserved.
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>