<template>
    <view>
        <view class="content border_top">
            <view class="contentTitle">
                <view class="title">
                    明細保有期間は12ヶ月間です
                </view>
                <view class="boldTitle">
                    ご利用明細
                </view>
            </view>
            <view class="dateList">
                <view class="dateItem" v-for="item,index in array" @click="toDetail(item)">
                    <view class="dateName" v-text="item"></view>
                    <u-icon name="arrow-right"></u-icon>
                </view>
            </view>

        </view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                array: []
            };
        },
        created() {
            this.getlastYearMonth()
        },
        methods: {
            getlastYearMonth() {
                let that = this
                for (var i = 0; i < 13; i++) {
                    var d = new Date();
                    d.setDate(1);
                    d.setMonth(d.getMonth() - i);
                    var m = d.getMonth() + 1;
                    m = m < 10 ? "0" + m : m; //在这里可以自定义输出的日期格式 
                    that.array.push(d.getFullYear() + "年" + m + "月"); //result.push(d.getFullYear() + "年" + m + '月'); 
                }
            },
            toDetail(e){
                uni.navigateTo({
                    url:"../detailed/seStep?date="+e
                })
            }
        }
    }
</script>

<style lang="scss">
    .content {
        .dateList{
            .dateItem{
                display: flex;
                align-items: center;
                justify-content: space-between;
                box-sizing: border-box;
                padding: 40upx 60upx;
                background: #fff;
                border-bottom: 2upx solid #d2d2d7;
                
                .dateName{
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }
            }
        }
        
        .contentTitle {
            background: #F5F5F7;
            box-sizing: border-box;
            padding: 80upx 46upx 32upx;
            border-bottom: 2upx solid #d2d2d7;

            .title {
                font-size: 24upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #707070;
            }

            .boldTitle {
                font-size: 32upx;
                font-family: Hiragino Sans-W5, Hiragino Sans;
                font-weight: normal;
                margin-top: 60upx;
                color: #1D1D1F;
            }
        }

    }
</style>
