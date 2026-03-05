document.querySelectorAll(".lang-btn").forEach((btn) => {
  if (btn.classList.contains("active")) {
    return;
  }
  btn.addEventListener("click", function (e) {
    e.preventDefault();
    const lang = this.dataset.lang;

    fetch("/wp-admin/admin-ajax.php", {
      method: "POST",
      body: new URLSearchParams({
        action: "set_lang",
        lang: lang,
      }),
    }).then(() => {
      window.location.reload();
      this.classList.add("active");
      document.querySelectorAll(".lang-btn").forEach((b) => b.classList.remove("active"));
    });
  });
});
