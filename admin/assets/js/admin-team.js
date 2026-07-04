const teamState = { status: '', search: '', page: 1, totalPages: 1 };
const form = document.getElementById('teamMemberForm');
const formTitle = document.getElementById('formTitle');
const newMemberBtn = document.getElementById('newMemberBtn');
const resetMemberBtn = document.getElementById('resetMemberBtn');

async function requireAdmin() {
  const response = await fetch(apiUrl('/api/auth/me'), { credentials: 'include' });
  if (!response.ok) {
    window.location.href = './login.html';
    throw new Error('Not authenticated');
  }
  const data = await parseApiJson(response);
  document.getElementById('sessionText').textContent = `Signed in as ${data.admin.name} (${data.admin.email})`;
}

function resetForm() {
  form.reset();
  document.getElementById('memberId').value = '';
  document.getElementById('sortOrderInput').value = 0;
  document.getElementById('activeInput').value = 'true';
  formTitle.textContent = 'Add Team Member';
}

async function editMember(id) {
  const response = await fetch(apiUrl(`/api/admin/team/${id}`), { credentials: 'include' });
  if (!response.ok) throw new Error('Failed to load member');
  const data = await parseApiJson(response);
  const member = data.member;

  document.getElementById('memberId').value = member.id;
  document.getElementById('fullNameInput').value = member.full_name || '';
  document.getElementById('roleInput').value = member.role || '';
  document.getElementById('imageUrlInput').value = member.image_url || '';
  document.getElementById('linkedinInput').value = member.linkedin_url || '';
  document.getElementById('sortOrderInput').value = member.sort_order || 0;
  document.getElementById('activeInput').value = member.is_active ? 'true' : 'false';
  document.getElementById('bioInput').value = member.bio || '';
  formTitle.textContent = 'Edit Team Member';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function toggleMember(id) {
  const response = await fetch(apiUrl(`/api/admin/team/${id}/toggle`), {
    method: 'POST',
    credentials: 'include'
  });
  if (!response.ok) throw new Error('Failed to toggle member');
  await loadTeam();
}

async function deleteMember(id) {
  const confirmed = window.confirm('Delete this team member?');
  if (!confirmed) return;

  const response = await fetch(apiUrl(`/api/admin/team/${id}`), {
    method: 'DELETE',
    credentials: 'include'
  });
  if (!response.ok) throw new Error('Failed to delete member');
  await loadTeam();
}

async function loadTeam() {
  const params = new URLSearchParams({
    status: teamState.status,
    search: teamState.search,
    page: teamState.page,
    per_page: 20
  });

  const response = await fetch(apiUrl(`/api/admin/team?${params.toString()}`), { credentials: 'include' });
  if (!response.ok) throw new Error('Failed to load team');
  const data = await parseApiJson(response);

  document.getElementById('moduleStatus').textContent = data.enabled
    ? 'Team page is enabled'
    : 'Team page is disabled';
  document.getElementById('statTotal').textContent = data.stats?.total || 0;
  document.getElementById('statActive').textContent = data.stats?.active || 0;
  document.getElementById('statInactive').textContent = data.stats?.inactive || 0;

  const items = data.items || [];
  document.getElementById('teamTable').innerHTML = items.length
    ? items.map((item) => `
        <tr>
          <td>${item.full_name || 'N/A'}</td>
          <td>${item.role || 'N/A'}</td>
          <td>${item.linkedin_url ? `<a href="${item.linkedin_url}" target="_blank" rel="noreferrer">Profile</a>` : 'N/A'}</td>
          <td><span class="status ${item.is_active ? 'active' : 'inactive'}">${item.is_active ? 'active' : 'inactive'}</span></td>
          <td>
            <div style="display:flex; gap:8px;">
              <button class="btn" type="button" data-edit-id="${item.id}">Edit</button>
              <button class="btn" type="button" data-toggle-id="${item.id}">${item.is_active ? 'Deactivate' : 'Activate'}</button>
              <button class="btn" type="button" data-delete-id="${item.id}">Delete</button>
            </div>
          </td>
        </tr>
      `).join('')
    : '<tr><td colspan="5">No team members found.</td></tr>';

  document.querySelectorAll('[data-edit-id]').forEach((button) => {
    button.addEventListener('click', () => editMember(button.dataset.editId));
  });
  document.querySelectorAll('[data-toggle-id]').forEach((button) => {
    button.addEventListener('click', () => toggleMember(button.dataset.toggleId));
  });
  document.querySelectorAll('[data-delete-id]').forEach((button) => {
    button.addEventListener('click', () => deleteMember(button.dataset.deleteId));
  });

  teamState.totalPages = data.pagination?.total_pages || 1;
  document.getElementById('pageInfo').textContent = `Page ${data.pagination?.current_page || 1} of ${teamState.totalPages}`;
  document.getElementById('prevBtn').disabled = teamState.page <= 1;
  document.getElementById('nextBtn').disabled = teamState.page >= teamState.totalPages;
}

document.addEventListener('DOMContentLoaded', async () => {
  try {
    await requireAdmin();
    await loadTeam();
  } catch (error) {
    console.error(error);
  }

  document.getElementById('searchBtn').addEventListener('click', () => {
    teamState.status = document.getElementById('statusFilter').value;
    teamState.search = document.getElementById('searchInput').value.trim();
    teamState.page = 1;
    loadTeam();
  });

  document.getElementById('prevBtn').addEventListener('click', () => {
    if (teamState.page > 1) {
      teamState.page -= 1;
      loadTeam();
    }
  });

  document.getElementById('nextBtn').addEventListener('click', () => {
    if (teamState.page < teamState.totalPages) {
      teamState.page += 1;
      loadTeam();
    }
  });

  newMemberBtn.addEventListener('click', resetForm);
  resetMemberBtn.addEventListener('click', resetForm);

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const memberId = document.getElementById('memberId').value;
    const payload = Object.fromEntries(new FormData(form).entries());
    const method = memberId ? 'PUT' : 'POST';
    const url = memberId ? `/api/admin/team/${memberId}` : '/api/admin/team';

    const response = await fetch(apiUrl(url), {
      method,
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      throw new Error('Failed to save member');
    }

    resetForm();
    await loadTeam();
  });
});
