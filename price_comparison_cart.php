<!DOCTYPE html>
<html>

<body>
    <h1>ショッピングカート</h1>
    <p><a href="price_comparison.php">商品一覧へ</a></p>
    <p><a href="price_comparison_delete.php">カートをすべて空に</a></p>

    <?php
    session_start();
    $cart = array();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $product_name = $_POST['product_name'];
        $kind = $_POST['kind'];
        if ($kind === 'change_amount') {
            $num = $_POST['num'];
            $_SESSION['cart'][$product_name]['num'] = $num;
        } elseif ($kind === 'delete') {
            unset($_SESSION['cart'][$product_name]);
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
        <td>'.$key.'</td>
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
                <input type="hidden" name="product_name" value="' . $key . '">
                <input type="submit" value="変更">
            </td>
        </form>
        <form action="" method="POST">
            <td>
                <input type="hidden" name="kind" value="delete">
                <input type="hidden" name="product_name" value="'.$key.'">
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

    <button onclick="copyToClipboard()">Copy text</button>

    <script>
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
    </script>
    
</body>
<script src="//code.jquery.com/jquery-3.1.1.min.js"></script>
<script src="price_comparison.js"></script>
</html>

