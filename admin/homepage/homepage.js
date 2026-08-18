(() => {
  const form = document.querySelector("#homepageForm");
  const editor = document.querySelector("#mediaEditor");
  const stage = document.querySelector("#editorStage");
  const image = document.querySelector("#editorImage");
  const title = document.querySelector("#editorTitle");
  const zoom = document.querySelector("#editorZoom");
  const rotation = document.querySelector("#editorRotation");
  const zoomValue = document.querySelector("#editorZoomValue");
  const rotationValue = document.querySelector("#editorRotationValue");

  if (!form || !editor || !stage || !image) return;

  let card = null;
  let state = null;
  let drag = null;

  const clamp = (n, min, max) => Math.max(min, Math.min(max, n));
  const meta = (name) => card?.querySelector(`[data-meta="${name}"]`);

  function readState() {
    return {
      x: Number(meta("crop_x")?.value || 50),
      y: Number(meta("crop_y")?.value || 50),
      zoom: Number(meta("zoom")?.value || 1),
      rotation: Number(meta("rotation")?.value || 0),
      fit: meta("fit")?.value || "cover"
    };
  }

  function paint(target = image) {
    if (!state || !target) return;
    target.style.setProperty("--crop-x", `${state.x}%`);
    target.style.setProperty("--crop-y", `${state.y}%`);
    target.style.setProperty("--crop-zoom", state.zoom);
    target.style.setProperty("--crop-rotation", `${state.rotation}deg`);
    target.style.setProperty("--crop-fit", state.fit);

    zoom.value = state.zoom;
    rotation.value = state.rotation;
    zoomValue.textContent = `${state.zoom.toFixed(2)}×`;
    rotationValue.textContent = `${Math.round(state.rotation)}°`;

    editor.querySelectorAll("[data-fit]").forEach(btn => {
      btn.classList.toggle("active", btn.dataset.fit === state.fit);
    });
  }

  function openEditor(nextCard) {
    card = nextCard;
    state = readState();

    const previewBox = card.querySelector("[data-media-preview]");
    const preview = card.querySelector("img[data-preview-element]");
    if (!preview?.src || !previewBox) return;

    image.src = preview.src;
    title.textContent =
      card.querySelector(".media-card-head strong")?.textContent || "Imagine";

    // Editorul folosește exact raportul cardului real.
    const rect = previewBox.getBoundingClientRect();
    if (rect.width > 0 && rect.height > 0) {
      stage.style.setProperty("--editor-ratio", `${rect.width} / ${rect.height}`);
    }

    paint();
    editor.hidden = false;
    document.body.style.overflow = "hidden";
  }

  function closeEditor() {
    editor.hidden = true;
    document.body.style.overflow = "";
    drag = null;
  }

  function apply() {
    if (!card || !state) return;

    meta("crop_x").value = state.x.toFixed(2);
    meta("crop_y").value = state.y.toFixed(2);
    meta("zoom").value = state.zoom.toFixed(2);
    meta("rotation").value = Math.round(state.rotation);
    meta("fit").value = state.fit;

    paint(card.querySelector("img[data-preview-element]"));
  }

  document.querySelectorAll("[data-media-card]").forEach(mediaCard => {
    const input = mediaCard.querySelector("[data-media-input]");
    const preview = mediaCard.querySelector("[data-preview-element]");
    const fileName = mediaCard.querySelector("[data-file-name]");

    input?.addEventListener("change", () => {
      const file = input.files?.[0];
      if (!file || !preview) return;

      const url = URL.createObjectURL(file);
      preview.src = url;
      if (fileName) fileName.textContent = file.name;

      if (preview.tagName === "VIDEO") {
        preview.load();
        preview.play().catch(() => {});
      }
    });

    mediaCard.querySelector("[data-adjust-media]")?.addEventListener(
      "click",
      () => openEditor(mediaCard)
    );
  });

  zoom.addEventListener("input", () => {
    state.zoom = Number(zoom.value);
    paint();
  });

  rotation.addEventListener("input", () => {
    state.rotation = Number(rotation.value);
    paint();
  });

  editor.querySelectorAll("[data-fit]").forEach(btn => {
    btn.addEventListener("click", () => {
      state.fit = btn.dataset.fit;
      paint();
    });
  });

  stage.addEventListener("pointerdown", event => {
    if (!state) return;
    stage.setPointerCapture(event.pointerId);
    stage.classList.add("dragging");
    drag = {
      pointerId: event.pointerId,
      startX: event.clientX,
      startY: event.clientY,
      x: state.x,
      y: state.y
    };
  });

  stage.addEventListener("pointermove", event => {
    if (!drag || event.pointerId !== drag.pointerId) return;

    const rect = stage.getBoundingClientRect();
    const dx = (event.clientX - drag.startX) / rect.width * 100;
    const dy = (event.clientY - drag.startY) / rect.height * 100;

    // Inversarea face ca imaginea să urmeze natural direcția mouse-ului.
    state.x = clamp(drag.x - dx, 0, 100);
    state.y = clamp(drag.y - dy, 0, 100);
    paint();
  });

  const endDrag = event => {
    if (!drag || event.pointerId !== drag.pointerId) return;
    drag = null;
    stage.classList.remove("dragging");
  };

  stage.addEventListener("pointerup", endDrag);
  stage.addEventListener("pointercancel", endDrag);

  editor.querySelector("[data-editor-reset]")?.addEventListener("click", () => {
    state = { x: 50, y: 50, zoom: 1, rotation: 0, fit: "cover" };
    paint();
  });

  editor.querySelector("[data-editor-apply]")?.addEventListener("click", () => {
    apply();
    closeEditor();
  });

  editor.querySelector("[data-editor-save]")?.addEventListener("click", () => {
    apply();
    closeEditor();
    form.requestSubmit();
  });

  editor.querySelector("[data-editor-close]")?.addEventListener("click", closeEditor);

  editor.addEventListener("click", event => {
    if (event.target === editor) closeEditor();
  });

  document.addEventListener("keydown", event => {
    if (event.key === "Escape" && !editor.hidden) closeEditor();
  });
})();