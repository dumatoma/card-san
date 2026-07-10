<template>
    <div>
        <div class="tit u-flex u-row-between">
            <div>ダッシュボード</div>
            <div class="green shou u-flex u-row-center" @click="projectClick">
                販促アイテムはこちら
            </div>
        </div>
        <div class="u-flex u-row-between" style="margin-top: 24px; width: 672px">
            <div class="number">
                <div class="grey">今日</div>
                <div class="blue">{{day_order_count}}</div>
            </div>
            <div class="number">
                <div class="grey">今月</div>
                <div class="blue">{{month_order_count}}</div>
            </div>
            <div class="number">
                <div class="grey">メンバー数</div>
                <div class="blue" style="color: #000">{{member_count}}</div>
            </div>
        </div>
        <div class="jie" v-if="vips.length == 1 && vips[0].cancel_time != 0">
            <div class="jieleft">
                <img src="../static/icon/tips.png" alt="" />
                <span>現在のご契約は{{cancelTime}}にキャンセルされます。</span>
            </div>
            <div class="jieright" @click="toBuy">
                継続はこちら
            </div>
        </div>
        <div class="content_tit">運営からのお知らせ</div>
        <div class="content no-size">
            <div class="user-list no-size">
                <scrollbar-container ref="scrollbarRef" height="331px" class="scroll-container">
                    <div style="border: 1px solid #e8e8e8; width: 98%">
                        <div class="item" v-for="(item, index) in list" :key="index">
                            <div class="box">
                                <div class="name" v-text="item.title"></div>
                                <div class="time" v-text="item.create_time"></div>
                                <div class="info inlie2" v-text="item.content"></div>
                            </div>
                        </div>
                    </div>
                </scrollbar-container>
            </div>
        </div>

        <div class="mask" v-if="showd">
            <div class="maskContent">
                <div class="maskTitle">
                    無料トライアル期間終了のため、現在ご利用できません。
                </div>
                <div class="maskDetail">
                    お申し込み手続きをすることで、すぐにご利用ができます
                </div>
                <div class="bt" @click="toBuy">
                    お申し込みはこちら
                </div>
            </div>
        </div>
        
        <div class="mask" v-if="showg">
            <div class="maskContent">
                <div class="maskTitle">
                    ご契約期間終了のため、現在ご利用できません。
                </div>
                <div class="maskDetail">
                    お申し込みを再開することで、すぐにご利用ができます。
                </div>
                <div class="bt" @click="toBuy">
                    再開はこちら
                </div>
            </div>
        </div>
        
        
        <div class="mask" v-if="showsms">
            <div class="maskContent" style="height: auto;">
                <div class="maskTitle" style="color: #1d1d1f;font-weight: bold;">
                    SMS通知の配信数超過のお知らせ
                </div>
                <div class="maskDetail" style="text-align: center;font-size: 12px;">
                    SMS通知数が配信上限数を超過しました。追加 <br/> 
                        配信利用料のお支払いください。
                    <br/> <br/> <br/> 
                    利用料（￥1,200）で、
                    <br/> 
                    更に100通のSMS通知を配信できます。
                    <br/> 
                    お支払い完了後に、この表示は消去されます。

                </div>
                <div class="bt" @click="buysms">
                    お支払い
                </div>
            </div>
        </div>
        
        <div class="mask" v-if="showevery">
            <div class="maskContent" style="height: auto;">
                <div class="maskTitle" style="color: #1d1d1f;font-weight: bold;">
                    現在のプラン：プレミアム
                </div>
                <div class="maskDetail" style="text-align: center;font-size: 12px;">
                   お試し期間中は、全ての機能が無料でご<br/>利用いただけますので、お店の運用に<br/>合った機能を選んでお試しください。
                </div>
                <div class="bt" @click="closeevery">
                    OK
                </div>
            </div>
        </div>
        
    </div>
</template>

<script>
    import ScrollbarContainer from "@/components/scrollContainer.vue";
    import {
        getStore,
        getYYNotice,
        getExtra,
        topay
    } from "@/http/api.js"
    export default {
        components: {
            ScrollbarContainer,
        },

        data() {
            return {
                day_order_count: 0,
                member_count: 0,
                month_order_count: 0,
                list: [],
                vips: [],
                cancelTime: "",
                showd: false,
                showg: false,
                showsms:false,
                showevery:false
            };
        },
        mounted() {
            // *注：demo更新需要更新容器
            this.$nextTick(() => {
                this.$refs.scrollbarRef.updateScroll();
            });
        },
        created() {
            let that = this
            let ale = localStorage.getItem("ale")
            console.log("ale",ale)
            getStore().then((res) => {
                if (res.code == 200) {
                    console.log("storeinfo", res.data.shop_info.last_vip_is_try)
                    that.day_order_count = res.data.day_order_count
                    that.month_order_count = res.data.month_order_count
                    that.member_count = res.data.member_count
                    that.vips = res.data.shop_info.vips
                    if(res.data.shop_info.vips.length > 0){
                        that.cancelTime = res.data.shop_info.vips[0].end_time
                    }else{
                        that.cancelTime = ''
                    }
                    
                    if(res.data.shop_info.vip.is_try == 1 && that.showsms == false && ale == 1){
                        that.showevery = true
                    }
                    
                    if (res.data.shop_info.vip.type == 0) {
                        if (res.data.shop_info.last_vip_is_try == 1) {
                            that.showd = true
                        } else {
                            that.showg = true
                        }
                    }else{
                        if(res.data.shop_info.vip_sms_alert == 1){
                            that.showsms = true
                        }
                       
                    }  
                }
            })
            getYYNotice().then((res) => {
                if (res.code == 200) {
                    that.list = res.data.notices
                }
            })
        },
        methods: {
            closeevery(){
                this.showevery = false
                localStorage.removeItem("ale")
            },
            
            buysms(){
                let that = this
                let data = {}
                data.type = 3
                data.card_type = 1
                getExtra(data).then((res) => {
                    console.log(res)
                    if(res.code == 200){
                       let  temp = {}
                       temp.order_no = res.data.order_no
                       topay(temp).then((rest) => {
                           if(rest.code == 200){
                               window.open(rest.data.url)
                           }else{
                               that.$message({
                                   message: '' + rest.message,
                                   type: 'error',
                                   offset: 400,
                                   center: true
                               });
                           }
                       }) 
                    }else{
                        that.$message({
                            message: '' + res.message,
                            type: 'error',
                            offset: 400,
                            center: true
                        });
                    }
                   
                })
            },
            toBuy() {
                let that = this
                this.$router.push({
                    path: "/utilizePlan",
                })
            },
            projectClick() {
                this.$router.push({
                    path: "/projectList",
                });
            },
        },
    };
</script>

<style lang="scss" scoped>
    .maskDetail {
        font-size: 14px;
        color: #1D1D1F;
        margin: 30px 0;
        text-align: center;
    }

    .bt {
        width: 265px;
        height: 48px;
        background: rgba(26, 115, 232, 1);
        border-radius: 10px 10px 10px 10px;
        text-align: center;
        line-height: 48px;
        font-size: 16px;
        color: #fff;
        cursor: pointer;
    }
    
    .bt:hover{
        background: rgba(26, 115, 232, 0.6) !important;
    }

    .mask {
        width: 100vw;
        height: 100vh;
        background: rgba(29, 29, 31, 0.5);
        position: fixed;
        left: 0;
        top: 0;
        z-index: 109 !important;

        .maskContent {
            width: 345px;
            height: 263px;
            background: #FFFFFF;
            border-radius: 10px 10px 10px 10px;
            box-sizing: border-box;
            padding: 40px;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);

            .maskTitle {
                font-size: 16px;
                color: #D93025;
                text-align: center;
            }
        }
    }

    .jie {
        width: 672px;
        height: 60px;
        background: rgba(26, 115, 232, 0.5);
        box-shadow: 0px 0px 5px 1px rgba(0, 0, 0, 0.16);
        border: 1px solid #D2D2D7;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 0 20px;
        margin-top: 30px;

        .jieleft {
            display: flex;
            align-items: center;
            font-weight: bold;
            font-size: 16px;
            color: #1D1D1F;

            img {
                width: 20px;
                height: 20px;
                margin-right: 10px;
            }
        }

        .jieright {
            font-weight: bold;
            font-size: 16px;
            color: #D93025;
        }
    }

    .scroll-container {
        border: 1px solid #e4e7ed;
        border-radius: 4px;
    }

    .list-item {
        line-height: 80px;
        font-size: 16px;
        font-weight: 600;
    }

    .tit {
        width: 672px;
        color: #1d1d1f;
        font-size: 21px;
        font-weight: bold;

        .green {
            width: 196px;
            height: 44px;
            background: #53b1af;
            border-radius: 25px;
            font-size: 16px;
            color: #fff;
        }
    }

    .number {
        width: 204px;
        height: 101px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);

        .grey {
            font-size: 16px;
            color: #707070;
        }

        .blue {
            font-size: 25px;
            color: #1a73e8;
            margin-top: 20px;
            font-weight: lighter;
        }
    }

    .content_tit {
        font-size: 16px;
        color: #1d1d1f;
        font-weight: bold;
        margin-top: 30px;
    }

    .content {
        margin-top: 10px;
        width: 672px;
        height: 331px;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
        // overflow: scroll;
        // overflow-x: hidden;

        // &::-webkit-scrollbar {
        //   width: 0px;
        //   height: 0px;
        //   background-color: rgba(240, 240, 240, 1);
        // }

        .item {
            width: 98%;
            background: #ffffff;

            .box {
                margin-left: 16px;
                border-bottom: 1px solid #e3e3e7;
            }

            .name {
                font-size: 16px;
                color: #1d1d1f;
                font-weight: bold;
                margin-top: 20px;
            }

            .time {
                font-size: 14px;
                color: #1d1d1f;
                margin: 10px 0;
            }

            .info {
                font-size: 14px;
                color: #707070;
                margin-bottom: 20px;
            }
        }
    }
</style>