<script>
	export default {
		onLaunch: function() {
			console.log('App Launch')
		},
		onShow: function() {
			console.log('App Show')
		},
		onHide: function() {
			console.log('App Hide')
		}
	}
</script>

<style lang="scss">
    @import "@/uni_modules/uview-ui/index.scss";
	/*每个页面公共css */
    @import "@/uni_modules/uview-ui/index.scss";
    	/*每个页面公共css */
     /*每个页面公共css */
    @font-face {
        font-family: Hiragino Sans-W6;
        src: url('~@/static/font/NotoSansJP-Bold.otf');
    }
    
    @font-face {
        font-family: Hiragino Sans-W3;
        src: url('~@/static/font/NotoSansJP-Regular.otf');
    }
    
    @font-face {
        font-family: Hiragino Sans;
        src: url('~@/static/font/NotoSansJP-Regular.otf');
    }
     
     // * {
     //     font-family: Hiragino Sans-W3, Hiragino Sans;
     // }
     
     page{
       font-family: Hiragino Sans-W3, Hiragino Sans, Hiragino Sans-W5 !important;  
     }
     body{
         font-family: Hiragino Sans-W3, Hiragino Sans, Hiragino Sans-W5 !important;  
     }
</style>
