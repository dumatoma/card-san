// [カスタムエイド 2026-10-22] Instagram×GBP投稿管理・クチコミ管理（管理WEB jppc と同じ API）
import request from '../utils/request.js';

const BASE = '/api/shop/google_business/'

// 連携状況：{ connected, review_on, ins_on, ins_auto, instagram:{connected,username,avatar}, ... }
export const gbpOverview = () => request({ url: BASE + 'overview', method: 'get' })

// 機能トグル（ins_auto：Instagram投稿時にGBP最新情報へ自動転載）
export const gbpFeatures = (data) => request({ url: BASE + 'features', method: 'put', data })

// Instagram 投稿一覧（転載状態つき）：{ items:[{id,image,images?,caption,date,status}], next, total }
export const gbpInsPosts = (after) => request({ url: BASE + 'ins_posts', method: 'get', params: { after: after || '' }, timeout: 30000 })

// 1件をGBPへ転載（重複はサーバー側で防止）
export const gbpInsRepost = (id) => request({ url: BASE + 'ins_posts/' + encodeURIComponent(id) + '/repost', method: 'post', data: {}, timeout: 60000 })

// クチコミ一覧：params { filter: 'all'|'unreplied', offset, limit }
export const gbpReviewList = (params) => request({ url: BASE + 'review_list', method: 'get', params, timeout: 30000 })

// 既読にする（ids: クチコミIDの配列）
export const gbpReviewRead = (ids) => request({ url: BASE + 'review_read', method: 'post', data: { ids } })

// 未読件数
export const gbpReviewUnread = () => request({ url: BASE + 'review_unread', method: 'get' })

// 返信・返信の編集（同じAPI）
export const gbpReviewReply = (id, comment) => request({ url: BASE + 'review_reply/' + encodeURIComponent(id), method: 'post', data: { comment }, timeout: 30000 })
