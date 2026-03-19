(() => {
  "use strict";

  function ready(fn) {
    if (document.readyState !== "loading") fn();
    else document.addEventListener("DOMContentLoaded", fn);
  }

  function setActiveTabByUrl() {
    // Marca automaticamente a aba ativa conforme a URL
    const path = window.location.pathname.toLowerCase();
    const tabs = document.querySelectorAll(".tabs .tab");
    if (!tabs.length) return;

    tabs.forEach(t => t.classList.remove("active"));

    const match = (p) => path.endsWith(p);

    let activeSelector = null;
    if (match("/people.php")) activeSelector = '.tabs a[href="/people.php"]';
    else if (match("/attendance.php")) activeSelector = '.tabs a[href="/attendance.php"]';
    else if (match("/history.php")) activeSelector = '.tabs a[href="/history.php"]';
    else if (match("/dashboard.php")) activeSelector = null;

    if (activeSelector) {
      const el = document.querySelector(activeSelector);
      if (el) el.classList.add("active");
    }
  }

  function wireDeleteConfirm() {
    // Confirma exclusão para links com .delete-btn
    document.querySelectorAll('a.delete-btn, button.delete-btn').forEach(el => {
      el.addEventListener("click", (e) => {
        // se já existe onclick confirm no HTML, isso aqui é redundante,
        // mas garante caso você remova do HTML.
        const msg = el.dataset.confirm || "Tem certeza que deseja excluir? Essa ação não pode ser desfeita.";
        const ok = window.confirm(msg);
        if (!ok) e.preventDefault();
      });
    });
  }

  function autoSubmitSelect() {
    // Para selects com atributo data-autosubmit="1"
    document.querySelectorAll('select[data-autosubmit="1"]').forEach(sel => {
      sel.addEventListener("change", () => {
        const form = sel.closest("form");
        if (form) form.submit();
      });
    });
  }

  function improveSearchEnterSubmit() {
    // Se quiser: enter no campo de busca submete o form
    document.querySelectorAll(".search-bar").forEach(bar => {
      const input = bar.querySelector('input[type="text"]');
      const form = bar.closest("form");
      if (!input || !form) return;
      input.addEventListener("keydown", (e) => {
        if (e.key === "Enter") form.submit();
      });
    });
  }

  ready(() => {
    setActiveTabByUrl();
    wireDeleteConfirm();
    autoSubmitSelect();
    improveSearchEnterSubmit();
  });
})();