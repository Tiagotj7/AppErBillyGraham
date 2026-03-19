(() => {
  "use strict";

  function ready(fn) {
    if (document.readyState !== "loading") fn();
    else document.addEventListener("DOMContentLoaded", fn);
  }

  function wireDeleteConfirm() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
      el.addEventListener("click", (e) => {
        const msg = el.getAttribute("data-confirm") || "Confirmar ação?";
        if (!window.confirm(msg)) e.preventDefault();
      });
    });
  }

  function autoSubmitSelect() {
    document.querySelectorAll('select[data-autosubmit="1"]').forEach(sel => {
      sel.addEventListener("change", () => {
        const form = sel.closest("form");
        if (form) form.submit();
      });
    });
  }

  ready(() => {
    wireDeleteConfirm();
    autoSubmitSelect();
  });
})();