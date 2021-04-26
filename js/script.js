var mySwiper = new Swiper('.swiper-container', {

    // 無限ループのため
    loop: true,

    // 自動でスライドする間隔 5000: 5秒ごとにスライド
    autoplay: {
        delay: 5000,
    },

    // 画像下の丸いやつを動かすために必要
    pagination: {
        el: '.swiper-pagination',
    },

    // 右と左の矢印を押すとスライドさせる機能
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
})

// 「MENU」ボタンがクリックされた時
function menuopen() {

    // <nav></nav>を操作対象にするために、要素を取得
   const target = document.getElementById("menu");

   // ターゲットに「open」クラスを付加 or 消去
   target.classList.toggle("open");

   /*
    この部分の解説
    target.classList.toggle("open");

    toggleとは、
    トグルは、クラスを新しく追加したり消去したりします。

    今回の場合は、「open」という名前のクラスを追加したり消去したりします。

    「open」クラスがない時

        <nav></nav>

        この場合は「open」クラスを追加します。

    「open」クラスがある時

        <nav class="open"></nav>

        この場合は、反対に「open」クラスを消去します。


    つまり、「open」クラスの有無で、メニューの表示・非表示を、CSSと組み合わせて切り替えている
   */
}
