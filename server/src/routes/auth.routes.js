const express = require("express");
const {
  login,
  logout,
  me,
  profile,
  updateProfile,
  updatePassword
} = require("../controllers/auth.controller");
const { requireAdmin } = require("../middleware/require-admin");

const router = express.Router();

router.post("/login", login);
router.post("/logout", logout);
router.get("/me", requireAdmin, me);
router.get("/profile", requireAdmin, profile);
router.put("/profile", requireAdmin, updateProfile);
router.post("/change-password", requireAdmin, updatePassword);

module.exports = router;
