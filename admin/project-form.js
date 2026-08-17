(() => {
  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

  const titleInput = $('#title');
  const serviceInput = $('#service');
  const categoryInput = $('#category');

  const liveTitle = $('#liveTitle');
  const liveService = $('#liveService');
  const liveMedia = $('#liveMedia');

  const clamp = (value, min, max) => Math.max(min, Math.min(max, value));

  function updateTextPreview() {
    if (liveTitle) {
      liveTitle.textContent =
        titleInput?.value.trim() || 'Titlul proiectului';
    }

    if (liveService) {
      const serviceText =
        serviceInput?.value.trim() ||
        categoryInput?.selectedOptions?.[0]?.textContent?.trim() ||
        'Serviciu';

      liveService.textContent = serviceText;
    }
  }

  function getCropValues(slot) {
    return {
      x: slot.querySelector('[data-crop-x]')?.value || '50',
      y: slot.querySelector('[data-crop-y]')?.value || '50',
      zoom: slot.querySelector('[data-crop-zoom]')?.value || '1',
      fit: slot.querySelector('[data-crop-fit]')?.value || 'cover',
    };
  }

  function getPreviewSource() {
    const selectedPrimary = $(
      '.existing-slot input[name="primary_image_id"]:checked'
    )?.closest('[data-crop-target]');

    if (
      selectedPrimary &&
      !selectedPrimary.querySelector(
        'input[name="delete_image[]"]:checked'
      )
    ) {
      return selectedPrimary;
    }

    const existing = $$('.existing-slot').find(
      slot =>
        !slot.querySelector(
          'input[name="delete_image[]"]:checked'
        )
    );

    if (existing) return existing;

    const newSlots = $$('.upload-slot.has-image');

    return newSlots[0] || null;
  }

  function updateImagePreview() {
    if (!liveMedia) return;

    const source = getPreviewSource();

    liveMedia.innerHTML = '';

    if (!source) {
      liveMedia.innerHTML =
        '<div class="live-card-placeholder">' +
        'Imaginea principală va apărea aici' +
        '</div>';

      return;
    }

    const sourceImage = source.querySelector('[data-crop-image]');

    if (!sourceImage || !sourceImage.src) {
      liveMedia.innerHTML =
        '<div class="live-card-placeholder">' +
        'Imaginea principală va apărea aici' +
        '</div>';

      return;
    }

    const crop = getCropValues(source);

    const img = document.createElement('img');
    img.src = sourceImage.src;
    img.alt = '';

    img.style.setProperty('--crop-x', crop.x + '%');
    img.style.setProperty('--crop-y', crop.y + '%');
    img.style.setProperty('--zoom', crop.zoom);
    img.style.setProperty('--fit', crop.fit);

    liveMedia.appendChild(img);
  }

  function updateLivePreview() {
    updateTextPreview();
    updateImagePreview();
  }

  titleInput?.addEventListener('input', updateTextPreview);
  serviceInput?.addEventListener('input', updateTextPreview);
  categoryInput?.addEventListener('change', updateTextPreview);

  $$('.upload-slot input[type="file"]').forEach(input => {
    input.addEventListener('change', () => {
      const slot = input.closest('.upload-slot');
      const file = input.files?.[0];

      if (!slot || !file) return;

      let preview = slot.querySelector('.slot-preview');

      if (!preview) {
        preview = document.createElement('div');
        preview.className = 'slot-preview';
        preview.innerHTML =
          '<img data-crop-image alt="">';

        slot.appendChild(preview);
      }

      const img = preview.querySelector('[data-crop-image]');
      const objectUrl = URL.createObjectURL(file);

      img.onload = () => {
        updateLivePreview();
      };

      img.src = objectUrl;

      slot.classList.add('has-image');

      const empty = slot.querySelector('.slot-empty');

      if (empty) {
        empty.style.display = 'none';
      }

      const adjustButton = slot.querySelector('.js-crop-open');

      if (adjustButton) {
        adjustButton.style.display = '';
      }

      updateLivePreview();
    });
  });

  $$(
    'input[name="primary_image_id"], ' +
    'input[name="delete_image[]"]'
  ).forEach(input => {
    input.addEventListener('change', updateLivePreview);
  });

  /*
   * Editor vizual imagine
   */
  const modal = $('#cropModal');
  const stage = $('#cropStage');
  const stageImg = $('#cropStageImg');
  const zoomInput = $('#cropZoom');
  const zoomValue = $('#cropZoomValue');
  const fitButtons = $$('.fit-switch button');
  const applyButton = $('#cropApply');
  const cancelButton = $('#cropCancel');
  const resetButton = $('#cropReset');

  let activeSlot = null;
  let state = {
    x: 50,
    y: 50,
    zoom: 1,
    fit: 'cover',
  };
  let drag = null;

  function renderCropEditor() {
    if (!stageImg) return;

    stageImg.style.setProperty('--crop-x', state.x + '%');
    stageImg.style.setProperty('--crop-y', state.y + '%');
    stageImg.style.setProperty('--zoom', state.zoom);
    stageImg.style.setProperty('--fit', state.fit);

    if (zoomInput) {
      zoomInput.value = String(state.zoom);
    }

    if (zoomValue) {
      zoomValue.textContent =
        Number(state.zoom).toFixed(2) + '×';
    }

    fitButtons.forEach(button => {
      button.classList.toggle(
        'active',
        button.dataset.fit === state.fit
      );
    });
  }

  function openCropEditor(slot) {
    if (!modal || !stageImg) return;

    const img = slot.querySelector('[data-crop-image]');

    if (!img || !img.src) return;

    activeSlot = slot;

    const crop = getCropValues(slot);

    state = {
      x: parseFloat(crop.x),
      y: parseFloat(crop.y),
      zoom: parseFloat(crop.zoom),
      fit: crop.fit,
    };

    stageImg.src = img.src;

    renderCropEditor();
    modal.classList.add('open');
  }

  function closeCropEditor() {
    modal?.classList.remove('open');
    activeSlot = null;
    drag = null;
  }

  function applyCrop() {
    if (!activeSlot) return;

    const x = activeSlot.querySelector('[data-crop-x]');
    const y = activeSlot.querySelector('[data-crop-y]');
    const zoom = activeSlot.querySelector('[data-crop-zoom]');
    const fit = activeSlot.querySelector('[data-crop-fit]');
    const img = activeSlot.querySelector('[data-crop-image]');

    if (x) x.value = state.x.toFixed(2);
    if (y) y.value = state.y.toFixed(2);
    if (zoom) zoom.value = state.zoom.toFixed(2);
    if (fit) fit.value = state.fit;

    if (img) {
      img.style.setProperty('--crop-x', state.x + '%');
      img.style.setProperty('--crop-y', state.y + '%');
      img.style.setProperty('--zoom', state.zoom);
      img.style.setProperty('--fit', state.fit);
    }

    updateLivePreview();
    closeCropEditor();
  }

  $$('.js-crop-open').forEach(button => {
    button.addEventListener('click', event => {
      event.preventDefault();
      event.stopPropagation();

      const slot = button.closest('[data-crop-target]');

      if (slot) {
        openCropEditor(slot);
      }
    });
  });

  zoomInput?.addEventListener('input', () => {
    state.zoom = parseFloat(zoomInput.value);
    renderCropEditor();
  });

  fitButtons.forEach(button => {
    button.addEventListener('click', () => {
      state.fit = button.dataset.fit || 'cover';
      renderCropEditor();
    });
  });

  resetButton?.addEventListener('click', () => {
    state = {
      x: 50,
      y: 50,
      zoom: 1,
      fit: 'cover',
    };

    renderCropEditor();
  });

  cancelButton?.addEventListener('click', closeCropEditor);
  applyButton?.addEventListener('click', applyCrop);

  modal?.addEventListener('click', event => {
    if (event.target === modal) {
      closeCropEditor();
    }
  });

  stage?.addEventListener('pointerdown', event => {
    stage.setPointerCapture(event.pointerId);

    drag = {
      pointerX: event.clientX,
      pointerY: event.clientY,
      x: state.x,
      y: state.y,
    };
  });

  stage?.addEventListener('pointermove', event => {
    if (!drag) return;

    const rect = stage.getBoundingClientRect();

    const dx =
      ((event.clientX - drag.pointerX) / rect.width) * 100;

    const dy =
      ((event.clientY - drag.pointerY) / rect.height) * 100;

    state.x = clamp(drag.x - dx, 0, 100);
    state.y = clamp(drag.y - dy, 0, 100);

    renderCropEditor();
  });

  stage?.addEventListener('pointerup', () => {
    drag = null;
  });

  stage?.addEventListener('pointercancel', () => {
    drag = null;
  });

  /*
   * Completează preview-ul imediat la încărcarea paginii.
   */
  updateLivePreview();
})();