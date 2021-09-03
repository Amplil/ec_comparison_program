<?php
header("Content-Type: application/json; charset=UTF-8"); //ヘッダー情報の明記。必須。
/*
$item_id=filter_input(INPUT_POST,"item_id");
$image=filter_input(INPUT_POST,"image");
$url=filter_input(INPUT_POST,"url");
$title=filter_input(INPUT_POST,"title");
$price=filter_input(INPUT_POST,"price");
$shop=filter_input(INPUT_POST,"shop");
$quantity=filter_input(INPUT_POST,"quantity");
*/
$request_body = file_get_contents('php://input'); //送信されてきたbodyを取得(JSON形式）
$data = json_decode($request_body,true); // デコード
//var_dump($data);
$item=$data['item'];
$item_id=$item['item_id'];
//echo "item_id";
//echo $data['item_id'];
//echo $num;
session_start();
//if ($_SERVER['REQUEST_METHOD'] === 'POST' && $item_id!==null) {
//if ($data['item_id']!==null) {
if ($item_id!==null) {
        //$_SESSION['cart'][$title] = ['num'=>$num,'url'=>$url];
    $_SESSION['cart'][$item_id] = [
        'image'=>$item['image'],
        'url'=>$item['url'],
        'title'=>$item['title'],
        'price'=>$item['price'],
        'shop'=>$item['shop'],
        'quantity'=>$item['quantity']
    ];
    //$_SESSION['cart'][]=$data;
    //echo "post_method okay";
    //var_dump($_SESSION['cart']);
}

$cart=[];
if (isset($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
}
echo json_encode($cart); //jsonオブジェクト化。必須。配列でない場合は、敢えてjson化する必要はない

exit; //処理の終了
