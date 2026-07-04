const bcrypt = require("bcryptjs");
const jwt = require("jsonwebtoken");
const { supabaseAdmin } = require("../config/supabase");
const env = require("../config/env");

function signAdminToken(admin) {
  return jwt.sign(
    {
      sub: admin.id,
      email: admin.email,
      name: admin.name,
      role: admin.role
    },
    env.jwtSecret,
    { expiresIn: "7d" }
  );
}

async function loginAdmin(email, password) {
  const normalizedEmail = String(email || "").trim().toLowerCase();

  if (!normalizedEmail || !password) {
    const error = new Error("Please fill in all fields");
    error.statusCode = 400;
    throw error;
  }

  if (
    env.allowedAdminEmails.length > 0 &&
    !env.allowedAdminEmails.includes(normalizedEmail)
  ) {
    const error = new Error("Invalid email or password");
    error.statusCode = 401;
    throw error;
  }

  const { data, error } = await supabaseAdmin
    .from("admin_users")
    .select("id, email, password_hash, name, role, is_active")
    .eq("email", normalizedEmail)
    .single();

  if (error || !data || !data.is_active) {
    const authError = new Error("Invalid email or password");
    authError.statusCode = 401;
    throw authError;
  }

  const valid = await bcrypt.compare(password, data.password_hash);

  if (!valid) {
    const authError = new Error("Invalid email or password");
    authError.statusCode = 401;
    throw authError;
  }

  const admin = {
    id: data.id,
    email: data.email,
    name: data.name,
    role: data.role
  };

  return {
    admin,
    token: signAdminToken(admin)
  };
}

async function getAdminById(id) {
  const { data, error } = await supabaseAdmin
    .from("admin_users")
    .select("id, email, name, role, is_active, created_at, updated_at")
    .eq("id", id)
    .single();

  if (error || !data || !data.is_active) {
    const profileError = new Error("Admin not found");
    profileError.statusCode = 404;
    throw profileError;
  }

  return data;
}

async function updateAdminProfile(id, payload) {
  const name = String(payload.name || "").trim();

  if (!name) {
    const error = new Error("Name is required");
    error.statusCode = 400;
    throw error;
  }

  const { data, error } = await supabaseAdmin
    .from("admin_users")
    .update({ name })
    .eq("id", id)
    .select("id, email, name, role, is_active, created_at, updated_at")
    .single();

  if (error) {
    throw new Error(error.message);
  }

  return data;
}

async function changeAdminPassword(id, currentPassword, newPassword, confirmPassword) {
  if (!currentPassword || !newPassword || !confirmPassword) {
    const error = new Error("All password fields are required");
    error.statusCode = 400;
    throw error;
  }

  if (newPassword !== confirmPassword) {
    const error = new Error("New passwords do not match");
    error.statusCode = 400;
    throw error;
  }

  if (newPassword.length < 6) {
    const error = new Error("Password must be at least 6 characters");
    error.statusCode = 400;
    throw error;
  }

  const { data, error } = await supabaseAdmin
    .from("admin_users")
    .select("id, password_hash, is_active")
    .eq("id", id)
    .single();

  if (error || !data || !data.is_active) {
    const authError = new Error("Admin not found");
    authError.statusCode = 404;
    throw authError;
  }

  const valid = await bcrypt.compare(currentPassword, data.password_hash);
  if (!valid) {
    const authError = new Error("Current password is incorrect");
    authError.statusCode = 400;
    throw authError;
  }

  const password_hash = await bcrypt.hash(newPassword, 10);
  const updateResult = await supabaseAdmin
    .from("admin_users")
    .update({ password_hash })
    .eq("id", id);

  if (updateResult.error) {
    throw new Error(updateResult.error.message);
  }
}

module.exports = {
  loginAdmin,
  signAdminToken,
  getAdminById,
  updateAdminProfile,
  changeAdminPassword
};
