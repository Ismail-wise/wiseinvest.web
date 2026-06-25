<?php
session_start();

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit();
}

include 'includes/header.php';
?>

<style>

body{
background:#020617;
color:white;
font-family:Segoe UI;
}

/* LAYOUT */

.dashboard-wrapper{
display:flex;
gap:30px;
padding:40px;
min-height:80vh;
}

.main{
flex:1;
}

/* PAGE */

.target-container{
max-width:1200px;
margin:auto;
}

/* TITLE */

.page-title{
font-size:34px;
font-weight:700;
margin-bottom:30px;
color:#d4af37;
}

/* GRID */

.level-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
gap:25px;
}

/* CARD */

.level-card{
padding:25px;
border-radius:18px;
border:1px solid #1e293b;
background:linear-gradient(145deg,#0f172a,#020617);
transition:0.3s;
position:relative;
overflow:hidden;
}

.level-card:hover{
transform:translateY(-6px);
box-shadow:0 15px 30px rgba(0,0,0,0.6);
}

/* LEFT COLOR BAR */

.level1{border-left:6px solid #22c55e;}
.level2{border-left:6px solid #eab308;}
.level3{border-left:6px solid #3b82f6;}
.level4{border-left:6px solid #ef4444;}

/* TITLE */

.level-title{
font-size:20px;
font-weight:700;
margin-bottom:8px;
}

/* RETURN BADGE */

.return-badge{
display:inline-block;
font-size:12px;
padding:4px 10px;
border-radius:20px;
margin-bottom:15px;
background:#111827;
color:#94a3b8;
}

/* DESCRIPTION */

.level-desc{
font-size:14px;
line-height:1.7;
color:#cbd5f5;
margin-bottom:18px;
}

/* STOCK TAGS */

.stock-list{
display:flex;
flex-wrap:wrap;
gap:10px;
}

.stock{
padding:6px 12px;
border-radius:20px;
font-size:13px;
font-weight:600;
background:#020617;
border:1px solid #1e293b;
transition:0.2s;
}

/* COLOR TAGS */

.level1 .stock{color:#22c55e;}
.level2 .stock{color:#eab308;}
.level3 .stock{color:#3b82f6;}
.level4 .stock{color:#ef4444;}

.stock:hover{
transform:scale(1.08);
background:#111827;
}

/* EXTRA GLOW EFFECT */

.level-card::after{
content:'';
position:absolute;
width:200px;
height:200px;
background:radial-gradient(circle,rgba(255,255,255,0.05),transparent);
top:-50px;
right:-50px;
}

</style>

<div class="dashboard-wrapper">

<?php include 'includes/sidebar.php'; ?>

<div class="main">

<div class="target-container">

<h2 class="page-title">🎯 Target Portfolio Strategy</h2>

<div class="level-grid">

<!-- LEVEL 1 -->
<div class="level-card level1">
<div class="level-title">🟢 Level 1: Beginner</div>
<div class="return-badge">8–10% Return</div>

<div class="level-desc">
Beginner Level က ရင်းနှီးမြှုပ်နှံမှုအသစ်စတဲ့သူတွေ အတွက်ဖြစ်ပြီး  
Risk နည်းပြီး Stable ETFs များကို အဓိကထားပါတယ်။  
Long-term Wealth တည်ဆောက်ဖို့ အကောင်းဆုံးပါ။
</div>

<div class="stock-list">
<span class="stock">VOO</span>
<span class="stock">QQQ</span>
<span class="stock">IVV</span>
</div>

</div>

<!-- LEVEL 2 -->
<div class="level-card level2">
<div class="level-title">🟡 Level 2: Balanced</div>
<div class="return-badge">10–12% Return</div>

<div class="level-desc">
Balanced Level မှာ ETFs + Tech Stocks တွေကို ပေါင်းပြီး  
Growth နဲ့ Stability ကို Balance လုပ်ထားပါတယ်။  
Moderate Risk ဖြစ်ပါတယ်။
</div>

<div class="stock-list">
<span class="stock">AAPL</span>
<span class="stock">MSFT</span>
<span class="stock">QQQ</span>
</div>

</div>

<!-- LEVEL 3 -->
<div class="level-card level3">
<div class="level-title">🔵 Level 3: Growth</div>
<div class="return-badge">12–15% Return</div>

<div class="level-desc">
Growth Level မှာ High-growth ETFs နဲ့  
Individual Stocks များကို ပိုပြီးထည့်ပါတယ်။  
Return မြင့်နိုင်ပေမယ့် Risk ပိုရှိပါတယ်။
</div>

<div class="stock-list">
<span class="stock">NVDA</span>
<span class="stock">AMZN</span>
<span class="stock">QQQ</span>
</div>

</div>

<!-- LEVEL 4 -->
<div class="level-card level4">
<div class="level-title">🔴 Level 4: Aggressive</div>
<div class="return-badge">15–20% Return</div>

<div class="level-desc">
Aggressive Level မှာ Individual Stocks များကို အဓိကထားပြီး  
High Risk / High Return Strategy ဖြစ်ပါတယ်။  
Volatility မြင့်ပြီး Short-term gains ရနိုင်ပါတယ်။
</div>

<div class="stock-list">
<span class="stock">TSLA</span>
<span class="stock">NVDA</span>
<span class="stock">META</span>
</div>

</div>

</div>

</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>