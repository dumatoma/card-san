# Card-San デプロイメントガイド

**最終更新**: 2026-04-24

---

## 前提条件

| ツール | バージョン | 用途 |
|-------|-----------|------|
| HBuilderX | 最新版（3.x推奨） | uni-app ビルド・実機テスト |
| Node.js | 16.x 以上 | admin ビルド |
| npm / yarn | — | 依存パッケージ管理 |
| Git | — | ソース管理 |

---

## リポジトリ

```bash
# フロントエンド
git clone https://github.com/dumatoma/card-san.git

# バックエンドAPI（別リポジトリ）
# api.card-san.jp（Laravel 8）は別途管理
```

---

## 1. jpcard — 会員端アプリ（Card-San）

### ローカル開発（HBuilderX）

1. HBuilderX でフォルダを開く: `card-san/jpcard/`
2. ツールバー → 実行 → ブラウザ（H5プレビュー）または実機/シミュレータ

### 依存パッケージのインストール

```bash
cd jpcard
npm install
```

### API ベースURL変更

`jpcard/utils/request.js` の `baseURL` を変更:

```js
// 開発
baseURL: 'http://localhost:8000'  // ローカルLaravel

// 本番
baseURL: 'https://api.card-san.jp'
```

### アプリビルド（HBuilderX）

1. メニュー → 発行 → ネイティブApp-クラウドパッケージ
2. iOS: Apple Developer証明書・プロビジョニングプロファイル必要
3. Android: キーストアファイル必要

### H5デプロイ

1. メニュー → 発行 → Webサイト-H5手動
2. `unpackage/dist/build/h5/` の内容をWebサーバーにアップロード
3. nginxリバースプロキシ設定例:

```nginx
server {
    listen 80;
    server_name card-san.jp;
    root /var/www/card-san/jpcard;
    index index.html;
    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

---

## 2. jpshop — 店舗管理端アプリ（CardSan管理）

### ローカル開発

1. HBuilderX でフォルダを開く: `card-san/jpshop/`
2. `jpshop/utils/request.js` の `baseURL` を確認・変更

### 依存パッケージ

```bash
cd jpshop
npm install
```

### ビルド手順

jpcard と同様。HBuilderX → 発行 → ネイティブApp-クラウドパッケージ

### 注意事項

- Google Pay 設定: `manifest.json` の `payment.google` SDK設定を確認
- Apple IAP 設定: `manifest.json` の `payment.appleiap` 設定を確認
- App Store / Google Play 審査時は本番APIを使用すること

---

## 3. jpRegister — 新規店舗登録（H5のみ）

### ローカル開発

1. HBuilderX でフォルダを開く: `card-san/jpRegister/`
2. 発行先は H5のみ（アプリ配布しない）

### H5デプロイ

1. HBuilderX → 発行 → Webサイト-H5手動
2. `unpackage/dist/build/h5/` をサーバーにデプロイ
3. 推奨デプロイ先: `https://register.card-san.jp/` など

```nginx
server {
    listen 443 ssl;
    server_name register.card-san.jp;
    root /var/www/card-san/jpRegister;
    index index.html;
    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

---

## 4. outsiteAppointment — 外部リンク簡易予約（H5のみ）

### ローカル開発

1. HBuilderX でフォルダを開く: `card-san/outsiteAppointment/`

### H5デプロイ

店舗の外部予約リンクとして使用されるため、URLに `shop_id` などのパラメータを付与して利用。

```bash
# ビルド出力: unpackage/dist/build/h5/
# デプロイ先例: https://book.card-san.jp/
```

---

## 5. admin/admin — Webシステム管理画面

### ローカル開発

```bash
cd admin/admin
npm install
npm run dev
# → http://localhost:8080 で起動
```

### 本番ビルド

```bash
cd admin/admin
npm run build
# → dist/ フォルダに出力される
```

### 本番デプロイ（nginx例）

```bash
# dist/ の内容をサーバーにアップロード
scp -r dist/* user@server:/var/www/admin.card-san.jp/
```

```nginx
server {
    listen 443 ssl;
    server_name admin.card-san.jp;
    root /var/www/admin.card-san.jp;
    index index.html;
    location / {
        try_files $uri $uri/ /index.html;
    }
    location /adminapi/ {
        proxy_pass https://api.card-san.jp/adminapi/;
        proxy_set_header Host api.card-san.jp;
    }
}
```

### APIベースURL変更

`admin/admin/src/http/api.js` を確認:

```js
// 本番URLになっていることを確認
const BASE_URL = 'https://api.card-san.jp'
```

---

## 6. 環境変数・設定まとめ

| 設定項目 | ファイル | 本番値 |
|---------|--------|-------|
| 会員端 API URL | `jpcard/utils/request.js` | `https://api.card-san.jp` |
| 店舗管理端 API URL | `jpshop/utils/request.js` | `https://api.card-san.jp` |
| 登録端 API URL | `jpRegister/` 内 request設定 | `https://api.card-san.jp` |
| 予約端 API URL | `outsiteAppointment/api/index.js` | `https://api.card-san.jp` |
| admin API URL | `admin/admin/src/http/api.js` | `https://api.card-san.jp` |
| WebSocket | `jpcard/store/index.js` | `ws://47.245.1.2:21000` |
| WebSocket | `jpshop/store/index.js` | `ws://47.245.1.2:21000` |

---

## 7. uni-app AppID確認

各アプリのAppIDは以下の通り（DCloud開発者センターで管理）:

| アプリ | AppID |
|-------|-------|
| jpcard | `__UNI__1ED8C1A` |
| jpshop | `__UNI__40CEDE2` |
| jpRegister | `__UNI__ED993F2` |
| outsiteAppointment | `__UNI__26F5B98` |

---

## 8. CI/CDの推奨構成（参考）

```
GitHub Actions / GitLab CI
├── push to main
│   ├── admin → npm build → upload to server
│   ├── jpRegister → HBuilderX CLI build H5 → upload
│   └── outsiteAppointment → HBuilderX CLI build H5 → upload
└── tag release
    ├── jpcard → HBuilderX Cloud Pack → App Store / Google Play
    └── jpshop → HBuilderX Cloud Pack → App Store / Google Play
```

---

## 9. よくある問題

### uni-app ビルドエラー

```bash
# node_modules の再インストール
rm -rf node_modules package-lock.json
npm install
```

### API 通信エラー（CORS）

- バックエンド（Laravel）の CORS設定を確認
- `config/cors.php` の `allowed_origins` にフロントエンドドメインを追加

### WebSocket 接続失敗

- `ws://47.245.1.2:21000` のポート21000が開放されているか確認
- 本番環境では `wss://` （TLS）に変更推奨

### H5でのルーティング404

- nginx の `try_files $uri $uri/ /index.html;` 設定を確認
- uni-app の `manifest.json` → `h5.router.mode` を `history` に設定している場合は必須
