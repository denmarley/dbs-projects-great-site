<?php
// GN.04.usersPage.php - System Users Management Panel (Admin Only)
session_start();
require_once 'GN.01.db.php';

// 1. Yetki Kontrolü: Giriş yapılmış mı ve rol Admin mi?
if (!isset($_SESSION['giris_yapildi']) || $_SESSION['role'] !== 'Admin') {
    header("Location: GN.02.login.php");
    exit;
}

$mesaj = "";
$hata = "";

// 2. Yeni Kullanıcı Ekleme İşlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $profession = trim($_POST['profession'] ?? '');
    $hire_date = !empty($_POST['hire_date']) ? $_POST['hire_date'] : null;
    $title = trim($_POST['title'] ?? '');
    $role = trim($_POST['role'] ?? 'Engineering');

    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($password)) {
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $hata = "This email address is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, profession, hire_date, title, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $insert_stmt->bind_param("sssssssss", $first_name, $last_name, $email, $phone, $hashed_password, $profession, $hire_date, $title, $role);
            
            if ($insert_stmt->execute()) {
                $mesaj = "New user successfully created.";
            } else {
                $hata = "An error occurred while adding the user.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $hata = "Please fill in all required fields.";
    }
}

// 3. Kullanıcı Güncelleme (Edit) İşlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $edit_id = intval($_POST['edit_id']);
    $first_name = trim($_POST['edit_first_name'] ?? '');
    $last_name = trim($_POST['edit_last_name'] ?? '');
    $email = trim($_POST['edit_email'] ?? '');
    $phone = trim($_POST['edit_phone'] ?? '');
    $profession = trim($_POST['edit_profession'] ?? '');
    $title = trim($_POST['edit_title'] ?? '');
    $hire_date = !empty($_POST['edit_hire_date']) ? $_POST['edit_hire_date'] : null;
    $role = trim($_POST['edit_role'] ?? 'Engineering');

    if (!empty($first_name) && !empty($last_name) && !empty($email)) {
        $update_stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, profession = ?, title = ?, hire_date = ?, role = ? WHERE id = ?");
        $update_stmt->bind_param("ssssssssi", $first_name, $last_name, $email, $phone, $profession, $title, $hire_date, $role, $edit_id);
        
        if ($update_stmt->execute()) {
            $mesaj = "User details successfully updated.";
        } else {
            $hata = "An error occurred while updating the user.";
        }
        $update_stmt->close();
    } else {
        $hata = "Name, surname and email fields cannot be empty.";
    }
}

// 4. Soft Delete / Status Değiştirme İşlemi
if (isset($_GET['toggle_status_id'])) {
    $target_id = intval($_GET['toggle_status_id']);
    if ($target_id != $_SESSION['user_id']) {
        $status_stmt = $conn->prepare("UPDATE users SET status = 1 - status WHERE id = ?");
        $status_stmt->bind_param("i", $target_id);
        $status_stmt->execute();
        $status_stmt->close();
        header("Location: GN.04.usersPage.php" . (isset($_GET['show_deactivated']) ? '?show_deactivated=1' : ''));
        exit;
    } else {
        $hata = "You cannot deactivate your own active admin account.";
    }
}

// 5. Filtreleme ve Listeleme
$show_deactivated = isset($_GET['show_deactivated']) ? 1 : 0;
if ($show_deactivated) {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, profession, hire_date, title, role, status, created_at, last_login FROM users ORDER BY id DESC");
} else {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, profession, hire_date, title, role, status, created_at, last_login FROM users WHERE status = 1 ORDER BY id DESC");
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Users | DBS Architecture Portal</title>
    <style>
        :root {
            --dbs-green: #8FC93E;
            --dbs-green-hover: #7fb433;
            --dbs-dark: #1e293b;
            --bg-color: #f8fafc;
            --text-color: #334155;
            --border-color: #e2e8f0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: filter 0.3s ease;
        }
        body.drawer-open, body.modal-open {
            overflow: hidden;
        }
        header {
            background: #ffffff;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }
        .logo-box {
            background-color: var(--dbs-green);
            color: #fff;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 16px;
            text-decoration: none;
        }
        .nav-links a {
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s;
        }
        .nav-links a:hover {
            color: var(--dbs-dark);
        }
        .container {
            max-width: 1150px;
            margin: 40px auto;
            padding: 0 20px;
        }
        body.drawer-open .container, body.drawer-open header,
        body.modal-open .container, body.modal-open header {
            filter: blur(4px);
            pointer-events: none;
            user-select: none;
        }
        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--dbs-dark);
            margin-bottom: 24px;
        }
        .card {
            background: #ffffff;
            padding: 28px;
            border-radius: 14px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        .card h3 {
            margin-top: 0;
            color: var(--dbs-dark);
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: 600;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
            margin-bottom: 20px;
        }
        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .input-group label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }
        input, select {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            outline: none;
            background-color: #f8fafc;
            color: var(--dbs-dark);
            transition: all 0.2s;
        }
        input:focus, select:focus {
            border-color: var(--dbs-green);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(143, 201, 62, 0.15);
        }
        .btn {
            background-color: var(--dbs-green);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }
        .btn:hover {
            background-color: var(--dbs-green-hover);
        }
        .btn-danger {
            background-color: #dc2626;
        }
        .btn-danger:hover {
            background-color: #b91c1c;
        }
        .btn-secondary {
            background-color: #64748b;
        }
        .btn-secondary:hover {
            background-color: #475569;
        }
        .filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .filter-bar h3 {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .checkbox-label {
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .checkbox-label input {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            min-width: 700px;
        }
        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
            white-space: nowrap;
        }
        th {
            background-color: #f1f5f9;
            color: var(--dbs-dark);
            font-weight: 600;
        }
        tr.clickable-row {
            cursor: pointer;
            transition: background-color 0.15s;
        }
        tr.clickable-row:hover {
            background-color: #f1f5f9;
        }
        tr.deactivated-row {
            background-color: #f8fafc;
            color: #94a3b8;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-Admin { background-color: #fee2e2; color: #991b1b; }
        .badge-Procurement { background-color: #fef3c7; color: #92400e; }
        .badge-Engineering { background-color: #dbeafe; color: #1e40af; }
        .badge-Site { background-color: #dcfce7; color: #166534; }
        .badge-status-active { background-color: #dcfce7; color: #166534; }
        .badge-status-passive { background-color: #fee2e2; color: #b91c1c; }
        
        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #bbf7d0;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 10px rgba(22, 101, 52, 0.05);
        }
        .alert-error {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 10px rgba(220, 38, 38, 0.05);
        }
        .action-link {
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            padding: 6px 12px;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .action-link.deactivate { color: #dc2626; background: #fee2e2; }
        .action-link.deactivate:hover { background: #fecaca; }
        .action-link.activate { color: #166534; background: #dcfce7; }
        .action-link.activate:hover { background: #bbf7d0; }

        /* YAN PANEL (DRAWER) - ESKİ DÜZEN, RENKLİ BAŞLIK */
        .drawer-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(6px);
            display: none;
            z-index: 1000;
            justify-content: flex-end;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .drawer-overlay.open {
            opacity: 1;
            display: flex;
        }
        .drawer {
            width: 440px;
            max-width: 100%;
            height: 100%;
            background: #ffffff;
            box-shadow: -15px 0 40px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .drawer-overlay.open .drawer {
            transform: translateX(0);
        }
        .drawer-header {
            padding: 24px 28px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .drawer-header-content {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .drawer-avatar {
            width: 46px;
            height: 46px;
            background: var(--dbs-green);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(143, 201, 62, 0.3);
        }
        .drawer-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        .drawer-header p {
            margin: 2px 0 0 0;
            font-size: 12px;
            color: #94a3b8;
        }
        .drawer-close {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .drawer-close:hover { background: rgba(255, 255, 255, 0.2); }
        
        .drawer-body {
            padding: 24px 28px;
            overflow-y: auto;
            flex: 1;
            background: #ffffff;
        }
        
        /* ESKİ AKICI LİSTE DÜZENİ (DÜZ TEXT) */
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label-old {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
        }
        .detail-value-old {
            font-size: 14px;
            color: var(--dbs-dark);
            font-weight: 600;
            text-align: right;
        }

        .drawer-footer {
            padding: 18px 28px;
            border-top: 1px solid var(--border-color);
            background: #ffffff;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        /* MODERN ONAY MODALI */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(5px);
            display: none;
            z-index: 2000;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .modal-backdrop.open {
            opacity: 1;
            display: flex;
        }
        .modal-box {
            background: #ffffff;
            width: 400px;
            max-width: 90%;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            text-align: center;
            transform: scale(0.95);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .modal-backdrop.open .modal-box {
            transform: scale(1);
        }
        .modal-icon {
            width: 56px;
            height: 56px;
            background: #fef2f2;
            color: #dc2626;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 18px auto;
        }
        .modal-box h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
            color: var(--dbs-dark);
        }
        .modal-box p {
            margin: 0 0 24px 0;
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
        }
        .modal-actions {
            display: flex;
            gap: 12px;
        }
        .modal-actions button {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
        }
        .btn-cancel {
            background: #f1f5f9;
            color: #475569;
        }
        .btn-cancel:hover { background: #e2e8f0; }
    </style>
</head>
<body>

    <header>
        <a href="GN.03.home.php" class="logo-box">dbs</a>
        <div class="nav-links">
            <a href="GN.03.home.php">&larr; Back to Dashboard</a>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">System Users Management</h1>

        <?php if (!empty($mesaj)): ?>
            <div class="alert-success">
                <span>&#10003;</span> <?php echo htmlspecialchars($mesaj); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($hata)): ?>
            <div class="alert-error">
                <span>&#9888;</span> <?php echo htmlspecialchars($hata); ?>
            </div>
        <?php endif; ?>

        <!-- Yeni Kullanıcı Ekleme Formu -->
        <div class="card">
            <h3>Add New Portal User</h3>
            <form action="" method="POST" autocomplete="off">
                <input type="text" style="display:none" name="fakeusernameremembered">
                <input type="password" style="display:none" name="fakepasswordremembered">

                <div class="form-grid">
                    <div class="input-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" placeholder="Enter first name" required autocomplete="off">
                    </div>
                    <div class="input-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" placeholder="Enter last name" required autocomplete="off">
                    </div>
                    <div class="input-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="name@dbsprotor.com" required autocomplete="off">
                    </div>
                    <div class="input-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="0532..." autocomplete="off">
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="••••••••" required autocomplete="new-password">
                    </div>
                    <div class="input-group">
                        <label>Profession</label>
                        <input type="text" name="profession" placeholder="e.g. Civil Engineer">
                    </div>
                    <div class="input-group">
                        <label>Title</label>
                        <input type="text" name="title" placeholder="e.g. Project Manager">
                    </div>
                    <div class="input-group">
                        <label>Hire Date</label>
                        <input type="date" name="hire_date">
                    </div>
                    <div class="input-group">
                        <label>System Role</label>
                        <select name="role">
                            <option value="Engineering">Engineering</option>
                            <option value="Site">Site</option>
                            <option value="Procurement">Procurement</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="add_user" class="btn">Create User Account</button>
            </form>
        </div>

        <!-- Kullanıcı Listesi -->
        <div class="card">
            <div class="filter-bar">
                <h3>Authorized Portal Users</h3>
                <form method="GET" id="filterForm">
                    <label class="checkbox-label">
                        <input type="checkbox" name="show_deactivated" value="1" <?php echo $show_deactivated ? 'checked' : ''; ?> onchange="document.getElementById('filterForm').submit();">
                        Show Deactivated Users
                    </label>
                </form>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name Surname</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        while ($row = $result->fetch_assoc()): 
                            $is_passive = ($row['status'] == 0);
                        ?>
                            <tr class="clickable-row <?php echo $is_passive ? 'deactivated-row' : ''; ?>" 
                                onclick="openDrawer(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)">
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $row['role']; ?>">
                                        <?php echo $row['role']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo !$is_passive ? 'badge-status-active' : 'badge-status-passive'; ?>">
                                        <?php echo !$is_passive ? 'Active' : 'Deactivated'; ?>
                                    </span>
                                </td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td onclick="event.stopPropagation();">
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <a href="#" class="action-link <?php echo !$is_passive ? 'deactivate' : 'activate'; ?>" 
                                           onclick="confirmStatusChange('GN.04.usersPage.php?toggle_status_id=<?php echo $row['id']; ?><?php echo $show_deactivated ? '&show_deactivated=1' : ''; ?>', '<?php echo !$is_passive ? 'deactivate' : 'activate'; ?>'); return false;">
                                           <?php echo !$is_passive ? 'Deactivate' : 'Activate'; ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 13px;">Current Account</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- DETAY VE DÜZENLEME YAN PANELİ (DRAWER) -->
    <div class="drawer-overlay" id="userDrawerOverlay" onclick="closeDrawer(event)">
        <div class="drawer" id="userDrawer">
            <div class="drawer-header">
                <div class="drawer-header-content">
                    <div class="drawer-avatar" id="drawerAvatar">DA</div>
                    <div>
                        <h3 id="drawerTitle">User Details</h3>
                        <p id="drawerSubtitle">Portal User Profile</p>
                    </div>
                </div>
                <button class="drawer-close" onclick="closeDrawerDirect()">&times;</button>
            </div>
            
            <div class="drawer-body" id="drawerBody">
                <!-- Görüntüleme Modu (Eski Sade ve Akıcı Düzen) -->
                <div id="viewModeContent">
                    <div class="detail-row"><span class="detail-label-old">Full Name</span><span class="detail-value-old" id="d_name">-</span></div>
                    <div class="detail-row"><span class="detail-label-old">Email Address</span><span class="detail-value-old" id="d_email">-</span></div>
                    <div class="detail-row"><span class="detail-label-old">Phone Number</span><span class="detail-value-old" id="d_phone">-</span></div>
                    <div class="detail-row"><span class="detail-label-old">Profession</span><span class="detail-value-old" id="d_profession">-</span></div>
                    <div class="detail-row"><span class="detail-label-old">Title</span><span class="detail-value-old" id="d_title">-</span></div>
                    <div class="detail-row"><span class="detail-label-old">Hire Date</span><span class="detail-value-old" id="d_hire_date">-</span></div>
                    <div class="detail-row"><span class="detail-label-old">System Role</span><span class="detail-value-old" id="d_role">-</span></div>
                    <div class="detail-row"><span class="detail-label-old">Account Status</span><span class="detail-value-old" id="d_status">-</span></div>
                    <div class="detail-row"><span class="detail-label-old">Created Date</span><span class="detail-value-old" id="d_created_at">-</span></div>
                    <div class="detail-row"><span class="detail-label-old">Last Login</span><span class="detail-value-old" id="d_last_login">-</span></div>
                </div>

                <!-- Düzenleme Modu (Form) -->
                <form id="editModeForm" action="" method="POST" style="display: none;">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="input-group" style="margin-bottom: 14px;">
                        <label>First Name</label>
                        <input type="text" name="edit_first_name" id="edit_first_name" required>
                    </div>
                    <div class="input-group" style="margin-bottom: 14px;">
                        <label>Last Name</label>
                        <input type="text" name="edit_last_name" id="edit_last_name" required>
                    </div>
                    <div class="input-group" style="margin-bottom: 14px;">
                        <label>Email Address</label>
                        <input type="email" name="edit_email" id="edit_email" required>
                    </div>
                    <div class="input-group" style="margin-bottom: 14px;">
                        <label>Phone Number</label>
                        <input type="text" name="edit_phone" id="edit_phone">
                    </div>
                    <div class="input-group" style="margin-bottom: 14px;">
                        <label>Profession</label>
                        <input type="text" name="edit_profession" id="edit_profession">
                    </div>
                    <div class="input-group" style="margin-bottom: 14px;">
                        <label>Title</label>
                        <input type="text" name="edit_title" id="edit_title">
                    </div>
                    <div class="input-group" style="margin-bottom: 14px;">
                        <label>Hire Date</label>
                        <input type="date" name="edit_hire_date" id="edit_hire_date">
                    </div>
                    <div class="input-group" style="margin-bottom: 14px;">
                        <label>System Role</label>
                        <select name="edit_role" id="edit_role">
                            <option value="Engineering">Engineering</option>
                            <option value="Site">Site</option>
                            <option value="Procurement">Procurement</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <button type="submit" name="update_user" class="btn" style="width: 100%; margin-top: 10px;">Save Changes</button>
                </form>
            </div>

            <div class="drawer-footer" id="drawerFooter">
                <button type="button" class="btn" onclick="switchToEditMode()">Edit User</button>
            </div>
        </div>
    </div>

    <!-- MODERN ONAY MODALI (CUSTOM CONFIRM) -->
    <div class="modal-backdrop" id="customModal">
        <div class="modal-box">
            <div class="modal-icon" id="modalIcon">!</div>
            <h3 id="modalTitle">Are you sure?</h3>
            <p id="modalMessage">Do you want to change this user status?</p>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeCustomModal()">Cancel</button>
                <button type="button" class="btn" id="modalConfirmBtn" onclick="executeModalAction()">Confirm</button>
            </div>
        </div>
    </div>

    <script>
        let currentUserData = null;
        let pendingActionUrl = '';

        function openDrawer(userData) {
            currentUserData = userData;
            
            let fullName = userData.first_name + ' ' + userData.last_name;
            let initials = (userData.first_name ? userData.first_name[0] : '') + (userData.last_name ? userData.last_name[0] : '');

            document.getElementById('drawerTitle').innerText = fullName;
            document.getElementById('drawerAvatar').innerText = initials.toUpperCase();
            document.getElementById('drawerSubtitle').innerText = userData.role + ' • ' + (userData.status == 1 ? 'Active' : 'Deactivated');
            
            document.getElementById('d_name').innerText = fullName;
            document.getElementById('d_email').innerText = userData.email || 'Not specified';
            document.getElementById('d_phone').innerText = userData.phone || 'Not specified';
            document.getElementById('d_profession').innerText = userData.profession || 'Not specified';
            document.getElementById('d_title').innerText = userData.title || 'Not specified';
            document.getElementById('d_hire_date').innerText = userData.hire_date || 'Not specified';
            document.getElementById('d_role').innerText = userData.role;
            document.getElementById('d_status').innerText = (userData.status == 1) ? 'Active' : 'Deactivated';
            document.getElementById('d_created_at').innerText = userData.created_at || '-';
            document.getElementById('d_last_login').innerText = userData.last_login || 'Never logged in';

            document.getElementById('edit_id').value = userData.id;
            document.getElementById('edit_first_name').value = userData.first_name || '';
            document.getElementById('edit_last_name').value = userData.last_name || '';
            document.getElementById('edit_email').value = userData.email || '';
            document.getElementById('edit_phone').value = userData.phone || '';
            document.getElementById('edit_profession').value = userData.profession || '';
            document.getElementById('edit_title').value = userData.title || '';
            document.getElementById('edit_hire_date').value = userData.hire_date || '';
            document.getElementById('edit_role').value = userData.role || 'Engineering';

            document.getElementById('viewModeContent').style.display = 'block';
            document.getElementById('editModeForm').style.display = 'none';
            document.getElementById('drawerFooter').style.display = 'flex';

            document.body.classList.add('drawer-open');
            document.getElementById('userDrawerOverlay').classList.add('open');
        }

        function switchToEditMode() {
            document.getElementById('drawerTitle').innerText = 'Edit User';
            document.getElementById('drawerSubtitle').innerText = currentUserData.first_name + ' ' + currentUserData.last_name;
            document.getElementById('viewModeContent').style.display = 'none';
            document.getElementById('editModeForm').style.display = 'block';
            document.getElementById('drawerFooter').style.display = 'none';
        }

        function closeDrawer(event) {
            if (event.target.id === 'userDrawerOverlay') {
                closeDrawerDirect();
            }
        }

        function closeDrawerDirect() {
            document.getElementById('userDrawerOverlay').classList.remove('open');
            document.body.classList.remove('drawer-open');
        }

        function confirmStatusChange(url, actionType) {
            pendingActionUrl = url;
            const modal = document.getElementById('customModal');
            const confirmBtn = document.getElementById('modalConfirmBtn');
            const modalIcon = document.getElementById('modalIcon');
            const modalTitle = document.getElementById('modalTitle');
            const modalMessage = document.getElementById('modalMessage');

            if (actionType === 'deactivate') {
                modalIcon.style.background = '#fef2f2';
                modalIcon.style.color = '#dc2626';
                modalIcon.innerHTML = '&#9888;';
                modalTitle.innerText = 'Deactivate User?';
                modalMessage.innerText = 'This user will lose access to the system until reactivated.';
                confirmBtn.className = 'btn btn-danger';
                confirmBtn.innerText = 'Deactivate';
            } else {
                modalIcon.style.background = '#f0fdf4';
                modalIcon.style.color = '#166534';
                modalIcon.innerHTML = '&#10003;';
                modalTitle.innerText = 'Activate User?';
                modalMessage.innerText = 'This user will regain access to the portal.';
                confirmBtn.className = 'btn';
                confirmBtn.innerText = 'Activate';
            }

            document.body.classList.add('modal-open');
            modal.classList.add('open');
        }

        function closeCustomModal() {
            document.getElementById('customModal').classList.remove('open');
            document.body.classList.remove('modal-open');
        }

        function executeModalAction() {
            if (pendingActionUrl) {
                window.location.href = pendingActionUrl;
            }
        }
    </script>
</body>
</html>