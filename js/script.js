document.addEventListener("DOMContentLoaded", () => {
  const works = [
    {
      title: "Bonjour Tour",
      service: "Litere în volum & Standuri",
      category: "volum",
      image: "images/BonjourLightBox.jpg",
      date: "2025",
      desc: "Light box personalizat pentru promovare vizuală.",
      tags: "Light Box, Exterior, Vizibilitate",
      search: "bonjour tour light box litere volum stand"
    },
    {
      title: "Urnă personalizată",
      service: "P.O.S.M.",
      category: "posm",
      image: "images/box.jpg",
      date: "2025",
      desc: "Material promoțional pentru campanii.",
      tags: "Urnă, Print, P.O.S.M.",
      search: "urna box donatie posm campanie"
    },
    {
      title: "Ambalaj Market",
      service: "Branding Auto",
      category: "auto",
      image: "images/brand_auto1.jpg",
      date: "2025",
      desc: "Colantare auto pentru transport comercial.",
      tags: "Auto, Colantare, Comercial",
      search: "ambalaj market auto branding colantare"
    },
    {
      title: "Documente MD",
      service: "Branding Auto",
      category: "auto",
      image: "images/brand_auto2.jpg",
      date: "2025",
      desc: "Grafică aplicată pe transport comercial.",
      tags: "Microbuz, Colantare, Grafică",
      search: "documente md auto microbus branding colantare"
    },
    {
      title: "Cub promoțional",
      service: "P.O.S.M.",
      category: "posm",
      image: "images/cub-sticla.jpg",
      date: "2025",
      desc: "Element personalizat pentru expunere vizuală.",
      tags: "Display, Print, Expunere",
      search: "cub sticla vara aquacity posm stand"
    },
    {
      title: "Stand promoțional",
      service: "P.O.S.M.",
      category: "posm",
      image: "images/cub-sticla0.jpg",
      date: "2025",
      desc: "Material personalizat pentru campanie.",
      tags: "Stand, Print, P.O.S.M.",
      search: "stand vara aquacity posm"
    },
    {
      title: "Fișe de preț",
      service: "Poligrafie",
      category: "poligrafie",
      image: "images/fise_pret.jpg",
      date: "2025",
      desc: "Materiale tipărite pentru prezentarea produselor.",
      tags: "Print, Meniu, Produs",
      search: "fise pret meniuri poligrafie print"
    },
    {
      title: "Good Break",
      service: "Litere în volum & Standuri",
      category: "volum",
      image: "images/good-break.jpg",
      date: "2025",
      desc: "Reclamă luminoasă pentru fațadă.",
      tags: "Fațadă, Luminos, Volumetric",
      search: "good break litere volum light box fatada"
    },
    {
      title: "Etichete produs",
      service: "Poligrafie",
      category: "poligrafie",
      image: "images/inghetata_uv.jpg",
      date: "2025",
      desc: "Print personalizat pentru produse.",
      tags: "Etichete, Print, Produs",
      search: "inghetata uv print etichete poligrafie"
    },
    {
      title: "Mango Passion Fruit",
      service: "Poligrafie",
      category: "poligrafie",
      image: "images/inghetata-uv1.jpg",
      date: "2025",
      desc: "Etichete personalizate pentru produse.",
      tags: "Etichete, UV, Produs",
      search: "mango passion fruit uv print etichete poligrafie"
    },
    {
      title: "Logo ArtLife Design",
      service: "Litere în volum & Standuri",
      category: "volum",
      image: "images/litere_volum1.jpg",
      date: "2025",
      desc: "Element volumetric pentru identitate vizuală.",
      tags: "Logo, Interior, Volumetric",
      search: "art life design litere volum logo interior"
    },
    {
      title: "Smile Dent",
      service: "Litere în volum & Standuri",
      category: "volum",
      image: "images/litere_volum2.jpg",
      date: "2025",
      desc: "Reclamă de fațadă cu litere volumetrice.",
      tags: "Fațadă, Clinică, Volumetric",
      search: "smile dent litere volum fatada"
    },
    {
      title: "Ambalaj Market",
      service: "Litere în volum & Standuri",
      category: "volum",
      image: "images/litere_volum3.jpg",
      date: "2025",
      desc: "Elemente de fațadă pentru spațiu comercial.",
      tags: "Fațadă, Litere, Exterior",
      search: "ambalaj market litere volum stand"
    },
    {
      title: "Lux Tavane",
      service: "Branding Auto",
      category: "auto",
      image: "images/lux-tavene.jpg",
      date: "2025",
      desc: "Colantare auto pentru servicii locale.",
      tags: "Auto, Colantare, Grafică",
      search: "lux tavane auto branding colantare"
    },
    {
      title: "Stickere personalizate",
      service: "Poligrafie",
      category: "poligrafie",
      image: "images/plotter-2.jpg",
      date: "2025",
      desc: "Decupare și print pentru etichete de brand.",
      tags: "Stickere, Plotter, Etichete",
      search: "stickere etichete plotter poligrafie"
    }
  ];

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

  function workCard(work, isHome = false) {
    const cardClass = isHome ? "work-card reveal" : "portfolio-card reveal";
    const mediaClass = isHome ? "work-img gallery-item" : "portfolio-media gallery-item";
    const orderHref = isHome ? "#contact" : `index.html?service=${encodeURIComponent(work.service)}#contact`;

    return `
      <article class="${cardClass}" data-category="${work.category}" data-search="${work.search}">
        <button class="${mediaClass}"
          type="button"
          data-full="${work.image}"
          data-title="${work.title}"
          data-service="${work.service}"
          data-date="${work.date}"
          data-desc="${work.desc}"
          data-tags="${work.tags}">
          <img src="${work.image}" alt="${work.title}" loading="lazy">
          <span>${work.service.replace("Litere în volum & Standuri", "Litere & Standuri")}</span>
          ${isHome ? `<div class="media-overlay"><i class="bi bi-arrows-fullscreen"></i><small>Vezi lucrarea</small></div>` : ""}
        </button>

        <div class="${isHome ? "work-info" : "portfolio-content"}">
          <small>${work.service}</small>
          <h3>${work.title}</h3>
          <p>${work.desc}</p>

          <div class="work-actions">
            <a href="${orderHref}" class="work-order-btn btn-arrow" data-order-service="${work.service}">
              Produs similar
              <i class="bi bi-arrow-right"></i>
            </a>
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

  if (portfolioGrid) {
    portfolioGrid.innerHTML = works.map((work) => workCard(work)).join("");
  }

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

    let active = "all";

    function render() {
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
      });

      detailPanels.forEach((panel) => {
        const visible =
          detailMode &&
          panel.dataset.serviceDetail === active;

        panel.classList.toggle("active", visible);
        panel.setAttribute("aria-hidden", visible ? "false" : "true");
      });
    }

    buttons.forEach((button) => {
      button.addEventListener("click", () => {
        active = button.dataset.service || "all";

        buttons.forEach((btn) => btn.classList.remove("active"));
        button.classList.add("active");

        render();
      });
    });

    if (search) {
      search.addEventListener("input", render);
    }

    render();
  }

  filterServices();

  function filterPortfolio() {
    const search = $("#workSearch");
    const buttons = $$(".filter-btn");
    const noResults = $("#noResults");
    let active = "all";

    function render() {
      const query = search ? search.value.toLowerCase().trim() : "";
      let visibleCount = 0;

      $$(".portfolio-card").forEach((card) => {
        const category = card.dataset.category;
        const text = `${card.dataset.search || ""} ${card.textContent}`.toLowerCase();

        const visible =
          (active === "all" || active === category) &&
          (!query || text.includes(query));

        card.style.display = visible ? "" : "none";

        if (visible) visibleCount++;
      });

      if (noResults) {
        noResults.style.display = visibleCount ? "none" : "block";
      }
    }

    buttons.forEach((button) => {
      button.addEventListener("click", () => {
        active = button.dataset.filter || "all";

        buttons.forEach((btn) => btn.classList.remove("active"));
        button.classList.add("active");

        render();
      });
    });

    if (search) search.addEventListener("input", render);

    const params = new URLSearchParams(window.location.search);
    const filterFromUrl = params.get("filter");
    const searchFromUrl = params.get("search");
    const selected = filterFromUrl ? $(`.filter-btn[data-filter="${filterFromUrl}"]`) : null;

    if (search && searchFromUrl) {
      search.value = searchFromUrl;
    }

    if (selected) {
      selected.click();
    } else {
      render();
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

  const modal = $("#galleryModal");
  const modalImg = $("#galleryModalImage");
  const modalTitle = $("#galleryModalTitle");
  const modalService = $("#galleryModalService");
  const modalDate = $("#galleryModalDate");
  const modalDesc = $("#galleryModalDesc");
  const modalTags = $("#galleryModalTags");
  const galleryOrderBtn = $("#galleryOrderBtn");

  function updateGalleryItems() {
    galleryItems = [...$$(".gallery-item")];
  }

  function renderGallery(index) {
    const item = galleryItems[index];
    if (!item || !modalImg) return;

    modalImg.src = item.dataset.full || "";
    modalImg.alt = item.dataset.title || "Lucrare ArtLife Design";

    if (modalTitle) modalTitle.textContent = item.dataset.title || "";
    if (modalService) modalService.textContent = item.dataset.service || "";
    if (modalDate) modalDate.textContent = item.dataset.date || "";
    if (modalDesc) modalDesc.textContent = item.dataset.desc || "";

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
  }

  function openGallery(index) {
    if (!modal || !galleryItems.length) return;

    galleryIndex = index;
    renderGallery(index);
    modal.classList.add("active");
    document.body.style.overflow = "hidden";
  }

  function closeGallery() {
    if (!modal) return;

    modal.classList.remove("active");

    if (modalImg) modalImg.src = "";

    document.body.style.overflow = "";
  }

  function nextGallery() {
    if (!galleryItems.length) return;

    galleryIndex = (galleryIndex + 1) % galleryItems.length;
    renderGallery(galleryIndex);
  }

  function prevGallery() {
    if (!galleryItems.length) return;

    galleryIndex = (galleryIndex - 1 + galleryItems.length) % galleryItems.length;
    renderGallery(galleryIndex);
  }

  updateGalleryItems();

  galleryItems.forEach((item, index) => {
    item.addEventListener("click", () => openGallery(index));
  });

  $("#galleryModalClose")?.addEventListener("click", closeGallery);
  $("#galleryNext")?.addEventListener("click", nextGallery);
  $("#galleryPrev")?.addEventListener("click", prevGallery);

  if (modal) {
    modal.addEventListener("click", (event) => {
      if (event.target === modal) closeGallery();
    });
  }

  document.addEventListener("keydown", (event) => {
    if (!modal || !modal.classList.contains("active")) return;

    if (event.key === "Escape") closeGallery();
    if (event.key === "ArrowRight") nextGallery();
    if (event.key === "ArrowLeft") prevGallery();
  });

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
   ART LIFE DESIGN — BOTPRESS WEBCHAT FINAL
   ---------------------------------------------------------
   Comportament:
   - Nu deschide chatul automat.
   - Afișează o notificare mică după câteva secunde.
   - Click pe notificare -> deschide chatul.
   - Refresh / intrare nouă -> conversație nouă.
   - Navigare internă index <-> lucrari -> păstrează sesiunea.
   - X pe chat -> conversația rămâne.
   - Transcriptul este păstrat temporar și sincronizat în
     user.data.artlifeTranscript pentru Botpress / Make.
   ========================================================= */

(() => {
  const CLIENT_ID = "5f16efb4-a4db-46f0-a01b-df2f36f2f4a7";
  const TEASER_DELAY = 3200;

  let botpressInitialized = false;
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

  /*
    Resetăm numai când este:
    - refresh;
    - acces nou/direct din afara site-ului.

    NU resetăm când utilizatorul merge din index.html în lucrari.html
    sau invers în același tab.
  */
  function prepareSession() {
    const navigationType = getNavigationType();
    const internal = isInternalNavigation();

    const freshVisit =
      navigationType === "reload" ||
      !internal;

    if (!freshVisit) return;

    try {
      sessionStorage.clear();
    } catch (error) {
      console.warn("Art Life Design: sesiunea nu a putut fi resetată.", error);
    }
  }

  function loadTranscript() {
    try {
      const saved = sessionStorage.getItem("artlife_chat_transcript_json");
      transcript = saved ? JSON.parse(saved) : [];
      if (!Array.isArray(transcript)) transcript = [];
    } catch {
      transcript = [];
    }
  }

  function extractMessageText(message) {
    if (!message) return "";

    const payload = message.payload || {};

    if (typeof payload.text === "string") return payload.text.trim();
    if (typeof payload.title === "string") return payload.title.trim();
    if (typeof payload.message === "string") return payload.message.trim();

    if (Array.isArray(payload.options)) {
      return payload.options
        .map((option) => {
          if (typeof option === "string") return option;
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
      console.warn("Art Life Design: transcriptul nu a putut fi salvat.", error);
    }

    if (
      window.botpress &&
      typeof window.botpress.updateUser === "function"
    ) {
      window.clearTimeout(transcriptSyncTimer);

      transcriptSyncTimer = window.setTimeout(() => {
        window.botpress.updateUser({
          data: {
            artlifeTranscript: transcriptAsText(),
            artlifeConversationId: currentConversationId || ""
          }
        }).catch((error) => {
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

    /*
      Evită doar duplicatele reale / consecutive.
      Nu elimină răspunsuri legitime repetate mai târziu.
    */
    if (id && transcript.some((entry) => entry.id === id)) return;

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

  function createTeaser() {
    if (document.getElementById("artlifeChatTeaser")) return;

    const teaser = document.createElement("div");
    teaser.id = "artlifeChatTeaser";
    teaser.className = "artlife-chat-teaser";
    teaser.setAttribute("role", "button");
    teaser.setAttribute("tabindex", "0");
    teaser.setAttribute(
      "aria-label",
      "Deschide asistentul virtual Art Life Design"
    );

    teaser.innerHTML = `
      <div class="artlife-chat-teaser-icon" aria-hidden="true">
        <i class="bi bi-chat-dots"></i>
      </div>

      <div class="artlife-chat-teaser-copy">
        <strong>Salut 👋 Sunt aici să te ajut</strong>
        <span>Alege rapid ce te interesează.</span>
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

    function hideTeaser() {
      teaser.classList.remove("show");

      window.setTimeout(() => {
        teaser.style.display = "none";
      }, 280);
    }

    function openChat() {
      try {
        sessionStorage.setItem("artlife_chat_teaser_seen", "1");
      } catch {}

      hideTeaser();

      if (
        window.botpress &&
        typeof window.botpress.open === "function"
      ) {
        window.botpress.open();
      }
    }

    teaser.addEventListener("click", (event) => {
      if (event.target.closest(".artlife-chat-teaser-close")) return;
      openChat();
    });

    teaser.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        openChat();
      }
    });

    closeButton?.addEventListener("click", (event) => {
      event.stopPropagation();

      try {
        sessionStorage.setItem("artlife_chat_teaser_seen", "1");
      } catch {}

      hideTeaser();
    });

    let alreadySeen = false;

    try {
      alreadySeen =
        sessionStorage.getItem("artlife_chat_teaser_seen") === "1";
    } catch {}

    if (!alreadySeen) {
      window.setTimeout(() => {
        teaser.style.display = "grid";

        requestAnimationFrame(() => {
          teaser.classList.add("show");
        });
      }, TEASER_DELAY);
    }

    window.__artlifeHideChatTeaser = hideTeaser;
  }

  function initBotpress() {
    prepareSession();
    loadTranscript();
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
          Nu trimitem "De la început" prin sendMessage().
          Astfel nu mai apare ca mesaj al utilizatorului și nu se repetă.
        */
        window.botpress.init({
          clientId: CLIENT_ID
        });

        window.botpress.on("webchat:initialized", () => {
          persistTranscript();
        });

        window.botpress.on("webchat:opened", () => {
          window.__artlifeHideChatTeaser?.();

          try {
            sessionStorage.setItem("artlife_chat_teaser_seen", "1");
          } catch {}
        });

        window.botpress.on("webchat:closed", () => {
          /*
            Intenționat NU resetăm conversația aici.
            X -> redeschidere = aceeași conversație.
          */
          persistTranscript();
        });

        window.botpress.on("conversation", (conversationId) => {
          currentConversationId =
            typeof conversationId === "string"
              ? conversationId
              : "";

          persistTranscript();
        });

        window.botpress.on("message", addTranscriptEntry);

        window.botpress.on("error", (error) => {
          console.warn("Art Life Design / Botpress:", error);
        });
      }

      if (Date.now() - startedAt > 20000) {
        window.clearInterval(timer);
      }
    }, 200);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initBotpress, { once:true });
  } else {
    initBotpress();
  }
})();
