# Card-San 部署指南

**最后更新**: 2026-04-24

---

## 前置条件

| 工具 | 版本 | 用途 |
|-----|------|------|
| HBuilderX | 最新版（推荐 3.x） | uni-app 构建与真机调试 |
| Node.js | 16.x 以上 | admin 构建 |
| npm / yarn | — | 依赖包管理 |
| Git | — | 源码管理 |

---

## 克隆仓库

```bash
# 前端
git clone https://github.com/dumatoma/card-san.git

# 后端 API（另一仓库）
# api.card-san.jp（Laravel 8）单独管理
```

---

## 1. jpcard — 会员端 App（Card-San）

### 本地开发（HBuilderX）

1. 用 HBuilderX 打开文件夹：`card-san/jpcard/`
2. 工具栏 → 运行 → 浏览器（H5 预览）或真机/模拟器

### 安装依赖

```bash
cd jpcard
npm install
```

### 修改 API 基础地址

编辑 `jpcard/utils/request.js` 中的 `baseURL`：

```js
// 本地开发
baseURL: 'http://localhost:8000'

// 生产环境
baseURL: 'https://api.card-san.jp'
```

### App 打包（HBuilderX）

1. 菜单 → 发行 → 原生 App-云打包
2. iOS：需要 Apple Developer 证书与描述文件
3. Android：需要 keystore 签名文件

### H5 部署

1. 菜单 → 发行 → 网站-H5 手动发行
2. 将 `unpackage/dist/build/h5/` 的内容上传到 Web 服务器
3. nginx 配置示例：

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

## 2. jpshop — 门店管理端 App（CardSan管理）

### 本地开发

1. 用 HBuilderX 打开文件夹：`card-san/jpshop/`
2. 检查并修改 `jpshop/utils/request.js` 中的 `baseURL`

### 安装依赖

```bash
cd jpshop
npm install
```

### 打包步骤

与 jpcard 相同：HBuilderX → 发行 → 原生 App-云打包

### 注意事项

- Google Pay 配置：检查 `manifest.json` 中 `payment.google` SDK 设置
- Apple IAP 配置：检查 `manifest.json` 中 `payment.appleiap` 设置
- 提交 App Store / Google Play 审核时必须使用生产 API

---

## 3. jpRegister — 新商户注册（仅 H5）

### 本地开发

1. 用 HBuilderX 打开文件夹：`card-san/jpRegister/`
2. 仅发布 H5（不做 App 分发）

### H5 部署

1. HBuilderX → 发行 → 网站-H5 手动发行
2. 将 `unpackage/dist/build/h5/` 部署到服务器
3. 推荐部署域名：`https://register.card-san.jp/`

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

## 4. outsiteAppointment — 外链轻量预约（仅 H5）

### 本地开发

1. 用 HBuilderX 打开文件夹：`card-san/outsiteAppointment/`

### H5 部署

该应用作为店铺外部预约链接使用，URL 中携带 `shop_id` 等参数。

```bash
# 构建输出：unpackage/dist/build/h5/
# 推荐部署域名：https://book.card-san.jp/
```

---

## 5. admin/admin — Web 系统管理后台

### 本地开发

```bash
cd admin/admin
npm install
npm run dev
# 启动后访问 → http://localhost:8080
```

### 生产构建

```bash
cd admin/admin
npm run build
# 输出至 dist/ 文件夹
```

### 生产部署（nginx 示例）

```bash
# 将 dist/ 内容上传到服务器
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

### 修改 API 基础地址

检查 `admin/admin/src/http/api.js`：

```js
// 确认已指向生产地址
const BASE_URL = 'https://api.card-san.jp'
```

---

## 6. 环境变量与配置汇总

| 配置项 | 文件 | 生产环境值 |
|-------|------|-----------|
| 会员端 API 地址 | `jpcard/utils/request.js` | `https://api.card-san.jp` |
| 门店管理端 API 地址 | `jpshop/utils/request.js` | `https://api.card-san.jp` |
| 注册端 API 地址 | `jpRegister/` 内 request 配置 | `https://api.card-san.jp` |
| 预约端 API 地址 | `outsiteAppointment/api/index.js` | `https://api.card-san.jp` |
| admin API 地址 | `admin/admin/src/http/api.js` | `https://api.card-san.jp` |
| WebSocket 地址 | `jpcard/store/index.js` | `ws://47.245.1.2:21000` |
| WebSocket 地址 | `jpshop/store/index.js` | `ws://47.245.1.2:21000` |

---

## 7. uni-app AppID 确认

各应用的 AppID 在 DCloud 开发者中心管理：

| 应用 | AppID |
|-----|-------|
| jpcard | `__UNI__1ED8C1A` |
| jpshop | `__UNI__40CEDE2` |
| jpRegister | `__UNI__ED993F2` |
| outsiteAppointment | `__UNI__26F5B98` |

---

## 8. CI/CD 推荐配置（参考）

```
GitHub Actions / GitLab CI
├── 推送到 main 分支
│   ├── admin → npm build → 上传到服务器
│   ├── jpRegister → HBuilderX CLI 构建 H5 → 上传
│   └── outsiteAppointment → HBuilderX CLI 构建 H5 → 上传
└── 发布 tag
    ├── jpcard → HBuilderX 云打包 → App Store / Google Play
    └── jpshop → HBuilderX 云打包 → App Store / Google Play
```

---

## 9. 常见问题

### uni-app 构建报错

```bash
# 重新安装 node_modules
rm -rf node_modules package-lock.json
npm install
```

### API 通信报错（CORS 跨域）

- 检查后端（Laravel）的 CORS 配置
- 在 `config/cors.php` 的 `allowed_origins` 中添加前端域名

### WebSocket 连接失败

- 检查 `ws://47.245.1.2:21000` 的 21000 端口是否开放
- 生产环境建议改为 `wss://`（TLS 加密）

### H5 路由刷新 404

- 检查 nginx 是否配置了 `try_files $uri $uri/ /index.html;`
- uni-app `manifest.json` → `h5.router.mode` 设为 `history` 时此配置为必须项
