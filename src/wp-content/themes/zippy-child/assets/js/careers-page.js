document.addEventListener("DOMContentLoaded", function () {
  const dropdowns = document.querySelectorAll(".custom-select");
  const search = document.getElementById("job-search");
  const allJobs = Array.from(document.querySelectorAll(".job-item"));
  const clear = document.getElementById("clear-filters");
  const tagContainer = document.querySelector(".show-tags-choised");
  const labelGroup = document.querySelector(".label-search-group");
  const loadMoreBtn = document.getElementById("load-more");
  const loadingOverlay = document.getElementById("job-loading");

  let visibleCount = 8;
  const increment = 8;
  let filteredJobs = [...allJobs];

  // ========== Loading ==========
  function showLoading(fixedHeight = true) {
    const jobList = document.getElementById("job-list");

    if (fixedHeight) {
      jobList.style.height = "300px";
    }
    jobList.style.opacity = "0.3";
    loadingOverlay.style.display = "flex";
    loadMoreBtn.style.display = "none";
  }

  function hideLoading() {
    const jobList = document.getElementById("job-list");
    jobList.style.height = "auto";
    jobList.style.opacity = "1";
    loadingOverlay.style.display = "none";
    if (visibleCount < filteredJobs.length) {
      loadMoreBtn.style.display = "inline-block";
    } else {
      loadMoreBtn.style.display = "none";
    }
  }

  // ========== Helper ==========
  function getItemValue(itemTextEl) {
    const dv = itemTextEl.dataset?.value;
    return dv && dv.trim() !== ""
      ? dv.trim().toLowerCase()
      : itemTextEl.textContent.trim().toLowerCase().replace(/\s+/g, "-");
  }

  function closeAllDropdowns(except = null) {
    dropdowns.forEach((d) => {
      if (d !== except) d.classList.remove("open");
    });
  }

  // ========== Dropdown ==========
  dropdowns.forEach((drop) => {
    const btn = drop.querySelector(".select-btn");
    const items = drop.querySelectorAll(".item");

    btn.addEventListener("click", (e) => {
      e.stopPropagation();
      closeAllDropdowns(drop);
      drop.classList.toggle("open");
    });

    items.forEach((item) => {
      item.addEventListener("click", (e) => {
        e.stopPropagation();
        item.classList.toggle("checked");
        updateDropdownText(drop);
        updateTags();
        triggerFilterWithLoading();
        updateLabelGroupVisibility();
      });
    });
  });
  document.addEventListener("click", () => closeAllDropdowns());

  function updateDropdownText(drop) {
    const btnText = drop.querySelector(".btn-text");
    const label =
      drop.id === "location-filter"
        ? "Location"
        : drop.id === "work-filter"
        ? "Work Type"
        : "Option";
    const checkedCount = drop.querySelectorAll(".checked .item-text").length;
    btnText.innerText = checkedCount > 0 ? `${label} (${checkedCount})` : label;
  }

  // ========== Tags ==========
  function updateTags() {
    tagContainer.innerHTML = "";

    dropdowns.forEach((drop) => {
      const group =
        drop.id === "location-filter"
          ? "location"
          : drop.id === "work-filter"
          ? "work"
          : "option";

      const checkedItems = drop.querySelectorAll(".checked .item-text");
      checkedItems.forEach((itemEl) => {
        const valueForCompare = getItemValue(itemEl);
        const displayLabel = itemEl.textContent.trim();

        const tag = document.createElement("div");
        tag.className = "filter-tag";
        tag.dataset.group = group;
        tag.dataset.value = valueForCompare;
        tag.innerHTML = `${displayLabel} <span class="remove-tag">×</span>`;
        tagContainer.appendChild(tag);
      });
    });
    updateLabelGroupVisibility();
  }

  // ========== Filter Logic ==========
  function filterJobs() {
    const keyword = (search.value || "").toLowerCase();
    const selectedLocations = Array.from(
      document.querySelectorAll("#location-filter .checked .item-text")
    ).map((i) => getItemValue(i));
    const selectedWork = Array.from(
      document.querySelectorAll("#work-filter .checked .item-text")
    ).map((i) => getItemValue(i));

    filteredJobs = allJobs.filter((job) => {
      const name = job.querySelector(".job-name").textContent.toLowerCase();
      const matchSearch = name.includes(keyword);
      const jobLoc = (job.dataset.location || "").toLowerCase().trim();
      const jobWork = (job.dataset.work || "").toLowerCase().trim();

      const matchLoc =
        selectedLocations.length === 0 ||
        selectedLocations.some((loc) => jobLoc.includes(loc));
      const matchWork =
        selectedWork.length === 0 ||
        selectedWork.some((work) => jobWork.includes(work));

      return matchSearch && matchLoc && matchWork;
    });

    // Hide all jobs
    allJobs.forEach((job) => (job.style.display = "none"));
  }

  // ========== Show job after filter ==========
  function showJobs() {
    const noJobsMsg = document.getElementById("no-jobs");

    if (filteredJobs.length === 0) {
      allJobs.forEach((job) => (job.style.display = "none"));
      noJobsMsg.style.display = "block";
      loadMoreBtn.style.display = "none";
      return;
    }

    noJobsMsg.style.display = "none";

    filteredJobs.forEach((job, index) => {
      job.style.display = index < visibleCount ? "flex" : "none";
    });

    loadMoreBtn.style.display =
      visibleCount >= filteredJobs.length ? "none" : "inline-block";
  }

  // ========== Filter + Loading ==========
  function triggerFilterWithLoading() {
    showLoading(true);
    setTimeout(() => {
      filterJobs();
      visibleCount = 8;
      showJobs();
      hideLoading();
    }, 2000);
  }

  // ========== Tag remove + Clear ==========
  tagContainer.addEventListener("click", (e) => {
    if (!e.target.classList.contains("remove-tag")) return;
    const tagEl = e.target.closest(".filter-tag");
    const group = tagEl.dataset.group;
    const value = tagEl.dataset.value;

    const drop = document.getElementById(`${group}-filter`);
    if (drop) {
      drop.querySelectorAll(".item").forEach((i) => {
        const txtEl = i.querySelector(".item-text");
        if (txtEl && getItemValue(txtEl) === value) {
          i.classList.remove("checked");
        }
      });
      updateDropdownText(drop);
    }
    tagEl.remove();
    triggerFilterWithLoading();
    updateLabelGroupVisibility();
  });

  clear.addEventListener("click", () => {
    search.value = "";
    document
      .querySelectorAll(".item.checked")
      .forEach((i) => i.classList.remove("checked"));
    dropdowns.forEach(updateDropdownText);
    tagContainer.innerHTML = "";
    triggerFilterWithLoading();
    updateLabelGroupVisibility();
  });

  search.addEventListener("input", triggerFilterWithLoading);

  // ========== Load More ==========
  loadMoreBtn.addEventListener("click", () => {
    showLoading(false);
    setTimeout(() => {
      visibleCount += increment;
      showJobs();
      hideLoading();
    }, 2000);
  });

  // ========== Label visibility ==========
  function updateLabelGroupVisibility() {
    const hasTags = tagContainer?.children.length > 0;
    const hasSearch = search?.value.trim() !== "";
    labelGroup.style.display = hasTags || hasSearch ? "flex" : "none";
  }

  // ========== Init ===========
  labelGroup.style.display = "none";
  updateTags();
  dropdowns.forEach(updateDropdownText);
  filterJobs();
  visibleCount = 8;
  showJobs();
  hideLoading();
});
