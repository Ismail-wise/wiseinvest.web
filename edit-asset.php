<?php
session_start();

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit();
}

require_once 'includes/db.php';

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id']);

if($_SERVER['REQUEST_METHOD']=='POST'){

$shares = $_POST['shares'];
$price  = $_POST['price'];

$stmt = $conn->prepare("
UPDATE user_portfolio
SET shares=?, avg_price=?
WHERE id=? AND user_id=?
");

$stmt->bind_param("ddii",$shares,$price,$id,$user_id);
$stmt->execute();

header("Location: portfolio.php");
exit();
}

$result = $conn->query("
SELECT * FROM user_portfolio
WHERE id=$id AND user_id=$user_id
");

$asset = $result->fetch_assoc();
?>

<?php include 'includes/header.php'; ?>

<style>

.edit-container{
max-width:500px;
margin:60px auto;
background:#0f172a;
padding:40px;
border-radius:16px;
box-shadow:0 20px 40px rgba(0,0,0,0.5);
color:white;
animation:fadeUp .5s ease;
}

@keyframes fadeUp{
from{opacity:0;transform:translateY(20px)}
to{opacity:1;transform:translateY(0)}
}

.edit-title{
font-size:26px;
margin-bottom:20px;
color:#d4af37;
}

.edit-container input{
width:100%;
padding:12px;
margin-top:10px;
margin-bottom:20px;
background:#020617;
border:1px solid #1e293b;
border-radius:8px;
color:white;
}

.btn-update{
background:#22c55e;
border:none;
padding:12px 20px;
border-radius:8px;
cursor:pointer;
font-weight:600;
transition:.2s;
}

.btn-update:hover{
transform:scale(1.05);
}

.symbol-box{
font-size:18px;
margin-bottom:20px;
color:#94a3b8;
}

</style>

<div class="edit-container">

<h2 class="edit-title">Edit Asset</h2>

<div class="symbol-box">
Symbol: <b><?php echo $asset['symbol']; ?></b>
</div>

<form method="POST">

<label>Shares</label>

<input type="number"
step="0.0001"
name="shares"
value="<?php echo $asset['shares']; ?>"
required>

<label>Average Price</label>

<input type="number"
step="0.01"
name="price"
value="<?php echo $asset['avg_price']; ?>"
required>

<button class="btn-update">Update Asset</button>

</form>

</div>

<?php include 'includes/footer.php'; ?>