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
| 4 | スタッフルームチャット | jpshop |

---

## 2. 既存機能との連携ポイント

新機能は以下の既存実装を最大限に流用する。

| 既存資産 | 流用箇所 |
|---------|---------|
| Instagram OAuthフロー（`getInsUrl` → `bindIns`） | GBP OAuthも同パターンで実装 |
| WebView容器（`webins.vue`） | GBP投稿プレビュー・Instagram投稿表示 |
| WebSocket通知（`wss://wss.card-san.jp/wss`） | 新着クチコミのリアルタイム通知 |
| チャット `messageType`（1=テキスト/2=画像/3=リンク） | スタッフルームでも同じtype体系を流用 |
| `pages/message/chat/chat.vue` | グループチャット・1対1チャット画面のベースとして流用 |
| `pages/message/message.vue` | 上部にスタッフルームセクションを追加 |
| `pages/message/userInfo/userInfo.vue` | 参加ON/OFF・1対1許可ON/OFF設定を追加 |
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

### 機能4: スタッフルームチャット

#### 3.4.1 概要

CardSan管理App（jpshop）の管理者・副管理者間の業務連絡専用グループチャット。LINEの職場利用問題（情報漏洩・ハラスメントリスク・心理的負担）を解決することを目的とする。

#### 3.4.2 ユーザーストーリー

- 店舗オーナーとして、スタッフとの業務連絡をCard-Sanアプリ内で完結させ、プライベートなLINEを使わずに済むようにしたい
- 副管理者として、グループ全体への連絡と、必要に応じた1対1の業務連絡の両方を行いたい
- 店舗オーナーとして、スタッフが1対1トークを許可するかどうかを各自で設定できるようにして、プライバシーを守りたい

#### 3.4.3 確定済み仕様

| 項目 | 仕様 |
|-----|------|
| 1対1トーク送受信 | 送信者・受信者の**両者ともON**の場合のみ送信可能。片方がOFFの場合はボタンをグレーアウト |
| 画像送信 | JPG/PNG・5MB上限・保存期間180日 |
| テキスト保存期間 | 1年（既存個別チャットと同じ） |
| 件数上限 | 無制限（既存個別チャットと同じ） |
| 退職者のチャット履歴 | 副管理者アカウント削除と同時にチャット履歴も削除 |

#### 3.4.4 対象ユーザーと権限

| ロール | 権限 |
|------|------|
| 主管理者 | スタッフルームの作成・ON/OFF・ルーム名・アイコン設定。参加・1対1設定も可 |
| 副管理者 | スタッフルームへの参加ON/OFF・1対1トーク許可ON/OFFのみ設定可 |

#### 3.4.5 UI要件

**UI-1: メッセージ設定画面**（`pages/message/setting/setting.vue` 改修）
- 主管理者のみ「スタッフルーム」メニュー項目を表示

**UI-2: スタッフルーム作成画面**（新規: `pages/message/staffRoom/staffRoom.vue`）
- スタッフルームを設定するON/OFFトグル（デフォルトON）
- アイコン設定（未設定時はデフォルトグループアイコン表示）
- ルーム名入力
- 保存ボタン

**UI-3: メッセージアカウント情報画面**（`pages/message/userInfo/userInfo.vue` 改修）
- 「スタッフルームチャット設定」セクションを追加
- 「スタッフルームに参加する」ON/OFF（デフォルトON）
- 「1対1のトークを許可する」ON/OFF（デフォルトOFF）

**UI-4: メッセージ一覧画面**（`pages/message/message.vue` 改修）
- スタッフルームON時、一覧上部に「スタッフルーム」セクションを表示
- グループルーム行：アイコン・ルーム名・参加人数・最新メッセージ・未読バッジ
- 1対1トーク行：相手アイコン・名前・最新メッセージ

**UI-5: グループチャット画面**（`pages/message/chat/chat.vue` ベースに流用）
- 全参加メンバーのグループチャット
- テキスト・画像（カメラ/ライブラリ）送信
- ヘッダー右の「…」ボタンでメンバー一覧へ遷移

**UI-6: メンバー一覧画面**（新規: `pages/message/staffRoom/members.vue`）
- 「あなた」（自分）セクション
- 「グループメンバー」セクション：参加中の他メンバー一覧
- 各メンバーに1対1チャットボタン（相手がOFFの場合はグレーアウト）

**UI-7: 1対1アテンション確認ダイアログ**
- 「この人に1対1メッセージを送りますか？」確認モーダル
- 「業務連絡のみに使用してください。不要な使用は禁止されています。」の注意文
- 「1対1メッセージを送る」「キャンセル」ボタン

**UI-8: 1対1チャット画面**（`pages/message/chat/chat.vue` ベースに流用）
- 送信後、メッセージ一覧のスタッフルームセクションに相手との履歴が表示される

#### 3.4.6 必要なAPI（後端追加）

| メソッド | パス | 説明 |
|---------|-----|------|
| GET/PUT | `/api/shop/staff_room/settings` | スタッフルーム設定の取得・保存（主管理者） |
| GET | `/api/shop/staff_room/members` | メンバー一覧取得 |
| GET/PUT | `/api/shop/staff_room/my_settings` | 自分の参加・1対1設定の取得・保存 |
| GET | `/api/shop/staff_room/messages` | グループチャット履歴取得 |
| POST | `/api/shop/staff_room/messages` | グループチャットメッセージ送信 |
| GET | `/api/shop/staff_room/dm` | 1対1チャット履歴取得 |
| POST | `/api/shop/staff_room/dm` | 1対1メッセージ送信 |

#### 3.4.7 フロントエンド追加API（`jpshop/api/index.js`）

```js
export const getStaffRoomSettings = () => request({ url: '/api/shop/staff_room/settings', method: 'get' })
export const updateStaffRoomSettings = (data) => request({ url: '/api/shop/staff_room/settings', method: 'put', data })
export const getStaffRoomMembers = () => request({ url: '/api/shop/staff_room/members', method: 'get' })
export const getMyStaffRoomSettings = () => request({ url: '/api/shop/staff_room/my_settings', method: 'get' })
export const updateMyStaffRoomSettings = (data) => request({ url: '/api/shop/staff_room/my_settings', method: 'put', data })
export const getStaffRoomMessages = (params) => request({ url: '/api/shop/staff_room/messages', method: 'get', params })
export const sendStaffRoomMessage = (data) => request({ url: '/api/shop/staff_room/messages', method: 'post', data })
export const getStaffRoomDM = (params) => request({ url: '/api/shop/staff_room/dm', method: 'get', params })
export const sendStaffRoomDM = (data) => request({ url: '/api/shop/staff_room/dm', method: 'post', data })
```

#### 3.4.8 WebSocket通知フォーマット（スタッフルーム新着）

```json
{
  "type": "staff_room_message",
  "data": {
    "room_type": "group",
    "sender_name": "管理者",
    "content": "明日のスケジュールを確認してください",
    "created_at": "2026-05-08T10:00:00Z"
  }
}
```

#### 3.4.9 実装方針（流用 vs 新規）

| 区分 | 内容 |
|-----|------|
| 流用 | WebSocket接続・`chat.vue`チャットUI・画像アップロード・UniPush通知 |
| 改修 | `message.vue`（スタッフルームセクション追加）・`setting.vue`（入口追加）・`userInfo.vue`（設定追加） |
| 新規 | `staffRoom.vue`（ルーム設定）・`members.vue`（メンバー一覧）|

---

## 4. 新規ページ一覧

| アプリ | ページパス | 機能 | 実装方法 |
|-------|---------|------|---------|
| jpshop | `pages/settings/gbpSetting/gbpSetting.vue` | GBP連携設定 | 新規 |
| jpshop | `pages/message/gbpReviews/gbpReviews.vue` | GBPクチコミ管理 | 新規 |
| jpshop | `pages/settings/socialMedia/socialMedia.vue` | SNS統合管理・投稿 | 新規 |
| jpshop | `pages/message/staffRoom/staffRoom.vue` | スタッフルーム設定 | 新規 |
| jpshop | `pages/message/staffRoom/members.vue` | スタッフルームメンバー一覧 | 新規 |

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
- **機能4**: スタッフルームグループチャット（基本機能）
- 理由: 既存WebSocket・チャットUIを流用でき、影響範囲が限定的

### フェーズ2
- **機能4**: 1対1チャット機能
- **機能1**: GBP OAuth連携・基本情報同期
- **機能2**: GBPクチコミ一覧・返信
- 理由: GBP OAuth実装後にクチコミ機能を追加できる

### フェーズ3
- **機能3**: Instagram × GBP 投稿連動
- 理由: 依存機能（GBP OAuth）の完成後に実装

---

## 7. バックエンド連携要件まとめ

後端（Laravel 8）で追加が必要な主要コンポーネント：

| コンポーネント | 説明 |
|-------------|------|
| Google OAuth 2.0 クライアント | GBP連携用（`business.manage`スコープ） |
| Google My Business API クライアント | 店舗情報・クチコミ・投稿の読み書き |
| スタッフルームメッセージ管理 | グループ・1対1メッセージの保存・配信・未読管理 |
| クチコミWebhook | GBPの新着クチコミをWebSocketで配信する仕組み |
| トークン暗号化保存 | GBP・Instagram APIキーの安全な管理 |
