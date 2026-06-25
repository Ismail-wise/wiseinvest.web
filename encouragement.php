<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include 'includes/header.php';
?>

<style>
/* ════════════════════════════════════════
   ENCOURAGEMENT — INVESTOR MINDSET
   ════════════════════════════════════════ */
@keyframes enFadeUp  { from{opacity:0;transform:translateY(22px)} to{opacity:1;transform:translateY(0)} }
@keyframes enShimmer { from{background-position:-300% center} to{background-position:300% center} }
@keyframes enPulse   { 0%,100%{box-shadow:0 0 0 0 rgba(34,197,94,.3)} 70%{box-shadow:0 0 0 14px rgba(34,197,94,0)} }
@keyframes enOrb     { 0%,100%{transform:scale(1) translate(0,0)} 50%{transform:scale(1.12) translate(14px,-10px)} }
@keyframes enGlow    { 0%,100%{opacity:.6} 50%{opacity:1} }

.en-page { background: #020617; min-height: 100vh; }

/* ── Hero ── */
.en-hero {
    position: relative; background: #020617;
    padding: 80px 20px 70px; text-align: center; overflow: hidden;
}
.en-grid {
    position: absolute; inset: 0; pointer-events: none;
    background-image:
        linear-gradient(rgba(34,197,94,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(34,197,94,0.03) 1px, transparent 1px);
    background-size: 60px 60px;
}
.en-orb {
    position: absolute; border-radius: 50%; filter: blur(90px); pointer-events: none;
}
.en-orb-1 {
    width: 480px; height: 480px;
    background: radial-gradient(circle, rgba(34,197,94,0.1) 0%, transparent 70%);
    top: -150px; left: -100px; animation: enOrb 9s ease-in-out infinite;
}
.en-orb-2 {
    width: 360px; height: 360px;
    background: radial-gradient(circle, rgba(212,175,55,0.08) 0%, transparent 70%);
    bottom: -80px; right: -80px; animation: enOrb 11s ease-in-out infinite reverse;
}
.en-hero-inner { position: relative; z-index: 2; max-width: 700px; margin: 0 auto; }
.en-badge {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3);
    color: #4ade80; padding: 5px 18px; border-radius: 99px;
    font-size: 0.72rem; font-weight: 800; letter-spacing: 2px; text-transform: uppercase;
    margin-bottom: 20px; animation: enFadeUp 0.5s 0.1s ease both;
}
.en-title {
    font-size: clamp(2rem, 4.5vw, 3.2rem);
    font-weight: 900; color: #fff; margin: 0 0 14px; line-height: 1.1;
    animation: enFadeUp 0.6s 0.2s ease both;
}
.en-title-green {
    background: linear-gradient(135deg, #22c55e, #4ade80, #86efac, #22c55e);
    background-size: 200% auto;
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    animation: enShimmer 4s linear infinite;
}
.en-sub {
    color: #64748b; max-width: 520px; margin: 0 auto;
    line-height: 1.7; animation: enFadeUp 0.6s 0.3s ease both;
}

/* ── Body ── */
.en-body { padding: 60px 24px 80px; max-width: 1200px; margin: 0 auto; }

/* Quote grid */
.en-quotes {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 18px; margin-bottom: 52px;
}
.en-quote-card {
    border-radius: 18px; padding: 24px;
    position: relative; overflow: hidden;
    opacity: 0; transform: translateY(18px);
    transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s;
}
.en-quote-card.visible { opacity: 1; transform: translateY(0); }
.en-quote-card:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,0.4); }

.en-q-red    { background: linear-gradient(135deg, #450a0a, #7f1d1d); border: 1px solid rgba(239,68,68,0.2); }
.en-q-amber  { background: linear-gradient(135deg, #451a03, #78350f); border: 1px solid rgba(245,158,11,0.2); }
.en-q-green  { background: linear-gradient(135deg, #052e16, #14532d); border: 1px solid rgba(34,197,94,0.2); }
.en-q-blue   { background: linear-gradient(135deg, #0c1a45, #1e3a8a); border: 1px solid rgba(99,102,241,0.2); }

.en-q-emoji { font-size: 1.8rem; display: block; margin-bottom: 12px; }
.en-q-text  { font-size: 0.9rem; font-weight: 600; color: #e2e8f0; line-height: 1.6; margin: 0; }
.en-q-mm    { font-size: 0.85rem; font-weight: 600; color: #94a3b8; line-height: 1.65; margin: 0; }
.en-q-divider {
    height: 1px; background: rgba(255,255,255,0.08); margin: 10px 0;
}

/* Section header */
.en-sec-head { display: flex; align-items: center; gap: 16px; margin-bottom: 28px; }
.en-sec-head h3 { font-size: 1.1rem; font-weight: 800; color: #fff; margin: 0; white-space: nowrap; }
.en-sec-head::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(90deg, rgba(34,197,94,0.3), transparent);
}

/* MAGNET framework */
.en-magnet {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px; margin-bottom: 52px;
}
.en-mag-card {
    background: #0a1628; border: 1px solid rgba(255,255,255,0.07);
    border-radius: 16px; padding: 22px; text-align: center;
    opacity: 0; transform: translateY(18px);
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), border-color 0.3s;
}
.en-mag-card.visible { opacity: 1; transform: translateY(0); }
.en-mag-card:hover { transform: translateY(-5px); border-color: rgba(34,197,94,0.25); }
.en-mag-letter {
    font-size: 2rem; font-weight: 900; color: #22c55e;
    font-family: 'Playfair Display', Georgia, serif;
    display: block; margin-bottom: 8px;
}
.en-mag-label { font-size: 0.78rem; font-weight: 700; color: #fff; margin-bottom: 4px; }
.en-mag-sub   { font-size: 0.72rem; color: #64748b; }

/* RAS box */
.en-ras {
    background: linear-gradient(135deg, rgba(34,197,94,0.06), rgba(212,175,55,0.04));
    border: 1px solid rgba(34,197,94,0.15);
    border-radius: 20px; padding: 36px 32px; margin-bottom: 52px;
    opacity: 0; transform: translateY(18px);
}
.en-ras.visible { opacity: 1; transform: translateY(0); transition: opacity 0.5s ease, transform 0.5s ease; }
.en-ras-label {
    font-size: 0.7rem; font-weight: 800; color: #4ade80;
    letter-spacing: 2px; text-transform: uppercase; margin-bottom: 12px;
}
.en-ras h3 { font-size: 1.2rem; font-weight: 800; color: #fff; margin: 0 0 14px; }
.en-ras p  { color: #64748b; line-height: 1.8; margin: 0; font-size: 0.95rem; }

/* PDF section */
.en-pdf-card {
    background: #0a1628; border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px; padding: 32px; overflow: hidden;
}
.en-pdf-label {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 20px;
}
.en-pdf-icon {
    width: 44px; height: 44px; border-radius: 12px;
    background: rgba(212,175,55,0.1); border: 1px solid rgba(212,175,55,0.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: #d4af37;
}
.en-pdf-label h4 { color: #fff; font-size: 1rem; font-weight: 800; margin: 0; }
.en-pdf-label p  { color: #64748b; font-size: 0.8rem; margin: 0; }
.en-pdf-iframe {
    width: 100%; height: 600px; border: none;
    border-radius: 12px; background: #020617;
}

@media (max-width: 640px) {
    .en-magnet { grid-template-columns: repeat(3, 1fr); }
    .en-body { padding: 40px 16px 60px; }
}
</style>

<div class="en-page">

<!-- HERO -->
<section class="en-hero">
    <div class="en-grid"></div>
    <div class="en-orb en-orb-1"></div>
    <div class="en-orb en-orb-2"></div>

    <div class="en-hero-inner">
        <div class="en-badge"><i class="fas fa-brain"></i> Mindset Training</div>
        <h1 class="en-title">Investor <span class="en-title-green">Mindset</span> Training</h1>
        <p class="en-sub">
            ရင်းနှီးမြှုပ်နှံမှုမှာ အောင်မြင်ဖို့ဆိုတာ Strategy ထက် Mindset ပိုအရေးကြီးပါတယ်။
            Fear ကိုထိန်းနိုင်ရင် Opportunity ကိုမြင်နိုင်ပါတယ်။
        </p>
    </div>
</section>

<!-- BODY -->
<div class="en-body">

    <!-- Quotes -->
    <div class="en-sec-head">
        <i class="fas fa-quote-left" style="color:#22c55e;"></i>
        <h3>Key Mindset Principles</h3>
    </div>

    <div class="en-quotes">
        <div class="en-quote-card en-q-red" data-reveal>
            <span class="en-q-emoji">❌</span>
            <p class="en-q-text">Fear → Panic Sell</p>
            <div class="en-q-divider"></div>
            <p class="en-q-mm">ကြောက်ရွံ့မှု → လွယ်လွယ်နဲ့ရောင်းခြင်း</p>
        </div>
        <div class="en-quote-card en-q-amber" data-reveal>
            <span class="en-q-emoji">⚠️</span>
            <p class="en-q-text">Emotion Control is everything for an investor</p>
            <div class="en-q-divider"></div>
            <p class="en-q-mm">Investor တစ်ယောက်အတွက် စိတ်ထိန်းချုပ်မှုက အဓိကပါ</p>
        </div>
        <div class="en-quote-card en-q-green" data-reveal>
            <span class="en-q-emoji">✅</span>
            <p class="en-q-text">Calm Mind → Opportunity</p>
            <div class="en-q-divider"></div>
            <p class="en-q-mm">စိတ်တည်ငြိမ်မှ အခွင့်အရေးတွေမြင်နိုင်တယ်</p>
        </div>
        <div class="en-quote-card en-q-blue" data-reveal>
            <span class="en-q-emoji">🚀</span>
            <p class="en-q-text">Train your mind like you train your skills</p>
            <div class="en-q-divider"></div>
            <p class="en-q-mm">Investor ဆိုတာ စိတ်ကို Train လုပ်ထားရတယ်</p>
        </div>
    </div>

    <!-- RAS -->
    <div class="en-ras" data-reveal>
        <div class="en-ras-label"><i class="fas fa-brain"></i> RAS — Brain Filtering System</div>
        <h3>Your Brain Shows What You Focus On</h3>
        <p>
            သင် focus လုပ်တဲ့အရာကိုပဲ brain ကပိုမြင်စေပါတယ်။ Opportunity ကိုရှာမယ်ဆိုရင် Opportunity တွေကိုပဲမြင်လာမယ်။
            Fear ကိုပဲ focus လုပ်ရင် Fear တွေကိုပဲမြင်နေမှာပါ။ Your attention shapes your reality.
        </p>
    </div>

    <!-- MAGNET -->
    <div class="en-sec-head">
        <i class="fas fa-magnet" style="color:#d4af37;"></i>
        <h3>MAGNET Framework</h3>
    </div>

    <?php
    $magnet = [
        ['M','Mind Conditioning','Train your mindset daily'],
        ['A','Attention & Environment','Curate what surrounds you'],
        ['G','Gratitude','Appreciate your journey'],
        ['N','Visualization','See your goals vividly'],
        ['E','Positive Energy','Radiate what you attract'],
        ['T','Thought → Action','Execute, not just think'],
    ];
    ?>
    <div class="en-magnet">
    <?php foreach ($magnet as $i => $m): ?>
    <div class="en-mag-card" data-reveal data-delay="<?php echo $i * 70; ?>">
        <span class="en-mag-letter"><?php echo $m[0]; ?></span>
        <div class="en-mag-label"><?php echo $m[1]; ?></div>
        <div class="en-mag-sub"><?php echo $m[2]; ?></div>
    </div>
    <?php endforeach; ?>
    </div>

    <!-- PDF -->
    <div class="en-sec-head">
        <i class="fas fa-file-pdf" style="color:#d4af37;"></i>
        <h3>Mindset Guide</h3>
    </div>

    <div class="en-pdf-card">
        <div class="en-pdf-label">
            <div class="en-pdf-icon"><i class="fas fa-book-open"></i></div>
            <div>
                <h4>Mindset Guide PDF</h4>
                <p>Investor psychology & mental frameworks</p>
            </div>
        </div>
        <iframe src="uploads/mindset.pdf" class="en-pdf-iframe"></iframe>
    </div>

</div><!-- /.en-body -->
</div><!-- /.en-page -->

<script>
(function () {
    var els = document.querySelectorAll('[data-reveal]');
    if (!els.length) return;
    var obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                var d = parseInt(e.target.dataset.delay) || 0;
                setTimeout(function () { e.target.classList.add('visible'); }, d);
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });
    els.forEach(function (el) { obs.observe(el); });
}());
</script>

<?php include 'includes/footer.php'; ?>
