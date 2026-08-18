(() => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const editor = $("#mediaEditor");
  const stage = $("#editorStage");
  const image = $("#editorImage");
  const zoom = $("#editorZoom");
  const rotation = $("#editorRotation");
  const zoomValue = $("#editorZoomValue");
  const rotationValue = $("#editorRotationValue");

  if (!editor || !stage || !image) return;

  let card = null;
  let state = null;
  let drag = null;

  const clamp = (n, min, max) => Math.max(min, Math.min(max, n));
  const meta = name => card?.querySelector(`[data-meta="${name}"]`);

  // Se citesc valorile curente ale imaginii.
  function readState() {
    return {
      x: +(meta("crop_x")?.value || 50),
      y: +(meta("crop_y")?.value || 50),
      zoom: +(meta("zoom")?.value || 1),
      rotation: +(meta("rotation")?.value || 0),
      fit: meta("fit")?.value || "cover"
    };
  }

  // Se aplică poziția, zoomul și rotirea.
  function render(target = image) {
    if (!state || !target) return;

    target.style.setProperty("--crop-x", `${state.x}%`);
    target.style.setProperty("--crop-y", `${state.y}%`);
    target.style.setProperty("--crop-zoom", state.zoom);
    target.style.setProperty("--crop-rotation", `${state.rotation}deg`);
    target.style.setProperty("--crop-fit", state.fit);

    if (zoom) zoom.value = state.zoom;
    if (rotation) rotation.value = state.rotation;
    if (zoomValue) zoomValue.textContent = `${state.zoom.toFixed(2)}×`;
    if (rotationValue) rotationValue.textContent = `${Math.round(state.rotation)}°`;

    $$("[data-fit]", editor).forEach(button =>
      button.classList.toggle("active", button.dataset.fit === state.fit)
    );
  }

  // Se deschide editorul pentru imaginea selectată.
  function openEditor(nextCard) {
    const preview = $("[data-preview-element]", nextCard);
    const box = $("[data-media-preview]", nextCard);

    if (!preview?.src || !box) return;

    card = nextCard;
    state = readState();
    image.src = preview.src;

    const rect = box.getBoundingClientRect();
    if (rect.width && rect.height) {
      stage.style.setProperty("--editor-ratio", `${rect.width}/${rect.height}`);
    }

    const grid = card.closest(".service-media-grid");
    editor.dataset.size =
      grid && card !== $(".media-card", grid) ? "secondary" : "primary";

    render();
    editor.hidden = false;
    document.body.style.overflow = "hidden";
  }

  // Se salvează valorile editorului în câmpurile formularului.
  function applyEditor() {
    if (!card || !state) return;

    meta("crop_x").value = state.x.toFixed(2);
    meta("crop_y").value = state.y.toFixed(2);
    meta("zoom").value = state.zoom.toFixed(2);
    meta("rotation").value = Math.round(state.rotation);
    meta("fit").value = state.fit;

    render($("[data-preview-element]", card));
  }

  function closeEditor() {
    editor.hidden = true;
    editor.removeAttribute("data-size");
    document.body.style.overflow = "";
    drag = null;
  }

  // Se afișează imediat imaginea nouă selectată.
  document.addEventListener("change", event => {
    const input = event.target.closest("[data-media-input]");
    if (!input) return;

    const file = input.files?.[0];
    const mediaCard = input.closest("[data-media-card]");
    const preview = mediaCard?.querySelector("[data-preview-element]");

    if (!file || !preview) return;

    preview.src = URL.createObjectURL(file);
    preview.style.visibility = "visible";

    const name = mediaCard.querySelector("[data-file-name]");
    if (name) name.textContent = file.name;
  });

  document.addEventListener("click", event => {
    const button = event.target.closest("[data-adjust-media]");
    if (button) openEditor(button.closest("[data-media-card]"));
  });

  zoom?.addEventListener("input", () => {
    if (!state) return;
    state.zoom = +zoom.value;
    render();
  });

  rotation?.addEventListener("input", () => {
    if (!state) return;
    state.rotation = +rotation.value;
    render();
  });

  $$("[data-fit]", editor).forEach(button =>
    button.addEventListener("click", () => {
      if (!state) return;
      state.fit = button.dataset.fit;
      render();
    })
  );

  // Se mută imaginea prin drag.
  stage.addEventListener("pointerdown", event => {
    if (!state) return;

    stage.setPointerCapture(event.pointerId);
    drag = {
      id: event.pointerId,
      x: event.clientX,
      y: event.clientY,
      startX: state.x,
      startY: state.y
    };
    stage.classList.add("dragging");
  });

  stage.addEventListener("pointermove", event => {
    if (!drag || event.pointerId !== drag.id || !state) return;

    const rect = stage.getBoundingClientRect();
    state.x = clamp(drag.startX - (event.clientX - drag.x) / rect.width * 100, 0, 100);
    state.y = clamp(drag.startY - (event.clientY - drag.y) / rect.height * 100, 0, 100);
    render();
  });

  ["pointerup", "pointercancel"].forEach(type =>
    stage.addEventListener(type, () => {
      drag = null;
      stage.classList.remove("dragging");
    })
  );

  $("[data-editor-reset]", editor)?.addEventListener("click", () => {
    state = { x: 50, y: 50, zoom: 1, rotation: 0, fit: "cover" };
    render();
  });

  $("[data-editor-apply]", editor)?.addEventListener("click", () => {
    applyEditor();
    closeEditor();
  });

  $("[data-editor-save]", editor)?.addEventListener("click", () => {
    applyEditor();
    const form = card?.closest("form");
    closeEditor();
    form?.requestSubmit();
  });

  $("[data-editor-close]", editor)?.addEventListener("click", closeEditor);
  editor.addEventListener("click", event => {
    if (event.target === editor) closeEditor();
  });
})();