# B:App内課金・Google Play課金 → クレジットカードの予約 作業手順(2026-11-02)

## 変えるもの(サーバー api.card-san.jp)
| ファイル | 内容 |
|---|---|
| app/Validators/ShopVipValidator.php | アプリの契約中にカードで申し込んだとき、Apple・Google に自動更新の状態を聞く。OFF なら受け付け、開始日=アプリの契約が終わる1時間前。ON なら解約の手順を出して止める |
| app/Libs/ApplePay.php | 関数 csSubscriptionStatus を足す(App Store Server API /inApps/v1/subscriptions。本番→サンドボックス) |
| app/Services/IapNotifyService.php | Apple の自動更新 ON・Google の再開(RESTARTED)の知らせで、予約中のカード払いを取り消す |
| app/Console/Kernel.php | 毎時20分に cs:iap-switch-guard |
| app/Libs/CsIapSwitch.php(新) | 状態を聞く・予約を取り消す・今すぐ始める |
| app/Console/Commands/CsIapSwitchGuard.php(新) | 48時間以内に始まる予約を見張る(自動更新 ON に戻った→取り消し/アプリが先に終わった→今すぐ開始)。--dry で見るだけ |

Stripe の作り(StripeBillingService)は変えない。開始日が1時間以上先なら trial_end で「その日から課金」になる、今ある処理を使う。予約は status 0 で記録され、始まる日の invoice.paid で有効になる。

## 手順
1. 4回に分けて貼る → md5 を確かめて展開(/root/cs_iapswitch/)
2. `php -d disable_functions= iapswitch_patch.php`(確認だけ)
3. `... apply`(控え /www/backup_customaid_iapswitch_日時/)
4. `... test 店舗ID`(Apple・Google に状態を聞くだけ)・`php -d disable_functions= artisan cs:iap-switch-guard --dry`
5. テスト:Stripe テストモードでの通しの確認(別に手順を作る)
6. 管理WEB(jppc)の画面の文と契約更新日の計算を直す → ビルド・反映
7. 佐藤さんへの報告に入れる
戻すとき:`... undo`

## 気をつけること
- Stripe の決済画面(カード未登録のお店)では、お試し期間の表示(「〇日間無料」など)が出ることがある。管理WEB の画面で「App内課金の終わりから始まります」と説明する
- 予約中に、お店が管理WEB でもう一度申し込んだ場合:Stripe 上に終わっていない契約(trialing)があるので「処理中」で止まる(今ある二重契約の防止②)

## 実施記録(2026-11-02)
- 本番に apply 済み(22:09)。控え /www/backup_customaid_iapswitch_20261102_220905/。Stripe の部品 Stripe\StripeClient を確認。見張り --dry は 0 件
- テストの道具と見張りが、未完了の注文(status 0)を拾っていたので、status 1 だけを見るように直した(見張りの md5 3f7c41779e06d1beb9decd7eab4a156a)
- 今は有効な App内課金・Google Play課金の契約がないため、通しの確認はまだ

## 通しの確認(有効な App内課金の契約が作れるようになったら)
1. テスト店舗で、TestFlight(サンドボックス)の App内課金を申し込む(または Android のテスト課金)
2. `php -d disable_functions= /root/cs_iapswitch/iapswitch_patch.php test 店舗ID` → 自動更新 ON・有効 はい
3. 管理WEB でカード払いを申し込む → 「定期購入が続いています。先に解約…」で止まること
4. iPhone の設定(サンドボックスのアカウント)で解約 → test で 自動更新 OFF になること
5. 管理WEB でカード払いを申し込む → 「現在のご契約終了日(〇/〇)から新しいプランが開始…」。Stripe テストのダッシュボードに trialing の契約、DB に status 0・start_time=アプリの終わり−1時間の注文
6. iPhone で定期購入を再開 → Apple の知らせ(DID_CHANGE_RENEWAL_STATUS)で予約が取り消されること(ログ my_stripe_pay の [iapswitch] reservation canceled)
7. もう一度 4〜5 → アプリの期限の1時間前に Stripe で請求され、注文が status 1 になること(サンドボックスは期間が短いので数分で確かめられる)
- 本番の Stripe はテストカードが使えないため、Stripe はテストモードの店舗で行う(やり方は実施時に相談)
