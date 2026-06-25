<?php

require_once 'includes/db.php';

$symbol = $_GET['symbol'] ?? '';

if(!$symbol){
echo json_encode(["price"=>0]);
exit();
}

$api_key = "0cba35d6ffdb4e93b1a0424245157f44";

$url = "https://api.twelvedata.com/price?symbol=$symbol&apikey=$api_key";

$response = file_get_contents($url);

$data = json_decode($response,true);

if(isset($data['price'])){

echo json_encode([
"price"=>$data['price']
]);

}else{

echo json_encode([
"price"=>0
]);

}

?>