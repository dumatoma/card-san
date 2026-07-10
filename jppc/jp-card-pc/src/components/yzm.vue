<template>
    <div style="width: 100%;">
        <div class="xskcodeinput">
            <div class="box" :style="{
          width: width + 'px',
          height: height + 'px',
          backgroundColor: backgroundColor,
        }" @click="focus = true" v-for="(item, index) in length" :key="index">
                <div v-if="password.length > index" :style="{ bold: bold ? 'bold' : 'normal' }">
                    {{ showVal ? password[index] : "*" }}
                </div>
                <div class="line" v-if="password.length == index"
                    style="font-weight: normal; font-size: 30px; line-height: 76px">
                    |
                </div>
            </div>

            <input ref="focusInput" class="input" type="number" :focus="false" v-model="password" maxlength="6" @focus="focus = true"
                @blur="focus = false" @input="userinput" />
        </div>
    </div>
</template>

<script>
    export default {
        name: "yzm",
        props: {
            width: {
                type: [Number, String],
                default: 84,
            },
            height: {
                type: [Number, String],
                default: 84,
            },
            backgroundColor: {
                type: String,
                default: "#EDEDED",
            },
            bold: {
                type: Boolean,
                default: true,
            },
            showVal: {
                type: Boolean,
                default: false,
            },
            length: {
                type: Number,
                default: 6,
            },
            password: {
                type: String,
                default: "",
            }
        },
        data() {
            return {
                focus: true,
            };
        },
        mounted() {
            this.$refs.focusInput.focus();
        },
        methods: {
            userinput(e) {
                this.$emit("changeCode", this.password);

                // if (this.password.length == this.length) {
                //   this.focus = false;
                //   this.$emit("confirm");
                // }
            },
        },
    };
</script>

<style lang="scss" scoped>
    .xskcodeinput {
        display: flex;
        position: relative;
        align-items: center;
        justify-content: center;

        .box {
            width: 48px;
            height: 76px;
            line-height: 76px;
            outline: 0;
            text-align: center;
            font-weight: bold;
            font-size: 30px;
            margin-right: 15px;
            border-radius: 10px;
            border: 2px solid #aaa;
            // &:nth-child(2) {
            //   border: 2px solid #1a73e8;
            // }
            // &:nth-child(3) {
            //   border: 2px solid #d93025;
            // }

            &:last-child {
                margin-right: 0;
            }

            .line {
                opacity: 0;
                animation-name: donghua;
                animation-timing-function: linear;
                animation-iteration-count: infinite;
                animation-duration: 0.5s;
                animation-direction: alternate;
            }

            @keyframes donghua {
                0% {
                    opacity: 1;
                }

                100% {
                    opacity: 0;
                }
            }
        }

        .input {
            position: absolute;
            top: 0;
            left: -100%;
            width: 200%;
            height: 100%;
            opacity: 0;
        }
    }
</style>