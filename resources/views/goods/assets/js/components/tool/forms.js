define({
  name: "forms",
  template: `
    <div>
      <el-form ref="form" label-width="15%">
        <div id="vue_head">
            <div class="base_set">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">表单</div>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="商品表单">
                        <el-switch v-model="form.status" :active-value="1" :inactive-value="0"></el-switch>
                        <div class="tip">表单用途：用于跨境商品报税使用，开启后首次购买跨境商品的会员需要填写身份证号和真实姓名</div>
                    </el-form-item>
                </div>
            </div>
        </div>
      </el-form>
    </div>
  `,
  style: ``,
  data(){
    return {
      
    }
  },
  mounted() {
    // this.form.status = 1
  },
  methods:{
    validate(){
      return {
        status:this.form.status ? this.form.status : 0
      }
    },
    extraDate(){
      
    },
  },
  props: {
    form: {
      // type: Object,
      default() {
        return {}
      }
    }
  }
})