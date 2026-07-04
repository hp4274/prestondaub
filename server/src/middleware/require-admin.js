const jwt = require("jsonwebtoken");
const env = require("../config/env");
const { fail } = require("../utils/http");

function requireAdmin(req, res, next) {
  const authHeader = req.headers.authorization || "";
  const bearerToken = authHeader.startsWith("Bearer ")
    ? authHeader.slice(7)
    : null;
  const cookieToken = req.cookies?.admin_token || null;
  const token = bearerToken || cookieToken;

  if (!token) {
    return fail(res, "Admin authentication required", 401);
  }

  try {
    req.admin = jwt.verify(token, env.jwtSecret);
    return next();
  } catch (error) {
    return fail(res, "Invalid admin token", 401);
  }
}

module.exports = {
  requireAdmin
};
