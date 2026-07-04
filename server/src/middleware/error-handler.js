const { fail } = require("../utils/http");

function errorHandler(err, req, res, next) {
  if (res.headersSent) {
    return next(err);
  }

  console.error(err);

  return fail(
    res,
    err.message || "Internal server error",
    err.statusCode || 500
  );
}

module.exports = {
  errorHandler
};
