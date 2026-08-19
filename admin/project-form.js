(() => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const clamp = (v, min, max) => Math.max(min, Math.min(max, v));

  const title = $('#title');
  const category = $('#category');
  const description = $('#description');
  const smallMedia = $('#smallMedia');
  const smallTitle = $('#smallTitle');
  const smallCategory = $('#smallCategory');
  const siteMain = $('#siteMain');
  const siteThumbs = $('#siteThumbs');
  const siteTitle = $('#siteTitle');
  const siteCategory = $('#siteCategory');
  const siteDescription = $('#siteDescription');
  const siteTags = $('#siteTags');

  // Se gestionează tagurile.
  const tagsHidden = $('#tags');
  const tagInput = $('#tagInput');
  const tagList = $('#tagList');
  const tagCount = $('#tagCount');
  const MAX_TAGS = 14;

  let tags = (tagsHidden?.value || '')
    .split(',')
    .map(v => v.trim())
    .filter(Boolean);

  function syncTags() {
    tags = [...new Map(tags.map(tag => [tag.toLowerCase(), tag])).values()].slice(0, MAX_TAGS);

    if (tagsHidden) tagsHidden.value = tags.join(', ');
    if (tagCount) tagCount.textContent = `${tags.length}/${MAX_TAGS}`;

    if (tagList) {
      tagList.innerHTML = '';

      tags.forEach((tag, index) => {
        const chip = document.createElement('span');
        chip.className = 'tag-chip';
        chip.append(document.createTextNode(tag));

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.textContent = '×';
        remove.title = 'Șterge';
        remove.onclick = () => {
          tags.splice(index, 1);
          syncTags();
        };

        chip.appendChild(remove);
        tagList.appendChild(chip);
      });
    }

    if (siteTags) {
      siteTags.innerHTML = '';

      tags.forEach(tag => {
        const span = document.createElement('span');
        span.className = 'site-modal-tag';
        span.textContent = tag;
        siteTags.appendChild(span);
      });
    }
  }

  function addTag() {
    const value = tagInput?.value.trim();

    if (!value || tags.length >= MAX_TAGS) return;

    if (!tags.some(tag => tag.toLowerCase() === value.toLowerCase())) {
      tags.push(value);
    }

    if (tagInput) tagInput.value = '';
    syncTags();
  }

  $('#tagAdd')?.addEventListener('click', addTag);

  tagInput?.addEventListener('keydown', event => {
    if (event.key === 'Enter') {
      event.preventDefault();
      addTag();
    }
  });

  // Se actualizează textele din previzualizare.
  function updateText() {
    const projectTitle = title?.value.trim() || 'Titlul proiectului';
    const projectCategory = category?.selectedOptions?.[0]?.textContent?.trim() || 'Categorie';
    const projectDescription = description?.value.trim() || 'Descrierea proiectului va apărea aici.';

    if (smallTitle) smallTitle.textContent = projectTitle;
    if (smallCategory) smallCategory.textContent = projectCategory;
    if (siteTitle) siteTitle.textContent = projectTitle;
    if (siteCategory) siteCategory.textContent = projectCategory;
    if (siteDescription) siteDescription.textContent = projectDescription;
  }

  title?.addEventListener('input', updateText);
  category?.addEventListener('change', updateText);
  description?.addEventListener('input', updateText);

  // Se citesc ajustările pentru card sau modal.
  function display(slot, mode = 'detail') {
    const prefix = mode === 'card' ? 'card' : 'detail';

    return {
      x: slot.querySelector(`[data-${prefix}-x]`)?.value || '50',
      y: slot.querySelector(`[data-${prefix}-y]`)?.value || '50',
      zoom: slot.querySelector(`[data-${prefix}-zoom]`)?.value || '1',
      fit: slot.querySelector(`[data-${prefix}-fit]`)?.value || 'contain',
      rotation: slot.querySelector(`[data-${prefix}-rotation]`)?.value || '0'
    };
  }

  function writeDisplay(slot, mode, values) {
    const prefix = mode === 'card' ? 'card' : 'detail';
    const map = {
      x: `[data-${prefix}-x]`,
      y: `[data-${prefix}-y]`,
      zoom: `[data-${prefix}-zoom]`,
      fit: `[data-${prefix}-fit]`,
      rotation: `[data-${prefix}-rotation]`
    };

    Object.entries(map).forEach(([key, selector]) => {
      const input = slot.querySelector(selector);
      if (input) input.value = values[key];
    });
  }

  function applyDisplay(media, values) {
    if (!media) return;

    media.style.setProperty('--crop-x', `${values.x}%`);
    media.style.setProperty('--crop-y', `${values.y}%`);
    media.style.setProperty('--zoom', values.zoom);
    media.style.setProperty('--fit', values.fit);
    media.style.setProperty('--rotation', `${values.rotation}deg`);
  }

  function usableSlots() {
    return $$('.media-slot').filter(slot =>
      slot.dataset.deleted !== '1' && slot.querySelector('[data-media-preview]')
    );
  }

  // Se arată ajustarea cardului mic doar la media principală.
  function syncPrimaryControls() {
    $$('.media-slot').forEach(slot => {
      const existing = slot.querySelector('input[name="primary_image_id"]');
      const fresh = slot.querySelector('.js-new-primary');
      const cardButton = slot.querySelector('.js-card-adjust');
      const hasMedia = slot.dataset.deleted !== '1' && !!slot.querySelector('[data-media-preview]');
      const isPrimary = !!(existing?.checked || fresh?.checked);

      if (cardButton) {
        cardButton.style.display = hasMedia && isPrimary ? '' : 'none';
      }
    });
  }

  function primarySlot() {
    const existing = $('.media-slot input[name="primary_image_id"]:checked')?.closest('.media-slot');
    if (existing?.dataset.deleted !== '1') return existing;

    const fresh = $('.media-slot .js-new-primary:checked')?.closest('.media-slot');
    if (fresh?.dataset.deleted !== '1') return fresh;

    return usableSlots()[0] || null;
  }

  // Se clonează media cu ajustarea potrivită.
  function cloneMedia(slot, mode = 'detail', controls = false) {
    const source = slot?.querySelector('[data-media-preview]');
    if (!source) return null;

    const media = document.createElement(source.tagName === 'VIDEO' ? 'video' : 'img');
    media.src = source.currentSrc || source.src;

    if (media.tagName === 'VIDEO') {
      media.muted = true;
      media.playsInline = true;
      media.controls = controls;
      media.loop = !controls;
      media.autoplay = !controls;
    } else {
      media.alt = '';
    }

    applyDisplay(media, display(slot, mode));
    return media;
  }

  // Se actualizează cardul mic.
  function updateSmall() {
    if (!smallMedia) return;

    smallMedia.innerHTML = '';
    const media = cloneMedia(primarySlot(), 'card');

    if (media) {
      smallMedia.appendChild(media);
    } else {
      smallMedia.innerHTML = '<div class="preview-placeholder">Media principală va apărea aici</div>';
    }
  }

  // Se actualizează modalul mare și miniaturile.
  function updateModal() {
    if (!siteMain || !siteThumbs) return;

    const slots = usableSlots();
    const primary = primarySlot() || slots[0] || null;

    siteMain.innerHTML = '';
    const main = cloneMedia(primary, 'detail', true);

    if (main) {
      siteMain.appendChild(main);
    } else {
      siteMain.innerHTML = '<div class="preview-placeholder">Media principală va apărea aici</div>';
    }

    siteThumbs.innerHTML = '';

    if (!slots.length) {
      siteThumbs.innerHTML = '<span style="color:rgba(245,242,233,.36);font-size:11px">Miniaturile vor apărea aici</span>';
      return;
    }

    slots.forEach(slot => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = `site-thumb${slot === primary ? ' active' : ''}`;

      const thumb = cloneMedia(slot, 'detail');
      if (thumb) button.appendChild(thumb);

      button.onclick = () => {
        siteMain.innerHTML = '';
        const chosen = cloneMedia(slot, 'detail', true);
        if (chosen) siteMain.appendChild(chosen);

        $$('.site-thumb', siteThumbs).forEach(item => item.classList.remove('active'));
        button.classList.add('active');
      };

      siteThumbs.appendChild(button);
    });
  }

  function updateAll() {
    updateText();
    updateSmall();
    updateModal();
    syncTags();
    syncPrimaryControls();
  }

  // Se afișează un fișier nou sau înlocuit.
  function buildPreview(file) {
    const media = document.createElement(file.type.startsWith('video/') ? 'video' : 'img');
    media.src = URL.createObjectURL(file);
    media.dataset.mediaPreview = '1';

    if (media.tagName === 'VIDEO') {
      media.muted = true;
      media.loop = true;
      media.autoplay = true;
      media.playsInline = true;
    } else {
      media.alt = '';
    }

    return media;
  }

  function resetDisplay(slot) {
    ['detail', 'card'].forEach(mode => {
      writeDisplay(slot, mode, {
        x: '50',
        y: '50',
        zoom: '1',
        fit: 'contain',
        rotation: '0'
      });
    });
  }

  function showNewControls(slot, show) {
    slot.querySelectorAll('.new-media-control').forEach(el => {
      el.style.display = show ? '' : 'none';
    });
  }

  const newPrimaryIndex = $('#newPrimaryIndex');

  $$('.js-new-primary').forEach(radio => {
    radio.addEventListener('change', () => {
      if (!radio.checked) return;

      const slot = radio.closest('.media-slot');
      const input = slot?.querySelector('input[type=file][name^="media["]');
      const index = input?.name.match(/media\[(\d+)\]/)?.[1];

      if (newPrimaryIndex && index !== undefined) newPrimaryIndex.value = index;

      $$('input[name="primary_image_id"]').forEach(item => item.checked = false);
      $$('.js-new-primary').forEach(item => {
        if (item !== radio) item.checked = false;
      });

      updateAll();
    });
  });

  $$('input[name="primary_image_id"]').forEach(radio => {
    radio.addEventListener('change', () => {
      if (!radio.checked) return;

      if (newPrimaryIndex) newPrimaryIndex.value = '';
      $$('.js-new-primary').forEach(item => item.checked = false);
      updateAll();
    });
  });

  $$('input[type=file][name^="media["]').forEach(input => {
    input.addEventListener('change', () => {
      const slot = input.closest('.media-slot');
      const file = input.files?.[0];
      if (!slot || !file) return;

      slot.querySelector('.slot-preview')?.remove();

      const wrap = document.createElement('div');
      wrap.className = 'slot-preview';

      const media = buildPreview(file);
      applyDisplay(media, display(slot, 'detail'));
      wrap.appendChild(media);
      slot.prepend(wrap);

      slot.classList.add('has-media');
      slot.querySelector('.slot-empty')?.style.setProperty('display', 'none');
      showNewControls(slot, true);

      const primary = slot.querySelector('.js-new-primary');

      if (
        primary &&
        !$('.media-slot input[name="primary_image_id"]:checked') &&
        !$('.media-slot .js-new-primary:checked')
      ) {
        primary.checked = true;
        primary.dispatchEvent(new Event('change', { bubbles: true }));
      }

      media.addEventListener('load', updateAll, { once: true });
      media.addEventListener('loadeddata', updateAll, { once: true });
      updateAll();
    });
  });

  $$('.js-replace-media').forEach(input => {
    input.addEventListener('change', () => {
      const slot = input.closest('.media-slot');
      const file = input.files?.[0];
      if (!slot || !file) return;

      const media = buildPreview(file);
      applyDisplay(media, display(slot, 'detail'));
      slot.querySelector('[data-media-preview]')?.replaceWith(media);

      media.addEventListener('load', updateAll, { once: true });
      media.addEventListener('loadeddata', updateAll, { once: true });
      updateAll();
    });
  });

  $$('.js-remove-new').forEach(button => {
    button.addEventListener('click', () => {
      const slot = button.closest('.media-slot');
      if (!slot) return;

      const input = slot.querySelector('input[type=file][name^="media["]');
      const primary = slot.querySelector('.js-new-primary');

      if (primary?.checked && newPrimaryIndex) newPrimaryIndex.value = '';
      if (input) input.value = '';
      if (primary) primary.checked = false;

      slot.querySelector('.slot-preview')?.remove();
      slot.classList.remove('has-media');
      slot.querySelector('.slot-empty')?.style.setProperty('display', 'grid');

      resetDisplay(slot);
      showNewControls(slot, false);
      updateAll();
    });
  });

  // Se marchează media existentă pentru ștergere la salvare.
  $$('.js-delete-existing').forEach(button => {
    button.addEventListener('click', () => {
      if (!confirm('Sigur vrei să ștergi acest fișier?')) return;

      const slot = button.closest('.media-slot');
      const deleteInput = slot?.querySelector('[data-delete-existing]');
      if (!slot || !deleteInput) return;

      deleteInput.value = deleteInput.dataset.mediaId || '';
      slot.dataset.deleted = '1';
      slot.style.display = 'none';

      slot.querySelectorAll('input, button').forEach(el => {
        if (el !== deleteInput) el.disabled = true;
      });

      updateAll();
    });
  });

  // Se deschide editorul pentru cardul mic sau modalul mare.
  const modal = $('#cropModal');
  const stage = $('#cropStage');
  const cropTitle = $('#cropTitle');
  const zoom = $('#cropZoom');
  const zoomValue = $('#cropZoomValue');
  const rotation = $('#cropRotation');
  const rotationValue = $('#cropRotationValue');
  const fitButtons = $$('.fit-switch button');

  let activeSlot = null;
  let activeMode = 'detail';
  let state = { x: 50, y: 50, zoom: 1, fit: 'contain', rotation: 0 };
  let drag = null;

  function renderEditor() {
    const media = $('#cropStageMedia');
    if (!media) return;

    applyDisplay(media, state);

    if (zoom) zoom.value = state.zoom;
    if (zoomValue) zoomValue.textContent = `${Number(state.zoom).toFixed(2)}×`;
    if (rotation) rotation.value = state.rotation;
    if (rotationValue) rotationValue.textContent = `${Math.round(state.rotation)}°`;

    fitButtons.forEach(button => {
      button.classList.toggle('active', button.dataset.fit === state.fit);
    });
  }

  function openEditor(slot, mode) {
    const source = slot.querySelector('[data-media-preview]');
    if (!source || !modal || !stage) return;

    activeSlot = slot;
    activeMode = mode === 'card' ? 'card' : 'detail';
    state = { ...display(slot, activeMode) };
    state.x = +state.x;
    state.y = +state.y;
    state.zoom = +state.zoom;
    state.rotation = +state.rotation;

    if (cropTitle) {
      cropTitle.textContent = activeMode === 'card'
        ? 'Ajustează cardul mic'
        : 'Ajustează modalul mare';
    }

    // Se folosește același raport ca pe site.
    stage.style.aspectRatio = '4 / 3';

    stage.innerHTML = '';

    const media = document.createElement(source.tagName === 'VIDEO' ? 'video' : 'img');
    media.id = 'cropStageMedia';
    media.src = source.currentSrc || source.src;

    if (media.tagName === 'VIDEO') {
      media.muted = true;
      media.loop = true;
      media.autoplay = true;
      media.playsInline = true;
    } else {
      media.alt = '';
    }

    stage.appendChild(media);
    renderEditor();
    modal.classList.add('open');
  }

  function closeEditor() {
    modal?.classList.remove('open');
    activeSlot = null;
    drag = null;
  }

  function applyEditor(save = false) {
    if (!activeSlot) return;

    writeDisplay(activeSlot, activeMode, {
      x: state.x.toFixed(2),
      y: state.y.toFixed(2),
      zoom: state.zoom.toFixed(2),
      fit: state.fit,
      rotation: Math.round(state.rotation)
    });

    if (activeMode === 'detail') {
      applyDisplay(activeSlot.querySelector('[data-media-preview]'), state);
    }

    const form = activeSlot.closest('form');
    updateAll();
    closeEditor();

    if (save && form) form.requestSubmit();
  }

  $$('.js-adjust').forEach(button => {
    button.addEventListener('click', () => {
      const slot = button.closest('.media-slot');
      if (slot) openEditor(slot, button.dataset.adjustMode);
    });
  });

  zoom?.addEventListener('input', () => {
    state.zoom = +zoom.value;
    renderEditor();
  });

  rotation?.addEventListener('input', () => {
    state.rotation = +rotation.value;
    renderEditor();
  });

  fitButtons.forEach(button => {
    button.addEventListener('click', () => {
      state.fit = button.dataset.fit || 'contain';
      renderEditor();
    });
  });

  $('#cropReset')?.addEventListener('click', () => {
    state = { x: 50, y: 50, zoom: 1, fit: 'contain', rotation: 0 };
    renderEditor();
  });

  $('#cropApply')?.addEventListener('click', () => applyEditor(false));
  $('#cropSave')?.addEventListener('click', () => applyEditor(true));
  $('#cropCancel')?.addEventListener('click', closeEditor);

  modal?.addEventListener('click', event => {
    if (event.target === modal) closeEditor();
  });

  // Se mută imaginea prin tragere.
  stage?.addEventListener('pointerdown', event => {
    if (event.button !== undefined && event.button !== 0) return;

    stage.setPointerCapture(event.pointerId);
    stage.classList.add('dragging');

    drag = {
      startX: event.clientX,
      startY: event.clientY,
      cropX: state.x,
      cropY: state.y
    };

    event.preventDefault();
  });

  stage?.addEventListener('pointermove', event => {
    if (!drag) return;

    const rect = stage.getBoundingClientRect();
    const dx = ((event.clientX - drag.startX) / rect.width) * 100;
    const dy = ((event.clientY - drag.startY) / rect.height) * 100;

    state.x = clamp(drag.cropX - dx, 0, 100);
    state.y = clamp(drag.cropY - dy, 0, 100);

    renderEditor();
    event.preventDefault();
  });

  function endDrag() {
    drag = null;
    stage?.classList.remove('dragging');
  }

  stage?.addEventListener('pointerup', endDrag);
  stage?.addEventListener('pointercancel', endDrag);
  stage?.addEventListener('lostpointercapture', endDrag);

  syncTags();
  updateAll();
})();