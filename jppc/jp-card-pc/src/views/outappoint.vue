<template>
    <div>
        <div class="tit">
            予約機能外部LINK設定
        </div>
        <div id="cons">
            <div class="words">
                Webサイトのボタンやページに以下のLINK URLをコピーして貼り付けてくださ<br />
                い。　アプリと連携した予約ページが作成できます。
            </div>
            <!-- [カスタムエイド 2026-10-13] 予約ページがPC・タブレットに対応したため、「PCサイトには対応していません」の注意書きを削除 -->
            <div class="wtitle">
                予約ページLINK URL
            </div>
            <div class="urlContent" v-text="link"></div>
            <div class="btn" @click="copyurl">
                URLコピー
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "",
        components: {},
        data() {
            return {
                link: ""
            };
        },
        created() {
            let that = this
            let info = localStorage.getItem("admin")
            let infos = JSON.parse(info)
            that.link = "https://order.card-san.jp/reserve/?id=" + infos.sid // [カスタムエイド 2026-10-13] 新しい予約URL(Googleビジネスプロフィールに登録できる形)
        },
        methods: {
            async copyurl() {
                let that = this
                try {
                    await navigator.clipboard.writeText(that.link);
                    that.$message({
                        message: "コピーしました",
                        type: 'success',
                        offset: 400
                    });
                } catch (err) {
                    that.$message({
                        message: "エラーが発生しました",
                        type: 'error',
                        offset: 400
                    });
                }
            }
        },
    };
</script>

<style lang="scss" scoped>
    .tit {
        color: #1d1d1f;
        font-size: 21px;
        font-weight: bold;
    }

    #cons {
        width: 680px;
        background: #FFFFFF;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
        box-sizing: border-box;
        padding: 40px;
        margin-top: 20px;
    }

    .urlContent {
        width: 100%;
        height: 100px;
        background: #FFFFFF;
        border-radius: 10px 10px 10px 10px;
        border: 1px solid #AAAAAA;
        box-sizing: border-box;
        padding: 10px;
        font-size: 16px;
        color: #1D1D1F;
    }

    .words {
        font-family: Hiragino Sans, Hiragino Sans;
        font-weight: normal;
        font-size: 16px;
        color: #1D1D1F;
        line-height: 20px;
        text-align: left;
        font-style: normal;
        text-transform: none;
        margin-bottom: 20px;
    }

    .wtitle {
        margin-top: 40px;
        margin-bottom: 5px;
    }

    .btn {
        width: 120px;
        height: 40px;
        background: rgba(29, 29, 31, 0.2);
        border: 1px solid #1D1D1F;
        font-family: Hiragino Sans, Hiragino Sans;
        font-weight: normal;
        font-size: 16px;
        color: #1D1D1F;
        text-align: center;
        line-height: 40px;
        border-radius: 20px;
        margin: 20px auto 0;
    }

    .btn:hover {
        cursor: pointer;
        background: rgba(29, 29, 31, 0.1) !important;
    }
</style>