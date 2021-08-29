//let shop_item ={rakuten:'楽天',amazon:'Amazon',ebay:'ebay'}; // 表示の順番
//let order_str = {'relevanceblender':'おすすめ順','review-rank':'人気順', 'price-asc-rank':'価格の安い順', 'price-desc-rank':'価格の高い順'}; // 表示の順番
let order = 'relevanceblender';
let shop_disp=['rakuten','ebay'];
//let item_data=[];

let v = new URLSearchParams(window.location.search);
let v_keyword=v.get('search-key');
let v_shop_disp=v.getAll('shop-disp[]');
let v_order=v.get('order-select');
//if(v_shop_disp!=null)shop_disp=v_shop_disp;
//if(v_order!=null)order=v_order;
    
Vue.filter('formatCurrency', function (value) { // カンマ区切り
  return '¥' + String(value).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
});

// カートコンポーネント
Vue.component('cart_detail', {
  template: `
    <div id="total">小計:{{total | formatCurrency}}円</div>
    <div id="goods_list">
      <div v-for="key in goods" class="item col-xs-3">
        <div class="img-block"><img src="key.image"></div>
        <i class="far fa-trash-alt"></i>
      </div>
    </div>
  `,
  props: ['goods'],
  computed: {
    total: function () {
      let total = 0;
      this.goods.forEach(item => {
        total += (item.price * item.quantity);
      });
      return total;
    }
  },
  methods: {
    // カートから削除
    remove(index) {
      this.goods.splice(index, 1)
    }
  }
});



const app=new Vue({
  el: '#app',
  data(){
    return{
      keyword:v_keyword,
      shop_disp:v_shop_disp,
      order:v_order,
      shop_item: {rakuten:'楽天',amazon:'Amazon',ebay:'ebay'}, // 表示の順番
      order_str: {'relevanceblender':'おすすめ順','review-rank':'人気順', 'price-asc-rank':'価格の安い順', 'price-desc-rank':'価格の高い順'}, // 表示の順番
      cartItems:[],
      cart_component:false,
      cart_text:'カートに入れる',
      item_data: [],
      loading: true,
      errored: false
    }
  },
  mounted() {
    axios.get('price_comparison_ajax.php', {
      params: {
        shop:this.shop_disp,
        keyword:this.keyword,
        order:this.order
      }
    })
    .then(response => {
      this.item_data = response.data
    })
    .catch(error => {
      console.log(error);
      this.errored = true
    })
    .finally(() => {
      this.loading = false
    })
  },
  methods: {
    // カートに追加
    addToCart(itemToAdd) {
      let existence = false;
      this.cart_component=true;
      this.cartItems.forEach(item => {
        if (item.item_id === itemToAdd.item_id) {
          existence = true;
        }
      });
      // 新規商品の場合は商品を追加
      if (existence === false) {
        // 通常のオブジェクトからVueオブジェクトを作り、追加する
        this.cartItems.push(Vue.util.extend({}, itemToAdd));
      }

    },
    cart_update(itemToAdd={}){
      // 引数が何もないときphpのsessionのカート情報を呼び出すだけの機能になる
      axios.get('add_cart.php', {
        params: {
          item_id:itemToAdd.item_id,
          image:itemToAdd.image,
          url:itemToAdd.url,
          title:itemToAdd.title,
          price:itemToAdd.price,
          shop:itemToAdd.shop,
          quantity:itemToAdd.quantity
        }
      })
      .then(response => {
        this.cartItems = response.data
      })
      .catch(error => {
        console.log(error);
      })
      .finally(() => {
        this.cart_text='追加済み'
      })
    },
    searchbtn_click(){ // サーチボタンをクリックしてサブミット
      document.getElementById('searchbtn').click();
    }
  }
});


/*
for(let key in shop_item){
    shop_select.insertAdjacentHTML('beforeend','<label><input type="checkbox" name="shop-disp[]" value="'+key+'"'+(shop_disp.includes(key) ? ' checked' : '')+'>'+shop_item[key]+'　</label>');
}
for(let key in order_str){
    order_select.insertAdjacentHTML('beforeend','<option value='+key +(key === order ? ' selected' : '')+'>'+order_str[key]+'</option>');
}
*/
//search();
//cart_update(); // searchよりも前に実行されてしまう模様 searchのdoneに入れる

/*
function search(){
    if(keyword!='' && keyword!=null){

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


                  disp_str=disp_str.concat('<div class="input-group-btn"><select id=goods'+id+'>');
                  for (let i = 1; i < 10; i++){
                      disp_str=disp_str.concat('<option value="'+i+'">'+i+'</option>');
                  } // 個数選択
                  disp_str=disp_str.concat('</select><button type="button" class="btn add_goods" value="'+id+'">カートに入れる</button></div></div>');
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
*/
/*
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
*/
/*
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
      'item_id':data.item_id,
      'image':data.image,
      'url':data.url,
      'title':data.title,
      'price':data.price,
      'shop':data.shop,
      'quantity':quantity,
      'sub_total':sub_total
    };

    //console.log("#goods"+id);
    //console.log(quantity);
    //console.log(sub_total);
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
    
    let html = '';
    let key;
    let total = 0;
    html += '<div>';
    for (key in goods){
      html += '<div class="item col-xs-3">';
      html += '<div class="img-block"><img src="'+goods[key].image+'"></div>';
      html += '<i class="far fa-trash-alt"></i></div>';
      total += goods[key].sub_total;
    }
    html += '</div>';
    html = '<div id="total">小計:'+comma( total )+'円</div>'+html;

    //オブジェクトなのでそのままではPOSTの出来ないためJSON形式にする
    //let data = JSON.stringify(goods);
    
    $("#goods_list").html(html); //上部カートにHTMLを挿入
    $("#cart_detail").show();    //カートを開く
    //$("#data").val(data);        //POST[data]にカートの中身をセット
  }
  
  $(document).on('click',"#close_cart span",function(){ // カートを閉じる
    $("#cart_detail").hide();
  });
    
});
*/

/*
function comma(num) { // 3桁ごとにカンマ
    return num.toString().replace( /([0-9]+?)(?=(?:[0-9]{3})+$)/g , '$1,' );
} 
*/
