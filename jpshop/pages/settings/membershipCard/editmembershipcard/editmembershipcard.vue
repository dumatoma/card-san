<template>
    <view>
        <!-- 自定义顶部导航 -->
        <u-navbar title="アイコン設定" :placeholder="true" :safeAreaInsetTop="true" :border="true">
            <view class="u-nav-slot" slot="left">
               <image src="../../../../static/icons/tclose.png" style="width: 50upx;height: 50upx;" mode="" @click="goBack"></image>
            </view>
            <view class="u-nav-slot" slot="right">
                <image src="../../../../static/icons/preview.svg" style="width: 50upx;height: 50upx;" @click="showPopup = true"
                    mode=""></image>
            </view>
        </u-navbar>
        <view class="titleContent">
            <view class="title">
                カードアプリのアイコン設定
            </view>
        </view>
        <view class="item">
            <view class="itemName">
                <text>※</text>店舗名(アプリ名)
            </view>
            <u-input v-model="shopName"></u-input>
        </view>
        <view class="item">
            <view class="itemName">
                <text>※</text>カードのアイコン画像をアップロード
            </view>
            <view class="uploadImage" @click="uploadImage">
                <image class="fImage" :src="image" mode="aspectFill"></image>
                <view class="add">
                    <u-icon name="plus-circle-fill" size="40" color="rgba(29,29,31,0.3)"></u-icon>
                </view>  
            </view>
        </view>
        
        <view class="tips">
            <view class="tipItem">
                ・ファイル形式：JPG、JPEG、GIF、PDF
            </view>
            <view class="tipItem">
                ・推奨画像サイズ：1316px X 792px
            </view>
        </view>
        <u-button type="primary" text="次へ" :disabled="shopName == '' || image==''" class="btn" @click="nextStep"></u-button>
        <view style="height: 120upx;"></view>
        
        <u-popup :show="showPopup" round="32">
            <view class="pTitle">
                <view class="closeImage">
                    <image src="../../../../static/icons/tclose.png" @click="showPopup = false" mode=""></image>
                </view>
                <view class="pt">
                    プレビュー
                </view>
            </view>
            <view class="preTitle">
                <view class="iconLeft">
                    <image src="../../../../static/icons/menu1.png" mode=""></image>
                </view>
                <view class="iconMiddle">
                    CARD BOX
                </view>
                <view class="iconRight">
                    <image src="../../../../static/icons/heart.png" mode=""></image>
                </view>
            </view>
            <view class="preContent">
                <view class="preItem">
                    <view class="preImage">
                        <image style="width: 100%;height: 100%;" :src="image" mode="aspectFill"></image>
                    </view>
                    <view class="preName" v-text="shopName"></view>
                </view>
            </view>
        </u-popup>
    </view>
</template>

<script>
    
    export default {
        data() {
            return {
                showPopup:false,
                shopName:"",
                image:"",
                type:""
            }
        },
        created() {
          
        },
        onLoad(options) {
           let query = options
           this.type = query.type 
           this.shopName = decodeURIComponent(query.name)
           this.image = query.image
           this.id = query.id
        },
        methods: {
             nextStep(){
                 let obj  = {}
                 let shop_name =   encodeURIComponent(this.shopName) 
                 let image = this.image
                 uni.navigateTo({
                     url:"../editSecondStep/editSecondStep?shop_name="+shop_name+"&image="+image+"&type="+this.type+"&id="+this.id
                 })
             },
             goBack(){
               uni.navigateBack()  
             },
             uploadImage() {
                 let that = this
                 uni.chooseImage({
                     count: 1,
                     success: (res) => {
                         const tempPaths = res.tempFilePaths
                         uni.uploadFile({
                             url: that.$baseUrl+'/tool/upload/file',
                             filePath: res.tempFilePaths[0],
                             name:"file",
                             success: (result) => {
                                 if (result.errMsg == "uploadFile:ok") {
                                     let re = JSON.parse(result.data)
                                     if (re.code == 200) {
                                         that.image = re.data.path
                                     }
                                 }
                             }
                         })
                     }
                 });
             },
        }
    }
</script>

<style lang="scss">
    .u-border {
        border-width: 0.5px !important;
        border-color: #aaaaaa !important;
        border-style: solid;
    }
    .tips{
        box-sizing: border-box;
        padding: 0 46upx 42upx 48upx;
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        box-shadow: 0px 4upx 6upx 2upx rgba(0,0,0,0.16);
    }
    
    .fImage{
        width: 100%;
        height: 100%;
    }
    
    .preContent{
        box-sizing: border-box;
        padding: 80upx 66upx;
        height: 60vh;
        
        .preItem{
            width: 292upx;
            .preImage{
                width: 280upx;
                height: 170upx;
                background: #FBFBFD;
                box-shadow: 0px 0px 6upx 2upx rgba(0,0,0,0.16);
                overflow: hidden;
            }
            
            .preName{
                margin-top: 10upx;
                font-size: 24upx;
                font-family: Hiragino Sans-W6, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                text-align: center; 
            }
        }
    }
    
    
    
    .preTitle{
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2upx solid #d2d2d7;
        border-top: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding: 32upx;
        
        .iconLeft{
            width: 66upx;
            height: 48upx;
            
            image{
                width: 100%;
                height: 100%;
            }
        }
        
        .iconMiddle{
            font-size: 48upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: rgba(29,29,31,0.9);
        }
        
        .iconRight{
            width: 66upx;
            height: 60upx;
            
            image{
                width: 100%;
                height: 100%;
            }
        }
    }
    
    .pTitle{
        position:relative;
        height: 111upx;
        background: #FBFBFD;
        border-radius: 32upx;
        
        .pt{
            width: 100%;
            text-align: center;
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            line-height: 111upx;
        }
        
        .closeImage{
            width: 60upx;
            height: 60upx;
            position: absolute;
            top: 26upx;
            left: 32upx;
            
            image{
                width: 100%;
                height: 100%;
            }
        }
    }
    
    .btn{
        width: 480upx !important;
        height: 100upx;
        background: rgba(26,115,232,1);
        border-radius: 20upx;
        margin-top: 120upx;
    }
    
    .item{
        box-sizing: border-box;
        padding: 0 46upx;
        margin-bottom: 80upx;
        
        .itemName{
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-bottom: 20upx;
            
            text{
                color: #D93025 !important;
            }
        }
        
        .uploadImage{
            width: 100%;
            height: 396upx;
            background: #E4E4E4;
            box-shadow: 0px 0px 10upx 2upx rgba(0,0,0,0.16);
            border-radius: 20upx;
            position: relative;
            overflow: hidden;
            
            .add{
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%,-50%);
            }
        }
    }
    
    .titleContent{
        box-sizing: border-box;
        padding-top: 40upx;
        margin-bottom: 80upx;
        
        .title{
            height: 112upx;
            background: #F5F5F7;
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: rgba(29,29,31,0.9);
            line-height: 112upx;
            box-sizing: border-box;
            padding-left: 40upx;
        }
    }
</style>
