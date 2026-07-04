const welcomeText = document.getElementById('welcomeText');
const refreshBtn = document.getElementById('refreshBtn');
const logoutBtn = document.getElementById('logoutBtn');
const recentFormsTable = document.getElementById('recentFormsTable');

function formatDate(value) {
  if (!value) return 'N/A';
  const date = new Date(value);
  return Number.isNaN(date.getTime())
    ? 'N/A'
    : date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
}

async function requireAdminSession() {
  const response = await fetch(apiUrl('/api/auth/me'), { credentials: 'include' });

  if (!response.ok) {
    window.location.href = './login.html';
    throw new Error('Not authenticated');
  }

  const data = await parseApiJson(response);
  welcomeText.textContent = `Signed in as ${data.admin.name} (${data.admin.email})`;
  return data.admin;
}

function setStats(stats) {
  document.getElementById('statTotal').textContent = stats.total || 0;
  document.getElementById('statNew').textContent = stats.new || 0;
  document.getElementById('statRead').textContent = stats.read || 0;
  document.getElementById('statSpam').textContent = stats.spam || 0;
  document.getElementById('statNews').textContent = stats.news_total || 0;
}

function setRecentForms(forms) {
  if (!forms.length) {
    recentFormsTable.innerHTML = '<tr><td colspan="5">No submissions found.</td></tr>';
    return;
  }

  recentFormsTable.innerHTML = forms.map((form) => `
    <tr>
      <td>${form.name || 'N/A'}</td>
      <td>${form.email || 'N/A'}</td>
      <td>${form.form_type || 'N/A'}</td>
      <td><span class="status ${form.status || 'new'}">${form.status || 'new'}</span></td>
      <td>${formatDate(form.created_at)}</td>
    </tr>
  `).join('');
}

async function loadDashboard() {
  const response = await fetch(apiUrl('/api/admin/dashboard'), { credentials: 'include' });

  if (!response.ok) {
    throw new Error('Failed to load dashboard');
  }

  const data = await parseApiJson(response);
  const payload = data.stats ? data : data;
  setStats(payload.stats || {});
  setRecentForms(payload.recent_forms || []);
}

async function logout() {
  await fetch(apiUrl('/api/auth/logout'), {
    method: 'POST',
    credentials: 'include'
  });
  window.location.href = './login.html';
}

document.addEventListener('DOMContentLoaded', async () => {
  try {
    await requireAdminSession();
    await loadDashboard();
  } catch (error) {
    console.error(error);
  }
});

refreshBtn?.addEventListener('click', loadDashboard);
logoutBtn?.addEventListener('click', logout);
