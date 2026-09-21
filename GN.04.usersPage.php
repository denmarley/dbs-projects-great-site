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
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'User');

    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($password)) {
        // Email kontrolü
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $hata = "This email address is already registered.";
        } else {
            // Şifreyi güvenli bir şekilde hash'le
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $role);
            
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

// 3. Kullanıcı Silme İşlemi
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Admin kendi kendini silmesin
    if ($delete_id != $_SESSION['user_id']) {
        $del_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $del_stmt->bind_param("i", $delete_id);
        $del_stmt->execute();
        $del_stmt->close();
        header("Location: GN.04.usersPage.php");
        exit;
    } else {
        $hata = "You cannot delete your own active admin account.";
    }
}

// 4. Veritabanından Kullanıcıları Çekme
$result = $conn->query("SELECT id, first_name, last_name, email, role, created_at FROM users ORDER BY id DESC");
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
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--dbs-dark);
            margin-bottom: 24px;
        }
        .card {
            background: #ffffff;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            overflow: hidden; /* Kart dışına taşmaları engeller */
        }
        .card h3 {
            margin-top: 0;
            color: var(--dbs-dark);
            font-size: 18px;
            margin-bottom: 16px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }
        input, select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            outline: none;
            background-color: #f8fafc;
        }
        input:focus, select:focus {
            border-color: var(--dbs-green);
            background-color: #fff;
        }
        .btn {
            background-color: var(--dbs-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: var(--dbs-green-hover);
        }
        /* Kesin Çözüm: Mobil Uyumlu Tablo Kaydırma Alanı */
        .table-responsive {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-top: 10px;
            box-sizing: border-box;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            min-width: 600px;
        }
        th, td {
            padding: 12px 16px;
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
        tr:hover {
            background-color: #f8fafc;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-admin {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-user {
            background-color: #f1f5f9;
            color: #475569;
        }
        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            border: 1px solid #bbf7d0;
            font-size: 14px;
        }
        .alert-error {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            border: 1px solid #fecaca;
            font-size: 14px;
        }
        .delete-btn {
            color: #dc2626;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
        }
        .delete-btn:hover {
            text-decoration: underline;
        }
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
            <div class="alert-success"><?php echo htmlspecialchars($mesaj); ?></div>
        <?php endif; ?>

        <?php if (!empty($hata)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($hata); ?></div>
        <?php endif; ?>

        <!-- Yeni Kullanıcı Ekleme Formu -->
        <div class="card">
            <h3>Add New Portal User</h3>
            <form action="" method="POST">
                <div class="form-grid">
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="last_name" placeholder="Last Name" required>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <select name="role">
                        <option value="User">User (Standard)</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn">Create User Account</button>
            </form>
        </div>

        <!-- Mevcut Kullanıcılar Listesi -->
        <div class="card">
            <h3>Authorized Portal Users</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name Surname</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $row['role'] === 'Admin' ? 'badge-admin' : 'badge-user'; ?>">
                                        <?php echo $row['role']; ?>
                                    </span>
                                </td>
                                <td><?php echo $row['created_at']; ?></td>
                                <td>
                                    <?php if ($row['id'] != $_SESSION['user_id']): ?>
                                        <a href="GN.04.usersPage.php?delete_id=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
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

</body>
</html>