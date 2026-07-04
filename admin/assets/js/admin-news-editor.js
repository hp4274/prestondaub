const params = new URLSearchParams(window.location.search);
const articleId = params.get('id');

const pageTitle = document.getElementById('pageTitle');
const sessionText = document.getElementById('sessionText');
const messageBox = document.getElementById('message');
const form = document.getElementById('newsEditorForm');
const deleteBtn = document.getElementById('deleteBtn');
const categoryList = document.getElementById('categoryList');

let categories = [];

function showMessage(text, type = 'success') {
  messageBox.textContent = text;
  messageBox.className = `message show ${type}`;
}

async function requireAdmin() {
  const response = await fetch(apiUrl('/api/auth/me'), { credentials: 'include' });
  if (!response.ok) {
    window.location.href = './login.html';
    throw new Error('Not authenticated');
  }

  const data = await parseApiJson(response);
  sessionText.textContent = `Signed in as ${data.admin.name} (${data.admin.email})`;
}

async function loadCategories() {
  const response = await fetch(apiUrl('/api/admin/news/categories'), { credentials: 'include' });
  if (!response.ok) throw new Error('Failed to load categories');

  const data = await parseApiJson(response);
  const select = document.getElementById('categoryInput');
  categories = data.categories || [];

  select.innerHTML = '<option value="">No category</option>' + categories
    .map((item) => `<option value="${item.id}">${item.name}</option>`)
    .join('');

  renderCategoryList();
}

function renderCategoryList() {
  categoryList.innerHTML = categories.length
    ? categories.map((item) => `
        <div class="category-row">
          <div style="flex:1;">
            <strong>${item.name}</strong>
            <div style="font-size:12px; color:#6b7280;">${item.slug}</div>
          </div>
          <button class="btn" type="button" data-category-edit="${item.id}">Edit</button>
          <button class="btn" type="button" data-category-delete="${item.id}">Delete</button>
        </div>
      `).join('')
    : '<div style="color:#6b7280; font-size:14px;">No categories yet.</div>';

  document.querySelectorAll('[data-category-edit]').forEach((button) => {
    button.addEventListener('click', () => editCategory(button.dataset.categoryEdit));
  });

  document.querySelectorAll('[data-category-delete]').forEach((button) => {
    button.addEventListener('click', () => {
      deleteCategory(button.dataset.categoryDelete).catch((error) => {
        console.error(error);
        showMessage(error.message, 'error');
      });
    });
  });
}

function resetCategoryForm() {
  document.getElementById('categoryIdInput').value = '';
  document.getElementById('categoryNameInput').value = '';
  document.getElementById('categorySlugInput').value = '';
}

function editCategory(id) {
  const category = categories.find((item) => item.id === id);
  if (!category) return;

  document.getElementById('categoryIdInput').value = category.id;
  document.getElementById('categoryNameInput').value = category.name || '';
  document.getElementById('categorySlugInput').value = category.slug || '';
}

function fillForm(article) {
  document.getElementById('articleId').value = article.id || '';
  document.getElementById('titleInput').value = article.title || '';
  document.getElementById('slugInput').value = article.slug || '';
  document.getElementById('excerptInput').value = article.excerpt || '';
  document.getElementById('contentInput').value = article.content || '';
  document.getElementById('featuredImageInput').value = article.featured_image_url || '';
  document.getElementById('statusInput').value = article.status || 'draft';
  if (article.category_id) {
    document.getElementById('categoryInput').value = article.category_id;
  }
}

async function loadArticle() {
  if (!articleId) return;

  const response = await fetch(apiUrl(`/api/admin/news/${articleId}`), { credentials: 'include' });
  if (!response.ok) throw new Error('Failed to load article');

  const data = await parseApiJson(response);
  fillForm(data.article);
  pageTitle.textContent = 'Edit Article';
  deleteBtn.style.display = 'inline-block';
}

async function saveArticle(event) {
  event.preventDefault();

  const payload = {
    title: document.getElementById('titleInput').value.trim(),
    slug: document.getElementById('slugInput').value.trim(),
    excerpt: document.getElementById('excerptInput').value.trim(),
    content: document.getElementById('contentInput').value.trim(),
    category_id: document.getElementById('categoryInput').value,
    status: document.getElementById('statusInput').value,
    featured_image_url: document.getElementById('featuredImageInput').value.trim()
  };

  const method = articleId ? 'PUT' : 'POST';
  const url = articleId ? `/api/admin/news/${articleId}` : '/api/admin/news';

  const response = await fetch(apiUrl(url), {
    method,
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  });

  const data = await parseApiJson(response);
  if (!response.ok || !data.success) {
    throw new Error(data.message || 'Failed to save article');
  }

  showMessage(data.message || 'Article saved', 'success');

  if (!articleId && data.article?.id) {
    window.location.href = `./news-editor.html?id=${data.article.id}`;
  }
}

async function deleteArticle() {
  if (!articleId) return;
  const confirmed = window.confirm('Delete this article?');
  if (!confirmed) return;

  const response = await fetch(apiUrl(`/api/admin/news/${articleId}`), {
    method: 'DELETE',
    credentials: 'include'
  });

  const data = await parseApiJson(response);
  if (!response.ok || !data.success) {
    throw new Error(data.message || 'Failed to delete article');
  }

  window.location.href = './news.html';
}

async function saveCategory() {
  const categoryId = document.getElementById('categoryIdInput').value;
  const payload = {
    name: document.getElementById('categoryNameInput').value.trim(),
    slug: document.getElementById('categorySlugInput').value.trim()
  };

  const response = await fetch(
    apiUrl(categoryId ? `/api/admin/news/categories/${categoryId}` : '/api/admin/news/categories'),
    {
    method: categoryId ? 'PUT' : 'POST',
    credentials: 'include',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  });

  const data = await parseApiJson(response);
  if (!response.ok || !data.success) {
    throw new Error(data.message || 'Failed to save category');
  }

  await loadCategories();
  if (!categoryId && data.category?.id) {
    document.getElementById('categoryInput').value = data.category.id;
  }
  resetCategoryForm();
  showMessage(data.message || 'Category saved', 'success');
}

async function deleteCategory(id) {
  const confirmed = window.confirm('Delete this category? Existing articles will keep working but lose the category assignment.');
  if (!confirmed) return;

  const response = await fetch(apiUrl(`/api/admin/news/categories/${id}`), {
    method: 'DELETE',
    credentials: 'include'
  });

  const data = await parseApiJson(response);
  if (!response.ok || !data.success) {
    throw new Error(data.message || 'Failed to delete category');
  }

  if (document.getElementById('categoryInput').value === id) {
    document.getElementById('categoryInput').value = '';
  }

  await loadCategories();
  resetCategoryForm();
  showMessage(data.message || 'Category deleted', 'success');
}

document.addEventListener('DOMContentLoaded', async () => {
  try {
    await requireAdmin();
    await loadCategories();
    await loadArticle();
  } catch (error) {
    console.error(error);
    showMessage(error.message, 'error');
  }

  form.addEventListener('submit', (event) => {
    saveArticle(event).catch((error) => {
      console.error(error);
      showMessage(error.message, 'error');
    });
  });

  deleteBtn.addEventListener('click', () => {
    deleteArticle().catch((error) => {
      console.error(error);
      showMessage(error.message, 'error');
    });
  });

  document.getElementById('saveCategoryBtn').addEventListener('click', () => {
    saveCategory().catch((error) => {
      console.error(error);
      showMessage(error.message, 'error');
    });
  });

  document.getElementById('resetCategoryBtn').addEventListener('click', resetCategoryForm);
});
