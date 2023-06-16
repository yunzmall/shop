define({
  name: "trade",
  template: `
    <div>
      <el-form ref="form" label-width="15%">
        <div id="vue_head">
            <div class="base_set">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">交易设置</div>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="隐藏售后按钮时间段">
                      <el-switch v-model="form.hide_status" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>
                    <el-form-item>
                      <el-input v-model="form.begin_hide_day" style="width:18%;margin-right: 15px">
                        <template slot="prepend">第</template>
                        <template slot="append">天</template>
                      </el-input>
                      <el-time-select
                        v-model="form.begin_hide_time"
                        value-format="timestamp"
                        :picker-options="{
                            start: '00:00',
                            step: '00:05',
                            end: '24:00'
                        }"
                        placeholder="选择开始隐藏时间">
                      </el-time-select>
                    </el-form-item>
                    <el-form-item>
                       <el-select v-model="form.end_hide_day" placeholder="请选择" style="width:18%;margin-right: 15px">
                          <el-option
                            v-for="item in hide_day_arr"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                          </el-option>
                        </el-select>
                        <el-time-select
                        v-model="form.end_hide_time"
                        value-format="timestamp"
                        :picker-options="{
                            start: '00:00',
                            step: '00:05',
                            end: '24:00'
                        }"
                        placeholder="选择结束隐藏时间">
                      </el-time-select>
                    </el-form-item>
                    <el-form-item label="自动发货">
                       <el-switch v-model="form.auto_send" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>
                    <el-form-item>
                      <el-input v-model="form.auto_send_day" style="width:18%;margin-right: 15px">
                        <template slot="prepend">第</template>
                        <template slot="append">天</template>
                      </el-input>
                      <el-time-select
                        v-model="form.auto_send_time"
                        value-format="timestamp"
                        :picker-options="{
                            start: '00:00',
                            step: '00:05',
                            end: '24:00'
                        }"
                        placeholder="选择时间">
                      </el-time-select>
                    </el-form-item>
                    <el-form-item label="送达时间">
                      <el-input v-model="form.arrived_day" style="width:18%;margin-right: 15px">
                        <template slot="prepend">第</template>
                        <template slot="append">天</template>
                      </el-input>
                      <el-time-select
                        v-model="form.arrived_time"
                        value-format="timestamp"
                        :picker-options="{
                            start: '00:00',
                            step: '00:05',
                            end: '24:00'
                        }"
                        placeholder="选择时间">
                      </el-time-select>
                    </el-form-item>
                    <el-form-item label="自定义文字">
                      <el-input v-model="form.arrived_word" style="width:35%;margin-right: 15px"></el-input>
                      <div style="color: #0ad76d;cursor: pointer;width: 68px" @click="add('送达时间')">[送达时间]</div>
                    </el-form-item>
                </div>
            </div>
        </div>
      </el-form>
    </div>
  `,
  data(){
    return {
      hide_day_arr: [
        {
          label:'当日',
          value:0,
        },
        {
          label:'次日',
          value:1,
        },
      ],
    }
  },
  style: ``,
  mounted() {
  },
  methods:{
    extraDate(){
      
    },
    validate(){
      if (this.form.hide_status==1 && this.form.begin_hide_day < 1) {
          this.$message.error('隐藏售后天数不能小于1');return false;
      }
      if (this.form.auto_send==1 && this.form.auto_send_day < 1) {
        this.$message.error('自动发货天数不能小于1');return false;
      }
      if (this.form.arrived_day < 1) {
        this.$message.error('送达时间天数不能小于1');return false;
      }
      return {
        hide_status:this.form.hide_status,
        begin_hide_day:this.form.begin_hide_day,
        begin_hide_time:this.form.begin_hide_time,
        end_hide_day:this.form.end_hide_day,
        end_hide_time:this.form.end_hide_time,
        auto_send:this.form.auto_send,
        auto_send_day:this.form.auto_send_day,
        auto_send_time:this.form.auto_send_time,
        arrived_day:this.form.arrived_day,
        arrived_time:this.form.arrived_time,
        arrived_word:this.form.arrived_word,
      }
    },
    add(item){
      this.form.arrived_word = this.form.arrived_word+`[${item}]`
    },
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