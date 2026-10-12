<script>
	export default {
		onLaunch: function() {
			console.log('App Launch')
		},
		onShow: function() {
			console.log('App Show')
		},
		onHide: function() {
			console.log('App Hide')
		}
	}
</script>

<style lang="scss">
    @import "@/uni_modules/uview-ui/index.scss";
	/*每个页面公共css */
    @font-face {
        font-family: Hiragino Sans-W5;
        src: url('~@/static/fonts/NotoSansJP-Bold.otf');
    }
    
    @font-face {
        font-family: Hiragino Sans-W3;
        src: url('~@/static/fonts/NotoSansJP-Regular.otf');
    }
    
    @font-face {
        font-family: Hiragino Sans;
        src: url('~@/static/fonts/NotoSansJP-Regular.otf');
    }
    page{
      font-family: Hiragino Sans-W3, Hiragino Sans, Hiragino Sans-W5;  
    }
    
    body{
        font-family: Hiragino Sans-W3, Hiragino Sans, Hiragino Sans-W5;  
    }

    // [カスタムエイド 2026-10-12] ページ端での引っ張り(バウンス)と、引っ張って再読み込みを止める。予約の途中で入力が消えないようにするため
    html,
    body{
        overscroll-behavior-y: none;
    }

    // [カスタムエイド 2026-10-13] タブレット・PC(481px以上)では、中央に幅480pxで表示する(部品の大きさの基準は pages.json の rpxCalc 480)
    @media (min-width: 481px) {
        html,
        body {
            background: #ececf0 !important;
        }
        uni-app,
        uni-page,
        uni-page-wrapper {
            background: transparent !important;
        }
        uni-page-body {
            display: block;
            max-width: 480px;
            margin: 0 auto;
            min-height: 100vh;
            background: #fff;
            box-shadow: 0 0 24px rgba(0, 0, 0, .08);
        }
        // 画面の上下に固定されている部分も同じ幅にそろえる
        .u-navbar--fixed,
        .fix-btn,
        .fix-btn-ok,
        .uni-actionsheet,
        .u-popup__content {
            left: 50% !important;
            right: auto !important;
            width: 100% !important;
            max-width: 480px;
            transform: translateX(-50%);
        }
        // 画面幅(100vw)で作られている部分を、中央の幅に収める
        uni-page-body .date,
        uni-page-body .con:has(> .manager),
        uni-page-body [style*="100vw"] {
            width: 100% !important;
            max-width: 100% !important;
        }
    }

    // [カスタムエイド 2026-10-13] 押したときの見た目(日付・時間の枠・ボタン)。PCはマウスを乗せると色が変わる
    .dd,
    .cs-slot,
    .btn1,
    .fix,
    .fix-b,
    .con-btns {
        -webkit-tap-highlight-color: transparent;
        cursor: pointer;
        transition: transform .08s ease, background-color .12s ease, filter .12s ease;
    }
    .dd:active {
        transform: scale(.92) !important;
        background: rgba(26, 115, 232, .14) !important;
        border-radius: 10px;
    }
    // 時間の枠: 枠全体を押せるようにして、押したとき・マウスを乗せたときに枠の色が変わる
    .dateItem .cs-slot {
        display: block !important;
        width: 100% !important;
        height: 100% !important;
        border-radius: 4px;
    }
    .dateItem .cs-slot > div {
        background-size: auto 62% !important;
        background-position: center !important;
        background-repeat: no-repeat !important;
    }
    .cs-slot:active {
        transform: scale(.92) !important;
        background-color: rgba(26, 115, 232, .22) !important;
    }
    .btn1:active,
    .fix:not(.fixed):active,
    .fix-b:active,
    .con-btns:active {
        transform: scale(.97) !important;
        filter: brightness(.9) !important;
    }
    @media (hover: hover) {
        .dd:hover {
            background: rgba(26, 115, 232, .08) !important;
            border-radius: 10px;
        }
        .cs-slot:hover {
            background-color: rgba(26, 115, 232, .10) !important;
        }
        .btn1:hover,
        .fix:not(.fixed):hover,
        .fix-b:hover,
        .con-btns:hover {
            filter: brightness(.95) !important;
        }
    }
    
</style>
