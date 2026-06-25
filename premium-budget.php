<?php
session_start();
include_once 'includes/db.php';

/* ================= SAVE ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_budget') {

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
echo json_encode(["status"=>"error","msg"=>"User not logged in"]);
exit();
}

$uid = intval($_SESSION['user_id']);
$month = $_POST['month'];
$goal = floatval($_POST['goal']);
$transactions = json_encode(json_decode($_POST['transactions']), JSON_UNESCAPED_UNICODE);

/* Create table if not exists */
$conn->query("CREATE TABLE IF NOT EXISTS user_budgets(
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
month_year VARCHAR(7) NOT NULL,
savings_goal DECIMAL(10,2),
transactions JSON,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
UNIQUE KEY(user_id,month_year)
)");

$stmt = $conn->prepare("INSERT INTO user_budgets(user_id,month_year,savings_goal,transactions)
VALUES(?,?,?,?)
ON DUPLICATE KEY UPDATE
savings_goal=VALUES(savings_goal),
transactions=VALUES(transactions)");

$stmt->bind_param("isds",$uid,$month,$goal,$transactions);

if($stmt->execute()){
echo json_encode(["status"=>"success"]);
}else{
echo json_encode(["status"=>"error"]);
}

exit();
}

/* ================= LOAD ================= */
if(($_GET['action'] ?? '') === 'load_budget'){

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
echo json_encode(["status"=>"error"]);
exit();
}

$uid = intval($_SESSION['user_id']);
$month = $_GET['month'];

$stmt = $conn->prepare("SELECT savings_goal,transactions FROM user_budgets
WHERE user_id=? AND month_year=?");

$stmt->bind_param("is",$uid,$month);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){

echo json_encode([
"status"=>"found",
"goal"=>$row['savings_goal'],
"transactions"=>json_decode($row['transactions'])
]);

}else{

echo json_encode(["status"=>"empty"]);

}

exit();
}

include_once 'includes/header.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'premium') {
echo "<script>location.href='services.php';</script>";
exit();
}
?>

<style>

:root{
--gold:#d4af37;
--dark:#0f172a;
--bg:#f1f5f9;
}

body{
background:var(--bg);
font-family:system-ui;
}

.premium-hero{
background:linear-gradient(135deg,#020617,#0f172a);
padding:60px;
color:white;
text-align:center;
border-bottom:3px solid var(--gold);
}

.budget-container{
max-width:1200px;
margin:-40px auto 60px;
background:white;
border-radius:18px;
padding:40px;
box-shadow:0 30px 80px rgba(0,0,0,.12);
}

.control-bar{
display:flex;
justify-content:space-between;
flex-wrap:wrap;
gap:20px;
margin-bottom:30px;
}

input,select{
padding:8px;
border-radius:6px;
border:1px solid #cbd5e1;
}

.goal-input{
border:2px solid var(--gold);
font-weight:700;
}

.add-btn{
background:var(--dark);
color:white;
border:none;
padding:8px 16px;
border-radius:8px;
cursor:pointer;
}

.save-btn{
background:var(--gold);
color:black;
font-weight:700;
}

.budget-summary{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
gap:20px;
margin-bottom:30px;
}

.b-card{
padding:20px;
border-radius:12px;
color:white;
text-align:center;
}

.bg-income{background:#10b981}
.bg-expense{background:#ef4444}
.bg-balance{background:#0f172a}
.bg-goal{background:#d4af37;color:black}
.bg-diff{background:#6366f1}

.b-value{
font-size:1.6rem;
font-weight:700;
}

.progress{
height:12px;
background:#e5e7eb;
border-radius:999px;
margin-top:10px;
overflow:hidden;
}

.progress-bar{
height:100%;
background:#10b981;
width:0%;
transition:width .5s;
}

.sheet-table{
width:100%;
border-collapse:collapse;
}

.sheet-table th{
background:#f1f5f9;
padding:10px;
text-align:left;
}

.sheet-table td{
padding:8px;
border-bottom:1px solid #e5e7eb;
}

/* ── Responsive (mobile) ── */
@media(max-width:600px){
.premium-hero{ padding:36px 20px; }
.budget-container{ padding:22px 16px; margin:-30px 12px 40px; }
.control-bar{ gap:14px; }
/* Let the wide transactions table scroll instead of overflowing the page */
.sheet-table{ display:block; overflow-x:auto; white-space:nowrap; -webkit-overflow-scrolling:touch; }
}

</style>

<div class="premium-hero">
<h1>Monthly Wealth Planner</h1>
<p>Track income, expenses and achieve your savings goal</p>
</div>

<div class="budget-container">

<div class="control-bar">

<div>
<b>Month</b>
<input type="month" id="month-picker">
</div>

<div>
<b>Goal</b>
<input type="number" id="goal" class="goal-input">
<button class="add-btn save-btn" onclick="save()">Save</button>
</div>

</div>

<div class="budget-summary">

<div class="b-card bg-income">
<div>Total Income</div>
<div class="b-value" id="income">$0</div>
</div>

<div class="b-card bg-expense">
<div>Total Expense</div>
<div class="b-value" id="expense">$0</div>
</div>

<div class="b-card bg-balance">
<div>Balance</div>
<div class="b-value" id="balance">$0</div>
</div>

<div class="b-card bg-goal">
<div>Goal</div>
<div class="b-value" id="goal-display">$0</div>
</div>

<div class="b-card bg-diff">
<div>Balance vs Goal</div>
<div class="b-value" id="difference">$0</div>
</div>

</div>

<div class="progress">
<div id="progress" class="progress-bar"></div>
</div>

<button class="add-btn" onclick="addRow()">Add Entry</button>

<table class="sheet-table">
<thead>
<tr>
<th>Description</th>
<th>Category</th>
<th>Type</th>
<th>Amount</th>
<th></th>
</tr>
</thead>
<tbody id="table"></tbody>
</table>

</div>

<script>

const monthPicker=document.getElementById("month-picker");
monthPicker.value=new Date().toISOString().slice(0,7);

let data={transactions:[]};

function addRow(){
data.transactions.push({
desc:"",
cat:"Expense",
type:"out",
amount:0
});
render();
}

function render(){

const tb=document.getElementById("table");
tb.innerHTML="";

data.transactions.forEach((t,i)=>{

tb.innerHTML+=`
<tr>
<td><input value="${t.desc}" oninput="update(${i},'desc',this.value)"></td>
<td><input value="${t.cat}" oninput="update(${i},'cat',this.value)"></td>
<td>
<select onchange="update(${i},'type',this.value)">
<option value="in"${t.type==='in'?'selected':''}>Income</option>
<option value="out"${t.type==='out'?'selected':''}>Expense</option>
</select>
</td>
<td><input type="number" value="${t.amount}" oninput="update(${i},'amount',this.value)"></td>
<td><button onclick="del(${i})">🗑</button></td>
</tr>`;
});

calc();
}

function update(i,f,v){
data.transactions[i][f]=v;
calc();
}

function del(i){
data.transactions.splice(i,1);
render();
}

function calc(){

let inc=0;
let exp=0;

data.transactions.forEach(t=>{
let v=parseFloat(t.amount)||0;
if(t.type==='in')inc+=v;
else exp+=v;
});

let balance=inc-exp;
let goal=parseFloat(document.getElementById("goal").value)||0;
let diff=balance-goal;

document.getElementById("income").innerText="$"+inc;
document.getElementById("expense").innerText="$"+exp;
document.getElementById("balance").innerText="$"+balance;
document.getElementById("goal-display").innerText="$"+goal;
document.getElementById("difference").innerText="$"+diff;

if(goal>0){
let p=Math.min(100,(balance/goal)*100);
document.getElementById("progress").style.width=p+"%";
}

}

function save(){

const form=new FormData();
form.append("action","save_budget");
form.append("month",monthPicker.value);
form.append("goal",document.getElementById("goal").value);
form.append("transactions",JSON.stringify(data.transactions));

fetch("premium-budget.php",{method:"POST",body:form})
.then(r=>r.json())
.then(r=>{
alert("Budget Saved Successfully");
});

}

function load(){

fetch(`premium-budget.php?action=load_budget&month=${monthPicker.value}`)
.then(r=>r.json())
.then(r=>{

if(r.status==="found"){
data.transactions=r.transactions;
document.getElementById("goal").value=r.goal;
}else{
data.transactions=[];
}

render();

});

}

monthPicker.addEventListener("change",load);
document.getElementById("goal").addEventListener("input",calc);

load();

</script>

<?php include 'includes/footer.php'; ?>
```
