define({
  name: "promotion",
  template: `
    <div class="promotion">
      <el-form ref="promotion"  :model="json" :rules="rules">
      <div class="vue-main-title">
        <div class="vue-main-title-left"></div>
        <div class="vue-main-title-content">单品优惠</div>
      </div>

      <div style="margin:0 auto;width:80%;">
        <el-form-item label="单品满件包邮" label-width="155px" prop="ed_num">
          <el-input  v-model.trim="json.ed_num" maxlength="300">
            <template slot="prepend">满</template>
            <template slot="append">件</template>
          </el-input>
          <div class="form-item_tips">设置为空或0，则不支持单品满件包邮</div>
        </el-form-item>

        <el-form-item label="单品满额包邮" label-width="155px" prop="ed_money">
          <el-input v-model.trim="json.ed_money" maxlength="300">
            <template slot="prepend">满</template>
            <template slot="append">元</template>
          </el-input>
          <div class="form-item_tips">设置为空或0，则不支持单品满额包邮</div>
        </el-form-item>

        <el-form-item label="单品满额立减" label-width="155px">
          <div style="display: flex;">
            <el-form-item prop="ed_full">
              <el-input v-model.trim="json.ed_full" maxlength="300">
                <template slot="prepend">满</template>
                <template slot="append">元</template>
              </el-input>
            </el-form-item>
            <el-form-item prop="ed_reduction">
              <el-input v-model.trim="json.ed_reduction" maxlength="300">
                  <template slot="prepend">减</template>
                  <template slot="append">元</template>
              </el-input>
            </el-form-item>
          </div>
        </el-form-item>


        <el-form-item label="不参与单品包邮地区" label-width="155px">
          <div>{{json.ed_areas}}</div>
          <el-button @click="openAreaDialog">添加不参加包邮的地区</el-button>
        </el-form-item>
        
      </div>
      
      <div class="vue-main-title">
        <div class="vue-main-title-left"></div>
        <div class="vue-main-title-content">余额设置</div>
      </div>

      <div style="margin:0 auto;width:80%;">
        <el-form-item label="赠送余额" label-width="155px" prop="award_balance">
          <el-input  v-model.trim="json.award_balance" maxlength="300">
            <template slot="append">余额</template>
          </el-input>
          <div class="form-item_tips">如果设置为0，则不赠送</div>
          <div class="form-item_tips">如：购买两件，设置10余额，不管成交价格是多少，则购买后获得20余额</div>
          <div class="form-item_tips">如：购买两件，设置10%余额，成交价格2*200=400，则购买后获得40余额(400*10%)</div>
        </el-form-item>
        <el-form-item label="赠送门店余额" label-width="155px" prop="pay_reward_balance" v-if="json.is_store == 1">
          <el-input  v-model.trim="json.pay_reward_balance" maxlength="300">
            <template slot="append">余额</template>
          </el-input>
          <div class="form-item_tips">如果设置为0，则不赠送</div>
          <div class="form-item_tips">如：购买两件，设置10余额，不管成交价格是多少，则购买后获得20余额</div>
          <div class="form-item_tips">如：购买两件，设置10%余额，成交价格2*200=400，则购买后获得40余额(400*10%)</div>
        </el-form-item>
        <el-form-item label="余额抵扣" label-width="155px">
          <el-switch
            v-model="json.balance_deduct"
            :active-value="1"
            :inactive-value="0">
          </el-switch>
          <div class="form-item_tips">开启余额抵扣，订单使用余额抵扣则不能使用余额支付。关闭则不支持余额抵扣</div>
        </el-form-item>
         <el-form-item v-if="json.balance_deduct" prop="" label-width="155px">
             <div style="display: flex;">
                <el-form-item prop="max_balance_deduct">
                    <el-input  v-model.trim="json.max_balance_deduct" maxlength="300">
                        <template slot="prepend">最多抵扣</template>
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
                <el-form-item prop="min_balance_deduct">
                    <el-input  v-model.trim="json.min_balance_deduct" maxlength="300">
                        <template slot="prepend">最少抵扣</template>
                        <template slot="append">元</template>
                    </el-input>
                </el-form-item>
             </div>
             <div class="form-item_tips">抵扣金额不能大于商品现价</div>
             <div class="form-item_tips">如果设置为空为0，则采用余额统一设置</div>
          </el-form-item>
      </div>

      <div class="vue-main-title">
        <div class="vue-main-title-left"></div>
        <div class="vue-main-title-content">积分设置</div>
      </div>

      <div style="margin:0 auto;width:80%;">
        <el-form-item label="赠送积分" label-width="155px" prop="point">
          <el-input  v-model.trim="json.point" maxlength="300">
            <template slot="append">积分</template>
          </el-input>
          <div class="form-item_tips">如果设置为空，则走积分统一设置</div>
          <div class="form-item_tips">如果设置为0，则不赠送</div>
          <div class="form-item_tips">如：购买2件，设置10积分，不管成交价格是多少，则购买后获得20积分</div>
          <div class="form-item_tips">如：购买2件，设置10%积分，成交价格2*200=400，则购买后获得40积分(400*10%)</div>
        </el-form-item>
        
        <el-form-item label="上级赠送积分" label-width="155px" prop="point">
          <el-input  v-model.trim="json.first_parent_point" maxlength="300">
            <template slot="prepend">一级上级</template>
            <template slot="append">积分</template>
          </el-input>
          <br>
          <el-input  v-model.trim="json.second_parent_point" maxlength="300">
            <template slot="prepend">二级上级</template>
            <template slot="append">积分</template>
          </el-input>
          <div class="form-item_tips">如果设置为空，则走积分统一设置</div>
          <div class="form-item_tips">如果设置为0，则不赠送</div>
          <div class="form-item_tips">如：购买2件，一级/二级设置10积分，不管成交价格是多少，则购买后一级/二级各获得20积分</div>
          <div class="form-item_tips">如：购买2件，一级/二级设置10%积分，成交价格2*200=400，则购买后一级/二级各获得40积分(400*10%)</div>
        </el-form-item>

        <div style="margin:0 0 10px 155px">
          <el-radio v-model="json.point_type" :label="0">订单完成立即赠送</el-radio>
          <el-radio v-model="json.point_type" :label="1">每月一号赠送</el-radio>
          <el-radio v-model="json.point_type" :label="2">订单支付后赠送</el-radio>
          <el-form-item v-if="json.point_type == 1" prop="max_once_point">
            <div>
              <el-input v-model.trim="json.max_once_point" maxlength="300" >
                <template slot="prepend">每月赠送</template>
                <template slot="append">积分</template>
              </el-input>
            </div>
          </el-form-item>
        </div>

  
        <el-form-item label="积分抵扣" label-width="155px">
         <el-radio  v-model="json.point_deduct_type" :label="0">固定金额</el-radio>
          <el-radio v-model="json.point_deduct_type" :label="1">支付金额比例抵扣</el-radio>
          <div style="display: flex;">
            <el-form-item prop="max_point_deduct">
              <el-input  v-model.trim="json.max_point_deduct" maxlength="300">
                <template slot="prepend">最多抵扣</template>
                <template slot="append">{{json.point_deduct_type==1?'%':'元'}}</template>
              </el-input>
            </el-form-item>
            <el-form-item prop="min_point_deduct">
              <el-input  v-model.trim="json.min_point_deduct" maxlength="300">
                <template slot="prepend">最少抵扣</template>
                <template slot="append">{{json.point_deduct_type==1?'%':'元'}}</template>
              </el-input>
            </el-form-item>
          </div>
          <div class="form-item_tips">按固定金额：抵扣金额不能大于商品现价</div>
          <div class="form-item_tips">按比例抵扣：比例值为0-100的整数,设置最少抵扣不能大于最多抵扣</div>
          <div class="form-item_tips">比例抵扣说明：例如商品原价100元填写5，即为抵扣5%的商品价格，积分可以抵扣5元</div>
          <div class="form-item_tips">如果设置为空，则采用积分统一设置</div>
          <div class="form-item_tips">如果设置为0，则不支持积分抵扣</div>
        </el-form-item>
        <el-form-item label="积分全额抵扣" label-width="155px">
          <el-switch
            v-model="json.has_all_point_deduct"
            :active-value="1"
            :inactive-value="0">
          </el-switch>
          <el-form-item v-if="json.has_all_point_deduct" prop="all_point_deduct">
            <el-input v-model.trim="json.all_point_deduct" maxlength="300">
            <template slot="append">积分</template>
            </el-input>
          </el-form-item>
        </el-form-item>
      </div>

      <div class="vue-main-title">
        <div class="vue-main-title-left"></div>
        <div class="vue-main-title-content">其他设置</div>
      </div>

      <div style="margin:0 auto;width:80%;">
        <el-form-item label="推广相关商品显示设置" label-width="155px">
          <el-switch
            v-model="json.is_push"
            :active-value="1"
            :inactive-value="0">
          </el-switch>
        </el-form-item>

        <el-form-item v-if="json.is_push">
          <div class="choose" style="margin: 0 0 10px 155px;display:flex;">
              <div class="choose-item" v-for="(item,index) in json.push_goods_ids" :key="index">
                <div class="close-item" @click="deleteGoods(item.id)"><i class="el-icon-close"></i></div>
                <img :src="item.thumb_url" alt="">
                <div class="goods-title" style="color:#999">{{item.title}}</div>
                <div style="color:#999">[ID:{{item.id}}]</div>
              </div>
              
              <div class="add-goods" @click="openGoodsDialog">
                <i class="el-icon-plus"></i>
                <div>选择商品</div>
              </div>
          </div>
        </el-form-item>
      </div>
    </el-form>
    <el-dialog
      center
      title="请选择地区"
      :visible.sync="areaDialog"
      @close="handleAreaClose"
      custom-class="area-dialog"
      :close-on-click-modal="false"
    >
      <el-tree
        :props="treeProps"
        node-key="id"
        lazy
        show-checkbox
        :data="areaData"
        ref="addressTree"
        :load="loadNode"
        @check-change="checkAreas"
        :default-checked-keys="area_ids"
        :default-expanded-keys="province_ids"
      >
      </el-tree>

      <span slot="footer" class="dialog-footer">
      <el-button type="primary" @click="saveAreas">确 定</el-button>
        <el-button @click="handleAreaClose">取 消</el-button>
      </span>
    </el-dialog>
      
    <el-dialog
        v-loading="loading"
        :visible.sync="goodsDialog"
        width="60%"
        @close="handleGoodsClose">
        <div slot="title">
            选择商品
        </div>
        <div style="height:600px;">
            <div style="display:flex">
              <el-input
                class="inline-input"
                v-model="goodsKey"
                placeholder="请输入搜索内容"
              ></el-input>
              <el-button style="margin-left: -4px;" @click="searchGoods">搜索</el-button>
            </div>
          

          <div>
            <el-table
              :data="goodsList"
              height="500"
              style="width: 100%">
              <el-table-column label="ID" prop="id" ></el-table-column>
              <el-table-column
                label="商品图片"
                width="100">
                <template slot-scope="scope">
                  <img style="width:30px;height:30px;" :src="scope.row.thumb_url" alt="">
                </template>
              </el-table-column>

              <el-table-column
                prop="title"
                label="商品名称"
                align="center"
                width="450">
                <template slot-scope="scope">
                  <span class="overflow-2">{{scope.row.title}}</span>
                </template>
              </el-table-column>

              <el-table-column
                label="操作"
                width="90">
                <template slot-scope="scope">
                  <el-button
                    size="mini"
                    @click="selectGoods(scope.$index)">选择</el-button>
                </template>
              </el-table-column>
            
            </el-table>

          </div>

          <el-pagination style="text-align: right;"
            background
            layout="prev, pager, next"
            :total="paginationOpt.total"
            :page-size="paginationOpt.pageSize"
            :current-page="paginationOpt.page"
            @current-change="searchGoods"
            >
          </el-pagination>
        </div>
      </el-dialog>
    </div>
  `,
  style: `
  .promotion .two-input {
    display:flex;
  }
  .promotion .overflow-2 {
    display:-webkit-box;
    -webkit-box-orient:vertical;
    -webkit-line-clamp:2;
    overflow:hidden;
  }
  .promotion p {
    height:10px;
  }
  .promotion .el-radio {
    margin: 0px 30px 10px 0;
  }
  .promotion .el-input-group {
    width:300px;
  }

  .promotion .el-autocomplete{
    width:90%;
  }

  .promotion .goods-item {
    display: flex;
    margin: 10px 0 0 10px;
  }
  .promotion .goods-item-left {
    display: flex;
    justify-content: center;
  }
  .promotion .goods-name {
    display: flex;
    align-items: center;
  }
  .promotion .goods-item-left span {
    display:block;
    margin-left:5px;
  }
  .promotion .goods-item-left img {
    width:30px;
    height:30px;
  }

  .promotion .goods-item-right {
    margin: 5px 0 0 200px;
    cursor: pointer;
  }

  .promotion .choose {
    width:600px;
    display:flex;
    flex-wrap: wrap;
  }

  .promotion .choose-item {
    position: relative;
    width:120px;
    margin: 10px;
    cursor: pointer;
  }
  .promotion .choose-item .close-item{
    position: absolute;
    top:-7px;
    right:-7px;
    width:20px;
    height:20px;
    background-color: #ccc;
    border-radius:50%;
    text-align: center;
  }
  .promotion .choose-item .close-item .el-icon-close{
    position: absolute;
    top: 3px;
    right: 3px;
  }
  .promotion .choose-item img {
    width:120px;
    height:120px;
  }

  .promotion .choose-item .goods-title {
    width: 100%;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }
  .promotion .add-goods {
    width:120px;
    height:120px;
    text-align: center;
    margin: 10px;
    cursor: pointer;
    border:1px dashed #a59f9f;
  }

  .promotion .add-goods  i{
    font-size:30px;
    margin-top:25px;
  }


  .promotion .area-dialog {
    width: 60%;
    height: 80%;
    overflow-y: scroll;
  }
  `,
  props: {
    form: {
      default() {
        return {};
      },
    },
    http_url:{
      type:String,
      default() {
        return "";
      },
    }
  },
  data() {
    let intReg = /^[0-9]*$/;
    let floatReg = /^\d+(\.\d+)?$/;
    let rateReg = /^([0-9.]+)[ ]*%$/;
    // 整数校验
    let intNum = (rule,value,callback)=>{
      let regular = intReg.test(value);
      if(!value){
         callback();
      }else{
        if (regular) {
          callback();
        }else{
          callback(new Error("请输入正整数"));
        }
      }
    };
    // 浮点数校验
    let floatNum = (rule,value,callback)=>{
      let regular = floatReg.test(value);
      if(!value){
         callback();
      }else{
        if (regular) {
          callback();
        }else{
          callback(new Error("请输入大于零或等于零的数字"));
        }
      }
    };
    // 百分比校验
    let rateNum = (rule, value, callback) => {
      let regular = floatReg.test(value);
      let regular2 = rateReg.test(value);
      if(!value){
         callback();
      }else{
        if (regular || regular2) {
           callback();
        }else{
          callback(new Error("请输入大于零等于零的数字,或百分比"));
        }
      }
    };

    return {
      rules: {
        ed_num: { validator: intNum },
        ed_money: { validator: floatNum },
        ed_full: { validator: floatNum },
        ed_reduction: { validator: floatNum },
        award_balance: { validator: rateNum },
        pay_reward_balance: { validator: rateNum },
        point: { validator: rateNum },
        first_parent_point: { validator: rateNum },
        second_parent_point: { validator: rateNum },
        max_once_point: { validator: rateNum },
        max_point_deduct: { validator: floatNum },
        min_point_deduct: { validator: floatNum },
        all_point_deduct: { validator: floatNum },
        max_balance_deduct: { validator: floatNum },
        min_balance_deduct: { validator: floatNum },
      },
      goodsDialog: false,
      areaDialog: false,
      areas: [],
      area_ids: [],
      province_ids: [],
      areaData: [],
      treeProps: {
        label: 'areaname',
        children: 'children',
        isLeaf: 'isLeaf'
      },
      loading: false,
    
      goodsKey: "",
      goodsList: [],
      paginationOpt: {
        total: 0,
        pageSize: 1,
        page: 1,
      },
      json: {
        ed_num: '',
        ed_money: '',
        ed_full: '',
        ed_areas: '',
        ed_areaids: '',
        ed_reduction: '',
        award_balance: '',
        pay_reward_balance: '',
        point: '',
        first_parent_point: '',
        second_parent_point: '',
        point_type: 0,
        max_once_point: '',
        point_deduct_type: 0,
        max_point_deduct: '',
        min_point_deduct: '',
        has_all_point_deduct: 0,
        all_point_deduct: '',
        balance_deduct: 0,
        max_balance_deduct:'',
        min_balance_deduct:'',
        is_push: 0,
        push_goods_ids: [],
        is_store:0
      },
    };
  },
  mounted() {
    if (this.form && this.form.sale) {
      let sale = this.form.sale;
      this.json.ed_num = sale.ed_num;
      this.json.ed_money = sale.ed_money;
      this.json.ed_full = sale.ed_full;
      this.json.ed_areas = sale.ed_areas;
      this.json.ed_areaids = sale.ed_areaids;
      this.json.ed_reduction = sale.ed_reduction;
      this.json.award_balance = sale.award_balance;
      this.json.is_store = sale.is_store;
      this.json.pay_reward_balance = sale.pay_reward_balance;
      this.json.point = sale.point;
      this.json.first_parent_point = sale.first_parent_point;
      this.json.second_parent_point = sale.second_parent_point;
      this.json.point_type = sale.point_type ? sale.point_type : 0;
      this.json.max_once_point = sale.max_once_point;
      this.json.point_deduct_type = sale.point_deduct_type;
      this.json.max_point_deduct = sale.max_point_deduct;
      this.json.min_point_deduct = sale.min_point_deduct;
      this.json.has_all_point_deduct = sale.has_all_point_deduct ? sale.has_all_point_deduct : 0;
      this.json.all_point_deduct = sale.all_point_deduct;
      this.json.balance_deduct = sale.balance_deduct ? sale.balance_deduct : 0;
      this.json.max_balance_deduct = sale.max_balance_deduct ? sale.max_balance_deduct : '';
      this.json.min_balance_deduct = sale.min_balance_deduct ? sale.min_balance_deduct : '';
      this.json.is_push = sale.is_push ? sale.is_push : 0;
      this.json.push_goods_ids = sale.push_goods_ids ? sale.push_goods_ids : [];
    }
  },
  methods: {
    extraDate(){
      
    },
    async validate() {
      try {
        this.validateResult = await this.$refs['promotion'].validate();
      } catch (err) {
        this.validateResult = false;
      }
      return this.validateResult ? this.json : false;
    },
    openGoodsDialog() {
      this.goodsDialog = true;
    },
    handleGoodsClose() {
      this.goodsDialog = false;
    },
    // 地区
    openAreaDialog() {
      this.areaDialog = true;
      if(this.area_ids){
        this.area_ids.forEach((item, index) => {
          this.area_ids[index] = Number(item)
        });
      }
    },
    handleAreaClose() {
      this.areaDialog = false;
    },
    loadNode(node, resolve) {
      this.loading = true;
      if (!node.data.id) {
        //省份
        node.data.id = 0;
        this.$http.get(this.http_url + "area.list&parent_id=0").then(response => {
            response.data.data.forEach(function (province) {
                province.isLeaf = false;
            });
            resolve(response.data.data);
            this.loading = false;
        }, response => {
            console.log(response);
        });
      } else {
        //城市
        this.$http.get(this.http_url + "area.list&parent_id=" + node.data.id).then(response => {
            //城市没有子节点
            response.data.data.forEach(function (city) {
                city.isLeaf = true;
            })
            resolve(response.data.data);
            // 载入数据后,刷新已选中
            this.loading = false;
        }, response => {
            console.log(response);
        });
      }
    },
    checkAreas(node, checked, children) {
      if (node.isLeaf) {
        return;
      }
      if (checked) {
        if (!this.province_ids) {
            this.province_ids = [];
        }
        this.province_ids.push(node.id)
      }
    },
    saveAreas() {
      let areas = [];
      let area_ids = [];
      let province_ids = [];
      this.$refs.addressTree.getCheckedNodes().forEach(function (node) {
        if (node.level == 1) {
            province_ids.push(node.id);
        } else if (node.level == 2) {
            area_ids.push(node.id);
            areas.push(node.areaname)
        }
      });
      this.$refs.addressTree.getHalfCheckedNodes().forEach(function (node) {
        if (node.level == 1) {
            province_ids.push(node.id);
        }
      });
      this.province_ids = province_ids;
      this.area_ids = area_ids;
      this.json.ed_areas = areas.join(";");
      this.json.ed_areaids = area_ids.join(",");
      this.areaDialog = false
    },

    // 商品
    searchGoods (page=this.paginationOpt.page) {
      if (this.goodsKey === "") {
        return;
      }
      let params = "goods.goods.get-search-goods-json&keyword=" + this.goodsKey;
      if (typeof page === "number") {
        params = params + "&page=" + page;
      }
      this.$http.get(this.http_url + params).then(response => {
       if (response.data.result != 1) {
        return this.$message.error(response.data.msg);
       }
       let resultData = response.data.data.goods;
       this.goodsList = resultData ? resultData.data : [];
       this.paginationOpt.page = resultData.current_page;
       this.paginationOpt.total = resultData.total;
       this.paginationOpt.pageSize = resultData.per_page;
      })
      .catch((err) => {
        this.$message.error(err);
      })
    },
    selectGoods(index) {
      let list = this.goodsList.slice(index, index + 1);
      let check = this.json.push_goods_ids.some(item=>item.id === list[0].id);
      if (check) {
        this.$message.error("已选择改商品,请勿重复选择");
      } else {
        this.json.push_goods_ids.push(...list);
        this.goodsDialog = false;
      }
    },
    deleteGoods(id) {
      this.json.push_goods_ids.some((item, i) => {
        if (item.id == id) {
          this.json.push_goods_ids.splice(i, 1);
          return true;
        }
      });
    },
  },
});
