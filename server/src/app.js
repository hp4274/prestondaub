const express = require("express");
const cors = require("cors");
const cookieParser = require("cookie-parser");
const env = require("./config/env");
const routes = require("./routes");
const { errorHandler } = require("./middleware/error-handler");
const { fail } = require("./utils/http");

const app = express();

app.use(
  cors({
    origin: env.frontendUrl === "*" ? true : env.frontendUrl,
    credentials: true
  })
);
app.use(express.json({ limit: "2mb" }));
app.use(express.urlencoded({ extended: true, limit: "2mb" }));
app.use(cookieParser());

app.use("/api", routes);

app.use((req, res) => fail(res, "Route not found", 404));
app.use(errorHandler);

module.exports = app;
