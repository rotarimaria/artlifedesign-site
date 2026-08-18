document.addEventListener("DOMContentLoaded", async () => {
  let works = [];

  try {
    const response = await fetch("api/projects.php", {
      cache: "no-store"
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const payload = await response.json();

    if (!payload.ok || !Array.isArray(payload.projects)) {
      throw new Error(
        payload.message || "Răspuns invalid de la API."
      );
    }

    works = payload.projects;
  } catch (error) {
    console.error(
      "Proiectele nu au putut fi încărcate din baza de date:",
      error
    );
  }

  const $ = (selector) => document.querySelector(selector);
  const $$ = (selector) => document.querySelectorAll(selector);

  const navbar = $("#navbarTop");
  const scrollProgress = $("#scrollProgress");
  const navMenu = $("#navMenu");
  const navLinksWrap = $("#navLinksWrap");

  function showToast(text, type = "success") {
    const toast = $("#siteToast");
    if (!toast) return;

    toast.textContent = text;
    toast.className = `site-toast show ${type}`;

    setTimeout(() => {
      toast.className = "site-toast";
    }, 2500);
  }

  function handleScroll() {
    const top = window.scrollY;
    const height = document.documentElement.scrollHeight - window.innerHeight;
    const percent = height > 0 ? (top / height) * 100 : 0;

    if (navbar) navbar.classList.toggle("nav-scrolled", top > 40);
    if (scrollProgress) scrollProgress.style.setProperty("--scroll-progress", percent + "%");

    let current = "";

    $$("section[id]").forEach((section) => {
      const sectionTop = section.offsetTop - 130;

      if (top >= sectionTop && top < sectionTop + section.offsetHeight) {
        current = section.id;
      }
    });

    $$(".nav-link").forEach((link) => {
      const href = link.getAttribute("href");

      if (href && href.startsWith("#")) {
        link.classList.toggle("active", href === `#${current}`);
      }
    });
  }

  window.addEventListener("scroll", handleScroll);
  handleScroll();

  if (navMenu && navLinksWrap) {
    navMenu.addEventListener("click", () => {
      const open = navLinksWrap.classList.toggle("active");
      navMenu.setAttribute("aria-expanded", String(open));
    });
  }

  $$('a[href^="#"]').forEach((link) => {
    link.addEventListener("click", (event) => {
      const target = $(link.getAttribute("href"));
      if (!target) return;

      event.preventDefault();

      window.scrollTo({
        top: target.offsetTop - 78,
        behavior: "smooth"
      });

      if (navLinksWrap) navLinksWrap.classList.remove("active");
      if (navMenu) navMenu.setAttribute("aria-expanded", "false");
    });
  });


  async function copyText(value) {
    if (!value) return;

    try {
      await navigator.clipboard.writeText(value);
      showToast("Textul a fost copiat.", "success");
    } catch {
      showToast("Nu s-a putut copia textul.", "error");
    }
  }

  document.addEventListener("click", (event) => {
    const copyButton = event.target.closest(".copy-text");
    if (!copyButton) return;

    copyText(copyButton.dataset.copy);
  });

  function selectService(serviceName) {
    const select = $('select[name="serviciu"]');
    if (!select || !serviceName) return;

    [...select.options].forEach((option) => {
      option.selected = option.value === serviceName;
    });
  }

  const serviceFromUrl = new URLSearchParams(window.location.search).get("service");

  if (serviceFromUrl) {
    selectService(decodeURIComponent(serviceFromUrl));
  }

  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-order-service]");
    if (!button) return;

    // Rândurile din lista de servicii deschid detaliile serviciului,
    // nu formularul de contact.
    if (button.closest("[data-service-card]")) return;

    event.preventDefault();

    const service = button.dataset.orderService || "";
    const contact = $("#contact");
    const modal = $("#galleryModal");

    if (modal && modal.classList.contains("active")) {
      closeGallery();
    }

    if (contact) {
      selectService(service);
      contact.scrollIntoView({ behavior: "smooth", block: "start" });
    } else {
      window.location.href = `index.html?service=${encodeURIComponent(service)}#contact`;
    }
  });

  function escapeHTML(value = "") {
    return String(value)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function cardMediaHTML(work) {
    const media = work.media?.[0];

    if (!media?.src) {
      return `
        <div class="public-media-placeholder">
          Fără media
        </div>
      `;
    }

    const vars = [
      `--crop-x:${Number(media.cropX ?? 50)}%`,
      `--crop-y:${Number(media.cropY ?? 50)}%`,
      `--crop-zoom:${Number(media.zoom ?? 1)}`,
      `--crop-fit:${media.fit === "contain" ? "contain" : "cover"}`,
      `--crop-rotation:${Number(media.rotation ?? 0)}deg`
    ].join(";");

    if (media.type === "video") {
      return `
        <video
          src="${escapeHTML(media.src)}"
          muted
          loop
          autoplay
          playsinline
          preload="metadata"
          style="${vars}"
          aria-label="${escapeHTML(work.title)}"
        ></video>
      `;
    }

    return `
      <img
        src="${escapeHTML(media.src)}"
        alt="${escapeHTML(work.title)}"
        loading="lazy"
        decoding="async"
        style="${vars}"
      >
    `;
  }

  function workCard(work, isHome = false) {
    const orderHref = isHome
      ? "#contact"
      : `index.html?service=${encodeURIComponent(work.service)}#contact`;

    const examplesHref =
      work.category === "laser"
        ? "lucrari.html?search=plotter"
        : `lucrari.html?filter=${encodeURIComponent(work.category)}`;

    const encodedMedia = encodeURIComponent(
      JSON.stringify(work.media || [])
    );

    const safeTitle = escapeHTML(work.title);
    const safeService = escapeHTML(work.service);
    const safeCategory = escapeHTML(work.category);
    const safeSearch = escapeHTML(work.search);
    const safeDesc = escapeHTML(work.desc);
    const safeTags = escapeHTML(work.tags);
    const mediaHTML = cardMediaHTML(work);

    const shortService = escapeHTML(
      String(work.service || "").replace(
        "Litere în volum & Standuri",
        "Litere & Standuri"
      )
    );

    const commonData = `
      data-category="${safeCategory}"
      data-media="${encodedMedia}"
      data-title="${safeTitle}"
      data-service="${safeService}"
      data-desc="${safeDesc}"
      data-tags="${safeTags}"
    `;

    if (isHome) {
      return `
        <article
          class="work-card reveal"
          data-category="${safeCategory}"
          data-search="${safeSearch}"
        >
          <button
            class="work-img gallery-item"
            type="button"
            ${commonData}
            aria-label="Deschide proiectul ${safeTitle}"
          >
            ${mediaHTML}

            <span>${shortService}</span>

            <div class="media-overlay">
              <i class="bi bi-arrows-fullscreen"></i>
              <small>Vezi proiectul</small>
            </div>
          </button>

          <div class="work-info">
            <small>${safeService}</small>
            <h3>${safeTitle}</h3>

            <div class="work-actions work-actions-double">
              <a
                href="${orderHref}"
                class="work-order-btn work-order-primary"
                data-order-service="${safeService}"
              >
                Cere ofertă
                <i class="bi bi-arrow-up-right"></i>
              </a>

              <a
                href="${examplesHref}"
                class="work-order-btn work-order-secondary"
              >
                Vezi exemple
                <i class="bi bi-arrow-up-right"></i>
              </a>
            </div>
          </div>
        </article>
      `;
    }

    return `
      <article
        class="portfolio-card reveal"
        data-category="${safeCategory}"
        data-search="${safeSearch}"
      >
        <button
          class="portfolio-media gallery-item"
          type="button"
          ${commonData}
          aria-label="Deschide proiectul ${safeTitle}"
        >
          ${mediaHTML}

          <span>${shortService}</span>

          <div class="portfolio-media-overlay">
            <span>Vezi proiectul</span>
            <i class="bi bi-arrow-up-right"></i>
          </div>
        </button>

        <div class="portfolio-content">
          <small>${safeService}</small>
          <h3>${safeTitle}</h3>

          <div class="portfolio-card-actions">
            <a
              href="${orderHref}"
              class="portfolio-action portfolio-action-primary"
              data-order-service="${safeService}"
            >
              Cere ofertă
              <i class="bi bi-arrow-up-right"></i>
            </a>

            <button
              type="button"
              class="portfolio-action portfolio-action-secondary"
              data-open-card-work
            >
              Vezi proiectul
              <i class="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>
      </article>
    `;
  }

  const homeGrid = $("#homeWorksGrid");
  const portfolioGrid = $("#portfolioGrid");

  if (homeGrid) {
    homeGrid.innerHTML = works.slice(0, 4).map((work) => workCard(work, true)).join("");
  }


  function initMobileWorksCarousel() {
    const grid = $("#homeWorksGrid");
    if (!grid) return;

    const mq = window.matchMedia("(max-width: 760px)");

    let rafId = null;
    let resumeTimer = null;
    let paused = false;
    let cycleWidth = 0;
    let lastTime = 0;

    function originalCards() {
      return [
        ...grid.querySelectorAll(
          ".work-card:not([data-carousel-clone])"
        )
      ];
    }

    function cloneCards() {
      return [
        ...grid.querySelectorAll("[data-carousel-clone]")
      ];
    }

    function removeClones() {
      cloneCards().forEach((clone) => clone.remove());
    }

    function measureCycle() {
      const firstOriginal = originalCards()[0];
      const firstClone = cloneCards()[0];

      if (!firstOriginal || !firstClone) {
        cycleWidth = 0;
        return;
      }

      cycleWidth =
        firstClone.getBoundingClientRect().left -
        firstOriginal.getBoundingClientRect().left;
    }

    function buildLoop() {
      removeClones();

      const cards = originalCards();
      if (cards.length < 2) return;

      cards.forEach((card, index) => {
        const clone = card.cloneNode(true);

        clone.dataset.carouselClone = "true";
        clone.dataset.carouselSourceIndex = String(index);
        clone.setAttribute("aria-hidden", "true");

        grid.appendChild(clone);
      });

      requestAnimationFrame(measureCycle);
    }

    function stopAnimation() {
      if (rafId) {
        cancelAnimationFrame(rafId);
        rafId = null;
      }
    }

    function frame(time) {
      if (!mq.matches) {
        rafId = null;
        return;
      }

      if (!lastTime) lastTime = time;

      const delta = Math.min(32, time - lastTime);
      lastTime = time;

      if (!paused && cycleWidth > 0) {
        grid.scrollLeft += delta * 0.032;

        if (grid.scrollLeft >= cycleWidth) {
          grid.scrollLeft -= cycleWidth;
        }
      }

      rafId = requestAnimationFrame(frame);
    }

    function startAnimation() {
      stopAnimation();
      lastTime = 0;

      if (!mq.matches || document.hidden) return;

      rafId = requestAnimationFrame(frame);
    }

    function pauseTemporarily() {
      if (!mq.matches) return;

      paused = true;

      if (resumeTimer) {
        clearTimeout(resumeTimer);
      }

      resumeTimer = setTimeout(() => {
        paused = false;
      }, 2200);
    }

    function syncMode() {
      stopAnimation();

      if (mq.matches) {
        grid.classList.add("mobile-two-card-carousel");

        buildLoop();

        requestAnimationFrame(() => {
          grid.scrollLeft = 0;
          measureCycle();
          startAnimation();
        });
      } else {
        grid.classList.remove("mobile-two-card-carousel");

        removeClones();

        grid.scrollLeft = 0;
        cycleWidth = 0;
      }
    }

    grid.addEventListener(
      "touchstart",
      pauseTemporarily,
      { passive: true }
    );

    grid.addEventListener(
      "pointerdown",
      pauseTemporarily,
      { passive: true }
    );

    grid.addEventListener(
      "wheel",
      pauseTemporarily,
      { passive: true }
    );

    grid.addEventListener(
      "scroll",
      () => {
        if (
          mq.matches &&
          cycleWidth > 0 &&
          grid.scrollLeft >= cycleWidth
        ) {
          grid.scrollLeft -= cycleWidth;
        }
      },
      { passive: true }
    );

    document.addEventListener(
      "visibilitychange",
      () => {
        if (document.hidden) {
          stopAnimation();
        } else {
          startAnimation();
        }
      }
    );

    window.addEventListener("resize", () => {
      if (!mq.matches) return;

      requestAnimationFrame(measureCycle);
    });

    if (mq.addEventListener) {
      mq.addEventListener("change", syncMode);
    } else {
      mq.addListener(syncMode);
    }

    syncMode();
  }

  initMobileWorksCarousel();




  function revealOnScroll() {
    const items = $$(".reveal");

    if (!("IntersectionObserver" in window)) {
      items.forEach((item) => item.classList.add("visible"));
      return;
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        entry.target.classList.add("visible");
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.15 });

    items.forEach((item) => observer.observe(item));
  }

  revealOnScroll();

  function filterServices() {
    const search = $("#serviceSearch");
    const cards = $$("[data-service-card]");
    const buttons = $$(".service-filter");
    const servicesList = $(".services-list");
    const detailPanels = $$("[data-service-detail]");
    const detailsWrap = $("#serviceDetailsWrap");

    let active = "all";

    function setActiveButton(serviceKey) {
      buttons.forEach((button) => {
        button.classList.toggle(
          "active",
          (button.dataset.service || "all") === serviceKey
        );
      });
    }

    function render({ focusDetail = false } = {}) {
      const query = search ? search.value.toLowerCase().trim() : "";
      const detailMode = active !== "all";

      if (servicesList) {
        servicesList.classList.toggle("is-detail-mode", detailMode);
        servicesList.style.display = detailMode ? "none" : "";
      }

      cards.forEach((card) => {
        const text = `${card.dataset.searchService || ""} ${card.textContent}`.toLowerCase();

        const visible =
          active === "all" &&
          (!query || text.includes(query));

        card.classList.toggle("hidden", !visible);
        card.style.display = visible ? "" : "none";
        card.classList.toggle(
          "is-selected",
          detailMode && card.dataset.serviceCard === active
        );
      });

      let activePanel = null;

      detailPanels.forEach((panel) => {
        const visible =
          detailMode &&
          panel.dataset.serviceDetail === active;

        panel.classList.toggle("active", visible);
        panel.setAttribute("aria-hidden", visible ? "false" : "true");

        if (visible) activePanel = panel;
      });

      if (focusDetail && activePanel) {
        requestAnimationFrame(() => {
          activePanel.scrollIntoView({
            behavior: "smooth",
            block: "start"
          });
        });
      }
    }

    function openService(serviceKey, { focusDetail = true } = {}) {
      if (!serviceKey) return;

      active = serviceKey;
      setActiveButton(active);
      render({ focusDetail });
    }

    buttons.forEach((button) => {
      button.addEventListener("click", () => {
        active = button.dataset.service || "all";
        setActiveButton(active);
        render({ focusDetail: active !== "all" });
      });
    });

    cards.forEach((card) => {
      card.addEventListener("click", (event) => {
        // Linkul "Vezi lucrările" trebuie să rămână link normal.
        if (event.target.closest(".service-example")) return;

        event.preventDefault();
        openService(card.dataset.serviceCard || card.dataset.openService);
      });

      card.addEventListener("keydown", (event) => {
        if (event.key !== "Enter" && event.key !== " ") return;
        if (event.target.closest(".service-example")) return;

        event.preventDefault();
        openService(card.dataset.serviceCard || card.dataset.openService);
      });
    });

    // Și linkul/titlul din interiorul rândului poate deschide explicit panoul.
    $$("[data-open-service]").forEach((trigger) => {
      if (trigger.matches("[data-service-card]")) return;

      trigger.addEventListener("click", (event) => {
        event.preventDefault();
        openService(trigger.dataset.openService);
      });
    });

    if (search) {
      search.addEventListener("input", () => {
        active = "all";
        setActiveButton(active);
        render();
      });
    }

    // Dacă pagina este deschisă cu ?serviceDetail=poligrafie etc.,
    // deschidem direct serviciul respectiv.
    const params = new URLSearchParams(window.location.search);
    const serviceDetailFromUrl = params.get("serviceDetail");

    if (
      serviceDetailFromUrl &&
      [...detailPanels].some(
        (panel) => panel.dataset.serviceDetail === serviceDetailFromUrl
      )
    ) {
      openService(serviceDetailFromUrl, { focusDetail: false });
    } else {
      setActiveButton(active);
      render();
    }
  }

  filterServices();

  function filterPortfolio() {
    if (!portfolioGrid) return;

    const search = $("#workSearch");
    const buttons = $$(".filter-btn");
    const noResults = $("#noResults");
    const moreButton = $("#loadMoreWorks");

    const PAGE_SIZE = 16;
    let active = "all";
    let shown = PAGE_SIZE;

    function matches() {
      const query = (search?.value || "").toLowerCase().trim();

      return works.filter((work) => {
        const categoryOk =
          active === "all" || work.category === active;

        const text =
          `${work.search || ""} ${work.title || ""} ` +
          `${work.service || ""} ${work.desc || ""} ${work.tags || ""}`;

        return (
          categoryOk &&
          (!query || text.toLowerCase().includes(query))
        );
      });
    }

    function render(reset = false) {
      if (reset) shown = PAGE_SIZE;

      const list = matches();
      const visible = list.slice(0, shown);

      portfolioGrid.innerHTML =
        visible.map((work) => workCard(work)).join("");

      if (noResults) {
        noResults.style.display = list.length ? "none" : "block";
      }

      if (moreButton) {
        moreButton.hidden = shown >= list.length;
      }

      revealOnScroll();
    }

    buttons.forEach((button) => {
      button.addEventListener("click", () => {
        active = button.dataset.filter || "all";

        buttons.forEach((btn) =>
          btn.classList.toggle("active", btn === button)
        );

        render(true);
      });
    });

    search?.addEventListener("input", () => render(true));

    moreButton?.addEventListener("click", () => {
      shown += PAGE_SIZE;
      render();
    });

    const params = new URLSearchParams(location.search);
    const filter = params.get("filter");
    const query = params.get("search");

    if (search && query) search.value = query;

    const selected = filter
      ? $(`.filter-btn[data-filter="${filter}"]`)
      : null;

    if (selected && !selected.hidden) {
      selected.click();
    } else {
      render(true);
    }
  }

  filterPortfolio();

  function carousel(trackSelector, speed = 0.45) {
    const track = $(trackSelector);
    if (!track) return;

    const parent = track.parentElement;
    let offset = 0;
    let paused = false;

    function gap() {
      return parseFloat(getComputedStyle(track).gap) || 0;
    }

    function firstWidth() {
      const first = track.children[0];
      return first ? first.getBoundingClientRect().width + gap() : 0;
    }

    function move() {
      if (!paused) {
        offset -= speed;

        if (Math.abs(offset) >= firstWidth()) {
          offset += firstWidth();
          track.appendChild(track.firstElementChild);
        }

        track.style.transform = `translateX(${offset}px)`;
      }

      requestAnimationFrame(move);
    }

    if (parent) {
      parent.addEventListener("mouseenter", () => paused = true);
      parent.addEventListener("mouseleave", () => paused = false);
      parent.addEventListener("focusin", () => paused = true);
      parent.addEventListener("focusout", () => paused = false);
    }

    move();
  }


  let galleryItems = [];
  let galleryIndex = 0;
  let projectMedia = [];
  let projectMediaIndex = 0;

  const modal = $("#galleryModal");
  const modalImg = $("#galleryModalImage");
  const modalVideo = $("#galleryModalVideo");
  const modalTitle = $("#galleryModalTitle");
  const modalService = $("#galleryModalService");
  const modalDesc = $("#galleryModalDesc");
  const modalTags = $("#galleryModalTags");
  const modalThumbs = $("#galleryThumbs");
  const galleryOrderBtn = $("#galleryOrderBtn");

  function updateGalleryItems() {
    galleryItems = [...$$(".gallery-item")].filter((item) => {
      if (item.closest("[data-carousel-clone]")) return false;

      const card = item.closest(".portfolio-card, .work-card");

      if (!card) return true;

      const computed = window.getComputedStyle(card);

      return (
        computed.display !== "none" &&
        computed.visibility !== "hidden" &&
        !card.classList.contains("hidden")
      );
    });
  }

  function getProjectMedia(item) {
    if (!item) return [];

    try {
      const decoded = decodeURIComponent(
        item.dataset.media || ""
      );

      const parsed = JSON.parse(decoded);

      if (Array.isArray(parsed)) {
        return parsed.slice(0, 4);
      }
    } catch (error) {
      console.warn("Media proiect invalidă:", error);
    }

    return [];
  }

  function stopModalVideo() {
    if (!modalVideo) return;

    modalVideo.pause();
    modalVideo.removeAttribute("src");
    modalVideo.load();
  }

  function renderProjectMedia() {
    const media = projectMedia[projectMediaIndex];

    if (!media) return;

    // Mai întâi ascundem ambele tipuri de media.
    // Apoi afișăm doar elementul activ.
    if (modalImg) {
      modalImg.hidden = true;
      modalImg.removeAttribute("src");
    }

    if (modalVideo) {
      modalVideo.pause();
      modalVideo.hidden = true;
      modalVideo.removeAttribute("src");
      modalVideo.load();
    }

    if (media.type === "video") {
      if (modalVideo) {
        modalVideo.src = media.src;
        modalVideo.hidden = false;

        // Autoplay-ul modern funcționează sigur când video-ul
        // pornește fără sunet. Utilizatorul poate activa sunetul
        // din controalele playerului.
        modalVideo.muted = true;
        modalVideo.autoplay = true;
        modalVideo.loop = true;
        modalVideo.controls = true;
        modalVideo.playsInline = true;

        modalVideo.load();

        const playPromise = modalVideo.play();

        if (playPromise?.catch) {
          playPromise.catch(() => {
            // Dacă browserul blochează autoplay-ul,
            // controalele rămân disponibile pentru Play manual.
          });
        }
      }
    } else if (modalImg) {
      modalImg.src = media.src;
      modalImg.alt =
        `${modalTitle?.textContent || "Proiect ArtLife Design"} — imaginea ${projectMediaIndex + 1}`;
      modalImg.hidden = false;
    }

    renderThumbnails();
  }

  function renderThumbnails() {
    if (!modalThumbs) return;

    modalThumbs.innerHTML = "";

    if (projectMedia.length <= 1) {
      modalThumbs.hidden = true;
      return;
    }

    modalThumbs.hidden = false;

    projectMedia.forEach((media, index) => {
      const button = document.createElement("button");

      button.type = "button";
      button.className = "gallery-thumb";
      button.classList.toggle(
        "active",
        index === projectMediaIndex
      );

      button.setAttribute(
        "aria-label",
        `Afișează fișierul ${index + 1} din proiect`
      );

      let preview;

      if (media.type === "video") {
        preview = document.createElement("video");
        preview.src = media.src;
        preview.muted = true;
        preview.loop = true;
        preview.autoplay = true;
        preview.preload = "metadata";
        preview.playsInline = true;
      } else {
        preview = document.createElement("img");
        preview.src = media.src;
        preview.alt = "";
        preview.loading = "lazy";
      }

      button.appendChild(preview);

      button.addEventListener("click", () => {
        projectMediaIndex = index;
        renderProjectMedia();
      });

      modalThumbs.appendChild(button);
    });
  }

  function previousPhoto() {
    if (projectMedia.length <= 1) return;

    projectMediaIndex =
      (projectMediaIndex - 1 + projectMedia.length) %
      projectMedia.length;

    renderProjectMedia();
  }

  function nextPhoto() {
    if (projectMedia.length <= 1) return;

    projectMediaIndex =
      (projectMediaIndex + 1) % projectMedia.length;

    renderProjectMedia();
  }

  function renderGallery(index) {
    const item = galleryItems[index];

    if (!item) return;

    if (modalTitle) {
      modalTitle.textContent = item.dataset.title || "";
    }

    if (modalService) {
      modalService.textContent = item.dataset.service || "";
    }

    if (modalDesc) {
      modalDesc.textContent = item.dataset.desc || "";
    }

    if (modalTags) {
      modalTags.innerHTML = "";

      (item.dataset.tags || "")
        .split(",")
        .map((tag) => tag.trim())
        .filter(Boolean)
        .forEach((tag) => {
          const span = document.createElement("span");
          span.textContent = tag;
          modalTags.appendChild(span);
        });
    }

    if (galleryOrderBtn) {
      const service = item.dataset.service || "";

      galleryOrderBtn.dataset.orderService = service;

      galleryOrderBtn.href = $("#contact")
        ? "#contact"
        : `index.html?service=${encodeURIComponent(service)}#contact`;
    }

    projectMedia = getProjectMedia(item);
    projectMediaIndex = 0;

    renderProjectMedia();
  }

  function openGalleryFromItem(item) {
    if (!modal || !item) return;

    updateGalleryItems();

    let sourceItem = item;

    const cloneCard = item.closest(
      "[data-carousel-clone]"
    );

    if (cloneCard) {
      const sourceIndex = Number(
        cloneCard.dataset.carouselSourceIndex
      );

      const originalCards = [
        ...document.querySelectorAll(
          "#homeWorksGrid .work-card:not([data-carousel-clone])"
        )
      ];

      sourceItem =
        originalCards[sourceIndex]
          ?.querySelector(".gallery-item") ||
        item;
    }

    const index = galleryItems.indexOf(sourceItem);

    if (index < 0) return;

    galleryIndex = index;
    renderGallery(galleryIndex);

    modal.classList.add("active");
    document.body.classList.add("gallery-open");
  }

  function closeGallery() {
    if (!modal) return;

    modal.classList.remove("active");
    document.body.classList.remove("gallery-open");

    if (modalImg) {
      modalImg.removeAttribute("src");
    }

    stopModalVideo();
  }

  function nextProject() {
    updateGalleryItems();

    if (!galleryItems.length) return;

    galleryIndex =
      (galleryIndex + 1) % galleryItems.length;

    renderGallery(galleryIndex);
  }

  function previousProject() {
    updateGalleryItems();

    if (!galleryItems.length) return;

    galleryIndex =
      (galleryIndex - 1 + galleryItems.length) %
      galleryItems.length;

    renderGallery(galleryIndex);
  }

  document.addEventListener("click", (event) => {
    const item = event.target.closest(".gallery-item");

    if (item) {
      event.preventDefault();
      openGalleryFromItem(item);
      return;
    }

    const openButton = event.target.closest(
      "[data-open-card-work]"
    );

    if (openButton) {
      event.preventDefault();

      const media = openButton
        .closest(".portfolio-card")
        ?.querySelector(".gallery-item");

      if (media) {
        openGalleryFromItem(media);
      }

      return;
    }

    const card = event.target.closest(
      ".work-card, .portfolio-card"
    );

    if (
      card &&
      !event.target.closest("a") &&
      !event.target.closest("button")
    ) {
      const media = card.querySelector(".gallery-item");

      if (media) {
        event.preventDefault();
        openGalleryFromItem(media);
      }
    }
  });

  $("#galleryModalClose")
    ?.addEventListener("click", closeGallery);

  $("#galleryPrev")
    ?.addEventListener("click", previousProject);

  $("#galleryNext")
    ?.addEventListener("click", nextProject);

  if (modal) {
    modal.addEventListener("click", (event) => {
      if (event.target === modal) {
        closeGallery();
      }
    });
  }

  document.addEventListener("keydown", (event) => {
    if (
      !modal ||
      !modal.classList.contains("active")
    ) {
      return;
    }

    if (event.key === "Escape") {
      closeGallery();
    }

    if (event.key === "ArrowLeft") {
      previousProject();
    }

    if (event.key === "ArrowRight") {
      nextProject();
    }
  });

  const imageWrap = $(".gallery-image-stage");

  if (imageWrap) {
    let touchStartX = 0;
    let touchStartY = 0;

    imageWrap.addEventListener(
      "touchstart",
      (event) => {
        const touch = event.changedTouches[0];

        touchStartX = touch.clientX;
        touchStartY = touch.clientY;
      },
      { passive: true }
    );

    imageWrap.addEventListener(
      "touchend",
      (event) => {
        const touch = event.changedTouches[0];

        const deltaX =
          touch.clientX - touchStartX;

        const deltaY =
          touch.clientY - touchStartY;

        if (
          Math.abs(deltaX) < 50 ||
          Math.abs(deltaX) <= Math.abs(deltaY)
        ) {
          return;
        }

        if (projectMedia.length > 1) {
          deltaX < 0
            ? nextPhoto()
            : previousPhoto();
        } else {
          deltaX < 0
            ? nextProject()
            : previousProject();
        }
      },
      { passive: true }
    );
  }

  function clearErrors(form) {
    form.querySelectorAll(".field-error").forEach((error) => error.remove());
    form.querySelectorAll(".field-invalid").forEach((field) => {
      field.classList.remove("field-invalid");
    });
  }

  function showFieldError(field, text) {
    if (!field) return;

    field.classList.add("field-invalid");

    const error = document.createElement("span");
    error.className = "field-error";
    error.textContent = text;

    field.insertAdjacentElement("afterend", error);
    field.focus();
  }

  function validateForm(form) {
    clearErrors(form);

    const fields = [
      {
        el: form.querySelector('input[name="nume"]'),
        message: "Scrie numele și prenumele tău."
      },
      {
        el: form.querySelector('input[name="telefon"]'),
        message: "Scrie numărul tău de telefon."
      },
      {
        el: form.querySelector('input[name="email"]'),
        message: "Scrie adresa ta de email."
      },
      {
        el: form.querySelector('select[name="serviciu"]'),
        message: "Alege o opțiune sau consultare."
      },
      {
        el: form.querySelector('textarea[name="mesaj"]'),
        message: "Scrie mesajul tău."
      }
    ];

    for (const field of fields) {
      if (!field.el || !field.el.value.trim()) {
        showFieldError(field.el, field.message);
        return false;
      }
    }

    const email = form.querySelector('input[name="email"]');

    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
      showFieldError(email, "Scrie o adresă de email validă.");
      return false;
    }

    return true;
  }


  function getChisinauWorkStatus() {
    const parts = new Intl.DateTimeFormat("en-GB", {
      timeZone: "Europe/Chisinau",
      weekday: "short",
      hour: "2-digit",
      minute: "2-digit",
      hour12: false
    }).formatToParts(new Date());

    const map = Object.fromEntries(parts.map((part) => [part.type, part.value]));
    const minutes = Number(map.hour) * 60 + Number(map.minute);
    const workingDay = ["Mon", "Tue", "Wed", "Thu", "Fri"].includes(map.weekday);
    const workingHours = minutes >= 9 * 60 && minutes < 18 * 60;

    return workingDay && workingHours;
  }

  const workStatus = $("#workStatus");
  if (workStatus) {
    const open = getChisinauWorkStatus();
    workStatus.textContent = open ? "· acum suntem în program" : "· momentan în afara programului";
    workStatus.classList.toggle("is-open", open);
  }

  $$(".ajax-form").forEach((form) => {
    form.setAttribute("novalidate", "novalidate");

    form.addEventListener("input", () => clearErrors(form));

    form.addEventListener("submit", async (event) => {
      event.preventDefault();

      const messageBox = form.querySelector(".form-message");
      const button = form.querySelector('button[type="submit"]');

      if (!validateForm(form)) return;

      if (messageBox) {
        messageBox.textContent = "Se trimite mesajul...";
        messageBox.className = "form-message";
      }

      if (button) {
        button.disabled = true;
        button.style.opacity = "0.7";
      }

      try {
        const response = await fetch(form.action, {
          method: "POST",
          body: new FormData(form),
          headers: {
            Accept: "application/json"
          }
        });

        if (!response.ok) throw new Error();

        form.reset();

        const inProgram = getChisinauWorkStatus();

        if (messageBox) {
          messageBox.textContent = inProgram
            ? "Mesajul a fost trimis cu succes. Echipa Art Life Design va reveni cu un răspuns."
            : "Mesajul a fost trimis. Momentan suntem în afara programului de lucru și vom reveni în următorul interval de lucru.";
          messageBox.className = "form-message success";
        }

        showToast(
          inProgram ? "Mesaj trimis cu succes." : "Mesaj trimis. Revenim în următorul interval de lucru.",
          "success"
        );
      } catch {
        if (messageBox) {
          messageBox.textContent = "Mesajul nu a fost trimis. Verifică datele și încearcă din nou.";
          messageBox.className = "form-message error";
        }

        showToast("Mesajul nu a fost trimis.", "error");
      }

      if (button) {
        button.disabled = false;
        button.style.opacity = "";
      }
    });
  });

  // Subtle motion for the About image.
  const aboutVisual = document.querySelector("#aboutVisual img");
  if (aboutVisual && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    window.addEventListener("scroll", () => {
      const box = aboutVisual.parentElement.getBoundingClientRect();
      if (box.bottom > 0 && box.top < window.innerHeight) {
        const progress = (window.innerHeight - box.top) / (window.innerHeight + box.height);
        const shift = (progress - 0.5) * 14;
        aboutVisual.style.transform = `scale(1.045) translateY(${shift}px)`;
      }
    }, { passive: true });
  }

  // Prefer Gmail on mobile; fall back to Gmail web if the app is unavailable.
  document.querySelectorAll(".gmail-launch").forEach((link) => {
    link.addEventListener("click", (event) => {
      const email = link.dataset.email;
      if (!email) return;

      const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
      if (!isMobile) return; // Desktop uses the Gmail web URL from href.

      event.preventDefault();
      const fallback = link.href;
      const appUrl = `googlegmail:///co?to=${encodeURIComponent(email)}`;

      let pageHidden = false;
      const onVisibility = () => {
        if (document.hidden) pageHidden = true;
      };
      document.addEventListener("visibilitychange", onVisibility, { once: true });

      window.location.href = appUrl;

      window.setTimeout(() => {
        if (!pageHidden) window.location.href = fallback;
      }, 900);
    });
  });


});

/* =========================================================
   ART LIFE DESIGN — BOTPRESS WEBCHAT
   ---------------------------------------------------------
   Design restaurat:
   - launcher verde Art Life Design;
   - iconiță chat în launcher;
   - notificare albă cu avatar robot + punct online;
   - culoare principală Botpress #1F4D3A;
   - nu modifică designul restului site-ului;
   - păstrează logica actuală pentru sesiune și transcript.
   ========================================================= */

(() => {
  "use strict";

  const CLIENT_ID = "5f16efb4-a4db-46f0-a01b-df2f36f2f4a7";
  const BOT_ID = "133db566-e14b-4bfb-bf62-c19460ad72d7";
  const TEASER_DELAY = 3200;

  let botpressInitialized = false;
  let webchatOpen = false;
  let currentConversationId = "";
  let transcript = [];
  let transcriptSyncTimer = null;

  function getNavigationType() {
    const entry = performance.getEntriesByType("navigation")[0];
    return entry ? entry.type : "navigate";
  }

  function isInternalNavigation() {
    if (!document.referrer) return false;

    try {
      return new URL(document.referrer).origin === window.location.origin;
    } catch {
      return false;
    }
  }

  function prepareSession() {
    const navigationType = getNavigationType();
    const internal = isInternalNavigation();

    const freshVisit =
      navigationType === "reload" ||
      !internal;

    if (!freshVisit) return;

    try {
      sessionStorage.removeItem("artlife_chat_transcript_json");
      sessionStorage.removeItem("artlife_chat_transcript");
      sessionStorage.removeItem("artlife_chat_conversation");
      sessionStorage.removeItem("artlife_chat_teaser_seen");
    } catch (error) {
      console.warn(
        "Art Life Design: sesiunea locală nu a putut fi resetată.",
        error
      );
    }
  }

  function loadTranscript() {
    try {
      const saved =
        sessionStorage.getItem("artlife_chat_transcript_json");

      transcript = saved ? JSON.parse(saved) : [];

      if (!Array.isArray(transcript)) {
        transcript = [];
      }
    } catch {
      transcript = [];
    }
  }

  function extractMessageText(message) {
    if (!message) return "";

    const payload = message.payload || {};

    if (typeof payload.text === "string") {
      return payload.text.trim();
    }

    if (typeof payload.title === "string") {
      return payload.title.trim();
    }

    if (typeof payload.message === "string") {
      return payload.message.trim();
    }

    if (Array.isArray(payload.options)) {
      return payload.options
        .map((option) => {
          if (typeof option === "string") {
            return option;
          }

          return option?.label || option?.value || "";
        })
        .filter(Boolean)
        .join(" / ");
    }

    return "";
  }

  function transcriptAsText() {
    return transcript
      .map((entry) => `${entry.speaker}: ${entry.text}`)
      .join("\n");
  }

  function persistTranscript() {
    try {
      sessionStorage.setItem(
        "artlife_chat_transcript_json",
        JSON.stringify(transcript)
      );

      sessionStorage.setItem(
        "artlife_chat_transcript",
        transcriptAsText()
      );

      if (currentConversationId) {
        sessionStorage.setItem(
          "artlife_chat_conversation",
          currentConversationId
        );
      }
    } catch (error) {
      console.warn(
        "Art Life Design: transcriptul nu a putut fi salvat.",
        error
      );
    }

    if (
      window.botpress &&
      typeof window.botpress.updateUser === "function"
    ) {
      window.clearTimeout(transcriptSyncTimer);

      transcriptSyncTimer = window.setTimeout(() => {
        window.botpress
          .updateUser({
            data: {
              artlifeTranscript: transcriptAsText(),
              artlifeConversationId: currentConversationId || ""
            }
          })
          .catch((error) => {
            console.warn(
              "Art Life Design: transcriptul nu a putut fi sincronizat cu Botpress.",
              error
            );
          });
      }, 220);
    }
  }

  function addTranscriptEntry(message) {
    const text = extractMessageText(message);
    if (!text) return;

    const speaker =
      message?.direction === "outgoing"
        ? "Art Life Design"
        : "Client";

    const id = message?.id || "";

    if (
      id &&
      transcript.some((entry) => entry.id === id)
    ) {
      return;
    }

    const last = transcript[transcript.length - 1];

    if (
      !id &&
      last &&
      last.speaker === speaker &&
      last.text === text
    ) {
      return;
    }

    transcript.push({
      id,
      speaker,
      text
    });

    persistTranscript();
  }

  function hideNativeLauncher() {
    document.documentElement.classList.add(
      "artlife-custom-chat"
    );
  }

  function createLauncher() {
    let launcher =
      document.getElementById("artlifeChatLauncher");

    if (launcher) {
      return launcher;
    }

    launcher = document.createElement("button");
    launcher.id = "artlifeChatLauncher";
    launcher.className = "artlife-chat-launcher";
    launcher.type = "button";
    launcher.setAttribute(
      "aria-label",
      "Deschide asistentul virtual Art Life Design"
    );

    launcher.innerHTML = `
      <i class="bi bi-chat-dots-fill" aria-hidden="true"></i>
    `;

    document.body.appendChild(launcher);

    launcher.addEventListener("click", (event) => {
      event.preventDefault();

      if (!window.botpress) {
        return;
      }

      if (
        webchatOpen &&
        typeof window.botpress.close === "function"
      ) {
        window.botpress.close();
        return;
      }

      window.__artlifeHideChatTeaser?.();

      if (typeof window.botpress.open === "function") {
        window.botpress.open();
      }
    });

    return launcher;
  }

  function createTeaser() {
    let teaser =
      document.getElementById("artlifeChatTeaser");

    if (teaser) {
      return teaser;
    }

    teaser = document.createElement("div");
    teaser.id = "artlifeChatTeaser";
    teaser.className = "artlife-chat-teaser";
    teaser.setAttribute("role", "button");
    teaser.setAttribute("tabindex", "0");
    teaser.setAttribute(
      "aria-label",
      "Deschide asistentul virtual Art Life Design"
    );

    teaser.innerHTML = `
      <div class="artlife-chat-teaser-avatar" aria-hidden="true">
        <i class="bi bi-robot"></i>
        <span class="artlife-chat-online-dot"></span>
      </div>

      <div class="artlife-chat-teaser-copy">
        <strong>Bună 👋 Sunt aici să vă ajut.</strong>
        <span>Apăsați pentru a deschide conversația.</span>
      </div>

      <button
        type="button"
        class="artlife-chat-teaser-close"
        aria-label="Închide notificarea"
      >
        ×
      </button>
    `;

    document.body.appendChild(teaser);

    const closeButton = teaser.querySelector(
      ".artlife-chat-teaser-close"
    );

    const hide = () => {
      teaser.classList.remove("show");

      window.setTimeout(() => {
        if (!teaser.classList.contains("show")) {
          teaser.style.display = "none";
        }
      }, 300);
    };

    const show = () => {
      if (webchatOpen) {
        return;
      }

      teaser.style.display = "grid";

      requestAnimationFrame(() => {
        teaser.classList.add("show");
      });
    };

    const open = () => {
      hide();

      try {
        sessionStorage.setItem(
          "artlife_chat_teaser_seen",
          "1"
        );
      } catch {}

      if (
        window.botpress &&
        typeof window.botpress.open === "function"
      ) {
        window.botpress.open();
      }
    };

    teaser.addEventListener("click", (event) => {
      if (
        event.target.closest(
          ".artlife-chat-teaser-close"
        )
      ) {
        return;
      }

      open();
    });

    teaser.addEventListener("keydown", (event) => {
      if (
        event.key === "Enter" ||
        event.key === " "
      ) {
        event.preventDefault();
        open();
      }
    });

    closeButton?.addEventListener(
      "click",
      (event) => {
        event.preventDefault();
        event.stopPropagation();

        try {
          sessionStorage.setItem(
            "artlife_chat_teaser_seen",
            "1"
          );
        } catch {}

        hide();
      }
    );

    window.__artlifeShowChatTeaser = show;
    window.__artlifeHideChatTeaser = hide;

    return teaser;
  }

  function initBotpress() {
    prepareSession();
    loadTranscript();
    hideNativeLauncher();

    const launcher = createLauncher();
    createTeaser();

    const startedAt = Date.now();

    const timer = window.setInterval(() => {
      if (
        window.botpress &&
        typeof window.botpress.init === "function" &&
        !botpressInitialized
      ) {
        botpressInitialized = true;
        window.clearInterval(timer);

        /*
          Păstrăm resetarea sesiunii Botpress deja folosită în versiunea curentă,
          fără a modifica stilul site-ului.
        */
        try {
          Object.keys(localStorage).forEach((key) => {
            const lower = key.toLowerCase();

            if (
              lower.includes("botpress") ||
              lower.includes("webchat")
            ) {
              localStorage.removeItem(key);
            }
          });
        } catch (error) {
          console.warn(
            "Art Life Design: sesiunea Botpress nu a putut fi curățată.",
            error
          );
        }

        window.botpress.init({
          botId: BOT_ID,
          clientId: CLIENT_ID,

          configuration: {
            botName: "Art Life Design",
            botDescription:
              "Asistent virtual Art Life Design",
            website: {},
            email: {},
            phone: {},
            termsOfService: {},
            privacyPolicy: {},
            color: "#1F4D3A",
            variant: "solid",
            themeMode: "light",
            fontFamily: "inter",
            radius: 2,
            composerPlaceholder:
              "Scrieți mesajul..."
          }
        });

        window.botpress.on(
          "webchat:initialized",
          () => {
            persistTranscript();

            let teaserSeen = false;

            try {
              teaserSeen =
                sessionStorage.getItem(
                  "artlife_chat_teaser_seen"
                ) === "1";
            } catch {}

            if (!teaserSeen) {
              window.setTimeout(() => {
                window.__artlifeShowChatTeaser?.();
              }, TEASER_DELAY);
            }
          }
        );

        window.botpress.on(
          "webchat:opened",
          () => {
            webchatOpen = true;
            launcher?.classList.add("is-hidden");
            window.__artlifeHideChatTeaser?.();

            try {
              sessionStorage.setItem(
                "artlife_chat_teaser_seen",
                "1"
              );
            } catch {}
          }
        );

        window.botpress.on(
          "webchat:closed",
          () => {
            webchatOpen = false;
            launcher?.classList.remove("is-hidden");
            persistTranscript();
          }
        );

        window.botpress.on(
          "conversation",
          (conversationId) => {
            currentConversationId =
              typeof conversationId === "string"
                ? conversationId
                : "";

            persistTranscript();
          }
        );

        window.botpress.on(
          "message",
          addTranscriptEntry
        );

        window.botpress.on(
          "error",
          (error) => {
            console.warn(
              "Art Life Design / Botpress:",
              error
            );
          }
        );
      }

      if (Date.now() - startedAt > 20000) {
        window.clearInterval(timer);
      }
    }, 200);
  }

  if (document.readyState === "loading") {
    document.addEventListener(
      "DOMContentLoaded",
      initBotpress,
      { once: true }
    );
  } else {
    initBotpress();
  }
})();