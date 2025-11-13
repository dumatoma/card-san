<template>
	<view class="drag-and-drop-sort">
		<template v-if="controlsPositionArray.length !== 0">
			<view v-for="(item, index) in controlsArray" :key="index" class="_item"
				:style="{ 'transition': (curretnControlsIndex === index ? 'initial' : '.3s'), 'z-index': (curretnControlsIndex === index ? 1 : 0), 'width': controlsSize.width + 'px', 'height': controlsSize.height + 'px', 'top': controlsPositionArray[index].top + 'px',  'left': controlsPositionArray[index].left + 'px'}">
				<view @touchstart="handleTouchstart($event, index)" @touchmove="handleTouchmove" @touchend="handleTouchend" style="width: 100%; height: 100%;">
					<!-- 自定义内容 -->
					<view style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%;">
            <view class="">
              <image :src="item.img" mode=""></image>
              <view class="name">
                 {{item.title}}
              </view>
            </view>
					</view>
				</view>
			</view>
		</template>
	</view>
</template>

<script>
export default {
	name: "drag-and-drop-sort",
	props: {
		// 控件的大小
		controlsSize: {
			type: Object,
			default: () => ({ width: 0, height: 0 }),
		},
		// 数据列表
		controlsList: {
			type: Array,
			default: () => [],
		},
	},
	data() {
		return {
			// 控件列表
			controlsArray: [],
			// 每行最大存放的个数
			maxWidthCount: 0,
			// 控件的间距
			margin: {
				margin_x: 10,
				margin_y: 10,
			},
			// 记录所有控件的初始位置
			recordInitControlsPoisitonList: [],
			// 控件的数据
			controlsPositionArray: [],
			// 记录当前手指的位置
			recordPosition: {
				x: 0,
				y: 0,
			},
			// 记录当前操作的控件数据
			recordControlsPositionItem: {},
			// 当前操作的控件的下标
			curretnControlsIndex: -1,
		};
	},
	mounted() {
		// 获取系统信息
		this.systemInfo = uni.getSystemInfoSync();
		// 获取控件列表
		this.controlsArray = this.controlsList;
		
		// 初始化控件的位置
		this.controlsPositionArray = this.initControlsPosition();
	},
	methods: {
		/** 初始化各个控件的位置 */
		initControlsPosition() {
			// 用于返回出去的新数组
			let tempArray = [];
			// 每行最大存放的个数
			// parseInt(this.systemInfo.windowWidth / (this.controlsSize.width + this.margin.margin_x));
      this.maxWidthCount =  2
			// 设置控件位置 - 这边多记录一个位置 之后会用到
			for(let i = 0, j = 0, k = 0; i < this.controlsList.length + 1; i++) {
				tempArray[i] = {
					left: j * (this.controlsSize.width + this.margin.margin_x) + this.margin.margin_x,
					top: k * (this.controlsSize.height + this.margin.margin_y) + this.margin.margin_y,
				}
				k = j + 1 === this.maxWidthCount ? ++k : k;
				j = j + 1 === this.maxWidthCount ? 0 : ++j;
			}
			
			// 记录数据 - 进行深拷贝
			this.recordInitControlsPoisitonList = [...tempArray];
			// 返回数据
			return tempArray;
		},
		
		/** 处理手指触摸后移动 */
		handleTouchmove(event) {
			const { pageX, pageY } = event.touches[0];
			
			// 获取移动的差
			this.$set(this.controlsPositionArray, this.curretnControlsIndex, {
				left: this.controlsPositionArray[this.curretnControlsIndex].left + (pageX - this.recordPosition.x),
				top: this.controlsPositionArray[this.curretnControlsIndex].top + (pageY - this.recordPosition.y),
			});
			// 记录位置
			this.recordPosition = { x: pageX, y: pageY };
			// 判断当前移动的位置是否需要进行排序
			// 向右移动
			if((this.curretnControlsIndex + 1) % this.maxWidthCount !== 0 && this.controlsPositionArray[this.curretnControlsIndex].left >= this.recordInitControlsPoisitonList[this.curretnControlsIndex + 1].left + this.controlsSize.width / 2 && this.controlsPositionArray[this.curretnControlsIndex].top < this.recordInitControlsPoisitonList[this.curretnControlsIndex].top + this.controlsSize.height / 3 + this.margin.margin_y && this.controlsPositionArray[this.curretnControlsIndex].top > this.recordInitControlsPoisitonList[this.curretnControlsIndex].top - this.controlsSize.height / 3 - this.margin.margin_y) {
				// 交换控件
				this._handleChangeControlsPosition(0, this.curretnControlsIndex + 1);
			}
			// 向左移动
			else if(this.curretnControlsIndex % this.maxWidthCount !== 0 && this.controlsPositionArray[this.curretnControlsIndex].left <= this.recordInitControlsPoisitonList[this.curretnControlsIndex - 1].left + this.controlsSize.width / 2 && this.controlsPositionArray[this.curretnControlsIndex].top < this.recordInitControlsPoisitonList[this.curretnControlsIndex].top + this.controlsSize.height / 3 + this.margin.margin_y && this.controlsPositionArray[this.curretnControlsIndex].top > this.recordInitControlsPoisitonList[this.curretnControlsIndex].top - this.controlsSize.height / 3 - this.margin.margin_y) {
				// 交换控件
				this._handleChangeControlsPosition(0, this.curretnControlsIndex - 1);
			}
			// 向下拖动
			else if(parseInt(this.curretnControlsIndex  / this.maxWidthCount) + 1 !== this.controlsList.length / this.maxWidthCount && this.controlsPositionArray[this.curretnControlsIndex].top < this.recordInitControlsPoisitonList[this.curretnControlsIndex].top + this.controlsSize.height + this.margin.margin_y + this.controlsSize.height / 3 && this.controlsPositionArray[this.curretnControlsIndex].top + this.controlsSize.height > this.recordInitControlsPoisitonList[this.curretnControlsIndex].top + this.controlsSize.height + this.margin.margin_y + this.controlsSize.height / 2) {
				// 向右移动
				if(this.controlsPositionArray[this.curretnControlsIndex].left > this.recordInitControlsPoisitonList[this.curretnControlsIndex + this.maxWidthCount].left + this.controlsSize.width / 2) {
					// 交换控件
					this._handleChangeControlsPosition(1, this.curretnControlsIndex + this.maxWidthCount);
				}
				// 向左移动
				else if(this.curretnControlsIndex % this.maxWidthCount !== 0 && this.controlsPositionArray[this.curretnControlsIndex].left + this.controlsSize.width < this.recordInitControlsPoisitonList[this.curretnControlsIndex + this.maxWidthCount].left + this.controlsSize.width / 2) {
					// 交换控件
					this._handleChangeControlsPosition(1, this.curretnControlsIndex + this.maxWidthCount - 1);
				}
			}
			// 向上拖动
			else if(parseInt(this.curretnControlsIndex  / this.maxWidthCount) !== 0 && this.controlsPositionArray[this.curretnControlsIndex].top < this.recordInitControlsPoisitonList[this.curretnControlsIndex].top - this.margin.margin_y - this.controlsSize.height / 2 && this.controlsPositionArray[this.curretnControlsIndex].top + this.controlsSize.height > this.recordInitControlsPoisitonList[this.curretnControlsIndex].top - this.margin.margin_y - this.controlsSize.height / 2) {
				// 向右移动
				if((this.curretnControlsIndex + 1) % this.maxWidthCount !== 0 && this.controlsPositionArray[this.curretnControlsIndex].left > this.recordInitControlsPoisitonList[this.curretnControlsIndex - this.maxWidthCount].left + this.controlsSize.width / 2) {
					// 交换控件
					this._handleChangeControlsPosition(2, this.curretnControlsIndex - this.maxWidthCount + 1);
				}
				// 向左移动
				else if(this.controlsPositionArray[this.curretnControlsIndex].left + this.controlsSize.width < this.recordInitControlsPoisitonList[this.curretnControlsIndex - this.maxWidthCount].left + this.controlsSize.width / 2) {
					// 交换控件
					this._handleChangeControlsPosition(2, this.curretnControlsIndex - this.maxWidthCount);
				}
			}
		},
		
		/** 处理手指触摸开始事件 */
		handleTouchstart(event, index) {
			const { pageX, pageY } = event.touches[0];
			
			// 记录一些数据
			this.curretnControlsIndex = index;
			this.recordPosition = { x: pageX, y: pageY };
			this.recordControlsPositionItem = this.controlsPositionArray[index];
		},
	
		/** 处理手指松开事件 */
		handleTouchend(event) {
			// 将操控的控件归位
			this.controlsPositionArray[this.curretnControlsIndex] = this.recordInitControlsPoisitonList[this.curretnControlsIndex];
			this.curretnControlsIndex = -1;
		},
		
		/**
		 * 处理交换控件位置的方法 - 
		 * @param {number} type		当前操作的类型 - 0: 横向移动  1: 向下+横向  2: 向上+横向
		 * @param {number} index	需要与第几个下标交换位置
		 * */
		_handleChangeControlsPosition(type, index) {
			// 记录当前操控的控件数据
			let tempControls = this.controlsArray[this.curretnControlsIndex];
			
			// 判断类型
			if(type === 0) {
				this.controlsArray[this.curretnControlsIndex] = this.controlsArray[index];
			}
			else if(type === 1) {
				// 循环交换控件列表数据
				for(let i = this.curretnControlsIndex; i < index; i++) {
					this.controlsArray[i] = this.controlsArray[i + 1];
				}
			}
			else if(type === 2) {
				// 循环交换控件列表数据
				for(let i = this.curretnControlsIndex; i > index; i--) {
					this.controlsArray[i] = this.controlsArray[i - 1];
				}
			}
			// 将临时存放的数据设置好
			this.controlsArray[index] = tempControls;
			
			// 调整控件位置数据
			this.controlsPositionArray[index] = this.controlsPositionArray[this.curretnControlsIndex];
			this.controlsPositionArray[this.curretnControlsIndex] = this.recordControlsPositionItem;
			
			// 改变当前选中的位置
			this.curretnControlsIndex = index;
			
			// 记录新位置的数据
			this.recordControlsPositionItem = this.recordInitControlsPoisitonList[this.curretnControlsIndex];
		},
	}
}
</script>

<style lang="scss">
.drag-and-drop-sort {
	position: relative;
	width: 100vw;
	height: 100vh;
}
 
.drag-and-drop-sort ._item {
    position: absolute;
}
image{
  width: 141px;
  height: 85px;
  margin-bottom: 11px;
}
.name{
  font-size: 12px;
  text-align: center;
}
</style>