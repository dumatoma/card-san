<template>
    <view style="width: 100vw;overflow:hidden">
        <!-- 自定义顶部导航 -->
        <u-navbar :title="tti" :placeholder="true" :safeAreaInsetTop="true" :border="true">
            <view class="u-nav-slot" slot="left">
                <image src="../../../../static/icons/tclose.png" style="width: 50upx;height: 50upx;" @click="goBakc" mode=""></image>
            </view>
            <view class="u-nav-slot" slot="right">
                <image src="../../../../static/icons/preview.svg" style="width: 50upx;height: 50upx;"
                    @click="showPopup = true" mode=""></image>
            </view>
        </u-navbar>
        <view class="balnk"></view>
        <view class="title" v-if="type != 3">
            デザイン・詳細設定
        </view>
        <view class="content" v-if="type != 3">
            <!-- 带笑脸的印章 -->
            <view class="cardY" :style="`background:${background}`" v-if="type ==1">
                <view class="cardInner">
                    <view class="seal" v-for="(item,index) in list">
                        <text v-text="index + 1"></text>
                        <view class="sealed" v-if="item.sealed">
                            <image v-if="method == 2" :src="selfLogo" mode=""></image>
                            <view v-if="method == 1">
                                <view :style="`backgroundImage: url(${svgData11})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 0"></view>
                                <view :style="`backgroundImage: url(${svgData12})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 1"></view>
                                <view :style="`backgroundImage: url(${svgData13})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 2"></view>
                                <view :style="`backgroundImage: url(${svgData14})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 3"></view>
                                <view :style="`backgroundImage: url(${svgData15})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 4"></view>
                                <view :style="`backgroundImage: url(${svgData16})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 5"></view>
                                <view :style="`backgroundImage: url(${svgData17})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 6"></view>
                                <view :style="`backgroundImage: url(${svgData18})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 7"></view>
                            </view>
                            
                            <view class="sealTime" v-text="item.time"></view>
                        </view>
                    </view>
                </view>
            </view>
            <!-- 不带笑脸的印章 -->
            <view class="cardY" :style="`background:${background}`" v-if="type == 2">
                <view class="cardTable">
                    <view class="cardTitle">
                        <view class="tableItem">
                            NO
                        </view>
                        <view class="tableItem" style="padding: 0 14upx;">
                            日付
                        </view>
                        <view class="tableItem">
                            <view style="margin: 0 auto;width: 48upx;">
                                <u-icon name="checkmark-circle" size="20"></u-icon>
                            </view>
                        </view>
                        <view class="tableItem">
                            <view style="margin: 0 auto;width: 48upx;">
                                <u-icon name="account-fill" size="20"></u-icon>
                            </view>
                        </view>
                        <view class="tableItem">
                            <image style="visibility: hidden;" class="set" src="../../../../static/icons/cright.png"
                                mode=""></image>
                        </view>
                    </view>
                    <view class="tableContent">
                        <view class="contentItem" v-for="(item,index) in list">
                            <view class="tableItem" v-text="index+1"></view>
                            <view class="tableItem">
                                YY/MM/DD(月)
                            </view>
                            <view class="tableItem">
                                00:00
                            </view>
                            <view class="tableItem">
                                担当者名
                            </view>
                            <view class="tableItem">
                                 <image v-if="method == 2" :src="selfLogo" style="width: 40upx;height: 40upx;" mode=""></image>
                                 <view v-else>
                                     <view :style="`backgroundImage: url(${svgData11})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 0"></view>
                                     <view :style="`backgroundImage: url(${svgData12})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 1"></view>
                                     <view :style="`backgroundImage: url(${svgData13})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 2"></view>
                                     <view :style="`backgroundImage: url(${svgData14})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 3"></view>
                                     <view :style="`backgroundImage: url(${svgData15})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 4"></view>
                                     <view :style="`backgroundImage: url(${svgData16})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 5"></view>
                                     <view :style="`backgroundImage: url(${svgData17})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 6"></view>
                                     <view :style="`backgroundImage: url(${svgData18})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 7"></view>
                                 </view>
                            </view>
                        </view>
                    </view>
                </view>
            </view>
            <view class="insertContent">
                <view class="insertTitle">
                    <text style="color: #D93025;">※</text>スタンプカード名
                </view>
                <u--input v-model="name" fontSize="32rpx"></u--input>
            </view>
            <view class="chooseContent">
                <view class="chooseLeft">
                    カード背景色
                </view>
                <view class="chooseRight">
                    <view @click="toChoose('background')" class="colorSqu" :style="`background:${background}`"></view>
                </view>
            </view>
            <view class="chooseContent">
                <view class="chooseLeft">
                    スタンプ数
                </view>
                <view class="chooseRight">
                    <view class="picker" @click="show2 = true">
                        <text v-text="number"></text>
                        <u-icon name="arrow-down"></u-icon>
                    </view>
                    <view>
                        個
                    </view>
                </view>
            </view>
            <view class="tips mgt_20">
                <text>※</text>３〜３０の個数を設定してください。
            </view>
        </view>
        <!-- 优惠券设定 -->
        <view class="insertContent" style="margin: 0 0 60upx;padding: 0 18upx;" v-if="type == 3">
            <view class="insertTitle">
                <text style="color: #D93025;">※</text>ポイントカード名
            </view>
            <u-input v-model="name"></u-input>
        </view>
        <view class="title" v-if="type == 3">
            ポイント付与設定
        </view>
        <view class="content" v-if="type == 3">
            
            <view class="newItem">
                <view class="newLeft">
                    <view class="selectContents" @click="changeUse(true)">
                        <view class="unselect" v-if="!use"></view>
                        <view class="selected" v-if="use">
                            <view class="fill"></view>
                        </view>
                    </view>
                </view>
                <view class="newRight">
                    <view class="rightTitle" :style="use == true?'color:#1d1d1d' : ''">
                        設定する
                    </view>
                    <view class="rightContent">
                        <view class="inpItem">
                            <view class="inps">
                                <input v-model="amount" type="digit">
                            </view>
                            <view class="inpName">
                                円 利用ごとに
                            </view>
                        </view>
                        <view class="inpItem">
                            <view class="inps">
                                <input v-model="point" type="digit">
                            </view>
                            <view class="inpName">
                                ポイントを付与
                            </view>
                        </view>
                    </view>
                </view>
            </view>

            <view class="newItem">
                <view class="newLeft">
                    <view class="selectContents" @click="changeUse(false)">
                        <view class="unselect" v-if="use"></view>
                        <view class="selected" v-if="!use">
                            <view class="fill"></view>
                        </view>
                    </view>
                </view>
                <view class="newRight">
                    <view class="rightTitle" :style="use == false?'color:#1d1d1d' : ''">
                        設定・表示しない
                    </view>
                    <view class="rightContent">
                        <view class="btip">
                            他社のポイント管理システムをご利用の場合も、こちらを選択してください。
                        </view>
                    </view>
                </view>
            </view>
        </view>
        <view class="title" v-if="type != 3">
            スタンプデザイン
        </view>
        <view class="showY pdn" style="padding-bottom: 0 !important;" v-if="type != 3">
            <view class="logoContent">
                <!-- <image class="sv"
                    :style="`transform: translate(-111220px);filter: drop-shadow(111220px 0 0 ${logoColor});`"
                    :src="logo" v-if="method == 1" mode=""></image> -->
                    <view :style="`backgroundImage: url(${svgData11})`" class="icon" v-if="currentLogo == 0"></view>
                    <view :style="`backgroundImage: url(${svgData12})`" class="icon" v-if="currentLogo == 1"></view>
                    <view :style="`backgroundImage: url(${svgData13})`" class="icon" v-if="currentLogo == 2"></view>
                    <view :style="`backgroundImage: url(${svgData14})`" class="icon" v-if="currentLogo == 3"></view>
                    <view :style="`backgroundImage: url(${svgData15})`" class="icon" v-if="currentLogo == 4"></view>
                    <view :style="`backgroundImage: url(${svgData16})`" class="icon" v-if="currentLogo == 5"></view>
                    <view :style="`backgroundImage: url(${svgData17})`" class="icon" v-if="currentLogo == 6"></view>
                    <view :style="`backgroundImage: url(${svgData18})`" class="icon" v-if="currentLogo == 7"></view>

                <!-- <image v-if="method == 2" :src="logo2" mode=""></image> -->
            </view>

            <view class="selectContent" @click="selectLogoType(1)">
                <view class="select" :style="method == 1?'border:1px solid transparent !important' : ''">
                    <image v-if="method == 1" src="../../../../static/icons/select.png" mode=""></image>
                </view>
                <view class="selectName">
                    テンプレートから選択
                </view>
            </view>
            <view class="logoList">
                <view class="logoItem" v-for="(item,index) in logos" :key="index">
                    <view class="logoImage " style="position: relative;">
                        <!-- <image :src="item.logo" mode=""></image> -->
                       <view :style="`backgroundImage: url(${svgData11})`" class="icon1" v-if="index == 0"></view>
                       <view :style="`backgroundImage: url(${svgData12})`" class="icon1" v-if="index == 1"></view>
                       <view :style="`backgroundImage: url(${svgData13})`" class="icon1" v-if="index == 2"></view>
                       <view :style="`backgroundImage: url(${svgData14})`" class="icon1" v-if="index == 3"></view>
                       <view :style="`backgroundImage: url(${svgData15})`" class="icon1" v-if="index == 4"></view>
                       <view :style="`backgroundImage: url(${svgData16})`" class="icon1" v-if="index == 5"></view>
                       <view :style="`backgroundImage: url(${svgData17})`" class="icon1" v-if="index == 6"></view>
                       <view :style="`backgroundImage: url(${svgData18})`" class="icon1" v-if="index == 7"></view>
                       
                    </view>
                    <view class="selectContents" @click="chooseLogo(index)">
                        <view class="unselect" v-if="!item.checked"></view>
                        <view class="selected" v-if="item.checked">
                            <view class="fill"></view>
                        </view>
                    </view>
                </view>
            </view>
            <view class="chooseContent">
                <view class="chooseLeft">
                    スタンプカラー
                </view>
                <view class="chooseRight">
                    <view class="colorSqu" @click="toChoose('logo')" :style="`background:${logoColor}`"></view>
                </view>
            </view>
            <view class="blank"></view>
            <view class="self">
                <view class="selectContent" @click="selectLogoType(2)">
                    <view class="select" :style="method == 2?'border:1px solid transparent !important' : ''">
                        <image v-if="method == 2" src="../../../../static/icons/select.png" mode=""></image>
                    </view>
                    <view class="selectName">
                        オリジナルデザインをアップロード
                    </view>
                </view>
                <view class="tips mgt_20 pd">
                    <text>※</text>ファイル形式は.PNGのみ可
                </view>
                <view class="tips mgt_20 pd">
                    <text>※</text>スタンプカラーの変更はできま
                </view>
                <view class="picker pd mgt_60 mgb_60" @click="chooseSelfLogo">
                    <u-icon name="plus"></u-icon>
                    <!-- <text >ファイル選択</text> -->
                    <view style="font-size: 32rpx !important;margin: 0 auto;">ファイル選択</view>
                </view>
                <view class="selfImage pd" v-if="fileName != ''">
                    <view class="fileName" v-text="fileName"></view>
                    <u-icon name="close-circle" size="28" @click="deleteSelfLogo"></u-icon>
                </view>
            </view>
            <view class="blank"></view>
        </view>
        <view class="title">
            <view>有効期限</view>
            <u-switch v-model="value" activeColor="#34C759"></u-switch>
        </view>
        <view class="newContent">
            <view class="fuTitle">オフにすると、有効期限は設定されません</view>
            <view class="newItem" v-if="value == true">
                <view class="newLeft">
                    <view class="selectContents" @click="changeDateType(1)">
                        <view class="unselect" v-if="dateType != 1"></view>
                        <view class="selected" v-if="dateType == 1">
                            <view class="fill"></view>
                        </view>
                    </view>
                </view>
                <view class="newRight">
                    <view class="rightTitle" :style="dateType == 1?'color:#1d1d1d' : ''">
                        カード発行日から
                    </view>
                    <view class="rightContent">
                        <view class="picker" @click="show3 = true">
                            <text v-text="`${showdays}`"></text>
                            <u-icon name="arrow-down"></u-icon>
                        </view>
                    </view>
                </view>
            </view>
            <view class="newItem" v-if="value == true">
                <view class="newLeft">
                    <view class="selectContents" @click="changeDateType(2)">
                        <view class="unselect" v-if="dateType != 2"></view>
                        <view class="selected" v-if="dateType == 2">
                            <view class="fill"></view>
                        </view>
                    </view>
                </view>
                <view class="newRight">
                    <view class="rightTitle" :style="dateType == 2?'color:#1d1d1d' : ''">
                        年月日を指定する
                    </view>
                    <view class="rightContent" @click="chooseDate">
                        <view class="can">
                            <view class="canLeft">
                                <image src="../../../../static/svg/calogo.svg" mode=""></image>
                            </view>
                            <view style="box-sizing: border-box;padding: 0 20upx;font-size: 16px !important;" v-text="`${endDate} (${week})`"></view>
                        </view>
                    </view>
                </view>
            </view>
        </view>
        <view class="title">
            <view>備考欄</view>
            <u-switch v-model="value1" activeColor="#34C759"></u-switch>
        </view>
        <view class="newContent">
            <view class="fuTitle">オフにすると、備考欄は設定されません</view>
            <view class="newItem" v-if="value1 == true">
                <u--textarea v-model="remark" fontSize="32rpx" placeholder="備考欄への追記事項をご記入ください。"></u--textarea>
            </view>
        </view>
        <view class="title">
            <view>特典</view>
            <u-switch v-model="value2" activeColor="#34C759"></u-switch>
        </view>
        <view class="newContent">
            <view class="fuTitle">オフにすると、特典は設定されません。</view>
            <view class="topImage"  v-if="value2 == true">
                <view class="bannerItem" v-for="(item,index) in banner">
                    <image :src="item" mode="aspectFill"></image>
                    <view class="close" @click="del(index)">
                        <image src="../../../../static/icons/wclose.png"></image>
                    </view>
                </view>
            </view>
            <view class="caution"  v-if="value2 == true">
                画像は4枚まで掲載可
            </view>
            <view class="addImage" @click="uploadBanner"  v-if="value2 == true">
                <text>+</text>
                <view style="font-size: 16px !important;">画像を選択</view>
            </view>
           <!-- <view class="inp"  v-if="value2 == true && type == 3">
                <view class="inpTitle">
                    <text style="color: #D93025;">※</text>タイトル
                </view>
                <u-input v-model="yTitle" border="bottom" placeholder="タイトルテキストタイトルテキスト"></u-input>
            </view> -->
            <view class="inp"  v-if="value2 == true">
                <view class="inpTitle" style="font-size: 16px !important;">
                    特典内容
                </view>
                <u--textarea fontSize="60px" height="100" v-model="yContent" placeholder="特典内容を記入"> </u--textarea>
            </view>
        </view>
        <u-button type="primary" text="保存する" class="btn" :disabled="banner.length < 0 || name == ''" @click="save"></u-button>
        <view style="height: 218upx;"></view>
        <!-- 预览弹窗 -->
        <u-popup :show="showPopup" round="32">
            <view class="pTitle">
                <view class="closeImage">
                    <image src="../../../../static/icons/tclose.png" @click="showPopup = false" mode=""></image>
                </view>
                <view class="pt">
                    プレビュー
                </view>
            </view>
            <view class="containe">
                <view class="storeName" v-text="shop_name"></view>
                <view class="userInfo">
                    <view class="userNo">
                        NO:105
                    </view>
                    <view class="usersName">
                        山田太郎さん
                    </view>
                    <view class="userCode" v-if="type != 3">
                        <image src="../../../../static/images/qrcode.png" mode=""></image>
                    </view>
                    <view class="tCode" v-else>
                        <image src="../../../../static/images/ITF.png" mode=""></image>
                    </view>
                    <view class="cardNo">
                        1923055025809
                    </view>
                    <view class="myPoints" v-if="type == 3 && use">
                        <view>
                            保有ポイント数
                        </view>
                        <view class="points">
                            0 pt
                        </view>
                    </view>
                </view>

                <view class="cardInfo">
                    <view class="cardTitle" style="display: block;text-align: center;border: none;" v-text="name"></view>
                    <!-- 带笑脸的印章 -->
                    <view class="cardY" style="height: auto;" :style="`background:${background}`" v-if="type ==1">
                        <view class="cardInner" style="overflow-y: hidden;">
                            <view class="seal" v-for="(item,index) in list">
                                <text v-text="index + 1"></text>
                                <view class="sealed" v-if="item.sealed">
                                     <image v-if="method == 2" :src="selfLogo" mode=""></image>
                                    <view v-if="method == 1">
                                        <view :style="`backgroundImage: url(${svgData11})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 0"></view>
                                        <view :style="`backgroundImage: url(${svgData12})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 1"></view>
                                        <view :style="`backgroundImage: url(${svgData13})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 2"></view>
                                        <view :style="`backgroundImage: url(${svgData14})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 3"></view>
                                        <view :style="`backgroundImage: url(${svgData15})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 4"></view>
                                        <view :style="`backgroundImage: url(${svgData16})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 5"></view>
                                        <view :style="`backgroundImage: url(${svgData17})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 6"></view>
                                        <view :style="`backgroundImage: url(${svgData18})`" class="icon" style="width: 80upx;height: 80upx;" v-if="currentLogo == 7"></view>
                                    </view>
                                    <view class="sealTime" v-text="item.time"></view>
                                </view>
                            </view>
                        </view>
                    </view>
                    <!-- 不带笑脸的印章 -->
                    <view class="cardY" :style="`background:${background}`" v-if="type == 2">
                        <view class="cardTable" style="overflow-y: scroll;position: relative;" >
                            <view class="cardTitle" style="position: sticky;top:0;left: 0;background: #fff;">
                                <view class="tableItem">
                                    NO
                                </view>
                                <view class="tableItem" style="padding: 0 14upx;">
                                    日付
                                </view>
                                <view class="tableItem">
                                    <view style="margin: 0 auto;width: 48upx;">
                                        <u-icon name="checkmark-circle" size="20"></u-icon>
                                    </view>
                                </view>
                                <view class="tableItem">
                                    <view style="margin: 0 auto;width: 48upx;">
                                        <u-icon name="account-fill" size="20"></u-icon>
                                    </view>
                                </view>
                                <view class="tableItem">
                                    <image style="visibility: hidden;" class="set"
                                        src="../../../../static/icons/cright.png" mode=""></image>
                                </view>
                            </view>
                            <view class="tableContent">
                                <view class="contentItem" v-for="(item,index) in list">
                                    <view class="tableItem" v-text="index+1"></view>
                                    <view class="tableItem">
                                        YY/MM/DD(水)
                                    </view>
                                    <view class="tableItem">
                                        15:08
                                    </view>
                                    <view class="tableItem">
                                        長谷川杏
                                    </view>
                                    <view class="tableItem">
                                        <image v-if="method == 2" :src="selfLogo" style="width: 40upx;height: 40upx;" mode=""></image>
                                        <view v-if="method == 1">
                                            <view :style="`backgroundImage: url(${svgData11})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 0"></view>
                                            <view :style="`backgroundImage: url(${svgData12})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 1"></view>
                                            <view :style="`backgroundImage: url(${svgData13})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 2"></view>
                                            <view :style="`backgroundImage: url(${svgData14})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 3"></view>
                                            <view :style="`backgroundImage: url(${svgData15})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 4"></view>
                                            <view :style="`backgroundImage: url(${svgData16})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 5"></view>
                                            <view :style="`backgroundImage: url(${svgData17})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 6"></view>
                                            <view :style="`backgroundImage: url(${svgData18})`" class="icon" style="width: 40upx;height: 40upx;" v-if="currentLogo == 7"></view>
                                        </view>
                                    </view>
                                </view>
                            </view>
                        </view>
                    </view>
                    <view class="cardInfos">
                        <view class="ciItem" v-if="value == true">
                            <view class="ciLeft">
                                有効期限
                            </view>
                            <view class="ciRight" v-if="dateType == 1" v-text="`カード発効日から${days}日間`"></view>
                            <view class="ciRight" v-if="dateType == 2" v-text="`${endDate} (${week})`"></view>
                        </view>
                        <view class="ciItem" v-if="value1 == true">
                            <view class="ciLeft">
                                備　考
                            </view>
                            <view class="ciRight">
                               {{remark}}
                            </view>
                        </view>
                    </view>
                </view>
                <view class="appoint">
                    <view class="appointTitle">
                        予 約
                    </view>
                    <view class="appointItem">
                        <view class="aItem">
                            <view class="aItemImage">
                                <image src="../../../../static/icons/record1.png" mode=""></image>
                            </view>
                            <view class="aTime">
                                <view class="time">
                                    10/12(月)　09:30〜
                                </view>
                                <view class="copy">
                                    カット&カラー
                                </view>
                            </view>
                        </view>
                        <view class="aItem">
                            <view class="aItemImage">
                                <image src="../../../../static/icons/record3.png" mode=""></image>
                            </view>
                            <view class="aUser">
                                長谷川杏
                            </view>
                        </view>
                    </view>
                    <view class="appointItem">
                        <view class="aItem">
                            <view class="aItemImage">
                                <image src="../../../../static/icons/record1.png" mode=""></image>
                            </view>
                            <view class="aTime">
                                <view class="time">
                                    10/12(月)　09:30〜
                                </view>
                                <view class="copy">
                                    カット&カラー
                                </view>
                            </view>
                        </view>
                        <view class="aItem">
                            <view class="aItemImage">
                                <image src="../../../../static/icons/record3.png" mode=""></image>
                            </view>
                            <view class="aUser">
                                長谷川杏
                            </view>
                        </view>
                    </view>
                </view>
                <view class="special" v-if="value2 == true">
                    <view class="specialTitle">
                        特 典
                    </view>
                    <u-swiper :list="banner" height="250" @click="click" :indicator="true"
                        indicatorMode="dot" indicatorActiveColor="#000000" indicatorInactiveColor="rgba(0,0,0,.3)" v-if="banner.length > 0">
                    </u-swiper>
                   <!-- <view class="remark">
                        {{yTitle}}
                    </view> -->
                    <view class="remark" style="padding-top: 0 !important;">
                        {{yContent}}
                    </view>
                </view>
            </view>
        </u-popup>
        <!-- 选择颜色 -->
        <view v-if="show1" class="mask" @touchmove.stop.prevent="">
            <view class="maskContent">
                <view class="ptitle">
                    <view @click="show1 = false">
                        <image src="../../../../static/icons/tclose.png" style="width: 50upx;height: 50upx;" mode="">
                        </image>
                    </view>
                    <view class="pName">
                        カラー
                    </view>
                    <view class="saveColor" @click="confirmColor">
                        保存
                    </view>
                </view>
                <chooseColor :source='colorType' @selectColor="saveTempColor"></chooseColor>
            </view>
        </view>
        <!-- 选择数量 -->
        <u-picker @confirm="selectLogoNum" @cancel="show2 = false" @close="show2 = false" cancelText="キャンセル" confirmText="保存" :show="show2" :columns="columns">
        </u-picker>
        <!-- 选择日期 -->
        <u-calendar :show="showCalendar" @close="showCalendar = false" monthNum="36" :showTitle = "false" mode="single" @confirm="confirm">
        </u-calendar>
        <!-- 选择有效期 -->
        <u-picker @confirm="selectDayNum" @cancel="show3 = false" @close="show3 = false" cancelText="キャンセル"
            confirmText="保存" :show="show3" :columns="columns2">
        </u-picker>
        <view class="maskk" v-if="k == true">
            
        </view>
    </view>
</template>

<script>
    import chooseColor from "../chooseColor/chooseColor.vue"
    import {addMembershipCard} from "@/api/index.js"
    import svg1 from '../../../../static/svgjs/sv_01.js'
    import svg2 from '../../../../static/svgjs/sv_02.js'
    import svg3 from '../../../../static/svgjs/sv_03.js'
    import svg4 from '../../../../static/svgjs/sv_04.js'
    import svg5 from '../../../../static/svgjs/sv_05.js'
    import svg6 from '../../../../static/svgjs/sv_06.js'
    import svg7 from '../../../../static/svgjs/sv_07.js'
    import svg8 from '../../../../static/svgjs/sv_08.js'
    export default {
        components: {
            chooseColor
        },
        data() {
            return {
                isdisabled:true,
                svgData1:'"' + svg1 + '"',
                svgData2:'"' + svg2 + '"',
                svgData3:'"' + svg3 + '"',
                svgData4:'"' + svg4 + '"',
                svgData5:'"' + svg5 + '"',
                svgData6:'"' + svg6 + '"',
                svgData7:'"' + svg7 + '"',
                svgData8:'"' + svg8 + '"',
                svgData11:'"' + svg1 + '"',
                svgData12:'"' + svg2 + '"',
                svgData13:'"' + svg3 + '"',
                svgData14:'"' + svg4 + '"',
                svgData15:'"' + svg5 + '"',
                svgData16:'"' + svg6 + '"',
                svgData17:'"' + svg7 + '"',
                svgData18:'"' + svg8 + '"',
                colorType: "background",
                currentLogo:0,
                k:false,
                columns: [
                    [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27,
                        28, 29, 30
                    ]
                ],
                tti:"スタンプカード作成",
                banner: [],
                method: 1,
                dateType: 1,
                showCalendar: false,
                list: [{
                        sealed: true,
                        sealImage: "../../../../static/svg/sv_01.svg",
                        time: "YY/MM/DD"
                    },
                    {
                        sealed: true,
                        sealImage: "../../../../static/svg/sv_01.svg",
                        time: "YY/MM/DD"
                    },
                    {
                        sealed: true,
                        sealImage: "../../../../static/svg/sv_01.svg",
                        time: "YY/MM/DD"
                    },
                    {
                        sealed: true,
                        sealImage: "../../../../static/svg/sv_01.svg",
                        time: "YY/MM/DD"
                    },
                    {
                        sealed: true,
                        sealImage: "../../../../static/svg/sv_01.svg",
                        time: "YY/MM/DD"
                    },
                    {
                        sealed: true,
                        sealImage: "../../../../static/svg/sv_01.svg",
                        time: "YY/MM/DD"
                    },
                    {
                        sealed: true,
                        sealImage: "../../../../static/svg/sv_01.svg",
                        time: "YY/MM/DD"
                    },
                    {
                        sealed: true,
                        sealImage: "../../../../static/svg/sv_01.svg",
                        time: "YY/MM/DD"
                    },
                    {
                        sealed: true,
                        sealImage: "../../../../static/svg/sv_01.svg",
                        time: "YY/MM/DD"
                    },
                    {
                        sealed: true,
                        sealImage: "../../../../static/svg/sv_01.svg",
                        time: "YY/MM/DD"
                    }
                ],
                logos: [{
                        logo: "../../../../static/svg/sv_01.svg",
                        checked: true
                    },
                    {
                        logo: "../../../../static/svg/sv_02.svg",
                        checked: false
                    },
                    {
                        logo: "../../../../static/svg/sv_03.svg",
                        checked: false
                    },
                    {
                        logo: "../../../../static/svg/sv_04.svg",
                        checked: false
                    },
                    {
                        logo: "../../../../static/svg/sv_05.svg",
                        checked: false
                    },
                    {
                        logo: "../../../../static/svg/sv_06.svg",
                        checked: false
                    },
                    {
                        logo: "../../../../static/svg/sv_07.svg",
                        checked: false
                    },
                    {
                        logo: "../../../../static/svg/sv_08.svg",
                        checked: false
                    },
                ],
                columns2: [
                    [
                        "7日", "15日", "30日", "60日","90日", "180日", "1年", "2年","3年"
                    ]
                ],
                value: false,
                value1: false,
                value2: false,
                type: 0,
                background: "#54DBDB",
                logoColor: "#D93025",
                chooseColor: "#54DBDB",
                fileName: "",
                number: 10,
                show: false,
                show1: false,
                show2: false,
                show3: false,
                showPopup:false,
                logo: "../../../../static/svg/sv_01.svg",
                shop_name: "",
                image: "",
                name: "",
                moveDistance: "",
                selfLogo: "",
                logo2: "",
                days: 7,
                remark: "",
                yTitle: "",
                yContent: "",
                point:"",
                amount:"",
                endDate:"XXXX-XX-XX",
                use:false,
                week:"X",
                tempColor:"",
                seal_icon_name:"",
                showdays:"日数を選択"
            };
        },
        created() {
            
        },
        onLoad(options) {
            let query = options
            // console.log(decodeURIComponent(query.shop_name) )
            this.type = query.type
            this.shop_name =  decodeURIComponent(query.shop_name) 
            this.image = query.image
            if(query.type == 3){
                this.tti = "ポイントカード作成"
            }
        },
        methods: {
            changeColor(url,color){
             	let res = url.replace(/%23[a-zA-Z0-9]{6}/g, color.replace("#", "%23"));//转义后的#等于%23，利用正则表达式，替换所有%23后6位为新的十六进制六位数。
             	return res;
             },
            goBakc(){
                let that = this
                if(that.show1 == true){
                    that.show1 = false
                }else{
                    uni.navigateBack() 
                }
            },
            chooseLogo(index) {
                let that = this
                that.logos.forEach((value, idx) => {
                    if (index == idx) {
                        value.checked = true
                        that.logo = value.logo
                    } else {
                        value.checked = false
                    }
                })
                that.currentLogo = index
            },
            
            changeUse(e){
              this.use = e  
            },
            confirm(e){
                this.showCalendar = !this.showCalendar
                this.endDate = e[0]
                let date = new Date(e[0])
                let week = date.getDay()
                let wek = week == 0? "日": week == 1? "月" : week == 2? "火" : week == 3? '水' : week == 4? '木': week == 4? '金': '土'
                this.week = wek
            },
            uploadBanner() {
                let that = this
                if (that.banner.length < 4) {
                    uni.chooseImage({
                        count: 4 - that.banner.length,
                        success: (res) => {
                            uni.showLoading({
                                title:"アップロード中"
                            })
                            that.k = true
                            const tempPaths = res.tempFilePaths
                            for (let i = 0; i < tempPaths.length; i++) {
                                uni.uploadFile({
                                    url: that.$baseUrl+'/tool/upload/file',
                                    filePath: res.tempFilePaths[i],
                                    name:"file",
                                    success: (result) => {
                                        uni.hideLoading()
                                        that.k = false
                                        if (result.errMsg == "uploadFile:ok") {
                                            let re = JSON.parse(result.data)
                                            if (re.code == 200) {
                                                that.banner.push(re.data.path)
                                            }
                                        }
                                    }
                                })
                            }
                        }
                    });
                } else {
                    uni.showToast({
                        title: "画像は4枚まで掲載可",
                        icon: "none"
                    })
                }
            },
            del(index) {
                this.banner.splice(index, 1)
            },
            selectDayNum(e) {
                this.show3 = false
                if(e.value[0] == "1年"){
                    this.days = 365
                    this.showdays = "1年"
                }else if(e.value[0] == '2年'){
                    this.days = 730
                     this.showdays = "2年"
                }else if(e.value[0] == '3年'){
                    this.days = 1095
                    this.showdays = "3年"
                }else{
                    this.days = e.value[0].substring(0, e.value[0].length - 1)
                    this.showdays = e.value[0].substring(0, e.value[0].length - 1) + '日'
                }
            },
            chooseDate() {
                this.showCalendar = true
            },

            changeDateType(e) {
                let that = this
                that.dateType = e
            },

            deleteSelfLogo() {
                let that = this
                that.fileName = ''
                that.logo2 = ""
            },

            chooseSelfLogo() {
                let that = this
                uni.chooseImage({
                    count: 1,
                    success: (res) => {
                        const tempPaths = res.tempFilePaths
                        uni.uploadFile({
                            url: that.$baseUrl+'/tool/upload/file',
                            filePath: res.tempFilePaths[0],
                            name:"file",
                            success: (result) => {
                                if (result.errMsg == "uploadFile:ok") {
                                    let re = JSON.parse(result.data)
                                    if (re.code == 200) {
                                        that.selfLogo = re.data.path
                                        that.fileName = re.data.file_name
                                        that.logo2 = re.data.path
                                    }
                                }
                            }
                        })
                    }
                });
            },

            selectLogoType(e) {
                let that = this
                that.method = e
            },

            selectLogoNum(e) {
                this.show2 = false
                this.number = e.value[0]
                let temp = []
                let length = e.value[0]
                for (let i = 0; i < length; i++) {
                    let data = {}
                    data.sealed = true
                    data.time = "YY/MM/DD"
                    data.sealImage = "../../../../static/icons/smile.png"
                    temp.push(data)
                }
                this.list = temp
            },
            saveTempColor(e){
              this.tempColor = e  
            },
            confirmColor(e) {
                let that = this
                if (that.colorType == "background") {
                    that.background = that.tempColor
                } else {
                    that.logoColor = that.tempColor
                    this.svgData11 = this.changeColor(this.svgData1,that.tempColor);
                   this.svgData12 = this.changeColor(this.svgData2,that.tempColor);
                   this.svgData13 = this.changeColor(this.svgData3,that.tempColor);
                   this.svgData14 = this.changeColor(this.svgData4,that.tempColor);
                   this.svgData15 = this.changeColor(this.svgData5,that.tempColor);
                   this.svgData16 = this.changeColor(this.svgData6,that.tempColor);
                   this.svgData17 = this.changeColor(this.svgData7,that.tempColor);
                   this.svgData18 = this.changeColor(this.svgData8,that.tempColor);
                }
                that.show1 = false
            },

            changeColors(e) {
            },
            toChoose(e) {
                this.colorType = e
                this.show1 = true
                
            },
            save() {
                uni.showLoading({
                    title:"読み込み中",
                    mask:true
                })
                let that = this
                let data = {}
                data.type = that.type
                data.shop_name = that.shop_name
                data.background_color = that.background
                data.count = that.number
                data.name = that.name
                data.seal_icon = that.method == 1? that.logo : that.logo2
                data.seal_color = that.logoColor
                data.discount_content = that.yContent
                data.valid_type = that.value == false ? 0 : that.dateType
                data.valid_day = that.days
                data.expire_time = that.endDate
                data.remark = that.remark
                data.is_discount = that.value2 == true? 1 : 0
                data.discount_images = that.banner
                data.point = that.point
                // data.discount_title = that.yTitle
                data.is_remark = that.value1 == true? 1 : 0 
                data.amount = that.amount
                data.is_point = that.use == true?1:0
                data.image = that.image
                data.seal_icon_name = that.fileName
                addMembershipCard(data).then((res) => {
                    if(res.code == 200){
                        uni.hideLoading()
                        uni.showToast({
                            title:res.message,
                            duration:2000
                        })
                        setTimeout(function(){
                            uni.redirectTo({
                                url:"../membershipCard"
                            })
                        },2000)
                    }else{
                        uni.showToast({
                            title:res.message,
                            icon:"none"
                        })
                    }
                })
            },

        }
    }
</script>

<style lang="scss">
    .uni-textarea{
        font-size: 16px !important;
    }
    .tCode {
        width: 460upx;
        height: 102upx;
        margin: 48upx auto 20upx;

        image {
            width: 100%;
            height: 100%;
        }
    }
    
    .saveColor{
        font-size: 30upx;
        color: #007AFF;
        font-family: Hiragino Sans-W3, Hiragino Sans;
    }
    
    .icon{
        width: 240upx;
        height: 240upx;
        background-size: 100% 100%;
        display: inline-block;
    }
    
    .icon1{
        width: 160upx;
        height: 160upx;
        background-size: 100% 100%;
        display: inline-block;
    }
    
    .maskContent{
       background: #fff;
       position: absolute;
       bottom: 0;
       left: 0;
       width: 100vw;
    }
    
    .mask{
        position: fixed;
        top:0;
        left: 0;
        background: rgba(0, 0, 0, .8);
        width: 100vw;
        height: 100vh;
        z-index: 19999 !important;
    }

    .sv {
        transform: translate(-111220px);
        filter: drop-shadow(111220px 0 0 yellow); // 参数详解：阴影X偏移、阴影Y偏移、模糊值大小、色值
    }

    .ptitle {
        display: flex;
        padding: 20upx 30upx;
        position: relative;
        border-top-left-radius: 20upx;
        border-top-right-radius: 20upx;
        justify-content: space-between;
    }

    .pName {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        top: 20upx;
    }

    .myPoints {
        display: flex;
        align-items: center;
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        justify-content: center;
        padding-bottom: 80upx;

        .points {
            font-size: 48upx;
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: normal;
            color: rgba(217, 48, 37, 0.65);
            margin-left: 66upx;
        }
    }

    .containe {
        width: 100%;
        height: 80vh;
        overflow-y: scroll;

        .usersName {
            text-align: center;
            font-size: 48upx;
            font-family: Hiragino Sans-W6, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
        }

        .userCode {
            width: 300upx;
            height: 300upx;
            margin: 48upx auto 32upx;

            image {
                width: 100%;
                height: 100%;
            }
        }

        .cardNo {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            padding-bottom: 80upx;
        }

        .storeName {
            width: 100%;
            box-sizing: border-box;
            padding: 36upx 0;
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            border-top: 2upx solid #d2d2d7;
        }
    }

    .pTitle {
        position: relative;
        height: 111upx;
        background: #FBFBFD;
        border-radius: 32upx;

        .pt {
            width: 100%;
            text-align: center;
            font-size: 40upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            line-height: 111upx;
        }

        .closeImage {
            width: 60upx;
            height: 60upx;
            position: absolute;
            top: 26upx;
            left: 32upx;

            image {
                width: 100%;
                height: 100%;
            }
        }
    }

    .userInfo {
        border-top: 2upx solid #d2d2d7;
        border-bottom: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding: 32upx 40upx 12upx;
    }

    .special {
        background: #FFFFFF;
        box-shadow: 0px 0px 10upx 2upx rgba(29, 29, 31, 0.16);
        box-sizing: border-box;
        padding: 40upx 0;

        .remark {
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            box-sizing: border-box;
            padding: 58upx 40upx 40upx;
        }

        .specialTitle {
            width: 144upx;
            height: 60upx;
            background: #D93025;
            border-radius: 20upx;
            font-size: 28upx;
            font-family: Hiragino Sans-W4, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            line-height: 60upx;
            text-align: center;
            position: relative;
            left: 46upx;
            margin-bottom: 20upx;
        }
    }

    .appoint {
        box-sizing: border-box;
        padding: 32upx 46upx;
        box-shadow: 0px 0px 10upx 2upx rgba(0, 0, 0, 0.16);
        margin-top: 40upx;
        margin-bottom: 40upx;

        .appointTitle {
            width: 144upx;
            height: 60upx;
            background: #1A73E8;
            border-radius: 20upx;
            font-size: 28upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #FFFFFF;
            text-align: center;
            line-height: 60upx;
        }

        .appointItem {
            display: flex;
            justify-content: space-between;
            margin-top: 32upx;

            .aItem {
                display: flex;

                .aItemImage {
                    width: 40upx;
                    height: 40upx;
                    margin-right: 20upx;

                    image {
                        width: 100%;
                        height: 100%;
                    }
                }
            }
        }
    }

    .cardInfo {
        box-sizing: border-box;
        padding: 40upx 18upx 0;
        box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);
        background: #fff;
        z-index: 99;

        .cardTitle {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #1D1D1F;
            text-align: center;
            width: 100%;
        }

        .cardInfos {
            margin-top: 40upx;
            box-sizing: border-box;
            padding: 0 32upx 40upx;
            background: #fff;

            .ciItem {
                display: flex;
                align-items: center;
                margin-bottom: 20upx;

                .ciLeft {
                    width: 144upx;
                    height: 60upx;
                    background: #F5F5F7;
                    font-size: 28upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #707070;
                    text-align: center;
                    line-height: 60upx;
                    margin-right: 28upx;
                }

                .ciRight {
                    font-size: 28upx;
                    font-family: Hiragino Sans-W3, Hiragino Sans;
                    font-weight: normal;
                    color: #1D1D1F;
                }
            }
        }

        .cardY {
            width: 100%;
            height: 398upx;
            background: #53B1AF;
            box-shadow: 0px 0px 10upx 2upx rgba(0, 0, 0, 0.16);
            border-radius: 12upx;
            margin-top: 20upx;
            box-sizing: border-box;
            padding: 36upx;

            .cardInner {
                width: 100%;
                height: 100%;
                background-color: #fff;
                display: flex;
                flex-wrap: wrap;

                .seal {
                    width: 20%;
                    height: 50%;
                    box-sizing: border-box;
                    border-right: 2upx solid #eaeaea;
                    border-bottom: 2upx solid #eaeaea;
                    text-align: center;
                    position: relative;

                    .sealed {
                        position: absolute;
                        width: 100%;
                        height: 100%;
                        left: 0;
                        top: 32upx;

                        image {
                            width: 80upx;
                            height: 80upx;
                        }

                        .sealTime {
                            font-size: 20upx;
                            font-family: Hiragino Sans-W3, Hiragino Sans;
                            font-weight: normal;
                            color: #707070;
                            text-align: center;
                        }
                    }

                    text {
                        font-size: 48upx;
                        font-family: Hiragino Sans-W6, Hiragino Sans;
                        font-weight: normal;
                        color: #EAEAEA;
                        line-height: 170upx;
                    }
                }
            }
        }
    }

    .infoItem {
        display: flex;
        align-items: center;
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        margin-bottom: 20upx;

        .itemname {
            color: #707070;
            margin-right: 70upx;
            width: 128upx;

        }

        .itemValue {
            color: #1D1D1F;
        }
    }



    .btn {
        width: 480upx !important;
        height: 100upx !important;
        border-radius: 20upx;
        background: #1A73E8;
    }

    .btip {
        width: 100%;
        background: rgba(143, 168, 248, 0.2);
        border-radius: 20upx;
        color: #007AFF;
        box-sizing: border-box;
        padding: 24upx 20upx;
        font-size: 32rpx;
    }

    .inpItem {
        display: flex;
        align-items: center;
        margin-bottom: 60upx;

        .inps {
            width: 200upx;
            height: 88upx;
            border-radius: 20upx;
            border: 2upx solid #d2d2d7;
            margin-right: 20upx;

            input {
                width: 100%;
                height: 100%;
                text-align: center;
            }
        }
    }

    .caution {
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #1D1D1F;
        text-align: center;
        margin: 40upx 10upx 20upx;
    }

    .newItem {
        display: flex;
        margin-bottom: 84upx;

        .newLeft {
            margin-right: 20upx;
        }

        .newRight {

            .rightTitle {
                margin-bottom: 24upx;
                font-size: 32upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #AAAAAA;
            }
        }
    }
    ::v-deep .uni-textarea-textarea{
        font-size: 16px !important;
    }
    ::v-deep textarea{
        font-size: 16px !important;
    }
    ::v-deep input{
        font-size: 16px !important;
    }
    .inp {
        box-sizing: border-box;
        padding: 0 54upx;
        margin-bottom: 80upx;

        .inpTitle {
            margin-bottom: 10upx;
            color: #86868B;
        }
    }

    .swit {
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 44upx 36upx;
    }

    .addImage {
        width: 480upx;
        height: 96upx;
        background: #F5F5F7;
        border: 2upx solid #707070;
        text-align: center;
        line-height: 96upx;
        margin: 0 auto 98upx;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32upx;

        text {
            font-size: 60upx;
            margin-right: 26upx;
            position: relative;
            top: -5upx;
        }
    }
    .maskk{
        width: 100vw;
        height: 100vh;
        background: transparent;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 999 !important;
    }
    .topImage {
        box-sizing: border-box;
        padding-top: 40upx;
        display: flex;
        overflow-x: scroll;

        .bannerItem {
            width: 514upx;
            height: 344upx;
            flex-shrink: 0;
            margin-right: 46upx;
            position: relative;

            .close {
                width: 52upx;
                height: 52upx;
                position: absolute;
                right: 14upx;
                top: 14upx;
                z-index: 9;

                image {
                    width: 100%;
                    height: 100%;
                }
            }

            image {
                width: 100%;
                height: 100%;
            }
        }
    }

    .can {
        width: 600upx;
        height: 80upx;
        border-radius: 20upx;
        display: flex;
        align-items: center;
        // justify-content: space-between;
        border: 2upx solid #aaa;
        overflow: hidden;
        margin: 10upx 0 60upx;

        .canLeft {
            background: #EAEAEA;
            text-align: center;
            border-right: 2upx solid #aaa;
            height: 100%;
            line-height: 80upx;
            width: 80upx;
            box-sizing: border-box;
            padding-left: 8upx;
            display: flex;
            align-items: center;
            justify-content: center;
            
            
            image{
                width: 38rpx;
                height: 34rpx;
            }
        }
    }

    .newContent {
        background: #fff;
        box-sizing: border-box;
        padding: 10upx 32upx;

        .fuTitle {
            font-size: 24upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: #707070;
            margin-bottom: 40upx;
        }


    }

    .balnk {
        height: 40upx;
    }

    .selfImage {
        display: flex;
        align-items: center;

        .fileName {
            color: #1A73E8;
            text-decoration: underline;
            margin-right: 40upx;
        }
    }

    .blank {
        height: 80upx;
        border-bottom: 2upx solid #d2d2d7;
    }

    .picker {
        width: 300upx;
        height: 80upx;
        background: #F5F5F7;
        border-radius: 20upx;
        opacity: 1;
        border: 2upx solid #AAAAAA;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-sizing: border-box;
        padding: 0 20upx;
        margin-right: 20upx;
        font-size: 32rpx;

        text {
            flex: 1;
            text-align: center;
            font-size: 32rpx !important;
        }
    }

    .pd {
        position: relative;
        left: 64upx;
    }

    .self {
        box-sizing: border-box;
        padding: 64upx 0 160upx;
    }

    .logoList {
        display: flex !important;
        margin-top: 80upx;
        overflow-x: scroll;

        .logoItem {
            margin-right: 60upx;

            .logoImage {
                width: 160upx;
                height: 160upx;

                image {
                    width: 100%;
                    height: 100%;

                }
            }

            .selectContents {
                margin: 40upx auto 0;
                display: flex;
                justify-content: center;
            }
        }
    }

    .selectContent {
        display: flex;
        align-items: center;

        .selectName {
            font-size: 32upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: rgba(29, 29, 31, 0.9);
        }

        .select {
            width: 40upx;
            height: 40upx;
            border: 2upx solid #1A73E8;
            border-radius: 8upx;
            margin-right: 20upx;

            image {
                width: 100%;
                height: 100%;
            }
        }
    }

    .showY {
        box-sizing: border-box;
        padding: 60upx 28upx;

        .logoContent {
            width: 240upx;
            height: 240upx;
            background: #FFFFFF;
            box-shadow: 0px 0px 10upx 2upx rgba(0, 0, 0, 0.16);
            border-radius: 12upx;
            padding: 20upx;
            margin: 0 auto 80upx;

            image {
                width: 100%;
                height: 100%;
            }
        }
    }

    .tips {
        font-size: 24upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;

        text {
            color: #D93025;
        }
    }

    .chooseContent {
        display: flex;
        align-items: center;
        margin-top: 80upx;

        .chooseLeft {
            margin-right: 20upx;
            font-size: 32upx;
            font-family: Hiragino Sans-W3, Hiragino Sans;
            font-weight: normal;
            color: rgba(29, 29, 31, 0.9);
        }

        .chooseRight {
            display: flex;
            align-items: center;
        }



        .colorSqu {
            width: 200upx;
            height: 80upx;
            box-shadow: 0px 0px 12upx 2upx rgba(0, 0, 0, 0.16);
            border-radius: 12upx;
        }
    }

    .insertContent {
        margin-top: 60upx;
    }

    .insertTitle {
        font-size: 32upx;
        font-family: Hiragino Sans-W3, Hiragino Sans;
        font-weight: normal;
        color: #707070;
        margin-bottom: 10upx;
    }

    .tableContent {
        height: 360upx;
        overflow-y: scroll;

        .contentItem {
            display: flex;
            align-items: center;
            box-sizing: border-box;
            padding: 20upx 0;
            border-bottom: 2upx dashed #d2d2d7;

            .tableItem {
                flex: 1;
                font-size: 25upx;
                font-family: Hiragino Sans-W3, Hiragino Sans;
                font-weight: normal;
                color: #1D1D1F;
                text-align: center;
                flex-shrink: 0;
            }

            .set {
                width: 42upx;
                height: 42upx;
            }
        }
    }

    .cardTitle {
        display: flex;
        align-items: center;
        border-bottom: 2upx solid #d2d2d7;
        box-sizing: border-box;
        padding: 29upx 0 10upx;

        .tableItem {
            flex: 1;
            text-align: center;
            font-size: 25upx;
            font-family: Hiragino Sans-W5, Hiragino Sans;
            font-weight: normal;
            color: #707070;

            .set {
                width: 42upx;
                height: 42upx;
            }
        }
    }

    .content {
        box-sizing: border-box;
        padding: 40upx 18upx;
    }

    .title {
        box-sizing: border-box;
        padding: 40upx 36upx;
        font-size: 32upx;
        font-family: Hiragino Sans-W5, Hiragino Sans;
        font-weight: normal;
        color: rgba(29, 29, 31, 0.9);
        background: #F5F5F7;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pdn {
        padding-top: none !important;
    }

    .cardY {
        width: 100%;
        // height: 398upx;
        // background: #53B1AF;
        box-shadow: 0px 0px 10upx 2upx rgba(0, 0, 0, 0.16);
        border-radius: 12upx;
        box-sizing: border-box;
        padding: 36upx;

        .cardTable {
            width: 100%;
            height: 100%;
            background: #fff;
            box-sizing: border-box;
            padding: 0 8upx;
        }

        .cardInner {
            width: 100%;
            height: 100%;
            background-color: #fff;
            display: flex;
            flex-wrap: wrap;

            .seal {
                width: 20%;
                height: 50%;
                box-sizing: border-box;
                border-right: 2upx solid #eaeaea;
                border-bottom: 2upx solid #eaeaea;
                text-align: center;
                position: relative;

                .sealed {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    left: 0;
                    top: 32upx;

                    image {
                        width: 80upx;
                        height: 80upx;
                    }

                    .sealTime {
                        font-size: 20upx;
                        font-family: Hiragino Sans-W3, Hiragino Sans;
                        font-weight: normal;
                        color: #707070;
                        text-align: center;
                    }
                }

                text {
                    font-size: 48upx;
                    font-family: Hiragino Sans-W6, Hiragino Sans;
                    font-weight: normal;
                    color: #EAEAEA;
                    line-height: 170upx;
                }
            }
        }
    }
</style>
