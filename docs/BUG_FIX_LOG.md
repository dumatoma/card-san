# バグ修正記録

フロントエンド・バックエンドを横断して記載。ユーザーへの影響ありとなしで分けて記録する。

---

### 1. Apple IAP 解約後に「継続する」を押すと新規課金が発生する

**影響：** 管理画面から Apple IAP サブスクリプションを解約操作すると、店舗アプリで「継続する」ボタンが表示される。このボタンを押すと、通常の購入フローに誘導されて新規の Apple IAP 課金が発生してしまう。また「継続の手続きが完了しました」の成功メッセージも表示されない

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `app/Http/Controllers/Api/Shop/VipController.php` | 管理側の解約操作は DB の `cancel_time` と `is_subscription` を更新するだけで Apple 側のサブスクリプションはそのまま継続している。しかし「継続する」ボタンは通常の新規購読フロー（`store()` → Apple IAP ネイティブ購入 → `pay()`）に遷移するため、Apple IAP が重複して起動され新たな課金が発生していた。また resume 用エンドポイントが存在しないため成功メッセージも表示されなかった | `store()` の冒頭に Apple IAP の resume 判定を追加。期間内（`end_time > time()`）かつ `cancel_time > 0` の Apple IAP サブスクリプションが同一プランで申込まれた場合、新規注文を作成せず `cancel_time=0, is_subscription=1` にリセットしてコード 201 で「継続の手続きが完了しました」を返す。フロントエンドはコード 201 をエラーとして扱いトーストで表示するため Apple IAP は起動されず二重課金が防止される |

---

### 2. プラン変更時に Stripe で二重課金が発生する

**影響：** 店舗がプランを変更すると、旧プランのサブスクリプションが残ったまま新サブスクリプションも課金される

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `app/Http/Controllers/Api/Shop/VipController.php` | Stripe 分岐で早期 `return` しており、旧サブスクリプションのキャンセル処理（`CreateSubscriptionsEndJob`）が一切実行されなかった。また注文の `is_subscription` も更新されず、同一注文から重複した支払いリンクを生成できた | Stripe でも Apple/Google Pay と同様に、支払いリンク返却前に注文を更新し `CreateSubscriptionsEndJob` を dispatch するよう修正 |

---

### 2. 追加オプション購入後に店舗設定の再入力が必要になる

**影響：** Stripe の追加カウント購入（メッセージ・クーポン・SMS）が完了してもアプリが未払いと判断し、設定入力画面へ再誘導される

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `resources/views/pay/show/stripe.blade.php` | 支払い成功後に `"Payment succeeded!"` を表示するだけで、バックエンドの注文ステータスを更新していなかった。アプリが WebView から戻った時点で注文が `status=0` のまま残るため未払い扱いになる | 支払い成功時に `/api/pay/stripe/vip_count_complete` を呼び出してバックエンドを更新するよう修正。エラーメッセージも日本語化 |
| `app/Http/Controllers/Api/PayController.php` | 上記コールバックに対応するエンドポイントが存在しなかった | `stripeVipCountComplete()` を新設。`client_secret` で注文を特定し、Stripe 側で決済ステータスを検証した上で注文・店舗カウント・支払いログを更新 |
| `app/Libs/StripePay.php` | `retrievePaymentIntent()` メソッドが存在しなかった | メソッドを追加 |
| `routes/api.php` | 上記エンドポイントのルートが未登録 | `POST /api/pay/stripe/vip_count_complete` を追加（認証不要、Stripe 検証で安全性を担保） |

---

### 3. API エラーが握りつぶされ、画面が無反応になる

**影響：** 通信エラーや認証エラーが発生しても、画面に何も表示されず処理が止まる

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpcard・jpshop・outsiteAppointment / utils/request.js` | エラーハンドラで `return` なし → `Promise.reject` が破棄される | `return Promise.reject(error)` に修正 |
| `jpcard/utils/request.js` | `Message.error()` を呼び出しているが import がコメントアウト済み → `ReferenceError` | 該当行を削除 |
| `jpcard・jpshop・outsiteAppointment / utils/request.js` | ネットワークエラー時に `Promise.resolve(error.response)` でエラーを正常扱い | `Promise.reject(error)` に修正 |

---

### 2. ネイティブアプリで 401 後もログイン状態が続く

**影響：** トークン期限切れや不正アクセス時にログアウトされず、以降のリクエストがすべて 401 になり続ける

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpcard・jpshop・outsiteAppointment / utils/request.js` | `localStorage.removeItem()` を使用（uni-app ネイティブ・WeChat では非対応） | `uni.removeStorageSync()` に変更 |

---

### 3. チャット画面で WebSocket が切断後に再接続されない

**影響：** バックグラウンドからの復帰やネットワーク切替後、リアルタイム通知が届かなくなる

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpcard・jpshop / common/socket.js` | `onClose` 内に `return;` があり、以降のクリーンアップ・再接続処理がデッドコード | `return;` を削除 |
| `jpcard・jpshop / common/socket.js` | 再接続条件 `traderDetailIndex==2 \|\| accountStateIndex==0 \|\| followFlake` がコンストラクタ初期値（100/100/false）と一致せず永遠に再接続しない | `if (!this.is_open_socket)` に簡略化 |
| `jpcard・jpshop / store/index.js` | WebSocket メッセージの `JSON.parse()` が try-catch なし → 不正 JSON でアプリクラッシュ | try-catch を追加 |
| `jpcard・jpshop / store/index.js` | `result.data.client_id` の null ガードなし → 接続直後に `TypeError` が発生することがある | `result.data &&` の条件を追加 |
| `jpshop / store/index.js` | `bindSocket()` の Promise に `.catch()` なし → 失敗時に Unhandled Promise Rejection | `.catch()` ハンドラを追加 |

---

### 4. Apple Pay で購入しても商品が付与されない

**影響：** 課金は完了するが店舗プランや追加カウントが反映されない。低速回線では特に発生しやすい

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpshop/manifest.json` | Apple IAP タイムアウトが 10 秒 → 低速回線で課金後にセッション切断 | `"timeout": 30000` に変更 |
| `app/Libs/ApplePay.php` | IAP 共有シークレット（`password`）がソースコードにハードコード → 漏洩時にレシート偽造が可能 | クラス内定数を廃止し、`.env` の `APPLE_IAP_PASSWORD` から読み込むよう変更 |
| `app/Http/Controllers/Api/Shop/VipCountController.php` | `getTransactionInfo()` の失敗判定が `if(!$res)` → 配列は常に truthy のため失敗を検出できず、以降で `$res['output']` にアクセスしてクラッシュ | `if(!$res['success'])` に修正 |
| `app/Http/Controllers/Api/Shop/VipCountController.php` | Apple / Google Pay エラー時に生配列 `return $res` → HTTP 200 が返りフロントが成功と誤認 | `return responseToJson(400, $res['message'])` に修正 |
| `app/Services/ShopVipService.php` | `applePaySubscriptions()` 内で同じ `if(!$res)` 判定ミス | `if(!$res['success'])` に修正 |
| `サーバー .env` | `APPLE_IAP_PASSWORD` キーが未定義 → ApplePay クラスが `null` を Apple に送信していた | `APPLE_IAP_PASSWORD=...` を追加 |

---

### 5. プラン解約時にサーバーエラー 500 が発生する

**影響：** 解約操作中に DB エラーが起きると、エラーハンドラ自体がクラッシュして原因不明の 500 が返る

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `app/Http/Controllers/Api/Shop/VipController.php` | `catch (\Exception $e)` で捕捉しているのに `$th->getMessage()` を参照（`$th` は未定義）→ 二重エラー | `$e->getMessage()` に修正、`\Exception` に名前空間を追加 |

---

### 6. 認証コード再送時のトースト表示が壊れる

**影響：** メール認証画面で「再送」を押すと、トーストに `undefined` または空文字が表示される

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpRegister/pages/secondStep/secondStep.vue` | `success` コールバック内で未定義変数 `code` を参照（正しくは `that.code`） | `that.code` に修正 |

---

### 7. 本番環境にデバッグコードが残存

**影響：** Apple Pay 回調（Webhook）レスポンスにデバッグ出力が混入する。Apple からの通知処理が正常に完了しない

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `app/Libs/ApplePay.php` | `notify()` 内に `dump($decoded)` / `dump($child_decoded)` が残存 | 削除 |
| `app/Http/Controllers/Api/Shop/VipCountController.php` | Stripe 処理の `return` 以降のデッドコード内に `dd($res['output'])` が残存 | デッドコードごと削除 |

---

### 8. エラーメッセージが中国語で表示される

**影響：** 日本語アプリなのに HTTP ステータスエラー時のトーストが中国語になる

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpcard・jpshop・outsiteAppointment / utils/request.js` | switch-case のエラーメッセージが中国語（例：「服务器错误」） | すべて日本語に翻訳（例：「サーバーエラーが発生しました」） |

---

---

## ユーザーには見えない修正（コード品質・予防的対応）

### A. UUID 取得の潜在的 TypeError

**背景：** 通常フローでは発生しないが、ストレージが空の状態でリクエストが走った場合にクラッシュする

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpcard・jpshop・outsiteAppointment / utils/request.js` | `uni.getStorageSync("user")` が null/undefined の場合に `.uuid` 参照でエラー | `\|\| {}` でデフォルト値を保証し `uuid = info.uuid \|\| ''` に修正 |

---

### B. 追加購入 API の重複定義

**背景：** 現時点では動作に差はないが、将来どちらかだけ修正した場合に乖離するリスク

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpshop/api/index.js` | `buyextras` / `buyextra` が `getExtra` / `payExtra` と同一の実装で重複定義 | `buyextras = getExtra`、`buyextra = payExtra` のエイリアスに変更し実装を一本化 |

---

### C. WebSocket 再接続処理のデッドコード

**背景：** ユーザーから見ると「再接続しない」という症状（ユーザー影響あり・3 に記録）だが、その根本原因となったデッドコード自体はコード品質問題として別途記録

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpcard・jpshop / common/socket.js` | `onClose` 内の `return;` 以降の処理（`clearInterval`・reconnect 呼び出し）がデッドコードになっており、コードを読んでも挙動が把握できない | `return;` を削除してデッドコードを解消 |
| `jpcard・jpshop / common/socket.js` | 再接続条件に使われる `traderDetailIndex`・`accountStateIndex`・`followFlake` がコンストラクタで設定されるが、再接続ロジックとの対応が不明確でメンテ不能 | 条件を `!this.is_open_socket` のみに簡略化 |

---

### D. Apple IAP 共有シークレットの管理方法

**背景：** ユーザー影響（4）と同根だが、セキュリティ管理上の問題として独立して記録

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `app/Libs/ApplePay.php` | Apple IAP 共有シークレットがソースコード内にリテラルで記述 → リポジトリ漏洩時にレシート偽造が可能 | クラスプロパティから削除し `env('APPLE_IAP_PASSWORD')` で取得するよう変更 |
| `サーバー .env` | `APPLE_IAP_PASSWORD` キーが存在せず `env()` が `null` を返していた | `APPLE_IAP_PASSWORD=...` を追加 |
| `jpshop/manifest.json` | 同じシークレットがクライアントアプリにも記述（フロントエンドのビルド成果物に含まれる） | コメントで警告を追記（根本対応はサーバーサイドでのレシート検証への移行） |

---

### E. `bindSocket()` の未処理 Promise Rejection

**背景：** エラーが発生してもコンソールに警告が出るだけでユーザーには見えないが、将来的な Promise チェーン追加時に予期せぬ動作を引き起こすリスク

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpshop/store/index.js` | `bindSocket()` の呼び出しに `.catch()` がなく、失敗時に Unhandled Promise Rejection が発生 | `.catch((err) => { console.log(...) })` を追加 |

---

### 9. ネイティブ決済（Apple IAP / Google Pay）の複数バグ

**影響：** 漏単（課金済みなのにプランが付与されない）・クラッシュ・二重支払い・UI フリーズが発生する可能性があった

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpshop/pages/payment/payment.vue:265` | Apple IAP の `buyPlans()` に `.catch()` がなく、ネットワーク異常時に loading が永久に表示されたまま漏単が発生 | `.catch()` を追加し `uni.hideLoading()` とエラートースト表示 |
| `jpshop/pages/payment/payment.vue:406` | Google Pay の `buyPlans()` も同様に `.catch()` なし | 同上 |
| `jpshop/pages/payment/payment.vue:398` | Google Pay の `payAll` コールバックで `e.msg != 'success'` 時に何もしない（ユーザーに無反応） | `else` ブランチを追加してエラートーストを表示 |
| `jpshop/pages/payment/payment.vue:382` | `subscriptionOfferDetails[1]` を決め打ちで参照（要素が 1 件のとき `undefined` → クラッシュ） | `[1]` を廃止し `basePlanId` による `forEach` 照合に統一。一致なしの場合は `[0]` にフォールバック |
| `jpshop/pages/payment/payment.vue:402` | `e.data[0].original.purchaseToken` を配列長チェックなしで参照（空配列でクラッシュ） | `e.data` の長さと `e.data[0].original` の存在を確認してからアクセス |
| `jpshop/pages/payment/payment.vue:400` | Google Pay が `transaction_id` を後端に送らず、Apple IAP との整合性がなく対帳不能 | `e.data[0].original.orderId` を `da.transaction_id` に追加 |
| `jpshop/pages/payment/payment.vue:286` | Apple IAP の `fail` コールバックで Error オブジェクトをそのまま `content` に渡し `[object Object]` 表示 | 固定の日本語メッセージに変更 |
| `jpshop/pages/payment/payment.vue:241` | `requestOrder` のエラータイトルが英語 `"warning"` | 日本語 `"エラー"` に変更、`content` も `errormsg.message` を優先する日本語表記に |
| `jpshop/pages/payment/payment.vue:330` | `restoreComplateRequest` の `finishTransaction` 失敗コールバックが完全に空（iOS で未完了取引が蓄積する恐れ） | `console.log` によるエラーロギングを追加 |
| `jpshop/pages/payment/payment.vue:433` | `querySku` 失敗時に `uni.hideLoading()` がなく UI がフリーズ | 失敗ブランチ先頭に `uni.hideLoading()` を追加 |
| `jpshop/pages/payment/payment.vue:413` | Google Pay 成功後の遷移が `uni.switchTab` / 2500ms、Apple は `uni.reLaunch` / 2000ms と不統一 | Google Pay も `uni.reLaunch` / 2000ms に統一 |
| `jpshop/pages/payment/payment.vue:data` | 支払いボタン連打で `requestPayment` が複数起動する競合状態 | `paying` フラグを追加し `pay()` 入口でガード、`complete` コールバックでリセット |

---

### 10. サブスクリプション終了後に再購読できない（支払い方法変更不可）

**影響：** Stripe（card_type=1）で契約していたユーザーがサブスクリプション期限切れ後にモバイルアプリから再購読しようとすると、「PCブラウザでお手続きください」と表示されて再購読できない

**根本原因：** `toSuccess1()` の Stripe ブロック条件が `that.using.card_type == 1` のみで、サブスクリプションが有効かどうかを判定していなかった。`that.using`（= `shop_info.vip`）は期限切れ後も前回の `card_type=1` を保持し続けるため、失効済みユーザーも誤ってブロックされていた

| 修正箇所 | 問題 | 修正内容 |
|----------|------|----------|
| `jpshop/pages/payment/payment.vue:481` | `card_type == 1` だけでブロック判定 → 期限切れStripeユーザーが再購読できない | `vips.length > 0 && vips[0].cancel_time == 0`（有効な契約が存在する）を条件に追加。期限切れ・解約済みはネイティブ決済での再購読を許可 |
