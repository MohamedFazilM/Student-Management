<?php
include 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$student = $db->from('student')->where('id', $id)->one();

if (!$student) {
  header("Location: index.php");
  exit;
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

  $name       = trim($_POST['name'] ?? '');
  $email      = trim($_POST['email'] ?? '');
  $department = trim($_POST['department'] ?? '');

  if (empty($name))   $errors[] = 'Student name is required.';
  if (empty($email))  $errors[] = 'Email is required.';
  elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
  if (empty($department)) $errors[] = 'Department is required.';

  // Check duplicate email (exclude current)
  if (empty($errors)) {
    $existing = $db->from('student')->where('email', $email)->one();
    if ($existing && $existing['id'] != $id) $errors[] = 'This email is already used by another student.';
  }

  if (empty($errors)) {
    $data = [
      'name'       => strtoupper($name),
      'email'      => $email,
      'department' => strtoupper($department),
    ];
    $db->from('student')->where('id', $id)->update($data)->execute();
    $success = true;
    $student = array_merge($student, $data);
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Student — Student Hub</title>
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
    </div>
  </nav>

  <!-- HERO -->
  <div class="hero" style="margin-bottom:1.5rem">
    <div>
      <h1 class="hero-title"><span>Edit</span> Student</h1>
      <p class="hero-sub">Update the details for student #<?= str_pad($id, 3, '0', STR_PAD_LEFT) ?></p>
    </div>
  </div>

  <!-- FORM -->
  <div class="form-card">

    <?php if ($success): ?>
    <div class="alert alert-success">✅ Student details updated successfully!</div>
    <?php endif; ?>

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

    <form method="POST">

      <div class="form-group">
        <label class="form-label" for="name">Full Name *</label>
        <input type="text" id="name" name="name" class="form-control"
               value="<?= htmlspecialchars($student['name']) ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Email Address *</label>
        <input type="email" id="email" name="email" class="form-control"
               value="<?= htmlspecialchars($student['email']) ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="department">Department *</label>
        <select id="department" name="department" class="form-control" required>
          <option value="">Select Department</option>
          <?php
          $departments = ['PHP','Python','Flutter','React','Java','Embedded Systems','Testing','DevOps','UI/UX Design','Data Science'];
          $curDept = $student['department'];
          foreach ($departments as $d):
          ?>
          <option value="<?= $d ?>" <?= strtoupper($curDept) === strtoupper($d) ? 'selected' : '' ?>><?= $d ?></option>
          <?php endforeach; ?>
          <option value="<?= htmlspecialchars($curDept) ?>"
            <?= !in_array(strtoupper($curDept), array_map('strtoupper', $departments)) ? 'selected' : '' ?>>
            <?= htmlspecialchars($curDept) ?>
          </option>
        </select>
      </div>

      <div class="form-actions">
        <button type="submit" name="update" class="btn btn-primary btn-lg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Update Student
        </button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
      </div>

    </form>
  </div>

</div>

<div class="toast-container" id="toast-container"></div>

</body>
</html>
