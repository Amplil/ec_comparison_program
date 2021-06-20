<?php
header("Content-Type: application/json; charset=UTF-8"); //ヘッダー情報の明記。必須。

$item_id=filter_input(INPUT_POST,"item_id");
$title=filter_input(INPUT_POST,"title");
$num=filter_input(INPUT_POST,"num");
$url=filter_input(INPUT_POST,"url");
$image=filter_input(INPUT_POST,"image");
$price=filter_input(INPUT_POST,"price");

//echo $item_id;
//echo $num;
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $item_id!==null) {
    //$_SESSION['cart'][$title] = ['num'=>$num,'url'=>$url];
    $_SESSION['cart'][$item_id] = [
        'title'=>$title,
        'num'=>$num,
        'url'=>$url,
        'image'=>$image,
        'price'=>$price
    ];
    //echo "post_method okay";
    //var_dump($_SESSION['cart']);
}

$cart=[];
if (isset($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
}
echo json_encode($cart); //jsonオブジェクト化。必須。配列でない場合は、敢えてjson化する必要はない

exit; //処理の終了
