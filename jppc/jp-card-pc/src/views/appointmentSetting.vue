<template>
    <div>
        <div class="tit">
            {{ shows == true ? "予約詳細設定" : "予約基本設定" }}
        </div>
        <div class="dan no-size" v-if="shows">担当者：{{ names }}</div>
        <div class="setting-box" v-if="!shows">
            <div class="set-tit no-size">
                <div class="no-check shou" v-if="!checked" @click="change()"></div>
                <div class="checked shou" v-if="checked" @click="change()">
                    <span></span>
                </div>
                <div class="tit-name">指名制予約を利用する</div>
            </div>
            <div class="yuezhi no-size">
                オフにすると指名制の予約は利用できません。
                予約に対応するスタッフの人数分の枠数で予約を受け付けます。
            </div>
            <div class="set-tit no-size" style="border-top: 1px solid #d2d2d7">
                <div class="tit-set">予約に対応するスタッフを選択</div>
            </div>
            <div class="selet">

                <div class="zhi no-size" style="justify-content: space-between; padding-right: 40px" v-if="checked">
                    <div class="left">
                        <div class="no-check shou" v-if="no_appoint == false" @click="noChange"></div>
                        <div class="checked shou" v-if="no_appoint == true" @click="noChange">
                            <span></span>
                        </div>
                        <div :class="no_appoint == true ? 'av-name' : 'name'">指名なし</div>
                    </div>
                </div>

                <div class="zhi no-size" style="justify-content: space-between; padding-right: 40px"
                    v-for="(item, index) in nameList" :key="index">
                    <div class="left">
                        <div class="no-check shou" v-if="item.is_appoint == false" @click="itemChange(index, item.id)">
                        </div>
                        <div class="checked shou" v-if="item.is_appoint" @click="itemChange(index, item.id)">
                            <span></span>
                        </div>
                        <div :class="item.is_appoint ? 'av-name' : 'name'">{{ item.name }}</div>
                        <div class="color" :style="{ background: item.color }" v-if="item.color"></div>
                        <div class="color" style="background: #d2d2d7;" v-else></div>
                    </div>
                    <div class="rigt shou" v-if="item.is_appoint == true" @click="setDetail(item.name,item.id)">
                        詳細設定
                    </div>
                </div>
            </div>
        </div>
        <div class="set-detail" v-if="shows == true">
            <div class="tabs">
                <div :class="tabsShow ? 'lef active' : 'lef shou'" @click="tabs(1)">
                    対応メニュー
                </div>
                <div :class="!tabsShow ? 'rig active' : 'rig shou'" @click="tabs(2)">
                    その他設定
                </div>
            </div>
            <div class="all-xuan no-size" v-if="tabsShow">
                <div class="no-check shou" v-if="!allCheck" @click="allChange()"></div>
                <div class="checked shou" v-if="allCheck" @click="allChange()">
                    <span></span>
                </div>
                <div class="xuan">すべてのメニューに対応</div>
            </div>
            <div class="detai-box" v-if="tabsShow">
                <div class="items" v-for="(item, index) in list" :key="index">
                    <div class="items-menu-title no-size">{{ item.name }}</div>
                    <div class="item no-size" v-for="(item1, index1) in item.menus" :key="index1">
                        <div class="le">
                            <div class="f-no-check shou" v-if="!item1.check" @click="menuCheck(item1.id, 1)"></div>
                            <div class="f-check shou" v-if="item1.check" @click="menuCheck(item1.id, 2)">
                                <img src="../static/fang-check.png" alt="" />
                            </div>
                        </div>
                        <img :src="item1.image[0]" alt="" style="object-fit: cover;" v-if="item1.image.length > 0" />
                        <div class="rig">
                            <div class="tit">{{ item1.title }}</div>
                            <div class="price">{{ item1.price }}</div>
                            <div class="time">所要時間:{{ item1.order_time }}分</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="setting-b no-size" v-if="!tabsShow">
                <div class="sex-b no-size">
                    <div class="all-x no-size">
                        <div class="no-check shou" v-if="!sexChange" @click="sexAllChange()"></div>
                        <div class="checked shou" v-if="sexChange" @click="sexAllChange()">
                            <span></span>
                        </div>
                        <div class="xuan" :style="sexChange?'color:#1d1d1f !important' : ''">すべてのジェンダーに対応</div>
                    </div>
                    <div class="sex-flex">
                        <div class="s-item" v-for="(item, index) in sexList" :key="index">
                            <div class="f-no-check shou" v-if="!item.check" @click="sexCheck(item.id, 1)"></div>
                            <div class="f-check shou" v-if="item.check" @click="sexCheck(item.id, 2)">
                                <img src="../static/fang-check.png" alt="" />
                            </div>
                            <div :class="item.check ? 'active' : 'names'">
                                {{ item.name }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="set-liao no-size">
                    <div class="no-check shou" v-if="admins.is_appoint_price == 0" @click="changeMoney"></div>
                    <div class="checked shou" v-if="admins.is_appoint_price == 1" @click="changeMoney">
                        <span></span>
                    </div>

                    <div class="name"> <span :style="admins.is_appoint_price == 1?'color:#1d1d1f' : ''">指名料金を設定する</span> <br/> <span style="color: red;font-size: 14px !important;">半角数字のみ</span> </div>
                    <div style="margin:0 10px;width: 200px;" v-if="admins.is_appoint_price == 1">
                        <el-input placeholder="0" type="number" v-model="admins.appoint_price" style="border-radius: 25px;" clearable></el-input>
                    </div>
                    <div v-if="admins.is_appoint_price == 1">
                        円
                    </div>
                </div>
                
                <div class="dan-color no-size">
                    <div class="name">担当者別カラー</div>
                    <div class="xuanze">
                        <div class="selet-color" :style="{
                position: 'absolute',
                left: '20px',
                bottom: '14px',
                background: seletColor,
                width: '20px',
                height: '20px',
                borderRadius: '4px',
                zIndex: 10,
              }"></div>
                        <el-select v-model="value" placeholder="選択してください">
                            <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value"
                                :disabled="item.disabled" @click.native="getIndex(item.color)">
                                <div class="cl" style="display: flex; align-items: center">
                                    <div :style="{
                      background: item.color,
                      width: '20px',
                      height: '20px',
                      borderRadius: '4px',
                      marginRight: '16px',
                    }"></div>
                                    <span>{{ item.value }}</span>
                                </div>
                            </el-option>
                        </el-select>
                    </div>
                </div>
            </div>
            <div class="bot-btn">
                <div class="le shou" @click="shows = false">戻　る</div>
                <div class="ri shou" @click="keep()">保　存</div>
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
        getStaffList,
        getFuDetail,
        getCates,
        getMenuList,
        setZhi,
        getStore,
        editStaffSkill,
        setZhi2
    } from "@/http/api.js"
    export default {
        name: "",
        components: {},
        data() {
            return {
                admins:{},
                zhiPriceShow: false,
                sexChange: false,
                keepShow: false,
                allCheck: false,
                shows: false,
                checked: false,
                check: false,
                tabsShow: true,
                names: "",
                current: 0,
                money: 0,
                seletColor: "",
                // 菜单id
                pid:"",
                menuid: [],
                // 性别id
                sexId: [],
                nameList: [{
                    name: "指名なし",
                    color: "",
                    check: false,
                    id: 0,
                }],
                no_appoint: false,
                list: [],
                options: [{
                        value: "コバルト",
                        label: "コバルト",
                        color: "#175491",
                    },
                    {
                        value: "パープル",
                        label: "パープル",
                        color: "#5F28B7",
                    },
                    {
                        value: "セージ",
                        label: "セージ",
                        color: "#00A5A5",
                    },
                    {
                        value: "マスタード",
                        label: "マスタード",
                        color: "#D9B625 ",
                    },
                    {
                        value: "マリン",
                        label: "マリン",
                        color: "#007AFF",
                    },
                    {
                        value: "オレンジ",
                        label: "オレンジ",
                        color: "#ED8E20 ",
                    },
                    {
                        value: "ブラック",
                        label: "ブラック",
                        color: "#1D1D1F",
                    },
                    {
                        value: "グリーン",
                        label: "グリーン",
                        color: "#1F8037 ",
                    },
                    {
                        value: "ピンク",
                        label: "ピンク",
                        color: "#EA459E ",
                    },
                    {
                        value: "ボルドー",
                        label: "ボルドー",
                        color: "#7B201B",
                    },
                ],
                value: "",
                sexList: [{
                        check: false,
                        id: 1,
                        name: "男性",
                    },
                    {
                        check: false,
                        id: 2,
                        name: "女性",
                    },
                    {
                        check: false,
                        id: 3,
                        name: "Xジェンダー",
                    },
                ],
            };
        },
        created() {
            let that = this
            that.getStaffList()
            that.getList()
            that.getShopInfo()
        },
        methods: {
            changeMoney(){
                let that = this 
                console.log(that.admins.is_appoint_price)
                // that.admins.is_appoint_price = 1
                if(that.admins.is_appoint_price == 1){
                    that.admins.is_appoint_price = 0
                }else{
                    that.admins.is_appoint_price = 1
                }
            },
            noChange(){
              let that = this
              let data = {}
              data.type = "no_appoint"
              data.no_appoint = that.no_appoint == true?0:1
              setZhi2(data).then((res) => {
                  if(res.code == 200){
                      that.no_appoint = !that.no_appoint
                  }else{
                      that.$message({
                          message: res.message,
                          type: 'error',
                          offset: 400
                      });
                  }
              })
            },
            getShopInfo() {
                let that = this
                getStore().then((res) => {
                    console.log("shopInfo", res)
                    if (res.code == 200) {
                        that.checked = res.data.shop_info.is_appoint == 1 ? true : false
                        that.no_appoint = res.data.shop_info.no_appoint == 1 ? true : false
                    }
                })
            },
            getList() {
                let that = this
                getCates().then((res) => {
                    console.log("res", res)
                    if (res.code == 200) {
                        getMenuList().then((result) => {
                            if (result.code == 200) {
                                res.data.cates.forEach((item, index) => {
                                    item.menus = []
                                    result.data.menus.forEach((ite, idx) => {
                                        if (ite.cid == item.id) {
                                            ite.check = false
                                            item.menus.push(ite)
                                        }
                                    })
                                })
                                that.list = res.data.cates
                                console.log(that.list)
                            }
                        })
                    }
                })
            },
            getStaffList() {
                let that = this
                let data = {}
                data['startTime'] = " "
                data["endTime"] = ' '
                data['id'] = " "
                getStaffList(data).then((res) => {
                    console.log("staff", res)
                    if (res.code == 200) {
                        res.data.admins.forEach((v, i) => {
                            v.checked = false
                        })
                        that.nameList = res.data.admins
                    }
                })
            },
            getInfo() {
                let that = this
                getStore().then((res) => {
                    console.log("res", res)
                })
            },
           
            keep() {
                let that = this
                let arr = that.list
                let data = {}
                let as = []
                console.log(arr)
                arr.forEach((v,i) => {
                    v.menus.forEach((val,idx) => {
                        if(val.check == true){
                            as.push(val.id)
                        }
                    })
                })
                data['smids'] = as.join()
                data['sex'] = that.sexId.toString()
                data['appoint_price'] = that.admins.appoint_price
                data['color'] = that.seletColor
                data['is_appoint_price'] = that.admins.is_appoint_price
                editStaffSkill(data, that.pid).then((res) => {
                    if (res.code == 200) {
                        that.keepShow = true;
                        setTimeout(() => {
                            that.keepShow = false;
                            history.go(0)
                        }, 1000);
                    } else {
                        that.$message.error(res.message)
                    }
                })
                console.log(data)

            },
            // 性别全选择
            sexAllChange() {
                console.log(this.sexChange)
                if (this.sexChange) {
                    this.sexChange = false;
                    this.sexList.forEach((item) => {
                        item.check = false;
                         this.sexId = [];
                    });
                } else {
                    this.sexChange = true;
                    this.sexList.forEach((item) => {
                        item.check = true;
                        this.sexId.push(item.id);
                    });
                }
            },
            // 菜单全选
            allChange() {
                if (this.allCheck) {
                    this.allCheck = false;
                    this.list.forEach((item) => {
                        item.menus.forEach((item1) => {
                            item1.check = false;
                            this.menuid = [];
                        });
                    });
                } else {
                    this.allCheck = true;
                    this.list.forEach((item) => {
                        item.menus.forEach((item1) => {
                            item1.check = true;
                            this.menuid.push(item1.id);
                        });
                    });
                }
            },
            // 判断菜单是否全选
            all() {
                this.list.forEach((item) => {
                    item.menus.forEach((item1) => {
                        if (!item1.check) {
                            this.allCheck = false;
                            throw new Error("结束");
                        } else if (item1.check) {
                            this.allCheck = true;
                        }
                    });
                });
            },
            // 判断性别是否全选
            sexAll() {
                this.sexList.forEach((item) => {
                    if (!item.check) {
                        this.sexChange = false;
                        throw new Error("结束");
                    } else if (item.check) {
                        this.sexChange = true;
                    }
                });
            },
            // 性别选择
            sexCheck(id, i) {
                let arr = [];
                if (i == 1) {
                    this.sexList.forEach((item) => {
                        if (item.id == id) {
                            item.check = true;
                        }
                        if (item.check == true) {
                            arr.push(item.id);
                        }
                    });
                } else if (i == 2) {
                    this.sexList.forEach((item) => {
                        if (item.id == id) {
                            item.check = false;
                        }
                        if (item.check == true) {
                            arr.push(item.id);
                        }
                    });
                }
                this.sexId = arr;
                this.sexAll();
            },
            // 菜单选择
            menuCheck(id, i) {
                // this.menuid = [];
                let arr = [];
                if (i == 1) {
                    this.list.forEach((item) => {
                        item.menus.forEach((item1) => {
                            if (item1.id == id) {
                                item1.check = true;
                            }
                            if (item1.check == true) {
                                arr.push(item1.id);
                            }
                        });
                    });
                } else if (i == 2) {
                    this.list.forEach((item) => {
                        item.menus.forEach((item1) => {
                            if (item1.id == id) {
                                item1.check = false;
                            }
                            if (item1.check == true) {
                                arr.push(item1.id);
                            }
                        });
                    });
                }
                this.menuid = arr;
                console.log("sex",this.sexId);
                this.all();
            },
            tabs(i) {
                if (i == 1) {
                    this.tabsShow = true;
                } else {
                    this.tabsShow = false;
                }
            },
            setDetail(itm, id) {
                let that = this
                that.shows = true;
                that.names = itm;
                that.pid = id
                let data = {}
                data['id'] = id
                getFuDetail(data).then((res) => {
                    console.log("datas",res)
                    if (res.code == 200) {
                        that.admins = res.data.admin
                        that.allCheck = true
                        that.sexChange = true
                        that.list.forEach((value, index) => {
                            value.menus.forEach((val, idx) => {
                                if (res.data.admin.smids.indexOf(val.id) != -1) {
                                    val.check = true
                                } else {
                                    that.allCheck = false
                                }
                            })
                        })
                        that.options.forEach((value, index) => {
                            if (value.color.split(" ").join("") == res.data.admin.color.split(" ").join("")) {
                                that.seletColor = value.color
                                that.value = value.value
                            }
                        })

                        that.sexList.forEach((value, index) => {
                            if (res.data.admin.sex.indexOf(value.id) != -1) {
                                value.check = true
                            } else {
                                that.sexChange = false
                            }
                        })
                        that.sexId = res.data.admin.sex.split(",")
                        if (res.data.admin.appoint_price) {
                            that.money = res.data.admin.appoint_price
                            that.zhiPriceShow = true
                        } else {
                            that.zhiPriceShow = false
                            that.money = 0
                        }

                    }
                })
            },
            change() {
                let that = this
                let type = that.checked == true ? 0 : 1
                setZhi(type).then((res) => {
                    if (res.code == 200) {
                        that.$message({
                            message: res.message,
                            type: 'success',
                            offset: 400
                        });
                        if (!that.checked) {
                            that.checked = true;
                        } else {
                            that.checked = false;
                            that.no_appoint = false
                            that.nameList.forEach((v, i) => {
                                v.is_appoint = false
                            })
                            console.log(that.nameList)
                        }
                    } else {
                        that.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });
                    }
                })

            },
            danChange() {
                if (!this.checked) {
                    return;
                }
                if (!this.check) {
                    this.check = true;
                    this.nameList.forEach((item) => {
                        item.check = false;
                    });
                } else {
                    this.check = false;
                }
            },
            itemChange(i, id) {

                let that = this
                let options = {}
                options.text = "読み込み中"
                that.$loading(options)
                let data = {}
                data.is_appoint = !that.nameList[i].is_appoint
                editStaffSkill(data, id).then((res) => {
                    that.$loading().close()
                    if (res.code == 200) {
                        that.nameList[i].is_appoint = !that.nameList[i].is_appoint
                    } else {
                        that.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });
                    }
                })
            },
            getIndex(color) {
                this.seletColor = color;
            },
        },
    };
</script>

<style lang="scss" scoped>
    .tit {
        color: #1d1d1f;
        font-size: 21px;
        font-weight: bold;
    }
    ::v-deep .el-input__inner {
        border-radius: 24px !important;
    }
    .no-check {
        width: 20px;
        height: 20px;
        background: #ffffff;
        border-radius: 50%;
        border: 1px solid #707070;
    }

    .checked {
        width: 20px;
        height: 20px;
        background: #ffffff;
        border-radius: 50%;
        border: 1px solid #1a73e8;

        span {
            display: inline-block;
            width: 14px;
            height: 14px;
            background: #1a73e8;
            border-radius: 50%;
            margin: 3px;
        }
    }

    .setting-box {
        margin-top: 20px;
        width: 820px;
        // height: 443px;
        background: #ffffff;
        padding-bottom: 40px;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);

        .set-tit {
            display: flex;
            align-items: center;
            width: 100%;
            height: 50px;
            background: #f5f5f7;
            border-bottom: 1px solid #d2d2d7;
            padding-left: 40px;

            .tit-name {
                margin-left: 10px;
                color: #1d1d1f;
                font-size: 16px;
                font-weight: bold;
            }

            .tit-set {
                font-weight: 540;
            }
        }

        .yuezhi {
            height: 96px;
            padding-left: 63px;
            padding-right: 40px;
            padding-top: 21px;
            font-size: 16px;
            color: #707070;
        }

        .selet {
            .zhi {
                display: flex;

                align-items: center;
                margin-left: 40px;
                width: 780px;
                height: 68px;
                border-bottom: 1px solid #d2d2d7;

                .left {
                    display: flex;
                    align-items: center;
                    height: 100%;
                }

                .name {
                    margin-left: 20px;
                    color: #707070;
                    font-size: 16px;
                }

                .av-name {
                    margin-left: 20px;
                    color: #1d1d1f;
                    font-size: 16px;
                }

                .color {
                    width: 20px;
                    height: 20px;
                    border-radius: 4px 4px 4px 4px;
                    margin-left: 16px;
                }

                .rigt {
                    width: 92px;
                    height: 44px;
                    background: #1a73e8;
                    color: #ffffff;
                    text-align: center;
                    line-height: 44px;
                    font-size: 16px;
                    border-radius: 10px;
                }
            }
        }
    }

    .dan {
        margin-top: 30px;
        width: 410px;
        height: 62px;
        padding-left: 20px;
        line-height: 62px;
        background: #ffffff;
        border-radius: 10px 10px 10px 10px;
        opacity: 1;
        border: 1px solid #707070;
        color: #1d1d1f;
        font-size: 20px;
        font-weight: 700;
    }

    ::v-deep .set-detail {
        width: 820px;
        // height: 1124px;
        background: #ffffff;
        margin-top: 31px;
        padding-bottom: 51px;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);

        .tabs {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            height: 50px;

            .lef,
            .rig {
                flex: 1;
                color: #1d1d1f;
                background: #f5f5f7;
                font-size: 16px;
                height: 50px;
                text-align: center;
                line-height: 50px;
                font-weight: 600;
                border-bottom: 1px solid #d2d2d7;
            }

            .active {
                background: #1a73e8;
                border: 0;
                color: #ffffff;
            }
        }

        .all-xuan {
            display: flex;
            align-items: center;
            height: 101px;
            padding-left: 40px;

            .xuan {
                margin-left: 10px;
                color: #1d1d1f;
                font-size: 16px;
                font-weight: 550;
            }
        }

        .detai-box {
            width: 100%;

            .items {
                width: 100%;

                .items-menu-title {
                    width: 100%;
                    height: 40px;
                    line-height: 40px;
                    padding-left: 20px;
                    font-size: 16px;
                    color: #1d1d1f;
                    font-weight: 550;
                    background: rgba(29, 29, 31, 0.1);
                }

                .item {
                    // height: 120px;
                    padding-top: 20px;
                    padding-bottom: 40px;
                    margin-left: 40px;
                    border-bottom: 1px solid #d2d2d7;
                    display: flex;
                    align-items: center;

                    .le {
                        width: 16px;
                        margin-right: 20px;
                    }

                    .f-no-check {
                        width: 16px;
                        height: 16px;
                        border-radius: 2px 2px 2px 2px;
                        border: 1px solid #aaaaaa;
                    }

                    .f-check {
                        img {
                            width: 16px;
                            height: 16px;
                            border-radius: 2px 2px 2px 2px;
                        }
                    }

                    img {
                        width: 100px;
                        height: 100px;
                        border-radius: 6px;
                        margin-right: 20px;
                    }

                    .rig {
                        .tit {
                            color: #1d1d1f;
                            font-size: 16px;
                            font-weight: 600;
                            margin-bottom: 16px;
                        }

                        .price {
                            font-size: 16px;
                            color: rgba(217, 48, 37, 0.65);
                            margin-bottom: 16px;
                        }

                        .time {
                            font-size: 16px;
                            color: #707070;
                        }
                    }
                }
            }
        }

        .setting-b {
            .all-x {
                display: flex;

                .xuan {
                    margin-left: 10px;
                    color: #707070;
                    font-weight: 600;
                    font-size: 16px;
                }
            }

            .sex-flex {
                display: flex;
                padding-left: 70px;
            }

            .sex-b {
                padding-bottom: 41px;
                padding-left: 40px;
                padding-top: 32px;
                // height: 147px;
                border-bottom: 1px solid #d2d2d7;

                .s-item {
                    display: flex;
                    // align-items: center;
                    margin-top: 42px;
                    margin-right: 60px;
                }

                .f-no-check {
                    margin-right: 20px;
                    width: 16px;
                    height: 16px;
                    border-radius: 2px 2px 2px 2px;
                    border: 1px solid #aaaaaa;
                }

                .f-check {
                    margin-right: 20px;

                    img {
                        width: 16px;
                        height: 16px;
                        border-radius: 2px 2px 2px 2px;
                    }
                }

                .names {
                    font-size: 16px;
                    line-height: 1.2em;
                    color: #707070;
                }

                .active {
                    font-weight: 700;
                    font-size: 16px;
                    color: #1d1d1f;
                }
            }

            .set-liao {
                height: 90px;
                display: flex;
                align-items: center;
                border-bottom: 1px solid#d2d2d7;
                padding-left: 41px;

                .name {
                    margin-left: 10px;
                    font-size: 16px;
                    font-weight: 600;
                    color: #707070;
                }
            }

            .dan-color {
                padding-top: 31px;
                padding-left: 40px;
                padding-bottom: 40px;
                border-bottom: 1px solid#d2d2d7;

                .name {
                    font-size: 16px;
                    font-weight: 600;
                    color: #707070;
                    margin-bottom: 16px;
                }

                .xuanze {
                    position: relative;

                    .el-select {}
                }

                .el-input__inner {
                    padding-left: 50px;
                    width: 330px;
                    height: 48px;
                    background: rgba(29, 29, 31, 0.1);
                    border-radius: 10px 10px 10px 10px;
                    opacity: 1;
                    border: 1px solid #707070;
                }

                .el-select .el-input .el-select__caret {
                    color: #707070;
                }

                .el-select-dropdown__item {
                    .cl {
                        span {
                            position: absolute;
                            top: 0;
                            left: 0;
                            z-index: 999;
                            width: 20px;
                            height: 20px;
                            border-radius: 4px;
                            margin-right: 16px;
                        }
                    }
                }
            }
        }

        .bot-btn {
            display: flex;
            align-items: center;
            width: 300px;
            margin: 0 auto;
            margin-top: 40px;

            .le,
            .ri {
                width: 120px;
                height: 40px;
                background: rgba(29, 29, 31, 0.1);
                border-radius: 26px;
                border: 1px solid #707070;
                color: #707070;
                font-size: 16px;
                text-align: center;
                line-height: 40px;
            }

            .ri {
                margin-left: 51px;
                border: 0;
                background: #1a73e8;
                border-radius: 26px;
                color: #ffffff;
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