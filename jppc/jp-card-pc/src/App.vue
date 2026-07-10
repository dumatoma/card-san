<template>
    <div id="app">
        <el-container>
            <div style="height: 114px;background: red;" v-if="main_swiper == true"></div>
            <el-header height="114px" style="position: fixed;left: 0;top: 0;width: 100vw;z-index: 99 !important;"
                v-if="main_swiper == true">
                <top></top>
                <header-view @index="value" :ind="index1" v-if="main_swiper == true"></header-view>
            </el-header>
            <el-container>
                <el-aside v-if="main_swiper == true" width="225px">
                    <sidebar :current="index" :num="hui" :yueNum="hui1"></sidebar>
                </el-aside>
                <!-- <keep-alive :include="FirstStep,editCard">
                     
                 </keep-alive> -->
                <el-main v-if="main_swiper == true" style="overflow-y: auto;height: calc(100vh-164px) !important;">
              
                    <router-view />
                </el-main>
                
                <router-view v-if="main_swiper == false" />
            </el-container>
            <el-footer v-if="main_swiper == true" height="50px">
                <foot></foot>
            </el-footer>
        </el-container>
        <!-- 保存弹窗 -->
        <div class="fixed_notice" v-show="keepShow">
            <div class="end_notice">
                <div class="end_tit u-flex-center">無料お試し期間終了のお知らせ</div>
                <div class="end_cont">
                    無料お試し期間が終了しました。<br />
                    現在アプリの操作、更新が出来ない状態となっております。
                </div>
                <div style="width: 506px; margin: 0 auto">
                    このままアプリをご利用の場合は、お申し込み手続きをお願いします。　お申し込み手続き完了後、すぐにご利用できます。
                </div>
                <img class="end_img" src="@/static/index/topOne.png" alt="" />
                <div class="end_btn u-flex-center">お申し込みはこちら</div>
            </div>
        </div>
        <!-- 计划弹窗 -->
        <div class="fixed_notice" v-show="planShow">
            <div class="plan_criteria">
                <div class="plan_tit u-flex-center">ご利用プランの選択</div>
                <div class="plan_cont u-flex-center">
                    現在のプラン：
                    <div style="font-weight: bold">スタンダード</div>
                </div>
                <div style="width: 506px; margin: 0px auto">
                    <div style="text-align: left">
                        無料お試し期間中は、全てのプラン、オプションが無料でご利用いただけます。
                        ご自身のビジネスに合うプランをお試しください。
                    </div>
                    <div class="u-flex m-t-20" style="text-align: left">
                        ご利用プランは
                        「アカウント」→「ご利用プラン」からいつでも変更ができますので、ご安心ください。
                    </div>
                </div>
                <div class="plan_btn u-flex-center plan_grey">
                    このままスタンダードプランを試す
                </div>
                <div class="plan_btn u-flex-center">プランを確認・選択</div>
            </div>
        </div>
        <!-- メンバーズカードアプリ  会员卡应用程序 -->
        <div class="fixed_notice" v-if="programShow">
            <div class="program no-size">
                <div class="u-flex-center pro_tit">
                    <img src="@/static/index/dlogo.png" style="width: 200px" />

                </div>
                <div class="pro_two">
                    お手続きありがとうございます<br />お申し込みが完了しました
                </div>
                <div class="pro_three">
                    ご登録のEメールアドレスに「お申し込み完了」のEメールを送信しましたのでご確認ください。　万が一、メールが届いていない場合は、迷惑メールフォルダをご確認お願いします。
                </div>
                <div class="pro_four">
                    <div class="u-flex">
                        <div class="pro_left">ID:</div>
                        <div class="pro_right">cardsan0706@example.com</div>
                    </div>
                    <div class="u-flex m-t-10">
                        <div class="pro_left">ご利用プラン:</div>
                        <div class="pro_right">スタンダード</div>
                    </div>
                    <div class="u-flex m-t-10">
                        <div class="pro_left">オプション:</div>
                        <div class="pro_right">クーポン機能</div>
                    </div>
                    <div class="u-flex m-t-10">
                        <div class="pro_left">支払い周期:</div>
                        <div class="pro_right">6ヶ月</div>
                    </div>
                    <div class="u-flex m-t-10">
                        <div class="pro_left">支払い方法:</div>
                        <div class="pro_right">クレジットカード</div>
                    </div>
                </div>
                <div class="pro_five">
                    ご利用プラン、オプション、支払い周期、支払い方法は後で変更できます。
                </div>
                <div class="pro_btn u-flex-center shou">TOP画面に戻る</div>
            </div>
        </div>
    </div>
</template>
<script>
    import top from "./components/header.vue";
    import sidebar from "./components/sidebar.vue";
    import foot from "./components/footer.vue";
    import headerView from "./components/headerView.vue";
    import {
        getConfig,
        getMessageList
    } from "@/http/api.js"
    export default {
        components: {
            top,
            sidebar,
            foot,
            headerView,
        },
        data() {
            return {
                main_swiper: false,
                index: 0,
                hui: 0,
                hui1: 0,
                keepShow: false,
                planShow: false,
                programShow: false,
                index1:0
            };
        },
        created() {
            // localStorage.setItem('ale', 0)
            getConfig().then((res) => {
                if (res.code == 200) {
                    // alert(res.data.config.timestamp)
                    localStorage.setItem('timestamp', res.data.config.timestamp)
                }
            })
            if (
                this.$route.name === "deputyAdmin" ||
                this.$route.name === "register" ||
                this.$route.name === "login" ||
                this.$route.name === "code" ||
                this.$route.name === "phone"
            ) {
                this.main_swiper = false;
            } else {
                this.main_swiper = true;
            }
            
            setInterval(() => {
                // this.getUnreadnumber()
            },1000)
            
            
        },
        watch: {
            // 如果路由有变化，会再次执行该方法
            $route: "fetchDate",
        },
        mounted() {
            this.initWebSocket();
        },
        methods: {
            getUnreadnumber(){
               let that = this
               let data = {}
               data['type'] = "list"
               data['uid'] = 0
               data['mid'] = 0
               data['timestamp'] = 0
                getMessageList(data).then((res) => {
                    console.log('appunread',res)
                    let num = 0
                    if(res.data.messages.length > 0){
                      res.data.messages.forEach((val,index) => {
                          num += val.unread_num
                      })
                      localStorage.setItem("unreadnum", num)          
                      // that.list[3].current = num  
                    }
                    
                })  
            },
            initWebSocket() {
                console.log('链接socket中')
                setTimeout(() => {
                    let token = localStorage.getItem("token")
                    if(token){
                        this.$socketPublic.dispatch('webSocketInit'); //初始化ws
                    }else{
                        this.initWebSocket();
                    }
                },1000)  
            },
            fetchDate() {
                console.log("name",this.$route.name)
                if(this.$route.name == 'home'){
                    this.index1 = 0
                }else if(this.$route.name == 'storeInfo'){
                    this.index1 = 1
                }else if(this.$route.name == 'user'){
                    this.index1 = 2
                }else if(this.$route.name == 'message'){
                    this.index1 = 3
                }else if(this.$route.name == 'appointment'){
                    this.index1 = 4
                }
                
                
                if (
                    this.$route.name === "deputyAdmin" ||
                    this.$route.name === "register" ||
                    this.$route.name === "login" ||
                    this.$route.name === "code" ||
                    this.$route.name === "phone"
                ) {
                    this.main_swiper = false;
                } else {
                    this.main_swiper = true;
                }
                
              
                
            },
            value(i) {
                this.index = i.index;
                this.hui = Number(i.hui);
                this.hui1 = Number(i.yueHui);
            }
        },
    };
</script>
<style>
    .nqbt {
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
    
    .nqbt:hover{
        background: rgba(26, 115, 232, 0.6) !important;
    }
    
    .nqbt2{
        width: 265px;
        height: 48px;
        background: rgba(29,29,31,0.2);
        border-radius: 10px;
        text-align: center;
        line-height: 48px;
        font-size: 16px;
        color: #707070;
        cursor: pointer;
        margin-top: 30px;
    }
    
    .nqbt:hover{
        opacity: 0.6 !important;
    }
    
    .nqbt2:hover{
        opacity: 0.6 !important;
    }
    
    .nqmask {
        width: 100vw;
        height: 100vh;
        background: rgba(29, 29, 31, 0.5);
        position: fixed;
        left: 0;
        top: 0;
        z-index: 109 !important;
    }
    
    .nqmaskContent {
        width: 345px;
        /* height: 263px; */
        background: #FFFFFF;
        border-radius: 10px 10px 10px 10px;
        box-sizing: border-box;
        padding: 40px;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%); 
    }
    .nqmaskDetail {
        font-size: 14px;
        color: #1D1D1F;
        margin: 30px 0;
        text-align: center;
    }
    
    .nqmaskTitle {
        font-size: 16px;
        color: #D93025;
        text-align: center;
    }
    
    
    ::v-deep .el-input__inner::-webkit-input-placeholder {
      color: #aaa !important; /* 你想要的颜色 */
    }
    ::v-deep .el-input__inner:-moz-placeholder {
      color: #aaa !important; /* 兼容Firefox 4-18 */
    }
    ::v-deep .el-input__inner::-moz-placeholder {
      color: #aaa !important; /* 兼容Firefox 19+ */
    }
    ::v-deep .el-input__inner:-ms-input-placeholder {
      color: #aaa !important; /* 兼容IE 10+ */
    }
    .el-main{
        height: calc(100vh - 164px);
    }
    .inlie1 {
        display: -webkit-box;
        text-overflow: ellipsis;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 1;
    }
    .fixed{
        z-index: 199 !important;
    }
    input,
    textarea {
        font-size: 14px !important;
    }

    .inlie2 {
        display: -webkit-box;
        text-overflow: ellipsis;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    input[type="search"]::-webkit-search-cancel-button {
        -webkit-appearance: none;
    }

    * {
        padding: 0;
        margin: 0;
    }

    .el-header {
        padding: 0;
    }

    .el-main {
        padding-left: 50px;
        padding-top: 26px;
        background-color: #fbfbfd;
    }

    .el-footer {
        padding: 0;
        margin: 0;
    }

    .el-message {
        min-width: 20px !important;
    }
</style>
<style lang="scss">
    /* 针对Webkit内核（如Chrome、Safari） */
    ::-webkit-scrollbar {
        width: 8px; /* 设置滚动条宽度 */
    }
     
    ::-webkit-scrollbar-track {
        background-color: #f5f5f5 !important; /* 设置背景色 */
    }
     
    ::-webkit-scrollbar-thumb {
        background-color: #888; /* 设置滑块颜色 */
        border-radius: 4px !important;
        height: 50px !important;
    }
     
    ::-webkit-scrollbar-thumb:hover {
        background-color: #555 !important; /* 当鼠标悬停在滑块上时的颜色 */
        cursor: pointer !important;
    }
     
    /* 针对Firefox内核 */
    // * {
    //     scrollbar-width: thin; /* 设置滚动条宽度为默认值 */
    //     scrollbar-color: #888 transparent; /* 设置滑块颜色及背景色 */
    // }
    .shou {
        &:hover {
            cursor: pointer;
            opacity: 0.6;
        }
    }

    .no-size {
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        -o-box-sizing: border-box;
        -ms-box-sizing: border-box;
        box-sizing: border-box;
    }

    #app {
        font-family: Avenir, Helvetica, Arial, sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        color: #2c3e50;
    }

    nav {
        padding: 30px;

        a {
            font-weight: bold;
            color: #2c3e50;

            &.router-link-exact-active {
                color: #42b983;
            }
        }
    }

    .fixed_notice {
        position: fixed;
        top: 0%;
        left: 0%;
        width: 100vw;
        height: 100vh;
        z-index: 99;
        background: rgba(29, 29, 31, 0.2);

        .end_notice {
            width: 586px;
            height: 410px;
            background: #ffffff;
            box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;

            .end_tit {
                height: 60px;
                font-size: 20px;
                color: #d93025;
                background: rgba(217, 48, 37, 0.1);
            }

            .end_cont {
                width: 506px;
                font-size: 16px;
                text-align: center;
                margin: 0 auto;
                margin-top: 40px;
                line-height: 45px;
                padding-bottom: 16px;
            }

            .end_yellow {
                margin-top: 35px;

                .bg_yellow {
                    color: #da3a23;
                    background: #ffeb00;
                }
            }

            .end_img {
                width: 10px;
                height: 30px;
                margin-top: 20px;
            }

            .end_btn {
                width: 291px;
                height: 48px;
                font-size: 16px;
                margin: 0 auto;
                margin-top: 18px;
                color: #ffffff;
                background: #1a73e8;
                border-radius: 24px;
            }
        }

        .plan_criteria {
            width: 586px;
            height: 480px;
            background: #fff;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);

            .plan_tit {
                width: 586px;
                height: 60px;
                font-size: 20px;
                color: #1d1d1f;
                font-weight: bold;
                background: rgba(143, 168, 248, 0.1);
            }

            .plan_cont {
                font-size: 20px;
                padding: 40px 0;
                color: #1d1d1f;
            }

            .plan_btn {
                width: 291px;
                height: 48px;
                color: #fff;
                margin: 0 auto;
                margin-top: 30px;
                background: #1a73e8;
                border-radius: 10px;
            }

            .plan_grey {
                font-size: 16px;
                font-weight: bold;
                margin-top: 50px;
                color: rgba(29, 29, 31, 0.9);
                background: rgba(29, 29, 31, 0.1);
            }
        }

        .program {
            width: 640px;
            background: #ffffff;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            padding: 30px 120px 0px;
            box-shadow: 0px 20px 40px 1px rgba(0, 0, 0, 0.07);

            .pro_tit {
                margin: 0 auto;

                .tit_grey {
                    font-size: 11px;
                    color: #1d1d1f;
                }

                .tit_black {
                    font-size: 30px;
                }
            }

            .pro_two {
                font-size: 20px;
                color: #1d1d1f;
                font-weight: bold;
                margin-top: 39px;
                line-height: 28px;
            }

            .pro_three {
                width: 400px;
                margin: 0 auto;
                margin-top: 40px;
                font-size: 14px;
                color: #707070;
            }

            .pro_four {
                width: 400px;
                padding: 20px 0;
                font-size: 12px;
                margin: 30px auto;
                border-top: 1px solid #d2d2d7;
                border-bottom: 1px solid #d2d2d7;

                .pro_left {
                    color: #707070;
                    margin-right: 10px;
                }

                .pro_right {
                    color: #1d1d1f;
                }
            }

            .pro_five {
                width: 400px;
                font-size: 12px;
                color: #707070;
                text-align: left;
                margin: 30px auto;
            }

            .pro_btn {
                width: 263px;
                height: 48px;
                margin: 50px auto;
                border-radius: 50px;
                font-size: 16px;
                color: #707070;
                background: rgba(0, 0, 0, 0.1);
                border: 1px solid #707070;
            }
        }
    }

    .u-flex {
        /* #ifndef APP-NVUE */
        display: flex;
        /* #endif */
        flex-direction: row;
        align-items: center;
    }

    .u-flex-center {
        /* #ifndef APP-NVUE */
        display: flex;
        /* #endif */
        flex-direction: row;
        align-items: center;
        justify-content: center;
    }

    .u-flex-wrap {
        flex-wrap: wrap;
    }

    .u-flex-nowrap {
        flex-wrap: nowrap;
    }

    .u-col-center {
        align-items: center;
    }

    .u-col-top {
        align-items: flex-start;
    }

    .u-col-bottom {
        align-items: flex-end;
    }

    .u-row-center {
        justify-content: center;
    }

    .u-row-left {
        justify-content: flex-start;
    }

    .u-row-right {
        justify-content: flex-end;
    }

    .u-row-between {
        justify-content: space-between;
    }

    .u-row-around {
        justify-content: space-around;
    }

    .u-text-left {
        text-align: left;
    }

    .u-text-center {
        text-align: center;
    }

    .u-text-right {
        text-align: right;
    }

    .u-flex-col {
        /* #ifndef APP-NVUE */
        display: flex;
        /* #endif */
        flex-direction: column;
    }

    .m-t-10 {
        margin-top: 10px;
    }

    .m-t-20 {
        margin-top: 20px;
    }

    .m-t-30 {
        margin-top: 30px;
    }

    .m-t-40 {
        margin-top: 40px;
    }

    .m-t-50 {
        margin-top: 50px;
    }

    .m-t-60 {
        margin-top: 60px;
    }

    .m-r-10 {
        margin-right: 10px;
    }

    .m-r-20 {
        margin-right: 20px;
    }

    .m-r-50 {
        margin-right: 50px;
    }

    .m-l-10 {
        margin-left: 10px;
    }

    .m-l-30 {
        margin-left: 30px;
    }

    .m-l-50 {
        margin-left: 50px;
    }

    .m-l-60 {
        margin-left: 60px;
    }

    .m-b-10 {
        margin-bottom: 10px;
    }

    .m-b-40 {
        margin-bottom: 40px;
    }
</style>