// public/assets/js/components/Slider.js
(function (global) {
  class Slider {
    /**
     * @param {HTMLElement} root
     * @param {Object} options
     */
    constructor(root, options = {}) {
      if (!root) throw new Error("Slider: root element is required");

      this.root = root;

      // Options (lze přepsat parametrem i data-atributy)
      const dataset = root.dataset || {};

      this.mode = options.mode || dataset.sliderMode || "fade"; // "fade" | "track"
      this.intervalMs = this._toInt(options.intervalMs ?? dataset.interval ?? 5000, 5000);
      this.pauseOnHover = this._toBool(options.pauseOnHover ?? dataset.pauseOnHover ?? true);
      this.loop = this._toBool(options.loop ?? dataset.loop ?? true);

      // Selektory (defaults podle tvých atributů)
      this.selBg = options.selBg || dataset.sliderBg || "[data-slide-bg]";
      this.selText = options.selText || dataset.sliderText || "[data-slide-text]";
      this.selDots = options.selDots || dataset.sliderDots || "[data-dot]";

      // Track mode
      this.selTrack = options.selTrack || dataset.sliderTrack || "[data-slider-track]";
      this.selTrackSlides = options.selTrackSlides || dataset.sliderTrackSlide || "[data-gallery-slide]";

      // Optional controls
      this.selPrev = options.selPrev || dataset.sliderPrev || "[data-prev]";
      this.selNext = options.selNext || dataset.sliderNext || "[data-next]";

      this.idx = 0;
      this.timer = null;

      this.bgSlides = [];
      this.textSlides = [];
      this.dots = [];
      this.track = null;
      this.trackSlides = [];
      this.count = 0;

      this._onMouseEnter = () => this.stop();
      this._onMouseLeave = () => this.start();
    }

    init() {
      // Detect reduced motion
      this.reduceMotion =
        !!window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

      if (this.mode === "fade") {
        this.bgSlides = Array.from(this.root.querySelectorAll(this.selBg));
        this.textSlides = Array.from(this.root.querySelectorAll(this.selText));
        this.dots = Array.from(this.root.querySelectorAll(this.selDots));

        this.count = Math.min(this.bgSlides.length, this.textSlides.length || this.bgSlides.length, this.dots.length || this.bgSlides.length);
        if (this.count <= 1) return this;

        this._bindDots();
        this._bindHover();
        this._bindPrevNext();

        this.setActive(0, { silent: true });
        if (!this.reduceMotion) this.start();
        return this;
      }

      if (this.mode === "track") {
        this.track = this.root.querySelector(this.selTrack);
        if (!this.track) return this;

        this.trackSlides = Array.from(this.track.querySelectorAll(this.selTrackSlides));
        this.dots = Array.from(this.root.querySelectorAll(this.selDots));

        this.count = this.trackSlides.length;
        if (this.count <= 1) return this;

        this._bindDots();
        this._bindHoverTo(this.track);
        this._bindPrevNext();

        this.setActive(0, { silent: true });
        if (!this.reduceMotion) this.start();
        return this;
      }

      throw new Error("Slider: unknown mode: " + this.mode);
    }

    destroy() {
      this.stop();
      this._unbindHover();
      this._unbindDots();
      this._unbindPrevNext();
    }

    start() {
      if (this.timer || this.intervalMs <= 0) return;
      this.timer = window.setInterval(() => this.next(), this.intervalMs);
    }

    stop() {
      if (!this.timer) return;
      window.clearInterval(this.timer);
      this.timer = null;
    }

    next() {
      this.setActive(this.idx + 1);
    }

    prev() {
      this.setActive(this.idx - 1);
    }

    setActive(next, { silent = false } = {}) {
      if (this.count <= 0) return;

      let n = next;

      if (this.loop) {
        n = (n + this.count) % this.count;
      } else {
        n = Math.max(0, Math.min(this.count - 1, n));
      }

      this.idx = n;

      if (this.mode === "fade") {
        // BG fade
        for (let i = 0; i < this.bgSlides.length; i++) {
          const isActive = i === this.idx;
          this.bgSlides[i].classList.toggle("opacity-100", isActive);
          this.bgSlides[i].classList.toggle("opacity-0", !isActive);
        }

        // Text fade + slide up
        if (this.textSlides.length) {
          for (let i = 0; i < this.textSlides.length; i++) {
            const isActive = i === this.idx;
            const el = this.textSlides[i];

            el.classList.toggle("opacity-100", isActive);
            el.classList.toggle("translate-y-0", isActive);

            el.classList.toggle("opacity-0", !isActive);
            el.classList.toggle("-translate-y-4", !isActive);
            el.classList.toggle("pointer-events-none", !isActive);

            // aby se neklikalo na neaktivní text (a aby se překrývalo)
            el.classList.toggle("absolute", !isActive);
            el.classList.toggle("inset-0", !isActive);
          }
        }
      }

      if (this.mode === "track") {
        const offset = -this.idx * 100;
        this.track.style.transform = "translateX(" + offset + "%)";
      }

      // Dots – raději přes data-active, aby se ti to nepurgovalo v Tailwind build
      // (a zároveň je to použitelné na více místech)
      this.dots.forEach((dot, i) => {
        dot.dataset.active = i === this.idx ? "true" : "false";
      });

      // Custom event, kdybys chtěl navazovat animace / analytics
      if (!silent) {
        this.root.dispatchEvent(
          new CustomEvent("slider:change", { detail: { index: this.idx, count: this.count } })
        );
      }
    }

    _bindDots() {
      this._dotHandlers = [];
      this.dots.forEach((btn) => {
        const handler = () => {
          const n = this._toInt(btn.getAttribute("data-dot"), 0);
          this.setActive(n);
          this.start(); // restart autoplay po kliknutí
        };
        btn.addEventListener("click", handler);
        this._dotHandlers.push([btn, handler]);
      });
    }

    _unbindDots() {
      if (!this._dotHandlers) return;
      this._dotHandlers.forEach(([btn, handler]) => btn.removeEventListener("click", handler));
      this._dotHandlers = null;
    }

    _bindPrevNext() {
      this.prevBtn = this.root.querySelector(this.selPrev);
      this.nextBtn = this.root.querySelector(this.selNext);

      if (this.prevBtn) {
        this._prevHandler = () => {
          this.prev();
          this.start();
        };
        this.prevBtn.addEventListener("click", this._prevHandler);
      }

      if (this.nextBtn) {
        this._nextHandler = () => {
          this.next();
          this.start();
        };
        this.nextBtn.addEventListener("click", this._nextHandler);
      }
    }

    _unbindPrevNext() {
      if (this.prevBtn && this._prevHandler) this.prevBtn.removeEventListener("click", this._prevHandler);
      if (this.nextBtn && this._nextHandler) this.nextBtn.removeEventListener("click", this._nextHandler);
      this.prevBtn = null;
      this.nextBtn = null;
      this._prevHandler = null;
      this._nextHandler = null;
    }

    _bindHover() {
      if (!this.pauseOnHover) return;
      this.root.addEventListener("mouseenter", this._onMouseEnter);
      this.root.addEventListener("mouseleave", this._onMouseLeave);
    }

    _bindHoverTo(el) {
      if (!this.pauseOnHover || !el) return;
      el.addEventListener("mouseenter", this._onMouseEnter);
      el.addEventListener("mouseleave", this._onMouseLeave);
      this._hoverEl = el;
    }

    _unbindHover() {
      if (!this.pauseOnHover) return;
      this.root.removeEventListener("mouseenter", this._onMouseEnter);
      this.root.removeEventListener("mouseleave", this._onMouseLeave);
      if (this._hoverEl) {
        this._hoverEl.removeEventListener("mouseenter", this._onMouseEnter);
        this._hoverEl.removeEventListener("mouseleave", this._onMouseLeave);
        this._hoverEl = null;
      }
    }

    _toInt(v, fallback) {
      const n = parseInt(v, 10);
      return Number.isFinite(n) ? n : fallback;
    }

    _toBool(v) {
      if (typeof v === "boolean") return v;
      if (typeof v === "string") return v !== "false" && v !== "0" && v !== "";
      return !!v;
    }
  }

  global.Slider = Slider;
})(window);
