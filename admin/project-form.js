(() => {
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>[...r.querySelectorAll(s)];
  const clamp=(v,min,max)=>Math.max(min,Math.min(max,v));

  const title=$('#title'), category=$('#category'), description=$('#description');
  const smallTitle=$('#smallTitle'), smallCategory=$('#smallCategory'), smallMedia=$('#smallMedia');
  const siteTitle=$('#siteTitle'), siteCategory=$('#siteCategory'), siteDescription=$('#siteDescription');
  const siteMain=$('#siteMain'), siteThumbs=$('#siteThumbs'), siteTags=$('#siteTags');

  /* TAGURI */
  const tagsHidden=$('#tags'), tagInput=$('#tagInput'), tagAdd=$('#tagAdd'), tagList=$('#tagList'), tagCount=$('#tagCount');
  const MAX_TAGS=14;
  let tags=(tagsHidden?.value||'').split(',').map(v=>v.trim()).filter(Boolean);

  function renderSiteTags(){
    if(!siteTags)return;
    siteTags.innerHTML='';
    tags.forEach(tag=>{
      const span=document.createElement('span');
      span.className='site-modal-tag';
      span.textContent=tag;
      siteTags.appendChild(span);
    });
  }

  function syncTags(){
    tags=[...new Set(tags)].slice(0,MAX_TAGS);
    if(tagsHidden)tagsHidden.value=tags.join(', ');
    if(tagCount)tagCount.textContent=`${tags.length}/${MAX_TAGS}`;

    if(tagList){
      tagList.innerHTML='';
      tags.forEach((tag,i)=>{
        const chip=document.createElement('span');
        chip.className='tag-chip';
        chip.append(document.createTextNode(tag));

        const remove=document.createElement('button');
        remove.type='button';
        remove.textContent='×';
        remove.title='Șterge';
        remove.addEventListener('click',()=>{
          tags.splice(i,1);
          syncTags();
        });

        chip.appendChild(remove);
        tagList.appendChild(chip);
      });
    }

    renderSiteTags();
  }

  function addTag(){
    const value=tagInput?.value.trim();
    if(!value||tags.length>=MAX_TAGS)return;

    if(!tags.some(t=>t.toLowerCase()===value.toLowerCase())){
      tags.push(value);
    }

    if(tagInput)tagInput.value='';
    syncTags();
  }

  tagAdd?.addEventListener('click',addTag);
  tagInput?.addEventListener('keydown',e=>{
    if(e.key==='Enter'){
      e.preventDefault();
      addTag();
    }
  });

  /* TEXTE PREVIEW */
  const categoryLabel=()=>category?.selectedOptions?.[0]?.textContent?.trim()||'Categorie';

  function updateText(){
    const t=title?.value.trim()||'Titlul proiectului';
    const c=categoryLabel();
    const d=description?.value.trim()||'Descrierea proiectului va apărea aici.';

    if(smallTitle)smallTitle.textContent=t;
    if(smallCategory)smallCategory.textContent=c;
    if(siteTitle)siteTitle.textContent=t;
    if(siteCategory)siteCategory.textContent=c;
    if(siteDescription)siteDescription.textContent=d;
  }

  title?.addEventListener('input',updateText);
  category?.addEventListener('change',updateText);
  description?.addEventListener('input',updateText);

  /* MEDIA */
  function crop(slot){
    return {
      x:slot.querySelector('[data-crop-x]')?.value||'50',
      y:slot.querySelector('[data-crop-y]')?.value||'50',
      zoom:slot.querySelector('[data-crop-zoom]')?.value||'1',
      fit:slot.querySelector('[data-crop-fit]')?.value||'cover',
      rotation:slot.querySelector('[data-rotation]')?.value||'0'
    };
  }

  function usableSlots(){
    return $$('.media-slot').filter(slot=>
      slot.querySelector('[data-media-preview]') &&
      !slot.querySelector('input[name="delete_image[]"]:checked')
    );
  }

  function primarySlot(){
    const existing=$('.media-slot input[name="primary_image_id"]:checked')?.closest('.media-slot');

    if(
      existing &&
      !existing.querySelector('input[name="delete_image[]"]:checked')
    ){
      return existing;
    }

    const fresh=$('.media-slot .js-new-primary:checked')?.closest('.media-slot');

    if(fresh){
      return fresh;
    }

    return usableSlots()[0]||null;
  }

  function cloneMedia(slot,forModal=false){
    const src=slot?.querySelector('[data-media-preview]');
    if(!src)return null;

    let el;

    if(src.tagName==='VIDEO'){
      el=document.createElement('video');
      el.src=src.currentSrc||src.src;
      el.muted=true;
      el.playsInline=true;

      if(forModal){
        el.controls=true;
      }else{
        el.loop=true;
        el.autoplay=true;
      }
    }else{
      el=document.createElement('img');
      el.src=src.src;
      el.alt='';
    }

    if(!forModal){
      const c=crop(slot);
      el.style.setProperty('--crop-x',c.x+'%');
      el.style.setProperty('--crop-y',c.y+'%');
      el.style.setProperty('--zoom',c.zoom);
      el.style.setProperty('--fit',c.fit);
      el.style.setProperty('--rotation',c.rotation+'deg');
    }

    return el;
  }

  function updateSmall(){
    if(!smallMedia)return;

    smallMedia.innerHTML='';

    const media=cloneMedia(primarySlot());

    if(media){
      smallMedia.appendChild(media);
    }else{
      const p=document.createElement('div');
      p.className='preview-placeholder';
      p.textContent='Media principală va apărea aici';
      smallMedia.appendChild(p);
    }
  }

  function updateSiteModal(){
    if(!siteMain||!siteThumbs)return;

    const slots=usableSlots();
    const primary=primarySlot()||slots[0]||null;

    siteMain.innerHTML='';
    const main=cloneMedia(primary,true);

    if(main){
      siteMain.appendChild(main);
    }else{
      const p=document.createElement('div');
      p.className='preview-placeholder';
      p.textContent='Media principală va apărea aici';
      siteMain.appendChild(p);
    }

    siteThumbs.innerHTML='';

    if(!slots.length){
      const t=document.createElement('span');
      t.style.cssText='color:rgba(245,242,233,.36);font-size:11px';
      t.textContent='Miniaturile vor apărea aici';
      siteThumbs.appendChild(t);
      return;
    }

    slots.forEach(slot=>{
      const btn=document.createElement('button');
      btn.type='button';
      btn.className='site-thumb'+(slot===primary?' active':'');

      const media=cloneMedia(slot,true);

      if(media){
        if(media.tagName==='VIDEO'){
          media.controls=false;
        }
        btn.appendChild(media);
      }

      btn.addEventListener('click',()=>{
        siteMain.innerHTML='';

        const chosen=cloneMedia(slot,true);

        if(chosen){
          siteMain.appendChild(chosen);
        }

        $$('.site-thumb',siteThumbs).forEach(x=>x.classList.remove('active'));
        btn.classList.add('active');
      });

      siteThumbs.appendChild(btn);
    });
  }

  function updateAll(){
    updateText();
    updateSmall();
    updateSiteModal();
    renderSiteTags();
  }

  const newPrimaryIndex=$('#newPrimaryIndex');

  function showNewMediaControls(slot){
    const primaryWrap=slot.querySelector('.new-primary-wrap');
    const deleteWrap=slot.querySelector('.new-delete-wrap');
    const adjust=slot.querySelector('.js-adjust');

    if(primaryWrap)primaryWrap.style.display='flex';
    if(deleteWrap)deleteWrap.style.display='flex';
    if(adjust)adjust.style.display='';
  }

  function clearNewSlot(slot){
    const fileInput=slot.querySelector('input[type=file]');
    const primary=slot.querySelector('.js-new-primary');

    if(
      primary?.checked &&
      newPrimaryIndex
    ){
      newPrimaryIndex.value='';
    }

    if(fileInput)fileInput.value='';
    if(primary)primary.checked=false;

    slot.querySelector('.slot-preview')?.remove();
    slot.classList.remove('has-media');

    const empty=slot.querySelector('.slot-empty');
    if(empty)empty.style.display='grid';

    const primaryWrap=slot.querySelector('.new-primary-wrap');
    const deleteWrap=slot.querySelector('.new-delete-wrap');
    const adjust=slot.querySelector('.js-adjust');

    if(primaryWrap)primaryWrap.style.display='none';
    if(deleteWrap)deleteWrap.style.display='none';
    if(adjust)adjust.style.display='none';

    const resetMap=[
      ['[data-crop-x]','50'],
      ['[data-crop-y]','50'],
      ['[data-crop-zoom]','1'],
      ['[data-crop-fit]','cover'],
      ['[data-rotation]','0']
    ];

    resetMap.forEach(([selector,value])=>{
      const el=slot.querySelector(selector);
      if(el)el.value=value;
    });

    updateAll();
  }

  $$('.js-new-primary').forEach(radio=>{
    radio.addEventListener('change',()=>{
      if(!radio.checked)return;

      const slot=radio.closest('.media-slot');
      const fileInput=slot?.querySelector('input[type=file]');
      const match=fileInput?.name.match(/media\[(\d+)\]/);
      const index=match?.[1];

      if(index!==undefined && newPrimaryIndex){
        newPrimaryIndex.value=index;
      }

      $$('input[name="primary_image_id"]').forEach(oldRadio=>{
        oldRadio.checked=false;
      });

      $$('.js-new-primary').forEach(other=>{
        if(other!==radio)other.checked=false;
      });

      updateAll();
    });
  });

  $$('input[name="primary_image_id"]').forEach(radio=>{
    radio.addEventListener('change',()=>{
      if(!radio.checked)return;

      if(newPrimaryIndex)newPrimaryIndex.value='';

      $$('.js-new-primary').forEach(newRadio=>{
        newRadio.checked=false;
      });

      updateAll();
    });
  });

  $$('.js-remove-new').forEach(button=>{
    button.addEventListener('click',event=>{
      event.preventDefault();
      event.stopPropagation();

      const slot=button.closest('.media-slot');

      if(slot){
        clearNewSlot(slot);
      }
    });
  });

  $$('.media-slot input[type=file]').forEach(input=>{
    input.addEventListener('change',()=>{
      const slot=input.closest('.media-slot');
      const file=input.files?.[0];

      if(!slot||!file)return;

      slot.querySelector('.slot-preview')?.remove();

      const wrap=document.createElement('div');
      wrap.className='slot-preview';

      const media=document.createElement(
        file.type.startsWith('video/')?'video':'img'
      );

      if(media.tagName==='VIDEO'){
        media.muted=true;
        media.loop=true;
        media.autoplay=true;
        media.playsInline=true;
      }else{
        media.alt='';
      }

      media.dataset.mediaPreview='1';
      media.src=URL.createObjectURL(file);

      wrap.appendChild(media);
      slot.prepend(wrap);
      slot.classList.add('has-media');

      const empty=slot.querySelector('.slot-empty');
      if(empty)empty.style.display='none';

      showNewMediaControls(slot);

      const freshPrimary=slot.querySelector('.js-new-primary');

      if(
        freshPrimary &&
        !$('.media-slot input[name="primary_image_id"]:checked') &&
        !$('.media-slot .js-new-primary:checked')
      ){
        freshPrimary.checked=true;
        freshPrimary.dispatchEvent(new Event('change',{bubbles:true}));
      }

      media.addEventListener('loadeddata',updateAll,{once:true});
      media.addEventListener('load',updateAll,{once:true});

      updateAll();
    });
  });

  $$(
    'input[name="primary_image_id"],' +
    'input[name="delete_image[]"]'
  ).forEach(el=>el.addEventListener('change',updateAll));

  /* EDITOR NATURAL: TRAGI CU MOUSE-UL / DEGETUL */
  const modal=$('#cropModal');
  const stage=$('#cropStage');
  const zoom=$('#cropZoom');
  const zoomValue=$('#cropZoomValue');
  const rotation=$('#cropRotation');
  const rotationValue=$('#cropRotationValue');
  const fitButtons=$$('.fit-switch button');

  let active=null;
  let state={
    x:50,
    y:50,
    zoom:1,
    fit:'cover',
    rotation:0
  };

  let drag=null;

  function stageMedia(){
    return $('#cropStageMedia');
  }

  function renderEditor(){
    const media=stageMedia();

    if(!media)return;

    media.style.setProperty('--crop-x',state.x+'%');
    media.style.setProperty('--crop-y',state.y+'%');
    media.style.setProperty('--zoom',state.zoom);
    media.style.setProperty('--fit',state.fit);
    media.style.setProperty('--rotation',state.rotation+'deg');

    if(zoom)zoom.value=state.zoom;
    if(zoomValue)zoomValue.textContent=Number(state.zoom).toFixed(2)+'×';

    if(rotation)rotation.value=state.rotation;
    if(rotationValue)rotationValue.textContent=Math.round(state.rotation)+'°';

    fitButtons.forEach(button=>{
      button.classList.toggle(
        'active',
        button.dataset.fit===state.fit
      );
    });
  }

  function openEditor(slot){
    const src=slot.querySelector('[data-media-preview]');

    if(!src||!modal||!stage)return;

    active=slot;

    const c=crop(slot);

    state={
      x:+c.x,
      y:+c.y,
      zoom:+c.zoom,
      fit:c.fit,
      rotation:+c.rotation
    };

    stage.innerHTML='';

    const media=document.createElement(
      src.tagName==='VIDEO'?'video':'img'
    );

    media.id='cropStageMedia';
    media.src=src.currentSrc||src.src;

    if(media.tagName==='VIDEO'){
      media.muted=true;
      media.loop=true;
      media.autoplay=true;
      media.playsInline=true;
    }else{
      media.alt='';
    }

    stage.appendChild(media);
    renderEditor();

    modal.classList.add('open');
  }

  function closeEditor(){
    modal?.classList.remove('open');
    active=null;
    drag=null;
  }

  function applyEditor(saveAfter=false){
    if(!active)return;

    const form=active.closest('form');

    const set=(selector,value)=>{
      const el=active.querySelector(selector);
      if(el)el.value=value;
    };

    set('[data-crop-x]',state.x.toFixed(2));
    set('[data-crop-y]',state.y.toFixed(2));
    set('[data-crop-zoom]',state.zoom.toFixed(2));
    set('[data-crop-fit]',state.fit);
    set('[data-rotation]',Math.round(state.rotation));

    const media=active.querySelector('[data-media-preview]');

    if(media){
      media.style.setProperty('--crop-x',state.x+'%');
      media.style.setProperty('--crop-y',state.y+'%');
      media.style.setProperty('--zoom',state.zoom);
      media.style.setProperty('--fit',state.fit);
      media.style.setProperty('--rotation',state.rotation+'deg');
    }

    updateAll();

    if(saveAfter && form){
      closeEditor();

      if(typeof form.requestSubmit==='function'){
        form.requestSubmit();
      }else{
        form.submit();
      }

      return;
    }

    closeEditor();
  }

  $$('.js-adjust').forEach(button=>{
    button.addEventListener('click',event=>{
      event.preventDefault();

      const slot=button.closest('.media-slot');

      if(slot){
        openEditor(slot);
      }
    });
  });

  zoom?.addEventListener('input',()=>{
    state.zoom=+zoom.value;
    renderEditor();
  });

  rotation?.addEventListener('input',()=>{
    state.rotation=+rotation.value;
    renderEditor();
  });

  fitButtons.forEach(button=>{
    button.addEventListener('click',()=>{
      state.fit=button.dataset.fit||'cover';
      renderEditor();
    });
  });

  $('#cropReset')?.addEventListener('click',()=>{
    state={
      x:50,
      y:50,
      zoom:1,
      fit:'cover',
      rotation:0
    };

    renderEditor();
  });

  $('#cropApply')?.addEventListener('click',()=>applyEditor(false));
  $('#cropSave')?.addEventListener('click',()=>applyEditor(true));
  $('#cropCancel')?.addEventListener('click',closeEditor);

  modal?.addEventListener('click',event=>{
    if(event.target===modal){
      closeEditor();
    }
  });

  /*
   * Drag intuitiv:
   * - tragi imaginea la dreapta -> imaginea se mută la dreapta
   * - tragi la stânga -> se mută la stânga
   * - sus/jos la fel
   *
   * Folosim pointer events, deci funcționează și cu mouse,
   * touchpad, stylus și deget pe telefon/tabletă.
   */
  stage?.addEventListener('pointerdown',event=>{
    if(event.button!==undefined && event.button!==0)return;

    stage.setPointerCapture(event.pointerId);
    stage.classList.add('dragging');

    drag={
      startX:event.clientX,
      startY:event.clientY,
      cropX:state.x,
      cropY:state.y
    };

    event.preventDefault();
  });

  stage?.addEventListener('pointermove',event=>{
    if(!drag)return;

    const rect=stage.getBoundingClientRect();

    const dx=
      ((event.clientX-drag.startX)/rect.width)*100;

    const dy=
      ((event.clientY-drag.startY)/rect.height)*100;

    /*
     * object-position funcționează invers față de gestul vizual,
     * de aceea scădem deplasarea ca imaginea să urmeze mâna.
     */
    state.x=clamp(drag.cropX-dx,0,100);
    state.y=clamp(drag.cropY-dy,0,100);

    renderEditor();
    event.preventDefault();
  });

  function endDrag(){
    drag=null;
    stage?.classList.remove('dragging');
  }

  stage?.addEventListener('pointerup',endDrag);
  stage?.addEventListener('pointercancel',endDrag);
  stage?.addEventListener('lostpointercapture',endDrag);

  syncTags();
  updateAll();
})();