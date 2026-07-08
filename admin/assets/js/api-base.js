(function () {
  function trimTrailingSlashes(s) {
    return s.replace(/\/+$/, "");
  }

  const meta = document.querySelector('meta[name="api-base-url"]');
  let base = meta ? String(meta.getAttribute("content") || "").trim() : "";

  if (!base) {
    const host = window.location.hostname;
    const port = window.location.port;
    const isLocal = host === "127.0.0.1" || host === "localhost";
    if (isLocal && port !== "4000") {
      base = `${window.location.protocol}//${host}:4000`;
    }
  }

  const normalized = trimTrailingSlashes(base);

  window.apiUrl = function apiUrl(path) {
    const p = String(path || "");
    
    const host = window.location.hostname;
    const port = window.location.port;
    const isLocal = host === "127.0.0.1" || host === "localhost";
    const isPhpEnv = isLocal && port !== "4000";
    
    if (isPhpEnv) {
      if (p.startsWith('/api/auth/login')) return 'login.php';
      if (p.startsWith('/api/auth/me')) return 'api-auth-me.php';
      if (p.startsWith('/api/auth/logout')) return 'api-logout.php';
      if (p.startsWith('/api/admin/dashboard')) return 'api-dashboard.php';
      if (p.startsWith('/api/admin/forms/poll')) return 'api-poll.php';
      if (p.startsWith('/api/admin/forms/actions')) return 'api-action.php';
      
      const detailMatch = p.match(/^\/api\/admin\/forms\/([^\/?]+)\/detail/);
      if (detailMatch) {
        return `api-form-detail.php?id=${encodeURIComponent(detailMatch[1])}`;
      }
      
      if (p.startsWith('/api/admin/forms')) {
        const qIdx = p.indexOf('?');
        const qs = qIdx !== -1 ? p.substring(qIdx) : '';
        return 'api-forms.php' + qs;
      }
    }
    
    const withSlash = p.startsWith("/") ? p : `/${p}`;
    return normalized ? normalized + withSlash : withSlash;
  };

  window.parseApiJson = async function parseApiJson(response) {
    const raw = await response.text();
    const trimmed = raw.trim();
    if (!trimmed) {
      const hint =
        response.status === 404
          ? " Nothing at /api on this port. Start the Node server (e.g. npm run dev in server/) on port 4000, or set <meta name=\"api-base-url\" content=\"http://127.0.0.1:YOUR_PORT\">."
          : " The server returned an empty body.";
      throw new Error(`Could not read API response (HTTP ${response.status}).${hint}`);
    }
    try {
      return JSON.parse(trimmed);
    } catch {
      throw new Error(
        "Server returned non-JSON (often an HTML page from Live Server). Check API base URL and that the Node API is running."
      );
    }
  };
})();
