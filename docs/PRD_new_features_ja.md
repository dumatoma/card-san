# Card-San 新機能 プロダクト要件定義書（PRD）

**バージョン**: 1.0  
**作成日**: 2026-04-24  
**対象読者**: 開発者・デザイナー・プロダクトマネージャー  
**対象アプリ**: jpshop（店舗管理端）/ jpcard（会員端）

---

## 1. 背景・目的

Card-Sanは現在、Instagram連携・Googleマップ表示・チャット機能を個別に提供している。顧客からの要望に基づき、以下4機能を追加することで、店舗オーナーの業務効率と顧客体験の向上を図る。

| # | 機能 | 対象端 |
|---|-----|-------|
| 1 | Google Business Profile（GBP）データ連動 | jpshop |
| 2 | Googleマップ クチコミ管理 | jpshop |
| 3 | Instagram × GBP 投稿連動 | jpshop |
| 4 | チャット AIカスタマーサービス | jpshop / jpcard |

---

## 2. 既存機能との連携ポイント

新機能は以下の既存実装を最大限に流用する。

| 既存資産 | 流用箇所 |
|---------|---------|
| Instagram OAuthフロー（`getInsUrl` → `bindIns`） | GBP OAuthも同パターンで実装 |
| WebView容器（`webins.vue`） | GBP投稿プレビュー・Instagram投稿表示 |
| WebSocket通知（`wss://wss.card-san.jp/wss`） | 新着クチコミのリアルタイム通知 |
| チャット `messageType`（1=テキスト/2=画像/3=リンク） | type=4 でAIメッセージを追加 |
| `reviewSetting.vue` | GBPクチコミ管理タブを統合 |
| `shopInfo.vue` | GBP連携ボタンを追加 |

---

## 3. 機能詳細仕様

---

### 機能1: Google Business Profile（GBP）データ連動

#### 3.1.1 概要

店舗オーナーが Card-San アプリ内で Google Business Profile と連携し、店舗基本情報（営業時間・住所・業種・写真）をGBPと双方向同期できる。

#### 3.1.2 ユーザーストーリー

- 店舗オーナーとして、GBPのアカウントをCard-Sanに一度連携するだけで、Card-San側で更新した店舗情報が自動的にGBPにも反映されてほしい
- 店舗オーナーとして、GBPに登録済みの最新情報をCard-Sanに取り込んで、二重入力を避けたい

#### 3.1.3 連携フロー

```
① 「Google Business Profile 連携」ボタンタップ（shopInfo.vue）
      ↓
② API呼び出し: GET /api/shop/google_business/get_auth_url
      ↓
③ Google OAuth 2.0 承認ページ（ブラウザ / WebView）
      ↓
④ ユーザーが許可 → コールバック受信
      ↓
⑤ API呼び出し: POST /api/shop/google_business/create_access_token
      ↓
⑥ アクセストークンをサーバーに保存
      ↓
⑦ GBP情報取得 → Card-Sanに自動反映（確認ダイアログあり）
```

#### 3.1.4 UI要件

**連携設定画面** (`pages/settings/gbpSetting/gbpSetting.vue` 新規)

| 要素 | 説明 |
|-----|------|
| 連携状態表示 | 未連携 / 連携済み（連携日時表示） |
| 連携ボタン | 未連携時のみ表示 |
| 解除ボタン | 連携済み時に表示 |
| 同期設定トグル | Card-San → GBP の自動同期のON/OFF |
| 手動同期ボタン | 今すぐ同期を実行 |
| 同期項目選択 | 営業時間・住所・業種・写真（チェックボックス） |
| 最終同期時刻 | 「最終同期: 2026/04/24 15:30」など |

**shopInfo.vue への追加**

- 「Google Business Profile と連携する」ボタンを店舗情報ページ下部に追加
- 連携済みの場合は連携マーク + 「GBP設定」リンクを表示

#### 3.1.5 必要なAPI（後端追加）

| メソッド | パス | 説明 |
|---------|-----|------|
| GET | `/api/shop/google_business/get_auth_url` | OAuth認証URL取得 |
| POST | `/api/shop/google_business/create_access_token` | アクセストークン保存 |
| GET | `/api/shop/google_business/info` | GBP店舗情報取得 |
| POST | `/api/shop/google_business/sync` | Card-San→GBP同期 |
| DELETE | `/api/shop/google_business/disconnect` | 連携解除 |
| GET | `/api/shop/google_business/status` | 連携状態確認 |

#### 3.1.6 フロントエンド追加API（`jpshop/api/index.js`）

```js
export const getGBPAuthUrl = () => request({ url: '/api/shop/google_business/get_auth_url', method: 'get' })
export const bindGBP = (data) => request({ url: '/api/shop/google_business/create_access_token', method: 'post', data })
export const getGBPInfo = () => request({ url: '/api/shop/google_business/info', method: 'get' })
export const syncToGBP = (data) => request({ url: '/api/shop/google_business/sync', method: 'post', data })
export const disconnectGBP = () => request({ url: '/api/shop/google_business/disconnect', method: 'delete' })
```

#### 3.1.7 必要なGBP APIスコープ

- `https://www.googleapis.com/auth/business.manage`（店舗情報の読み書き）

---

### 機能2: Googleマップ クチコミ管理

#### 3.2.1 概要

店舗オーナーが Card-San アプリ内でGoogleマップのクチコミを確認・返信でき、新着クチコミをリアルタイムで通知として受け取れる。

#### 3.2.2 ユーザーストーリー

- 店舗オーナーとして、Googleマップの新着クチコミをCard-Sanのアプリ内で通知として受け取り、そのままアプリ内で返信したい
- 店舗オーナーとして、クチコミの評価・件数推移を確認して、接客改善の参考にしたい

#### 3.2.3 機能詳細

**クチコミ一覧**
- 評価（星1〜5）、投稿者名、コメント本文、投稿日時を表示
- 返信済み / 未返信のフィルタリング
- 星評価別フィルタリング（「★3以下を表示」等）
- 無限スクロール（ページネーション）

**クチコミ返信**
- 返信入力フォーム（最大4096文字）
- 投稿後は返信内容をその場に表示
- 返信の編集・削除

**クチコミ統計**
- 平均評価スコア（数値 + 星表示）
- 直近30日・90日の評価件数グラフ
- 未返信件数バッジ

**リアルタイム通知**
- 新着クチコミをWebSocket経由でプッシュ通知
- アプリのバッジ（未読数）に反映
- 通知タップでクチコミ詳細へ直遷移

#### 3.2.4 UI要件

**クチコミ管理画面** (`pages/message/gbpReviews/gbpReviews.vue` 新規)

| 要素 | 説明 |
|-----|------|
| 統計ヘッダー | 平均評価・総件数・未返信件数 |
| フィルタータブ | 全て / 未返信 / 返信済み |
| クチコミカード | 投稿者アイコン・名前・星・日時・本文・返信ボタン |
| 返信欄 | テキストエリア + 送信ボタン（返信済みは編集ボタン） |
| 空状態 | 「クチコミがまだありません」 |

**既存ページへの統合**
- `reviewSetting.vue` の上部に「Google クチコミ管理」タブを追加
- GBP未連携の場合は「連携してクチコミを管理する」へ誘導バナーを表示

#### 3.2.5 必要なAPI（後端追加）

| メソッド | パス | 説明 |
|---------|-----|------|
| GET | `/api/shop/google_business/reviews` | クチコミ一覧取得 |
| POST | `/api/shop/google_business/reviews/{id}/reply` | 返信投稿 |
| PUT | `/api/shop/google_business/reviews/{id}/reply` | 返信編集 |
| DELETE | `/api/shop/google_business/reviews/{id}/reply` | 返信削除 |
| GET | `/api/shop/google_business/reviews/stats` | 統計情報取得 |

#### 3.2.6 フロントエンド追加API（`jpshop/api/index.js`）

```js
export const getGBPReviews = (data) => request({ url: '/api/shop/google_business/reviews', method: 'get', params: data })
export const replyGBPReview = (id, data) => request({ url: `/api/shop/google_business/reviews/${id}/reply`, method: 'post', data })
export const editGBPReply = (id, data) => request({ url: `/api/shop/google_business/reviews/${id}/reply`, method: 'put', data })
export const deleteGBPReply = (id) => request({ url: `/api/shop/google_business/reviews/${id}/reply`, method: 'delete' })
export const getGBPReviewStats = () => request({ url: '/api/shop/google_business/reviews/stats', method: 'get' })
```

#### 3.2.7 WebSocket通知フォーマット（新着クチコミ）

```json
{
  "type": "gbp_review",
  "data": {
    "review_id": "xxx",
    "reviewer_name": "山田 太郎",
    "star_rating": 5,
    "comment": "とても良いサービスでした",
    "create_time": "2026-04-24T10:00:00Z"
  }
}
```

---

### 機能3: Instagram × GBP 投稿連動

#### 3.3.1 概要

InstagramとGoogle Business Profileの投稿を Card-San アプリから一元管理し、Instagram投稿をGBPの「最新情報」に連動させることで、SNS運用の手間を半減させる。

#### 3.3.2 ユーザーストーリー

- 店舗オーナーとして、Instagramに投稿した写真やキャプションを、自動でGBPの「最新情報」にも転載したい
- 店舗オーナーとして、GBP専用の「最新情報」（クーポン・イベント・お知らせ）をCard-Sanアプリから直接投稿したい

#### 3.3.3 機能詳細

**Instagram → GBP 連動投稿**

| 機能 | 説明 |
|-----|------|
| 自動連動ON/OFF | Instagram投稿時にGBPへ自動転載するか設定 |
| 連動種別フィルター | 画像投稿のみ / リール除く 等 |
| 手動転載 | Instagram投稿一覧から個別にGBPへ転載 |
| プレビュー確認 | 転載前にGBP表示イメージを確認できる |

**GBP「最新情報」投稿**

| 投稿種別 | 必須項目 | 任意項目 |
|---------|---------|---------|
| お知らせ | 本文（1500文字以内） | 画像・ボタン（詳細を見る等）|
| イベント | タイトル・開始日・終了日 | 本文・画像・ボタン |
| クーポン | タイトル・開始日・終了日 | 本文・クーポンコード・画像 |

**投稿スケジュール**
- 予約投稿（日時指定）
- 投稿済み履歴確認（GBP・Instagram）

#### 3.3.4 UI要件

**SNSアカウント統合管理画面** (`pages/settings/socialMedia/socialMedia.vue` 新規)

**タブ構成**:
1. **「連携設定」タブ** — Instagram / GBP の連携状態・自動連動ON/OFF
2. **「投稿管理」タブ** — 投稿一覧（Instagram / GBP 切り替え）・新規GBP投稿ボタン
3. **「連動履歴」タブ** — Instagram→GBP転載の履歴

**新規GBP投稿フォーム**

| 要素 | 説明 |
|-----|------|
| 投稿種別選択 | お知らせ / イベント / クーポン |
| 本文入力 | テキストエリア（文字数カウンター付き） |
| 画像アップロード | 最大1枚（GBP制限） |
| 日時設定 | イベント・クーポン時に表示 |
| プレビュー | 投稿前の表示確認 |
| 今すぐ投稿 / 予約投稿 | 選択式 |

#### 3.3.5 必要なAPI（後端追加）

| メソッド | パス | 説明 |
|---------|-----|------|
| GET | `/api/shop/google_business/posts` | GBP投稿一覧 |
| POST | `/api/shop/google_business/posts` | GBP投稿作成 |
| DELETE | `/api/shop/google_business/posts/{id}` | GBP投稿削除 |
| POST | `/api/shop/google_business/posts/sync_instagram` | Instagram→GBP転載 |
| GET/PUT | `/api/shop/google_business/integration/settings` | 連動設定の取得/保存 |

#### 3.3.6 フロントエンド追加API（`jpshop/api/index.js`）

```js
export const getGBPPosts = (data) => request({ url: '/api/shop/google_business/posts', method: 'get', params: data })
export const createGBPPost = (data) => request({ url: '/api/shop/google_business/posts', method: 'post', data })
export const deleteGBPPost = (id) => request({ url: `/api/shop/google_business/posts/${id}`, method: 'delete' })
export const syncInsToGBP = (data) => request({ url: '/api/shop/google_business/posts/sync_instagram', method: 'post', data })
export const getIntegrationSettings = () => request({ url: '/api/shop/google_business/integration/settings', method: 'get' })
export const updateIntegrationSettings = (data) => request({ url: '/api/shop/google_business/integration/settings', method: 'put', data })
```

---

### 機能4: チャット AIカスタマーサービス

#### 3.4.1 概要

既存のチャット機能にAIを統合し、店舗側の応答効率と顧客満足度を向上させる。店舗側にはAI返信候補の提示と自動返信機能を、顧客側にはAI対応のUI表示を提供する。

#### 3.4.2 ユーザーストーリー

- 店舗オーナー/スタッフとして、顧客からのよくある質問にはAIが返信候補を自動提示してほしい。候補を選んで少し編集するだけで素早く返信できる
- 店舗オーナーとして、営業時間外の顧客メッセージにはAIが自動返信してほしい
- 顧客として、AIが返信した場合はそれとわかるように表示してほしい

#### 3.4.3 機能詳細（jpshop 店舗側）

**AI返信候補機能**

| 要素 | 説明 |
|-----|------|
| AIボタン | チャット入力エリア横に「AI」アイコンボタンを配置 |
| 候補表示 | 会話履歴を元にAIが3案を提示、ボトムシートで表示 |
| 候補選択 | タップで入力エリアにコピー → 編集 → 送信 |
| ローディング | AI生成中はスピナー表示（最大5秒） |

**AI自動返信機能**

| 要素 | 説明 |
|-----|------|
| 自動返信ON/OFF | チャット設定画面（`message/setting/setting.vue`）にトグル追加 |
| 時間帯設定 | 「常時」「営業時間外のみ」「カスタム時間帯」 |
| 待機時間設定 | スタッフが未読の場合、X分後に自動返信（5/10/30分） |
| AI学習データ | 店舗情報・メニュー・営業時間・FAQ（設定から入力） |

**AI FAQ管理**（新規設定画面）

| 要素 | 説明 |
|-----|------|
| よくある質問登録 | 質問例と回答を自由に追加（最大50件） |
| 自動取得 | 店舗情報・メニューからAIが自動でFAQ候補を生成 |
| 有効/無効 | 各FAQのON/OFF切り替え |

#### 3.4.4 機能詳細（jpcard 会員側）

**AIメッセージ表示**

- 既存の `messageType` を拡張（type=4 = AIメッセージ）
- AIが返信したメッセージに「AI」バッジを表示
- バッジデザイン：グレー背景に「AI」テキスト

**AIクイックリプライ**

- チャット画面に「AIに質問する」ショートカットボタンを常時表示
- タップで「予約したい」「メニューを知りたい」「営業時間は？」等のクイックリプライ選択肢を表示
- 選択するとそのままチャットに送信

#### 3.4.5 UI要件

**jpshop チャット画面改修** (`pages/message/chat/chat.vue`)

```
[テキスト入力エリア] [AI] [画像] [送信]
                     ↑
                  AIアイコンボタン（新規追加）
```

AIボトムシート表示:
```
┌──────────────────────────────┐
│ AI返信候補                    │
├──────────────────────────────┤
│ ① ご来店ありがとうございます…  │ ← タップで選択
│ ② お問い合わせいただき…       │
│ ③ 承知いたしました…           │
└──────────────────────────────┘
```

**jpshop チャット設定画面改修** (`pages/message/setting/setting.vue`)

```
AI自動返信             [ON ●]
 └ 対応時間帯          [営業時間外のみ ▼]
 └ 自動返信まで        [10分後 ▼]

AI FAQ管理             [設定する >]
```

**jpcard チャット画面改修** (`pagesA/message/chat.vue`)

```
[AIに質問する ✦]                ← 常時表示ボタン

── AI返信メッセージ例 ──
[AI] ご予約は毎日9:00〜19:00...
```

#### 3.4.6 必要なAPI（後端追加）

| メソッド | パス | 説明 |
|---------|-----|------|
| POST | `/api/shop/ai/suggest_reply` | AI返信候補生成（会話履歴を送信） |
| GET/PUT | `/api/shop/ai/settings` | AI自動返信設定の取得/保存 |
| GET | `/api/shop/ai/faq` | FAQ一覧取得 |
| POST | `/api/shop/ai/faq` | FAQ追加 |
| PUT | `/api/shop/ai/faq/{id}` | FAQ編集 |
| DELETE | `/api/shop/ai/faq/{id}` | FAQ削除 |
| POST | `/api/member/ai/quick_reply` | クイックリプライ選択肢取得 |

#### 3.4.7 フロントエンド追加API

**jpshop/api/index.js**

```js
export const getAISuggestReply = (data) => request({ url: '/api/shop/ai/suggest_reply', method: 'post', data })
export const getAISettings = () => request({ url: '/api/shop/ai/settings', method: 'get' })
export const updateAISettings = (data) => request({ url: '/api/shop/ai/settings', method: 'put', data })
export const getAIFaqList = () => request({ url: '/api/shop/ai/faq', method: 'get' })
export const addAIFaq = (data) => request({ url: '/api/shop/ai/faq', method: 'post', data })
export const editAIFaq = (id, data) => request({ url: `/api/shop/ai/faq/${id}`, method: 'put', data })
export const deleteAIFaq = (id) => request({ url: `/api/shop/ai/faq/${id}`, method: 'delete' })
```

**jpcard/api/index.js**

```js
export const getAIQuickReplies = (sid) => request({ url: `/api/member/ai/quick_reply?sid=${sid}`, method: 'get' })
```

#### 3.4.8 AIメッセージWebSocket通知フォーマット

```json
{
  "type": "message",
  "data": {
    "message_type": 4,
    "content": "ご質問ありがとうございます。営業時間は...",
    "is_ai": true,
    "created_at": "2026-04-24T10:00:00Z"
  }
}
```

---

## 4. 新規ページ一覧

| アプリ | ページパス | 機能 | 実装方法 |
|-------|---------|------|---------|
| jpshop | `pages/settings/gbpSetting/gbpSetting.vue` | GBP連携設定 | 新規 |
| jpshop | `pages/message/gbpReviews/gbpReviews.vue` | GBPクチコミ管理 | 新規 |
| jpshop | `pages/settings/socialMedia/socialMedia.vue` | SNS統合管理・投稿 | 新規 |
| jpshop | `pages/message/aiFaqSetting/aiFaqSetting.vue` | AI FAQ管理 | 新規 |

---

## 5. 非機能要件

| 項目 | 要件 |
|-----|------|
| GBP API制限 | Google My Business API のレートリミット（1分あたり100リクエスト）を考慮し、バックエンドでキャッシュする |
| AI応答速度 | AI返信候補生成は5秒以内。タイムアウト時は「もう一度試す」を表示 |
| セキュリティ | GBP OAuth トークンはサーバーサイドで暗号化保存。フロントに露出しない |
| オフライン | GBP/AI機能はネットワーク必須。オフライン時は適切なエラーメッセージを表示 |
| 多言語 | UI文言はすべて日本語。AI返信は日本語で生成 |
| プライバシー | チャット内容をAI学習に使用する場合は、ユーザーへの明示的な同意を取得 |

---

## 6. 優先度・フェーズ計画

### フェーズ1（最優先）
- **機能4**: チャット AI返信候補（jpshop）
- **機能4**: AIメッセージバッジ表示（jpcard）
- 理由: 既存チャットへの追加のため影響範囲が小さく、効果が即時

### フェーズ2
- **機能1**: GBP OAuth連携・基本情報同期
- **機能2**: GBPクチコミ一覧・返信
- 理由: GBP OAuth実装後にクチコミ機能を追加できる

### フェーズ3
- **機能3**: Instagram × GBP 投稿連動
- **機能4**: AI自動返信・FAQ管理
- 理由: 依存機能（GBP OAuth・AI基礎）の完成後に実装

---

## 7. バックエンド連携要件まとめ

後端（Laravel 8）で追加が必要な主要コンポーネント：

| コンポーネント | 説明 |
|-------------|------|
| Google OAuth 2.0 クライアント | GBP連携用（`business.manage`スコープ） |
| Google My Business API クライアント | 店舗情報・クチコミ・投稿の読み書き |
| AIサービス連携 | LLM API（例: OpenAI GPT / Claude API）との統合 |
| クチコミWebhook | GBPの新着クチコミをWebSocketで配信する仕組み |
| トークン暗号化保存 | GBP・Instagram・AI APIキーの安全な管理 |
