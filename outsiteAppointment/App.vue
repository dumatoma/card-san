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
    @font-face {
        font-family: Hiragino Sans-W5;
        src: url('~@/static/fonts/NotoSansJP-Bold.otf');
    }
    
    @font-face {
        font-family: Hiragino Sans-W3;
        src: url('~@/static/fonts/NotoSansJP-Regular.otf');
    }
    
    @font-face {
        font-family: Hiragino Sans;
        src: url('~@/static/fonts/NotoSansJP-Regular.otf');
    }
    page{
      font-family: Hiragino Sans-W3, Hiragino Sans, Hiragino Sans-W5;  
    }
    
    body{
        font-family: Hiragino Sans-W3, Hiragino Sans, Hiragino Sans-W5;  
    }
    
</style>
