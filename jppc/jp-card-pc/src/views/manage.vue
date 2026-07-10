<template>
    <div>
        <div class="title">メンバー管理</div>
        <div style="width: 820px">
            <div class="box no-size">
                <div style="padding: 40px 23px">
                    <div class="u-flex u-row-between">
                        <div class="search no-size u-flex">
                            <img src="@/static/index/search.png" style="width:18px;">
                            <div class="left" style="flex-shrink: 0;">検索</div>
                            <el-input
                                style="border: none !important;outline:none !important;background: transparent !important;"
                                placeholder="名前、電話番号、IDで検索" @blur="searchMember" v-model="keywords"
                                @keyup.enter.native="searchMember"></el-input>
                            <img @click="keywords = ''" v-if="keywords != ''" src="../static/searchclose.svg"
                                style="width: 16px;height: 16px;cursor: pointer;" alt="" />
                        </div>
                        <div class="CSV u-flex u-row-center shou" @click="goDownload">
                            Export CSV
                        </div>
                    </div>
                    <div class="choice u-flex">
                        <div class="left">並び替え</div>
                        <el-select v-model="industry" @change="selectChanged">
                            <el-option v-for="item in industryList" :key="item" :label="item" :value="item">
                            </el-option>
                        </el-select>
                    </div>
                    <div class="left" style="margin-top: 30px">メンバー数:{{total}}件</div>
                </div>

                <!-- 表格 -->
                <div class="content">
                    <div class="selectAll">
                        <div :class="[
                'select',
                SelectId.length == CheckList.length
                  ? 'selectActv selectCss'
                  : '',
              ]" @click="allFun()"></div>
                        <div style="width: 30px; margin-right: 20px"></div>
                        <div class="name big no-size" style="padding-left: 12px">名前</div>
                        <div class="grey big">カナ</div>
                        <div class="grey big">電話番号</div>
                        <div class="grey big sixty">顧客NO</div>
                        <div class="grey big hundred_thirty">会員ID</div>
                    </div>
                    <div class="CheckBox">
                        <div class="CheckItem" v-for="(item, index) in CheckList" :key="index">
                            <div class="u-flex shou">
                                <div :class="[
                    'select',
                    SelectId.includes(item.id) ? 'selectActv selectCss' : '',
                  ]" @click="selectFun(item.id)"></div>
                                <img :src="item.avatar" alt="" class="ava"/>
                                <div @click="goDetail(item.id)" class="name">{{ item.name }}</div>
                                <div class="grey">{{ item.pronunciation }}</div>
                                <div class="grey">{{ item.phone }}</div>
                                <div class="grey sixty">{{ item.member_no }}</div>
                                <div class="grey hundred_thirty">{{ item.member_id }}</div>
                            </div>
                            <div class="dot" v-if="!SelectId.includes(item.id)">
                                <img class="dot" v-if="item.hovers == false" @mouseover="hoverthis(index)"
                                    src="../static/index/dian.svg" alt="" />
                                <img class="dot" v-else @mouseleave="leavethis(index)" @click.stop="tocome(item.id)"
                                    src="../static/index/bluedian.svg" alt="" />
                            </div>
                            <div class="delo" v-else>
                                <el-tooltip class="item" effect="dark" content="削除" placement="bottom">
                                    <img class="deloimg" src="../static/index/delo.svg" @mouseover="hoverthis(index)"
                                        @mouseleave="leavethis(index)" alt="" />
                                </el-tooltip>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import {
        getMemberList
    } from "@/http/api.js"
    export default {
        data() {
            return {
                industry: "五十音順",
                SelectId: [], // 已选列表
                color: true,
                CheckList: [],
                ChooseList: [],
                currentPage: 1, //当前页码
                pageSize: 18, //每页显示条数
                list: [], //要显示的表格数据
                industryList: ["五十音順", "来店が新しい順", "来店が古い順"],
                keywords: "",
                total: 0
            };
        },
        created() {
            let that = this
            that.getMemberList()
        },
        methods: {
            hoverthis(e) {
                let that = this
                that.CheckList[e].hovers = true
            },
            leavethis(e) {
                let that = this
                that.CheckList[e].hovers = false
            },
            getMemberList() {
                let that = this
                let data = {}
                data["page"] = that.currentPage
                data["page_size"] = that.pageSize
                data["sort"] = that.industry == '五十音順' ? 1 : that.industry == '来店が新しい順' ? 2 : 3
                data["keywords"] = that.keywords
                getMemberList(data).then((res) => {
                    console.log(res)
                    if (res.code == 200) {
                        res.data.members.forEach((v, i) => {
                            v.hovers = false
                        })
                        that.CheckList = res.data.members
                        that.total = res.data.total
                    }
                })
            },
            searchMember() {
                this.getMemberList()
            },
            command(command) {
                this.title = command;
            },
            selectFun(id) {
                if (!this.SelectId.includes(id)) {
                    this.SelectId.push(id); // 判断已选列表中是否存在该id，不是则追加进去
                } else {
                    let index = this.SelectId.indexOf(id); // 求出当前id的所在位置
                    this.SelectId.splice(index, 1); // 否则则删除
                }

            },
            // 全选、反选
            allFun() {
                if (this.SelectId.length == this.CheckList.length) {
                    this.SelectId = []; // 判断是否已全部选中，是则清空已选列表
                } else {
                    this.CheckList.forEach((item) => {
                        if (!this.SelectId.includes(item.id)) {
                            this.SelectId.push(item.id); // 否则将未选中的全部加入已选列表中
                        }
                    });
                }
            },
            handleCurrentChange(val) {
                this.currentPage = val;
            },
            goDownload() {
                this.$router.push({
                    path: "/download",
                });
            },
            tocome(id) {
                this.$router.push({
                    path: "/cardInfo?id=" + id + "&type=kkslkldjd&show=1",
                });
            },
            goDetail(id) {
                console.log(id)
                this.$router.push({
                    path: "/editinfo?id=" + id,
                });
            },
            selectChanged(e) {
                console.log(e, "eeee");
                let that = this
                that.getMemberList()
            },
            change(val) {
                console.log(`每页 ${val} 条`);
            },
        },
    };
</script>


<style lang="scss" scoped>
    .ava{
        object-fit: cover;
        border-radius: 50%;
    }
    ::v-deep .el-pager li.active {
        color: #1d1d1f;
    }

    ::v-deep .el-icon-arrow-right:before {
        content: "";
    }

    ::v-deep .el-icon-arrow-left:before {
        content: "";
    }

    ::v-deep .el-pager li {
        font-size: 16px;
        color: #1d1d1f;
        font-weight: lighter;
    }

    ::v-deep .search .el-input__inner {
        background: transparent !important;
        outline: none !important;
        border: none !important;
        padding: 0 !important;
    }

    .el-select-dropdown__item.selected {
        font-size: 13px !important;
        font-weight: lighter !important;
    }

    ::v-deep .el-select-dropdown__item.hover,
    .el-select-dropdown__item:hover {
        background: none;
    }

    .deloimg {
        margin-right: 0 !important;
        display: block;
    }

    .deloword {
        display: block;
        margin-right: 0 !important;
    }

    .delo {
        box-sizing: border-box;
        padding-top: 10px;
        cursor: pointer;
    }

    .title {
        font-size: 21px;
        color: #1d1d1f;
        font-weight: bold;
    }

    .box {
        width: 820px;
        height: 863px;
        margin-top: 20px;
        background: #ffffff;
        box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);
        border: 1px solid #d2d2d7;

        .search {
            width: 600px;
            height: 40px;
            background: #eaeaea;
            border-radius: 20px;
            padding: 0 20px;
            border: 1px solid #eaeaea;

            .left {
                font-size: 16px;
                color: #1d1d1f;
                margin: 0 16px 0 7px;
            }

            input::placeholder {
                color: #aaa;
            }

            input {
                border: none;
                font-size: 16px;
                padding-top: 4px;
                background: #eaeaea;
                outline: none;
            }
        }

        .CSV {
            width: 112px;
            height: 40px;
            color: #fff;
            font-size: 14px;
            background: #0a4b6d;
            border-radius: 10px;
        }

        .left {
            font-size: 16px;
            color: #1d1d1f;
            font-weight: bold;
            margin-right: 20px;
        }

        .choice {
            margin-top: 30px;

            ::v-deep .el-input__inner {
                width: 245px;
                height: 40px;
                font-weight: bold;
                position: relative;
                padding-left: 20px;
                border-radius: 5px;
                background: rgba(0, 0, 0, 0.07);
                border: 1px solid #aaaaaa;
            }

            ::v-deep .el-icon-arrow-up:before {
                color: #1d1d1f;
                font-weight: bold;
            }
        }

        .table {
            margin-top: 40px;
        }
    }

    .el-dropdown-menu__item:not(.is-disabled):hover {
        background-color: transparent !important;
        color: #1a73e8 !important;
    }

    .content {
        width: 100%;

        // border: 1px solid pink;
        .selectAll {
            display: flex;
            padding: 10px 23px;
            border-bottom: 2px solid #d2d2d7;
        }

        .CheckBox {
            width: 100%;
        }

        .CheckItem {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 17px 22px;
            border-bottom: 2px solid #d2d2d7;

            .dot {
                width: 23px;
                height: 25px;
                border-radius: 0;
                margin-right: 0;
                cursor: pointer;
            }
        }

        .select {
            width: 18px;
            height: 18px;
            border-radius: 2px;
            border: 1px solid #aaa;
            display: flex;
            justify-content: center;
            cursor: pointer;
            margin-right: 23px;
            align-items: center;
        }

        .selectActv::before {
            content: "";
            // width: 5px;
            // height: 12px;
            // border-bottom: 2px solid #aaa;
            // border-right: 2px solid #aaa;
            // transform: rotate(45deg);
            // background: #1A73E8;
            width: 19px;
            height: 19px;
            background: url("@/static/fang-check.png") no-repeat;
            background-size: 100% 100%;
        }

        .selectCss {
            border: 1px solid #1a73e8;
            background: #1a73e8;
        }

        img {
            width: 30px;
            height: 30px;
            margin-right: 20px;
            border-radius: 50%;
        }

        .name {
            width: 100px;
            font-size: 12px;
            color: #1d1d1f;
            font-weight: bold;
            margin-right: 12px;
        }

        .name:hover {
            color: #1a73e8 !important;
            text-decoration: underline;
        }

        .grey {
            width: 100px;
            font-size: 12px;
            margin-right: 12px;
            color: #1d1d1f;
        }

        .sixty {
            width: 60px;
        }

        .hundred_thirty {
            width: 130px;
        }

        .big {
            font-size: 14px;
        }
    }

    .block {
        margin-top: 18px;
    }
</style>