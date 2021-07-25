let shop_item ={rakuten:'楽天',amazon:'Amazon',ebay:'ebay'}; // 表示の順番

$.post({
  url: 'add_cart.php',
  data:add_data={}, // 引数が何もないときphpのsessionのカート情報を呼び出すだけの機能になる
  dataType: 'json' //必須。json形式で返すように設定
}).done(function(data){
  let cart=data;
  let items_ele=document.getElementById('items');
  //console.log(cart);
  //console.log(item_data);

  let disp_str='';
  for(let id in cart) {
    disp_str=disp_str.concat(
      '<tr><td>'+cart[id].title+'</td>'
      +'<td>'+cart[id].num+'個</td>'
      +'<td><select id='+id+'>');
    for (let i = 1; i < 10; i++){
      disp_str=disp_str.concat('<option value="'+i+'">'+i+'</option>');
    } // 個数選択
    disp_str=disp_str.concat(
      '</select></td>'
      +'<td><button type="button" class="btn add_goods">削除</button></td>'
      +'</tr>');
  }
  items_ele.insertAdjacentHTML('beforeend',disp_str);

}).fail(function(XMLHttpRequest, textStatus, errorThrown){
    alert(errorThrown);
})  


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
  
function comma(num) { // 3桁ごとにカンマ
    return num.toString().replace( /([0-9]+?)(?=(?:[0-9]{3})+$)/g , '$1,' );
} 

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

/*
function copyToClipboard() {
  var copyTarget = document.getElementById("items");
  //document.write(copyTarget.value);
  var range = document.createRange();
  range.selectNode(copyTarget);
  window.getSelection().addRange(range);

  //copyTarget.select();

  document.execCommand("Copy");

  alert("コピーできました！");
}
*/
