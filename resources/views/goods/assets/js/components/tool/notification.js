define({
  name: "notification",
  template: `
    <div>
      <el-form ref="form" label-width="15%">
        <div id="vue_head">
            <div class="base_set">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">消息通知</div>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="商家通知">
                      <div class="upload-box" >
                        <div class="upload-box-member" @click="btnNotificationData">
                            <i class="el-icon-plus notcie_icon" style="font-size:32px"></i><br>
                            <span>选择通知人</span>
                        </div>
                      </div>
                      <div class="img_box" v-if="member.avatar_image">
                        <img :src="member.avatar_image"/>
                        <span class="introduce_name">{{name}}</span>
                      </div>
                      <div class="tip">单品下单通知，可指定某个用户，通知商品下单备货通知，如果商品为同一商家，建议使用系统一设置</div>
                      <div class="tip">非公众号注册会员，无法绑定通知人员</div>
                    </el-form-item>
                    <el-form-item label="通知方式">
                      <el-checkbox-group v-model="form.notice_type">
                        <el-checkbox v-for="(item,index) in notification_list" :label="item.id" :key="index" :value="item.id">{{item.name}}</el-checkbox>
                      </el-checkbox-group>
                      <div class="tip">通知商家方式</div>
                  </el-form-item>
                </div>
            </div>
        </div>
      </el-form>

      <!-- 选择图片弹窗 -->
      <el-dialog :visible.sync="choose_goods_show" width="60%" left title="选择通知人">
          <div>
              <div class="search_line">
                  <el-input v-if="is_storeCashier"  style="width:90%" placeholder="请输入完整的手机号码！！" v-model="keyword_mobile"></el-input>
                  <el-input v-if="!is_storeCashier"  style="width:90%" placeholder="" v-model="keyword"></el-input>
                  <el-button v-if="is_storeCashier" @click="btnSearchMobile">搜索</el-button>
                  <el-button v-if="!is_storeCashier" @click="btnSearch">搜索</el-button>
              </div>
              <el-table :data="goodsData" style="width: 100%;height:500px;overflow:auto"  v-loading="loading">
                  <el-table-column label="昵称" align="" >
                      <template slot-scope="scope">
                          <div style="display:flex;">
                              <img style="width: 50px;height:50px" :src="scope.row.avatar_image" alt="">
                              <span class="nickname_card">{{scope.row.nickname}}</span>
                          </div>
                      </template>
                  </el-table-column>
                  <el-table-column label="会员ID" align="center" prop="uid"></el-table-column>
                  <el-table-column label="姓名" align="center" prop="username"></el-table-column>
                  <el-table-column label="手机号码" align="center" prop="mobile"></el-table-column>
                  <el-table-column label="" align="center">
                      <template slot-scope="scope">
                          <span  style="color:#399fbd" @click="btnSelect(scope.row)">
                              选择
                          </span>
                      </template>
                  </el-table-column>
              </el-table>
          </div>
          <span slot="footer" class="dialog-footer">
              <el-button @click="choose_goods_show = false">取 消</el-button>
          </span>
      </el-dialog>
    </div>
  `,
  data(){
    return {
      notification_list:[{
        id:1,
        name:"下单通知"
      },{
        id:2,
        name:"付款通知"
      },{
        id:3,
        name:"买家收货通知"
      }],
      choose_goods_loading:false,
      choose_goods_show:false,
      // current_page:1,
      // total:1,
      // per_page:1,
      goodsData:[],
      member:{
        avatar_image:"",
        uid:"",
      },
      name:"",
      keyword:"",
      keyword_mobile:'',
      loading:false
    }
  },
  style: `
    .img_box{
      margin:20px 0;
      display: flex;
      flex-direction: column;
    }
    .img_box img{
      width:150px;
      height:150px;
    }
    .introduce_name{
      width: 150px;
      line-height: 20px;
    }
    .notcie_icon{
      font-size: 32px;
      width: 100%;
      text-align: center;
    }
    .el-checkbox-group{
      display: contents;
    }
    .nickname_card{
      align-items: center;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 1;
      height: fit-content;
      line-height: 50px;
      margin-left:10px;
    }
  `,
  created() {
    this.member.avatar_image = this.form.member ? this.form.member.avatar_image : "";
    this.member.uid = this.form.member ? this.form.member.uid : "";
    this.is_storeCashier = this.form.is_storeCashier ? this.form.is_storeCashier : '';
    // this.form.notice_type = [1,2]
    this.name = this.form.member.username ? this.form.member.username : "";
  },
  methods: {
    btnNotificationData(){
      this.choose_goods_show = true;
      this.choose_goods_loading = true
    },
    btnSelect(data){
      // console.log(data,'data');
      this.name = data.nickname
      this.member.avatar_image = data.avatar_image
      this.member.uid = data.uid
      this.choose_goods_show = false
    },
    btnSearch(){
      this.loading = true
      this.$http.post(this.http_url +'member.member.get-search-member-json',{
        keyword:this.keyword
      }).then(response => {
        if(response.data.result==1){
          this.$message({
              message: '成功',
              type: 'success'
          });
          this.loading = false
          this.goodsData = response.data.data.members
        }
        else{
          this.$message.error(response.data);
        }
      }),function(res){
        console.log(res);
      };
    },
    btnSearchMobile() {
      this.loading = true
      this.$http.post(this.http_url +'plugin.store-cashier.store.admin.member-information.get-member-only-mobile',{
        keyword:this.keyword_mobile
      }).then(response => {
        if(response.data.result==1){
          this.$message({
            message: '成功',
            type: 'success'
          });
          this.loading = false
          this.goodsData = response.data.data.members
        }
        else{
          this.$message.error(response.data.msg);
          this.loading = false
        }
      })
    },
    extraDate(){
      
    },
    validate(){
      return {
        type:this.form.notice_type,
        uid:this.member.uid ? this.member.uid : ""
      }
    }
  },
  props: {
    form: {
      // type: Object,
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
  }
})