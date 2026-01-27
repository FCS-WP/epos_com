// Scroll To Menu toggle
document.addEventListener("DOMContentLoaded", function () {
  if (
    window.location.pathname.includes("/bluetap-faq") ||
    window.location.pathname.includes("/bluetap-onboarding")
  ) {
    let observer = new MutationObserver(function () {
      const shortcutMenu = document.querySelector(".shortcut-menu");
      if (!shortcutMenu) return;
      observer.disconnect();
      shortcutMenu.classList.add("is-hidden");

      const btnMenu = document.createElement("button");
      btnMenu.className = "scroll-bullets-toggle";
      btnMenu.innerHTML = "ⓘ Guides <span class='down-icon'>▼</span>";
      document.body.appendChild(btnMenu);

      const showMenu = () => {
        shortcutMenu.classList.remove("is-hidden");
        btnMenu.classList.add("is-active");
      };

      const hideMenu = () => {
        shortcutMenu.classList.add("is-hidden");
        btnMenu.classList.remove("is-active");
      };

      // breakpoint handle
      if (window.innerWidth <= 768) {
        btnMenu.addEventListener("click", function (e) {
          shortcutMenu.classList.toggle("is-hidden");
          btnMenu.classList.toggle("is-active");
        });

        document.addEventListener("click", function (e) {
          if (!shortcutMenu.contains(e.target) && !btnMenu.contains(e.target)) {
            hideMenu();
          }
        });
      } else {
        btnMenu.addEventListener("mouseenter", showMenu);
        btnMenu.addEventListener("mouseleave", hideMenu);
        shortcutMenu.addEventListener("mouseenter", showMenu);
        shortcutMenu.addEventListener("mouseleave", hideMenu);
      }
    });

    observer.observe(document.body, { childList: true, subtree: true });
  }
});
