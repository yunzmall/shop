<style>
  .verify-popup-component {
    position: fixed;
    z-index: 10;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    margin: auto;
    width: 100%;
    height: 100%;
    background-color: transparent;
  }

  .verify-popup-component-content {
    position: fixed;
    z-index: 1;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    padding: 15px;
    margin: auto;
    width: 31.6vw;
    height: 300px;
    background-color: white;
    border-radius: 15px;
    box-sizing: border-box;
    box-shadow: 0 0 11px 4px rgb(0 0 0 / 8%);
  }

  .verify-popup-component-content>h4 {
    text-align: center;
  }

  .verify-form {
    width: 80%;
    margin: 20px auto 0;
  }

  .verify-form-row {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .verify-form-row:not(:last-child) {
    margin-bottom: 15px;
  }

  .verify-form-row .verify-form-col:first-child {
    flex-shrink: 0;
    width: 60px;
  }

  .verify-form-row .verify-form-col:nth-child(2) {
    flex-grow: 1;
  }

  .verify-form-row .verify-form-col:nth-child(3) {
    flex-grow: 1;
  }

  .verify-form-operation {
    margin-top: 30px;
  }

  .verify-form-operation button {
    margin-right: 20px;
  }

  .verify-form-code-prompt {
    text-align: left;
    color: red;
  }

  .verify-popup-component-mask {
    position: fixed;
    z-index: -1;
    top: 0;
    right: 0;
    left: 0;
    bottom: 0;
    margin: auto;
    width: inherit;
    height: inherit;
    background-color: rgba(0, 0, 0, 0.2);
  }
</style>

<div class="verify-popup-component" hidden>
  <div class="verify-popup-component-content">
    <h4>二次校验</h4>
    <form class="verify-form" action="">
      <div class="verify-form-row">
        <div class="verify-form-col">手机号</div>
        <div class="verify-form-col"> @if($verify_phone){{ $verify_phone }}@else <a href="{{ yzWebFullUrl('password.setting.index') }}">设置二次校验手机号</a> @endif</div>
        <div class="verify-form-col">
          @if($verify_phone)
          <button class="btn btn-success" type='button' onclick="getVerifyCode(this)"><span></span>
            获取手机验证码
          </button>
          @endif
        </div>
      </div>
      <div class="verify-form-row verify-form-code-prompt"></div>
      <div class="verify-form-row">
        <div class="verify-form-col">验证码</div>
        <div class="verify-form-col"><input type="text" name="code" placeholder="请输入手机验证码"></div>
      </div>
      <div class="verify-form-row">
        通过验证后{{ $verify_expire }}分钟内无需再次验证
      </div>
      <div class="verify-form-row verify-form-operation">
        @if($verify_phone)
        <button class="btn btn-success" type="button" onclick="verifyCode()">确定</button>
        @endif
        <button class="btn btn-default" type="button" onclick="hideGetVerifyCodePopup()">取消</button>
      </div>
    </form>
  </div>
  <div class="verify-popup-component-mask" onclick="hideGetVerifyCodePopup()"></div>
</div>

<script>
  let getCodeVerifyTimes = 60;
  let getCodeVerifyTimer = null;
  const verifyPhoneNumber = Number("{{$verify_phone}}");
  let verifyed = Boolean("{{ $is_verify }}");
  let expireTime = Number("{{ $expire_time }}");
  const codePromptEl = document.querySelector(".verify-form-code-prompt");

  function showGetVerifyCodePopup() {
    document.querySelector(".verify-popup-component").hidden = false;
  }

  function hideGetVerifyCodePopup() {
    document.querySelector(".verify-popup-component").hidden = true;
  }

  function getVerifyCode(buttonEl) {
    if (!verifyPhoneNumber) {
      alert("还未设置二次校验验证手机号，请前往资产密码页面设置二次校验验证手机号码");
      return;
    }
    codePromptEl.innerText = "";
    if (getCodeVerifyTimer === null) {
      codePromptEl.innerText = "获取验证码中";
      fetch("{!! yzWebFullUrl('finance.withdraw.sendCode') !!}").then(res => res.json()).then(res => {
        if (res.result === 0) {
          codePromptEl.innerText = res.msg;
          return;
        }
        codePromptEl.innerText = "获取验证码成功";
        setTimeout(() => {
          codePromptEl.innerText = "";
        }, 3000);
        getCodeVerifyTimer = setInterval(() => {
          buttonEl.innerText = --getCodeVerifyTimes + "秒后重发";
          if (getCodeVerifyTimes <= 0) {
            clearInterval(getCodeVerifyTimer);
            getCodeVerifyTimer = null;
            buttonEl.innerText = "获取手机验证码";
          }
        }, 1000);
      });

    }
  }

  function verifyCode() {
    const code = document.querySelector("input[name='code']").value;
    if (!code) {
      codePromptEl.innerText = "请输入手机接收到的验证码";
      return;
    }
    codePromptEl.innerText = "验证中，请稍后";
    const submitData = new FormData();
    submitData.append("code", code);
    fetch("{!! yzWebFullUrl('finance.withdraw.checkVerifyCode') !!}", {
      method: "POST",
      body: submitData
    }).then(res => res.json()).then(res => {
      if (res.result === 0) {
        codePromptEl.innerText = res.msg;
        return;
      }
      codePromptEl.innerText = "验证成功";
      verifyed = true;
      expireTime = res.data.expire;
      clearInterval(getCodeVerifyTimer);
      getCodeVerifyTimer = null;
      setTimeout(() => {
        hideGetVerifyCodePopup();
      }, 1000);
    });
  }
</script>