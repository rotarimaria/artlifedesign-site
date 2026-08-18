(() => {
  const $ = s => document.querySelector(s);

  const setText = (s,v) => {
    const el=$(s);
    if (el && v !== undefined) el.textContent=v;
  };

  async function init() {
    try {
      const res=await fetch("api/works-page.php",{cache:"no-store"});
      const data=await res.json();
      if (!res.ok || !data.ok) throw new Error(data.message || `HTTP ${res.status}`);

      const c=data.content || {};

      setText(".back-link",c.back_text);
      setText(".portfolio-header .section-label",c.label);
      setText(".portfolio-header h1",c.title);
      setText(".portfolio-header .container > p",c.intro);
      setText("#noResults",c.no_results);

      const search=$("#workSearch");
      if (search) search.placeholder=c.search_placeholder || "";

      const all=$('[data-filter="all"]');
      if (all) all.textContent=c.filter_all || "Toate";

      const more=$("#loadMoreWorks");
      if (more) more.textContent=c.load_more || "Mai multe";

      setText(".portfolio-contact .section-label",c.cta_label);
      setText(".portfolio-contact h2",c.cta_title);

      const cta=$(".portfolio-contact .btn-main");
      if (cta) {
        const icon=cta.querySelector("i");
        cta.textContent=c.cta_button || "";
        if (icon) cta.append(" ",icon);
      }
    } catch (err) {
      console.error("Works page content:",err);
    }
  }

  document.readyState==="loading"
    ? document.addEventListener("DOMContentLoaded",init,{once:true})
    : init();
})();