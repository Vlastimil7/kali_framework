class FilterGrid {
  constructor(root) {
    this.root = root;
    this.defaultFilter = root.getAttribute("data-default") || "vse";

    this.btnWrap = root.querySelector("[data-filter-buttons]");
    this.buttons = Array.from(root.querySelectorAll("[data-filter]"));
    this.items = Array.from(root.querySelectorAll("[data-item]"));

    if (!this.buttons.length || !this.items.length) return;

    this.active = this.defaultFilter;

    this.onClick = this.onClick.bind(this);

    this.buttons.forEach((b) => b.addEventListener("click", this.onClick));

    // init
    this.setActive(this.active, false);
  }

  onClick(e) {
    const btn = e.currentTarget;
    const filter = btn.getAttribute("data-filter");
    if (!filter) return;
    this.setActive(filter, true);
  }

  setActive(filter, animate) {
    this.active = filter;

    // buttons style
    this.buttons.forEach((b) => {
      const isActive = b.getAttribute("data-filter") === filter;

      b.classList.toggle("bg-[var(--color-red)]", isActive);
      b.classList.toggle("text-white", isActive);
      b.classList.toggle("border-[var(--color-red)]", isActive);

      b.classList.toggle("bg-white", !isActive);
      b.classList.toggle("text-slate-700", !isActive);
      b.classList.toggle("border-slate-300", !isActive);
    });

    // items
    this.items.forEach((item) => {
      const cat = item.getAttribute("data-cat");
      const show = filter === "vse" || cat === filter;

      if (animate) {
        item.style.transition = "opacity 250ms ease, transform 250ms ease";
      }

      if (show) {
        item.classList.remove("hidden");
        // allow layout first
        requestAnimationFrame(() => {
          item.style.opacity = "1";
          item.style.transform = "translateY(0)";
        });
      } else {
        item.style.opacity = "0";
        item.style.transform = "translateY(6px)";
        // after fade-out, hide
        setTimeout(() => item.classList.add("hidden"), animate ? 220 : 0);
      }
    });
  }

  destroy() {
    this.buttons.forEach((b) => b.removeEventListener("click", this.onClick));
  }

  static initAll() {
    document.querySelectorAll("[data-filter-grid]").forEach((root) => {
      // prevent double init
      if (root.__filterGrid) return;
      root.__filterGrid = new FilterGrid(root);
    });
  }
}

// auto init
document.addEventListener("DOMContentLoaded", () => {
  FilterGrid.initAll();
});
