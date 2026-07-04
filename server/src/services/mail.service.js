const nodemailer = require("nodemailer");
const env = require("../config/env");

function canSendMail() {
  return Boolean(env.smtp.host && env.smtp.user && env.smtp.pass);
}

function createTransporter() {
  return nodemailer.createTransport({
    host: env.smtp.host,
    port: env.smtp.port,
    secure: env.smtp.secure,
    auth: {
      user: env.smtp.user,
      pass: env.smtp.pass
    }
  });
}

async function sendMail({ to, subject, html, text }) {
  if (!canSendMail()) {
    console.warn("SMTP is not configured. Skipping outbound email.");
    return { skipped: true };
  }

  const transporter = createTransporter();

  return transporter.sendMail({
    from: `${env.smtp.fromName} <${env.smtp.fromEmail}>`,
    to,
    subject,
    html,
    text
  });
}

module.exports = {
  canSendMail,
  sendMail
};
