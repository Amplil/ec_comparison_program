let shop_item ={rakuten:'楽天',amazon:'Amazon',ebay:'ebay'}; // 表示の順番
let order_item = ['人気順', '価格の安い順', '価格の高い順']; // 表示の順番
let order = '価格の安い順';
//let shop_disp={rakuten:true,amazon:false,ebay:true};
let shop_disp=["rakuten","ebay"];
let item_data=[];
var shop_select=document.getElementById('shop-select');
var order_select=document.getElementById('order-select');
/*
var label=document.createElement('label');
var input=document.createElement('input');
var name=document.createElement('input');
var text=document.createTextNode(message);
*/
let v = new URLSearchParams(window.location.search);
keyword=v.get('search-key');
v_shop_disp=v.getAll('shop-disp[]');
v_order=v.get('order-select');
if(v_shop_disp!=null)shop_disp=v_shop_disp;
if(v_order!=null)order=v_order;

for(var key in shop_item){
    shop_select.insertAdjacentHTML('beforeend','<label><input type="checkbox" name="shop-disp[]" value="'+key+'"'+(shop_disp.includes(key) ? ' checked' : '')+'>'+shop_item[key]+'　</label>');
}
for(var item of order_item){
    order_select.insertAdjacentHTML('beforeend','<option value='+item +(item === order ? ' selected' : '')+'>'+item+'</option>');
}
search();

$(function(){
    $('#shop-select').on("click",function(){
        /*
        var vals=[];
        //vals=$('#shop-select input').map(function(){
        vals=$(this).children('label').map(function(){
                return $(this).children('input').prop('checked');
        })
        console.log(vals);
        //console.log(vals[2]);
        var i=0;
        for(var item in shop_disp){
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
        //var vals=$(this).val();
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

        var item_list=document.getElementById('item-list');
        let searchtextbox=document.getElementById('searchtextbox');
        $("#loading").fadeIn(); // ローディング表示
        searchtextbox.value=keyword;
        while(item_list.firstChild){
            item_list.removeChild(item_list.firstChild); // 子要素を全部削除
        }
        if(!shop_disp.length){ // shop_dispが空の場合、子要素を全部削除するだけで終わる
            console.log("shop none")
            $("#loading").fadeOut();
            return;
        }
        $.post({
            url: 'price_comparison_ajax.php',
            data:{
                //"shop-disp": {"rakuten":true,"amazon":true,"ebay":true},
                "shop-disp": shop_disp,
                "keyword":keyword,
                "order":order,
                "order-item":order_item
            },
            dataType: 'json' //必須。json形式で返すように設定
        }).done(function(data){
            //console.log(data[0]['url'])
            //console.log(data)

            //連想配列のプロパティがそのままjsonオブジェクトのプロパティとなっている
            //$("#pos").text(data.position); //取得した集合場所の値を、html上のID値がposの箇所に代入。
            //$("#time").text(data.ap_time); //取得した集合時刻の値を、html上のID値がtimeの箇所に代入。

            //var frag=document.createDocumentFragment();
            /*
            var td_cnt = 0;
            while(item_list.firstChild)item_list.removeChild(item_list.firstChild); // 子要素を全部削除
            item_list.insertAdjacentHTML('beforeend','<tr>');
            */
            var disp_str='';
            var id=0;
            item_data=data;
            for(var item of item_data){
              //console.log(item)

              disp_str=disp_str.concat('<div class="item"><a href="'
              +item['url']+'" target="_blank"><div class="img-block"><img src="'
              +item['image']+'"></div><div>'
              +item['title']+'</div><div>'
              +"￥"+item['price']+'</div></a>');

              /*
              item_list.insertAdjacentHTML('beforeend',
              '<div class="item"><a href="'
              +item['url']+'" target="_blank"><div class="img-block"><img src="'
              +item['image']+'"></div><div>'
              +item['title']+'</div><div>'
              +"￥"+item['price']+'</div></a></div>');
              */

              disp_str=disp_str.concat('<select id=goods'+id+'>');
              for (var i = 1; i < 10; i++){
                  disp_str=disp_str.concat('<option value="'+i+'">'+i+'</option>');
              } // 個数選択
              disp_str=disp_str.concat('</select><button type="button" class="add_goods" value="'+id+'">カートに入れる</button></div>');
              id++;
            }
            //console.log(disp_str)
            item_list.insertAdjacentHTML('beforeend',disp_str);
            //console.log(data[1])
            //console.log(data[1].image)
            
        }).fail(function(XMLHttpRequest, textStatus, errorThrown){
            alert(errorThrown);
        })
		$("#loading").fadeOut();
    }
}

// 商品をカートに入れる
$(function() {
  let goods = new Object();
  $(document).on('click',".add_goods",function(){
    var id=$(this).val();
    var data=item_data[id];
    var quantity  = $("#goods"+id).val();        //選択した数量
    var sub_total = data.price * quantity;  //単価 * 数量
    goods[id] = {
      'name':data.title,
      'price':data.price,
      'quantity':quantity,
      'sub_total':sub_total
    };

    console.log("#goods"+id);
    console.log(quantity);
    console.log(sub_total)
    
    cart_open();
  });
         
  var cart_open = function(){
    $("#goods_list").html('');
    
    var html = '<ul>';
    var key;
    var total = 0;
    for (key in goods){
      html  += '<li>'+goods[key].name+' 個数:'+goods[key].quantity+' &nbsp;&nbsp;'+comma( goods[key].sub_total )+'円</li>';
      total += goods[key].sub_total;
    }
    html += '</ul>';
    html += '<div id="total">合計:'+comma( total )+'円</div>';
    
    //オブジェクトなのでそのままではPOSTの出来ないためJSON形式にする
    var data = JSON.stringify(goods);
    
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
  