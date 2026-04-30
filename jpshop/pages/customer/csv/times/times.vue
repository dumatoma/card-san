<template>
    <view class="contain">
        <view class="titleContent">
            <view class="title">
               来店回数
            </view>
            <view class="choose" @click="opens">
                <view v-text="status"></view>
                <image class="arrow" src="../../../../static/images/awd.svg" mode=""></image>
            </view>
        </view>
        
        <view class="content">
            <view class="item" v-for="(item,index) in array" @click="changeTab(index)">
                <view class="itemLeft">
                    <view class="unselect" v-if="current != index"></view>
                    <view class="selected" v-if="current == index">
                        <view class="fill"></view>
                    </view>
                </view>
                <view class="itemRight" :style="current == index? 'color:#1D1D1F;' : 'color: #AAAAAA' "
                    v-text="item.value"></view>
            </view>
        </view>
        
        <u-action-sheet @select="selectClick" title="回数を選択" :actions="list" @close="closeMask" :show="show"
            :closeOnClickOverlay="true" :closeOnClickAction="true"></u-action-sheet>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                current:0,
                show:false,
                status:"回数を選択",
                array:[
                    {
                        value:"以上"
                    },
                    {
                        value:"以下"
                    }
                ],
                list: [{
                        name: '1回',
                        id: 1
                    },
                    {
                        name: '2回',
                        id: 2
                    },
                    {
                        name: '3回',
                        id: 3
                    },
                    {
                        name: '4回',
                        id: 4
                    },
                    {
                        name: '5回',
                        id: 5
                    },
                    {
                        name: '6回',
                        id: 6
                    },
                    {
                        name: '7回',
                        id: 7
                    },
                    {
                        name: '8回',
                        id:8
                    },
                    {
                        name: '9回',
                        id: 9
                    },
                    {
                        name: '10回',
                        id: 10
                    }
                ],
            }
        },
       onShow() {
         let that = this
         let csvData = uni.getStorageSync("csvData")  
         if(csvData.shop_count_desc == 2){
             that.current = 0
         }else if(csvData.shop_count_desc == 1){
             that.current = 1
         }else{
             that.current = 3
         }
         that.list.forEach((v,i) => {
             if(csvData.shop_count == v.id){
                 that.status = v.name
             }
         })
       },
        methods: {
            changeTab(e){
                let that = this
                if(that.current == e){
                    that.current = 3
                    let csvData = uni.getStorageSync("csvData")
                    if(csvData){
                        csvData['shop_count_desc'] = 3
                    }else{
                        csvData = {}
                        csvData['shop_count_desc'] = 3
                    }
                     uni.setStorageSync("csvData",csvData)
                }else{
                    that.current = e
                    let csvData = uni.getStorageSync("csvData")
                    if(csvData){
                        csvData['shop_count_desc'] = e == 0?2:1
                    }else{
                        csvData = {}
                        csvData['shop_count_desc'] = e == 0?2:1
                    }
                     uni.setStorageSync("csvData",csvData)
                }
                
            },
            closeMask(){
                this.show = false
            },
            opens(){
                this.show = true
            },
            selectClick(e){
                let csvData = uni.getStorageSync("csvData")
                if(csvData){
                    csvData['shop_count'] = e.id
                }else{
                    csvData = {}
                    csvData['shop_count'] = e.id
                }
                uni.setStorageSync("csvData",csvData)
                this.status = e.name
            }
        }
    }
</script>

<style lang="scss">
    .contain{
        border-top: 2upx solid #D2D2D7;
        
        .titleContent{
            box-sizing: border-box;
            padding: 60upx 40upx 80upx;
            
            .title{
                font-size: 32upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
            }
            
            .choose{
                width: 400upx;
                height: 80upx;
                background: rgba(249,249,249,0.94);
                border-radius: 20upx;
                opacity: 1;
                border: 2upx solid #AAAAAA;
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-sizing: border-box;
                padding: 0 20upx;
                font-size: 32upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;
                margin-top: 20upx;
                
                .arrow{
                    width: 24upx;
                    height: 24upx;
                    
                    image{
                        width: 100%;
                        height: 100%;
                    }
                }
            }
        }
        
        .item {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 28upx 38upx;
            border-bottom: 2upx solid #EAEAEA;
        
            .itemLeft {
                margin-right: 28upx;
            }
        
            .unselect {
                width: 40upx;
                height: 40upx;
                background: #FFFFFF;
                opacity: 1;
                box-sizing: border-box;
                border: 2upx solid #707070;
                border-radius: 50%;
            }
        
            .selected {
                width: 40upx;
                height: 40upx;
                background: #FFFFFF;
                border: 2upx solid #1A73E8;
                border-radius: 50%;
                box-sizing: border-box;
                padding: 6upx;
        
                .fill {
                    width: 100%;
                    height: 100%;
                    background: #1A73E8;
                    border-radius: 50%;
                }
            }
        }
    }
    
    
</style>
