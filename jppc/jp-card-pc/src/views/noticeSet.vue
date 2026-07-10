<template>
    <div>
        <div class="not_tit u-flex u-row-between">お知らせ設定</div>
        <div class="not_cont">
            <div class="not_top u-flex">
                <div class="radius shou" :class="{ radiusCss: type == 1 }" @click="setClick(1)">
                    <div class="blue_dot" v-if="type == 1"></div>
                </div>
                <div :class="[type == 1 ? 'text_black' : 'text_grey']">
                    お知らせ投稿機能
                </div>
            </div>
            <div class="not_item" :class="[type == 2 ? 'text_black' : 'text_grey']">
                <div class="u-flex">
                    <div class="radius shou" :class="{ radiusCss: type == 2 }" @click="setClick(2)">
                        <div class="blue_dot" v-if="type == 2"></div>
                    </div>
                    <div>Instagram連携(ビジネスアカウント)</div>
                </div>
                <div class="m-l-50 m-t-10">
                    <div style="font-size: 14px">
                       連携にはInstagram「ビジネスアカウント」が必要です。　連携したアカウントの投稿内容がユーザーアプリの「お知らせ」に表示されます。　Instagramを持っていないユーザーが「お知らせ」を閲覧した場合、Instagramアプリのダウンロード画面に推移します。　アプリダウンロード後は、Instagramの投稿内容がユーザーアプリの「お知らせ」に表示されます。
                    </div>
                    <div class="btn left shou" style="border-radius: 30px;border: 1px solid #707070;margin: 20px auto 0;" @click="connectIns">連携する</div>
                </div>
            </div>
           <!-- 
            <div v-if="insShow">
                <iframe :src="insurl" frameborder="0"></iframe>
            </div>
            -->
            <div class="u-flex u-row-center" style="padding: 30px 0 70px 0">
                <div class="btn left shou" @click="back">戻　る</div>
                <div class="btn right shou" @click="keepClick">保　存</div>
            </div>
        </div>
        <!-- 更新小弹窗 -->
        <div class="fixed" v-show="noticeShow">
            <div class="notice no-size">
                <div class="notice_one" v-if="type == 2">
                    お知らせ設定を<br />
                    “Instagram連携” に変更しました
                </div>
                <div class="notice_one" v-if="type == 3">
                    お知らせ設定を<br />
                    “Twitter連携” に変更しました
                </div>
                <div class="close u-flex u-row-center" @click="noticeShow = false">
                    閉じる
                </div>
            </div>
        </div>
        <!-- 保存弹窗 -->
        <div class="fixed" v-show="keepShow">
            <div class="zhe"></div>
            <div class="sus-box">
                <img src="../static/success.png" alt="" />
                <div class="keep">保存しました</div>
            </div>
        </div>
        
        <!-- ins弹窗 -->
        <div class="masktip" v-if="type == 2">
            <div class="avatar">
                <img :src="avatar" alt="" />
            </div>
            <div class="userName" v-text="nickName"></div>
            <div class="word1">
                Instagramビジネスアカウントと連携中です。
            </div>
            <!-- <div class="word1">
                通常のお知らせ機能を利用す場合は、
            </div>
            <div class="word1">
                ”お知らせ設定”の設定を変更してください。
            </div> -->
        </div>
    </div>
</template>

<script>
    import {
        noticeSetting,
        getStore,
        getInsUrl
    } from "@/http/api.js"
    export default {
        data() {
            return {
                insShow:false,
                show: 1,
                noticeShow: false,
                keepShow: false,
                instagram: "",
                twitter: "",
                type: 1,
                insurl:'',
                avatar:"",
                nickName:""
            };
        },
        created() {
            let that = this
            that.getStoreInfo()
        },
        methods: {
            getStoreInfo() {
                let that = this
                getStore().then((res) => {
                    console.log(res)
                    if (res.code == 200) {
                        that.instagram = res.data.shop_info.instagram
                        that.twitter = res.data.shop_info.twitter
                        that.type = res.data.shop_info.notice_type
                        that.avatar = res.data.shop_info.instagram_avatar
                        that.nickName = res.data.shop_info.instagram_username
                    }
                })
            },
            connectIns(){
                let that = this
                let data = {}
                data['type'] = "notice"
                data['notice_type'] = 2
                data['instagram'] = that.ins
                noticeSetting(data).then((res) => {
                    if (res.code == 200) {
                       getInsUrl().then((rest) => {
                           console.log("rest",rest)
                           if (rest.code == 200) {
                               that.insShow = true
                               that.insurl = rest.data.url
                               // uni.redirectTo({
                               //     url: "../../webview/webview?link=" + encodeURIComponent(rest.data.url)
                               // })
                               window.open(rest.data.url)
                           }
                       })
                    } else {
                        this.$message.error(res.message)
                    }
                })
            },
            setClick(e) {
                this.type = e;
                // if (e != 1) {
                //     this.noticeShow = true
                // }
            },
            back() {
                this.$router.push({
                    path: "/notice",
                });
            },
            keepClick() {
                let that = this
                let data = {}
                data["type"] = "notice"
                data["notice_type"] = that.type
                data["instagram"] = that.instagram
                data["twitter"] = that.twitter
                noticeSetting(data).then((res) => {
                    if (res.code == 200) {
                        that.keepShow = true;
                        setTimeout(() => {
                            that.keepShow = false;
                        }, 2000);
                    } else {
                        that.$message({
                            message: res.message,
                            type: 'error'
                        });
                    }
                })

            },
        },
    };
</script>

<style lang="scss" scoped>
    .avatar{
        width: 80px;
        height: 80px;
        border-radius: 50%;
        overflow: hidden;
    }
    .userName{
        font-size: 24px;
        margin: 28px 0 8px
    }
    .avatar img{
        width: 100%;
        height: 100%;
    }
    
    .masktip{
        position: fixed;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
        z-index: 10091 !important;
        width:423px;
        height: 320px;
        background: rgb(29, 29, 31,0.8);
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        box-sizing: border-box;
        padding: 0 40px;
        border-radius: 12px;
    }
    .word1{
        width: 100%;
        text-align: center;
        font-size: 20px;
        line-height: 30px;
    }
    .not_tit {
        width: 816px;
        padding: 10px 0 22px 0;
        font-size: 21px;
        color: #1d1d1f;
        font-weight: bold;
    }

    .radius {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin: 0 14px 0 19px;
        background: #fff;
        border: 1px solid #707070;

        .blue_dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 2px auto;
            background: #1a73e8;
        }
    }

    .text_grey {
        color: #707070;
    }

    .text_black {
        color: #1d1d1f;
    }

    .not_cont {
        width: 816px;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);

        .not_top {
            padding: 20px 0 40px 0px;
            font-size: 16px;
            color: #1d1d1f;
            font-weight: bold;
            border-bottom: 1px solid #d2d7d6;
        }

        .radiusCss {
            border: 1px solid #1a73e8;
        }

        .not_item {
            padding: 20px 53px 30px 0;
            border-bottom: 1px solid #d2d7d6;

            .tarea {
                width: 716px;
                height: 84px;
                outline: 0;
                margin-top: 10px;
                background: #ffffff;
                border-radius: 10px;
                padding: 10px;
                border: 1px solid #aaa;
                resize: none !important;
            }

            textarea::-webkit-input-placeholder {
                /* WebKit browsers */
                color: #aaa;
            }

            textarea:-moz-placeholder {
                /* Mozilla Firefox 4 to 18 */
                color: #aaa;
            }

            textarea::-moz-placeholder {
                /* Mozilla Firefox 19+ */
                color: #aaa;
            }

            textarea::-ms-input-placeholder {
                /* Internet Explorer 10+ */
                color: #aaa;
            }
        }

        .btn {
            width: 200px;
            height: 50px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }

        .left {
            color: #707070;
            background: rgba(0, 0, 0, 0.07);
        }

        .right {
            color: #fff;
            margin-left: 50px;
            background: #1a73e8;
        }
    }

    .fixed {
        position: fixed;
        top: 0%;
        left: 0%;
        width: 100vw;
        height: 100vh;
        background: rgba(29, 29, 31, 0.2);

        .notice {
            width: 480px;
            height: 243px;
            background: #ffffff;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);
            border-radius: 11px;

            .notice_one {
                line-height: 26px;
                margin: 0 auto;
                font-weight: bold;
                margin-top: 62px;
                text-align: center;
                font-size: 16px;
                color: #1d1d1f;
            }

            .close {
                width: 112px;
                height: 40px;
                font-size: 14px;
                color: #1d1d1f;
                background: #eaeaea;
                border-radius: 20px;
                margin: 0 auto;
                font-weight: bold;
                margin-top: 42px;
                border: 1px solid #707070;
            }
        }

        .zhe {
            position: absolute;
            width: 100vw;
            height: 100vh;
            background: rgba(29, 29, 31, 0.2);
        }

        .sus-box {
            width: 160px;
            height: 146px;
            background: #ffffff;
            border-radius: 10px 10px 10px 10px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;

            img {
                margin-top: 30px;
                width: 50px;
                height: 50px;
            }

            .keep {
                margin-top: 20px;
                font-size: 16px;
                color: #2699fb;
            }
        }
    }
</style>