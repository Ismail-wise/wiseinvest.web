<?php
session_start();

if(!isset($_SESSION['user_id'])){
header("Location: login.php");
exit();
}

require_once 'includes/db.php';

$user_id = $_SESSION['user_id'];
$id = intval($_GET['id']);

if(isset($_POST['confirm'])){

$conn->query("
DELETE FROM user_portfolio
WHERE id=$id AND user_id=$user_id
");

header("Location: portfolio.php");
exit();

}

?>

<?php include 'includes/header.php'; ?>

<style>

.delete-container{
max-width:450px;
margin:80px auto;
background:#0f172a;
padding:40px;
border-radius:16px;
box-shadow:0 20px 40px rgba(0,0,0,0.5);
text-align:center;
color:white;
animation:fadeUp .5s ease;
}

@keyframes fadeUp{
from{opacity:0;transform:translateY(20px)}
to{opacity:1;transform:translateY(0)}
}

.delete-title{
font-size:24px;
color:#ef4444;
margin-bottom:20px;
}

.delete-text{
color:#94a3b8;
margin-bottom:30px;
}

.btn-delete{
background:#ef4444;
border:none;
padding:12px 20px;
border-radius:8px;
cursor:pointer;
margin-right:10px;
}

.btn-cancel{
background:#1e293b;
border:none;
padding:12px 20px;
border-radius:8px;
cursor:pointer;
}

.btn-delete:hover{
transform:scale(1.05);
}

.btn-cancel:hover{
transform:scale(1.05);
}

</style>

<div class="delete-container">

<h2 class="delete-title">Delete Asset</h2>

<p class="delete-text">
Are you sure you want to remove this asset from your portfolio?
</p>

<form method="POST">

<button class="btn-delete" name="confirm">
Yes, Delete
</button>

<a href="portfolio.php" class="btn-cancel">
Cancel
</a>

</form>

</div>

<?php include 'includes/footer.php'; ?>