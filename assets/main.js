document.addEventListener('DOMContentLoaded', () => {

  /* ── TOAST ─────────────────────────────────────────── */
  function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    const icon = type === 'success' ? '✅' : '❌';
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span>${icon}</span> ${message}`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
  }

  // Show toast from URL params
  const params = new URLSearchParams(location.search);
  if (params.get('added'))    showToast('Student added successfully!', 'success');
  if (params.get('updated'))  showToast('Student updated successfully!', 'success');
  if (params.get('deleted'))  showToast('Student deleted.', 'success');

  /* ── DELETE MODAL ───────────────────────────────────── */
  const modal     = document.getElementById('delete-modal');
  const delLink   = document.getElementById('confirm-delete-btn');
  let   deleteUrl = '';

  document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      deleteUrl = btn.dataset.url;
      const name = btn.dataset.name;
      document.getElementById('modal-student-name').textContent = name;
      modal.classList.add('open');
    });
  });

  document.getElementById('cancel-delete-btn')?.addEventListener('click', () => {
    modal.classList.remove('open');
  });

  modal?.addEventListener('click', (e) => {
    if (e.target === modal) modal.classList.remove('open');
  });

  delLink?.addEventListener('click', () => {
    if (deleteUrl) window.location.href = deleteUrl;
  });

  /* ── SEARCH & FILTER ────────────────────────────────── */
  const searchInput  = document.getElementById('search-input');
  const deptFilter   = document.getElementById('dept-filter');
  const rows         = document.querySelectorAll('tbody .data-row');
  const countLabel   = document.getElementById('result-count');

  function filterTable() {
    const q    = searchInput ? searchInput.value.toLowerCase() : '';
    const dept = deptFilter  ? deptFilter.value.toLowerCase() : '';
    let visible = 0;

    rows.forEach(row => {
      const name   = row.dataset.name   || '';
      const email  = row.dataset.email  || '';
      const rdept  = row.dataset.dept   || '';

      const matchQ    = name.includes(q) || email.includes(q) || rdept.includes(q);
      const matchDept = !dept || rdept.includes(dept);

      const show = matchQ && matchDept;
      row.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    if (countLabel) countLabel.textContent = visible;

    // Empty state
    const tbody = document.querySelector('tbody');
    let empty = document.getElementById('empty-row');
    if (visible === 0) {
      if (!empty) {
        empty = document.createElement('tr');
        empty.id = 'empty-row';
        empty.innerHTML = `<td colspan="6" style="text-align:center;padding:3rem;color:var(--text-soft)">
          <div style="font-size:2rem;margin-bottom:.5rem">🔍</div>
          <div>No students match your search</div>
        </td>`;
        tbody.appendChild(empty);
      }
    } else {
      empty?.remove();
    }
  }

  searchInput?.addEventListener('input', filterTable);
  deptFilter?.addEventListener('change', filterTable);

  /* ── SORT ───────────────────────────────────────────── */
  let sortCol = '', sortDir = 1;

  document.querySelectorAll('th.sortable').forEach(th => {
    th.addEventListener('click', () => {
      const col = th.dataset.col;
      if (sortCol === col) sortDir *= -1;
      else { sortCol = col; sortDir = 1; }

      document.querySelectorAll('th.sortable').forEach(t => t.querySelector('.sort-arrow')?.remove());
      const arrow = document.createElement('span');
      arrow.className = 'sort-arrow';
      arrow.textContent = sortDir === 1 ? ' ↑' : ' ↓';
      th.appendChild(arrow);

      const tbody = document.querySelector('tbody');
      const rowArr = Array.from(tbody.querySelectorAll('.data-row'));
      rowArr.sort((a, b) => {
        const av = (a.dataset[col] || '').toLowerCase();
        const bv = (b.dataset[col] || '').toLowerCase();
        return av < bv ? -sortDir : av > bv ? sortDir : 0;
      });
      rowArr.forEach(r => tbody.appendChild(r));
    });
  });

  /* ── BUTTON RIPPLE ──────────────────────────────────── */
  document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('mousedown', () => btn.classList.add('active'));
    btn.addEventListener('mouseup',   () => btn.classList.remove('active'));
    btn.addEventListener('mouseleave',() => btn.classList.remove('active'));
  });

});
