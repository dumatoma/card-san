<template>
    <view>
        <u-navbar title="顧客データダウンロード" :placeholder="true" :border="true" :safeAreaInsetTop="true" :autoBack="true"></u-navbar>
        <view class="container">
            <view class="memCount">
                <view class="mem1">
                    メンバー数
                </view>
                <view class="mem2">
                    <text>{{sceenMember1}}</text>/{{totalMember}}
                </view>
            </view>
            <view class="title">
                メンバーリストを選択してください
            </view>
            <view class="chooseItem">
                <view class="item">
                    <view class="itemLeft">
                        <view class="unselect" @click="changeTab(0)" v-if="current == 1"></view>
                        <view class="selected" v-if="current == 0">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view class="itemRight" :style="current == 0? 'color:#1D1D1F;' : 'color: #AAAAAA' ">
                        すべて
                    </view>
                </view>
                <view class="item">
                    <view class="itemLeft">
                        <view class="unselect" @click="changeTab(1)" v-if="current == 0"></view>
                        <view class="selected" v-if="current == 1">
                            <view class="fill"></view>
                        </view>
                    </view>
                    <view class="itemRight" :style="current == 1? 'color:#1D1D1F;' : 'color: #AAAAAA' ">
                        絞り込み
                    </view>
                </view>
            </view>
            <view class="content" v-if="current == 1">
                <view class="contentItem" v-for="(item,index) in tabs" @click="toChoose(index)">
                    <view class="tabLeft">
                        <view class="tabTop" v-text="item.name"></view>
                        <view class="tabBot" v-text="item.title" :style="item.title== '誕生月を設定'?'' : item.title== '性別を設定'?'' : item.title== '来店期間を設定'? '' : item.title == '来店回数を設定'? '': 'color:#1d1d1f !important'"></view>
                    </view>
                    <view class="tabRight">
                        <image src="../../../static/icons/right.png" mode=""></image>
                    </view>
                </view>
            </view>
            <u-button class="btn" @click="dc">Excelダウンロード</u-button>
            <view class="blank"></view>
        </view>
    </view>
</template>

<script>
    import {sceneMember,exportData} from "@/api/index.js"
    export default {
        data() {
            return {
                current: 0,
                sceenMember1:0,
                searchArr:[],
                totalMember:0,
                tabs: [{
                        name: "誕生月",
                        title: "誕生月を設定"
                    },
                    {
                        name: "性別",
                        title: "性別を設定"
                    },
                    {
                        name: "来店期間",
                        title: "来店期間を設定"
                    },
                    {
                        name: "来店回数",
                        title: "来店回数を設定"
                    }
                ]
            }
        },
        onLoad() {
            uni.removeStorageSync("csvData")
            let data = {}
            data['birthday_month'] = ''
            data['sex'] = ''
            data['shop_time'] = ""
            data['shop_time_desc'] = 3
            data['shop_count'] = ""
            data['shop_count_desc'] = 3
            uni.setStorageSync("csvData",data)
        },
        onShow() {
          let that = this 
          let csvData = uni.getStorageSync("csvData")
          that.getAllmember()
          that.sceenMember()
          // that.getAllmember()
          // if(csvData){
          //     that.sceenMember()
          // }else{
          //      that.getAllmember()
          // }
          if(csvData.birthday_month){
              that.tabs[0].title = csvData.birthday_month1
          }else{
              that.tabs[0].title = '誕生月を設定'
          }
          
          if(csvData.sex){
              that.tabs[1].title = csvData.sex1
          }else{
              that.tabs[1].title = '性別を設定'
          }
          
          if(csvData.shop_time && csvData.shop_time_desc != 3){
              that.tabs[2].title = '前回来店から'+ csvData.shop_time / 86400+'日' + (csvData.shop_time_desc == 2?'以上経過':'以内')
          }else{
              that.tabs[2].title = '来店期間を設定'
          }
          
          if(csvData.shop_count && csvData.shop_count_desc != 3){
               that.tabs[3].title = csvData.shop_count + '回' + (csvData.shop_count_desc == 2?'以上':'以下')
          }else{
              that.tabs[3].title = '来店回数を設定'
          }
          
        },
        methods: {
            changeTab(e) {
                this.current = e
            },
            sceenMember(){
                let that = this
                let csvData = uni.getStorageSync("csvData")
                let data = {}
                if(csvData){
                    data['birthday_month'] = csvData.birthday_month || ''
                    data['sex'] = csvData.sex || ''
                    data['shop_time'] = csvData.shop_time_desc == 3? '' : csvData.shop_time
                    data['shop_time_desc'] = csvData.shop_time_desc == 3? '' : csvData.shop_time_desc
                    data['shop_count'] = csvData.shop_count_desc == 3? '': csvData.shop_count
                    data['shop_count_desc'] = csvData.shop_count_desc == 3? '': csvData.shop_count_desc
                }else{
                    data['birthday_month'] = ""
                    data['sex'] = ""
                    data['shop_time'] = ""
                    data['shop_time_desc'] = ""
                    data['shop_count'] = ""
                    data['shop_count_desc'] = ""
                }
               
                uni.showLoading({
                    title:"読み込み中"
                })
                sceneMember(data).then((res) => {
                    uni.hideLoading()
                    if(res.code == 200){
                        that.sceenMember1 = res.data.total
                    }
                })
            },
            getAllmember(){
                let that =this
                let data = {}
                data['birthday_month'] = ''
                data['sex'] = ''
                data['shop_time'] = ''
                data['shop_time_desc'] = ''
                data['shop_count'] = ''
                data['shop_count_desc'] = ''
                uni.showLoading({
                    title:"読み込み中"
                })
                sceneMember(data).then((res) => {
                    uni.hideLoading()
                    if(res.code == 200){
                        that.totalMember = res.data.total
                    }
                })
            },
            dc() {
                let that = this
                let csvData = uni.getStorageSync("csvData")
                let data = {}
                data['birthday_month'] = csvData.birthday_month || ''
                data['sex'] = csvData.sex || ''
                data['shop_time'] = csvData.shop_time_desc == 3? '' : csvData.shop_time
                data['shop_time_desc'] = csvData.shop_time_desc == 3? '' : csvData.shop_time_desc
                data['shop_count'] = csvData.shop_count_desc == 3? '': csvData.shop_count
                data['shop_count_desc'] = csvData.shop_count_desc == 3? '': csvData.shop_count_desc
                exportData(data).then((rest) => {
                    if(rest.code == 200){
                        let res = []
                        let t = rest.data.members
                        t.forEach((val,index) => {
                            let obj = {}
                            obj['ID'] = val.member_id
                            obj['顧客NO'] = val.member_no
                            obj['氏名'] = val.name
                            obj['カナ'] = val.pronunciation
                            obj['電話番号'] = val.phone
                            obj['郵便番号'] = val.zip_code
                            obj['住所'] = val.province + val.address1 + val.address2
                            obj['性別'] = val.sex == 1?'男性' : val.sex== 2? '女性' : 'ジェンダー'
                            obj['誕生月'] = val.birthday_month
                            obj['来店回数'] = val.shop_count
                            obj['来店期間'] = val.last_shop_time < 0 ? '' : val.last_shop_time + "日前"
                            res.push(obj)
                        })
                        
                        let str = res.map(m => {
                        
                            let str2 = Object.values(m).map(mp => {
                                return `<td>${mp}</td>`
                            }).join('')
                            return `<tr>${str2}</tr>`
                        }).join('')
                        let str1 = Object.keys(res[0]).map(mp => {
                            return `<td>${mp}</td>`
                        }).join('')
                        str = `<tr>${str1}</tr>${str}`
                        let template = `<html xmlns:o="urn:schemas-microsoft-com:office:office"
                             xmlns:x="urn:schemas-microsoft-com:office:excel" 
                             xmlns="http://www.w3.org/TR/REC-html40">
                             <head><!--[if gte mso 9]><xml encoding="UTF-8"><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
                             <x:Name>sheet1</x:Name>
                             <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>
                             </x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->
                             </head><body><table>${str}</table></body></html>`;
                        
                        
                        plus.io.requestFileSystem(plus.io.PRIVATE_DOC, function(fs) {
                            fs.root.getFile(
                                `customer.xls`, {
                                    create: true,
                                    exclusive: false
                                },
                                fileEntry => {
                                    fileEntry.createWriter(
                                        writer => {
                         					              writer.onwrite = e => {
                                                // that.count = 100;
                                                // that.tap = `成功导出[${length}]条数据，文件路径为：${e.target.fileName}`;
                                                setTimeout(function() {
                                                    // that.proshow = false;
                                                    uni.openDocument({
                                                        filePath: `file://${e.target.fileName}`, //这里必须加file://否则会报错打不开文件
                                                        success: function(res) {
                                                            uni.removeStorageSync("csvData")
                                                        },
                                                        fail(res) {
                                                             uni.removeStorageSync("csvData")
                                                        }
                                                    });
                                                }, 2000);
                                            };
                                            `在这里插入代码片`
                                            writer.write(template);
                                        },
                                        function(e) {
                                             uni.removeStorageSync("csvData")
                                            uni.showToast({
                           						title: '导出文件失败，请检查你的权限',
                        		                icon: 'none'
                                            });
                                        }
                                    );
                                },
                                e => {
                                     uni.removeStorageSync("csvData")
                                }
                        
                            );
                        
                        }, function(e) {
                            alert("Request file system failed: " + e.message);
                             uni.removeStorageSync("csvData")
                        });
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
               
            },
            toChoose(e) {
                if (e == 0) {
                    uni.navigateTo({
                        url: "../csv/birthday/birthday"
                    })
                } else if (e == 1) {
                    uni.navigateTo({
                        url: "../csv/sex/sex"
                    })
                } else if (e == 2) {
                    uni.navigateTo({
                        url: "../csv/time/time"
                    })
                } else {
                    uni.navigateTo({
                        url: "../csv/times/times"
                    })
                }
            }
        }
    }
</script>

<style lang="scss">
    .container {
        border-top: 2upx solid #D2D2D7;
        box-sizing: border-box;
        padding-top: 24upx;

        .blank {
            width: 100%;
            height: 50upx;
        }

        .btn {
            width: 478upx;
            height: 96upx;
            background: rgba(29, 29, 31, 0.1);
            border-radius: 20upx;
            opacity: 1;
            border: 2upx solid #707070;
            font-size: 40upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-top: 144upx;
        }

        .content {
            border-left: 2upx solid #D2D2D7;
            border-right: 2upx solid #D2D2D7;
            border-top: 2upx solid #D2D2D7;
            margin: 32upx 0 0;
        }

        .contentItem {
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
            padding: 32upx;
            border-bottom: 2upx solid #D2D2D7;

            .tabLeft {
                .tabTop {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                    margin-bottom: 20upx;
                }

                .tabBot {
                    font-size: 32upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #AAAAAA;
                }
            }

            .tabRight {
                image {
                    height: 46upx;
                    width: 23upx;
                }
            }
        }


        .item {
            display: flex;
            align-items: center;
            margin-top: 60upx;

            .itemLeft {
                margin-right: 28upx;
            }

            .unselect {
                width: 40upx;
                height: 40upx;
                background: #FFFFFF;
                opacity: 1;
                box-sizing: border-box;
                border: 2upx solid #707070;
                border-radius: 50%;
            }

            .selected {
                width: 40upx;
                height: 40upx;
                background: #FFFFFF;
                border: 2upx solid #1A73E8;
                border-radius: 50%;
                box-sizing: border-box;
                padding: 6upx;

                .fill {
                    width: 100%;
                    height: 100%;
                    background: #1A73E8;
                    border-radius: 50%;
                }
            }

        }

        .mem1 {
            font-size: 28upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            text-align: right;
            margin-bottom: 14upx;
        }

        .mem2 {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            text-align: right;

            text {
                color: #1D1D1F;
            }
        }

        .title {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            margin-top: 40upx;
        }
    }
</style>
