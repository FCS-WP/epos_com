document.addEventListener("DOMContentLoaded", function () {
  const popup = document.getElementById("BlueTap-Promo");

  if (!popup) return;
  setTimeout(function () {
    popup.classList.add("show");
  }, 3000);

  document.addEventListener("click", function (e) {
    if (
      e.target.classList.contains("bluetap-promo-close") ||
      e.target.classList.contains("bluetap-promo-overlay")
    ) {
      popup.classList.remove("show");
    }
  });
});
