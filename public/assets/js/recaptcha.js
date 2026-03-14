document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll("form[data-contact-form]");

  forms.forEach((form) => {
    const submitBtn = form.querySelector(
      'button[type="submit"], input[type="submit"]',
    );
    const overlayEl = form.querySelector("[data-contact-overlay]");

    const msgRecaptchaUnavailable =
      form.dataset.i18nRecaptchaUnavailable || "reCAPTCHA unavailable";
    const msgRecaptchaFailed =
      form.dataset.i18nRecaptchaFailed || "reCAPTCHA failed";

    let submitting = false;
    let safetyTimer = null;

    const showMsg = (type, message, title = "") => {
      if (window.toast) {
        toast.show(message, { type, title, timeout: 4500 });
      } else {
        alert(message);
      }
    };

    const setLoading = (on) => {
      if (submitBtn) {
        submitBtn.disabled = on;
        submitBtn.classList.toggle("opacity-60", on);
        submitBtn.classList.toggle("cursor-not-allowed", on);
      }

      if (overlayEl) {
        overlayEl.classList.toggle("hidden", !on);
        overlayEl.classList.toggle("flex", on);
        overlayEl.setAttribute("aria-hidden", on ? "false" : "true");
      }

      if (safetyTimer) window.clearTimeout(safetyTimer);
      if (on) {
        safetyTimer = window.setTimeout(() => {

          setLoading(false);
          showMsg(
            "warning",
            "Odeslání trvá déle než obvykle. Zkuste to prosím znovu.",
            "Chvíli počkejte",
          );
        }, 15000);
      }
    };

    setLoading(false);

    form.addEventListener(
      "submit",
      (e) => {
        if (submitting) return;

        let hidden = form.querySelector('input[name="recaptcha_token"]');
        if (!hidden) {
          hidden = document.createElement("input");
          hidden.type = "hidden";
          hidden.name = "recaptcha_token";
          form.appendChild(hidden);
        }

        hidden.value = "";
        e.preventDefault();
        setLoading(true);

        if (typeof window.grecaptcha === "undefined") {
          setLoading(false);
          showMsg("error", msgRecaptchaUnavailable, "Ověření");
          return;
        }

        if (!window.RECAPTCHA_SITE_KEY) {
          setLoading(false);
          showMsg("error", "Chybí reCAPTCHA site key.", "Konfigurace");
          return;
        }

        window.grecaptcha.ready(() => {
          window.grecaptcha
            .execute(window.RECAPTCHA_SITE_KEY, { action: "contact" })
            .then((token) => {
              hidden.value = token;

              submitting = true;
              form.submit();
            })
            .catch(() => {
              hidden.value = "";
              setLoading(false);
              showMsg("error", msgRecaptchaFailed, "Ověření");
            });
        });
      },
      { passive: false },
    );
  });
});
