<template>
  <div class="scrollbar-box" :style="{height: height}">
    <div class="scrollbar-y">
      <div ref="scrollRef" class="scroll-wrap" @scroll="onMosewheel">
        <slot></slot>
      </div>
      <div v-show="heightPre < 1" ref="barRef" class="scrollbar-track">
        <div
          :style="{height: barHeight + 'px', transform: 'translateY(' + translateY + 'px)'}"
          class="scrollbar-thumb"
          @mousedown.stop.prevent="moveStart"
        ></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  components: {},
  props: {
    height: {
      type: String,
      default: '100%'
    }
  },
  data() {
    return {
      isMove: false,
      trackHeight: 0, // 滚动条轨道高度
      wrapHeight: 0, // 容器高度（可视高度）
      wrapContentHeight: 0, // 内容高度（可滚动内容的高度）
      translateY: 0,

      moveClientY: 0,
      scrollTop: 0,
    }
  },
  computed: {
    barHeight() {
      return this.heightPre * this.trackHeight
    },
    // 可视高度与内容高度的比例
    heightPre() {
      return this.wrapHeight / this.wrapContentHeight
    }
  },
  watch: {},
  // 生命周期 - 创建完成（可以访问当前this实例）
  created() {},
  // 生命周期 - 挂载完成（可以访问DOM元素）
  mounted() {
    this.initScrollListner()
  },
  activated() {},
  methods: {
    initScrollListner(e) {
      const scroll = this.$refs.scrollRef
      const bar = this.$refs.barRef
      this.wrapContentHeight = scroll.scrollHeight
      this.wrapHeight = scroll.clientHeight
      this.trackHeight = bar.clientHeight
    },
    updateScroll() {
      this.initScrollListner()
    },
    onMosewheel(e) {
      this.scrollY(e.target.scrollTop * this.heightPre)
    },
    scrollY(y) {
      this.translateY = y
    },
    // 鼠标开始移动
    moveStart(e) {
      this.isMove = true
      // 开始移动时鼠标距离thumb顶部的位置
      this.moveClientY = e.clientY - this.translateY
      this.moveTo()
      this.moveEnd()
    },
    // 鼠标移动，改变thumb的位置以及容器scrollTop的位置
    moveTo() {
      document.onmousemove = e => {
        if (this.isMove) {
          if (
            e.clientY - this.moveClientY >
            this.trackHeight - this.barHeight
          ) {
            this.translateY = this.trackHeight - this.barHeight
          } else if (e.clientY - this.moveClientY < 0) {
            this.translateY = 0
          } else {
            this.translateY = e.clientY - this.moveClientY
          }
          this.$refs.scrollRef.scrollTop = (this.translateY) / this.heightPre
        }
      }
    },
    // 鼠标移动结束
    moveEnd() {
      document.onmouseup = e => {
        if (this.isMove) {
          this.isMove = false
        }
      }
    }
  }
}
</script>
<style lang='scss' scoped>
.scrollbar-box {
  height: 600px;
  overflow: hidden;
}
.scrollbar-y {
  position: relative;
  height: 100%;
  /* margin-right: -17px; */

  .scroll-wrap {
    height: 100%;
    overflow-y: scroll;
    background: #fff !important;
  }
  .scrollbar-track {
    position: absolute;
    top: 3px;
    right: 16px;
    bottom: 10px;
    width: 10px;
    background-color: #fff;
    border-radius: 10px;
    z-index: 20;
    .scrollbar-thumb {
        width: 74%;
      height: 20%;
      background-color: #c4c4c4;
      border-radius: 10px;
      cursor: default;
      // transition: all 0.01s;
    }
  }
}
</style>

