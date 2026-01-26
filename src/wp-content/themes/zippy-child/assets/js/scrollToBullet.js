// Scroll To Menu toggle
document.addEventListener("DOMContentLoaded", function () {
  if (
    window.location.pathname.includes("/bluetap-faq") ||
    window.location.pathname.includes("/bluetap-onboarding")
  ) {
    let observer = new MutationObserver(function () {
      let shortcutMenu = document.querySelector(".shortcut-menu"); 
      if (!shortcutMenu) return;
      observer.disconnect();
      shortcutMenu.classList.add("is-hidden");

      let btnMenu = document.createElement("button");
      btnMenu.className = "scroll-bullets-toggle";
      btnMenu.innerHTML = "ⓘ Shortcuts";
      document.body.appendChild(btnMenu); 

      btnMenu.addEventListener("click", function (e) {
        e.stopPropagation();
        shortcutMenu.classList.toggle("is-hidden");
        btnMenu.classList.toggle("is-active");
      });

      document.addEventListener("click", function (e) {
        if (!shortcutMenu.contains(e.target) && !btnMenu.contains(e.target)) {
          shortcutMenu.classList.add("is-hidden");
          btnMenu.classList.remove("is-active");
        }
      });
    });

    observer.observe(document.body, { childList: true, subtree: true });
  }
});
