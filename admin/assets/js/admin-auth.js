const loginForm = document.getElementById('adminLoginForm');
const messageBox = document.getElementById('message');
const submitBtn = document.getElementById('submitBtn');

async function ensureLoggedOutPage() {
  try {
    const response = await fetch(apiUrl('/api/auth/me'), { credentials: 'include' });
    if (response.ok) {
      window.location.href = './dashboard.html';
    }
  } catch (error) {
    console.error('Auth check failed:', error);
  }
}

if (loginForm) {
  ensureLoggedOutPage();

  loginForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    messageBox.className = 'message';
    messageBox.textContent = '';
    submitBtn.disabled = true;
    submitBtn.textContent = 'Signing in...';

    try {
      const formData = new FormData(loginForm);
      const payload = Object.fromEntries(formData.entries());
      const response = await fetch(apiUrl('/api/auth/login'), {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify(payload)
      });

      const data = await parseApiJson(response);

      if (!response.ok || !data.success) {
        throw new Error(data.message || 'Login failed');
      }

      window.location.href = './dashboard.html';
    } catch (error) {
      messageBox.textContent = error.message;
      messageBox.className = 'message error';
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Login';
    }
  });
}
