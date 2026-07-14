<template>
	<view class="iv-mask" v-if="show" @touchmove.stop.prevent="noop">
		<!-- 閉じる -->
		<view class="iv-close" @click="close">
			<u-icon name="close" color="#fff" size="44"></u-icon>
		</view>
		<!-- ピンチ拡大縮小できる画像 -->
		<movable-area class="iv-area" scale-area>
			<movable-view class="iv-view" direction="all" :scale="true" scale-min="1" scale-max="4"
				:scale-value="1" :out-of-bounds="false" :inertia="true">
				<image :src="src" class="iv-img" mode="widthFix" @click="close"></image>
			</movable-view>
		</movable-area>
		<!-- ダウンロードアイコン（画面下部） -->
		<view class="iv-dl" @click="save">
			<u-icon name="download" color="#fff" size="52"></u-icon>
		</view>
	</view>
</template>

<script>
	export default {
		name: "img-viewer",
		props: {
			show: {
				type: Boolean,
				default: false
			},
			src: {
				type: String,
				default: ""
			}
		},
		methods: {
			noop() {},
			close() {
				this.$emit("update:show", false);
				this.$emit("close");
			},
			save() {
				let url = this.src;
				if (!url) return;
				uni.showLoading({
					title: "保存中..."
				});
				uni.downloadFile({
					url: url,
					success: (res) => {
						if (res.statusCode === 200) {
							uni.saveImageToPhotosAlbum({
								filePath: res.tempFilePath,
								success: () => {
									uni.hideLoading();
									uni.showToast({
										title: "保存しました",
										icon: "success"
									});
								},
								fail: () => {
									uni.hideLoading();
									uni.showToast({
										title: "保存に失敗しました",
										icon: "none"
									});
								}
							});
						} else {
							uni.hideLoading();
							uni.showToast({
								title: "保存に失敗しました",
								icon: "none"
							});
						}
					},
					fail: () => {
						uni.hideLoading();
						uni.showToast({
							title: "保存に失敗しました",
							icon: "none"
						});
					}
				});
			}
		}
	};
</script>

<style lang="scss" scoped>
	.iv-mask {
		position: fixed;
		top: 0;
		left: 0;
		width: 100vw;
		height: 100vh;
		background: #000;
		z-index: 10099;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.iv-close {
		position: fixed;
		top: 60upx;
		right: 40upx;
		z-index: 10101;
		width: 72upx;
		height: 72upx;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.iv-area {
		width: 100vw;
		height: 100vh;
		background: transparent;
	}

	.iv-view {
		width: 100vw;
		height: 100vh;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.iv-img {
		width: 100vw;
	}

	.iv-dl {
		position: fixed;
		bottom: 90upx;
		left: 50%;
		transform: translateX(-50%);
		z-index: 10101;
		width: 96upx;
		height: 96upx;
		border-radius: 50%;
		background: rgba(0, 0, 0, 0.5);
		display: flex;
		align-items: center;
		justify-content: center;
	}
</style>
