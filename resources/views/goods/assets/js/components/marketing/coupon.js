define({
  name:"coupon",
  template:`
    <div id="coupon">
      <div class="vue-main-title">
        <div class="vue-main-title-left"></div>
        <div class="vue-main-title-content">优惠券</div>
      </div>
      <div style="margin:0 auto;width:80%;">
        <el-form>
        <el-form-item label="赠送优惠券" label-width="155px">
          <el-switch v-model="form.is_give" active-color="#29BA9C" inactive-color="#ccc" :active-value="1" :inactive-value="0"></el-switch>
          <div class="form-item_tips">订单完成赠送优惠券</div>
          <template v-if="form.is_give">
            <el-radio v-model="form.send_type" :label="0">每月1号0:00发放</el-radio>
            <el-radio v-model="form.send_type" :label="1">订单完成后的1分钟后发放</el-radio>
            <el-radio v-model="form.send_type" :label="2">订单付款后的1分钟后发放</el-radio>
            <el-form-item v-if="form.send_type == 0" prop="month">
              <el-input style="width:300px;" v-model.trim="form.send_num" type="number" @keyup.native="prevent($event)">
                <template slot="prepend">连续发放</template>
                <template slot="append">月</template>
              </el-input>
            </el-form-item>
            <el-form-item  v-for="(item,index) in giveCouponItems" :key="index"  >
              <div style="display:flex;margin-top:20px;">
                <el-input style="width:400px;"  v-model="item.coupon_name"  class="chooseCoupon" @click.native="openCouponDialog(index,'give')">
                  <template slot="append">选择优惠券</template>
                </el-input>
                <el-form-item prop="giveCouponItems" >
                <el-input  v-model="item.coupon_several" style="margin-left: -8px;" type="number" @keyup.native="prevent($event)">
                  <template slot="append">张</template>
                </el-input>
                </el-form-item>
                <el-button type="danger" @click="removeCoupon(index,'give')">删除</el-button>
              </div>
            </el-form-item>
            <div>
            <el-button style="margin-top: 20px;color: #29BA9C;border: 2px solid #29BA9C;" @click="addCoupon('give')">+ 增加优惠券</el-button>
            </div>
            <div class="form-item_tips">两项都填写才能生效</div>
            <div class="form-item_tips">订单完成后，按照勾选发放规则发放</div>
          </template>

        </el-form-item>

        <el-form-item label="购买商品分享优惠券" label-width="155px">
        <el-switch v-model="form.shopping_share" active-color="#29BA9C" inactive-color="#ccc" :active-value="1" :inactive-value="0"></el-switch>
          <div class="form-item_tips">会员购买指定商品，获得优惠券分享资格</div>
          <div v-if="form.shopping_share" style="margin-top: 30px;">
            <el-form-item  v-for="(item,index) in shareCouponItems" :key="index">
              <div style="display:flex;margin-top:10px;">
                <el-input style="width:400px;" v-model="item.coupon_name" class="chooseCoupon" @click.native="openCouponDialog(index,'share')">
                  <template slot="append">选择优惠券</template>
                </el-input>
                <el-form-item>
                <el-input v-model="item.coupon_several" style="margin-left: -8px;" type="number" @keyup.native="prevent($event)">
                  <template slot="append">张</template>
                </el-input>
                </el-form-item>
                <el-button type="danger" @click="removeCoupon(index,'share')">删除</el-button>
              </div>
            </el-form-item>
            <el-button class="add-coupon" @click="addCoupon('share')">+ 增加优惠券</el-button>
            <div class="tip">两项都填写才能生效;请勿重复选择优惠券</div>
            <div class="tip">订单支付后，按照勾选发放规则获得对应优惠券分享资格</div>
          </div>

        </el-form-item>

        <el-form-item label="禁止使用优惠券" label-width="155px">
          <el-switch v-model="form.no_use" active-color="#29BA9C" inactive-color="#ccc" :active-value="1" :inactive-value="0"></el-switch>
          <div class="form-item_tips">开启后购买该商品不能使用优惠券</div>
        </el-form-item>

        <el-form-item label="可使用优惠券数量限制" label-width="155px">
          <el-switch v-model="form.is_use_num" active-color="#29BA9C" inactive-color="#ccc" :active-value="1" :inactive-value="0"></el-switch>
          <div class="form-item_tips">开启后此商品订单可限制使用优惠券数量，注：开启拆单此设置不生效</div>

          <el-form-item v-if="form.is_use_num" style="margin: 30px 0 0 -155px;" label="可使用数量" label-width="155px" prop="isUse" >
            <el-input v-model="form.use_num" style="width:300px;" type="number" @keyup.native="prevent($event)"></el-input>
            <div class="form-item_tips">商品订单可使用优惠券数量，为0不限制，只能设置0或整数</div>
            <div class="form-item_tips">注：开启拆单此设置不生效</div>
          </el-form-item>

        </el-form-item>
      </el-form>
      </div>

      <el-dialog
        :visible.sync="couponDialog"
        width="40%"
        class="el-dialog-class"
        @close="handleCouponClose">
        <div slot="title">
          选择优惠券
        </div>
        <div calss="dialog-content" style="height:600px;">
          <el-input
            style="width:80%"
            class="inline-input"
            v-model="keyword"
            placeholder="请输入优惠券名称"
          ></el-input>
          <el-button style="margin-left: -4px;" @click="queryBtn">搜索</el-button>

          <div>
            <el-table
              :data="couponList"
              style="width: 100%">

              <el-table-column
                prop="name"
                label="优惠券"
                width="450">
              </el-table-column>

              <el-table-column
                label="操作"
                width="90">
                <template slot-scope="scope">
                  <el-button
                    size="mini"
                    @click="selectCoupon(scope.row)">选择</el-button>
                </template>
              </el-table-column>

            </el-table>
          </div>

        </div>
      </el-dialog>

    </div>
  `,
  style:`
    .chooseCoupon .el-input-group__append {
      background-color: #29BA9C;
      color: #fff;
      cursor: pointer;
    }，
    #coupon input::-webkit-outer-spin-button,
    #coupon input::-webkit-inner-spin-button {
      -webkit-appearance: none;
    }
    #coupon input[type="number"] {
      -moz-appearance: textfield;
    }
    .add-coupon{
      margin-top: 10px;
      margin-bottom:10px;
      color: #29BA9C;
      border: 2px solid #29BA9C;
    }
    .el-dialog-class .el-dialog__header{
      padding:25px !important;
    }
    .el-dialog-class .el-dialog__body{
      padding: 10px 20px !important;
    }
  `,
  props: {
    form: {
      default() {
        return {}
      }
    },
    http_url:{
      type:String,
      default() {
        return "";
      },
    }
  },
  data() {
    return {
      keyword:"",
      couponList:[],
      giveCouponItems:[],//赠送优惠券
      giveId:1,
      type:"",
      shareCouponItems:[],//分享优惠券
      couponDialog:false
    }
  },
  mounted(){

    this.giveCouponItems = this.form.coupon === null ? [] :this.form.coupon;
    this.shareCouponItems =  this.form.share_coupon === null ? [] :this.form.share_coupon;

  },
  methods: {
    extraDate(){

    },
    validate(){
      let coupon = JSON.parse(JSON.stringify(this.giveCouponItems)).filter(item => {
        return item.coupon_name
      })
      let share_coupon= JSON.parse(JSON.stringify(this.shareCouponItems)).filter(item => {
        return item.coupon_name
      })
      return {
        is_give:this.form.is_give,
        send_type:this.form.send_type,
        send_num:this.form.send_num,
        shopping_share:this.form.shopping_share,
        no_use:this.form.no_use,
        is_use_num:this.form.is_use_num,
        use_num:this.form.use_num,
        coupon,
        share_coupon
      }
    },
    openCouponDialog(id,type){
      if(type == 'give'){
        this.type = type
        this.giveId = id;
      }
      if(type == 'share'){
        this.shareId = id
        this.type = type
      }
      this.couponDialog = true;
    },
    handleCouponClose(){
      this.couponDialog = false;
    },
    //搜索按钮
    queryBtn(){
      this.$http.post(this.http_url +'coupon.coupon.get-search-coupons-v2&keyword='+this.keyword).then(response => {
        if(response.data.result==1){
          this.$message({
              message: '成功',
              type: 'success'
          });
          this.couponList = response.data.data
        }
        else{
          this.$message.error(response.data);
        }
      }),function(res){
        console.log(res);
      };
    },
    //选择优惠券
    selectCoupon(data) {
      if(this.type == "give"){
        this.giveCouponItems[this.giveId].coupon_name = data.name
        this.giveCouponItems[this.giveId].coupon_id = data.id
      }
      if(this.type == "share"){
        this.shareCouponItems[this.shareId].coupon_name = data.name
        this.shareCouponItems[this.shareId].coupon_id = data.id
      }
      this.couponDialog = false;
    },
    prevent(e){
      let keynum = window.event ? e.keyCode : e.which;   //获取键盘码
        if (keynum ==189|| keynum==190||keynum == 109 ||keynum == 110 ) {
            e.target.value = ""
        }
    },
    //增加优惠券
    addCoupon(type){
      if(type == 'give'){
        this.giveCouponItems.push(
          {
            coupon_id: "",
            coupon_name:'',
            coupon_several:'1',
          }
        );
      }
      if(type == 'share'){
        this.shareCouponItems.push(
          {
            coupon_id: "",
            coupon_name:'',
            coupon_several:'1'
          }
        );
      }
    },
    //删除优惠券
    removeCoupon(id,type){
      if(type == 'give'){
        this.giveCouponItems.splice(id,1)
      }
      if(type == 'share'){
        this.shareCouponItems.splice(id,1)
      }
    },
  }
})