<?php
header("Content-Type: application/json; charset=UTF-8"); //ヘッダー情報の明記。必須。
$shop_disp = $_POST["shop-disp"];
//$shop_disp=filter_input(INPUT_POST,"shop_disp",FILTER_DEFAULT,FILTER_REQUIRE_ARRAY); // まだ配列だとfilter_inputが使えない
//var_dump($shop_disp);
//$shop_disp="rakuten";
//$keyword="abc";
$keyword=filter_input(INPUT_POST,"keyword");
$order=filter_input(INPUT_POST,"order");
$order_item=filter_input(INPUT_POST,"order-item",FILTER_DEFAULT,FILTER_REQUIRE_ARRAY); // まだ配列だとfilter_inputが使えない$order_item=$_POST["order-item"];
$items=get_items($shop_disp,$keyword);
$items=sort_items($items,$order,$order_item);


echo json_encode($items); //jsonオブジェクト化。必須。配列でない場合は、敢えてjson化する必要はない
//echo json_encode($shop_disp); //jsonオブジェクト化。必須。配列でない場合は、敢えてjson化する必要はない

function get_items($shop_disp,$keyword){
    $json = file_get_contents("./../data/shop_data.json"); // jsonファイルの読み込み（非ハードコード化）
    $shop_data = json_decode($json,true); // 連想配列にする。
    //require_once("./../data/shop_data.php"); // shop_data連想配列の読み込み

    //var_dump($shop_data);
    $rakuten_items = array();
    $amazon_items = array();
    $ebay_items = array();
    if (in_array('rakuten',$shop_disp)) {
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
        // アプリID
        $applicationId = $shop_data["applicationId"];
        // アフィリエイトID
        $affiliateId = $shop_data["affiliateId"];
        // 楽天リクエストURLから楽天市場の商品情報を取得
        $rakutenUrl = "https://app.rakuten.co.jp/services/api/IchibaItem/Search/20130805?format=xml&keyword=" . $url_word . "&sort=" . $url_sort . "&page=" . $page . "&hits=" . $hits_set . "&applicationId=" . $applicationId . "&affiliateId=" . $affiliateId;
        // レスポンス取得
        $contents = @file_get_contents($rakutenUrl);
        // XMLオブジェクトに変換
        $xml = simplexml_load_string($contents);
        foreach ($xml->Items->Item as $item) {
    
            $affiliateUrl = (string)$item->affiliateUrl;
            $mediumImageUrl = (string)$item->mediumImageUrls->imageUrl;
            $detail = $item->itemCaption;
            $detail = mb_substr($detail, 0, 30, "UTF-8") . '・・・';
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
    
    if (in_array('amazon',$shop_disp)) {
        //$html=mb_convert_encoding(file_get_contents("https://www.amazon.co.jp/s?k=".urlencode($keyword)),'HTML-ENTITIES','UTF-8');
        $ch = curl_init(); // cURLセッションを初期化
        curl_setopt($ch, CURLOPT_URL, "https://www.amazon.co.jp/s?k=" . urlencode($keyword)); // 取得するURLを指定
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない
        $html = curl_exec($ch); // URLの情報を取得
        curl_close($ch); // セッションを終了

        $dom = new DOMDocument;
        @$dom->loadHTML($html); // エラーを出さずにDOMDocumentに読み込む
        $xpath = new DOMXPath($dom); // DOMDocumentからXPath式を実行するためのDOMXPathを生成        
        //$dom = \phpQuery::newDocument($html);
        /*
        foreach ($dom[".s-include-content-margin.s-border-bottom.s-latency-cf-section"] as $entry) {
            $image = pq($entry)->find('img')->attr('src');
            $url = 'https://www.amazon.co.jp/' . pq($entry)->find('.a-link-normal')->attr('href');
            //$title=pq($entry)->find('.a-size-base-plus')->text();
            $title = pq($entry)->find('.a-color-base.a-text-normal')->text();
            $price = str_replace(array('￥', ','), array('', ''), pq($entry)->find('.a-price-whole')->text());
            if ($price == "") continue;
            $amazon_items[] = ['image' => $image, 'url' => $url, 'title' => $title, 'price' => $price];
        }
        */
        foreach ($xpath->query('//span[3]/div[2]/div') as $node) {
            $image = $xpath->evaluate('string(.//img[contains(@class, "s-image")]/@src)', $node);
            $url = 'https://www.amazon.co.jp/' . $xpath->evaluate('string(.//a[contains(@class, "a-link-normal")]/@href)', $node);
            $title = $xpath->evaluate('string(.//div/div/div[2]/h2/a/span)', $node);
            $price = str_replace(array('￥', ','), array('', ''),$xpath->evaluate('string(.//span[contains(@class, "a-price-whole")])', $node));
            if ($price == "") continue;
            $amazon_items[] = ['image' => $image, 'url' => $url, 'title' => $title, 'price' => $price];
        }
    }

    if (in_array('ebay',$shop_disp)) {
        // ソートのデフォルト設定 - レビュー件数降順
        $sort = '-reviewCount';
        // 1ページあたりの取得件数（商品数）
        $hits_set = 10;
        // エンコーディング
        $url_word = htmlspecialchars(urlencode($keyword));
        $url_sort = htmlspecialchars(urlencode($sort));
        // SECURITY-APPNAME
        $appname=$shop_data["appname"];
        // ebayリクエストURLからebay市場の商品情報を取得
        $ebayUrl = "https://svcs.ebay.com/services/search/FindingService/v1?SECURITY-APPNAME=".$appname."&OPERATION-NAME=findItemsByKeywords&SERVICE-VERSION=1.0.0&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&keywords=".$keyword."&paginationInput.entriesPerPage=".$hits_set."&GLOBAL-ID=EBAY-US&siteid=0";
        // レスポンス取得
        $contents = @file_get_contents($ebayUrl);
        // jsonオブジェクトに変換
        $json = json_decode($contents);
        //var_dump($json);
        if ($json!==NULL) {
            foreach($json->findItemsByKeywordsResponse[0]->searchResult[0]->item as $item){
                $affiliateUrl = $item->viewItemURL[0];
                $mediumImageUrl = $item->galleryURL[0];
                $detail = $item->title[0];
                //$price = $item->sellingStatus[0]->currentPrice[0];
                $price = 0;
                $ebay_items[] = ['image' => $mediumImageUrl, 'url' => $affiliateUrl, 'title' => $detail, 'price' => $price];
            }
        }
    }
    //print_r($rakuten_items);
    //print_r($amazon_items);
    //if($amazon_items==[])print("dom=".$dom);
    //if ($amazon_items == []) print("html=" . $html);
    
    $items = array_merge($rakuten_items, $amazon_items,$ebay_items);
    
    return $items;
}
function sort_items($items,$order,$order_item){
    $sort = array();
    foreach ($items as $key => $value) {
        $sort[$key] = $value['price'];
    }
    //print_r($sort);
    switch($order){
        case $order_item[0]: break;
        case $order_item[1]:
            array_multisort($sort, SORT_ASC, $items);
            break;
        case $order_item[2]:
            array_multisort($sort, SORT_DESC, $items);
            break;
    }
    //print_r($items);
    return $items;
}

exit; //処理の終了

