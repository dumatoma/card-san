<template>
    <view class="contain">
        <view class="item">
            <view class="title">
                メッセージ内容
            </view>
            <view class="content">
                テキスト or 画像・写真
            </view>
        </view>
        <view class="item">
            <view class="title">
                配信日時
            </view>
            <view class="content">
                2022/07/06 15:06
            </view>
        </view>
        <view class="item">
            <view class="title">
                配信メッセージ数
            </view>
            <view class="content">
                300
            </view>
        </view>
        <view class="item">
            <view class="title">
                配信ターゲット
            </view>
            <view class="content">
                誕生月９月
            </view>
        </view>
        <view class="item">
            <view class="title">
                編集日時
            </view>
            <view class="content">
                2022/06/06 09:16
            </view>
        </view>
        <view class="btns">
            <u-button class="btn" :plain="true" color="#1A73E8">編集</u-button>
            <u-button class="btn" :plain="true" color="#D93025">削除</u-button>
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                
            }
        },
        methods: {
            
        }
    }
</script>

<style lang="scss">
    .item{
        box-sizing: border-box;
        padding: 40upx 30upx;
        border-bottom: 2upx solid #eaeaea;
        
        .content{
           font-size: 28upx;
           font-family: Hiragino Sans-W3, Hiragino Sans;
           font-weight: normal; 
           color: #707070; 
           margin-top: 28upx;
        }
    }
    
    .btns{
        margin-top: 112upx;
        
        .btn{
            width: 480upx;
            height: 96upx;
            background: #FFFFFF;
            border-radius: 20upx;
            margin-bottom: 60upx;
        }
    }
    
</style>
