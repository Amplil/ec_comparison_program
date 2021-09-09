//let shop_item ={rakuten:'楽天',amazon:'Amazon',ebay:'ebay'}; // 表示の順番
//let order_str = {'relevanceblender':'おすすめ順','review-rank':'人気順', 'price-asc-rank':'価格の安い順', 'price-desc-rank':'価格の高い順'}; // 表示の順番
//let order = 'relevanceblender';
//let shop_disp=['rakuten','ebay'];
//let item_data=[];

let v = new URLSearchParams(window.location.search);
/*
let v_keyword=v.get('keyword');
let v_shop_disp=v.getAll('shop[]');
let v_order=v.get('order');
*/
Vue.filter('formatCurrency', function (value) { // カンマ区切り
  return '¥' + String(value).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
});
// カートコンポーネント
const cm=Vue.component('cart_detail', {
  template: `
  <div>
    <div id="total">小計:{{total | formatCurrency}}円</div>
    <div id="goods_list">
      <div v-for="(item, index) in citems" class="item col-xs-3">
        <div class="img-block">
          <img v-bind:src="item.image">
          <div>
            {{item.quantity}}個
            <button @click="delete_cart([index])"><i class="far fa-trash-alt"></i></button>
          </div>
        </div>
      </div>
    </div>
  </div>
  `,
  props: ['citems'],
  computed: {
    total: function () {
      let total = 0;
      for(let id in this.citems){
        total += (this.citems[id].price * this.citems[id].quantity);
      }
      return total;
    }
  },
  methods: {
    delete_cart(del_list) { // カートから削除
      /*
      del_list.forEach(id=>{
        delete this.citems[id];
      });
      */
      axios.post('delete_cart.php',{
        del_list
      })
      .then(response => {
        app.cartItems = response.data
      })
      .catch(error => {
        console.log(error);
      })
    }
  }
});

const app=new Vue({
  el: '#app',
  data(){
    return{
      keyword:v.get('keyword'),
      shop_disp:v.getAll('shop[]'),
      order:v.get('order'),
      shop_item: {rakuten:'楽天',amazon:'Amazon',ebay:'ebay'}, // 表示の順番
      order_str: {'relevanceblender':'おすすめ順','review-rank':'人気順', 'price-asc-rank':'価格の安い順', 'price-desc-rank':'価格の高い順'}, // 表示の順番
      cartItems:[],
      //cart_component:false,
      //cart_text:'カートに入れる',
      items: [],
      loading: true,
      errored: false,
      item_list:false
    }
  },
  mounted() {
    this.cart_update();
    if(
      this.keyword!==null && 
      this.order!==null && 
      this.keyword!=='' && 
      this.order!=='' && 
      this.shop_disp.length!==0
    ){
      axios.get('price_comparison_ajax.php', {
        params: {
          keyword:this.keyword,
          shop:this.shop_disp,
          order:this.order
        }
      })
      .then(response => {
        this.items = response.data
        this.items.forEach(item => {
          item.quantity=1; // quantityプロパティを追加
          /*
          item.existence = false; // existence(カートにあるかどうか)プロパティを追加

          for(let cart_id in this.cartItems){
            if (cart_id === item.item_id) {
              item.existence = true;
            }
          }
          */
          /*
          this.cartItems.forEach(citem => {
            if (citem.item_id === item.item_id) {
              item.existence = true;
            }
          });
          */
        });
      })
      .catch(error => {
        console.log(error);
        this.errored = true;
      })
      .finally(() => {
        this.loading = false;
        this.item_list=true;
      })
    }
    else{
      this.loading = false;
    }
  },
  methods: {
    cart_update(item={}){
      // 引数が何もないときphpのsessionのカート情報を呼び出すだけの機能になる
      //let cartItem={}
      if (item!=={}){
        //item.existence=true;
        /*
        cartItem={
          item_id:itemToAdd.item_id,
          image:itemToAdd.image,
          url:itemToAdd.url,
          title:itemToAdd.title,
          price:itemToAdd.price,
          shop:itemToAdd.shop,
          quantity:itemToAdd.quantity
        }
        */
        /*
        if (itemToAdd.existence === false) { // 新規商品の場合は商品を追加
          this.cartItems.push(Vue.util.extend({}, cartItem)); // 通常のオブジェクトからVueオブジェクトを作り、追加する
          //this.cart_text='追加済み';
          itemToAdd.existence=true;
        }
        */
      }
      axios.post('add_cart.php',{
        /*
        item_id:itemToAdd.item_id,
        image:itemToAdd.image,
        url:itemToAdd.url,
        title:itemToAdd.title,
        price:itemToAdd.price,
        shop:itemToAdd.shop,
        quantity:itemToAdd.quantity
        */
        item
      })
      .then(response => {
        this.cartItems = response.data
      })
      .catch(error => {
        console.log(error);
      })
      .finally(() => {
        //this.cart_text='追加済み'
        //this.cart_component=(this.cartItems.length!==0 ? true : false); //カートにアイテムが入っている場合は表示
      })
    },
    searchbtn_click(){ // サーチボタンをクリックしてサブミット
      document.getElementById('searchbtn').click();
    },
  }
});
