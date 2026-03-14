class FilterGridModal {
  constructor(root) {
    this.root = root;

    this.modal = root.querySelector("[data-modal]");
    this.overlay = root.querySelector("[data-modal-overlay]");
    this.btnClose = root.querySelector("[data-modal-close]");
    this.btnPrev = root.querySelector("[data-modal-prev]");
    this.btnNext = root.querySelector("[data-modal-next]");
    this.elTitle = root.querySelector("[data-modal-title]");
    this.elDesc = root.querySelector("[data-modal-desc]");
    this.elImg = root.querySelector("[data-modal-img]");

    this.items = Array.from(root.querySelectorAll("[data-item]"));
    if (!this.modal || !this.items.length) return;

    this.openIndex = -1;

    this.onKeyDown = this.onKeyDown.bind(this);
    this.close = this.close.bind(this);
    this.prev = this.prev.bind(this);
    this.next = this.next.bind(this);

    this.items.forEach((item, i) => {
      item.addEventListener("click", () => this.open(i));
      item.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          this.open(i);
        }
      });
    });

    this.overlay?.addEventListener("click", this.close);
    this.btnClose?.addEventListener("click", this.close);
    this.btnPrev?.addEventListener("click", this.prev);
    this.btnNext?.addEventListener("click", this.next);
  }

  getVisibleItems() {
    return this.items.filter((el) => !el.classList.contains("hidden"));
  }

  open(indexInAll) {
    const visible = this.getVisibleItems();
    const item = this.items[indexInAll];

    if (!item || item.classList.contains("hidden")) {
      if (!visible.length) return;
      this.openIndex = this.items.indexOf(visible[0]);
    } else {
      this.openIndex = indexInAll;
    }

    this.render();
    this.modal.classList.remove("hidden");
    this.modal.setAttribute("aria-hidden", "false");
    document.addEventListener("keydown", this.onKeyDown);
    document.documentElement.classList.add("overflow-hidden");
  }

  close() {
    this.modal.classList.add("hidden");
    this.modal.setAttribute("aria-hidden", "true");
    document.removeEventListener("keydown", this.onKeyDown);
    document.documentElement.classList.remove("overflow-hidden");
    this.openIndex = -1;
  }

  prev() {
    const visible = this.getVisibleItems();
    if (!visible.length) return;

    const currentEl = this.items[this.openIndex];
    let pos = visible.indexOf(currentEl);
    if (pos === -1) pos = 0;

    pos = (pos - 1 + visible.length) % visible.length;
    this.openIndex = this.items.indexOf(visible[pos]);
    this.render();
  }

  next() {
    const visible = this.getVisibleItems();
    if (!visible.length) return;

    const currentEl = this.items[this.openIndex];
    let pos = visible.indexOf(currentEl);
    if (pos === -1) pos = 0;

    pos = (pos + 1) % visible.length;
    this.openIndex = this.items.indexOf(visible[pos]);
    this.render();
  }

  onKeyDown(e) {
    if (e.key === "Escape") return this.close();
    if (e.key === "ArrowLeft") return this.prev();
    if (e.key === "ArrowRight") return this.next();
  }

  render() {
    const item = this.items[this.openIndex];
    if (!item) return;

    const title = item.getAttribute("data-title") || "Detail";
    const desc = item.getAttribute("data-desc") || "";
    const img = item.getAttribute("data-img") || "";

    if (this.elTitle) this.elTitle.textContent = title;
    if (this.elDesc) this.elDesc.textContent = desc;

    if (this.elImg) {
      if (img) {
        this.elImg.src = img;
        this.elImg.alt = title;
      } else {
        this.elImg.src =
          "data:image/svg+xml;charset=utf-8," +
          encodeURIComponent(
            `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="750">
              <defs><linearGradient id="g" x1="0" x2="1">
                <stop stop-color="#0f172a"/><stop offset="1" stop-color="#334155"/>
              </linearGradient></defs>
              <rect width="100%" height="100%" fill="url(#g)"/>
              <text x="50%" y="50%" fill="#ffffff" font-family="Arial" font-size="44" text-anchor="middle">${title}</text>
            </svg>`,
          );
        this.elImg.alt = title;
      }
    }
  }

  static initAll() {
    document.querySelectorAll("[data-filter-grid]").forEach((root) => {
      if (root.__filterGridModal) return;
      root.__filterGridModal = new FilterGridModal(root);
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  FilterGridModal.initAll();
});
