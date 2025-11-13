<template>
    <view>
        <web-view :src="links"></web-view>
    </view>
</template>

<script>
    export default {
        data() {
            return {
                links:""
            }
        },
        onLoad(options) {
            this.links = options.link
        },
        methods: {
            
        }
    }
</script>

<style>

</style>
