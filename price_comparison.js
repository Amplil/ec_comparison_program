let shop_item ={rakuten:'楽天',amazon:'Amazon',ebay:'ebay'}; // 表示の順番
//let order_item = ['人気順', '価格の安い順', '価格の高い順']; // 表示の順番
let order_str = {'relevanceblender':'おすすめ順','review-rank':'人気順', 'price-asc-rank':'価格の安い順', 'price-desc-rank':'価格の高い順'}; // 表示の順番
let order = 'price-asc-rank';
//let shop_disp={rakuten:true,amazon:false,ebay:true};
let shop_disp=['rakuten','ebay'];
let item_data=[];
let shop_select=document.getElementById('shop-select');
let order_select=document.getElementById('order-select');
/*
let label=document.createElement('label');
let input=document.createElement('input');
let name=document.createElement('input');
let text=document.createTextNode(message);
*/
let v = new URLSearchParams(window.location.search);
let keyword=v.get('search-key');
//console.log('keyword: '+keyword);
let v_shop_disp=v.getAll('shop-disp[]');
let v_order=v.get('order-select');
if(v_shop_disp!=null)shop_disp=v_shop_disp;
if(v_order!=null)order=v_order;

for(let key in shop_item){
    shop_select.insertAdjacentHTML('beforeend','<label><input type="checkbox" name="shop-disp[]" value="'+key+'"'+(shop_disp.includes(key) ? ' checked' : '')+'>'+shop_item[key]+'　</label>');
}
for(let key in order_str){
    order_select.insertAdjacentHTML('beforeend','<option value='+key +(key === order ? ' selected' : '')+'>'+order_str[key]+'</option>');
}
search();
//cart_update(); // searchよりも前に実行されてしまう模様 searchのdoneに入れる

$(function(){
    $('#shop-select').on("click",function(){
        /*
        let vals=[];
        //vals=$('#shop-select input').map(function(){
        vals=$(this).children('label').map(function(){
                return $(this).children('input').prop('checked');
        })
        console.log(vals);
        //console.log(vals[2]);
        let i=0;
        for(let item in shop_disp){
            shop_disp[item]=vals[i];
            //console.log(item,vals[i]);
            i++;
        }
        console.log(shop_disp);
        search();
        */
       document.getElementById('searchbtn').click();
    })
})

$(function(){
    $('#order-select').on("change",function(){
        //let vals=$(this).val();
        /*
        order=$(this).val();
        console.log(order);
        search();
        */
       document.getElementById('searchbtn').click();
    })
})

function search(){
    if(keyword!='' && keyword!=null){
      /*
        console.log(keyword);
        console.log(shop_disp);
        console.log(order);
        */

        let item_list=document.getElementById('item-list');
        let searchtextbox=document.getElementById('searchtextbox');
        $("#loading").fadeIn(); // ローディング表示
        searchtextbox.value=keyword;
        while(item_list.firstChild){
            item_list.removeChild(item_list.firstChild); // 子要素を全部削除
        }
        if(!shop_disp.length){ // shop_dispが空の場合、子要素を全部削除するだけで終わる
            console.log("shop none")
            $("#loading").fadeOut(100);
            return;
        }
        $.post({
            url: 'price_comparison_ajax.php',
            data:{
                //"shop-disp": {"rakuten":true,"amazon":true,"ebay":true},
                "shop-disp": shop_disp,
                "keyword":keyword,
                "order":order,
            },
            dataType: 'json' //必須。json形式で返すように設定
        }).done(function(data){ // ここでitem_dataを使ってしまうと、item_dataはこのブロック内だけで使える変数となってしまう。
            //console.log(data[0]['url'])
            //console.log(data)

            //連想配列のプロパティがそのままjsonオブジェクトのプロパティとなっている
            //$("#pos").text(data.position); //取得した集合場所の値を、html上のID値がposの箇所に代入。
            //$("#time").text(data.ap_time); //取得した集合時刻の値を、html上のID値がtimeの箇所に代入。

            //let frag=document.createDocumentFragment();
            /*
            let td_cnt = 0;
            while(item_list.firstChild)item_list.removeChild(item_list.firstChild); // 子要素を全部削除
            item_list.insertAdjacentHTML('beforeend','<tr>');
            */
            let disp_str='';
            let id=0;
            item_data=data; // グローバルスコープのitem_dataにブロック内の変数であるdataの値を代入
            //console.log(item_data.length);
            $("#loading").fadeOut(100,function(){
              if(item_data.length===0){
                disp_str="検索した結果何も見つかりませんでした。";
                //console.log("検索した結果何も見つかりませんでした。");
              }
              else{
                for(let item of item_data){
                  //console.log(item)
                  disp_str=disp_str.concat('<div class="item col-xs-3 lazyload"><a href="'
                  +item['url']+'" target="_blank"><div class="img-block"><img src="'
                  +item['image']+'"></div><div class="pos-left">'
                  +'['+shop_item[item['shop']]+'] '
                  +item['title']+'</div><div class="pos-left a-price">'
                  +"￥"+item['price']+'</div></a>');

                  /*
                  item_list.insertAdjacentHTML('beforeend',
                  '<div class="item"><a href="'
                  +item['url']+'" target="_blank"><div class="img-block"><img src="'
                  +item['image']+'"></div><div>'
                  +item['title']+'</div><div>'
                  +"￥"+item['price']+'</div></a></div>');
                  */

                  disp_str=disp_str.concat('<div class="input-group-btn"><select id=goods'+id+'>');
                  for (let i = 1; i < 10; i++){
                      disp_str=disp_str.concat('<option value="'+i+'">'+i+'</option>');
                  } // 個数選択
                  disp_str=disp_str.concat('</select><button type="button" class="add_goods btn-warning" value="'+id+'">カートに入れる</button></div></div>');
                  id++;
                }
              }
              //console.log(disp_str)
              item_list.insertAdjacentHTML('beforeend',disp_str);
              //console.log(data[1])
              //console.log(data[1].image)
              
              cart_update(); // searchが終わってから実行するようにする
            });

        }).fail(function(XMLHttpRequest, textStatus, errorThrown){
            alert(errorThrown);
        })
    }
}
function cart_update(add_data={}){
  // 引数が何もないときphpのsessionのカート情報を呼び出すだけの機能になる
  $.post({
    url: 'add_cart.php',
    data:add_data,
    dataType: 'json' //必須。json形式で返すように設定
  }).done(function(data){
    let cart=data;
    //console.log(cart);
    //console.log(item_data);
    
    for(let key in cart){
      let id=item_data.findIndex(u=>((u.item_id)==key));
      //console.log(item_data);
      //console.log(key);
      //console.log(id);
      //console.log(cart[key].num);
      if(id!=-1){
      //if(item_data.item_id){
        $('.add_goods[value='+id+']').text('追加済み');
        $("#goods"+id).val(cart[key].num);
        //console.log($("#goods"+id));
      }
    }
    
  }).fail(function(XMLHttpRequest, textStatus, errorThrown){
      alert(errorThrown);
  })  
}

// 商品をカートに入れる
$(function() {
  let goods = new Object();
  $(document).on('click',".add_goods",function(){
    let id=$(this).val();
    let data=item_data[id];
    //console.log(id);
    //console.log(data);
    let quantity  = $("#goods"+id).val();        //選択した数量
    let sub_total = data.price * quantity;  //単価 * 数量
    goods[id] = {
      'name':data.title,
      'price':data.price,
      'quantity':quantity,
      'sub_total':sub_total
    };

    //console.log("#goods"+id);
    //console.log(quantity);
    //console.log(sub_total);
    /*
    add_data={
      "product_url": data.url,
      "product_name":data.title,
      "num":quantity,
    };
    */
    //add_data=JSON.stringify(item_data[id]); // 下手にjsonにする必要はない
    add_data=item_data[id]; // 下手にjsonにする必要はない
    add_data.num=quantity; // item_dataにはnumがないため、numを追加
    //console.log(add_data);
    cart_update(add_data);
    //cart_update(item_data[id]); // そのままitem_data[id]を送るとnumが送れない
    cart_open();
  });
         
  let cart_open = function(){
    $("#goods_list").html('');
    
    let html = '<ul>';
    let key;
    let total = 0;
    for (key in goods){
      html  += '<li>'+goods[key].name+' 個数:'+goods[key].quantity+' &nbsp;&nbsp;'+comma( goods[key].sub_total )+'円</li>';
      total += goods[key].sub_total;
    }
    html += '</ul>';
    html += '<div id="total">合計:'+comma( total )+'円</div>';
    
    //オブジェクトなのでそのままではPOSTの出来ないためJSON形式にする
    let data = JSON.stringify(goods);
    
    $("#goods_list").html(html); //上部カートにHTMLを挿入
    $("#cart_detail").show();    //カートを開く
    $("#data").val(data);        //POST[data]にカートの中身をセット
  }
  
  $(document).on('click',"#go_cart a",function(){ // フォームをPOSTする
    document.form.submit();
  });
  $(document).on('click',"#close_cart span",function(){ // カートを閉じる
    $("#cart_detail").hide();
  });
    
});

function comma(num) { // 3桁ごとにカンマ
    return num.toString().replace( /([0-9]+?)(?=(?:[0-9]{3})+$)/g , '$1,' );
} 
  