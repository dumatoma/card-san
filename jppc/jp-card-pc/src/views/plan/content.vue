<template>
    <div>
        <div class="cont_tit">ご契約内容</div>
        <div class="wrap">
            <div class="item">
                <div class="u-flex">
                    <div>ご利用中のプラン：</div>
                    <div class="right" v-text="planInfo.type == 1? 'ライト' :planInfo.type == 2 ? 'スタンダード' : 'プレミアム' ">
                    </div>
                </div>
                <div class="text_blue shou box" @click="changeCurrent">プランを変更</div>
            </div>
            <div class="item">
                <div class="u-flex">
                    <div>支払い周期：</div>
                    <div class="right">{{planInfo.month}}ヶ月</div>
                </div>
                <div class="text_blue shou box" @click="openModule">
                    支払い周期を変更
                </div>
            </div>
            <div class="item" style="border: none;">
                <div class="u-flex box">
                    <div>契約更新日：</div>
                    <div class="right">{{format(planInfo.end_time*1000)}}</div>
                </div>
            </div>
        </div>
        
        <div class="wrap" style="padding: 0;width: 450px;">
            <div class="paytitle">
                お支払い方法
            </div>
            <div class="paycontent" v-if="planInfo.card_type == 1">
                <div class="payitem">
                    クレジットカード決済
                </div>
            </div>
            
            
            <div class="paycontent" v-else>
                <div class="payitem" v-text="planInfo.card_type == 0?'-' : planInfo.card_type == 1?'クレジットカード決済' :planInfo.card_type == 2?'App Store課金' : 'Google Play課金'"></div>
                <div class="payitem fe" v-if="planInfo.card_type != 0">
                    <div class="btn" @click="showchagne = true">
                        お支払い方法の変更
                    </div>
                </div>
            </div> 
        </div>
        <div class="btn" style="width: 200px;margin: 50px 0 0 120px;" @click="tooff" v-if="showCancel && planInfo.card_type == 1">
            解約手続き
        </div>
        <div class="fixed" v-show="keepShow">
            <div class="zhe"></div>
            <div class="change">
                <div class="change_tit">支払い情報を変更しました</div>
                <div class="change_keep u-flex-center shou" @click="keepClick">
                    閉じる
                </div>
            </div>
        </div>
        
        
        <div class="mask" v-if="showchagne">
            <div class="attention">
                <div class="aptitle">
                    <img src="../../static/index/close.png" @click="showchagne = false" alt="" />
                    <span>お支払い方法の変更について</span>
                </div>
                <div class="apcontent">
                    <div class="firstPart">
                        App課金、Google Play課金でご契約の方は、このウェブサイトからお支払い方法の変更ができません。　お支払い方法を変更するには、アプリで契約したサブスクリプション契約を一旦解除した後、このウェブサイトから再度ご契約手続きをお願いします。
                    </div>
                    <div class="secondPart">
                        ご契約をWEB決済に変更することで、このウェブサイトから、全ての手続きが簡単になります。　PCで管理することが多い方や、プランや支払い周期の変更など柔軟に運用したい方は、お支払い方法を変更することを推奨します。
                        <br/><br/>
                        【支払い方法の変更方法】
                        iosもしくはAndroidのデバイスから、ご契約中のサブスクリプションの解約、もしくは定期購入を解約をします。
                        <br/><br/>
                        契約解除日に、契約期限切れのアテンションが表示されますので、このウェブサイトから再度ご契約手続きを行うことで、支払い方法の変更が可能です。　一旦契約を解除してもアカウト情報は引き継がれますので、運用には影響はございません。
                    </div>
                </div>
            </div>
        </div>
        
        
        
        
        <!-- <div class="mask" v-if="showca">
            <div class="sur_box sur_one m-t-30">
                <div class="sur_blue" style="">お支払い方法を選択</div>
                <div class="payment_bg">
                    <div style="padding-left: 16px">
                        <img src="@/static/cards.png" style="width: 275px;height: 40px; margin: 10px 0 0" />
                        <div class="pay_top">カード番号</div>
                        <div style="border-bottom: 1px solid #d2d2d7;width: 300px;">
                            <input class="pay_bottom pay_big" type="text" placeholder="3456 7898 1987 0001" v-model="cardInfo.card_no" />
                        </div>
                        
                        <div style="display: flex;align-items: center;">
                            <div>
                                <div class="pay_top">有効期限</div>
                                <div style="border-bottom: 1px solid #d2d2d7;width: 130px;">
                                    <input class="pay_bottom pay_big" type="text" placeholder="mm/yy" v-model="cardInfo.expire_date" />
                                </div>    
                                
                            </div>
                            <div style="margin-left: 25px;">
                                <div class="pay_top">CVV</div>
                                <div style="border-bottom: 1px solid #d2d2d7;width: 130px;">
                                    <input class="pay_bottom pay_big" type="text" placeholder="3桁or4桁の数字" v-model="cardInfo.cvv" />
                                </div>
                                
                            </div>
                        </div>
                        <div class="pay_top">カード名義人</div>
                        <div style="border-bottom: 1px solid #d2d2d7;width: 300px;">
                            <input class="pay_bottom pay_big" type="text" placeholder="ICHIROU SUZUKI"
                                v-model="cardInfo.name" />
                        </div>
                        <div class="payitem fe" style="margin-top: 11px;">
                            <div class="btn" @click="saveD">
                                保存
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
</template>

<script>
    import {
        getStore,
        getPackageList,
        getBindCard,
        buyPlan,
        editPaymethod
    } from "@/http/api.js"
    export default {
        data() {
            return {
                cycleShow: false,
                cycleIndex: 0,
                planInfo: {},
                plans: {},
                cycleList: [],
                keepShow: false,
                cardInfo:{},
                cardid:"",
                showca:false,
                infos:{},
                showchagne:false,
                showCancel:false,
                id:""
            };
        },
        created() {
            let that = this
            this.Loading = this.$loading({
                spinner: "", //自定义加载图标类名
                text: "読み込み中…", //显示在加载图标下方的加载文案
                background: "rgba(0, 0, 0, 0.6)", //遮罩背景色
            })
            getStore().then((res) => {
                console.log(":111",res.data.shop_info.vip)
                if (res.code == 200) {
                    that.planInfo = res.data.shop_info.vip
                    that.id = res.data.shop_info.vips[0].id
                    let infos = res.data.shop_info.vips
                    if(infos[0].cancel_time == 0){
                        that.showCancel = true
                    }
                    that.getPlanDetail()
                    if (that.planInfo.month == 1) {
                        that.cycleIndex = 0
                    } else if (that.planInfo.month == 6) {
                        that.cycleIndex = 1
                    } else {
                        that.cycleIndex = 2
                    }
                }
            })
            getBindCard().then((res) => {
                console.log("change",res)
                if(res.code == 200){
                    that.cardInfo = res.data.cards[0]
                    that.cardid = res.data.cards[0].id
                }
            })
            this.Loading.close()
        },
        methods: {
            tooff(){
              this.$router.push('/terminationFulfil?id='+this.id+"&refresh="+this.format(this.planInfo.end_time*1000))  
            },
            // saveD(){
            //     let that = this
            //      let data = {}
            //      data['_method'] = "put"
            //      data['card_no'] = that.cardInfo.card_no
            //      data['expire_date'] = that.cardInfo.expire_date
            //      data['cvv'] = that.cardInfo.cvv
            //      data['name'] = that.cardInfo.name
            //      data["id"] = that.cardid
            //      editPaymethod(data).then((res) => {
            //          if(res.code == 200){
            //             that.$message({
            //             	message: res.message,
            //             	type: 'success',
            //                  offset: 400
            //             });
            //             that.showca = false
            //          }else{
            //              that.$message({
            //              	message: res.message,
            //              	type: 'error',
            //                   offset: 400
            //              });
            //          }
            //      })
            // },
            hideBankCard(value){
              if (value && value.length > 8) {
                return `${value.substring(0, 4)} ${"*".repeat(value.length - 8).replace(/(.{4})/g, `$1 `)}${value.length % 4 ? " " : ""}${value.slice(-4)}`;
              }
              return value;
            },
            openModule() {
                let that = this
                console.log("123123123123123")
                that.$router.push({
                    path: "/planConfirm?index="+(that.planInfo.type * 1 - 1),
                });
            },
            format(timestamp) {
                console.log("st",timestamp)
                let date = new Date(timestamp*1);
                let year = date.getFullYear();
                let month = date.getMonth() + 1;
                let day = date.getDate();
                let hours = date.getHours();
                let minutes = date.getMinutes();
                let seconds = date.getSeconds();
                let result = year+"年"+month+"月"+day+"日"
                console.log('result',result)
                return result
            },
            changePlan() {
                let that = this
                that.Loading = this.$loading({
                    spinner: "", //自定义加载图标类名
                    text: "読み込み中…", //显示在加载图标下方的加载文案
                    background: "rgba(0, 0, 0, 0.6)", //遮罩背景色
                })
                getBindCard().then((res) => {
                    if (res.code == 200) {
                        if (res.data.cards.length == 0) {
                            that.$message("请添加支付方式")
                        } else {
                            let temp = {}
                            temp['type'] = that.planInfo.type
                            temp['month'] = that.cycleIndex == 0 ? 1 : that.cycleIndex == 1 ? 6 : 12
                            temp['card_type'] = 1
                            temp['card_no'] = res.data.cards[0].card_no
                            temp['expire_date'] = res.data.cards[0].expire_date
                            temp['cvv'] = res.data.cards[0].cvv
                            temp['name'] = res.data.cards[0].name
                            buyPlan(temp).then((res) => {
                                console.log(res)
                                if (res.code == 200) {
                                    if (res.data.amount == 0) {
                                        that.$message.success(res.message)
                                    } else {
                                        window.open(res.data.url)
                                    }
                                }
                                that.Loading.close()
                            })
                        }
                    }
                })

            },
            changeCurrent() {
                this.$router.push("/utilizePlan")
            },
            changeMoney() {
                this.$router.push("/payMethod?id="+this.cardid)
            },
            formatTime(stamp) {
                let that = this
                var date = new Date(stamp * 1000)
                let y = date.getFullYear()
                let m = date.getMonth() + 1
                return y + '年' + m + '月'
            },
            getPlanDetail() {
                let that = this
                getPackageList().then((res) => {
                    // console.log("plan",res)
                    if (res.code == 200) {
                        res.data.vip_contract.forEach((val, index) => {
                            if (val.id == that.planInfo.type) {
                                that.plans = val
                            }
                        })
                        that.calculate()
                    }
                })
            },
            calculate() {
                let that = this
                let array = []
                let temp = that.plans
                temp.price.forEach((val, index) => {
                    let obj = {}
                    if (index == 0) {
                        obj.time = "月額"
                        obj.money = '￥' + val + "/月"
                        obj.value = val
                    } else if (index == 1) {
                        obj.time = "6ヶ月"
                        obj.money = '￥' + val + "×6ヶ月"
                        obj.value = val
                    } else {
                        obj.time = "1年間"
                        obj.money = '￥' + val + "×12ヶ月"
                        obj.value = val
                    }
                    array.push(obj)
                })
                for (let i = 0; i < array.length; i++) {
                    if (i == 1) {
                        array[i].tip = "￥" + (array[0].value * 6 - array[i].value) + "お得！"
                    } else if (i == 2) {
                        array[i].tip = "￥" + (array[0].value * 12 - array[i].value) + "お得！"
                    }
                }
                that.cycleList = array
                that.Loading.close()
            },
            cycleClick(index) {
                this.cycleIndex = index;
            },
            keepClick() {
                this.cycleShow = false;
                this.keepShow = false;
            },
        },
    };
</script>

<style lang="scss" scoped>
    .attention{
        width: 600px;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
        .apcontent{
            box-sizing: border-box;
            padding: 40px;
            background: #fff;
            width: 100%;
            
            .firstPart{
                width: 100%;
                background: rgba(230,191,24,0.5);
                font-family: Noto Sans Kannada, Noto Sans Kannada;
                font-weight: bold;
                font-size: 16px;
                color: #1D1D1F;
                box-sizing: border-box;
                padding: 16px;
            }
            
            .secondPart{
                margin-top: 20px;
                background: #FFFFFF;
                box-shadow: 0px 0px 10px 1px rgba(0,0,0,0.07);
                border: 1px solid #D2D2D7;
                font-family: Noto Sans Kannada, Noto Sans Kannada;
                font-weight: 400;
                font-size: 16px;
                color: #1D1D1F;
                box-sizing: border-box;
                padding: 16px;
                white-space: pre-wrap;
            }
        }
        
        .aptitle{
            width:100%;
            height: 40px;
            background: linear-gradient( 180deg, #EDECED 0%, #D2D1D2 100%);
            border-radius: 5px 4px 0px 0px;
            text-align: center;
            position: relative;
            font-weight: normal;
            font-size: 16px;
            color: #1D1D1F;
            line-height: 40px;
            
            img{
                position: absolute;
                left: 13px;
                top: 9px;
                width: 20px;
                height: 20px;
            }
        }
    }
    .sur_box {
        width: 524px;
        background: #ffffff;
        box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.2);
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%,-50%);
        
        .sur_blue {
            height: 40px;
            font-size: 16px;
            line-height: 40px;
            color: #1d1d1f;
            font-weight: bold;
            background: rgba(26, 115, 232, 0.1);
            box-sizing: border-box;
            padding-left: 20px;
        }
        
        .sur_bg {
            padding: 20px;
        
            .text_blue {
                color: #1a73e8;
                text-align: right;
                margin-top: 14px;
            }
        }
        
        .sur_bor {
            border-bottom: 1px solid #d2d2d7;
        
            &:last-child {
                border: none;
            }
        }
        
        .payment_bg {
            padding: 24px;
            border-bottom: 1px solid #d2d2d7;
        
            &:last-child {
                border: none;
            }
        
            .pay_item {
                font-size: 16px;
                color: #1d1d1f;
        
                img {
                    width: 30px;
                    margin-right: 10px;
                }
            }
        
            .pay_top {
                font-size: 14px;
                color: #707070;
                margin-top: 20px;
            }
        
            .pay_bottom {
                width: 100%;
                height: 40px;
                background: #fff;
                border-radius: 10px;
                padding: 14px 0;
                margin-top: 10px;
                border: none;
                // border-bottom: 1px solid #aaa;
                -moz-box-sizing: border-box;
                -webkit-box-sizing: border-box;
                -o-box-sizing: border-box;
                -ms-box-sizing: border-box;
                box-sizing: border-box;
            }
        
            input::-webkit-input-placeholder {
                /* WebKit browsers */
                font-size: 16px;
                color: #d7d7d7;
            }
        
            input:-moz-placeholder {
                /* Mozilla Firefox 4 to 18 */
                font-size: 16px;
                color: #d7d7d7;
            }
        
            input::-moz-placeholder {
                /* Mozilla Firefox 19+ */
                font-size: 16px;
                color: #d7d7d7;
            }
        
            input::-ms-input-placeholder {
                /* Internet Explorer 10+ */
                font-size: 16px;
                color: #d7d7d7;
            }
        }
    }
     input{
         outline: none;
     }   
    .mask{
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, .4);
        z-index: 99;
    }
    .payitem{
        font-family: Hiragino Sans, Hiragino Sans;
        font-weight: normal;
        font-size: 14px;
        color: #1D1D1F;
        margin: 5px 0;
    }
    .fe{
        display: flex;
        flex-direction: row-reverse;
    }
    .btn{
        padding: 0 10px;
        height: 34px;
        background: rgba(29,29,31,0.1);
        border-radius: 10px 10px 10px 10px;
        border: 1px solid #707070;
        font-family: Hiragino Sans, Hiragino Sans;
        font-weight: normal;
        font-size: 12px;
        color: #1D1D1F;
        text-align: center;
        line-height: 34px;
        cursor: pointer;
    }
    .btn:hover{
        opacity: 0.6 !important;
    }
    .paytitle{
        width: 100%;
        height: 42px;
        line-height: 42px;
        background: rgba(26,115,232,0.1);
        box-sizing: border-box;
        padding: 0 20px;
        font-family: Hiragino Sans, Hiragino Sans;
        font-weight: normal;
        font-size: 14px;
        color: #1D1D1F;
    }
    .paycontent{
        box-sizing: border-box;
        padding: 20px 25px;
    }
    .cont_tit {
        font-size: 21px;
        color: #1d1d1f;
        font-weight: bold;
        margin-top: 20px;
    }

    .radius {
        width: 14px;
        height: 14px;
        margin-right: 14px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid #707070;

        .blue_dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin: 2px auto;
            background: #1a73e8;
        }
    }

    .radiusCss {
        border: 1px solid #1a73e8;
    }

    .wrap {
        width: 409px;
        background: #ffffff;
        margin-top: 20px;
        padding: 20px;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);

        .item {
            padding: 20px;
            font-size: 16px;
            border-bottom: 1px solid #d2d2d7;
            display: flex;
            align-items: center;
            justify-content: space-between;

            .right {
                font-weight: bold;
            }

            .text_blue {
                font-size: 14px;
                color: #1a73e8;
                text-align: right;
            }
        }

        .empty {
            height: 135px;
        }
    }

    .fixed {
        position: fixed;
        top: 0%;
        left: 0%;
        width: 100vw;
        height: 100vh;
        z-index: 1;
        background: rgba(29, 29, 31, 0.2);

        .zhe {
            position: absolute;
            width: 100vw;
            height: 100vh;
            background: rgba(29, 29, 31, 0.2);
        }

        ::-webkit-scrollbar {
            display: none;
        }

        .sus-box {
            width: 460px;
            height: 787px;
            background: #ffffff;
            box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
            position: fixed;
            top: 50%;
            left: 50%;

            transform: translate(-50%, -50%);

            .sus_wrap {
                height: 726px;
                overflow-y: scroll !important;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .sus_tit {
                height: 60px;
                font-size: 20px;
                color: #1d1d1f;
                font-weight: bold;
                background: #f9f9f9;
                position: relative;
                border-bottom: 1px solid #d2d2d7;

                .cha {
                    width: 24px;
                    height: 24px;
                    top: 18px;
                    right: 20px;
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(29, 29, 31, 0.1);

                    .el-icon-close {
                        color: rgba(29, 29, 31, 0.6);
                        font-weight: bold;
                        font-size: 17px;
                    }
                }
            }

            .sus_one {
                width: 420px;
                background: #fff;
                border-radius: 10px;
                margin: 0 auto;
                font-size: 16px;
                margin-top: 40px;
                padding: 30px 0 30px 16px;
                border: 1px solid #d2d2d7;

                .grey {
                    color: #707070;
                }

                .black {
                    color: #1d1d1f;
                    font-weight: bold;
                }
            }

            .sur_two {
                width: 420px;
                background: #fff;
                margin: 0 auto;
                font-size: 16px;
                margin-top: 40px;
                box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);

                .sur_blue {
                    height: 40px;
                    font-size: 16px;
                    color: #1d1d1f;
                    font-weight: bold;
                    background: rgba(26, 115, 232, 0.1);
                }

                .sur_bg {
                    padding: 20px;

                    .text_blue {
                        color: #1a73e8;
                        text-align: right;
                        margin-top: 14px;
                    }
                }

                .sur_bor {
                    border-bottom: 1px solid #d2d2d7;

                    &:last-child {
                        border: none;
                    }
                }
            }

            .tip {
                width: 420px;
                height: 71px;
                background: rgba(26, 115, 232, 0.1);
                border-radius: 10px;
                font-size: 14px;
                margin: 40px auto;
                color: #1a73e8;
                padding: 0 18px;
            }

            .bottom {
                border-top: 1px solid #d2d2d7;
                padding: 40px 0 56px 0;

                .btn {
                    width: 295px;
                    height: 48px;
                    font-size: 16px;
                    margin: 0 auto;
                    color: #ffffff;
                    background: #1a73e8;
                    border-radius: 24px;
                }
            }
        }

        .change {
            width: 312px;
            height: 242px;
            background: #ffffff;
            box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);
            border-radius: 11px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;

            .change_tit {
                margin-top: 60px;
                font-size: 16px;
                color: #1d1d1f;
            }

            .change_keep {
                width: 112px;
                height: 40px;
                font-size: 14px;
                color: #1d1d1f;
                margin: 0 auto;
                margin-top: 66px;
                background: #eaeaea;
                border-radius: 20px 20px 20px 20px;
                border: 1px solid #707070;
            }
        }
    }
</style>