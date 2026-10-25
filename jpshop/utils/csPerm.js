// [カスタムエイド 2026-10-23] カメラ・写真の許可を、写真の画面を開く前に確かめる
// 許可されていないとき、アプリの土台(DCloud)は英語の案内(Can not access photos in album など)を出すため、
// 先に確かめて日本語の案内を出す。「設定を開く」で、iPhone の設定アプリの Card-San管理 の画面を開く
// 使い方:checkPerm('camera' | 'album').then((ok) => { if (ok) uni.chooseImage(...) })

function guide(kind) {
    return new Promise((resolve) => {
        uni.showModal({
            title: kind == 'camera' ? 'カメラが使えません' : '写真を開けません',
            content: kind == 'camera'
                ? 'カメラの使用が許可されていません。設定アプリで「カメラ」をオンにしてください。'
                : '写真へのアクセスが許可されていません。設定アプリで「写真」を「すべての写真」または「選択した写真」にしてください。',
            confirmText: '設定を開く',
            cancelText: '閉じる',
            success: (r) => {
                if (r.confirm) openSettings()
                resolve(false)
            },
            fail: () => resolve(false)
        })
    })
}

export function openSettings() {
    // #ifdef APP-PLUS
    try {
        if (plus.os.name == 'iOS') {
            plus.runtime.openURL('app-settings:')
        } else {
            const main = plus.android.runtimeMainActivity()
            const Intent = plus.android.importClass('android.content.Intent')
            const Settings = plus.android.importClass('android.provider.Settings')
            const Uri = plus.android.importClass('android.net.Uri')
            const it = new Intent(Settings.ACTION_APPLICATION_DETAILS_SETTINGS)
            it.setData(Uri.fromParts('package', main.getPackageName(), null))
            main.startActivity(it)
        }
    } catch (e) {}
    // #endif
}

// 許可されている(またはまだ聞いていない)とき true。断られているときは日本語の案内を出して false
export function checkPerm(kind) {
    // #ifdef APP-PLUS
    try {
        if (plus.os.name == 'iOS') {
            let st = -1
            if (kind == 'camera') {
                const AV = plus.ios.importClass('AVCaptureDevice')
                st = AV.authorizationStatusForMediaType('vide') // 0 未確認 / 1 制限 / 2 拒否 / 3 許可
            } else {
                const PH = plus.ios.importClass('PHPhotoLibrary')
                st = PH.authorizationStatus() // 0 未確認 / 1 制限 / 2 拒否 / 3 許可 / 4 一部の写真
            }
            if (st == 1 || st == 2) return guide(kind)
            return Promise.resolve(true)
        }
        if (kind == 'camera') {
            return new Promise((resolve) => {
                plus.android.requestPermissions(['android.permission.CAMERA'], (r) => {
                    if (r.deniedAlways && r.deniedAlways.length) guide(kind).then(resolve)
                    else if (r.deniedPresent && r.deniedPresent.length) resolve(false)
                    else resolve(true)
                }, () => resolve(true))
            })
        }
    } catch (e) {}
    // #endif
    return Promise.resolve(true)
}
