(function () {
  const CONTAINER_PREFIX = "toast-container";

  // Tailwind pozice pro container
  const POSITIONS = {
    "top-right": "top-4 right-4 items-end",
    "top-left": "top-4 left-4 items-start",
    "top-center": "top-4 left-1/2 -translate-x-1/2 items-center",
    "bottom-right": "bottom-4 right-4 items-end",
    "bottom-left": "bottom-4 left-4 items-start",
    "bottom-center": "bottom-4 left-1/2 -translate-x-1/2 items-center",
    center: "top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 items-center",
  };

  function ensureContainer(position = "top-right") {
    const posKey = POSITIONS[position] ? position : "top-right";
    const id = `${CONTAINER_PREFIX}-${posKey}`;

    let el = document.getElementById(id);
    if (el) return el;

    el = document.createElement("div");
    el.id = id;

    // container: fixed + stack + pointer-events none (kliknutí bere toast samotný)
    el.className = [
      "fixed z-[10000] flex flex-col gap-3 pointer-events-none",
      POSITIONS[posKey],
    ].join(" ");

    el.setAttribute("aria-live", "polite");
    el.setAttribute("aria-atomic", "true");

    document.body.appendChild(el);
    return el;
  }

  function icon(type) {
    switch (type) {
      case "success":
        return "✓";
      case "error":
        return "✕";
      case "warning":
        return "!";
      default:
        return "ℹ";
    }
  }

  function styles(type) {
    switch (type) {
      case "success":
        return "border-green-400/40 bg-green-500/10 text-green-100";
      case "error":
        return "border-red-400/40 bg-red-500/10 text-red-100";
      case "warning":
        return "border-yellow-400/40 bg-yellow-500/10 text-yellow-100";
      default:
        return "border-cyan-400/40 bg-cyan-500/10 text-cyan-100";
    }
  }

  function createToast({
    message,
    type = "info",
    title = "",
    timeout = 3500,
    closable = true,
    position = "top-right",
  }) {
    const container = ensureContainer(position);

    const el = document.createElement("div");
    el.className = [
      "pointer-events-auto",
      "rounded-2xl border shadow-2xl backdrop-blur",
      "px-4 py-3",
      "flex gap-3 items-start",
      "max-w-[22rem] w-full", // aby to nebylo moc široké
      "animate-[toastIn_.18s_ease-out]",
      styles(type),
    ].join(" ");

    el.innerHTML = `
      <div class="mt-0.5 text-lg font-extrabold">${icon(type)}</div>
      <div class="min-w-0 flex-1">
        ${title ? `<div class="text-sm font-bold text-white">${escapeHtml(title)}</div>` : ""}
        <div class="text-sm leading-snug text-white/90">${escapeHtml(message)}</div>
      </div>
      ${closable ? `<button type="button" class="ml-1 text-xl leading-none opacity-70 hover:opacity-100 cursor-pointer">&times;</button>` : ""}
    `;

    function remove() {
      el.classList.remove("animate-[toastIn_.18s_ease-out]");
      el.classList.add("animate-[toastOut_.18s_ease-in]");
      window.setTimeout(() => el.remove(), 160);
    }

    if (closable) {
      el.querySelector("button")?.addEventListener("click", remove);
    }

    if (timeout > 0) window.setTimeout(remove, timeout);

    container.appendChild(el);
    return { el, remove };
  }

  function escapeHtml(s) {
    return String(s)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  // Public API
  window.toast = {
    show: (message, opts = {}) => createToast({ message, ...opts }),
    success: (message, opts = {}) =>
      createToast({ message, type: "success", ...opts }),
    error: (message, opts = {}) =>
      createToast({ message, type: "error", ...opts }),
    warning: (message, opts = {}) =>
      createToast({ message, type: "warning", ...opts }),
    info: (message, opts = {}) =>
      createToast({ message, type: "info", ...opts }),
  };
  // flush queued toasts (rendered before toast.js loaded)
  if (Array.isArray(window.__toastQueue)) {
    window.__toastQueue.forEach((t) => {
      window.toast.show(t.message || "", {
        type: t.type || "info",
        title: t.title || "",
        position: t.position || "top-right",
        timeout: t.timeout || 7000,
      });
    });
    window.__toastQueue = [];
  }
})();
