# cardsan-file-picker(カスタムエイド 2026-10-24)

スマホに保存してある PDF を1つ選ぶ。カルテの「PDFのカルテを読み込む」で使う。

```js
// #ifdef APP-PLUS
import { pickPdf } from '@/uni_modules/cardsan-file-picker'
// #endif
pickPdf({
  success: (r) => { /* r.path(一時フォルダにコピーした場所)・r.name・r.size */ },
  fail: (e) => { /* e.cancel が true なら選ばずに閉じた */ }
})
```

- iPhone:ファイルアプリの選択画面(UIDocumentPickerViewController、PDF のみ)。`CardsanFilePicker.swift`
- Android:標準のファイル選択画面(ACTION_OPEN_DOCUMENT、application/pdf)。`CardsanPdfCopy.kt` で一時フォルダにコピー
