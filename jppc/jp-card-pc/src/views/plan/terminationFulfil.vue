<template>
    <div>

        <div v-if="step == 1">
            <div class="title">解約手続き</div>
            <div class="cont no-size">
                <div class="border_bottom">
                    <div class="cont_left m-r-10">本日解約手続きを行うと、<span v-text="date" style="font-weight: bold;"></span>
                        を持ちまして契約を終了いたします。 </div>
                    <div class="cont_left m-r-10">ご解約しても<span style="font-weight: bold;">契約終了日までアカウントは有効</span> です。
                    </div>
                </div>
                <div class="cb border_bottom">
                    <div class="cbtitle">
                        残念ですが、解約されてしまうあなたへ。
                    </div>
                    <div class="cbContent">
                        契約終了後には、今まで作成したデータ、コンテンツは全て失われてしまう可能性がござい<br />ます。いかなる理由があってもデータの復旧は対応致し兼ねますので、ご了承ください。
                    </div>
                    <div style="display: flex;justify-content: center;">
                        <el-checkbox v-model="checked">上記内容を確認した上で、解約を了承しました。</el-checkbox>
                    </div>
                </div>
                <div class="btns">
                    <div class="btn shou u-flex-center" @click="back">戻　る</div>
                    <div class="btn shou u-flex-center redbtn" :style="checked?'opacity:1' : 'opacity:0.6'"
                        @click="offf">解約する</div>
                </div>

            </div>
        </div>
        <div class="" v-else>
            <div class="title">解約手続き完了</div>
            <div class="cont no-size"  style="text-align: center;">
                <div class="cont_tit">解約手続きが完了しました。</div>
                <div class="border_bottom">
                    <div class="cont_left m-r-10" style="padding-top: 30px;">本内容のメールを{{jp.email}}に送信しました。<br />
                        契約終了日の{{date}}まで引き続きご利用ができます。</div>
                    <div class="cont_left m-r-10" style="margin-top: 30px;padding-bottom: 20px;">

                        ご契約の継続は、いつでも「アカウント」の「ご契約内容」から手続きが可能です。</div>
                </div>
                <div class="btn shou u-flex-center" style="margin: 66px auto 0;" @click="back">完了</div>
            </div>
        </div>

    </div>
</template>

<script>
    import {
        offtrad
    } from "@/http/api.js"
    export default {
        data() {
            return {
                step: 1,
                id: '',
                date: "",
                checked: false,
                jp:{}
            };
        },
        created() {
            console.log(this.$route.query)
            this.id = this.$route.query.id
            this.date = this.$route.query.refresh
            let admin = localStorage.getItem("admin")
            let jp = JSON.parse(admin)
            this.jp = jp
            console.log(admin)
        },
        methods: {
            back() {
                history.go(-1)
            },
            offf() {
                offtrad(this.id).then((res) => {
                    if (res.code == 200) {
                        this.step = 2
                    } else {
                        this.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });
                    }
                })
            }
        },
    };
</script>

<style lang="scss" scoped>
    .redbtn {
        background: #D93025 !important;
        color: #fff !important;
        border: none !important;
    }

    .redbtn:hover {
        opacity: 0.6 !important;
    }

    .btns {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cb {
        box-sizing: border-box;
        padding: 50px 0 20px;
    }

    .title {
        font-size: 21px;
        color: #1d1d1f;
        font-weight: bold;
    }

    .cbtitle {
        font-size: 21px;
        color: #1D1D1F;
        text-align: center;
        font-weight: bolder;
    }

    .cbContent {
        font-size: 16px;
        color: #D93025;
        line-height: 21px;
        margin-top: 40px;
        text-align: center;
        margin-bottom: 50px
    }

    .border_bottom {
        border-bottom: 1px solid #d2d2d7;
        padding-bottom: 20px;


    }

    .cont {
        width: 894px;
        margin-top: 28px;
        background: #fff;
        padding: 40px 40px 69px 40px;
        box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);

        .cont_tit {
            font-size: 21px;
            color: #1d1d1f;
            font-weight: bold;
            padding-bottom: 20px;
            border-bottom: 1px solid #d2d2d7;
        }

        .cont_bottom {
            margin-top: 23px;
            padding-bottom: 69px;
            border-bottom: 1px solid #d2d2d7;
        }

        .cont_left {
            font-size: 16px;
            color: #1d1d1f;
            // font-weight: bold;
        }

        .cont_right {
            font-size: 16px;
            color: #707070;
        }

        .btn {
            width: 200px;
            height: 50px;
            background: rgba(29, 29, 31, 0.1);
            border-radius: 10px;
            border: 1px solid #1d1d1f;
            font-size: 20px;
            color: #1d1d1f;
            font-weight: bold;

            margin: 0 20px 0;
            margin-top: 31px;
        }
    }
</style>