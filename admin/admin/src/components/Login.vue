<template>
  <div>
    <div class="logContent">
      <div class="login">
        <div class="logotitle">
          <img src="../assets/logo.png" alt="" class="logo">
          <span class="title">マスター管理</span>
        </div>
        <div class="fu">
          Log-in
        </div>

        <div class="item">
          <div>
            <div class="itemTop">ID:メールアドレス</div>
            <div class="itemBottom">
              <el-input class="inputDeep" v-model="account" placeholder="Required Field@cardsan.com"></el-input>
            </div>
          </div>

        </div>

        <div class="item">
          <div>
            <div class="itemTop"> Password </div>
            <div class="itemBottom">
              <el-input class="inputDeep" v-model="pwd" type="password" placeholder="card-san1110"></el-input>
            </div>
          </div>

        </div>

        <div class="logs" @click="logIn">
          ログイン
        </div>

      </div>
    </div>
    <div class="bottomLog">
      Customaid:LLC
    </div>
  </div>
</template>

<script>
  import {
    Login
  } from "@/http/api.js"
  export default {
    name: 'Login',
    data() {
      return {
        msg: 'This is Login Pages',
        account: "",
        pwd: ""
      }
    },
    methods: {
      logIn() {
        let that = this
        let data = {}
        data.admin_name = that.account
        data.password = that.pwd
        Login(data).then((res) => {
          console.log(res)
          if (res.code == 200) {
            let admin = JSON.stringify(res.data.admin)
            localStorage.setItem("token", res.data.token)
            localStorage.setItem("aid", res.data.aid)
            localStorage.setItem("admin", admin)
            that.$message({
              message: res.message,
              type: 'success',
              offset:400
            });
            setTimeout(() => {
              this.$router.push("/Home")
            },2000)
          }
        })
      }
    }
  }
</script>

<style scoped>
  .bottomLog {
    width: 100vw;
    height: 124px;
    background: #174a84;
    text-align: right;
    font-size: 29px;
    font-family: Hiragino Sans-W6, Hiragino Sans;
    font-weight: 400;
    color: #fff;
    position: fixed;
    bottom: 0;
    left: 0;
    z-index: 888;
    line-height: 124px;
    box-sizing: border-box;
    padding: 0 68px;
  }

  .logs {
    width: 144px;
    height: 48px;
    background: #007aff;
    border-radius: 5px;
    margin: 0 auto;
    text-align: center;
    line-height: 48px;
    font-size: 12px;
    font-family: Arial-Bold, Arial;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    margin-top: 30px;
  }

  input {
    border: none !important;
  }

  .el-input__inner::placeholder {
    color: #bce0fd;
  }

  .item {
    margin: 20px auto 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .inputDeep {
    border: 1px solid #bce0fd;
  }

  .itemTop {
    font-size: 17px;
    font-family: Arial-Regular, Arial;
    font-weight: 400;
    color: #bce0fd;
    box-sizing: border-box;
    width: 180px;
  }

  .itemBottom {
    height: 48px;
    background: #fff;
    text-align: center;
    width: 408px;
  }

  .logContent {
    width: 100vw;
    height: 100vh;
    background: #f1f9ff;
    position: relative;
  }

  .login {
    width: 768px;
    height: 480px;
    background: #fff;
    box-shadow: 0 24px 48px 1px rgba(38, 153, 251, .2);
    position: absolute;
    left: 50%;
    top: 40%;
    transform: translate(-50%, -50%);
    box-sizing: border-box;
    padding-top: 72px;
  }

  .logotitle {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .logo {
    width: 244px;
    height: 35px;
    margin-right: 24px;
  }

  .title {
    font-size: 36px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 700;
    color: #2699fb;
  }

  .fu {
    font-size: 36px;
    font-family: Hiragino Sans-W5, Hiragino Sans;
    font-weight: 400;
    color: #2699fb;
    margin-top: 25px;
    box-sizing: border-box;
    padding-left: 145px;
  }
</style>
