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
        <!-- [カスタムエイド 2026-10-20] 見た目をこれまでの画面に合わせた(検索欄・並び替え・白い角丸のまとまりの一覧)。
             電話番号・IDの前の「・」をなくし、電話番号がないときは「ー」。削除は今までどおり左へスライド -->
        <view class="cm">
            <view class="cm-top">
                <view class="cm-search" :class="{ on: searchFocus }">
                    <view class="cm-glass"></view>
                    <input class="cm-search-in" v-model="requestData.keywords" placeholder="名前、電話番号、IDで検索" placeholder-class="cm-ph"
                        confirm-type="search" @confirm="completeInsert" @focus="searchFocus = true" @blur="searchFocus = false" />
                </view>
                <view class="cm-bar">
                    <text class="cm-count">メンバー <text class="cm-count-n">{{array.length}}</text> 人</text>
                    <view class="cm-sort" hover-class="cm-hover" @click="chooseOrder">
                        <text>{{sortName}}</text>
                        <view class="cm-sort-ic"><view class="cm-up"></view><view class="cm-down"></view></view>
                    </view>
                </view>
            </view>
            <view class="cm-list" v-if="array.length">
                <u-swipe-action autoClose>
                    <u-swipe-action-item v-for="(item, index) in array" :key="item.id" :options="options1" @click="handleClick(item)">
                        <view class="cm-row" :class="{ last: index == array.length - 1 }" hover-class="cm-row-hover" @click="toDetail(item.id)">
                            <image class="cm-avatar" :src="item.avatar || '../../static/images/avatar.png'" mode="aspectFill"></image>
                            <view class="cm-info">
                                <view class="cm-name-row">
                                    <text class="cm-name">{{item.name || '-'}}</text>
                                    <text class="cm-kana" v-if="item.pronunciation">{{item.pronunciation}}</text>
                                </view>
                                <view class="cm-tel">{{item.phone || 'ー'}}</view>
                                <view class="cm-ids">
                                    <text>顧客NO {{item.member_no || '-'}}</text>
                                    <text class="cm-id">ID {{item.member_id}}</text>
                                </view>
                            </view>
                            <view class="cm-chev"></view>
                        </view>
                    </u-swipe-action-item>
                </u-swipe-action>
            </view>
            <view class="cm-empty" v-else>メンバーはまだいません</view>
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
                searchFocus: false, // [カスタムエイド 2026-10-20]
                sortName: '五十音順', // [カスタムエイド 2026-10-20] 今の並び順の名前
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
                that.sortName = index.name // [カスタムエイド 2026-10-20]
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
    /* [カスタムエイド 2026-10-20] これまでの画面と同じ見た目 */
    .tc {
        font-size: 32rpx;
        font-weight: 700;
        color: #1A73E8;
    }

    .cm {
        min-height: 100vh;
        background: #F5F5F7;
        padding-bottom: calc(60rpx + env(safe-area-inset-bottom));
        color: #1D1D1F;
        font-family: 'Hiragino Sans', sans-serif;
    }

    .cm-top {
        padding: 28rpx 24rpx 8rpx;
    }

    .cm-search {
        display: flex;
        align-items: center;
        height: 84rpx;
        padding: 0 24rpx;
        border-radius: 24rpx;
        background: #FFFFFF;
        border: 1px solid #D2D2D7;
        box-sizing: border-box;
    }

    .cm-search.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    /* 虫めがね */
    .cm-glass {
        position: relative;
        width: 26rpx;
        height: 26rpx;
        flex-shrink: 0;
        margin-right: 18rpx;
        border: 3rpx solid #8A8A8E;
        border-radius: 50%;
        box-sizing: border-box;
    }

    .cm-glass::after {
        content: '';
        position: absolute;
        right: -9rpx;
        bottom: -7rpx;
        width: 11rpx;
        height: 3rpx;
        background: #8A8A8E;
        transform: rotate(45deg);
    }

    .cm-search-in {
        flex: 1;
        height: 84rpx;
        font-size: 28rpx;
    }

    .cm-ph {
        color: #8A8A8E;
    }

    .cm-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24rpx 8rpx 8rpx;
    }

    .cm-count {
        font-size: 26rpx;
        color: #515154;
    }

    .cm-count-n {
        font-size: 30rpx;
        font-weight: 700;
        color: #1D1D1F;
    }

    .cm-sort {
        display: flex;
        align-items: center;
        height: 60rpx;
        padding: 0 20rpx 0 24rpx;
        border-radius: 30rpx;
        background: #FFFFFF;
        border: 1px solid #D2D2D7;
        font-size: 24rpx;
        color: #1D1D1F;
        box-sizing: border-box;
    }

    .cm-sort-ic {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-left: 12rpx;
    }

    .cm-up,
    .cm-down {
        width: 0;
        height: 0;
        border-left: 7rpx solid transparent;
        border-right: 7rpx solid transparent;
    }

    .cm-up {
        border-bottom: 9rpx solid #1A73E8;
        margin-bottom: 3rpx;
    }

    .cm-down {
        border-top: 9rpx solid #1A73E8;
    }

    .cm-hover {
        opacity: 0.6;
    }

    .cm-list {
        margin: 12rpx 24rpx 0;
        background: #FFFFFF;
        border-radius: 32rpx;
        overflow: hidden;
    }

    .cm-row {
        display: flex;
        align-items: center;
        padding: 28rpx 28rpx 28rpx 32rpx;
        background: #FFFFFF;
        position: relative;
    }

    .cm-row::after {
        content: '';
        position: absolute;
        left: 32rpx; /* [カスタムエイド 2026-10-21] 区切り線を写真の左端から(以前は名前の列から) */
        right: 0;
        bottom: 0;
        height: 1px;
        background: #E5E5EA;
    }

    .cm-row.last::after {
        display: none;
    }

    .cm-row-hover {
        background: #F5F5F7;
    }

    .cm-avatar {
        width: 92rpx;
        height: 92rpx;
        flex-shrink: 0;
        border-radius: 50%;
        background: #E5E5EA;
        margin-right: 28rpx;
    }

    .cm-info {
        flex: 1;
        min-width: 0;
    }

    .cm-name-row {
        display: flex;
        align-items: baseline;
        margin-bottom: 4rpx;
    }

    .cm-name {
        font-size: 32rpx;
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cm-kana {
        margin-left: 14rpx;
        font-size: 22rpx;
        color: #8A8A8E;
        white-space: nowrap;
    }

    .cm-tel {
        font-size: 28rpx;
        color: #3A3A3C;
        margin-bottom: 4rpx;
    }

    .cm-ids {
        font-size: 24rpx;
        color: #8A8A8E;
    }

    .cm-id {
        margin-left: 24rpx;
    }

    /* 右向きの矢印 */
    .cm-chev {
        width: 14rpx;
        height: 14rpx;
        flex-shrink: 0;
        margin-left: 16rpx;
        border-right: 3rpx solid #C7C7CC;
        border-bottom: 3rpx solid #C7C7CC;
        transform: rotate(-45deg);
    }

    .cm-empty {
        padding: 160rpx 0;
        text-align: center;
        font-size: 28rpx;
        color: #8A8A8E;
    }

    .cm-hint {
        margin-top: 20rpx;
        text-align: center;
        font-size: 22rpx;
        color: #8A8A8E;
    }
</style>
