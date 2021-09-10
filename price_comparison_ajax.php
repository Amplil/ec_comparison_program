<?php
define("USDJPY",105); // ドル円
header("Content-Type: application/json; charset=UTF-8"); //ヘッダー情報の明記。必須。
session_start();
//var_dump($_POST["shop-disp"]);
//var_dump(filter_input(INPUT_POST,"shop-disp",FILTER_DEFAULT,FILTER_REQUIRE_ARRAY));
//var_dump($$_POST["order-item"]);
//var_dump(filter_input(INPUT_POST,"order-item",FILTER_DEFAULT,FILTER_REQUIRE_ARRAY));
/*
$shop_disp=filter_input(INPUT_POST,"shop",FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
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

    private $item_id=0;
    private $image="";
    private $url="";
    private $title="";
    private $price="";
    private $shop="";

    function __construct(){
        $json = file_get_contents("/home/private_data/shop_data.json"); // jsonファイルの読み込み（非ハードコード化）
        $this->shop_data = json_decode($json,true); // 連想配列にする。
        $this->shop_disp=filter_input(INPUT_GET,"shop",FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
        $this->keyword=filter_input(INPUT_GET,"keyword");
        $this->sort=filter_input(INPUT_GET,"order");
        $this->tr_keyword=filter_input(INPUT_GET,"tr_keyword");
        $this->tr_keyword=$this->tr_keyword===null ? "" : $this->tr_keyword; // nullなら""にする。
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
        $sort_str=['relevanceblender'=>'standard',
                'review-rank'=>'-reviewCount',
                'price-asc-rank'=>'+itemPrice',
                'price-desc-rank'=>'-itemPrice']; // 各ショップでのsortの名称
        $page = 1; // 取得ページ
        $hits_set = 10; // 1ページあたりの取得件数（商品数）
        //$url_word = htmlspecialchars(urlencode($this->keyword));
        //$url_sort = htmlspecialchars(urlencode($sort_str[$this->sort]));
        $applicationId = $this->shop_data["rakuten"]["applicationId"]; // アプリID
        $affiliateId = $this->shop_data["rakuten"]["affiliateId"]; // アフィリエイトID
        // 楽天リクエストURLから楽天市場の商品情報を取得
        $get_url = "https://app.rakuten.co.jp/services/api/IchibaItem/Search/20130805?format=xml&keyword=" 
            . htmlspecialchars(urlencode($this->keyword))
            . "&sort=" . htmlspecialchars(urlencode($sort_str[$this->sort]))
            . "&page=" . $page 
            . "&hits=" . $hits_set 
            . "&applicationId=" . $applicationId 
            . "&affiliateId=" . $affiliateId;
        $contents = @file_get_contents($get_url); // レスポンス取得
        $xml = simplexml_load_string($contents); // XMLオブジェクトに変換
        foreach ($xml->Items->Item as $item) {
            $this->shop='rakuten';
            $this->url = (string)$item->affiliateUrl;
            $this->image = (string)$item->mediumImageUrls->imageUrl;
            $this->title = (string)$item->itemName;
            //$detail = (string)$item->itemCaption;
            //$detail = mb_substr($detail, 0, 30, "UTF-8") . '・・・';
            $this->price = (string)$item->itemPrice;
            $this->item_id=md5($this->image); // 画像URLでitem_idを生成する
            //$this->items[] = ['item_id'=>md5($mediumImageUrl),'image' => $mediumImageUrl, 'url' => $affiliateUrl, 'title' => $title, 'price' => $price,'shop'=>'rakuten'];
            $this->add_item();
        }
    }
    function amazon(){
        $sort_str=['relevanceblender'=>'relevanceblender',
                'review-rank'=>'review-rank',
                'price-asc-rank'=>'price-asc-rank',
                'price-desc-rank'=>'price-desc-rank']; // 各ショップでのsortの名称 おすすめ（relevanceblender）でなくreview-rankにする
        $hits_set = 10; // 取得件数（商品数）
        $url="https://www.amazon.co.jp/s?k=" 
            . htmlspecialchars(urlencode($this->keyword))
            ."&s=".htmlspecialchars(urlencode($sort_str[$this->sort]));
        //echo "***search url: ".$url." ***";
        $ch = curl_init(); // cURLセッションを初期化
        curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない
        $html = curl_exec($ch); // URLの情報を取得
        curl_close($ch); // セッションを終了

        //$all_items=[]; // スクレイピングにつきitem数を指定できないため一旦ページにある商品すべてを読み込む
        $dom = new DOMDocument;
        @$dom->loadHTML($html); // エラーを出さずにDOMDocumentに読み込む
        $xpath = new DOMXPath($dom); // DOMDocumentからXPath式を実行するためのDOMXPathを生成
        $item_num=0; // 何番目のアイテムか
        foreach ($xpath->query('//span[3]/div[2]/div') as $node) {
            $this->shop='amazon';
            $this->image = $xpath->evaluate('string(.//img[contains(@class, "s-image")]/@src)', $node);
            $oringin_url = 'https://www.amazon.co.jp' . $xpath->evaluate('string(.//a[contains(@class, "a-link-normal")]/@href)', $node);
            $this->title = $xpath->evaluate('string(.//div[1]/h2/a/span)', $node);
            //var_dump($this->title);
            $this->price = str_replace(array('￥', ','), array('', ''),$xpath->evaluate('string(.//span[contains(@class, "a-price-whole")])', $node));
            if ($this->price == "") continue; // 価格が書いていないものは飛ばす
            //$item_id=md5(substr($url,0,strcspn($url,'?'))); // 加工したURL いつも同じURLになるように'?'以降は削除したあと、ハッシュ化したものをIDとする。
            $this->item_id=md5($this->image); // 画像URLでitem_idを生成する
            //plain_url=substr($url,0,strcspn($url,'?')); // 加工したURL いつも同じURLになるように'?'以降は削除
            $this->url=$oringin_url.'&tag=aikotobahaabu-22'; // 元のURLにそのままアフィリエイトタグを付ける
            //echo '<**url**>'.$url;
            //echo 'xpath'.$xpath->evaluate('string(.//a[contains(@class, "a-link-normal")]/@href)', $node);
            //echo 'url_processed'.$url_processed;
            //$all_items[] = ['item_id'=>$item_id,'image' => $image,'url' => $affiliateUrl,'title' => $title,'price' => $price,'shop'=>'amazon'];
            if($this->add_item()){
                $item_num++; // 正常に追加できたら
            }
            //var_dump($item_num);
            //var_dump($this->items);
            if($item_num===$hits_set) break; // hits_set分、アイテムを追加したら終了
        }
        //$this->items=array_merge($this->items,array_slice($all_items,0,$hits_set)); // 0番目から$hits_set個取得して$this->itemsと結合
    }
    function ebay(){
        $AccessToken= (isset($_SESSION['AccessToken'])) ? $_SESSION['AccessToken'] : $this->ebay_get_token(); // セッションにアクセストークンがない場合は取得する
        $json = $this->ebay_search($AccessToken);
        if($json==NULL){
            $AccessToken=$this->ebay_get_token(); // アクセストークンが切れていたら再取得
            $json = $this->ebay_search($AccessToken);
        }

        //var_dump($json);
        if ($json!==NULL) {
            foreach($json->itemSummaries as $item){
                //var_dump($item->sellingStatus[0]->convertedCurrentPrice[0]->__value__);
                $this->shop='ebay';
                $this->url = $item->itemAffiliateWebUrl;
                $this->image = $item->image->imageUrl;
                $this->title = $item->title;
                $this->price = round(($item->price->value)*USDJPY,2); // USDのためJPYに直す
                $this->item_id=md5($item->itemId); // ebayのitemIdからitem_idを生成する
                //$this->items[] = ['item_id'=>$item_id,'image' => $mediumImageUrl, 'url' => $affiliateUrl, 'title' => $detail, 'price' => $price,'shop'=>'ebay'];
                $this->add_item();
            }
        }
    }
    function ebay_get_token(){
        $client_id=$this->shop_data["NewEbayApi"]["ClientId"];
        $client_secret=$this->shop_data["NewEbayApi"]["ClientSecret"];
        
        $url = "https://api.ebay.com/identity/v1/oauth2/token";
        $header=[
          "Content-Type: application/x-www-form-urlencoded",
          "Authorization: Basic ".base64_encode("$client_id:$client_secret")
        ];
        $data=[
          "grant_type"=>"client_credentials",
          "scope"=>"https://api.ebay.com/oauth/api_scope"
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = json_decode(curl_exec($ch)); // json文字列をPHPオブジェクトに変換
        $AccessToken=$result->access_token; // アクセストークン
        curl_close($ch);
      
        $_SESSION['AccessToken']=$AccessToken;
        return $AccessToken;
    }
    function ebay_search($AccessToken){
        $sort_str=['relevanceblender'=>'',
                'review-rank'=>'distance',
                'price-asc-rank'=>'price',
                'price-desc-rank'=>'-price']; // 各ショップでのsortの名称
        $hits_set = 10; // 取得件数（商品数）
        $affiliateId=$this->shop_data["ebay"]["affiliateId"];
        $opts = array(
          'http'=>array(
            'method'=>"GET",
            'header'=>"Authorization: Bearer $AccessToken\r\n".
                      "X-EBAY-C-ENDUSERCTX: affiliateCampaignId=$affiliateId\r\n"
          )
        );
        $context = stream_context_create($opts);
        // ebayリクエストURLからebay市場の商品情報を取得
        $sarch_word=$this->tr_keyword==='' ? $this->keyword : $this->tr_keyword;
        $ebayUrl = "https://api.ebay.com/buy/browse/v1/item_summary/search"
        ."?q=".htmlspecialchars(urlencode($sarch_word))
        ."&limit=$hits_set"
        ."&sort=".$sort_str[$this->sort];
        $contents = @file_get_contents($ebayUrl,false,$context); // レスポンス取得
        return json_decode($contents); // json文字列をPHPオブジェクトに変換
    }
    function add_item(){ // アイテムの追加
        if(array_search($this->item_id,array_column($this->items,'item_id'))===false){ // 同じ商品が出てきてしまうことがあるため対策
            $this->items[] = ['item_id'=>$this->item_id,'image'=>$this->image,'url'=>$this->url,'title'=>$this->title,'price'=>$this->price,'shop'=>$this->shop];
            return true;
        }
        else{
            return false;
        }
    }
    function sort_items(){
        switch($this->sort){
            case 'relevanceblender':
                $this->shop_assort();
                break;
            case 'review-rank':
                $this->shop_assort();
                break;
            case 'price-asc-rank':
                array_multisort($this->price_sort_ref(), SORT_ASC, $this->items);
                break;
            case 'price-desc-rank':
                array_multisort($this->price_sort_ref(), SORT_DESC, $this->items);
                break;
        }
    }
    function price_sort_ref(){
        $sort_ref = array();
        foreach ($this->items as $key => $value) {
            $sort_ref[$key] = $value['price'];
        }
        //print_r($sort);
        return $sort_ref;
    }
    function shop_assort(){
        //echo "this items: ";
        //var_dump($this->items);
        $temp_items=$this->items;
        $assort_items=[];
        for($i=0;($i<count($this->items) and $temp_items!==[]);$i++){ // itemsの数分かtemp_itemsが空になるまで
            foreach($this->shop_disp as $shop){
                $item_num=array_search($shop,array_column($temp_items,'shop'));
                if($item_num!==false){
                    //var_dump($item_num);
                    $assort_items[]=$temp_items[$item_num];
                    array_splice($temp_items,$item_num,1);
                }
            }
        }
        $this->items=$assort_items;
    }
}
//$items = array_merge($rakuten_items, $amazon_items,$ebay_items);

exit; //処理の終了

