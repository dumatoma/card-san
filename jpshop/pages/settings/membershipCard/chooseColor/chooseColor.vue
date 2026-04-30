<template>
    <view>
        <chooseColor :color="Colors" @pickerColor="changeColors"></chooseColor>
        <view class="resultContent">
            <view class="result" :style="`background:${Colors}`"></view>
            <view class="colorList">
                <view class="colorItem" @click="selectColor(item.color,index)" :style="`background:${item.color}`" v-for="(item,index) in colorList">
                    <view class="colorSelected" :style="item.selected? 'border-radius:50%;border:4upx solid #fff' : '' "></view>
                </view>
              <!--  <view class="colorItem" style="padding: 0;" @click="diy">
                    <image :style="isdiy == true?'transform:rotate(45deg)':''" style="width: 60upx;height: 60upx;" src="../../../../static/svg/addcolor.svg" mode=""></image>
                </view> -->
            </view>
        </view>
        <view style="height: 2upx;"></view>
    </view>
</template>

<script>
    import chooseColor from "../../../../components/holy-colorPicker/colorPicker.vue"
    import {getShopInfo,settingDiyColor} from "@/api/index.js"
    export default {
        components: {
            chooseColor
        },
        props:{
           source:String,
           Colorss:String
        },
        data() {
            return {
                Colors: "#000000",
                tempColor:"",
                isdiy:true,
                current:5645,
                kb:false,
                colorList:[],
                templist: [{
                        color: "#FEECD4",
                        selected: false
                    },
                    {
                        color: "#B1DD8B",
                        selected: false
                    },
                    {
                        color: "#EAF28F",
                        selected: false
                    },
                    {
                        color: "#FFC5AB ",
                        selected: false
                    },
                    {
                        color: "#B51A00",
                        selected: false
                    },
                    {
                        color: "#74A7FF",
                        selected: false
                    }, {
                        color: "#BE38F3",
                        selected: false
                    }, {
                        color: "#00A1D8",
                        selected: false
                    }, {
                        color: "#0042A9 ",
                        selected: false
                    },{
                        color: "#ffac02 ",
                        selected: false
                    },
                ]
            }
        },
        watch:{
           Colorss: { // 对对象进行深度监听
                handler(nv) {
                  this.Colors = this.rgbToHex(nv)
                },
                immediate: true,
                deep: true
              },  
        },
        created() {
          let that = this 
          // that.Colors = that.Colorss
          getShopInfo().then((res) => {
              if(res.code == 200){
                  if(res.data.shop_info.color.length == 0){
                      that.colorList = that.templist
                  }else{
                      that.colorList = res.data.shop_info.color
                  }
                  that.colorList.forEach((val,i) => {
                      val.selected = false
                  })
                  
              }
          })
        },
        onLoad(options) {
        },
        methods: {
            diy(){
               let that = this 
               let data = {}
               data["type"] = "color"
               data['color'] = that.colorList
               settingDiyColor(data).then((res) => {
                   if(res.code == 200){
                   }else{
                       uni.showToast({
                           title:res.message
                       })
                   }
               })
            },
            changeColors(e) {
                 let that = this
                if(that.kb == true){
                    that.colorList[that.current].color = e.rgb
                    this.diy()
                }
                this.Colors = e.rgb
                if(that.isdiy == false){
                    this.colorList.forEach((value,index) => {
                        value.selected = false
                    })
                }else{
                     
                }
                this.$emit("selectColor",this.Colors)
                
            },
            selectColor(e,index){
                let that = this
                that.current = index
                that.colorList.forEach((value,idx) => {
                    if(index == idx){
                        value.selected = true
                        if(e.indexOf("#") == -1){
                            that.Colors = that.rgbToHex(e)
                        }else{
                            that.Colors = e
                        }
                         that.$emit("selectColor",that.Colors)
                    }else{
                        value.selected = false
                    }
                   
                })
                // if(that.isdiy == false){
                //     this.Colors = e
                //     this.$emit("selectColor",this.Colors)
                // }
                that.kb = true
            },
            rgbToHex(color) {
                var values = color
                  .replace(/rgba?\(/, '')
                  .replace(/\)/, '')
                  .replace(/[\s+]/g, '')
                  .split(',');
                var a = parseFloat(values[3] || 1),
                  r = Math.floor(a * parseInt(values[0]) + (1 - a) * 255),
                  g = Math.floor(a * parseInt(values[1]) + (1 - a) * 255),
                  b = Math.floor(a * parseInt(values[2]) + (1 - a) * 255);
                return "#" +
                  ("0" + r.toString(16)).slice(-2) +
                  ("0" + g.toString(16)).slice(-2) +
                  ("0" + b.toString(16)).slice(-2);
              },
        }
    }
</script>

<style lang="scss">
    .resultContent{
        display: flex;
        align-items: center;
        box-sizing: border-box;
        padding: 0 32upx;
        
        .result {
            width: 164upx;
            height: 164upx;
            border-radius: 20upx;
            flex-shrink: 0;
            margin-right: 46upx;
        }
        
        .colorList {
            display: flex;
            flex-wrap: wrap;
            padding-bottom: 40upx;
            .colorItem {
                width: 60upx;
                height: 60upx;
                border-radius: 50%;
                margin-right: 44upx;
                box-sizing: border-box;
                padding: 8upx;
                
                image{
                    transition: all linear 0.3s;
                }
                
                .colorSelected{
                    width: 100%;
                    height: 100%;
                    box-sizing: border-box;
                }
            }
            
            .colorItem:nth-child(1){
                margin-bottom: 44upx;
            }
            
            .colorItem:nth-child(5n){
                margin-right: 0;
            }
        }
    }
    
   
</style>
