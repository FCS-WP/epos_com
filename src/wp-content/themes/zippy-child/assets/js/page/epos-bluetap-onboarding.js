document.addEventListener("click", function (e) {
  const card = e.target.closest(".bt-video-card");
  const close = e.target.closest(".bt-close, .bt-video-overlay");
  const main = document.querySelector("main");

  // OPEN MODAL
  if (card) {
    const modal = document.getElementById(card.dataset.target);
    if (!modal) return;

    modal.classList.add("active");
    document.body.classList.add("bt-modal-open");
    if (main) main.classList.add("modal-open");

    const video = modal.querySelector("video");
    if (video) {
      video.currentTime = 0;
      video.muted = false;
      video.volume = 1;
      video.play().catch(() => {});
    }
    return;
  }

  if (close) {
    const modal = close.closest(".bt-video-modal");
    if (!modal) return;

    const video = modal.querySelector("video");
    if (video) {
      video.pause();
      video.currentTime = 0;
      video.muted = true;
    }

    modal.classList.remove("active");
    document.body.classList.remove("bt-modal-open");
    if (main) main.classList.remove("modal-open");
  }
});

document.querySelectorAll(".bt-chapter-header").forEach((header) => {
  const card = header.closest(".bt-chapter-card");

  if (!card.querySelector(".bt-chapter-body")) return;

  card.classList.add("has-body");

  header.addEventListener("click", () => {
    document
      .querySelectorAll(".bt-chapter-card.has-body.expanded")
      .forEach((openCard) => {
        if (openCard !== card) openCard.classList.remove("expanded");
      });

    card.classList.toggle("expanded");
  });
});
