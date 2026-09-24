<?php
// PR.04.projectdet_contractual.php - Contractual & Agreement Management Module

// Örnek olarak proje için kayıtlı sözleşme detaylarını çekelim (yoksa varsayılan boş dizi)
$contract_query = $conn->prepare("SELECT * FROM project_contracts WHERE project_id = ?");
$contract_query->bind_param("i", $project_id);
$contract_query->execute();
$contract_result = $contract_query->get_result();
$contract_data = $contract_result->fetch_assoc();
?>

<div class="row g-4">
    <div class="col-md-12">
        <div class="card card-custom">
            <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-file-contract me-2 text-primary"></i>Contract Summary & Agreement Documents</span>
                <div>
                    <button class="btn btn-warning btn-sm px-3 shadow-sm text-dark fw-semibold me-2" data-bs-toggle="modal" data-bs-target="#editContractModal">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Contract
                    </button>
                    <button class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadContractModal">
                        <i class="fa-solid fa-upload me-1"></i> Upload New Document
                    </button>
                    <span class="badge bg-secondary ms-2"><?= htmlspecialchars($contract_data['status'] ?? 'Draft') ?></span>
                </div>
            </div>
            <div class="card-body">
                <!-- ERP Tarzı Sözleşme Özet Tablosu -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle small mb-0">
                        <tbody>
                            <tr>
                                <th class="table-light" style="width: 18%;">Asıl İşveren / Client</th>
                                <td style="width: 32%;"><?= htmlspecialchars($contract_data['employer'] ?? $project['client'] ?? '-') ?></td>
                                <th class="table-light" style="width: 18%;">Para Birimi / Currency</th>
                                <td style="width: 32%;"><span class="badge bg-secondary"><?= htmlspecialchars($contract_data['currency'] ?? $project['currency'] ?? 'TL') ?></span></td>
                            </tr>
                            <tr>
                                <th class="table-light">Sözleşme No</th>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($contract_data['contract_no'] ?? $project['contract_no'] ?? '-') ?></td>
                                <th class="table-light">İmza Tarihi</th>
                                <td><?= htmlspecialchars($contract_data['signature_date'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th class="table-light">Resmi Sözleşme No</th>
                                <td><?= htmlspecialchars($contract_data['official_contract_no'] ?? '-') ?></td>
                                <th class="table-light">İş Başlangıç / Süre</th>
                                <td><?= htmlspecialchars($contract_data['start_date'] ?? '-') ?> / <span class="fw-semibold text-dark"><?= htmlspecialchars($contract_data['duration_months'] ?? 0) ?> Ay</span></td>
                            </tr>
                            <tr>
                                <th class="table-light">Sözleşme Nevi / Türü</th>
                                <td><?= htmlspecialchars($contract_data['contract_type'] ?? 'Götürü Bedel') ?></td>
                                <th class="table-light">Ödeme Şekli</th>
                                <td><?= htmlspecialchars($contract_data['payment_terms'] ?? 'Hakediş Usulü') ?></td>
                            </tr>
                            <tr>
                                <th class="table-light">Sözleşme Bedeli (KDV Hariç)</th>
                                <td class="fw-bold text-success"><?= number_format($contract_data['contract_amount'] ?? $project['contract_amount'] ?? 0, 2, ',', '.') ?></td>
                                <th class="table-light">KDV Oranı (%)</th>
                                <td>%<?= htmlspecialchars($contract_data['vat_rate'] ?? 20) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr class="text-muted my-4">

                <!-- Doküman Listesi Tablosu -->
                <p class="text-muted small mb-3">Projeye ait onaylı sözleşmeler, ekler ve doküman arşiv listesi:</p>
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
                                    echo '<a href="#" onclick="openViewer(\'' . $fpath . '\', \'' . htmlspecialchars($doc['doc_name']) . '\', \'' . htmlspecialchars($doc['doc_no']) . '\', \'' . htmlspecialchars($doc['doc_date'] ?? '') . '\'); return false;" class="btn btn-outline-primary btn-sm me-1" title="View"><i class="fa-solid fa-eye"></i></a>';
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

                <!-- Sözleşme Sayaç Özeti -->
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
        </div>
    </div>
</div>