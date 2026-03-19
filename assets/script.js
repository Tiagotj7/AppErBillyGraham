(() => {
  "use strict";

  function ready(fn) {
    if (document.readyState !== "loading") fn();
    else document.addEventListener("DOMContentLoaded", fn);
  }

  // Confirmar ações destrutivas
  function wireConfirm() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
      el.addEventListener("click", (e) => {
        const msg = el.getAttribute("data-confirm") || "Confirmar ação?";
        if (!window.confirm(msg)) e.preventDefault();
      });
    });
  }

  // Auto submit em selects
  function autoSubmitSelect() {
    document.querySelectorAll('select[data-autosubmit="1"]').forEach(sel => {
      sel.addEventListener("change", () => {
        const form = sel.closest("form");
        if (form) form.submit();
      });
    });
  }

  // Loading overlay (cria dinamicamente)
  function ensureLoadingOverlay() {
    if (document.querySelector(".loading-overlay")) return;

    const overlay = document.createElement("div");
    overlay.className = "loading-overlay";
    overlay.innerHTML = `
      <div class="loading-box">
        <div class="spinner"></div>
        <div>Carregando...</div>
      </div>
    `;
    document.body.appendChild(overlay);
  }

  function showLoading() {
    ensureLoadingOverlay();
    document.querySelector(".loading-overlay").classList.add("show");
  }

  function hideLoading() {
    const el = document.querySelector(".loading-overlay");
    if (el) el.classList.remove("show");
  }

  // Mostra loading ao enviar forms (sem exagero)
  function wireFormLoading() {
    const forms = document.querySelectorAll("form");
    forms.forEach(form => {
      form.addEventListener("submit", () => {
        // só mostra loading se não for GET simples de navegação (opcional)
        // Se quiser mostrar também no GET, remova este if:
        const method = (form.getAttribute("method") || "get").toLowerCase();
        if (method === "post") showLoading();
      });
    });

    // Se a navegação voltar do cache, remove overlay
    window.addEventListener("pageshow", () => hideLoading());
  }

  ready(() => {
    wireConfirm();
    autoSubmitSelect();
    wireFormLoading();
  });
})();