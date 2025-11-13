<template>
    <view>
        <!-- <button @click="closeSocket">关闭socket</button>
        <button @click="openSocket">链接socket</button> -->
        <web-view src="https://cardsan.zendesk.com/"></web-view>
    </view>
</template>

<script>
    import {getShopInfo} from "@/api/index.js"
    export default {
        data() {
            return {
                links:"https://cardsan.zendesk.com/"
            }
        },
        onShow() {
           let that = this
           // getShopInfo().then((res) => {
           //     console.log(res)
           //     if(res.code == 200){
           //         let urlArr = res.data.shop_info.out_url.split("!@")
           //         if(urlArr[1] == 1){
           //             that.links = urlArr[0]
           //         }else{
           //             that.links = ""
           //         }
           //     }
           // }) 
        },
        methods: {
            // closeSocket() {
            //     this.$store.commit('closeSocket', false)
            // },
            // openSocket() {
            //     this.$store.commit('connectSocketInit', true)
            // }
        }
    }
</script>

<style>

</style>
