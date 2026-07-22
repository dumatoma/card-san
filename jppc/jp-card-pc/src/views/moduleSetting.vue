<template>
    <div>
        <div class="tit" v-if="step == 1">メッセージテンプレート</div>
        <div class="tit" v-if="step == 2" v-text="mod == 1?'テンプレート①' : 'テンプレート②'"></div>
        <div class="avatar-box1" v-if="step == 1">
            <div class="stepItem" @click="nextStep(1)">
                テンプレート①
            </div>
            <div class="stepItem" @click="nextStep(2)">
                テンプレート②
            </div>
        </div>
        <div class="avatar-box" v-if="step == 2">
            <div class="mtitle">
                <span style="color: #D93025;">※</span><span>タイトル</span><span
                    style="color: #AAAAAA;">(タイトルはメッセージに反映されません）</span>
            </div>
            <div class='minp'>
                <el-input type="text" v-model="title" placeholder="タイトルを入力してください"></el-input>
            </div>
            <div style="margin-top: 20px;">
                <el-input rows="10" resize="none" type="textarea" placeholder="テキストを入力してください。" v-model="content">
                </el-input>
            </div>
            <div class="btns">
                <div class="btn can" @click="step = 1">
                    戻　る
                </div>
                <div class="btn con" @click="saveData">
                    保　存
                </div>
            </div>
        </div>
        <div class="fixed" v-show="keepShow">
          <div class="zhe"></div>
          <div class="sus-box">
            <img src="../static/success.png" alt="" />
            <div class="keep">保存しました</div>
          </div>
        </div>
    </div>
</template>

<script>
    import {
        getStore,
        setMessageOn,
        reviewSetting
    } from "@/http/api.js"
    export default {
        name: "",
        components: {},
        data() {
            return {
                keepShow: false,
                imgUrl: require("../static/yulan-tou.png"),
                step: 1,
                mod: 1,
                title: "",
                content: "",
                m1:[],
                m2:[]
            };
        },
        created() {
            this.getInfo()
        },
        methods: {
            nextStep(e){
                let that = this 
                 that.mod = e
                that.step = 2
                if(e == 1){
                    that.title = that.m1[0]
                    that.content = that.m1[1]
                }else{
                    that.title = that.m2[0]
                    that.content = that.m2[1]
                }
            },
            saveData(){
                let that = this 
                let data = {}
                data.type = 'message'
                data.field = that.mod == 1?'timing_message_tpl1' : 'timing_message_tpl2'
                if (that.mod == 1) {
                    data.timing_message_tpl1 = [that.title, that.content]
                    data.timing_message_tpl2 = that.m2 ? [...that.m2] : []
                } else {
                    data.timing_message_tpl1 = that.m1 ? [...that.m1] : []
                    data.timing_message_tpl2 = [that.title, that.content]
                }
                reviewSetting(data).then((res) => {
                    if(res.code == 200){
                        that.getInfo()
                        that.keepShow = true
                        setTimeout(() => {
                            that.keepShow = false
                        },2000)
                    }else{
                        that.$message.error(res.message)
                    }
                })
            },
            getInfo() {
                let that = this
                getStore().then((res) => {
                    if(res.code == 200){
                        that.m1 = res.data.shop_info.timing_message_tpl1
                        that.m2 = res.data.shop_info.timing_message_tpl2
                    }
                })
            },
            handleAvatarSuccess(res, file) {
                let that = this
                if (res.code == 200) {
                    that.admin.avatar = res.data.path
                } else {
                    that.$message.error(res.message)
                }
                // this.imgUrl = URL.createObjectURL(file.raw);
            },
            keep(e) {
                let that = this
                let data = {}
                data['name'] = that.admin.name
                data['avatar'] = that.admin.avatar
                data['type'] = "info"
                setMessageOn(data).then((res) => {
                    if (res.code == 200) {
                        that.keepShow = true;
                        let admin = that.admin
                        localStorage.setItem("admin", JSON.stringify(admin))
                        setTimeout(() => {
                            that.keepShow = false;
                        }, 1500);
                    }
                })

            },
        },
    };
</script>

<style lang="scss" scoped>
    .sus-box {
      width: 160px;
      height: 146px;
      background: #ffffff;
      border-radius: 10px 10px 10px 10px;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      img {
        margin-top: 30px;
        width: 50px;
        height: 50px;
      }
      .keep {
        margin-top: 20px;
        font-size: 16px;
        color: #2699fb;
      }
    }
    .fixed {
      position: fixed;
      top: 0%;
      left: 0%;
      width: 100vw;
      height: 100vh;
      background: rgba(29, 29, 31, 0.2);
      .zhe {
        position: absolute;
        width: 100vw;
        height: 100vh;
        background: rgba(29, 29, 31, 0.2);
      }
    }
    .btns{
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 40px;
        .btn{
            width: 200px;
            height: 50px;
            border-radius: 10px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            cursor: pointer;
        }
        
        .btn:hover{
            opacity: 0.6;
        }
        
        .can{
            background: #D2D2D7;
            color: #707070;
            margin-right: 30px;
        }
        
        .con{
            background: #1A73E8;
            color: #ffffff;
        }
    }
    .mtxt {
        height: 300px;
        border-radius: 10px;
        border: 1px solid #AAAAAA;
    }

    .mtitle {
        font-size: 16px;
        color: #1d1d1f;
        margin-bottom: 5px;
    }

    .stepItem {
        box-sizing: border-box;
        padding: 23px 20px;
        font-size: 16px;
        color: rgba(29, 29, 31, 0.9);
        cursor: pointer;
    }

    .stepItem:first-child {
        border-bottom: 1px solid #d2d2d7;
    }

    .stepItem:hover {
        color: #1A73E8;
        text-decoration: underline;
    }

    .selContent {
        width: 16px;
        height: 16px;
        border: 1px solid #AAAAAA;
        box-sizing: border-box;
        padding: 3px;
        border-radius: 50%;
        cursor: pointer;
        margin-right: 15px;

        .inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: #1A73E8;
        }
    }

    .tit {
        color: #1d1d1f;
        font-size: 21px;
        font-weight: bold;
        margin-bottom: 29px;
    }

    .reviewImage {
        height: 60px;
        width: 246px;

        img {
            width: 100%;
            height: 100%;
        }
    }

    ::v-deep .el-upload-dragger {
        height: 62px;
        width: 246px;
        border: none;
    }

    .upload-demo {
        height: 60px;
        width: 246px;

        .el-upload {
            width: 100%;
            height: 60px;

            .el-upload-dragger {
                width: 100%;
                height: 60px;
                border: 0;
            }

            .tuo {
                height: 60px;
                width: 100%;
                background: #f7faff;
                border-radius: 10px;
                opacity: 1;
                border: 1px solid #2699fb;

                img {
                    margin-top: 6px;
                    width: 20px;
                    height: 20px;
                }

                .txt {
                    margin-top: 5px;
                    font-size: 14px;
                    color: #2699fb;
                }
            }
        }
    }

    .avatar-box1 {
        width: 410px;
        background: #ffffff;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
    }

    .avatar-box {
        width: 780px;
        background: #ffffff;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
        box-sizing: border-box;
        padding: 40px;

    }
</style>