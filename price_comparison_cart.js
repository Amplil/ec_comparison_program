//let shop_item ={rakuten:'楽天',amazon:'Amazon',ebay:'ebay'}; // 表示の順番

const app=new Vue({
  el: '#app',
  data() {
    return {
      shop_item: {rakuten:'楽天',amazon:'Amazon',ebay:'ebay'},
      cartItems:[],
      loading: true,
      errored: false,
      pop_disabled: true,
      pop_nofade: true,
      pop_show: false,
      }
  },
  mounted() {
    //let item={};
    axios.post('add_cart.php',{})
    .then(response => {
      this.cartItems = response.data
    })
    .catch(error => {
      console.log(error);
    })
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
        this.cartItems = response.data
      })
      .catch(error => {
        console.log(error);
      })
    },
    copy() {
      let copy_text="";
      for(let id in this.cartItems){
        copy_text=copy_text
        .concat(this.cartItems[id].title)
        .concat(": ")
        .concat(this.cartItems[id].quantity)
        .concat("個\n");
      }
      //console.log(copy_text)
      //this.$copyText(this.$el.querySelector("#items").textContent).then(
      this.$copyText(copy_text).then(
          (e) => {
          //console.log("テキストコピー完了");
          //console.log(e);

          this.$refs.popover.$emit("enable");
          this.pop_show = true;
          setTimeout(() => {
            this.pop_show = false;
            this.$refs.popover.$emit("disable");
          }, 1000);
        },
        (e) => {
          console.log("コピーエラー");
          console.error(e);
        }
      );
    },
    cart_modify(id){
      // 引数が何もないときphpのsessionのカート情報を呼び出すだけの機能になる
      axios.post('add_cart.php',{
        item:{
          item_id:id,
          image:this.cartItems[id].image,
          url:this.cartItems[id].url,
          title:this.cartItems[id].title,
          price:this.cartItems[id].price,
          shop:this.cartItems[id].shop,
          quantity:this.cartItems[id].quantity,
        }
      })
      .then(response => {
        this.cartItems = response.data
      })
      .catch(error => {
        console.log(error);
      })
    },
  }
})

/*
setTimeout(function () {
  $('#copy-button')
  // tooltip設定
  .tooltip({
      trigger: 'manual',
      placement:'right'
  })
  // tooltip表示後の動作を設定
  .on('shown.bs.tooltip', function(){
      setTimeout((function(){
          $(this).tooltip('hide');
      }).bind(this), 1500);
  })
  // クリック時の動作を設定
  .on('click', function(){
      //$('#items').select();

      let copyTarget = document.getElementById("items");
      //document.write(copyTarget.value);
      let range = document.createRange();
      range.selectNode(copyTarget);
      window.getSelection().addRange(range);

      const copyResult = document.execCommand('copy');
      console.log(copyResult)
      // コピー結果によって表示変更
      if(copyResult){
          $('#copy-button').attr('data-bs-original-title', 'コピーしました');
      }else{
          $('#copy-button').attr('data-bs-original-title', 'コピー失敗しました');
      }
      // tooltip表示
      $(this).tooltip('show');
  });
}, 545*1.33); // 545ms timing to load jQuery.js + network estimated delay
*/
