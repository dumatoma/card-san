// [カスタムエイド 2026-10-28] 通信をまとめる・やり直すための共通の部品(管理App・Card-San App で同じ)
//   起きたこと:10/27 に入れた「数をすぐ読み直す」が、読んだ・届いた・戻ったのたびに同じ問い合わせを重ねて出し(9秒で約55回)、
//   サーバーの回数の制限(当時 1分60回)を超えて断られ、一覧が空・会員証が undefined になった。

// csCoalesce(fn, gap):何回呼ばれても、gap ミリ秒に1回だけ fn を動かす。
//   ・しばらく動いていなければ、すぐ動かす(画面を開いたときに待たせない)
//   ・gap の間にまた呼ばれたら、gap がたってからもう1回だけ動かす(読んだ・届いたのあとの数を取りこぼさない)
//   ・force でない呼び出し(画面に戻った など)は、gap の間なら捨てる(同じ問い合わせを重ねない)
//   ・fn は Promise を返す。動いている間に呼ばれたら、終わってからもう1回だけ
export function csCoalesce(fn, gap) {
    gap = gap || 1500
    let last = 0, timer = null, running = false, again = false
    function run() {
        timer = null
        if (running) { again = true; return }
        running = true
        last = Date.now()
        let p = null
        try { p = fn() } catch (e) {}
        Promise.resolve(p).catch(() => {}).then(() => {
            running = false
            if (again) { again = false; call(true) }
        })
    }
    function call(force) {
        if (timer) return
        const since = Date.now() - last
        if (!running && since >= gap) { run(); return }
        if (!force) return
        timer = setTimeout(run, Math.max(50, gap - since))
    }
    return call
}

// csRetry(make, delays):make() が返す通信が失敗したら(返事がない・code が 200 でない)、少し待ってやり直す。
//   最後まで失敗したら null を返す(画面は「読み込めませんでした」を出す)
export function csRetry(make, delays) {
    delays = delays || [1500, 4000]
    return new Promise((resolve) => {
        let i = 0
        const go = () => {
            let p
            try { p = make() } catch (e) { p = Promise.reject(e) }
            Promise.resolve(p).then((res) => {
                if (res && res.code == 200) return resolve(res)
                if (res && res.code && res.code != 429 && res.code < 500) return resolve(res) // 断られた理由がはっきりしている返事(403 など)はやり直さない
                next()
            }, next)
        }
        const next = () => { if (i < delays.length) setTimeout(go, delays[i++]); else resolve(null) }
        go()
    })
}
