<template>
    <view>
        <u-navbar title="各種設定" :border="true" leftIconColor="transparent" :autoBack="false" :placeholder="true" :safeAreaInsetTop="true">
        </u-navbar>
        <view class="settingItem" @click="toNext(0)">
            <view class="itemLeft">
                <view class="itemIcon">
                    <image src="../../static/svg/setting1.svg" mode="widthFix"></image>
                </view>
                <view class="itemName">
                    店舗情報
                </view>
            </view>
          <!--  <view class="itemRight warning" v-if="show==true">
                <image src="../../static/icons/notification.png" mode="widthFix"></image>
            </view> -->
        </view>
        
        <view class="settingItem" @click="toNext(1)">
            <view class="itemLeft">
                <view class="itemIcon">
                    <image src="../../static/svg/setting2.svg" mode="widthFix"></image>
                </view>
                <view class="itemName">
                    メンバーズカード作成
                </view>
            </view>
            <view class="itemRight"></view>
        </view>
        
        <view class="settingItem" @click="toNext(2)">
            <view class="itemLeft">
                <view class="itemIcon">
                    <image src="../../static/svg/setting3.svg" mode="widthFix"></image>
                </view>
                <view class="itemName">
                    デザイン設定
                </view>
            </view>
            <view class="itemRight"></view>
        </view>
        
        <view class="settingItem" @click="toNext(3)">
            <view class="itemLeft">
                <view class="itemIcon">
                    <image src="../../static/svg/setting4.svg" mode="widthFix"></image>
                </view>
                <view class="itemName">
                    会員登録情報
                </view>
            </view>
            <view class="itemRight"></view>
        </view>
        
        <view class="settingItem" @click="changeMenuStatus">
            <view class="itemLeft">
                <view class="itemIcon">
                    <image src="../../static/svg/setting5.svg" mode="widthFix"></image>
                </view>
                <view class="itemName">
                    メニュー
                </view>
            </view>
            <view class="itemRight right">
                <image :style="showMenu? 'transform:rotate(90deg)' : '' " src="../../static/icons/right.png" mode=""></image>
            </view>
        </view>
        <view class="menuContents" v-if="showMenu">
            <view class="menuItem" @click="toNext(4)">
                カテゴリーを登録
            </view>
            <view class="menuItem" @click="toNext(5)">
                メニューを登録
            </view>
            <view class="menuItem" @click="toNext(6)">
                外部リンク
            </view>
        </view>
        
        <view class="settingItem" @click="toNext(7)">
            <view class="itemLeft">
                <view class="itemIcon">
                    <image src="../../static/svg/setting6.svg" mode="widthFix"></image>
                </view>
                <view class="itemName">
                    スタッフ登録
                </view>
            </view>
            <view class="itemRight"></view>
        </view>
        
        <view class="settingItem" @click="toNext(8)" v-if="admin.admin_type == 1">
            <view class="itemLeft">
                <view class="itemIcon">
                    <image src="../../static/svg/setting7.svg" mode="widthFix"></image>
                </view>
                <view class="itemName">
                    副管理者の設定
                </view>
            </view>
            <view class="itemRight"></view>
        </view>
        <view class="settingItem" style="opacity: 0.5;" v-if="admin.admin_type != 1">
            <view class="itemLeft">
                <view class="itemIcon">
                    <image src="../../static/svg/setting7.svg" mode="widthFix"></image>
                </view>
                <view class="itemName">
                    副管理者の設定
                </view>
            </view>
            <view class="itemRight"></view>
        </view>
        <permission :show="showpermission" @close='showpermission = false'></permission>
    </view>
</template>

<script>
    import {getShopInfo,getAdminDetail } from '@/api/index.js'
    import permission from "@/components/permission/permission.vue"
    export default {
        components: {
            permission
        },
        data() {
            return {
                showMenu:false,
                admin:{},
                show:false,
                showpermission:false
            }
        },
        onShow() {
          let that = this  
          getShopInfo().then((res) => {
              if(res.code == 200){
                  that.show = res.data.shop_info.shop_cate_name == ''?true:false
              }
          })
          that.admin = uni.getStorageSync("admin")
        },
        methods: {
            toNext(e){
                let that = this
                let admin = uni.getStorageSync("admin")
                if(that.admin.admin_type == 2){
                    uni.showLoading({
                        title: "読み込み中",
                        mask:true
                    })
                    getAdminDetail(admin.id).then((res) => {
                        if(res.code == 200){
                            uni.hideLoading()
                            
                            if(e == 0){
                                if(res.data.admin.privileges.includes(9)){
                                    uni.navigateTo({
                                        url:"../settings/shopInfo/shopInfo"
                                    })
                                }else{
                                    that.showpermission = true
                                }
                            }else if(e == 1){
                                if(res.data.admin.privileges.includes(10)){
                                    uni.navigateTo({
                                        url:"../settings/membershipCard/membershipCard"
                                    })
                                }else{
                                    that.showpermission = true
                                }
                                
                            }else if(e == 2){
                                if(res.data.admin.privileges.includes(11)){
                                    uni.navigateTo({
                                        url:"../settings/store/store"
                                    })
                                }else{
                                    that.showpermission = true
                                }
                                
                            }else if(e == 3){
                                if(res.data.admin.privileges.includes(12)){
                                    uni.navigateTo({
                                        url:"../settings/vipRegister/vipRegister"
                                    })
                                }else{
                                    that.showpermission = true
                                }
                                
                            }else if(e == 4){
                                if(res.data.admin.privileges.includes(13)){
                                    uni.navigateTo({
                                        url:"../settings/menu/menu"
                                    })
                                }else{
                                    that.showpermission = true
                                } 
                            }else if(e == 5){
                                
                                if(res.data.admin.privileges.includes(13)){
                                    uni.navigateTo({
                                        url:"../menu/menu"
                                    })
                                }else{
                                    that.showpermission = true
                                } 
                                
                               
                            }else if(e == 6){
                                if(res.data.admin.privileges.includes(13)){
                                    uni.navigateTo({
                                        url:"../settings/menu/link/link"
                                    })
                                }else{
                                    that.showpermission = true
                                } 
                            }else if(e == 7){
                                if(res.data.admin.privileges.includes(14)){
                                    uni.navigateTo({
                                        url:"../settings/addStaff/addStaff"
                                    })
                                }else{
                                    that.showpermission = true
                                } 
                                
                            }else if(e == 8){
                                uni.navigateTo({
                                    url:"../settings/Administrators/Administrators"
                                })
                            }    
                        }
                    })
                }else{
                    if(e == 0){
                        uni.navigateTo({
                            url:"../settings/shopInfo/shopInfo"
                        })
                    }else if(e == 1){
                        uni.navigateTo({
                            url:"../settings/membershipCard/membershipCard"
                        })
                    }else if(e == 2){
                        uni.navigateTo({
                            url:"../settings/store/store"
                        })
                    }else if(e == 3){
                        uni.navigateTo({
                            url:"../settings/vipRegister/vipRegister"
                        })
                    }else if(e == 4){
                        uni.navigateTo({
                            url:"../settings/menu/menu"
                        })
                    }else if(e == 5){
                        uni.navigateTo({
                            url:"../menu/menu"
                        })
                    }else if(e == 6){
                        uni.navigateTo({
                            url:"../settings/menu/link/link"
                        })
                    }else if(e == 7){
                        uni.navigateTo({
                            url:"../settings/addStaff/addStaff"
                        })
                    }else if(e == 8){
                        uni.navigateTo({
                            url:"../settings/Administrators/Administrators"
                        })
                    }
                }
                
            },
            changeMenuStatus(){
                this.showMenu = !this.showMenu
            }
        }
    }
</script>

<style lang="scss">
    page{
        background: #f5f5f5;
    }
    
    .menuContents{
        background: #fff;
        
        
        .menuItem{
            box-sizing: border-box;
            padding: 0 112upx 40upx;
        }
    }
    
    .settingItem{
        background: #fff;
        display: flex;
        height: 112upx;
        justify-content: space-between;
        box-sizing: border-box;
        align-items: center;
        padding: 0 32upx;
        border-top: 2upx solid #D2D2D7;
        
        .itemLeft{
            display: flex;
            align-items: center;
            
            .itemIcon{
                width: 60upx;
                margin-right: 20upx;
                
                image{
                    width: 100%;
                }
            }
            
            .itemName{
                font-family: 'Hiragino Sans';
                font-style: normal;
                font-weight: 300;
                font-size: 32upx;
                color: #1D1D1F;
            }
        }
        
        .settingItem:last-child{
            border-bottom: 2upx solid #D2D2D7;
        }
        
        .warning{
            width: 52upx;
            height: 52upx;
            
            image{
                width: 100%;
                height: 100%;
            }
        }
        
        .right{
            width: 22upx;
            height: 44upx;
            
            image{
                width: 100%;
                height: 100%;
            }
        }
        
    }
</style>
