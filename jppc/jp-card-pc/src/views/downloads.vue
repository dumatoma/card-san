<template>
    <div @click="wrapClick">
        <!-- 印章卡弹窗 -->
        <div class="fixed" v-show="keepShow">
            <div class="read_display">
                <div class="header no-size u-flex">
                    <div class="error shou u-flex u-row-center" @click.stop="backGo">
                        ×
                    </div>
                </div>
                <div class="u-flex">
                    <div class="left no-size">
                        <div class="name" v-text="userInfo.name"></div>
                        <div class="u-flex">
                            <div class="grey_left">会員ID</div>
                            <div class="black_right" v-text="userInfo.member_id"></div>
                        </div>
                        <div class="u-flex one_bottom">
                            <div class="grey_left">顧客NO</div>
                            <div class="black_right" v-text="userInfo.member_no"></div>
                        </div>
                        <div class="u-flex">
                            <div class="grey_back">有効期限</div>
                            <div class="black_right" v-text="userInfo.is_valid == 1? cardInfo.expire_time : 'なし'"></div>
                        </div>
                        <div class="u-flex u-col-top two_bottom">
                            <div class="grey_back">備　考</div>
                            <div class="black_right" style="white-space: pre-wrap;" v-text="cardInfo.remark"></div>
                        </div>
                        <div v-if="card == 'chapter' && userInfo.orders.length > 0">
                            <div class="btn blue_back">予 約</div>
                            <div class="u-flex u-col-top three_bottom" v-for="(item,index) in userInfo.orders"
                                :key="index" style="margin-top: 12px; font-size: 14px">
                                <div style="width: 40px; text-align: center">
                                    <i class="el-icon-circle-check" style="color: #aaaaaa; font-size: 16px"></i>
                                </div>
                                <div class="u-flex-col" style="margin-right: 30px">
                                    <div>{{item.date}}({{item.week}})　{{item.start}}〜{{item.end}}</div>
                                    <div style="margin-top: 5px" v-text="item.menu_name"></div>
                                </div>
                                <div class="u-flex">
                                    <img src="@/static/index/record3.png" style="width: 22px; margin-right: 8px"
                                        alt="" />
                                    {{item.admin_name}}
                                </div>
                            </div>
                        </div>
                        <div class="btn red_back">特典</div>
                        <div style="margin-top: 20px;">
                            <el-carousel height="223px">
                                <el-carousel-item v-for="item in cardInfo.discount_images" :key="item">
                                    <img :src="item" style="width: 100%;height:100%;object-fit: cover;" alt="">
                                </el-carousel-item>
                            </el-carousel>
                        </div>
                        <div class="red_text" style="white-space: pre-wrap;" v-text="userInfo.card.discount_content">
                        </div>
                    </div>
                    <div class="right" v-if="userInfo.card.card_type == 1">
                        <scrollbar-container ref="scrollbarRef" height="409px" class="scroll-container">
                            <div class="scroll no-size">
                                <div class="tit_right" v-text="userInfo.card.card_name"></div>
                                <div class="cardY" :style="`background:${cardInfo.background_color}`">
                                    <div class="cardInner">
                                        <div class="seal u-flex u-row-center"
                                            v-for="(item, index) in userInfo.card.count" :key="index">
                                            <div class="day">{{ index + 1 }}</div>

                                            <div class="sealed" v-if="userInfo.card.seals[index]">
                                                <div class="admin" v-if="userInfo.card.seals[index].admin_name"
                                                    v-text="userInfo.card.seals[index].admin_name"></div>
                                                <img src="@/static/svg/sv_01.svg"
                                                    v-if="userInfo.card.seals[index].seal_icon.includes('sv_01')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_02.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_02')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_03.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_03')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_04.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_04')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_05.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_05')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_06.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_06')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_07.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_07')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_08.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_08')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img :src="userInfo.card.seals[index].seal_icon" v-else />
                                                <div class="sealTime"
                                                    v-text="timestampToTime(userInfo.card.seals[index].time)"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </scrollbar-container>
                        <div class="right_bottom">
                            <div class="num">スタンプ付与数</div>
                            <div class="u-flex u-row-between center">
                                <img src="@/static/index/reduce.svg" @click.stop="reduce" class="repl" alt="" />
                                <div class="number" :style="number < 0?'color:#d93025' : ''">{{ number }}</div>
                                <img src="@/static/index/plus.svg" @click.stop="plus" alt="" class="repl" />
                            </div>
                            <div class="blue_btn u-flex u-row-center shou" @click="addSeals">決　定</div>
                        </div>
                    </div>
                    <div class="right" v-else-if="userInfo.card.card_type == 2">
                        <scrollbar-container ref="scrollbarRef" height="409px" class="scroll-container">
                            <div class="scroll no-size">
                                <div class="tit_right" v-text="userInfo.card.card_name"></div>
                                <div class="cardY":style="`background:${cardInfo.background_color}`">
                                    <div class="cardInner" style="display: block;">
                                        <div class="sealTitle">
                                            <div class="st1">NO</div>
                                            <div class="st2">日付</div>
                                            <div class="st3">
                                                <img src="../static/corent.png" alt="">
                                            </div>
                                            <div class="st4" style="width: 25%;">
                                                <img src="../static/userAdmin.png" alt="">
                                            </div>
                                            <div class="st5" style="width: 15%;"></div>
                                        </div>
                                        <div class="u-flex u-row-center seals" v-for="(item, index) in userInfo.seals"
                                            style="position: relative;font-size: 14px;">
                                            <div class="sti1" v-text="index + 1"></div>
                                            <div class="sti2" v-text="item.date"></div>
                                            <div class="sti3" v-text="item.time"></div>
                                            <div class="sti4" style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis" v-text="item.admin_name"></div>
                                            <div class="sti5" v-if="userInfo.card.seals.length == 0" style="width: 15%;align-items: center;"> </div>
                                            <div class="sti5" v-if="userInfo.card.seals.length > 0" style="width: 15%;align-items: center;">
                                                <img src="@/static/svg/sv_01.svg"
                                                    v-if="userInfo.card.seals[index].seal_icon.includes('sv_01')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_02.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_02')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_03.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_03')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_04.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_04')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_05.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_05')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_06.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_06')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_07.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_07')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img src="@/static/svg/sv_08.svg"
                                                    v-else-if="userInfo.card.seals[index].seal_icon.includes('sv_08')"
                                                    :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[index].seal_color})`"
                                                    alt="" />
                                                <img :src="diyImage" v-else alt=""  :style="`transform: translate(-10000px);filter: drop-shadow(10000px 0 0 ${userInfo.card.seals[0].seal_color})`"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </scrollbar-container>
                        <div class="right_bottom">
                            <div class="num">スタンプ付与数</div>
                            <div class="u-flex u-row-between center">
                                <img src="@/static/index/reduce.svg" @click.stop="reduce" class="repl" alt="" />
                                <div class="number" :style="number < 0?'color:#D93025':'' ">{{ number }}</div>
                                <img src="@/static/index/plus.svg" @click.stop="plus" alt="" class="repl" />
                            </div>
                            <div class="blue_btn u-flex u-row-center shou" @click="addSeals">決　定</div>
                        </div>
                    </div>
                    <div class="right" v-else>
                        <div class="dot_top">
                            <div class="dot_tit">{{cardInfo.card_name}}</div>
                            <img src="@/static/index/ITF.png" alt="" />
                            <div class="code_num font_color">{{userInfo.card.member_no}}</div>
                            <div class="u-flex u-col-bottom u-row-center pt">
                                <div class="font_color">保有ポイント数</div>
                                <div class="pink">{{userInfo.point}} pt</div>
                            </div>
                        </div>
                        <div class="dot_bottom">
                            <div class="mask" v-if="userInfo.card.is_point != 1"></div>
                            <div>
                                <div class="u-flex u-col-top">
                                    <div class="radius shou" :class="{ radiusCss: dotShow }" @click.stop="dotClick"
                                        style="margin-top: 3px">
                                        <div class="blue_dot" v-if="dotShow"></div>
                                    </div>
                                    <div class="u-flex-col">
                                        <div style="font-size: 14px" :class="[dotShow ? 'text_black' : 'text_grey']">
                                            ポイントを付与する
                                        </div>
                                        <div class="u-flex m-t-10" style="font-size: 12px; color: #707070">
                                            <div class="text_pink">{{userInfo.card.amount}}円</div>
                                            で
                                            <div class="text_pink">{{userInfo.card.point}}ポイント</div>
                                            を付与
                                        </div>
                                    </div>
                                </div>
                                <div class="money u-flex u-row-center" :class="[dotShow ? 'text_black' : 'text_grey']">
                                    金額
                                    <input class="int no-size" :disabled="!dotShow" type="number" v-model="money" />円
                                </div>
                            </div>

                            <div class="u-flex">
                                <div class="radius shou" :class="{ radiusCss: !dotShow }" @click.stop="dotClick">
                                    <div class="blue_dot" v-if="!dotShow"></div>
                                </div>
                                <div style="font-size: 14px" :class="[!dotShow ? 'text_black' : 'text_grey']">
                                    ポイントを使う
                                </div>
                            </div>
                            <div class="money no-size u-flex u-row-right"
                                :class="[!dotShow ? 'text_black' : 'text_grey']" style="padding: 17px 42px 20px 0">
                                <input class="int no-size" :disabled="dotShow" type="number" v-model="reduceMoney" />
                                ポイント
                            </div>
                        </div>
                        <div class="u-flex u-row-center u-col-center" style="
                border-top: 1px solid #e6e6e9;
                height: 86px;
                width: 90%;
                margin: 0 auto;
                position: relative;
              ">
                            <div class="blue_btn u-flex u-row-center" @click="addPoints">決　定</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 更新小弹窗 -->
        <div class="fixed" v-show="newShow">
            <div class="new no-size">
                <div class="new_one">
                    {{
            state == "new"
              ? "スタンプが満了しました"
              : "GOLD(メンバーズカード名)"
          }}
                </div>
                <div class="new_two">
                    {{ state == "new" ? "カードを更新して下さい" : "に変更しますか？" }}
                </div>
                <div class="new_three shou" @click="clearseal">
                    {{ state == "new" ? "このカードを更新" : "変更する" }}
                </div>
                <div class="new_four shou" @click="cardShow = true,newShow = false,keepShow = false">
                    {{ state == "new" ? "カードの種類を変更" : "キャンセル" }}
                </div>
            </div>
        </div>
        <!-- 来店履历 -->
        <div class="fixed" v-show="query.type == 'list' && resumeShow && comeList.length > 0">
            <div class="resume read_display"
                style="width: 470px;height: 670px;box-shadow: 0px 10px 10px 1px rgba(0,0,0,0.35);">
                <div class="header no-size u-flex">
                    <div class="error shou u-flex u-row-center" @click.stop="backGo">
                        ×
                    </div>
                    <div class="resume_tit">来店履歴</div>
                </div>
                <scrollbar-container style="box-sizing: border-box;padding: 0 20px;" ref="scrollbarRef" height="630px"
                    class="scroll-container">
                    <div class="resume_wrap">
                        <!-- [カスタムエイド 2026-10-16] 来店履歴の編集(メニュー・担当者)。編集中の行は青枠、ほかの行は薄くして押せなくする -->
                        <div class="comeitem" v-for="item,index in comeList" :key="item.id"
                            :class="{'cs-come-editing': editingId == item.id, 'cs-come-dim': editingId && editingId != item.id, 'cs-come-saved': savedId == item.id}">
                            <div class="cs-come-act" v-if="editingId != item.id">
                                <span class="cs-come-edit" @click="startEditCome(item)">編集</span>
                                <span class="cs-come-del" @click="deleteItem(item.id)">削除</span>
                            </div>
                            <div class="c1">
                                <div class="cleft">
                                    <img src="../static/svg/correct.svg" alt="" />
                                </div>
                                <div class="cright">
                                    <div v-text="item.date" style="margin-right: 10px;"></div>
                                    <div class="cl" v-for="ite,idx in item.times" v-text="ite"></div>
                                </div>
                            </div>
                            <div class="c1">
                                <div class="cleft">
                                    <img src="../static/svg/menu.svg" alt="" />
                                </div>
                                <div class="cright" v-if="editingId != item.id">
                                    <div v-if="item.menu_names.length > 0" v-for="it,idx in item.menu_names"
                                        v-text="it"></div>
                                    <div v-if="item.menu_names.length == 0">--</div>
                                </div>
                                <!-- [カスタムエイド 2026-10-16] 編集中:メニューは札(×で外す)+「メニューを選択」。今は登録のない名前は灰色・×なし(押すと入れ替え) -->
                                <div class="cright cs-come-field" v-else>
                                    <span class="cs-come-label">メニュー</span>
                                    <span class="cs-come-none" v-if="editMenus.length == 0">未設定</span>
                                    <span class="cs-chip" :class="{'cs-chip-legacy': m.legacy}" v-for="m,mi in editMenus"
                                        :key="'m' + mi" @click="m.legacy ? openMenuPicker(mi) : null">
                                        {{ m.legacy ? m.title + '(現在は登録なし)' : m.title }}
                                        <span class="cs-chip-x" v-if="!m.legacy" @click.stop="removeEditMenu(mi)">×</span>
                                    </span>
                                    <span class="cs-chip-add" @click="openMenuPicker(null)">メニューを選択</span>
                                </div>
                            </div>
                            <div class="c1">
                                <div class="cleft">
                                    <img src="../static/svg/user.svg" alt="" />
                                </div>
                                <div class="cright" v-if="editingId != item.id">
                                    <div v-for="it,idx in item.day_sadmin_names" v-if="item.day_sadmin_names.length > 0"
                                        v-text="it"></div>
                                    <div v-if="item.day_sadmin_names.length == 0">--</div>
                                </div>
                                <!-- [カスタムエイド 2026-10-16] 編集中:担当者は選択欄(予約に対応するスタッフだけ)。今は登録のない名前は「未設定」に戻せず、入れ替えだけできる -->
                                <div class="cright cs-come-field" v-else>
                                    <span class="cs-come-label">担当者</span>
                                    <select class="cs-come-select" v-for="s,si in editStaffs" :key="'s' + si" v-model="editStaffs[si]">
                                        <option v-if="!isKeepStaff(s)" :value="0">未設定(--)</option>
                                        <option v-if="isKeepStaff(s)" :value="s">{{ s.slice(5) }}(現在は登録なし)</option>
                                        <option v-for="a in comeOptions.admins" :key="a.id" :value="a.id">{{ a.name }}</option>
                                    </select>
                                </div>
                            </div>
                           <div class="c1" v-if="item.point.length > 0">
                                <div class="cleft">
                                    <img src="../static/svg/p.svg" alt="" v-if="item.point[0].card == 'p'" />
                                    <img src="../static/svg/s.svg" alt="" v-else />
                                </div>
                                <div class="cright">
                                    <div v-for="it,idx in item.point">
                                        <div class="count" style="color: #1d1d1f;min-width: 20px;text-align: center;"
                                            v-text="it.point" v-if="it.point > 0"></div>
                                        <div class="count" style="min-width: 20px;text-align: center;" v-text="it.point"
                                            v-else></div>
                                    </div>
                                </div>
                            </div>
                            <div class="c1">
                                <div class="cleft">
                                    <img src="../static/svg/coupon.svg" alt="" />
                                </div>
                                <div class="cright">
                                    <div v-text="it" v-for="it,idx in item.coupon_names"
                                        v-if="item.coupon_names.length > 0"></div>
                                    <div v-if="item.coupon_names.length == 0">--</div>
                                </div>
                            </div>
                            <!-- [カスタムエイド 2026-10-16] 編集中の保存・キャンセル -->
                            <div class="cs-come-btns" v-if="editingId == item.id">
                                <button type="button" class="cs-btn-cancel" @click="cancelEditCome">キャンセル</button>
                                <button type="button" class="cs-btn-save" :disabled="comeSaving" @click="saveEditCome(item)">保存</button>
                            </div>
                        </div>
                    </div>
                </scrollbar-container>
            </div>
            <!-- [カスタムエイド 2026-10-16] 来店履歴の編集:メニューを選択(複数可。追加・外す・入れ替えをまとめて行う) -->
            <div class="cs-pick-mask" v-if="menuPickerShow" @click.self="menuPickerShow = false">
                <div class="cs-pick" role="dialog" aria-label="メニューを選択">
                    <div class="cs-pick-tit">メニューを選択</div>
                    <div class="cs-pick-hint">複数選べます。外すときはチェックを外してください</div>
                    <div class="cs-pick-list">
                        <label class="cs-pick-opt" v-for="m in comeOptions.menus" :key="m.id">
                            <input type="checkbox" :value="m.id" v-model="menuPickerChecked">
                            <span>{{ m.title }}</span>
                        </label>
                        <div class="cs-pick-empty" v-if="comeOptions.menus.length == 0">登録されているメニューがありません</div>
                    </div>
                    <div class="cs-pick-btns">
                        <button type="button" class="cs-btn-cancel" @click="menuPickerShow = false">キャンセル</button>
                        <button type="button" class="cs-btn-save" @click="applyMenuPicker">決定</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- 卡片一览表 -->
        <div class="fixed" v-show="cardShow && query.type != 'list'">
            <div class="card no-size">
                <div class="header no-size u-flex">
                    <div class="error shou u-flex u-row-center" @click.stop="backGo">
                        ×
                    </div>
                    <div class="resume_tit">カード一覧</div>
                </div>
                <div style="padding: 10px 30px 10px;height: 600px;overflow-y: scroll;">
                    <div class="card_item no-size u-flex u-row-between" v-for="(item, index) in cardList" :key="index"
                        :class="[cardIndex == index ? 'itemCss' : 'itemGrey']" @click="toDetail(index,item.id)">
                        <div class="u-flex">
                            <div class="box shou u-flex u-row-center" :class="{ boxCss: cardIndex == index }"
                                @click.stop="cardClick(item.name,item.id)">
                                <i class="el-icon-check" style="color: #fff"></i>
                            </div>
                            <div class="grey_bor u-flex-col u-col-center u-row-center">
                                <img :src="item.image" style="width: 100%;height: 100%;object-fit: cover;" alt="">
                            </div>
                            <div class="card_text u-flex-col">
                                <div style="font-size: 16px; color: #1d1d1f; font-weight: bold"
                                    v-text="`${item.shop_name}${item.name}`"></div>
                                <div style="margin: 5px 0" v-if="item.type != 3">スタンプ数 : {{item.count}}個</div>
                                <div style="margin: 5px 0" v-if="item.type == 3">ポイント数 : <span
                                        v-if="item.is_point == 0">-</span><span
                                        v-if="item.is_point == 1">{{item.amount}}円で{{item.point}}ポイント</span></div>
                                <div>有効期限：{{item.expire_time?item.expire_time : 'なし'}}</div>
                                <div class="u-flex m-t-10" v-if="item.background_color">
                                    カード背景色
                                    <div class="card_bg" :style="`background:${item.background_color}`"></div>
                                </div>
                                <div class="u-flex m-t-10" v-else>
                                    カード背景色:
                                    <div>-</div>
                                </div>
                            </div>
                        </div>
                        <div class="utilize u-flex u-row-center" v-if="cardIndex == index">
                            利用中
                        </div>
                    </div>
                    <div style="25px" </div>
                    </div>
                </div>
                <div class="masks" v-if="showmask == true">
                    <div class="maskContent">
                        <div class="maskTitle">
                            {{cardName}}<br />にカードを変更しますか？
                        </div>
                        <div class="masksubtitle">
                            変更すると、現在ご利用中のカードのスタンプ数・ポイント数はリセットされます。
                        </div>
                        <div class="cfm" @click="changedefault">
                            変更する
                        </div>
                        <div class="cle" @click="showmask = false">
                            キャンセル
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>

<script>
    import ScrollbarContainer from "@/components/scrollContainer.vue";
    import {
        getCardList,
        getMemberDetail,
        setdefaultCard,
        addSeals,
        getComeList,
        getConfig,
        deleteAppointment,
        deleteAppointOrders,
        getComeOptions, // [カスタムエイド 2026-10-16] 来店履歴の編集
        updateCome // [カスタムエイド 2026-10-16] 来店履歴の編集
    } from '@/http/api.js'
    export default {
        components: {
            ScrollbarContainer,
        },
        mounted() {
            // *注：demo更新需要更新容器
            this.$nextTick(() => {
                this.$refs.scrollbarRef.updateScroll();
            });
            getConfig().then((res) => {
                console.log("config", res)
            })
        },
        data() {
            return {
                cardInfo: {},
                radShow: false,
                withShow: false,
                oneShow: false,
                twoShow: false,
                cardName: "",
                shengChangeShow: false,
                sexChangeShow: false,
                reduceMoney: "",
                money: "",
                cishu: "",
                showmask: false,
                number: "1",
                keepShow: false, //印章卡
                card: "chapter", //印章卡QR读取显示chapter ,点卡QR读取显示dot
                newShow: false,
                state: "change", //使用卡变更注意change,卡更新new
                dotShow: true,
                resumeShow: true, //来店履历
                // [カスタムエイド 2026-10-16] 来店履歴の編集
                editingId: null, // 編集中の来店履歴のid
                editMenus: [], // 編集中のメニュー [{id, title, legacy}]。legacy=今は登録のない名前
                editStaffs: [0], // 編集中の担当者(スタッフid、0=未設定、'keep:名前'=今は登録のない名前)
                comeOptions: { menus: [], admins: [] }, // 選べるメニュー・スタッフ
                comeOptionsLoaded: false,
                menuPickerShow: false,
                menuPickerChecked: [],
                menuReplaceIndex: null, // 今は登録のない名前の札を押して開いたとき、その札の位置
                comeSaving: false,
                savedId: null, // 保存直後に薄い緑にする行
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
                comeList: [
                    {
                        menu_names:[],
                        day_sadmin_names:[],
                        point:[
                            {
                                card:""
                            }
                        ]
                    }
                ],
                total: 0,
                id: "",
                name: ""
            };
        },
        created() {
            let that = this
            let query = that.$route.query
            console.log(query)
            that.state = 'new'
            that.query = query
            if (query.show == 1) {
                that.keepShow = true
                that.cardShow = false
                
            }
            that.getCardList()
            that.getComeList()
        },

        methods: {
            changedefault() {
                let that = this
                let data = {}
                data['mid'] = that.userInfo.id
                data['cid'] = that.id
                setdefaultCard(data).then((res) => {
                    that.showmask = false
                    if (res.code == 200) {
                        that.$message({
                            message: res.message,
                            type: 'success',
                            offset: 400
                        });
                        that.getCardList()

                    } else {
                        that.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });
                    }
                })
            },
            getComeList() {
                let that = this
                let data = {}
                data['date'] = "2022-12-19"
                data['id'] = that.$route.query.id
                getComeList(data).then((res) => {
                    console.log("order",res)
                    if (res.code == 200) {
                        that.comeList = res.data.orders
                        that.total = res.data.total
                    }
                })
            },
            deleteItem(id) {
                let that = this
                deleteAppointOrders(id).then((res) => {
                    if (res.code == 200) {
                        that.$message({
                            message: res.message,
                            type: 'success',
                            offset: 400
                        });
                        that.editingId = null // [カスタムエイド 2026-10-16] 削除したら編集も終える
                        that.getComeList()
                    } else {
                        that.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });
                    }
                })
            },
            // [カスタムエイド 2026-10-16] 来店履歴の編集 ここから
            isKeepStaff(v) {
                return typeof v === 'string' && v.indexOf('keep:') === 0
            },
            startEditCome(item) {
                let that = this
                let begin = () => {
                    that.editMenus = (item.menu_names || []).filter((n) => n !== '' && n !== null).map((n) => {
                        let m = that.comeOptions.menus.find((x) => x.title === n)
                        return m ? { id: m.id, title: m.title, legacy: false } : { id: null, title: n, legacy: true }
                    })
                    let staffs = (item.day_sadmin_names || []).filter((n) => n !== '' && n !== null).map((n) => {
                        let a = that.comeOptions.admins.find((x) => x.name === n)
                        return a ? a.id : 'keep:' + n
                    })
                    that.editStaffs = staffs.length > 0 ? staffs : [0]
                    that.editingId = item.id
                    that.$nextTick(() => {
                        if (that.$refs.scrollbarRef) that.$refs.scrollbarRef.updateScroll()
                    })
                }
                if (that.comeOptionsLoaded) {
                    begin()
                    return
                }
                getComeOptions().then((res) => {
                    if (res && res.code == 200) {
                        that.comeOptions = { menus: res.data.menus || [], admins: res.data.admins || [] }
                        that.comeOptionsLoaded = true
                        begin()
                    } else {
                        that.$message({ message: (res && res.message) || '読み込みに失敗しました', type: 'error', offset: 400 })
                    }
                }).catch(() => {
                    that.$message({ message: '読み込みに失敗しました', type: 'error', offset: 400 })
                })
            },
            cancelEditCome() {
                this.editingId = null
                this.menuPickerShow = false
                this.$nextTick(() => {
                    if (this.$refs.scrollbarRef) this.$refs.scrollbarRef.updateScroll()
                })
            },
            removeEditMenu(mi) {
                this.editMenus.splice(mi, 1)
            },
            openMenuPicker(replaceIndex) {
                this.menuReplaceIndex = replaceIndex
                this.menuPickerChecked = this.editMenus.filter((m) => !m.legacy).map((m) => m.id)
                this.menuPickerShow = true
            },
            applyMenuPicker() {
                let that = this
                let checked = that.menuPickerChecked
                let before = that.editMenus.filter((m) => !m.legacy).map((m) => m.id)
                let legacy = that.editMenus.filter((m) => m.legacy)
                let added = checked.filter((id) => before.indexOf(id) < 0)
                // 今は登録のない名前の札から開いて、新しいメニューを選んだら、その札と入れ替える
                if (that.menuReplaceIndex !== null && added.length > 0) {
                    let target = that.editMenus[that.menuReplaceIndex]
                    legacy = legacy.filter((m) => m !== target)
                }
                // 並び順:残っている今までのもの → 新しく選んだもの(選んだ順)
                let ids = before.filter((id) => checked.indexOf(id) >= 0).concat(added)
                that.editMenus = legacy.concat(ids.map((id) => {
                    let m = that.comeOptions.menus.find((x) => x.id === id)
                    return { id: id, title: m ? m.title : '', legacy: false }
                }))
                that.menuPickerShow = false
            },
            saveEditCome(item) {
                let that = this
                if (that.comeSaving) return
                let data = {
                    menu_ids: that.editMenus.filter((m) => !m.legacy).map((m) => m.id),
                    keep_menu_names: that.editMenus.filter((m) => m.legacy).map((m) => m.title),
                    admin_ids: that.editStaffs.filter((v) => typeof v === 'number' && v > 0),
                    keep_admin_names: that.editStaffs.filter((v) => that.isKeepStaff(v)).map((v) => v.slice(5))
                }
                that.comeSaving = true
                updateCome(item.id, data).then((res) => {
                    that.comeSaving = false
                    if (res && res.code == 200) {
                        item.menu_names = res.data.menu_names
                        item.day_sadmin_names = res.data.day_sadmin_names
                        that.editingId = null
                        that.savedId = item.id
                        setTimeout(() => {
                            if (that.savedId === item.id) that.savedId = null
                        }, 2000)
                        that.$message({ message: '保存しました', type: 'success', offset: 400 })
                        that.$nextTick(() => {
                            if (that.$refs.scrollbarRef) that.$refs.scrollbarRef.updateScroll()
                        })
                    } else {
                        that.$message({ message: (res && res.message) || '保存できませんでした', type: 'error', offset: 400 })
                    }
                }).catch(() => {
                    that.comeSaving = false
                    that.$message({ message: '保存できませんでした', type: 'error', offset: 400 })
                })
            },
            // [カスタムエイド 2026-10-16] 来店履歴の編集 ここまで
            cardClick(index, id) {
                let that = this
                that.id = id
                that.cardName = index
                that.showmask = true
            },
            radClick() {
                this.radShow = !this.radShow;
            },
            addSeals() {
                let that = this
                // that.newShow = true
                console.log(that.money)
                if (that.number != 0) {
                    that.addSealsConfirm()
                }

            },
            addPoints() {
                let that = this
                // that.newShow = true
                console.log(that.userInfo.card.card_type)
                that.addSealsConfirm()
            },
            clearseal() {
                let that = this
                let data = {}
                data['mid'] = that.userInfo.id
                data['plus'] = 0
                addSeals(data).then((res) => {
                    console.log(res)
                    if (res.code == 200) {
                        that.$message({
                            message: res.message,
                            type: 'success',
                            offset: 400
                        });
                        that.getCardList()
                    } else {
                        that.$message({
                            message: res.message,
                            type: 'error',
                            offset: 400
                        });

                    }
                    that.newShow = false
                })
            },
            addSealsConfirm() {
                let that = this
                // that.newShow = true
                let data = {}
                if (that.userInfo.card.card_type == 3) {
                    data['mid'] = that.userInfo.id
                    if(that.userInfo.card.is_point != 1){
                         data['plus'] = 0
                    }else{
                         data['plus'] = that.money
                    }
                    addSeals(data).then((res) => {
                        console.log(res)
                        if (res.code == 200) {
                            that.$message({
                                message: res.message,
                                type: 'success',
                                offset: 400
                            });
                        } else {
                            that.$message({
                                message: res.message,
                                type: 'error',
                                offset: 400
                            });
                        }
                    })
                } else {
                    data['mid'] = that.userInfo.id
                    data['plus'] = that.number
                    addSeals(data).then((res) => {
                        console.log("num1",that.userInfo.card.count)
                        console.log("num2",that.userInfo.card.seals.length)
                        if (res.code == 200) {
                            that.$message({
                                message: res.message,
                                type: 'success',
                                offset: 400
                            });
                            
                            console.log("111",that.userInfo.card.count - that.userInfo.card.seals.length)
                            console.log("222",that.userInfo.card.seals.length)
                            
                            
                            that.getCardList()
                        }else if(res.code == 888){
                              that.newShow = true
                        } else {
                            that.$message({
                                message: res.message,
                                type: 'error',
                                offset: 400
                            });
                        }
                    })
                }
            },
            getCardList() {
                let that = this
                getCardList(" ").then((res) => {
                    if (res.code == 200) {
                        that.cardList = res.data.cards
                        getMemberDetail(that.$route.query.id).then((result) => {
                            if (result.code == 200) {
                                console.log("result", result)
                                res.data.cards.forEach((value, index) => {
                                    if (value.id == result.data.cid) {
                                        that.cardIndex = index
                                        result.data.card.seals.forEach((value, index) => {
                                            if (value.seal_icon.indexOf('static/svg') !=
                                                -1) {
                                                let temp = value.seal_icon.split(
                                                    "static/")
                                                let images = "@/static/" + temp[1]
                                                value.seal_icon = images
                                                console.log(value.seal_icon)
                                            }
                                        })
                                        that.userInfo = result.data
                                        that.cardInfo = result.data.card
                                       if(that.userInfo.card.card_type != 3){
                                           if(that.userInfo.card.count - that.userInfo.card.seals.length  <= 0){
                                               that.newShow = true
                                           }
                                       }
                                    }
                                })
                            }
                        })
                    }
                })
            },
            withClick() {
                this.withShow = !this.withShow;
            },
            oneClick() {
                this.oneShow = !this.oneShow;
            },
            twoClick() {
                this.twoShow = !this.twoShow;
            },
            Check(i) {
                this.options[i].checked = true;
                this.change(this.options, 1);
            },
            quChecked(i) {
                this.options[i].checked = false;
                this.change(this.options, 1);
            },
            sexCheck(i) {
                this.sexOptions[i].checked = true;
                this.change(this.sexOptions, 2);
            },
            sexquChecked(i) {
                this.sexOptions[i].checked = false;
                this.change(this.sexOptions, 2);
            },
            change(it, dat) {
                let arr = [];
                it.forEach((item) => {
                    if (item.checked) {
                        arr.push(item.label);
                        console.log(arr);
                    }
                });
                if (dat == 1) {
                    this.dataMonth = arr + "";
                } else {
                    this.sex = arr + "";
                }
            },
            reduce() {
                this.number--
            },
            timestampToTime(timestamp) {
                var date = new Date(timestamp * 1000); //时间戳为10位需*1000，时间戳为13位的话不需乘1000
                var Y = date.getFullYear() + '/';
                var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1) + '/';
                var D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();
                return Y + M + D;
            },
            plus() {
                this.number++;
            },
            backGo() {
                history.go(-1)
            },
            toDetail(index, id) {
                let that = this
                if (that.cardIndex == index) {
                    that.getCardList()
                    that.cardShow = false
                    that.keepShow = true
                }
            },
            dotClick() {
                this.dotShow = !this.dotShow;
            },
            numberClick(index, item) {
                this.num = item;
                this.numIndex = index;
                this.limitShow = false;
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
    .masks {
        width: 100vw;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background: rgba(29, 29, 31, .3);
        z-index: 199 !important;

    }

    .maskTitle {
        width: 100%;
        text-align: center;
        font-family: Hiragino Sans, Hiragino Sans;
        font-size: 16px;
        color: #1D1D1F;
        box-sizing: border-box;
        padding: 20px 0;
        font-weight: bold;
    }

    .masksubtitle {
        font-family: Hiragino Sans, Hiragino Sans;
        font-size: 14px;
        color: #1D1D1F;
        text-align: center;
        width: 100%;
        padding-bottom: 20px;
        border-bottom: 1px solid #86868B;
        box-sizing: border-box;
        padding: 0 20px 20px;
    }

    .cfm {
        font-family: Hiragino Sans, Hiragino Sans;
        font-size: 16px;
        color: #007AFF;
        text-align: center;
        border-bottom: 1px solid #86868B;
        padding: 20px 0;
        cursor: pointer;
    }

    .cle {
        font-family: Hiragino Sans, Hiragino Sans;
        font-size: 16px;
        color: #D93025;
        text-align: center;
        padding: 20px 0;
        cursor: pointer;
    }

    .maskContent {
        background: #fff;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 325px;
        border-radius: 10px;
    }

    .repl:hover {
        opacity: 0.6 !important;
        cursor: pointer;
    }

    .scroll-wrap {
        overflow-y: auto !important;
    }

    .mask {
        position: absolute;
        left: 0;
        width: 100%;
        top: 0;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        z-index: 7;
    }

    .seals {
        height: 28px;
        border-bottom: 1px dotted #d2d2d7;
        display: flex;
        align-items: center;
        width: 100%;
        text-align: center;

        .sti1 {
            width: 10%;
        }

        .sti2 {
            width: 30%;
        }

        .sti3 {
            width: 20%;
        }

        .sti4 {
            width: 20%;

        }

        .sti5 {
            width: 20%;
            display: flex;
            flex-direction: column;
            justify-content: center;

            img {
                width: 20px;
                height: 20px;
            }
        }


    }

    .sealTitle {
        display: flex;
        align-items: center;
        width: 100%;
        text-align: center;
        font-size: 14px;
        // justify-content: space-between;

        .st1 {
            width: 10%;
        }

        .st2 {
            width: 30%;
        }

        .st3 {
            width: 20%;

            img {
                width: 20px;
                height: 20px;
            }
        }

        .st4 {
            width: 20%;

            img {
                width: 20px;
                height: 20px;
            }
        }

        .st5 {
            width: 20%;
        }

    }

    .cl {
        margin: 0 5px;
    }

    .del {
        position: absolute;
        bottom: 20px;
        right: 12px;
        font-family: Hiragino Sans, Hiragino Sans;
        font-weight: normal;
        font-size: 14px;
        color: #D93025;
        cursor: pointer;
    }

    .count {
        color: #D93025;
    }

    .comeitem {
        border-bottom: 1px solid #d2d2d7;
        padding: 10px 0;
        position: relative;

        .c1 {
            margin: 10px 0;
            display: flex;

            .cright {
                flex: 1;
                margin-left: 10px;
                font-size: 14px;
                display: flex;
                align-items: center;
                font-family: Hiragino Sans, Hiragino Sans;
                font-weight: normal;
                font-size: 14px;
                color: #1D1D1F;
                flex-wrap: wrap;
            }
        }
    }

    // [カスタムエイド 2026-10-16] 来店履歴の編集 ここから
    .cs-come-act {
        position: absolute; // 今の「削除」と同じ位置(行の右下)
        bottom: 20px;
        right: 12px;
        display: flex;
        gap: 18px; // 「編集」と「削除」の間隔
        font-size: 14px;
    }
    .cs-come-edit {
        color: #1F6FE5; // 編集は青文字
        cursor: pointer;
    }
    .cs-come-del {
        color: #D93025; // 削除は今までどおり赤文字
        cursor: pointer;
    }
    .comeitem.cs-come-editing {
        background: #EEF4FD; // 編集中の行は薄い青
        border: 2px solid #1F6FE5; // 青い枠で編集中だと分かるようにする
        border-radius: 8px;
        padding: 10px 12px;
        margin: 8px 0;
    }
    .comeitem.cs-come-dim {
        opacity: 0.45; // 編集中は、ほかの行を薄くする
        pointer-events: none; // ほかの行の編集・削除を押せないようにする
    }
    .comeitem.cs-come-saved {
        background: #EEF8F0; // 保存した行を少しの間だけ薄い緑にする
        transition: background 0.6s;
    }
    .cs-come-field {
        gap: 6px; // 札どうしの間隔
    }
    .cs-come-label {
        width: 100%; // 項目名を1行使って札の上に置く
        font-size: 12px;
        color: #3A5A8C;
        font-weight: bold;
    }
    .cs-come-none {
        color: #8E8E93; // 「未設定」は灰色
        margin-right: 4px;
    }
    .cs-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        height: 30px;
        padding: 0 4px 0 12px; // ×の右側は詰める
        border-radius: 15px;
        background: #FFFFFF;
        border: 1px solid #9BB8E8;
        font-size: 13px;
    }
    .cs-chip-legacy {
        padding: 0 12px; // ×がないので左右同じ余白
        background: #F2F2F7; // 今は登録のない名前は灰色
        border-color: #D1D1D6;
        color: #6E6E73;
        cursor: pointer; // 押すと一覧が開き、入れ替えられる
    }
    .cs-chip-x {
        width: 22px;
        height: 22px;
        border-radius: 11px;
        background: #E3E8F0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        cursor: pointer;
    }
    .cs-chip-add {
        display: inline-flex;
        align-items: center;
        height: 30px;
        padding: 0 12px;
        border-radius: 15px;
        border: 1.5px dashed #1F6FE5; // 点線で「押すと選べる」ことを示す
        color: #1F6FE5;
        background: #FFFFFF;
        font-size: 13px;
        cursor: pointer;
    }
    .cs-come-select {
        height: 34px;
        min-width: 200px;
        padding: 0 8px;
        border: 1px solid #9BB8E8;
        border-radius: 6px;
        background: #FFFFFF;
        font-size: 14px;
        color: #1D1D1F;
        margin: 0 6px 6px 0; // 担当者が2人いる日は選択欄を並べる
    }
    .cs-come-btns {
        display: flex;
        justify-content: flex-end; // 右寄せ
        gap: 10px;
        margin-top: 8px;
    }
    .cs-btn-cancel,
    .cs-btn-save {
        height: 34px;
        padding: 0 20px;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
    }
    .cs-btn-cancel {
        background: #E5E5EA; // キャンセルは灰色
        color: #1D1D1F;
    }
    .cs-btn-save {
        background: #1F6FE5; // 保存・決定は青
        color: #FFFFFF;
        font-weight: bold;
        &:disabled {
            opacity: 0.6; // 保存中は二重に押せない
            cursor: default;
        }
    }
    .cs-pick-mask {
        position: fixed; // 画面全体を薄暗くして、その上に一覧を出す
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.35);
        z-index: 3000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cs-pick {
        width: 360px;
        background: #FFFFFF;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    }
    .cs-pick-tit {
        height: 48px;
        line-height: 48px;
        text-align: center;
        font-size: 15px;
        font-weight: bold;
        border-bottom: 1px solid #D2D2D7;
    }
    .cs-pick-hint {
        padding: 8px 16px 4px 16px;
        font-size: 12px;
        color: #6E6E73;
    }
    .cs-pick-list {
        max-height: 360px; // メニューが多いときは一覧の中でスクロール
        overflow-y: auto;
    }
    .cs-pick-opt {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 44px;
        padding: 0 16px;
        border-bottom: 1px solid #EFEFF4;
        font-size: 14px;
        color: #1D1D1F;
        cursor: pointer;
        input {
            width: 18px;
            height: 18px;
            accent-color: #1F6FE5; // チェックの色を青にそろえる
            margin: 0;
        }
    }
    .cs-pick-empty {
        padding: 20px 16px;
        font-size: 13px;
        color: #8E8E93;
    }
    .cs-pick-btns {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 12px 16px;
    }
    // [カスタムエイド 2026-10-16] 来店履歴の編集 ここまで

    .font_color {
        font-size: 16px;
        color: #1d1d1f;
    }

    .cleft {
        width: 20px;
        height: 20px;
        flex-shrink: 0;

        img {
            width: 100%;
            height: 100%;
        }
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
                margin-top: 10px;
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
            background: #f5f5f7;
            opacity: 1;
            border-radius: 10px;
            border: 1px solid #aaaaaa !important;
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
            overflow-y: auto;
            flex-shrink: 0;
            overflow-x: hidden;
            
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
            padding-right: 0 !important;
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
                    margin-top: 10px;

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

                .cardInner::-webkit-scrollbar {
                    display: none !important;
                }

                .cardInner {
                    width: 100%;
                    height: 100%;
                    background-color: #fff;
                    display: flex;
                    flex-wrap: wrap;
                    overflow-y: auto;

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
                position: relative;

                .text_pink {
                    color: #D93025;
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
        margin-bottom: 5px;
    }
</style>