<?php
header("Content-Type: application/json; charset=UTF-8"); //ヘッダー情報の明記。必須。
//var_dump($_POST["shop-disp"]);
//var_dump(filter_input(INPUT_POST,"shop-disp",FILTER_DEFAULT,FILTER_REQUIRE_ARRAY));
//var_dump($$_POST["order-item"]);
//var_dump(filter_input(INPUT_POST,"order-item",FILTER_DEFAULT,FILTER_REQUIRE_ARRAY));
/*
$shop_disp=filter_input(INPUT_POST,"shop-disp",FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
$keyword=filter_input(INPUT_POST,"keyword");
$order=filter_input(INPUT_POST,"order");
$order_item=filter_input(INPUT_POST,"order-item",FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
*/

//$items=get_items($shop_disp,$keyword);
//$items=sort_items($items,$order,$order_item);

$is=New ItemSearch();
//echo $is->i;
echo json_encode($is->items); //jsonオブジェクト化。必須。配列でない場合は、敢えてjson化する必要はない
//echo json_encode($shop_disp); //jsonオブジェクト化。必須。配列でない場合は、敢えてjson化する必要はない

class ItemSearch{
    //public $i=0;
    private $shop_data = []; // 連想配列にする。
    public $shop_disp=[];
    public $keyword="";
    public $sort="";
    public $items=[];

    function __construct(){
        $json = file_get_contents("./../data/shop_data.json"); // jsonファイルの読み込み（非ハードコード化）
        $this->shop_data = json_decode($json,true); // 連想配列にする。
        $this->shop_disp=filter_input(INPUT_POST,"shop-disp",FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
        $this->keyword=filter_input(INPUT_POST,"keyword");
        $this->sort=filter_input(INPUT_POST,"order");
        //public $sort_item=filter_input(INPUT_POST,"order-item",FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
        $this->get();
    }
    function get(){
        //$this->i++;
        if (in_array('rakuten',$this->shop_disp)) $this->rakuten();
        if (in_array('amazon',$this->shop_disp)) $this->amazon();
        if (in_array('ebay',$this->shop_disp)) $this->ebay();
        $this->sort_items();
        return $this->items;
    }
    function rakuten(){
        $sort_str=['review-rank'=>'-reviewCount',
                'price-asc-rank'=>'+itemPrice',
                'price-desc-rank'=>'-itemPrice']; // 各ショップでのsortの名称
        $page = 1; // 取得ページ
        $hits_set = 10; // 1ページあたりの取得件数（商品数）
        //$url_word = htmlspecialchars(urlencode($this->keyword));
        //$url_sort = htmlspecialchars(urlencode($sort_str[$this->sort]));
        $applicationId = $this->shop_data["applicationId"]; // アプリID
        $affiliateId = $this->shop_data["affiliateId"]; // アフィリエイトID
        // 楽天リクエストURLから楽天市場の商品情報を取得
        $url = "https://app.rakuten.co.jp/services/api/IchibaItem/Search/20130805?format=xml&keyword=" 
            . htmlspecialchars(urlencode($this->keyword))
            . "&sort=" . htmlspecialchars(urlencode($sort_str[$this->sort]))
            . "&page=" . $page 
            . "&hits=" . $hits_set 
            . "&applicationId=" . $applicationId 
            . "&affiliateId=" . $affiliateId;
        $contents = @file_get_contents($url); // レスポンス取得
        $xml = simplexml_load_string($contents); // XMLオブジェクトに変換
        foreach ($xml->Items->Item as $item) {
            $affiliateUrl = (string)$item->affiliateUrl;
            $mediumImageUrl = (string)$item->mediumImageUrls->imageUrl;
            $title = (string)$item->itemName;
            //$detail = (string)$item->itemCaption;
            //$detail = mb_substr($detail, 0, 30, "UTF-8") . '・・・';
            $price = (string)$item->itemPrice;
            $this->items[] = ['image' => $mediumImageUrl, 'url' => $affiliateUrl, 'title' => $title, 'price' => $price];
        }
    }
    function amazon(){
        $sort_str=['review-rank'=>'relevanceblender',
                'price-asc-rank'=>'price-asc-rank',
                'price-desc-rank'=>'price-desc-rank']; // 各ショップでのsortの名称
        $hits_set = 10; // 取得件数（商品数）
        $url="https://www.amazon.co.jp/s?k=" 
            . htmlspecialchars(urlencode($this->keyword))
            ."&s=".htmlspecialchars(urlencode($sort_str[$this->sort]));
        $ch = curl_init(); // cURLセッションを初期化
        curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない
        $html = curl_exec($ch); // URLの情報を取得
        curl_close($ch); // セッションを終了

        $all_items=[]; // スクレイピングにつきitem数を指定できないため一旦ページにある商品すべてを読み込む
        $dom = new DOMDocument;
        @$dom->loadHTML($html); // エラーを出さずにDOMDocumentに読み込む
        $xpath = new DOMXPath($dom); // DOMDocumentからXPath式を実行するためのDOMXPathを生成
        foreach ($xpath->query('//span[3]/div[2]/div') as $node) {
            $image = $xpath->evaluate('string(.//img[contains(@class, "s-image")]/@src)', $node);
            $url = 'https://www.amazon.co.jp/' . $xpath->evaluate('string(.//a[contains(@class, "a-link-normal")]/@href)', $node);
            $title = $xpath->evaluate('string(.//div/div/div[2]/h2/a/span)', $node);
            $price = str_replace(array('￥', ','), array('', ''),$xpath->evaluate('string(.//span[contains(@class, "a-price-whole")])', $node));
            if ($price == "") continue;
            $all_items[] = ['image' => $image, 'url' => $url, 'title' => $title, 'price' => $price];
        }
        $this->items=array_merge($this->items,array_slice($all_items,0,$hits_set)); // 0番目から$hits_set個取得して$this->itemsと結合
    }
    function ebay(){
        $sort_str=['review-rank'=>'BestMatch',
                'price-asc-rank'=>'PricePlusShippingLowest',
                'price-desc-rank'=>'PricePlusShippingHighest']; // 各ショップでのsortの名称
        //$sort = 'PricePlusShippingLowest';
        $hits_set = 10; // 取得件数（商品数）
        $appname=$this->shop_data["appname"]; // SECURITY-APPNAME
        // ebayリクエストURLからebay市場の商品情報を取得
        $url = "https://svcs.ebay.com/services/search/FindingService/v1?SECURITY-APPNAME="
            .$appname
            ."&OPERATION-NAME=findItemsByKeywords&SERVICE-VERSION=1.0.0&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&keywords="
            .htmlspecialchars(urlencode($this->keyword))
            ."&paginationInput.entriesPerPage=".$hits_set
            ."&sortOrder=".htmlspecialchars(urlencode($sort_str[$this->sort]))
            ."&GLOBAL-ID=EBAY-US&siteid=0";
        $contents = @file_get_contents($url); // レスポンス取得
        $json = json_decode($contents); // jsonオブジェクトに変換
        //var_dump($json);
        if ($json!==NULL) {
            foreach($json->findItemsByKeywordsResponse[0]->searchResult[0]->item as $item){
                //var_dump($item->sellingStatus[0]->convertedCurrentPrice[0]->__value__);
                $affiliateUrl = $item->viewItemURL[0];
                $mediumImageUrl = $item->galleryURL[0];
                $detail = $item->title[0];
                //$price = $item->sellingStatus[0]->currentPrice[0];
                $price = ($item->sellingStatus[0]->convertedCurrentPrice[0]->__value__)*105; // USDのためJPYに直す
                $this->items[] = ['image' => $mediumImageUrl, 'url' => $affiliateUrl, 'title' => $detail, 'price' => $price];
            }
        }
        
    }
    function sort_items(){
        $sort_ref = array();
        foreach ($this->items as $key => $value) {
            $sort_ref[$key] = $value['price'];
        }
        //print_r($sort);
        switch($this->sort){
            case 'review-rank': break;
            case 'price-asc-rank':
                array_multisort($sort_ref, SORT_ASC, $this->items);
                break;
            case 'price-desc-rank':
                array_multisort($sort_ref, SORT_DESC, $this->items);
                break;
        }
    }
}

//$items = array_merge($rakuten_items, $amazon_items,$ebay_items);



exit; //処理の終了

