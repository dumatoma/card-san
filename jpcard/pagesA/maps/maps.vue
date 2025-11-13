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
                arr: [],
                
            };
        },
        onLoad(options) {
            let that = this
            // that.src = "https://www.baidu.com"
            setTimeout(() => {
                that.src = that.$baseUrl+"/api/google/map?address="+encodeURI(options.address)  
            },2500)
            
            console.log(that.src)
        },
        methods: {}
    };
</script>

<style></style>
