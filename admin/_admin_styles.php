<style>
:root{
    --green:#92ff22;--bg:#050706;--panel:#0d110e;--white:#f7f4eb;
    --muted:rgba(247,244,235,.62);--border:rgba(247,244,235,.12);
}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--white);font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
a{color:inherit;text-decoration:none}
.topbar{min-height:70px;display:flex;align-items:center;justify-content:space-between;gap:18px;padding:0 28px;border-bottom:1px solid var(--border);background:rgba(5,7,6,.97);position:sticky;top:0;z-index:20}
.brand{font-weight:900}.brand span{color:var(--green)}
.top-actions{display:flex;align-items:center;gap:10px}.top-actions small{color:var(--muted)}
.btn,button.btn{min-height:42px;padding:0 15px;border:1px solid var(--border);border-radius:11px;background:var(--panel);color:var(--white);font:inherit;font-size:13px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;gap:8px;cursor:pointer}
.btn:hover{border-color:rgba(146,255,34,.35)}.btn-primary{border-color:var(--green);background:var(--green);color:#071006}.btn-danger{border-color:rgba(255,102,102,.3);color:#ff9a9a}.btn-ghost{background:transparent}
.container{width:min(1220px,calc(100% - 32px));margin:0 auto;padding:34px 0 70px}
.page-head{display:flex;justify-content:space-between;gap:20px;align-items:end;margin-bottom:26px}
.eyebrow{color:var(--green);font-size:11px;font-weight:900;letter-spacing:.14em;text-transform:uppercase}
h1{margin:7px 0 0;font-size:clamp(30px,5vw,48px);line-height:1}.muted{color:var(--muted)}
.notice,.error-box{margin-bottom:20px;padding:13px 15px;border-radius:12px;font-size:13px;line-height:1.55}
.notice{border:1px solid rgba(146,255,34,.22);background:rgba(146,255,34,.07);color:#caff9a}
.error-box{border:1px solid rgba(255,102,102,.28);background:rgba(255,102,102,.07);color:#ffb4b4}
.toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:16px;flex-wrap:wrap}.toolbar form{display:flex;gap:8px;flex-wrap:wrap}
input,textarea,select{width:100%;border:1px solid var(--border);border-radius:11px;background:#080b09;color:var(--white);outline:none;font:inherit}
input,select{min-height:46px;padding:0 13px}textarea{min-height:140px;padding:12px 13px;resize:vertical;line-height:1.55}
input:focus,textarea:focus,select:focus{border-color:rgba(146,255,34,.55);box-shadow:0 0 0 3px rgba(146,255,34,.07)}
.search{width:min(320px,100%)}
.card{border:1px solid var(--border);border-radius:18px;background:var(--panel);overflow:hidden}.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:850px}th,td{padding:14px 16px;border-bottom:1px solid var(--border);text-align:left;vertical-align:middle}
th{color:rgba(247,244,235,.5);font-size:11px;text-transform:uppercase;letter-spacing:.08em}td{font-size:13px}tr:last-child td{border-bottom:0}
.thumb{width:74px;aspect-ratio:4/3;object-fit:cover;border-radius:9px;background:#000;display:block}.no-thumb{width:74px;aspect-ratio:4/3;display:grid;place-items:center;border-radius:9px;background:#070907;color:rgba(247,244,235,.28);font-size:11px;text-align:center}
.title-cell strong{display:block;margin-bottom:3px;font-size:14px}.title-cell span{color:var(--muted);font-size:12px}
.badge{display:inline-flex;align-items:center;min-height:28px;padding:0 9px;border:1px solid var(--border);border-radius:999px;color:var(--muted);font-size:11px;font-weight:800}.badge-green{border-color:rgba(146,255,34,.25);background:rgba(146,255,34,.07);color:#bfff7b}
.row-actions{display:flex;gap:7px;flex-wrap:wrap}.row-actions .btn{min-height:35px;padding:0 10px;font-size:12px}
.form-card{padding:22px}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.field{display:grid;gap:7px}.field-full{grid-column:1/-1}.field label{font-size:13px;font-weight:800}.field .help{margin:0;color:var(--muted);font-size:11px;line-height:1.5}
.checkbox{min-height:46px;display:flex;align-items:center;gap:10px;padding:0 13px;border:1px solid var(--border);border-radius:11px;background:#080b09}.checkbox input{width:18px;min-height:auto;height:18px}
.form-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px}
.image-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:12px}.image-item{padding:10px;border:1px solid var(--border);border-radius:14px;background:#080b09}.image-item img{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:10px;background:#000}.image-meta{display:grid;gap:8px;margin-top:9px}.image-meta label{display:flex;align-items:center;gap:7px;color:var(--muted);font-size:11px}.image-meta input{width:16px;height:16px;min-height:auto}
.empty{padding:54px 20px;text-align:center;color:var(--muted)}.danger-zone{margin-top:24px;padding:18px;border:1px solid rgba(255,102,102,.2);border-radius:16px;background:rgba(255,102,102,.035)}.danger-zone h3{margin:0 0 7px;color:#ff9a9a}.danger-zone p{margin:0 0 14px;color:var(--muted);font-size:13px;line-height:1.5}
@media(max-width:800px){.topbar{padding:0 16px}.top-actions small{display:none}.page-head{align-items:stretch;flex-direction:column}.form-grid{grid-template-columns:1fr}.field-full{grid-column:auto}.image-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:500px){.image-grid{grid-template-columns:1fr}}
</style>