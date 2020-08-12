<?php
    require_once("phpQuery-onefile.php");
    $keyword = '';
    $default_word='キーボード';
    if (isset($_POST['searchtextbox'])) {
        $keyword = $_POST['searchtextbox'];
        $pattern="^(\s|　)+$";
        if(mb_ereg_match($pattern, $keyword)) {
        $keyword=$default_word;
        }
    }
    // 検索キーワードのデフォルト設定（初期値）
    if ($keyword == '') {
        $keyword=$default_word;
    }
    // 検索キーワード入力ボックス
    $searchTextBox = '
    <input type="text" name="searchtextbox" value="'.$keyword.'" placeholder="キーワードで探す" style="width:240px;height:28px;vertical-align: top;">
    <input type="submit" name="btn" value="Go" style="font-size:1.1rem;height:34px;vertical-align: top;">';
    //print '<table><tr><td >'.$searchTextBox.'</td></tr></table>';

    $shop_disp=array('rakuten'=>true,'amazon'=>true); // 表示するショップの種類
    if (isset($_POST['shop']) && is_array($_POST['shop'])){
        foreach($shop_disp as &$value)$value=false;
        unset($value);
        foreach($_POST['shop'] as $shop)$shop_disp[$shop]=true;
    }
    $order_item=array('人気順','価格の安い順','価格の高い順'); // 表示の順番
    $order=$order_item[1];
    if (isset($_POST['order']))$order=$_POST['order'];
    
    $rakuten_items=array();
    $amazon_items=array();
    if($shop_disp['rakuten']){
        // 商品検索
        $page = '';
        // ソートのデフォルト設定 - レビュー件数降順
        $sort = '-reviewCount';
        // 取得ページの初期設定
        if ($page == '') {
        $page = 1;
        }
        // 1ページあたりの取得件数（商品数）
        $hits_set = 10;
        // エンコーディング
        $url_word = htmlspecialchars(urlencode($keyword));
        $url_sort = htmlspecialchars(urlencode($sort));
        $applicationId='アプリID';
        $affiliateId='アフィリエイトID';
        // 楽天リクエストURLから楽天市場の商品情報を取得
        $rakutenUrl = "https://app.rakuten.co.jp/services/api/IchibaItem/Search/20130805?format=xml&keyword=".$url_word."&sort=".$url_sort."&page=".$page."&hits=".$hits_set."&applicationId=".$applicationId."&affiliateId=".$affiliateId;
        // レスポンス取得
        $contents = @file_get_contents($rakutenUrl);
        // XMLオブジェクトに変換
        $xml = simplexml_load_string($contents);
        foreach($xml->Items->Item as $item){
            
            $affiliateUrl = (string)$item->affiliateUrl;
            $mediumImageUrl = (string)$item->mediumImageUrls->imageUrl;
            $detail = $item->itemCaption;
            $detail = mb_substr($detail, 0, 30,"UTF-8") .'・・・';
            $price = (string)$item->itemPrice;
            /*
            $affiliateUrl = $item->affiliateUrl;
            $mediumImageUrl = $item->mediumImageUrls->imageUrl;
            $detail = $item->itemCaption;
            $detail = mb_substr($detail, 0, 30,"UTF-8") .'・・・';
            $price = $item->itemPrice;
    */
            $rakuten_items[] = ['image' => $mediumImageUrl, 'url' => $affiliateUrl, 'title' => $detail, 'price' => $price];
        }
    }
        
    if($shop_disp['amazon']){
        //$html=mb_convert_encoding(file_get_contents("https://www.amazon.co.jp/s?k=".urlencode($keyword)),'HTML-ENTITIES','UTF-8');
        $ch = curl_init(); // cURLセッションを初期化
        curl_setopt($ch, CURLOPT_URL,"https://www.amazon.co.jp/s?k=".urlencode($keyword)); // 取得するURLを指定
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない
        $html=curl_exec($ch); // URLの情報を取得
        curl_close($ch); // セッションを終了
    
        $dom=\phpQuery::newDocument($html);
        //foreach ($dom[".s-expand-height.s-include-content-margin.s-border-bottom.s-latency-cf-section"] as $entry){
        foreach ($dom[".s-include-content-margin.s-border-bottom.s-latency-cf-section"] as $entry){
            $image=pq($entry)->find('img')->attr('src');
            $url='https://www.amazon.co.jp/'.pq($entry)->find('.a-link-normal')->attr('href');
            //$title=pq($entry)->find('.a-size-base-plus')->text();
            $title=pq($entry)->find('.a-color-base.a-text-normal')->text();
            $price=str_replace(array('￥',','),array('',''),pq($entry)->find('.a-price-whole')->text());
            if($price=="")continue;
            $amazon_items[] = ['image' => $image, 'url' => $url, 'title' => $title, 'price' => $price];
        }
    }
    //print_r($rakuten_items);
    //print_r($amazon_items);
    //if($amazon_items==[])print("dom=".$dom);
    if($amazon_items==[])print("html=".$html);

    $items=array_merge($rakuten_items,$amazon_items);

    $sort=array();
    foreach ($items as $key => $value) {
        $sort[$key] = $value['price'];
    }
    //print_r($sort);

    if($order==$order_item[0]);
    else if($order==$order_item[1])array_multisort($sort, SORT_ASC, $items);
    else if($order==$order_item[2])array_multisort($sort, SORT_DESC, $items);

    //print_r($items);

    print '
    <!DOCTYPE html >
    <html lang="ja">
    <head>
    <meta charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <TITLE>商品比較サイト</TITLE>
    <meta name="Keywords" content=""> 
    <meta name="Description" content="">
    </head>
    <body>
    <div id="container" style="padding:10px 10px 40px;color:#1D56A5;">
    <div style="font-size:1.6rem;">いろいろな商品を比較！</div><br />
    ';
    print '<br />';
    // 検索キーワード入力ボックス
    print '<form method="post" action=""><table><tr><td >'.$searchTextBox.'</td></tr></table>';
    // ショップ選択チェックボックス
    print '<label><input type="checkbox" name="shop[]" value="rakuten" '.($shop_disp['rakuten']?'checked':'').'> 楽天　</label>
    <label><input type="checkbox" name="shop[]" value="amazon" '.($shop_disp['amazon']?'checked':'').'> Amazon　</label>
    <select name="order">';
    foreach($order_item as $item){
        print '<option value='.$item.($item==$order?' selected':'').'>'.$item.'</option>';
    }
    print '</select></form>';
    // 商品表示
    print '<table border="0"><tr>';
    $i = 0;
    foreach ($items as $item){
        print '<td style="padding:10px;"><div>
        <a href="'.$item['url'].'" target="_blank"><img src="'.$item['image'].'"><br />'
        .$item['title'].'<br />'
        ."￥".number_format($item['price']).
        ' </a></div></td>';
        $i++;
        if ($i%5 == 0) {
          print '</tr><tr>';
        }
        
    }
    print '</table>';
    print '</div><!--end/container-->';
    print '<br />';
    print '</body></html>';
    
?>
