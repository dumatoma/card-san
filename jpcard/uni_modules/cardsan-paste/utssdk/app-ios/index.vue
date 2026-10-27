<template>
    <view></view>
</template>

<script lang="uts">
    // [カスタムエイド 2026-10-26] 店舗QR統一:iPhone 標準の「ペースト」ボタン(nvue の画面で <cardsan-paste @paste="..."> として使う)
    //   中身は同じフォルダの CsPasteView.swift。押すと @paste に { text: コピーされていた文字 } を渡す
    export default {
        name: "cardsan-paste",
        emits: ["paste"],
        NVLoad(): CsPasteView {
            const v = new CsPasteView()
            v.onPaste = (text: string): void => {
                this.$emit("paste", CsPasteView.detail(text))
            }
            return v
        }
    }
</script>
