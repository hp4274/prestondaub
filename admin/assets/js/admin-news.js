const newsState = { status: '', search: '', page: 1, totalPages: 1 };

function formatDate(value) {
  if (!value) return 'N/A';
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? 'N/A' : date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

async function requireAdmin() {
  const response = await fetch(apiUrl('/api/auth/me'), { credentials: 'include' });
  if (!response.ok) {
    window.location.href = './login.html';
    throw new Error('Not authenticated');
  }
  const data = await parseApiJson(response);
  document.getElementById('sessionText').textContent = `Signed in as ${data.admin.name} (${data.admin.email})`;
}

async function loadNews() {
  const params = new URLSearchParams({
    status: newsState.status,
    search: newsState.search,
    page: newsState.page,
    per_page: 20
  });

  const response = await fetch(apiUrl(`/api/admin/news?${params.toString()}`), { credentials: 'include' });
  if (!response.ok) throw new Error('Failed to load news');
  const data = await parseApiJson(response);

  document.getElementById('statTotal').textContent = data.stats?.total || 0;
  document.getElementById('statPublished').textContent = data.stats?.published || 0;
  document.getElementById('statDraft').textContent = data.stats?.draft || 0;

  const items = data.items || [];
  document.getElementById('newsTable').innerHTML = items.length
    ? items.map((item) => `
        <tr>
          <td>${item.title || 'Untitled'}</td>
          <td><span class="status ${item.status || 'draft'}">${item.status || 'draft'}</span></td>
          <td>${item.view_count || 0}</td>
          <td>${formatDate(item.published_at || item.created_at)}</td>
          <td><a href="./news-editor.html?id=${item.id}">Edit</a></td>
        </tr>
      `).join('')
    : '<tr><td colspan="5">No articles found.</td></tr>';

  newsState.totalPages = data.pagination?.total_pages || 1;
  document.getElementById('pageInfo').textContent = `Page ${data.pagination?.current_page || 1} of ${newsState.totalPages}`;
  document.getElementById('prevBtn').disabled = newsState.page <= 1;
  document.getElementById('nextBtn').disabled = newsState.page >= newsState.totalPages;
}

document.addEventListener('DOMContentLoaded', async () => {
  try {
    await requireAdmin();
    await loadNews();
  } catch (error) {
    console.error(error);
  }

  document.getElementById('searchBtn').addEventListener('click', () => {
    newsState.status = document.getElementById('statusFilter').value;
    newsState.search = document.getElementById('searchInput').value.trim();
    newsState.page = 1;
    loadNews();
  });

  document.getElementById('prevBtn').addEventListener('click', () => {
    if (newsState.page > 1) {
      newsState.page -= 1;
      loadNews();
    }
  });

  document.getElementById('nextBtn').addEventListener('click', () => {
    if (newsState.page < newsState.totalPages) {
      newsState.page += 1;
      loadNews();
    }
  });
});
