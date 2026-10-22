<template>
    <!-- [カスタムエイド 2026-10-20] メンバーズカードの作成・編集(スタンプカードA・B/ポイントカード)の新しい画面
         モック「管理App メンバーズカード作成 改善モック」③。作成(secondStep)と編集(editSecondStep)で同じ部品を使う
         ・カードの見本を一番上に。下で変えた内容がすぐ反映
         ・カードの枠:色でぬる/画像を使う(柄としてならべる・全体に広げる)
         ・スタンプ数は −/+(編集では変えられないので、押すと案内)
         ・スタンプデザイン:テンプレート(今の8種類)/オリジナル画像
         ・特典の画像:店舗情報と同じ(押すと拡大・長押しで並び替え・×で削除・4枚まで)
         ・色は新しい色えらびパネル(csColorPanel) -->
    <view class="cf">
        <u-navbar :title="title" :placeholder="true" :safeAreaInsetTop="true" :border="true" bgColor="#FBFBFD">
            <view class="u-nav-slot" slot="left">
                <view class="cf-nav-btn" @click="goBack">
                    <image src="../../static/icons/tclose.png" style="width: 50upx;height: 50upx;" mode=""></image>
                </view>
            </view>
            <view class="u-nav-slot" slot="right">
                <image src="../../static/icons/preview.svg" style="width: 50upx;height: 50upx;"
                    @click="showPopup = true" mode=""></image>
            </view>
        </u-navbar>

        <view class="cf-body">
            <!-- カードの見本 -->
            <view class="cf-sample" v-if="type != 3">
                <view class="cf-frame" :style="frameCss">
                    <!-- スタンプカードA -->
                    <!-- [カスタムエイド 2026-10-20] スタンプが3段以上(11個以上)のときは、見本の高さを2段半に止めて、中をスクロール(お客様アプリと同じ決まり) -->
                    <scroll-view class="cf-grid-scroll" scroll-y v-if="type == 1" :style="list.length > 10 ? 'height: ' + (CF_ROW * 2.5) + 'rpx;' : ''">
                    <view class="cf-grid">
                        <view class="cf-cell" v-for="(item, index) in list" :key="index">
                            <block v-if="index < 3">
                                <image v-if="method == 2 && selfLogo" class="cf-cell-stamp" :src="selfLogo" mode="aspectFit"></image>
                                <view v-else class="cf-cell-stamp cf-svg" :style="'background-image: url(' + stampSvg + ')'"></view>
                                <text class="cf-cell-date">YY/MM/DD</text>
                            </block>
                            <text v-else class="cf-cell-no">{{index + 1}}</text>
                        </view>
                    </view>
                    </scroll-view>
                    <!-- スタンプカードB -->
                    <view class="cf-table" v-if="type == 2">
                        <view class="cf-tr cf-th">
                            <text class="c1">NO</text>
                            <text class="c2">日付</text>
                            <view class="c3"><u-icon name="checkmark-circle" size="16" color="#515154"></u-icon></view>
                            <view class="c4"><u-icon name="account-fill" size="16" color="#515154"></u-icon></view>
                            <text class="c5"></text>
                        </view>
                        <!-- [カスタムエイド 2026-10-20] 行は全部出し、高さは行の数にかかわらず、見出しと合わせていつもスタンプカードAの2段半と同じ。多いときは中をスクロール -->
                        <scroll-view class="cf-grid-scroll" scroll-y :style="'height: ' + (CF_ROW * 2.5 - CF_TR) + 'rpx;'">
                        <view class="cf-tr" v-for="(item, index) in list" :key="index">
                            <text class="c1">{{index + 1}}</text>
                            <text class="c2">YY/MM/DD(月)</text>
                            <text class="c3">00:00</text>
                            <text class="c4">担当者名</text>
                            <view class="c5">
                                <image v-if="method == 2 && selfLogo" class="cf-tstamp" :src="selfLogo" mode="aspectFit"></image>
                                <view v-else class="cf-tstamp cf-svg" :style="'background-image: url(' + stampSvg + ')'"></view>
                            </view>
                        </view>
                        </scroll-view>
                    </view>
                </view>
                <view class="cf-sub cf-center">上の見本は、下で変えた内容がすぐに反映されます</view>
            </view>

            <!-- カード -->
            <view class="cf-h3">カード</view>
            <view class="cf-card">
                <view class="cf-field">
                    <view class="cf-label"><text class="cf-req">※</text>{{type == 3 ? 'ポイントカード名' : 'スタンプカード名'}}</view>
                    <input class="cf-in" :class="{ on: fk == 'name' }" v-model="name" maxlength="-1" :cursor-spacing="120"
                        placeholder-class="cf-ph" :placeholder="type == 3 ? '例:CARD SALON ポイントカード' : '例:CARD SALON スタンプカード'"
                        @focus="fk = 'name'" @blur="fk = ''" />
                </view>

                <block v-if="type != 3">
                    <view class="cf-field">
                        <view class="cf-label">カードの枠</view>
                        <view class="cf-seg">
                            <view class="cf-seg-item" :class="{ on: frameMode == 'color' }" @click="frameMode = 'color'">色でぬる</view>
                            <view class="cf-seg-item" :class="{ on: frameMode == 'image' }" @click="frameMode = 'image'">画像を使う</view>
                        </view>
                        <block v-if="frameMode == 'color'">
                            <view class="cf-color-row" hover-class="cf-hover" @click="openColor('background')">
                                <view class="cf-chip" :style="{ background: background }"></view>
                                <text class="cf-color-hex">{{background}}</text>
                                <text class="cf-link">色を選ぶ</text>
                            </view>
                        </block>
                        <block v-else>
                            <view class="cf-frameimg">
                                <view class="cf-frameimg-thumb" v-if="frameImage" @click="previewOne(frameImage)">
                                    <image :src="frameImage" mode="aspectFill"></image>
                                    <view class="cf-x-btn" @click.stop="frameImage = ''"><view class="cf-x-in"><view class="cf-x"></view></view></view>
                                </view>
                                <view class="cf-frameimg-thumb cf-add-tile" v-else hover-class="cf-hover" @click="chooseFrameImage">
                                    <text class="cf-plus">+</text>
                                    <text>画像を選ぶ</text>
                                </view>
                                <view class="cf-frameimg-right">
                                    <view class="cf-outline-btn" v-if="frameImage" hover-class="cf-hover" @click="chooseFrameImage">画像を変える</view>
                                    <text class="cf-sub">写真は「全体に広げる」、小さな柄の画像は「柄としてならべる」がおすすめ。JPG・PNG・HEIC</text>
                                </view>
                            </view>
                            <view class="cf-label cf-mt">画像の見せ方</view>
                            <view class="cf-seg">
                                <!-- [カスタムエイド 2026-10-20] 「全体に広げる」を先に、最初からこちらを選んでおく(写真を「柄としてならべる」にすると、小さな写真がたくさん並んで見えるため) -->
                                <view class="cf-seg-item" :class="{ on: frameFit == 'cover' }" @click="frameFit = 'cover'">全体に広げる</view>
                                <view class="cf-seg-item" :class="{ on: frameFit == 'tile' }" @click="frameFit = 'tile'">柄としてならべる</view>
                            </view>
                        </block>
                    </view>

                    <view class="cf-count cf-line-top">
                        <text class="cf-count-name">スタンプ数</text>
                        <view class="cf-stepper">
                            <view class="cf-step-btn" :class="{ off: isEdit || number <= 3 }" @click="stepCount(-1)">−</view>
                            <text class="cf-step-num">{{number}}</text>
                            <view class="cf-step-btn" :class="{ off: isEdit || number >= 30 }" @click="stepCount(1)">+</view>
                            <text class="cf-step-unit">個</text>
                        </view>
                    </view>
                    <view class="cf-sub cf-right">{{isEdit ? '作成後は変えられません' : '3〜30個で設定できます'}}</view>
                </block>
            </view>

            <!-- ポイント付与設定(ポイントカード) -->
            <block v-if="type == 3">
                <view class="cf-h3">ポイント付与設定</view>
                <view class="cf-card">
                    <view class="cf-opt" :class="{ on: use }">
                        <view class="cf-opt-head" @click="use = true">
                            <view class="cf-radio" :class="{ on: use }"></view>
                            <text>設定する</text>
                        </view>
                        <view class="cf-opt-body" v-if="use">
                            <view class="cf-inline">
                                <input class="cf-in cf-num" :class="{ on: fk == 'amount' }" v-model="amount" type="digit"
                                    :cursor-spacing="120" @focus="fk = 'amount'" @blur="fk = ''" />
                                <text>円 利用ごとに</text>
                            </view>
                            <view class="cf-inline">
                                <input class="cf-in cf-num" :class="{ on: fk == 'point' }" v-model="point" type="digit"
                                    :cursor-spacing="120" @focus="fk = 'point'" @blur="fk = ''" />
                                <text>ポイントを付与</text>
                            </view>
                            <text class="cf-sub">例:100円ごとに1ポイント → 5,000円で50ポイント</text>
                        </view>
                    </view>
                    <view class="cf-opt" :class="{ on: !use }">
                        <view class="cf-opt-head" @click="use = false">
                            <view class="cf-radio" :class="{ on: !use }"></view>
                            <text>設定・表示しない</text>
                        </view>
                        <text class="cf-opt-note">他社のポイント管理システムをご利用の場合も、こちらを選択してください。</text>
                    </view>
                </view>
            </block>

            <!-- スタンプデザイン -->
            <block v-if="type != 3">
                <view class="cf-h3">スタンプデザイン</view>
                <view class="cf-card">
                    <view class="cf-seg">
                        <view class="cf-seg-item" :class="{ on: method == 1 }" @click="method = 1">テンプレート</view>
                        <view class="cf-seg-item" :class="{ on: method == 2 }" @click="method = 2">オリジナル画像</view>
                    </view>
                    <block v-if="method == 1">
                        <view class="cf-tpls">
                            <view class="cf-tpl" v-for="(item, index) in logos" :key="index" :class="{ on: currentLogo == index }"
                                @click="chooseLogo(index)">
                                <view class="cf-tpl-svg cf-svg" :style="'background-image: url(' + svgList[index] + ')'"></view>
                            </view>
                        </view>
                        <view class="cf-label cf-mt">スタンプの色</view>
                        <view class="cf-color-row" hover-class="cf-hover" @click="openColor('logo')">
                            <view class="cf-chip" :style="{ background: logoColor }"></view>
                            <text class="cf-color-hex">{{logoColor}}</text>
                            <text class="cf-link">色を選ぶ</text>
                        </view>
                    </block>
                    <block v-else>
                        <view class="cf-frameimg cf-mt">
                            <view class="cf-own-thumb" v-if="logo2 || (selfLogo && method == 2 && fileName)" @click="previewOne(logo2 || selfLogo)">
                                <image :src="logo2 || selfLogo" mode="aspectFit"></image>
                                <view class="cf-x-btn" @click.stop="deleteSelfLogo"><view class="cf-x-in"><view class="cf-x"></view></view></view>
                            </view>
                            <view class="cf-own-thumb cf-add-tile" v-else hover-class="cf-hover" @click="chooseSelfLogo">
                                <text class="cf-plus">+</text>
                                <text>画像を選ぶ</text>
                            </view>
                            <view class="cf-frameimg-right">
                                <text class="cf-sub">PNG(背景が透明なもの)のみ</text>
                                <text class="cf-sub">オリジナル画像のときは、スタンプの色は変えられません</text>
                            </view>
                        </view>
                    </block>
                </view>
            </block>

            <!-- 有効期限 -->
            <view class="cf-card cf-card-list">
                <view class="cf-sw-row">
                    <view class="cf-sw-text">
                        <text class="cf-sw-name">有効期限</text>
                        <text class="cf-sub">{{isEdit ? '作成後は変えられません' : 'オフにすると、有効期限は設定されません'}}</text>
                    </view>
                    <view @click="isEdit && (ale = true)">
                        <u-switch v-model="value" :disabled="isEdit" activeColor="#34C759"></u-switch>
                    </view>
                </view>
                <view class="cf-sw-body" v-if="value">
                    <view class="cf-opt" :class="{ on: dateType == 1, off: isEdit && dateType != 1 }" @click="pickDateType(1)">
                        <view class="cf-opt-head">
                            <view class="cf-radio" :class="{ on: dateType == 1 }"></view>
                            <text>カード発行日から</text>
                        </view>
                        <view class="cf-opt-body">
                            <view class="cf-in cf-select" @click.stop="openDays">
                                <text :class="{ 'cf-ph': !daysLabel }">{{daysLabel || '日数を選ぶ'}}</text>
                                <u-icon name="arrow-down" color="#6E6E73" size="14"></u-icon>
                            </view>
                        </view>
                    </view>
                    <view class="cf-opt" :class="{ on: dateType == 2, off: isEdit && dateType != 2 }" @click="pickDateType(2)">
                        <view class="cf-opt-head">
                            <view class="cf-radio" :class="{ on: dateType == 2 }"></view>
                            <text>年月日を指定する</text>
                        </view>
                        <view class="cf-opt-body">
                            <view class="cf-in cf-select" @click.stop="openCalendar">
                                <text :class="{ 'cf-ph': !hasEndDate }">{{hasEndDate ? endDate + '(' + week + ')' : '日付を選ぶ'}}</text>
                                <u-icon name="calendar" color="#6E6E73" size="18"></u-icon>
                            </view>
                        </view>
                    </view>
                </view>
            </view>

            <!-- 備考欄 -->
            <view class="cf-card cf-card-list">
                <view class="cf-sw-row">
                    <view class="cf-sw-text">
                        <text class="cf-sw-name">備考欄</text>
                        <text class="cf-sub">オフにすると、備考欄は設定されません</text>
                    </view>
                    <u-switch v-model="value1" activeColor="#34C759"></u-switch>
                </view>
                <view class="cf-sw-body" v-if="value1">
                    <view class="cf-ta-wrap">
                        <textarea class="cf-ta" :class="{ on: fk == 'remark' }" v-model="remark" maxlength="-1"
                            :show-confirm-bar="false" :cursor-spacing="120" placeholder-class="cf-ph"
                            placeholder="備考欄への追記事項をご記入ください。" @focus="fk = 'remark'" @blur="fk = ''"></textarea>
                        <view class="cf-fade" v-if="fk != 'remark' && isLong(remark)"></view>
                    </view>
                </view>
            </view>

            <!-- 特典 -->
            <view class="cf-card cf-card-list">
                <view class="cf-sw-row">
                    <view class="cf-sw-text">
                        <text class="cf-sw-name">特典</text>
                        <text class="cf-sub">オフにすると、特典は設定されません</text>
                    </view>
                    <u-switch v-model="value2" activeColor="#34C759"></u-switch>
                </view>
                <view class="cf-sw-body" v-if="value2">
                    <view class="cf-img-head">
                        <text class="cf-label cf-nomb">特典の画像</text>
                        <text class="cf-count-txt">{{banner.length}} / 4枚</text>
                    </view>
                    <view class="cf-grid3" id="cfGiftGrid">
                        <view class="cf-tile" v-for="(item, index) in banner" :key="item" :class="{ drag: dragIndex == index }"
                            @click="previewGift(index)" @longpress="startDrag(index)" @touchmove="moveDrag"
                            @touchend="endDrag" @touchcancel="endDrag">
                            <image class="cf-tile-img" :src="item" mode="aspectFill"></image>
                            <view class="cf-x-btn" @click.stop="del(index)"><view class="cf-x-in"><view class="cf-x"></view></view></view>
                            <text class="cf-no">{{index + 1}}</text>
                        </view>
                        <view class="cf-tile cf-add-tile" v-if="banner.length < 4" hover-class="cf-hover" @click="uploadBanner">
                            <view class="cf-add-in"><text class="cf-plus">+</text><text>追加</text></view>
                        </view>
                    </view>
                    <text class="cf-sub">4枚まで。押すと拡大、長押しで並び替えできます。</text>
                    <view class="cf-label cf-mt">特典の内容</view>
                    <view class="cf-ta-wrap">
                        <textarea class="cf-ta" :class="{ on: fk == 'gift' }" v-model="yContent" maxlength="-1"
                            :show-confirm-bar="false" :cursor-spacing="120" placeholder-class="cf-ph" placeholder="特典内容を記入"
                            @focus="fk = 'gift'" @blur="fk = ''"></textarea>
                        <view class="cf-fade" v-if="fk != 'gift' && isLong(yContent)"></view>
                    </view>
                </view>
            </view>

            <view class="cf-save" :class="{ off: !name }" hover-class="cf-save-hover" @click="save">保存する</view>
            <view class="cf-sub cf-center" v-if="!name">カード名を入れると保存できます</view>
        </view>

        <!-- プレビュー(今までの画面と同じ内容。枠は新しい見た目に合わせる) -->
        <u-popup :show="showPopup" round="32" @close="showPopup = false">
            <view class="cf-pv">
            <view class="pTitle">
                <view class="closeImage">
                    <image src="../../static/icons/tclose.png" @click="showPopup = false" mode=""></image>
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
                        <image src="../../static/images/qrcode.png" mode=""></image>
                    </view>
                    <view class="tCode" v-else>
                        <image src="../../static/images/ITF.png" mode=""></image>
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
                    <view class="cardY" style="height: auto;" :style="frameCss" v-if="type ==1">
                        <!-- [カスタムエイド 2026-10-20] お客様アプリと同じく、3段以上は高さを2段半に止めて中をスクロール -->
                        <scroll-view class="cf-grid-scroll" scroll-y :style="list.length > 10 ? 'height: ' + (PV_ROW * 2.5) + 'rpx;' : ''">
                        <view class="cardInner cf-pv-inner" style="overflow-y: hidden;">
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
                        </scroll-view>
                    </view>
                    <!-- 不带笑脸的印章 -->
                    <view class="cardY" :style="frameCss" v-if="type == 2">
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
                                        src="../../static/icons/cright.png" mode=""></image>
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
                                <image src="../../static/icons/record1.png" mode=""></image>
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
                                <image src="../../static/icons/record3.png" mode=""></image>
                            </view>
                            <view class="aUser">
                                長谷川杏
                            </view>
                        </view>
                    </view>
                    <view class="appointItem">
                        <view class="aItem">
                            <view class="aItemImage">
                                <image src="../../static/icons/record1.png" mode=""></image>
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
                                <image src="../../static/icons/record3.png" mode=""></image>
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
                    <u-swiper :list="banner" height="250" :indicator="true"
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
        </view>
        </u-popup>

        <!-- 色えらび -->
        <cs-color-panel :show="cpShow" :title="colorType == 'background' ? 'カードの枠の色' : 'スタンプの色'"
            :value="colorType == 'background' ? background : logoColor" @close="cpShow = false" @confirm="confirmColor"></cs-color-panel>
        <!-- 日付を選ぶ -->
        <u-calendar :show="showCalendar" @close="showCalendar = false" monthNum="36" :showTitle="false" mode="single"
            @confirm="confirm"></u-calendar>
        <!-- 有効期限の日数 -->
        <u-picker @confirm="selectDayNum" @cancel="show3 = false" @close="show3 = false" cancelText="キャンセル"
            confirmText="保存" :show="show3" :columns="columns2"></u-picker>
        <!-- 編集では変えられない項目の案内 -->
        <u-modal :show="ale" title="スタンプ数・有効期限は変えられません"
            content="スタンプ数・有効期限を変えるときは、新しいカードを作成してください。" confirmText="OK" @confirm="ale = false"></u-modal>
    </view>
</template>

<script>
    import pickImage from '@/utils/pickImage.js'
    import csColorPanel from '@/components/csColorPanel/csColorPanel.vue'
    import {
        addMembershipCard,
        editCard,
        getCardDetail
    } from '@/api/index.js'
    import svg1 from '@/static/svgjs/sv_01.js'
    import svg2 from '@/static/svgjs/sv_02.js'
    import svg3 from '@/static/svgjs/sv_03.js'
    import svg4 from '@/static/svgjs/sv_04.js'
    import svg5 from '@/static/svgjs/sv_05.js'
    import svg6 from '@/static/svgjs/sv_06.js'
    import svg7 from '@/static/svgjs/sv_07.js'
    import svg8 from '@/static/svgjs/sv_08.js'
    const SVGS = [svg1, svg2, svg3, svg4, svg5, svg6, svg7, svg8]

    export default {
        components: {
            csColorPanel
        },
        props: {
            mode: { type: String, default: 'add' }, // add:作成 / edit:編集
            type: { type: [String, Number], default: 1 }, // 1:スタンプA 2:スタンプB 3:ポイント
            cardId: { type: [String, Number], default: '' },
            shopName: { type: String, default: '' },
            image: { type: String, default: '' }
        },
        data() {
            return {
                fk: '',
                // 今までの画面と同じ値(サーバーに送る形も同じ)
                background: '#54DBDB',
                logoColor: '#D93025', // スタンプの色の最初の値は赤
                name: '',
                number: 10,
                method: 1,
                currentLogo: 0,
                logos: SVGS.map((s, i) => ({ logo: '../../../../static/svg/sv_0' + (i + 1) + '.svg', checked: i == 0 })),
                logo: '../../../../static/svg/sv_01.svg',
                selfLogo: '',
                logo2: '',
                fileName: '',
                value: false,
                value1: false,
                value2: false,
                dateType: 1,
                days: 7,
                showdays: '',
                endDate: 'XXXX-XX-XX',
                week: 'X',
                remark: '',
                yContent: '',
                banner: [],
                point: '',
                amount: '',
                use: false,
                // 枠の画像(新しい機能)
                CF_ROW: 142, // [カスタムエイド 2026-10-20] 見本のスタンプカードAの1段の高さ(rpx)
                CF_TR: 72, // [カスタムエイド 2026-10-20] 見本のスタンプカードBの1行の高さ(rpx)
                PV_ROW: 172, // [カスタムエイド 2026-10-20] プレビューのスタンプカードAの1段の高さ(rpx。お客様アプリと同じ)
                frameMode: 'color',
                frameImage: '',
                frameFit: 'cover', // [カスタムエイド 2026-10-20] 最初は「全体に広げる」
                // 画面の状態
                showPopup: false,
                cpShow: false,
                colorType: 'background',
                showCalendar: false,
                show3: false,
                ale: false,
                dragIndex: -1,
                gridRect: null,
                columns2: [
                    ['7日', '15日', '30日', '60日', '90日', '180日', '1年', '2年', '3年']
                ]
            }
        },
        computed: {
            isEdit() {
                return this.mode == 'edit'
            },
            title() {
                const t = this.type == 3 ? 'ポイントカード' : 'スタンプカード'
                return t + (this.isEdit ? '編集' : '作成')
            },
            list() {
                const a = []
                for (let i = 0; i < this.number; i++) a.push({ sealed: true, time: 'YY/MM/DD' })
                return a
            },
            tableRows() {
                return this.list.slice(0, 6)
            },
            svgList() {
                const c = (this.logoColor || '#D93025').replace('#', '%23')
                return SVGS.map((s) => ('"' + s + '"').replace(/%23[a-zA-Z0-9]{6}/g, c))
            },
            stampSvg() {
                return this.svgList[this.currentLogo] || this.svgList[0]
            },
            // 今のプレビュー画面(下の popup)が使っている名前
            svgData11() { return this.svgList[0] },
            svgData12() { return this.svgList[1] },
            svgData13() { return this.svgList[2] },
            svgData14() { return this.svgList[3] },
            svgData15() { return this.svgList[4] },
            svgData16() { return this.svgList[5] },
            svgData17() { return this.svgList[6] },
            svgData18() { return this.svgList[7] },
            shop_name() {
                return this.shopName
            },
            frameCss() {
                if (this.frameMode == 'image' && this.frameImage) {
                    return 'background-color: ' + this.background + '; background-image: url(' + this.frameImage + '); background-position: center; ' +
                        (this.frameFit == 'tile' ? 'background-size: 160rpx 160rpx; background-repeat: repeat;' : 'background-size: cover; background-repeat: no-repeat;')
                }
                return 'background: ' + this.background + ';'
            },
            daysLabel() {
                return this.showdays && this.showdays != '日数を選択' ? this.showdays : ''
            },
            hasEndDate() {
                return this.endDate && this.endDate != 'XXXX-XX-XX'
            }
        },
        created() {
            if (this.isEdit && this.cardId) this.getDetail(this.cardId)
        },
        methods: {
            goBack() {
                if (this.cpShow) {
                    this.cpShow = false
                    return
                }
                uni.navigateBack()
            },
            isLong(v) {
                if (!v) return false
                let lines = 0
                String(v).split('\n').forEach((l) => {
                    lines += Math.max(1, Math.ceil(l.length / 20))
                })
                return lines > 3
            },
            getDetail(id) {
                getCardDetail(id).then((res) => {
                    if (res.code != 200) return
                    const c = res.data.card
                    this.background = c.background_color || this.background
                    this.name = c.name
                    this.number = c.count * 1 || 10
                    this.logoColor = c.seal_color || this.logoColor
                    this.remark = c.remark
                    this.banner = Array.isArray(c.discount_images) ? c.discount_images : []
                    this.yContent = c.discount_content
                    this.amount = c.amount
                    this.point = c.point
                    this.selfLogo = c.seal_icon
                    this.use = c.is_point != 0
                    this.value = c.valid_type != 0
                    this.value1 = c.is_remark != 0
                    this.value2 = c.is_discount != 0
                    // 枠の画像(サーバーが対応してから値が入る)
                    this.frameImage = c.frame_image || ''
                    this.frameFit = c.frame_fit == 'tile' ? 'tile' : 'cover'
                    this.frameMode = this.frameImage ? 'image' : 'color'
                    const icon = String(c.seal_icon || '')
                    let found = -1
                    for (let i = 0; i < 8; i++) {
                        if (icon.includes('sv_0' + (i + 1))) found = i
                    }
                    if (found > -1) {
                        this.chooseLogo(found)
                        this.method = 1
                    } else {
                        this.fileName = c.seal_icon_name
                        this.logo2 = c.seal_icon
                        this.method = 2
                    }
                    if (c.valid_type == 1) {
                        this.days = c.valid_day
                        this.dateType = 1
                        this.showdays = (c.valid_day * 1) > 280 ? ((c.valid_day * 1) / 365) + '年' : (c.valid_day * 1) + '日'
                    }
                    if (c.valid_type == 2) {
                        this.dateType = 2
                        this.endDate = c.expire_time
                        this.week = this.weekOf(c.expire_time)
                    }
                })
            },
            weekOf(d) {
                const w = new Date(String(d).replace(/-/g, '/')).getDay()
                return ['日', '月', '火', '水', '木', '金', '土'][w] || 'X'
            },
            stepCount(n) {
                if (this.isEdit) {
                    this.ale = true
                    return
                }
                this.number = Math.min(30, Math.max(3, this.number + n))
            },
            chooseLogo(index) {
                this.logos.forEach((v, i) => {
                    v.checked = i == index
                })
                this.logo = this.logos[index].logo
                this.currentLogo = index
            },
            openColor(t) {
                this.colorType = t
                this.cpShow = true
            },
            confirmColor(c) {
                if (this.colorType == 'background') this.background = c
                else this.logoColor = c
                this.cpShow = false
            },
            pickDateType(t) {
                if (this.isEdit) {
                    this.ale = true
                    return
                }
                this.dateType = t
            },
            openDays() {
                if (this.isEdit) {
                    this.ale = true
                    return
                }
                this.dateType = 1
                this.show3 = true
            },
            openCalendar() {
                if (this.isEdit) {
                    this.ale = true
                    return
                }
                this.dateType = 2
                this.showCalendar = true
            },
            confirm(e) {
                this.showCalendar = false
                this.endDate = e[0]
                this.week = this.weekOf(e[0])
            },
            selectDayNum(e) {
                this.show3 = false
                const v = e.value[0]
                if (v == '1年') {
                    this.days = 365
                } else if (v == '2年') {
                    this.days = 730
                } else if (v == '3年') {
                    this.days = 1095
                } else {
                    this.days = v.substring(0, v.length - 1)
                }
                this.showdays = v
            },
            upload(path) {
                return new Promise((resolve) => {
                    uni.uploadFile({
                        url: this.$baseUrl + '/tool/upload/file',
                        filePath: path,
                        name: 'file',
                        success: (result) => {
                            try {
                                const re = JSON.parse(result.data)
                                resolve(re.code == 200 ? re.data : null)
                            } catch (e) {
                                resolve(null)
                            }
                        },
                        fail: () => resolve(null)
                    })
                })
            },
            chooseFrameImage() {
                pickImage({
                    count: 1,
                    sizeType: ['compressed'],
                    success: (res) => {
                        uni.showLoading({ title: 'アップロード中', mask: true })
                        this.upload(res.tempFilePaths[0]).then((d) => {
                            uni.hideLoading()
                            if (d) this.frameImage = d.path
                            else uni.showToast({ title: 'アップロードできませんでした', icon: 'none' })
                        })
                    }
                })
            },
            chooseSelfLogo() {
                pickImage({
                    count: 1,
                    success: (res) => {
                        uni.showLoading({ title: 'アップロード中', mask: true })
                        this.upload(res.tempFilePaths[0]).then((d) => {
                            uni.hideLoading()
                            if (!d) {
                                uni.showToast({ title: 'アップロードできませんでした', icon: 'none' })
                                return
                            }
                            this.selfLogo = d.path
                            this.fileName = d.file_name
                            this.logo2 = d.path
                        })
                    }
                })
            },
            deleteSelfLogo() {
                this.fileName = ''
                this.logo2 = ''
                this.selfLogo = ''
            },
            previewOne(url) {
                if (url) uni.previewImage({ urls: [url] })
            },
            // 特典の画像:選んだ順に追加・アップロード中はお知らせ
            uploadBanner() {
                const left = 4 - this.banner.length
                if (left <= 0) {
                    uni.showToast({ title: '画像は4枚まで掲載可', icon: 'none' })
                    return
                }
                pickImage({
                    count: left,
                    sizeType: ['compressed'],
                    success: (res) => {
                        const paths = (res.tempFilePaths || []).slice(0, left)
                        if (!paths.length) return
                        uni.showLoading({ title: 'アップロード中', mask: true })
                        Promise.all(paths.map((p) => this.upload(p))).then((list) => {
                            uni.hideLoading()
                            let failed = 0
                            list.forEach((d) => {
                                if (d && this.banner.length < 4) this.banner.push(d.path)
                                else if (!d) failed++
                            })
                            if (failed) uni.showToast({ title: failed + '枚をアップロードできませんでした', icon: 'none' })
                        })
                    }
                })
            },
            del(index) {
                this.banner.splice(index, 1)
            },
            previewGift(index) {
                if (this.dragIndex > -1) return
                uni.previewImage({ urls: this.banner, current: index })
            },
            // 長押しでつかんで並び替え(店舗情報のTOP画像と同じ)
            startDrag(index) {
                this.dragIndex = index
                this.gridRect = null
                this.$emit('lock', true)
                uni.vibrateShort({})
                uni.createSelectorQuery().in(this).select('#cfGiftGrid').boundingClientRect((r) => {
                    this.gridRect = r
                }).exec()
            },
            moveDrag(e) {
                if (this.dragIndex < 0 || !this.gridRect) return
                const t = (e.touches && e.touches[0]) || (e.changedTouches && e.changedTouches[0])
                if (!t) return
                const r = this.gridRect
                const gap = uni.upx2px(16)
                const w = (r.width - gap * 2) / 3
                const h = w * 2 / 3
                let col = Math.min(2, Math.max(0, Math.floor((t.clientX - r.left) / (w + gap))))
                let row = Math.max(0, Math.floor((t.clientY - r.top) / (h + gap)))
                const to = Math.min(this.banner.length - 1, row * 3 + col)
                const from = this.dragIndex
                if (to == from) return
                const item = this.banner.splice(from, 1)[0]
                this.banner.splice(to, 0, item)
                this.dragIndex = to
            },
            endDrag() {
                if (this.dragIndex < 0) return
                this.$emit('lock', false)
                setTimeout(() => {
                    this.dragIndex = -1
                }, 300)
            },
            save() {
                if (!this.name) {
                    uni.showToast({ title: 'カード名を入力してください', icon: 'none' })
                    return
                }
                if (this.type != 3 && this.method == 2 && !this.logo2) {
                    uni.showToast({ title: 'スタンプのオリジナル画像を選んでください', icon: 'none' })
                    return
                }
                uni.showLoading({ title: '読み込み中', mask: true })
                const data = {}
                if (this.isEdit) {
                    data._method = 'put'
                    data.update_type = 'update'
                    data.id = this.cardId
                }
                data.type = this.type
                data.shop_name = this.shopName
                data.background_color = this.background
                data.count = this.number
                data.name = this.name
                data.seal_icon = this.method == 1 ? this.logo : this.logo2
                data.seal_color = this.logoColor
                data.discount_content = this.yContent
                data.valid_type = this.value == false ? 0 : this.dateType
                data.valid_day = this.days
                data.expire_time = this.endDate
                data.remark = this.remark
                data.is_discount = this.value2 == true ? 1 : 0
                data.discount_images = this.banner
                data.point = this.point
                data.is_remark = this.value1 == true ? 1 : 0
                data.amount = this.amount
                data.is_point = this.use == true ? 1 : 0
                data.image = this.image
                data.seal_icon_name = this.fileName
                // 枠の画像(新しい機能。色でぬるときは空にする)
                data.frame_image = this.frameMode == 'image' ? this.frameImage : ''
                data.frame_fit = this.frameFit
                const api = this.isEdit ? editCard : addMembershipCard
                api(data).then((res) => {
                    uni.hideLoading()
                    if (res.code == 200) {
                        uni.showToast({ title: res.message, duration: 2000 })
                        setTimeout(() => {
                            uni.redirectTo({ url: '/pages/settings/membershipCard/membershipCard' })
                        }, 2000)
                    } else {
                        uni.showToast({ title: res.message, icon: 'none' })
                    }
                }).catch(() => {
                    uni.hideLoading()
                })
            }
        }
    }
</script>

<style lang="scss">
    /* [カスタムエイド 2026-10-20] 店舗情報と同じ決まり:枠 #D2D2D7・区切り線 #E5E5EA・入力中 #1A73E8・角 24rpx */
    .cf {
        min-height: 100vh;
        background: #F5F5F7;
        font-family: 'Hiragino Sans', sans-serif;
        color: #1D1D1F;
    }

    .cf-nav-btn {
        width: 64rpx;
        height: 64rpx;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cf-body {
        padding: 32rpx 24rpx calc(80rpx + env(safe-area-inset-bottom));
    }

    .cf-sample {
        margin-bottom: 40rpx;
    }

    .cf-frame {
        padding: 28rpx;
        border-radius: 28rpx;
        box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.08);
        margin-bottom: 16rpx;
    }

    .cf-grid {
        display: flex;
        flex-wrap: wrap;
        background: #FFFFFF;
        border-radius: 12rpx;
        overflow: hidden;
    }

    /* [カスタムエイド 2026-10-20] 見本・プレビューのスタンプの中のスクロール */
    .cf-grid-scroll {
        width: 100%;
        background: #FFFFFF;
        border-radius: 12rpx;
    }

    .cf-cell {
        width: 20%;
        /* [カスタムエイド 2026-10-20] 高さを決まった値に(2段半で止めるため。今までは横幅の22%) */
        height: 142rpx;
        position: relative;
        box-sizing: border-box;
        border-right: 1px solid #E5E5EA;
        border-bottom: 1px solid #E5E5EA;
    }

    .cf-pv-inner {
        height: auto !important;

        .seal {
            height: 172rpx !important;
        }
    }

    .cf-cell:nth-child(5n) {
        border-right: 0;
    }

    .cf-cell-stamp {
        position: absolute;
        left: 50%;
        top: 14%;
        width: 64rpx;
        height: 64rpx;
        margin-left: -32rpx;
    }

    .cf-cell-date {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 10%;
        text-align: center;
        font-size: 16rpx;
        color: #6E6E73;
    }

    .cf-cell-no {
        position: absolute;
        left: 0;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        text-align: center;
        font-size: 26rpx;
        font-weight: 500;
        color: #AEAEB2;
    }

    .cf-svg {
        background-size: 100% 100%;
        background-repeat: no-repeat;
    }

    .cf-table {
        background: #FFFFFF;
        border-radius: 12rpx;
        overflow: hidden;
    }

    .cf-tr {
        display: flex;
        align-items: center;
        height: 72rpx;
        padding: 0 12rpx;
        border-bottom: 1px dashed #D2D2D7;
        font-size: 22rpx;
        color: #1D1D1F;

        .c1 { width: 56rpx; text-align: center; }
        .c2 { flex: 1.6; text-align: center; }
        .c3 { flex: 1; text-align: center; display: flex; justify-content: center; }
        .c4 { flex: 1.3; text-align: center; display: flex; justify-content: center; }
        .c5 { width: 64rpx; display: flex; justify-content: center; }
    }

    .cf-th {
        border-bottom: 1px solid #D2D2D7;
        font-weight: 700;
        color: #515154;
    }

    .cf-tstamp {
        width: 44rpx;
        height: 44rpx;
    }

    .cf-h3 {
        margin: 0 8rpx 16rpx;
        font-size: 30rpx;
        font-weight: 700;
        color: #515154;
    }

    .cf-card {
        background: #FFFFFF;
        border-radius: 32rpx;
        padding: 32rpx;
        margin-bottom: 40rpx;
    }

    .cf-card-list {
        padding: 8rpx 32rpx;
    }

    /* [カスタムエイド 2026-10-21] 項目と項目のあいだを約6px広げる */
    .cf-field {
        margin-bottom: 44rpx;
    }

    .cf-field:last-child {
        margin-bottom: 0;
    }

    /* [カスタムエイド 2026-10-21] 見出しの文字と入力枠の見た目のすき間を約6pxに(行の高さを詰め、余白 4px) */
    .cf-label {
        font-size: 26rpx;
        font-weight: 500;
        margin-bottom: 8rpx;
        line-height: 1.2;
    }

    .cf-nomb {
        margin-bottom: 0;
    }

    .cf-mt {
        margin-top: 24rpx;
    }

    .cf-req {
        color: #C5221F;
    }

    .cf-sub {
        display: block;
        font-size: 24rpx;
        line-height: 1.6;
        color: #6E6E73;
    }

    .cf-center {
        text-align: center;
    }

    .cf-right {
        text-align: right;
    }

    .cf-in {
        width: 100%;
        box-sizing: border-box;
        height: 88rpx;
        line-height: normal;
        padding: 0 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        background: #FFFFFF;
        font-size: 30rpx;
        color: #1D1D1F;
        transition: border-color .15s, box-shadow .15s;
    }

    .cf-in.on,
    .cf-ta.on {
        border-color: #1A73E8;
        box-shadow: 0 0 0 6rpx rgba(26, 115, 232, 0.16);
    }

    .cf-ph {
        color: #8A8A8E;
    }

    .cf-select {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 80rpx;
        border-radius: 20rpx;
    }

    .cf-num {
        width: 220rpx;
        text-align: right;
        margin-right: 20rpx;
    }

    .cf-ta-wrap {
        position: relative;
    }

    .cf-ta {
        width: 100%;
        box-sizing: border-box;
        height: 192rpx;
        padding: 20rpx 28rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        background: #FFFFFF;
        font-size: 30rpx;
        line-height: 1.6;
        color: #1D1D1F;
    }

    .cf-fade {
        position: absolute;
        left: 2px;
        right: 2px;
        bottom: 2px;
        height: 56rpx;
        border-radius: 0 0 22rpx 22rpx;
        background: linear-gradient(rgba(255, 255, 255, 0), #FFFFFF 85%);
        pointer-events: none;
    }

    .cf-seg {
        display: flex;
        padding: 6rpx;
        background: #F2F2F5;
        border-radius: 24rpx;
    }

    .cf-seg-item {
        flex: 1;
        height: 72rpx;
        line-height: 72rpx;
        text-align: center;
        border-radius: 18rpx;
        font-size: 28rpx;
        font-weight: 500;
        color: #515154;
    }

    /* [カスタムエイド 2026-10-21] 切り替えの選んでいる方:薄い青の面+青い線+青い文字(全画面共通) */
    .cf-seg-item {
        border: 1px solid transparent;
        box-sizing: border-box;
    }

    .cf-seg-item.on {
        background: rgba(26, 115, 232, 0.10);
        border-color: #1A73E8;
        color: #1A73E8;
        font-weight: 700;
    }

    .cf-color-row {
        display: flex;
        align-items: center;
        height: 96rpx;
        margin-top: 20rpx;
        padding: 0 24rpx 0 12rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
    }

    .cf-chip {
        width: 72rpx;
        height: 72rpx;
        border-radius: 16rpx;
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.08);
        margin-right: 24rpx;
    }

    .cf-color-hex {
        flex: 1;
        font-size: 30rpx;
    }

    .cf-link {
        font-size: 28rpx;
        font-weight: 500;
        color: #1A73E8;
    }

    .cf-hover {
        opacity: 0.6;
    }

    .cf-frameimg {
        display: flex;
        align-items: center;
        margin-top: 20rpx;
    }

    .cf-frameimg-thumb,
    .cf-own-thumb {
        position: relative;
        width: 176rpx;
        height: 176rpx;
        flex-shrink: 0;
        border-radius: 24rpx;
        overflow: hidden;
        background: #F5F5F7;
        margin-right: 24rpx;

        image {
            width: 100%;
            height: 100%;
        }
    }

    .cf-frameimg-right {
        flex: 1;
        min-width: 0;
    }

    .cf-outline-btn {
        display: inline-block;
        height: 72rpx;
        line-height: 72rpx;
        padding: 0 28rpx;
        border: 1px solid #1A73E8;
        border-radius: 36rpx;
        color: #1A73E8;
        font-size: 28rpx;
        font-weight: 500;
        margin-bottom: 12rpx;
    }

    .cf-add-tile {
        background: #F7FAFF;
        border: 3rpx dashed #8AB0EE;
        box-sizing: border-box;
        color: #1A73E8;
        font-size: 24rpx;
        font-weight: 500;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .cf-plus {
        font-size: 44rpx;
        line-height: 1;
        margin-bottom: 4rpx;
    }

    .cf-x-btn {
        position: absolute;
        top: 0;
        right: 0;
        width: 80rpx;
        height: 80rpx;
        display: flex;
        justify-content: flex-end;
        z-index: 2;
    }

    .cf-x-in {
        margin: 10rpx;
        width: 48rpx;
        height: 48rpx;
        border-radius: 24rpx;
        background: rgba(29, 29, 31, 0.72);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cf-x {
        position: relative;
        width: 22rpx;
        height: 22rpx;
    }

    .cf-x::before,
    .cf-x::after {
        content: '';
        position: absolute;
        left: 50%;
        top: 50%;
        width: 26rpx;
        height: 4rpx;
        margin: -2rpx 0 0 -13rpx;
        border-radius: 2rpx;
        background: #FFFFFF;
        transform: rotate(45deg);
    }

    .cf-x::after {
        transform: rotate(-45deg);
    }

    .cf-count {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 28rpx;
    }

    .cf-line-top {
        border-top: 1px solid #E5E5EA;
        margin-top: 32rpx;
    }

    .cf-count-name {
        font-size: 30rpx;
    }

    .cf-stepper {
        display: flex;
        align-items: center;
    }

    .cf-step-btn {
        width: 80rpx;
        height: 80rpx;
        line-height: 76rpx;
        text-align: center;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        font-size: 40rpx;
        box-sizing: border-box;
    }

    .cf-step-btn.off {
        color: #C7C7CC;
        background: #F5F5F7;
    }

    .cf-step-num {
        min-width: 96rpx;
        text-align: center;
        font-size: 34rpx;
        font-weight: 700;
    }

    .cf-step-unit {
        font-size: 30rpx;
        margin-left: 8rpx;
    }

    .cf-tpls {
        display: flex;
        flex-wrap: wrap;
        margin-top: 28rpx;
    }

    .cf-tpl {
        width: calc((100% - 60rpx) / 4);
        height: 0;
        padding-bottom: calc((100% - 60rpx) / 4);
        margin: 0 20rpx 20rpx 0;
        position: relative;
        box-sizing: border-box;
        border: 1px solid #E5E5EA;
        border-radius: 24rpx;
        background: #FFFFFF;
    }

    .cf-tpl:nth-child(4n) {
        margin-right: 0;
    }

    .cf-tpl.on {
        border: 4rpx solid #1A73E8;
        background: #F3F7FF;
    }

    .cf-tpl-svg {
        position: absolute;
        left: 18%;
        top: 18%;
        width: 64%;
        height: 64%;
    }

    .cf-opt {
        padding: 24rpx;
        border: 1px solid #D2D2D7;
        border-radius: 24rpx;
        margin-bottom: 20rpx;
    }

    .cf-opt:last-child {
        margin-bottom: 0;
    }

    .cf-opt.on {
        border-color: #1A73E8;
        background: #F3F7FF;
    }

    .cf-opt.off {
        opacity: 0.5;
    }

    .cf-opt-head {
        display: flex;
        align-items: center;
        font-size: 30rpx;
        font-weight: 500;
    }

    .cf-opt-body {
        padding: 16rpx 0 0 68rpx;
    }

    .cf-opt-note {
        display: block;
        padding: 12rpx 0 0 68rpx;
        font-size: 26rpx;
        line-height: 1.6;
        color: #515154;
    }

    .cf-radio {
        width: 44rpx;
        height: 44rpx;
        box-sizing: border-box;
        border-radius: 50%;
        border: 3rpx solid #AEAEB2;
        margin-right: 24rpx;
        flex-shrink: 0;
    }

    .cf-radio.on {
        border: 14rpx solid #1A73E8;
    }

    .cf-inline {
        display: flex;
        align-items: center;
        font-size: 30rpx;
        margin-bottom: 20rpx;
    }

    .cf-sw-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 112rpx;
    }

    .cf-sw-text {
        display: flex;
        flex-direction: column;
    }

    .cf-sw-name {
        font-size: 32rpx;
        font-weight: 700;
    }

    .cf-sw-body {
        border-top: 1px solid #E5E5EA;
        padding: 28rpx 0 32rpx;
    }

    .cf-img-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: 20rpx;
    }

    .cf-count-txt {
        font-size: 26rpx;
        color: #515154;
    }

    .cf-grid3 {
        display: flex;
        flex-wrap: wrap;
    }

    .cf-tile {
        position: relative;
        width: calc((100% - 32rpx) / 3);
        height: 0;
        padding-bottom: calc((100% - 32rpx) / 3 * 2 / 3);
        margin: 0 16rpx 16rpx 0;
        border-radius: 20rpx;
        overflow: hidden;
        background: #E5E5EA;
        transition: transform .12s;
    }

    .cf-tile:nth-child(3n) {
        margin-right: 0;
    }

    .cf-tile.drag {
        transform: scale(1.06);
        opacity: 0.9;
        z-index: 5;
        box-shadow: 0 12rpx 32rpx rgba(0, 0, 0, 0.25);
    }

    .cf-tile.cf-add-tile {
        display: block;
    }

    .cf-add-in {
        position: absolute;
        left: 0;
        top: 0;
        right: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .cf-tile-img {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
    }

    .cf-no {
        position: absolute;
        right: 10rpx;
        bottom: 10rpx;
        min-width: 40rpx;
        height: 36rpx;
        line-height: 36rpx;
        padding: 0 10rpx;
        box-sizing: border-box;
        border-radius: 18rpx;
        background: rgba(255, 255, 255, 0.92);
        font-size: 22rpx;
        font-weight: 700;
        text-align: center;
    }

    .cf-save {
        height: 104rpx;
        line-height: 104rpx;
        border-radius: 28rpx;
        background: #1A73E8;
        color: #FFFFFF;
        text-align: center;
        font-size: 32rpx;
        font-weight: 700;
        margin-bottom: 16rpx;
    }

    .cf-save.off {
        background: #C6DAFC;
    }

    .cf-save-hover {
        opacity: 0.8;
    }

    /* 下はプレビュー(右上の目のマーク)の見た目。今までの画面のものをそのまま使う */
    .cf-pv {

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


    }
</style>
