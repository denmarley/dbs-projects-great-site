<?php
// GN.02.login.php - Portal Access (Login) Page
session_start();
require_once 'GN.01.db.php';

$hata = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($password)) {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Verify hashed password
            if (password_verify($password, $row['password'])) {
                
                // --- SON GİRİŞ TARİHİNİ GÜNCELLEME KODU ---
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $row['id']);
                $update_stmt->execute();
                $update_stmt->close();
                // -------------------------------------------

                $_SESSION['giris_yapildi'] = true;
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['first_name'] = $row['first_name'];
                $_SESSION['last_name'] = $row['last_name'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['role'] = $row['role'];

                // Redirect to GN.03.home.php
                header("Location: GN.03.home.php");
                exit;
            } else {
                $hata = "Invalid email or password.";
            }
        } else {
            $hata = "Invalid email or password.";
        }
        $stmt->close();
    } else {
        $hata = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Access | DBS Architecture</title>
    <style>
        :root {
            --dbs-green: #8FC93E;
            --dbs-green-hover: #7fb433;
            --dbs-dark: #1e293b;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-card {
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            text-align: center;
        }
        .lock-icon {
            background-color: var(--dbs-green);
            color: white;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            font-size: 24px;
        }
        h2 {
            margin: 0 0 8px 0;
            color: var(--dbs-dark);
            font-size: 24px;
            font-weight: 700;
        }
        .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .form-group {
            margin-bottom: 16px;
            text-align: left;
        }
        .input-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            font-size: 16px;
        }
        input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            color: var(--dbs-dark);
            outline: none;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: #f8fafc;
        }
        input:focus {
            border-color: var(--dbs-green);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(143, 201, 62, 0.15);
        }
        .login-btn {
            background-color: var(--dbs-green);
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            margin-top: 8px;
        }
        .login-btn:hover {
            background-color: var(--dbs-green-hover);
            transform: translateY(-1px);
        }
        .error-msg {
            background-color: #fef2f2;
            color: #dc2626;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 16px;
            border: 1px solid #fecaca;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: var(--dbs-dark);
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="lock-icon">🔒</div>
        <h2>Portal Access</h2>
        <div class="subtitle">Enter your credentials to continue</div>

        <?php if (!empty($hata)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($hata); ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <div class="input-container">
                    <span class="input-icon">✉️</span>
                    <input type="email" name="email" placeholder="Email address" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-container">
                    <span class="input-icon">🔑</span>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
            </div>

            <button type="submit" class="login-btn">Login &rarr;</button>
        </form>

        <a href="index.html" class="back-link">&larr; Back to Home</a>
    </div>

</body>
</html>