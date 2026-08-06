<template>
    <div style="position: relative;">
        <div class="tit">予約一覧</div>
        <div class="app-box no-size" style="position: sticky;top:0;z-index: 67;height: 80vh;overflow-y: scroll;">
            <div class="tab">
                <div class="item no-size" style="border-bottom: 1px solid #d2d2d7" @click="yueTongShow = true">
                    <div class="lef">予約通知</div>
                    <div class="rigs" style="cursor: pointer;" v-text="allNum"></div>
                </div>
                <div class="item no-size">
                    <div class="lef">今日の予約</div>
                    <div class="rig">{{todaynum}}</div>
                </div>
            </div>
            <div style="position: sticky;top: 0;z-index: 66;background: #fff;border-bottom:1px solid #aaa;padding-bottom:10px">
                <div class="time">
                    <div class="lef-time">{{timers}}</div>
                    <!-- <div class="lef-time">{{timers2}}</div> -->
                    <div class="rig-time shou" @click="changeTab">
                        <div :class="tab ? 't-le lef' : 't-le'">日</div>
                        <div :class="!tab ? 't-le lef' : 't-le'">週</div>
                    </div>
                </div>
                <div class="tabs" v-if="!tab">
                    <div :class="item.checked ? 'item active' : 'item shou'" v-for="(item, index) in staff" :key="index"
                        @click="selectStaff(index)">
                        {{ item.name }}
                    </div>
                </div>
                <div style="box-sizing: border-box;padding: 0 50px;">
                    <calandars @change="change"></calandars>
                </div>
            </div>

            <!-- 日 -->
            <div v-if="tab" class="pres" style="height: 65vh;overflow-y: scroll;">
                <progres :manager="managers" @addNew="addNewAppointment" :tdate="dDate" :times="times"
                    @checkdetail="godetail" @checkorder="goOrder"></progres>
            </div>
            <!-- 周 -->
            <div v-if="!tab" id="wk" style="height: 65vh;overflow-y: scroll;">
                <week :times="times" @addNew="addNewAppointment" :info="cinfo" :weekData="weekData"></week>
            </div>
        </div>
        <!-- 预约通知 -->
        <div class="fixed" v-show="yueTongShow">
            <div class="zhe"></div>
            <div class="tongzhi-box no-size">
                <div class="tit-flex no-size">
                    <div class="lef">予約通知一覧 <span>({{tongList.length}})</span></div>
                    <div class="rig" @click="yueTongShow = false">
                        <img src="../static/tclose.png" alt="" />
                    </div>
                </div>
                <div class="item no-size" @click="godetail(item.id,item.oid)" v-for="(item, index) in tongList" :key="index" @mousemove="move(index, 1)"
                    @mouseleave="move(index, 2)">
                    <div class="left">
                        <div class="dian" v-if="item.is_read == 0"></div>
                    </div>
                    <div class="right">
                        <div class="tit-b shou">
                            <div class="tt">{{ item.title }}</div>
                            <div class="tm">{{ item.time }}</div>
                        </div>
                        <div class="time-boxs shou">
                            <div class="t-le shou">
                                <img src="../static/xie-gou.png" alt="" />{{ item.day }}
                            </div>
                            <div class="t-ri">
                                {{ item.cl_t }}
                            </div>
                        </div>
                        <div class="des shou">
                            {{ item.des }}
                        </div>
                        <div class="bots">
                            <div class="b-l">
                                <div class="zhi" v-if="item.appoint_admin != null">
                                    指名<span class="hui"
                                        :style="`background:${item.appoint_admin.color}`"></span>{{item.appoint_admin.name}}
                                </div>
                                <div class="zhi" style="margin-bottom: 0">
                                    当日<span class="lan"
                                        :style="`background:${item.day_admin.color}`"></span>{{ item.day_admin.name }}
                                </div>
                            </div>
                            <div class="b-r">
                                <div class="del shou" @click.stop="del(item.id,item.oid)" v-if="item.show">
                                    <img src="../static/del-box.png" alt="" />
                                    <div class="txt">削除</div>
                                </div>
                                <div class="collect">
                                    <img src="../static/collect.png" alt="" class="shou" v-if="item.is_collect == 0"
                                        @click.stop="collectNotice(item.id,index)" />
                                    <img src="../static/collect1.png" alt="" class="shou" v-if="item.is_collect == 1"
                                        @click.stop="collectNotice(item.id,index)" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 预约详情 -->
        <div class="fixed" v-show="yueDetailShow1">
            <div class="zhe"></div>
            <div class="tong-detail-box">
                <div class="detail-tit">
                    <div class="lef">
                        予約内容
                        <img src="../static/tclose.png" alt="" @click="yueDetailShow1 = false" class="shou" />
                    </div>
                </div>
                <div class="detail-box no-size">
                    <div class="time" v-text="orderInfo.create_time"></div>
                    <div class="d-b">
                        <div class="d-it">
                            <div class="name">氏名</div>
                            <div class="text" style="color: #1a73e8" v-text="orderDetail.name"></div>
                        </div>
                        <div class="d-it">
                            <div class="name">電話番号</div>
                            <div class="text" style="color: #1d1d1f">
                                <div class="text" v-text="orderDetail.phone"></div>
                            </div>
                        </div>
                        <div class="d-it">
                            <div class="name">会員ID</div>
                            <div class="text" v-text="orderDetail.member_id"></div>
                        </div>
                        <div class="d-it">
                            <div class="name" style="margin-bottom: 0">予約番号1</div>
                            <div class="text" v-text="orderDetail.order_no"></div>
                        </div>
                    </div>
                    <div class="d-b" v-if="orderDetail.smid">
                        <div class="des">
                            {{orderDetail.menuName}}
                        </div>
                        <div class="p-time">所要時間：{{orderDetail.menuTime}}分<span>¥{{orderDetail.menuPrice}}</span>
                        </div>
                    </div>
                    <div v-else class="d-b">
                        <div class="des" style="color: rgba(217, 48, 37, 0.65);font-size: 16px;">
                            お休み・勤務時間外
                        </div>
                    </div>
                    <div class="d-b" style="padding: 0">
                        <div class="it-for">
                            <div class="it-l">日付</div>
                            <div class="it-r" v-text="orderDetail.day"></div>
                        </div>
                        <div class="it-for">
                            <div class="it-l">開始終了</div>
                            <div class="it-r" v-text="orderDetail.time"></div>
                        </div>
                        <!-- <div class="it-for">
              <div class="it-l"></div>
              <div class="it-r">11:00</div>
            </div> -->
                        <div class="it-for" v-if="orderDetail.appoint_admin == null">
                            <div class="it-l">指名担当</div>
                            <div class="it-r">
                                <span style="background: #aaaaaa"></span> 指名無し
                            </div>
                        </div>
                        <div class="it-for" v-if="orderDetail.appoint_admin != null">
                            <div class="it-l">指名担当</div>
                            <div class="it-r">
                                <span :style="`background:${orderDetail.appoint_admin.color}`"></span>
                                {{orderDetail.appoint_admin.name}}
                            </div>
                        </div>
                        <div class="it-for" style="border: 0" v-if="orderDetail.day_admin != null">
                            <div class="it-l">当日担当</div>
                            <div class="it-r">
                                <span v-if="orderDetail.day_admin.color"
                                    :style="`background:${orderDetail.day_admin.color}`"></span><span v-else
                                    style="background: #d2d2d7;"></span>{{orderDetail.day_admin.name}}
                            </div>
                        </div>
                    </div>
                    <div class="d-b no-size" style="padding: 10px; min-height: 108px">
                        <div class="d-tt">メモ</div>
                        <div class="d-text" v-text="orderDetail.remark"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="fixed" v-show="yueDetailShow">
            <div class="zhe"></div>
            <div class="tong-detail-box">
                <div class="detail-tit">
                    <div class="lef">
                        予約内容
                        <img src="../static/tclose.png" alt="" @click="yueDetailShow = false" class="shou" />
                    </div>
                </div>
                <div class="detail-box no-size">
                    <div class="time" v-text="orderInfo.create_time"></div>
                    <div class="d-b">
                        <div class="d-it">
                            <div class="name">氏名</div>
                            <div class="text" style="color: #1a73e8" v-text="orderDetail.name"></div>
                        </div>
                        <div class="d-it">
                            <div class="name">電話番号</div>
                            <div class="text" style="color: #1d1d1f">
                                <div class="text" v-text="orderDetail.phone"></div>
                            </div>
                        </div>
                        <div class="d-it">
                            <div class="name">会員ID</div>
                            <div class="text" v-text="orderDetail.member_id"></div>
                        </div>
                        <div class="d-it">
                            <div class="name" style="margin-bottom: 0">予約番号</div>
                            <div class="text" v-text="orderDetail.order_no"></div>
                        </div>
                    </div>
                    <div class="d-b" v-if="orderDetail.smid">
                        <div class="des">
                            {{orderDetail.menuName}}
                        </div>
                        <div class="p-time">所要時間：{{orderDetail.menuTime}}分<span>{{orderDetail.menuPrice}}</span>
                        </div>
                    </div>
                    <div v-else class="d-b">
                        <div class="des" style="color: rgba(217, 48, 37, 0.65);font-size: 16px;">
                            お休み・勤務時間外
                        </div>
                    </div>
                    <div class="d-b" style="padding: 0">
                        <div class="it-for">
                            <div class="it-l">日付</div>
                            <div class="it-r" v-text="orderDetail.day"></div>
                        </div>
                        <div class="it-for">
                            <div class="it-l">開始終了</div>
                            <div class="it-r" v-text="orderDetail.time"></div>
                        </div>
                        <!-- <div class="it-for">
              <div class="it-l"></div>
              <div class="it-r">11:00</div>
            </div> -->
                        <div class="it-for" v-if="orderDetail.appoint_admin == null">
                            <div class="it-l">指名担当</div>
                            <div class="it-r">
                                <span style="background: #aaaaaa"></span> 指名なし
                            </div>
                        </div>
                        <div class="it-for" v-if="orderDetail.appoint_admin != null">
                            <div class="it-l">指名担当</div>
                            <div class="it-r">
                                <span :style="`background:${orderDetail.appoint_admin.color}`"></span>
                                {{orderDetail.appoint_admin.name}}
                            </div>
                        </div>
                        <div class="it-for" style="border: 0" v-if="orderDetail.day_admin != null">
                            <div class="it-l">当日担当</div>
                            <div class="it-r">
                                <span v-if="orderDetail.day_admin.color"
                                    :style="`background:${orderDetail.day_admin.color}`"></span><span v-else
                                    style="background: #d2d2d7;"></span>{{orderDetail.day_admin.name}}
                            </div>
                        </div>
                    </div>
                    <div class="d-b no-size" style="padding: 10px; min-height: 108px">
                        <div class="d-tt">メモ</div>
                        <div class="d-text" v-text="orderDetail.remark"></div>
                    </div>
                    <div class="changhe no-size">
                        予約内容変更をお客さんにお知らせする場合は<span class="kfl" style="cursor: pointer;"
                            @click.stop="sendMessage">こちら</span>
                    </div>
                </div>
                <div class="fenge"></div>
                <div class="flex-btns">
                    <div class="l-btn shou" @click="showale2 = true">削　除</div>
                    <div class="r-btn shou" @click="edit()">編　集</div>
                </div>
            </div>
        </div>
        <!-- 新增预约 -->
        <div class="fixed" v-show="newYueShow">
            <div class="zhe"></div>
            <div class="edit-box">
                <div class="edit-tit" style="position: sticky;top: 0;z-index: 1998;">
                    <div class="lef">
                        新規予約
                        <img src="../static/tclose.png" alt="" @click="resetData" class="shou" />
                    </div>
                </div>
                <div class="edit-detail-box no-size" style="margin-top: 40px">
                    <div class="ed-boo no-size">
                        <div class="search">
                            <div class="sea">
                                <div class="big-m">
                                    <img src="../static/search.png" alt="" />
                                </div>
                                <input type="search" class="no-size" placeholder="検索" @focus="focuss" />
                            </div>
                            <i class="el-icon-close shou"></i>
                        </div>
                        <div class="d-it">
                            <div class="name">氏名</div>
                            <div class="text new-ipt" style="color: #1a73e8">
                                <input type="text" v-model="userName" class="no-size" placeholder="" />
                            </div>
                        </div>
                        <div class="d-it">
                            <div class="name">電話番号</div>
                            <div class="text new-ipt" style="color: #1d1d1f">
                                <input type="text" v-model="userPhone" class="no-size" placeholder="" />
                            </div>
                        </div>
                        <div class="d-it">
                            <div class="name">性別</div>
                            <div class="text">
                                <el-select v-model="sex" style="box-sizing: border-box;padding: 0 10px;" placeholder="選択">
                                    <el-option v-for="item in sexOptions" :key="item.value" :label="item.label"
                                        :value="item.value">
                                    </el-option>
                                </el-select>
                            </div>
                        </div>
                        <div class="d-it">
                            <div class="name">会員ID</div>
                            <div class="text new-ipt" style="color: #1d1d1f;border:none">
                                <input type="text" v-model="userId" class="no-size" disabled />
                            </div>
                        </div>
                    </div>
                    <div class="ed-bo shou flex" @click="huiYuan(2)">
                        <div class="le">
                            <div class="hui-selet" v-if="orderInfo.id > 0">
                                <div class="des" v-text="selectInfo.name">
                                </div>
                                <div v-if="selectInfo.price" class="p-time">
                                    所要時間：{{selectInfo.time}}分<span>¥{{selectInfo.price}}</span></div>
                            </div>
                            <div v-if="orderInfo.id == 0" style="color: rgba(217, 48, 37, 0.65);font-size: 16px;">
                                お休み・勤務時間外
                            </div>
                            <div v-if="orderInfo.id == -1">メニューを選択</div>
                        </div>
                        <div class="ri" style="position: relative;right: 10px;">
                            <i class="el-icon-arrow-right" style="font-size: 20px"></i>
                        </div>
                    </div>
                    <div class="ed-bo no-size" style="padding: 0">
                        <div class="flex spbet no-size" style="position: relative">
                            <div class="left">日付</div>
                            <div class="right shou">
                                {{ time }}
                            </div>
                        </div>
                        <div class="flex spbet no-size">
                            <div class="left">開始</div>
                            <div class="right shou">
                                {{startTime}}
                            </div>
                        </div>
                        <div class="flex spbet no-size">
                            <div class="left">終了</div>
                            <div class="right shou" v-if="orderInfo.id != 0">
                                {{endTime}}
                            </div>
                            <div class="right shou" v-if="orderInfo.id == 0">
                                <el-time-select v-model="endTime" :picker-options="{
                      start: '09:00',
                      step: '00:30',
                      end: '22:00',
                    }" placeholder="選択してください">
                                </el-time-select>
                            </div>
                        </div>
                        <div class="flex spbet no-size">
                            <div class="left">指名担当</div>
                            <div class="right selet" style="display: flex;align-items: center;" @click="chooseZhi">
                                <span class="cor" :style="`background:${staffColor}`"></span>
                                <div class="ho">{{zhiName}}</div>
                            </div>
                        </div>
                        <div class="flex spbet no-size" style="border: 0">
                            <div class="left">当日担当</div>
                            <div class="right selet" style="display: flex;align-items: center;" @click="chooseDang">
                                <span class="cor" :style="`background:${staffColor1}`"></span>
                                <div class="ho">{{dangName}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="ed-bo no-size" style="padding-left: 10px; padding-right: 10px">
                        <div class="txt">メモ</div>
                        <textarea placeholder="メモがあれば入力してください。" v-model="newBeizhu"></textarea>
                    </div>
                </div>
                <div class="fen"></div>
                <div class="btn-sf">
                    <!-- <div class="le bts shou" @click="yueEditShow = false">戻　る</div> -->
                    <div class="ri bts shou" style="margin: 0 auto" @click="showale1 = true">
                        この内容で予約する
                    </div>
                </div>
            </div>
        </div>
        <!-- 指名列表 -->
        <div class="mask" v-if="staffShow">
            <div class="staffContent">
                <div class="staffTitle">
                    <img src="../static/tclose.png" alt="" @click="staffShow = false" class="closeStaff" />
                    担当者を選択
                </div>
                <div class="staffAll">
                    <div class="staffItem" v-for="item,index in showStaffList">
                        <div class="selContent" :class='zhgiId == item.id?"borderblue" : ""'
                            @click="selThis(item,index)">
                            <div class="selInner" v-if="zhgiId == item.id"></div>
                        </div>
                        <div class="selDetail">
                            <div class="colorContent" :style="`background:${item.color?item.color:defaultColor}`"></div>
                            <div class="selName" :style="zhgiId == item.id?'color:#000' : ''" v-text="item.name"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- 当日担当列表 -->
        <div class="mask" v-if="staffShow1">
            <div class="staffContent">
                <div class="staffTitle">
                    <img src="../static/tclose.png" alt="" @click="staffShow1 = false" class="closeStaff" />
                    担当者を選択
                </div>
                <div class="staffAll">
                    <div class="staffItem" v-for="item,index in showStaffList">
                        <div class="selContent" :class='dangiId == item.id?"borderblue" : ""'
                            @click="selThis(item,index)">
                            <div class="selInner" v-if="dangiId == item.id"></div>
                        </div>
                        <div class="selDetail">
                            <div class="colorContent" :style="`background:${item.color?item.color:defaultColor}`"></div>
                            <div class="selName" :style="dangiId == item.id?'color:#000' : ''" v-text="item.name"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- 搜索列表 -->
        <div class="fixed" v-show="searchShow">
            <div class="zhe"></div>
            <div class="search-box">
                <div class="title no-size">
                    <div class="lef">
                        <img src="../static/tclose.png" alt="" @click="searchShow = false" class="shou" />
                        メンバー検索
                    </div>
                </div>
                <div class="sea-box">
                    <div class="search" style="display: flex;align-items: center;">
                        <div class="sea">
                            <div class="big-m">
                                <img src="../static/search.png" alt="" />
                            </div>
                            <input type="search" class="no-size" v-model="keyWord" placeholder="名前、電話番号、IDで検索"
                                @search="showMemberList" />
                        </div>
                        <div class="sum">検索結果:{{ searchList.length }}件</div>
                    </div>

                    <div class="list-box">
                        <div class="top">
                            <div class="it" style="margin-left: 70px; width: 100px">名前</div>
                            <div class="it" style="margin-left: 2px; width: 100px">カナ</div>
                            <div class="it" style="margin-left: 12px; width: 100px">
                                電話番号
                            </div>
                            <div class="it" style="margin-left: 21px; width: 60px">
                                顧客NO
                            </div>
                            <div class="it" style="width: 120px; margin-left: 20px">
                                会員ID
                            </div>
                        </div>
                        <div class="items no-size" v-for="(item, index) in searchList" :key="index">
                            <div class="img">
                                <img :src="item.avatar" alt="" />
                            </div>
                            <div class="name">
                                {{ item.name }}
                            </div>
                            <div class="txt">
                                {{ item.pronunciation }}
                            </div>
                            <div class="tel">
                                {{ item.phone }}
                            </div>
                            <div class="no">
                                {{ item.member_no }}
                            </div>
                            <div class="id">
                                {{ item.member_id }}
                            </div>
                            <div class="slet shou" @click="selectMember(item)">選択</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 菜单列表 -->
        <div class="fixed" v-show="huiYuanShow">
            <div class="zhe"></div>
            <div class="huiYuanSelet">
                <div class="title no-size">
                    <div class="lef">
                        <img src="../static/tclose.png" alt="" @click="huiYuanBack()" class="shou" />
                        メニューを選択
                    </div>
                </div>
                <div class="form">
                    <div class="it-flex no-size">
                        <div class="left">お休み・勤務時間外</div>
                        <div class="right shou" @click="chooseMenuItem(0)">選択</div>
                    </div>
                    <div class="form-box">
                        <div class="items" v-for="(item, index) in menuList" :key="index">
                            <div class="items-menu-title no-size">{{ item.name }}</div>
                            <div class="item no-size" v-for="(item1, index1) in item.menus" :key="index1">
                                <div class="ll">
                                    <div class="le">
                                        <img :src="item1.image[0]" alt="" style="object-fit: cover;"
                                            v-if="item1.image.length > 0" />
                                    </div>
                                    <div class="rig">
                                        <div class="tit">{{ item1.title }}</div>
                                        <div class="time" v-if="item1.is_order == 1">
                                            所要時間:{{ item1.order_time }}分
                                            <span class="price" >{{ item1.price }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="sel shou" v-if="item1.is_order == 1" @click="chooseMenuItem(item,item1)">選択</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 删除详情 -->
        <div class="fixed" v-show="delShow">
            <div class="zhe"></div>
            <div class="del-box">
                <div class="tit">
                    <img src="../static/wclose.png" alt="" class="shou" @click="delShow = false" />
                    予約の削除
                </div>
                <div class="box no-size">
                    <div class="item">
                        <div class="let">氏名</div>
                        <div class="rig" style="color: #1a73e8" v-text="orderInfo.name"></div>
                    </div>
                    <div class="item">
                        <div class="let">電話番号</div>
                        <div class="rig" style="color: #1d1d1f" v-text="orderInfo.phone"></div>
                    </div>
                    <div class="item">
                        <div class="let">会員ID</div>
                        <div class="rig" v-text="orderInfo.member_id"></div>
                    </div>
                    <div class="item">
                        <div class="let">予約番号</div>
                        <div class="rig" v-text="orderInfo.order_no"></div>
                    </div>
                    <div class="des" v-if="orderInfo.menu != null">
                        {{orderInfo.menu.title}}
                        <div class="tim">
                            所要時間：{{orderInfo.menu.order_time}}分　　
                            <span class="pri">¥3,500</span>
                        </div>
                        <div class="pay">日付 : {{orderInfo.day}} {{orderInfo.time}}</div>
                    </div>
                    <div class="items" v-if="orderInfo.appoint_admin != null">
                        <div class="left">指名担当</div>
                        <div class="color" :style="`background: ${orderInfo.appoint_admin.color}`"></div>
                        <div class="name" v-text="orderInfo.appoint_admin.name"></div>
                    </div>
                    <div class="items" v-if="orderInfo.day_admin != null">
                        <div class="left">当日担当</div>
                        <div class="color" :style="`background: ${orderInfo.day_admin.color}`"></div>
                        <div class="name" v-text="orderInfo.day_admin.name"></div>
                    </div>
                    <div class="ti">この予約を削除してよろしですか？</div>
                </div>
                <div class="del-btn no-size">
                    <div class="btn shou" @click="deletenoticeAppointment">削　除</div>
                </div>
            </div>
        </div>
        <div class="fixed" v-show="keepShow">
            <div class="zhe"></div>
            <div class="sus-box">
                <img src="../static/success-hui.png" alt="" />
                <div class="keep">配信しました</div>
            </div>
        </div>
        
        <!-- 发送通知 -->
        <div class="fixed" v-show="tongKeepShow">
            <div class="zhe"></div>
            <div class="del-box">
                <div class="tit">
                    <img src="../static/wclose.png" alt="" class="shou" @click="tongKeepShow = false" />
                    予約内容の通知
                </div>
                <div class="box no-size">
                    <div class="item">
                        <div class="let">氏名</div>
                        <div class="rig" style="color: #1a73e8" v-text="orderDetail.name"></div>
                    </div>
                    <div class="item">
                        <div class="let">電話番号</div>
                        <div class="rig" style="color: #1d1d1f" v-text="orderDetail.phone"></div>
                    </div>
                    <div class="item">
                        <div class="let">会員ID</div>
                        <div class="rig" v-text="orderDetail.member_id"></div>
                    </div>
                    <div class="item">
                        <div class="let">予約番号</div>
                        <div class="rig" v-text="orderDetail.order_no"></div>
                    </div>

                    <div class="des" v-if="selectInfo.id > 0">
                        {{orderDetail.menu.title}}
                        <div class="tim">
                            所要時間：{{selectInfo.time}}分　　
                            <span class="pri">{{selectInfo.price}}</span>
                        </div>
                        <div class="pay">日付 : {{orderDetail.day}}</div>
                    </div>
                    <div class="des" v-else>
                        お休み・勤務時間外
                    </div>
                    <div class="items">
                        <div class="left">指名担当</div>
                        <div class="color" :style="`background: ${staffColor}`"></div>
                        <div class="name">{{zhiName}}</div>
                    </div>
                    <div class="items">
                        <div class="left">当日担当</div>
                        <div class="color" :style="`background: ${staffColor1}`"></div>
                        <div class="name">{{dangName}}</div>
                    </div>
                    <div class="ti">この予約を削除してよろしですか？</div>
                </div>
                <div class="del-btn no-size">
                    <div class="btns shou" @click="sends()">
                        この内容を送る<img src="../static/send-fei.png" alt="" />
                    </div>
                </div>
            </div>
        </div>
        <!-- 用户详情 -->
        <div class="fixed" v-show="zuoQingShow">
            <div class="zhe"></div>
            <div class="zuo-box">
                <div class="member">
                    <div class="me-le">
                        <img src="@/static/icon3.svg" alt="" />メンバー情報
                    </div>
                    <div class="me-ri">
                        <img src="../static/wclose.png" alt="" class="shou" @click="zuoQingShow = false" />
                    </div>
                </div>
                <div class="member_info">
                    <div class="member_item h">
                        <div class="grey_left">ID</div>
                        <div class="bor">1923055025809</div>
                    </div>
                    <div class="member_item h">
                        <div class="grey_left">顧客NO</div>
                        <div class="bor">105</div>
                    </div>
                    <div class="member_item h">
                        <div class="grey_left">カードの種類</div>
                        <div style="font-weight: bold">メンバーズカード名</div>
                    </div>
                    <div class="member_item h">
                        <div class="grey_left">来店回数</div>
                        <div class="blue">5</div>
                    </div>
                    <div class="member_item h">
                        <div class="grey_left">氏名</div>
                        <div>山田太郎</div>
                    </div>
                    <div class="member_item h">
                        <div class="grey_left">カナ</div>
                        <div>ヤマダタロウ</div>
                    </div>
                    <div class="member_item h">
                        <div class="grey_left">電話番号</div>
                        <div>08012345678</div>
                    </div>
                    <div class="member_item h">
                        <div class="grey_left">性別</div>
                        <div>男性</div>
                    </div>
                    <div class="member_item h">
                        <div class="grey_left">生年月日</div>
                        <div>2000年　10月　09日</div>
                    </div>

                    <div class="member_item" style="align-items: flex-start">
                        <div class="grey_left">住所</div>
                        <div>
                            123-456<br />東京都 <br />世田谷区テキスト町1-22-5 <br />201号室
                        </div>
                    </div>
                    <div class="member_item" style="margin-top: 24px; align-items: flex-start">
                        <div class="grey_left">備考</div>
                        <div class="remarks no-size" style="width: 502px">
                            <textarea v-model="beizhus" v-if="isKeep"></textarea>
                            <span v-if="!isKeep">{{ beizhus }}</span>
                        </div>
                    </div>
                </div>
                <div class="fenge"></div>
                <div class="add-pictur no-size">
                    <div class="pic-tit">添付画像(最大12枚)</div>
                    <div class="des">
                        アップロードしたデータは、お客様には反映されません。
                    </div>
                    <div class="img-bos">
                        <div class="img-boxs">
                            <div class="img-item" v-for="(item, index) in imgList" :key="index">
                                <img v-if="isKeep" src="../static/wclose.png" alt="" class="colse shou"
                                    @click="delPicture(index)" />
                                <img :src="item" alt="" class="tu" />
                            </div>
                        </div>
                        <el-upload v-if="isKeep" class="upload-demo" accept="image/*"
                            action="http://che.lanyanceshi.com/tool/upload/file" :on-success="handleAvatarSuccess" drag
                            :limit="12" :show-file-list="false" :file-list="fileList">
                            <div class="tuo no-size">
                                <img src="../static/edit-image.png" alt="" />
                                <br />
                                <span class="txt">ここにファイルをドラッグ</span>
                            </div>
                        </el-upload>
                        <div class="add" v-if="isKeep">
                            <el-upload class="upload-demo" accept="image/*"
                                action="http://che.lanyanceshi.com/tool/upload/file" :on-success="handleAvatarSuccess"
                                drag :limit="12" :show-file-list="false" :file-list="fileList">
                                <div class="zhui">
                                    <span class="el-icon-plus" style="font-weight: 700"></span>
                                    ファイル追加
                                </div>
                            </el-upload>
                        </div>
                    </div>
                </div>
                <div class="bots-border"></div>
                <div class="botss">
                    <div class="left shou" @click="zuoQingShow = false">戻　る</div>
                    <div class="right shou" v-if="isKeep == false" @click="isKeep = true">
                        編　集
                    </div>
                    <div class="right shou" v-if="isKeep == true">保　存</div>
                </div>
            </div>
        </div>
        <!-- 预约编辑 -->
        <div class="fixed" v-show="yueEditShow">
            <div class="zhe"></div>
            <div class="edit-box">
                <div class="edit-tit" style="position: sticky;top: 0;z-index: 1998;">
                    <div class="lef">
                        予約編集
                        <img src="../static/tclose.png" alt="" @click="yueEditShow = false" class="shou" />
                    </div>
                </div>
                <div class="edit-detail-box no-size">
                    <div class="time" v-text="orderDetail.create_time"></div>
                    <div class="ed-bo no-size">
                        <div class="d-it">
                            <div class="name">氏名</div>
                            <div class="text" style="color: #1a73e8" v-text="orderDetail.name"></div>
                        </div>
                        <div class="d-it">
                            <div class="name">電話番号</div>
                            <div class="text" style="color: #1d1d1f">
                                <input type="text" v-model="orderDetail.phone" class="no-size" />
                            </div>
                        </div>
                        <div class="d-it">
                            <div class="name">会員ID</div>
                            <div class="text" v-text="orderDetail.member_id"></div>
                        </div>
                        <div class="d-it">
                            <div class="name" style="margin-bottom: 0">予約番号</div>
                            <div class="text" v-text="orderDetail.order_no"></div>
                        </div>
                    </div>
                    <div class="ed-bo shou flex" @click="huiYuan(1)">
                        <div class="le">
                            <div class="hui-selet" v-if="orderDetail.smid > 0">
                                <div class="des" v-text="selectInfo.name">
                                </div>
                                <div v-if="selectInfo.price" class="p-time">
                                    所要時間：{{selectInfo.time}}分<span>¥{{selectInfo.price}}</span></div>
                            </div>
                            <div v-if="orderDetail.smid == 0" style="color: rgba(217, 48, 37, 0.65);font-size: 16px;">
                                お休み・勤務時間外
                            </div>
                            <div v-if="orderDetail.smid == -1">メニューを選択</div>
                        </div>
                        <div class="ri" style="position: relative;right: 6px;z-index: 799;">
                            <i class="el-icon-arrow-right" style="font-size: 20px"></i>
                        </div>
                    </div>

                    <div class="ed-bo no-size" style="padding: 0">
                        <div class="flex spbet no-size" style="position: relative">
                            <div class="left">日付</div>
                            <div class="right shou" style="width: 73px;right: 8px;position: relative;">
                                <el-date-picker style="width: 85px;" v-model="time" type="date" placeholder="选择日期"
                                    size="small" @change="changeTimes">
                                </el-date-picker>
                            </div>
                        </div>
                        <div class="flex spbet no-size">
                            <div class="left">開始</div>
                            <div class="right shou">
                                <el-time-select v-model="startTime" :picker-options="{
                              start: '09:00',
                              step: '00:30',
                              end: '22:00',
                            }" placeholder="请选择" @change="editTimeVal">
                                </el-time-select>
                            </div>
                        </div>
                        <div class="flex spbet no-size">
                            <div class="left">終了</div>
                            <!-- 通常メニュー(smid>0)の予約編集でも終了時間のドラムロールを表示し編集可能にする
                                 (従来は smid==0 の お休み・勤務時間外 のみ表示。不具合修正 2026.7.29) -->
                            <div class="right shou">
                                <el-time-select v-model="endTime" :picker-options="{
                      start: '09:00',
                      step: '00:30',
                      end: '22:00',
                    }" placeholder="请选择">
                                </el-time-select>
                            </div>
                        </div>
                        <div class="flex spbet no-size" v-if="!zhgiId">
                            <div class="left">指名担当</div>
                            <div class="right selet" style="display: flex;align-items: center;" @click="chooseZhi">
                                <span class="cor" style="background:#aaa"></span>
                                <div class="ho">{{zhiName}}</div>
                            </div>
                        </div>
                        <div class="flex spbet no-size"  v-else>
                            <div class="left">指名担当{{zhgiId}}</div>
                            <div class="right selet" style="display: flex;align-items: center;" @click="chooseZhi">
                                <span class="cor" :style="`background:${staffColor}`"></span>
                                <div class="ho">{{zhiName}}</div>
                            </div>
                        </div>
                        <div class="flex spbet no-size" style="border: 0">
                            <div class="left">当日担当</div>
                            <div class="right selet" style="display: flex;align-items: center;" @click="chooseDang">
                                <span class="cor" :style="`background:${staffColor1}`"></span>
                                <div class="ho">{{dangName}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="ed-bo no-size" style="padding-left: 10px; padding-right: 10px">
                        <div class="txt">メモ</div>
                        <textarea v-model="orderDetail.remark" placeholder=""></textarea>
                    </div>
                </div>
                <div class="fen"></div>
                <div class="btn-sf">
                    <div class="le bts shou" @click="yueEditShow = false">戻　る</div>
                    <div class="ri bts shou" @click="editKeep()">保　存</div>
                </div>
            </div>
        </div>
        <div class="mask" v-if="showale1">
            <div class="ale">
                <div class="aleTop">
                    <div class="aleTitle">
                        予約を決定しますか？
                    </div>
                    <div class="alecontent">
                        決定すると、この予約情報は
                        お客様に送信されます。
                    </div>
                </div>
                <div class="alebtn colorBlue" @click="saveData">
                    予約する
                </div>
                <div class="alebtn colorRed" @click="showale1 = false">
                    キャンセル
                </div>
            </div>
        </div>
        <div class="mask" v-if="showale2">
            <div class="ale">
                <div class="aleTop">
                    <div class="aleTitle">
                        この予約を取り消しますか？？
                    </div>
                    <div class="alecontent">
                       取り消しすると、この予約情報は
                       完全に削除されます。
                    </div>
                </div>
                <div class="alebtn colorRed" @click="dele">
                    予約を取り消す
                </div>
                <div class="alebtn colorBlue" @click="showale2 = false">
                    キャンセル
                </div>
            </div>
        </div>
        <div class="nqmask" v-if="showg">
            <div class="nqmaskContent">
                <div class="nqmaskTitle">
                    ご契約のプランでは <br/>この機能をご利用できません。<br/> プランを変更しますか？
                </div>
                <div class="nqmaskDetail">
                   プランを変更するとすぐに、この機能をご利用できます。
                </div>
                <div class="nqbt" @click="toBuy">
                    プランを変更
                </div>
                <div class="nqbt2" @click="goback">
                   キャンセル
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Month from "@/components/rili";
    import progres from "@/components/progress/progress.vue"
    import week from "@/components/week/week.vue"
    import calandars from "@/components/calandars.vue"
    import {
        getStaffList,
        getAppointmentList,
        getMemberList,
        getFuDetail,
        getCates,
        getMenuList,
        addAppointment,
        getAppointmentLists,
        editAppointNotice,
        deleteAppointNotice,
        getAppointNoticeDetail,
        deleteAppointment,
        editAppointment,
        getConfig,
        getCalendar,
        cahngeInof,
        deleteOrder,
        getStore,
        readAppointNotice
    } from "@/http/api.js"
    export default {
        name: "",
        components: {
            Month,
            week,
            progres,
            calandars
        },
        data() {
            return {
                showale1: false,
                showStaffList: [],
                zhgiId: 0,
                dangiId: "",
                defaultColor: "#d2d2d7",
                staffShow: false, //控制员工选择页面是否显示
                chooseStaff: 1,
                staffShow1: false,
                isKeep: false,
                dDate: "",
                deleteid: "",
                allNum: 0,
                cinfo: {},
                showale2:false,
                deleteindex: "",
                tb: "",
                beizhus: "テキスト・テキスト・テキスト・テキスト・テキスト・テキスト・テキスト・",
                selectInfo: {},
                zhiName: "指名なし",
                dangName: "指名なし",
                timers: "",
                sex: "",
                yueName: "",
                yuePhone: "",
                dayPay: "",
                newBeizhu: "",
                riliShow: false,
                editTime: "2022/08/10",
                startTime: "10:00",
                endTime: "00:00",
                tab: true,
                current: 0,
                searchShow: false,
                zuoQingShow: false,
                newYueShow: false,
                tongKeepShow: false,
                yueTongShow: false,
                yueDetailShow: false,
                yueDetailShow1: false,
                yueEditShow: false,
                keepShow: false,
                delShow: false,
                huiYuanShow: false,
                tel: "131311331311",
                timers2: "",
                todaynum: 0,
                keyWord: "",
                imgList: [],
                userId: "",
                times: [{
                        time: "09:00",
                        val: 9
                    },
                    {
                        time: "09:30",
                        val: 9.5
                    },
                    {
                        time: "10:00",
                        val: 10
                    },
                    {
                        time: "10:30",
                        val: 10.5
                    },
                    {
                        time: "11:00",
                        val: 11
                    },
                    {
                        time: "11:30",
                        val: 11.5
                    },
                    {
                        time: "12:00",
                        val: 12
                    },
                    {
                        time: "12:30",
                        val: 12.5
                    },
                    {
                        time: "13:00",
                        val: 13
                    },
                    {
                        time: "13:30",
                        val: 13.5
                    },
                    {
                        time: "14:00",
                        val: 14
                    },
                    {
                        time: "14:30",
                        val: 14.5
                    },
                    {
                        time: "15:00",
                        val: 15
                    },
                    {
                        time: "15:30",
                        val: 15.5
                    },
                    {
                        time: "16:00",
                        val: 16
                    },
                    {
                        time: "16:30",
                        val: 16.5
                    },
                    {
                        time: "17:00",
                        val: 17
                    },
                    {
                        time: "17:30",
                        val: 17.5
                    },
                    {
                        time: "18:00",
                        val: 18
                    },
                    {
                        time: "18:30",
                        val: 18.5
                    },
                    {
                        time: "19:00",
                        val: 19
                    },
                    {
                        time: "19:30",
                        val: 19.5
                    },
                    {
                        time: "20:00",
                        val: 20
                    },
                    {
                        time: "20:30",
                        val: 20.5
                    },
                    {
                        time: "21:00",
                        val: 21
                    },
                    {
                        time: "21:30",
                        val: 21.5
                    }
                ],
                array: [],
                manager: [],
                managers: [],
                weekData: [],
                staffColor: "#d2d2d7",
                staffColor1: "#d2d2d7",
                sexOptions: [{
                        value: 1,
                        label: "男性",
                    },
                    {
                        value: 2,
                        label: "女性",
                    },
                    {
                        value: 3,
                        label: "Xジェンダー",
                    },
                ],
                menuList: [],
                tongList: [],
                nameList: [],
                staffList: [],
                searchList: [],
                monthIndex: 0,
                months: ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"],
                year: -1,
                day: -1,
                days: 0,
                managers: [],
                staffId: 0,
                staff: [{
                    id: 0,
                    name: "指名なし",
                    checked: true
                }],
                firstday: "",
                selectday: "",
                userName: "",
                userPhone: "",
                userId: "",
                temp: {},
                zhiId: "",
                dangId: "",
                time: "",
                orderInfo: {
                    menu: {
                        title: ""
                    },
                    id:-1
                },
                orderDetail: {},
                showg:false
            };
        },
        created() {
            let i = new Date();
            this.dayPay = i.getFullYear() + "/" + (i.getMonth() + 1) + "/" + i.getDay();
            // this.timers = 
            // this.getNoticeList()
            // this.getStaffData()
            this.getToday()
            this.getCalendar()
        },
        mounted() {
            let myDate = new Date();
            this.monthIndex = myDate.getMonth();
            this.year = myDate.getFullYear();
            this.day = myDate.getDate() - 1;
            this.days = myDate.getDate();
        },
        methods: {
            toBuy() {
                this.$router.push({
                    path: "/utilizePlan",
                })
            },
            goback(){
                this.showg = false
                // // this.$router.go(-1)
                // window.history.back();
                this.$router.replace("topIndex")
            },
            // 编辑日期
            editTimeVal(e) {
                let that = this
                console.log("editTimer", e)
                console.log("time", that.time)

                that.calcuteTime(that.selectInfo.time)
            },

            getCalendar() {
                let that = this
                let data = {}
                data.date = this.getToday()
                data.type = "day"
                getCalendar(data).then((res) => {
                    console.log("Calendar", res)
                    // uni.hideLoading()
                    if (res.code == 200) {
                        if (res.data.week_business.length == 0) {
                            // uni.showToast({
                            //     title:"営業時間を設定してください",
                            //     icon:"none",
                            //     duration:2500
                            // })
                            // setTimeout(() => {
                            //     uni.switchTab({
                            //         url:"/pages/index/index"
                            //     })
                            // },3000)
                        } else {
                            let t = res.data.week_business
                            res.data.week_business = [...res.data.week_business, ...t]
                            that.cinfo = res.data
                            that.getList(that.getToday())
                            that.getStaffData()
                        }
                    }
                })
            },


            // 格式化时间格式
            replaceSlashWithHyphen(dateString) {
                return dateString.replace(/\//g, '-');
            },
            // 订单预约通知列表
            getNoticeList() {
                let that = this
                getAppointmentLists().then((res) => {
                    let num = 0
                    if (res.code == 200) {
                        res.data.notices.forEach((value, index) => {
                            value.show = false
                            if (value.is_read == 0) {
                                num += 1
                            }
                        })
                        that.tongList = res.data.notices
                        console.log("all", res)
                        that.allNum = res.data.unread_count
                        that.todaynum = res.data.today_count
                    }
                })
            },
            // 查看订单详情
            godetail(e,oid) {
                readAppointNotice(e).then((rest) => {
                    console.log("rest",rest)
                    if(rest.code == 200){
                        this.getNoticeList()
                    }
                })
                let that = this
                getAppointNoticeDetail(oid).then((res) => {
                    console.log("orders", res)
                    if (res.code == 200) {
                        let timeArr = res.data.order.time.split("-")
                        that.orderDetail = res.data.order
                        if(res.data.order.menu){
                             that.orderInfo.menu.id = res.data.order.menu.id
                             that.orderDetail.menuName = res.data.order.menu.title
                             that.orderDetail.menuTime = res.data.order.menu.order_time
                             that.orderDetail.menuPrice = res.data.order.menu.price
                             that.selectInfo.name = res.data.order.menu.title
                             that.selectInfo.time = res.data.order.menu.order_time
                             that.selectInfo.price = res.data.order.menu.price
                             that.orderDetail.smid = res.data.order.menu.id
                        }else{
                            that.orderDetail.smid = 0
                        }
                        
                        that.startTime = timeArr[0]
                        that.endTime = timeArr[1]
                        if (res.data.order.appoint_admin) {
                            that.zhiName = res.data.order.appoint_admin.name
                            that.zhgiId = res.data.order.appoint_admin.id
                            that.staffColor = res.data.order.appoint_admin.color
                        } else {
                            that.zhiName = '指名なし'
                            that.zhgiId = 0
                            that.staffColor = "#d2d2d7"
                        }

                        that.staffColor1 = res.data.order.day_admin.color || "#d2d2d7"
                        that.dangName = res.data.order.day_admin.name
                        that.dangiId = res.data.order.day_admin.id
                        that.yueDetailShow = true
                         // that.sceenStaff()
                    }
                })
            },
            goOrder(e) {
                console.log("goorder", e)
                let that = this
                getAppointNoticeDetail(e).then((res) => {
                    console.log("orders", res)
                    if (res.code == 200) {
                         that.orderDetail = res.data.order
                        let timeArr = res.data.order.time.split("-")
                        if(res.data.order.menu.id){
                            that.orderInfo.menu.id = res.data.order.menu.id
                            that.orderDetail.menuName = res.data.order.menu.title
                            that.orderDetail.menuTime = res.data.order.menu.order_time
                            that.orderDetail.menuPrice = res.data.order.menu.price
                            that.selectInfo.name = res.data.order.menu.title
                            that.selectInfo.time = res.data.order.menu.order_time
                            that.selectInfo.price = res.data.order.menu.price
                            that.orderDetail.smid = res.data.order.menu.id
                        }else{
                            that.orderDetail.smid = 0
                        }
                        that.startTime = timeArr[0]
                        that.endTime = timeArr[1]
                        if (res.data.order.appoint_admin) {
                            that.zhiName = res.data.order.appoint_admin.name
                            that.zhgiId = res.data.order.appoint_admin.id
                            that.staffColor = res.data.order.appoint_admin.color
                        } else {
                            that.zhiName = '指名なし'
                            that.zhgiId = 0
                            that.staffColor = "#d2d2d7"
                        }

                        that.staffColor1 = res.data.order.day_admin.color || "#d2d2d7"
                        that.dangName = res.data.order.day_admin.name
                        that.dangiId = res.data.order.day_admin.id
                        that.yueDetailShow1 = true
                        console.log("color",that.staffColor1)
                        that.sceenStaff()
                    }
                })
            },
            // 收藏通知
            collectNotice(id, index) {
                let that = this
                let data = {}
                data['_method'] = 'put'
                data['type'] = 'collect'
                data['id'] = id
                editAppointNotice(data).then((res) => {
                    if (res.code == 200) {
                        that.$message.success(res.message)
                        that.tongList[index].is_collect = that.tongList[index].is_collect == 1 ? 0 : 1
                    }
                })
            },
            // 删除通知
            deleteNotice(id, index) {
                let that = this
                deleteAppointNotice(id).then((res) => {
                    if (res.code == 200) {
                        that.$message.success(res.message)
                        that.tongList.splice(index, 1)
                    }
                })
            },
            // 保存新增预约
            saveData() {
                let that = this
                let data = {}
                let temp = that.temp
                data['name'] = that.userName
                data['phone'] = that.userPhone
                data['sex'] = that.sex
                data['member_id'] = that.userId
                data['smid'] = that.orderInfo.id
                data['start_time'] = that.replaceSlashWithHyphen(that.time) + " " + that.startTime
                data['end_time'] = that.replaceSlashWithHyphen(that.time) + " " + that.endTime
                data['remark'] = that.newBeizhu
                data['appoint_said'] = that.zhgiId
                data['day_said'] = that.dangiId
                if (temp.name == that.userName && temp.phone == that.userPhone && temp.member_id == that.userId && temp
                    .sex == that.sex) {
                    // console.log(temp)
                    data['mid'] = temp.id
                }
                
                addAppointment(data).then((res) => {
                    if (res.code == 200) {
                        that.$message({
                            // message: res.message,
                            message: "予約しました",
                            type: 'success',
                            offset: 400
                        });
                        setTimeout(() => {
                           location.reload();
                        }, 2000)
                    } else {
                        that.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });   
                    }
                })

            },

            // 选择用户
            selectMember(e) {
                let that = this
                // console.log(e)
                that.userName = e.name
                that.userPhone = e.phone
                that.sex = e.sex
                that.userId = e.member_id
                that.mid = e.id
                that.temp = e
                that.searchShow = false
            },
            // 获取焦点时跳转搜索列表
            focuss() {
                let that = this
                that.searchshow = true
                that.showMemberList()
            },

            // 点击搜索框
            showMemberList() {
                let that = this
                let data = {}
                data['page'] = 1
                data['page_size'] = 500
                data['keywords'] = that.keyWord
                data['sort'] = 1
                getMemberList(data).then((res) => {
                    // console.log(res)
                    if (res.code == 200) {
                        that.searchShow = true
                        that.searchList = res.data.members
                    }
                })
            },


            //选择员工 
            selectStaff(index) {
                let that = this
                that.staff[index].checked = true
                that.staff.forEach((value, idx) => {
                    if (idx == index) {
                        value.checked = true
                    } else {
                        value.checked = false
                    }
                })
                that.staffId = that.staff[index].id
                that.zhiId = that.staff[index].id
                that.zhiName = that.staff[index].name
                // that.dangId = that.staff[index].id
                // that.dangName = that.staff[index].name
                that.staffColor = that.staff[index].color
                // console.log(that.firstday)
                that.getWeekList(that.firstday)
            },
            // 获取周的数据
            // getWeekList(date) {
            //     let that = this
            //     let data = {}
            //     data.date = date
            //     data.type = "week"
            //     data.id = that.staffId
            //     getAppointmentList(data).then((res) => {
            //         if (res.code == 200) {
            //             let array = res.data.data
            //             array.forEach((val, index) => {
            //                 val.times = that.times
            //             })
            //             that.weekData = array
            //         } else {
            //             this.$message.error(res.message)
            //         }
            //     })
            // },

            getWeekList(date) {
                // uni.showLoading({
                //     title:"読み込み中",
                //     mask:true
                // })
                let that = this
                let data = {}
                data.date = date
                data.type = "order_week"
                data.id = that.staffId
                getAppointmentList(data).then((res) => {
                    // uni.hideLoading()
                    if (res.code == 200) {
                        let array = res.data.data
                        array.forEach((val, index) => {
                            val.times = JSON.parse(JSON.stringify(that.times))
                        })
                        that.weekData = array
                    } else {
                        // uni.showToast({
                        //     title:res.message,
                        //     icon:"none"
                        // })
                    }
                })
            },



            // 获取今日日期
            getToday() {
                let that = this
                getConfig().then((res) => {
                    // console.log("config",res)
                    if (res.code == 200) {
                        let date = new Date(res.data.config.time_date)
                        let year = date.getFullYear()
                        let month = date.getMonth() + 1
                        let day = date.getDate()
                        month = (month > 9) ? month : ("0" + month);
                        day = (day < 10) ? ("0" + day) : day;
                        let today = year + "年" + month + "月" + day
                        that.timers = today
                        that.timers2 = today
                        that.time = year + "/" + month + "/" + day
                    }
                })

            },
            toSet() {
                let that = this
                let array = that.manager
                that.managers = array
            },
            // 颜色转换
            hexToRgba(hex) {
                if (!hex) hex = '#ededed';
                let rgb = 'rgb(' + parseInt('0x' + hex.slice(1, 3)) + ',' +
                    parseInt('0x' + hex.slice(3, 5)) + ',' +
                    parseInt('0x' + hex.slice(5, 7)) + ')'
                return rgb
            },
            // 获取每日预约列表
            getList(date) {
                let that = this
                let data = {}
                data.date = date || ""
                data.id = 0
                data.type = "day"
                getAppointmentList(data).then((res) => {
                    console.log("sdsdsdsds",res)
                    if (res.code == 200) {
                        let tem = []
                        let timeArr = JSON.parse(JSON.stringify(that.times))
                        res.data.admins.forEach((val, index) => {
                            console.log("valkkk、",val)
                            let obj = {}
                            obj.name = val.name
                            obj.times = JSON.parse(JSON.stringify(that.times))
                            obj.total = that.times.length
                            obj.date = date
                            obj.adminid = val.id
                            if (val.color) {
                                obj.color = that.hexToRgba(val.color)
                            } else {
                                obj.color = "rgb(23, 84, 145)"
                            }
                            obj.events = []
                            val.orders.forEach((value, idx) => {
                                let t = {}
                                t.start = value.start_time
                                t.end = value.end_time
                                t.duration = value.m / 60
                                t.name = value.name
                                t.event = value.cate_name
                                t.id = value.id
                                t.appoint_said = value.appoint_said
                                obj.events.push(t)
                            })
                            if(val.is_appoint == 1){
                                tem.push(obj)
                            }
                            
                        })
                        console.log("tem", tem)
                        that.manager = tem
                        that.toSet()
                    } else if(res.code == 403) {
                        that.showg = true
                    }
                })
            },
            // 新增预约
            addNewAppointment(e,item,source) {
                 let that = this
                console.log("addnew")
                console.log(e)
                console.log("item,",item)
                console.log(that.staff)
               
                that.staff.forEach((v,i) => {
                    if(v.id == item.adminid){
                        that.staffColor1 = v.color
                    }
                })
               if(source != 'week'){
                   that.newYueShow = true
                   that.startTime = e
                   this.addNew = true
                   this.dangName = item.name
                   this.dangId = item.adminid
               }else{
                   that.newYueShow = true
                   that.startTime = e
                   this.addNew = true
          
               }
                
                console.log("addnew",item)
            },
            chooseMenuItem(item, ite) {
                let that = this
                if (item == 0) {
                    that.orderInfo.menu.title = "お休み・勤務時間外"
                    that.orderInfo.menu.id = 0
                    that.orderInfo.menu.order_time = ""
                    that.orderInfo.menu.price = ""
                    that.orderInfo.id = 0
                }
                if (item.id) {
                    console.log("not", ite.id)
                    let ids = ite.id 
                    // that.selectInfo.id = ids
                    that.selectInfo.name = item.name + "·" + ite.title
                    that.selectInfo.time = ite.order_time
                    that.selectInfo.price = ite.price
                    that.orderInfo.menu.title = item.name + "·" + ite.title
                    that.orderInfo.menu.order_time = ite.order_time
                    that.orderInfo.menu.price = ite.price
                    that.orderInfo.id = ite.id
                    that.orderInfo.menu.id = ite.id
                    setTimeout(() => {
                        that.calcuteTime(ite.order_time)
                        that.sceenStaff()
                    }, 600)
                }
                that.huiYuanBack()
            },
            // 计算结束时间
            calcuteTime(minu) {
                let that = this
                let date = that.time
                let startTime = that.startTime
                let fullDate = date + " " + startTime
                let now = new Date(fullDate)
                let stamp = now.getTime()
                let endstamp = stamp + minu * 60 * 1000
                let timestamp4 = new Date(endstamp)
                let hour = timestamp4.getHours() < 10 ? '0' + timestamp4.getHours() : timestamp4.getHours()
                let minute = timestamp4.getMinutes() < 10 ? '0' + timestamp4.getMinutes() : timestamp4.getMinutes()
                that.endTime = hour + ":" + minute
            },
            deleteOrder() {
                let that = this
                deleteAppointment(that.orderInfo.id).then((res) => {
                    if (res.code == 200) {
                        that.yueDetailShow = false
                        that.getNoticeList()
                        that.$message.success(res.message)
                    } else {
                        that.$message.error(res.message)
                    }
                })
            },
            search() {
                this.searchShow = true;
            },
            changeTimes(e) {
                // console.log(e)  
                const Y = e.getFullYear()
                const M = e.getMonth() + 1 < 10 ? '0' + (e.getMonth() + 1) : e.getMonth() + 1
                const D = e.getDate() < 10 ? '0' + e.getDate() : e.getDate()
                this.time = `${Y}-${M}-${D}`
                this.getNoticeList()
            },
            formatDates(e) {
                // const date = new Date(ms)
                const Y = e.getFullYear()
                const M = e.getMonth() + 1 < 10 ? '0' + (e.getMonth() + 1) : e.getMonth() + 1
                const D = e.getDate() < 10 ? '0' + e.getDate() : e.getDate()
                return `${Y}-${M}-${D}`
            },
            editKeep() {
                let that = this
                // 終了時間が開始時間より前（または同じ）にならないよう検証
                if (that.startTime && that.endTime && that.endTime <= that.startTime) {
                    that.$message({
                        message: '終了時間は開始時間より後に設定してください',
                        type: 'error',
                        offset: 400
                    })
                    return
                }
                let data = {}
                data['_method'] = 'put'
                data['mid'] = that.orderDetail.mid
                data['name'] = that.orderDetail.name
                data['phone'] = that.orderDetail.phone
                data['sex'] = that.orderDetail.sex
                data['member_id'] = that.orderDetail.member_id
                data['smid'] = that.orderInfo.menu.id
                data['start_time'] = that.time + " " + that.startTime
                data['end_time'] = that.time + " " + that.endTime
                data['appoint_said'] = that.zhgiId
                data['day_said'] = that.dangiId
                data['remark'] = that.orderDetail.remark
                data['id'] = that.orderDetail.id
                editAppointment(data).then((res) => {
                    if (res.code == 200) {
                        // that.$message.success(res.message)
                        that.newYueShow = false;
                        that.yueEditShow = false;
                        // that.getNoticeList()
                        that.$message({
                        	message: res.message,
                        	type: 'success',
                             offset: 400
                        });
                        setTimeout(() => {
                            history.go(0)
                        },2000)
                        
                        
                    } else {
                        that.$message({
                        	message: res.message,
                        	type: 'error',
                             offset: 400
                        });
                    }
                })


                // console.log(data)
            },
            choose(i) {
                this.editTime = i.time;
                this.days = i.name;
                // this.timeInfo = i;
            },
            // 格式化日期
            formatDate(ms) {
                const date = new Date(ms)
                const Y = date.getFullYear()
                const M = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1
                const D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate()
                return `${Y}-${M}-${D}`
            },
            // 获取本周日期
            getWeekDate(dms) {
                let that = this
                let today = new Date(dms);
                //构建当前日期,格式：2022-08-22 00:00:00
                let year = today.getFullYear(); //本年 
                let month = today.getMonth() + 1; //本月
                let day = today.getDate(); //本日
                let newDate = new Date(year + "/" + month + "/" + day + " 00:00:00"); //年月日拼接
                let nowTime = newDate.getTime(); //当前的时间戳
                let weekDay = newDate.getDay(); //当前星期 0.1.2.3.4.5.6 【0 = 周日】
                let oneDayTime = 24 * 60 * 60 * 1000; //一天的总ms
                let sundayTime = (7 - weekDay) * oneDayTime + nowTime; //本周日的时间戳
                let lastSundayTime = nowTime - (weekDay + 0) * oneDayTime; //上周日的时间戳
                return lastSundayTime
            },
            // 员工列表
            getStaffData() {
                let that = this
                let data = {}
                data.id = 0
                data.startTime = " "
                data.endTIme = " "
                getStaffList(data).then((res) => {
                    if (res.code == 200) {
                        getStore().then((rest) => {
                            if(rest.code == 200){
                                console.log("store",rest)
                                let tep = []
                                res.data.admins.forEach((v, i) => {
                                    v.checked = false
                                    if(v.is_appoint == 1){
                                        tep.push(v)
                                    }
                                })
                                that.staffList = tep
                                console.log("1113333",that.staffList)
                                if(rest.data.shop_info.is_appoint == 1){
                                     that.staff = [...that.staff, ...that.staffList]
                                }else{
                                    that.staff = that.staffList
                                    that.selectStaff(0)
                                }
                            }
                        })    
                    }
                })
            },

            // 筛选员工
            sceenStaff() {
                let that = this
                let data = {}
                data.id = that.orderInfo.menu.id || 0
                data.startTime = that.time + " " + that.startTime
                data.endTIme = " "
                getStaffList(data).then((res) => {
                    console.log("datalist",res)
                    if (res.code == 200) {
                        that.staffList = res.data.admins
                        that.staffList.forEach((v, i) => {
                            v.checked = false
                            if (!v.color) {
                                v.color = "#d2d2d7"
                            }
                        })
                        that.dangiId = res.data.admins[0].id
                        that.staffColor1 = res.data.admins[0].color
                        that.dangName = res.data.admins[0].name
                    }
                })
            },
            
            dele(){
                let that = this
              deleteOrder(that.orderDetail.id).then((res) => {
                  if (res.code == 200) {
                      if (res.code == 200) {
                          that.$message({
                              message: res.message,
                              type: 'success',
                              offset: 400
                          });
                          setTimeout(() => {
                              history.go(0)
                          }, 2000)
                      } else {
                          that.$message({
                              message: res.message,
                              type: 'error',
                              offset: 400
                          });
                      }
                  }
              })  
            },

            // 选择指名担当
            chooseZhi() {
                let that = this
                if (that.orderInfo.menu.id != -1) {
                    let obj = {}
                    obj.name = "指名なし"
                    obj.checked = true
                    obj.color = "#d2d2d7"
                    obj.id = 0
                    let tep = []
                    tep.push(obj)
                    that.showStaffList = [...tep, ...that.staffList]
                    that.chooseStaff = 1
                    that.staffShow = true
                }
            },

            // 选择当日担当
            chooseDang() {
                let that = this
            
                if (that.orderInfo.menu.id != -1 && that.zhgiId == 0) {
                    that.showStaffList = that.staffList
                    that.chooseStaff = 2
                    that.staffShow1 = true
                }
            },

            // 选择员工
            selThis(item, index) {
                let that = this
                console.log(that.zhiId)
                that.showStaffList.forEach((v, i) => {
                    if (i == index) {
                        v.checked = true
                        console.log("que")
                        if (that.chooseStaff == 1) {
                            that.zhgiId = v.id
                            that.staffColor = v.color
                            that.zhiName = v.name
                            that.dangiId = v.id
                            that.staffColor1 = v.color
                            that.dangName = v.name
                            console.log("equal",v)
                        } else {
                            that.dangiId = v.id
                            that.staffColor1 = v.color
                            that.dangName = v.name
                        }
                    } else {
                        v.checked = false
                    }
                })
                console.log(that.zhiId)
                // that.staffShow = false
            },

            // 选择日期
            change(e) {
                let that = this
                that.firstday = e.firstDay
                that.selectday = e.today
                console.log("chajnge", e)
                let temp = e.today.split("-")
                that.timers = temp[0] + "年" + temp[1] + "月" + temp[2] + "日"
                if (that.tab == true) {
                    that.getList(e.today)
                } else {
                    that.getWeekList(e.firstDay)
                }
                that.dDate = e.today
                that.time = e.today
                that.getNoticeList()
            },
            // 切换日周
            changeTab() {
                let that = this
                if (that.tab == true) {
                    that.tab = false
                    that.getWeekList(that.firstday)
                } else {
                    that.tab = true
                    that.getList(that.selectday)
                }
                that.getNoticeList()
            },
            staffChanges(e) {
                // console.log(e)
                let that = this
                that.staff.forEach((value, index) => {
                    if (e == value.id) {
                        that.orderInfo.appoint_admin.color = value.color
                    }
                })
                that.getNoticeList()
            },
            staffChange(e) {
                // console.log(e)
                let that = this
                that.staffList.forEach((value, index) => {
                    if (e == value.id) {
                        that.orderInfo.day_admin.color = value.color
                        // console.log(value)
                    }
                })
            },
            choses() {
                this.editTime =
                    this.year + "/" + this.months[this.monthIndex] + "/" + this.days;
                this.riliShow = false;
            },
            chooseOver(i) {
                this.editTime = i.time;
                this.riliShow = false;
            },
            back() {
                let myDate = new Date();

                if (
                    this.monthIndex <= myDate.getMonth() &&
                    this.year <= myDate.getFullYear()
                ) {
                    return;
                }
                if (!this.monthIndex) {
                    this.monthIndex = 11;
                    this.year = this.year - 1;
                } else {
                    this.monthIndex = this.monthIndex - 1;
                }
            },
            backYear() {
                // this.monthIndex = 11;
                let myDate = new Date();

                // console.log(myDate.getFullYear);
                if (this.year <= myDate.getFullYear()) {
                    return;
                } else {
                    this.year = this.year - 1;
                }
            },
            addYear() {
                // this.monthIndex = 0;
                this.year = this.year + 1;
            },
            forward() {
                if (this.monthIndex == 11) {
                    this.monthIndex = 0;
                    this.year = this.year + 1;
                } else {
                    this.monthIndex = this.monthIndex + 1;
                }
            },
            move(i, cut) {
                if (cut == 1) {
                    this.tongList[i].show = true;
                } else if (cut == 2) {
                    this.tongList[i].show = false;
                }
            },
            resetData(){
                let that = this 
                that.userName = ''
                that.userPhone = ''
                that.sex = ''
                that.userId = ''
                that.orderInfo.id = -1
                that.startTime = ''
                that.endTime = ''
                that.dangName = ''
                that.zhiName = '指名なし'
                that.newBeizhu = ''
                that.dangId = ''
                that.zhiId = ''
                that.newYueShow = false
            },
            edit() {
                this.yueDetailShow = false;
                this.yueEditShow = true;
            },
            del(id, oid) {
                this.delShow = true;
                this.deleteid = id
                getAppointNoticeDetail(oid).then((res) => {
                    // console.log(res)
                    if (res.code == 200) {
                        this.orderInfo = res.data.order
                    }
                })
            },
            deletenoticeAppointment() {
                let that = this
                deleteAppointment(that.orderInfo.id).then((res) => {
                    if (res.code == 200) {
                        that.delShow = false
                        that.getNoticeList()
                        that.$message.success(res.message)
                    } else {
                        that.$message.error(res.message)
                    }
                })
            },
            detail(id, oid) {
                console.log("oid",oid)
                let that = this
                let data = {}
                data['id'] = oid
                data['type'] = "read"
                data['_method'] = "put"
                editAppointNotice(data).then((res) => {
                    if (res.code == 200) {
                        that.yueDetailShow = true;
                        that.getNoticeList()
                    } else {
                        that.$message.success(res.message)
                    }
                })
                getAppointNoticeDetail(oid).then((res) => {
                    // console.log(res)
                    if (res.code == 200) {
                        let temp = res.data.order.time.split('-')
                        let temp1 = res.data.order.day.split('年')
                        let temp2 = temp1[1].split("月")
                        let temp3 = temp2[1].split("日")
                        // console.log(temp1[0])
                        // console.log(temp2[0])
                        // console.log(temp3[0])
                        res.data.order["startTime"] = temp[0]
                        res.data.order["endTime"] = temp[1]
                        res.data.order["day"] = temp1[0] + "-" + temp2[0] + "-" + temp3[0]
                        that.orderInfo = res.data.order
                    }
                })



            },
            sends() {
                this.tongKeepShow = false;
                this.keepShow = true;
                setTimeout(() => {
                    this.keepShow = false;
                }, 1000);
            },

            huiYuan(i) {
                let id = 0
                let that = this

                that.tb = i;
                if (i == 1) {
                    that.yueEditShow = false;
                } else if (i == 2) {
                    that.newYueShow = false;
                }

                that.staff.forEach((value, index) => {
                    if (value.name == that.zhiName) {
                        id = value.id
                    }
                })
                if (id == 0) {
                    getCates().then((res) => {
                        if (res.code == 200) {
                            getMenuList().then((result) => {
                                if (res.code == 200) {
                                    res.data.cates.forEach((item, index) => {
                                        item.menus = []
                                        result.data.menus.forEach((ite, idx) => {
                                            if (ite.cid == item.id) {
                                                item.menus.push(ite)
                                            }
                                        })
                                    })
                                    that.menuList = res.data.cates
                                    this.huiYuanShow = true;
                                }
                            })
                        }
                    })
                } else {
                    // getFuDetail の戻り値は未使用で、失敗するとメニュー選択画面が開かない不具合の原因だったため除去。
                    // お休み分岐と同じ信頼できるフローでメニューを読み込み、モーダルを開く。
                    getCates().then((res) => {
                        if (res.code == 200) {
                            getMenuList().then((result) => {
                                if (result.code == 200) {
                                    res.data.cates.forEach((item, index) => {
                                        item.menus = []
                                        result.data.menus.forEach((ite, idx) => {
                                            if (ite.cid == item.id && ite.is_order == 1) {
                                                item.menus.push(ite)
                                            }
                                        })
                                    })
                                    that.menuList = res.data.cates
                                    this.huiYuanShow = true;
                                }
                            })
                        }
                    })
                }
                that.getNoticeList()
            },

            huiYuanBack() {
                if (this.tb == 1) {
                    this.yueEditShow = true;
                } else if (this.tb == 2) {
                    this.newYueShow = true;
                }
                this.huiYuanShow = false;
            },
            handleAvatarSuccess(res, file) {
                this.imgList.push(res.data.path);
                this.fileList.push({
                    url: res.data.path
                });
            },
            delPicture(i) {
                this.imgList.splice(i, 1);
                this.fileList.splice(i, 1);
            },
            sendMessage(){
                let that = this
                console.log(that.orderDetail)
                cahngeInof(that.orderDetail.id).then((res) => {
                    if(res.code == 200){
                        that.$message({
                        	message: res.message,
                        	type: 'success',
                             offset: 400
                        });
                    }else{
                        that.$message({
                        	message: res.message,
                        	type: 'success',
                             offset: 400
                        });
                    }
                })
            }
        },
    };
</script>

<style lang="scss" scoped>
    .kfl:hover{
        opacity: 0.6 !important;
    }
    ::v-deep .el-date-editor .el-input__inner{
        text-align: right !important;
    }
    .ho:hover{
        opacity: 0.6 !important;
        cursor: pointer;
    }
    #wk::-webkit-scrollbar {
        display: none !important;
    }
    ::v-deep .ed-bo .el-input__inner {
        background: transparent !important;
        padding: 0 !important;
        font-size: 16px !important;
    }
    ::v-deep .ed-boo .el-input__inner{
        width: 287px !important;
        height: 42px !important;
        background: #f5f5f7 !important;
        border-radius: 10px !important;
        border: 1px solid #aaaaaa !important;
    }
    .colorBlue {
        color: #007aff !important;
        cursor: pointer;
    }

    .colorRed {
        color: #D93025 !important;
        cursor: pointer;
    }

    .ale {
        width: 270px;
        background: #fff;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        border-radius: 14px;

        .alebtn {
            box-sizing: border-box;
            padding: 17px 0;
            text-align: center;
            font-family: Hiragino Sans, Hiragino Sans;
            font-weight: normal;
            font-size: 20px;
            border-top: 1px solid #86868b;
        }

        .aleTop {
            box-sizing: border-box;
            padding: 20px 26px;
            text-align: center;

            .aleTitle {
                font-family: Noto Sans Kannada, Noto Sans Kannada;
                font-weight: bold;
                font-size: 16px;
                color: #1D1D1F;
                margin-bottom: 14px;
            }

            .alecontent {
                font-family: Noto Sans Kannada, Noto Sans Kannada;
                font-weight: 400;
                font-size: 14px;
                color: #1D1D1F;
            }
        }
    }
    
    .selContent {
        width: 20px;
        height: 20px;
        border: 1px solid #707070;
        border-radius: 50%;
        cursor: pointer;
        box-sizing: border-box;
        padding: 3px;

        .selInner {
            width: 100%;
            height: 100%;
            background: #007aff;
            border-radius: 50%;
        }
    }

    .selDetail {
        display: flex;
        align-items: center;
    }

    .colorContent {
        width: 20px;
        height: 20px;
        border-radius: 4px;
        margin: 0 10px 0 30px;
    }

    .borderblue {
        border: 1px solid #007aff !important;
    }

    .selName {
        color: #707070;
    }

    .staffAll {
        width: 375px;
        height: 40vh;

        .staffItem {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 30px 16px;
            border-bottom: 1px solid #d2d2d7;
        }
    }

    .staffContent {
        width: 375px;
        height: 60vh;
        background: #fff;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        overflow-y: auto;

        .staffTitle {
            width: 100%;
            height: 40px;
            background: linear-gradient(180deg, #EDECED 0%, #D2D1D2 100%);
            border-radius: 5px 4px 0px 0px;
            position: relative;
            font-weight: normal;
            font-size: 16px;
            color: #1D1D1F;
            text-align: center;
            line-height: 40px;
            font-weight: bold;

            .closeStaff {
                width: 20px;
                height: 20px;
                position: absolute;
                left: 13px;
                top: 10px;
            }
        }
    }

    .mask {
        width: 100vw;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: rgba(0, 0, 0, .3);
        z-index: 1002 !important;
    }

    .zuo-box::-webkit-scrollbar {
        width: 0 !important;
    }

    .el-date-editor.el-input {
        width: 134px;
    }

    .search {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 0 16px;
        margin-bottom: 30px;

        .sea {
            position: relative;

            .big-m {
                position: absolute;
                left: 8px;
                top: 9px;

                img {
                    width: 16px;
                    height: 16px;
                }
            }

            input {
                padding: 0 15px 0 30px;
                border: 0;
                outline: none;
                width: 353px;
                height: 36px;
                background: #e9e9ea;
                border-radius: 10px 10px 10px 10px;
            }
        }

        .el-icon-close {
            font-size: 22px;
            font-weight: 700;
        }
    }

    ::v-deep .zuo-box {
        width: 820px;
        height: 700px;
        background: #ffffff;
        box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);
        border: 1px solid #d2d2d7;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        overflow: scroll;
        overflow-x: hidden;
        -ms-overflow-style: none;
        overflow: -moz-scrollbars-none;

        .member {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0 40px;
            margin-top: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #d2d2d7;

            .me-le {
                img {
                    width: 47px;
                    height: 49px;
                    margin-right: 22px;
                    vertical-align: middle;
                }

                font-size: 21px;
                color: #333335;
                font-weight: 700;
            }

            .me-ri {
                img {
                    width: 24px;
                    height: 24px;
                }
            }
        }

        .member_info {
            margin-top: 20px;

            .h {
                height: 40px;
            }

            .member_item {
                display: flex;
                align-items: center;

                margin-left: 80px;
                font-size: 16px;

                .grey_left {
                    text-align: center;
                    width: 140px;
                    color: #707070;
                    margin-right: 18px;
                }

                .remarks {
                    white-space: pre-wrap;
                    word-wrap: break-word;
                    word-break: break-all;

                    textarea {
                        border: 0;
                        outline: none;
                        resize: none;
                        width: 100%;
                        height: 100px;
                        border-bottom: 1px solid #d2d2d7;
                    }
                }

                .blue {
                    color: #2699fb;
                }

                .bor {
                    color: #1d1d1f;
                }
            }
        }

        .fenge {
            margin: 40px;
            margin-top: 50px;
            height: 1px;
            background: #d2d2d7;
        }

        .add-pictur {
            padding: 0 40px;
            width: 100%;

            .pic-tit {
                font-size: 16px;
                color: #707070;
                margin-bottom: 10px;
            }

            .des {
                font-size: 12px;
                color: #707070;
                margin-bottom: 10px;
            }

            .img-bos {
                .img-boxs {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    margin-bottom: 20px;
                }

                .img-item:nth-child(3n) {
                    margin-right: 0 !important;
                }

                .img-item {
                    margin-top: 10px;
                    position: relative;
                    margin-right: 10px;

                    .colse {
                        position: absolute;
                        right: 8px;
                        top: 8px;
                        width: 20px;
                        height: 20px;
                    }

                    .tu {
                        width: 237px;
                        height: 157px;
                        object-fit: cover;
                        border: 1px solid #aaaaaa;
                    }
                }

                .upload-demo {
                    height: 100px;

                    .el-upload {
                        width: 100%;
                        height: 100px;

                        .el-upload-dragger {
                            width: 100%;
                            height: 100px;
                            border: 0;
                        }

                        .tuo {
                            height: 100px;
                            width: 100%;
                            // line-height: 100px;
                            background: #f7faff;
                            border-radius: 10px 10px 10px 10px;
                            opacity: 1;
                            border: 1px solid #2699fb;

                            img {
                                margin-top: 25px;
                                width: 30px;
                                height: 24px;
                            }

                            .txt {
                                margin-top: 10px;
                                font-size: 16px;
                                color: #2699fb;
                            }
                        }
                    }
                }

                .add {
                    margin-top: 20px;
                    margin-bottom: 20px;
                    width: 140px;
                    height: 53px;
                    font-size: 14px;
                    color: #2699fb;
                    background: #ffffff;

                    .upload-demo {
                        width: 100%;
                        height: 40px;

                        .el-upload {
                            width: 100%;
                            height: 40px;
                        }
                    }

                    .el-upload .el-upload-dragger {
                        height: 42px;
                        line-height: 40px;
                    }

                    .zhui {
                        border-radius: 10px;
                        border: 1px solid #2699fb;

                        .fu {
                            font-size: 14px;
                            font-weight: 700;
                            color: #2699fb;
                        }
                    }
                }
            }
        }

        .bots-border {
            width: 100%;
            height: 1px;
            background: #d2d2d7;
        }

        .botss {
            width: 450px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            padding-bottom: 70px;
            padding-top: 30px;

            .left,
            .right {
                width: 200px;
                height: 50px;
                font-size: 20px;
                text-align: center;
                line-height: 50px;

                border-radius: 10px 10px 10px 10px;
            }

            .left {
                background: rgba(0, 0, 0, 0.07);
                color: #707070;
                margin-right: 50px;
            }

            .right {
                background: #1a73e8;
                color: #ffffff;
            }
        }
    }

    ::v-deep .warp {
        position: absolute;
        top: 0px;
        height: 366px;
        width: 343px;

        .le {
            position: absolute;
            color: #007aff;
            font-size: 24px;
            top: 59px;
            left: 0px;
            font-weight: 600;
            z-index: 999;
        }

        .ri {
            position: absolute;
            color: #007aff;
            font-size: 24px;
            top: 59px;
            left: 118px;
            font-weight: 600;
            z-index: 999;
        }

        .switch {
            top: 60px;
            right: 16px;
            // left: 11rem;
            font-weight: 600;
            position: absolute;
            display: flex;
            color: #007aff;
            font-size: 24px;
            z-index: 1000;

            .forward {
                margin-left: 36px;
            }
        }

        .riliSelect {
            position: absolute;
            top: -50px;
            z-index: 989;
        }

        .month {
            height: 360px;
        }

        .finss-box {
            border-top: 1px solid #eaeaea;
            position: absolute;
            padding: 0 20px;
            height: 57px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 1000;
            bottom: -50px;
            font-size: 22px;
            font-weight: 400;
            color: #1a73e8;
            width: 100%;

            .left {
                font-size: 22px !important;
                color: #1a73e8 !important;
            }
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
            color: #707070;
        }
    }

    .tit {
        color: #1d1d1f;
        font-size: 21px;
        font-weight: bold;
        margin-bottom: 20px;
    }

    .app-box {
        width: 820px;
        // height: 1373px;
        background: #ffffff;
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
        padding: 40px 40px 0 40px;

        .tab {
            width: 384px;
            height: 112px;
            background: #ffffff;
            border-radius: 10px 10px 10px 10px;
            opacity: 1;
            border: 1px solid #d2d2d7;
            margin-bottom: 30px;

            .item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0 17px;
                height: 56px;

                .lef {
                    font-size: 16px;
                    color: #1d1d1f;
                }

                .rigs {
                    width: 30px;
                    height: 30px;
                    line-height: 30px;
                    text-align: center;
                    background: #2699fb;
                    color: #fbfbfd;
                    font-size: 16px;
                    border-radius: 50%;
                }

                .rig {
                    width: 30px;
                    text-align: center;
                    font-size: 16px;
                    color: #1d1d1f;
                }
            }
        }

        .time {
            display: flex;
            align-items: center;
            margin-bottom: 42px;

            .lef-time {
                margin-right: 20px;
                color: #1d1d1f;
                font-size: 20px;
                font-weight: 600;
            }

            .rig-time {
                width: 349px;
                height: 32px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #eaeaea;
                border-radius: 8px;

                .t-le {
                    font-size: 16px;
                    flex: 1;
                    text-align: center;
                    color: #707070;
                }

                .lef {
                    width: 172px;
                    height: 32px;
                    line-height: 32px;
                    background: #ffffff;
                    box-shadow: 0px 3px 8px 1px rgba(0, 0, 0, 0.16);
                    border-radius: 8px;
                    color: #1a73e8;
                    border: 1px solid rgba(255, 255, 255, 0.04);
                }
            }
        }

        .tabs {
            margin-left: 40px;
            display: flex;
            margin-bottom: 20px;

            .item {
                height: 30px;
                background: #ffffff;
                font-size: 14px;
                color: #aaaaaa;
                border: 1px solid #aaaaaa;
                padding: 0 8px;
                border-radius: 8px;
                margin-right: 20px;
                line-height: 30px;
            }

            .active {
                color: #007aff;
                border: 1px solid #1a73e8;
                background: rgba(26, 115, 232, 0.1);
            }
        }
    }

    .fixed {
        position: fixed;
        top: 0%;
        left: 0%;
        width: 100vw;
        background: rgba(29, 29, 31, 0.2);

        .zhe {
            position: absolute;
            width: 100vw;
            height: 100vh;
            background: rgba(29, 29, 31, 0.2);
        }

        .tongzhi-box::-webkit-scrollbar {
            width: 0 !important;
        }

        .tongzhi-box {
            -ms-overflow-style: none;
        }

        .tongzhi-box {
            overflow: -moz-scrollbars-none;
        }
        
        .tongzhi-box {
            // padding: 0 10px;
            box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
            border: 1px solid #d2d2d7;
            background: #ffffff;
            width: 460px;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            height: 70vh;
            overflow-y: auto;
            overflow-x: hidden;

            .tit-flex {
                display: flex;
                justify-content: space-between;
                align-items: center;
                height: 60px;
                padding: 0 20px;
                border-bottom: 1px solid #d2d2d7;
                position: sticky;
                top: 0;
                z-index: 1009 !important;
                background: #fff;

                .lef {
                    font-size: 20px;
                    font-weight: 700;
                    color: #1d1d1f;

                    span {
                        margin-left: 10px;
                        color: #2699fb;
                        font-size: 20px;
                    }
                }

                .rig {
                    img {
                        width: 24px;
                        height: 24px;
                    }
                }
            }

            .item {
                display: flex;
                border-bottom: 1px solid #d2d2d7;
                margin: 0 10px;
                padding: 20px 0;

                &:hover {
                    box-shadow: 0px 3px 6px 1px rgba(0, 0, 0, 0.16);
                }

                .left {
                    margin-left: 10px;
                    flex-shrink: 0;
                    width: 16px;
                    height: 16px;

                    .dian {
                        width: 100%;
                        height: 100%;
                        background: #1a73e8;
                        border-radius: 50%;
                    }
                }

                .right {
                    margin-left: 18px;
                    margin-right: 21px;
                    width: 100%;

                    .tit-b {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;

                        .tt {
                            color: #1d1d1f;
                            font-size: 14px;
                            font-weight: 600;
                        }

                        .tm {
                            color: #707070;
                            font-size: 14px;
                        }
                    }

                    .time-boxs {
                        display: flex;
                        align-items: center;
                        font-size: 14px;
                        color: #1d1d1f;
                        margin-top: 14px;
                        margin-bottom: 14px;

                        .t-le {
                            margin-right: 20px;

                            img {
                                width: 14px;
                                height: 14px;
                                margin-right: 10px;
                                vertical-align: middle;
                            }
                        }
                    }

                    .des {
                        color: #1d1d1f;
                        font-size: 14px;
                        margin-bottom: 14px;
                        display: -webkit-box;
                        -webkit-box-orient: vertical;
                        -webkit-line-clamp: 2;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }

                    .bots {
                        display: flex;
                        justify-content: space-between;
                        width: 100%;

                        .zhi {
                            margin-bottom: 14px;
                            color: #1d1d1f;
                            font-size: 14px;
                            display: flex;
                            align-items: center;

                            .hui {
                                display: inline-block;
                                width: 14px;
                                height: 14px;
                                background: #aaaaaa;
                                border-radius: 4px 4px 4px 4px;
                                margin-left: 10px;
                                margin-right: 5px;
                            }

                            .lan {
                                display: inline-block;
                                width: 14px;
                                height: 14px;
                                background: #175491;
                                border-radius: 4px 4px 4px 4px;
                                margin-left: 10px;
                                margin-right: 5px;
                            }
                        }

                        .b-r {
                            display: flex;
                            align-items: center;

                            .del {
                                position: relative;

                                img {
                                    width: 40px;
                                    height: 40px;
                                }

                                .txt {
                                    position: absolute;
                                    bottom: -20px;
                                    left: 4px;
                                    width: 32px;
                                    height: 16px;
                                    text-align: center;
                                    line-height: 16px;
                                    font-size: 10px;
                                    color: #fff;
                                    background: rgba(0, 0, 0, 0.5);
                                    border-radius: 3px 3px 3px 3px;
                                }
                            }

                            .collect {
                                margin-left: 10px;

                                img {
                                    width: 30px;
                                    height: 30px;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    .flex {
        display: flex;
        align-items: center;
    }

    .tong-detail-box::-webkit-scrollbar {
        width: 0 !important;
    }

    .tong-detail-box {
        -ms-overflow-style: none;
    }

    .tong-detail-box {
        overflow: -moz-scrollbars-none;
    }

    // .edit-box::-webkit-scrollbar {
    //   width: 0 !important;
    // }
    // .edit-box {
    //   -ms-overflow-style: none;
    // }
    // .edit-box {
    //   overflow: -moz-scrollbars-none;
    // }
    .sea-box {
        padding-top: 15px;
        -ms-overflow-style: none;
        overflow: -moz-scrollbars-none;
    }

    .sea-box::-webkit-scrollbar {
        width: 0px;
    }

    .form::-webkit-scrollbar-thumb {
        width: 8px !important;
        height: 65px;
        background: rgb(196, 196, 196);
        border-radius: 7px 7px 7px 7px;
    }

    .form {
        -ms-overflow-style: none;
    }

    .form {
        overflow: -moz-scrollbars-none;
    }

    .search-box {
        width: 680px;
        background: #ffffff;
        box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);
        opacity: 1;
        border: 1px solid #d2d2d7;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);

        .title {
            background: linear-gradient(180deg, #edeced 0%, #d2d1d2 100%);
            border-bottom: 1px solid #d2d2d7;
            border-radius: 5px 4px 0px 0px;
            padding-left: 13px;

            .lef {
                font-size: 20px;
                color: #1d1d1f;
                font-weight: 600;
                height: 40px;
                display: flex;
                align-items: center;

                img {
                    width: 20px;
                    height: 20px;
                    margin-right: 251px;
                }
            }
        }

        .sea-box {
            height: 298px;
            overflow: scroll;
            overflow-x: hidden;

            .sum {
                color: #1d1d1f;
                font-size: 14px;
            }

            .list-box {
                margin-top: 22px;

                .top {
                    display: flex;
                    align-items: center;
                    height: 38px;
                    border-bottom: 1px solid #d2d2d7;

                    .it {
                        font-size: 14px;
                        color: #1d1d1f;
                    }
                }

                .items {
                    display: flex;
                    align-items: center;
                    height: 64px;
                    font-size: 12px;
                    color: #1d1d1f;
                    padding-left: 20px;
                    border-bottom: 1px solid #d2d2d7;

                    .img {
                        width: 30px;
                        height: 30px;
                        margin-right: 20px;

                        img {
                            width: 30px;
                            height: 30px;
                            border-radius: 50%;
                        }
                    }

                    .name {
                        width: 100px;
                        overflow: hidden;
                        white-space: nowrap;
                        text-overflow: ellipsis;
                    }

                    .txt {
                        width: 100px;
                        margin-left: 2px;
                        overflow: hidden;
                        white-space: nowrap;
                        text-overflow: ellipsis;
                    }

                    .tel {
                        margin-left: 12px;
                        width: 100px;
                    }

                    .no {
                        margin-left: 21px;
                        width: 60px;
                    }

                    .id {
                        width: 120px;
                        margin-left: 20px;
                        margin-right: 6px;
                    }

                    .slet {
                        width: 48px;
                        height: 24px;
                        line-height: 24px;
                        text-align: center;
                        background: #1a73e8;
                        font-size: 12px;
                        color: #ffffff;
                        border-radius: 15px 15px 15px 15px;
                    }
                }
            }
        }
    }
    .ed-boo {
        width: 100%;
        background: #ffffff;
        border-radius: 10px 10px 10px 10px;
        opacity: 1;
        border: 1px solid #d2d2d7;
        padding: 20px 0;
        margin-bottom: 30px;
        
        .search {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0 16px;
            margin-bottom: 30px;
        
            .sea {
                position: relative;
        
                .big-m {
                    position: absolute;
                    left: 8px;
                    top: 9px;
        
                    img {
                        width: 16px;
                        height: 16px;
                    }
                }
        
                input {
                    padding: 0 15px 0 30px;
                    border: 0;
                    outline: none;
                    width: 353px;
                    height: 36px;
                    background: #e9e9ea;
                    border-radius: 10px 10px 10px 10px;
                }
            }
        
            .el-icon-close {
                font-size: 22px;
                font-weight: 700;
            }
        }
        
        .d-it {
            margin: 0 16px;
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        
            .name {
                width: 80px;
                font-size: 14px;
                color: #707070;
                // margin-bottom: 16px;
                margin-right: 10px;
            }
        
            .text {
                font-size: 14px;
                color: #707070;
        
                .el-input__inner {
                    width: 287px !important;
                    height: 42px !important;
                    background: #f5f5f7 !important;
                    border-radius: 10px !important;
                    border: 1px solid #aaaaaa !important;
                }
                
                
                input {
                    width: 268px;
                    border: 0;
                    border-bottom: 1px solid #d2d2d7;
                    outline: none;
                    padding-bottom: 4px;
                }
            }
        
            .new-ipt {
                input {
                    width: 280px;
                }
            }
        }
        
        .le {
            width: 358px;
            margin-left: 16px;
            margin-right: 21px;
        
            .des {
                color: #1d1d1f;
                font-size: 14px;
                font-weight: 600;
            }
        
            .p-time {
                font-size: 14px;
                color: #1d1d1f;
                // margin: 0 16px;
                margin-top: 14px;
        
                span {
                    margin-left: 20px;
                    color: rgba(217, 48, 37, 0.65);
                }
            }
        }
        .spbet:hover{
            opacity: 0.6 !important;
        }
        .spbet {
            height: 53px;
            padding-right: 16px;
            margin-left: 14px;
            justify-content: space-between;
            border-bottom: 1px solid #d2d2d7;
        
            .left {
                font-size: 14px;
                color: #1d1d1f;
            }
        
            .t-tims {
                padding: 10px;
                background: #f5f5f7;
                border-radius: 10px 10px 10px 10px;
            }
        
            .selet {
        
                // width: 40%;
                //
                span {
                    display: inline-block;
                    width: 16px;
                    height: 16px;
                    margin-right: 10px;
                    border-radius: 4px;
                    vertical-align: middle;
                }
        
                .el-input__inner {
                    border: 0;
                    padding: 0;
                    width: 120px;
                    height: 20px;
                }
        
                .el-select .el-input .el-select__caret {
                    font-size: 20px;
                    transform: rotateZ(-90deg);
                    color: #6e6e73;
                }
        
                .el-icon-arrow-up:before {
                    content: "\e6df" !important;
                }
            }
        
            .right {
                .el-date-editor.el-input {
                    min-width: 60px;
                    width: 60px;
                    max-width: 107px;
        
                    .el-input__inner {
                        border: 0;
                        padding: 10px;
                        background: #f5f5f7;
                        border-radius: 10px 10px 10px 10px;
                    }
        
                    .el-input__prefix,
                    .el-input__icon {
                        display: none;
                    }
                }
            }
        }
        
        .txt {
            font-size: 14px;
            color: #1d1d1f;
        }
        
        textarea {
            margin-top: 10px;
            border: 0;
            resize: none;
            outline: none;
            width: 100%;
            height: 64px;
        }
        
    }
    ::v-deep .edit-box {
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
        border: 1px solid #d2d2d7;
        background: #ffffff;
        width: 460px;
        height: 75vh;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-height: 800px;
        overflow: scroll;
        overflow-x: hidden;
        padding-bottom: 50px;

        .edit-tit {
            background: #f9f9f9;
            border-bottom: 1px solid #d2d2d7;

            .lef {
                font-size: 20px;
                color: #1d1d1f;
                font-weight: 600;
                height: 60px;
                display: flex;
                align-items: center;
                margin-left: 172px;

                img {
                    margin-left: 164px;
                    width: 24px;
                    height: 24px;
                }
            }
        }

        .edit-detail-box {
            width: 100%;
            padding: 0 20px;

            .time {
                margin-top: 30px;
                margin-bottom: 10px;
                width: 100%;
                display: flex;
                justify-content: flex-end;
                color: #707070;
                font-size: 14px;
            }

            .ed-bo {
                width: 100%;
                background: #ffffff;
                border-radius: 10px 10px 10px 10px;
                opacity: 1;
                border: 1px solid #d2d2d7;
                padding: 10px 0;
                margin-bottom: 30px;

                .search {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin: 0 16px;
                    margin-bottom: 30px;

                    .sea {
                        position: relative;

                        .big-m {
                            position: absolute;
                            left: 8px;
                            top: 9px;

                            img {
                                width: 16px;
                                height: 16px;
                            }
                        }

                        input {
                            padding: 0 15px 0 30px;
                            border: 0;
                            outline: none;
                            width: 353px;
                            height: 36px;
                            background: #e9e9ea;
                            border-radius: 10px 10px 10px 10px;
                        }
                    }

                    .el-icon-close {
                        font-size: 22px;
                        font-weight: 700;
                    }
                }

                .d-it {
                    margin: 0 16px;
                    display: flex;
                    align-items: center;
                    margin-bottom: 10px;

                    .name {
                        width: 80px;
                        font-size: 14px;
                        color: #707070;
                        // margin-bottom: 16px;
                        margin-right: 10px;
                    }

                    .text {
                        font-size: 14px;
                        color: #707070;

                        .el-input__inner {
                            width: 287px;
                            height: 42px;
                            background: #f5f5f7;
                            border-radius: 10px 10px 10px 10px;
                            opacity: 1;
                            padding-bottom: 0;
                            border: 1px solid #aaaaaa;
                        }

                        input {
                            width: 268px;
                            border: 0;
                            border-bottom: 1px solid #d2d2d7;
                            outline: none;
                            padding-bottom: 4px;
                        }
                    }

                    .new-ipt {
                        input {
                            width: 280px;
                        }
                    }
                }

                .le {
                    width: 358px;
                    margin-left: 16px;
                    margin-right: 21px;

                    .des {
                        color: #1d1d1f;
                        font-size: 14px;
                        font-weight: 600;
                    }

                    .p-time {
                        font-size: 14px;
                        color: #1d1d1f;
                        // margin: 0 16px;
                        margin-top: 14px;

                        span {
                            margin-left: 20px;
                            color: rgba(217, 48, 37, 0.65);
                        }
                    }
                }

                .spbet {
                    height: 53px;
                    padding-right: 16px;
                    margin-left: 14px;
                    justify-content: space-between;
                    border-bottom: 1px solid #d2d2d7;

                    .left {
                        font-size: 14px;
                        color: #1d1d1f;
                    }

                    .t-tims {
                        padding: 10px;
                        background: #f5f5f7;
                        border-radius: 10px 10px 10px 10px;
                    }

                    .selet {

                        // width: 40%;
                        //
                        span {
                            display: inline-block;
                            width: 16px;
                            height: 16px;
                            margin-right: 10px;
                            border-radius: 4px;
                            vertical-align: middle;
                        }

                        .el-input__inner {
                            border: 0;
                            padding: 0;
                            width: 120px;
                            height: 20px;
                        }

                        .el-select .el-input .el-select__caret {
                            font-size: 20px;
                            transform: rotateZ(-90deg);
                            color: #6e6e73;
                        }

                        .el-icon-arrow-up:before {
                            content: "\e6df" !important;
                        }
                    }

                    .right {
                        .el-date-editor.el-input {
                            min-width: 60px;
                            width: 60px;
                            max-width: 107px;

                            .el-input__inner {
                                border: 0;
                                padding: 10px;
                                background: #f5f5f7;
                                border-radius: 10px 10px 10px 10px;
                            }

                            .el-input__prefix,
                            .el-input__icon {
                                display: none;
                            }
                        }
                    }
                }

                .txt {
                    font-size: 14px;
                    color: #1d1d1f;
                }

                textarea {
                    margin-top: 10px;
                    border: 0;
                    resize: none;
                    outline: none;
                    width: 100%;
                    height: 64px;
                }
            }
        }

        .fen {
            width: 100%;
            height: 1px;
            background: #d2d2d7;
        }

        .btn-sf {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 300px;
            margin: 0 auto;
            margin-top: 30px;

            .bts {
                width: 120px;
                height: 40px;
                text-align: center;
                line-height: 40px;
                font-size: 16px;
                background: #1a73e8;
                border-radius: 26px 26px 26px 26px;
            }

            .le {
                background: rgba(29, 29, 31, 0.1);
                color: #707070;
                border: 1px solid #707070;
            }

            .ri {
                color: #ffffff;
                position: relative;
                right: 16px;
            }
        }
    }

    .huiYuanSelet {
        width: 680px;
        background: #ffffff;
        box-shadow: 0px 0px 10px 1px rgba(0, 0, 0, 0.16);
        opacity: 1;
        border: 1px solid #d2d2d7;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);

        .title {
            background: linear-gradient(180deg, #edeced 0%, #d2d1d2 100%);
            border-bottom: 1px solid #d2d2d7;
            border-radius: 5px 4px 0px 0px;
            padding-left: 13px;

            .lef {
                font-size: 20px;
                color: #1d1d1f;
                font-weight: 600;
                height: 40px;
                display: flex;
                align-items: center;

                img {
                    width: 20px;
                    height: 20px;
                    margin-right: 251px;
                }
            }
        }

        .form {
            height: 600px;
            overflow: scroll;
            overflow-x: hidden;

            .it-flex {
                padding-left: 20px;
                padding-right: 25px;
                width: 665px;
                height: 80px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-right: 1px solid #e8e8e8;
                border-bottom: 1px solid #e8e8e8;

                .left {
                    color: rgba(217, 48, 37, 0.65);
                    font-size: 16px;
                }

                .right {
                    width: 48px;
                    height: 24px;
                    line-height: 24px;
                    text-align: center;
                    font-size: 12px;
                    color: #ffffff;
                    background: #1a73e8;
                    border-radius: 15px 15px 15px 15px;
                }
            }

            .form-box {
                .items {
                    .items-menu-title {
                        padding-left: 20px;
                        height: 40px;
                        font-size: 14px;
                        line-height: 40px;
                        color: #1d1d1f;
                        font-weight: 600;
                        background: rgba(29, 29, 31, 0.1);
                    }

                    .item {
                        height: 100px;
                        border-bottom: 1px solid #d2d2d7;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 10px 0;
                        padding-left: 10px;
                        padding-right: 25px;

                        .ll {
                            display: flex;
                            align-items: center;
                        }

                        .le {
                            flex-shrink: 0;

                            img {
                                width: 80px;
                                height: 80px;
                                border-radius: 6px 6px 6px 6px;
                            }
                        }

                        .rig {
                            margin-left: 10px;
                            width: 462px;

                            .tit {
                                font-size: 14px;
                                color: #1d1d1f;
                                font-weight: 600;
                                margin-bottom: 10px;
                            }

                            .time {
                                color: #707070;
                                font-size: 16px;

                                .price {
                                    margin-left: 24px;
                                    font-size: 16px;
                                    color: rgba(217, 48, 37, 0.65);
                                }
                            }
                        }

                        .sel {
                            width: 48px;
                            height: 24px;
                            line-height: 24px;
                            text-align: center;
                            font-size: 12px;
                            color: #ffffff;
                            background: #1a73e8;
                            border-radius: 15px 15px 15px 15px;
                        }
                    }
                }
            }
        }
    }

    .del-box {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 460px;
        // height: 541px;
        background: #ffffff;

        .tit {
            position: relative;
            width: 100%;
            height: 48px;
            line-height: 48px;
            background: #fbfbfd;
            color: #1d1d1f;
            text-align: center;
            font-size: 16px;
            border-bottom: 1px solid #d2d2d7;

            img {
                position: absolute;
                left: 0;
                top: 0;
                width: 30px;
                height: 30px;
            }
        }

        .box {
            padding-left: 79px;
            padding-right: 23px;
            padding-top: 30px;
            padding-bottom: 20px;

            .item {
                display: flex;
                align-items: center;
                margin-bottom: 16px;

                .let {
                    width: 80px;
                    color: #707070;
                    margin-right: 8px;
                }

                .rig {
                    font-size: 14px;
                    color: #707070;
                }
            }

            .des {
                margin-top: 4px;
                color: #1d1d1f;
                font-size: 14px;
                margin-bottom: 15px;

                .tim {
                    margin-top: 14px;
                    margin-right: 34px;
                    margin-bottom: 14px;

                    span {
                        color: rgba(217, 48, 37, 0.65);
                    }
                }
            }

            .items {
                display: flex;
                align-items: center;
                margin-bottom: 16px;

                .left {
                    font-size: 14px;
                    color: #1d1d1f;
                    margin-right: 26px;
                }

                .color {
                    width: 16px;
                    height: 16px;
                    border-radius: 4px;
                    margin-right: 10px;
                }

                .name {
                    font-size: 14px;
                    color: #6e6e73;
                }
            }

            .ti {
                margin-top: 35px;
                color: #1d1d1f;
                font-size: 20px;
                margin-left: -7px;
            }
        }

        .del-btn {
            margin-top: 20px;
            border-top: 1px solid #d2d2d7;
            padding-top: 30px;
            height: 101px;

            .btn {
                margin: 0 auto;
                width: 120px;
                height: 40px;
                line-height: 40px;
                background: #d93025;
                color: #ffffff;
                font-size: 16px;
                text-align: center;
                border-radius: 26px 26px 26px 26px;
            }

            .btns {
                margin: 0 auto;
                width: 343px;
                height: 46px;
                line-height: 46px;
                text-align: center;
                background: #1a73e8;
                color: #ffffff;
                font-size: 16px;
                border-radius: 26px 26px 26px 26px;

                img {
                    margin-left: 10px;
                    width: 16px;
                    height: 16px;
                    // background-color: #fff;
                    vertical-align: middle;
                }
            }
        }
    }

    .tong-detail-box {
        box-shadow: 0px 3px 10px 1px rgba(0, 0, 0, 0.16);
        border: 1px solid #d2d2d7;
        background: #ffffff;
        width: 460px;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        height: 75vh;
        overflow-y: auto;
        overflow-x: hidden;
        padding-bottom: 50px;

        .detail-tit {
            background: #f9f9f9;
            border-bottom: 1px solid #d2d2d7;
            position: sticky;
            top: 0;
            z-index: 1009 !important;

            .lef {
                font-size: 20px;
                color: #1d1d1f;
                font-weight: 600;
                height: 60px;
                display: flex;
                align-items: center;
                margin-left: 172px;

                img {
                    margin-left: 164px;
                    width: 24px;
                    height: 24px;
                }
            }
        }

        .detail-box {
            width: 100%;
            padding: 0 20px;

            .time {
                margin-top: 30px;
                margin-bottom: 10px;
                width: 100%;
                display: flex;
                justify-content: flex-end;
                color: #707070;
                font-size: 14px;
            }

            .d-b {
                width: 100%;
                background: #ffffff;
                border-radius: 10px 10px 10px 10px;
                opacity: 1;
                border: 1px solid #d2d2d7;
                padding: 20px 0;
                margin-bottom: 30px;

                .d-it {
                    margin: 0 16px;
                    display: flex;

                    .name {
                        width: 80px;
                        font-size: 14px;
                        color: #707070;
                        margin-bottom: 16px;
                        margin-right: 10px;
                    }

                    .text {
                        font-size: 14px;
                        color: #707070;
                    }
                }

                .des {
                    color: #1d1d1f;
                    font-size: 14px;
                    font-weight: 600;
                    margin: 0 16px;
                }

                .p-time {
                    font-size: 14px;
                    color: #1d1d1f;
                    margin: 0 16px;
                    margin-top: 14px;

                    span {
                        margin-left: 20px;
                        color: rgba(217, 48, 37, 0.65);
                    }
                }

                .it-for {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-left: 16px;
                    padding-right: 16px;
                    border-bottom: 1px solid #d2d2d7;
                    height: 54px;

                    .it-l,
                    .it-r {
                        font-size: 14px;
                        color: #1d1d1f;

                        span {
                            display: inline-block;
                            width: 16px;
                            height: 16px;
                            border-radius: 4px;
                            margin-right: 10px;
                            vertical-align: middle;
                        }
                    }
                }

                .d-tt {
                    font-size: 14px;
                    color: #1d1d1f;
                }

                .d-text {
                    margin-top: 10px;
                    color: #aaaaaa;
                    font-size: 14px;
                }
            }
        }

        .changhe {
            width: 415px;
            height: 42px;
            line-height: 42px;
            padding: 0 14px;
            background: #f5f5f7;
            border-radius: 10px 10px 10px 10px;
            margin: 0 auto;
            color: #1d1d1f;
            font-size: 14px;
            margin-bottom: 30px;

            span {
                color: #2699fb;
                margin-left: 10px;
            }
        }

        .fenge {
            width: 100%;
            height: 1px;
            background: #d2d2d7;
            margin-bottom: 30px;
        }

        .flex-btns {
            width: 290px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;

            .l-btn,
            .r-btn {
                width: 120px;
                height: 40px;
                line-height: 40px;
                font-size: 16px;
                text-align: center;
                border-radius: 26px 26px 26px 26px;
            }

            .l-btn {
                color: #ffffff;
                background: rgba(217, 48, 37, 1);
            }
            
            .l-btn:hover {
                background: rgba(217, 48, 37, 0.6) !important;
            }

            .r-btn {
                color: #1a73e8;
                background: #ffffff;
                border: 1px solid #1a73e8;
            }
        }
    }

    .pres::-webkit-scrollbar {
        width: 0;
        height: 0;
    }

    .app-box::-webkit-scrollbar {
        width: 0;
        height: 0;
    }
</style>