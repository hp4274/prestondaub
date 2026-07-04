const { ok, fail } = require("../utils/http");
const {
  getTeamMembers,
  getPublishedNews,
  getPublishedNewsBySlug,
  getAdminSettings,
  updateAdminSettings,
  getAdminNewsList,
  getAdminTeamList,
  listNewsCategories,
  getAdminNewsById,
  saveNewsCategory,
  deleteNewsCategory,
  saveAdminNews,
  deleteAdminNews,
  getTeamMemberById,
  saveTeamMember,
  toggleTeamMemberStatus,
  deleteTeamMember
} = require("../services/content.service");

async function listTeamMembers(req, res, next) {
  try {
    const result = await getTeamMembers();
    return ok(res, result);
  } catch (error) {
    return next(error);
  }
}

async function listPublishedNews(req, res, next) {
  try {
    const result = await getPublishedNews({
      page: req.query.page,
      perPage: req.query.per_page,
      category: req.query.category,
      search: req.query.search
    });

    return ok(res, result);
  } catch (error) {
    return next(error);
  }
}

async function getPublishedNewsItem(req, res, next) {
  try {
    const result = await getPublishedNewsBySlug(req.params.slug);
    return ok(res, result);
  } catch (error) {
    return next(error);
  }
}

async function listAdminNews(req, res, next) {
  try {
    const result = await getAdminNewsList({
      status: req.query.status,
      search: req.query.search,
      page: req.query.page,
      perPage: req.query.per_page
    });

    return ok(res, result);
  } catch (error) {
    return next(error);
  }
}

async function listAdminTeam(req, res, next) {
  try {
    const result = await getAdminTeamList({
      status: req.query.status,
      search: req.query.search,
      page: req.query.page,
      perPage: req.query.per_page
    });

    return ok(res, result);
  } catch (error) {
    return next(error);
  }
}

async function getSettings(req, res, next) {
  try {
    const settings = await getAdminSettings();
    return ok(res, { settings });
  } catch (error) {
    return next(error);
  }
}

async function saveSettings(req, res, next) {
  try {
    const settings = await updateAdminSettings(req.body);
    return ok(res, { settings }, "Settings updated");
  } catch (error) {
    return next(error);
  }
}

async function listAdminNewsCategories(req, res, next) {
  try {
    const categories = await listNewsCategories();
    return ok(res, { categories });
  } catch (error) {
    return next(error);
  }
}

async function getAdminNewsItem(req, res, next) {
  try {
    const article = await getAdminNewsById(req.params.id);
    return ok(res, { article });
  } catch (error) {
    return next(error);
  }
}

async function createAdminNewsCategory(req, res, next) {
  try {
    const category = await saveNewsCategory(req.body);
    return ok(res, { category }, "Category created");
  } catch (error) {
    return next(error);
  }
}

async function updateAdminNewsCategory(req, res, next) {
  try {
    const category = await saveNewsCategory(req.body, req.params.id);
    return ok(res, { category }, "Category updated");
  } catch (error) {
    return next(error);
  }
}

async function removeAdminNewsCategory(req, res, next) {
  try {
    await deleteNewsCategory(req.params.id);
    return ok(res, {}, "Category deleted");
  } catch (error) {
    return next(error);
  }
}

async function createAdminNews(req, res, next) {
  try {
    const article = await saveAdminNews(req.body);
    return ok(res, { article }, "Article created");
  } catch (error) {
    return next(error);
  }
}

async function updateAdminNews(req, res, next) {
  try {
    const article = await saveAdminNews(req.body, req.params.id);
    return ok(res, { article }, "Article updated");
  } catch (error) {
    return next(error);
  }
}

async function removeAdminNews(req, res, next) {
  try {
    await deleteAdminNews(req.params.id);
    return ok(res, {}, "Article deleted");
  } catch (error) {
    return next(error);
  }
}

async function getAdminTeamMember(req, res, next) {
  try {
    const member = await getTeamMemberById(req.params.id);
    return ok(res, { member });
  } catch (error) {
    return next(error);
  }
}

async function createAdminTeamMember(req, res, next) {
  try {
    const member = await saveTeamMember(req.body);
    return ok(res, { member }, "Team member created");
  } catch (error) {
    return next(error);
  }
}

async function updateAdminTeamMember(req, res, next) {
  try {
    const member = await saveTeamMember(req.body, req.params.id);
    return ok(res, { member }, "Team member updated");
  } catch (error) {
    return next(error);
  }
}

async function toggleAdminTeamMember(req, res, next) {
  try {
    const member = await toggleTeamMemberStatus(req.params.id);
    return ok(res, { member }, "Team member status updated");
  } catch (error) {
    return next(error);
  }
}

async function removeAdminTeamMember(req, res, next) {
  try {
    await deleteTeamMember(req.params.id);
    return ok(res, {}, "Team member deleted");
  } catch (error) {
    return next(error);
  }
}

module.exports = {
  listTeamMembers,
  listPublishedNews,
  getPublishedNewsItem,
  listAdminNews,
  listAdminTeam,
  getSettings,
  saveSettings,
  listAdminNewsCategories,
  getAdminNewsItem,
  createAdminNewsCategory,
  updateAdminNewsCategory,
  removeAdminNewsCategory,
  createAdminNews,
  updateAdminNews,
  removeAdminNews,
  getAdminTeamMember,
  createAdminTeamMember,
  updateAdminTeamMember,
  toggleAdminTeamMember,
  removeAdminTeamMember
};
