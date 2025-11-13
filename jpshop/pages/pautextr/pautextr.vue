<template>
    <view>
        <web-view :src="link"></web-view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                link:""
            }
        },
        methods: {
            
        },
        onLoad(options) {
            let that = this
            that.link = options.url
        }
    }
</script>

<style>

</style>
