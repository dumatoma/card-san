<template>
    <div>
        <div class="tit u-flex u-row-between">
            <div>スタッフ一覧</div>
            <div class="append no-size u-flex-center" @click="personnelClick">
                <i class="el-icon-circle-plus" style="color: #1a73e8; margin-right: 5px"></i>
                スタッフを登録
            </div>
        </div>
        <div class="cont no-size">
            <div class="cont_item no-size u-flex u-row-between" v-for="(item, index) in list" :key="index">
                <div class="u-flex">
                    <div class="img u-flex-col u-row-center u-col-center" v-if="item.image.length > 0">
                        <img :src="item.image[0]" alt="" style="width: 100%; height: 100%;object-fit: cover;" />
                    </div>
                    <div class="cont_right">
                        <div class="cont_one" v-text="item.name" @click="toDetail(item.id)"></div>
                        <div class="cont_two">{{item.post}}</div>
                        <div class="cont_two">
                            {{item.self_introduction}}
                        </div>
                    </div>
                </div>
                <div style="display: flex;align-items: center;">
                    <img src="@/static/svg/upup.svg" style="margin-right: 20px;width: 26px;height: 26px;" class="shou"
                        @click.stop="sort(item,index)" v-if="index != 0" />
                    <el-tooltip class="item" effect="dark" content="削除" placement="bottom">
                        <img src="@/static/index/delete.png" class="delete" @click.stop="deleteItem(item.id)" />
                    </el-tooltip>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        getStaffList,
        deleteStaff,
        staffSort
    } from "@/http/api.js"
    export default {
        data() {
            return {
                // appendShow: true,
                edit: false,
                list: []
            };
        },
        created() {
            let that = this
            that.getList()
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
                staffSort(data).then((res) => {
                    if (res.code == 200) {
                        that.getList()
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
            getList() {
                let that = this
                let data = {}
                data["startTime"] = ""
                data["endTIme"] = ""
                data["id"] = ""
                getStaffList(data).then((res) => {
                    console.log(res)
                    if (res.code == 200) {
                        that.list = res.data.admins
                    }
                })
            },

            toDetail(id) {
                let that = this
                this.$router.push({
                    path: "/personnel?type=edit&id=" + id,
                });
            },

            personnelClick() {
                this.$router.push({
                    path: "/personnel?type=add",
                });
            },

            deleteItem(id) {
                let that = this
                deleteStaff(id).then((res) => {
                    if (res.code == 200) {
                        that.$message({
                            message: res.message,
                            type: "success",
                            offset: 400
                        })
                        that.getList()
                    } else {

                    }
                })
            }
        },
    };
</script>

<style lang="scss" scoped>
    .append:hover {
        background: rgba(26, 115, 232, 0.1) !important;
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
            background: #fff;
            font-size: 16px;
            color: #1a73e8;
            border-radius: 20px;
            border: 1px solid #1a73e8;
        }
    }

    .cont_one:hover {
        color: #1a73e8 !important;
        cursor: pointer;
        text-decoration: underline;
    }

    .cont {
        width: 820px;
        background: #fff;
        margin-top: 20px;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);

        .cont_item {
            width: 100%;
            font-size: 16px;
            color: #1d1d1f;
            padding: 9px 10px;
            border-bottom: 1px solid #d2d2d7;

            .img {
                width: 75px;
                height: 100px;
                background: rgba(29, 29, 31, 0.1);
                border-radius: 6px;
                border: 1px solid #eaeaea;
                font-size: 9px;
                margin-left: 10px;
                color: #aaaaaa;

                img {
                    width: 22px;
                    margin-bottom: 5px;
                }
            }

            .cont_right {
                padding: 20px;

                .cont_one {
                    font-size: 16px;
                    color: #1d1d1f;
                    font-weight: bold;
                }

                .cont_two {
                    font-size: 14px;
                    color: #707070;
                    margin-top: 14px;
                }
            }

            .delete {
                width: 38px;
                margin-right: 10px;
                cursor: pointer
            }
        }
    }
</style>