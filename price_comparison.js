//let shop_item ={rakuten:'楽天',amazon:'Amazon',ebay:'ebay'}; // 表示の順番
//let order_str = {'relevanceblender':'おすすめ順','review-rank':'人気順', 'price-asc-rank':'価格の安い順', 'price-desc-rank':'価格の高い順'}; // 表示の順番
let order = 'relevanceblender';
let shop_disp=['rakuten','ebay'];
//let item_data=[];

let v = new URLSearchParams(window.location.search);
let v_keyword=v.get('keyword');
let v_shop_disp=v.getAll('shop[]');
let v_order=v.get('order');

Vue.filter('formatCurrency', function (value) { // カンマ区切り
  return '¥' + String(value).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
});

// カートコンポーネント
Vue.component('cart_detail', {
  template: `
  <div>
    <div id="total">小計:{{total | formatCurrency}}円</div>
    <div id="goods_list">
      <div v-for="key in goods" class="item col-xs-3">
        <div class="img-block"><img src="key.image"></div>
        <i class="far fa-trash-alt"></i>
      </div>
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
