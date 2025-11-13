<template>
    <view>
        <web-view :src="link"></web-view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                link: ""
            }
        },
        created() {
            
        },
        onLoad(options) {
            let query = options
            this.link = decodeURIComponent(query.link) 
        },
        onBackPress() {
            let pages = getCurrentPages();
            let currPage = pages[pages.length - 1]
            let prePage = pages[pages.length - 2]
            if (prePage.route == "pages/message/chat/chat") {
                prePage.$vm.getHistory()
            }
        },
        methods: {

        }
    }
</script>

<style>

</style>
