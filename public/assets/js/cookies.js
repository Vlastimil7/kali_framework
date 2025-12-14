/**
 * Cookies/banner logic + univerzální aktivace skriptů/iframe podle souhlasu
 */
document.addEventListener("DOMContentLoaded", function () {
  const cookieBanner = document.getElementById("cookie-banner");

  // --- Helpers: čtení a evaluace souhlasu ---
  function getConsentFromCookie() {
    // Bezpečné dekódování s podporou + => mezera a opakovaným decode
    function safeDecode(str) {
      if (!str) return "";
      // Některé servery zapisují mezery jako '+'
      let out = str.replace(/\+/g, "%20");
      try {
        out = decodeURIComponent(out);
      } catch (_) {}
      return out;
    }

    // Zkus JSON.parse; když to spadne, zkus znovu po extra decode
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

      // 1) první decode
      let decoded = safeDecode(raw);

      // Pokud je to ještě v uvozovkách (např. "\"%7B%22...%7D\""), sundej je
      if (
        decoded.length >= 2 &&
        decoded[0] === '"' &&
        decoded[decoded.length - 1] === '"'
      ) {
        decoded = decoded.slice(1, -1);
      }

      // 2) zkus rovnou parse
      let parsed = tryParse(decoded);
      if (!parsed) {
        // 3) někdy pomůže ještě jednou decode (double-encoding)
        decoded = safeDecode(decoded);
        parsed = tryParse(decoded);
      }
      if (!parsed || typeof parsed !== "object") return {};

      // Podporuj oba formáty:
      // a) starý: přímo {analytics:..., marketing:...}
      // b) nový podepsaný: { v: '{"analytics":...}', s: '...'}
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

    const form = document.querySelector('form[action*="/cookies/save"]');
    if (form) {
      form.addEventListener("submit", function () {
        hideCookieBanner();
        localStorage.setItem("cookie_action_pending", "true");
      });
    }
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
        s.style.backgroundColor = "var(--color-indigo)";
        s.classList.remove("bg-gray-200");
      }
      cb.addEventListener("change", function () {
        const s = this.nextElementSibling;
        if (!s) return;
        if (this.checked) {
          s.style.backgroundColor = "var(--color-indigo)";
          s.classList.remove("bg-gray-200");
        } else {
          s.style.backgroundColor = "";
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

        if (tag.hasAttribute("data-src")) {
          const s = document.createElement("script");
          s.async = true;
          s.src = tag.getAttribute("data-src");
          document.head.appendChild(s);
        } else if (tag.textContent.trim()) {
          const s = document.createElement("script");
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
