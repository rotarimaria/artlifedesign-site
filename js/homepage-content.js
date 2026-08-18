(() => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const text = (s, v) => { const e = $(s); if (e && v !== undefined) e.textContent = v; };
  const src = (s, v) => { const e = $(s); if (e && v) e.src = v; };

  const setFirstText = (el, value) => {
    if (!el || value === undefined) return;
    const icon = el.querySelector("i");
    el.textContent = value || "";
    if (icon) {
      el.append(" ");
      el.append(icon);
    }
  };

  const crop = (el, c, key) => {
    if (!el) return;
    el.style.objectFit = c[`${key}_fit`] || "cover";
    el.style.objectPosition = "50% 50%";
    el.style.transform =
      `translate(calc(50% - ${c[`${key}_crop_x`] || 50}%),` +
      `calc(50% - ${c[`${key}_crop_y`] || 50}%)) ` +
      `rotate(${c[`${key}_rotation`] || 0}deg) ` +
      `scale(${c[`${key}_zoom`] || 1})`;
    el.style.transformOrigin = "center";
  };

  function apply(c) {
    // Navigație
    [
      ['.nav-links-wrap a[href="#about"]', 'nav_about'],
      ['.nav-links-wrap a[href="#services"]', 'nav_services'],
      ['.nav-links-wrap a[href="#works"]', 'nav_works'],
      ['.nav-links-wrap a[href="#contact"]', 'nav_contact'],
    ].forEach(([s,k]) => text(s,c[k]));
    setFirstText($(".nav-cta"), c.nav_cta);
    src(".logo-svg", c.header_logo);

    // Hero
    text(".hero-kicker", c.hero_kicker);
    const h1 = $(".hero h1");
    if (h1) {
      h1.textContent = `${c.hero_title_main || ""} `;
      const span = document.createElement("span");
      span.textContent = c.hero_title_accent || "";
      h1.append(span);
    }
    text(".hero-content > p", c.hero_text);
    const heroButtons = $$(".hero-actions a");
    setFirstText(heroButtons[0], c.hero_btn_works);
    setFirstText(heroButtons[1], c.hero_btn_quote);
    text(".hero-bottom > span", c.hero_bottom_label);

    const heroVideo = $(".hero-video");
    if (heroVideo) {
      if (c.hero_poster) heroVideo.poster = c.hero_poster;
      const source = heroVideo.querySelector("source");
      if (source && c.hero_video) {
        source.src = c.hero_video;
        heroVideo.load();
        heroVideo.play().catch(() => {});
      }
    }

    // Despre noi
    [
      [".about-section .section-label","about_label"],
      [".about-final-copy h2","about_title"],
      [".about-final-copy .lead","about_lead"],
      [".about-image-note","about_image_note"],
    ].forEach(([s,k]) => text(s,c[k]));
    const aboutP = $(".about-final-copy > p:not(.lead)");
    if (aboutP) aboutP.textContent = c.about_text || "";
    const aboutBtns = $$(".about-final-actions .about-btn");
    setFirstText(aboutBtns[0], c.about_btn_services);
    setFirstText(aboutBtns[1], c.about_btn_portfolio);
    const aboutImg = $(".about-final-visual img");
    if (aboutImg && c.about_image) aboutImg.src = c.about_image;
    crop(aboutImg, c, "about_image");

    // Servicii intro
    text(".services-heading .section-label", c.services_label);
    text(".services-heading h2", c.services_title);
    text(".services-heading > p", c.services_text);

    // Las serviciile în script.js, fiindcă acolo le iau direct din BD.


    // CTA
    text(".project-cta .section-label", c.cta_label);
    text(".project-cta h2", c.cta_title);
    text(".project-cta p", c.cta_text);
    setFirstText($(".project-cta > a"), c.cta_button);

    // Social
    text(".social-heading .section-label", c.social_label);
    text(".social-heading h2", c.social_title);
    const social = [
      ["instagram",0],["tiktok",1],["facebook",2]
    ];
    const networks = $$(".social-network");
    social.forEach(([key,i]) => {
      if (!networks[i]) return;
      const strong = networks[i].querySelector("strong");
      const span = networks[i].querySelector("span");
      if (strong) strong.textContent = c[`${key}_name`] || "";
      if (span) span.textContent = c[`${key}_handle`] || "";
      if (c[`${key}_url`]) networks[i].href = c[`${key}_url`];
    });

    // Contact
    text(".contact-intro .section-label", c.contact_label);
    text(".contact-intro h2", c.contact_title);
    text(".contact-intro p", c.contact_text);
    text(".contact-info-head span", c.contact_direct_label);
    text(".contact-info-head h3", c.contact_direct_title);

    const lines = $$(".contact-info .contact-line");
    const values = [
      c.contact_address,c.contact_phone_1,c.contact_phone_2,
      c.contact_email_1,c.contact_email_2
    ];
    values.forEach((value,i) => {
      const btn = lines[i]?.querySelector(".copy-text");
      if (btn) {
        btn.textContent = value || "";
        btn.dataset.copy = value || "";
      }
    });

    if (lines[1]?.querySelector("a")) lines[1].querySelector("a").href = "tel:" + String(c.contact_phone_1||"").replace(/[^\d+]/g,"");
    if (lines[2]?.querySelector("a")) lines[2].querySelector("a").href = "tel:" + String(c.contact_phone_2||"").replace(/[^\d+]/g,"");

    if (lines[5]) {
      const span = lines[5].querySelector("span:not(.contact-icon-link)");
      if (span) {
        const status = span.querySelector(".work-status");
        span.textContent = `${c.contact_hours || ""} `;
        if (status) span.append(status);
      }
    }

    const map = $(".map-box");
    if (map && c.contact_address) {
      const q = encodeURIComponent(c.contact_address);
      map.href = `https://www.google.com/maps/search/?api=1&query=${q}`;
      const iframe = map.querySelector("iframe");
      if (iframe) iframe.src = `https://www.google.com/maps?q=${q}&output=embed`;
      const label = map.querySelector("span");
      if (label) setFirstText(label, c.contact_map_text);
    }

    // Formular
    const rows = $$(".contact-form-card .form-row label");
    const labels = $$(".contact-form-card form > label");
    const formMap = [
      [rows[0],"form_name_label","form_name_placeholder","input"],
      [rows[1],"form_phone_label","form_phone_placeholder","input"],
      [labels[0],"form_email_label","form_email_placeholder","input"],
      [labels[2],"form_message_label","form_message_placeholder","textarea"],
    ];
    formMap.forEach(([label,k,p,field]) => {
      if (!label) return;
      const control = label.querySelector(field);
      if (control) control.placeholder = c[p] || "";
      const first = [...label.childNodes].find(n => n.nodeType === Node.TEXT_NODE);
      if (first) first.textContent = `${c[k] || ""} `;
    });
    if (labels[1]) {
      const first = [...labels[1].childNodes].find(n => n.nodeType === Node.TEXT_NODE);
      if (first) first.textContent = `${c.form_service_label || ""} `;
    }
    text(".privacy-note", c.form_privacy);
    setFirstText($(".contact-form-card .form-submit"), c.form_submit);

    // Footer
    src(".footer-logo", c.footer_logo);
    text(".footer-brand p", c.footer_text);
    text(".footer-nav-block h4", c.footer_nav_title);
    text(".footer-social-block h4", c.footer_social_title);

    const footerNav = $$(".footer-nav-block .footer-links a");
    [c.nav_about,c.nav_services,c.nav_works,c.nav_contact].forEach((v,i) => {
      if (footerNav[i]) footerNav[i].textContent = v || "";
    });

    const urls = [c.instagram_url,c.tiktok_url,c.facebook_url];
    const names = [c.instagram_name,c.tiktok_name,c.facebook_name];
    const footerSocial = $$(".footer-social-block .footer-links a");
    footerSocial.forEach((a,i) => {
      if (urls[i]) a.href = urls[i];
      if (names[i]) a.textContent = `${names[i]} ↗`;
    });
  }

  async function init() {
    try {
      const res = await fetch("api/homepage.php", {cache:"no-store"});
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.message || `HTTP ${res.status}`);
      apply(data.content || {});
    } catch (err) {
      console.error("Homepage content:", err);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }

  // Aplicăm încă o dată după încărcarea completă a paginii.
  // Astfel, conținutul din admin rămâne sursa finală chiar dacă
  // scriptul principal finalizează inițializarea puțin mai târziu.
  window.addEventListener("load", init, { once: true });
})();