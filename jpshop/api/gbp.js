import request from '../utils/request.js';

/**
 * Googleビジネス連携（GBP）設定画面用 API
 * URL は管理WEB（jppc/jp-card-pc/src/http/api.js の gbp*）と同じサーバー側の新API
 */

// 連携状態・機能設定の取得
export const gbpOverview = () => request({
    url: '/api/shop/google_business/overview',
    method: 'get'
})

// Google 認証URLの取得（client=app：コールバック後に cardsan://gbp?... でアプリへ戻す）
export const gbpAuthUrl = () => request({
    url: '/api/shop/google_business/get_auth_url?client=app',
    method: 'get'
})

// 認可コードでトークン取得（候補が複数ある場合 need_select=1 で候補一覧を返す）
export const gbpConnect = (data) => request({
    url: '/api/shop/google_business/create_access_token',
    method: 'post',
    data: data,
    timeout: 30000
})

// 連携する店舗（ロケーション）の確定
export const gbpSelectLocation = (data) => request({
    url: '/api/shop/google_business/select_location',
    method: 'post',
    data: data,
    timeout: 30000
})

// 連携解除
export const gbpDisconnect = () => request({
    url: '/api/shop/google_business/disconnect',
    method: 'delete',
    data: { full: 1 }
})

// 機能のON/OFF・自動同期設定・同期項目の保存
export const gbpFeatures = (data) => request({
    url: '/api/shop/google_business/features',
    method: 'put',
    data: data
})

// 今すぐ同期
export const gbpSyncNow = () => request({
    url: '/api/shop/google_business/sync_now',
    method: 'post',
    data: {},
    timeout: 60000
})
