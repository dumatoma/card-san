<template>
    <view class="border_top">
        <view class="list">
            <view class="item border_bottom" v-for="(item,index) in array">
                <view class="noticeTitle" v-text="item.title"></view>
                <view class="noticeDate" v-text="item.create_time"></view>
                <u-textarea border="none" v-model="item.content" autoHeight disabled></u-textarea>
            </view>
        </view>
        <view class="ge" v-if="array.length > 0">
            <view class="geLeft"></view>
            <view class="geContent">
                ここまで未読
            </view>
            <view class="geLeft"></view>
        </view>
        <view class="list">
            <view class="item border_bottom" v-for="(item,index) in array1">
                <view class="noticeTitle" v-text="item.title"></view>
                <view class="noticeDate" v-text="item.create_time"></view>
                <u-textarea border="none" v-model="item.content" autoHeight disabled></u-textarea>
            </view>
        </view>
    </view>
</template>

<script>
    import {
        getYY
    } from "@/api/index.js"
    export default {
        data() {
            return {
                array: [],
                array1: []
            };
        },
        created() {
            let that = this
            getYY("list").then((res) => {
                let temp = []
                let temp1 = []
                if (res.code == 200) {
                    res.data.notices.forEach((val, index) => {
                        if (val.read == 0) {
                            temp.push(val)
                        } else {
                            temp1.push(val)
                        }
                    })
                    that.array = temp
                    that.array1 = temp1
                }
            })
        },
        methods: {
            format(val) {
                val.replace(/[\n\r]/g, '<br/>')
                return val
            }
        }
    }
</script>

<style lang="scss">
    .ge {
        display: flex;
        align-items: center;

        .geLeft {
            flex: 1;
            height: 2upx;
            background: #1A73E8;
        }

        .geContent {
            width: 402upx;
            height: 40upx;
            background: RGBA(208, 226, 251, 1);
            border-radius: 20upx;
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1A73E8;
            text-align: center;
            line-height: 40upx;
        }

    }

    .item:last-child {
        border: none !important;
    }

    .item {
        box-sizing: border-box;
        padding: 60upx 40upx;

        .noticeTitle {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .noticeDate {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin: 24upx 0 32upx;
        }

        .noticeContent {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
        }
    }
</style>
