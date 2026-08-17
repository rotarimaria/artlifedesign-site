(() => {
  const $ = (s, root = document) => root.querySelector(s);
  const $$ = (s, root = document) => [...root.querySelectorAll(s)];
  const clamp = (n, min, max) => Math.max(min, Math.min(max, n));

  const modal = $('#cropModal');
  if (!modal) return;

  const stage = $('#cropStage');
  const stageImg = $('#cropStageImg');
  const zoomInput = $('#cropZoom');
  const zoomValue = $('#cropZoomValue');
  const fitButtons = $$('.fit-switch button', modal);
  const applyBtn = $('#cropApply');
  const cancelBtn = $('#cropCancel');
  const resetBtn = $('#cropReset');

  let active = null;
  let state = { x: 50, y: 50, zoom: 1, fit: 'cover' };
  let drag = null;

  function renderStage() {
    stageImg.style.setProperty('--crop-x', state.x + '%');
    stageImg.style.setProperty('--crop-y', state.y + '%');
    stageImg.style.setProperty('--zoom', state.zoom);
    stageImg.style.setProperty('--fit', state.fit);
    zoomInput.value = state.zoom;
    zoomValue.textContent = Number(state.zoom).toFixed(2) + '×';
    fitButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.fit === state.fit));
  }

  function openEditor(target) {
    active = target;
    const img = target.querySelector('[data-crop-image]');
    const x = target.querySelector('[data-crop-x]');
    const y = target.querySelector('[data-crop-y]');
    const zoom = target.querySelector('[data-crop-zoom]');
    const fit = target.querySelector('[data-crop-fit]');

    if (!img || !img.src) return;

    state = {
      x: parseFloat(x?.value || 50),
      y: parseFloat(y?.value || 50),
      zoom: parseFloat(zoom?.value || 1),
      fit: fit?.value || 'cover'
    };

    stageImg.src = img.src;
    renderStage();
    modal.classList.add('open');
  }

  function closeEditor() {
    modal.classList.remove('open');
    active = null;
    drag = null;
  }

  function applyToTarget() {
    if (!active) return;
    const img = active.querySelector('[data-crop-image]');
    const x = active.querySelector('[data-crop-x]');
    const y = active.querySelector('[data-crop-y]');
    const zoom = active.querySelector('[data-crop-zoom]');
    const fit = active.querySelector('[data-crop-fit]');

    x.value = state.x.toFixed(2);
    y.value = state.y.toFixed(2);
    zoom.value = state.zoom.toFixed(2);
    fit.value = state.fit;

    img.style.setProperty('--crop-x', state.x + '%');
    img.style.setProperty('--crop-y', state.y + '%');
    img.style.setProperty('--zoom', state.zoom);
    img.style.setProperty('--fit', state.fit);

    updateLivePreview();
    closeEditor();
  }

  $$('.js-crop-open').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      e.stopPropagation();
      openEditor(btn.closest('[data-crop-target]'));
    });
  });

  $$('.upload-slot input[type=file]').forEach(input => {
    input.addEventListener('change', () => {
      const slot = input.closest('.upload-slot');
      const file = input.files?.[0];
      if (!file) return;

      const url = URL.createObjectURL(file);
      let preview = slot.querySelector('.slot-preview');
      if (!preview) {
        preview = document.createElement('div');
        preview.className = 'slot-preview';
        preview.innerHTML = '<img data-crop-image alt="">';
        slot.appendChild(preview);
      }

      const img = preview.querySelector('img');
      img.src = url;
      slot.classList.add('has-image');

      const empty = slot.querySelector('.slot-empty');
      if (empty) empty.style.display = 'none';

      const cropBtn = slot.querySelector('.js-crop-open');
      if (cropBtn) cropBtn.style.display = '';

      updateLivePreview();
    });
  });

  zoomInput.addEventListener('input', () => {
    state.zoom = parseFloat(zoomInput.value);
    renderStage();
  });

  fitButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      state.fit = btn.dataset.fit;
      if (state.fit === 'contain' && state.zoom < 1) state.zoom = 1;
      renderStage();
    });
  });

  resetBtn.addEventListener('click', () => {
    state = { x: 50, y: 50, zoom: 1, fit: 'cover' };
    renderStage();
  });

  cancelBtn.addEventListener('click', closeEditor);
  applyBtn.addEventListener('click', applyToTarget);

  modal.addEventListener('click', e => {
    if (e.target === modal) closeEditor();
  });

  stage.addEventListener('pointerdown', e => {
    stage.setPointerCapture(e.pointerId);
    drag = {
      px: e.clientX,
      py: e.clientY,
      x: state.x,
      y: state.y
    };
  });

  stage.addEventListener('pointermove', e => {
    if (!drag) return;
    const rect = stage.getBoundingClientRect();
    const dx = (e.clientX - drag.px) / rect.width * 100;
    const dy = (e.clientY - drag.py) / rect.height * 100;

    state.x = clamp(drag.x - dx, 0, 100);
    state.y = clamp(drag.y - dy, 0, 100);
    renderStage();
  });

  stage.addEventListener('pointerup', () => drag = null);
  stage.addEventListener('pointercancel', () => drag = null);

  const titleInput = $('#title');
  const serviceInput = $('#service');
  const liveTitle = $('#liveTitle');
  const liveService = $('#liveService');
  const liveMedia = $('#liveMedia');

  function findPreviewSource() {
    const primaryExisting = $('.existing-slot input[name="primary_image_id"]:checked')?.closest('.existing-slot');
    if (primaryExisting && !primaryExisting.querySelector('input[name="delete_image[]"]:checked')) {
      return primaryExisting;
    }

    const firstExisting = $$('.existing-slot').find(slot => !slot.querySelector('input[name="delete_image[]"]:checked'));
    if (firstExisting) return firstExisting;

    return $$('.upload-slot.has-image')[0] || null;
  }

  function updateLivePreview() {
    if (liveTitle) liveTitle.textContent = titleInput?.value.trim() || 'Titlul proiectului';
    if (liveService) liveService.textContent = serviceInput?.value.trim() || 'Serviciu';

    if (!liveMedia) return;
    const source = findPreviewSource();
    liveMedia.innerHTML = '';

    if (!source) {
      liveMedia.innerHTML = '<div class="live-card-placeholder">Imaginea principală va apărea aici</div>';
      return;
    }

    const srcImg = source.querySelector('[data-crop-image]');
    if (!srcImg || !srcImg.src) return;

    const img = document.createElement('img');
    img.src = srcImg.src;
    img.alt = '';
    img.style.setProperty('--crop-x', (source.querySelector('[data-crop-x]')?.value || 50) + '%');
    img.style.setProperty('--crop-y', (source.querySelector('[data-crop-y]')?.value || 50) + '%');
    img.style.setProperty('--zoom', source.querySelector('[data-crop-zoom]')?.value || 1);
    img.style.setProperty('--fit', source.querySelector('[data-crop-fit]')?.value || 'cover');
    liveMedia.appendChild(img);
  }

  titleInput?.addEventListener('input', updateLivePreview);
  serviceInput?.addEventListener('input', updateLivePreview);

  $$('input[name="primary_image_id"], input[name="delete_image[]"]').forEach(el => {
    el.addEventListener('change', updateLivePreview);
  });

  updateLivePreview();
})();