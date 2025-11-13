<template>
    <view class="contents">
        <view class="titleContent">
            <view class="t1">
                ご利用履歴
            </view>
            <view class="t2" v-text="currentdate"></view>
            <view class="t3" @click="downloadFile">
               <image src="../../../static/icons/download.svg" mode=""></image> <view>ご利用明細書 (PDF)</view>
            </view>
        </view>
        <view class="itemContent"  v-if="array.length > 0">
            <view class="itemDetail">
                <view class="itemds" v-for="item,index in array">
                    <view class="itemDate" v-text="item.date"></view>
                    <view class="itemsDetail">
                        <view class="itemName" v-text='item.remark'></view>
                        <view class="itemPrice" v-text="`￥${item.amount}`"></view>
                    </view>
                </view>
            </view>
            
        </view>
        <view class="itemContent" v-else style="padding: 20upx;">
            期間内の課金はありません。
        </view>
        
        <view class="mask" v-if="mask" @click="mask = false">
            <view class="maskContent">
                期間内の課金はありません
            </view>
        </view>
    </view>
</template>

<script>
    import {getPayDetails,getpdf} from "@/api/index.js"
    export default {
        data() {
            return {
               currentdate:"",
               array:[],
               cmonth:'',
               mask:false
            }
        },
        onLoad(options) {
            let that = this
            let date = options.date
            let day = ''
             uni.setNavigationBarTitle({
              title:date
            })
            let temp1 = date.split("年")
            let year = temp1[0]
            let temp2 = temp1[1].split("月")
            let month = temp2[0]
            if(month == '01' || month == '03' || month == '05' || month == '07' || month == '08' || month == '10' || month == '12'){
                day = 31
            }else if(month == '02'){
                day = 28
            }else{
                day = 30
            }
            that.currentdate = year + "/"+month + "/01" + " - " + year + "/" + month + "/" +day
            that.cmonth = year+"-"+month
            that.getList(year+"-"+month)
        },
        methods: {
            getList(e){
                let that = this
                getPayDetails(e).then((res) => {
                    if(res.code == 200){
                        let array = []
                        for(let i = 0; i < res.data.logs.length; i++){
                            for(let j = 0; j < res.data.logs[i].list.length; j++){
                                res.data.logs[i].list[j].date = res.data.logs[i].date
                                array.push(res.data.logs[i].list[j])
                            }
                        }
                        that.array = array
                    }
                })
            },
            downloadFile(){
                let that = this
                if(that.array.length > 0){
                   getpdf(that.cmonth).then((result) => {
                       if(result.code == 200){
                          uni.downloadFile({
                              url:result.data.pdf,
                              success: (res) => {
                                  if (res.statusCode == 200) {
                                      let filePath = res.tempFilePath;
                                      uni.openDocument({
                                          filePath,
                                          success: function(file) {
                                          }
                                      });
                                  }
                              }
                          }) 
                       }
                   })   
                }else{
                    that.mask = true
                }    
            }
        }
    }
</script>

<style lang="scss">
    page{
        background: #f5f5f7;
    }
    .mask{
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, .6);
        position: fixed;
        top: 0;
        left: 0;
        z-index: 19;
        
        .maskContent{
            width: 50vw;
            box-sizing: border-box;
            padding: 40upx;
            border-radius: 20upx;
            background: #fff;
            text-align: center;
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
        }
    }
    .itemContent{
        background-color: #f5f5f7;
        box-sizing: border-box;
        padding: 60upx 20upx 0;
        
        .itemDetail{
            background-color: #fff;
            border: 2upx solid #d2d2d7;
            .itemds:last-child{
                border-bottom: none !important;
            }
            .itemds{
                box-sizing: border-box;
                padding: 40upx 28upx;
                border-bottom: 2upx solid #d2d2d7;
                
                .itemDate{
                    font-size: 20upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                }
                
                .itemsDetail{
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    font-size: 28upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                    
                }
            }
        }
    }
    
    .titleContent{
        background: #fff;
        box-sizing: border-box;
        padding: 80upx 40upx 60upx;
        
        .t1{
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }
        
        .t2{
            font-size: 20upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin: 20upx 0 44upx;
        }
        
        .t3{
            display: flex;
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
            align-items: center;
            
            image{
                width: 36upx;
                height: 42upx;
                margin-right: 20upx;
            }
        }
    }
</style>
