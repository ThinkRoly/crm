<template>
  <div id="app1">
    <div class="wrap">
      <div class="login">
        <div class="header">
          <p>欢迎进入沪汇融CRM平台</p>
          <p>Welcome to crm</p>
        </div>
        <div class="form" action="#" on>
          <div class="form-item">
            <img src="@/assets/images/username.png" alt="" />
            <input type="text" name="username" placeholder="请输入" v-model="userInfo.username" />
          </div>
          <div class="form-item">
            <img src="@/assets/images/password.png" alt="" />
            <input type="password" name="password" placeholder="请输入"  v-model="userInfo.password"/>
          </div>
          <div class="form-item">
            <img src="@/assets/images/verify.png" alt="" />
            <input type="text" name="verify" placeholder="请输入"  v-model="userInfo.captcha"/>
            <img class="img-code"
            :src="captchaurl"
            onclick="this.src='/api/captcha?t='+(new Date()).getTime()"
            />
          </div>
          <div class="form-item remember">
            <input type="checkbox" name="remember" id="" checked/>
            <span>记住密码</span>
          </div>
          <input type="submit" class="submit" value="登录" @click="handleSubmit"/>
        </div >
        <div class="footer">由 @arco 提供专业服务</div>
      </div>
    </div>
  </div>

</template>

<script lang="ts" setup>
  import { ref, reactive } from 'vue';
  import { useRouter } from 'vue-router';
  import { Message } from '@arco-design/web-vue';
  import { ValidatedError } from '@arco-design/web-vue/es/form/interface';
  import { useI18n } from 'vue-i18n';
  import { useStorage } from '@vueuse/core';
  import { useUserStore } from '@/store';
  import useLoading from '@/hooks/loading';
  import type { LoginData } from '@/api/user';

  const router = useRouter();
  const { t } = useI18n();
  const errorMessage = ref<string>('');
  const { loading, setLoading } = useLoading();
  const userStore = useUserStore();

  const loginConfig = useStorage('login-config', {
    rememberPassword: true,
    username: '', // 演示默认值
    password: '', // demo default value
  });
  const userInfo = reactive({
    username: loginConfig.value.username,
    password: loginConfig.value.password,
    captcha: ''
  });
  const captchaurl = ref(`/api/captcha?t=${(new Date()).getTime()}`);
  const flashCaptcha = () => {
    const url = `/api/captcha?t=${(new Date()).getTime()}` ;
    captchaurl.value = url;
  }
  const handleSubmit = async () => {
      setLoading(true);
      try {
        const page = await userStore.login(userInfo as LoginData);
        const { redirect, ...othersQuery } = router.currentRoute.value.query;
        router.push({
          name: (redirect as string) || page || 'Workplace',
          query: {
            ...othersQuery,
          },
        });
        Message.success(t('login.form.login.success'));
        const { rememberPassword } = loginConfig.value;
        const { username, password } = userInfo;
        // 实际生产环境需要进行加密存储。
        // The actual production environment requires encrypted storage.
        loginConfig.value.username = rememberPassword ? username : '';
        loginConfig.value.password = rememberPassword ? password : '';
      } catch (err) {
        errorMessage.value = (err as Error).message;
      } finally {
        flashCaptcha();
        setLoading(false);
      }
  };

  const setRememberPassword = (value: boolean) => {
    loginConfig.value.rememberPassword = value;
  };
</script>

<style lang="less" scoped>
  .container {
    display: flex;
    height: 100vh;

    .banner {
      width: 550px;
      background: linear-gradient(163.85deg, #1d2129 0%, #00308f 100%);
    }

    .content {
      position: relative;
      display: flex;
      flex: 1;
      align-items: center;
      justify-content: center;
      padding-bottom: 40px;
    }

    .footer {
      position: absolute;
      right: 0;
      bottom: 0;
      width: 100%;
    }
  }

  .logo {
    position: fixed;
    top: 24px;
    left: 22px;
    z-index: 1;
    display: inline-flex;
    align-items: center;

    &-text {
      margin-right: 4px;
      margin-left: 4px;
      color: var(--color-fill-1);
      font-size: 20px;
    }
  }
  * {
    margin: 0;
    padding: 0;
  }
  html,
  body,
  #app1 {
    height: 100%;
  }
  input {
    border: none;
  }
  input:focus {
    outline: none;
  }
  #app1 {
    height:100vh;
    background-image: url("/resource/bg.png");
    background-repeat: no-repeat;
    -o-background-size: cover;
       background-size: cover;
  }
  #app1 .wrap {
    width: 1160px;
    height: 100%;
    margin: 0 auto;
    position: relative;
  }
  #app1 .wrap .logo {
    position: absolute;
    left: 10px;
    top: 30px;
    width: 241px;
    height: 60px;
    background-image: url("/resource/logo.png");
    background-repeat: no-repeat;
    -o-background-size: contain;
       background-size: contain;
  }
  #app1 .wrap .login {
    position: absolute;
    right: 0px;
    top: 50%;
    -webkit-transform: translateY(-50%);
       -moz-transform: translateY(-50%);
        -ms-transform: translateY(-50%);
         -o-transform: translateY(-50%);
            transform: translateY(-50%);
    width: 531px;
    background: rgba(255, 255, 255, 0.78);
    border: 1px solid #ffffff;
    border-radius: 34px;
    padding: 84px 99px 31px;
    -webkit-box-sizing: border-box;
       -moz-box-sizing: border-box;
            box-sizing: border-box;
  }
  #app1 .wrap .login .header p {
    color: #082e6e;
    letter-spacing: 0;
  }
  #app1 .wrap .login .header p:first-child {
    font-family: AlibabaPuHuiTiM;
    font-size: 26px;
    font-weight: 500;
    margin-bottom: 10px;
  }
  #app1 .wrap .login .header p:last-child {
    font-family: AlibabaPuHuiTiR;
    font-size: 22px;
    font-weight: 400;
  }
  #app1 .wrap .login .form {
    margin-top: 22px;
  }
  #app1 .wrap .login .form .form-item:not(.remember) {
    width: 100%;
    height: 50px;
    -webkit-box-sizing: border-box;
       -moz-box-sizing: border-box;
            box-sizing: border-box;
    background: #ffffff;
    border-radius: 6px;
    display: -webkit-box;
    display: -webkit-flex;
    display: -moz-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
       -moz-box-align: center;
        -ms-flex-align: center;
            align-items: center;
    -webkit-box-pack: start;
    -webkit-justify-content: flex-start;
       -moz-box-pack: start;
        -ms-flex-pack: start;
            justify-content: flex-start;
    padding: 12px 19px;
    margin-bottom: 20px;
  }
  #app1 .wrap .login .form .form-item:not(.remember) img {
    display: block;
    width: 20px;
    height: 20px;
  }
  #app1 .wrap .login .form .form-item:not(.remember) input {
    -webkit-box-flex: 1;
    -webkit-flex: 1;
       -moz-box-flex: 1;
        -ms-flex: 1;
            flex: 1;
    height: 100%;
    margin-left: 18px;
    font-family: AlibabaPuHuiTiR;
    font-size: 18px;
    color: #333333;
    letter-spacing: 0;
    font-weight: 400;
  }
  #app1 .wrap .login .form .form-item:not(.remember) input::-webkit-input-placeholder {
    font-family: AlibabaPuHuiTiR;
    font-size: 18px;
    color: #adadb0;
    letter-spacing: 0;
    font-weight: 400;
  }
  #app1 .wrap .login .form .form-item:not(.remember) input::-moz-placeholder {
    font-family: AlibabaPuHuiTiR;
    font-size: 18px;
    color: #adadb0;
    letter-spacing: 0;
    font-weight: 400;
  }
  #app1 .wrap .login .form .form-item:not(.remember) input:-ms-input-placeholder {
    font-family: AlibabaPuHuiTiR;
    font-size: 18px;
    color: #adadb0;
    letter-spacing: 0;
    font-weight: 400;
  }
  #app1 .wrap .login .form .form-item:not(.remember) input::-ms-input-placeholder {
    font-family: AlibabaPuHuiTiR;
    font-size: 18px;
    color: #adadb0;
    letter-spacing: 0;
    font-weight: 400;
  }
  #app1 .wrap .login .form .form-item:not(.remember) input:-moz-placeholder {
    font-family: AlibabaPuHuiTiR;
    font-size: 18px;
    color: #adadb0;
    letter-spacing: 0;
    font-weight: 400;
  }
  #app1 .wrap .login .form .form-item:not(.remember) input::placeholder {
    font-family: AlibabaPuHuiTiR;
    font-size: 18px;
    color: #adadb0;
    letter-spacing: 0;
    font-weight: 400;
  }
  #app1 .wrap .login .form .form-item:not(.remember) .img-code {
    width: 70px;
    height: 28px;
    background-color: bisque;
    cursor: pointer;
  }
  #app1 .wrap .login .form .form-item.remember {
    display: -webkit-box;
    display: -webkit-flex;
    display: -moz-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
       -moz-box-align: center;
        -ms-flex-align: center;
            align-items: center;
    -webkit-box-pack: start;
    -webkit-justify-content: flex-start;
       -moz-box-pack: start;
        -ms-flex-pack: start;
            justify-content: flex-start;
  }
  #app1 .wrap .login .form .form-item.remember input {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }
  #app1 .wrap .login .form .form-item.remember span {
    font-family: AlibabaPuHuiTiR;
    font-size: 18px;
    color: #0a0d55;
    letter-spacing: 0;
    font-weight: 400;
    margin-left: 8px;
  }
  #app1 .wrap .login .form .submit {
    width: 100%;
    height: 50px;
    background: #155dff;
    border-radius: 6px;
    display: -webkit-box;
    display: -webkit-flex;
    display: -moz-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-align: center;
    -webkit-align-items: center;
       -moz-box-align: center;
        -ms-flex-align: center;
            align-items: center;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
       -moz-box-pack: center;
        -ms-flex-pack: center;
            justify-content: center;
    font-family: AlibabaPuHuiTiM;
    font-size: 18px;
    color: #ffffff;
    letter-spacing: 0;
    font-weight: 500;
    margin-top: 29px;
    cursor: pointer;
  }
  #app1 .wrap .login .footer {
    font-family: AlibabaPuHuiTiR;
    font-size: 18px;
    color: #999999;
    letter-spacing: 0;
    font-weight: 400;
    margin-top: 45px;
    text-align: center;
  }

</style>

<style lang="less" scoped>
  // responsive
  @media (max-width: @screen-lg) {
    .container {
      .banner {
        width: 25%;
      }
    }
  }
</style>
