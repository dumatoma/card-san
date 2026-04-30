<template>
    <view>
        <!-- 自定义顶部导航 -->
        <u-navbar :title="name" :placeholder="true" :safeAreaInsetTop="true" bgColor="#1D1D1F" :border="true" :titleStyle="{color:'#fff'}">
            <view class="u-nav-slot" slot="left">
                <image src="../../../static/icons/wclose.png" style="width: 60upx;height: 60upx;" mode=""></image>
            </view>
            <view class="u-nav-slot" slot="right">
                <image src="../../../static/icons/delete.png" style="width: 60upx;height: 60upx;" @click="deleteImg"
                    mode=""></image>
            </view>
        </u-navbar>
        <view style="width: 100%;height: 140upx;"></view>
        <view class="contain">
            <u-swiper :autoplay="false" :list="imageArray" @change="change" keyName="img" imgMode="widthFix"
                height='455' bgColor="transparent"></u-swiper>
        </view>
        <view class="count">
            {{current}}/{{imageArray.length}}
        </view>
        <u-action-sheet :actions="list" @select="handleSelect" @close="handleClose" :closeOnClickOverlay='true' :closeOnClickAction="true" :safeAreaInsetBottom='true' :show="show"></u-action-sheet>
        <mod v-if="showModule" @getStatus="getStatus" :config="configs"></mod>
    </view>
</template>

<script>
    import mod from '@/components/mod.vue'
    export default {
        components:{
            mod
        },
        data() {
            return {
                name: "fileName",
                current: 1,
                show: false,
                configs:{},
                showModule:false,
                list: [{
                    name: "写真を削除",
                    color:'#D93025',
                }, {
                    name: 'キャンセル'
                }],
                imageArray: [{
                        img: "../../../static/images/1.jpg",
                        name:"fiel1"
                    },
                    {
                        img: "../../../static/images/1.jpg",
                        name:"fiel2"
                    },
                    {
                        img: "../../../static/images/1.jpg",
                        name:"fiel3"
                    },
                    {
                        img: "../../../static/images/1.jpg",
                        name:"fiel4"
                    },
                    {
                        img: "../../../static/images/1.jpg",
                        name:"fiel5"
                    },
                    {
                        img: "../../../static/images/1.jpg",
                        name:"fiel6"
                    }
                ]
            }
        },
        methods: {
            change(e) {
                this.current = e.current + 1
                this.name = this.imageArray[e.current].name
            },

            deleteImg() {
                let that = this
                that.show = true
            },
            handleClose(){
                this.show = false
            },
            handleSelect(e){
                let that = this 
                if(e.name = "写真を削除"){
                    that.configs = {
                        title:"この情報を削除しますか？", 
                        content:"削除すると、この情報は完全に削除されます。",
                        confirm:true,
                        cancel:true,
                        confirmText:"情報を削除",
                        cancelText:"キャンセル"
                    }
                    that.showModule = true
                }
            },
            getStatus(status){
                if(status == 'confrim'){
                    
                }else if(status == "cancel"){
                    
                }else{
                    
                }
            }
        }
    }
</script>

<style lang="scss">
    page {
        background: rgba(29, 29, 31, 0.9);
        position: relative;
        min-height: 100vh;
    }

    .count {
        font-size: 36upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #FFFFFF;
        text-align: center;
        position: absolute;
        bottom: 146upx;
        width: 100%;
    }
</style>
