const { ok, fail } = require("../utils/http");
const {
  getFormById,
  getFormDetail,
  getNewForms,
  updateForm,
  deleteForm,
  getDashboardData,
  getFormsList
} = require("../services/admin-form.service");

async function getForm(req, res, next) {
  try {
    const form = await getFormById(req.params.id);
    return res.json(form);
  } catch (error) {
    return next(error);
  }
}

async function pollForms(req, res, next) {
  try {
    if (!req.query.form_type) {
      return fail(res, "form_type is required", 400);
    }

    const result = await getNewForms(req.query.form_type, req.query.since);
    return ok(res, result);
  } catch (error) {
    return next(error);
  }
}

async function getFormDetailView(req, res, next) {
  try {
    const form = await getFormDetail(req.params.id);
    return ok(res, { form }, "Form detail loaded");
  } catch (error) {
    return next(error);
  }
}

async function handleTableAction(req, res, next) {
  try {
    const { action, form_id: formId, new_status: newStatus } = req.body;

    if (!action || !formId) {
      return fail(res, "Missing action or form ID", 400);
    }

    if (action === "delete") {
      await deleteForm(formId);
      return ok(res, { formId, action, newStatus }, "Action completed successfully");
    }

    if (action === "toggle-read" || action === "toggle-spam") {
      const form = await updateForm(formId, { status: newStatus });
      return ok(res, { formId, action, newStatus: form.status }, "Action completed successfully");
    }

    return fail(res, `Unknown action: ${action}`, 400);
  } catch (error) {
    return next(error);
  }
}

async function handleModalAction(req, res, next) {
  try {
    const formId = req.params.id;
    const action = String(req.body.action || "").trim();

    if (!action) {
      return fail(res, "Action is required", 400);
    }

    if (action === "update_priority") {
      const form = await updateForm(formId, { priority: req.body.priority || "low" });
      return ok(res, { priority: form.priority, formId, form }, "Priority updated successfully");
    }

    if (action === "update_notes") {
      const form = await updateForm(formId, { notes: req.body.notes || "" });
      return ok(res, { notes: form.notes || "", formId }, "Notes updated successfully");
    }

    if (action === "update_status") {
      const form = await updateForm(formId, { status: req.body.status || "read" });
      return ok(res, { status: form.status, formId }, "Status updated successfully");
    }

    if (action === "delete") {
      await deleteForm(formId);
      return ok(res, { formId }, "Form deleted successfully");
    }

    if (action === "send_reply") {
      return ok(res, { formId }, `Reply would be sent to: ${req.body.email || ""}`);
    }

    return fail(res, `Unknown action: ${action}`, 400);
  } catch (error) {
    return next(error);
  }
}

async function getDashboard(req, res, next) {
  try {
    const data = await getDashboardData();
    return ok(res, data, "Dashboard loaded");
  } catch (error) {
    return next(error);
  }
}

async function listForms(req, res, next) {
  try {
    const data = await getFormsList({
      module: req.query.module,
      status: req.query.status,
      search: req.query.search,
      page: req.query.page,
      perPage: req.query.per_page
    });

    return ok(res, data, "Forms loaded");
  } catch (error) {
    return next(error);
  }
}

module.exports = {
  getForm,
  getFormDetailView,
  pollForms,
  handleTableAction,
  handleModalAction,
  getDashboard,
  listForms
};
