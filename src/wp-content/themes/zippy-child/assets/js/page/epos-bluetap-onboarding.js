document.addEventListener('click', function (e) {
  const card = e.target.closest('.bt-video-card');
  if (card) {
    const modal = document.getElementById(card.dataset.target);
    const main = document.querySelector('main');
    if (!modal) return;

    modal.classList.add('active');
    document.body.classList.add('bt-modal-open');
    main.classList.add('modal-open');

    const video = modal.querySelector('video');
    if (video) {
      video.currentTime = 0;
      video.muted = true;
      video.play().catch(() => {});
    }
  }

  const close = e.target.closest('.bt-close, .bt-video-overlay');
  if (close) {
    const modal = close.closest('.bt-video-modal');
    if (!modal) return;

    const video = modal.querySelector('video');
    if (video) {
      video.pause();
      video.currentTime = 0;
    }

    modal.classList.remove('active');
    document.body.classList.remove('bt-modal-open');
    main.classList.remove('modal-open');
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