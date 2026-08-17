(() => {
  const $=(s,r=document)=>r.querySelector(s);
  const $$=(s,r=document)=>[...r.querySelectorAll(s)];
  const clamp=(v,min,max)=>Math.max(min,Math.min(max,v));

  const titleInput=$('#title');
  const categoryInput=$('#category');
  const descriptionInput=$('#description');

  const smallTitle=$('#smallTitle');
  const smallCategory=$('#smallCategory');
  const smallMedia=$('#smallMedia');

  const largeTitle=$('#largeTitle');
  const largeCategory=$('#largeCategory');
  const largeDescription=$('#largeDescription');
  const largeMedia=$('#largeMedia');

  function categoryLabel(){
    return categoryInput?.selectedOptions?.[0]?.textContent?.trim() || 'Categorie';
  }

  function updateTextPreview(){
    const title=titleInput?.value.trim() || 'Titlul proiectului';
    const category=categoryLabel();
    const description=descriptionInput?.value.trim() || 'Descrierea proiectului va apărea aici.';

    if(smallTitle) smallTitle.textContent=title;
    if(smallCategory) smallCategory.textContent=category;
    if(largeTitle) largeTitle.textContent=title;
    if(largeCategory) largeCategory.textContent=category;
    if(largeDescription) largeDescription.textContent=description;
  }

  function cropValues(slot){
    return {
      x:slot.querySelector('[data-crop-x]')?.value||'50',
      y:slot.querySelector('[data-crop-y]')?.value||'50',
      zoom:slot.querySelector('[data-crop-zoom]')?.value||'1',
      fit:slot.querySelector('[data-crop-fit]')?.value||'cover',
      rotation:slot.querySelector('[data-rotation]')?.value||'0',
    };
  }

  function previewSource(){
    const selected=$('.media-slot input[name="primary_image_id"]:checked')?.closest('.media-slot');
    if(selected && !selected.querySelector('input[name="delete_image[]"]:checked')) return selected;

    const existing=$$('.media-slot.existing').find(slot=>!slot.querySelector('input[name="delete_image[]"]:checked'));
    if(existing) return existing;

    return $$('.media-slot.has-media')[0] || null;
  }

  function cloneMedia(slot){
    const src=slot?.querySelector('[data-media-preview]');
    if(!src) return null;
    const crop=cropValues(slot);
    let el;

    if(src.tagName==='VIDEO'){
      el=document.createElement('video');
      el.src=src.currentSrc || src.src;
      el.muted=true; el.loop=true; el.autoplay=true; el.playsInline=true;
    } else {
      el=document.createElement('img');
      el.src=src.src;
      el.alt='';
    }

    el.style.setProperty('--crop-x',crop.x+'%');
    el.style.setProperty('--crop-y',crop.y+'%');
    el.style.setProperty('--zoom',crop.zoom);
    el.style.setProperty('--fit',crop.fit);
    el.style.setProperty('--rotation',crop.rotation+'deg');
    return el;
  }

  function updateMediaPreview(){
    const source=previewSource();

    [smallMedia,largeMedia].forEach(target=>{
      if(!target) return;
      target.innerHTML='';
      const media=cloneMedia(source);
      if(media){
        target.appendChild(media);
      }else{
        const p=document.createElement('div');
        p.className='preview-placeholder';
        p.textContent='Imaginea sau videoul principal va apărea aici';
        target.appendChild(p);
      }
    });
  }

  function updateAll(){
    updateTextPreview();
    updateMediaPreview();
  }

  titleInput?.addEventListener('input',updateTextPreview);
  descriptionInput?.addEventListener('input',updateTextPreview);
  categoryInput?.addEventListener('change',updateTextPreview);

  $$('.media-slot input[type=file]').forEach(input=>{
    input.addEventListener('change',()=>{
      const slot=input.closest('.media-slot');
      const file=input.files?.[0];
      if(!slot||!file) return;

      const old=slot.querySelector('.slot-preview');
      if(old) old.remove();

      const wrap=document.createElement('div');
      wrap.className='slot-preview';

      let media;
      if(file.type.startsWith('video/')){
        media=document.createElement('video');
        media.muted=true; media.loop=true; media.autoplay=true; media.playsInline=true;
      }else{
        media=document.createElement('img');
        media.alt='';
      }

      media.dataset.mediaPreview='1';
      media.src=URL.createObjectURL(file);
      wrap.appendChild(media);
      slot.appendChild(wrap);

      slot.classList.add('has-media');
      const empty=slot.querySelector('.slot-empty');
      if(empty) empty.style.display='none';
      const adjust=slot.querySelector('.js-adjust');
      if(adjust) adjust.style.display='';

      media.addEventListener('loadeddata',updateAll,{once:true});
      media.addEventListener('load',updateAll,{once:true});
      updateAll();
    });
  });

  $$('input[name="primary_image_id"],input[name="delete_image[]"]').forEach(el=>{
    el.addEventListener('change',updateMediaPreview);
  });

  const modal=$('#cropModal');
  const stage=$('#cropStage');
  const zoom=$('#cropZoom');
  const zoomValue=$('#cropZoomValue');
  const fitButtons=$$('.fit-switch button');
  let activeSlot=null;
  let state={x:50,y:50,zoom:1,fit:'cover',rotation:0};
  let drag=null;

  function stageMedia(){
    return $('#cropStageMedia');
  }

  function renderStage(){
    const media=stageMedia();
    if(!media) return;
    media.style.setProperty('--crop-x',state.x+'%');
    media.style.setProperty('--crop-y',state.y+'%');
    media.style.setProperty('--zoom',state.zoom);
    media.style.setProperty('--fit',state.fit);
    media.style.setProperty('--rotation',state.rotation+'deg');
    if(zoom) zoom.value=state.zoom;
    if(zoomValue) zoomValue.textContent=Number(state.zoom).toFixed(2)+'×';
    fitButtons.forEach(b=>b.classList.toggle('active',b.dataset.fit===state.fit));
  }

  function openEditor(slot){
    const source=slot.querySelector('[data-media-preview]');
    if(!source||!modal||!stage) return;

    activeSlot=slot;
    const c=cropValues(slot);
    state={
      x:parseFloat(c.x),y:parseFloat(c.y),zoom:parseFloat(c.zoom),
      fit:c.fit,rotation:parseInt(c.rotation,10)||0
    };

    stage.innerHTML='';
    let media;
    if(source.tagName==='VIDEO'){
      media=document.createElement('video');
      media.src=source.currentSrc||source.src;
      media.muted=true; media.loop=true; media.autoplay=true; media.playsInline=true;
    }else{
      media=document.createElement('img');
      media.src=source.src; media.alt='';
    }
    media.id='cropStageMedia';
    stage.appendChild(media);
    renderStage();
    modal.classList.add('open');
  }

  function closeEditor(){
    modal?.classList.remove('open');
    activeSlot=null; drag=null;
  }

  function applyEditor(){
    if(!activeSlot) return;
    const map=[
      ['[data-crop-x]',state.x.toFixed(2)],
      ['[data-crop-y]',state.y.toFixed(2)],
      ['[data-crop-zoom]',state.zoom.toFixed(2)],
      ['[data-crop-fit]',state.fit],
      ['[data-rotation]',String(state.rotation)],
    ];
    map.forEach(([sel,val])=>{const el=activeSlot.querySelector(sel); if(el) el.value=val;});

    const media=activeSlot.querySelector('[data-media-preview]');
    if(media){
      media.style.setProperty('--crop-x',state.x+'%');
      media.style.setProperty('--crop-y',state.y+'%');
      media.style.setProperty('--zoom',state.zoom);
      media.style.setProperty('--fit',state.fit);
      media.style.setProperty('--rotation',state.rotation+'deg');
    }
    updateMediaPreview();
    closeEditor();
  }

  $$('.js-adjust').forEach(btn=>btn.addEventListener('click',e=>{
    e.preventDefault(); e.stopPropagation();
    const slot=btn.closest('.media-slot');
    if(slot) openEditor(slot);
  }));

  zoom?.addEventListener('input',()=>{state.zoom=parseFloat(zoom.value); renderStage();});
  fitButtons.forEach(btn=>btn.addEventListener('click',()=>{state.fit=btn.dataset.fit||'cover'; renderStage();}));

  $$('.js-move').forEach(btn=>btn.addEventListener('click',()=>{
    const step=5;
    if(btn.dataset.move==='left') state.x=clamp(state.x-step,0,100);
    if(btn.dataset.move==='right') state.x=clamp(state.x+step,0,100);
    if(btn.dataset.move==='up') state.y=clamp(state.y-step,0,100);
    if(btn.dataset.move==='down') state.y=clamp(state.y+step,0,100);
    renderStage();
  }));

  $$('.js-rotate').forEach(btn=>btn.addEventListener('click',()=>{
    state.rotation += btn.dataset.rotate==='left' ? -90 : 90;
    if(state.rotation>180) state.rotation-=360;
    if(state.rotation<-180) state.rotation+=360;
    renderStage();
  }));

  $('#cropReset')?.addEventListener('click',()=>{
    state={x:50,y:50,zoom:1,fit:'cover',rotation:0};
    renderStage();
  });
  $('#cropApply')?.addEventListener('click',applyEditor);
  $('#cropCancel')?.addEventListener('click',closeEditor);
  modal?.addEventListener('click',e=>{if(e.target===modal) closeEditor();});

  stage?.addEventListener('pointerdown',e=>{
    stage.setPointerCapture(e.pointerId);
    drag={px:e.clientX,py:e.clientY,x:state.x,y:state.y};
  });
  stage?.addEventListener('pointermove',e=>{
    if(!drag) return;
    const rect=stage.getBoundingClientRect();
    const dx=(e.clientX-drag.px)/rect.width*100;
    const dy=(e.clientY-drag.py)/rect.height*100;
    state.x=clamp(drag.x-dx,0,100);
    state.y=clamp(drag.y-dy,0,100);
    renderStage();
  });
  stage?.addEventListener('pointerup',()=>drag=null);
  stage?.addEventListener('pointercancel',()=>drag=null);

  updateAll();
})();