<style>
:root{
  --green:#92ff22;
  --bg:#050706;
  --panel:#0d110e;
  --panel-2:#111611;
  --white:#f6f3ea;
  --muted:rgba(246,243,234,.66);
  --border:rgba(246,243,234,.13);
  --danger:#ff8f8f;
}
*{box-sizing:border-box}
body{
  margin:0;
  background:var(--bg);
  color:var(--white);
  font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
  font-weight:400;
}
a{color:inherit;text-decoration:none}
.topbar{
  min-height:70px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:18px;
  padding:0 28px;
  border-bottom:1px solid var(--border);
  background:rgba(5,7,6,.97);
  position:sticky;
  top:0;
  z-index:20;
}
.brand{font-weight:600}
.brand span{color:var(--green)}
.top-actions{display:flex;align-items:center;gap:10px}
.top-actions small{color:var(--muted)}
.btn,button.btn{
  min-height:42px;
  padding:0 15px;
  border:1px solid var(--border);
  border-radius:11px;
  background:var(--panel);
  color:var(--white);
  font:inherit;
  font-size:13px;
  font-weight:500;
  display:inline-flex;
  align-items:center;
  justify-content:center;
  gap:8px;
  cursor:pointer;
}
.btn:hover{border-color:rgba(146,255,34,.35)}
.btn-primary{
  border-color:var(--green);
  background:var(--green);
  color:#071006;
}
.btn-danger{
  border-color:rgba(255,143,143,.3);
  color:var(--danger);
}
.btn-ghost{background:transparent}
.container{
  width:min(1240px,calc(100% - 32px));
  margin:0 auto;
  padding:34px 0 70px;
}
.page-head{
  display:flex;
  justify-content:space-between;
  gap:20px;
  align-items:end;
  margin-bottom:26px;
}
.eyebrow{
  color:var(--green);
  font-size:11px;
  font-weight:600;
  letter-spacing:.12em;
  text-transform:uppercase;
}
h1{
  margin:7px 0 0;
  font-size:clamp(30px,5vw,48px);
  line-height:1;
  font-weight:600;
}
h2,h3,strong{font-weight:600}
.muted{color:var(--muted)}
.notice,.error-box{
  margin-bottom:20px;
  padding:13px 15px;
  border-radius:12px;
  font-size:13px;
  line-height:1.55;
}
.notice{
  border:1px solid rgba(146,255,34,.22);
  background:rgba(146,255,34,.07);
  color:#caff9a;
}
.error-box{
  border:1px solid rgba(255,102,102,.28);
  background:rgba(255,102,102,.07);
  color:#ffb4b4;
}
.card{
  border:1px solid var(--border);
  border-radius:18px;
  background:var(--panel);
  overflow:hidden;
}
.form-card{padding:22px}
.form-layout{
  display:grid;
  grid-template-columns:minmax(0,1.35fr) minmax(320px,.65fr);
  gap:22px;
  align-items:start;
}
.form-grid{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:18px;
}
.field{display:grid;gap:7px}
.field-full{grid-column:1/-1}
.field label{
  font-size:13px;
  font-weight:500;
}
.field .help{
  margin:0;
  color:var(--muted);
  font-size:11px;
  line-height:1.5;
}
input,textarea,select{
  width:100%;
  border:1px solid var(--border);
  border-radius:11px;
  background:#080b09;
  color:var(--white);
  outline:none;
  font:inherit;
  font-weight:400;
}
input,select{min-height:46px;padding:0 13px}
textarea{
  min-height:140px;
  padding:12px 13px;
  resize:vertical;
  line-height:1.55;
}
input:focus,textarea:focus,select:focus{
  border-color:rgba(146,255,34,.55);
  box-shadow:0 0 0 3px rgba(146,255,34,.07);
}
.checkbox{
  min-height:46px;
  display:flex;
  align-items:center;
  gap:10px;
  padding:0 13px;
  border:1px solid var(--border);
  border-radius:11px;
  background:#080b09;
  font-weight:400;
}
.checkbox input{width:18px;min-height:auto;height:18px}
.form-actions{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  margin-top:22px;
}
.media-title{
  display:flex;
  align-items:end;
  justify-content:space-between;
  gap:12px;
  margin-bottom:10px;
}
.media-title p{
  margin:0;
  color:var(--muted);
  font-size:11px;
}
.upload-grid{
  display:grid;
  grid-template-columns:repeat(4,minmax(0,1fr));
  gap:12px;
}
.media-slot{
  position:relative;
  min-height:185px;
  aspect-ratio:4/3;
  border:1px dashed rgba(246,243,234,.22);
  border-radius:14px;
  overflow:hidden;
  background:#080b09;
}
.media-slot.has-media{border-style:solid}
.media-slot input[type=file]{
  position:absolute;
  inset:0;
  opacity:0;
  cursor:pointer;
  z-index:3;
}
.slot-empty{
  position:absolute;
  inset:0;
  display:grid;
  place-items:center;
  text-align:center;
  padding:16px;
  color:var(--muted);
  font-size:12px;
  line-height:1.45;
}
.slot-empty strong{
  display:block;
  color:var(--white);
  font-size:14px;
  font-weight:500;
  margin-bottom:5px;
}
.slot-preview{
  position:absolute;
  inset:0;
  overflow:hidden;
  background:#020302;
}
.slot-preview img,.slot-preview video,
.preview-media img,.preview-media video,
.crop-stage img,.crop-stage video,
.table-media img,.table-media video{
  width:100%;
  height:100%;
  display:block;
  object-fit:var(--fit,cover);
  object-position:var(--crop-x,50%) var(--crop-y,50%);
  transform:rotate(var(--rotation,0deg)) scale(var(--zoom,1));
  transform-origin:center;
}
.slot-preview video,.preview-media video,.crop-stage video,.table-media video{
  background:#000;
}
.slot-actions{
  position:absolute;
  left:8px;
  right:8px;
  bottom:8px;
  display:flex;
  gap:6px;
  z-index:6;
}
.slot-actions button{
  flex:1;
  min-height:34px;
  border:1px solid rgba(255,255,255,.16);
  border-radius:9px;
  background:rgba(5,7,6,.88);
  color:#fff;
  font-size:11px;
  font-weight:500;
  cursor:pointer;
}
.primary-wrap,.delete-wrap{
  position:absolute;
  top:8px;
  z-index:7;
  background:rgba(5,7,6,.86);
  padding:6px 8px;
  border-radius:8px;
  font-size:11px;
  font-weight:400;
}
.primary-wrap{left:8px}
.delete-wrap{right:8px}
.primary-wrap input,.delete-wrap input{
  width:15px;
  height:15px;
  min-height:auto;
}
.preview-panel{
  position:sticky;
  top:92px;
  display:grid;
  gap:16px;
}
.preview-box{
  border:1px solid var(--border);
  border-radius:18px;
  padding:16px;
  background:var(--panel);
}
.preview-box h3{
  margin:0 0 6px;
  font-size:14px;
  font-weight:500;
}
.preview-box>p{
  margin:0 0 14px;
  color:var(--muted);
  font-size:11px;
}
.preview-small{
  overflow:hidden;
  border-radius:16px;
  background:#f1eee6;
  color:#111;
}
.preview-small .preview-media{aspect-ratio:4/3}
.preview-large{
  overflow:hidden;
  border-radius:16px;
  background:#080a08;
  border:1px solid var(--border);
}
.preview-large .preview-media{
  aspect-ratio:16/10;
  background:#000;
}
.preview-media{
  overflow:hidden;
  position:relative;
}
.preview-placeholder{
  height:100%;
  display:grid;
  place-items:center;
  text-align:center;
  padding:14px;
  color:rgba(255,255,255,.48);
  font-size:12px;
}
.preview-body{padding:15px}
.preview-kicker{
  font-size:10px;
  font-weight:500;
  text-transform:uppercase;
  letter-spacing:.06em;
  opacity:.58;
}
.preview-title{
  margin:5px 0 14px;
  font-size:20px;
  line-height:1.08;
  font-weight:600;
}
.preview-link{
  font-size:11px;
  font-weight:500;
}
.preview-large-copy{
  padding:14px 15px 16px;
}
.preview-large-copy .preview-title{
  color:var(--white);
  margin:4px 0 7px;
}
.preview-large-copy p{
  margin:0;
  color:var(--muted);
  font-size:12px;
  line-height:1.5;
}
.crop-modal{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.8);
  display:none;
  place-items:center;
  padding:18px;
  z-index:100;
}
.crop-modal.open{display:grid}
.crop-box{
  width:min(800px,100%);
  border:1px solid var(--border);
  border-radius:20px;
  background:#0b0f0c;
  padding:18px;
}
.crop-head{
  display:flex;
  justify-content:space-between;
  gap:12px;
  align-items:center;
  margin-bottom:14px;
}
.crop-head h3{
  margin:0;
  font-weight:500;
}
.crop-stage{
  width:min(600px,100%);
  aspect-ratio:4/3;
  margin:0 auto;
  background:#020302;
  overflow:hidden;
  border-radius:14px;
  position:relative;
  touch-action:none;
  cursor:grab;
}
.crop-stage:active{cursor:grabbing}
.crop-stage img,.crop-stage video{
  pointer-events:none;
  user-select:none;
}
.crop-tip{
  text-align:center;
  color:var(--muted);
  font-size:11px;
  margin:10px 0 0;
}
.crop-controls{
  display:grid;
  gap:12px;
  margin-top:16px;
}
.fit-switch{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:8px;
}
.fit-switch button,.move-grid button,.rotate-row button{
  min-height:40px;
  border:1px solid var(--border);
  border-radius:10px;
  background:#080b09;
  color:var(--white);
  font:inherit;
  font-weight:400;
  cursor:pointer;
}
.fit-switch button.active{
  border-color:var(--green);
  color:var(--green);
}
.move-grid{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  gap:7px;
  max-width:300px;
  margin:0 auto;
}
.move-grid .blank{visibility:hidden}
.rotate-row{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:8px;
}
.zoom-row{
  display:grid;
  grid-template-columns:auto 1fr auto;
  gap:10px;
  align-items:center;
}
.zoom-row input{min-height:auto}
.crop-foot{
  display:flex;
  justify-content:flex-end;
  gap:8px;
  margin-top:16px;
}
.toolbar{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:14px;
  margin-bottom:16px;
  flex-wrap:wrap;
}
.toolbar form{display:flex;gap:8px;flex-wrap:wrap}
.search{width:min(320px,100%)}
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:820px}
th,td{
  padding:14px 16px;
  border-bottom:1px solid var(--border);
  text-align:left;
  vertical-align:middle;
}
th{
  color:rgba(246,243,234,.5);
  font-size:11px;
  font-weight:500;
  text-transform:uppercase;
  letter-spacing:.06em;
}
td{font-size:13px}
.table-media{
  width:74px;
  aspect-ratio:4/3;
  overflow:hidden;
  border-radius:9px;
  background:#000;
}
.no-thumb{
  width:74px;
  aspect-ratio:4/3;
  display:grid;
  place-items:center;
  border-radius:9px;
  background:#070907;
  color:rgba(246,243,234,.28);
  font-size:11px;
  text-align:center;
}
.title-cell strong{
  display:block;
  margin-bottom:3px;
  font-size:14px;
  font-weight:500;
}
.title-cell span{color:var(--muted);font-size:12px}
.badge{
  display:inline-flex;
  align-items:center;
  min-height:28px;
  padding:0 9px;
  border:1px solid var(--border);
  border-radius:999px;
  color:var(--muted);
  font-size:11px;
  font-weight:500;
}
.badge-green{
  border-color:rgba(146,255,34,.25);
  background:rgba(146,255,34,.07);
  color:#bfff7b;
}
.row-actions{display:flex;gap:7px;flex-wrap:wrap}
.row-actions .btn{min-height:35px;padding:0 10px;font-size:12px}
.empty{padding:54px 20px;text-align:center;color:var(--muted)}
.danger-zone{
  margin-top:24px;
  padding:18px;
  border:1px solid rgba(255,102,102,.2);
  border-radius:16px;
  background:rgba(255,102,102,.035);
}
@media(max-width:980px){
  .form-layout{grid-template-columns:1fr}
  .preview-panel{position:static}
  .upload-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(max-width:700px){
  .topbar{padding:0 16px}
  .top-actions small{display:none}
  .page-head{align-items:stretch;flex-direction:column}
  .form-grid{grid-template-columns:1fr}
  .field-full{grid-column:auto}
}
@media(max-width:480px){
  .upload-grid{grid-template-columns:1fr}
}
</style>