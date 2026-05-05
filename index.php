<?php
include 'auth.php';  // starts session
requireLogin();      // redirect to login if not logged in

include 'db.php';

$students    = $db->from('student')->sortAsc('name')->many();
$totalCount  = count($students);
$depts       = array_unique(array_column($students, 'department'));
$deptCount   = count($depts);

$isAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Hub — Management System</title>
  <link rel="stylesheet" href="assets/style.css">
  <script src="assets/main.js" defer></script>
  <style>
    .role-pill {
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      padding: 0.28rem 0.75rem;
      border-radius: 99px;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .pill-admin {
      background: rgba(124,58,237,0.15);
      border: 1px solid rgba(124,58,237,0.3);
      color: #c4b5fd;
    }
    .pill-user {
      background: rgba(79,142,255,0.12);
      border: 1px solid rgba(79,142,255,0.25);
      color: #93c5fd;
    }
    .user-info { display: flex; align-items: center; gap: 0.6rem; }
    .logout-btn {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0.4rem 0.85rem;
      background: rgba(255,77,109,0.1);
      border: 1px solid rgba(255,77,109,0.2);
      border-radius: 8px;
      color: #fb7185;
      font-size: 0.78rem;
      font-weight: 600;
      text-decoration: none;
      transition: background 0.2s;
    }
    .logout-btn:hover { background: rgba(255,77,109,0.2); }
    .readonly-notice {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.75rem 1.1rem;
      background: rgba(79,142,255,0.08);
      border: 1px solid rgba(79,142,255,0.18);
      border-radius: 10px;
      font-size: 0.84rem;
      color: #93c5fd;
      margin-bottom: 1rem;
      animation: fadeUp 0.7s ease 0.2s both;
    }
  </style>
</head>
<body>

<div class="page-wrapper">

  <nav class="topbar">
    <a href="index.php" class="topbar-brand">
      <div class="brand-icon">🎓</div>
      <span class="brand-name">Student Hub</span>
    </a>
    <div class="topbar-right">
      <span class="badge-count"><?= $totalCount ?> students</span>
      <div class="user-info">
        <span class="role-pill <?= $isAdmin ? 'pill-admin' : 'pill-user' ?>">
          <?= $isAdmin ? '👑 Admin' : '👁️ User' ?>
        </span>
        <span style="font-size:0.83rem;color:var(--text-soft);"><?= currentUser() ?></span>
        <a href="logout.php" class="logout-btn">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          Logout
        </a>
      </div>
    </div>
  </nav>

  <div class="hero">
    <div>
      <h1 class="hero-title"><span>Student Management System</span></h1>
      <p class="hero-sub"><?= $isAdmin ? 'Manage student records — add, search, edit and track.' : 'Browse and search student records.' ?></p>
    </div>
    <?php if ($isAdmin): ?>
    <a href="insert.php" class="btn btn-success btn-lg">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Student
    </a>
    <?php endif; ?>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="stat-label">Total Students</div><div class="stat-value blue"><?= $totalCount ?></div></div>
    <div class="stat-card"><div class="stat-label">Departments</div><div class="stat-value purple"><?= $deptCount ?></div></div>
    <div class="stat-card"><div class="stat-label">Active Records</div><div class="stat-value green"><?= $totalCount ?></div></div>
    <div class="stat-card"><div class="stat-label">Last Updated</div><div class="stat-value yellow" style="font-size:1rem;margin-top:4px"><?= date('d M Y') ?></div></div>
  </div>

  <?php if (!$isAdmin): ?>
  <div class="readonly-notice">👁️ You are in <strong>view-only</strong> mode. Contact an admin to make changes.</div>
  <?php endif; ?>

  <div class="toolbar">
    <div class="search-wrap">
      <span class="search-icon">🔍</span>
      <input type="text" id="search-input" class="search-input" placeholder="Search by name, email or department…">
    </div>
    <select id="dept-filter" class="filter-select">
      <option value="">All Departments</option>
      <?php foreach ($depts as $d): ?>
        <option value="<?= htmlspecialchars(strtolower($d)) ?>"><?= htmlspecialchars($d) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="table-card">
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th class="sortable" data-col="id">#<span class="sort-arrow"></span></th>
            <th class="sortable" data-col="name">Name</th>
            <th>Email</th>
            <th class="sortable" data-col="dept">Department</th>
            <?php if ($isAdmin): ?><th>Actions</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($students)): ?>
          <tr><td colspan="<?= $isAdmin ? 5 : 4 ?>">
            <div class="empty-state">
              <div class="empty-icon">📋</div>
              <p>No students yet. <?php if ($isAdmin): ?><a href="insert.php">Add your first student →</a><?php endif; ?></p>
            </div>
          </td></tr>
          <?php else: ?>
          <?php foreach ($students as $row):
            $initials = strtoupper(substr($row['name'],0,1).(strpos($row['name'],' ')?substr($row['name'],strpos($row['name'],' ')+1,1):''));
          ?>
          <tr class="data-row"
              data-name="<?= htmlspecialchars(strtolower($row['name'])) ?>"
              data-email="<?= htmlspecialchars(strtolower($row['email'])) ?>"
              data-dept="<?= htmlspecialchars(strtolower($row['department'])) ?>"
              data-id="<?= $row['id'] ?>">
            <td class="td-id"><?= str_pad($row['id'],3,'0',STR_PAD_LEFT) ?></td>
            <td class="td-name">
              <div class="name-cell">
                <div class="student-avatar"><?= htmlspecialchars($initials) ?></div>
                <?= htmlspecialchars($row['name']) ?>
              </div>
            </td>
            <td class="td-email"><?= htmlspecialchars($row['email']) ?></td>
            <td><span class="dept-badge"><?= htmlspecialchars($row['department']) ?></span></td>
            <?php if ($isAdmin): ?>
            <td>
              <div class="actions">
                <a href="update.php?id=<?= $row['id'] ?>" class="btn btn-primary">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  <span>Edit</span>
                </a>
                <button class="btn btn-danger delete-btn"
                        data-url="delete.php?id=<?= $row['id'] ?>"
                        data-name="<?= htmlspecialchars($row['name']) ?>">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                  <span>Delete</span>
                </button>
              </div>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="pagination">
      <span>Showing <strong id="result-count"><?= $totalCount ?></strong> of <strong><?= $totalCount ?></strong> records</span>
      <div class="pg-buttons"><button class="pg-btn active">1</button></div>
    </div>
  </div>

</div>

<?php if ($isAdmin): ?>
<div class="modal-backdrop" id="delete-modal">
  <div class="modal">
    <div class="modal-title">⚠️ Delete Student</div>
    <div class="modal-body">Are you sure you want to delete <strong id="modal-student-name"></strong>? This action cannot be undone.</div>
    <div class="modal-actions">
      <button class="btn btn-secondary" id="cancel-delete-btn">Cancel</button>
      <a href="#" class="btn btn-danger" id="confirm-delete-btn">Yes, Delete</a>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="toast-container" id="toast-container"></div>

</body>
</html>
