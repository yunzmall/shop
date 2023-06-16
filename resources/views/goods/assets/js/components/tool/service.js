define({
  name: "service",
  template: `
    <div>
      <el-form ref="form" label-width="15%">
        <div id="vue_head">
            <div class="base_set">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">服务提供</div>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="是否自动上下架">
                      <el-radio v-model="form.is_automatic" :label="1">是</el-radio>
                      <el-radio v-model="form.is_automatic" :label="0">否</el-radio>
                    </el-form-item>
                    <el-form-item label="时间方式">
                      <el-radio v-model="form.time_type" :label="0">固定</el-radio>
                      <el-radio v-model="form.time_type" :label="1">循环</el-radio>
                      <div class="tip">固定：在设置的时间商品自动上下架时间</div>
                      <div class="tip">循环：在循环日期内商品每天在设置的时间点自动循环上下架</div>
                    </el-form-item>
                    <el-form-item label="上下架时间" v-if="form.time_type==0">
                      <el-date-picker
                          v-model="shelves_time"
                          type="datetimerange"
                          value-format="timestamp"
                          align="right"
                          unlink-panels
                          range-separator="至"
                          start-placeholder="开始日期"
                          end-placeholder="结束日期"
                          :picker-options="pickerOptions">
                      </el-date-picker>
                  </el-form-item>
                  <el-form-item label="循环日期" v-if="form.time_type==1">
                      <el-date-picker
                          v-model="loop_date"
                          type="daterange"
                          value-format="timestamp"
                          align="right"
                          unlink-panels
                          range-separator="至"
                          start-placeholder="开始日期"
                          end-placeholder="结束日期"
                          :picker-options="pickerOptions"
                          >
                      </el-date-picker>
                  </el-form-item>
                  <el-form-item label="上架时间" v-if="form.time_type==1">
                      <el-time-select
                        v-model="form.loop_time_up"
                        value-format="timestamp"
                        :picker-options="{
                            start: '00:00',
                            step: '00:05',
                            end: '24:00'
                        }"
                        placeholder="选择时间">
                      </el-time-select>
                      <span style="margin-left: 15px;margin-right: 8px">下架时间</span>
                      <el-time-select
                        v-model="form.loop_time_down"
                        value-format="timestamp"
                        :picker-options="{
                            start: '00:00',
                            step: '00:05',
                            end: '24:00',
                            minTime: form.loop_time_up
                        }"
                        placeholder="选择时间">
                      </el-time-select>
                  </el-form-item>
                  <el-form-item label="库存自动刷新" v-if="form.time_type==1">
                      <el-switch v-model="form.auth_refresh_stock" active-color="#13ce66" inactive-color="#ff4949" :active-value="1" :inactive-value="0"></el-switch>
                       <div class="tip">开启后，循环日期期间，每日重新上架时，库存商品数自动刷新为原始库存数</div>
                  </el-form-item>
                  <el-form-item label="原始库存" v-if="form.time_type==1">
                       <el-input v-model="form.original_stock" style="width:30%;"></el-input>
                  </el-form-item>
                </div>
            </div>
        </div>
      </el-form>
    </div>
  `,
  data(){
    return {
      // shelves:"1",
      pickerOptions: {
        shortcuts: [{
          text: "最近一周",
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit("pick", [start, end]);
          }
        }, {
          text: "最近一个月",
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit("pick", [start, end]);
          }
        }, {
          text: "最近三个月",
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit("pick", [start, end]);
          }
        }]
      },
      shelves_time: [],
      loop_date: [],
      widgets:{
        service:{
          is_automatic:"1",
        }
      }
    }
  },
  style: ``,
  mounted() {
    // console.log(this.form,'服务提供')
    this.shelves_time = [this.form.starttime * 1000,this.form.endtime * 1000]
    this.loop_date = [this.form.loop_date_start * 1000,this.form.loop_date_end * 1000]
  },
  methods:{
    extraDate(){
      
    },
    validate(){
      return {
        starttime:this.shelves_time[0] / 1000,
        endtime:this.shelves_time[1] / 1000,
        loop_date_start:this.loop_date[0] / 1000,
        loop_date_end:this.loop_date[1] / 1000,
        is_automatic:this.form.is_automatic,
        time_type:this.form.time_type,
        loop_time_up:this.form.loop_time_up,
        loop_time_down:this.form.loop_time_down,
        auth_refresh_stock:this.form.auth_refresh_stock,
        original_stock:this.form.original_stock,
      }
    }
  },
  props: {
    form: {
      type: Object,
      default() {
        return {}
      }
    }
  }
})