window.addEventListener("load", () => {
  gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

  initAutoSlider();

  if (window.innerWidth < 992) {
    SectionManageMobilePhoneOnly();
    return;
  }
  HeroBannerScrollMotion();
  SectionSell();
  // SectionSave();
  SectionManage();
  // SectionLoan();
  SectionGrowIntro();
  ScrollTrigger.refresh();
});

/*
BANNER AUTO SLIDER */
function initAutoSlider() {
  gsap.set(".epos360-banner img", {
    force3D: true,
    willChange: "transform, opacity",
  });

  gsap.set(".epos360-banner .image-center-2", {
    autoAlpha: 0,
    yPercent: 15,
  });

  const tl = gsap.timeline({
    repeat: -1,
    repeatDelay: 1.2,
    paused: true,
    defaults: { ease: "power3.inOut" },
  });

  tl.to(".epos360-banner .image-center-1", {
    autoAlpha: 0,
    yPercent: -15,
    duration: 0.5,
  })
    .fromTo(
      ".epos360-banner .image-center-2",
      { autoAlpha: 0, yPercent: 15 },
      { autoAlpha: 1, yPercent: 0, duration: 0.6 },
      "<0.1",
    )
    .to({}, { duration: 1.2 })
    .to(".epos360-banner .image-center-2", {
      autoAlpha: 0,
      yPercent: -15,
      duration: 0.5,
    })
    .fromTo(
      ".epos360-banner .image-center-1",
      { autoAlpha: 0, yPercent: 15 },
      { autoAlpha: 1, yPercent: 0, duration: 0.6 },
      "<0.1",
    )
    .to({}, { duration: 2 });

  ScrollTrigger.create({
    trigger: ".epos360-banner",
    start: "top 70%",
    end: "bottom 30%",
    onEnter: () => tl.restart(true),
    onEnterBack: () => tl.restart(true),
    onLeave: () => tl.pause(0),
    onLeaveBack: () => tl.pause(0),
  });
}
function HeroBannerScrollMotion() {
  const section = document.querySelector(".epos360-banner");
  if (!section) return;

  const leftItems = section.querySelectorAll(
    ".image-absolute-left-1, .image-absolute-left-2, .image-absolute-left-3",
  );
  const rightItems = section.querySelectorAll(
    ".image-absolute-right-1, .image-absolute-right-2, .image-absolute-right-3",
  );

  /* ================= INTRO: BUNG RA ================= */
  const introTl = gsap.timeline({
    delay: 0.8,
  });

  introTl
    .to(leftItems, {
      autoAlpha: 1,
      visibility: "visible",
      x: 0,
      scale: 1,
      duration: 0.9,
      ease: "power3.out",
      stagger: 0.08,
    })
    .to(
      rightItems,
      {
        autoAlpha: 1,
        visibility: "visible",
        x: 0,
        scale: 1,
        duration: 0.9,
        ease: "power3.out",
        stagger: 0.08,
      },
      "<",
    );

  /* ================= SCROLL: THU LẠI ================= */
  gsap
    .timeline({
      scrollTrigger: {
        trigger: section,
        start: "top top",
        end: "bottom top",
        scrub: 1.2,
      },
    })
    .to(leftItems, {
      x: 120,
      scale: 0.85,
      autoAlpha: 0,
      ease: "power2.inOut",
    })
    .to(
      rightItems,
      {
        x: -120,
        scale: 0.85,
        autoAlpha: 0,
        ease: "power2.inOut",
      },
      "<",
    );
}

/*
SECTION SELL */
function SectionSell() {
  const s = document.querySelector(".s1");
  if (!s) return;

  const text = s.querySelector(".text-info");
  const image = s.querySelector(".image-center");
  const abs = s.querySelectorAll(
    ".sell-absolute-left, .sell-absolute-center, .sell-absolute-right",
  );

  /* ================= INIT ================= */
  gsap.set(s, { autoAlpha: 0 });
  gsap.set([text, image, abs], { autoAlpha: 0 });

  /* ================= INTRO TIMELINE ================= */
  const introTl = gsap.timeline({ paused: true });

  introTl
    .to({}, { duration: 0.4 })
    .fromTo(
      text,
      { autoAlpha: 0, scale: 0.94 },
      { autoAlpha: 1, scale: 1, duration: 0.6, ease: "power3.out" },
    )
    .fromTo(
      image,
      { autoAlpha: 0, scale: 0.94 },
      { autoAlpha: 1, scale: 1, duration: 0.6, ease: "power3.out" },
      "<",
    )
    .fromTo(
      abs,
      { autoAlpha: 0, y: 8 },
      {
        autoAlpha: 1,
        y: 0,
        duration: 0.35,
        stagger: 0.06,
        ease: "power2.out",
      },
    );

  /* ================= SCROLL TRIGGER ================= */
  ScrollTrigger.create({
    trigger: s,
    start: "top 70%",
    end: "bottom 30%",

    // 👉 SCROLL VÀO
    onEnter() {
      gsap.to(s, {
        autoAlpha: 1,
        duration: 0.5,
        ease: "power2.out",
      });
      introTl.restart();
    },

    onEnterBack() {
      gsap.to(s, {
        autoAlpha: 1,
        duration: 0.5,
        ease: "power2.out",
      });
      introTl.restart();
    },
    onLeave() {
      gsap.to(s, {
        autoAlpha: 0,
        duration: 0.6,
        ease: "power2.inOut",
      });
    },

    onLeaveBack() {
      gsap.to(s, {
        autoAlpha: 0,
        duration: 0.6,
        ease: "power2.inOut",
      });
    },
  });
}

/*
SECTION SAVE */
function SectionSave() {
  const s = document.querySelector(".s2");
  if (!s) return;

  const text = s.querySelector(".text-info");
  const image = s.querySelector(".image-center");
  const abs = s.querySelectorAll(
    ".save-absolute-left.v1, .save-absolute-right.v1, .save-absolute-right-2.v1",
  );

  gsap.set(s, { autoAlpha: 0 });
  gsap.set([text, image, abs], { autoAlpha: 0 });
  gsap.set(text, { y: 20 });
  gsap.set(image, { scale: 0.94 });
  gsap.set(abs, { y: 28 });

  const introTl = gsap.timeline({ paused: true });

  introTl
    .to({}, { duration: 0.6 })
    .fromTo(
      text,
      { autoAlpha: 0, y: 20 },
      { autoAlpha: 1, y: 0, duration: 0.6 },
    )
    .fromTo(
      image,
      { autoAlpha: 0, scale: 0.94 },
      { autoAlpha: 1, scale: 1, duration: 0.6 },
      "<",
    )
    .fromTo(
      abs,
      { autoAlpha: 0, y: 10 },
      { autoAlpha: 1, y: 0, duration: 0.35, stagger: 0.08 },
      "<0.1",
    );

  ScrollTrigger.create({
    trigger: s,
    start: "top 80%",
    end: "bottom 30%",

    onEnter() {
      gsap.to(s, { autoAlpha: 1, duration: 0.5, ease: "power2.out" });
      introTl.restart();
    },

    onEnterBack() {
      gsap.to(s, { autoAlpha: 1, duration: 0.5, ease: "power2.out" });
      introTl.restart();
    },

    onLeave() {
      gsap.to(s, { autoAlpha: 0, duration: 0.6, ease: "power2.inOut" });
    },

    onLeaveBack() {
      gsap.to(s, { autoAlpha: 0, duration: 0.6, ease: "power2.inOut" });
    },
  });
}

/*
SECTION MANAGE */
function SectionManage() {
  const section = document.querySelector(".s3");
  if (!section) return;

  const isMobile = window.innerWidth < 992;

  const text = section.querySelector(".text-info");
  const frame = section.querySelector(".phone-wrapper");
  const absolutes = section.querySelectorAll(
    ".manage-absolute-left.v1, .manage-absolute-right.v1, .manage-absolute-right.v2",
  );

  const headerItems = section.querySelectorAll(".screen-header-item");
  const bodyItems = section.querySelectorAll(".screen-body-item");

  const tabOrder = ["g1", "g2", "g3"];
  let currentTab = 0;
  let loopTl = null;

  const HOLD_TIME = 2; // 2000ms
  const ANIM_TIME = 1.2;

  /* ================= INIT ================= */
  gsap.set(section, { autoAlpha: 0 });
  gsap.set(frame, { autoAlpha: 0, scale: 0.92 });

  // ❗ Desktop only
  if (!isMobile) {
    gsap.set(text, { autoAlpha: 0, y: 20 });
    gsap.set(absolutes, { autoAlpha: 0, y: 30 });
  }

  gsap.set(headerItems, { autoAlpha: 0 });
  gsap.set(bodyItems, { autoAlpha: 0, xPercent: 100 });

  /* ================= TAB CORE ================= */
  function initFirstTab() {
    currentTab = 0;

    headerItems.forEach((h) => gsap.set(h, { autoAlpha: 0 }));
    bodyItems.forEach((b) => gsap.set(b, { autoAlpha: 0, xPercent: 100 }));

    gsap.set(section.querySelector(".screen-header-item.g1"), { autoAlpha: 1 });
    gsap.set(section.querySelector(".screen-body-item.g1"), {
      autoAlpha: 1,
      xPercent: 0,
    });
  }

  function switchTab(tabName) {
    headerItems.forEach((h) => {
      const active = h.classList.contains(tabName);
      gsap.to(h, {
        autoAlpha: active ? 1 : 0,
        duration: ANIM_TIME,
        ease: "power2.out",
      });
    });

    bodyItems.forEach((b) => {
      const active = b.classList.contains(tabName);
      if (active) {
        gsap.fromTo(
          b,
          { xPercent: 100, autoAlpha: 0 },
          {
            xPercent: 0,
            autoAlpha: 1,
            duration: 0.6,
            ease: "power2.out",
          },
        );
      } else {
        gsap.to(b, {
          xPercent: -100,
          autoAlpha: 0,
          duration: 0.25,
          ease: "power2.inOut",
        });
      }
    });
  }

  /* ================= PHONE LOOP (DESKTOP + MOBILE) ================= */
  function startPhoneLoop() {
    stopPhoneLoop();

    loopTl = gsap.timeline({ repeat: -1 });

    loopTl.to({}, { duration: HOLD_TIME });

    loopTl.call(() => {
      currentTab = (currentTab + 1) % tabOrder.length;
      switchTab(tabOrder[currentTab]);
    });

    loopTl.to({}, { duration: ANIM_TIME });
  }

  function stopPhoneLoop() {
    if (loopTl) {
      loopTl.kill();
      loopTl = null;
    }
  }

  /* ================= INTRO ================= */
  const introTl = gsap.timeline({ paused: true });

  // 👉 DESKTOP: full intro
  if (!isMobile) {
    introTl
      .to({}, { duration: 0.4 })
      .add("showCenter")
      .fromTo(
        text,
        { autoAlpha: 0, y: 20 },
        { autoAlpha: 1, y: 0, duration: 0.6, ease: "power3.out" },
        "showCenter",
      )
      .fromTo(
        frame,
        { autoAlpha: 0, scale: 0.92 },
        { autoAlpha: 1, scale: 1, duration: 0.6, ease: "power3.out" },
        "showCenter",
      )
      .to({}, { duration: 0.15 })
      .call(initFirstTab)
      .to(absolutes, {
        autoAlpha: 1,
        y: 0,
        duration: 0.35,
        stagger: 0.12,
        ease: "power2.out",
      })
      .call(startPhoneLoop);
  }

  // 👉 MOBILE: chỉ show phone + loop
  else {
    introTl
      .fromTo(
        frame,
        { autoAlpha: 0, scale: 0.92 },
        { autoAlpha: 1, scale: 1, duration: 0.5, ease: "power3.out" },
      )
      .call(initFirstTab)
      .call(startPhoneLoop);
  }

  /* ================= SCROLL TRIGGER ================= */
  ScrollTrigger.create({
    trigger: section,
    start: "top 80%",
    end: "bottom 20%",

    onEnter() {
      gsap.to(section, { autoAlpha: 1, duration: 0.5 });
      introTl.restart();
    },

    onEnterBack() {
      gsap.to(section, { autoAlpha: 1, duration: 0.5 });
      introTl.restart();
    },

    onLeave() {
      gsap.to(section, { autoAlpha: 0, duration: 0.6 });
      stopPhoneLoop();
    },

    onLeaveBack() {
      gsap.to(section, { autoAlpha: 0, duration: 0.6 });
      stopPhoneLoop();
    },
  });
}
function SectionManageMobilePhoneOnly() {
  const section = document.querySelector(".s3");
  if (!section) return;

  const headerItems = section.querySelectorAll(".screen-header-item");
  const bodyItems = section.querySelectorAll(".screen-body-item");

  const tabOrder = ["g1", "g2", "g3"];
  let currentTab = 0;
  let loopTl = null;

  const HOLD_TIME = 2; // 2000ms
  const ANIM_TIME = 1.2;

  /* ================= FORCE VISIBLE ================= */
  gsap.set(section, { autoAlpha: 1 });

  /* ================= INIT PHONE ================= */
  function initPhone() {
    gsap.set(headerItems, { autoAlpha: 0 });
    gsap.set(bodyItems, { autoAlpha: 0, xPercent: 100 });

    gsap.set(section.querySelector(".screen-header-item.g1"), {
      autoAlpha: 1,
    });

    gsap.set(section.querySelector(".screen-body-item.g1"), {
      autoAlpha: 1,
      xPercent: 0,
    });
  }

  /* ================= SWITCH TAB ================= */
  function switchPhone(tabName) {
    headerItems.forEach((h) => {
      const active = h.classList.contains(tabName);
      gsap.to(h, {
        autoAlpha: active ? 1 : 0,
        duration: ANIM_TIME,
        ease: "power2.out",
      });
    });

    bodyItems.forEach((b) => {
      const active = b.classList.contains(tabName);

      if (active) {
        gsap.fromTo(
          b,
          { xPercent: 100, autoAlpha: 0 },
          {
            xPercent: 0,
            autoAlpha: 1,
            duration: ANIM_TIME,
            ease: "power2.out",
          },
        );
      } else {
        gsap.to(b, {
          xPercent: -100,
          autoAlpha: 0,
          duration: ANIM_TIME * 0.6,
          ease: "power2.inOut",
        });
      }
    });
  }

  /* ================= AUTO LOOP ================= */
  function startLoop() {
    stopLoop();

    loopTl = gsap.timeline({ repeat: -1 });

    // ⏸ giữ screen
    loopTl.to({}, { duration: HOLD_TIME });

    // ▶️ chuyển screen
    loopTl.call(() => {
      currentTab = (currentTab + 1) % tabOrder.length;
      switchPhone(tabOrder[currentTab]);
    });

    // ⏳ đợi animation xong
    loopTl.to({}, { duration: ANIM_TIME });
  }

  function stopLoop() {
    if (loopTl) {
      loopTl.kill();
      loopTl = null;
    }
  }

  /* ================= START ================= */
  initPhone();
  startLoop();
}

/*
SECTION LOAN */
function SectionLoan() {
  const s = document.querySelector(".s4");
  if (!s) return;

  const text = s.querySelector(".text-info");
  const image = s.querySelector(".image-center");
  const abs = s.querySelectorAll(
    ".loan-absolute-left, .loan-absolute-center, .loan-absolute-right",
  );

  /* ================= INIT ================= */
  gsap.set(s, { autoAlpha: 0 });
  gsap.set(text, { autoAlpha: 0, y: 20 });
  gsap.set(image, { autoAlpha: 0, scale: 0.94 });
  gsap.set(abs, { autoAlpha: 0, y: 28 });

  /* ================= INTRO TIMELINE ================= */
  const introTl = gsap.timeline({ paused: true });

  introTl
    .to({}, { duration: 0.4 })
    .fromTo(
      text,
      { autoAlpha: 0, y: 20 },
      {
        autoAlpha: 1,
        y: 0,
        duration: 0.6,
        ease: "power3.out",
      },
    )
    .fromTo(
      image,
      { autoAlpha: 0, scale: 0.94 },
      {
        autoAlpha: 1,
        scale: 1,
        duration: 0.6,
        ease: "power3.out",
      },
      "<",
    )
    .fromTo(
      abs,
      { autoAlpha: 0, y: 12 },
      {
        autoAlpha: 1,
        y: 0,
        duration: 0.35,
        stagger: 0.12,
        ease: "power2.out",
      },
    );

  /* ================= SCROLL TRIGGER ================= */
  ScrollTrigger.create({
    trigger: s,
    start: "top 75%",
    end: "bottom 15%",

    onEnter() {
      gsap.to(s, { autoAlpha: 1, duration: 0.5, ease: "power2.out" });
      introTl.restart();
    },

    onEnterBack() {
      gsap.to(s, { autoAlpha: 1, duration: 0.5, ease: "power2.out" });
      introTl.restart();
    },

    onLeave() {
      gsap.to(s, { autoAlpha: 0, duration: 0.6, ease: "power2.inOut" });
      introTl.pause();
    },

    onLeaveBack() {
      gsap.to(s, { autoAlpha: 0, duration: 0.6, ease: "power2.inOut" });
      introTl.pause();
    },
  });
}

/*
SECTION GROW */
let growIntroDone = false;
let growIntroTl = null;

function SectionGrowIntro() {
  const s = document.querySelector(".s5");
  if (!s) return;

  const text = s.querySelectorAll(".text-info");
  const image = s.querySelector(".image-center");
  const tabs = s.querySelector(".s5-tabs");

  /* ================= INIT ================= */
  gsap.set(s, { autoAlpha: 0 });
  gsap.set(text, { autoAlpha: 0, y: 20 });
  gsap.set(image, { autoAlpha: 0, scale: 0.94 });
  gsap.set(tabs, { autoAlpha: 0, y: 20 });

  /* ================= INTRO TIMELINE ================= */
  const introTl = gsap.timeline({ paused: true });

  introTl
    .to({}, { duration: 0.6 })
    .to(text, {
      autoAlpha: 1,
      y: 0,
      duration: 0.6,
      stagger: 0.05,
      ease: "power3.out",
    })
    .to(
      image,
      {
        autoAlpha: 1,
        scale: 1,
        duration: 0.6,
        ease: "power3.out",
      },
      "<",
    )
    .to(
      tabs,
      {
        autoAlpha: 1,
        y: 0,
        duration: 0.6,
        ease: "power3.out",
      },
      "<0.1",
    );

  growIntroTl = introTl;

  /* ================= SCROLL TRIGGER ================= */
  ScrollTrigger.create({
    trigger: s,
    start: "top bottom",
    end: "bottom top",

    onEnter() {
      gsap.to(s, { autoAlpha: 1, duration: 0.6, ease: "power2.out" });
      introTl.restart();
    },

    onEnterBack() {
      gsap.to(s, { autoAlpha: 1, duration: 0.6, ease: "power2.out" });
      introTl.restart();
    },

    onLeave() {
      gsap.to(s, { autoAlpha: 0, duration: 0.6, ease: "power2.inOut" });
      introTl.pause(0);
    },

    onLeaveBack() {
      gsap.to(s, { autoAlpha: 0, duration: 0.6, ease: "power2.inOut" });
      introTl.pause(0);
    },
  });
}

document.addEventListener("DOMContentLoaded", () => {
  if (window.innerWidth < 850) return;

  const container = document.getElementById("content");
  if (!container) return;

  container.classList.add("snap-container");

  const snapSections = [...container.querySelectorAll(".snap-section")];
  if (!snapSections.length) return;
  const snapReadyMap = new Map();

  const snapObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        snapReadyMap.set(entry.target, entry.intersectionRatio >= 0.9);
      });
    },
    { threshold: [0.9] },
  );

  snapSections.forEach((sec) => snapObserver.observe(sec));

  /* =================================================
    S5 SETUP
================================================= */
  const s5 = container.querySelector(".s5");
  if (!s5) return;

  const tabs = [...s5.querySelectorAll(".s5-tabs .tab")];
  const groups = [...s5.querySelectorAll(".grow-visual-group")];
  if (!tabs.length || !groups.length) return;

  const tabOrder = tabs.map((t) => t.dataset.tab);

  let currentTabIndex = 0;
  let isTabAnimating = false;
  let isSnapping = false;
  let activeTimeline = null;
/* =================================================
   AUTO CENTER S5 WHEN 5% VISIBLE
================================================= */
let s5AutoCentered = false;

const s5AutoObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.target !== s5) return;
      if (
        entry.isIntersecting &&
        entry.intersectionRatio >= 0.21 &&
        !s5AutoCentered &&
        !isSnapping &&
        !isSectionCentered(s5)
      ) {
        s5AutoCentered = true;
        isSnapping = true;

        gsap.to(window, {
          scrollTo: {
            y: s5,
            offsetY: getHeaderHeight(),
            autoKill: false,
          },
          duration: 0.8,
          ease: "power3.out",
          onComplete: () => {
            isSnapping = false;
          },
        });
      }

      // reset khi rời xa S5 để lần sau vào lại còn dùng
      if (!entry.isIntersecting && entry.intersectionRatio === 0) {
        s5AutoCentered = false;
      }
    });
  },
  {
    threshold: [0, 0.21],
  },
);

s5AutoObserver.observe(s5);

  /* =================================================
    RESET HELPERS
================================================= */
  function killTimeline() {
    if (activeTimeline) {
      activeTimeline.kill();
      activeTimeline = null;
    }
  }

  function resetVisuals() {
    killTimeline();

    gsap.set(
      s5.querySelectorAll(
        ".phone-screen, .ai-step, .screen-body-item, .screen-footer-item",
      ),
      {
        autoAlpha: 0,
        clearProps: "transform",
      },
    );
  }

  /* =================================================
    PHONE ANIMATIONS
================================================= */

  // ---------- TAB g1 ----------
  function playG1() {
    const screens = s5.querySelectorAll(".grow-visual-group.g1 .phone-screen");
    if (screens.length <= 1) {
      if (screens[0]) gsap.set(screens[0], { autoAlpha: 1 });
      return;
    }

    let current = 0;
    let timer = null;

    // reset
    gsap.set(screens, {
      autoAlpha: 0,
      y: 6,
    });

    gsap.set(screens[0], {
      autoAlpha: 1,
      y: 0,
    });

    function switchScreen(next) {
      const currentScreen = screens[current];
      const nextScreen = screens[next];

      // fade out
      gsap.to(currentScreen, {
        autoAlpha: 0,
        duration: 0.35,
        ease: "power1.out",
      });

      // fade + lift next
      gsap.fromTo(
        nextScreen,
        { autoAlpha: 0, y: 20 },
        {
          autoAlpha: 1,
          y: 0,
          duration: 0.45,
          ease: "power2.out",
        },
      );

      current = next;
    }

    // clear loop
    if (window.__g1Loop) {
      clearInterval(window.__g1Loop);
    }

    // loop smooth
    window.__g1Loop = setInterval(() => {
      const next = (current + 1) % screens.length;
      switchScreen(next);
    }, 2200); // hold time
  }

  // ---------- TAB g2 ----------
  function playG2() {
    const g2Group = s5.querySelector(".grow-visual-group.g2");
    if (!g2Group) return;

    const phoneScreen = g2Group.querySelector(".phone-screen");
    const bodies = [...g2Group.querySelectorAll(".screen-body-item")];
    const footers = [...g2Group.querySelectorAll(".screen-footer-item")];

    if (!phoneScreen || bodies.length < 2 || footers.length < 2) return;

    gsap.set(g2Group, { autoAlpha: 1 });
    gsap.set(phoneScreen, { autoAlpha: 1 });

    const order = ["g1", "g2"];
    let current = 0;

    /* ===============================
     RESET STATE
  =============================== */
    bodies.forEach((b, i) => {
      gsap.set(b, {
        autoAlpha: i === 0 ? 1 : 0,
        xPercent: i === 0 ? 0 : 100,
      });
      b.classList.toggle("active", i === 0);
    });

    footers.forEach((f, i) => {
      gsap.set(f, {
        autoAlpha: i === 0 ? 1 : 0,
      });
      f.classList.toggle("active", i === 0);
    });

    if (window.__g2Loop) clearInterval(window.__g2Loop);

    /* ===============================
     ONE-WAY LOOP (BODY SLIDE / FOOTER FADE)
  =============================== */
    window.__g2Loop = setInterval(() => {
      const next = (current + 1) % order.length;
      const nextKey = order[next];

      const tl = gsap.timeline({
        defaults: { ease: "power2.inOut" },
      });

      /* ---------- BODY ---------- */
      bodies.forEach((b) => {
        const isNext = b.classList.contains(nextKey);

        if (isNext) {
          tl.fromTo(
            b,
            { autoAlpha: 0, xPercent: 100 },
            { autoAlpha: 1, xPercent: 0, duration: 0.9 },
            0,
          );
        } else {
          tl.to(b, { autoAlpha: 0, xPercent: -100, duration: 0.9 }, 0);
        }

        b.classList.toggle("active", isNext);
      });

      /* ---------- FOOTER (FADE ONLY) ---------- */
      footers.forEach((f) => {
        const isNext = f.classList.contains(nextKey);

        tl.to(
          f,
          {
            autoAlpha: isNext ? 1 : 0,
            duration: isNext ? 0.4 : 0.25,
            ease: "power2.out",
          },
          0.1,
        );

        f.classList.toggle("active", isNext);
      });

      current = next;
    }, 2200);
  }

  // ---------- TAB g3 ----------
  function playG3() {
    const steps = s5.querySelectorAll(".grow-visual-group.g3 .ai-step");
    if (!steps.length) return;

    gsap.set(steps, { autoAlpha: 0, y: 20 });
    gsap.set(steps[0], { autoAlpha: 1, y: 0 });

    activeTimeline = gsap.timeline({
      repeat: -1,
      defaults: { ease: "power2.out" },
    });

    steps.forEach((step, i) => {
      if (i === 0) return;

      activeTimeline
        .to({}, { duration: 2 })
        .to(steps[i - 1], { autoAlpha: 0, duration: 0.4 })
        .fromTo(
          step,
          { autoAlpha: 0, y: 20 },
          { autoAlpha: 1, y: 0, duration: 0.5 },
          "<",
        );
    });
  }

  /* =================================================
    ACTIVATE TAB
================================================= */
  function activateTab(index) {
    if (index < 0 || index >= tabOrder.length) return;
    if (isTabAnimating) return;

    isTabAnimating = true;
    currentTabIndex = index;

    const tabKey = tabOrder[index];

    // tabs
    tabs.forEach((t) => t.classList.toggle("active", t.dataset.tab === tabKey));

    // groups
    groups.forEach((g) => {
      const active = g.classList.contains(tabKey);

      if (active) {
        gsap.set(g, { pointerEvents: "auto" });
        gsap.to(g, {
          autoAlpha: 1,
          scale: 1,
          duration: 0.45,
          ease: "power2.out",
        });
      } else {
        gsap.to(g, {
          autoAlpha: 0,
          scale: 0.96,
          duration: 0.35,
          ease: "power2.out",
          onComplete: () => gsap.set(g, { pointerEvents: "none" }),
        });
      }
    });

    resetVisuals();

    if (tabKey === "g1") playG1();
    // if (tabKey === "g2") playG2();
    if (tabKey === "g3") playG3();

    setTimeout(() => {
      isTabAnimating = false;
    }, 600);
  }

  activateTab(0);

  /* =================================================
    SNAP + TAB WHEEL CONTROL
================================================= */
  let wheelLock = false;
  const WHEEL_LOCK_TIME = 700;
  window.addEventListener(
    "wheel",
    (e) => {
      if (wheelLock) {
        e.preventDefault();
        return;
      }
      const delta = e.deltaY;
      if (Math.abs(delta) < 25) return;

      if (isSnapping || isTabAnimating) {
        e.preventDefault();
        return;
      }

      const direction = delta > 0 ? "DOWN" : "UP";

      const current = getMostVisibleSnap(snapSections);
      if (!current) return;
      if (current === s5) {
        const centeredNow = isSectionCentered(s5);

        if (centeredNow && !s5Centered) {
          s5Centered = true;
        }

        if (!centeredNow && s5Centered) {
          s5Centered = false;
        }
      }

      const index = snapSections.indexOf(current);
      if (index === -1) return;

      const isFirst = index === 0;
      const isLast = index === snapSections.length - 1;

      /* =================================================
      PREVENT SCROLL
  ================================================= */

      if (direction === "DOWN" && isFirst && !snapReadyMap.get(current)) {
        return;
      }

      if (direction === "UP" && isLast && !snapReadyMap.get(current)) {
        return;
      }

      /* =================================================
      S5 TAB MODE
  ================================================= */
      if (current === s5) {
        if (direction === "DOWN" && currentTabIndex < tabOrder.length - 1) {
          e.preventDefault();
          activateTab(currentTabIndex + 1);
          setTimeout(() => (wheelLock = false), WHEEL_LOCK_TIME);
          return;
        }

        if (direction === "UP" && currentTabIndex > 0) {
          e.preventDefault();
          wheelLock = true;
          activateTab(currentTabIndex - 1);
          setTimeout(() => (wheelLock = false), WHEEL_LOCK_TIME);
          return;
        }
      }

      /* SNAP SECTION */
      let target = null;

      if (direction === "DOWN" && index < snapSections.length - 1) {
        target = snapSections[index + 1];
      }

      if (direction === "UP" && index > 0) {
        target = snapSections[index - 1];
      }
      if (!target) return;

      e.preventDefault();
      isSnapping = true;

      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });

      setTimeout(() => {
        isSnapping = false;
      }, 850);
    },
    { passive: false },
  );
});

/* =================================================
  HELPER
================================================= */
function getHeaderHeight() {
  const header = document.getElementById("header");
  if (!header) return 0;

  return Math.round(header.getBoundingClientRect().height);
}

function isSectionCentered(section) {
  const r = section.getBoundingClientRect();
  const headerH = getHeaderHeight();
  const vh = window.innerHeight;

  const topAligned = Math.abs(r.top - headerH) <= 4;
  const bottomFilled = r.bottom >= vh - 4;
  const heightEnough = r.height >= vh - headerH - 4;

  return topAligned && bottomFilled && heightEnough;
}

let s5Centered = false;

function getMostVisibleSnap(sections) {
  let maxVisible = 0;
  let selected = null;
  const vh = window.innerHeight;

  sections.forEach((sec) => {
    const r = sec.getBoundingClientRect();
    const visible = Math.min(r.bottom, vh) - Math.max(r.top, 0);

    if (visible > maxVisible) {
      maxVisible = visible;
      selected = sec;
    }
  });

  return selected;
}
