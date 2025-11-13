<template>
    <view>
        <view style="width: 100%;height: 500upx;background: red;">
            
        </view>
        
        
        
        <u-index-list :index-list="indexList">
            <template v-for="(item, index) in itemArr">
                <!-- #ifdef APP-NVUE -->
                <u-index-anchor :text="indexList[index]"></u-index-anchor>
                <!-- #endif -->
                <u-index-item>
                    <!-- #ifndef APP-NVUE -->
                    <u-index-anchor :text="indexList[index]"></u-index-anchor>
                    <!-- #endif -->
                    <view class="list-cell" v-for="(cell, index) in item">
                        {{cell}}
                    </view>
                </u-index-item>
            </template>
        </u-index-list>
    </view>
</template>

<script>
    export default {
        data() {
            return {
               indexList: ["A", "B", "C","D","E","F","G","H","I"],
                itemArr: [
                    ['列表A1','列表A2','列表A3'],
                    ['列表B1','列表B2','列表B3'],
                    ['列表C1','列表C2','列表C3'],
                    ['列表A1','列表A2','列表A3'],
                    ['列表B1','列表B2','列表B3'],
                    ['列表C1','列表C2','列表C3'],
                    ['列表A1','列表A2','列表A3'],
                    ['列表B1','列表B2','列表B3'],
                    ['列表C1','列表C2','列表C3']
                ] 
            };
        }
    }
</script>

<style lang="scss">
.list-cell {
		display: flex;
		box-sizing: border-box;
		width: 100%;
		padding: 10px 24rpx;
		overflow: hidden;
		color: #323233;
		font-size: 14px;
		line-height: 24px;
		background-color: #fff;
	}
</style>
