<?php
include 'includes/db.php';
include 'includes/header.php'; 
include 'includes/functions.php';

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'premium') {
    echo "<script>window.location.href='services.php';</script>";
    exit();
}
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body {
    background: #0b0f14;
    color: #e5e7eb;
    font-family: 'Inter', sans-serif;
}

.terminal {
    max-width: 1550px;
    margin: 20px auto;
    padding: 0 15px;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.brand {
    font-size: 20px;
    font-weight: 700;
    color: #f9fafb;
    letter-spacing: 1px;
}

.brand span {
    color: #d4af37;
}

.controls {
    display: flex;
    gap: 10px;
}

.ctrl-btn {
    background: #0b0f14;
    border: 1px solid #1f2933;
    color: #cbd5e1;
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 4px;
    cursor: pointer;
}

.ctrl-btn.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.panel {
    background: #11161d;
    border: 1px solid #1f2933;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;
}

.add-form {
    display: flex;
    gap: 8px;
}

.add-form input {
    background: #0b0f14;
    border: 1px solid #1f2933;
    color: #e5e7eb;
    padding: 6px 10px;
    font-size: 12px;
    border-radius: 4px;
    font-family: 'JetBrains Mono', monospace;
}

.add-form button {
    background: #2563eb;
    border: none;
    color: white;
    padding: 7px 14px;
    font-size: 12px;
    border-radius: 4px;
    cursor: pointer;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

th {
    text-align: left;
    font-weight: 600;
    color: #94a3b8;
    padding: 10px;
    border-bottom: 1px solid #1f2933;
}

td {
    padding: 10px;
    border-bottom: 1px solid #1f2933;
    font-family: 'JetBrains Mono', monospace;
}

tr:hover {
    background: rgba(37,99,235,0.08);
}

.profit-pos { color: #22c55e; }
.profit-neg { color: #ef4444; }

.symbol {
    color: #d4af37;
    cursor: pointer;
    font-weight: 600;
}
</style>

<div class="terminal">

    <div class="top-bar">
        <div class="brand">WISE INVEST <span>TERMINAL</span></div>
        <div class="controls">
            <button class="ctrl-btn active" id="curUSD">USD</button>
            <button class="ctrl-btn" id="curMMK">MMK</button>
            <button class="ctrl-btn" id="curTHB">THB</button>

            <button class="ctrl-btn active" id="sessionRegular">REG</button>
            <button class="ctrl-btn" id="sessionPre">PRE</button>
            <button class="ctrl-btn" id="sessionPost">AFT</button>
        </div>
    </div>

    <div class="panel">
        <div class="add-form">
            <input id="inTicker" placeholder="AAPL">
            <input id="inQty" type="number" placeholder="Shares">
            <input id="inPrice" type="number" placeholder="Avg Cost">
            <button id="btnAdd">ADD</button>
        </div>
    </div>

    <div class="panel">
        <table>
            <thead>
                <tr>
                    <th>Symbol</th>
                    <th>Shares</th>
                    <th>Live Price</th>
                    <th>P/L ($)</th>
                    <th>P/L (%)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="portfolioBody"></tbody>
        </table>
    </div>

</div>
<!-- Candle Modal -->
<div id="candleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:9999;">
    <div style="max-width:900px; margin:50px auto; background:#11161d; border:1px solid #1f2933; border-radius:8px; padding:20px;">
        <h3 id="candleTitle" style="color:#d4af37; text-align:center; margin-bottom:10px;"></h3>
        <canvas id="candleChart"></canvas>
        <div style="text-align:center; margin-top:10px;">
            <button onclick="closeCandle()" class="ctrl-btn">Close</button>
        </div>
    </div>
</div>

<script>
let portfolio = JSON.parse(localStorage.getItem('wise_terminal')) || [];
let currentCurrency = 'USD';
let currentSession = 'regular';
let fxRates = { USD:1, MMK:1, THB:1 };
let candleChart = null;

async function loadFX() {
    const res = await fetch("https://query1.finance.yahoo.com/v7/finance/quote?symbols=USDMMK=X,USDTHB=X");
    const data = await res.json();
    data.quoteResponse.result.forEach(q => {
        if(q.symbol === "USDMMK=X") fxRates.MMK = q.regularMarketPrice;
        if(q.symbol === "USDTHB=X") fxRates.THB = q.regularMarketPrice;
    });
}

async function getPrice(symbol) {
    const res = await fetch(`/api/yahoo_proxy.php?ticker=${symbol}`);
    const data = await res.json();
    return data.chart.result[0].meta.regularMarketPrice;
}

function convert(val) {
    return (val * fxRates[currentCurrency]).toFixed(2);
}

document.getElementById('btnAdd').onclick = () => {
    const t = inTicker.value.toUpperCase().trim();
    const q = parseFloat(inQty.value);
    const p = parseFloat(inPrice.value);
    if(!t || !q || !p) return alert("Fill all fields");
    portfolio.push({ ticker:t, qty:q, price:p });
    localStorage.setItem('wise_terminal', JSON.stringify(portfolio));
    render();
};

function removeStock(i) {
    portfolio.splice(i,1);
    localStorage.setItem('wise_terminal', JSON.stringify(portfolio));
    render();
}

async function render() {
    const body = document.getElementById("portfolioBody");
    body.innerHTML = "";

    for(let i=0;i<portfolio.length;i++){
        const s = portfolio[i];
        const live = await getPrice(s.ticker);
        const value = live * s.qty;
        const cost = s.price * s.qty;
        const pl = value - cost;
        const plp = (pl / cost) * 100;

        body.innerHTML += `
        <tr>
            <td class="symbol" onclick="openCandle('${s.ticker}')">${s.ticker}</td>
            <td>${s.qty}</td>
            <td>${convert(live)}</td>
            <td class="${pl>=0?'profit-pos':'profit-neg'}">${convert(pl)}</td>
            <td class="${plp>=0?'profit-pos':'profit-neg'}">${plp.toFixed(2)}%</td>
            <td><button class="ctrl-btn" onclick="removeStock(${i})">X</button></td>
        </tr>`;
    }
}

async function openCandle(symbol) {
    document.getElementById("candleModal").style.display = "block";
    document.getElementById("candleTitle").innerText = symbol + " • Intraday";

    const res = await fetch(`/api/yahoo_proxy.php?ticker=${symbol}`);
    const data = await res.json();
    const result = data.chart.result[0];

    const prices = result.indicators.quote[0].close;
    const labels = result.timestamp.map(t => new Date(t*1000).toLocaleTimeString());

    const ctx = document.getElementById("candleChart").getContext("2d");
    if(candleChart) candleChart.destroy();

    candleChart = new Chart(ctx, {
        type:'line',
        data:{
            labels: labels,
            datasets:[{
                data: prices,
                borderColor:'#2563eb',
                tension:0.3,
                pointRadius:0
            }]
        },
        options:{
            responsive:true,
            plugins:{legend:{display:false}},
            scales:{x:{display:false}}
        }
    });
}

function closeCandle(){
    document.getElementById("candleModal").style.display = "none";
}

// Currency buttons
curUSD.onclick = () => setCurrency('USD', curUSD);
curMMK.onclick = () => setCurrency('MMK', curMMK);
curTHB.onclick = () => setCurrency('THB', curTHB);

function setCurrency(cur, btn){
    currentCurrency = cur;
    document.querySelectorAll('#curUSD,#curMMK,#curTHB').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    render();
}

// Session buttons (UI ready for future extended feeds)
sessionRegular.onclick = () => setSession('regular', sessionRegular);
sessionPre.onclick = () => setSession('pre', sessionPre);
sessionPost.onclick = () => setSession('post', sessionPost);

function setSession(s, btn){
    currentSession = s;
    document.querySelectorAll('#sessionRegular,#sessionPre,#sessionPost').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    render();
}

// Init
loadFX().then(render);
setInterval(render, 30000);
</script>

<?php include 'includes/footer.php'; ?>
