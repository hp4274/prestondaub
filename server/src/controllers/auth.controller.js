const { ok, fail } = require("../utils/http");
const {
  loginAdmin,
  signAdminToken,
  getAdminById,
  updateAdminProfile,
  changeAdminPassword
} = require("../services/auth.service");

async function login(req, res, next) {
  try {
    const { admin, token } = await loginAdmin(req.body.email, req.body.password);

    res.cookie("admin_token", token, {
      httpOnly: true,
      sameSite: "lax",
      secure: false,
      maxAge: 7 * 24 * 60 * 60 * 1000
    });

    return ok(res, { admin, token }, "Login successful");
  } catch (error) {
    return next(error);
  }
}

function logout(req, res) {
  res.clearCookie("admin_token");
  return ok(res, {}, "Logout successful");
}

function me(req, res) {
  if (!req.admin) {
    return fail(res, "Admin authentication required", 401);
  }

  return ok(
    res,
    {
      admin: {
        id: req.admin.sub,
        email: req.admin.email,
        name: req.admin.name,
        role: req.admin.role
      }
    },
    "Authenticated"
  );
}

async function profile(req, res, next) {
  try {
    const admin = await getAdminById(req.admin.sub);
    return ok(res, { admin }, "Profile loaded");
  } catch (error) {
    return next(error);
  }
}

async function updateProfile(req, res, next) {
  try {
    const admin = await updateAdminProfile(req.admin.sub, req.body);
    const token = signAdminToken(admin);
    res.cookie("admin_token", token, {
      httpOnly: true,
      sameSite: "lax",
      secure: false,
      maxAge: 7 * 24 * 60 * 60 * 1000
    });

    return ok(res, { admin }, "Profile updated");
  } catch (error) {
    return next(error);
  }
}

async function updatePassword(req, res, next) {
  try {
    await changeAdminPassword(
      req.admin.sub,
      req.body.current_password,
      req.body.new_password,
      req.body.confirm_password
    );

    return ok(res, {}, "Password updated");
  } catch (error) {
    return next(error);
  }
}

module.exports = {
  login,
  logout,
  me,
  profile,
  updateProfile,
  updatePassword
};
