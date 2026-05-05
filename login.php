<?php
session_start();

// Already logged in → go to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Both username and password are required.';
    } else {
        // Fetch user
        $conn = new mysqli("localhost", "root", "", "tests");
        if ($conn->connect_error) {
            $error = 'Database connection failed. Please try again.';
        } else {
            $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if (!$user) {
                $error = 'No account found with that username.';
            } else {
                $ok = false;
                $info = password_get_info($user['password']);
                if (!empty($info['algo'])) {
                    $ok = password_verify($password, $user['password']);
                } else {
                    $ok = ($password === $user['password']);
                }

                if (!$ok) {
                    $error = 'Incorrect password. Please try again.';
                } else {
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role']     = $user['role'] ?? 'user';
                    header('Location: index.php');
                    exit;
                }
            }
        }
    }
}

$loggedOut = isset($_GET['logged_out']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Student Hub</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>
    /* ── Auth-page centering ── */
    .auth-wrapper {
      position: relative;
      z-index: 1;
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 2rem 1rem;
    }

    .auth-card {
      background: var(--surface);
      border: 1px solid var(--border2);
      border-radius: 22px;
      padding: 2.8rem 2.5rem;
      width: 100%;
      max-width: 420px;
      backdrop-filter: blur(20px);
      box-shadow: var(--shadow);
      animation: fadeUp 0.6s ease both;
    }

    .auth-logo {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 2rem;
      justify-content: center;
    }

    .auth-brand-icon {
      width: 46px; height: 46px;
      border-radius: 13px;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      display: grid; place-items: center;
      font-size: 1.4rem;
      box-shadow: 0 0 24px rgba(79,142,255,0.45);
    }

    .auth-brand-name {
      font-size: 1.35rem;
      font-weight: 800;
      background: linear-gradient(90deg, #a5c0ff, #c4b5fd);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .auth-title {
      font-size: 1.5rem;
      font-weight: 800;
      letter-spacing: -0.02em;
      margin-bottom: 0.35rem;
      text-align: center;
    }

    .auth-subtitle {
      color: var(--text-soft);
      font-size: 0.88rem;
      text-align: center;
      margin-bottom: 2rem;
    }

    .auth-divider {
      border: none;
      border-top: 1px solid var(--border);
      margin: 1.8rem 0;
    }

    .auth-footer-text {
      text-align: center;
      font-size: 0.85rem;
      color: var(--text-soft);
      margin-top: 1.5rem;
    }

    .auth-footer-text a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 600;
    }

    .auth-footer-text a:hover { text-decoration: underline; }

    .input-icon-wrap {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 0.9rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-soft);
      font-size: 0.95rem;
      pointer-events: none;
    }

    .input-icon-wrap .form-control {
      padding-left: 2.6rem;
    }

    .btn-full { width: 100%; justify-content: center; }

    .role-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0.25rem 0.75rem;
      border-radius: 99px;
      font-size: 0.72rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }

    .role-admin {
      background: rgba(124,58,237,0.15);
      border: 1px solid rgba(124,58,237,0.3);
      color: #c4b5fd;
    }

    .role-user {
      background: rgba(79,142,255,0.12);
      border: 1px solid rgba(79,142,255,0.25);
      color: #93c5fd;
    }
  </style>
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">

    <!-- Logo -->
    <div class="auth-logo">
      <div class="auth-brand-icon">🎓</div>
      <span class="auth-brand-name">Student Hub</span>
    </div>

    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-subtitle">Sign in to access your dashboard</p>

    <!-- Alerts -->
    <?php if ($loggedOut): ?>
    <div class="alert alert-success">✅ You have been logged out successfully.</div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" autocomplete="off">

      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <div class="input-icon-wrap">
          <span class="input-icon">👤</span>
          <input
            type="text"
            id="username"
            name="username"
            class="form-control"
            placeholder="Enter your username"
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
            required
            autofocus
          >
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-icon-wrap">
          <span class="input-icon">🔒</span>
          <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            placeholder="Enter your password"
            required
          >
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-lg btn-full" style="margin-top:0.5rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
          <polyline points="10 17 15 12 10 7"/>
          <line x1="15" y1="12" x2="3" y2="12"/>
        </svg>
        Sign In
      </button>

    </form>

    <hr class="auth-divider">

    <!-- Role info hint -->
    <div style="display:flex;gap:0.6rem;justify-content:center;flex-wrap:wrap;margin-bottom:0.5rem;">
      <span class="role-badge role-admin">👑 Admin — Full CRUD access</span>
      <span class="role-badge role-user">👁️ User — View only</span>
    </div>

    <p class="auth-footer-text">
      Don't have an account? <a href="signup.php">Create one →</a>
    </p>

  </div>
</div>
</body>
</html>
