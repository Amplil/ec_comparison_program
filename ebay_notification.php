<?php
header('HTTP/1.1 200 OK');
header("Content-Type: application/json; charset=UTF-8"); //ヘッダー情報の明記。必須。

$shop_data_json = file_get_contents("/home/private_data/shop_data.json"); // jsonファイルの読み込み（非ハードコード化）
$shop_data = json_decode($shop_data_json,true); // 連想配列にする。
$verificationToken=$shop_data["ebay"]["NotificationAPIverificationToken"];
$endpoint=$shop_data["ebay"]["NotificationEndpoint"];

$challengeCode=filter_input(INPUT_GET,"challenge_code");

$hash = hash_init('sha256');
hash_update($hash, $challengeCode);
hash_update($hash, $verificationToken);
hash_update($hash, $endpoint);
$responseHash = hash_final($hash);
$respons["challengeResponse"]=$responseHash;
echo json_encode($respons);