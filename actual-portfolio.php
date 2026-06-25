<?php
session_start();

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit();
}

require_once 'includes/db.php';

$user_id = $_SESSION['user_id'];

if($_SERVER['REQUEST_METHOD']=='POST'){

$symbol = strtoupper(trim($_POST['symbol']));
$shares = floatval($_POST['shares']);
$price  = floatval($_POST['price']);
$type   = $_POST['type'];

$stmt = $conn->prepare("INSERT INTO user_portfolio(user_id,symbol,shares,avg_price,asset_type) VALUES(?,?,?,?,?)");
$stmt->bind_param("isdds",$user_id,$symbol,$shares,$price,$type);
$stmt->execute();

header("Location: portfolio.php");
exit();
}

$result = $conn->query("SELECT * FROM user_portfolio WHERE user_id=$user_id");

$symbols=[];
$values=[];
$total_cost=0;

include 'includes/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
background:#020617;
color:white;
font-family:Segoe UI;
}

/* DASHBOARD LAYOUT */

.dashboard-wrapper{
display:flex;
gap:30px;
padding:40px;
min-height:80vh;
}

.sidebar{
width:220px;
background:#0f172a;
padding:30px;
border-radius:12px;
height:fit-content;
}

.sidebar a{
display:block;
padding:12px 0;
color:#94a3b8;
text-decoration:none;
}

.sidebar a:hover{
color:#d4af37;
}

.main{
flex:1;
}

.portfolio-container{
max-width:1100px;
}

.page-title{
font-size:32px;
font-weight:700;
margin-bottom:30px;
color:#d4af37;
}

/* STATS */

.stats{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:20px;
margin-bottom:30px;
}

.stat-card{
background:#0f172a;
padding:20px;
border-radius:12px;
text-align:center;
border:1px solid #1e293b;
}

/* TABLE */

.portfolio-card{
background:#0f172a;
padding:30px;
border-radius:16px;
border:1px solid #1e293b;
}

table{
width:100%;
border-collapse:collapse;
}

th{
text-align:left;
padding:14px;
color:#94a3b8;
border-bottom:1px solid #1e293b;
}

td{
padding:14px;
border-bottom:1px solid #1e293b;
}

tr:hover{
background:#111c34;
}

.actions a{
margin-right:10px;
color:#d4af37;
}

/* BUTTON */

.btn-add{
background:#d4af37;
border:none;
padding:10px 18px;
border-radius:8px;
color:black;
font-weight:600;
cursor:pointer;
}

/* CHARTS */

.charts{
display:grid;
grid-template-columns:1fr 1fr;
gap:30px;
margin-top:40px;
}

.chart-box{
background:#0f172a;
padding:25px;
border-radius:14px;
border:1px solid #1e293b;
}

/* MODAL */

.modal{
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.6);
display:none;
justify-content:center;
align-items:center;
}

.modal-box{
background:#0f172a;
padding:30px;
border-radius:12px;
width:400px;
}

.modal input,.modal select{
width:100%;
padding:10px;
margin-bottom:15px;
background:#020617;
border:1px solid #1e293b;
color:white;
border-radius:6px;
}

.btn-save{
background:#22c55e;
border:none;
padding:10px 15px;
border-radius:8px;
cursor:pointer;
}

.close-btn{
float:right;
cursor:pointer;
font-size:20px;
}

/* EDUCATION SECTION */

.edu-section{
margin-top:60px;
}

.edu-title{
font-size:26px;
font-weight:700;
margin-bottom:20px;
}

.edu-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
gap:20px;
}

.edu-card{
background:#0f172a;
border:1px solid #1e293b;
border-radius:12px;
padding:20px;
}

.edu-card h3{
color:#22c55e;
margin-bottom:10px;
}

.edu-card p{
font-size:14px;
line-height:1.6;
color:#cbd5f5;
}

</style>


<div class="dashboard-wrapper">

<?php include 'includes/sidebar.php'; ?>


<div class="main">

<div class="portfolio-container">

<h2 class="page-title">Your Portfolio</h2>

<?php

$result_stats = $conn->query("SELECT * FROM user_portfolio WHERE user_id=$user_id");

while($row = $result_stats->fetch_assoc()){

$cost = $row['shares'] * $row['avg_price'];
$total_cost += $cost;

$symbols[] = $row['symbol'];
$values[] = $cost;

}

?>

<div class="stats">

<div class="stat-card">
<h3>Total Cost</h3>
<p>$<?php echo number_format($total_cost,2); ?></p>
</div>

<div class="stat-card">
<h3>Total Value</h3>
<p id="portfolioValue">Loading...</p>
</div>

<div class="stat-card">
<h3>Profit / Loss</h3>
<p id="portfolioProfit">Loading...</p>
</div>

</div>


<div class="portfolio-card">

<button class="btn-add" onclick="openModal()">+ Add Asset</button>

<table>

<tr>
<th>Symbol</th>
<th>Shares</th>
<th>Avg Price</th>
<th>Live Price</th>
<th>Value</th>
<th>Actions</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>

<tr>

<td><?php echo $row['symbol']; ?></td>
<td class="shares"><?php echo $row['shares']; ?></td>
<td class="avg">$<?php echo $row['avg_price']; ?></td>
<td class="live-price" data-symbol="<?php echo $row['symbol']; ?>">Loading...</td>
<td class="asset-value">Loading...</td>

<td class="actions">
<a href="edit-asset.php?id=<?php echo $row['id']; ?>">Edit</a>
<a href="delete-asset.php?id=<?php echo $row['id']; ?>">Delete</a>
</td>

</tr>

<?php endwhile; ?>

</table>

</div>


<div class="charts">

<div class="chart-box">
<h3>Asset Allocation</h3>
<canvas id="allocationChart"></canvas>
</div>

<div class="chart-box">
<h3>Portfolio Performance</h3>
<canvas id="performanceChart"></canvas>
</div>

</div>


<!-- PORTFOLIO EDUCATION -->

<div class="edu-section">

<div class="edu-title">Portfolio ဆိုတာဘာလဲ?</div>

<div class="edu-grid">

<div class="edu-card">
<h3>Portfolio ဆိုတာ</h3>
<p>
Portfolio ဆိုတာက Stocks, ETFs, Crypto စတဲ့ ရင်းနှီးမြှုပ်နှံမှုများကို
ပေါင်းစည်းထားတဲ့ သင့်ရဲ့ ရင်းနှီးမြှုပ်နှံမှုစုစုပေါင်းဖြစ်ပါတယ်။
</p>
</div>

<div class="edu-card">
<h3>Diversification</h3>
<p>
Diversification ဆိုတာက Asset တစ်ခုထဲမှာပဲ မရင်းနှီးဘဲ
Asset အမျိုးမျိုးထဲမှာ ခွဲခြားပြီး ရင်းနှီးမြှုပ်နှံခြင်းဖြစ်ပါတယ်။
ဒါက Risk ကိုလျှော့ချပေးပါတယ်။
</p>
</div>

<div class="edu-card">
<h3>Asset Allocation</h3>
<p>
Portfolio ထဲမှာ Stocks, ETFs, Crypto, Cash စတဲ့
Asset များကို ဘယ်လောက်ရာခိုင်နှုန်းထားမလဲဆိုတာကို
Asset Allocation လို့ခေါ်ပါတယ်။
</p>
</div>

<div class="edu-card">
<h3>Risk Management</h3>
<p>
Risk Management ဆိုတာက Portfolio ကို Balance ဖြစ်အောင်
Asset များကို စနစ်တကျ စီမံခြင်းဖြစ်ပါတယ်။
</p>
</div>

<div class="edu-card">
<h3>Long-term Investing</h3>
<p>
Portfolio Investing က Long-term Wealth တည်ဆောက်ဖို့
အထောက်အကူပြုပါတယ်။
</p>
</div>

<div class="edu-card">
<h3>Portfolio Monitoring</h3>
<p>
Portfolio ကို အချိန်အခါအလိုက် စစ်ဆေးပြီး
Performance ကို စောင့်ကြည့်သင့်ပါတယ်။
</p>
</div>

</div>

</div>


</div>

</div>

</div>


<div class="modal" id="assetModal">

<div class="modal-box">

<span class="close-btn" onclick="closeModal()">×</span>

<h3>Add Asset</h3>

<form method="POST">

<input type="text" name="symbol" placeholder="Symbol (AAPL)" required>

<input type="number" step="0.0001" name="shares" placeholder="Shares" required>

<input type="number" step="0.01" name="price" placeholder="Average Price" required>

<select name="type">
<option value="stock">Stock</option>
<option value="etf">ETF</option>
<option value="crypto">Crypto</option>
</select>

<button class="btn-save">Save Asset</button>

</form>

</div>

</div>

<script>

function openModal(){
document.getElementById('assetModal').style.display='flex';
}

function closeModal(){
document.getElementById('assetModal').style.display='none';
}

const symbols = <?php echo json_encode($symbols); ?>;
const values = <?php echo json_encode($values); ?>;

new Chart(document.getElementById('allocationChart'),{
type:'doughnut',
data:{
labels:symbols,
datasets:[{
data:values,
backgroundColor:['#d4af37','#22c55e','#3b82f6','#f59e0b','#8b5cf6']
}]
}
});

new Chart(document.getElementById('performanceChart'),{
type:'line',
data:{
labels:["Start","Now"],
datasets:[{
label:"Portfolio Cost",
data:[0,<?php echo $total_cost ?>],
borderColor:'#22c55e',
backgroundColor:'rgba(34,197,94,0.2)',
tension:0.4
}]
}
});

let portfolioValue=0;

document.querySelectorAll("table tr").forEach(function(row){

let priceCell=row.querySelector(".live-price");
if(!priceCell) return;

let symbol=priceCell.dataset.symbol;
let shares=parseFloat(row.querySelector(".shares").innerText);

fetch("get-price.php?symbol="+symbol)
.then(res=>res.json())
.then(data=>{

let price=parseFloat(data.price);

if(price>0){

priceCell.innerHTML="$"+price.toFixed(2);

let value=shares*price;

row.querySelector(".asset-value").innerHTML="$"+value.toFixed(2);

portfolioValue+=value;

document.getElementById("portfolioValue").innerHTML="$"+portfolioValue.toFixed(2);

let totalCost=<?php echo $total_cost ?>;

let profit=portfolioValue-totalCost;

let profitEl=document.getElementById("portfolioProfit");

profitEl.innerHTML="$"+profit.toFixed(2);

profitEl.style.color=profit>=0?"#22c55e":"#ef4444";

}else{

priceCell.innerHTML="N/A";

}

});

});

</script>

<?php include 'includes/footer.php'; ?>