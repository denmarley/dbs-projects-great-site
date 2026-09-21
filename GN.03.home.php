<?php
// GN.03.home.php - Portal Dashboard
session_start();

// Çıkış yapma isteği kontrolü
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    header("Location: index.html");
    exit;
}

// Oturum açılmamışsa kullanıcıyı direkt login sayfasına yönlendir
if (!isset($_SESSION['giris_yapildi']) || $_SESSION['giris_yapildi'] !== true) {
    header("Location: GN.02.login.php");
    exit;
}

$first_name = $_SESSION['first_name'] ?? 'User';
$last_name = $_SESSION['last_name'] ?? '';
$role = $_SESSION['role'] ?? 'Member';
$email = $_SESSION['email'] ?? '';
$is_admin = ($role === 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | DBS Architecture Portal</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            color: #0f172a;
            text-decoration: none;
        }

        .nav-brand span {
            background: linear-gradient(135deg, #84cc16, #65a30d);
            color: white;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .logout-btn {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .logout-btn:hover {
            background: #fecaca;
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            width: 100%;
            flex: 1;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #ffffff, #f1f5f9);
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .welcome-banner h1 {
            font-size: 1.85rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .welcome-banner p {
            color: #64748b;
            font-size: 1rem;
        }

        /* Dashboard Layout Grid */
        .dashboard-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        /* Admin active olduğunda grid yapısını 2 sütuna bölüyoruz */
        @media (min-width: 900px) {
            .dashboard-layout.has-admin {
                grid-template-columns: 2fr 1fr;
            }
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }

        .card-icon {
            width: 45px;
            height: 45px;
            background: #ecfccb;
            color: #65a30d;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .card p {
            font-size: 0.875rem;
            color: #64748b;
            line-height: 1.5;
        }

        /* Stylish Admin Panel Sidebar Card */
        .admin-sidebar-card {
            background: linear-gradient(145deg, #0f172a, #1e293b);
            border-radius: 16px;
            padding: 1.75rem;
            color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.3);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid #334155;
        }

        .admin-sidebar-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1rem;
        }

        .admin-sidebar-icon {
            width: 40px;
            height: 40px;
            background: rgba(132, 204, 22, 0.2);
            color: #84cc16;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .admin-sidebar-card h3 {
            color: #ffffff;
            font-size: 1.15rem;
            font-weight: 600;
        }

        .admin-sidebar-card p {
            color: #94a3b8;
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        .admin-action-btn {
            background: #84cc16;
            color: #0f172a;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s, transform 0.2s;
        }

        .admin-action-btn:hover {
            background: #65a30d;
            color: #ffffff;
            transform: translateY(-2px);
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 1.5rem;
            font-size: 0.8rem;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
            margin-top: auto;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <header class="navbar">
        <a href="GN.03.home.php" class="nav-brand">
            <span>dbs</span> Portal Dashboard
        </a>
        <!-- Çıkış yapıldığında index.html'e yönlendiren doğru bağlantı -->
        <a href="GN.03.home.php?action=logout" class="logout-btn">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
        </a>
    </header>

    <!-- Main Container -->
    <main class="container">
        <div class="welcome-banner">
            <h1>Welcome back, <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?>! 👋</h1>
            <p>Role: <strong><?php echo htmlspecialchars($role); ?></strong> &bull; Email: <?php echo htmlspecialchars($email); ?></p>
        </div>

        <!-- Dashboard Layout: Admin ise sağda panel açılır, değilse sadece grid görünür -->
        <div class="dashboard-layout <?php echo $is_admin ? 'has-admin' : ''; ?>">
            
            <!-- Sol / Ana Modüller Grid -->
            <div class="grid">
                <div class="card">
                    <div class="card-icon">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <h3>Projects</h3>
                    <p>Manage active construction projects, schedules, and documentation workflows.</p>
                </div>

                <div class="card">
                    <div class="card-icon">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <h3>Team Management</h3>
                    <p>View site personnel, subcontractors, and task delegation matrices.</p>
                </div>

                <div class="card">
                    <div class="card-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3>Reports & Progress</h3>
                    <p>Access analytical summaries, change orders, and financial overviews.</p>
                </div>
            </div>

            <!-- Sağ Taraf: Sadece Adminler İçin Stylish Admin Panel Kartı -->
            <?php if ($is_admin): ?>
                <div class="admin-sidebar-card">
                    <div>
                        <div class="admin-sidebar-header">
                            <div class="admin-sidebar-icon">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <h3>Admin Panel</h3>
                        </div>
                        <p>Manage portal accounts, user access roles, permissions, and system security credentials.</p>
                    </div>
                    <a href="GN.04.usersPage.php" class="admin-action-btn">
                        <i class="fa-solid fa-users-cog"></i> Manage Users
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Footer -->
    <footer>
        &copy; 2026 DBS Mimarlık Mühendislik İnşaat Taah. San. ve Tic. A.Ş. All rights reserved.
    </footer>

</body>
</html>