<?php
include 'auth.php';
requireAdmin(); // only admins can add students

include 'db.php';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

  $name       = trim($_POST['name'] ?? '');
  $email      = trim($_POST['email'] ?? '');
  $department = trim($_POST['department'] ?? '');
  $phone      = trim($_POST['phone'] ?? '');
  $year       = trim($_POST['year'] ?? '');

  // Validation
  if (empty($name))                            $errors[] = 'Student name is required.';
  elseif (strlen($name) < 2)                   $errors[] = 'Name must be at least 2 characters.';

  if (empty($email))                           $errors[] = 'Email is required.';
  elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';

  if (empty($department))                      $errors[] = 'Department is required.';

  // Check duplicate email
  if (empty($errors)) {
    $existing = $db->from('student')->where('email', $email)->one();
    if ($existing) $errors[] = 'A student with this email already exists.';
  }

  if (empty($errors)) {
    $data = [
      'name'       => strtoupper($name),
      'email'      => $email,
      'department' => strtoupper($department),
    ];
    $db->from('student')->insert($data)->execute();
    header("Location: index.php?added=1");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Student — Student Hub</title>
  <link rel="stylesheet" href="assets/style.css">
  <script src="assets/main.js" defer></script>
</head>
<body>

<div class="page-wrapper">

  <!-- TOPBAR -->
  <nav class="topbar">
    <a href="index.php" class="topbar-brand">
      <div class="brand-icon">🎓</div>
      <span class="brand-name">Student Hub</span>
    </a>
    <div class="topbar-right">
      <a href="index.php" class="btn btn-secondary">← Back to List</a>
      <a href="logout.php" style="display:inline-flex;align-items:center;gap:.3rem;padding:.4rem .85rem;background:rgba(255,77,109,0.1);border:1px solid rgba(255,77,109,0.2);border-radius:8px;color:#fb7185;font-size:.78rem;font-weight:600;text-decoration:none;">🚪 Logout</a>
    </div>
  </nav>

  <!-- HERO -->
  <div class="hero" style="margin-bottom:1.5rem">
    <div>
      <h1 class="hero-title"><span>Add New</span> Student</h1>
      <p class="hero-sub">Fill in the details below to register a new student.</p>
    </div>
  </div>

  <!-- FORM -->
  <div class="form-card">

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <span>⚠️</span>
      <div>
        <?php foreach ($errors as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <form method="POST" id="add-form">

      <div class="form-group">
        <label class="form-label" for="name">Full Name *</label>
        <input type="text" id="name" name="name" class="form-control"
               placeholder="e.g. sulthan"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Email Address *</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="e.g. student @gmail.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="department">Department *</label>
        <select id="department" name="department" class="form-control" required>
          <option value="">Select Department</option>
          <?php
          $departments = ['PHP','Python','Flutter','React','Java','Embedded Systems','Testing','DevOps','UI/UX Design','Data Science','Data Analytics'];
          $selected = $_POST['department'] ?? '';
          foreach ($departments as $d):
          ?>
          <option value="<?= $d ?>" <?= $selected === $d ? 'selected' : '' ?>><?= $d ?></option>
          <?php endforeach; ?>
          <option value="other">Other</option>
        </select>
      </div>

      <div class="form-group" id="other-dept-group" style="display:none">
        <label class="form-label" for="other_dept">Specify Department</label>
        <input type="text" id="other_dept" class="form-control" placeholder="Enter department name">
      </div>

      <div class="form-actions">
        <button type="submit" name="submit" class="btn btn-success btn-lg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Save Student
        </button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
      </div>

    </form>
  </div>

</div>

<div class="toast-container" id="toast-container"></div>

<script>
// Show "other" dept input
document.getElementById('department').addEventListener('change', function() {
  document.getElementById('other-dept-group').style.display = this.value === 'other' ? '' : 'none';
  if (this.value === 'other') {
    const otherInput = document.getElementById('other_dept');
    otherInput.addEventListener('input', function() {
      // set hidden field when user types custom dept
    });
  }
});
</script>

</body>
</html>
