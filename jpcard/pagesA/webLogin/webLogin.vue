<template>
    <view>
       <web-view src="../../hybrid/html/line.html"></web-view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                link:""
            }
        },
        onLoad(options) {
            this.link = options.url
        },
        methods: {
            
        }
    }
</script>

<style>

</style>
