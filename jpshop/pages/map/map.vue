<template>
    <view>
        <web-view :src="src"></web-view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                src: '',
                arr: []
            };
        },
        onLoad(options) {
            let that = this
            uni.request({
                url: "https://maps.googleapis.com/maps/api/geocode/json?address=〒606-0816  京都府京都市左京区下鴨松ノ木町53 加茂みたらし茶屋&key=AIzaSyAOQupmIQwbcpUgbLGynK3IwWxNzFDcQRI",
                method: "get",
                success(res) {
                    that.arr.push(res.data.results[0].geometry.location)
                    that.src = '/hybrid/html/map.html?arr=' + encodeURIComponent(JSON.stringify(that.arr));
                }
            })
        },
        methods: {}
    };
</script>

<style></style>
