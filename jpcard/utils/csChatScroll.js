// [カスタムエイド 2026-10-25] チャット4画面(お客様チャット×2アプリ・スタッフルーム・個別チャット)で共通の
//   「最新のメッセージを、入力欄のすぐ上に出す」処理。ここ1か所を直せば4画面すべてに効く。
//
//   以前の作り:入力欄(sendBox)の位置を測ってスクロールしていた。iPhone はキーボードが出るとき、入力欄が
//   キーボードの上に動き終わるまで時間がかかるため、早く測ると「まだ画面の下にある入力欄」に合わせてしまい、
//   キーボードが出きると最新のメッセージがキーボード・入力欄の裏に隠れていた。
//   今の作り:入力欄の位置は測らず、「画面の高さ − キーボードの高さ − 入力欄の高さ」で入力欄の上端を計算する
//   (入力欄の高さはキーボードで変わらない)。キーボードが動き終わるころ(0.35秒後)にもう一度だけ合わせる。
//
//   使う画面の約束:メッセージ1件ごとに class="msgItem"、入力欄に class="sendBox"、data に kbH(キーボードの高さ px)と
//   csBoxPad(下の余白に使う入力欄の高さ px。初めは 0)を持ち、一番下の余白(blank)の高さを (csBoxPad || 最初の値) + kbH にする。
// [カスタムエイド 2026-10-26] 管理App は manifest.json の safearea(下の offset auto)で、画面(WebView)の下端が
//   ホームバーの分(iPhone で約34px)だけ上にある。キーボードの高さは画面のいちばん下から数えるので、その分を足して
//   入力欄の位置を出す(足していなかったため、管理App だけ最新のメッセージと入力欄の間が広く空いていた)。
//   Card-San App は safearea の指定がないので 0。
const WEBVIEW_ABOVE_HOME_BAR = false

export function csChatScroll(vm, again) {
    vm.$nextTick(() => {
        setTimeout(() => {
            const q = uni.createSelectorQuery()
            q.selectAll('.msgItem').boundingClientRect()
            q.select('.sendBox').boundingClientRect()
            q.selectViewport().scrollOffset()
            q.exec((r) => {
                const items = (r && r[0]) || []
                const box = r && r[1]
                const vp = (r && r[2]) || {}
                if (!items.length) {
                    uni.pageScrollTo({ scrollTop: 0, duration: 0 })
                    return
                }
                let winH = 0
                let homeBar = 0
                try {
                    const si = uni.getSystemInfoSync()
                    winH = si.windowHeight
                    if (WEBVIEW_ABOVE_HOME_BAR && si.platform == 'ios') homeBar = (si.safeAreaInsets && si.safeAreaInsets.bottom) || 0
                } catch (e) {}
                if (!winH) return
                // キーボードが出ていないときの画面の高さを覚えておく。Android などで、キーボードの分だけ画面(WebView)が
                // 縮む機種では、縮んだ分を差し引く(キーボードの高さを2回引かないように)
                const kb = vm.kbH || 0
                if (!kb || !vm._csWinH0) vm._csWinH0 = Math.max(vm._csWinH0 || 0, winH)
                const shrunk = Math.max(0, (vm._csWinH0 || winH) - winH)
                // キーボードのうち、画面(WebView)にかかっている分。ホームバーの分は画面の外なので引く
                const kbLeft = Math.max(0, kb - shrunk - (kb ? homeBar : 0))
                const boxH = box && box.height ? box.height : uni.upx2px(130)
                const gap = uni.upx2px(24) // 最新のメッセージと入力欄のすき間(12px)
                const boxTop = winH - kbLeft - boxH
                const last = items[items.length - 1]
                const top = (vp.scrollTop || 0) + last.bottom - (boxTop - gap)
                // 一番下の余白を、入力欄の高さ+すき間に合わせる(入力欄が改行で高くなっても、最新のメッセージが隠れない)
                const pad = Math.ceil(boxH + gap)
                if (vm.csBoxPad !== pad) vm.csBoxPad = pad
                // [カスタムエイド 2026-10-26] ほとんど同じ位置なら動かさない(送信のあと画面が上下に小さくぶれる対策)
                const cur = vp.scrollTop || 0
                if (Math.abs(top - cur) < 3) return
                uni.pageScrollTo({ scrollTop: Math.max(0, top), duration: 0 })
            })
        }, 30)
    })
    // キーボード・入力欄が動き終わるころに、もう一度だけ合わせる。
    // [カスタムエイド 2026-10-26] キーボードが出た・閉じたときだけにする(送信・受信のたびに2回目が走り、画面がぶれていた)
    const kbNow = vm.kbH || 0
    if (!again && kbNow !== vm._csLastKb) {
        vm._csLastKb = kbNow
        clearTimeout(vm._csT2)
        vm._csT2 = setTimeout(() => csChatScroll(vm, true), 350)
    }
}
