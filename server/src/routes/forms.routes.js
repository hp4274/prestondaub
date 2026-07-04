const express = require("express");
const {
  createContact,
  createProspera,
  createMosaic,
  createSports,
  createFinancing
} = require("../controllers/form.controller");

const router = express.Router();

router.post("/contact", createContact);
router.post("/prospera", createProspera);
router.post("/mosaic", createMosaic);
router.post("/sports", createSports);
router.post("/financing", createFinancing);

module.exports = router;
