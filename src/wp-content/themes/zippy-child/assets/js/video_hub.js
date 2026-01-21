document.addEventListener("DOMContentLoaded", function () {
  const video = document.getElementById("myVideo");
  const playBtn = document.getElementById("playBtn");
  const card = document.getElementById("videoCard");
  let loaded = false;

  function loadAndPlay() {
    if (!loaded) {
      const src = video.dataset.src;
      if (src) {
        const source = document.createElement("source");
        source.src = src;
        source.type = "video/mp4";
        video.appendChild(source);
        loaded = true;
        video.load();
      }
    }
    video.controls = true;
    const playPromise = video.play();
    if (playPromise) {
      playPromise.catch(() => (video.controls = true));
    }
    card.classList.add("playing");
  }

  function resetToThumbnail() {
    video.pause();
    video.currentTime = 0;
    video.controls = false;
    video.load();
    card.classList.remove("playing");
  }

  playBtn.addEventListener("click", (e) => {
    e.preventDefault();
    loadAndPlay();
  });

  playBtn.addEventListener("keydown", (e) => {
    if (e.key === "Enter" || e.key === " ") {
      e.preventDefault();
      loadAndPlay();
    }
  });

  video.addEventListener("ended", resetToThumbnail);
  video.addEventListener("pause", () => {
    if (video.currentTime === 0 || video.currentTime >= video.duration - 0.1) {
      resetToThumbnail();
    }
  });

  video.controls = false;
});
