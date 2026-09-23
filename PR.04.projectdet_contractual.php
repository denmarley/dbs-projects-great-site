<?php
// Önce bu projeye ait sözleşme detaylarını çekelim
$contract_query = $conn->prepare("SELECT * FROM contracts WHERE project_id = ?");
$contract_query->bind_param("i", $project_id);
$contract_query->execute();
$contract_result = $contract_query->get_result();
$contract = $contract_result->fetch_assoc();
?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white fw-bold">
        <i class="fas fa-file-contract me-2"></i> Sözleşme ve Mali Detaylar (Contractual Overview)
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label text-muted">İşveren (Employer)</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($contract['employer'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted">Sözleşme No</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($contract['contract_no'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted">Resmi Sözleşme No</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($contract['official_contract_no'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted">Sözleşme Bedeli & Para Birimi</label>
                <div class="input-group">
                    <input type="text" class="form-control" value="<?= number_format($contract['contract_amount'] ?? 0, 2, ',', '.') ?>" readonly>
                    <span class="input-group-text"><?= htmlspecialchars($contract['currency'] ?? 'TL') ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted">Avans Tutarı</label>
                <input type="text" class="form-control" value="<?= number_format($contract['advance_payment'] ?? 0, 2, ',', '.') ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted">Kesin Teminat / Performans Bonosu</label>
                <input type="text" class="form-control" value="<?= number_format($contract['performance_bond'] ?? 0, 2, ',', '.') ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted">İmza Tarihi</label>
                <input type="date" class="form-control" value="<?= htmlspecialchars($contract['signature_date'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted">İş Başlangıç Tarihi</label>
                <input type="date" class="form-control" value="<?= htmlspecialchars($contract['start_date'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted">İş Süresi (Ay)</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($contract['duration_months'] ?? 0) ?> Ay" readonly>
            </div>
            <div class="col-md-12">
                <label class="form-label text-muted">Ödeme Koşulları (Payment Terms)</label>
                <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($contract['payment_terms'] ?? '') ?></textarea>
            </div>
        </div>
    </div>
</div>