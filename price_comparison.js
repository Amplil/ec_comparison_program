let shop_item ={rakuten:'楽天',amazon:'Amazon',ebay:'ebay'}; // 表示の順番
let order_item = ['人気順', '価格の安い順', '価格の高い順']; // 表示の順番
let order = '価格の安い順';
//let shop_disp={rakuten:true,amazon:false,ebay:true};
let shop_disp=["rakuten","ebay"];
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
            for(var item of data){
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

              disp_str=disp_str.concat('<select id="'+id+'">');
              for (var i = 1; i < 10; i++){
                  disp_str=disp_str.concat('<option value="'+i+'">'+i+'</option>');
              } // 個数選択
              disp_str=disp_str.concat('</select><input type="button" class="add_goods" value="カートに入れる"><span class="glyphicon glyphicon-shopping-cart"></span> </button></div>');
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
  var goods = new Object();
  $(document).on('click',".add_goods",function(){
    var data      = $(this).data();          //ボタンに定義されているデータ
    var quantity  = $(data.id).val();        //選択した数量
    var sub_total = data.kakaku * quantity;  //単価 * 数量
           
         //オブジェクトを定義
           goods[data.number] = new Object();  //同じ商品を初期化
           goods[data.number] = {
              'name':data.name
             ,'kakaku':data.kakaku
             ,'quantity':quantity
             ,'sub_total':sub_total
           };
           
           cart_open();
         });
         
      /*
       * カートの中身のHTMLを作る
       */
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
        
      /*
       * フォームをPOSTする
       */
        $(document).on('click',"#go_cart a",function(){
          document.form.submit();
        });
        
      /*
       * カートを閉じる
       */
        $(document).on('click',"#close_cart span",function(){
          $("#cart_detail").hide();
        });
    
  });
  
  /*
   * 3桁ごとにカンマ
   */
    function comma(num) {
        return num.toString().replace( /([0-9]+?)(?=(?:[0-9]{3})+$)/g , '$1,' );
    } 
  