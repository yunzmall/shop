define({
  name:"invite",
  template:`
    <div>
    <el-form>
      <div class="vue-main-title">
        <div class="vue-main-title-left"></div>
        <div class="vue-main-title-content">商品邀请页面</div>
      </div>
      <div style="margin:0 auto;width:80%;">
          <el-form-item label="商品邀请页面">
            <el-radio v-model="status" :label="1">开启</el-radio>
            <el-radio v-model="status" :label="0">关闭</el-radio>
          </el-form-item>
      </div>
      </el-form>
    </div>
  `,
  style:`
  `,
  props: {
    form: {
      default() {
        return {}
      }
    }
  },
  data(){
    return{
        status:"0",
    }
  },
  created() {
      if(this.form){
          if( JSON.stringify(this.form) !== '[]'){
              this.status = parseInt(this.form.status);
          }
      }
  },
  methods: {
    extraDate(){
      
    },
    validate(){
      return {
          status:this.status,
      }
    }
  },
});