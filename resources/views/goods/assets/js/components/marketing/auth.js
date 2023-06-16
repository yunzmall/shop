define({
  name:"auth",
  template:`
    <div class="auth">
      <el-form ref="auth" :model="json">
      <div class="vue-main-title">
        <div class="vue-main-title-left"></div>
        <div class="vue-main-title-content">权限</div>
      </div>

      <div style="margin:0 auto;width:80%;">        
          <el-form-item label="商品规格">
          <div style="display:flex;padding: 12px 0 0 75px;">
                <el-checkbox-group v-model="goodsOptionIds" class="checkbo-group"  @change="checked => handleCheckedChange(checked,'option')">
                  <el-checkbox v-for="item in goodsOption" :key="item.id" :label="item.id + ''">{{item.title}}</el-checkbox>
                </el-checkbox-group>
          </div>
           <div style="margin-left: 142px;" class="form-item_tips">仅控制购买权限，浏览权限按整个商品；选全部规格按整个商品控制</div>
          </el-form-item>
          <el-form-item label="会员等级浏览权限">
          <div style="display:flex;padding: 12px 0 0 20px;">
                <el-checkbox-group v-model="levelAccessList" class="checkbo-group"  @change="checked => handleCheckedChange(checked,'watch')">
                  <el-checkbox v-for="item in levels" :key="item.id" :label="item.id + ''">{{item.level_name}}</el-checkbox>
                </el-checkbox-group>
          </div>
          </el-form-item>

          <el-form-item label="会员等级购买权限">
          <div style="display:flex;padding: 12px 0 0 20px;">
              <el-checkbox-group v-model="levelBuyList" class="checkbo-group" @change="checked => handleCheckedChange(checked,'buy')">
                <el-checkbox v-for="item in levels" :key="item.id" :label="item.id + ''">{{item.level_name}}</el-checkbox>
              </el-checkbox-group>
          </div>
          </el-form-item>

          
          <el-form-item label="会员组浏览权限">
          <div style="display:flex;padding: 12px 0 0 33px;">
              <el-checkbox-group v-model="vipAccessList" class="checkbo-group" @change="checked => handleCheckedChange(checked,'watches')">
                <el-checkbox v-for="item in groups" :key="item.id" :label="item.id + ''">{{item.group_name}}</el-checkbox>
              </el-checkbox-group>
          </div>
          </el-form-item>

          <el-form-item label="会员组购买权限">
          <div style="display:flex;padding: 12px 0 0 33px;">
              <el-checkbox-group v-model="vipBuyList" class="checkbo-group" @change="checked => handleCheckedChange(checked,'buys')">
                <el-checkbox v-for="item in groups" :key="item.id" :label="item.id + ''">{{item.group_name}}</el-checkbox>
              </el-checkbox-group>
          </div>
          </el-form-item>


        <el-form-item label="每次限购数量">
          <el-input style="width:400px;margin-left:48px;" v-model="json.once_buy_limit" oninput="if(value<0)value=''" type="number"></el-input>
          <div style="margin-left: 145px;" class="form-item_tips">每次下单限购数量</div>
        </el-form-item>

        <el-form-item label="会员限购总数">
          <el-input style="width:400px;margin-left:48px;" v-model="json.total_buy_limit" oninput="if(value<0)value=''" type="number"></el-input>
          <div style="margin-left: 145px;" class="form-item_tips">会员限购的总数</div>
        </el-form-item>

        <el-form-item label="会员每天限购数量">
          <el-input style="width:400px;margin-left:20px;" v-model="json.day_buy_limit" oninput="if(value<0)value=''" type="number"></el-input>
          <div style="margin-left: 145px;" class="form-item_tips">会员每天限购数量</div>
        </el-form-item>

        <el-form-item label="会员每周限购数量">
          <el-input style="width:400px;margin-left:20px;" v-model="json.week_buy_limit" oninput="if(value<0)value=''" type="number"></el-input>
          <div style="margin-left: 145px;" class="form-item_tips">会员每周限购数量</div>
        </el-form-item>

        <el-form-item label="会员每月限购数量">
          <el-input style="width:400px;margin-left:20px;" v-model="json.month_buy_limit" oninput="if(value<0)value=''" type="number"></el-input>
          <div style="margin-left: 145px;" class="form-item_tips">会员每月限购数量</div>
        </el-form-item>

        <el-form-item label="会员起购数量">
          <el-input style="width:400px;margin-left:48px;" v-model="json.min_buy_limit" oninput="if(value<0)value=''" type="number"></el-input>
          <div style="margin-left: 145px;" class="form-item_tips">会员单次最少购买数量，为空、0不限制</div>
        </el-form-item>
        
         <el-form-item label="商品每日限购总量">
          <el-input style="width:400px;margin-left:15px;" v-model="json.day_buy_total_limit" oninput="if(value<0)value=''" type="number"></el-input>
          <div style="margin-left: 145px;" class="form-item_tips">如设置为1，则今日售出1件(需已支付),会员今天无法再购买此商品</div>
        </el-form-item>

        <el-form-item label="购买倍数限制">
          <el-input style="width:400px;margin-left:48px;" v-model="json.buy_multiple" oninput="if(value<0)value=''" type="number"></el-input>
          <div style="margin-left: 145px;" class="form-item_tips">会员单次最少购买数量，为空、0不限制</div>
        </el-form-item>
        
        <el-form-item label="限购时段" v-if="is_show_buy_limit == true">
          <el-switch
            v-model="json.buy_limit_status"
            :active-value="1"
            :inactive-value="0"
            active-color="#29BA9C"
            inactive-color="#ccc"
            style="width:400px;margin-left:76px;">
          </el-switch>
          <div style="margin-left: 145px;" class="form-item_tips">注意：如果开启限购时段还开启限时购，则优先走限购时段</div>
          
          <el-form-item label="自定义前端显示名称" v-if="json.buy_limit_status">
            <el-input style="width:400px;" v-model="json.buy_limit_name" placeholder="限时购" ></el-input>
            <div style="margin-left: 145px;" class="form-item_tips">不填，默认为限时购</div>
          </el-form-item>
          
          <div style="margin-left: 81px;">
           <el-button class="add-coupon" v-if="json.buy_limit_status" @click="addTimeLimit()" style="margin-left:61px;">添加时间段</el-button>
          </div>
          
        </el-form-item>

        <el-form-item v-for="(item,index) in json.time_limits" :key="index" v-if="json.buy_limit_status && is_show_buy_limit == true">
              <div style="display:flex;margin-left:141px;width: 100%;">
                <el-date-picker
                  value-format="timestamp"
                  type="datetimerange" 
                  v-model="item.time_limit"
                  range-separator="至"
                  start-placeholder="开始日期"
                  end-placeholder="结束日期">
                </el-date-picker>
                <span style="margin: 0 10px;">限制数量</span>
                <el-form-item style="display: flex;align-items: center">
                  <el-input v-model="item.limit_number" style="margin-left: 8px;" type="number">
                  </el-input>
                </el-form-item>
                <el-button icon="el-icon-delete" @click="removeTimeLimit(index)"></el-button>
              </div>
            </el-form-item>

        
      </div>
    </el-form>
    </div>
  `,
  style:`
  .auth .checkbo-group {
    display: flex;
    flex-flow: wrap;
    white-space: pre-wrap;
  }
  .auth .checkbo-group .el-checkbox {
    line-height: 25px;
  }
  `,
  props: {
    form: {
      default() {
        return {}
      }
    }
  },
  data(){
    // let intReg = /^[0-9]*$/;
    // let validateNum = (rule,value, callback) => {
    //   let regular = intReg.test(value);
    //   if(!value){
    //      callback();
    //   }else{
    //     if (regular) {
    //       callback();
    //     }else{
    //       callback(new Error("请输入正整数"));
    //     }
    //   }
    // };
    return {
      goodsOptionIds:[], //商品规格购物权限
      levelAccessList:[],//会员等级浏览权限
      levelBuyList:[],//会员等级购买权限
      vipAccessList:[],//会员组浏览权限
      vipBuyList:[],//会员组购买权限
      levels: [],
      groups: [],
      goodsOption:[],
      json: {
        show_levels: '',
        show_groups: '',
        buy_levels: '',
        buy_groups: '',
        once_buy_limit: '',
        total_buy_limit: '',
        day_buy_limit: '',
        week_buy_limit: '',
        month_buy_limit: '',
        min_buy_limit: '',
        day_buy_total_limit:'',
        buy_multiple:'',
        option_buy_limit:'',
        buy_limit_status:'',
        buy_limit_name:'',
        time_limits:[],
      },
      is_show_buy_limit:''
      // rules:{
      //   once_buy_limit: {validator: validateNum },
      //   total_buy_limit: {validator: validateNum },
      //   day_buy_limit: {validator: validateNum },
      //   week_buy_limit: {validator: validateNum },
      //   month_buy_limit: {validator: validateNum },
      //   min_buy_limit: {validator: validateNum },
      // },
    }
  },
  mounted(){
    if (this.form && this.form.privilege) {
      let data = this.form.privilege;
      this.json = this.pick(data, [
        "show_levels",
        "show_groups",
        "buy_levels",
        "buy_groups",
        'once_buy_limit',
        'total_buy_limit',
        'day_buy_limit',
        'week_buy_limit',
        'month_buy_limit',
        'min_buy_limit',
        'day_buy_total_limit',
        'buy_multiple',
        'option_buy_limit',
        'buy_limit_status',
        'buy_limit_name',
        'time_limits',
      ]);
      this.levelAccessList = data.show_levels ? data.show_levels.split(",") : [''];
      this.vipAccessList = data.show_groups ? data.show_groups.split(",") : [''];
      this.levelBuyList = data.buy_levels ? data.buy_levels.split(",") : [''];
      this.vipBuyList = data.buy_groups ? data.buy_groups.split(",") : [''];
      this.goodsOptionIds = data.option_buy_limit ? data.option_buy_limit.split(",") : [''];

    }
    this.levels = this.form.levels ? JSON.parse(JSON.stringify(this.form.levels)) : [];
    this.groups = this.form.groups ? JSON.parse(JSON.stringify(this.form.groups)) : [];
    this.goodsOption = this.form.goods_option ? JSON.parse(JSON.stringify(this.form.goods_option)) : [];
    this.is_show_buy_limit = this.form.is_show_buy_limit;
  },
  methods:{
    filterData() {
      let value = true;
      if (this.json.buy_limit_status == 1) {
        for(let [index,item] of this.json.time_limits.entries()) {
          for(let [cindex,citem] of this.json.time_limits.entries()) {
            if (index !== cindex) {
              if((item.time_limit[0] <= citem.time_limit[0]) && (item.time_limit[1] >= citem.time_limit[0])) {
                // json.push(item);
                this.$message({
                  message: '限购时段有时间段重复，请检查',
                  type: 'error'
                });
                value = false;
                return
              }
            }
          }
        }
      }
      return value
    },
    pick(obj, params) {
      return params.reduce((iter, val) => (val in obj && (iter[val] = obj[val]?obj[val]:''), iter), {});
    },
    async validate(){
      if(this.filterData()) {
        return this.json;
      }else {
        return false
      }
      // return this.json;
    },
    handleCheckedChange(checked,type) {
      console.log(checked);
      let isCheckAll = false;
      let checkAll = [];
      checked.forEach((item, index) => {
        if (item === '' && checked.length > 1) {
          if (index !== 0) {
            isCheckAll = true;
            checkAll = [''];
          }
        } else {
          checkAll.push(item);
        }
      });
      let levelStr = isCheckAll ? '' : checkAll.join(',');
      if(type == 'option') {
        this.json.option_buy_limit = levelStr;
        this.goodsOptionIds = checkAll;
      }
      if(type == 'watch') {
        this.json.show_levels = levelStr;
        this.levelAccessList = checkAll;
      }
      if(type == 'buy') {
        this.json.buy_levels = levelStr;
        this.levelBuyList = checkAll;
      }
      if(type == 'watches'){
        this.json.show_groups = levelStr;
        this.vipAccessList = checkAll;
      }
      if(type == 'buys'){
        this.json.buy_groups = levelStr;
        this.vipBuyList = checkAll;
      }
    },

    addTimeLimit(){
      if(!this.json.time_limits) {
        this.$set(this.json,'time_limits',[]);
      }
      this.json.time_limits.push(
          {
            time_limit: '',
            limit_number:'',
          }
      );
    },

    removeTimeLimit(id){
      this.json.time_limits.splice(id,1)
    },
  }
})