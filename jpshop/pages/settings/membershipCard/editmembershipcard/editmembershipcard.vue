<template>
    <!-- [カスタムエイド 2026-10-20] ②アイコン設定(編集するとき)。中身は共通の部品 components/csIconSetting -->
    <view>
        <cs-icon-setting v-if="ready" mode="edit" :type="type" :card-id="id" :shop-name="shopName" :image="image"></cs-icon-setting>
    </view>
</template>

<script>
    import csIconSetting from '@/components/csIconSetting/csIconSetting.vue'
    export default {
        components: {
            csIconSetting
        },
        data() {
            return {
                ready: false,
                type: 1,
                id: '',
                shopName: '',
                image: ''
            }
        },
        onLoad(options) {
            const dec = (v) => {
                try {
                    return decodeURIComponent(v || '')
                } catch (e) {
                    return v || ''
                }
            }
            this.type = options.type || 1
            this.id = options.id || ''
            this.shopName = dec(options.name)
            this.image = dec(options.image)
            this.ready = true
        }
    }
</script>
