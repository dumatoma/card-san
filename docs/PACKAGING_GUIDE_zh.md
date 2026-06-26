# App 打包发布操作手册

适用项目：**jpcard**（用户端 Card-San）、**jpshop**（管理端 CardSan管理）

---

## 基本信息

| 项目 | App名称 | AppID | 当前版本 |
|------|---------|-------|---------|
| jpcard | Card-San | `__UNI__1ED8C1A` | 1.1.1 (1066) |
| jpshop | CardSan管理 | `__UNI__40CEDE2` | 1.0.30 (1080) |

---

## 打包前准备

### 1. 更新版本号（必做）

每次打包前必须更新版本号，否则 App Store / Google Play 会拒绝上传。

用 HBuilderX 打开对应项目 → 找到 `manifest.json` → 切到「基础配置」选项卡：

| 字段 | 说明 | 示例 |
|------|------|------|
| 应用版本名称（versionName） | 给用户看的版本号，格式 x.x.x | `1.1.2` |
| 应用版本号（versionCode） | 纯数字，每次必须比上次大 | `1067` |

> **规则：** versionCode 每次 +1 即可；versionName 按「修复用+0.0.1 / 新功能+0.1.0 / 重大更新+1.0.0」递增。

---

## 第一步：云打包（HBuilderX）

> HBuilderX 云打包会把代码上传到 DCloud 服务器编译，不需要本地安装 Xcode / Android SDK。

### 1-1. 打开项目

HBuilderX → 文件 → 打开目录 → 选择 `jpcard` 或 `jpshop` 文件夹

### 1-2. 发起云打包

菜单栏 → **发行** → **原生App - 云打包**

弹出「App云端打包」对话框后，按以下说明填写：

---

### Android 打包设置

| 选项 | 选择 |
|------|------|
| Android包 | ✅ 勾选 |
| 打包方式 | 使用自有证书（keystore） |
| 证书别名（alias） | *(向负责人确认)* |
| 私钥密码 | *(向负责人确认)* |
| keystore文件 | *(选择对应的 .keystore 文件)* |
| 使用广告标识符 | 不勾选 |

> **⚠️ 注意：** 同一个 App 的 keystore 文件必须每次都用同一个，换了就无法在 Google Play 更新，只能重新发布。

---

### iOS 打包设置

| 选项 | 选择 |
|------|------|
| iOS包 | ✅ 勾选 |
| 打包方式 | 使用苹果证书 |
| 证书私钥（.p12） | *(选择对应的 .p12 文件)* |
| 私钥密码 | *(向负责人确认)* |
| Profile文件（.mobileprovision） | *(选择 App Store Distribution 证书)* |

> **⚠️ 注意：** 证书类型必须是「App Store Distribution」，不能用「Development」证书提交。

---

### 1-3. 提交打包

点击右下角「**打包**」按钮 → HBuilderX 右下角会显示打包进度。

等待时间：约 **5～15 分钟**（视服务器排队情况）

### 1-4. 下载安装包

打包完成后 HBuilderX 控制台会显示下载链接：

- Android：下载 `.apk` 文件（或 `.aab`）
- iOS：下载 `.ipa` 文件

将文件保存到本地备用。

---

## 第二步：提交 Android → Google Play

### 2-1. 登录后台

打开 [https://play.google.com/console](https://play.google.com/console) → 选择对应应用

### 2-2. 创建新版本

左侧菜单 → **发布** → **正式版（Production）** → 点击「**创建新版本**」

### 2-3. 上传 APK / AAB

- 点击「**上传**」→ 选择下载好的 `.apk` 或 `.aab` 文件
- 等待上传完成，Google 会自动解析版本号

> **推荐使用 `.aab`（Android App Bundle）**，体积更小，Google Play 优先推荐。

### 2-4. 填写版本说明

在「版本说明」处填写本次更新内容（支持多语言，日语填在 `ja-JP` 栏）：

```
例：
・スタッフルーム機能を追加しました
・Google Business Profile連携機能を追加しました
・プッシュ通知の不具合を修正しました
```

### 2-5. 审核与发布

点击「**保存**」→「**审核版本**」→「**开始发布到正式版**」

审核时间：通常 **数小时 ～ 3天**

---

## 第三步：提交 iOS → App Store

### 3-1. 上传 IPA（用 Transporter）

1. Mac 上打开 **Transporter**（App Store 免费下载）
2. 用 Apple ID 登录（需要是 App Store Connect 的开发者账号）
3. 将 `.ipa` 文件拖入 Transporter → 点击「**交付**」
4. 等待上传完成（约 5～20 分钟）

> 也可以用 Xcode → Window → Organizer → Distribute App，但 Transporter 更简单。

### 3-2. 登录 App Store Connect

打开 [https://appstoreconnect.apple.com](https://appstoreconnect.apple.com) → 我的 App → 选择对应应用

### 3-3. 创建新版本

左侧 → **iOS App** → 点击 **「+」→ 新建版本** → 输入版本号（与 manifest.json 一致）

### 3-4. 选择构建版本

在「构建版本」区域 → 点击「**+**」→ 选择刚上传的构建包（需等 Transporter 上传处理完约10分钟）

### 3-5. 填写更新说明

在「此版本的新增内容」填写日语更新说明：

```
例：
・スタッフルーム機能を追加しました
・Google Business Profile連携機能を追加しました
・プッシュ通知の不具合を修正しました
```

### 3-6. 提交审核

右上角点击「**提交以供审核**」→ 回答合规性问题（一般全选「否」或按实际情况填写）

审核时间：通常 **1～3天**

---

## 常见问题

**Q. 打包失败，提示「证书过期」**
→ iOS Distribution 证书有效期1年，需去 Apple Developer 后台重新生成 .p12 和 .mobileprovision，再重新打包。

**Q. Google Play 上传失败，提示「版本号已存在」**
→ versionCode 没有递增。回到 manifest.json 把 versionCode 改大再重新打包。

**Q. App Store 提示「ITMS-90xxx」错误**
→ 查看 Transporter 或 App Store Connect 的具体错误代码，常见原因：
- 90535：缺少必要的权限描述（隐私说明）
- 90726：包含废弃 API

**Q. iOS 审核被拒（Guideline 2.1）**
→ 通常是功能崩溃或缺少测试账号信息。在「App 审核信息」页面填写演示账号（手机号/密码）。

**Q. 打包后 Android 真机安装提示「解析包时出现问题」**
→ 设备 CPU 架构不匹配，或旧版本签名不同。卸载旧版本再安装即可。

---

## 每次发布检查清单

打包前，按以下顺序确认：

- [ ] `manifest.json` versionName 已更新
- [ ] `manifest.json` versionCode 已递增（比上次大）
- [ ] 代码已推送到 git（留存记录）
- [ ] 服务端接口已部署（新功能依赖的 API 已上线）
- [ ] 在真机上用测试账号验证核心功能正常

打包后：

- [ ] Android APK 已下载并在真机安装验证
- [ ] iOS IPA 已上传 Transporter 并收到「交付成功」邮件
- [ ] Google Play 版本说明已填写（日语）
- [ ] App Store 版本说明已填写（日语）

---

## 证书文件存放位置

> 请向负责人确认以下文件的存放位置，妥善备份，切勿遗失。

| 文件 | 用途 | 格式 |
|------|------|------|
| Android keystore | jpcard Android 签名 | `.keystore` |
| Android keystore | jpshop Android 签名 | `.keystore` |
| iOS Distribution Certificate | iOS 打包证书 | `.p12` |
| iOS Provisioning Profile (jpcard) | jpcard iOS 描述文件 | `.mobileprovision` |
| iOS Provisioning Profile (jpshop) | jpshop iOS 描述文件 | `.mobileprovision` |

---

*最后更新：2026年6月*
