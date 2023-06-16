define({
  name: "error",
  template: `
  <div class="error-component">
    <div class="el-icon-warning" ></div>
    页面加载失败
  </div>
  `,
  style: `
  .error-component {
    margin-top:20px;
    text-align:center;
    font-size:16px;
  }
  .error-component > div {
    display:block;
    margin-bottom:10px;
    font-size:26px;
  }
  `
})