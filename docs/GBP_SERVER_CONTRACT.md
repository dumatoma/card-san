# Googleビジネス連携（GBP）サーバー側 実装・修正 接口清单

对象服务器：`api.card-san.jp`（Laravel，`app/Http/Controllers/Api/Shop/GoogleBusinessController.php`、`app/Services/GbpService.php`、`app/Libs/GoogleBusiness.php`）
调用方：管理WEB `jppc/jp-card-pc/src/http/api.js`（gbp*）／管理App `jpshop/api/gbp.js`、`jpshop/api/gbpContent.js`
依据：GBP連携_2026.10.11（OAuth 回调、选店）、GBP関連の修正_2026.10.16（照片分类同步、评价附图、logo/cover 只读）

共通约定
- 返回格式沿用现有 `{ code, message, data }`；`code==200` 成功，`403` = 副管理者无权限（15=クチコミ管理，16=Googleビジネス連携）或プラン不支持，`400` = 业务错误（App 会直接 toast `message`）。
- 前缀统一 `/api/shop/google_business/`，下文省略。
- 两端 URL / 方法 / 参数名已核对一致（见文末“两端一致性”）。
- 布尔类字段一律返回整数 `0/1`（App 会 Number() 兜底，WEB 也已兜底，但请按整数返回）。

---

## 1. GET `get_auth_url` — 取得 Google 认证 URL

| 入参 | 说明 |
|---|---|
| `client`（query，可选） | `app` = 管理App；省略 = 管理WEB |

出参：`data.url`（Google OAuth URL）

行为
- `client=app` 时 `redirect_uri = https://api.card-san.jp/api/shop/google_business/callback/mobile`；否则 `.../callback/pc`。
- **必须把 client 种类和 shop 写进 `state`（或服务端 session/cache 以 state 为 key 记录）**，因为后续 `create_access_token` 换 token 时 `redirect_uri` 必须与这里完全一致（App 侧拿到 code 后调用的也是同一个 `create_access_token`，不带 client 参数）。
- scope：`https://www.googleapis.com/auth/business.manage openid email`，`access_type=offline`、`prompt=consent`（确保拿到 refresh_token）。

## 2. GET `callback/pc`、GET `callback/mobile` — Google 回调页（`auth_redirect.blade.php`）

- **PC**：保持现状，302/JS 跳回管理WEB `https://card-san.jp/#/googleBusiness?gbp_code=<code>&state=<state>`；用户拒绝时 `?gbp_error=<error>`（如 `access_denied`）。
- **mobile【10.11 §5 不具合，必须修】**：删除对 `uni-webview-js` / `UniAppJSBridgeReady` 的依赖（外部浏览器里不会触发）。改为：
  - 成功：`location.href = 'cardsan://gbp?code=' + encodeURIComponent(code) + '&state=' + encodeURIComponent(state)`
  - 失败：`location.href = 'cardsan://gbp?error=' + encodeURIComponent(error)`
  - 页面上同时放一个普通 `<a href="cardsan://gbp?...">アプリに戻る</a>` 按钮（自动跳转被浏览器拦截时手动点）。按钮不得依赖任何 bridge 事件。
  - App 侧已实现：URL scheme `cardsan`（Android `schemes`、iOS `urlschemes`），`App.vue handleGbpScheme` 解析 `code/state/error` 并交给 `gbpSetting` 页。参数名必须是 `code`、`state`、`error`（不是 gbp_code）。
- 回调页本身不做 token 交换（交换在 `create_access_token`），避免 code 被用两次。

## 3. POST `create_access_token` — 用认证码换 token（**改：不再自动采用第一家店**）

| 入参 | 说明 |
|---|---|
| `code` | Google 认证码 |
| `state` | 回调带回的 state（CSRF 校验 + 识别 pc/mobile 以确定 redirect_uri） |

行为【10.11 §6.4】
1. 校验 state → 用对应 redirect_uri 换 token。
2. Account Management API 取全部 accounts → 对**每个** account 调 Business Information API `GET v1/{account}/locations?readMask=name,title,storefrontAddress`（注意分页 `nextPageToken`），合并成一个候选列表。
3. 0 个 account → `code=400`，message「Google ビジネス プロフィールが見つかりません。ビジネス プロフィールを管理しているGoogleアカウントでログインしてください。」
4. 有 account 但 0 家店 → 可以返回 `code=400` 同文案，**或**返回 `need_select=1` + `candidates=[]`（两端都已处理空数组：显示「このGoogleアカウントにビジネス（店舗）が登録されていません…」）。
5. **≥1 家店：一律返回 `need_select=1`，不得自动连携**（1 家也要让用户在选店画面确认，客户规格：1件でも画面を省略せず選択済みで表示）。
6. token 暂存在服务端（cache，key=select_token，TTL 建议 15 分钟），**此时不要写入 web_shop 的连携状态**（用户关闭选店弹窗时不应变成已连携）。

出参（need_select 时）
```json
{
  "need_select": 1,
  "select_token": "一次性随机串",
  "email": "连携的 Google 账号邮箱",
  "candidates": [
    { "name": "accounts/123/locations/456", "title": "店舗名", "address": "〒100-0001 東京都千代田区…（storefrontAddress 拼接好的字符串）" }
  ]
}
```
- `candidates[].name`：**请用 v4 全路径 `accounts/{a}/locations/{l}`**（两端原样回传给 select_location；现有同步/评价/投稿 API 需要 v4 形式）。两端用 `name` 作唯一 key，必须唯一。
- `title` 缺失时前端会显示 `name`；`address` 为可选字符串（不要返回对象）。
- 兼容：若仍有直接连携成功的分支，返回 overview 同结构（`connected=1` 等），两端会提示「連携しました」。

## 4. POST `select_location` — 确定连携的店铺（新增）

| 入参 | 说明 |
|---|---|
| `select_token` | create_access_token 返回的一次性 token |
| `location_name` | 所选 `candidates[].name` |

行为
- 校验 token 有效且属于当前 shop；`location_name` 必须在该 token 的候选列表中（防篡改）。
- 写入 web_shop：token/refresh_token、location（v4）、gbp_email、gbp_location_title 等；之后与旧逻辑相同（初回评价取込 = 既读 等）。
- **token 一次性**：成功后立即作废。重复提交返回 400「有効期限が切れました。もう一度連携してください」。
- 过期 → 400 同上。
- 出参：overview 同结构（至少 `connected=1, email, location_title`）。

## 5. GET `overview` — 连携状态 + 功能设定

出参字段（两端都在用）
| 字段 | 类型 | 说明 |
|---|---|---|
| `configured` | 0/1 | .env 已设 GBP_CLIENT_ID/SECRET |
| `connected` | 0/1 | |
| `email` | string | 连携 Google 账号 |
| `location_title` | string | 连携店铺名 |
| `basic_on` | 0/1 | 基本情報 |
| `review_on` | 0/1 | クチコミ管理 |
| `ins_on` | 0/1 | Instagram×GBP 投稿連動 |
| `ins_auto` | 0/1 | Instagram 投稿时自动转载 |
| `auto_sync` | 0/1 | 基本情報 自動同期 |
| `sync_fields` | string[] | `hours` / `address` / `category` / `photos` 的子集，**未设置时返回 `[]` 不要 null** |
| `last_sync_at` | string | `Y-m-d H:i:s`（或 ISO8601）；无则 `""` |
| `instagram` | object | `{ connected:0/1, username, avatar }`，**未连携也要返回对象** |

## 6. PUT `features` — 功能开关保存

入参：以下任意子集 `basic_on, review_on, ins_on, ins_auto, auto_sync, sync_fields(string[])`
出参：保存后的 overview 同结构（两端用返回值覆盖本地）。
- `ins_auto=1` 需要 `ins_on=1` 且 Instagram/GBP 都已连携，否则 400。

## 7. DELETE `disconnect` — 解除连携

入参（body）：`{ full: 1 }`（两端都用 DELETE + body。若 Nginx/框架丢弃 DELETE body，请把 `full` 视为默认 1，或同时接受 `POST` + `_method=delete`）
行为：revoke token，清空连携字段及各开关（overview 返回 `connected=0`、各开关 0）。

## 8. POST `sync_now` — 立即同步（Card-San → GBP）

入参：无。`auto_sync=1` 时两端按钮都禁用（客户规格），服务器端也可直接返回 400。
出参：`{ detail: { hours:"ok"|说明, address:..., category:..., photos:"ok"|"n枚追加"|说明 }, last_sync_at }`；`message` 前端不再展示（统一显示「同期しました」），`detail` 中非 ok 且非“n枚追加”的会作为警告显示。

### 8.1 照片同步规则【10.16 必须修改】
1. **上传方向（Card-San → GBP）**：只上传店铺自己的 TOP 写真（`web_shop.carousel` 中的 URL 字符串），上传到 GBP 时 `category=ADDITIONAL`。**绝不上传/覆盖 PROFILE(logo)、COVER**。
2. **读取方向（GBP → Card-San）**：
   - 取 `accounts/{a}/locations/{l}/media`（店主媒体），**只把 `locationAssociation.category` 为 `PROFILE`、`COVER` 的各 1 张作为只读 logo/cover 回显**。
   - `ADDITIONAL` 等其它分类：只用于“是否已上传过”的去重判定，**不得**写回 carousel。
   - **不得调用 `media.customers`**（顾客单独投稿的照片），不取、不存、不显示。
3. `web_gbp_photo_log` 增加列：`category`（ADDITIONAL/PROFILE/COVER…）、`source`（`shop`=本系统上传 / `gbp_owner`=GBP 管理画面店主设定），去重与同步对象限定为 `category=ADDITIONAL AND source=shop`。
4. Card-San 删除了某张自传照片时，若要删除 GBP 侧对应 media，只能删除 `source=shop AND category=ADDITIONAL` 的记录；**PROFILE/COVER 永远不删**。

## 9. GET `/api/shop/shop`（店铺信息，非 google_business 前缀）— carousel 中的 logo/cover

两端（App `shopInfo.vue`、WEB `storeInfo.vue`）均已支持如下格式：
```json
"carousel": [
  "https://…/own1.jpg",
  "https://…/own2.jpg",
  { "url": "https://lh3.googleusercontent.com/…", "gbp_category": "PROFILE", "readonly": 1 },
  { "url": "https://lh3.googleusercontent.com/…", "gbp_category": "COVER",   "readonly": 1 }
]
```
- 店铺自传照片仍为 **URL 字符串**；GBP 回显的 logo/cover 为对象 `{url, gbp_category: "PROFILE"|"COVER", readonly: 1}`。
- **建议把 logo/cover 追加在数组末尾**（WEB 把第 1 张标为“メイン”，App/顾客端第 1 张是 TOP 首图）。
- 仅在 `basic_on=1`、`sync_fields` 含 `photos` 且已连携时附加；解除连携后不再附加。
- 两端 UI：logo/cover 显示「ロゴ／カバー・Googleから取得」标签，**无删除按钮**。

### 9.1 保存 POST `/api/shop/shop/info`（type=info）时的规则
- 两端发送的 `carousel` **已过滤掉 logo/cover 对象，只发 URL 字符串数组**。
- 服务器仍须防御：
  - carousel 中若混入对象（旧版本客户端），**丢弃对象元素**，不要 json_encode 进 `web_shop.carousel`。
  - 若某 URL 等于当前 GBP logo/cover 的 URL，也丢弃（防止被当成自传照片保存 → 下一次同步又以 ADDITIONAL 上传到 GBP，造成**重复**）。
  - **保存时不得因为 carousel 中不含 logo/cover 而删除 GBP 侧的 logo/cover**（它们本来就不在 web_shop.carousel 里）。
- **顾客端接口（jpcard 顾客App / 予約WEB 读取店铺信息的接口）**：这些页面把 carousel 当作字符串数组使用。若要在顾客端也显示 logo/cover，必须以 **URL 字符串** 返回；对象格式只能出现在管理端 `GET /api/shop/shop`。

## 10. GET `review_list` — 评价列表

| 入参（query） | 说明 |
|---|---|
| `filter` | `all` / `unreplied`（reply 为空） |
| `offset` | 起始偏移（0 起） |
| `limit` | 每页件数（两端固定 10） |

出参
```json
{
  "connected": 1, "review_on": 1, "warning": "",
  "stats": { "avg_rating": 4.3, "total": 25, "unreplied": 3, "unread": 2 },
  "count": 25,
  "last_sync_at": "2026-10-22 10:15:00",
  "list": [
    { "id": "…", "reviewer_name": "…", "reviewer_photo": "…", "star": 5, "comment": "…",
      "date": "2026/10/20", "reply": "…|null", "reply_date": "2026/10/21|''", "is_read": 0,
      "photos": ["https://…", "https://…"] }
  ]
}
```
- `count` = 当前 filter 下的总件数（用于“もっと見る”判断）；`stats` 始终是全体统计。
- **未连携 / review_on=0 时也请返回 `stats`（全 0）和 `list: []`**（两端已兜底，但按此返回）。
- `last_sync_at`：最近一次从 Google 拉取评价（`gbp:sync-reviews`，15 分钟）的时间，画面显示「最終更新」。可用 `web_shop.gbp_reviews_at`。
- `photos`【10.16 新增】：来自 Google review 资源的 `reviewMediaItems`，与评价本体一起取得并存入 `web_gbp_review`（新增 `photos` JSON 列）。返回 **URL 字符串数组**（推荐 `googleUrl`，没有时用 `thumbnailUrl`）。两端也兼容 `{url|googleUrl|thumbnail_url|thumbnailUrl}` 对象，但请统一字符串。无图返回 `[]`。
- `is_read`：两端改为“点击该条/点「返信する」时才 `review_read`”，**列表读取本身不得自动置已读**。

## 11. POST `review_read` — 已读

入参：`{ ids: [id, …] }`；出参无要求。

## 12. GET `review_unread` — 顶栏徽标

出参：`{ unread: <未读数>, unreplied: <未返信数(reply 为空)> }`
- **新增 `unreplied`**：管理WEB 顶栏「クチコミ」徽标显示未返信件数（客户规格），没有该字段时临时用 unread 代替。
- 无权限/未连携/OFF 时返回 `{unread:0, unreplied:0}` 或 403 均可。

## 13. POST `review_reply/{id}` — 返信 / 返信编辑（同一接口）

入参：`{ comment }`（≤4096 字）
出参：`{ reply, reply_date }`（请务必返回；两端用其更新画面）

## 14. GET `ins_posts` — Instagram 投稿列表（带转载状态）

| 入参（query） | 说明 |
|---|---|
| `after` | 下一页游标（首次为空串） |

出参
```json
{ "items": [ { "id": "…", "image": "https://…", "images": ["…"], "caption": "…", "date": "2026/10/20", "status": "synced|video|pending" } ],
  "next": "游标|''", "total": 37, "synced_total": 12 }
```
- `status` 取值固定：`synced`（已转载到 GBP）、`video`（视频/Reels，不可转载）、其它任意值（如 `pending`）= 未转载。
- `next` 无下一页时返回 `""`；`items` 无数据时 `[]`（不要 null）。
- `total`：Instagram 投稿总数（取不到可返回已知件数）。
- **新增 `synced_total`**：该店已转载到 GBP 的总件数（来自转载记录表计数）。WEB「GBP投稿」タブ显示「n / synced_total 件を表示中」并据此判断是否还有更多；不返回时前端只能用已加载件数代替（总数会偏小）。
- Instagram token 失效时 `code!=200`，`message` 中含「再連携」字样（WEB 据此显示「[要再連携]」）。

## 15. POST `ins_posts/{id}/repost` — 单条转载到 GBP

入参：无（body `{}`）；服务器负责防重复（已转载则返回 200 或 400 明确提示）。视频 → 400。
出参：任意；成功后两端把该条 status 置为 `synced`。

## 16. GET `posts`（旧，GBP localPosts 一览）

管理WEB 已不再调用（GBP投稿タブ改为显示 `ins_posts` 中 `status=synced` 的条目，客户规格 Insta投稿連動_Web版UIUX仕様 4.3）。服务器可保留，无需修改。

---

## 两端一致性核对结果（jppc ⇔ jpshop）

| 接口 | URL/方法 | 参数 | 返回字段使用 | 结论 |
|---|---|---|---|---|
| get_auth_url | GET | App 加 `?client=app` | `url` | 一致（差异为设计如此） |
| create_access_token | POST | `code,state` | `need_select,candidates[].name/title/address,select_token` | 一致 |
| select_location | POST | `select_token,location_name` | — | 一致 |
| disconnect | DELETE + `{full:1}` | | | 一致 |
| features | PUT | `basic_on/review_on/ins_on/ins_auto/auto_sync/sync_fields` | overview 结构 | 一致 |
| sync_now | POST `{}` | | `detail,last_sync_at` | 一致 |
| review_list | GET | `filter(all/unreplied),offset,limit=10` | `stats,count,list,photos,last_sync_at,warning` | 一致 |
| review_read | POST | `{ids}` | | 一致 |
| review_reply/{id} | POST | `{comment}` | `reply,reply_date` | 一致 |
| review_unread | GET | | WEB 用 `unreplied`(fallback unread)；**App 未调用**（无徽标） | 见报告 |
| ins_posts | GET | `after` | `items[].status(synced/video),next,total`；WEB 另用 `synced_total` | 一致 |
| ins_posts/{id}/repost | POST `{}` | | | 一致 |
| /api/shop/shop carousel | GET/POST info | 对象 `{url,gbp_category,readonly}` | 两端均过滤后只发 URL | 一致（WEB 本轮补上） |
