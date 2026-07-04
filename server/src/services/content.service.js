const { supabaseAdmin } = require("../config/supabase");

async function getTeamMembers() {
  const { data: settingsRows, error: settingsError } = await supabaseAdmin
    .from("settings")
    .select("setting_key, setting_value")
    .eq("setting_key", "team_module_enabled");

  if (settingsError && settingsError.code !== "PGRST116") {
    throw new Error(settingsError.message);
  }

  const enabledSetting = settingsRows?.[0];
  const enabled = enabledSetting ? enabledSetting.setting_value === "1" : true;

  if (!enabled) {
    return {
      enabled: false,
      members: [],
      count: 0
    };
  }

  const { data, error } = await supabaseAdmin
    .from("team_members")
    .select("id, full_name, role, bio, image_url, linkedin_url, sort_order")
    .eq("is_active", true)
    .order("sort_order", { ascending: true })
    .order("created_at", { ascending: false });

  if (error) {
    throw new Error(error.message);
  }

  const members = (data || []).map((member) => ({
    id: member.id,
    name: member.full_name,
    designation: member.role,
    short_bio: member.bio,
    photo: member.image_url,
    linkedin_url: member.linkedin_url,
    display_order: member.sort_order
  }));

  return {
    enabled: true,
    members,
    count: members.length
  };
}

async function getAdminSettings() {
  const { data, error } = await supabaseAdmin
    .from("settings")
    .select("setting_key, setting_value")
    .in("setting_key", ["team_module_enabled"]);

  if (error && error.code !== "PGRST116") {
    throw new Error(error.message);
  }

  const settings = Object.fromEntries((data || []).map((item) => [item.setting_key, item.setting_value]));
  return {
    team_module_enabled: settings.team_module_enabled !== "0"
  };
}

async function updateAdminSettings(payload) {
  const setting_value = payload.team_module_enabled === true || payload.team_module_enabled === "true" || payload.team_module_enabled === "1"
    ? "1"
    : "0";

  const { error } = await supabaseAdmin
    .from("settings")
    .upsert(
      {
        setting_key: "team_module_enabled",
        setting_value
      },
      { onConflict: "setting_key" }
    );

  if (error) {
    throw new Error(error.message);
  }

  return {
    team_module_enabled: setting_value === "1"
  };
}

async function getPublishedNews({ page = 1, perPage = 6, category = "", search = "" }) {
  const safePage = Math.max(1, Number(page) || 1);
  const safePerPage = Math.min(100, Math.max(1, Number(perPage) || 6));
  const from = (safePage - 1) * safePerPage;
  const to = from + safePerPage - 1;

  let query = supabaseAdmin
    .from("news")
    .select(
      "id, title, slug, excerpt, content, featured_image_url, status, view_count, published_at, news_categories(name)",
      { count: "exact" }
    )
    .eq("status", "published");

  if (category && category !== "all") {
    query = query.eq("news_categories.name", category);
  }

  if (search) {
    query = query.or(`title.ilike.%${search}%,excerpt.ilike.%${search}%`);
  }

  const { data, error, count } = await query
    .order("published_at", { ascending: false })
    .range(from, to);

  if (error) {
    throw new Error(error.message);
  }

  const articles = (data || []).map((row) => ({
    id: row.id,
    title: row.title,
    slug: row.slug,
    excerpt: row.excerpt && row.excerpt.length > 150 ? `${row.excerpt.slice(0, 150)}...` : (row.excerpt || ""),
    content: row.content,
    cover_image_url: row.featured_image_url,
    category: row.news_categories?.name || "News",
    featured: false,
    views: row.view_count || 0,
    published_at: row.published_at,
    published_at_formatted: row.published_at
      ? new Date(row.published_at).toLocaleDateString("en-US", {
          day: "numeric",
          month: "short",
          year: "numeric"
        })
      : ""
  }));

  const { data: categoriesData, error: categoriesError } = await supabaseAdmin
    .from("news_categories")
    .select("name")
    .order("name", { ascending: true });

  if (categoriesError) {
    throw new Error(categoriesError.message);
  }

  return {
    data: articles,
    pagination: {
      current_page: safePage,
      per_page: safePerPage,
      total_count: count || 0,
      total_pages: Math.ceil((count || 0) / safePerPage)
    },
    categories: (categoriesData || []).map((item) => item.name)
  };
}

async function getPublishedNewsBySlug(slug) {
  const safeSlug = String(slug || "").trim();

  if (!safeSlug) {
    const error = new Error("Article slug is required");
    error.statusCode = 400;
    throw error;
  }

  const { data: article, error } = await supabaseAdmin
    .from("news")
    .select("id, title, slug, excerpt, content, featured_image_url, view_count, published_at, news_categories(name)")
    .eq("slug", safeSlug)
    .eq("status", "published")
    .single();

  if (error || !article) {
    const notFoundError = new Error("Article not found");
    notFoundError.statusCode = 404;
    throw notFoundError;
  }

  const nextViewCount = (article.view_count || 0) + 1;
  await supabaseAdmin
    .from("news")
    .update({ view_count: nextViewCount })
    .eq("id", article.id);

  let relatedQuery = supabaseAdmin
    .from("news")
    .select("id, title, slug, excerpt, featured_image_url, published_at, news_categories(name)")
    .eq("status", "published")
    .neq("id", article.id)
    .order("published_at", { ascending: false })
    .limit(3);

  if (article.news_categories?.name) {
    relatedQuery = relatedQuery.eq("news_categories.name", article.news_categories.name);
  }

  const { data: relatedData, error: relatedError } = await relatedQuery;
  if (relatedError) {
    throw new Error(relatedError.message);
  }

  return {
    article: {
      id: article.id,
      title: article.title,
      slug: article.slug,
      excerpt: article.excerpt || "",
      content: article.content || "",
      cover_image_url: article.featured_image_url || "",
      category: article.news_categories?.name || "News",
      views: nextViewCount,
      published_at: article.published_at,
      published_at_formatted: article.published_at
        ? new Date(article.published_at).toLocaleDateString("en-US", {
            year: "numeric",
            month: "long",
            day: "numeric"
          })
        : ""
    },
    related_articles: (relatedData || []).map((item) => ({
      id: item.id,
      title: item.title,
      slug: item.slug,
      excerpt: item.excerpt || "",
      cover_image_url: item.featured_image_url || "",
      category: item.news_categories?.name || "News",
      published_at: item.published_at
    }))
  };
}

async function getAdminNewsList({ status = "", search = "", page = 1, perPage = 20 }) {
  const safePage = Math.max(1, Number(page) || 1);
  const safePerPage = Math.max(1, Math.min(100, Number(perPage) || 20));
  const from = (safePage - 1) * safePerPage;
  const to = from + safePerPage - 1;

  let query = supabaseAdmin
    .from("news")
    .select("id, title, slug, excerpt, featured_image_url, status, view_count, published_at, created_at", { count: "exact" });

  if (status) {
    query = query.eq("status", status);
  }

  if (search) {
    query = query.or(`title.ilike.%${search}%,excerpt.ilike.%${search}%`);
  }

  const { data, error, count } = await query
    .order("created_at", { ascending: false })
    .range(from, to);

  if (error) {
    throw new Error(error.message);
  }

  const { count: publishedCount, error: publishedError } = await supabaseAdmin
    .from("news")
    .select("id", { count: "exact", head: true })
    .eq("status", "published");

  if (publishedError) {
    throw new Error(publishedError.message);
  }

  const { count: draftCount, error: draftError } = await supabaseAdmin
    .from("news")
    .select("id", { count: "exact", head: true })
    .eq("status", "draft");

  if (draftError) {
    throw new Error(draftError.message);
  }

  return {
    stats: {
      total: count || 0,
      published: publishedCount || 0,
      draft: draftCount || 0
    },
    items: data || [],
    pagination: {
      current_page: safePage,
      per_page: safePerPage,
      total_count: count || 0,
      total_pages: Math.max(1, Math.ceil((count || 0) / safePerPage))
    }
  };
}

async function getAdminTeamList({ status = "", search = "", page = 1, perPage = 20 }) {
  const safePage = Math.max(1, Number(page) || 1);
  const safePerPage = Math.max(1, Math.min(100, Number(perPage) || 20));

  const { data: settingsRows, error: settingsError } = await supabaseAdmin
    .from("settings")
    .select("setting_key, setting_value")
    .eq("setting_key", "team_module_enabled");

  if (settingsError && settingsError.code !== "PGRST116") {
    throw new Error(settingsError.message);
  }

  let query = supabaseAdmin
    .from("team_members")
    .select("id, full_name, role, bio, image_url, linkedin_url, sort_order, is_active, created_at", { count: "exact" });

  if (status === "active") {
    query = query.eq("is_active", true);
  }
  if (status === "inactive") {
    query = query.eq("is_active", false);
  }
  if (search) {
    query = query.or(`full_name.ilike.%${search}%,role.ilike.%${search}%,bio.ilike.%${search}%`);
  }

  const from = (safePage - 1) * safePerPage;
  const to = from + safePerPage - 1;
  const { data, error, count } = await query
    .order("sort_order", { ascending: true })
    .order("created_at", { ascending: false })
    .range(from, to);

  if (error) {
    throw new Error(error.message);
  }

  const activeCount = (data || []).filter((item) => item.is_active).length;
  const totalEnabled = settingsRows?.[0]?.setting_value === "1";

  return {
    enabled: settingsRows?.length ? totalEnabled : true,
    stats: {
      total: count || 0,
      active: activeCount,
      inactive: Math.max(0, (count || 0) - activeCount)
    },
    items: data || [],
    pagination: {
      current_page: safePage,
      per_page: safePerPage,
      total_count: count || 0,
      total_pages: Math.max(1, Math.ceil((count || 0) / safePerPage))
    }
  };
}

async function listNewsCategories() {
  const { data, error } = await supabaseAdmin
    .from("news_categories")
    .select("id, name, slug")
    .order("name", { ascending: true });

  if (error) {
    throw new Error(error.message);
  }

  return data || [];
}

async function getAdminNewsById(id) {
  const { data, error } = await supabaseAdmin
    .from("news")
    .select("id, title, slug, excerpt, content, featured_image_url, status, published_at, created_at, category_id")
    .eq("id", id)
    .single();

  if (error) {
    throw new Error(error.message);
  }

  return data;
}

function slugify(value) {
  return String(value || "")
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "") || `article-${Date.now()}`;
}

async function saveNewsCategory(payload, id) {
  const record = {
    name: String(payload.name || "").trim(),
    slug: payload.slug ? slugify(payload.slug) : slugify(payload.name)
  };

  if (!record.name) {
    throw new Error("Category name is required");
  }

  if (id) {
    const { data, error } = await supabaseAdmin
      .from("news_categories")
      .update(record)
      .eq("id", id)
      .select("id, name, slug")
      .single();

    if (error) {
      throw new Error(error.message);
    }

    return data;
  }

  const { data, error } = await supabaseAdmin
    .from("news_categories")
    .insert(record)
    .select("id, name, slug")
    .single();

  if (error) {
    throw new Error(error.message);
  }

  return data;
}

async function deleteNewsCategory(id) {
  const { error } = await supabaseAdmin
    .from("news_categories")
    .delete()
    .eq("id", id);

  if (error) {
    throw new Error(error.message);
  }
}

async function saveAdminNews(payload, id) {
  const record = {
    title: payload.title,
    slug: payload.slug ? slugify(payload.slug) : slugify(payload.title),
    excerpt: payload.excerpt || "",
    content: payload.content || "",
    featured_image_url: payload.featured_image_url || "",
    status: payload.status || "draft",
    published_at: payload.status === "published" ? (payload.published_at || new Date().toISOString()) : null,
    category_id: payload.category_id || null
  };

  if (id) {
    const { data, error } = await supabaseAdmin
      .from("news")
      .update(record)
      .eq("id", id)
      .select("id, title, slug, excerpt, content, featured_image_url, status, published_at, created_at, category_id")
      .single();

    if (error) {
      throw new Error(error.message);
    }

    return data;
  }

  const { data, error } = await supabaseAdmin
    .from("news")
    .insert(record)
    .select("id, title, slug, excerpt, content, featured_image_url, status, published_at, created_at, category_id")
    .single();

  if (error) {
    throw new Error(error.message);
  }

  return data;
}

async function deleteAdminNews(id) {
  const { error } = await supabaseAdmin
    .from("news")
    .delete()
    .eq("id", id);

  if (error) {
    throw new Error(error.message);
  }
}

async function getTeamMemberById(id) {
  const { data, error } = await supabaseAdmin
    .from("team_members")
    .select("id, full_name, role, bio, image_url, linkedin_url, sort_order, is_active, created_at")
    .eq("id", id)
    .single();

  if (error) {
    throw new Error(error.message);
  }

  return data;
}

async function saveTeamMember(payload, id) {
  const record = {
    full_name: payload.full_name,
    role: payload.role,
    bio: payload.bio || "",
    image_url: payload.image_url || "",
    linkedin_url: payload.linkedin_url || "",
    sort_order: Number(payload.sort_order || 0),
    is_active: payload.is_active === true || payload.is_active === "true" || payload.is_active === "1"
  };

  if (id) {
    const { data, error } = await supabaseAdmin
      .from("team_members")
      .update(record)
      .eq("id", id)
      .select("id, full_name, role, bio, image_url, linkedin_url, sort_order, is_active, created_at")
      .single();

    if (error) {
      throw new Error(error.message);
    }

    return data;
  }

  const { data, error } = await supabaseAdmin
    .from("team_members")
    .insert(record)
    .select("id, full_name, role, bio, image_url, linkedin_url, sort_order, is_active, created_at")
    .single();

  if (error) {
    throw new Error(error.message);
  }

  return data;
}

async function toggleTeamMemberStatus(id) {
  const member = await getTeamMemberById(id);
  return saveTeamMember(
    {
      ...member,
      is_active: !member.is_active
    },
    id
  );
}

async function deleteTeamMember(id) {
  const { error } = await supabaseAdmin
    .from("team_members")
    .delete()
    .eq("id", id);

  if (error) {
    throw new Error(error.message);
  }
}

module.exports = {
  getTeamMembers,
  getPublishedNews,
  getPublishedNewsBySlug,
  getAdminSettings,
  updateAdminSettings,
  getAdminNewsList,
  getAdminTeamList,
  listNewsCategories,
  getAdminNewsById,
  saveNewsCategory,
  deleteNewsCategory,
  saveAdminNews,
  deleteAdminNews,
  getTeamMemberById,
  saveTeamMember,
  toggleTeamMemberStatus,
  deleteTeamMember
};
