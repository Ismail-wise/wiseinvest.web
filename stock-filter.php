<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']))           { header("Location: login.php"); exit(); }
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'premium') { header("Location: index.php"); exit(); }

$page_title = "Stock Filter";
include 'includes/header.php';

$filters = [
['id'=>1, 'category'=>'Growth',            'live'=>true,  'title'=>'Revenue Growth',          'threshold'=>'>= 12%',   'score'=>84, 'short'=>'Sales expansion rate',                     'explanation_mm'=>'ကုမ္ပဏီ၏ ဝင်ငွေတိုးတက်နှုန်းကို တိုင်းတာသော filter ဖြစ်သည်။'],
['id'=>2, 'category'=>'Growth',            'live'=>true,  'title'=>'EPS Growth',               'threshold'=>'>= 10%',   'score'=>80, 'short'=>'Earnings per share momentum',             'explanation_mm'=>'EPS Growth သည် share တစ်စုလျှင် အမြတ်တိုးတက်မှုကို ပြသသည်။'],
['id'=>3, 'category'=>'Growth',            'live'=>false, 'title'=>'5-Year Revenue Stability',  'threshold'=>'>= 75/100','score'=>78, 'short'=>'Consistency over 5 years',                 'explanation_mm'=>'၅ နှစ်အတွင်း ဝင်ငွေတည်ငြိမ်မှုကို စစ်ဆေးသည်။'],
['id'=>4, 'category'=>'Growth',            'live'=>true,  'title'=>'Operating Income Growth',   'threshold'=>'>= 10%',   'score'=>76, 'short'=>'Core business earnings growth',            'explanation_mm'=>'Operating Income Growth သည် အဓိကလုပ်ငန်းမှ အမြတ်တိုးတက်မှုကို ညွှန်ပြသည်။'],
['id'=>5, 'category'=>'Profitability',     'live'=>true,  'title'=>'Return on Equity (ROE)',   'threshold'=>'>= 15%',   'score'=>88, 'short'=>'Profit generated from shareholder capital', 'explanation_mm'=>'ROE သည် shareholder equity အပေါ် အမြတ်ထုတ်နိုင်မှုကို တိုင်းတာသည်။'],
['id'=>6, 'category'=>'Profitability',     'live'=>true,  'title'=>'Net Profit Margin',        'threshold'=>'>= 12%',   'score'=>82, 'short'=>'Bottom-line efficiency',                   'explanation_mm'=>'Net Profit Margin သည် ဝင်ငွေထဲမှ သန့်ရှင်းသောအမြတ် ရာခိုင်နှုန်းကို ဖော်ပြသည်။'],
['id'=>7, 'category'=>'Profitability',     'live'=>true,  'title'=>'ROIC',                     'threshold'=>'>= 12%',   'score'=>85, 'short'=>'Return on invested capital',               'explanation_mm'=>'ROIC သည် business capital အပေါ် return ကို ပြသသည်။'],
['id'=>8, 'category'=>'Profitability',     'live'=>false, 'title'=>'Free Cash Flow',            'threshold'=>'Positive', 'score'=>90, 'short'=>'Cash generation ability',                 'explanation_mm'=>'Free Cash Flow Positive ဆိုသည်မှာ cash generation ကောင်းကြောင်း ပြသည်။'],
['id'=>9, 'category'=>'Financial Strength','live'=>true,  'title'=>'Debt to Equity',           'threshold'=>'<= 1.0',   'score'=>73, 'short'=>'Leverage ratio',                           'explanation_mm'=>'Debt to Equity သည် leverage risk ကို ဖော်ပြသည်။'],
['id'=>10,'category'=>'Financial Strength','live'=>false, 'title'=>'Interest Coverage',         'threshold'=>'>= 5x',    'score'=>79, 'short'=>'Interest payment ability',                 'explanation_mm'=>'Interest Coverage မြင့်လျှင် default risk နည်းသည်။'],
['id'=>11,'category'=>'Financial Strength','live'=>true,  'title'=>'Altman Z Score',            'threshold'=>'>= 3',     'score'=>81, 'short'=>'Bankruptcy risk indicator',                'explanation_mm'=>'Altman Z Score သည် bankruptcy risk ကို ခန့်မှန်းရန် အသုံးပြုသည်။'],
['id'=>12,'category'=>'Financial Strength','live'=>true,  'title'=>'Piotroski F Score',         'threshold'=>'>= 7 / 9', 'score'=>82, 'short'=>'9-point fundamental health check',         'explanation_mm'=>'Piotroski F Score (0-9) သည် ကုမ္ပဏီ၏ financial health ကို point ၉ ဖြင့် တိုင်းတာသည်။'],
['id'=>13,'category'=>'Valuation',         'live'=>false, 'title'=>'P/E Ratio',                'threshold'=>'<= 25',    'score'=>68, 'short'=>'Price vs earnings',                        'explanation_mm'=>'PE Ratio သည် valuation metric တစ်ခုဖြစ်သည်။'],
['id'=>14,'category'=>'Valuation',         'live'=>false, 'title'=>'PEG Ratio',                'threshold'=>'<= 1.5',   'score'=>74, 'short'=>'PE adjusted for growth',                   'explanation_mm'=>'PEG Ratio သည် growth-adjusted valuation ဖြစ်သည်။'],
['id'=>15,'category'=>'Valuation',         'live'=>false, 'title'=>'Price to FCF',              'threshold'=>'<= 20',    'score'=>71, 'short'=>'Price vs free cash flow',                  'explanation_mm'=>'FCF valuation metric ဖြစ်သည်။'],
['id'=>16,'category'=>'Valuation',         'live'=>false, 'title'=>'EV / EBITDA',               'threshold'=>'<= 14',    'score'=>70, 'short'=>'Enterprise valuation',                     'explanation_mm'=>'EV / EBITDA သည် valuation comparison metric ဖြစ်သည်။'],
['id'=>17,'category'=>'Qualitative',       'live'=>false, 'title'=>'Competitive Moat',         'threshold'=>'>= 80',    'score'=>86, 'short'=>'Competitive advantage',                    'explanation_mm'=>'Moat ဆိုသည်မှာ long-term competitive advantage ဖြစ်သည်။'],
['id'=>18,'category'=>'Income',            'live'=>false, 'title'=>'Dividend Yield',           'threshold'=>'>= 1.5%',  'score'=>72, 'short'=>'Annual income return on stock price',      'explanation_mm'=>'Dividend Yield သည် income-seeking investors များအတွက် ရရှိနိုင်သော annual return ကို ဖော်ပြသည်။'],
];

function score_color($s){ return $s>=85?'excellent':($s>=75?'good':($s>=65?'watch':'weak')); }
$categories  = array_unique(array_column($filters,'category'));
$liveCount   = count(array_filter($filters, fn($f)=>!empty($f['live'])));
$excellentCt = count(array_filter($filters, fn($f)=>$f['score']>=85));
$avgScore    = round(array_sum(array_column($filters,'score'))/count($filters));
?>
<style>
:root{
  --gold:#d4af37; --gold-l:#facc15; --navy:#020617;
  --card:#0a0f24; --brd:rgba(212,175,55,.12);
  --text:#e2e8f0; --muted:#64748b;
  --green:#34d399; --amber:#fbbf24; --red:#f87171; --blue:#60a5fa; --purple:#a78bfa; --cyan:#22d3ee;
}
*{box-sizing:border-box}
body{background:var(--navy);color:var(--text);font-family:'Segoe UI',system-ui,sans-serif;margin:0}

/* ── BG ── */
.sf2-bg{position:fixed;inset:0;pointer-events:none;z-index:0;
  background:linear-gradient(rgba(212,175,55,.022) 1px,transparent 1px),
             linear-gradient(90deg,rgba(212,175,55,.022) 1px,transparent 1px);
  background-size:52px 52px}
.sf2-bg::after{content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse 65% 55% at 20% 20%,rgba(212,175,55,.07) 0%,transparent 55%),
             radial-gradient(ellipse 50% 45% at 85% 75%,rgba(96,165,250,.04) 0%,transparent 55%)}

/* ── Layout ── */
.sf2-wrap{display:flex;min-height:100vh;position:relative;z-index:1}
.sf2-main{flex:1;padding:40px 44px;min-width:0}

/* ── Hero ── */
.sf2-hero{margin-bottom:36px}
.sf2-eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:.68rem;font-weight:700;
  letter-spacing:3.5px;text-transform:uppercase;color:var(--gold);margin-bottom:14px;
  padding:6px 16px;background:rgba(212,175,55,.07);border:1px solid rgba(212,175,55,.2);border-radius:30px}
.sf2-eyebrow-dot{width:6px;height:6px;border-radius:50%;background:var(--gold);
  animation:sfDotPulse 1.8s ease-in-out infinite}
@keyframes sfDotPulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.3;transform:scale(.6)}}
.sf2-title{font-size:clamp(2rem,4vw,3rem);font-weight:900;line-height:1.05;margin:0 0 12px;
  background:linear-gradient(130deg,#fff 25%,var(--gold) 60%,var(--gold-l) 90%);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.sf2-sub{color:var(--muted);font-size:.95rem;max-width:540px;line-height:1.7;margin-bottom:28px}

/* ── Stats ── */
.sf2-stats{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:36px}
.sf2-stat{flex:1;min-width:120px;display:flex;align-items:center;gap:14px;
  background:rgba(10,15,36,.85);border:1px solid var(--brd);border-radius:16px;
  padding:16px 20px;backdrop-filter:blur(14px);transition:transform .3s,border-color .3s}
.sf2-stat:hover{transform:translateY(-3px);border-color:rgba(212,175,55,.3)}
.sf2-stat-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.sf2-stat-icon.gold{background:rgba(212,175,55,.12);color:var(--gold)}
.sf2-stat-icon.green{background:rgba(52,211,153,.1);color:var(--green)}
.sf2-stat-icon.blue{background:rgba(96,165,250,.1);color:var(--blue)}
.sf2-stat-icon.cyan{background:rgba(34,211,238,.1);color:var(--cyan)}
.sf2-stat-num{font-size:1.6rem;font-weight:900;color:#f1f5f9;line-height:1}
.sf2-stat-lbl{font-size:.65rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);margin-top:2px}

/* ── Controls row ── */
.sf2-controls{display:flex;align-items:center;gap:14px;margin-bottom:28px;flex-wrap:wrap}
.sf2-tabs{display:flex;gap:6px;flex-wrap:wrap;flex:1}
.sf2-tab{padding:8px 18px;border-radius:30px;border:1px solid rgba(255,255,255,.07);
  background:transparent;color:var(--muted);font-size:.8rem;font-weight:600;cursor:pointer;
  transition:all .22s;display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
.sf2-tab:hover{border-color:rgba(212,175,55,.35);color:var(--gold);background:rgba(212,175,55,.05)}
.sf2-tab.active{background:rgba(212,175,55,.14);border-color:rgba(212,175,55,.5);color:var(--gold);
  box-shadow:0 0 20px rgba(212,175,55,.1)}
.sf2-tab-n{background:rgba(255,255,255,.07);border-radius:8px;padding:1px 7px;font-size:.66rem;font-weight:700}
.sf2-tab.active .sf2-tab-n{background:rgba(212,175,55,.22);color:var(--gold)}

/* Search */
.sf2-search{display:flex;align-items:center;gap:10px;background:rgba(10,15,36,.9);
  border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:10px 16px;
  backdrop-filter:blur(12px);min-width:220px;transition:border-color .2s,box-shadow .2s}
.sf2-search:focus-within{border-color:rgba(212,175,55,.45);box-shadow:0 0 0 3px rgba(212,175,55,.08)}
.sf2-search i{color:var(--muted);font-size:.88rem;flex-shrink:0}
.sf2-search:focus-within i{color:var(--gold)}
.sf2-search input{background:none;border:none;outline:none;color:var(--text);font-size:.88rem;
  font-family:inherit;width:160px}
.sf2-search input::placeholder{color:var(--muted)}

/* ── Filter count ── */
.sf2-count{font-size:.8rem;color:var(--muted);margin-bottom:24px}
.sf2-count strong{color:var(--text);font-weight:700}

/* ── Grid ── */
.sf2-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px}

/* ── Card ── */
.sf2-card{background:rgba(8,13,30,.88);border:1px solid rgba(255,255,255,.06);border-radius:22px;
  overflow:hidden;backdrop-filter:blur(16px);position:relative;
  transition:transform .38s cubic-bezier(.34,1.56,.64,1),border-color .3s,box-shadow .3s;
  opacity:0;transform:translateY(22px) scale(.98)}
.sf2-card.visible{animation:sfCardReveal .5s ease forwards}
@keyframes sfCardReveal{to{opacity:1;transform:translateY(0) scale(1)}}
.sf2-card:hover{transform:translateY(-7px) scale(1.01);border-color:rgba(212,175,55,.3);
  box-shadow:0 24px 60px rgba(0,0,0,.55),0 0 40px rgba(212,175,55,.07)}
.sf2-card.hidden{display:none}

/* Card top accent line */
.sf2-card-accent{height:2px;width:100%}

/* Card body */
.sf2-card-body{padding:24px}

/* Top row: badge stack + score ring */
.sf2-card-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:18px}
.sf2-badge-col{display:flex;flex-direction:column;gap:7px;align-items:flex-start}
.sf2-cat-badge{display:inline-flex;align-items:center;gap:5px;font-size:.62rem;font-weight:700;
  letter-spacing:1.5px;text-transform:uppercase;padding:4px 11px;border-radius:20px}
.sf2-cat-badge.Growth{background:rgba(34,211,238,.1);color:var(--cyan);border:1px solid rgba(34,211,238,.2)}
.sf2-cat-badge.Profitability{background:rgba(52,211,153,.1);color:var(--green);border:1px solid rgba(52,211,153,.2)}
.sf2-cat-badge.Financial-Strength{background:rgba(96,165,250,.1);color:var(--blue);border:1px solid rgba(96,165,250,.2)}
.sf2-cat-badge.Valuation{background:rgba(251,191,36,.1);color:var(--amber);border:1px solid rgba(251,191,36,.2)}
.sf2-cat-badge.Qualitative{background:rgba(167,139,250,.1);color:var(--purple);border:1px solid rgba(167,139,250,.2)}
.sf2-cat-badge.Income{background:rgba(251,113,133,.1);color:#fb7185;border:1px solid rgba(251,113,133,.2)}
.sf2-live{display:inline-flex;align-items:center;gap:5px;font-size:.6rem;font-weight:700;
  letter-spacing:1px;text-transform:uppercase;padding:3px 9px;border-radius:20px;
  background:rgba(52,211,153,.08);border:1px solid rgba(52,211,153,.2);color:var(--green)}
.sf2-live-dot{width:5px;height:5px;border-radius:50%;background:var(--green);
  animation:sfDotPulse 1.8s ease-in-out infinite}

/* Score ring */
.sf2-ring{position:relative;width:72px;height:72px;flex-shrink:0}
.sf2-ring svg{width:72px;height:72px;transform:rotate(-90deg)}
.sf2-ring .rt{fill:none;stroke:rgba(255,255,255,.06);stroke-width:5}
.sf2-ring .rf{fill:none;stroke-width:5;stroke-linecap:round;stroke-dasharray:188;
  stroke-dashoffset:188;transition:stroke-dashoffset 1.3s cubic-bezier(.4,0,.2,1)}
.sf2-ring .rf.excellent{stroke:var(--green);filter:drop-shadow(0 0 5px rgba(52,211,153,.6))}
.sf2-ring .rf.good{stroke:var(--blue);filter:drop-shadow(0 0 5px rgba(96,165,250,.6))}
.sf2-ring .rf.watch{stroke:var(--amber);filter:drop-shadow(0 0 5px rgba(251,191,36,.6))}
.sf2-ring .rf.weak{stroke:var(--red);filter:drop-shadow(0 0 5px rgba(248,113,113,.6))}
.sf2-ring-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
.sf2-ring-val{font-size:1.05rem;font-weight:900;line-height:1}
.sf2-ring-val.excellent{color:var(--green)} .sf2-ring-val.good{color:var(--blue)}
.sf2-ring-val.watch{color:var(--amber)}     .sf2-ring-val.weak{color:var(--red)}
.sf2-ring-sub{font-size:.5rem;color:var(--muted);letter-spacing:.5px;margin-top:1px}

/* Card title + short */
.sf2-card-title{font-size:1.05rem;font-weight:700;color:#f1f5f9;margin:0 0 5px;line-height:1.3}
.sf2-card-short{font-size:.76rem;color:var(--muted);margin-bottom:18px}

/* Threshold */
.sf2-threshold{display:flex;align-items:center;justify-content:space-between;
  background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.05);
  border-radius:10px;padding:9px 13px;margin-bottom:14px}
.sf2-thr-lbl{font-size:.66rem;text-transform:uppercase;letter-spacing:1px;color:var(--muted)}
.sf2-thr-val{font-size:.84rem;font-weight:700;color:var(--gold);font-family:'Courier New',monospace}

/* Progress bar */
.sf2-bar-wrap{height:4px;background:rgba(255,255,255,.05);border-radius:10px;overflow:hidden;margin-bottom:16px}
.sf2-bar-fill{height:100%;border-radius:10px;width:0;transition:width 1.4s cubic-bezier(.4,0,.2,1)}
.sf2-bar-fill.excellent{background:linear-gradient(90deg,var(--green),#10b981)}
.sf2-bar-fill.good{background:linear-gradient(90deg,var(--blue),#3b82f6)}
.sf2-bar-fill.watch{background:linear-gradient(90deg,var(--amber),#f59e0b)}
.sf2-bar-fill.weak{background:linear-gradient(90deg,var(--red),#ef4444)}

/* Explanation toggle */
.sf2-toggle{display:flex;align-items:center;gap:7px;font-size:.75rem;color:var(--muted);
  cursor:pointer;background:none;border:none;padding:0;transition:color .2s;font-family:inherit}
.sf2-toggle:hover{color:var(--gold)}
.sf2-toggle i{transition:transform .3s;font-size:.65rem}
.sf2-toggle.open i{transform:rotate(180deg)}
.sf2-explain{max-height:0;overflow:hidden;transition:max-height .4s ease}
.sf2-explain.open{max-height:140px}
.sf2-explain-text{padding-top:12px;font-size:.82rem;color:#94a3b8;line-height:1.75;
  border-top:1px solid rgba(255,255,255,.05);margin-top:12px}

/* Empty state */
.sf2-empty{text-align:center;padding:80px 0;color:var(--muted)}
.sf2-empty i{font-size:2.5rem;opacity:.25;display:block;margin-bottom:14px}

/* Responsive */
@media(max-width:900px){.sf2-main{padding:24px 18px} .sf2-grid{grid-template-columns:1fr} .sf2-controls{flex-direction:column;align-items:stretch}}
@media(max-width:600px){.sf2-stats{flex-direction:column} .sf2-search{min-width:0}}
</style>

<div class="sf2-bg"></div>
<div class="sf2-wrap">
  <?php include 'includes/sidebar.php'; ?>
  <div class="sf2-main">

    <!-- Hero -->
    <div class="sf2-hero">
      <div class="sf2-eyebrow"><span class="sf2-eyebrow-dot"></span><i class="fas fa-sliders"></i> Premium Filter System</div>
      <h1 class="sf2-title">Stock Filter Criteria</h1>
      <p class="sf2-sub">WiseInvest ၏ quantitative screening framework — ကုမ္ပဏီများကို စစ်ဆေးသော မူဘောင်များ</p>
    </div>

    <!-- Stats -->
    <div class="sf2-stats">
      <div class="sf2-stat">
        <div class="sf2-stat-icon gold"><i class="fas fa-layer-group"></i></div>
        <div><div class="sf2-stat-num" data-target="<?= count($filters) ?>">0</div><div class="sf2-stat-lbl">Total Filters</div></div>
      </div>
      <div class="sf2-stat">
        <div class="sf2-stat-icon green"><i class="fas fa-database"></i></div>
        <div><div class="sf2-stat-num" data-target="<?= $liveCount ?>">0</div><div class="sf2-stat-lbl">Live Screener</div></div>
      </div>
      <div class="sf2-stat">
        <div class="sf2-stat-icon blue"><i class="fas fa-trophy"></i></div>
        <div><div class="sf2-stat-num" data-target="<?= $excellentCt ?>">0</div><div class="sf2-stat-lbl">Excellent Score</div></div>
      </div>
      <div class="sf2-stat">
        <div class="sf2-stat-icon cyan"><i class="fas fa-chart-bar"></i></div>
        <div><div class="sf2-stat-num" data-target="<?= $avgScore ?>">0</div><div class="sf2-stat-lbl">Avg Score</div></div>
      </div>
    </div>

    <!-- Controls -->
    <div class="sf2-controls">
      <div class="sf2-tabs" id="sfTabs">
        <button class="sf2-tab active" data-cat="All">All <span class="sf2-tab-n"><?= count($filters) ?></span></button>
        <?php foreach($categories as $cat):
          $n = count(array_filter($filters, fn($f) => $f['category'] === $cat)); ?>
        <button class="sf2-tab" data-cat="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?> <span class="sf2-tab-n"><?= $n ?></span></button>
        <?php endforeach; ?>
      </div>
      <div class="sf2-search">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text" id="sfSearch" placeholder="Search filters…" oninput="sfDoSearch(this.value)">
      </div>
    </div>

    <div class="sf2-count" id="sfCount"><strong><?= count($filters) ?></strong> filters shown</div>

    <!-- Grid -->
    <div class="sf2-grid" id="sfGrid">
      <?php foreach($filters as $i => $f):
        $score   = $f['score'];
        $color   = score_color($score);
        $catSlug = str_replace(' ', '-', $f['category']);
        $circ    = 188;
        $offset  = round($circ - ($score/100)*$circ, 1);
        $accentMap = ['Growth'=>'#22d3ee','Profitability'=>'#34d399','Financial-Strength'=>'#60a5fa','Valuation'=>'#fbbf24','Qualitative'=>'#a78bfa','Income'=>'#fb7185'];
        $accent  = $accentMap[$catSlug] ?? '#d4af37';
      ?>
      <div class="sf2-card" data-category="<?= htmlspecialchars($f['category']) ?>" style="animation-delay:<?= $i*0.04 ?>s">
        <div class="sf2-card-accent" style="background:linear-gradient(90deg,<?= $accent ?>,<?= $accent ?>44,transparent)"></div>
        <div class="sf2-card-body">
          <div class="sf2-card-top">
            <div class="sf2-badge-col">
              <span class="sf2-cat-badge <?= $catSlug ?>"><?= htmlspecialchars($f['category']) ?></span>
              <?php if(!empty($f['live'])): ?>
              <span class="sf2-live"><span class="sf2-live-dot"></span> Live Screener</span>
              <?php endif; ?>
            </div>
            <div class="sf2-ring">
              <svg viewBox="0 0 72 72"><circle class="rt" cx="36" cy="36" r="30"/><circle class="rf <?= $color ?>" cx="36" cy="36" r="30" data-offset="<?= $offset ?>"/></svg>
              <div class="sf2-ring-center">
                <div class="sf2-ring-val <?= $color ?>"><?= $score ?></div>
                <div class="sf2-ring-sub">/100</div>
              </div>
            </div>
          </div>
          <div class="sf2-card-title"><?= htmlspecialchars($f['title']) ?></div>
          <div class="sf2-card-short"><?= htmlspecialchars($f['short']) ?></div>
          <div class="sf2-threshold">
            <span class="sf2-thr-lbl">Threshold</span>
            <span class="sf2-thr-val"><?= htmlspecialchars($f['threshold']) ?></span>
          </div>
          <div class="sf2-bar-wrap"><div class="sf2-bar-fill <?= $color ?>" data-width="<?= $score ?>"></div></div>
          <button class="sf2-toggle" onclick="sfToggle(this)">
            <i class="fas fa-chevron-down"></i> မြန်မာဘာသာ ရှင်းလင်းချက်
          </button>
          <div class="sf2-explain">
            <div class="sf2-explain-text"><?= htmlspecialchars($f['explanation_mm']) ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="sf2-empty" id="sfEmpty" style="display:none">
      <i class="fas fa-magnifying-glass"></i>
      <div style="font-size:1rem;font-weight:600;margin-bottom:6px">No filters match</div>
      <div style="font-size:.85rem">Try a different search term</div>
    </div>

  </div>
</div>

<script>
let sfActiveCategory = 'All';

/* ── Tab click ── */
document.querySelectorAll('.sf2-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.sf2-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    sfActiveCategory = tab.dataset.cat;
    document.getElementById('sfSearch').value = '';
    sfApply();
  });
});

function sfApply(query) {
  const q = (query || '').toLowerCase().trim();
  let visible = 0;
  document.querySelectorAll('.sf2-card').forEach(card => {
    const catOk  = sfActiveCategory === 'All' || card.dataset.category === sfActiveCategory;
    const textOk = !q || card.textContent.toLowerCase().includes(q);
    const show   = catOk && textOk;
    card.classList.toggle('hidden', !show);
    if(show) {
      visible++;
      card.style.animationDelay = (visible * 0.04) + 's';
      card.style.animation = 'none';
      card.offsetHeight;
      card.style.animation = '';
    }
  });
  document.getElementById('sfCount').innerHTML = '<strong>' + visible + '</strong> filter' + (visible !== 1 ? 's' : '') + ' shown';
  document.getElementById('sfEmpty').style.display = visible ? 'none' : 'block';
}

function sfDoSearch(val) { sfApply(val); }

function sfToggle(btn) {
  btn.classList.toggle('open');
  btn.nextElementSibling.classList.toggle('open');
}

/* ── Intersection Observer for card reveal ── */
const sfObs = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting) { e.target.classList.add('visible'); sfObs.unobserve(e.target); } });
}, { threshold: 0.08 });
document.querySelectorAll('.sf2-card').forEach(c => sfObs.observe(c));

/* ── Animate rings + bars ── */
function sfAnimateCharts() {
  document.querySelectorAll('.rf').forEach(r => {
    setTimeout(() => { r.style.strokeDashoffset = r.dataset.offset; }, 300);
  });
  document.querySelectorAll('.sf2-bar-fill').forEach(b => {
    setTimeout(() => { b.style.width = b.dataset.width + '%'; }, 300);
  });
}

/* ── Count-up stats ── */
function sfCountUp() {
  document.querySelectorAll('.sf2-stat-num[data-target]').forEach(el => {
    const target = parseInt(el.dataset.target);
    const dur = 900, t0 = performance.now();
    (function step(now) {
      const p = Math.min((now - t0) / dur, 1);
      el.textContent = Math.round(p * target);
      if(p < 1) requestAnimationFrame(step);
    })(performance.now());
  });
}

if(document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => { sfAnimateCharts(); sfCountUp(); });
} else { sfAnimateCharts(); sfCountUp(); }
</script>
<?php include 'includes/footer.php'; ?>
