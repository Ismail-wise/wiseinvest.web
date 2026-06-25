<?php

require_once 'includes/db.php';
require_once 'includes/api_config.php';

$symbol = $_GET['symbol'] ?? '';

if(!$symbol){
echo json_encode(["price"=>0]);
exit();
}

$api_key = FINNHUB_API_KEY;

$url = "https://finnhub.io/api/v1/quote?symbol=$symbol&token=$api_key";

$response = file_get_contents($url);

$data = json_decode($response,true);

if(isset($data['c']) && $data['c'] > 0){

echo json_encode([
"price"=>$data['c']
]);

}else{

echo json_encode([
"price"=>0
]);

}

?>