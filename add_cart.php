<?php
header("Content-Type: application/json; charset=UTF-8"); //ヘッダー情報の明記。必須。

$product_url=filter_input(INPUT_POST,"product_url");
$product_name=filter_input(INPUT_POST,"product_name");
$num=filter_input(INPUT_POST,"num");

//echo $num;
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $product_url!==null && $product_name!==null && $num!==null) {
    //$_SESSION['cart'][$product_name] = ['num'=>$num,'product_url'=>$product_url];
    $_SESSION['cart'][$product_name] = ['num'=>$num,'product_url'=>$product_url];
    //echo "post_method okay";
}

$cart=[];
if (isset($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
}
echo json_encode($cart); //jsonオブジェクト化。必須。配列でない場合は、敢えてjson化する必要はない

exit; //処理の終了
