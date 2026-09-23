
<?php
//PR.03.projectdet_overview.php - General Overview Sub-Module
?>
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
