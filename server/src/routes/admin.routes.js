const express = require("express");
const {
  getForm,
  getFormDetailView,
  getDashboard,
  listForms,
  pollForms,
  handleTableAction,
  handleModalAction
} = require("../controllers/admin-form.controller");
const {
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
} = require("../controllers/content.controller");
const { requireAdmin } = require("../middleware/require-admin");

const router = express.Router();

router.use(requireAdmin);
router.get("/dashboard", getDashboard);
router.get("/forms", listForms);
router.get("/settings", getSettings);
router.put("/settings", saveSettings);
router.get("/news", listAdminNews);
router.post("/news", createAdminNews);
router.get("/news/categories", listAdminNewsCategories);
router.post("/news/categories", createAdminNewsCategory);
router.put("/news/categories/:id", updateAdminNewsCategory);
router.delete("/news/categories/:id", removeAdminNewsCategory);
router.get("/news/:id", getAdminNewsItem);
router.put("/news/:id", updateAdminNews);
router.delete("/news/:id", removeAdminNews);
router.get("/team", listAdminTeam);
router.post("/team", createAdminTeamMember);
router.get("/team/:id", getAdminTeamMember);
router.put("/team/:id", updateAdminTeamMember);
router.post("/team/:id/toggle", toggleAdminTeamMember);
router.delete("/team/:id", removeAdminTeamMember);
router.get("/forms/poll", pollForms);
router.get("/forms/:id", getForm);
router.get("/forms/:id/detail", getFormDetailView);
router.post("/forms/actions", handleTableAction);
router.post("/forms/:id/actions", handleModalAction);

module.exports = router;
