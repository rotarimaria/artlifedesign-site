(() => {
  const editor = document.querySelector("#mediaEditor");
  const stage = document.querySelector("#editorStage");
  const image = document.querySelector("#editorImage");
  const zoom = document.querySelector("#editorZoom");
  const rotation = document.querySelector("#editorRotation");
  const zoomValue = document.querySelector("#editorZoomValue");
  const rotationValue = document.querySelector("#editorRotationValue");

  if (!editor || !stage || !image) return;

  let card = null, state = null, drag = null;

  const clamp = (n,min,max) => Math.max(min,Math.min(max,n));
  const meta = name => card?.querySelector(`[data-meta="${name}"]`);

  function read() {
    return {
      x:+(meta("crop_x")?.value||50),
      y:+(meta("crop_y")?.value||50),
      zoom:+(meta("zoom")?.value||1),
      rotation:+(meta("rotation")?.value||0),
      fit:meta("fit")?.value||"cover"
    };
  }

  function paint(target=image) {
    if (!state || !target) return;
    target.style.setProperty("--crop-x",`${state.x}%`);
    target.style.setProperty("--crop-y",`${state.y}%`);
    target.style.setProperty("--crop-zoom",state.zoom);
    target.style.setProperty("--crop-rotation",`${state.rotation}deg`);
    target.style.setProperty("--crop-fit",state.fit);
    zoom.value=state.zoom;
    rotation.value=state.rotation;
    zoomValue.textContent=`${state.zoom.toFixed(2)}×`;
    rotationValue.textContent=`${Math.round(state.rotation)}°`;
    editor.querySelectorAll("[data-fit]").forEach(b =>
      b.classList.toggle("active",b.dataset.fit===state.fit)
    );
  }

  function open(nextCard) {
    card=nextCard;
    state=read();

    const preview=card.querySelector("img[data-preview-element]");
    const box=card.querySelector("[data-media-preview]");
    if (!preview?.src || !box) return;

    image.src=preview.src;

    const rect=box.getBoundingClientRect();
    if (rect.width && rect.height) {
      stage.style.setProperty("--editor-ratio",`${rect.width}/${rect.height}`);
    }

    const grid=card.closest(".service-media-grid");
    editor.dataset.size =
      grid && card !== grid.querySelector(".media-card")
        ? "secondary" : "primary";

    paint();
    editor.hidden=false;
    document.body.style.overflow="hidden";
  }

  function apply() {
    if (!card || !state) return;
    meta("crop_x").value=state.x.toFixed(2);
    meta("crop_y").value=state.y.toFixed(2);
    meta("zoom").value=state.zoom.toFixed(2);
    meta("rotation").value=Math.round(state.rotation);
    meta("fit").value=state.fit;
    paint(card.querySelector("img[data-preview-element]"));
  }

  function close() {
    editor.hidden=true;
    editor.removeAttribute("data-size");
    document.body.style.overflow="";
    drag=null;
  }

  document.addEventListener("change", e => {
    const input=e.target.closest("[data-media-input]");
    if (!input) return;

    const file=input.files?.[0];
    const mediaCard=input.closest("[data-media-card]");
    const preview=mediaCard?.querySelector("[data-preview-element]");
    if (!file || !preview) return;

    preview.src=URL.createObjectURL(file);
    preview.style.visibility="visible";

    const name=mediaCard.querySelector("[data-file-name]");
    if (name) name.textContent=file.name;
  });

  document.addEventListener("click", e => {
    const adjust=e.target.closest("[data-adjust-media]");
    if (adjust) open(adjust.closest("[data-media-card]"));
  });

  zoom?.addEventListener("input",()=>{state.zoom=+zoom.value;paint()});
  rotation?.addEventListener("input",()=>{state.rotation=+rotation.value;paint()});

  editor.querySelectorAll("[data-fit]").forEach(b =>
    b.addEventListener("click",()=>{state.fit=b.dataset.fit;paint()})
  );

  stage.addEventListener("pointerdown",e=>{
    stage.setPointerCapture(e.pointerId);
    drag={id:e.pointerId,x:e.clientX,y:e.clientY,sx:state.x,sy:state.y};
  });

  stage.addEventListener("pointermove",e=>{
    if (!drag || e.pointerId!==drag.id) return;
    const r=stage.getBoundingClientRect();
    state.x=clamp(drag.sx-(e.clientX-drag.x)/r.width*100,0,100);
    state.y=clamp(drag.sy-(e.clientY-drag.y)/r.height*100,0,100);
    paint();
  });

  ["pointerup","pointercancel"].forEach(type =>
    stage.addEventListener(type,()=>drag=null)
  );

  editor.querySelector("[data-editor-reset]")?.addEventListener("click",()=>{
    state={x:50,y:50,zoom:1,rotation:0,fit:"cover"};
    paint();
  });

  editor.querySelector("[data-editor-apply]")?.addEventListener("click",()=>{
    apply(); close();
  });

  editor.querySelector("[data-editor-save]")?.addEventListener("click",()=>{
    apply();
    const form=card?.closest("form");
    close();
    form?.requestSubmit();
  });

  editor.querySelector("[data-editor-close]")?.addEventListener("click",close);
  editor.addEventListener("click",e=>{if(e.target===editor)close()});
})();