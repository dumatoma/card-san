<template>
    <view class="contain">
        <view class="titleContent">
            <view class="title">
                前回来店日から
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
        
        <u-action-sheet @select="selectClick" title="日数を選択" :actions="list" @close="closeMask" :show="show"
            :closeOnClickOverlay="true" :closeOnClickAction="true"></u-action-sheet>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                current:3,
                show:false,
                status:"日数を選択",
                array:[
                    {
                        value:"以上経過",
                    },
                    {
                        value:"以内"
                    }
                ],
                list: [{
                        name: '7日',
                        id: 1,
                        value:7
                    },
                    {
                        name: '14日',
                        id: 2,
                         value:14
                    },
                    {
                        name: '30日',
                        id: 3,
                        value:30
                    },
                    {
                        name: '60日',
                        id: 4,
                        value:60
                    },
                    {
                        name: '90日',
                        value:90,
                        id: 5
                    },
                    {
                        name: '180日',
                        id: 6,
                        value:180,
                    },
                    {
                        name: '1年',
                        id: 7,
                        value:365,
                    },
                    {
                        name: '2年',
                        id: 8,
                        value:730,
                    },
                    {
                        name: '3年',
                        id: 9,
                        value:1095,
                    }
                ],
            }
        },
        onShow() {
          let that = this
          let csvData = uni.getStorageSync("csvData") 
          if(csvData.shop_time_desc == 2){
              that.current = 0
          }else if(csvData.shop_time_desc == 1){
              that.current = 1
          }else{
              that.current = 3
          }
          that.list.forEach((v,i) => {
              if(csvData.shop_time / 86400 == v.value){
                  that.status = v.name
              }
          })
        },
        methods: {
            changeTab(e){
                let that = this
                if(e == that.current){
                    that.current = 3
                    let csvData = uni.getStorageSync("csvData")
                    if(csvData){
                        csvData['shop_time_desc'] = 3
                    }else{
                        csvData = {}
                        csvData['shop_time_desc'] = 3
                    }
                    uni.setStorageSync("csvData",csvData)
                }else{
                    this.current = e
                    let csvData = uni.getStorageSync("csvData")
                    if(csvData){
                        csvData['shop_time_desc'] = e == 0?2:1
                    }else{
                        csvData = {}
                        csvData['shop_time_desc'] = e == 0?2:1
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
                    csvData['shop_time'] = e.value * 86400
                }else{
                    csvData = {}
                    csvData['shop_time'] = e.value * 86400
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
