const express = require("express");
const {
  listTeamMembers,
  listPublishedNews,
  getPublishedNewsItem
} = require("../controllers/content.controller");

const router = express.Router();

router.get("/team-members", listTeamMembers);
router.get("/news", listPublishedNews);
router.get("/news/:slug", getPublishedNewsItem);

module.exports = router;
