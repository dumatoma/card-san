<template>
    <div>
        <div class="tit u-flex u-row-between">
            <div>メニュー > カテゴリー一覧</div>
            <div class="append no-size u-flex-center" @click="addNew">
                <i class="el-icon-circle-plus" style="color: #1a73e8; margin-right: 5px"></i>
                カテゴリー追加
            </div>
        </div>
        <div class="cont no-size" v-el-dropzone="list">
            <div class="cont_item no-size u-flex u-row-between" v-for="item,index in list" :key="item.id"
                v-el-draggable="item">
                <span class="menuname" @click="editItem(item.id,item.name)">{{item.name}}</span>

                <div style="display: flex;align-items: center;">
                    <img src="@/static/svg/upup.svg" style="margin-right: 20px;width: 26px;height: 26px;" class="shou"
                        @click.stop="sort(item,index)" v-if="index != 0" />
                    <el-tooltip class="item" effect="dark" content="削除" placement="bottom">
                        <img src="@/static/index/delete.png" class="shou" @click.stop="delthis(item.id)" />
                    </el-tooltip>

                </div>
            </div>
        </div>
        <!-- 追加弹窗 -->
        <div class="fixed" v-show="appendShow">
            <div class="append-box no-size">
                <div class="app_top u-flex-center">
                    {{ edit ? "カテゴリー編集" : "カテゴリー追加" }}
                    <div class="cha shou u-flex-center" @click="appendShow = false">
                        <!-- <i class="el-icon-close" style="font-weight: bold"></i> -->
                        <img src="../../static/index/close.png" alt="" />
                    </div>
                </div>
                <div class="app_center u-flex-col u-col-top">
                    カテゴリー名
                    <input type="text" class="int no-size" placeholder="メニューカテゴリーを入力してください" v-model="cateName" />
                </div>
                <div class="btn shou u-flex-center" @click="keepClick">保　存</div>
            </div>
        </div>
        <!-- 保存弹窗 -->
        <div class="fixed" v-show="keepShow">
            <div class="zhe"></div>
            <div class="sus-box">
                <img src="@/static/success.png" alt="" />
                <div class="keep">保存しました</div>
            </div>
        </div>
        <!-- 删除时的弹窗 -->
        <div class="fixed" v-if="showDel">
            <div class="delcontent">
                <div class="delTitle">
                    このカテゴリーを削除しますか？
                </div>
                <div class="delContent">
                    このカテゴリーを削除すると、カテゴリー内に登録されたメニューも全て削除されます。<br /><br /> 残したいメニューがある場合は、別のカテゴリーに設定・登録してから削除してください。
                </div>
                <div class="confi" @click="deleteItem">
                    <div>
                        削除
                    </div>
                </div>
                <div class="cancl" @click="showDel = false">

                    <div>
                        キャンセル
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        getCates,
        deleteCate,
        addCate,
        editCate,
        menucatesort
    } from "@/http/api.js"
    export default {

        data() {
            return {
                appendShow: false,
                edit: false,
                keepShow: false,
                list: [],
                cateName: "",
                id: "",
                showDel: false
            };
        },
        created() {
            let that = this
            that.getCates()
        },

        methods: {
            sort(item, index) {
                let that = this
                let ar = []
                that.list.forEach((v, i) => {
                    ar.push(v.id)
                })
                that.swapArrayElements(ar, index * 1 - 1, index)
                let data = {}
                data.ids = ar
                menucatesort(data).then((res) => {
                    if (res.code == 200) {
                        that.getCates()
                    } else {
                        that.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });
                    }
                })
            },
            swapArrayElements(arr, indexA, indexB) {
                const temp = arr[indexA];
                arr[indexA] = arr[indexB];
                arr[indexB] = temp;
            },
            getCates() {
                let that = this
                getCates().then((res) => {
                    console.log(res)
                    if (res.code == 200) {
                        that.list = res.data.cates
                        that.showDel = false
                    }
                })
            },
            delthis(id) {
                let that = this
                that.id = id
                that.showDel = true
            },
            editItem(id, name) {
                let that = this
                that.edit = true
                that.appendShow = true
                that.cateName = name
                that.id = id
            },

            addNew() {
                let that = this
                that.edit = false
                that.appendShow = true
                that.cateName = ""
            },
            showdelcontent(id, index) {
                let that = this
                that.showDel = true
            },
            deleteItem() {
                let that = this
                let id = that.id
                deleteCate(id).then((res) => {
                    if (res.code == 200) {
                        that.$message({
                            message: res.message,
                            type: 'success',
                            offset: 400
                        });
                        that.getCates()
                    } else {
                        that.$message.error(res.message)
                    }
                })
            },

            keepClick() {
                let that = this
                if (that.edit == false) {
                    addCate(that.cateName).then((res) => {
                        if (res.code == 200) {
                            that.keepShow = true;
                            that.appendShow = false;
                            setTimeout(() => {
                                that.keepShow = false;
                                that.getCates()
                            }, 1500);
                        }
                    })
                } else {
                    let data = {}
                    data._method = "put"
                    data.name = that.cateName
                    editCate(data, that.id).then((res) => {
                        if (res.code == 200) {
                            that.appendShow = false
                            that.$message({
                                message: res.message,
                                type: 'success'
                            });
                            that.getCates()
                        } else {
                            that.$message.error(res.message)
                        }
                    })
                }

            },
        },
        // directives: {
        //     "el-draggable": {
        //         bind(el, binding, vnode) {
        //             el.draggable = true
        //             el.addEventListener('dragstart', (event) => {
        //                 console.log("value",binding.value)
        //                 event.dataTransfer.setData('text/plain', JSON.stringify(binding.value))
        //             })
        //             el.addEventListener('dragend',() => {
        //                 vnode.context.$forceUpdate();
        //             })
        //         }
        //     },
        //     'el-dropzone': {
        //         bind(el, binding, vnode) {
        //             el.addEventListener('dragover', (event) => {
        //                 event.preventDefault()
        //             })
        //             el.addEventListener('drop', (event) => {
        //                 event.preventDefault()
        //                 let draggedItem = JSON.parse(event.dataTransfer.getData('text/plain'))
        //                 let newList = binding.value.slice()
        //                 let index = newList.findIndex(item => item.id === draggedItem.id)
        //                 newList.splice(index, 1)
        //                 newList.splice(el.dataset.index, 0, draggedItem)
        //                 vnode.context[binding.expression] = newList
        //                 console.log("newList",vnode,el,binding)
        //             })
        //         }
        //     }
        // },
    };
</script>

<style lang="scss" scoped>
    .delcontent {
        width: 345px;
        height: 389px;
        background: #FFFFFF;
        border-radius: 10px;
        box-sizing: border-box;
        padding: 24px 20px;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);

        .delTitle {
            font-family: Hiragino Sans, Hiragino Sans;
            font-weight: normal;
            font-size: 16px;
            color: #1D1D1F;
            text-align: center;
            margin-bottom: 37px;
        }

        .delContent {
            font-family: Hiragino Sans, Hiragino Sans;
            font-weight: normal;
            font-size: 14px;
            color: #1D1D1F;
            text-align: center;
        }

        .confi {
            width: 240px;
            height: 48px;
            background: #D93025;
            border-radius: 6px;
            text-align: center;
            // line-height: 48px;
            color: #fff;
            font-family: Noto Sans Kannada, Noto Sans Kannada;
            font-weight: bold;
            font-size: 16px;
            color: #FFFFFF;
            margin: 52px auto 30px;
            vertical-align: middle;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .confi:hover {
            opacity: 0.6 !important;
            cursor: pointer;
        }

        .cancl:hover {
            opacity: 0.6 !important;
            cursor: pointer;
        }

        .cancl {
            width: 240px;
            height: 48px;
            background: rgba(29, 29, 31, 0.2);
            border-radius: 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #707070 !important;
            font-family: Noto Sans Kannada, Noto Sans Kannada;
            font-weight: bold;
            font-size: 16px;
            color: #FFFFFF;
            margin: 0 auto;
        }

    }

    .append:hover {
        background: rgba(26, 115, 232, .1) !important;
        cursor: pointer;
    }

    .menuname:hover {
        color: #1a73e8 !important;
        text-decoration: underline;
        cursor: pointer;
    }

    .tit {
        width: 820px;
        font-size: 21px;
        color: #1d1d1f;
        font-weight: bold;

        .append {
            width: 176px;
            height: 40px;
            background: #ffffff;
            font-size: 16px;
            color: #1a73e8;
            border-radius: 20px;
            border: 1px solid #1a73e8;
        }
    }

    .cont {
        width: 820px;
        background: #ffffff;
        padding: 40px;
        margin-top: 20px;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);

        .cont_item {
            width: 740px;
            height: 60px;
            font-size: 16px;
            color: #1d1d1f;
            font-weight: bold;
            background: #f5f5f7;
            border-radius: 10px;
            margin-bottom: 10px;
            padding: 22px 20px 22px 20px;

            &:last-child {
                margin: 0;
            }

            img {
                width: 38px;
            }
        }
    }

    .fixed {
        position: fixed;
        top: 0%;
        left: 0%;
        width: 100vw;
        height: 100vh;
        background: rgba(29, 29, 31, 0.2);

        .append-box {
            width: 510px;
            height: 336px;
            background: #ffffff;
            border-radius: 16px 16px 0 0;
            box-shadow: 0px 5px 10px 1px rgba(0, 0, 0, 0.16);
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;

            .app_top {
                height: 52px;
                font-size: 20px;
                color: #1d1d1f;
                font-weight: bold;
                background: #f5f5f7;
                border-radius: 16px 16px 0 0;

                .cha {
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    position: absolute;
                    top: 12px;
                    right: 20px;
                    // background: #000;

                    img {
                        width: 100%;
                        height: 100%;
                    }
                }
            }

            .app_center {
                color: #86868b;
                font-size: 14px;
                padding: 50px 40px 70px 40px;
                border-top: 2px solid #d2d2d7;
                border-bottom: 1px solid #d2d2d7;

                .int {
                    width: 430px;
                    height: 40px;
                    background: #ffffff;
                    border-radius: 10px;
                    padding-left: 12px;
                    margin-top: 5px;
                    font-size: 16px;
                    outline: 0;
                    border: 1px solid #d2d2d7;
                }

                ::-webkit-input-placeholder {
                    /* Chrome/Opera/Safari */
                    font-size: 16px;
                    color: #aaa;
                }

                ::-moz-placeholder {
                    /* Firefox 19+ */
                    font-size: 16px;
                    color: #aaa;
                }

                :-ms-input-placeholder {
                    /* IE 10+ */
                    font-size: 16px;
                    color: #aaa;
                }

                :-moz-placeholder {
                    /* Firefox 18- */
                    font-size: 16px;
                    color: #aaa;
                }
            }

            .btn {
                width: 120px;
                height: 40px;
                font-size: 16px;
                color: #ffffff;
                margin: 19px auto;
                background: #1a73e8;
                border-radius: 20px;
            }
        }

        .zhe {
            position: absolute;
            width: 100vw;
            height: 100vh;
            background: rgba(29, 29, 31, 0.2);
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
    }
</style>