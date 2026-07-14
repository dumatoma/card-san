<template>
    <div @click="wrapClick">
        <div class="title font_color">TOP > メンバー管理 > 顧客データダウンロード</div>
        <div class="wrap no-size">
            <div class="member u-flex u-row-between">
                <div style="font-weight: bold">顧客データダウンロード</div>
                <div class="right u-flex">
                    メンバー数
                    <div class="u-flex u-row-right num">
                        <div class="blue">{{radShow == true? totalMember : memberList.length}}</div>
                        /{{totalMember}}
                    </div>
                </div>
            </div>
            <div class="m-t-40">メンバーリストを選択してください</div>
            <div class="u-flex" style="margin-top: 32px">
                <div class="radius shou" :class="{ radiusCss: radShow }" @click.stop="radClick">
                    <div class="blue_dot" v-if="radShow"></div>
                </div>
                <div :style="radShow ? 'color:#1D1D1F;font-weight: bold;': 'color:#707070;'">全て</div>
            </div>
            <div class="u-flex" style="margin-top: 32px">
                <div class="radius shou" :class="{ radiusCss: !radShow }" @click.stop="radClick">
                    <div class="blue_dot" v-if="!radShow"></div>
                </div>
                <div :style="!radShow ? 'color:#1D1D1F;font-weight: bold;': 'color:#707070;'">詳細条件で絞り込む</div>
            </div>
            <div class="u-flex-col info" :style="!radShow?'visibility:visible':'visibility:hidden'">
                <div class="tit_info">誕生月</div>
                <div class="ip">
                    <input class="int shou" type="text" placeholder="誕生月を選択" readonly :value="months.join()"
                        @click.stop="shengChangeShow = true" />
                    <div class="data no-size" v-if="shengChangeShow">
                        <div class="tit no-size">
                            <div class="t-le">※複数選択可</div>
                            <div class="t-ri shou" @click.stop="monthChange">
                                <img src="../static/tclose.png" alt="" />
                            </div>
                        </div>
                        <div class="it no-size" v-for="(item, index) in options" :key="index"
                            :style="item.checked? 'color:#1d1d1f' : ''">
                            <span class="no-check shou" v-if="!item.checked" @click.stop="Check(index)"></span>
                            <span class="check shou" v-if="item.checked" @click.stop="quChecked(index)">
                                <span></span></span>
                            {{ item.label }}
                        </div>
                    </div>
                </div>
                <div class="tit_info">性別</div>
                <div class="ip">
                    <input class="int shou" type="text" placeholder="性別を選択" readonly :value="sexs.join()" @click.stop="sexChangeShow = true" />

                    <div class="data no-size" v-if="sexChangeShow">
                        <div class="tit no-size">
                            <div class="t-le">※複数選択可</div>
                            <div class="t-ri shou" @click.stop="sexChange">
                                <img src="../static/tclose.png" alt="" />
                            </div>
                        </div>
                        <div class="it no-size" v-for="(item, index) in sexOptions" :key="index"
                            :style="item.checked? 'color:#1d1d1f' : ''">
                            <span class="no-check shou" v-if="!item.checked" @click.stop="sexCheck(index)"></span>
                            <span class="check shou" v-if="item.checked" @click.stop="sexquChecked(index)">
                                <span></span></span>
                            {{ item.label }}
                        </div>
                    </div>
                </div>
                <div class="tit_info">前回来店日から</div>
                <div class="u-flex m-t-10">
                    <div class="le">
                        <div class="num_sel" v-if="limitShow">
                            <div class="num_item u-flex" @click="numberClick(index, item)" :key="index"
                                :class="[index == numIndex ? 'numCss' : '']" v-for="(item, index) in timeOptions">
                                {{ item }}
                            </div>
                        </div>
                        <el-select v-model="stime" @change="handleChange1" placeholder="日数を選択">
                            <el-option v-for="item in timeOptions" :key="item.value" :label="item.label"
                                :value="item.value">
                            </el-option>
                        </el-select>
                    </div>

                    <div class="radius shou" :class="{ radiusCss: oneShow == 1 }" @click.stop="oneClick(1)">
                        <div class="blue_dot" v-if="oneShow == 1"></div>
                    </div>
                    <div style="color: #707070; margin-right: 19px" :style="oneShow == 1?'color:#1d1d1f' : ''">以上経過</div>
                    <div class="radius shou" :class="{ radiusCss: oneShow == 2}" @click.stop="oneClick(2)">
                        <div class="blue_dot" v-if="oneShow == 2"></div>
                    </div>
                    <div style="color: #707070" :style="oneShow == 2?'color:#1d1d1f' : ''">以内</div>
                </div>

                <div class="tit_info">来店回数</div>
                <div class="u-flex m-t-10">
                    <div class="le">
                        <el-select v-model="cishu" @change="handleChange2" placeholder="回数を選択">
                            <el-option v-for="item in cishuOptions" :key="item.value" :label="item.label"
                                :value="item.value">
                            </el-option>
                        </el-select>
                    </div>
                    <div class="radius shou" :class="{ radiusCss: withShow == 2 }" @click.stop="withClick(2)">
                        <div class="blue_dot" v-if="withShow == 2"></div>
                    </div>
                    <div style="margin-right: 52px" :style="withShow == 2 ? 'color:#1D1D1F;': 'color:#707070;'">以上</div>
                    <div class="radius shou" :class="{ radiusCss: withShow == 1 }" @click.stop="withClick(1)">
                        <div class="blue_dot" v-if="withShow == 1"></div>
                    </div>
                    <div :style="withShow == 1 ? 'color:#1D1D1F;': 'color:#707070;'">以内</div>
                </div>
            </div>
            <div class="bottom shou u-flex u-row-center hv" @click="downloadCSV">CSVダウンロード</div>
        </div>
    </div>
</template>

<script>
    import ScrollbarContainer from "@/components/scrollContainer.vue";
    import {
        getMemberList,
        sceen,
        getConfig
    } from '@/http/api.js'
    export default {
        components: {
            ScrollbarContainer,
        },
        mounted() {
            // *注：demo更新需要更新容器
            // this.$nextTick(() => {
            //   this.$refs.scrollbarRef.updateScroll();
            // });
        },
        data() {
            return {
                months: [],
                sexs:[],
                radShow: true,
                withShow: 0,
                oneShow: 0,
                twoShow: false,
                shengChangeShow: false,
                sexChangeShow: false,
                reduceMoney: "",
                memberList: [],
                money: "",
                options: [{
                        checked: false,
                        label: "1月",
                        value: 1
                    },
                    {
                        checked: false,
                        label: "2月",
                        value: 2
                    },
                    {
                        checked: false,
                        label: "3月",
                        value: 3
                    },
                    {
                        checked: false,
                        label: "4月",
                        value: 4
                    },
                    {
                        checked: false,
                        label: "5月",
                        value: 5
                    },
                    {
                        checked: false,
                        label: "6月",
                        value: 6
                    },
                    {
                        checked: false,
                        label: "7月",
                        value: 7
                    },
                    {
                        checked: false,
                        label: "8月",
                        value: 8
                    },
                    {
                        checked: false,
                        label: "9月",
                        value: 9
                    },
                    {
                        checked: false,
                        label: "10月",
                        value: 10
                    },
                    {
                        checked: false,
                        label: "11月",
                        value: 11
                    },
                    {
                        checked: false,
                        label: "12月",
                        value: 12
                    },
                ],
                sexOptions: [{
                        checked: false,
                        label: "男性",
                        value: 1
                    },
                    {
                        checked: false,
                        label: "女性",
                        value: 2
                    },
                    {
                        checked: false,
                        label: "Xジェンダー",
                        value: 3
                    },
                ],
                stime: "",
                timeOptions: [{
                        value: "7",
                        label: "7日",
                    },
                    {
                        value: "14",
                        label: "14日",
                    },
                    {
                        value: "30",
                        label: "30日",
                    },
                    {
                        value: "60",
                        label: "60日",
                    },
                    {
                        value: "90",
                        label: "90日",
                    },
                    {
                        value: "180",
                        label: "180日",
                    },
                    {
                        value: "365",
                        label: "1年",
                    },
                    {
                        value: "730",
                        label: "2年",
                    },
                    {
                        value: "1095",
                        label: "3年",
                    },
                ],
                cishuOptions: [{
                        value: "1",
                        label: "1回",
                    },
                    {
                        value: "2",
                        label: "2回",
                    },
                    {
                        value: "3",
                        label: "3回",
                    },
                    {
                        value: "4",
                        label: "4回",
                    },
                    {
                        value: "5",
                        label: "5回",
                    },
                    {
                        value: "6",
                        label: "6回",
                    },
                    {
                        value: "7",
                        label: "7回",
                    },
                    {
                        value: "8",
                        label: "8回",
                    },
                    {
                        value: "9",
                        label: "9回",
                    },
                    {
                        value: "10",
                        label: "10回",
                    },
                ],
                cishu: "",
                number: "1",
                keepShow: false, //印章卡
                card: "chapter", //印章卡QR读取显示chapter ,点卡QR读取显示dot
                newShow: false,
                state: "change", //使用卡变更注意change,卡更新new
                dotShow: true,
                resumeShow: true, //来店履历
                cardShow: true, //卡片一览表
                cardIndex: 998534523453,
                limitShow: false,
                num: "日数を選択",
                numIndex: 0,
                storeNumShow: false,
                store_num: "回数を選択",
                storeIndex: 0,
                cardList: [],
                userInfo: {},
                defaultId: 9966996,
                query: {},
                comeList: [],
                total: 0,
                totalMember: 0,
                tableData: []
            };
        },
        created() {
            let that = this
            that.getList()
        },

        methods: {
            handleChange1() {
                this.sceen()
            },
            handleChange2() {
                this.sceen()
            },
            sexChange() {
                this.sexChangeShow = false
                this.sexs = []
                this.sceen()
            },
            monthChange() {
                this.shengChangeShow = false
                this.sceen()
            },
            downloadCSV() {
                let that = this
                that.getExportList()
            },
            formatCsv() {
                let that = this
                let t = that.memberList
                let s = []
                t.forEach((val, index) => {
                    let obj = {}
                    obj['ID'] = val.member_id
                    obj['顧客NO'] = val.member_no
                    obj['氏名'] = val.name
                    obj['カナ'] = val.pronunciation
                    obj['電話番号'] = val.phone
                    obj['郵便番号'] = val.zip_code
                    obj['住所'] = val.province + val.address1 + val.address2
                    obj['性別'] = val.sex == 1 ? '男性' : val.sex == 2 ? '女性' : 'ジェンダー'
                    obj['誕生月'] = val.birthday_month
                    obj['来店回数'] = val.shop_count
                    obj['来店期間'] = val.last_shop_time < 0 ? '' : val.last_shop_time + "日前"
                    s.push(obj)
                })
                that.tableData = s
            },
            getExportList() {

                const _self = this
                let jsonData = {
                    trade: {
                        tHeader: ["ID", '顧客NO', '氏名', 'カナ', '電話番号', '郵便番号', '住所', '性別', '誕生月', '来店回数',
                        '来店期間'], //导出对应的列头名称
                        filterVal: ["ID", '顧客NO', '氏名', 'カナ', '電話番号', '郵便番号', '住所', '性別', '誕生月', '来店回数',
                        '来店期間'], //导出对应的 tableData的列
                        list: _self.tableData
                    }
                }
                _self.exportPathMethod(jsonData) // 调用exportPathMethod对数据进行处理导出
                // _self.exportShow=false
            },
            exportPathMethod(data) {
                /*
                 *注：csv文件：","逗号换列，\n换行，\t防止excel将长数字变科学计算法等样式
                 */
                //要导出的json数据
                let mainLists = data.trade //主表
                let _self = this
                //## 数据处理
                //一级表
                let mainTitle = mainLists.tHeader; //一级标题
                let mainTitleForKey = mainLists.filterVal; //一级过滤
                let mainList = mainLists.list; //一级数据
                let mainStr = [];
                mainStr.push(mainTitle.join("\t,") + "\n"); //标题添加上换列转成字符串并存进数组
                for (let i = 0; i < mainList.length; i++) {
                    let temp = [];
                    for (let j = 0; j < mainTitleForKey.length; j++) {
                        temp.push(mainList[i][mainTitleForKey[j]]); //根据过滤器拿出对应的值
                    }
                    mainStr.push(temp.join("\t,") + "\n"); //取出来的值加上逗号换列转字符串存数组
                }
                // console.log(JSON.stringify(mainStr.join("")));//打印文本

                //两个表数组转成字符串合并
                let merged = mainStr.join("")
                //console.log(JSON.stringify(merged));//打印结果

                //## 导出操作
                // encodeURIComponent解决中文乱码
                const uri = 'data:text/csv;charset=utf-8,\ufeff' + encodeURIComponent(merged)
                // 通过创建a标签实现

                getConfig().then((result) => {
                    console.log("config", result)
                    if (result.code == 200) {
                        let datestr = ''
                        let da = new Date(result.data.config.timestamp * 1000)
                        let da1 = da.getFullYear()
                        let da2 = (da.getMonth() + 1) >= 10 ? (da.getMonth() + 1) : '0' + (da.getMonth() + 1)
                        let da3 = da.getDate() >= 10 ? da.getDate() : '0' + da.getDate()
                        datestr = 'cardsan' + da1 + da2 + da3
                        console.log('datestr', datestr)
                        let link = document.createElement('a')
                        link.href = uri
                        // 对下载的文件命名
                        link.download = datestr + `.csv`
                        document.body.appendChild(link)
                        link.click()
                    }
                })


            },
            radClick() {
                this.radShow = !this.radShow;
                if (this.radShow == false) {
                    this.sceen()
                } else {
                    this.getList()
                }
            },
            sceen() {
                let that = this
                let month = []
                let sex = []
                that.months = []
                that.options.forEach((val, index) => {
                    if (val.checked == true) {
                        month.push(val.value)
                        that.months.push(val.value+"月")
                    }
                })

                that.sexOptions.forEach((val, index) => {
                    if (val.checked == true) {
                        sex.push(val.value)
                        let sexname = val.value == 1?'男性':val.value == 2?'女性':'Xジェンダー'
                        that.sexs.push(sexname)
                    }
                })
                let data = {}
                data['birthday_month'] = month.toString()
                data['sex'] = sex.toString()
                data['shop_time'] = that.stime * 86400
                data['shop_time_desc'] = that.oneShow
                data['shop_count'] = that.cishu
                data['shop_count_desc'] = that.withShow
                if (that.radShow == false) {
                    that.showLoading()
                    sceen(data).then((res) => {
                        that.closeLoading()
                        if (res.code == 200) {
                            that.memberList = res.data.members
                        } else {
                            that.$message.error(res.message)
                        }
                        that.formatCsv()
                    })
                } else {

                }
            },
            getList() {
                let that = this
                let data = {}
                data['page'] = 1
                data['page_size'] = 300
                data['keywords'] = ""
                data['sort'] = 0
                getMemberList(data).then((res) => {
                    if (res.code == 200) {
                        console.log('members', res)
                        that.totalMember = res.data.total
                        that.memberList = res.data.members
                        that.formatCsv()
                    } else {
                        that.$message.error(res.message)
                    }
                })
            },
            showLoading() {
                let that = this
                that.Loading = that.$loading({
                    spinner: "", //自定义加载图标类名
                    text: "読み込み中…", //显示在加载图标下方的加载文案
                    background: "rgba(0, 0, 0, 0.6)", //遮罩背景色
                })
            },
            closeLoading() {
                let that = this
                that.Loading.close()
            },
            withClick(e) {
                let that = this
                if(that.withShow == e){
                    that.withShow = 0
                }else{
                    that.withShow = e
                }
                this.sceen()
            },
            oneClick(e) {
                if(this.oneShow == e){
                    this.oneShow = 0
                }else{
                    this.oneShow = e
                }
                this.sceen()
            },
            twoClick() {
                this.twoShow = !this.twoShow;
            },
            Check(i) {
                this.options[i].checked = true;
                // this.change(this.options, 1);
            },
            quChecked(i) {
                this.options[i].checked = false;
                // this.change(this.options, 1);
            },
            sexCheck(i) {
                this.sexOptions[i].checked = true;
                // this.change(this.sexOptions, 2);
            },
            sexquChecked(i) {
                this.sexOptions[i].checked = false;
                // this.change(this.sexOptions, 2);
            },
            timestampToTime(timestamp) {
                var date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
                var Y = date.getFullYear() + '/';
                var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '/';
                var D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
                return Y + M + D;
            },

            backGo() {
                history.go(-1)
            },

            dotClick() {
                this.dotShow = !this.dotShow;
            },
            numberClick(index, item) {
                this.num = item;
                this.numIndex = index;
                this.limitShow = false;
                this.sceen()
            },
            wrapClick() {
                this.limitShow = false;
                this.storeNumShow = false;
            },
            storeNumClick(index, item) {
                this.store_num = item;
                this.storeIndex = index;
                this.storeNumShow = false;
            },
        },
    };
</script>

<style lang="scss" scoped>
    .font_color {
        font-size: 16px;
        color: #1d1d1f;
    }

    .hv:hover {
        background: rgba(29, 29, 31, 0.2) !important;
    }

    .title {
        font-weight: bold;
    }

    .radius {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin: 0 14px;
        background: #fff;
        border: 1px solid #707070;

        .blue_dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin: 2px auto;
            background: #1a73e8;
        }
    }

    .radiusCss {
        border: 1px solid #1a73e8;
    }

    .wrap {
        width: 820px;
        margin-top: 25px;
        height: 844px;
        // padding-bottom: 40px;
        background: #fff;
        padding: 0 40px;
        box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);
        border: 1px solid #d2d2d7;

        .member {
            width: 100%;
            font-size: 21px;
            padding: 40px 0 10px 0;
            color: rgba(29, 29, 31, 0.9);
            border-bottom: 1px solid#D2D2D7;

            .right {
                color: #1d1d1f;
            }

            .num {
                width: 123px;
                margin-left: 10px;

                .blue {
                    color: #1a73e8;
                }
            }
        }

        .info {
            padding-left: 46px;
            padding: 0 0 40px 46px;
            border-bottom: 1px solid #d2d2d7;

            .tit_info {
                font-size: 16px;
                color: #1d1d1f;
                margin-top: 30px;
            }

            .day {
                width: 200px;
                height: 40px;
                background: #f5f5f7;
                opacity: 1;
                border-radius: 10px;
                border: 1px solid #aaaaaa;
            }

            .fre_select {
                width: 200px;
                height: 40px;
                margin-top: 10px;
                background: rgba(249, 249, 249, 0.94);
                border-radius: 10px;
                position: relative;
                padding-left: 10px;
                font-size: 16px;
                cursor: pointer;
                border: 1px solid #aaa;

                img {
                    top: 14px;
                    right: 10px;
                    position: absolute;
                }

                .num_sel {
                    position: absolute;
                    top: 40px;
                    left: 0;
                    width: 100px;
                    // height: 370px;
                    cursor: pointer;
                    background: #fff;
                    box-shadow: 0px 5px 10px 1px rgba(0, 0, 0, 0.25);
                    border-radius: 4px;
                    z-index: 9;
                    border: 1px solid #eaeaea;

                    .num_item {
                        height: 40px;
                        padding-left: 12px;
                    }

                    .numCss {
                        color: #1a73e8;
                    }
                }
            }

            .ip {
                margin-top: 5px;
                position: relative;
            }

            .int {
                width: 327px;
                height: 40px;
                font-size: 16px;
                border-radius: 10px;
                padding-left: 16px;
                outline: 0;
                background: rgba(29, 29, 31, 0.05);
                border: 1px solid #aaa;

                input {
                    border: none;
                    background: #f5f5f7;
                    outline: 0;
                    font-size: 16px;
                    width: 90%;
                }
            }

            .data {
                position: absolute;
                top: 50px;
                left: 0;
                width: 343px;
                // height: 540px;
                z-index: 9;
                background-color: #fff;
                box-shadow: 0px 5px 10px 1px rgba(0, 0, 0, 0.25);
                border-radius: 4px 4px 4px 4px;

                .tit {
                    padding: 0 10px 0 21px;
                    background: rgba(0, 0, 0, 0.07);
                    display: flex;
                    height: 40px;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 10px;

                    .t-le {
                        font-size: 14px;
                        color: #1d1d1f;
                    }

                    .t-ri {
                        img {
                            width: 24px;
                            height: 24px;
                        }
                    }
                }

                .it {
                    padding: 0 10px 0 21px;
                    height: 40px;
                    color: #707070;
                    font-size: 14px;
                    display: flex;
                    align-items: center;

                    .no-check {
                        display: inline-block;
                        width: 16px;
                        height: 16px;
                        background: #ffffff;
                        opacity: 1;
                        border-radius: 50%;
                        border: 1px solid #707070;
                        margin-right: 14px;
                    }

                    .check {
                        display: inline-block;
                        width: 16px;
                        height: 16px;
                        background: #ffffff;
                        opacity: 1;
                        border-radius: 50%;
                        border: 1px solid #1a73e8;
                        margin-right: 14px;

                        span {
                            display: inline-block;
                            width: 10px;
                            height: 10px;
                            border-radius: 50%;
                            background: #1a73e8;
                            margin: 3px;
                        }
                    }
                }
            }

            .small {
                width: 180px;
                padding: 0 10px;
            }

            .down {
                width: 12px;
                height: 12px;
            }
        }

        .bottom {
            width: 239px;
            height: 48px;
            margin: 30px auto;
            font-size: 20px;
            color: #707070;
            background: rgba(29, 29, 31, 0.1);
            border-radius: 10px 10px 10px 10px;
            opacity: 1;
            border: 1px solid #707070;
        }
    }

    ::v-deep .le {
        margin-right: 49px;
        margin: 0 30px 0 0;

        .el-input__inner {
            width: 200px !important;
            height: 40px;
            background: rgba(29, 29, 31, 0.05);
            opacity: 1;
            border-radius: 10px;
            border: 1px solid #aaaaaa !important;
        }

        .el-input__inner::placeholder {
            color: #aaa !important;
        }
    }

    .fixed {
        position: fixed;
        top: 0%;
        left: 0%;
        width: 100vw;
        height: 100vh;
        background: rgba(29, 29, 31, 0.2);

        .read_display {
            width: 820px;
            height: 602px;
            position: fixed;
            top: 50%;
            left: 50%;
            border-radius: 5px;
            transform: translate(-50%, -50%);
            background: #fff;
            box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
        }

        .header {
            width: 100%;
            height: 40px;
            background: linear-gradient(180deg, #edeced 0%, #d2d1d2 100%);
            border-radius: 5px 4px 0px 0px;
            padding-left: 10px;

            .error {
                width: 30px;
                height: 30px;
                font-size: 30px;
                color: #1d1d1f;
                border-radius: 50%;
                background: rgba(29, 29, 31, 0.1);
            }
        }

        .left {
            width: 410px;
            height: 560px;
            background: #ffffff;
            padding: 0 46px 0 30px;
            box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);

            .name {
                margin: 30px 0;
                font-size: 16px;
                font-weight: bold;
                color: #007aff;
            }

            .one_bottom {
                margin: 14px 0 20px 0;
                padding-bottom: 14px;
                border-bottom: 1px solid #d2d2d7;
            }

            .two_bottom {
                margin: 14px 0 20px 0;
                padding-bottom: 20px;
                border-bottom: 1px solid #d2d2d7;
            }

            .three_bottom {
                margin: 12px 0 20px 0;
                font-size: 14px;

                &:last-child {
                    padding-bottom: 20px;
                    border-bottom: 1px solid #d2d2d7;
                }
            }

            .btn {
                width: 60px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 10px;
                font-size: 12px;
                color: #ffffff;
            }

            .blue_back {
                background: #1a73e8;
            }

            .red_back {
                background: #d93025;
            }

            .grey_left {
                width: 64px;
                font-size: 14px;
                color: #707070;
                margin-right: 3px;
            }

            .black_right {
                width: 230px;
                font-size: 14px;
                color: #1d1d1f;
            }

            .grey_back {
                width: 60px;
                height: 24px;
                font-size: 12px;
                border-radius: 3px;
                color: #707070;
                margin-right: 11px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f5f5f7;
            }

            .red_text {
                width: 334px;
                height: 38px;
                line-height: 20px;
                font-size: 14px;
                margin-top: 18px;
                color: #1d1d1f;
                padding-left: 8px;
            }
        }

        .scroll-container {
            border: 1px solid #e4e7ed;
            border-radius: 4px;
        }

        .right {
            width: 100%;
            height: 560px;
            overflow: scroll;
            overflow-x: hidden;

            .right_bottom {
                width: 410px;
                height: 149px;
                text-align: center;
                background: #f5f5f7;
                box-shadow: 0px 0px 6px 1px rgba(0, 0, 0, 0.16);
                opacity: 0.95;

                .num {
                    font-size: 14px;
                    color: #707070;
                    padding-top: 10px;
                }

                .center {
                    width: 56%;
                    margin: 0 auto;
                    margin-top: 24px;

                    img {
                        width: 30px;
                        height: 30px;
                    }

                    .number {
                        font-size: 30px;
                        color: rgba(29, 29, 31, 0.9);
                    }
                }

                .blue_btn {
                    width: 120px;
                    height: 36px;
                    font-size: 16px;
                    color: #ffffff;
                    margin: 0 auto;
                    margin-top: 17px;
                    background: #1a73e8;
                    border-radius: 18px 18px 18px 18px;
                }
            }

            &::-webkit-scrollbar {
                width: 0px;
                height: 0px;
                background-color: rgba(240, 240, 240, 1);
            }

            .scroll {
                border-right: 1px solid #e8e8e8;
                width: 96.5%;
                height: 100%;
                padding: 0 24px 0 23px;
            }

            .tit_right {
                font-size: 16px;
                color: #1d1d1f;
                font-weight: bold;
                padding: 30px 0 16px 0;
            }

            .cardY {
                width: 357px;
                height: 199px;
                background: #53b1af;
                box-shadow: 0px 0px 5px 1px rgba(0, 0, 0, 0.16);
                border-radius: 12px;
                box-sizing: border-box;
                padding: 18px;

                .cardInner {
                    width: 100%;
                    height: 100%;
                    background-color: #fff;
                    display: flex;
                    flex-wrap: wrap;

                    .seal {
                        width: 19.68%;
                        height: 49.4%;
                        border-right: 1px solid #eaeaea;
                        border-bottom: 1px solid #eaeaea;
                        position: relative;

                        .day {
                            font-size: 24px;
                            color: #eaeaea;
                        }

                        .sealed {
                            position: absolute;
                            width: 100%;
                            height: 100%;
                            left: 0;
                            text-align: center;

                            img {
                                width: 40px;
                                height: 40px;
                            }

                            .sealTime {
                                font-size: 10px;
                                color: #707070;
                                margin-top: 3px;
                                font-weight: lighter;
                            }
                        }
                    }
                }
            }

            // 点卡QR读取显示
            .dot_top {
                text-align: center;
                padding: 30px 23px 0 23px;

                .dot_tit {
                    width: 100%;
                    font-size: 16px;
                    font-weight: bold;
                    color: #1d1d1f;
                    text-align: left;
                    padding-bottom: 16px;
                }

                img {
                    margin: 0 auto;
                    margin-top: 20px;
                }

                .code_num {
                    margin-top: 10px;
                }

                .pt {
                    padding: 25px 0 27px 0;
                    border-bottom: 1px solid #e6e6e9;

                    .pink {
                        font-size: 24px;
                        font-weight: bold;
                        margin-left: 33px;
                        text-decoration: underline;
                        color: rgba(217, 48, 37, 0.65);
                    }
                }
            }

            .dot_bottom {
                padding: 20px 20px 0 20px;

                .text_pink {
                    color: #f2b8b5;
                }

                .text_black {
                    color: #1d1d1f;
                }

                .text_grey {
                    color: #aaaaaa;
                }

                .money {
                    width: 100%;
                    padding: 12px 0 24px 0;

                    input::-webkit-outer-spin-button,
                    input::-webkit-inner-spin-button {
                        -webkit-appearance: none;
                    }

                    .int {
                        width: 118px;
                        height: 32px;
                        outline: 0;
                        margin: 0 10px;
                        background: #fff;
                        border-radius: 10px;
                        text-align: center;
                        padding: 0 10px;
                        border: 1px solid #aaaaaa;
                    }
                }
            }

            .blue_btn {
                width: 120px;
                height: 36px;
                font-size: 16px;
                color: #ffffff;
                background: #1a73e8;
                border-radius: 18px 18px 18px 18px;
            }
        }

        // 更新小弹窗
        .new {
            width: 270px;
            // height: 198px;
            position: fixed;
            top: 50%;
            left: 50%;
            border-radius: 5px;
            transform: translate(-50%, -50%);
            background: #f4f4f4;
            box-shadow: 0px 3px 6px 1px rgba(0, 0, 0, 0.16);
            border-radius: 14px;
            text-align: center;

            .new_one {
                font-size: 16px;
                margin-top: 20px;
                font-weight: bold;
                color: rgba(29, 29, 31, 0.9);
            }

            .new_two {
                font-size: 14px;
                color: #1d1d1f;
                padding: 14px 0 22px 0;
            }

            .new_three {
                font-size: 16px;
                color: #007aff;
                padding: 22px 0 18px 0;
                border-top: 1px solid #a5a5a8;
                border-bottom: 1px solid #a5a5a8;
            }

            .new_four {
                font-size: 16px;
                color: #d93025;
                padding: 22px 0 20px 0;
            }
        }

        // 来店履历
        .resume {
            .error {
                width: 20px;
                height: 20px;
                font-size: 18px;
                color: #fff;
                border-radius: 50%;
                background: rgba(29, 29, 31, 0.6);
            }

            .resume_wrap {
                width: 98.4%;
                min-height: 100%;
                border-right: 1px solid #eaeaea;

                .resume_top {
                    font-size: 20px;
                    font-weight: bold;
                    color: rgba(29, 29, 31, 0.9);
                    padding: 40px 0 0 20px;

                    .resume_blue {
                        color: #2699fb;
                        margin-left: 10px;
                    }
                }

                .form {
                    width: 100%;
                    padding: 40px 0 0 20px;

                    .form_tit {
                        width: 99.5%;
                        font-size: 14px;
                        color: #1d1d1f;
                        font-weight: bold;
                        padding: 12px 0;
                        border-bottom: 1px solid #d2d2d7;
                    }

                    .form_cont {
                        width: 99.5%;

                        font-size: 14px;
                        color: #1d1d1f;
                        padding: 20px 0;
                        border-bottom: 1px solid #d2d2d7;

                        &:last-child {
                            border: none;
                        }
                    }

                    .form_one {
                        width: 186px;
                    }

                    .form_two {
                        width: 50px;
                    }

                    .form_three {
                        width: 160px;
                    }

                    .form_four {
                        width: 60px;
                    }

                    .form_five {
                        width: 61px;

                        .form_red {
                            color: #d93025;
                            margin-left: 10px;
                        }
                    }

                    .form_six {
                        width: 160px;
                    }
                }
            }
        }

        // 卡片一览表 和 来店履历 共用
        .header {
            position: relative;
        }

        .resume_tit {
            font-size: 14px;
            font-weight: bold;
            color: #1d1d1f;
            position: absolute;
            top: 50%;
            left: 50%;
            border-radius: 5px;
            transform: translate(-50%, -50%);
        }

        // 卡片一览表
        .card {
            width: 746px;
            padding-bottom: 30px;
            background: #fff;
            position: absolute;
            top: 50%;
            left: 50%;
            border-radius: 5px;
            box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
            transform: translate(-50%, -50%);

            .error {
                width: 30px;
                height: 30px;
                font-size: 34px;
                color: rgba(29, 29, 31, 0.6);
                border-radius: 50%;
                background: rgba(29, 29, 31, 0.1);
            }

            .itemCss {
                box-shadow: 0px 0px 6px 1px rgba(26, 115, 232, 0.5);
                border: 1px solid #2699fb;
            }

            .itemGrey {
                border: 1px solid transparent;
                box-shadow: 0px 0px 6px 1px rgba(29, 29, 31, 0.2);
            }

            .card_item {
                width: 100%;
                height: 120px;
                background: #fff;
                margin-top: 20px;
                border-radius: 10px;
                padding: 10px 20px 10px 15px;

                .box {
                    width: 18px;
                    height: 18px;
                    border-radius: 5px;
                    background: #ffffff;
                    border: 1px solid #aaa;
                }

                .boxCss {
                    background: #1a73e8;
                    border: 1px solid #1a73e8;
                }

                .grey_bor {
                    width: 168px;
                    height: 100px;
                    border-radius: 3px;
                    margin-left: 15px;
                    background: #ffffff;
                    font-size: 12px;
                    margin-right: 16px;
                    color: #7f00a5;
                    box-shadow: 0px 0px 6px 0px rgba(0, 0, 0, 0.1);
                }

                .card_text {
                    font-size: 14px;
                    color: #1d1d1f;

                    .card_bg {
                        width: 100px;
                        height: 20px;
                        margin-left: 22px;
                        box-shadow: 0px 0px 3px 1px rgba(0, 0, 0, 0.09);
                        border-radius: 4px 4px 4px 4px;
                    }

                    .green {
                        background: #53b1af;
                    }

                    .yellow {
                        background: #d9b625;
                    }

                    .grey {
                        background: #f5f5f7;
                    }
                }

                .utilize {
                    width: 64px;
                    height: 32px;
                    font-size: 12px;
                    color: #2699fb;
                    background: #fff;
                    border-radius: 10px;
                    border: 1px solid #2699fb;
                }
            }
        }
    }

    .admin {
        font-size: 10px;
    }
</style>