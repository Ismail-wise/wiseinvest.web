<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'includes/header.php';

$brokers = [
    [
        "key"     => "ibkr",
        "logo"    => "ibkr.png",
        "name"    => "Interactive Brokers",
        "type"    => "Global Multi-Asset",
        "filter"  => "stocks",
        "rating"  => 9.4,
        "min_dep" => "$0",
        "fee"     => "$0.005 / share",
        "markets" => "135+",
        "reg"     => "SEC · FCA · ASIC",
        "best_for"=> "Professional Traders",
        "badge"   => "Best Overall",
        "badge_cls"=> "bc-gold",
        "tags"    => ["Professional","Low Fees","135+ Markets"],
        "desc"    => "Interactive Brokers သည် ကမ္ဘာ့ stock markets ၁၃၅+ ကို access လုပ်နိုင်သော professional brokerage platform ဖြစ်သည်။ Fee နည်းပါးပြီး global investors များအတွက် အကောင်းဆုံးဖြစ်သည်။",
    ],
    [
        "key"     => "tiger",
        "logo"    => "tiger.png",
        "name"    => "Tiger Brokers",
        "type"    => "US · HK · SG Markets",
        "filter"  => "stocks",
        "rating"  => 8.9,
        "min_dep" => "$0",
        "fee"     => "$0.005 / share",
        "markets" => "US · HK · SG",
        "reg"     => "ASIC · MAS · SFC",
        "best_for"=> "Asian Market Access",
        "badge"   => "Best for Asia",
        "badge_cls"=> "bc-indigo",
        "tags"    => ["Asia Focused","Beginner Friendly","Mobile App"],
        "desc"    => "Tiger Brokers သည် Asia region တွင် လူကြိုက်များပြီး US နှင့် Hong Kong markets ကို trading လုပ်နိုင်သည်။ Myanmar users အတွက် account ဖွင့်နိုင်သော broker ဖြစ်သည်။",
    ],
    [
        "key"     => "moomoo",
        "logo"    => "moomoo.png",
        "name"    => "Moomoo",
        "type"    => "Stocks · Options · ETFs",
        "filter"  => "stocks",
        "rating"  => 8.7,
        "min_dep" => "$0",
        "fee"     => "$0 commissions",
        "markets" => "US · SG · AU",
        "reg"     => "SEC · MAS · ASIC",
        "best_for"=> "Research & Analysis",
        "badge"   => "Best Research",
        "badge_cls"=> "bc-sky",
        "tags"    => ["Advanced Charts","Research Tools","Commission Free"],
        "desc"    => "Moomoo သည် advanced charting tools များပါဝင်သော trading platform ဖြစ်ပြီး active traders များအတွက် အသုံးဝင်ဆုံး research features ပေးထားသည်။",
    ],
    [
        "key"     => "etoro",
        "logo"    => "etoro.svg",
        "name"    => "eToro",
        "type"    => "Stocks · Crypto · ETFs",
        "filter"  => "social",
        "rating"  => 8.4,
        "min_dep" => "$50",
        "fee"     => "Spread based",
        "markets" => "3,000+ assets",
        "reg"     => "FCA · CySEC · ASIC",
        "best_for"=> "Social & Copy Trading",
        "badge"   => "Best Social",
        "badge_cls"=> "bc-green",
        "tags"    => ["Copy Trading","Social Platform","Crypto + Stocks"],
        "desc"    => "eToro သည် social trading platform ဖြစ်ပြီး beginner investors များအတွက် experienced traders များကို copy trading လုပ်နိုင်သော feature ပါဝင်သည်။",
    ],
    [
        "key"     => "schwab",
        "logo"    => "schwab.svg",
        "name"    => "Charles Schwab",
        "type"    => "US Stocks · ETFs · Bonds",
        "filter"  => "stocks",
        "rating"  => 8.2,
        "min_dep" => "$0",
        "fee"     => "$0 commissions",
        "markets" => "US Markets",
        "reg"     => "SEC · FINRA",
        "best_for"=> "US Investors",
        "badge"   => "Best US Broker",
        "badge_cls"=> "bc-orange",
        "tags"    => ["Commission Free","US Focused","Long-term"],
        "desc"    => "Charles Schwab သည် US တွင် ယုံကြည်စိတ်ချရသော brokerage company ဖြစ်ပြီး long-term investors များနှင့် US stocks ဝင်ယူလိုသူများအတွက် အထူးသင့်တော်သည်။",
    ],
    [
        "key"     => "longbridge",
        "logo"    => "longbridge.webp",
        "name"    => "Longbridge",
        "type"    => "HK · US · SG Markets",
        "filter"  => "stocks",
        "rating"  => 8.0,
        "min_dep" => "$0",
        "fee"     => "Low fees",
        "markets" => "HK · US · SG",
        "reg"     => "SFC · MAS",
        "best_for"=> "HK Market Access",
        "badge"   => "Best HK Access",
        "badge_cls"=> "bc-indigo",
        "tags"    => ["HK Focused","Low Fees","Mobile App"],
        "desc"    => "Longbridge သည် Hong Kong နှင့် Singapore markets ကို focus လုပ်သော broker ဖြစ်ပြီး Myanmar users အတွက် account ဖွင့်ရ လွယ်ကူသည်။",
    ],
    [
        "key"     => "xtb",
        "logo"    => "xtb.png",
        "name"    => "XTB",
        "type"    => "CFDs · Forex · Stocks",
        "filter"  => "forex",
        "rating"  => 7.8,
        "min_dep" => "$0",
        "fee"     => "Spread based",
        "markets" => "2,100+ instruments",
        "reg"     => "FCA · CySEC · KNF",
        "best_for"=> "Forex & CFD Trading",
        "badge"   => "Best CFD/Forex",
        "badge_cls"=> "bc-purple",
        "tags"    => ["Forex","CFD Trading","European Regulated"],
        "desc"    => "XTB သည် Europe based broker ဖြစ်ပြီး forex နှင့် CFD trading များအတွက် powerful trading tools များပေးထားသည်။ xStation platform ဖြင့် trading လုပ်နိုင်သည်။",
    ],
    [
        "key"     => "easyequities",
        "logo"    => "easyequities.png",
        "name"    => "EasyEquities",
        "type"    => "Fractional Shares",
        "filter"  => "stocks",
        "rating"  => 7.5,
        "min_dep" => "$0",
        "fee"     => "From $0",
        "markets" => "US · SA Markets",
        "reg"     => "FSP",
        "best_for"=> "Fractional Investing",
        "badge"   => "Best Fractional",
        "badge_cls"=> "bc-green",
        "tags"    => ["Fractional Shares","Low Minimum","Beginner"],
        "desc"    => "EasyEquities သည် fractional shares ဝယ်ယူနိုင်သော platform ဖြစ်ပြီး ငွေနည်းနည်းဖြင့် Amazon, Apple, Tesla ကဲ့သို့သော shares ဝယ်နိုင်သည်။",
    ],
    [
        "key"     => "wise",
        "logo"    => "wise.svg",
        "name"    => "Wise",
        "type"    => "Money Transfer",
        "filter"  => "transfer",
        "rating"  => 7.3,
        "min_dep" => "$0",
        "fee"     => "0.41% avg",
        "markets" => "70+ currencies",
        "reg"     => "FCA · FinCEN",
        "best_for"=> "International Transfers",
        "badge"   => "Best Transfer",
        "badge_cls"=> "bc-teal",
        "tags"    => ["Money Transfer","Low Fees","70+ Currencies"],
        "desc"    => "Wise သည် international money transfer ဝန်ဆောင်မှုဖြစ်ပြီး brokerage account funding အတွက် Myanmar မှ ငွေပို့ရာတွင် အသုံးဝင်ဆုံး platform ဖြစ်သည်။",
    ],
];

$compare_features = [
    ["label"=>"Min. Deposit",     "icon"=>"fas fa-wallet",        "key"=>"min_dep"],
    ["label"=>"Trading Fee",      "icon"=>"fas fa-percent",       "key"=>"fee"],
    ["label"=>"Markets",          "icon"=>"fas fa-globe",         "key"=>"markets"],
    ["label"=>"Regulation",       "icon"=>"fas fa-shield-alt",    "key"=>"reg"],
    ["label"=>"Best For",         "icon"=>"fas fa-star",          "key"=>"best_for"],
];
?>
<style>
/* ══════════════════════════════════════════════════════════════════
   BROKER COMPARISON — v2 Premium Dashboard
   ══════════════════════════════════════════════════════════════════ */

@keyframes bc-fadeUp  { from{opacity:0;transform:translateY(28px)} to{opacity:1;transform:translateY(0)} }
@keyframes bc-fadeIn  { from{opacity:0} to{opacity:1} }
@keyframes bc-shimmer { from{background-position:-300% center} to{background-position:300% center} }
@keyframes bc-orb     { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.12) translate(12px,-10px)} }
@keyframes bc-grid    { from{background-position:0 0} to{background-position:60px 60px} }
@keyframes bc-rise    { 0%{opacity:0;transform:translateY(50px)} 20%{opacity:1} 80%{opacity:.4} 100%{opacity:0;transform:translateY(-160px)} }
@keyframes bc-slide-l { from{opacity:0;transform:translateX(-24px)} to{opacity:1;transform:translateX(0)} }
@keyframes bc-pop     { 0%{opacity:0;transform:scale(.9) translateY(16px)} 70%{transform:scale(1.03)} 100%{opacity:1;transform:scale(1) translateY(0)} }
@keyframes bc-img-in  { from{opacity:0;transform:scale(.97)} to{opacity:1;transform:scale(1)} }
@keyframes bc-pulse   { 0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,.3)} 60%{box-shadow:0 0 0 10px rgba(212,175,55,0)} }

*{box-sizing:border-box;}

/* ── Layout ─────────────────────────────────────────────────── */
.dashboard-wrapper { display:flex; min-height:100vh; background:#020617; }
.bc-main { flex:1; padding:28px 32px 60px; background:#020617; overflow-x:hidden; }
.bc-page { max-width:1320px; margin:0 auto; }

/* ── Shared helpers ──────────────────────────────────────────── */
.bc-gold-text {
    background:linear-gradient(135deg,#d4af37 0%,#facc15 45%,#f59e0b 70%,#d4af37 100%);
    background-size:200% auto; -webkit-background-clip:text;
    -webkit-text-fill-color:transparent; background-clip:text;
    animation:bc-shimmer 4s linear infinite;
}
.bc-reveal     { opacity:0; transform:translateY(22px); }
.bc-reveal.visible { animation:bc-fadeUp .55s ease forwards; }
.bc-reveal-pop { opacity:0; }
.bc-reveal-pop.visible { animation:bc-pop .5s ease forwards; }

/* ── HERO ────────────────────────────────────────────────────── */
.bc-hero {
    position:relative; border-radius:24px; overflow:hidden;
    background:linear-gradient(145deg,#080f1e 0%,#040c18 100%);
    border:1px solid rgba(255,255,255,.08);
    padding:48px 44px 44px; margin-bottom:28px;
}
.bc-hero-grid {
    position:absolute; inset:0; pointer-events:none;
    background-image:
        linear-gradient(rgba(212,175,55,.025) 1px,transparent 1px),
        linear-gradient(90deg,rgba(212,175,55,.025) 1px,transparent 1px);
    background-size:60px 60px; animation:bc-grid 18s linear infinite;
}
.bc-hero-orb-1 { position:absolute; width:500px;height:500px; border-radius:50%; filter:blur(100px); pointer-events:none; background:radial-gradient(circle,rgba(212,175,55,.1) 0%,transparent 70%); top:-180px;right:-80px; animation:bc-orb 10s ease-in-out infinite; }
.bc-hero-orb-2 { position:absolute; width:320px;height:320px; border-radius:50%; filter:blur(80px); pointer-events:none; background:radial-gradient(circle,rgba(99,102,241,.07) 0%,transparent 70%); bottom:-80px;left:-60px; animation:bc-orb 13s ease-in-out infinite reverse; }

.bc-hero-particles { position:absolute; inset:0; pointer-events:none; overflow:hidden; }
.bc-hero-particle  { position:absolute; width:3px;height:3px;border-radius:50%; background:rgba(212,175,55,.5); animation:bc-rise var(--d,9s) var(--dl,0s) ease-in infinite; }

.bc-hero-inner { position:relative; z-index:2; }
.bc-hero-top { display:flex; align-items:flex-start; justify-content:space-between; gap:24px; flex-wrap:wrap; margin-bottom:36px; }

.bc-hero-badge {
    display:inline-flex; align-items:center; gap:7px;
    background:rgba(212,175,55,.09); border:1px solid rgba(212,175,55,.25);
    color:#d4af37; padding:5px 16px; border-radius:99px;
    font-size:.65rem; font-weight:800; letter-spacing:2px; text-transform:uppercase;
    margin-bottom:14px; animation:bc-fadeUp .5s .1s ease both;
}
.bc-hero-badge-dot { width:6px;height:6px;border-radius:50%;background:#d4af37;box-shadow:0 0 6px rgba(212,175,55,.9); }

.bc-hero h1 {
    font-size:clamp(1.9rem,3.5vw,2.8rem); font-weight:900; color:#fff;
    margin:0 0 10px; letter-spacing:-.5px; line-height:1.15;
    animation:bc-fadeUp .55s .15s ease both;
}
.bc-hero-sub {
    color:#475569; font-size:.95rem; line-height:1.7; max-width:580px;
    animation:bc-fadeUp .55s .25s ease both;
}
.bc-hero-recommended {
    display:flex; align-items:center; gap:14px;
    background:rgba(212,175,55,.07); border:1px solid rgba(212,175,55,.18);
    border-radius:16px; padding:14px 20px; flex-shrink:0;
    animation:bc-fadeUp .55s .3s ease both;
}
.bc-hero-rec-logo {
    width:44px; height:44px; border-radius:11px;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.09);
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.bc-hero-rec-logo img { max-width:34px; max-height:26px; object-fit:contain; filter:brightness(1.1); }
.bc-hero-rec-label { font-size:.6rem; font-weight:800; letter-spacing:1.5px; text-transform:uppercase; color:#d4af37; margin-bottom:2px; }
.bc-hero-rec-name  { font-size:.9rem; font-weight:800; color:#f1f5f9; }

/* Hero stats */
.bc-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
.bc-stat {
    background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07);
    border-radius:14px; padding:18px 20px; text-align:center; position:relative;
    animation:bc-fadeUp .55s ease both;
}
.bc-stat::before { content:''; position:absolute; top:0; left:50%; transform:translateX(-50%); width:40%; height:2px; background:linear-gradient(90deg,transparent,#d4af37,transparent); }
.bc-stat strong { display:block; font-size:1.6rem; font-weight:900; color:#d4af37; line-height:1; }
.bc-stat span   { font-size:.63rem; color:#334155; letter-spacing:1.5px; text-transform:uppercase; margin-top:5px; display:block; }

/* ── Section heading ─────────────────────────────────────────── */
.bc-sec-head { margin-bottom:28px; }
.bc-sec-head h2 { font-size:1.4rem; font-weight:900; color:#fff; margin:0 0 4px; }
.bc-sec-head p  { color:#475569; font-size:.88rem; margin:0; }
.bc-sec-line { width:44px; height:3px; border-radius:3px; background:linear-gradient(90deg,#d4af37,#facc15); margin-top:12px; }

/* ── Filter tabs ─────────────────────────────────────────────── */
.bc-filters {
    display:flex; flex-wrap:wrap; gap:8px; margin-bottom:28px;
}
.bc-filter-tab {
    padding:7px 18px; border-radius:99px; font-size:.78rem; font-weight:700; letter-spacing:.3px;
    border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.03); color:#64748b;
    cursor:pointer; transition:background .2s,border-color .2s,color .2s;
    user-select:none;
}
.bc-filter-tab:hover   { border-color:rgba(212,175,55,.3); color:#cbd5e1; }
.bc-filter-tab.active  { background:rgba(212,175,55,.12); border-color:rgba(212,175,55,.4); color:#d4af37; }

/* ── Broker card grid ────────────────────────────────────────── */
.bc-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(300px,1fr));
    gap:20px; margin-bottom:56px;
}

.bc-card {
    position:relative;
    background:linear-gradient(160deg,#0d1829 0%,#080f1e 100%);
    border:1px solid rgba(255,255,255,.08); border-radius:22px;
    overflow:hidden; display:flex; flex-direction:column;
    transition:transform .35s cubic-bezier(.34,1.56,.64,1), border-color .3s, box-shadow .3s;
    opacity:0; transform:translateY(20px);
}
.bc-card.visible { animation:bc-pop .5s ease forwards; }
.bc-card:hover {
    transform:translateY(-8px);
    border-color:rgba(212,175,55,.3);
    box-shadow:0 28px 65px rgba(0,0,0,.6), 0 0 0 1px rgba(212,175,55,.06);
}
.bc-card::before {
    content:''; position:absolute; top:0;left:0;right:0; height:2px;
    background:linear-gradient(90deg,transparent,#d4af37,transparent);
    opacity:0; transition:opacity .3s;
}
.bc-card:hover::before { opacity:1; }

/* Card badge */
.bc-card-badge {
    position:absolute; top:16px; right:16px;
    font-size:.6rem; font-weight:800; letter-spacing:.8px; text-transform:uppercase;
    padding:4px 10px; border-radius:99px;
}
.bc-gold   { background:rgba(212,175,55,.12); border:1px solid rgba(212,175,55,.3); color:#d4af37; }
.bc-indigo { background:rgba(99,102,241,.1);  border:1px solid rgba(99,102,241,.3);  color:#818cf8; }
.bc-sky    { background:rgba(56,189,248,.08); border:1px solid rgba(56,189,248,.3);  color:#38bdf8; }
.bc-green  { background:rgba(34,197,94,.08);  border:1px solid rgba(34,197,94,.3);   color:#4ade80; }
.bc-orange { background:rgba(249,115,22,.08); border:1px solid rgba(249,115,22,.3);  color:#fb923c; }
.bc-purple { background:rgba(168,85,247,.08); border:1px solid rgba(168,85,247,.3);  color:#c084fc; }
.bc-teal   { background:rgba(20,184,166,.08); border:1px solid rgba(20,184,166,.3);  color:#2dd4bf; }

/* Card header */
.bc-card-head {
    display:flex; align-items:center; gap:14px;
    padding:22px 22px 16px;
}
.bc-card-logo {
    width:60px; height:60px; border-radius:15px; flex-shrink:0;
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09);
    display:flex; align-items:center; justify-content:center;
    transition:background .3s,border-color .3s,box-shadow .3s;
}
.bc-card:hover .bc-card-logo { background:rgba(255,255,255,.08); border-color:rgba(212,175,55,.2); box-shadow:0 4px 20px rgba(212,175,55,.1); }
.bc-card-logo img { max-width:44px; max-height:34px; object-fit:contain; filter:brightness(1.1); }
.bc-card-title { min-width:0; }
.bc-card-name  { font-size:1rem; font-weight:800; color:#f1f5f9; margin:0 0 5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.bc-card-rating {
    display:flex; align-items:center; gap:5px;
    font-size:.78rem; font-weight:700; color:#d4af37;
}
.bc-card-rating .bc-stars { color:#d4af37; font-size:.68rem; letter-spacing:1px; }

/* Tags */
.bc-card-tags {
    display:flex; flex-wrap:wrap; gap:6px;
    padding:0 22px 16px;
}
.bc-tag {
    font-size:.62rem; font-weight:700; letter-spacing:.5px; text-transform:uppercase;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07);
    color:#475569; padding:3px 10px; border-radius:6px;
}

/* Mini stats row */
.bc-card-stats {
    display:grid; grid-template-columns:repeat(3,1fr);
    border-top:1px solid rgba(255,255,255,.05);
    border-bottom:1px solid rgba(255,255,255,.05);
}
.bc-card-stat { padding:12px 14px; text-align:center; border-right:1px solid rgba(255,255,255,.05); }
.bc-card-stat:last-child { border-right:none; }
.bc-card-stat-val { font-size:.82rem; font-weight:800; color:#e2e8f0; display:block; }
.bc-card-stat-lbl { font-size:.58rem; color:#334155; letter-spacing:1px; text-transform:uppercase; display:block; margin-top:2px; }

/* Actions */
.bc-card-actions { display:flex; gap:10px; padding:16px 18px 20px; margin-top:auto; }
.bc-card-btn {
    flex:1; padding:10px 12px; border-radius:11px; font-size:.82rem; font-weight:700;
    border:none; cursor:pointer; text-decoration:none;
    display:flex; align-items:center; justify-content:center; gap:7px;
    transition:transform .2s, box-shadow .2s, background .2s; font-family:inherit;
}
.bc-card-btn:hover { transform:translateY(-2px); }
.bc-btn-details { background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.11); color:#cbd5e1; }
.bc-btn-details:hover { background:rgba(255,255,255,.1); border-color:rgba(255,255,255,.2); }
.bc-btn-service { background:linear-gradient(135deg,#d4af37,#facc15); color:#020617; box-shadow:0 3px 14px rgba(212,175,55,.28); }
.bc-btn-service:hover { box-shadow:0 6px 22px rgba(212,175,55,.5); }

/* ── Feature comparison table ────────────────────────────────── */
.bc-compare-section { margin-bottom:56px; }
.bc-compare-wrap {
    overflow-x:auto; border-radius:18px; border:1px solid rgba(255,255,255,.07);
    scrollbar-width:thin; scrollbar-color:rgba(212,175,55,.2) transparent;
}
.bc-compare-wrap::-webkit-scrollbar { height:5px; }
.bc-compare-wrap::-webkit-scrollbar-thumb { background:rgba(212,175,55,.25); border-radius:3px; }

.bc-compare-table { width:100%; border-collapse:collapse; min-width:900px; }
.bc-compare-table thead tr { background:#030b18; }
.bc-compare-table thead th {
    padding:14px 16px; font-size:.68rem; font-weight:800; letter-spacing:1px;
    text-transform:uppercase; color:#334155; text-align:center; white-space:nowrap;
    border-bottom:1px solid rgba(255,255,255,.07);
}
.bc-compare-table thead th:first-child { text-align:left; min-width:140px; }
.bc-compare-table thead .bc-th-broker {
    min-width:130px; padding:12px 14px;
}
.bc-th-broker-inner {
    display:flex; flex-direction:column; align-items:center; gap:6px;
}
.bc-th-logo {
    width:36px; height:36px; border-radius:8px;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07);
    display:flex; align-items:center; justify-content:center;
}
.bc-th-logo img { max-width:28px; max-height:21px; object-fit:contain; filter:brightness(1.1); }
.bc-th-name { font-size:.68rem; font-weight:700; color:#94a3b8; letter-spacing:0; text-transform:none; }

.bc-compare-table tbody tr {
    border-bottom:1px solid rgba(255,255,255,.04);
    transition:background .2s;
}
.bc-compare-table tbody tr:hover { background:rgba(255,255,255,.02); }
.bc-compare-table tbody tr:last-child { border-bottom:none; }

.bc-compare-table tbody td { padding:13px 16px; text-align:center; font-size:.82rem; color:#94a3b8; vertical-align:middle; white-space:nowrap; }
.bc-compare-table tbody td:first-child {
    text-align:left; display:flex; align-items:center; gap:9px;
    font-size:.76rem; font-weight:700; color:#475569; letter-spacing:.5px; text-transform:uppercase;
}
.bc-compare-table tbody td:first-child i { color:rgba(212,175,55,.5); font-size:.8rem; width:16px; }
.bc-compare-table tbody td .bc-val { color:#e2e8f0; font-weight:700; }

/* ── Guide cards ─────────────────────────────────────────────── */
.bc-guides-section { margin-bottom:20px; }
.bc-guides-grid {
    display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px;
}
.bc-guide-card {
    background:rgba(255,255,255,.025); border:1px solid rgba(255,255,255,.07);
    border-radius:16px; padding:22px;
    transition:background .3s, border-color .3s, transform .3s;
    opacity:0; transform:translateY(16px);
}
.bc-guide-card.visible { animation:bc-fadeUp .5s ease forwards; }
.bc-guide-card:hover { background:rgba(255,255,255,.04); border-color:rgba(212,175,55,.2); transform:translateY(-3px); }
.bc-guide-logo {
    width:40px; height:40px; border-radius:10px; margin-bottom:12px;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08);
    display:flex; align-items:center; justify-content:center;
}
.bc-guide-logo img { max-width:30px; max-height:22px; object-fit:contain; filter:brightness(1.1); }
.bc-guide-card h4 { font-size:.92rem; font-weight:800; color:#f1f5f9; margin:0 0 8px; }
.bc-guide-card p  { font-size:.82rem; color:#475569; line-height:1.65; margin:0; }

/* ── MODAL — side-by-side ────────────────────────────────────── */
.bc-modal {
    position:fixed; inset:0; z-index:9999;
    display:none; align-items:center; justify-content:center; padding:16px;
}
.bc-modal.show { display:flex; }
.bc-modal-backdrop {
    position:absolute; inset:0;
    background:rgba(0,0,0,.9); backdrop-filter:blur(16px);
    animation:bc-fadeIn .25s ease;
}
.bc-modal-panel {
    position:relative; z-index:2;
    width:100%; max-width:1120px; height:88vh;
    background:linear-gradient(160deg,#0d1b2e 0%,#060f1c 100%);
    border:1px solid rgba(212,175,55,.18); border-radius:22px;
    display:flex; flex-direction:column; overflow:hidden;
    box-shadow:0 40px 100px rgba(0,0,0,.85);
    transform:scale(.93) translateY(20px); opacity:0;
    transition:transform .35s cubic-bezier(.34,1.56,.64,1), opacity .3s;
}
.bc-modal.show .bc-modal-panel { transform:scale(1) translateY(0); opacity:1; }

.bc-modal-topbar {
    height:2px; flex-shrink:0;
    background:linear-gradient(90deg,transparent,#d4af37,#facc15,#d4af37,transparent);
}
.bc-modal-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:15px 22px; border-bottom:1px solid rgba(255,255,255,.06); flex-shrink:0;
}
.bc-modal-broker { display:flex; align-items:center; gap:12px; }
.bc-modal-b-logo {
    width:44px; height:44px; border-radius:11px;
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09);
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.bc-modal-b-logo img { max-width:34px; max-height:26px; object-fit:contain; filter:brightness(1.2); }
.bc-modal-b-name   { font-size:1.05rem; font-weight:800; color:#fff; margin:0; }
.bc-modal-b-sub    { font-size:.68rem; color:#475569; margin:2px 0 0; }
.bc-modal-header-r { display:flex; align-items:center; gap:10px; }
.bc-modal-counter  {
    font-size:.75rem; font-weight:700; color:#64748b;
    background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07);
    padding:4px 11px; border-radius:8px;
}
.bc-modal-counter span { color:#d4af37; }
.bc-modal-close {
    width:34px; height:34px; border-radius:9px;
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09);
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:#64748b; font-size:.9rem;
    transition:background .2s, color .2s;
}
.bc-modal-close:hover { background:rgba(239,68,68,.12); color:#f87171; border-color:rgba(239,68,68,.2); }

/* Body */
.bc-modal-body { display:flex; flex:1; min-height:0; }

/* Image stage */
.bc-modal-stage {
    flex:1; position:relative; overflow:hidden;
    display:flex; align-items:center; justify-content:center;
    background:#040c18; min-height:0;
}
.bc-modal-img {
    max-width:100%; max-height:100%; width:100%; height:100%;
    object-fit:contain; display:block;
    padding:20px; box-sizing:border-box;
    animation:bc-img-in .3s ease;
}
.bc-modal-arrow {
    position:absolute; top:50%; transform:translateY(-50%);
    width:46px; height:46px; border-radius:50%;
    background:rgba(4,12,24,.88); border:1px solid rgba(255,255,255,.1);
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:#cbd5e1; font-size:.95rem; z-index:3;
    transition:background .2s, border-color .2s, color .2s;
    backdrop-filter:blur(8px);
}
.bc-modal-arrow:hover { background:rgba(212,175,55,.2); border-color:rgba(212,175,55,.4); color:#facc15; }
.bc-modal-prev { left:14px; }
.bc-modal-next { right:14px; }
.bc-modal-arrow.disabled { opacity:.2; pointer-events:none; }
.bc-modal-nodata { text-align:center; padding:60px 24px; color:#475569; }
.bc-modal-nodata i { font-size:2.5rem; color:rgba(212,175,55,.12); display:block; margin-bottom:14px; }
.bc-modal-nodata h4 { color:#475569; margin:0 0 6px; }

/* Sidebar */
.bc-modal-sidebar {
    width:260px; flex-shrink:0;
    border-left:1px solid rgba(255,255,255,.06);
    background:rgba(4,12,24,.5);
    display:flex; flex-direction:column; overflow:hidden;
}
.bc-modal-sidebar-lbl {
    padding:15px 18px 10px; font-size:.62rem; font-weight:800;
    letter-spacing:2px; text-transform:uppercase; color:#334155;
    border-bottom:1px solid rgba(255,255,255,.04); flex-shrink:0;
}
.bc-modal-thumbs {
    flex:1; overflow-y:auto; padding:10px;
    display:flex; flex-direction:column; gap:8px;
    scrollbar-width:thin; scrollbar-color:rgba(212,175,55,.2) transparent;
}
.bc-modal-thumbs::-webkit-scrollbar { width:3px; }
.bc-modal-thumbs::-webkit-scrollbar-thumb { background:rgba(212,175,55,.25); border-radius:2px; }
.bc-modal-thumb {
    border-radius:10px; overflow:hidden; border:2px solid rgba(255,255,255,.07);
    cursor:pointer; flex-shrink:0; transition:border-color .2s,transform .2s,box-shadow .2s;
    aspect-ratio:16/10;
}
.bc-modal-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.bc-modal-thumb:hover { border-color:rgba(212,175,55,.3); transform:scale(1.02); }
.bc-modal-thumb.active { border-color:#d4af37; box-shadow:0 4px 16px rgba(212,175,55,.3); }

.bc-modal-footer {
    padding:12px 16px 16px; border-top:1px solid rgba(255,255,255,.05);
    flex-shrink:0; display:flex; flex-direction:column; gap:9px;
}
.bc-modal-hint { font-size:.65rem; color:#1e293b; display:flex; align-items:center; gap:5px; flex-wrap:wrap; }
.bc-modal-hint kbd {
    background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.09);
    padding:2px 6px; border-radius:4px; font-size:.6rem; color:#334155; font-family:inherit;
}
.bc-modal-cta {
    display:flex; align-items:center; justify-content:center; gap:7px;
    background:linear-gradient(135deg,#d4af37,#facc15); color:#020617;
    font-weight:800; font-size:.82rem; padding:11px 16px; border-radius:10px;
    text-decoration:none; box-shadow:0 4px 14px rgba(212,175,55,.3);
    transition:transform .2s, box-shadow .2s;
}
.bc-modal-cta:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(212,175,55,.5); }

/* ── Rating bar ──────────────────────────────────────────────── */
.bc-rating-bar-wrap { padding:0 22px 14px; }
.bc-rating-track { height:5px;background:rgba(255,255,255,.06);border-radius:99px;overflow:hidden; }
.bc-rating-fill  { height:100%;border-radius:99px;background:linear-gradient(90deg,#d4af37,#facc15);width:0;transition:width .9s cubic-bezier(.4,0,.2,1); }

/* ── Find My Broker Quiz ─────────────────────────────────────── */
.bc-quiz-section { margin-bottom:40px; }
.bc-quiz-panel {
    background:linear-gradient(145deg,#080f1e 0%,#040c18 100%);
    border:1px solid rgba(212,175,55,.15); border-radius:22px; overflow:hidden;
}
.bc-quiz-topbar { height:2px; background:linear-gradient(90deg,transparent,#d4af37,#facc15,#d4af37,transparent); }
.bc-quiz-body { padding:28px 32px 32px; }
.bc-quiz-head { display:flex;align-items:center;gap:14px;margin-bottom:22px; }
.bc-quiz-icon { width:44px;height:44px;border-radius:12px;background:rgba(212,175,55,.1);border:1px solid rgba(212,175,55,.2);display:flex;align-items:center;justify-content:center;color:#d4af37;font-size:1rem;flex-shrink:0; }
.bc-quiz-head-text h3 { font-size:1.05rem;font-weight:900;color:#f1f5f9;margin:0 0 4px; }
.bc-quiz-head-text p  { font-size:.8rem;color:#475569;margin:0; }
.bc-quiz-progress { display:flex;gap:6px;margin-bottom:20px; }
.bc-quiz-dot { flex:1;height:3px;border-radius:99px;background:rgba(255,255,255,.08);transition:background .3s; }
.bc-quiz-dot.done { background:#d4af37; }
.bc-quiz-step { display:none; }
.bc-quiz-step.active { display:block;animation:bc-fadeUp .4s ease both; }
.bc-quiz-q { font-size:.88rem;font-weight:700;color:#e2e8f0;margin-bottom:14px; }
.bc-quiz-q span { color:#d4af37; }
.bc-quiz-options { display:flex;flex-wrap:wrap;gap:10px; }
.bc-quiz-opt { padding:9px 18px;border-radius:10px;font-size:.8rem;font-weight:600;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#94a3b8;cursor:pointer;transition:background .2s,border-color .2s,color .2s,transform .2s;user-select:none; }
.bc-quiz-opt:hover  { border-color:rgba(212,175,55,.3);color:#e2e8f0;transform:translateY(-2px); }
.bc-quiz-opt.chosen { background:rgba(212,175,55,.12);border-color:rgba(212,175,55,.4);color:#d4af37; }
.bc-quiz-result { display:none; }
.bc-quiz-result.show { display:block;animation:bc-fadeUp .5s ease both; }
.bc-quiz-result-inner { background:rgba(212,175,55,.06);border:1px solid rgba(212,175,55,.18);border-radius:16px;padding:22px;display:flex;align-items:center;gap:20px;flex-wrap:wrap; }
.bc-quiz-result-logo { width:56px;height:56px;border-radius:14px;flex-shrink:0;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);display:flex;align-items:center;justify-content:center; }
.bc-quiz-result-logo img { max-width:42px;max-height:32px;object-fit:contain;filter:brightness(1.1); }
.bc-quiz-result-text h4 { font-size:1rem;font-weight:900;color:#f1f5f9;margin:0 0 4px; }
.bc-quiz-result-text p  { font-size:.82rem;color:#64748b;margin:0 0 12px; }
.bc-quiz-result-btns { display:flex;gap:10px;flex-wrap:wrap; }
.bc-quiz-reset { padding:7px 16px;border-radius:9px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#64748b;cursor:pointer;font-size:.78rem;font-weight:600;font-family:inherit;transition:.2s; }
.bc-quiz-reset:hover { color:#cbd5e1;border-color:rgba(255,255,255,.2); }

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width:900px) {
    .bc-stats { grid-template-columns:repeat(2,1fr); }
    .bc-hero-top { flex-direction:column; }
    .bc-modal-sidebar { width:100%; height:150px; border-left:none; border-top:1px solid rgba(255,255,255,.06); flex-direction:row; }
    .bc-modal-sidebar-lbl { writing-mode:initial; border-bottom:none; border-right:1px solid rgba(255,255,255,.04); min-width:50px; display:flex; align-items:center; padding:10px 12px; }
    .bc-modal-thumbs { flex-direction:row; overflow-x:auto; overflow-y:hidden; }
    .bc-modal-thumb { width:90px; height:63px; aspect-ratio:unset; flex-shrink:0; }
    .bc-modal-footer { border-top:none; border-left:1px solid rgba(255,255,255,.04); justify-content:center; min-width:120px; }
    .bc-modal-body { flex-direction:column; }
}
@media (max-width:640px) {
    .bc-main { padding:18px 16px 40px; }
    .bc-hero  { padding:28px 22px 26px; }
    .bc-stats { grid-template-columns:repeat(2,1fr); }
    .bc-grid  { grid-template-columns:1fr; }
    .bc-hero-recommended { display:none; }
}
</style>

<div class="dashboard-wrapper">

<?php include 'includes/sidebar.php'; ?>

<div class="bc-main">
<div class="bc-page">

<!-- ══ HERO ══════════════════════════════════════════════════════ -->
<div class="bc-hero">
    <div class="bc-hero-grid"></div>
    <div class="bc-hero-orb-1"></div>
    <div class="bc-hero-orb-2"></div>
    <div class="bc-hero-particles">
        <div class="bc-hero-particle" style="left:10%;--d:9s;--dl:0s"></div>
        <div class="bc-hero-particle" style="left:28%;--d:12s;--dl:-3s"></div>
        <div class="bc-hero-particle" style="left:52%;--d:8s;--dl:-6s"></div>
        <div class="bc-hero-particle" style="left:74%;--d:11s;--dl:-2s"></div>
        <div class="bc-hero-particle" style="left:90%;--d:10s;--dl:-5s"></div>
    </div>

    <div class="bc-hero-inner">
        <div class="bc-hero-top">
            <div>
                <div class="bc-hero-badge"><span class="bc-hero-badge-dot"></span> Dashboard · Broker Comparison</div>
                <h1>Compare <span class="bc-gold-text">Global Brokers</span></h1>
                <p class="bc-hero-sub">Global brokerage platforms ကို နှိုင်းယှဉ်ကြည့်ပြီး သင့်အတွက် သင့်တော်သော broker ကိုရွေးချယ်နိုင်ပါသည်။</p>
            </div>
            <div class="bc-hero-recommended">
                <div class="bc-hero-rec-logo">
                    <img src="assets/logos/ibkr.png" alt="IBKR">
                </div>
                <div>
                    <div class="bc-hero-rec-label">🏆 Top Recommended</div>
                    <div class="bc-hero-rec-name">Interactive Brokers</div>
                </div>
            </div>
        </div>

        <div class="bc-stats">
            <div class="bc-stat" style="animation-delay:.1s">
                <strong data-count="9">0</strong><span>Brokers</span>
            </div>
            <div class="bc-stat" style="animation-delay:.18s">
                <strong>$0</strong><span>Min. Deposit</span>
            </div>
            <div class="bc-stat" style="animation-delay:.26s">
                <strong data-count="135">0</strong><span>Markets</span>
            </div>
            <div class="bc-stat" style="animation-delay:.34s">
                <strong>10K+</strong><span>Stocks</span>
            </div>
        </div>
    </div>
</div>

<!-- ══ FIND MY BROKER QUIZ ════════════════════════════════════════ -->
<div class="bc-quiz-section bc-reveal">
    <div class="bc-sec-head">
        <h2>Find My Broker</h2>
        <p>Answer 3 quick questions to get a personalized broker recommendation</p>
        <div class="bc-sec-line"></div>
    </div>
    <div class="bc-quiz-panel">
        <div class="bc-quiz-topbar"></div>
        <div class="bc-quiz-body">
            <div class="bc-quiz-head">
                <div class="bc-quiz-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
                <div class="bc-quiz-head-text">
                    <h3>Smart Broker Matcher</h3>
                    <p>Select your preferences and we'll recommend the best match for you</p>
                </div>
            </div>
            <div class="bc-quiz-progress">
                <div class="bc-quiz-dot" id="qd1"></div>
                <div class="bc-quiz-dot" id="qd2"></div>
                <div class="bc-quiz-dot" id="qd3"></div>
            </div>

            <div class="bc-quiz-step active" id="qs1">
                <div class="bc-quiz-q"><span>Q1.</span> What is your investing experience level?</div>
                <div class="bc-quiz-options">
                    <div class="bc-quiz-opt" onclick="quizAnswer(1,'beginner',this)">🌱 Complete Beginner</div>
                    <div class="bc-quiz-opt" onclick="quizAnswer(1,'intermediate',this)">📈 Intermediate</div>
                    <div class="bc-quiz-opt" onclick="quizAnswer(1,'advanced',this)">🚀 Advanced / Professional</div>
                </div>
            </div>

            <div class="bc-quiz-step" id="qs2">
                <div class="bc-quiz-q"><span>Q2.</span> Which markets are you most interested in?</div>
                <div class="bc-quiz-options">
                    <div class="bc-quiz-opt" onclick="quizAnswer(2,'us',this)">🇺🇸 US Stocks & ETFs</div>
                    <div class="bc-quiz-opt" onclick="quizAnswer(2,'asia',this)">🌏 Asian Markets (HK/SG)</div>
                    <div class="bc-quiz-opt" onclick="quizAnswer(2,'forex',this)">💱 Forex & CFDs</div>
                    <div class="bc-quiz-opt" onclick="quizAnswer(2,'social',this)">👥 Social / Copy Trading</div>
                    <div class="bc-quiz-opt" onclick="quizAnswer(2,'global',this)">🌍 Global (135+ Markets)</div>
                </div>
            </div>

            <div class="bc-quiz-step" id="qs3">
                <div class="bc-quiz-q"><span>Q3.</span> What matters most to you?</div>
                <div class="bc-quiz-options">
                    <div class="bc-quiz-opt" onclick="quizAnswer(3,'lowfee',this)">💰 Low Fees</div>
                    <div class="bc-quiz-opt" onclick="quizAnswer(3,'research',this)">🔬 Research & Analysis Tools</div>
                    <div class="bc-quiz-opt" onclick="quizAnswer(3,'easy',this)">📱 Easy Mobile App</div>
                    <div class="bc-quiz-opt" onclick="quizAnswer(3,'regulated',this)">🛡️ Strong Regulation</div>
                </div>
            </div>

            <div class="bc-quiz-result" id="quizResult">
                <div class="bc-quiz-result-inner">
                    <div class="bc-quiz-result-logo"><img id="quizResLogo" src="" alt=""></div>
                    <div class="bc-quiz-result-text">
                        <h4 id="quizResName"></h4>
                        <p id="quizResReason"></p>
                        <div class="bc-quiz-result-btns">
                            <a href="contact.php" class="bc-card-btn bc-btn-service" style="padding:8px 18px;font-size:.8rem;"><i class="fas fa-rocket"></i> Get Service</a>
                            <button class="bc-quiz-reset" onclick="quizReset()"><i class="fas fa-rotate-left"></i> Retake Quiz</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══ BROKER CARDS ═══════════════════════════════════════════════ -->
<div class="bc-reveal">
    <div class="bc-sec-head">
        <h2>Broker Platforms</h2>
        <p>Click "View Details" to see setup guide images for each broker</p>
        <div class="bc-sec-line"></div>
    </div>

    <!-- Filter tabs -->
    <div class="bc-filters">
        <div class="bc-filter-tab active" data-filter="all">All Brokers</div>
        <div class="bc-filter-tab" data-filter="stocks">Stocks & ETFs</div>
        <div class="bc-filter-tab" data-filter="social">Social Trading</div>
        <div class="bc-filter-tab" data-filter="forex">Forex & CFD</div>
        <div class="bc-filter-tab" data-filter="transfer">Money Transfer</div>
    </div>
</div>

<div class="bc-grid" id="brokerGrid">
<?php foreach ($brokers as $i => $b): ?>
<div class="bc-card" data-filter="<?= $b['filter']; ?>" data-delay="<?= $i * 60; ?>">

    <div class="bc-card-badge <?= $b['badge_cls']; ?>"><?= htmlspecialchars($b['badge']); ?></div>

    <div class="bc-card-head">
        <div class="bc-card-logo">
            <img src="assets/logos/<?= htmlspecialchars($b['logo']); ?>" alt="<?= htmlspecialchars($b['name']); ?>">
        </div>
        <div class="bc-card-title">
            <div class="bc-card-name"><?= htmlspecialchars($b['name']); ?></div>
            <div class="bc-card-rating">
                <span><?= number_format($b['rating'],1); ?></span>
                <span class="bc-stars">★★★★★</span>
            </div>
        </div>
    </div>

    <div class="bc-card-tags">
        <?php foreach ($b['tags'] as $tag): ?>
        <span class="bc-tag"><?= htmlspecialchars($tag); ?></span>
        <?php endforeach; ?>
    </div>

    <div class="bc-rating-bar-wrap">
        <div class="bc-rating-track">
            <div class="bc-rating-fill" data-rating="<?= $b['rating']; ?>"></div>
        </div>
    </div>

    <div class="bc-card-stats">
        <div class="bc-card-stat">
            <span class="bc-card-stat-val"><?= htmlspecialchars($b['min_dep']); ?></span>
            <span class="bc-card-stat-lbl">Min Dep.</span>
        </div>
        <div class="bc-card-stat">
            <span class="bc-card-stat-val" style="font-size:.7rem"><?= htmlspecialchars($b['markets']); ?></span>
            <span class="bc-card-stat-lbl">Markets</span>
        </div>
        <div class="bc-card-stat">
            <span class="bc-card-stat-val" style="font-size:.66rem"><?= htmlspecialchars($b['reg']); ?></span>
            <span class="bc-card-stat-lbl">Regulated</span>
        </div>
    </div>

    <div class="bc-card-actions">
        <button class="bc-card-btn bc-btn-details"
            onclick="openModal('<?= $b['key']; ?>','<?= htmlspecialchars($b['name']); ?>','assets/logos/<?= htmlspecialchars($b['logo']); ?>')">
            <i class="fas fa-images"></i> View Details
        </button>
        <a href="contact.php" class="bc-card-btn bc-btn-service">
            <i class="fas fa-rocket"></i> Get Service
        </a>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- ══ COMPARISON TABLE ═══════════════════════════════════════════ -->
<div class="bc-compare-section bc-reveal">
    <div class="bc-sec-head">
        <h2>Feature Comparison</h2>
        <p>Side-by-side breakdown of every broker's key specifications</p>
        <div class="bc-sec-line"></div>
    </div>
    <div class="bc-compare-wrap">
        <table class="bc-compare-table">
            <thead>
                <tr>
                    <th>Feature</th>
                    <?php foreach ($brokers as $b): ?>
                    <th class="bc-th-broker">
                        <div class="bc-th-broker-inner">
                            <div class="bc-th-logo">
                                <img src="assets/logos/<?= htmlspecialchars($b['logo']); ?>" alt="<?= htmlspecialchars($b['name']); ?>">
                            </div>
                            <span class="bc-th-name"><?= htmlspecialchars($b['name']); ?></span>
                        </div>
                    </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compare_features as $feat): ?>
                <tr>
                    <td><i class="<?= $feat['icon']; ?>"></i><?= htmlspecialchars($feat['label']); ?></td>
                    <?php foreach ($brokers as $b): ?>
                    <td><span class="bc-val"><?= htmlspecialchars($b[$feat['key']]); ?></span></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ══ BROKER GUIDES ══════════════════════════════════════════════ -->
<div class="bc-guides-section">
    <div class="bc-sec-head bc-reveal">
        <h2>Broker အကြောင်းရှင်းလင်းချက်</h2>
        <p>မြန်မာဘာသာဖြင့် broker တစ်ခုချင်းစီ၏ အသေးစိတ်ရှင်းလင်းချက်</p>
        <div class="bc-sec-line"></div>
    </div>
    <div class="bc-guides-grid">
        <?php foreach ($brokers as $i => $b): ?>
        <div class="bc-guide-card" data-delay="<?= $i * 55; ?>">
            <div class="bc-guide-logo">
                <img src="assets/logos/<?= htmlspecialchars($b['logo']); ?>" alt="<?= htmlspecialchars($b['name']); ?>">
            </div>
            <h4><?= htmlspecialchars($b['name']); ?></h4>
            <p><?= htmlspecialchars($b['desc']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</div><!-- /.bc-page -->
</div><!-- /.bc-main -->
</div><!-- /.dashboard-wrapper -->

<!-- ══ MODAL ══════════════════════════════════════════════════════ -->
<div class="bc-modal" id="bcModal">
    <div class="bc-modal-backdrop" onclick="bcCloseModal()"></div>
    <div class="bc-modal-panel">
        <div class="bc-modal-topbar"></div>

        <div class="bc-modal-header">
            <div class="bc-modal-broker">
                <div class="bc-modal-b-logo"><img id="bcModalLogo" src="" alt=""></div>
                <div>
                    <p class="bc-modal-b-name" id="bcModalName"></p>
                    <p class="bc-modal-b-sub">Setup Guide — Step-by-step</p>
                </div>
            </div>
            <div class="bc-modal-header-r">
                <div class="bc-modal-counter">Step <span id="bcCurrent">1</span> of <span id="bcTotal">3</span></div>
                <button class="bc-modal-close" onclick="bcCloseModal()"><i class="fas fa-times"></i></button>
            </div>
        </div>

        <div class="bc-modal-body">
            <div class="bc-modal-stage" id="bcStage">
                <button class="bc-modal-arrow bc-modal-prev disabled" id="bcPrev" onclick="bcNav(-1)"><i class="fas fa-chevron-left"></i></button>
                <img class="bc-modal-img" id="bcImg" src="" alt="">
                <button class="bc-modal-arrow bc-modal-next" id="bcNext" onclick="bcNav(1)"><i class="fas fa-chevron-right"></i></button>
            </div>

            <div class="bc-modal-sidebar">
                <div class="bc-modal-sidebar-lbl">All Steps</div>
                <div class="bc-modal-thumbs" id="bcThumbs"></div>
                <div class="bc-modal-footer">
                    <div class="bc-modal-hint"><kbd>←</kbd><kbd>→</kbd> navigate &nbsp;<kbd>Esc</kbd> close</div>
                    <a href="contact.php" class="bc-modal-cta"><i class="fas fa-rocket"></i> Get This Service</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
/* ── Broker image data ── */
const bcCards = {
    xtb:          ["assets/broker-cards/xtb/card-1.jpg","assets/broker-cards/xtb/card-2.jpg","assets/broker-cards/xtb/card-3.jpg"],
    etoro:        ["assets/broker-cards/etoro/card-1.jpg","assets/broker-cards/etoro/card-2.jpg","assets/broker-cards/etoro/card-3.jpg"],
    schwab:       ["assets/broker-cards/schwab/card-1.jpg","assets/broker-cards/schwab/card-2.jpg","assets/broker-cards/schwab/card-3.jpg"],
    ibkr:         ["assets/broker-cards/ibkr/card-1.jpg","assets/broker-cards/ibkr/card-2.jpg","assets/broker-cards/ibkr/card-3.jpg"],
    tiger:        ["assets/broker-cards/tiger/card-1.jpg","assets/broker-cards/tiger/card-2.jpg","assets/broker-cards/tiger/card-3.jpg"],
    moomoo:       ["assets/broker-cards/moomoo/card-1.jpg","assets/broker-cards/moomoo/card-2.jpg","assets/broker-cards/moomoo/card-3.jpg"],
    easyequities: ["assets/broker-cards/easyequities/card-1.jpg","assets/broker-cards/easyequities/card-2.jpg","assets/broker-cards/easyequities/card-3.jpg"],
    longbridge:   ["assets/broker-cards/longbridge/card-1.jpg","assets/broker-cards/longbridge/card-2.jpg","assets/broker-cards/longbridge/card-3.jpg"]
};

let bcImages = [], bcIndex = 0;

function openModal(broker, name, logo) {
    document.getElementById('bcModalName').textContent = name;
    document.getElementById('bcModalLogo').src         = logo;
    document.getElementById('bcModalLogo').alt         = name;

    bcImages = bcCards[broker] || [];
    bcIndex  = 0;

    const stage = document.getElementById('bcStage');
    const nd    = document.getElementById('bcNoData');
    if (nd) nd.remove();

    if (!bcImages.length) {
        document.getElementById('bcImg').style.display  = 'none';
        document.getElementById('bcPrev').style.display = 'none';
        document.getElementById('bcNext').style.display = 'none';
        document.getElementById('bcThumbs').innerHTML   = '';
        document.getElementById('bcCurrent').textContent = '0';
        document.getElementById('bcTotal').textContent   = '0';
        stage.insertAdjacentHTML('beforeend',
            `<div class="bc-modal-nodata" id="bcNoData">
                <i class="fas fa-clock"></i>
                <h4>Coming Soon</h4>
                <p>Setup guide for this broker will be available shortly.</p>
             </div>`);
    } else {
        document.getElementById('bcImg').style.display  = 'block';
        document.getElementById('bcPrev').style.display = 'flex';
        document.getElementById('bcNext').style.display = 'flex';
        document.getElementById('bcTotal').textContent  = bcImages.length;
        bcBuildThumbs();
        bcShow(0);
    }

    document.getElementById('bcModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function bcBuildThumbs() {
    const strip = document.getElementById('bcThumbs');
    strip.innerHTML = '';
    bcImages.forEach((src, i) => {
        const t = document.createElement('div');
        t.className = 'bc-modal-thumb' + (i === 0 ? ' active' : '');
        t.innerHTML = `<img src="${src}" alt="Step ${i+1}" loading="lazy">`;
        t.addEventListener('click', () => bcShow(i));
        strip.appendChild(t);
    });
}

function bcShow(i) {
    if (i < 0 || i >= bcImages.length) return;
    bcIndex = i;
    const img = document.getElementById('bcImg');
    img.style.animation = 'none'; img.offsetHeight; img.style.animation = '';
    img.src = bcImages[i]; img.alt = `Step ${i + 1}`;
    document.getElementById('bcCurrent').textContent = i + 1;
    document.getElementById('bcPrev').classList.toggle('disabled', i === 0);
    document.getElementById('bcNext').classList.toggle('disabled', i === bcImages.length - 1);
    document.querySelectorAll('.bc-modal-thumb').forEach((t, ti) => t.classList.toggle('active', ti === i));
}

function bcNav(dir) { bcShow(bcIndex + dir); }

function bcCloseModal() {
    document.getElementById('bcModal').classList.remove('show');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
    if (!document.getElementById('bcModal').classList.contains('show')) return;
    if (e.key === 'ArrowLeft')  bcNav(-1);
    if (e.key === 'ArrowRight') bcNav(1);
    if (e.key === 'Escape')     bcCloseModal();
});

/* ── Filter tabs ── */
document.querySelectorAll('.bc-filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.bc-filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const f = tab.dataset.filter;
        document.querySelectorAll('.bc-card').forEach(card => {
            const match = f === 'all' || card.dataset.filter === f;
            card.style.display = match ? '' : 'none';
        });
    });
});

/* ── Scroll reveal ── */
(function () {
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            const d = parseInt(e.target.dataset.delay) || 0;
            setTimeout(() => e.target.classList.add('visible'), d);
            obs.unobserve(e.target);
        });
    }, { threshold: 0.08 });
    document.querySelectorAll('.bc-card, .bc-guide-card, .bc-reveal').forEach(el => obs.observe(el));
}());

/* ── Animate rating bars on scroll ── */
(function () {
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            const fill = e.target;
            const pct  = (parseFloat(fill.dataset.rating) / 10) * 100;
            setTimeout(() => { fill.style.width = pct + '%'; }, 300);
            obs.unobserve(fill);
        });
    }, { threshold: 0.2 });
    document.querySelectorAll('.bc-rating-fill').forEach(el => obs.observe(el));
}());

/* ── Find My Broker Quiz ── */
const quizAnswers = {};
const quizRecommend = {
    beginner:     { us: 'moomoo',       asia: 'tiger',      forex: 'etoro',     social: 'etoro',   global: 'moomoo'       },
    intermediate: { us: 'schwab',       asia: 'longbridge', forex: 'xtb',       social: 'etoro',   global: 'tiger'        },
    advanced:     { us: 'ibkr',         asia: 'ibkr',       forex: 'xtb',       social: 'etoro',   global: 'ibkr'         },
};
const quizData = {
    ibkr:         { name:'Interactive Brokers', logo:'assets/logos/ibkr.png',        reason:'Best overall broker with access to 135+ global markets, professional tools, and ultra-low fees.' },
    tiger:        { name:'Tiger Brokers',        logo:'assets/logos/tiger.png',       reason:'Top pick for Asian market access (US, HK, SG) with beginner-friendly onboarding.' },
    moomoo:       { name:'Moomoo',               logo:'assets/logos/moomoo.png',      reason:'Excellent research tools and commission-free trading — perfect for your profile.' },
    etoro:        { name:'eToro',                logo:'assets/logos/etoro.svg',       reason:'Leading social trading platform. Copy experienced traders with ease.' },
    schwab:       { name:'Charles Schwab',       logo:'assets/logos/schwab.svg',      reason:'Trusted US broker with $0 commissions, ideal for long-term US investing.' },
    longbridge:   { name:'Longbridge',           logo:'assets/logos/longbridge.webp', reason:'Great for HK/SG market access with simple account setup for Myanmar users.' },
    xtb:          { name:'XTB',                  logo:'assets/logos/xtb.png',         reason:'Award-winning Forex & CFD platform with powerful xStation trading tools.' },
};

function quizAnswer(step, value, btn) {
    document.querySelectorAll(`#qs${step} .bc-quiz-opt`).forEach(b => b.classList.remove('chosen'));
    btn.classList.add('chosen');
    quizAnswers[step] = value;
    document.getElementById(`qd${step}`).classList.add('done');

    if (step < 3) {
        setTimeout(() => {
            document.getElementById(`qs${step}`).classList.remove('active');
            document.getElementById(`qs${step+1}`).classList.add('active');
        }, 320);
    } else {
        setTimeout(() => {
            document.getElementById('qs3').classList.remove('active');
            const level  = quizAnswers[1] || 'beginner';
            const market = quizAnswers[2] || 'global';
            const key    = (quizRecommend[level] || {})[market] || 'ibkr';
            const rec    = quizData[key];
            document.getElementById('quizResLogo').src    = rec.logo;
            document.getElementById('quizResName').textContent   = '🏆 We recommend: ' + rec.name;
            document.getElementById('quizResReason').textContent = rec.reason;
            document.getElementById('quizResult').classList.add('show');
        }, 320);
    }
}

function quizReset() {
    Object.keys(quizAnswers).forEach(k => delete quizAnswers[k]);
    document.querySelectorAll('.bc-quiz-opt').forEach(b => b.classList.remove('chosen'));
    document.querySelectorAll('.bc-quiz-dot').forEach(d => d.classList.remove('done'));
    document.getElementById('quizResult').classList.remove('show');
    document.querySelectorAll('.bc-quiz-step').forEach(s => s.classList.remove('active'));
    document.getElementById('qs1').classList.add('active');
}

/* ── Animated counters ── */
(function () {
    const obs = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            const el  = e.target;
            const end = parseInt(el.dataset.count);
            const dur = 1600, step = 16, inc = end / (dur / step);
            let cur = 0;
            const t = setInterval(() => {
                cur += inc;
                if (cur >= end) { cur = end; clearInterval(t); }
                el.textContent = Math.floor(cur) + (end > 10 ? '+' : '+');
            }, step);
            obs.unobserve(el);
        });
    }, { threshold: 0.6 });
    document.querySelectorAll('[data-count]').forEach(el => obs.observe(el));
}());
</script>

<?php include 'includes/footer.php'; ?>
