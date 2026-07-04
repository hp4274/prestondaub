const {
  submitContact,
  submitProspera,
  submitMosaic,
  submitSports,
  submitFinancing
} = require("../services/form.service");
const { ok, fail } = require("../utils/http");

async function handle(action, req, res, next, successMessage) {
  try {
    const submission = await action(req);

    return ok(
      res,
      { submissionId: submission.id },
      successMessage
    );
  } catch (error) {
    if (error.details) {
      return fail(res, error.message, error.statusCode || 400, error.details);
    }

    return next(error);
  }
}

function createContact(req, res, next) {
  return handle(
    submitContact,
    req,
    res,
    next,
    "Thank you for your inquiry! We will contact you shortly."
  );
}

function createProspera(req, res, next) {
  return handle(
    submitProspera,
    req,
    res,
    next,
    "Thank you for your Prospera inquiry! We will contact you shortly."
  );
}

function createMosaic(req, res, next) {
  return handle(
    submitMosaic,
    req,
    res,
    next,
    "Thank you for your demo request! We will contact you shortly."
  );
}

function createSports(req, res, next) {
  return handle(
    submitSports,
    req,
    res,
    next,
    "Thank you for your sports investment inquiry! We will contact you shortly."
  );
}

function createFinancing(req, res, next) {
  return handle(
    submitFinancing,
    req,
    res,
    next,
    "Thank you for your financing application! We will contact you shortly."
  );
}

module.exports = {
  createContact,
  createProspera,
  createMosaic,
  createSports,
  createFinancing
};
