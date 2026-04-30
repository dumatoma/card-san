<template>
    <view class="contain">
        <view class="title">
            <view>
                誕生月を選択
            </view>
            <view class="fu">
                ※複数選択可
            </view>
        </view>
        <view class="content">
            <view class="item" v-for="(item,index) in array"  @click="changeTab(index)">
                <view class="itemLeft">
                    <view class="unselect" v-if="!item.checked"></view>
                    <view class="selected" v-if="item.checked">
                        <view class="fill"></view>
                    </view>
                </view>
                <view class="itemRight" :style="item.checked? 'color:#1D1D1F;' : 'color: #AAAAAA' " v-text="item.value"></view>
            </view>
        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                current:0,
                str:"",
                array:[
                    {
                        value:"1月",
                        month:1,
                        checked:false
                    },
                    {
                        value:"2月",
                        checked:false,
                        month:2,
                    },
                    {
                        value:"3月",
                        checked:false,
                        month:3,
                    },
                    {
                        value:"4月",
                        checked:false,
                        month:4,
                    },
                    {
                        value:"5月",
                        checked:false,
                        month:5,
                    },
                    {
                        value:"6月",
                        checked:false,
                        month:6,
                    },
                    {
                        value:"7月",
                        checked:false,
                        month:7,
                    },
                    {
                        value:"8月",
                        checked:false,
                        month:8,
                    },
                    {
                        value:"9月",
                        checked:false,
                        month:9,
                    },
                    {
                        value:"10月",
                        checked:false,
                        month:10,
                    },
                    {
                        value:"11月",
                        checked:false,
                        month:11,
                    },
                    {
                        value:"12月",
                        checked:false,
                        month:12,
                    },
                ]
            }
        },
        onShow() {
          let that = this
          let csvData = uni.getStorageSync("csvData")
          let str = csvData.birthday_month
          let temp = str.split(",")
          that.array.forEach((i,v) => {
              if(temp.includes(i.month+"")){
                  i.checked = true
              }
          })
        },
        methods: {
            changeTab(e){
                let that = this
                let array = []
                let ar = []
                that.array[e].checked = !that.array[e].checked
                that.array.forEach((val,index) => {
                    if(val.checked){
                        array.push(val.month)
                        ar.push(val.month+'月')
                    }
                })
                let result = array.toString()
                let res = ar.toString()
                let csvData = uni.getStorageSync("csvData")
                if(csvData){
                    csvData['birthday_month'] = result
                    csvData['birthday_month1'] = res
                }else{
                    csvData = {}
                    csvData['birthday_month'] = result
                    csvData['birthday_month1'] = res
                }
                uni.setStorageSync("csvData",csvData)
            }
        }
    }
</script>

<style lang="scss">
    .title{
        display: flex;
        align-items: center;
        padding: 60upx 40upx 40upx;
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        
        .fu{
            color: #AAAAAA !important;
            margin-left: 32upx;
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
</style>
