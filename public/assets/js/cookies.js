/**
 * Cookies/banner logic + univerzální aktivace skriptů/iframe podle souhlasu
 */
document.addEventListener("DOMContentLoaded", function () {
  const cookieBanner = document.getElementById("cookie-banner");

  // --- Helpers: čtení a evaluace souhlasu ---
  function getConsentFromCookie() {
    function safeDecode(str) {
      if (!str) return "";

      let out = str.replace(/\+/g, "%20");
      try {
        out = decodeURIComponent(out);
      } catch (_) {}
      return out;
    }

    function tryParse(jsonLike) {
      try {
        return JSON.parse(jsonLike);
      } catch (_) {}
      try {
        return JSON.parse(safeDecode(jsonLike));
      } catch (_) {}
      return null;
    }

    try {
      const row = document.cookie
        .split("; ")
        .find((r) => r.startsWith("cookie_consent="));
      if (!row) return {};

      let raw = row.split("=")[1];
      if (!raw) return {};

      let decoded = safeDecode(raw);

      if (
        decoded.length >= 2 &&
        decoded[0] === '"' &&
        decoded[decoded.length - 1] === '"'
      ) {
        decoded = decoded.slice(1, -1);
      }

      let parsed = tryParse(decoded);
      if (!parsed) {
        decoded = safeDecode(decoded);
        parsed = tryParse(decoded);
      }
      if (!parsed || typeof parsed !== "object") return {};

      if (parsed.v) {
        const inner = tryParse(parsed.v) || {};
        return inner;
      } else {
        return parsed;
      }
    } catch (e) {
      console.error("Chyba při čtení cookie souhlasu (robustní parser):", e);
      return {};
    }
  }

  function hasCookieConsent() {
    return !!document.cookie
      .split("; ")
      .find((r) => r.startsWith("cookie_consent="));
  }
  function hasConsentFor(type) {
    const c = getConsentFromCookie();
    return c && c[type] === true;
  }

  // --- Banner show/hide ---
  function hideCookieBanner() {
    if (cookieBanner) cookieBanner.classList.add("hidden", "translate-y-full");
  }
  function showCookieBanner() {
    if (!hasCookieConsent() && cookieBanner) {
      cookieBanner.classList.remove("hidden", "translate-y-full");
    } else if (hasCookieConsent()) {
      hideCookieBanner();
    }
  }

  // --- Zachytávání akcí (UX: okamžité skrytí) ---
  function interceptCookieActions() {
    const links = document.querySelectorAll('a[href*="/cookies/"]');
    links.forEach((link) => {
      link.addEventListener("click", function () {
        const href = this.getAttribute("href") || "";
        if (
          href.includes("/accept-all") ||
          href.includes("/reject") ||
          href.includes("/save")
        ) {
          hideCookieBanner();
          localStorage.setItem("cookie_action_pending", "true");
        }
      });
    });

    const forms = document.querySelectorAll('form[action*="/cookies/"]');
    forms.forEach((form) => {
      form.addEventListener("submit", function () {
        hideCookieBanner();
        localStorage.setItem("cookie_action_pending", "true");
      });
    });
  }

  // --- Pending akce mezi reloady ---
  function checkPendingAction() {
    if (localStorage.getItem("cookie_action_pending")) {
      localStorage.removeItem("cookie_action_pending");
      hideCookieBanner();
    }
  }

  // --- Vizuální přepínače na /cookies/settings (volitelné) ---
  function activateGoldSwitches() {
    const cbs = document.querySelectorAll('input[type="checkbox"]');
    cbs.forEach((cb) => {
      if (!cb.classList.contains("sr-only") || !cb.nextElementSibling) return;

      if (cb.checked) {
        const s = cb.nextElementSibling;
        s.classList.add("bg-gradient-main");
        s.classList.remove("bg-gray-200");
      }
      cb.addEventListener("change", function () {
        const s = this.nextElementSibling;
        if (!s) return;
        if (this.checked) {
          s.classList.add("bg-gradient-main");
          s.classList.remove("bg-gray-200");
        } else {
          s.classList.remove("bg-gradient-main");
          s.classList.add("bg-gray-200");
        }
      });
    });
  }

  // --- Loader: aktivace skriptů/iframe podle consent tagů ---
  function enableConsentTaggedItems(consent) {
    document
      .querySelectorAll('script[type="text/plain"][data-consent]')
      .forEach((tag) => {
        const type = tag.getAttribute("data-consent");
        if (!consent[type]) return;

        if (tag.dataset.enabled === "1") return;
        tag.dataset.enabled = "1";

        if (tag.hasAttribute("data-src")) {
          const src = tag.getAttribute("data-src");

          if (
            document.querySelector(
              `script[data-loaded-src="${CSS.escape(src)}"]`,
            )
          )
            return;

          const s = document.createElement("script");
          s.async = false;
          s.src = src;
          s.dataset.loadedSrc = src;
          document.head.appendChild(s);
        } else if (tag.textContent.trim()) {
          const s = document.createElement("script");
          s.async = false;
          s.text = tag.textContent;
          document.head.appendChild(s);
        }
      });

    document
      .querySelectorAll("iframe[data-consent][data-src]")
      .forEach((ifr) => {
        const type = ifr.getAttribute("data-consent");
        if (consent[type]) {
          ifr.setAttribute("src", ifr.getAttribute("data-src"));
          ifr.removeAttribute("data-src");
        }
      });
  }

  // ===== INIT =====
  checkPendingAction();
  if (!localStorage.getItem("cookie_action_pending")) {
    showCookieBanner();
  }
  interceptCookieActions();
  if (document.getElementById("cookie-settings")) {
    activateGoldSwitches();
  }

  // Po načtení: promítnout consent do Consent Mode v2 a aktivovat značky
  (function () {
    const consent = getConsentFromCookie();
    if (typeof applyGtagConsent === "function") {
      applyGtagConsent(consent);
    }
    enableConsentTaggedItems(consent);
  })();

  // Globální hook pro FE i server
  window.__applyConsentEverywhere = function (newConsent) {
    try {
      if (typeof applyGtagConsent === "function") {
        applyGtagConsent(newConsent);
      }
      enableConsentTaggedItems(newConsent);
      hideCookieBanner();
    } catch (e) {
      console.error("Chyba při aplikaci souhlasu:", e);
    }
  };

  // Veřejné utility (pokud používáš jinde)
  window.hasConsentFor = hasConsentFor;
  window.hideCookieBanner = hideCookieBanner;
  window.getConsentFromCookie = getConsentFromCookie;
});
