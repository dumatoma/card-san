# バグ修正記録

フロントエンド・バックエンドを横断して記載。日付ごとにまとめる。

---

## 2026-03-12

### 1. API エラーが握りつぶされ、画面が無反応になる

**影響：** 通信エラーや認証エラーが発生しても、画面に何も表示されず処理が止まる

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpcard・jpshop・outsiteAppointment/utils/request.js` | エラーハンドラで `return` なし → `Promise.reject` が破棄される | `return Promise.reject(error)` に修正 |
| `jpcard/utils/request.js` | `Message.error()` を呼び出しているが import がコメントアウト済み → `ReferenceError` | 該当行を削除 |
| `jpcard・jpshop・outsiteAppointment/utils/request.js` | ネットワークエラー時に `Promise.resolve(error.response)` でエラーを正常扱い | `Promise.reject(error)` に修正 |

---

### 2. ネイティブアプリで 401 後もログイン状態が続く

**影響：** トークン期限切れや不正アクセス時にログアウトされず、以降のリクエストがすべて 401 になり続ける

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpcard・jpshop・outsiteAppointment/utils/request.js` | `localStorage.removeItem()` を使用（uni-app ネイティブ・WeChat では非対応） | `uni.removeStorageSync()` に変更 |

---

### 3. チャット画面で WebSocket が切断後に再接続されない

**影響：** バックグラウンドからの復帰やネットワーク切替後、リアルタイム通知が届かなくなる

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpcard・jpshop/common/socket.js` | `onClose` 内に `return;` があり、以降のクリーンアップ・再接続処理がデッドコード | `return;` を削除 |
| `jpcard・jpshop/common/socket.js` | 再接続条件 `traderDetailIndex==2 \|\| accountStateIndex==0 \|\| followFlake` がコンストラクタ初期値と一致せず永遠に再接続しない | `if (!this.is_open_socket)` に簡略化 |
| `jpcard・jpshop/store/index.js` | WebSocket メッセージの `JSON.parse()` が try-catch なし → 不正 JSON でアプリクラッシュ | try-catch を追加 |
| `jpcard・jpshop/store/index.js` | `result.data.client_id` の null ガードなし → 接続直後に `TypeError` | `result.data &&` の条件を追加 |
| `jpshop/store/index.js` | `bindSocket()` の Promise に `.catch()` なし → 失敗時に Unhandled Promise Rejection | `.catch()` ハンドラを追加 |

---

### 4. 認証コード再送時のトースト表示が壊れる

**影響：** メール認証画面で「再送」を押すと、トーストに `undefined` または空文字が表示される

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpRegister/pages/secondStep/secondStep.vue` | `success` コールバック内で未定義変数 `code` を参照（正しくは `that.code`） | `that.code` に修正 |

---

### 5. エラーメッセージが中国語で表示される

**影響：** 日本語アプリなのに HTTP ステータスエラー時のトーストが中国語になる

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpcard・jpshop・outsiteAppointment/utils/request.js` | switch-case のエラーメッセージが中国語（例：「服务器错误」） | すべて日本語に翻訳（例：「サーバーエラーが発生しました」） |

---

### 6. Stripe プラン変更時に二重課金が発生する

**影響：** 店舗がプランを変更すると、旧プランのサブスクリプションが残ったまま新サブスクリプションも課金される

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `app/Http/Controllers/Api/Shop/VipController.php` | Stripe 分岐で早期 `return` しており、旧サブスクリプションのキャンセル処理（`CreateSubscriptionsEndJob`）が一切実行されなかった。また注文の `is_subscription` も更新されず重複した支払いリンクを生成できた | Stripe でも Apple/Google Pay と同様に、支払いリンク返却前に注文を更新し `CreateSubscriptionsEndJob` を dispatch するよう修正 |

---

### 7. Stripe 追加オプション購入後に店舗設定の再入力が必要になる

**影響：** Stripe の追加カウント購入（メッセージ・クーポン・SMS）が完了してもアプリが未払いと判断し、設定入力画面へ再誘導される

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `resources/views/pay/show/stripe.blade.php` | 支払い成功後にメッセージを表示するだけで、バックエンドの注文ステータスを更新していなかった。アプリが WebView から戻った時点で注文が `status=0` のまま残るため未払い扱いになる | 支払い成功時に `/api/pay/stripe/vip_count_complete` を呼び出してバックエンドを更新するよう修正 |
| `app/Http/Controllers/Api/PayController.php` | 上記コールバックに対応するエンドポイントが存在しなかった | `stripeVipCountComplete()` を新設。`client_secret` で注文を特定し Stripe 側で決済ステータスを検証した上で注文・店舗カウント・支払いログを更新 |
| `app/Libs/StripePay.php` | `retrievePaymentIntent()` メソッドが存在しなかった | メソッドを追加 |
| `routes/api.php` | 上記エンドポイントのルートが未登録 | `POST /api/pay/stripe/vip_count_complete` を追加 |

---

### 8. Apple Pay で購入しても商品が付与されない

**影響：** 課金は完了するが店舗プランや追加カウントが反映されない。低速回線では特に発生しやすい

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpshop/manifest.json` | Apple IAP タイムアウトが 10 秒 → 低速回線で課金後にセッション切断 | `"timeout": 30000` に変更 |
| `app/Libs/ApplePay.php` | IAP 共有シークレットがソースコードにハードコード → 漏洩時にレシート偽造が可能 | `.env` の `APPLE_IAP_PASSWORD` から読み込むよう変更 |
| `app/Http/Controllers/Api/Shop/VipCountController.php` | `getTransactionInfo()` の失敗判定が `if(!$res)` → 配列は常に truthy のため失敗を検出できずクラッシュ | `if(!$res['success'])` に修正 |
| `app/Http/Controllers/Api/Shop/VipCountController.php` | Apple / Google Pay エラー時に生配列 `return $res` → HTTP 200 が返りフロントが成功と誤認 | `return responseToJson(400, $res['message'])` に修正 |
| `app/Services/ShopVipService.php` | `applePaySubscriptions()` 内で同じ `if(!$res)` 判定ミス | `if(!$res['success'])` に修正 |
| `サーバー .env` | `APPLE_IAP_PASSWORD` キーが未定義 → ApplePay クラスが `null` を Apple に送信していた | `APPLE_IAP_PASSWORD=...` を追加 |

---

### 9. プラン解約時にサーバーエラー 500 が発生する

**影響：** 解約操作中に DB エラーが起きると、エラーハンドラ自体がクラッシュして原因不明の 500 が返る

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `app/Http/Controllers/Api/Shop/VipController.php` | `catch (\Exception $e)` で捕捉しているのに `$th->getMessage()` を参照（`$th` は未定義）→ 二重エラー | `$e->getMessage()` に修正 |

---

### 10. 本番環境にデバッグコードが残存

**影響：** Apple Pay Webhook レスポンスにデバッグ出力が混入し通知処理が正常に完了しない

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `app/Libs/ApplePay.php` | `notify()` 内に `dump($decoded)` / `dump($child_decoded)` が残存 | 削除 |
| `app/Http/Controllers/Api/Shop/VipCountController.php` | デッドコード内に `dd($res['output'])` が残存 | デッドコードごと削除 |

---

### 11. Apple IAP 解約後に「継続する」を押すと新規課金が発生する

**影響：** 管理画面から Apple IAP サブスクリプションを解約操作すると、店舗アプリの「継続する」ボタンで新規 Apple IAP 課金が発生する。また成功メッセージも表示されない

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `app/Http/Controllers/Api/Shop/VipController.php` | 「継続する」ボタンが通常の新規購読フローに遷移するため Apple IAP が重複起動され新たな課金が発生。resume 用エンドポイントも存在しなかった | `store()` の冒頭に Apple IAP の resume 判定を追加。期間内かつ `cancel_time > 0` の場合、新規注文を作成せず `cancel_time=0, is_subscription=1` にリセットしてコード 201 を返す |

---

## 2026-05-01

### 12. ネイティブ決済（Apple IAP / Google Pay）の複数バグ

**影響：** 漏単（課金済みなのにプランが付与されない）・クラッシュ・二重支払い・UI フリーズが発生する可能性があった

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpshop/pages/payment/payment.vue:265` | Apple IAP の `buyPlans()` に `.catch()` がなく、ネットワーク異常時に loading が永久表示されたまま漏単が発生 | `.catch()` を追加し `uni.hideLoading()` とエラートースト表示 |
| `jpshop/pages/payment/payment.vue:406` | Google Pay の `buyPlans()` も同様に `.catch()` なし | 同上 |
| `jpshop/pages/payment/payment.vue:398` | Google Pay の `payAll` コールバックで失敗時に何もしない（ユーザーに無反応） | `else` ブランチを追加してエラートーストを表示 |
| `jpshop/pages/payment/payment.vue:382` | `subscriptionOfferDetails[1]` を決め打ちで参照（要素 1 件のとき `undefined` → クラッシュ） | `basePlanId` による `forEach` 照合に統一。一致なしの場合は `[0]` にフォールバック |
| `jpshop/pages/payment/payment.vue:402` | `e.data[0].original.purchaseToken` を配列長チェックなしで参照（空配列でクラッシュ） | `e.data` の長さと `e.data[0].original` の存在を確認してからアクセス |
| `jpshop/pages/payment/payment.vue:400` | Google Pay が `transaction_id` を送らず対帳不能 | `e.data[0].original.orderId` を `da.transaction_id` に追加 |
| `jpshop/pages/payment/payment.vue:286` | Apple IAP の `fail` コールバックで Error オブジェクトをそのまま渡し `[object Object]` 表示 | 固定の日本語メッセージに変更 |
| `jpshop/pages/payment/payment.vue:241` | `requestOrder` のエラータイトルが英語 `"warning"` | 日本語 `"エラー"` に変更 |
| `jpshop/pages/payment/payment.vue:433` | `querySku` 失敗時に `uni.hideLoading()` がなく UI がフリーズ | 失敗ブランチ先頭に `uni.hideLoading()` を追加 |
| `jpshop/pages/payment/payment.vue:413` | Google Pay 成功後の遷移方法が Apple IAP と不統一 | Google Pay も `uni.reLaunch` / 2000ms に統一 |
| `jpshop/pages/payment/payment.vue:data` | 支払いボタン連打で `requestPayment` が複数起動する競合状態 | `paying` フラグを追加し `pay()` 入口でガード |

---

### 13. サブスクリプション終了後に再購読できない

**影響：** Stripe（card_type=1）で契約していたユーザーがサブスクリプション期限切れ後にモバイルアプリから再購読しようとすると、「PCブラウザでお手続きください」と表示されて再購読できない

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpshop/pages/payment/payment.vue:481` | `card_type == 1` だけでブロック判定 → 期限切れ Stripe ユーザーが再購読できない | `vips.length > 0 && vips[0].cancel_time == 0`（有効な契約が存在する）を条件に追加。失効済み・解約済みはネイティブ決済での再購読を許可 |

---

### 14. アプリ Push 通知が一切届かない（メッセージ・クーポン共通）

**影響：** jpcard（消費者）・jpshop（店舗）ともに push 通知が動作しない

| # | 修正箇所 | 問題 | 修正内容 |
|---|----------|------|----------|
| 1 | `jpcard/App.vue:123` `jpshop/App.vue:163` | `connectPush()` 内で `uni.getStorageSync("user")` が未ログイン時 `null` を返し、`info.uuid` で **TypeError クラッシュ** → push token の bind が永遠に成功しない | `\|\| {}` と `\|\| ''` でデフォルト値を保証 |
| 2 | `jpcard/App.vue:198` `jpshop/App.vue:233` | `getConnect()` も同様に `info.uuid` で未ログイン時クラッシュ → WebSocket bind も失敗 | 同上 |
| 3 | `jpcard/App.vue:98` | `onHide` でも `info.uuid` クラッシュ → バッジ数更新処理が毎回エラー | 同上 |
| 4 | `jpcard/App.vue:17-41` `jpshop/App.vue:17-41` | iOS は `click` のみ・Android は `receive` のみ監視。Android はバックグラウンド時の通知タップを拾えず、iOS はフォアグラウンド受信を拾えない | `click` と `receive` を両方登録する共通ハンドラ関数 `handlePushMessage` に統一 |

---

### 15. 投稿した「お知らせ」が Card-San App に反映されない

**影響：** jpshop で投稿したお知らせが jpcard のお知らせタブに一切表示されない

| # | 修正箇所 | 問題 | 修正内容 |
|---|----------|------|----------|
| 1 | `jpcard/pagesA/notification/notification.vue:157` | `that.type = res.data.shop.notice_type` がコード確認（`if res.code == 200`）の前に実行される。API 失敗時は `res.data.shop` が undefined → TypeError でクラッシュし `getList()` が永遠に呼ばれない | `that.type` の代入を `if (res.code == 200)` ブロック内に移動 |
| 2 | `jpcard/pagesA/notification/notification.vue:159` | `notice_type` が未設定（null/0）のショップは `type == 1` が false → お知らせリストが非表示 | `res.data.shop.notice_type \|\| 1` でデフォルト値 1 を保証 |
| 3 | `jpcard/pagesA/notification/notification.vue:197` | `getShopNotice()` の Promise に `.catch()` なし | `.catch()` を追加 |

---

### 16. iOS の CardSan 管理 App（jpshop）でログアウトできない

**影響：** iOS 端末でログアウトボタンを押しても画面が変わらず、アプリが操作不能になる

| # | 修正箇所 | 問題 | 修正内容 |
|---|----------|------|----------|
| 1 | `jpshop/pages/index/index.vue:758` | ログアウト後に `uni.redirectTo` を使用。iOS では root ページからの `redirectTo` がサイレントに失敗することがある | `uni.reLaunch({ url: "/pages/login/login" })` に変更。ページスタックを完全クリア |
| 2 | `jpshop/pages/index/index.vue:758` | ログアウトブランチで `this.showModule = false` を呼ばない → `reLaunch` 失敗時にオーバーレイが残り操作不能 | ログアウトブランチの先頭に `this.showModule = false` を追加 |
| 3 | `jpshop/pages/index/index.vue:760` | `uni.closeSocket()` が iOS で WebSocket 未接続状態のときに例外を投げ、後続の `reLaunch` が実行されない | `try { uni.closeSocket() } catch (err) {}` でラップ |
| 4 | `jpshop/utils/request.js:87` | 401 レスポンス時の自動ログアウトで相対パス `"../login/login"` を使用。インターセプターはどのページから呼ばれるかわからないため相対パスが正しく解決されない | 絶対パス `"/pages/login/login"` に変更。`clearStorageSync()` も追加 |

---

## 2026-05-12

### 18. 未処理 Promise rejection による UI フリーズ・スピナー永続（大規模修正）

**影響：** Promise チェーンに `.catch()` がない箇所で API が失敗すると、`uni.showLoading()` のスピナーが消えない・ボタンローディング状態が解除されない・UI が操作不能になる

**根本原因：** jpshop / jpcard 全体で 100 以上の `.then()` チェーンに `.catch()` ハンドラが欠如。`uni.showLoading()` の直後に Promise が始まり `.then()` 内でのみ `uni.hideLoading()` を呼ぶパターンが多数存在し、ネットワーク失敗時にスピナーが永続する。また `btnloading = true` 等のフラグも未リセット。

| 修正箇所（ファイル） | 追加 `.catch()` 数 | showLoading 漏れ修正 |
|---|---|---|
| `jpshop/pages/index/index.vue` | 12 | あり |
| `jpshop/pages/login/login.vue` | 1 | あり |
| `jpshop/pages/message/chat/chat.vue` | 9 | あり |
| `jpshop/pages/message/message.vue` | 3 | あり |
| `jpshop/pages/message/userInfo/userInfo.vue` | 2 | なし |
| `jpshop/pages/message/setting/setting.vue` | 2 | なし |
| `jpshop/pages/accounts/admin/changeInfo.vue` | 3 | あり（getAddress） |
| `jpshop/pages/accounts/contract/contract.vue` | 1 | あり |
| `jpshop/pages/settings/addStaff/addStaff.vue` | 3 | あり（staffSort） |
| `jpshop/pages/settings/menu/menu.vue` | 5 | あり（menucatesort, menuCateList） |
| `jpshop/pages/accounts/rules/rules.vue` | 1 | なし |
| `jpshop/pages/settings/membershipCard/secondStep/secondStep.vue` | 1 | あり |
| `jpshop/pages/settings/membershipCard/editSecondStep/editSecondStep.vue` | 1 | あり |
| `jpshop/pages/coupon/setting/setting.vue` | 11 | あり（getShopInfo, sceneMember×2, buyextras×2, buyextra×2） |
| `jpshop/pages/appointment/appointment.vue` | 7 | あり（getCalendar, getAppointmentList週, getAdminDetail） |
| `jpshop/pages/message/moduleSettin/moduleSettin.vue` | 2 | なし |
| `jpshop/pages/message/notice/edit/edit.vue` | 2 | なし |
| `jpshop/pages/message/reviewSetting/reviewSetting.vue` | 2 | なし |
| `jpshop/pages/message/userInfo/edit/edit.vue` | 2 | なし |
| `jpshop/pages/payment/payment.vue` | 5 | なし |
| `jpcard/pages/login/login.vue` | 1 | なし |
| `jpcard/pages/index/index.vue` | 5 | あり（scanCode） |
| `jpcard/pages/sendCode/sendCode.vue` | 2 | あり（btnloading フラグリセット） |
| `jpcard/pages/register/register.vue` | 6 | なし |
| `jpcard/pagesA/message/chat.vue` | 7 | あり（getMessageList） |
| `jpcard/pagesA/shop/shop.vue` | 5 | なし |
| `jpcard/pagesA/BindCard/BindCard.vue` | 4 | あり（getShopInfo, getAddress, getMyCard） |
| `jpcard/pagesA/AppointmentConfirmation/AppointmentConfirmation.vue` | 3 | なし（setOrder: loads フラグをリセット） |
| `jpcard/pagesA/indexInfo/indexInfo.vue` | 2 | なし |
| `jpcard/pagesA/yuYue/yuYue.vue` | 3 | あり（getAppointmentList） |
| `jpcard/pagesA/notification/notification.vue` | 2 | あり（getInsList） |
| `jpcard/pagesA/storeInformation/storeInformation.vue` | 2 | なし |
| `jpcard/pagesA/shopDetails/shopDetails.vue` | 2 | なし |

また同セッションで全 `.vue` ファイルから `console.log` デバッグ出力 60 件以上を一括削除（本番ログへの混入防止）。

---

### 19. 登録会員数30人以下のショップがトライアル終了後にログインできない

**影響：** 会員数30人以下のショップは30日間の無料トライアル終了後にさらに30日間延長される仕様だが、延長処理が実装されておらず、トライアル終了と同時に管理アプリへのログインが完全にロックされる。customaid.card-san.jp でも当該ショップが「トライアル終了」として表示される。

**根本原因：** バックエンドの `ShopController::index()` にトライアル自動延長ロジックが実装されていなかった。フロントエンド（`jpshop/pages/index/index.vue:427`）は `/api/shop/shop` レスポンスの `vip.type = 0`（アクティブな VIP なし）を受けると閉じられないロックモーダルを表示する。

**影響ショップ（修正時点）：**

| sid | ショップ名 | 会員数 | トライアル終了日 |
|-----|-----------|--------|-----------------|
| 26 | ACTIVIST | 0 | 2025-12-01 |
| 27 | TEST PP | 0 | 2026-02-09 |
| 28 | One | 0 | 2026-03-11 |

| 修正箇所 | 修正内容 |
|----------|----------|
| `api.card-san.jp: app/Http/Controllers/Api/Shop/ShopController.php` | `index()` メソッドの `$shop_vip` クエリ直後に自動延長ロジックを追加。アクティブな VIP がない場合、`is_try=1 AND status=1` のトライアルが1件のみ（未延長）かつ会員数≤30人であれば、当日から30日間の新トライアルを自動生成する。 |

**延長ロジック概要：**
- `$trial_vip_count === 1`（初回トライアルのみ、延長済みでない）
- `$shop->member()->count() <= 30`（会員数30人以下）
- 上記を満たす場合、`start_time = 本日00:00` / `end_time = 本日+30日` / `is_try=1` で新レコードを作成
- 2回目以降の呼び出しでは `trial_vip_count === 2` となるため再延長は発生しない

**確認：** PHP 構文チェック・Laravel config/route キャッシュ再生成済み

---

### 20. Instagram token 期限切れによりショップ情報 API が全件 400 を返す

**影響：** Instagram 連携を設定しているショップで、token の有効期限（60日）が切れると `ShopController::index()` が `code:400` を返す。フロントエンドは `if (res.code == 200)` 条件に入れないため、ご利用プラン・メンバー数・統計など全データが初期値（空・0）のまま表示される。ロックモーダルは出ないが実質コンテンツが全て無効に見える。token が有効でない場合は Instagram API がタイムアウトし、ローディングスピナーが永続する。

**根本原因：** `ShopController::index()` の Instagram token 検証で失敗時に `return responseToJson(400, ...)` していたため、Instagram の問題が全ショップ情報取得をブロックしていた。

| 修正箇所 | 修正内容 |
|----------|----------|
| `api.card-san.jp: app/Http/Controllers/Api/Shop/ShopController.php` | Instagram API 失敗時に 400 を返すのをやめ、token をクリアして処理を続行するよう変更 |
| DB `web_shop.instagram_access_token` (sid=7) | 期限切れ token を直接クリア（タイムアウト解消） |

**日付：** 2026-05-13

---

### 21. プラン変更後に「ご契約プラン」名が更新されない

**影響：** jpshop 管理アプリ（web 版含む）でサブスクリプションプランを変更した後、ホーム画面・契約ページに表示されるプラン名が正しく反映されない。特に Stripe 決済でアップグレードすると、旧プランが `status=2`（キャンセル済み）・新プランが `status=0`（Stripe Webhook 到着前）の状態が発生し、`$shop_vip` が `null` になって「ご利用プラン」欄が空白になる。また `$vips` に ORDER BY がないため、プラン順序が非確定となり `vips[0]` が必ずしも現在有効なプランを指さない場合があった。

**根本原因：**
1. `ShopController::index()` の `$vips` クエリに ORDER BY がないため、MySQL が返す順序が非確定
2. `$shop_vip`（`status=1` のみ対象）が、Stripe アップグレード中間状態（旧=status2、新=status0）で `null` になる

| 修正箇所 | 修正内容 |
|----------|----------|
| `api.card-san.jp: app/Http/Controllers/Api/Shop/ShopController.php` | `$vips` クエリに `->orderBy('status', 'desc')->orderBy('start_time', 'asc')` を追加。`status=1`（有効）が `status=0`（決済確定待ち）より前に来るよう保証し、同一 status 内では `start_time` が早い（現在有効な）プランが先頭になるよう整列。 |
| `api.card-san.jp: app/Http/Controllers/Api/Shop/ShopController.php` | `$shop_vip = null` のとき、`status=0, is_subscription=1`（Stripe 決済確定済み・未有効化）のプランをフォールバックとして使用するロジックを追加。これにより Stripe アップグレード後の空白表示を解消。 |

**日付：** 2026-05-14

---

### 22. Stripe 契約中のショップでプラン変更操作をすると「支払い方法は変更できません」エラーが出る

**影響：** Stripe（クレジットカード）でサブスクリプション中のショップが jpshop 管理アプリ内でプランの升级・降级を試みると、「上記内容を確認しました」ボタンを押した時点でバックエンドから「支払い方法は変更できません」エラーが返り、操作が続行不能になる。

**根本原因：**
1. `toSuccess1()` で `hasActiveStripe = true`（Stripe 有効契約あり）を検知した場合、「PCブラウザよりCard-Sanウェブサイトにログインしてプラン変更をお願いします」という**案内ポップアップ**を表示して `return` する。
2. ポップアップ内の「上記内容を確認しました」ボタンが `subscribe.vue` から `btmnclick(1)` を emit する。
3. payment.vue の `changeS(1)` が呼ばれ、`e` の値をチェックせずそのまま `buyPlan` を実行してしまう。
4. バックエンドはネイティブ決済（card_type=2/3）と既存の Stripe 契約（card_type=1）の不一致を検出し、「支払い方法は変更できません」を返す。

本来は案内ポップアップの「確認」ボタンはポップアップを閉じるだけで、`buyPlan` を呼ぶべきでない。

| 修正箇所 | 修正内容 |
|----------|----------|
| `jpshop/pages/payment/payment.vue` | `changeS()` と `toSuccess1()` に `hasActiveStripe && !isUpgrade` チェックを追加。Stripe 有効契約中でも**アップグレードはアプリ内課金を許可**（バックエンドが Stripe をキャンセル・返金して切り替える）。降级・同等プランの場合のみポップアップを表示して `buyPlan` をブロック。 |

**日付：** 2026-05-15

---

## 2026-05-29

### 23. 配信超過時にStripe契約者・副管理者が誤ったフローに入る／超過UIが古いデザインのまま

**影響：**
- Stripe（クレジットカード）でサブスクリプション中のショップが配信上限を超えた状態でメッセージ・クーポンの「お支払い」ボタンを押すと、アプリ内課金（Apple IAP / Google Pay）の支払いフローに入ってしまう。バックエンドは `card_type` 不一致でエラーを返す。
- 副管理者が「お支払い」ボタンを押しても権限チェックが行われず、IAP フローに入ってしまう。
- 超過アラートモーダルのUI（`pe2.vue` / `you2.vue`）がデザイン仕様と乖離していた（タイトルや文言が旧仕様）。
- 配信設定画面に当月の配信数カウンター（例：`300/3000`）が表示されていなかった。
- SMS 超過アラート（`index.vue`）でも Stripe 契約者のケースが未処理だった。

**根本原因：**
`goPay()`（メッセージ）・`buythis()`（クーポン）・`buysms()`（SMS）において、`vip.card_type` による支払い方法の分岐と `admin_type` による権限チェックが実装されていなかった。Web（H5 ビルド）向けの Stripe 決済 URL リダイレクト処理も存在しなかった。

| 修正箇所 | 修正内容 |
|----------|----------|
| `jpshop/components/alerts/pe2/pe2.vue` | 新デザインに刷新：赤タイトル「メッセージ配信数超過のお知らせ」・本文・青「お支払い」ボタン・灰「キャンセル」ボタン |
| `jpshop/components/alerts/you2/you2.vue` | 同上（クーポン版） |
| `jpshop/pages/message/notice/setting/setting.vue` | ① 配信数カウンター（`messageUsedCount/messageMaxCount`）を UI に追加・超過時赤表示。② `getShopInfo()` で当月配信数・プラン上限・`vip.card_type` を取得。③ `goPay()` に副管理者チェック・Stripe チェック・H5 向け Stripe URL リダイレクト・アプリ契約者の「アプリで支払い」案内パネルを追加。 |
| `jpshop/pages/coupon/setting/setting.vue` | 同上（クーポン版・type=2） |
| `jpshop/pages/index/index.vue` | `buysms()` に `// #ifdef APP-PLUS` で Stripe 契約者チェックを追加。Stripe の場合は SMS 超過モーダルを閉じて「PCブラウザで支払い」案内パネルを表示。 |
| `card-san.jp/js/5565.cdb83bfb.js`（jp-card-pc） | PC Web 管理画面のダッシュボード（`topIndex.vue`）の SMS 超過モーダルを修正：① タイトル色を `#1d1d1f`→`#D93025`（赤）に変更。② 本文を「SMS通知配信数が配信上限（100通/月）を超過しております…」に更新。③「利用料金：¥1,200」行を追加。（ソースコードが手元になく直接 Build 済み JS を編集） |

**各ユーザー種別ごとの修正後フロー（App）：**

| ユーザー種別 | 修正前 | 修正後 |
|-------------|--------|--------|
| 副管理者 | IAP フローに入る | トースト「実行する権限がありません」で終了 |
| Stripe 契約（App） | IAP フローでエラー | 底部パネル「PCブラウザで支払い」を表示 |
| Apple/Google 契約（App） | 正常（変更なし） | 正常（変更なし） |
| Stripe 契約（Web） | 未実装 | 発注 → `payExtra()` でStripe URL取得 → リダイレクト |
| Apple/Google 契約（Web） | 未実装 | 「管理アプリで支払い」案内パネルを表示 |

**日付：** 2026-05-29

---

### 24. プラン変更後の確認画面にプラン名がハードコードされている（jp-card-pc）

**影響：**  
「ご契約プランの変更」確認画面（`changeContract.vue`）に「スタンダード」「予約機能、クーポン発行」が固定値でハードコードされており、どのプランに変更しても常に「スタンダード」と表示される。

**根本原因：**  
`changeContract.vue` が完全に静的コンポーネント（`_vm._m(0)`）として実装されており、`data()`・`created()` も空。`planconfirm.vue`の`change()`も遷移時にプラン名を渡していなかった。

| 修正箇所 | 修正内容 |
|----------|----------|
| `card-san.jp/js/6923.3022912d.js`（changeContract.vue） | 静的レンダー→動的レンダーに変更。`data()`に`planName:""`追加。`created()`で`this.$route.query.planName`を読んでセット。「O K」ボタンに`/utilizePlan`への戻り処理を追加。「オプション」行（これもハードコード）を削除。 |
| `card-san.jp/js/8559.46e17bbe.js`（planconfirm.vue） | `change()`メソッドで`/changeContract`へ遷移する際に`query: { planName: this.list[this.idx].name }`を追加。 |

**日付：** 2026-05-29

---

### 25. 配信ターゲット「来店期間」の絞り込み条件が完全に逆（jpshop）

**影響：**  
クーポン・メッセージ配信設定の「来店期間」絞り込みで「7日以上経過」を選択すると実際は7日以内の顧客に配信され、「7日以内」を選択すると7日以上経過した顧客に配信される。完全に逆のロジック。

**根本原因：**  
フロントエンドの `changeTab()` で `shop_time_desc = e == 0 ? 2 : 1`（index 0=以上経過 → desc=2）と設定していたが、バックエンドは `desc == 1 → <=（以上経過）`、`desc == 2 → >=（以内）` と定義しており真逆だった。

| 修正箇所 | 修正内容 |
|----------|----------|
| `jpshop/pages/message/notice/setting/time/time.vue` | `changeTab()`: `e==0?2:1` → `e==0?1:2`。`onShow()`: desc==1→current=0、desc==2→current=1 に修正。 |
| `jpshop/pages/coupon/setting/time/time.vue` | 同上 |
| `jpshop/pages/customer/csv/time/time.vue` | 同上 |
| `jpshop/pages/message/notice/setting/setting.vue` | タイトル表示: `desc==2?'以上経過'` → `desc==1?'以上経過'` |
| `jpshop/pages/coupon/setting/setting.vue` | 同上 |
| `jpshop/pages/customer/csv/csv.vue` | 同上 |

**日付：** 2026-05-31

---

### 26. 予約日時選択画面で「指名なし」がチェックオフ後も表示される（jpcard）

**影響：**  
管理App「予約基本設定」の「指名なし」チェックを外しても、ユーザーApp（jpcard）の予約日時選択画面のスタッフ欄に「指名なし」が引き続き表示される。

**根本原因：**  
① ユーザー向けスタッフ一覧API（`Member/ShopController@admin3`）がshopの`no_appoint`フィールドをレスポンスに含めていなかった。② `yuYue.vue`の「指名なし」表示が`info.is_appoint`のみで制御されており、`no_appoint`フラグを見ていなかった。

| 修正箇所 | 修正内容 |
|----------|----------|
| `card-san-api/app/Http/Controllers/Api/Member/ShopController.php` | `admin3()`レスポンスに`no_appoint`を追加（`ShopModel::find($sid)->no_appoint`） |
| `jpcard/pagesA/yuYue/yuYue.vue` | `data()`に`no_appoint: 0`追加。`getStaffData()`で`res.data.no_appoint`をセット。「指名なし」表示に`v-if="no_appoint == 1"`を追加。 |

**日付：** 2026-05-31

---

## 2026-06-03

### 27. jpshop パッケージアプリで上下に黒いフレームが表示される

**影響：**  
新規パッケージビルドした jpshop 管理アプリ（iOS / Android）で、画面の上下端に黒いフレームが表示される。ステータスバー・ホームインジケーター領域が黒くなる。

**根本原因：**  
`jpshop/manifest.json` を手動編集した際に、`app-plus` 直下の `statusbar` および `safearea` 設定が消失していた。これらは HBuilderX のビジュアルエディタが書き込む設定で、削除されると：
- `statusbar.immersed: "supportedDevice"` がないため、Android/iOS でコンテンツがステータスバー後ろに延伸されず黒い隙間が発生
- `safearea.background: "#1D1D1F"` がないため、底部セーフエリアの背景色が未設定（黒）になる

| 修正箇所 | 修正内容 |
|----------|----------|
| `jpshop/manifest.json` | `app-plus` 直下に `statusbar`・`safearea` 設定を復元。`iosStyle` を `"default"` → `"storyboard"`（`static/splash/LaunchScreen.zip`）に戻す。 |

**復元した設定：**
```json
"statusbar": { "background": "#FBFBFD", "immersed": "supportedDevice", "style": "dark" },
"safearea": { "background": "#1D1D1F", "bottom": { "offset": "auto" } }
```

**注意：** これらの設定は HBuilderX ビジュアルエディタで管理される。`manifest.json` を直接テキスト編集する場合は消失しないよう注意。

**日付：** 2026-06-03

---

### 28. 新規予約画面でメニュー選択時に当日担当の名前・カラーが変わる（jpshop APP・WEB）

**影響：**  
新規予約モーダルでメニューを変更すると、当日担当欄の「担当者名」と「担当者カラー」が選択済みのスタッフではなく常に `admins[0]`（スタッフリスト先頭）に上書きされる。

**根本原因：**

- **APP（jpshop）**: `getStaff(appoint, source)` の `source == 1` ブランチで、`forEach` の `else` 節が毎ループ `temp.id/name/color` を上書きしていた。ループが途中で終わらないため、マッチしたスタッフが見つかっても後続の不一致ループで上書きされる。
- **WEB（jp-card-pc）**: `sceenStaff()` がメニュー選択後に呼ばれ、APIレスポンスを受け取った後に常に `admins[0]` の値をセット。`dangId`（既選択スタッフID）を参照せず先頭固定だった。

**修正：**

| 修正箇所 | 修正内容 |
|----------|----------|
| `jpshop/components/appointment/new.vue` | `forEach` の破壊的 `else` 節を削除。`Array.find()` で現在の `todaystaff.id` に一致するスタッフを先に検索し、見つかればそのデータをセット、なければ `admins[0]` にフォールバック。 |
| `/www/wwwroot/card-san.jp/js/7988.a4f8b281.js`（サーバー直接パッチ） | `sceenStaff()` 内の `t.dangiId=e.data.admins[0].id,...` を IIFE に置き換え。`t.dangId\|\|t.dangiId` で現在スタッフIDを取得し `admins.find()` で検索、なければ `admins[0]` にフォールバック。 |

**WEB側の置換内容：**
```js
// Before
t.dangiId=e.data.admins[0].id,t.staffColor1=e.data.admins[0].color,t.dangName=e.data.admins[0].name

// After
(function(){var _cid=t.dangId||t.dangiId,_cs=e.data.admins.find(function(v){return v.id==_cid})||e.data.admins[0];t.dangiId=_cs.id,t.staffColor1=_cs.color,t.dangName=_cs.name})()
```

**日付：** 2026-06-05

---

### 29. 予約日時選択画面で指名料金設定時にプロフィール内容が表示されない（Android のみ）

**影響：**  
jpcard ユーザーアプリの予約日時選択画面（yuYue.vue）で、スタッフに指名料金を設定している場合、Android のみ以下が表示されない：
- 指名料金の金額（`¥` 記号は表示されるが数値が空白）
- スタッフの自己紹介（`self_introduction`）

iOS は正常表示。

**根本原因：**

1. `.staffItem` が `display: flex`（デフォルト `align-items: stretch`）で、`.staffImage` に `height: 200upx` → `.staffDetail` が 200upx に高さ固定される
2. `.staffFu` に flex 指定がなく、Android では複数の `<text>` 子要素が縦積みになる → staffName + staffFu (多行) + staffIntro が 200upx を超えてクリップ
3. `.staffDetail` に `flex: 1; min-width: 0;` がなくレイアウトが不安定
4. `Intl.NumberFormat()` が一部 Android WebView で想定どおり動作しない可能性

| 修正箇所 | 修正内容 |
|----------|----------|
| `jpcard/pagesA/yuYue/yuYue.vue` `.staffItem` | `align-items: flex-start` 追加（staffDetail の高さ制約を解除） |
| `jpcard/pagesA/yuYue/yuYue.vue` `.staffDetail` | `flex: 1; min-width: 0;` 追加 |
| `jpcard/pagesA/yuYue/yuYue.vue` `.staffFu` | `display: flex; flex-direction: row; align-items: center; flex-wrap: wrap;` 追加（テキスト横並び保証） |
| `jpcard/pagesA/yuYue/yuYue.vue` `formatNumberWithCommas()` | `Intl.NumberFormat` を regex ベースの実装に変更（Android 互換性向上） |

**日付：** 2026-06-05

---

### 30. 予約管理画面で当日が休日になる・予約状況が反映されない（jpshop iOS のみ）

**影響：**  
CardSan 管理 App（jpshop）の予約管理画面（`appointment.vue`）で iOS のみ以下が発生：
- 当日の日付が「休日」として表示される
- 当日の予約状況がまったく反映されない（予約一覧が空）

Android は正常。

**根本原因：**

`App.vue` の `onLaunch` で `/api/config` を叩き、レスポンスの `config.time_date`（形式：`"2026-06-05 10:30:00"` スペース区切り）を `uni.setStorageSync('today', ...)` に保存。

`appointment.vue` の `getToday()` がそれを `new Date(temdate)` で解析するが：

| プラットフォーム | `new Date("2026-06-05 10:30:00")` の結果 |
|-----------------|------------------------------------------|
| Android Chrome WebView | 正常に解析 ✓ |
| iOS WKWebView (Safari) | **Invalid Date** ✗（ISO 8601 の T 区切りのみ対応） |

iOS で `Invalid Date` になると `getFullYear()` が `NaN` → `"NaN-NaN-NaN"` が日付として API に送られ、カレンダー・予約一覧両方が誤動作。

| 修正箇所 | 修正内容 |
|----------|----------|
| `jpshop/pages/appointment/appointment.vue` `getToday()` | スペース区切りの時刻部分を除去し、`-` を `/` に変換してから `new Date()` で解析。Invalid Date の場合は `new Date()` にフォールバック。 |

**日付：** 2026-06-05

---

### 31. メッセージ画面の検索バーが機能しない（jpshop）

**影響：**  
jpshop 管理 App のメッセージ一覧画面（`message.vue`）で、検索バーにキーワードを入力しても一覧が絞り込まれない。

**根本原因：**  
`<u--input>` の `v-model` が未バインドのため、入力値がどこにも繋がっていなかった。また `getMessageList` API にキーワード検索パラメータがなく、サーバー側でのフィルタリングも不可能。

| 修正箇所 | 修正内容 |
|----------|----------|
| `jpshop/pages/message/message.vue` | `data()` に `keywords: ""` と `allMessages: []` を追加。`computed` プロパティ `array()` を追加し、`keywords` が空なら全件、入力があれば `item.name` でクライアント側フィルタリングを実施。`<u--input>` に `v-model="keywords"` を追加。`getList()` の結果を `allMessages` に保存するよう変更。 |

**日付：** 2026-06-05

---

### 32. 副管理者の権限設定が反映されない（jpshop）

**影響：**  
主管理者が副管理者の権限を変更・保存しても、変更が一切反映されない。

**根本原因：**  
`eidtAdmin` API 呼び出しの HTTP メソッドが `POST` だったが、バックエンドの Laravel `apiResource` ルーティングは更新（update）に `PUT` を要求する。`POST /api/admin/{id}` はルートが存在しないため 405 エラーが発生するか無視されていた。さらに JSON ボディに `_method: "put"` を付与する方法はフォームデータのみ有効で JSON では機能しない。

| 修正箇所 | 修正内容 |
|----------|----------|
| `jpshop/api/index.js` `eidtAdmin` | `method: "post"` → `method: "put"` に変更 |
| `jpshop/pages/settings/Administrators/addNew/jurisdiction.vue` `save()` | `data._method = "put"` の行を削除 |

**日付：** 2026-06-05

---

### 33. PUSH通知が表示されない・バッジがアプリを開かないと更新されない（jpcard）

**影響：**  
jpcard ユーザーアプリでPUSH通知がバックグラウンド・非起動時に一切表示されない。また通知バッジ（数字）がアプリを開くまで更新されない。

**根本原因：**
1. `jpcard/manifest.json` の UniPush 設定で `"offline": false` となっており、APNs（iOS）/ FCM（Android）ベンダーチャンネルが無効化されていた。アプリが起動していない・バックグラウンド時は GeTui の WebSocket 接続が切断されるため通知が届かない。（jpshop は `"offline": true` + `"fcm": {}` で正常動作していた）
2. `jpcard/App.vue` の `onShow` でバッジ更新処理がコメントアウトされており、アプリを開いた時に実際の未読数でバッジが更新されない。

| 修正箇所 | 修正内容 |
|----------|----------|
| `jpcard/manifest.json` `push.unipush` | `"offline": false` → `"offline": true` に変更し `"fcm": {}` を追加。APNs/FCM ベンダーチャンネルを有効化 |
| `jpcard/App.vue` `onShow` | `/api/member/member?request_type=unread_num` を呼び出し `plus.runtime.setBadgeNumber()` でバッジを実際の未読数に更新する処理を有効化 |

**備考：** `manifest.json` の変更は HBuilderX で再ビルドし App Store / Google Play へ再提出が必要。

**日付：** 2026-06-06

---

## 2026-06-07

### 34. 機能追加：スタッフルームチャット（機能4）

**概要：** 店舗スタッフ・管理者間でグループチャット・1対1 DM ができるスタッフルーム機能を実装。

| 対象 | 実装内容 |
|------|----------|
| **サーバー** `StaffRoomController.php` | グループチャット・DM 送受信、WebSocket ブロードキャスト、GeTui プッシュ通知、未読カウント API を実装（PHP 7.3 互換） |
| **DB** `web_shop_admin` | `staff_room_join`, `staff_room_dm`, `staff_room_last_read` カラム追加 |
| **DB** 動的テーブル | `web_staff_room_msg_{sid}`（グループ）, `web_staff_dm_{sid}_{min}_{max}`（1対1）を初回送信時に自動生成 |
| `jpshop/api/index.js` | スタッフルーム関連 API 10 本追加 |
| `jpshop/pages.json` | staffRoom/staffRoom, staffRoom/groupChat, staffRoom/memberList, staffRoom/dm の 4 ページ登録 |
| `jpshop/pages/message/staffRoom/staffRoom.vue` | ルーム設定ページ（名前・アバター・ON/OFF） |
| `jpshop/pages/message/staffRoom/groupChat.vue` | グループチャット UI（リアルタイム WebSocket 対応） |
| `jpshop/pages/message/staffRoom/memberList.vue` | メンバー一覧・1対1 DM 開始 |
| `jpshop/pages/message/staffRoom/dm.vue` | 1対1 DM チャット UI |
| `jpshop/pages/message/setting/setting.vue` | スタッフルーム設定メニュー追加（主管理者のみ表示） |
| `jpshop/pages/message/userInfo/userInfo.vue` | スタッフルーム参加/DM 許可スイッチ追加 |
| `jpshop/pages/message/message.vue` | スタッフルームへのエントリー・未読バッジ表示追加 |
| `jpshop/App.vue` | WebSocket・プッシュ通知で staff_room / staff_dm タイプ対応 |

**日付：** 2026-06-07

---

### 35. 機能追加：GBP OAuth連携・クチコミ管理（機能1・機能2）

**概要：** Google Business Profile との OAuth 連携、クチコミ確認・返信・削除、統計表示機能を実装。

| 対象 | 実装内容 |
|------|----------|
| **サーバー** `app/Libs/GoogleBusiness.php` | GBP API ラッパー（OAuth 認証、トークン自動更新、クチコミ・投稿 CRUD） |
| **サーバー** `GoogleBusinessController.php` | 18 エンドポイント実装（OAuth フロー、ステータス、クチコミ管理、投稿管理、設定） |
| **サーバー** `auth_redirect.blade.php` | OAuth コールバック後にアプリへリダイレクトする Blade ビュー |
| **DB** `web_shop` | `gbp_access_token`, `gbp_refresh_token`, `gbp_token_expires`, `gbp_account_name`, `gbp_location_name`, `gbp_connected_at`, `gbp_sync_on`, `gbp_ins_sync_on` カラム追加 |
| `routes/api.php` | GBP 関連 17 ルート追加、スタッフルーム 10 ルート追加 |
| `jpshop/api/index.js` | GBP 関連 API 16 本追加 |
| `jpshop/pages.json` | gbpSetting, gbpReviews, socialMedia の 3 ページ登録 |
| `jpshop/pages/settings/gbpSetting/gbpSetting.vue` | GBP 連携設定ページ（OAuth・同期設定・連携解除） |
| `jpshop/pages/message/gbpReviews/gbpReviews.vue` | クチコミ管理（統計ヘッダー・フィルタータブ・返信/編集/削除） |
| `jpshop/pages/message/reviewSetting/reviewSetting.vue` | 「Googleクチコミを管理」リンクを追加 |
| `jpshop/pages/message/setting/setting.vue` | Google Business設定・SNS投稿管理メニュー追加 |
| `jpshop/static/svg/google_g.svg` | Google G ロゴ SVG 追加 |

**備考：** `GoogleBusiness.php` の `$client_id` / `$client_secret` は Google Cloud Console でプロジェクト作成後に記入が必要。

**日付：** 2026-06-07

---

### 36. 機能追加：Instagram × GBP 投稿連動・SNS統合管理（機能3）

**概要：** Instagram 投稿を GBP に転載する機能、GBP 投稿管理（新規作成・削除）、Instagram→GBP 自動連動設定を実装。

| 対象 | 実装内容 |
|------|----------|
| **サーバー** `GoogleBusinessController@syncInstagram` | Instagram 投稿（メディア URL・キャプション）を GBP STANDARD 投稿として転載 |
| **サーバー** `GoogleBusinessController@createPost` | お知らせ/イベント/クーポン の 3 種別対応 GBP 投稿作成 |
| **サーバー** `GoogleBusinessController@integrationSettings` | Instagram→GBP 自動連動 ON/OFF 設定の GET/PUT |
| `jpshop/pages/settings/socialMedia/socialMedia.vue` | SNS統合管理ページ（連携設定タブ・GBP投稿管理タブ・連動履歴タブ） |

**日付：** 2026-06-07

---

### 37. バグ修正：管理画面ダッシュボードが空白・NaN%表示になる

**概要：** 管理画面（customaid.card-san.jp）のダッシュボードで、全データが空白になり「解約率/売上比」が「NaN%」と表示されるバグを修正。

**根本原因：**
1. `request.js` のリクエストインターセプターで、localStorage にトークンが存在しない場合に `"api": "1"` ヘッダーが送信されなかった。
2. PHP の `AdminAuth` ミドルウェアは `api: 1` ヘッダーが無い場合、JSON 401 を返す代わりに Blade ログインページへ 302 リダイレクトする仕様。
3. Axios が 302 を追跡して HTML を受け取り、`res.code` が undefined → `info = {}` のまま。
4. `formatNumber(undefined)` = `undefined * 1` = `NaN` → `NaN.toFixed(2)` = `"NaN"` として表示された。

| 対象 | 修正内容 |
|------|----------|
| `admin/admin/src/http/request.js` | トークン未保持時も `"api": "1"` ヘッダーを常に送信するよう変更 |
| `admin/admin/src/components/Home.vue` | `formatNumber()` を `parseFloat()` + `isNaN()` ガードに修正（undefined/null → `"0.00"`） |
| `customaid.card-san.jp` | ビルド・デプロイ済み（旧 JS ファイルもクリーンアップ） |

**日付：** 2026-06-18

---

### 38. バグ修正：Google Play 課金が正常に処理されない（更新後すぐ期限切れになる）

**概要：** Google Play のサブスクリプション更新通知（webhook）を受信すると、新しい VIP レコードの `end_time` が本来の「1ヶ月後」ではなく「5〜30分後」に設定されるバグを修正。

**根本原因：** `GoogleService::notifyUpdateSubscriptions()` にテスト用コードが本番環境に残されていた。

```php
// 誤（テストコード）:
'end_time' => time() + ([1 => 5, 6 => 15, 12 => 30][$shop_vip->month]) * 60,
// 正:
'end_time' => strtotime(date('Y-m-d', $shop_vip->end_time) . ' +' . $shop_vip->month . ' month'),
```

| 対象 | 修正内容 |
|------|----------|
| `app/Services/GoogleService.php` | テストコードを削除し、正しい月数計算に修正 |

**日付：** 2026-06-18

---

### 39. バグ修正：無料トライアルが60日で終了する（継続延長されない）

**概要：** 会員数30人以下のショップは無料トライアルを継続延長できる仕様だが、最初の30日延長後にカウントが2になり条件 `=== 1` を満たさなくなって停止していた。

**根本原因：**
1. `ShopController` の条件 `$trial_vip_count === 1` が、2回目以降の延長を阻止していた。
2. 過去に有料契約が存在すると `paid_vip_count > 0` となり延長が止まる可能性があった（有効期限切れの有料契約も対象になっていた）。

| 対象 | 修正内容 |
|------|----------|
| `app/Http/Controllers/Api/Shop/ShopController.php` | `=== 1` → `>= 1` に変更、`$paid_vip_count` クエリに `end_time > time()` 条件を追加（現在有効な有料契約のみカウント） |

**日付：** 2026-06-18

---

### 40. バグ修正：Instagram API `profile_picture_url` エラーが毎分発生

**概要：** Instagram API が `profile_picture_url` を返さなくなったため、`ShopController` で `Undefined index` エラーが毎分発生していた。

| 対象 | 修正内容 |
|------|----------|
| `app/Http/Controllers/Api/Shop/ShopController.php` | `$res['data']['profile_picture_url']` → `$res['data']['profile_picture_url'] ?? ''` に修正 |

**日付：** 2026-06-18

---

### 41. バグ修正：プラン変更時に `CreateSubscriptionsEndJob` が新規注文を誤ってキャンセルする

**概要：** Stripe でプランを変更した際、`CreateSubscriptionsEndJob` が「未生効の旧サブスクリプション」を探す処理で新しい注文（`lvid == oid`）を誤って `status=2` に変更・キャンセルしていた。

**根本原因：** `last_vip` のクエリで最も新しい注文（≒新規注文）が選ばれ、`lvid != nvid` の条件のみで取消し処理が走っていた。

| 対象 | 修正内容 |
|------|----------|
| `app/Jobs/CreateSubscriptionsEndJob.php` | `if($this->lvid && $this->lvid != $this->nvid)` → `if($this->lvid && $this->lvid != $this->nvid && $this->lvid != $this->oid)` に変更 |

**日付：** 2026-06-18

---

### 42. バグ修正：CardSan管理App 「利用プラン」ページがプラン変更後に更新されない・クラッシュする

**概要：** `package.vue` で、プラン変更後に前の画面に戻ってきた際にデータが再取得されず古いプランが表示されていた。また `vip.type == 0`（無契約）の場合に `current = -1` が `NaN` になるケースと、`vip` が null の場合のクラッシュを修正。

| 対象 | 修正内容 |
|------|----------|
| `jpshop/pages/accounts/package/package.vue` | `onShow()` フックを追加してページ表示時に `getShopInfo()` を再実行 |
| `jpshop/pages/accounts/package/package.vue` | `vip || {}` でデフォルト値ガード、`vipType > 0 ? vipType - 1 : -1` で `current` の安全な計算 |

**日付：** 2026-06-18

---

### 43. バグ修正：CardSan管理App 「ご契約プラン」ページのお支払い方法表示が常に「App 課金」

**概要：** `contract.vue` の「お支払い方法」欄が試用中以外は常に `'App 課金'` と表示されていた（Stripe/Google Play も同じ表示）。また `isexpire == false` 時に `vip` が undefined でクラッシュする問題も修正。

| 対象 | 修正内容 |
|------|----------|
| `jpshop/pages/accounts/contract/contract.vue` | `card_type` に応じて「クレジットカード」「Apple App Store」「Google Play」を表示 |
| `jpshop/pages/accounts/contract/contract.vue` | `vip.type` / `vip.month` → `(vip\|\|{}).type` でクラッシュ防止 |

**日付：** 2026-06-18

---

### 44. バグ修正：CardSan管理Web/App 解約ボタンが失敗する（ペイメントリンクIDをサブスクリプションとして取消しようとする）

**概要：** ユーザーが Stripe でプランを変更しようとしてペイメントリンク（`plink_xxx`）を発行した後、実際に支払いをせずに解約ボタンを押すと、`VipController::destroy()` が `cancelSubscription('plink_xxx')` を呼び出して Stripe API エラーになりボタンが動かなくなるバグを修正。

**根本原因：** Stripe の `paymentLinks` API が返すのはペイメントリンクオブジェクト（`id = plink_xxx`）であり、サブスクリプションID（`sub_xxx`）ではない。ペイメントリンクIDでサブスクリプション取消しAPIを呼ぶと必ずエラーになる。

| 対象 | 修正内容 |
|------|----------|
| `app/Http/Controllers/Api/Shop/VipController.php` | `destroy()` の `now_vip` および `last_vip` の Stripe 取消し処理で、`subscription_id` が `plink_` で始まる場合はキャンセルAPIをスキップするよう修正 |

**日付：** 2026-06-18

---

### 45. バグ修正：Google Play プラン変更が完全にブロックされていた

**概要：** `ShopVipValidator` に残っていた過剰な制限により、Google Play ユーザーがプランをアップグレード・ダウングレードしようとすると常にエラー「ご利用のプランが変更できません」が返されていた。

**根本原因：** 「Google Play は支払い方法変更不可」というガードが、同じ Google Play のままプランを変更するケースまで誤ってブロックしていた。

| 対象 | 修正内容 |
|------|----------|
| `app/Validators/ShopVipValidator.php` | Google Play (`card_type=3`) を Google Play のまま変更するケースを許可。別決済方法への変更のみ拒否するよう条件を分離 |

**日付：** 2026-06-18

---

### 46. バグ修正：プラン変更ジョブが plink_ IDで失敗し旧サブスクリプションが未解約になる

**概要：** `CreateSubscriptionsEndJob` がプラン変更時に旧プランの Stripe サブスクリプションを取消す処理で、`subscription_id` が `plink_xxx`（ペイメントリンクID）の場合に Stripe API エラーが発生し、ジョブが `false` を返して終了していた。その結果、旧サブスクリプションが未解約のまま残り二重請求の原因になっていた。

| 対象 | 修正内容 |
|------|----------|
| `app/Jobs/CreateSubscriptionsEndJob.php` | `now_vip->subscription_id` が `plink_` で始まる場合は `cancelSubscription` をスキップし、`cancel_time` のみ記録するよう修正 |

**日付：** 2026-06-18

---

### 47. バグ修正：Stripe Webhook 競合による旧サブスクリプション未解約（二重請求）

**概要：** Stripe の `customer.subscription.created` Webhook が `pay()` 後のプラン変更より先に届かない場合、元の注文レコードに実際の Stripe サブスクリプション ID（`sub_xxx`）が保存されないため、ジョブが旧サブスクリプションを取消せない問題があった。

**根本原因：** `notifyUpdateSubscriptions()` は Webhook で新しい VIP レコードを作成するが、元の注文レコードの `subscription_id`（`plink_xxx`）を更新していなかった。Webhook 到達前にプラン変更が走ると、ジョブは plink_ ID しか知らず取消しに失敗する。

| 対象 | 修正内容 |
|------|----------|
| `app/Services/StripeService.php` | `notifyUpdateSubscriptions()` 内で、元注文の `subscription_id` が `plink_` の場合に限り実際の Stripe Sub ID（`$object->id`）で上書き更新するよう修正 |

---

## 2026-06-30

### 48. バグ修正：SMS 予約確認通知が届かない（webサイト予約時）

**概要：** webサイト（order.card-san.jp）から予約した際、SMS 確認通知が届かない問題。

**根本原因：** `AlibabaCloudSms::send()` で電話番号を `'81' . $phoneNumbers` と結合していたが、日本の電話番号（例：`09012345678`）の先頭の `0` を除去せずに国番号を付与していたため、`8109012345678` という無効な番号になっていた。正しくは `819012345678`。

| 対象 | 修正内容 |
|------|----------|
| `app/Libs/AlibabaCloudSms.php`（サーバー） | `'To' => '81' . ltrim(preg_replace('/[^0-9]/', '', $phoneNumbers), '0')` に修正。非数字文字（ハイフン等）除去＋先頭ゼロ除去 |

**日付：** 2026-06-30

---

### 49. バグ修正：店舗情報「友達に共有」ボタンのリンク先が間違い（google.com を参照）

**概要：** Card-San App の店舗情報画面「アプリダウンロードリンクをコピー」ボタンが `http://www.google.com` をコピーしていた。

| 対象 | 修正内容 |
|------|----------|
| `jpcard/pagesA/storeInformation/storeInformation.vue` | プラットフォーム検出で Android/iOS 別ストアリンクを使用。バックエンドから `member_app_download_url` を受け取り、設定済みの場合はそちらを優先 |
| `app/Http/Controllers/Api/Member/ShopController.php`（サーバー） | `GET /api/member/shop/:id` レスポンスに `member_app_download_url`（`webConfig()->download_url`）を追加 |

**日付：** 2026-06-30

---

### 50. バグ修正：チャットメッセージの改行が入力できない

**概要：** チャット画面の入力欄が `<input>` 単行テキストフィールドのため改行を入力できなかった。表示側はすでに `white-space: pre-wrap` で対応済みだったが、入力側が未対応。

| 対象 | 修正内容 |
|------|----------|
| `jpcard/pagesA/message/chat.vue` | `<input>` を `<textarea>` に変更（`auto-height`、最大高さ 160upx） |
| `jpshop/pages/message/chat/chat.vue` | 同上 |

**日付：** 2026-06-30

---

### 51. 新機能：チャット画像の端末保存機能を追加

**概要：** チャット画面で受信した画像を長押しすると端末の写真ライブラリに保存できるようにした。

| 対象 | 修正内容 |
|------|----------|
| `jpcard/pagesA/message/chat.vue` | 画像に `@longpress="saveImage()"` を追加、`uni.saveImageToPhotosAlbum` で保存 |
| `jpshop/pages/message/chat/chat.vue` | 同上 |

**日付：** 2026-06-30

---

### 52. バグ修正：メニュー画像の Pinch In/Out（ズーム）に対応

**概要：** メニュー画像ポップアップのスワイパー内で画像をタップすると `uni.previewImage` が開き、ピンチ操作でズームできるようになった。

| 対象 | 修正内容 |
|------|----------|
| `jpcard/pagesA/menu/menu.vue` | スワイパー内の `<image>` に `@click="openPreview(idx)"` を追加、`openPreview()` メソッドで `uni.previewImage` を呼び出す |

**日付：** 2026-06-30

---

### 53. バグ修正：Instagram 連携時の動画コンテンツでヘッダー・フッターが隠れる問題

**概要：** お知らせページで Instagram 動画を表示する際、uni-app の `<video>` タグが iOS でネイティブレイヤーとしてレンダリングされ、`u-navbar` ヘッダーや `<list>` フッターの上に重なって隠れていた。

| 対象 | 修正内容 |
|------|----------|
| `jpcard/pagesA/notification/notification.vue` | `<video>` タグを削除し、サムネイル画像＋再生ボタンアイコンのオーバーレイに変更。タップで Instagram 詳細ページへ遷移する既存の動作を維持 |

**日付：** 2026-06-30

---

### 54. バグ修正：お知らせ入力の文字数制限（140文字上限）を撤廃

**概要：** uView UI の `u-textarea` コンポーネントのデフォルト `maxlength` が 140 に設定されており、お知らせ本文やその他テキストエリアに文字数制限が掛かっていた。

| 対象 | 修正内容 |
|------|----------|
| `jpshop/uni_modules/uview-ui/libs/config/props/textarea.js` | `maxlength: 140` → `maxlength: -1`（無制限）に変更 |
| `jpcard/uni_modules/uview-ui/libs/config/props/textarea.js` | 同上 |

**日付：** 2026-06-30

---

### 62. スタッフルームチャット + 予約バグ一括対応（2026-07-15〜16）

**スタッフルーム（P.1〜P.8、jpshop・要ビルド／サーバー反映済）**
| 項目 | 対応 |
|------|------|
| P.1 | メッセージ設定: スタッフルーム設定アイコン差替(12249)、Google Business設定/SNS投稿管理の2項目削除 |
| P.2 | userInfo: 保存ボタンをFigma塗り青(:plain除去+#1A73E8)、スタッフルーム項目の余白拡大 |
| P.3 | message一覧: ルーム名に参加人数、最新メッセージ表示(サーバーindexにroom.last_message追加)。P.3(3)1対1相手一覧はDMスレッド一覧API要で保留 |
| P.4-1 | groupChat: ヘッダーにルーム名+参加人数(getStaffRoom) |
| P.4-2/P.6-2 | group/dmチャットをinput→textareaで改行対応(8行追従+スクロール) |
| P.4-3 | メンバー一覧の1対1開始アイコンをGroup 63に |
| P.4-4 | サーバーindexで admin_type!=3(スタッフ登録除外)→副管理者のみ表示 |
| P.5 | 1対1確認ダイアログ: 文言変更+中央揃え |
| P.6-1 | 1対1相手アイコン表示(フォールバック+memberListからavatar受渡し) |
| P.7-5 | 送信内容の即時反映(楽観的追加+id重複除去) group/dm両方 |
| P.8 | スタッフルーム設定画面: アイコン(Group59)、ラベル「スタッフルーム」、説明文+区切り線、余白50px、保存青 |
| P.7-1/2 | バッジ/pushはクライアント側は⑤で対応済、残はサーバーpayload+DCloud設定 |

**予約バグ（サーバー反映済 + jppcデプロイ済 + jpshop/outsite要ビルド）**
| 項目 | 対応 |
|------|------|
| 予約[26] | サーバーOrderValidator: 通常メニューもclient指定end_timeを尊重(保存が反映されない根本修正) |
| 予約[27] | jpshop getCate: 現在メニューを初期チェック(チェック外れ修正) |
| 予約[29] | jppc huiYuan: 未使用getFuDetailラップ除去でメニュー選択画面が通常メニューでも開く |
| 予約B[1] | サーバーgetStaffListがno_appoint返却+outsiteAppointmentにv-if追加(jpcardは既読対応)→指名なし表示の設定反映 |
| 予約B[3/4] | サーバーtoday_count/order_todayにsmid!=0追加(お休み除外) |
| 予約B[5] | outsiteAppointment確定ボタンをisSubmittingでLoading中連打ガード |

**日付：** 2026-07-16

---

### 61. QA一括対応（クライアント報告19件・2026-07-14）

顧客からの不具合/要望19件を報告順に対応。サーバー(api.card-san.jp)/jppc(Web)は即デプロイ済、jpcard/jpshopはソース修正済でHBuilderXビルド＆配布待ち。

| # | 対象 | 修正内容 | 反映 |
|---|------|----------|------|
| ① | サーバー ShopController | vip(機能ゲーティング)が有効契約vipsの最上位typeを下回らない補正追加(Premium契約→Light表示の不整合防止)。php-fpm graceful reload済 | **本番済** |
| ② | jpcard BindCard.vue | 会員登録: ass配列5→6要素、存在しないuser.address判定→zip_code&&province&&address1、キー統一。住所必須で登録ボタン永久無効を解消 | 要ビルド |
| ③ | jpcard shop.vue | フッター背景page_footer_color undefined→透明を`||`で解消、page_footer_icon.forEach防御、アイコン色フォールバック、changeColorガード | 要ビルド |
| ④ | jpcard notification.vue | 画像付きお知らせLoading固まりは#56(getMore type!=2 return)で修正済。1067未反映が原因 | 要ビルド |
| ⑤ | jpcard/jpshop App.vue | push receive/click分離、フォアグラウンドでcreateMessage手動通知+badge反映。残:サーバーpayload+DCloud設定+実機テスト | 要ビルド/連携 |
| ⑥ | jppc mass/download/foundCoupon | 来店期間の以上経過↔以内が逆(jpshop正解に統一) | **本番済** |
| ⑦ | jppc appointment.vue | 新規予約ボタン「保存」→「この内容で予約する」 | **本番済** |
| ⑧⑨ | jpcard/jpshop chat.vue | メッセージ入力欄: .sendBox固定height→min-height(改行時の崩れ解消)+上部padding、textarea max-height 160→400upx | 要ビルド |
| ⑩ | jpcard img-viewer.vue(新規)+chat.vue | 受信画像フルスクリーン(pinch)+DLアイコン+saveImageToPhotosAlbum+「保存しました」 | 要ビルド |
| ⑪ | サーバー AlibabaCloudSms.php | 自動返信SMSの改行消失=Alibaba国際SMS(SendMessageToGlobe)がLF除去。送信層で改行をCRLFに正規化(全改行→\r\n、3連続以上→2圧縮)。php-fpm reload済。要実機テスト確認、CRLFも除去なら国内SMSプロバイダ切替が根本解決 | **本番済/要確認** |
| ⑫ | jpcard ModifyInformation/accountInformation | ログアウト: redirectTo→reLaunch(遷移確実化+キャッシュ氏名クリア)、user削除 | 要ビルド |
| ⑬ | jpshop notice/setting/setting.vue | お知らせ設定onLoadのsetTimeout 2000ms人為的遅延を撤去(約3秒→~1秒) | 要ビルド |
| ⑭ | jppc noticeSet.vue | Instagramラジオ選択不可(#60-1で修正済) | **本番済(7/11)** |
| ⑮ | jpcard menu.vue | メニュー画像pinchはopenPreviewが既にuni.previewImage使用(ネイティブpinch)。1067未反映が原因 | 要ビルド |
| ⑯ | jpcard notification.vue | Instagram: 動画はサムネイル方式でz-index回避済、caption 85文字カット+「続きを読む」実装 | 要ビルド |
| ⑰ | jpshop edit.vue / jppc appointment.vue | 終了時間編集: showEndのmenu.id==0制限撤去(通常メニューも編集可)+end>start検証。jppcはeditKeepに検証追加 | jpshop要ビルド/jppc本番済 |
| ⑱ | jpshop | UIアイコン差替+プラン説明追加はFigma素材が必要(未着手) | 要素材 |
| ⑲ | jpshop | チャットルーム機能はFigma仕様が必要・大型(未着手) | 要仕様 |

版号更新: jpcard 1.1.2(1067)→1.1.3(1068)、jpshop 1.0.32(1082)→1.0.33(1083)。

**日付：** 2026-07-14

---

### 60. jppc 追加バグ修正（4回目のソースコードレビュー・全ファイル横断監査）

全 view ファイル（約60本）を5グループに分けて監査し、以下の真正バグを修正。誤検出（`registration.vue` の `endTIme` は `api.js` の `getStaffList` 定義と一致するため正常／`editFirst.vue` の未 import `changeDefault` は到達不能なデッドコード／`coupon.vue` の `item.indate`・`appointmentSetting.vue` の sex 型・`deputyAdministrator.vue` の -1 変換は API 仕様依存でリスクあり）は対象外とした。

#### 60-1. 予約管理：未収藏アイコンのクリックが効かない
**概要：** `appointment.vue` のお気に入りアイコンで `@clic.stop`（`k` 抜けタイプミス）。`is_collect == 0` の通知をお気に入りに追加できない。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/appointment.vue`（94行） | `@clic.stop` → `@click.stop` |

#### 60-2. 予約管理：予約削除ボタンが無反応（メソッド空実装）
**概要：** 削除確認ダイアログの「削除」ボタンが呼ぶ `deletenoticeAppointment()` の中身が空。予約削除機能が完全に動かない。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/appointment.vue` | 既存の `deleteOrder()` に倣い `deleteAppointment(that.orderInfo.id)` を呼び、成功時 `delShow` を閉じ `getNoticeList()` で再取得する実装を追加 |

#### 60-3. 予約管理：スタッフ列が同一 `times` 配列参照を共有
**概要：** `getList()`（1597行）と `getWeekList()`（1534行）で全スタッフ列の `times` に `that.times` の同一参照を代入。子コンポーネントが1列を変更すると全列が汚染される。`getList` には深コピー用 `timeArr`（1592行）が用意済みだが未使用だった。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/appointment.vue` | 両箇所を `JSON.parse(JSON.stringify(that.times))` で列ごとに深コピー |

#### 60-4. 予約管理：`is_collect` の型が真偽値に化ける
**概要：** `collectNotice()` で `is_collect = !is_collect` とし、バックエンドの 0/1 数値が `true/false` に化ける（テンプレートの緩い比較で辛うじて動作するが型不整合）。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/appointment.vue`（1385行） | `= !...` → `= ... == 1 ? 0 : 1` で数値のまま切替 |

#### 60-5. 予約設定・メニュー一覧：ネスト回調で誤ったレスポンスを判定
**概要：** `appointmentSetting.vue`・`menuList.vue` とも `getMenuList().then((result) => { if(res.code == 200) ... })` と外側の `res` を判定。`getMenuList` が失敗すると `result.data.menus` が undefined となり `.forEach` で TypeError。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/appointmentSetting.vue`（331行） | `if(res.code == 200)` → `if(result.code == 200)` |
| `jppc/.../views/variousSet/menuList.vue`（165行） | 同上 |

#### 60-6. 解約完了ページに遷移しない（変数名タイプミス）
**概要：** `terminationFulfil.vue` の `offf()` で解約成功時 `this.setp = 2`（正しくは `step`）。存在しないフィールドに代入するため、テンプレートの `step == 1 / v-else` が切り替わらず「解約手続き完了」画面が永遠に表示されない。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/plan/terminationFulfil.vue`（80行） | `this.setp = 2` → `this.step = 2` |

#### 60-7. プラン購入判定で比較のつもりが代入
**概要：** `content.vue` の `buyPlan()` 回調で `if (res.code = 200)`（代入）。常に真となり、失敗レスポンスも成功扱いされる。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/plan/content.vue`（272行） | `if (res.code = 200)` → `if (res.code == 200)` |

#### 60-8. メッセージ作成の入力チェックが演算子優先順位で誤動作
**概要：** `deliveryHistory.vue` の `nextStep()` で `if((that.title != '' && that.txt) != '' || ...)` と括弧位置が誤り、「タイトルとテキスト両方入力」の判定が意図通りにならない。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/deliveryHistory.vue`（658行） | `(that.title != '' && that.txt) != ''` → `(that.title != '' && that.txt != '')` |

#### 60-9. CSV ファイル名が10月・10日でゼロ埋め誤り
**概要：** `download.vue` で `(month) > 10 ? month : '0'+month` と書かれており、値がちょうど 10 のとき `'010'` になる（10月・10日でファイル名が壊れる）。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/download.vue`（430-431行） | 月・日とも `> 10` → `>= 10` |

#### 60-10. 誕生月・性別の選択入力が `v-model` にメソッド呼び出しをバインド
**概要：** `download.vue`・`foundCoupon.vue` の絞り込み入力で `v-model="array.join()"` のように代入不可能な式に双方向バインド。書き戻しセッターが不正になる。これらは実際にはクリックでポップアップを開く表示専用フィールド。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/download.vue`（31・51行） | `v-model="months.join()"`/`"sexs.join()"` → `readonly :value="..."` |
| `jppc/.../views/foundCoupon.vue`（344・383行） | `v-model="array.join()"`/`"array1.join()"` → `readonly :value="..."` |

#### 60-11. 商品詳細→注文遷移で URL 未エンコード
**概要：** `projectDetail.vue` の `order()` で `goodsInfo` を `JSON.stringify` して未エンコードのまま query に連結。説明文に `&`・`#`・改行等が含まれると `/order` 側の `JSON.parse(query.info)` が SyntaxError。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/projectDetail.vue`（84行） | `JSON.stringify(that.goodsInfo)` → `encodeURIComponent(JSON.stringify(that.goodsInfo))` |

#### 60-12. 営業カレンダー：`days`/`pickerArr` 未初期化でクラッシュ
**概要：** `calendar.vue` で `days`・`pickerArr` が `data()` に未宣言。`selectDay()` の `that.days[index].business_time`、`else` 分岐の `that.pickerArr`（undefined）、および `addTime()` の `this.openArr.length` が状況次第で TypeError を起こす。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/variousSet/calendar.vue` | `data()` に `days:[]`・`pickerArr:[]` を追加 |

#### 60-13. 無効な CSS カラー値でアイコン色が反映されない
**概要：** 4ファイルの「登録」系アイコンで `color: #1a73e8k`（末尾に不要な `k`、7桁）。ブラウザに無視され青色にならない。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/variousSet/{typeRegister,menuList,deputyLook,registration}.vue` | `#1a73e8k` → `#1a73e8` |

**日付：** 2026-07-11

---

### 57. jppc 複数バグ修正（ソースコード取得後の対応）

#### 57-1. リクエストエラーが握りつぶされる
**概要：** `request.js` の axios インターセプターで、エラー時に `return` が欠落、かつ `Promise.resolve(error.response)` でエラーを正常扱いしていた。ネットワークエラー時に `undefined` が返り、呼び出し側が `res.code` を参照するとクラッシュ。

| 対象 | 修正内容 |
|------|----------|
| `jppc/jp-card-pc/src/http/request.js` | リクエストインターセプターに `return` を追加。レスポンスエラーハンドラを `Promise.resolve(error.response)` → `Promise.reject(error)` に変更 |

#### 57-2. 来店履歴 API URL のタイポ
**概要：** `getComeList` の URL で `&date"+data.date` となっており `=` が欠落。`?type=come&date20260707&mid=123` のような壊れたクエリが送信されていた。

| 対象 | 修正内容 |
|------|----------|
| `jppc/jp-card-pc/src/http/api.js` | `&date"+data.date` → `&date="+data.date` |

#### 57-3. ご利用プランページで機能リストが共有参照になるバグ
**概要：** `getList()` で `arr` を各プランの `items` に代入する際、同一配列参照を全プランに割り当てていた。1つのプランで機能詳細を開くと、全プランで同じ機能が開いてしまう。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/plan/utilizePlan.vue` | `val.items = arr` → `val.items = arr.map(v => ({...v}))` |
| `jppc/.../views/plan/planconfirm.vue` | 同上 |

#### 57-4. プラン確認ページの契約更新日計算が常に現在時刻基準になる
**概要：** `cycleClick()` 内で `that.endTIme` と誤記（正しくは `that.endDate`）。`endTIme` は常に `undefined` のため、既存契約の終了日ではなく現在時刻から更新日を計算していた。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/plan/planconfirm.vue` | `endTIme` → `endDate`（2 箇所） |

#### 57-5. お知らせ編集で保存ボタンが表示されず、表示されても保存しない
**概要：** `contribution.vue` で `info` フラグが編集時に `true` に設定されないため、「保存」ボタン（`v-if="info"`）が常に非表示。また「保存」ボタンのクリックハンドラが `releaseShow = true`（ポップアップ表示のみ）で、実際の保存 API を呼び出していなかった。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/contribution.vue` | 編集モード（`id != "000"`）時に `that.info = true` を追加 |
| `jppc/.../views/contribution.vue` | 保存ボタンの `@click.stop="releaseShow = true"` → `@click.stop="releaseClick()"` に変更 |

**日付：** 2026-07-07

---

### 58. jppc 追加バグ修正（2回目のソースコードレビュー）

#### 58-1. 住所検索後に `uni.nav` が残っておりエラーになる
**概要：** `address.vue` の `searchAddress()` 内に uni-app 固有の `uni.nav` が残存。web 環境では `uni` は未定義のため `ReferenceError` が発生し、住所自動入力後の処理がクラッシュする。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/address.vue` | `uni.nav` 1行を削除 |

#### 58-2. お知らせ設定で `uni.showToast` が残っておりエラーになる
**概要：** `noticeSet.vue` の `connectIns()` 内、Instagram 連携 API がエラーを返したときの処理で `uni.showToast({...})` が呼ばれている。web 環境では `uni` が未定義のためエラーとなり、エラーメッセージが一切表示されない。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/noticeSet.vue` | `uni.showToast({title: res.message, icon: "none"})` → `this.$message.error(res.message)` |

#### 58-3. メンバー情報編集の画像アップロードで成功判定変数が誤っている
**概要：** `editinfo.vue` の `handleAvatarSuccess()` 内で `uploadMemberImage` の結果を `result` に受け取るが、成功判定で `if(res.code == 200)` と書かれており（`res` はファイルアップロードのレスポンス）、`uploadMemberImage` API の成功/失敗が正しく判定されない。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/editinfo.vue` | `if(res.code == 200)` → `if(result.code == 200)` |

#### 58-4. メッセージテンプレート保存で両テンプレートが同じ内容で上書きされる
**概要：** `moduleSetting.vue` の `saveData()` で、テンプレート1を編集しているときもテンプレート2のデータに同じ `title`/`content` を push している。保存するたびに使用中でないテンプレートも上書きされる。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/moduleSetting.vue` | 編集中テンプレートのみ新内容を設定し、未編集側は保存済みの `that.m1`/`that.m2` を維持する分岐に変更 |

#### 58-5. レビューリンク設定でカスタム画像が保存されずブラウザを閉じると消える
**概要：** `reviewSetting.vue` の `handleAvatarSuccess()` で、アップロード成功後に `URL.createObjectURL(file.raw)`（ローカル blob URL）を `this.image` にセットしており、ページリロード後に画像が消える。また `that.admin.avatar` というこのコンポーネントに存在しないプロパティを更新している。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/reviewSetting.vue` | `that.admin.avatar = res.data.path` + `URL.createObjectURL(file.raw)` → `that.image = res.data.path` に統合 |

**日付：** 2026-07-10

---

### 59. jppc 追加バグ修正（3回目のソースコードレビュー）

#### 59-1. お知らせ設定で Instagram ラジオボタンをクリックしても選択されない
**概要：** `noticeSet.vue` の Instagram 連携ラジオボタンに `@click` ハンドラが欠落しており、クリックしても `type` が 2 に切り替わらない。保存しても「お知らせ投稿機能」のままになる。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/noticeSet.vue` | Instagram ラジオボタンの `div` に `@click="setClick(2)"` を追加 |

#### 59-2. 自動メッセージのプレビューでサンキューメッセージ表示が崩れる
**概要：** `automaticSetting.vue` のプレビューダイアログで、`v-show="title != 'サンキューメッセージ'"` と書かれており、`title` は常に `""` のため、サンキューメッセージ編集中でも予約情報ブロックが非表示にならない。正しくは `titles`（編集中のメッセージ種別を保持する変数）。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/automaticSetting.vue` | プレビュー内の `v-show="title != '..."` を `v-show="titles != '..."` に修正（2箇所） |

#### 59-3. メッセージ配信・編集で `editItem()` がクラッシュする
**概要：** `mass.vue` の `editItem()` 末尾に未定義変数 `data` を参照する孤立コードが残っており（`data['order_time'] = tempDate + " " + tempTime` 等）、`ReferenceError: data is not defined` が発生する。配信履歴から「編集」を開くたびにクラッシュする。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/mass.vue` | `editItem()` 末尾の孤立コード3行（`data['order_time']`・`data['birthday_month']`・`data['sex']`）を削除 |

#### 59-4. 予約管理で通知削除後にリストが更新されない
**概要：** `appointment.vue` の `deleteNotice()` で通知削除成功後に `that.tongList.split(index, 1)` を呼び出しているが、配列に `.split()` メソッドは存在しない（文字列の `.split()` と混同）。削除しても UI 上のリストから消えない。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/appointment.vue` | `that.tongList.split(index, 1)` → `that.tongList.splice(index, 1)` |

#### 59-5. 店舗情報の「業種」が毎回未選択になる・保存で空値が送られる
**概要：** `storeInfo.vue` の `getInfo()` が `shopInfo.shop_cate` を数値キーから業種名文字列（例："美容院"）に上書きしており、`getConfig()` で `array["美容院" * 1]`（= `array[NaN]`）を参照して `TypeError` が発生。業種ドロップダウンが常に未選択状態になる。さらに `conserveClick()` が `that.industry`（ドロップダウン変更時のみ更新）で保存するため、業種を変更しないと空値 `""` が API に送られ既存設定が上書きされる。

| 対象 | 修正内容 |
|------|----------|
| `jppc/.../views/variousSet/storeInfo.vue` | `getInfo()`：誤った名前文字列代入 → `String(shop_cate)` でキー文字列に変換 |
| `jppc/.../views/variousSet/storeInfo.vue` | `getConfig()`：クラッシュする `array[shop_cate*1].value` 代入行を削除 |
| `jppc/.../views/variousSet/storeInfo.vue` | `conserveClick()`：`that.industry` → `that.shopInfo.shop_cate` に変更 |

**日付：** 2026-07-10

---

### 56. バグ修正：画像付きお知らせを開くとアプリが Loading のまま固まる

**概要：** jpcard の「お知らせ」タブで、画像付きの投稿が1件でも存在する場合、画面を開いた瞬間に Loading インジケーター（mask: true）が表示されたままアプリが操作不能になる。テキストのみの投稿では発生しない。

**根本原因：**
1. `shop.vue` の `onReachBottom()` ページライフサイクルが `uni.$emit('onReachBottom')` を発火する
2. `notification.vue` の `mounted()` がこのイベントを `uni.$on` でリッスンし、`getMore()` を呼び出す
3. `getMore()` は type を確認せず無条件に `uni.showLoading({ mask: true })` を呼び出す
4. その後 `getInsLists()` を呼ぶが、`uni.hideLoading()` は `if (that.type == 2)` ブロック内にのみ存在する
5. type==1（お知らせモード）では `hideLoading` が一切呼ばれず、Loading オーバーレイが永続 → 全操作ブロック

画像付き投稿の場合、`u-swiper` の初期化が引き起こすレイアウト変化が `onReachBottom` の早期発火を招く。

| 対象 | 修正内容 |
|------|----------|
| `jpcard/pagesA/notification/notification.vue` | `getMore()` の先頭に `if (this.type !== 2) return` を追加し、type==1 では Loading 表示・API 呼び出し両方をスキップ |

**日付：** 2026-07-06

---

### 55. バグ修正：管理WEBサイト「ご利用プラン」ページが空白になる

**概要：** card-san.jp の管理画面で「ご利用プラン」を選択しても何も表示されない問題。`GET /api/shop/vip` が利用可能なプラン設定（`vip_contract`/`vip_function`）のみを返し、ショップの現在の契約情報（`current_vip`）を返していなかった。

| 対象 | 修正内容 |
|------|----------|
| `app/Http/Controllers/Api/Shop/VipController.php`（サーバー） | `index()` メソッドに現在有効な `ShopVipModel` レコードを `current_vip` として追加返却 |

**日付：** 2026-06-30

**日付：** 2026-06-18
