<?php
header("Content-Type: application/json; charset=UTF-8"); //ヘッダー情報の明記。必須。
$request_body = file_get_contents('php://input'); //送信されてきたbodyを取得(JSON形式）
$data = json_decode($request_body,true); // デコード
//var_dump($data);
$del_list = $data['del_list']; // デコード
session_start();

if (count($del_list)!==0){
    foreach ($del_list as $item_id){
        //var_dump($item_id);
        if (isset($_SESSION['cart'][$item_id])) {
            unset($_SESSION['cart'][$item_id]);
        }
    }
}
$cart=[];
if (isset($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
}
echo json_encode($cart); //jsonオブジェクト化。必須。配列でない場合は、敢えてjson化する必要はない
exit; //処理の終了
