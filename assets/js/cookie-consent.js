/**
 * Cookie Consent Banner
 * Professional GDPR-compliant cookie consent for Preston Daub
 */

(function () {
  "use strict";

  const COOKIE_NAME = "prestondaub_cookie_consent";
  const COOKIE_EXPIRY_DAYS = 365;

  // Check if consent was already given
  function hasConsent() {
    return document.cookie.split(";").some(function (item) {
      return item.trim().indexOf(COOKIE_NAME + "=") === 0;
    });
  }

  // Set consent cookie
  function setConsent(value) {
    const date = new Date();
    date.setTime(date.getTime() + COOKIE_EXPIRY_DAYS * 24 * 60 * 60 * 1000);
    document.cookie =
      COOKIE_NAME +
      "=" +
      value +
      ";expires=" +
      date.toUTCString() +
      ";path=/;SameSite=Lax";
  }

  // Get consent value
  function getConsentValue() {
    const match = document.cookie.match(
      new RegExp("(^| )" + COOKIE_NAME + "=([^;]+)"),
    );
    return match ? match[2] : null;
  }

  // Create and inject the cookie banner
  function createBanner() {
    if (hasConsent()) return;

    const bannerHTML = `
            <div id="cookie-consent-banner" class="cookie-consent-banner">
                <div class="cookie-consent-container">
                    <div class="cookie-consent-content">
                        <div class="cookie-consent-icon">
                            <i class="fa-light fa-cookie-bite"></i>
                        </div>
                        <div class="cookie-consent-text">
                            <h4>We Value Your Privacy</h4>
                            <p>We use cookies to enhance your browsing experience, analyze site traffic, and personalize content. By clicking "Accept All", you consent to our use of cookies. You can manage your preferences or learn more in our <a href="privacy-policy.html">Privacy Policy</a>.</p>
                        </div>
                    </div>
                    <div class="cookie-consent-actions">
                        <button id="cookie-settings-btn" class="cookie-btn cookie-btn-secondary">
                            <i class="fa-light fa-sliders me-2"></i>Customize
                        </button>
                        <button id="cookie-reject-btn" class="cookie-btn cookie-btn-outline">
                            Reject All
                        </button>
                        <button id="cookie-accept-btn" class="cookie-btn cookie-btn-primary">
                            <i class="fa-light fa-check me-2"></i>Accept All
                        </button>
                    </div>
                </div>
                
                <!-- Cookie Settings Modal -->
                <div id="cookie-settings-modal" class="cookie-settings-modal">
                    <div class="cookie-settings-content">
                        <div class="cookie-settings-header">
                            <h3><i class="fa-light fa-cookie me-2"></i>Cookie Preferences</h3>
                            <button id="cookie-modal-close" class="cookie-modal-close">
                                <i class="fa-light fa-times"></i>
                            </button>
                        </div>
                        <div class="cookie-settings-body">
                            <div class="cookie-category">
                                <div class="cookie-category-header">
                                    <div class="cookie-category-info">
                                        <h5>Essential Cookies</h5>
                                        <p>Required for the website to function properly. Cannot be disabled.</p>
                                    </div>
                                    <label class="cookie-toggle disabled">
                                        <input type="checkbox" checked disabled>
                                        <span class="cookie-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="cookie-category">
                                <div class="cookie-category-header">
                                    <div class="cookie-category-info">
                                        <h5>Analytics Cookies</h5>
                                        <p>Help us understand how visitors interact with our website.</p>
                                    </div>
                                    <label class="cookie-toggle">
                                        <input type="checkbox" id="analytics-cookies" checked>
                                        <span class="cookie-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="cookie-category">
                                <div class="cookie-category-header">
                                    <div class="cookie-category-info">
                                        <h5>Marketing Cookies</h5>
                                        <p>Used to deliver personalized advertisements.</p>
                                    </div>
                                    <label class="cookie-toggle">
                                        <input type="checkbox" id="marketing-cookies">
                                        <span class="cookie-toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="cookie-settings-footer">
                            <button id="cookie-save-preferences" class="cookie-btn cookie-btn-primary">
                                Save Preferences
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

    // Inject CSS
    const styleSheet = document.createElement("style");
    styleSheet.textContent = `
            .cookie-consent-banner {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: #fff;
                box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15);
                z-index: 999999;
                padding: 24px 0;
                transform: translateY(100%);
                animation: slideUp 0.5s ease forwards;
                animation-delay: 1s;
                /* No overlay - users can still interact with site */
            }

            @keyframes slideUp {
                to {
                    transform: translateY(0);
                }
            }

            .cookie-consent-container {
                max-width: 1320px;
                margin: 0 auto;
                padding: 0 24px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 30px;
                flex-wrap: wrap;
            }

            .cookie-consent-content {
                display: flex;
                align-items: flex-start;
                gap: 20px;
                flex: 1;
                min-width: 300px;
            }

            .cookie-consent-icon {
                width: 50px;
                height: 50px;
                background: linear-gradient(135deg, #0752c5, #0a3d8f);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .cookie-consent-icon i {
                font-size: 24px;
                color: #fff;
            }

            .cookie-consent-text h4 {
                font-size: 18px;
                font-weight: 600;
                color: #1a1a1a;
                margin: 0 0 8px 0;
                font-family: 'Clash Display', sans-serif;
            }

            .cookie-consent-text p {
                font-size: 14px;
                color: #666;
                margin: 0;
                line-height: 1.6;
            }

            .cookie-consent-text a {
                color: #0752c5;
                text-decoration: underline;
            }

            .cookie-consent-text a:hover {
                color: #0a3d8f;
            }

            .cookie-consent-actions {
                display: flex;
                gap: 12px;
                flex-shrink: 0;
                flex-wrap: wrap;
            }

            .cookie-btn {
                padding: 12px 24px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                border: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-family: 'Clash Display', sans-serif;
            }

            .cookie-btn-primary {
                background: linear-gradient(135deg, #0752c5, #0a3d8f);
                color: #fff;
            }

            .cookie-btn-primary:hover {
                background: linear-gradient(135deg, #0a3d8f, #01265e);
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(7, 82, 197, 0.35);
            }

            .cookie-btn-secondary {
                background: #f1f5f9;
                color: #334155;
            }

            .cookie-btn-secondary:hover {
                background: #e2e8f0;
            }

            .cookie-btn-outline {
                background: transparent;
                color: #64748b;
                border: 2px solid #e2e8f0;
            }

            .cookie-btn-outline:hover {
                border-color: #0752c5;
                color: #0752c5;
            }

            /* Cookie Settings Modal - Only shows when Customize is clicked */
            .cookie-settings-modal {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                z-index: 10000000;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 20px;
                backdrop-filter: blur(4px);
                opacity: 0;
                visibility: hidden;
            }

            .cookie-settings-modal.active {
                display: flex;
                opacity: 1;
                visibility: visible;
            }

            .cookie-settings-content {
                background: #fff;
                border-radius: 20px;
                max-width: 500px;
                width: 100%;
                max-height: 80vh;
                overflow-y: auto;
                animation: modalFadeIn 0.3s ease;
            }

            @keyframes modalFadeIn {
                from {
                    opacity: 0;
                    transform: scale(0.95) translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }

            .cookie-settings-header {
                padding: 24px;
                border-bottom: 1px solid #e2e8f0;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .cookie-settings-header h3 {
                font-size: 20px;
                font-weight: 600;
                color: #1a1a1a;
                margin: 0;
                font-family: 'Clash Display', sans-serif;
            }

            .cookie-modal-close {
                width: 36px;
                height: 36px;
                border: none;
                background: #f1f5f9;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }

            .cookie-modal-close:hover {
                background: #e2e8f0;
            }

            .cookie-settings-body {
                padding: 24px;
            }

            .cookie-category {
                padding: 20px;
                background: #f8fafc;
                border-radius: 12px;
                margin-bottom: 16px;
            }

            .cookie-category:last-child {
                margin-bottom: 0;
            }

            .cookie-category-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 20px;
            }

            .cookie-category-info h5 {
                font-size: 16px;
                font-weight: 600;
                color: #1a1a1a;
                margin: 0 0 6px 0;
                font-family: 'Clash Display', sans-serif;
            }

            .cookie-category-info p {
                font-size: 13px;
                color: #64748b;
                margin: 0;
                line-height: 1.5;
            }

            /* Toggle Switch */
            .cookie-toggle {
                position: relative;
                display: inline-block;
                width: 50px;
                height: 28px;
                flex-shrink: 0;
            }

            .cookie-toggle input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .cookie-toggle-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #cbd5e1;
                transition: 0.3s;
                border-radius: 28px;
            }

            .cookie-toggle-slider:before {
                position: absolute;
                content: "";
                height: 22px;
                width: 22px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: 0.3s;
                border-radius: 50%;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .cookie-toggle input:checked + .cookie-toggle-slider {
                background: linear-gradient(135deg, #0752c5, #0a3d8f);
            }

            .cookie-toggle input:checked + .cookie-toggle-slider:before {
                transform: translateX(22px);
            }

            .cookie-toggle.disabled .cookie-toggle-slider {
                opacity: 0.6;
                cursor: not-allowed;
            }

            .cookie-settings-footer {
                padding: 20px 24px;
                border-top: 1px solid #e2e8f0;
                text-align: right;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .cookie-consent-container {
                    flex-direction: column;
                    align-items: stretch;
                }

                .cookie-consent-content {
                    flex-direction: column;
                    text-align: center;
                    align-items: center;
                }

                .cookie-consent-actions {
                    justify-content: center;
                }

                .cookie-btn {
                    flex: 1;
                    min-width: 100px;
                }
            }

            @media (max-width: 480px) {
                .cookie-consent-actions {
                    flex-direction: column;
                }

                .cookie-btn {
                    width: 100%;
                }
            }

            /* Hide banner when accepted */
            .cookie-consent-banner.hidden {
                animation: slideDown 0.5s ease forwards;
            }

            @keyframes slideDown {
                to {
                    transform: translateY(100%);
                }
            }
        `;
    document.head.appendChild(styleSheet);

    // Inject HTML
    document.body.insertAdjacentHTML("beforeend", bannerHTML);

    // Add event listeners
    const banner = document.getElementById("cookie-consent-banner");
    const modal = document.getElementById("cookie-settings-modal");

    document
      .getElementById("cookie-accept-btn")
      .addEventListener("click", function () {
        setConsent("all");
        hideBanner(banner);
      });

    document
      .getElementById("cookie-reject-btn")
      .addEventListener("click", function () {
        setConsent("essential");
        hideBanner(banner);
      });

    document
      .getElementById("cookie-settings-btn")
      .addEventListener("click", function () {
        modal.classList.add("active");
      });

    document
      .getElementById("cookie-modal-close")
      .addEventListener("click", function () {
        modal.classList.remove("active");
      });

    document
      .getElementById("cookie-save-preferences")
      .addEventListener("click", function () {
        const analytics = document.getElementById("analytics-cookies").checked;
        const marketing = document.getElementById("marketing-cookies").checked;

        let consent = "essential";
        if (analytics && marketing) consent = "all";
        else if (analytics) consent = "analytics";
        else if (marketing) consent = "marketing";

        setConsent(consent);
        modal.classList.remove("active");
        hideBanner(banner);
      });

    // Close modal on backdrop click
    modal.addEventListener("click", function (e) {
      if (e.target === modal) {
        modal.classList.remove("active");
      }
    });
  }

  function hideBanner(banner) {
    banner.classList.add("hidden");
    setTimeout(function () {
      banner.remove();
    }, 500);
  }

  // Initialize
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", createBanner);
  } else {
    createBanner();
  }
})();

// Maintenance Mode Redirect Checker
(function () {
  "use strict";

  // Skip if we are inside the admin folder or on the maintenance page itself
  if (window.location.pathname.includes("/admin/")) return;
  if (window.location.pathname.includes("/maintenance.html")) return;

  // Determine the base path dynamically
  // If the path contains "about" or "financing-solutions", the parent directory is "../"
  let basePath = "./";
  if (
    window.location.pathname.includes("/about/") ||
    window.location.pathname.includes("/financing-solutions/")
  ) {
    basePath = "../";
  }

  // Support any custom dev server port (e.g. Live Server)
  const isLiveServer = window.location.port !== "";
  const apiEndpoint = isLiveServer
    ? window.location.protocol +
      "//" +
      window.location.hostname +
      "/prestondaub/admin/api-maintenance-status.php"
    : basePath + "admin/api-maintenance-status.php";

  const redirectUrl = basePath + "maintenance.html";

  fetch(apiEndpoint)
    .then(function (response) {
      if (!response.ok) throw new Error("API unreachable");
      return response.json();
    })
    .then(function (data) {
      if (data && data.maintenance_mode) {
        window.location.href = redirectUrl;
      }
    })
    .catch(function (err) {
      console.warn("Maintenance check bypassed:", err);
    });
})();
