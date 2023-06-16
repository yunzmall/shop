define({
  name: "delivery",
  template: `
    <div>
      <el-form label-width="15%" >
        <div id="vue_head">
            <div class="base_set">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">配送</div>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="运费配置">
                      <el-radio v-model="form.dispatch.dispatch_type" :label="1">统一邮费</el-radio>
                      <el-radio v-model="form.dispatch.dispatch_type" :label="0">运费模板</el-radio>
                    </el-form-item>
                    <el-form-item label=" ">
                      <div>
                        <el-input v-model="form.dispatch.dispatch_price" style="width:30%;" v-if="form.dispatch.dispatch_type == 1">
                          <template slot="append">元</template>
                        </el-input>
                        <div v-if="(Number(form.dispatch.dispatch_price) < 0 || form.dispatch.dispatch_price == '') && form.dispatch.dispatch_type" class="tip-bg">请输入大于等于0的数</div>
                        <el-select v-model="form.dispatch.dispatch_id" placeholder="请选择运费模板" v-if="form.dispatch.dispatch_type == 0" clearable filterable allow-create default-first-option>
                          <el-option :label="item.dispatch_name" :value="item.id" v-for="(item,index) in form.dispatch_templates" :key="index">{{item.dispatch_name}}</el-option>
                        </el-select>
                      </div>
                    </el-form-item>
                    <el-form-item label="配送方式" v-if="form.dispatchTypesSetting.length > 0">
                      <el-checkbox-group v-model="form.dispatch.dispatch_type_ids">
                        <el-checkbox :label="item.id" :value="item.id" v-for="(item,index) in form.dispatchTypesSetting" :key="index">{{item.name}}</el-checkbox>
                      </el-checkbox-group>
                    </el-form-item>
                </div>
            </div>
        </div>
      </el-form>
    </div>
  `,
  data(){
    return {
      freight_list:[{
        id:0,
        name:"默认模板"
      },{
        id:1,
        name:"其他模板"
      }],
      yesRegular:true,
    }
  },
  watch:{
    'form.dispatch.dispatch_type':{
      handler(val, olVal) {
        if(this.form.dispatch.dispatch_type) {
          this.yesRegular = this.form.dispatch.dispatch_price == "" ? false : true;
        }
      },
      deep:true
    },
    'form.dispatch.dispatch_price':{
      handler(val, olVal){
        let regular = new RegExp(/^[+]{0,1}(\d+)$|^[+]{0,1}(\d+\.\d+)$/)
        let dispatchPrice = regular.test(this.form.dispatch.dispatch_price);
        if(!dispatchPrice){
          this.yesRegular = false
          return
        }
        this.yesRegular = true
      },
      deep:true
    }
  },
  mounted() {
    let dispatch_type_ids_list = []
    this.form.dispatch.dispatch_type_ids.forEach(element => {
      dispatch_type_ids_list.push(element * 1)
    });
    if(this.form.dispatch.dispatch_id == null || this.form.dispatch.dispatch_id == 0){
      this.form.dispatch.dispatch_id = ""
    }
    this.form.dispatch.dispatch_type_ids = dispatch_type_ids_list
  },
  methods: {
    extraDate(){
      
    },
    validate(){
      if(!this.yesRegular){
        return false;
      }else{
        let new_dispatch_type_ids = [];
        for(let item of this.form.dispatch.dispatch_type_ids) {
          for(let citem of this.form.dispatchTypesSetting) {
            if(item == citem.id) {
              new_dispatch_type_ids.push(item)
            }
          }
        }
        return {
          dispatch_type:this.form.dispatch.dispatch_type,
          dispatch_price:this.form.dispatch.dispatch_price ? this.form.dispatch.dispatch_price : "",
          dispatch_type_ids:new_dispatch_type_ids,
          dispatch_id:this.form.dispatch.dispatch_id
        };
      }
    }
  },
  style: `.tip-bg {     
    color: #EE3939;
    font-size: 12px;
    line-height: 1; 
    margin-top: 10px;
  }`,
  props: {
    form: {
      type: Object,
      default() {
        return {}
      }
    }
  }
})