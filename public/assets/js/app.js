(() => {
  "use strict";

  const THEME_KEY = "sme_theme";

  window.SME = {
    notify(message, type = "info") {
      const notification = document.createElement("div");
      notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
      notification.style.cssText =
        "top: 20px; right: 20px; z-index: 9999; min-width: 280px; max-width: calc(100vw - 2rem);";
      notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
      document.body.appendChild(notification);
      setTimeout(() => notification.remove(), 5000);
    },

    initTheme() {
      const saved = localStorage.getItem(THEME_KEY);
      const prefersDark =
        window.matchMedia &&
        window.matchMedia("(prefers-color-scheme: dark)").matches;
      const theme = saved || (prefersDark ? "dark" : "light");
      document.documentElement.setAttribute("data-theme", theme);

      document.querySelectorAll(".theme-toggle-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
          const next =
            document.documentElement.getAttribute("data-theme") === "dark"
              ? "light"
              : "dark";
          document.documentElement.setAttribute("data-theme", next);
          localStorage.setItem(THEME_KEY, next);
          const icon = btn.querySelector("i");
          if (icon) {
            icon.className =
              next === "dark" ? "fas fa-sun" : "fas fa-moon";
          }
        });
        const icon = btn.querySelector("i");
        if (icon) {
          icon.className =
            theme === "dark" ? "fas fa-sun" : "fas fa-moon";
        }
      });
    },

    initSidebar() {
      const sidebar = document.getElementById("sidebar");
      const overlay = document.getElementById("sidebarOverlay");
      const toggles = document.querySelectorAll(
        "#sidebarToggle, .sidebar-toggle-btn"
      );

      if (!sidebar) return;

      const closeSidebar = () => {
        sidebar.classList.remove("open");
        overlay?.classList.remove("show");
        document.body.classList.remove("sidebar-open");
      };

      const openSidebar = () => {
        sidebar.classList.add("open");
        overlay?.classList.add("show");
        document.body.classList.add("sidebar-open");
      };

      const toggleSidebar = () => {
        if (sidebar.classList.contains("open")) {
          closeSidebar();
        } else {
          openSidebar();
        }
      };

      toggles.forEach((btn) => btn.addEventListener("click", toggleSidebar));
      overlay?.addEventListener("click", closeSidebar);

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeSidebar();
      });

      sidebar.querySelectorAll(".sidebar-link, .sidebar-footer-link").forEach((link) => {
        link.addEventListener("click", () => {
          if (window.innerWidth < 992) closeSidebar();
        });
      });

      window.addEventListener("resize", () => {
        if (window.innerWidth >= 992) closeSidebar();
      });
    },

    initScrollTop() {
      const btn = document.getElementById("scrollTopBtn");
      if (!btn) return;

      window.addEventListener(
        "scroll",
        () => {
          btn.classList.toggle("visible", window.scrollY > 400);
        },
        { passive: true }
      );

      btn.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
      });
    },

    initAnimations() {
      if (!("IntersectionObserver" in window)) return;
      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) entry.target.classList.add("animate-fade-in");
          });
        },
        { threshold: 0.1, rootMargin: "0px 0px -40px 0px" }
      );
      document.querySelectorAll(".stat-card, .card, .skill-card").forEach((el) => observer.observe(el));
    },

    initTooltips() {
      if (typeof bootstrap === "undefined") return;
      document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
        new bootstrap.Tooltip(el);
      });
    },

    initFormLoading() {
      document.querySelectorAll("form").forEach((form) => {
        form.addEventListener("submit", (e) => {
          const sub = e.submitter;
          let btn = null;
          if (
            sub &&
            sub.matches("button, input") &&
            sub.classList.contains("btn") &&
            !sub.classList.contains("no-loading")
          ) {
            btn = sub;
          } else {
            btn = form.querySelector(
              "button[type=\"submit\"].btn:not(.no-loading), input[type=\"submit\"].btn:not(.no-loading)"
            );
          }
          if (!btn) return;
          setTimeout(() => {
            if (btn.disabled) return;
            btn.disabled = true;
            if (btn.tagName === "INPUT") {
              btn.value = "Loading...";
            } else {
              btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            }
          }, 0);
        });
      });
    },

    initConfirm() {
      document.querySelectorAll("[data-confirm]").forEach((el) => {
        el.addEventListener("click", function (e) {
          if (!confirm(this.getAttribute("data-confirm") || "Are you sure?")) {
            e.preventDefault();
          }
        });
      });
    },

    init() {
      this.initTheme();
      this.initSidebar();
      this.initScrollTop();
      this.initAnimations();
      this.initTooltips();
      this.initFormLoading();
      this.initConfirm();
    },
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => window.SME.init());
  } else {
    window.SME.init();
  }
})();
