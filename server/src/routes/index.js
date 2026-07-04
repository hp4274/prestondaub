const express = require("express");
const formsRoutes = require("./forms.routes");
const publicRoutes = require("./public.routes");
const adminRoutes = require("./admin.routes");
const authRoutes = require("./auth.routes");
const { ok } = require("../utils/http");

const router = express.Router();

router.get("/health", (req, res) => {
  return ok(res, { uptime: process.uptime() }, "Server is healthy");
});

router.use("/forms", formsRoutes);
router.use("/public", publicRoutes);
router.use("/auth", authRoutes);
router.use("/admin", adminRoutes);

module.exports = router;
