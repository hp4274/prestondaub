const app = require("./app");
const env = require("./config/env");

app.listen(env.port, () => {
  console.log(`Preston Daub server running on port ${env.port}`);
});
