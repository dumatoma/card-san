<template>
    <view>
        <view class="content border_top">
            <view class="nowMail">
                <view class="now1">
                    現在のID(メールアドレス)
                </view>
                <view class="now2" v-text="query.mail"></view>
            </view>
            
            <view class="details">
               変更後のメールアドレスをご入力の上、「メールアドレス(ID)の変更」をクリックしてください。
            </view>
            
            <view class="details">
                <text style="font-weight: bold;">6桁の「メールアドレス変更認証コード」</text>を記載した Eメールをお送りします。
            </view>
            
            <view class="inp">
                <view class="inpName">
                    新しいメールアドレス(ID)を入力
                </view>
                <u-input placeholder="新しいメールアドレスを入力" v-model="mail"></u-input>
            </view>
            <u-button :disabled="mail == ''" :loading="logstatus" type="primary" class="bt" @click="sendCode">メールアドレス(ID) の変更</u-button>
        </view>
        <confirmMail v-if="show == true" @success="changeSuccess" :email="mail" class="mask"></confirmMail>
    </view>
</template>

<script>
    import {changeMail} from "@/api/index.js"
    import confirmMail from "@/components/confirmMail/confirmMail.vue"
    export default {
        components:{confirmMail},
        data() {
            return {
                query:{},
                mail:"",
                show:false,
                logstatus:false
            };
        },
        onLoad(options) {
            this.query = options
        },
        methods:{
            sendCode(){
                let that = this
                that.logstatus = true
                let data = {}
                data.email = that.mail
                data.request_type = 1
                changeMail(data).then((res) => {
                    that.logstatus = false
                    if(res.code == 200){
                        that.show = true
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
            },
            changeSuccess(){
                let that = this
                let pages = getCurrentPages();
                let currPage = pages[pages.length - 1] //当前页面
                let prePage = pages[pages.length - 2] //上一个页面
                //调用上一页拉取数据的方法
                if (prePage.route == "pages/accounts/admin/admin") {
                    prePage.$vm.getOwnerInformation()
                }
                uni.navigateBack({})
            }
        }
    }
</script>

<style lang="scss">
    .mask{
        position: fixed;
        top: 0;
        left: 0;
    }
    .content{
        box-sizing: border-box;
        padding: 80upx 46upx 0;
        
        .bt{
            width: 612upx;
            height: 100upx;
            // background: rgba(26,115,232,1);
            border-radius: 50upx;
            margin-top: 318upx;
        }
        
        .inp{
            margin-top: 100upx;
            .inpName{
                font-size: 32upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                color: #707070;
                margin-bottom: 8upx;
            }
        }
        
        .details{
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-top: 100upx;
        }
        
        .nowMail{
            background: #F5F5F7;
            border-radius: 20upx;
            box-sizing: border-box;
            padding: 32upx;
            
            
            
            .now1{
                font-size: 32upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;
                margin-bottom: 32upx;
            }
            
            .now2{
                font-size: 40upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }
        }
    }
</style>
