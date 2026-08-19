(() => {
  const editor = document.querySelector("#mediaEditor");
  const title = document.querySelector("#editorTitle");
  const stage = document.querySelector("#editorStage");
  const image = document.querySelector("#editorImage");
  const zoom = document.querySelector("#editorZoom");
  const rotation = document.querySelector("#editorRotation");
  const zoomValue = document.querySelector("#editorZoomValue");
  const rotationValue = document.querySelector("#editorRotationValue");

  if (!editor || !stage || !image) return;

  let card = null;
  let mode = "detail";
  let state = null;
  let drag = null;

  const clamp = (value, min, max) =>
    Math.max(min, Math.min(max, value));

  const meta = name =>
    card?.querySelector(`[data-meta="${mode}_${name}"]`);

  // Se ia ajustarea pentru cardul mic sau pentru imaginea din detalii.
  function read() {
    return {
      x: +(meta("crop_x")?.value || 50),
      y: +(meta("crop_y")?.value || 50),
      zoom: +(meta("zoom")?.value || 1),
      rotation: +(meta("rotation")?.value || 0),
      fit: meta("fit")?.value || "cover"
    };
  }

  // Se aplică ajustarea în editor sau în previzualizare.
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

    editor.querySelectorAll("[data-fit]").forEach(button => {
      button.classList.toggle("active", button.dataset.fit === state.fit);
    });
  }

  // Se deschide editorul pentru contextul ales.
  function open(nextCard, nextMode) {
    const preview = nextCard?.querySelector("img[data-preview-element]");
    if (!preview?.src) return;

    card = nextCard;
    mode = nextMode === "card" ? "card" : "detail";
    state = read();
    image.src = preview.src;

    const slot = Number(card.dataset.slot || 1);

    if (mode === "card") {
      stage.style.setProperty("--editor-ratio", "4/3");
      editor.dataset.size = "secondary";
      if (title) title.textContent = "Ajustează imaginea pentru card";
    } else {
      stage.style.setProperty("--editor-ratio", slot === 1 ? "16/9" : "4/3");
      editor.dataset.size = slot === 1 ? "primary" : "secondary";
      if (title) title.textContent = "Ajustează imaginea în detalii";
    }

    paint();
    editor.hidden = false;
    document.body.style.overflow = "hidden";
  }

  // Se salvează valorile editorului în formular.
  function apply() {
    if (!card || !state) return;

    meta("crop_x").value = state.x.toFixed(2);
    meta("crop_y").value = state.y.toFixed(2);
    meta("zoom").value = state.zoom.toFixed(2);
    meta("rotation").value = Math.round(state.rotation);
    meta("fit").value = state.fit;

    if (mode === "detail") {
      paint(card.querySelector("img[data-preview-element]"));
    }
  }

  function close() {
    editor.hidden = true;
    editor.removeAttribute("data-size");
    document.body.style.overflow = "";
    drag = null;
  }

  // Se afișează imediat o imagine nouă selectată.
  document.addEventListener("change", event => {
    const input = event.target.closest("[data-media-input]");
    if (!input) return;

    const file = input.files?.[0];
    const mediaCard = input.closest("[data-media-card]");
    const preview = mediaCard?.querySelector("[data-preview-element]");

    if (!file || !preview) return;

    preview.src = URL.createObjectURL(file);
    preview.style.visibility = "visible";

    const deleteInput = mediaCard.querySelector("[data-delete-input]");
    const deleteButton = mediaCard.querySelector("[data-delete-media]");
    const fileName = mediaCard.querySelector("[data-file-name]");

    if (deleteInput) deleteInput.value = "0";
    if (deleteButton) deleteButton.hidden = false;
    if (fileName) fileName.textContent = file.name;
  });

  // Se deschide ajustarea cerută sau se marchează imaginea pentru ștergere.
  document.addEventListener("click", event => {
    const adjust = event.target.closest("[data-adjust-media]");

    if (adjust) {
      open(
        adjust.closest("[data-media-card]"),
        adjust.dataset.adjustMedia
      );
      return;
    }

    const deleteButton = event.target.closest("[data-delete-media]");
    if (!deleteButton) return;

    const mediaCard = deleteButton.closest("[data-media-card]");
    const preview = mediaCard?.querySelector("[data-preview-element]");
    const input = mediaCard?.querySelector("[data-media-input]");
    const deleteInput = mediaCard?.querySelector("[data-delete-input]");
    const fileName = mediaCard?.querySelector("[data-file-name]");

    if (!mediaCard || !confirm("Sigur vrei să ștergi imaginea?")) return;

    if (input) input.value = "";
    if (deleteInput) deleteInput.value = "1";

    if (preview) {
      preview.removeAttribute("src");
      preview.style.visibility = "hidden";
    }

    if (fileName) fileName.textContent = "";
    deleteButton.hidden = true;
  });

  zoom?.addEventListener("input", () => {
    state.zoom = +zoom.value;
    paint();
  });

  rotation?.addEventListener("input", () => {
    state.rotation = +rotation.value;
    paint();
  });

  editor.querySelectorAll("[data-fit]").forEach(button => {
    button.addEventListener("click", () => {
      state.fit = button.dataset.fit;
      paint();
    });
  });

  // Se mută imaginea prin tragere.
  stage.addEventListener("pointerdown", event => {
    if (!state) return;

    stage.setPointerCapture(event.pointerId);
    stage.classList.add("dragging");

    drag = {
      id: event.pointerId,
      x: event.clientX,
      y: event.clientY,
      startX: state.x,
      startY: state.y
    };
  });

  stage.addEventListener("pointermove", event => {
    if (!drag || event.pointerId !== drag.id) return;

    const rect = stage.getBoundingClientRect();

    state.x = clamp(
      drag.startX - (event.clientX - drag.x) / rect.width * 100,
      0,
      100
    );

    state.y = clamp(
      drag.startY - (event.clientY - drag.y) / rect.height * 100,
      0,
      100
    );

    paint();
  });

  ["pointerup", "pointercancel"].forEach(type => {
    stage.addEventListener(type, () => {
      drag = null;
      stage.classList.remove("dragging");
    });
  });

  editor.querySelector("[data-editor-reset]")?.addEventListener("click", () => {
    state = { x: 50, y: 50, zoom: 1, rotation: 0, fit: "cover" };
    paint();
  });

  editor.querySelector("[data-editor-apply]")?.addEventListener("click", () => {
    apply();
    close();
  });

  editor.querySelector("[data-editor-save]")?.addEventListener("click", () => {
    apply();
    const form = card?.closest("form");
    close();
    form?.requestSubmit();
  });

  editor.querySelector("[data-editor-close]")?.addEventListener("click", close);

  editor.addEventListener("click", event => {
    if (event.target === editor) close();
  });
})();