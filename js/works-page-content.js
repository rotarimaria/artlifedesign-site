(() => {
  const $ = s => document.querySelector(s);

  const setText = (s, v) => {
    const el = $(s);
    if (el && v !== undefined) el.textContent = v;
  };

  async function init() {
    try {
      const res = await fetch("api/works-page.php", { cache: "no-store" });
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.message || `HTTP ${res.status}`);

      const c = data.content || {};

      setText(".back-link", c.back_text);
      setText(".portfolio-header .section-label", c.label);
      setText(".portfolio-header h1", c.title);
      setText(".portfolio-header .container > p", c.intro);
      setText("#noResults", c.no_results);
      const more = $("#loadMoreWorks");
      if (more && c.load_more) {
        const icon = more.querySelector("i");
        more.textContent = c.load_more;
        if (icon) more.append(" ", icon);
      }
      setText(".portfolio-contact .section-label", c.cta_label);
      setText(".portfolio-contact h2", c.cta_title);

      const search = $("#workSearch");
      if (search) search.placeholder = c.search_placeholder || "";

      const cta = $(".portfolio-contact .btn-main");
      if (cta) {
        const icon = cta.querySelector("i");
        cta.textContent = c.cta_button || "";
        if (icon) cta.append(" ", icon);
      }

      const filters = {
        all: ["filter_all", "1"],
        poligrafie: ["filter_poligrafie", c.show_poligrafie],
        volum: ["filter_volum", c.show_volum],
        posm: ["filter_posm", c.show_posm],
        auto: ["filter_auto", c.show_auto],
        laser: ["filter_laser", c.show_laser]
      };

      Object.entries(filters).forEach(([key, [labelKey, visible]]) => {
        let btn = document.querySelector(`[data-filter="${key}"]`);
if (!btn) return;
        btn.textContent = c[labelKey] || "";
        btn.hidden = visible === "0";
      });
    } catch (err) {
      console.error("Works page content:", err);
    }
  }

  document.readyState === "loading"
    ? document.addEventListener("DOMContentLoaded", init, { once: true })
    : init();
})();