define({
  name: "basic",
  template: `
  <div id="basicGoods">
    <div class="vue-main-title">
      <div class="vue-main-title-left"></div>
      <div class="vue-main-title-content">基础信息</div>
    </div>
    <div style="margin:0 auto;width:80%;">
      <el-form :rules="rules" :model="form" label-width="130px">
        <el-form-item label="排序" prop="sort" required>
          <el-input v-model="form.goods.display_order"></el-input>
          <div class="form-item_tips">数字大的排名在前,默认排序方式为创建时间，注意：输入最大数为9位数，只能输入数字</div>
        </el-form-item>
        <el-form-item label="商品名称" prop="title" required>
          <el-input v-model="form.goods.title"></el-input>
        </el-form-item>
         <el-form-item label="商品简称" prop="alias">
          <el-input v-model="form.goods.alias"></el-input>
           <div class="form-item_tips">用于打印电子面单，支付通道推送，不填默认用商品名称</div>
        </el-form-item>
        <el-form-item label="商品分类" required prop="category"  v-if="!attr_hide.hide_category">
          <el-row v-for="(categoryItem,itemIndex) in categoryList" style="margin-bottom:10px;" :key="itemIndex">
            <el-col :span="4" v-if="form.category_level * 1 >= 1">
              <el-select placeholder="请选择一级分类" v-model="categoryItem[0].id"  clearable filterable @change="btnfirst($event,itemIndex,categoryItem[0].id)" >
                <el-option :value="firstItem.id" :label="firstItem.name" v-for="(firstItem,firstIndex) in form.category_list" :key="firstItem.id">{{firstItem.name}}</el-option>
              </el-select>
            </el-col>
            <el-col :span="4" v-if="form.category_level * 1 >= 2">
              <el-select v-if="secondChildrensIsshow" placeholder="请选择二级分类" style="margin:0 10px;" v-model="categoryItem[1].id" clearable  filterable @change="btnSecond($event,itemIndex,categoryItem[1].id)" @click.native="btnChangeSecond($event,itemIndex,categoryItem[0])">
                <el-option :value="secondItem.id" :label="secondItem.name" v-for="(secondItem,secondIndex) in category_list_box[itemIndex].secondCategory" :key="secondItem.id">{{secondItem.name}}</el-option>
              </el-select>
            </el-col>
            <el-col :span="4" v-if="form.category_level * 1 >= 3">
              <el-select v-if="threeChildrensIsshow" placeholder="请选择三级分类" style="margin:0 10px;" v-model="categoryItem[2].id" clearable  filterable @change="btnThree($event,itemIndex)" @click.native="btnChangeThree($event,itemIndex,categoryItem[1],categoryItem[0])">
                <el-option :value="threeItem.id" :label="threeItem.name" v-for="(threeItem,threeIndex) in category_list_box[itemIndex].threeCategory" :key="threeItem.id">{{threeItem.name}}</el-option>
              </el-select>
            </el-col>
            <el-col :span="4" v-if="form.category_to_option_open==1&&form.goods.category_to_option.length!==0">
              <el-select  placeholder="请选择规格" style="margin:0 10px;" v-model="form.goods.category_to_option[itemIndex].goods_option_id" clearable  filterable>
                <el-option :value="optionItem.id" :label="optionItem.title" v-for="(optionItem,optionIndex) in form.options" :key="optionItem.id">{{optionItem.title}}</el-option>
              </el-select>
            </el-col>
            <el-col :span="4" >
              <el-button icon="el-icon-plus" v-if="itemIndex==0" @click="addCategory">添加分类</el-button>
              <el-button icon="el-icon-delete" v-else @click="removeCategory(itemIndex)"></el-button>
            </el-col>
          </el-row>
        </el-form-item>
        <el-form-item label="品牌" v-if="!attr_hide.hide_brand">
          <el-select v-model="form.goods.brand_id" placeholder="请选择" clearable filterable>
            <el-option
              v-for="item in form.brand"
              :key="item.id"
              :label="item.name"
              :value="item.id">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="商品类型" v-if="isGoodsType() == true" > 
          <el-radio-group v-model="form.goods.type">
            <el-radio v-if="(!attr_hide.appoint_type) || attr_hide.appoint_type === 1" :label="1">实体商品</el-radio>
            <el-radio v-if="(!attr_hide.appoint_type) || attr_hide.appoint_type === 2" :label="2">虚拟商品</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="下单是否需要地址" v-if="form.goods.type === 2 && form.goods.plugin_id != 154">
          <el-radio-group v-model="form.goods.need_address">
            <el-radio v-if="(!attr_hide.appoint_need_address) || attr_hide.appoint_need_address === 0" :label="0">需要地址</el-radio>
            <el-radio v-if="(!attr_hide.appoint_need_address) || attr_hide.appoint_need_address === 1" :label="1">不需要地址</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="商品单位" required>
          <el-input v-model="form.goods.sku"></el-input>
          <div class="form-item_tips">如: 个/件/包</div>
        </el-form-item>
        <el-form-item label="商品属性">
          <el-checkbox :true-label="1"  false-label="0"label="推荐"  v-model="form.goods.is_recommand" ></el-checkbox>
          <el-checkbox :true-label="1"  false-label="0"label="新上"  v-model="form.goods.is_new"></el-checkbox>
          <el-checkbox :true-label="1"  false-label="0"label="热卖"  v-model="form.goods.is_hot"></el-checkbox>
          <el-checkbox :true-label="1"  false-label="0"label="促销"  v-model="form.goods.is_discount"></el-checkbox>
        </el-form-item>
        <el-form-item label="商品图片" required prop="thumb">
          <div class="upload-boxed" @click="displaySelectMaterialPopup('thumb')">
            <img
              :src="form.goods.thumb_link"
              style="width: 150px; height: 150px; border-radius: 5px; cursor: pointer;object-fit:cover;"
              v-show="form.goods.thumb"
            />
            <div class="el-icon-plus" v-show="!form.goods.thumb"></div>
            <div class="upload-boxed-text">点击重新上传</div>
          </div>
          <div v-if="isDecorate" class="form-item_tips">建议尺寸: 640 * 640 ，或正方型图片 <span @click.stop="jumpUrl" style="color: #196dfa;font-weight: 600;margin-left: 15px;cursor:pointer;">图片智能在线设计</span><i class="el-icon-question" style="color:#196dfa;margin-left:2px;" @click="showIntroduce = true;"></i> </div>
        </el-form-item>
        <el-form-item label="其他图片">
          <div class="goods-image_list">
            <draggable v-model="form.goods.thumb_url">
              <div
                class="upload-boxed"
                v-for="(imageItem,itemIndex) in form.goods.thumb_url"
                :key="itemIndex"
              >
                <img
                  :src="imageItem.thumb_link"
                  style="width: 150px; height: 150px; border-radius: 5px; cursor: pointer;object-fit:cover;"
                />
                <i class="goods-images_remove el-icon-close" @click.stop="removeGoodsImage(itemIndex)"></i>
                <!-- <div class="upload-boxed-text"  @click="displaySelectMaterialPopup('other')">点击重新上传</div> -->
              </div>
            </draggable>
            <div
              class="upload-boxed goods-image_list-upload"
              @click="displaySelectMaterialPopup('other')"
            >
              <div class="el-icon-plus"></div>
              <div class="upload-boxed-text">点击上传图片</div>
            </div>
          </div>
          <div class="form-item_tips">建议尺寸: 640 * 640 ，或正方型图片</div>
        </el-form-item>
        <el-form-item label="首图视频">
          <el-row>
            <!-- <el-col :span="4">
              <video v-if="form.goods.goods_video" style="width: 150px;height:100px" controls="controls" class="" :src="form.goods.goods_video"></video>
            </el-col> -->
            <el-col :span="6">
              <div class="upload-boxed">
                <div class="el-icon-plus" v-show="!form.goods.goods_video_link"  @click="displaySelectMaterialPopup('video',3)"></div>
                <div>
                  <video v-if="form.goods.goods_video_link" style="width: 150px;height:119px;object-fit: cover;" controls="controls" class="" :src="form.goods.goods_video_link"></video>
                  <i class="goods-images_remove el-icon-close" style="top:-6px" v-if="form.goods.goods_video_link"  @click="removeVideo('video')"></i>
                </div>
                <div class="upload-boxed-text"  @click="displaySelectMaterialPopup('video',3)">点击重新上传视频</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="upload-boxed">
                <img
                  :src="form.goods.video_image_link"
                  style="width: 150px; height: 150px; border-radius: 5px; cursor: pointer;object-fit:cover;"
                  v-show="form.goods.video_image_link"
                  v-if="form.goods"
                />
                <i class="goods-images_remove el-icon-close"  v-if="form.goods.video_image_link" @click="removeVideo('image')"></i>
                <div class="el-icon-plus" v-show="!form.goods||!form.goods.video_image_link"  @click="displaySelectMaterialPopup('video_cover')"></div>
                <div class="upload-boxed-text"  @click="displaySelectMaterialPopup('video_cover')">点击重新上传封面</div>
              </div>
            </el-col>
          </el-row>
          <div class="form-item_tips">
            设置后商品详情首图默认显示视频，建议时长9-30秒
            <div>视频封面不上传默认商品主图</div>
          </div>
        </el-form-item>
        <el-form-item label="商品价格"  v-if="!attr_hide.hide_price">
          <el-row :gutter="10">
            <el-col :span="7">
              <el-input v-model="form.goods.price" :min="0" :disabled="readonly" oninput="if(value<0)value=0" type="number">
                <template slot="prepend">现价</template>
                <template slot="append">元</template>
              </el-input>
            </el-col>
            <el-col :span="7">
              <el-input v-model="form.goods.market_price" :min="0" :disabled="readonly" oninput="if(value<0)value=0" type="number">
                <template slot="prepend">原价</template>
                <template slot="append">元</template>
              </el-input>
            </el-col>
            <el-col :span="7">
              <el-input v-model="form.goods.cost_price" :min="0" :disabled="readonly" oninput="if(value<0)value=0" type="number">
                <template slot="prepend">成本</template>
                <template slot="append">元</template>
              </el-input>
            </el-col>
          </el-row>
          <div class="form-item_tips">尽量填写完整，有助于于商品销售的数据分析</div>
        </el-form-item>
        <el-form-item label="商品编码">
          <el-input v-model="form.goods.goods_sn"></el-input>
        </el-form-item>
        <el-form-item label="商品条码" v-if="!attr_hide.hide_product_sn">
          <el-input v-model="form.goods.product_sn"></el-input>
        </el-form-item>
        <el-form-item label="重量" v-if="!attr_hide.hide_weight">
          <el-input style="width:30%" v-model="form.goods.weight" :min="0" oninput="if(value<0)value=0" type="number">
            <template slot="append">克</template>
          </el-input>
          <div class="form-item_tips">商品重量设置为空或者0，则不计算重量</div>
        </el-form-item>
        <el-form-item label="体积" v-if="!attr_hide.hide_volume">
          <el-input style="width:30%" v-model="form.goods.volume" :min="0" oninput="if(value<0)value=0" type="number">
            <template slot="append">m³</template>
          </el-input>
        </el-form-item>
        <el-form-item label="第三方销量" required prop="virtualSales">
          <el-input style="width:30%" v-model="form.goods.virtual_sales">
            <template slot="append">件</template>
          </el-input>
          <div class="form-item_tips">第三方销量为平台再其他第三方平台销量同步汇总，前端显示销量=第三方销量+商城销量</div>
        </el-form-item>
        <template v-if="form.goods.hide_goods_sales_switch">
        <el-form-item label="总销量">
          <el-switch v-model="form.goods.hide_goods_sales_alone" :active-value="1" :inactive-value="0"></el-switch>
          <div class="form-item_tips">
            开启后，商品详情隐藏总销量走独立设置，不受默认设置限制
          </div>
        </el-form-item>
         <el-form-item v-if="form.goods.hide_goods_sales_alone" label="隐藏总销量">
          <el-switch v-model="form.goods.hide_goods_sales" :active-value="1" :inactive-value="0"></el-switch>
          <div class="form-item_tips">
            开启后，商品详情隐藏总销量，只对自营商品与拍卖商品起效
          </div>
        </el-form-item>
</template>
        <el-form-item label="库存" required prop="stock">
          <el-input style="width:30%" v-model="form.goods.stock">
            <template slot="append">件</template>
          </el-input>
          <div class="form-item_tips">商品的剩余数量, 如启用多规格或为虚拟卡密产品，则此处设置无效，请移至“商品规格”或“虚拟物品插件”中设置</div>
        </el-form-item>
        <el-form-item label="减库存方式">
          <el-radio-group v-model="form.goods.reduce_stock_method">
            <el-radio :label="0">拍下减库存</el-radio>
            <el-radio :label="1">付款减库存</el-radio>
            <el-radio :label="2">永不减库存</el-radio>
          </el-radio-group>
          <div class="form-item_tips">
            拍下减库存：买家拍下商品即减少库存，存在恶拍风险。秒杀、超低价等热销商品，如需避免超卖可选此方式。
            <div>付款减库存：买家拍下并完成付款方减少库存，存在超卖风险。如需减少恶拍、提高回款效率，可选此方式。</div>
            <div>订单支付前关闭或取消返还库存,订单支付后退换货的订单不返还库存。</div>
          </div>
        </el-form-item>
        <el-form-item label="预扣库存">
          <el-input style="width:30%" disabled v-model="form.goods.withhold_stock">
            <template slot="append">件</template>
          </el-input>
          <div class="form-item_tips">
            拍下减库存时,下单即锁定商品库存。
            <div>付款减库存时,用户点击支付订单后,1分钟内锁定商品库存。</div>
            <div>存在预扣库存时，无法编辑商品库存和减库存方式设置。</div>
          </div>
        </el-form-item>
        <el-form-item label="商品前端隐藏">
          <el-switch v-model="form.goods.is_hide" :active-value="2" :inactive-value="1"></el-switch>
          <div class="form-item_tips">
            开启后，该商品在前端列表中不显示，也不能在搜索框中搜索出该商品，可以通过分享链接购买
          </div>
        </el-form-item>
        <el-form-item label="不可退款退货">
          <el-switch v-model="form.goods.no_refund" :active-value="1" :inactive-value="0"></el-switch>
        </el-form-item>
        <el-form-item label="是否上架" v-if="!attr_hide.hide_status">
          <el-switch v-model="form.goods.status" :active-value="1" :inactive-value="0"></el-switch>
        </el-form-item>
      </el-form>
    </div>
    <upload-multimedia-img
      :upload-show="showSelectMaterialPopup"
      :type="materialType"
      :name="formFieldName"
      :selNum="selNum"
      :select="select"
      @replace="showSelectMaterialPopup = !showSelectMaterialPopup"
      @sureSelect="sureSelectImg"
      @sure="selectedMaterial"
    ></upload-multimedia-img>
    <introduce v-model="showIntroduce" v-show="showIntroduce"></introduce>
  </div>`,
  style: `
  .goods-image_list {
    margin-right:20px;
  }
  .goods-image_list .upload-boxed {
    position:relative;
  }
  .goods-image_list > div {
    display:inline-flex;
    flex-wrap:wrap;
    column-gap:10px;
    row-gap:10px;
  }
  #basicGoods input::-webkit-outer-spin-button,
  #basicGoods input::-webkit-inner-spin-button {
    -webkit-appearance: none;
  }
  #basicGoods input[type="number"] {
    -moz-appearance: textfield;
  }
  `,
  props: {
    form: {
      default() {
        return ;
      },
    },
    formKey: {
      type: String,
      default: "goods",
    },
    attr_hide:{
      default() {
        return {}
      }
    }
  },
  data() {
    let regular = new RegExp("^[0-9][0-9]*$")
    let checkoutSort = (rule, value, callback) => {
      this.sortRegular = true
      let sort = regular.test(this.form.goods.display_order);
      if(!sort){
        callback(new Error('请输入正整数'));
        this.sortRegular = false
      }
    };
    let checkoutTitle = (rule, value, callback) => {
      this.titleRegular = true
      if(!this.form.goods.title){
        callback(new Error('请输入商品名称'));
        this.titleRegular = false
      }
    };
    // let checkoutSku = (rule, value, callback) => {
    //   let sku = true
    //   if(this.form.goods.sku !== '个' || this.form.goods.sku !== '件' || this.form.goods.sku !== '包'){
    //     sku = false
    //   }else{
    //     sku = true
    //   }
    //   this.skuRegular = true
    //   if(!sku){
    //     callback(new Error('个/件/包'));
    //     this.skuRegular = false
    //   }
    // };
    let checkoutCategory = (rule, value, callback) => {
      if(!this.attr_hide.hide_category){
        let info  = true
        this.categoryList.forEach(item => {
          item.forEach(el => {
            if(!el.id){
              info = false
              return
            }
          })
        })
        this.categoryRegular = true
        if(!info){
          callback(new Error('请选择商品分类'));
          this.categoryRegular = false
        }
      }else{
        this.categoryRegular = true
      }
    };
    let checkoutThumb = (rule, value, callback) => {
      this.thumbRegular = true
      if(!this.form.goods.thumb){
        callback(new Error('请选择商品图片'));
        this.thumbRegular = false
      }
    };
    let checkoutVirtualSales = (rule, value, callback) => {
        let virtualSales = regular.test(this.form.goods.virtual_sales);
        this.virtualSalesRegular = true
        if(!virtualSales){
          callback(new Error('请输入正整数'));
          this.virtualSalesRegular = false
        }
    };
    let checkoutStock = (rule, value, callback) => {
      let stock = regular.test(this.form.goods.stock);
      this.stockRegular = true
      if(!stock){
        callback(new Error('请输入正整数'));
        this.stockRegular = false
      }
    };
    return {
      readonly:readonly,
      showIntroduce:false,
      showSelectMaterialPopup: false,
      formFieldName: "",
      goodsImagesChangeIndex: null,
      materialType: "",
      selNum: "one",
      select:"open",
      rules:{
        sort:{ validator: checkoutSort},
        title:{validator:checkoutTitle},
        category:{validator:checkoutCategory},
        // goodsSku:{validator:checkoutSku},
        thumb:{validator:checkoutThumb},
        virtualSales:{validator:checkoutVirtualSales},
        stock:{validator:checkoutStock},
      },
      submitPropertyKeyWhiteList: [],
      // yesRegular:true,
      categoryList:[],
      // 原始值
      OriginCategory:[],
      // 改变后的值
      changeCategoryList:[],
      // 分类值
      category_list_box:[],
      isShow:true,

      sortRegular:true,
      titleRegular:true,
      // skuRegular:true,
      categoryRegular:true,
      virtualSalesRegular:true,
      stockRegular:true,
      thumbRegular:true,
      // 提交的数据

      threeChildrensIsshow:true,
      secondChildrensIsshow:true,
      isDecorate:IsDecorate,
    };
  },
  mounted() {
    this.addDefaul()
    // 设置默认品牌
    if(this.form.goods.brand_id === 0){
      this.form.goods.brand_id = ""
    }

    this.goodsCategoryShow()
    this.getCategoryValue()
  },
  methods: {
    //是否显示商品类型
    isGoodsType(){
       let plugin_ids = [140, 154, 157];

       if (plugin_ids.indexOf(this.form.goods.plugin_id) === -1){
         return true;
       }else{
         return  false;
       }
    },
    jumpUrl() {
      window.open(CktUrl);
    },
    // 添加默认值
    addDefaul(){

      if(JSON.stringify(this.attr_hide) !== '[]'){

        if(this.attr_hide.appoint_type){
          this.form.goods.type = this.attr_hide.appoint_type
        }
        if(this.attr_hide.appoint_need_address){
          this.form.goods.need_address = this.attr_hide.appoint_need_address
        }
      }
      if(!this.form.goods.display_order){
        this.$set(this.form.goods,"display_order",0)
      }
      if(!this.form.goods.category_to_option){
        this.$set(this.form.goods,"category_to_option",[{goods_option_id:""}])
      }
      if(!this.form.goods.type){
        this.$set(this.form.goods,"type",1)
      }
      if(!this.form.goods.need_address){
        this.$set(this.form.goods,"need_address",0)
      }
      if(!this.form.goods.price){
        this.$set(this.form.goods,"price",0)
      }
      if(!this.form.goods.market_price){
        this.$set(this.form.goods,"market_price",0)
      }
      if(!this.form.goods.cost_price){
        this.$set(this.form.goods,"cost_price",0)
      }
      if(!this.form.goods.weight){
        this.$set(this.form.goods,"weight",0)
      }
      if(!this.form.goods.volume){
        this.$set(this.form.goods,"volume",0)
      }
      if(!this.form.goods.reduce_stock_method){
        this.$set(this.form.goods,"reduce_stock_method",0)
      }
      if(!this.form.goods.withhold_stock){
        this.$set(this.form.goods,"withhold_stock",0)
      }
      if(!this.form.goods.is_hide){
        this.$set(this.form.goods,"is_hide",1)
      }
      if(!this.form.goods.no_refund){
        this.$set(this.form.goods,"no_refund",0)
      }
      if(!this.form.goods.cost_price){
        this.$set(this.form.goods,"cost_price",0)
      }
      if(!this.form.goods.virtual_sales){
        this.$set(this.form.goods,"virtual_sales",0)
      }
    },
    // 商品分类回显
    goodsCategoryShow(){
      // 判断回显时商品类型是否存在，不存在根据category_level长度添加数据--this.judgeCategoryLevel()
      // 如果category_level的长度与返回的长度不一致，则不全默认值
      if(this.form.goods.category){
        this.OriginCategory = JSON.parse(JSON.stringify(this.form.goods.category))
        if(this.form.category_level == 3){
          this.OriginCategory.forEach(item => {
            if(item.length === 2){
              item.push({ "id": '', "level": 3 })
            }
          })
        }else if(this.form.category_level == 2){
          this.OriginCategory.forEach(item => {
            if(item.length === 1){
              item.push({ "id": '', "level": 2 })
            }
          })
        }
      }else{
        this.judgeCategoryLevel()
      }
      if(!this.form.goods.category){ this.isShow = false }

      if(this.form.goods.category){
        if(this.form.goods.category.length){
          if(this.form.goods.category[0].length){
            for(item of this.form.goods.category){
              item.forEach(cateItem => {
                let id = cateItem.id
                cateItem.id = cateItem.name
                cateItem.name = id
              })
              item = item.map(el => ({'id':el.id , 'level':el.level,'name':el.name}))
              // 判断是否有一级分类
              let firstLevel  = item.find(threeItem => threeItem.level === 1)
              if(this.form.category_level * 1 === 1 && !firstLevel){
                item.push({'id':'' , 'level':1})
              }
              // 判断是否有二级分类
              let secondLevel  = item.find(threeItem => threeItem.level === 2)
              if(this.form.category_level * 1 === 2 && !secondLevel){
                item.push({'id':'' , 'level':2})
              }
              // 判断是否有三级分类
              let threeLevel  = item.find(threeItem => threeItem.level === 3)
              if(this.form.category_level * 1 === 3 && !threeLevel){
                item.push({'id':'' , 'level':3})
              }
              this.categoryList.push(item)
              this.changeCategoryList = this.categoryList
            }
          }else{
            this.judgeCategoryLevel()
          }
        }else{
          this.judgeCategoryLevel()
        }
      }else{
        this.judgeCategoryLevel()
        this.changeCategoryList = this.categoryList
      }
    },
    // 获取分类值
    getCategoryValue(){
      this.category_list_box = []
      this.categoryList.forEach((item,index) => {
        let filterCategry = []
        filterCategry.push({secondCategory:[]})
        filterCategry.push({threeCategory:[]})
        this.category_list_box.push(filterCategry)
      })
    },
    // 判断分类等级
    judgeCategoryLevel(){
      switch (this.form.category_level * 1){
        case  1 :
          this.categoryList = [[{ "id": '', "level": 1 }]];
          this.OriginCategory = this.categoryList
          break;
        case  2 :
          this.categoryList = [[{ "id": '', "level": 1 }, { "id": '', "level": 2 }]];
          this.OriginCategory = this.categoryList
          break;
        default:
          this.categoryList = [[{ "id": '', "level": 1 }, { "id": '', "level": 2 }, { "id": '', "level": 3 }]];
          this.OriginCategory = this.categoryList
        break;
      }
    },
    displaySelectMaterialPopup(fieldName = "thumb", type = 1) {
      if(fieldName == 'other'){
        this.selNum = 'more'
      }else{
        this.selNum = 'one'
      }
      this.formFieldName = fieldName;
      this.showSelectMaterialPopup = !this.showSelectMaterialPopup;
      this.materialType = String(type);
    },
    selectedMaterial(name, image, imageUrl) {
      let originalImageUrl = JSON.parse(JSON.stringify(imageUrl))
      if(name == "video"){
        if(typeof imageUrl == 'string') {
          this.form.goods.goods_video_link = imageUrl;
          this.form.goods.goods_video = imageUrl;
          return
        }
        this.form.goods.goods_video_link = imageUrl[0].url
        this.form.goods.goods_video = originalImageUrl[0].attachment
      }
      if(name == "video_cover"){
        this.form.goods.video_image_link = imageUrl[0].url
        this.form.goods.video_image = originalImageUrl[0].attachment
      }
      if (Array.isArray(imageUrl)) {
        imageUrl = imageUrl.map((item) => {
          return item.url;
        });
      }
      if (this.formFieldName === "other") {
        if (this.goodsImagesChangeIndex === null) {
          if(!this.form.goods.thumb_url){
            this.$set(this.form.goods,"thumb_url",[])
          }
          for(let item of originalImageUrl){
            this.form.goods["thumb_url"].push({
              thumb_link:item.url,
              thumb:item.attachment
            });
          }
        } else {
          // this.form.goods["thumb_url"][this.goodsImagesChangeIndex] = imageUrl[0];
          this.goodsImagesChangeIndex = null;
        }
      } else {
        this.form.goods[this.formFieldName] = originalImageUrl[0].attachment;
        if(this.formFieldName == 'thumb') {
          this.form.goods['thumb_link'] = imageUrl[0]
        }
      }
    },
    // 点击图片选中
    sureSelectImg(val,id,item){
      // console.log(val,id,item,'点击图片选中');
      // if(this.formFieldName == "video_cover"){
      //   this.form.goods.video_image = item.url
      // }
      // if(this.formFieldName == "thumb"){
      //   this.form.goods[this.formFieldName] = item.url
      // }
    },
    changeGoodsImage(index,val) {
      this.goodsImagesChangeIndex = index;
      this.displayUploadImagePopup("images");
    },
    addCategory() {
      switch (this.form.category_level * 1){
        case  1 :
          this.categoryList.push([{ "id": '', "level": 1 }]);
          if(this.isShow){this.OriginCategory.push([{ "id": '', "level": 1 }])}
          break;
        case  2 :
          this.categoryList.push([{ "id": '', "level": 1 }, { "id": '', "level": 2 }]);
          if(this.isShow){this.OriginCategory.push([{ "id": '', "level": 1 }, { "id": '', "level": 2 }])}
          break;
        default:
          this.categoryList.push([{ "id": '', "level": 1 }, { "id": '', "level": 2 }, { "id": '', "level": 3 }]);
          if(this.isShow){this.OriginCategory.push([{ "id": '', "level": 1 }, { "id": '', "level": 2 },{ "id": '', "level": 3 }])}
        break;
      }
      this.form.goods.category_to_option.push({'goods_option_id':""});
      this.getCategoryValue()
    },
    removeCategory(itemIndex) {
      this.categoryList.splice(itemIndex, 1);
      this.category_list_box.splice(itemIndex,1)
      this.OriginCategory.splice(itemIndex,1)
      this.form.goods.category_to_option.splice(itemIndex,1);
    },
    // 视频删除
    removeVideo(type){
      if(type == 'video'){
        this.form.goods.goods_video = ""
        this.form.goods.goods_video_link = ""
      }
      if(type == 'image'){
        this.form.goods.video_image = ""
        this.form.goods.video_image_link = ""
      }
      this.$forceUpdate()
    },
    removeGoodsImage(itemIndex) {
      this.form.goods.thumb_url.splice(itemIndex, 1);
    },
    validate() {
      let submitCategoryList = []
      this.OriginCategory.forEach(element => {
        element = element.map(item => {
          delete item.name
        })
      })
      this.OriginCategory.forEach(item =>{
        let filterCatrgorys = item.filter((value, index, arr) => {
          return value.id !== ''
        })
        submitCategoryList.push(filterCatrgorys)
      })
      // 过滤空数组
      let filterSubmitCategoryList = []
      submitCategoryList.forEach((element,index) => {
        if(element.length !== 0){
          filterSubmitCategoryList.push(element)
        }
      });
      // 过滤商品分类重复的数据
      let idList = []
      filterSubmitCategoryList.forEach(item => {
        if(item.length >= this.form.category_level){
          idList.push(item[this.form.category_level * 1 - 1].id)
        }else{
          idList = []
        }
      })
      if(!this.attr_hide.hide_category){
        if(new Set(idList).size !== idList.length || idList.length == 0){
          this.$message({
            message: '请选择不同的商品分类',
            type: 'warning'
          });
          this.categoryRegular = false
        }else{
          this.categoryRegular = true
        }
      }else{ this.categoryRegular = true }
      this.tipValidator();

      if(this.form.goods.type == 1){
        this.form.goods.need_address = 0
      }
      let thumb_url = [];
      // 过滤其他图片http字段
      if(this.form.goods.thumb_url !== undefined ){
        for(let item of this.form.goods.thumb_url){
          thumb_url.push(item.thumb);
        }
      }
      if (this.categoryRegular && this.sortRegular && this.titleRegular && this.virtualSalesRegular && this.stockRegular && this.thumbRegular && this.form.goods.sku) {
        let saveGoods = {
          brand_id:this.form.goods.brand_id ? this.form.goods.brand_id : "",
          category: filterSubmitCategoryList,
          display_order:this.form.goods.display_order,
          title:this.form.goods.title,
          alias:this.form.goods.alias? this.form.goods.alias :'',
          type:this.form.goods.type,
          need_address: this.form.goods.need_address,
          sku:this.form.goods.sku,
          is_recommand:this.form.goods.is_recommand,
          is_new:this.form.goods.is_new,
          is_hot:this.form.goods.is_hot,
          is_discount:this.form.goods.is_discount,
          thumb:this.form.goods.thumb,
          thumb_url:thumb_url,
          price:this.form.goods.price,
          market_price:this.form.goods.market_price,
          cost_price:this.form.goods.cost_price,
          goods_sn:this.form.goods.goods_sn ? this.form.goods.goods_sn : "",
          product_sn:this.form.goods.product_sn ? this.form.goods.product_sn : "",
          weight:this.form.goods.weight,
          volume:this.form.goods.volume,
          virtual_sales:this.form.goods.virtual_sales,
          stock:this.form.goods.stock,
          reduce_stock_method:this.form.goods.reduce_stock_method,
          is_hide:this.form.goods.is_hide,
          no_refund:this.form.goods.no_refund,
          video_image:this.form.goods.video_image ? this.form.goods.video_image : "",
          goods_video:this.form.goods.goods_video ? this.form.goods.goods_video : "",
          withhold_stock:this.form.goods.withhold_stock,
          category_to_option:this.form.goods.category_to_option,
          hide_goods_sales:this.form.goods.hide_goods_sales,
          hide_goods_sales_alone:this.form.goods.hide_goods_sales_alone,
        };

        if(!this.attr_hide.hide_status){
          saveGoods.status = this.form.goods.status;
        }
        return saveGoods;
      } else {
        return false;
      }
    },
    tipValidator(){
      if(!this.sortRegular){
        this.$message({
          message: '请输入排序',
          type: 'warning'
        });
        return
      }else if(!this.form.goods.title){
        this.$message({
          message: '请输入商品名称',
          type: 'warning'
        });
        return
      }else if(!this.form.goods.sku){
        this.$message({
          message: '请填写商品单位',
          type: 'warning'
        });
      }else if(!this.form.goods.thumb){
        this.$message({
          message: '请选择商品图片',
          type: 'warning'
        });
        return
      }else if(!this.virtualSalesRegular && this.form.goods.virtual_sales !== ''){
        this.$message({
          message: '请输入第三方销量',
          type: 'warning'
        });
        return
      }else if(!this.stockRegular){
        this.$message({
          message: '请输入库存',
          type: 'warning'
        });
        return
      }
      // if(!this.form.goods.virtual_sales){this.virtualSalesRegular = false }
      // if(!this.form.goods.stock){this.stockRegular = false }
    },
    // extraDate(){
    //   return {
    //     'extra':"额外数据"
    //   }
    // },
    // 监听一级商品分类
    btnfirst(value,itemIndex,firstValue){
      if(!firstValue){
        this.category_list_box[itemIndex].secondCategory = []
        this.category_list_box[itemIndex].threeCategory = []
        this.categoryList[itemIndex].forEach(item => {
          item.id  = ""
        })
      }
      this.categoryList[itemIndex].forEach((item,key) => {
        if(key == 0){
          item.id  = item.id
        }else{
          item.id  = ""
        }
      })
      // 点击一级分类获取二级分类数据
      this.form.category_list.forEach(item => {
        if(item.id == value){
          // this.secondCategory = item.childrens
          this.category_list_box[itemIndex].secondCategory =  item.childrens
          return
        }
      })
      this.listenCategory(value,itemIndex,0)
    },
    // 监听二级商品分类
    btnChangeSecond(value,itemIndex,secondValue){
      let category_list = JSON.parse(JSON.stringify(this.form.category_list))
      let secondChildrens = []
      if(typeof secondValue.id == "string" && secondValue.id){
        this.secondChildrensIsshow = false
        category_list.forEach((item,index) => {
          if(item.id == secondValue.name){
            secondChildrens.push(...item.childrens)
            return
          }
        })
        this.category_list_box[itemIndex].secondCategory =  secondChildrens
        this.secondChildrensIsshow = true
      }
    },
    btnSecond(value,itemIndex,secondValue){
      if(!secondValue){
        this.category_list_box[itemIndex].threeCategory = []
      }
      this.categoryList[itemIndex].forEach((item,key) => {
        if(key === 2){
          item.id = ''
        }
      })
      // 点击二级分类获取三级分类数据
      if(this.category_list_box[itemIndex].secondCategory){
        this.category_list_box[itemIndex].secondCategory.forEach((item,index) => {
          if(item.id == value ){
            // this.threeCategory = item.childrens
            this.category_list_box[itemIndex].threeCategory =  item.childrens
            return
          }
        })
      }
      this.listenCategory(value,itemIndex,1)
    },
    // 监听三级商品分类
    btnChangeThree(value,itemIndex,secondValue,oneValue){
      let category_list = JSON.parse(JSON.stringify(this.form.category_list))
      let threeChildrens = []
      if(typeof secondValue.id == "string" && secondValue.id){
        this.threeChildrensIsshow = false
        category_list.forEach((item,index) => {
          if(item.id == oneValue.name){
            item.childrens.forEach((el,key) => {
              if(el.id == secondValue.name){
                threeChildrens.push(...el.childrens)
                return
              }
            })
            return
          }
        })
        this.category_list_box[itemIndex].threeCategory =  threeChildrens
        this.threeChildrensIsshow = true
      }
    },
    btnThree(value,itemIndex){
      this.listenCategory(value,itemIndex,2)
    },
    listenCategory(value,itemIndex,type){
      let info = false
      this.changeCategoryList[itemIndex].forEach((el,key) => {
        if(el.id === this.categoryList[itemIndex][key].id){
          info = true
          return
        }
      })
      if(this.isShow){
        if(info){
          let categoryBoxList = []
          this.OriginCategory[itemIndex].forEach((item,key) => {
            if(type == 2){
              if(key === 2){
                item = this.categoryList[itemIndex][key]
              }
            }else if(type == 1){
              if(key === 2 || key === 1){
                item = this.categoryList[itemIndex][key]
              }
            }else if(type == 0){
              if(key === 2 || key === 1 || key === 0){
                item = this.categoryList[itemIndex][key]
                // item.id = ""
              }
            }

            categoryBoxList.push(item)
          })
          this.OriginCategory[itemIndex] = categoryBoxList
        }else{
          this.OriginCategory[itemIndex] = this.categoryList[itemIndex]
        }
      }
    }
  },
});
