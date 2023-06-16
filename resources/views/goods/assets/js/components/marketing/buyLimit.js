define({
  name:"buyLimit",
  template:`
    <div class="buyLimit">
    <el-form label-width="20%">
      <div class="vue-main-title">
        <div class="vue-main-title-left"></div>
        <div class="vue-main-title-content">限时购</div>
      </div>
      
      <div style="margin:0 auto;width:80%;">
        <el-form-item label="限时购开关">
          <el-switch
            v-model="form.data.status"
            :active-value="1"
            :inactive-value="0"
            active-color="#29BA9C"
            inactive-color="#ccc">
          </el-switch>
        </el-form-item>
        <el-form-item label="限时时间" v-if="form.data.status">
          <el-date-picker
            value-format="timestamp"
            type="datetimerange" 
            v-model="timeLimit"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期">
          </el-date-picker>
        </el-form-item>

        <el-form-item label="自定义前端显示名称" v-if="form.data.status">
          <el-input style="width: 80%;"v-model="form.data.display_name"></el-input>
          <div class="tip">不填，默认为限时购</div>
        </el-form-item>
      </div>
      </el-form>
    </div>
  `,
  style:``,
  props: {
    form: {
      default() {
        return {}
      }
    }
  },
  data() {
    return {
      timeLimit:''
    }
  },
  mounted() {
    if(this.form.data.end_time || this.form.data.start_time){
      this.timeLimit = [this.form.data.start_time * 1000,this.form.data.end_time * 1000]
    }
  },
  methods:{
    extraDate(){
      
    },
    validate(){
      return {
        status:this.form.data.status,
        display_name:this.form.data.display_name ? this.form.data.display_name : "限时购",
        start_time:this.timeLimit ? this.timeLimit[0] / 1000: "",
        end_time:this.timeLimit ? this.timeLimit[1] / 1000: ""
      }
    }
  }
})