const moduleOptions = [
  { id: 'all', label: 'All' },
  { id: 'financing', label: 'Financing' },
  { id: 'sports', label: 'Sports' },
  { id: 'mosaic', label: 'Mosaic' },
  { id: 'prospera', label: 'Prospera' },
  { id: 'contact', label: 'Contact' }
];

const params = new URLSearchParams(window.location.search);

const state = {
  module: params.get('module') || 'all',
  status: '',
  search: '',
  page: 1,
  totalPages: 1
};

const moduleTabs = document.getElementById('moduleTabs');
const statusFilter = document.getElementById('statusFilter');
const searchInput = document.getElementById('searchInput');
const searchBtn = document.getElementById('searchBtn');
const formsTable = document.getElementById('formsTable');
const pageInfo = document.getElementById('pageInfo');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const sessionText = document.getElementById('sessionText');

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

function renderTabs() {
  moduleTabs.innerHTML = moduleOptions.map((item) => `
    <button class="tab ${state.module === item.id ? 'active' : ''}" data-module="${item.id}">
      ${item.label}
    </button>
  `).join('');

  moduleTabs.querySelectorAll('[data-module]').forEach((button) => {
    button.addEventListener('click', () => {
      state.module = button.dataset.module;
      state.page = 1;
      renderTabs();
      loadForms();
    });
  });
}

function setStats(stats) {
  document.getElementById('statTotal').textContent = stats.total || 0;
  document.getElementById('statNew').textContent = stats.new || 0;
  document.getElementById('statRead').textContent = stats.read || 0;
  document.getElementById('statSpam').textContent = stats.spam || 0;
}

function renderRows(forms) {
  if (!forms.length) {
    formsTable.innerHTML = '<tr><td colspan="7">No submissions found.</td></tr>';
    return;
  }

  formsTable.innerHTML = forms.map((form) => `
    <tr data-form-id="${form.id}" data-status="${form.status || 'new'}">
      <td>${form.name || 'N/A'}</td>
      <td>${form.email || 'N/A'}</td>
      <td>${form.form_type || 'N/A'}</td>
      <td><span class="priority ${form.priority || ''}">${form.priority || 'Not set'}</span></td>
      <td><span class="status ${form.status || 'new'}">${form.status || 'new'}</span></td>
      <td>${formatDate(form.created_at)}</td>
      <td>
        <div class="row-actions">
          <button class="row-btn" data-action="view" data-form-id="${form.id}">View</button>
          <button class="row-btn" data-action="toggle-read" data-form-id="${form.id}">Read</button>
          <button class="row-btn" data-action="toggle-spam" data-form-id="${form.id}">Spam</button>
        </div>
      </td>
    </tr>
  `).join('');
}

async function requireSession() {
  const response = await fetch(apiUrl('/api/auth/me'), { credentials: 'include' });
  if (!response.ok) {
    window.location.href = './login.html';
    throw new Error('Not authenticated');
  }
  const data = await parseApiJson(response);
  sessionText.textContent = `Signed in as ${data.admin.name} (${data.admin.email})`;
}

async function loadForms() {
  const params = new URLSearchParams({
    module: state.module,
    status: state.status,
    search: state.search,
    page: state.page,
    per_page: 20
  });

  const response = await fetch(apiUrl(`/api/admin/forms?${params.toString()}`), {
    credentials: 'include'
  });

  if (!response.ok) {
    throw new Error('Failed to load forms');
  }

  const data = await parseApiJson(response);
  setStats(data.stats || {});
  renderRows(data.forms || []);
  state.totalPages = data.pagination?.total_pages || 1;
  pageInfo.textContent = `Page ${data.pagination?.current_page || 1} of ${state.totalPages}`;
  prevBtn.disabled = state.page <= 1;
  nextBtn.disabled = state.page >= state.totalPages;
}

searchBtn.addEventListener('click', () => {
  state.status = statusFilter.value;
  state.search = searchInput.value.trim();
  state.page = 1;
  loadForms();
});

prevBtn.addEventListener('click', () => {
  if (state.page > 1) {
    state.page -= 1;
    loadForms();
  }
});

nextBtn.addEventListener('click', () => {
  if (state.page < state.totalPages) {
    state.page += 1;
    loadForms();
  }
});

document.addEventListener('DOMContentLoaded', async () => {
  renderTabs();
  try {
    await requireSession();
    await loadForms();
  } catch (error) {
    console.error(error);
  }
});
