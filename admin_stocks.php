<?php
include 'includes/db.php';

if($_SERVER['REQUEST_METHOD']=="POST"){

$ticker=$_POST['ticker'];
$company=$_POST['company'];
$sector=$_POST['sector'];
$market_cap=$_POST['market_cap'];
$pe=$_POST['pe'];
$roe=$_POST['roe'];

$conn->query("INSERT INTO stocks
(ticker,company,sector,market_cap,pe_ratio,roe)
VALUES
('$ticker','$company','$sector','$market_cap','$pe','$roe')");

}

?>

<h2>Add Stock</h2>

<form method="POST">

Ticker
<input name="ticker">

Company
<input name="company">

Sector
<input name="sector">

Market Cap
<input name="market_cap">

PE Ratio
<input name="pe">

ROE
<input name="roe">

<button>Add Stock</button>

</form>