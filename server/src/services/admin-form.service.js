const { supabaseAdmin } = require("../config/supabase");

function mapRow(row) {
  return {
    id: row.id,
    priority: row.priority,
    status: row.status,
    service: row.service,
    form_type: row.form_type,
    name: row.name,
    email: row.email,
    phone: row.phone,
    company: row.company,
    created_at: row.created_at,
    notes: row.notes
  };
}

async function getFormById(id) {
  const { data, error } = await supabaseAdmin
    .from("contact_forms")
    .select("id, priority, status, service, form_type, name, email, phone, company, created_at, notes, organization, organization_type, job_title, interests, goals_challenges, message, form_data")
    .eq("id", id)
    .single();

  if (error) {
    throw new Error(error.message);
  }

  return mapRow(data);
}

async function getFormDetail(id) {
  const { data, error } = await supabaseAdmin
    .from("contact_forms")
    .select("*")
    .eq("id", id)
    .single();

  if (error) {
    throw new Error(error.message);
  }

  if (data.status === "new") {
    const updated = await updateForm(id, { status: "read" });
    return {
      ...data,
      status: updated.status
    };
  }

  return data;
}

async function getNewForms(formType, sinceEpoch) {
  const sinceDate = new Date((Number(sinceEpoch) || Math.floor(Date.now() / 1000) - 30) * 1000).toISOString();

  const { data, error } = await supabaseAdmin
    .from("contact_forms")
    .select("id, name, email, phone, company, service, priority, status, form_type, created_at")
    .ilike("form_type", `%${formType}%`)
    .gt("created_at", sinceDate)
    .order("created_at", { ascending: false });

  if (error) {
    throw new Error(error.message);
  }

  return {
    count: data?.length || 0,
    forms: (data || []).map(mapRow),
    current_time: Math.floor(Date.now() / 1000)
  };
}

async function updateForm(id, fields) {
  const { data, error } = await supabaseAdmin
    .from("contact_forms")
    .update(fields)
    .eq("id", id)
    .select("id, priority, status, service, form_type, name, email, phone, company, created_at, notes")
    .single();

  if (error) {
    throw new Error(error.message);
  }

  return mapRow(data);
}

async function deleteForm(id) {
  const { error } = await supabaseAdmin
    .from("contact_forms")
    .delete()
    .eq("id", id);

  if (error) {
    throw new Error(error.message);
  }
}

async function getDashboardData() {
  const { data: allForms, error: formsError } = await supabaseAdmin
    .from("contact_forms")
    .select("id, name, email, form_type, status, created_at", { count: "exact" })
    .order("created_at", { ascending: false })
    .limit(15);

  if (formsError) {
    throw new Error(formsError.message);
  }

  const { count: totalCount, error: totalError } = await supabaseAdmin
    .from("contact_forms")
    .select("id", { count: "exact", head: true });

  if (totalError) {
    throw new Error(totalError.message);
  }

  const counts = {
    total: totalCount || 0,
    new: 0,
    read: 0,
    spam: 0
  };

  (allForms || []).forEach((form) => {
    if (form.status === "new") counts.new += 1;
    if (form.status === "read") counts.read += 1;
    if (form.status === "spam") counts.spam += 1;
  });

  const { count: newsCount, error: newsCountError } = await supabaseAdmin
    .from("news")
    .select("id", { count: "exact", head: true });

  if (newsCountError) {
    throw new Error(newsCountError.message);
  }

  const { count: newsPublished, error: newsPublishedError } = await supabaseAdmin
    .from("news")
    .select("id", { count: "exact", head: true })
    .eq("status", "published");

  if (newsPublishedError) {
    throw new Error(newsPublishedError.message);
  }

  return {
    stats: {
      total: counts.total,
      new: counts.new,
      read: counts.read,
      spam: counts.spam,
      news_total: newsCount || 0,
      news_published: newsPublished || 0
    },
    recent_forms: (allForms || []).map((form) => ({
      id: form.id,
      name: form.name,
      email: form.email,
      form_type: form.form_type,
      status: form.status,
      created_at: form.created_at
    }))
  };
}

function matchesModule(formType = "", module = "all") {
  const type = String(formType || "").toLowerCase();
  const target = String(module || "all").toLowerCase();

  if (target === "all") return true;
  if (target === "contact") return type === "contact" || type === "";
  if (target === "mosaic") return type.includes("mosaic");
  if (target === "prospera") return type.includes("prospera");
  if (target === "sports") return type.includes("sports");
  if (target === "financing") {
    return (
      type.includes("financing") ||
      type.includes("loan") ||
      [
        "business-loans",
        "sba-loans",
        "equipment-loans",
        "bridge-loans",
        "working-capital"
      ].includes(type)
    );
  }

  return true;
}

async function getFormsList({ module = "all", status = "", search = "", page = 1, perPage = 20 }) {
  const safePage = Math.max(1, Number(page) || 1);
  const safePerPage = Math.max(1, Math.min(100, Number(perPage) || 20));

  const { data, error } = await supabaseAdmin
    .from("contact_forms")
    .select("*")
    .order("created_at", { ascending: false });

  if (error) {
    throw new Error(error.message);
  }

  const filtered = (data || []).filter((row) => {
    const statusMatches = !status || row.status === status;
    const searchMatches =
      !search ||
      String(row.name || "").toLowerCase().includes(String(search).toLowerCase()) ||
      String(row.email || "").toLowerCase().includes(String(search).toLowerCase());

    return matchesModule(row.form_type, module) && statusMatches && searchMatches;
  });

  const start = (safePage - 1) * safePerPage;
  const forms = filtered.slice(start, start + safePerPage).map((row) => ({
    id: row.id,
    name: row.name,
    email: row.email,
    phone: row.phone,
    company: row.company,
    service: row.service,
    priority: row.priority,
    status: row.status,
    form_type: row.form_type,
    created_at: row.created_at
  }));

  const counts = filtered.reduce(
    (acc, row) => {
      acc.total += 1;
      if (row.status === "new") acc.new += 1;
      if (row.status === "read") acc.read += 1;
      if (row.status === "spam") acc.spam += 1;
      return acc;
    },
    { total: 0, new: 0, read: 0, spam: 0 }
  );

  return {
    stats: counts,
    forms,
    pagination: {
      current_page: safePage,
      per_page: safePerPage,
      total_count: filtered.length,
      total_pages: Math.max(1, Math.ceil(filtered.length / safePerPage))
    }
  };
}

module.exports = {
  getFormById,
  getFormDetail,
  getNewForms,
  updateForm,
  deleteForm,
  getDashboardData,
  getFormsList
};
