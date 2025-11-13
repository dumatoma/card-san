<template>
	<view class="wrapper" @touchstart="fingerstart" @touchend="fingerend">
		<slot></slot>
	</view>
</template>
 
<script>
	export default {
		name: "swipe-direct-com",
		data() {
			return { // 记录开始位置
				startData: {
					clientX: '',
					clientY: ''
				},
			};
		},
		props: {
			updDistance: { // 上下滑动 超过多少距离触发 updownDistance
				type: Number,
				default: 100
			},
			lrDistance: { // 左右滑动 超过多少距离触发
				type: Number,
				default: 50
			},
			topMed: { // 上划触发 方法名
				type: String,
				default: '',
			},
			bottomMed: { // 下滑触发 方法名
				type: String,
				default: '',
			},
			leftMed: { // 左滑触发 方法名
				type: String,
				default: '',
			},
			rightMed: { // 右滑触发 方法名
				type: String,
				default: '',
			}
		},
		// 解析数据 
		mounted() {
			
		},
		methods: {
			// 当按下去的时候
			fingerstart(e) {
				// 记录 距离可视区域左上角 左边距 和 上边距
				this.startData.clientX = e.changedTouches[0].clientX;
				this.startData.clientY = e.changedTouches[0].clientY;
			},
			// 当抬起来的时候
			fingerend(e) { 
				// 当前位置 减去 按下位置 计算 距离
				const subX = e.changedTouches[0].clientX - this.startData.clientX;
				const subY = e.changedTouches[0].clientY - this.startData.clientY;
				if (subY > this.updDistance || subY < -this.updDistance) {
					if (subY > this.updDistance) {
						this.bottomscroll(subY);
					} else if (subY < -this.updDistance) {
						this.topscroll(subY);
					}
				} else {
					if (subX > this.lrDistance) {
						this.rightscroll(subX);
					} else if (subX < -this.lrDistance) {
						this.leftscroll(subX);
					} else {
						console.log('无效操作')
					}
				}
			},
			// 上滑触发
			topscroll(dista) {
				this.topMed ? this.$emit(`${this.topMed}`,dista) : null
				console.log("触发了上滑方法!");
			},
			// 下滑触发
			bottomscroll(dista) {
				this.bottomMed ? this.$emit(`${this.bottomMed}`,dista) : null
				console.log("触发了下滑方法!");
			},
			// 右滑触发
			rightscroll(dista) {
				this.rightMed ? this.$emit(`${this.rightMed}`,dista) : null
				console.log("触发了右滑方法!");
			},
			// 左滑触发
			leftscroll(dista) {
				this.leftMed ? this.$emit(`${this.leftMed}`,dista) : null
				console.log("触发了左滑方法!");
			}
		}
	}
</script>