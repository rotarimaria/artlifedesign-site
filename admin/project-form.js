(() => {
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>[...r.querySelectorAll(s)];
  const clamp=(v,min,max)=>Math.max(min,Math.min(max,v));

  const title=$('#title'), category=$('#category'), description=$('#description');
  const smallTitle=$('#smallTitle'), smallCategory=$('#smallCategory'), smallMedia=$('#smallMedia');
  const siteTitle=$('#siteTitle'), siteCategory=$('#siteCategory'), siteDescription=$('#siteDescription');
  const siteMain=$('#siteMain'), siteThumbs=$('#siteThumbs'), siteTags=$('#siteTags');

  /* ---------- TAGS ---------- */
  const tagsHidden=$('#tags'), tagInput=$('#tagInput'), tagAdd=$('#tagAdd'), tagList=$('#tagList'), tagCount=$('#tagCount');
  const MAX_TAGS=14;
  let tags=(tagsHidden?.value||'').split(',').map(v=>v.trim()).filter(Boolean);

  function syncTags(){
    tags=[...new Set(tags)].slice(0,MAX_TAGS);
    if(tagsHidden) tagsHidden.value=tags.join(', ');
    if(tagCount) tagCount.textContent=`${tags.length}/${MAX_TAGS}`;
    if(tagList){
      tagList.innerHTML='';
      tags.forEach((tag,i)=>{
        const chip=document.createElement('span');
        chip.className='tag-chip';
        chip.append(document.createTextNode(tag));
        const remove=document.createElement('button');
        remove.type='button'; remove.textContent='×'; remove.title='Șterge';
        remove.addEventListener('click',()=>{tags.splice(i,1);syncTags();renderSiteTags();});
        chip.appendChild(remove);
        tagList.appendChild(chip);
      });
    }
    renderSiteTags();
  }

  function addTag(){
    const value=tagInput?.value.trim();
    if(!value||tags.length>=MAX_TAGS) return;
    if(!tags.some(t=>t.toLowerCase()===value.toLowerCase())) tags.push(value);
    tagInput.value='';
    syncTags();
  }

  tagAdd?.addEventListener('click',addTag);
  tagInput?.addEventListener('keydown',e=>{
    if(e.key==='Enter'){e.preventDefault();addTag();}
  });

  function renderSiteTags(){
    if(!siteTags) return;
    siteTags.innerHTML='';
    tags.forEach(tag=>{
      const span=document.createElement('span');
      span.className='site-modal-tag';
      span.textContent=tag;
      siteTags.appendChild(span);
    });
  }

  /* ---------- TEXT PREVIEW ---------- */
  const catLabel=()=>category?.selectedOptions?.[0]?.textContent?.trim()||'Categorie';
  function updateText(){
    const t=title?.value.trim()||'Titlul proiectului';
    const c=catLabel();
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

  /* ---------- MEDIA ---------- */
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
    const selected=$('.media-slot input[name="primary_image_id"]:checked')?.closest('.media-slot');
    return selected&&!selected.querySelector('input[name="delete_image[]"]:checked') ? selected : usableSlots()[0]||null;
  }

  function cloneMedia(slot,forModal=false){
    const src=slot?.querySelector('[data-media-preview]');
    if(!src)return null;
    let el;
    if(src.tagName==='VIDEO'){
      el=document.createElement('video');
      el.src=src.currentSrc||src.src; el.muted=true; el.playsInline=true;
      if(forModal) el.controls=true; else {el.loop=true;el.autoplay=true;}
    }else{
      el=document.createElement('img'); el.src=src.src; el.alt='';
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
    const m=cloneMedia(primarySlot());
    smallMedia.append(m||Object.assign(document.createElement('div'),{className:'preview-placeholder',textContent:'Media principală va apărea aici'}));
  }

  function updateSiteModal(){
    if(!siteMain||!siteThumbs)return;
    const slots=usableSlots(), primary=primarySlot()||slots[0]||null;
    siteMain.innerHTML='';
    const main=cloneMedia(primary,true);
    siteMain.append(main||Object.assign(document.createElement('div'),{className:'preview-placeholder',textContent:'Media principală va apărea aici'}));

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
      btn.type='button';btn.className='site-thumb'+(slot===primary?' active':'');
      const media=cloneMedia(slot,true);
      if(media){if(media.tagName==='VIDEO')media.controls=false;btn.appendChild(media);}
      btn.addEventListener('click',()=>{
        siteMain.innerHTML='';
        const chosen=cloneMedia(slot,true);
        if(chosen)siteMain.appendChild(chosen);
        $$('.site-thumb',siteThumbs).forEach(x=>x.classList.remove('active'));
        btn.classList.add('active');
      });
      siteThumbs.appendChild(btn);
    });
  }

  function updateAll(){updateText();updateSmall();updateSiteModal();renderSiteTags();}

  $$('.media-slot input[type=file]').forEach(input=>{
    input.addEventListener('change',()=>{
      const slot=input.closest('.media-slot'), file=input.files?.[0];
      if(!slot||!file)return;
      slot.querySelector('.slot-preview')?.remove();

      const wrap=document.createElement('div');wrap.className='slot-preview';
      const media=document.createElement(file.type.startsWith('video/')?'video':'img');
      if(media.tagName==='VIDEO'){media.muted=true;media.loop=true;media.autoplay=true;media.playsInline=true;} else media.alt='';
      media.dataset.mediaPreview='1';media.src=URL.createObjectURL(file);
      wrap.appendChild(media);slot.prepend(wrap);slot.classList.add('has-media');
      const empty=slot.querySelector('.slot-empty');if(empty)empty.style.display='none';
      const adjust=slot.querySelector('.js-adjust');if(adjust)adjust.style.display='';
      media.addEventListener('loadeddata',updateAll,{once:true});
      media.addEventListener('load',updateAll,{once:true});
      updateAll();
    });
  });

  $$('input[name="primary_image_id"],input[name="delete_image[]"]').forEach(el=>el.addEventListener('change',updateAll));

  /* ---------- FREE DRAG EDITOR ---------- */
  const modal=$('#cropModal'), stage=$('#cropStage');
  const zoom=$('#cropZoom'), zoomValue=$('#cropZoomValue');
  const rotation=$('#cropRotation'), rotationValue=$('#cropRotationValue');
  const fitButtons=$$('.fit-switch button');
  let active=null, state={x:50,y:50,zoom:1,fit:'cover',rotation:0}, drag=null;

  function stageMedia(){return $('#cropStageMedia');}
  function renderEditor(){
    const m=stageMedia();if(!m)return;
    m.style.setProperty('--crop-x',state.x+'%');
    m.style.setProperty('--crop-y',state.y+'%');
    m.style.setProperty('--zoom',state.zoom);
    m.style.setProperty('--fit',state.fit);
    m.style.setProperty('--rotation',state.rotation+'deg');
    if(zoom)zoom.value=state.zoom;if(zoomValue)zoomValue.textContent=Number(state.zoom).toFixed(2)+'×';
    if(rotation)rotation.value=state.rotation;if(rotationValue)rotationValue.textContent=Math.round(state.rotation)+'°';
    fitButtons.forEach(b=>b.classList.toggle('active',b.dataset.fit===state.fit));
  }

  function openEditor(slot){
    const src=slot.querySelector('[data-media-preview]');if(!src||!modal||!stage)return;
    active=slot;const c=crop(slot);
    state={x:+c.x,y:+c.y,zoom:+c.zoom,fit:c.fit,rotation:+c.rotation};
    stage.innerHTML='';
    const m=document.createElement(src.tagName==='VIDEO'?'video':'img');
    m.id='cropStageMedia';m.src=src.currentSrc||src.src;
    if(m.tagName==='VIDEO'){m.muted=true;m.loop=true;m.autoplay=true;m.playsInline=true;}else m.alt='';
    stage.appendChild(m);renderEditor();modal.classList.add('open');
  }

  function closeEditor(){modal?.classList.remove('open');active=null;drag=null;}
  function applyEditor(){
    if(!active)return;
    const set=(sel,val)=>{const el=active.querySelector(sel);if(el)el.value=val;};
    set('[data-crop-x]',state.x.toFixed(2));set('[data-crop-y]',state.y.toFixed(2));
    set('[data-crop-zoom]',state.zoom.toFixed(2));set('[data-crop-fit]',state.fit);set('[data-rotation]',Math.round(state.rotation));
    const m=active.querySelector('[data-media-preview]');
    if(m){m.style.setProperty('--crop-x',state.x+'%');m.style.setProperty('--crop-y',state.y+'%');m.style.setProperty('--zoom',state.zoom);m.style.setProperty('--fit',state.fit);m.style.setProperty('--rotation',state.rotation+'deg');}
    updateAll();closeEditor();
  }

  $$('.js-adjust').forEach(btn=>btn.addEventListener('click',e=>{e.preventDefault();const slot=btn.closest('.media-slot');if(slot)openEditor(slot);}));
  zoom?.addEventListener('input',()=>{state.zoom=+zoom.value;renderEditor();});
  rotation?.addEventListener('input',()=>{state.rotation=+rotation.value;renderEditor();});
  fitButtons.forEach(btn=>btn.addEventListener('click',()=>{state.fit=btn.dataset.fit||'cover';renderEditor();}));
  $('#cropReset')?.addEventListener('click',()=>{state={x:50,y:50,zoom:1,fit:'cover',rotation:0};renderEditor();});
  $('#cropApply')?.addEventListener('click',applyEditor);$('#cropCancel')?.addEventListener('click',closeEditor);
  modal?.addEventListener('click',e=>{if(e.target===modal)closeEditor();});

  stage?.addEventListener('pointerdown',e=>{stage.setPointerCapture(e.pointerId);drag={px:e.clientX,py:e.clientY,x:state.x,y:state.y};});
  stage?.addEventListener('pointermove',e=>{if(!drag)return;const r=stage.getBoundingClientRect();state.x=clamp(drag.x-(e.clientX-drag.px)/r.width*100,0,100);state.y=clamp(drag.y-(e.clientY-drag.py)/r.height*100,0,100);renderEditor();});
  stage?.addEventListener('pointerup',()=>drag=null);stage?.addEventListener('pointercancel',()=>drag=null);

  syncTags();updateAll();
})();