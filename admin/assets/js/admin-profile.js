const profileMessage = document.getElementById('message');

function showProfileMessage(text, type = 'success') {
  profileMessage.textContent = text;
  profileMessage.className = `message show ${type}`;
}

async function requireProfileSession() {
  const response = await fetch(apiUrl('/api/auth/me'), { credentials: 'include' });
  if (!response.ok) {
    window.location.href = './login.html';
    throw new Error('Not authenticated');
  }
  const data = await parseApiJson(response);
  document.getElementById('sessionText').textContent = `Signed in as ${data.admin.name} (${data.admin.email})`;
}

async function loadProfile() {
  const response = await fetch(apiUrl('/api/auth/profile'), { credentials: 'include' });
  if (!response.ok) throw new Error('Failed to load profile');
  const data = await parseApiJson(response);
  document.getElementById('nameInput').value = data.admin.name || '';
  document.getElementById('emailInput').value = data.admin.email || '';
}

document.getElementById('profileForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  try {
    const response = await fetch(apiUrl('/api/auth/profile'), {
      method: 'PUT',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name: document.getElementById('nameInput').value.trim() })
    });
    const data = await parseApiJson(response);
    if (!response.ok || !data.success) throw new Error(data.message || 'Failed to update profile');
    document.getElementById('sessionText').textContent = `Signed in as ${data.admin.name} (${data.admin.email})`;
    showProfileMessage(data.message || 'Profile updated');
  } catch (error) {
    showProfileMessage(error.message, 'error');
  }
});

document.getElementById('passwordForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  try {
    const response = await fetch(apiUrl('/api/auth/change-password'), {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        current_password: document.getElementById('currentPasswordInput').value,
        new_password: document.getElementById('newPasswordInput').value,
        confirm_password: document.getElementById('confirmPasswordInput').value
      })
    });
    const data = await parseApiJson(response);
    if (!response.ok || !data.success) throw new Error(data.message || 'Failed to update password');
    document.getElementById('passwordForm').reset();
    showProfileMessage(data.message || 'Password updated');
  } catch (error) {
    showProfileMessage(error.message, 'error');
  }
});

document.addEventListener('DOMContentLoaded', async () => {
  try {
    await requireProfileSession();
    await loadProfile();
  } catch (error) {
    showProfileMessage(error.message, 'error');
  }
});
