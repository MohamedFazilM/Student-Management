<?php
session_start();

// Already logged in → go to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $confirm   = trim($_POST['confirm_password'] ?? '');
    $role      = trim($_POST['role'] ?? 'user');

    // Whitelist roles
    if (!in_array($role, ['admin', 'user'])) $role = 'user';

    if ($username === '' || $password === '' || $confirm === '') {
        $error = 'All fields are required.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $conn = new mysqli("localhost", "root", "", "tests");
        if ($conn->connect_error) {
            $error = 'Database connection failed.';
        } else {
            // Ensure users table exists
            $conn->query("
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(80) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    role ENUM('admin','user') NOT NULL DEFAULT 'user',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");

            // Check duplicate username
            $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check->bind_param("s", $username);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = 'That username is already taken. Choose another.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $hashed, $role);
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up — Student Hub</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>
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
      max-width: 440px;
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

    .input-icon-wrap { position: relative; }

    .input-icon {
      position: absolute;
      left: 0.9rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-soft);
      font-size: 0.95rem;
      pointer-events: none;
    }

    .input-icon-wrap .form-control { padding-left: 2.6rem; }

    .btn-full { width: 100%; justify-content: center; }

    /* Role selector */
    .role-selector {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.75rem;
      margin-top: 0.4rem;
    }

    .role-option {
      position: relative;
    }

    .role-option input[type="radio"] {
      position: absolute;
      opacity: 0;
      width: 0; height: 0;
    }

    .role-label {
      height: auto;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.35rem;
      padding: 1rem 0.75rem;
      background: var(--surface2);
      border: 1px solid var(--border);
      border-radius: 12px;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
      text-align: center;
    }

    .role-option input:checked + .role-label {
      border-color: var(--accent);
      background: rgba(79,142,255,0.1);
    }
 
    .role-icon { font-size: 1.5rem; }

    .role-name {
      font-weight: 700;
      font-size: 0.88rem;
    }

    .role-desc {
      font-size: 0.72rem;
      color: var(--text-soft);
      line-height: 1.3;
    }

    /* Password strength */
    .strength-bar {
      height: 4px;
      border-radius: 99px;
      background: var(--border);
      margin-top: 0.5rem;
      overflow: hidden;
    }

    .strength-fill {
      height: 100%;
      border-radius: 99px;
      width: 0%;
      transition: width 0.3s, background 0.3s;
    }

    .strength-label {
      font-size: 0.72rem;
      margin-top: 0.3rem;
      color: var(--text-soft);
    }

    .success-state {
      text-align: center;
      padding: 1rem 0;
    }

    .success-icon {
      font-size: 3rem;
      margin-bottom: 1rem;
      display: block;
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

    <?php if ($success): ?>
    <!-- Success state -->
    <div class="success-state">
      <span class="success-icon">🎉</span>
      <h2 class="auth-title">Account Created!</h2>
      <p class="auth-subtitle" style="margin-bottom:1.5rem;">Your account is ready. You can now sign in.</p>
      <a href="login.php" class="btn btn-success btn-lg btn-full">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
          <polyline points="10 17 15 12 10 7"/>
          <line x1="15" y1="12" x2="3" y2="12"/>
        </svg>
        Go to Login
      </a>
    </div>

    <?php else: ?>

    <h1 class="auth-title">Create account</h1>
    <p class="auth-subtitle">Join Student Hub and get started</p>

    <?php if ($error): ?>
    <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">

      <!-- Username -->
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <div class="input-icon-wrap">
          <span class="input-icon">👤</span>
          <input
            type="text"
            id="username"
            name="username"
            class="form-control"
            placeholder="Choose a username"
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
            required
            autofocus
            minlength="3"
          >
        </div>
      </div>

      <!-- Password -->
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-icon-wrap">
          <span class="input-icon">🔒</span>
          <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            placeholder="At least 6 characters"
            required
            minlength="6"
          >
        </div>
        <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
        <div class="strength-label" id="strength-label"></div>
      </div>

      <!-- Confirm password -->
      <div class="form-group">
        <label class="form-label" for="confirm_password">Confirm Password</label>
        <div class="input-icon-wrap">
          <span class="input-icon">🔑</span>
          <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            class="form-control"
            placeholder="Re-enter your password"
            required
          >
        </div>
      </div>

      <!-- Role -->
      <div class="form-group">
        <label class="form-label">Account Role</label>
        <div class="role-selector">

          <div class="role-option">
            <input type="radio" name="role" id="role-user" value="user"
              <?= (($_POST['role'] ?? 'user') === 'user') ? 'checked' : '' ?>>
            <label class="role-label" for="role-user">
              <span class="role-icon">👁️</span>
              <span class="role-name">User</span>
              <span class="role-desc">View student records only</span>
            </label>
          </div>

          <div class="role-option">
            <input type="radio" name="role" id="role-admin" value="admin"
              <?= (($_POST['role'] ?? '') === 'admin') ? 'checked' : '' ?>>
            <label class="role-label" for="role-admin">
              <span class="role-icon">👑</span>
              <span class="role-name">Admin</span>
              <span class="role-desc">Full CRUD access for Admin</span>
            </label>
          </div>

        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-lg btn-full" style="margin-top:0.5rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="8.5" cy="7" r="4"/>
          <line x1="20" y1="8" x2="20" y2="14"/>
          <line x1="23" y1="11" x2="17" y2="11"/>
        </svg>
        Create Account
      </button>

    </form>

    <p class="auth-footer-text">
      Already have an account? <a href="login.php">Sign in →</a>
    </p>

    <?php endif; ?>
  </div>
</div>

<script>
// Password strength meter
const pwInput = document.getElementById('password');
const fill    = document.getElementById('strength-fill');
const label   = document.getElementById('strength-label');

if (pwInput) {
  pwInput.addEventListener('input', function () {
    const v = this.value;
    let score = 0;
    if (v.length >= 6)  score++;
    if (v.length >= 10) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;

    const levels = [
      { pct: '0%',   color: '',                  text: '' },
      { pct: '25%',  color: 'var(--red)',         text: '🔴 Weak' },
      { pct: '50%',  color: 'var(--yellow)',      text: '🟡 Fair' },
      { pct: '75%',  color: '#60a5fa',            text: '🔵 Good' },
      { pct: '100%', color: 'var(--green)',        text: '🟢 Strong' },
    ];

    const lvl = v.length === 0 ? levels[0] : (levels[Math.min(score, 4)] || levels[4]);
    fill.style.width      = lvl.pct;
    fill.style.background = lvl.color;
    label.textContent     = lvl.text;
  });
}
</script>
</body>
</html>
