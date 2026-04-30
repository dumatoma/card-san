# Card-San 前端

面向日本本地生活服务门店（美容、美甲、健身、餐饮等）的  
一体化数字会员管理平台。

> **后端 API**：Laravel 8（另一仓库）— 生产域名 `https://api.card-san.jp`

---

## 应用构成

本仓库包含 6 个独立的前端应用。

| 目录 | 应用名称 | 类型 | 目标用户 | 说明 |
|-----|---------|------|---------|------|
| `jpcard/` | Card-San | uni-app | 消费者（C端） | 电子会员卡、优惠券、预约、聊天 |
| `jpshop/` | CardSan管理 | uni-app | 店主/员工（B端） | CRM、预约、通知、会员卡、支付 |
| `jpRegister/` | 登録する | uni-app（H5） | 新入驻商户 | 3步邮箱注册流程 |
| `outsiteAppointment/` | ご予約 | uni-app（H5） | 到店前的顾客 | 从店铺外链进入的轻量预约 |
| `admin/admin/` | jadmin | Vue + Element UI | 系统管理员 | 全平台店铺、销售、通知管理 |
| `jppc/jp-card-pc/` | — | — | — | PC 浏览器版（未实现） |

---

## 技术栈

- **uni-app 应用**：Vue 2 + Vuex + uView UI 2.0 + axios
- **管理后台**：Vue 2 + Element UI 2.15 + Webpack 3
- **实时通信**：WebSocket
- **后端**：Laravel 8 / PHP（`https://api.card-san.jp`）
- **支付**：Google Pay / Apple IAP
- **社交登录**：Google / LINE / Facebook / Apple OAuth

---

## 快速开始

### uni-app 应用（jpcard / jpshop / jpRegister / outsiteAppointment）

需要安装 [HBuilderX](https://www.dcloud.io/hbuilderx.html)。

```bash
git clone https://github.com/dumatoma/card-san.git
# 用 HBuilderX 打开对应子目录
```

各应用 API 地址配置文件：
- `jpcard/utils/request.js`
- `jpshop/utils/request.js`

```js
baseURL: 'https://api.card-san.jp'  // 生产环境
```

### 管理后台（admin）

```bash
cd admin/admin
npm install
npm run dev      # 启动开发服务器 → http://localhost:8080
npm run build    # 生产构建 → dist/
```

---

## 文档

| 文档 | 内容 |
|-----|------|
| [docs/PRD_zh.md](docs/PRD_zh.md) | 产品需求文档（全功能与规格说明） |
| [docs/DEPLOYMENT_zh.md](docs/DEPLOYMENT_zh.md) | 部署指南（构建、环境配置、nginx） |
| [docs/PRD.md](docs/PRD.md) | Product Requirements Document（日本語版） |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | デプロイメントガイド（日本語版） |

---

## API 路由对应

| 路径前缀 | 用途 |
|---------|------|
| `/api/member/*` | 会员端（jpcard、outsiteAppointment） |
| `/api/shop/*` | 门店管理端（jpshop、jpRegister） |
| `/adminapi/*` | 系统管理后台（admin） |
| `/api/config` | 公共配置 |

---

## uni-app AppID

| 应用 | AppID |
|-----|-------|
| jpcard | `__UNI__1ED8C1A` |
| jpshop | `__UNI__40CEDE2` |
| jpRegister | `__UNI__ED993F2` |
| outsiteAppointment | `__UNI__26F5B98` |
