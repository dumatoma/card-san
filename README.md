# Card-San フロントエンド

日本のローカル生活サービス店舗（美容・ネイル・フィットネス・飲食等）向けの  
オールインワン デジタル会員管理プラットフォーム。

> **バックエンドAPI**: Laravel 8（別リポジトリ）— 本番ドメイン `https://api.card-san.jp`

---

## アプリ構成

このリポジトリには6つの独立したフロントエンドアプリが含まれています。

| ディレクトリ | アプリ名 | 種類 | 対象ユーザー | 説明 |
|------------|---------|------|------------|------|
| `jpcard/` | Card-San | uni-app | 消費者（C端） | 電子会員カード・クーポン・予約・チャット |
| `jpshop/` | CardSan管理 | uni-app | 店舗オーナー/スタッフ（B端） | CRM・予約・通知・会員カード・決済 |
| `jpRegister/` | 登録する | uni-app (H5) | 新規店舗 | 3ステップのメール登録フロー |
| `outsiteAppointment/` | ご予約 | uni-app (H5) | 来店前の顧客 | 店舗外部リンクからの簡易予約 |
| `admin/admin/` | jadmin | Vue + Element UI | システム管理者 | 全店舗・売上・通知の一元管理 |
| `jppc/jp-card-pc/` | — | — | — | PCブラウザ版（未実装） |

---

## 技術スタック

- **uni-app アプリ**: Vue 2 + Vuex + uView UI 2.0 + axios
- **管理画面**: Vue 2 + Element UI 2.15 + Webpack 3
- **リアルタイム通信**: WebSocket
- **バックエンド**: Laravel 8 / PHP（`https://api.card-san.jp`）
- **決済**: Google Pay / Apple IAP
- **ログイン**: Google / LINE / Facebook / Apple OAuth

---

## クイックスタート

### uni-appアプリ（jpcard / jpshop / jpRegister / outsiteAppointment）

[HBuilderX](https://www.dcloud.io/hbuilderx.html) が必要です。

```bash
git clone https://github.com/dumatoma/card-san.git
# HBuilderX でいずれかのサブディレクトリを開く
```

各アプリのAPIエンドポイント設定:
- `jpcard/utils/request.js`
- `jpshop/utils/request.js`

```js
baseURL: 'https://api.card-san.jp'  // 本番
```

### 管理画面（admin）

```bash
cd admin/admin
npm install
npm run dev      # 開発サーバー起動 → http://localhost:8080
npm run build    # 本番ビルド → dist/
```

---

## ドキュメント

| ドキュメント | 内容 |
|------------|------|
| [docs/PRD.md](docs/PRD.md) | プロダクト要件定義書（全機能・仕様） |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | デプロイメントガイド（ビルド・環境設定・nginx） |
| [docs/PRD_zh.md](docs/PRD_zh.md) | 产品需求文档（中文版） |
| [docs/DEPLOYMENT_zh.md](docs/DEPLOYMENT_zh.md) | 部署指南（中文版） |
| [README_zh.md](README_zh.md) | 中文版 README |

---

## APIルート対応

| パスプレフィックス | 用途 |
|-----------------|------|
| `/api/member/*` | 会員端（jpcard・outsiteAppointment） |
| `/api/shop/*` | 店舗管理端（jpshop・jpRegister） |
| `/adminapi/*` | システム管理画面（admin） |
| `/api/config` | 共通設定 |

---

## uni-app AppID

| アプリ | AppID |
|-------|-------|
| jpcard | `__UNI__1ED8C1A` |
| jpshop | `__UNI__40CEDE2` |
| jpRegister | `__UNI__ED993F2` |
| outsiteAppointment | `__UNI__26F5B98` |
