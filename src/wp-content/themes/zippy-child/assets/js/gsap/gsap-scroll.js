window.addEventListener("load", function () {
  if (window.innerWidth < 992) return;
  gsap.registerPlugin(ScrollTrigger);

  ScrollTrigger.matchMedia({
    /*
DESKTOP */
    "(min-width: 769px)": function () {
      /*SECTION 1 – SELL*/
      const s1Tl = gsap.timeline({
        scrollTrigger: {
          trigger: ".s1",
          start: "top top+=80",
          end: "+=220%",
          pin: true,
          scrub: true,
          anticipatePin: 1,
          invalidateOnRefresh: true,
        },
      });

      s1Tl
        .fromTo(
          ".s1 .image-center",
          { scale: 0.6, autoAlpha: 0 },
          { scale: 1, autoAlpha: 1, ease: "power2.out", duration: 0.5 },
          0
        )
        .fromTo(
          ".s1 .text-info",
          { y: 80, autoAlpha: 0 },
          { y: 0, autoAlpha: 1, ease: "power2.out", duration: 0.5 },
          0
        )
        .fromTo(
          ".s1 .sell-absolute-left, .s1 .sell-absolute-right, .s1 .sell-absolute-center",
          { y: 80, autoAlpha: 0 },
          {
            y: 0,
            autoAlpha: 1,
            ease: "power2.out",
            duration: 0.4,
            stagger: 0.05,
          },
          0.6
        );

      /*SECTION 2 – SAVE (2 VERSION)*/
      const s2Tl = gsap.timeline({
        scrollTrigger: {
          trigger: ".s2",
          start: "top top+=80",
          end: "+=220%",
          pin: true,
          scrub: true,
          anticipatePin: 1,
          invalidateOnRefresh: true,
        },
      });

      s2Tl
        .fromTo(
          ".s2 .image-center",
          { scale: 0.6, autoAlpha: 0 },
          { scale: 1, autoAlpha: 1, ease: "power2.out", duration: 0.5 },
          0
        )
        .fromTo(
          ".s2 .text-info",
          { y: 80, autoAlpha: 0 },
          { y: 0, autoAlpha: 1, ease: "power2.out", duration: 0.5 },
          0
        )
        .fromTo(
          ".s2 .save-absolute-left.v1, .s2 .save-absolute-right.v1",
          { y: 80, autoAlpha: 0 },
          { y: 0, autoAlpha: 1, ease: "power2.out", duration: 0.4 },
          0.6
        )
        .to(
          ".s2 .save-absolute-left.v1, .s2 .save-absolute-right.v1",
          { y: -60, autoAlpha: 0, ease: "power2.inOut", duration: 0.4 },
          1.2
        )
        .fromTo(
          ".s2 .save-absolute-left.v2, .s2 .save-absolute-right.v2",
          { y: 80, autoAlpha: 0 },
          { y: 0, autoAlpha: 1, ease: "power2.out", duration: 0.4 },
          1.2
        );

      /*SECTION 3 – MANAGE*/
      const s3Tl = gsap.timeline({
        scrollTrigger: {
          trigger: ".s3",
          start: "top top+=80",
          end: "+=220%",
          pin: true,
          scrub: true,
          anticipatePin: 1,
          invalidateOnRefresh: true,
        },
      });

      s3Tl
        .fromTo(
          ".s3 .image-center",
          { scale: 0.6, autoAlpha: 0 },
          { scale: 1, autoAlpha: 1, ease: "power2.out", duration: 0.5 },
          0
        )
        .fromTo(
          ".s3 .text-info",
          { y: 80, autoAlpha: 0 },
          { y: 0, autoAlpha: 1, ease: "power2.out", duration: 0.5 },
          0
        )
        .fromTo(
          ".s3 .manage-absolute-left, .s3 .manage-absolute-right, .s3 .manage-absolute-center",
          { y: 80, autoAlpha: 0 },
          {
            y: 0,
            autoAlpha: 1,
            ease: "power2.out",
            duration: 0.4,
            stagger: 0.05,
          },
          0.6
        );

      /*SECTION 4 – LOAN*/
      const s4Tl = gsap.timeline({
        scrollTrigger: {
          trigger: ".s4",
          start: "center center+=80",
          end: "+=220%",
          pin: true,
          scrub: true,
          anticipatePin: 1,
          invalidateOnRefresh: true,
        },
      });

      s4Tl
        .fromTo(
          ".s4 .image-center",
          { scale: 0.6, autoAlpha: 0 },
          { scale: 1, autoAlpha: 1, ease: "power2.out", duration: 0.5 },
          0
        )
        .fromTo(
          ".s4 .text-info",
          { y: 80, autoAlpha: 0 },
          { y: 0, autoAlpha: 1, ease: "power2.out", duration: 0.5 },
          0
        )
        .fromTo(
          ".s4 .loan-absolute-left, .s4 .loan-absolute-right, .s4 .loan-absolute-center",
          { y: 80, autoAlpha: 0 },
          {
            y: 0,
            autoAlpha: 1,
            ease: "power2.out",
            duration: 0.4,
            stagger: 0.05,
          },
          0.6
        );

      /* SECTION 5 – GROW (IMAGE ↔ TAB SYNC)*/
      // helper: set active tab
      function setActiveTab(index) {
        document.querySelectorAll(".s5 .tab").forEach((tab) => {
          tab.classList.remove("active");
        });
        const current = document.querySelector(".s5 .tab-" + index);
        if (current) current.classList.add("active");
      }

      // INIT STATE
      gsap.set(".s5 .img-1, .s5 .img-2, .s5 .img-3", {
        autoAlpha: 0,
      });
      setActiveTab(1);

      const s5Tl = gsap.timeline({
        scrollTrigger: {
          trigger: ".s5",
          start: "top top+=30",
          end: "+=300%", // 3 phase
          pin: true,
          scrub: true,
          anticipatePin: 1,
          invalidateOnRefresh: true,

          // ⭐ SYNC TAB THEO SCROLL
          onUpdate: (self) => {
            const p = self.progress;

            if (p < 0.33) {
              setActiveTab(1);
            } else if (p < 0.66) {
              setActiveTab(2);
            } else {
              setActiveTab(3);
            }
          },
        },
      });
      s5Tl.fromTo(
        ".s5 .text-info",
        { y: 80, autoAlpha: 0 },
        { y: 0, autoAlpha: 1, ease: "power2.out", duration: 0.5 },
        0
      ),
        s5Tl.fromTo(
          ".s5 .s5-tabs",
          { y: 80, autoAlpha: 0 },
          { y: 0, autoAlpha: 1, ease: "power2.out", duration: 0.5 },
          0
        ),
        /* ===== PHASE 1 – IMG 1 ===== */
        s5Tl.fromTo(
          ".s5 .img-1",
          { scale: 0.6, autoAlpha: 0 },
          { scale: 1, autoAlpha: 1, ease: "power2.out", duration: 0.5 },
          0
        );

      /* dead scroll */
      s5Tl.to({}, { duration: 0.3 });

      /* PHASE 2 – IMG 2 */
      s5Tl
        .to(".s5 .img-1", { autoAlpha: 0, y: -20, duration: 0.3 }, 0.8)
        .fromTo(
          ".s5 .img-2",
          { autoAlpha: 0, y: 20 },
          { autoAlpha: 1, y: 0, duration: 0.4 },
          0.9
        );

      /* dead scroll */
      s5Tl.to({}, { duration: 0.3 });

      /* PHASE 3 – IMG 3 */
      s5Tl
        .to(".s5 .img-2", { autoAlpha: 0, y: -20, duration: 0.3 }, 1.6)
        .fromTo(
          ".s5 .img-3",
          { autoAlpha: 0, y: 20 },
          { autoAlpha: 1, y: 0, duration: 0.4 },
          1.7
        );

      s5Tl.to({}, { duration: 0.6 });
    },

    /* MOBILE (NO PIN) */
    "(max-width: 768px)": function () {
      gsap.utils.toArray(".s1, .s2, .s3, .s4, .s5").forEach((section) => {
        const items = section.querySelectorAll(
          ".image-center, .text-info, [class*='absolute']"
        );

        if (!items.length) return;

        gsap.from(items, {
          opacity: 0,
          y: 60,
          duration: 0.6,
          stagger: 0.15,
          ease: "power2.out",
          scrollTrigger: {
            trigger: section,
            start: "top 80%",
          },
        });
      });
    },
  });
});
