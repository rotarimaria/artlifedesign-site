<style>
:root{--green:#92ff22;--bg:#050706;--panel:#0d110e;--white:#f5f2e9;--muted:rgba(245,242,233,.64);--border:rgba(245,242,233,.13);--danger:#ff9090}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--white);font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;font-weight:400}
a{color:inherit;text-decoration:none}.topbar{min-height:70px;display:flex;align-items:center;justify-content:space-between;gap:18px;padding:0 28px;border-bottom:1px solid var(--border);background:rgba(5,7,6,.97);position:sticky;top:0;z-index:20}
.brand{font-weight:600}.brand span{color:var(--green)}.top-actions{display:flex;align-items:center;gap:10px}.top-actions small{color:var(--muted)}
.btn,button.btn{min-height:42px;padding:0 15px;border:1px solid var(--border);border-radius:11px;background:var(--panel);color:var(--white);font:inherit;font-size:13px;font-weight:500;display:inline-flex;align-items:center;justify-content:center;gap:8px;cursor:pointer}
.btn:hover{border-color:rgba(146,255,34,.35)}.btn-primary{border-color:var(--green);background:var(--green);color:#071006}.btn-danger{border-color:rgba(255,144,144,.3);color:var(--danger)}.btn-ghost{background:transparent}
.container{width:min(1320px,calc(100% - 32px));margin:0 auto;padding:34px 0 70px}.page-head{display:flex;justify-content:space-between;gap:20px;align-items:end;margin-bottom:26px}.eyebrow{color:var(--green);font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase}
h1{margin:7px 0 0;font-size:clamp(30px,5vw,48px);line-height:1;font-weight:600}h2,h3,strong{font-weight:600}.muted{color:var(--muted)}
.notice,.error-box{margin-bottom:20px;padding:13px 15px;border-radius:12px;font-size:13px;line-height:1.55}.notice{border:1px solid rgba(146,255,34,.22);background:rgba(146,255,34,.07);color:#caff9a}.error-box{border:1px solid rgba(255,102,102,.28);background:rgba(255,102,102,.07);color:#ffb4b4}
.card{border:1px solid var(--border);border-radius:18px;background:var(--panel);overflow:hidden}.form-card{padding:22px}.form-layout{display:grid;grid-template-columns:minmax(0,1.12fr) minmax(460px,.88fr);gap:22px;align-items:start}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.field{display:grid;gap:7px}.field-full{grid-column:1/-1}.field label{font-size:13px;font-weight:500}.field .help{margin:0;color:var(--muted);font-size:11px;line-height:1.5}
input,textarea,select{width:100%;border:1px solid var(--border);border-radius:11px;background:#080b09;color:var(--white);outline:none;font:inherit;font-weight:400}input,select{min-height:46px;padding:0 13px}textarea{min-height:140px;padding:12px 13px;resize:vertical;line-height:1.55}
input:focus,textarea:focus,select:focus{border-color:rgba(146,255,34,.55);box-shadow:0 0 0 3px rgba(146,255,34,.07)}
.checkbox{min-height:46px;display:flex;align-items:center;gap:10px;padding:0 13px;border:1px solid var(--border);border-radius:11px;background:#080b09}.checkbox input{width:18px;height:18px;min-height:auto}.form-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px}

/* TAGS */
.tag-editor{display:grid;gap:10px;padding:12px;border:1px solid var(--border);border-radius:12px;background:#080b09}.tag-list{display:flex;flex-wrap:wrap;gap:8px;min-height:32px}.tag-chip{display:inline-flex;align-items:center;gap:7px;min-height:30px;padding:0 10px;border:1px solid rgba(146,255,34,.24);border-radius:999px;background:rgba(146,255,34,.06);color:#d7ffb2;font-size:12px}.tag-chip button{width:18px;height:18px;padding:0;border:0;background:transparent;color:inherit;cursor:pointer}
.tag-add-row{display:grid;grid-template-columns:1fr auto;gap:8px}.tag-add-row input{min-width:0}.tag-count{font-size:11px;color:var(--muted)}

/* MEDIA */
.media-title{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:12px}.media-title p{margin:3px 0 0;color:var(--muted);font-size:11px;line-height:1.45}.upload-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.media-slot{min-width:0;border:1px solid var(--border);border-radius:16px;overflow:hidden;background:#080b09;display:grid;grid-template-columns:1fr 1fr}.media-slot:not(.has-media){position:relative;min-height:250px;border-style:dashed;display:block}.media-slot:not(.has-media) input[type=file]{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer;z-index:5}
.slot-empty{min-height:250px;display:grid;place-items:center;text-align:center;padding:20px;color:var(--muted);font-size:13px;line-height:1.5}.slot-empty strong{display:block;margin-bottom:6px;color:var(--white);font-size:15px;font-weight:500}
.slot-preview{grid-column:1/-1;aspect-ratio:16/10;overflow:hidden;background:#020302;position:relative}.slot-preview img,.slot-preview video,.preview-media img,.preview-media video,.crop-stage img,.crop-stage video{width:100%;height:100%;display:block;object-fit:var(--fit,cover);object-position:var(--crop-x,50%) var(--crop-y,50%);transform:rotate(var(--rotation,0deg)) scale(var(--zoom,1));transform-origin:center}
.primary-wrap,.delete-wrap{display:flex;align-items:center;gap:7px;min-height:44px;padding:0 12px;border-top:1px solid var(--border);font-size:12px;color:var(--muted)}.delete-wrap{justify-content:flex-end}.primary-wrap input,.delete-wrap input{width:16px;height:16px;min-height:auto;margin:0}.primary-wrap:has(input:checked){color:var(--green)}
.slot-actions{grid-column:1/-1;display:flex;padding:10px;border-top:1px solid var(--border);background:#0b100c}.slot-actions button{width:100%;min-height:40px;border:1px solid rgba(245,242,233,.14);border-radius:10px;background:#080b09;color:var(--white);font:inherit;font-size:12px;font-weight:500;cursor:pointer}.slot-actions button:hover{border-color:rgba(146,255,34,.35);color:var(--green)}

/* PREVIEW */
.preview-panel{position:sticky;top:92px;display:grid;gap:16px}.preview-box{border:1px solid var(--border);border-radius:18px;padding:16px;background:var(--panel)}.preview-box h3{margin:0 0 6px;font-size:14px;font-weight:500}.preview-box>p{margin:0 0 14px;color:var(--muted);font-size:11px}
.preview-small{overflow:hidden;border-radius:16px;background:#f1eee6;color:#111}.preview-small .preview-media{aspect-ratio:4/3}.preview-media{overflow:hidden;position:relative;background:#000}.preview-placeholder{height:100%;display:grid;place-items:center;text-align:center;padding:14px;color:rgba(255,255,255,.48);font-size:12px}.preview-body{padding:15px}.preview-kicker{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;opacity:.58}.preview-title{margin:5px 0 14px;font-size:20px;line-height:1.08;font-weight:600}.preview-link{font-size:11px;font-weight:500}

/* Modal preview like website */
.site-modal-preview{display:grid;grid-template-columns:minmax(0,1.7fr) minmax(280px,.7fr);min-height:520px;overflow:hidden;border:1px solid rgba(245,242,233,.12);border-radius:16px;background:#020402}
.site-modal-left{display:grid;grid-template-rows:minmax(0,1fr) auto;min-width:0;background:#000}.site-modal-main{position:relative;min-height:410px;overflow:hidden;display:grid;place-items:center}.site-modal-main img,.site-modal-main video{width:100%;height:100%;object-fit:contain;background:#000}.site-modal-thumbs{min-height:86px;display:flex;gap:9px;align-items:center;padding:10px 12px 12px;background:#020302;overflow-x:auto}
.site-thumb{flex:0 0 78px;height:60px;border:1px solid rgba(255,255,255,.12);border-radius:8px;overflow:hidden;background:#090b09;cursor:pointer;padding:0}.site-thumb.active{border-color:var(--green)}.site-thumb img,.site-thumb video{width:100%;height:100%;object-fit:cover;display:block}
.site-modal-side{padding:28px 25px 22px;display:flex;flex-direction:column;min-width:0;background:#050805}.site-modal-category{color:var(--green);font-size:13px;font-weight:600;margin-bottom:20px}.site-modal-title{margin:0 0 20px;font-size:clamp(30px,3vw,42px);line-height:1.04;font-weight:600}.site-modal-description{margin:0 0 20px;color:rgba(245,242,233,.78);line-height:1.6;font-size:15px}.site-modal-tags{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:22px}.site-modal-tag{padding:5px 9px;border:1px solid rgba(146,255,34,.22);border-radius:999px;color:rgba(245,242,233,.72);background:rgba(146,255,34,.035);font-size:11px}.site-modal-cta{margin-top:auto;padding-top:20px;border-top:1px solid rgba(245,242,233,.12);color:var(--green);font-size:14px;font-weight:600;line-height:1.35}

/* EDITOR: free drag + sliders */
.crop-modal{position:fixed;inset:0;background:rgba(0,0,0,.82);display:none;place-items:center;padding:18px;z-index:100}.crop-modal.open{display:grid}.crop-box{width:min(780px,100%);border:1px solid var(--border);border-radius:20px;background:#0b0f0c;padding:18px}.crop-head{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:14px}.crop-head h3{margin:0;font-weight:500}
.crop-stage{width:min(600px,100%);aspect-ratio:4/3;margin:0 auto;background:#020302;overflow:hidden;border-radius:14px;position:relative;touch-action:none;cursor:grab}.crop-stage:active{cursor:grabbing}.crop-stage img,.crop-stage video{pointer-events:none;user-select:none}.crop-tip{text-align:center;color:var(--muted);font-size:11px;margin:10px 0 0}.crop-controls{display:grid;gap:12px;margin-top:16px}
.fit-switch{display:grid;grid-template-columns:1fr 1fr;gap:8px}.fit-switch button{min-height:40px;border:1px solid var(--border);border-radius:10px;background:#080b09;color:var(--white);font:inherit;font-weight:400;cursor:pointer}.fit-switch button.active{border-color:var(--green);color:var(--green)}
.slider-row{display:grid;grid-template-columns:80px 1fr 70px;gap:10px;align-items:center}.slider-row input{min-height:auto}.crop-foot{display:flex;justify-content:space-between;gap:8px;margin-top:16px}
.danger-zone{margin-top:24px;padding:18px;border:1px solid rgba(255,102,102,.2);border-radius:16px;background:rgba(255,102,102,.035)}

@media(max-width:1100px){.form-layout{grid-template-columns:1fr}.preview-panel{position:static}}
@media(max-width:850px){.site-modal-preview{grid-template-columns:1fr}.site-modal-side{min-height:340px}.upload-grid{grid-template-columns:1fr}}
@media(max-width:700px){.topbar{padding:0 16px}.page-head{align-items:stretch;flex-direction:column}.form-grid{grid-template-columns:1fr}.field-full{grid-column:auto}}
@media(max-width:480px){.tag-add-row{grid-template-columns:1fr}}

/* FIX: inputul de upload nu trebuie să reapară după selectarea fișierului */
.media-slot.has-media > input[type="file"]{
  position:absolute!important;
  width:1px!important;
  height:1px!important;
  opacity:0!important;
  pointer-events:none!important;
  overflow:hidden!important;
}

/* Controale pentru media nouă: identice vizual cu cele salvate */
.new-delete-wrap button{
  border:0;
  padding:0;
  background:transparent;
  color:var(--muted);
  font:inherit;
  font-size:12px;
  cursor:pointer;
}
.new-delete-wrap button:hover{color:var(--danger)}
.new-primary-wrap,.new-delete-wrap{border-top:1px solid var(--border)}


/* FIX XY DRAG:
   folosim translație reală, astfel media se poate muta vizibil
   și stânga/dreapta, și sus/jos, indiferent de raportul imaginii. */
.slot-preview img,
.slot-preview video,
.preview-media img,
.preview-media video,
.crop-stage img,
.crop-stage video{
  object-position:50% 50% !important;
  transform:
    translate(
      calc(50% - var(--crop-x,50%)),
      calc(50% - var(--crop-y,50%))
    )
    rotate(var(--rotation,0deg))
    scale(var(--zoom,1)) !important;
}

/* În modalul mare real păstrăm media completă, fără crop-ul cardului. */
.site-modal-main img,
.site-modal-main video{
  transform:none !important;
  object-position:center center !important;
}

</style>