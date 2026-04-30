<template>
    <view>
        <!-- 自定义顶部导航 -->
        <u-navbar title="メンバー管理" :placeholder="true" :safeAreaInsetTop="true" :border="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../static/icons/tclose.png" style="width: 50upx;height: 50upx;" mode="" @click="bac"></image>
            </view>
            <view class="u-nav-slot" slot="right">
                <text @click="toCsv" class="tc">xls</text>
            </view>
        </u-navbar>
        <view class="searchContent">
            <view class="search">
                <u--input placeholder="名前、電話番号、IDで検索" prefixIcon="search"
                    prefixIconStyle="font-size: 22px;color: #909399" v-model="requestData.keywords" @confirm="completeInsert"></u--input>
            </view>
            <view class="order">
                <image src="../../static/icons/order.png" mode="" @click="chooseOrder"></image>
                <text v-text="`メンバー数:${array.length}`"></text>
            </view>
        </view>
        <view class="listContent">
            <u-swipe-action autoClose>
                <u-swipe-action-item v-for="(item,index) in array" :options="options1"  @click="handleClick(item)">
                    <view class="swipe-action u-border-top u-border-bottom">
                        <view class="swipe-action__content">
                            <view class="itemContent"  @click="toDetail(item.id)">
                                <view class="itemLeft">
                                    <image style="border-radius: 50%;" :src="item.avatar || '../../static/images/avatar.png'" mode="aspectFill"></image>
                                </view>
                                <view class="itemRight">
                                    <view class="userName" v-text="item.name"></view>
                                    <view class="tel" v-text="`${item.pronunciation}・${item.phone}`"></view>
                                    <view class="userId" v-if="item.member_no" v-text="`顧客NO:${item.member_no}・ID:${item.member_id}`"></view>
                                    <view class="userId" v-else v-text="`顧客NO:-・ID:${item.member_id}`"></view>
                                </view>
                            </view>
                        </view>
                    </view>
                </u-swipe-action-item>
            </u-swipe-action>
        </view>
        <u-action-sheet @select="selectClick" :actions="list" @close="closeMask" :show="show"
            :closeOnClickOverlay="true" :closeOnClickAction="true"></u-action-sheet>
        <mod v-if="showModule" @getStatus="getStatus" :config="configs"></mod>
    </view>
</template>

<script>
    import {
        getMemberList,delMember
    } from "../../api/index.js";
    import mod from "@/components/mod.vue"
    export default {
        components:{
            mod
        },
        data() {
            return {
                showModule:false,
                configs:{},
                ae:{},
                options1: [{
                    text: '削除',
                    style: {
                        backgroundColor: '#D93025'
                    }
                }],
                show: false,
                list: [{
                        name: '五十音順',
                        id: 1
                    },
                    {
                        name: '来店が新しい順',
                        id: 2
                    },
                    {
                        name: '来店が古い順',
                        id: 3
                    }
                ],
                array: [],
                requestData:{
                    page:1,
                    page_size:50,
                    keywords:"",
                    sort:1,
                    desc:1,
                }
            }
        },
        created() {
          this.getList()
        },
        onPullDownRefresh() {
            this.array = []
            this.getList()
        },
        methods: {
            handleClick(e){
                this.ae = e
                let obj = {}
                obj.title = e.name+'の情報を削除しますか？'
                obj.content = '削除すると、この顧客情報は完全に削除されます。'
                obj.confirm = true
                obj.cancel = true
                obj.confirmText = "情報を削除"
                obj.cancelText = "キャンセル"
                this.configs = obj
                this.current = e
                this.showModule = true
            },
            // 获取列表
            getList(){
                let that = this
                getMemberList(that.requestData).then((res)=>{
                    uni.stopPullDownRefresh()
                    if(res.code == 200){
                        that.array = res.data.members
                    }
                })
            },
            getStatus(e){
                if(e == "confirm"){
                    this.del(this.ae)
                }else{
                    this.showModule = false
                }
            },
            
            // 输入完成
            completeInsert(e){
               let that = this
               that.getList()
            },
            
            // 删除会员
            del(e){
               let that = this
               delMember(e).then((res) => {
                   if(res.code == 200){
                       uni.showToast({
                           title:res.message
                       })
                       that.getList()
                       this.showModule = false
                   }
               })
            },
            
            // 返回
            bac(){
               uni.switchTab({
                   url:"../index/index"
               })
            },
            
            chooseOrder() {
                let that = this
                that.show = true
            },
            selectClick(index) {
                let that = this
                if(index.id == 1){
                    that.requestData.sort = 1
                    that.requestData.desc = 1
                    that.page = 1
                    that.getList()
                }else if(index.id == 2){
                    that.requestData.sort = 2
                    that.requestData.desc = 2
                    that.page = 1
                    that.getList()
                }else if(index.id == 3){
                    that.requestData.sort = 2
                    that.requestData.desc = 1
                    that.page = 1
                    that.getList()
                }
            },
            closeMask() {
                this.show = false
            },
            toCsv(){
                uni.navigateTo({
                    url:"csv/csv"
                })
            },
            toDetail(e){
                uni.navigateTo({
                    url:"../customer/detail/detail?id="+e
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    .u-page {
        padding: 0;
    }

    .listContent {
        box-sizing: border-box;
        padding: 5upx 32upx;
    }

    .u-demo-block__title {
        padding: 10upx 0 2upx 15upx;
    }

    .swipe-action {
        &__content {
            padding: 25rpx 0;

            &__text {
                font-size: 15px;
                color: $u-main-color;
                padding-left: 30rpx;
            }
        }
    }

    .userName {
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
    }

    .tel {
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
    }

    .userId {
        font-size: 28upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
    }

    .itemLeft {
        width: 80upx;
        height: 80upx;
        margin-right: 20upx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .itemContent {
        display: flex;
        align-items: center;
    }

    .tc {
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: #1A73E8;
    }

    .searchContent {
        width: 100%;
        height: 240upx;
        box-shadow: 0px 0px 0px 2upx rgba(0, 0, 0, 0.3);
        border-top: 2upx solid #FBFBFD;
        box-sizing: border-box;
        padding: 50upx 32upx 0;
    }

    .search {
        background: rgba(118, 118, 128, 0.12);
        border-radius: 12upx;
        overflow: hidden;
    }

    .order {
        display: flex;
        align-items: center;
        margin-top: 50upx;

        image {
            width: 60upx;
            height: 60upx;
            margin-right: 20upx;
        }
    }
</style>
