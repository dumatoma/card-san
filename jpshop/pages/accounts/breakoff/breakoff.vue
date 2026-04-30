<template>
    <view class="container">
        <view class="p1 bd">
            本日解約手続きを行うと、2024年5月９日 を持ちまして契約を終了いたします。　ご解約しても契約終了日までアカウントは有効です。
        </view>
        <view class="p1">
            <view class="ptitle">
                残念ながら解約されてしまうあなたへ
            </view>
            <view class="pcontent">
                契約終了後には、今まで作成したデータ、コンテンツは全て失われてしまう可能性がございます。いかなる理由があってもデータの復旧は対応致し兼ねますので、ご了承ください。
            </view>
        </view>
        <view class="ckbox">
            <view class="cbleft" @click="checked = !checked">
                <view class="inner" v-if="checked == true"></view>
            </view>
            <view class="cbText">
                上記内容を確認した上で、解約を了承しました。
            </view>
        </view>
        <view class="btns">
            <view class="btnLeft btn">
                戻る
            </view>
            <view class="btnRight btn" :style="checked?'' : 'opacity:0.6 !important'" @click="confirm">
                解約する
            </view>
        </view>
    </view>
</template>

<script>
    import {
        delPlan
    } from "@/api/index.js"
    export default {
        data() {
            return {
                id:"",
                checked:false
            };
        },
        onLoad(options) {
            this.id = options.id
            
        },
        methods:{
            confirm(){
                let that = this
                if(that.checked == true){
                   delPlan(that.id).then((res) => {
                       if(res.code == 200){
                           uni.navigateTo({
                               url:"../complete/complete"
                           })
                       }
                   }) 
                }
                
            }
        }
    }
</script>

<style lang="scss">
    .btns{
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 96upx;
        .btn{
            width: 324rpx;
            height: 112rpx;
            font-size: 32rpx;
            font-family: Hiragino Sans, Hiragino Sans;
            border-radius: 20upx;
            text-align: center;
            line-height: 112upx;
        }
        .btnLeft{
            background: rgba(29,29,31,0.1);
            color: #1d1d1f;
        }
        .btnRight{
            background: #D93025;
            color: #fff;
        }
    }
    .ckbox{
        display: flex;
        align-items: center;
        margin-top: 190upx;
        .cbText{
            font-size: 28rpx;
            color: #1D1D1F;
        }
        .cbleft{
            width: 32rpx;
            height: 32rpx;
            opacity: 1;
            border: 2rpx solid #1A73E8;
            box-sizing: border-box;
            padding: 6upx;
            border-radius: 50%;
            margin-right: 24upx;
            .inner{
                width: 100%;
                height: 100%;
                 border-radius: 50%;
                background: #1A73E8;
            }
        }
    }
    .container {
        border-top: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding: 0 40upx;

        .bd {
            border-bottom: 2upx solid #d2d2d7;
        }

        .p1 {
            width: 100%;
            box-sizing: border-box;
            padding: 80upx 0 80upx;
            font-size: 32rpx;
            color: rgba(29, 29, 31, 0.9);

            .ptitle {
                text-align: center;
                font-size: 32rpx;
                font-weight: bold;
                color: #1D1D1F;
                margin-bottom: 80upx;
            }

            .pcontent {
                font-size: 32rpx;
                color: #D93025;
                font-weight: 400;
            }
        }
    }
</style>