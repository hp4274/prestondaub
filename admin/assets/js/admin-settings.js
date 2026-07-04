const settingsMessage = document.getElementById('message');

function showSettingsMessage(text, type = 'success') {
  settingsMessage.textContent = text;
  settingsMessage.className = `message show ${type}`;
}

async function requireSettingsSession() {
  const response = await fetch(apiUrl('/api/auth/me'), { credentials: 'include' });
  if (!response.ok) {
    window.location.href = './login.html';
    throw new Error('Not authenticated');
  }
  const data = await parseApiJson(response);
  document.getElementById('sessionText').textContent = `Signed in as ${data.admin.name} (${data.admin.email})`;
}

async function loadSettings() {
  const response = await fetch(apiUrl('/api/admin/settings'), { credentials: 'include' });
  if (!response.ok) throw new Error('Failed to load settings');
  const data = await parseApiJson(response);
  document.getElementById('teamModuleInput').checked = Boolean(data.settings?.team_module_enabled);
}

document.getElementById('saveBtn').addEventListener('click', async () => {
  try {
    const response = await fetch(apiUrl('/api/admin/settings'), {
      method: 'PUT',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        team_module_enabled: document.getElementById('teamModuleInput').checked
      })
    });
    const data = await parseApiJson(response);
    if (!response.ok || !data.success) throw new Error(data.message || 'Failed to save settings');
    showSettingsMessage(data.message || 'Settings updated');
  } catch (error) {
    showSettingsMessage(error.message, 'error');
  }
});

document.addEventListener('DOMContentLoaded', async () => {
  try {
    await requireSettingsSession();
    await loadSettings();
  } catch (error) {
    showSettingsMessage(error.message, 'error');
  }
});
