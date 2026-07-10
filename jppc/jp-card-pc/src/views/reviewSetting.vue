<template>
    <div>
        <div class="tit">レビューリンク設定</div>
        <div class="avatar-box">
            <div class="firstPart">
                <div class="firstTitle">
                    レビューリンクボタンのデザイン
                </div>

                <div class="reviewItem">
                    <div class="selContent" :style="current == 1?'border:1px solid #1A73E8' : ''" @click="current = 1">
                        <div class="inner" v-if="current == 1"></div>
                    </div>
                    <div class="reviewImage">
                        <img src="../static/svg/review.svg" alt="" />
                    </div>
                </div>

                <div class="reviewItem">
                    <div class="selContent" :style="current == 2?'border:1px solid #1A73E8' : ''" @click="current = 2">
                        <div class="inner" v-if="current == 2"></div>
                    </div>
                    <div class="reviewImage" v-if="image == ''">
                        <el-upload class="upload-demo" accept="image/*"
                            action="https://api.card-san.jp/tool/upload/file" :on-success="handleAvatarSuccess" drag
                            :limit="1" :show-file-list="false" :file-list="fileList" name="file">
                            <div class="tuo no-size">
                                <img src="@/static/edit-image.png" alt="" />
                                <br />
                                <span class="txt">クリックorファイルをドロップ</span>
                            </div>
                        </el-upload>
                    </div>
                    <div class="reviewImage" style="position: relative;" v-else>
                        <img src="../static/gClose.png" class="closeImage" alt="" @click.stop="delImage" />
                        <img :src="image" alt="" />
                    </div>
                </div>
            </div>
            <div class="secondPart">
                <div class="secondTitle">
                    お店のGoogleビジネスプロフィールのレ<br/>ビュー用リンクURLを貼り付けてください
                </div>
                <div class="txtarea">
                    <textarea v-model="content"></textarea>
                </div>
                <div class="rule" @click="toOpen">
                    レビュー用リンクの作成方法　＞
                </div>
                <div class="btn" @click="saveData">
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
                name: "",
                admin: {},
                current:1,
                fileList:[],
                content:"",
                image:""
            };
        },
        created() {
            this.getInfo()
        },
        methods: {
            toOpen(){
                window.open("https://cardsan.zendesk.com/hc/ja/articles/39169673962393-Google%E3%83%93%E3%82%B8%E3%83%8D%E3%82%B9%E3%83%97%E3%83%AD%E3%83%95%E3%82%A3%E3%83%BC%E3%83%AB%E3%81%AE%E3%83%AC%E3%83%93%E3%83%A5%E3%83%BC%E3%83%AA%E3%83%B3%E3%82%AF%E3%81%AE%E4%BD%9C%E6%88%90")
            },
            delImage(){
                let that = this
                 that.image = ""
            },
            saveData(){
                let that = this
                let data = {}
                data.type = "comment_button"
                data.comment_button_style = that.current * 1 
                data.comment_button_style2_image = that.image
                data.comment_button_url = that.content
                reviewSetting(data).then((res) => {
                    console.log(res)
                    if(res.code == 200){
                        that.keepShow = true
                        setTimeout(() => {
                            that.keepShow = false
                        },2000)
                    }else{
                        that.$message({
                        	message: res.message,
                        	type: 'error',
                             offset: 400
                        });
                    }
                })
            },
            getInfo() {
                let that = this
                getStore().then((res) => {
                    console.log(res)
                    if(res.code == 200){
                        that.content = res.data.shop_info.comment_button_url
                        that.current = res.data.shop_info.comment_button_style
                        that.image = res.data.shop_info.comment_button_style2_image
                    }
                })
            },
            handleAvatarSuccess(res, file) {
                let that = this
                if (res.code == 200) {
                    that.image = res.data.path
                } else {
                    that.$message({
                    	message: res.message,
                    	type: 'error',
                         offset: 400
                    });
                }
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
    .closeImage{
        position: absolute;
        right: 4px;
        top: 4px;
        z-index: 19;
        width: 20px !important;
        height: 20px !important;
        cursor: pointer;
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
    .rule{
        margin-top: 10px;
        font-size: 14px;
        color: #1A73E8;
        cursor: pointer;
    }
    .rule:hover{
        opacity: 0.6;
    }
    .btn{
        width: 120px;
        height: 40px;
        font-size: 16px;
        color: #FFFFFF;
        text-align: center;
        line-height: 40px;
        background: #1A73E8;
        border-radius: 26px;
        margin: 0 auto;
        cursor: pointer;
        margin-top: 40px;
    }
    
    .btn:hover{
        opacity: 0.6;
    }
    .reviewItem{
        margin-top: 17px;
        display: flex;
        align-items: center;  
    }
    .txtarea{
        width: 329px;
        height: 80px;
        border-radius: 10px;
        border: 1px solid #D2D2D7;
        box-sizing: border-box;
        padding: 10px;
        
        textarea{
            display: block;
            width: 100%;
            height: 100%;
            border: none;
            outline: none;
            resize: none;
        }
    }
    .secondPart{
        box-sizing: border-box;
        padding: 40px;
        
        .secondTitle{
            font-size: 16px;
            color: rgba(29,29,31,0.9);
        }
    }
    
    .selContent{
        width: 16px;
        height: 16px;
        border: 1px solid #AAAAAA;
        box-sizing: border-box;
        padding: 3px;
        border-radius: 50%;
        cursor: pointer;
        margin-right: 15px;
        
        .inner{
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
    .reviewImage{
        height: 60px;
        width: 246px;
        
        img{
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    }
    ::v-deep .el-upload-dragger{
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

    .avatar-box {
        width: 410px;
        height: 533px;
        background: #ffffff;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);

        .firstPart {
            box-sizing: border-box;
            padding: 27px 40px;
            border-bottom: 1px solid #d2d2d7;
            .firstTitle {
                font-size: 16px;
                color: rgba(29, 29, 31, 0.9);
            }
        }

    }

    .add:hover {
        background: rgba(26, 115, 232, 0.1) !important;
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
</style>