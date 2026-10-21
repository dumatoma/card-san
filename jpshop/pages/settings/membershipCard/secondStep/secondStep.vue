<template>
    <!-- [カスタムエイド 2026-10-20] カードの作成画面。中身は新しい共通の部品 components/csCardForm(作成と編集で同じ)
         今までのこの画面の中身は GitHub の履歴で見られます -->
    <page-meta :page-style="lock ? 'overflow: hidden;' : ''"></page-meta>
    <view>
        <cs-card-form v-if="ready" mode="add" :type="type" :shop-name="shop_name" :image="image"
            @lock="lock = $event"></cs-card-form>
    </view>
</template>

<script>
    import csCardForm from '@/components/csCardForm/csCardForm.vue'
    export default {
        components: {
            csCardForm
        },
        data() {
            return {
                ready: false,
                lock: false,
                type: 1,
                id: '',
                shop_name: '',
                image: ''
            }
        },
        onLoad(options) {
            this.type = options.type || 1
            this.id = options.id || ''
            this.shop_name = decodeURIComponent(options.shop_name || '')
            this.image = options.image || ''
            this.ready = true
        }
    }
</script>
