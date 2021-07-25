<!-- いずれajaxで呼び出されるプログラムにする -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>カート一覧</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.1/css/all.css">
    <style>
        .copy-btn:hover{
            color: #007bff;
        }
        .table{
            width: 250px;
        }
    </style>
</head>

<body>
    <h1>ショッピングカート</h1>
    <!-- <p><a href="price_comparison.html">商品一覧へ</a></p> -->
    <p><a href="price_comparison_delete.php">カートをすべて空に</a></p>

    <?php
    session_start();
    //echo phpinfo();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $product_id = $_POST['product_id'];
        $kind = $_POST['kind'];
        if ($kind === 'change_amount') {
            $num = $_POST['num'];
            $_SESSION['cart'][$product_id]['num'] = $num;
        } elseif ($kind === 'delete') {
            unset($_SESSION['cart'][$product_id]);
        }
    }

    $cart = array();
    if (isset($_SESSION['cart'])) {
        $cart = $_SESSION['cart'];
    }
    
    //var_dump($cart);
    //echo '<br>';

    //var_dump($_SESSION);

    //echo '<br>';
    //var_dump($_SESSION['cart']);

    print '<table style="text-align:center" id="items">
    <tr><th>商品</th><th>個数</th><th>数量</th><th>変更ボタン</th><th>削除ボタン</th></tr>';
    foreach ($cart as $key => $var) {        
        print '
        <tr>
        <td>'.$var['title'].'</td>
        <td>' . $var['num'] . '個</td>
        <form action="" method="POST">
            <td>
                <select name="num">';
                for ($i = 1; $i < 10; $i++) {
                    print '<option value="' . $i . '">' . $i . '</option>';
                }
                print '</select>
            </td>
            <td>
                <input type="hidden" name="kind" value="change_amount">
                <input type="hidden" name="product_id" value="' . $key . '">
                <input type="submit" value="変更">
            </td>
        </form>
        <form action="" method="POST">
            <td>
                <input type="hidden" name="kind" value="delete">
                <input type="hidden" name="product_id" value="'.$key.'">
                <input type="submit" value="削除">
            </td>
        </form>
        </tr>';
    }
    print '
    </div>
    </table>';
    $copytext='';
    foreach ($cart as $key => $var) {
        $copytext=$copytext.$key;
    }
    //echo $copytext;
    ?>

    <button id="copy-button">Copy text</button>

    <script src="//code.jquery.com/jquery-3.1.1.min.js"></script>
    <!-- <script src="price_comparison.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW"
        crossorigin="anonymous"></script>
    <script>
        setTimeout(function () {
            $('#copy-button')
            // tooltip設定
            .tooltip({
                trigger: 'manual',
                placement:'right'
            })
            // tooltip表示後の動作を設定
            .on('shown.bs.tooltip', function(){
                setTimeout((function(){
                    $(this).tooltip('hide');
                }).bind(this), 1500);
            })
            // クリック時の動作を設定
            .on('click', function(){
                //$('#items').select();

                let copyTarget = document.getElementById("items");
                //document.write(copyTarget.value);
                let range = document.createRange();
                range.selectNode(copyTarget);
                window.getSelection().addRange(range);

                const copyResult = document.execCommand('copy');
                console.log(copyResult)
                // コピー結果によって表示変更
                if(copyResult){
                    $('#copy-button').attr('data-bs-original-title', 'コピーしました');
                }else{
                    $('#copy-button').attr('data-bs-original-title', 'コピー失敗しました');
                }
                // tooltip表示
                $(this).tooltip('show');
            });
        }, 545*1.33); // 545ms timing to load jQuery.js + network estimated delay 

        /*
        function copyToClipboard() {
            var copyTarget = document.getElementById("items");
            //document.write(copyTarget.value);
            var range = document.createRange();
            range.selectNode(copyTarget);
            window.getSelection().addRange(range);

            //copyTarget.select();

            document.execCommand("Copy");

            alert("コピーできました！");
        }
        */

    </script>
    
</body>
</html>

