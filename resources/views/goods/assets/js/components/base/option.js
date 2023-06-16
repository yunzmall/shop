define({
  template: `
  <div>
    <div class="vue-main-title">
      <div class="vue-main-title-left"></div>
      <div class="vue-main-title-content">商品规格</div>
    </div>
    <div class="option-box">是否启用商品规格：<el-switch v-model="has_option"></el-switch> <el-button type="text" @click="validate"></el-button></div>
    <div class="tips-box">
    1、启用商品规格后，商品的价格及库存以商品规格为准 <br/>
    2、拼团活动创建后的商品，请勿对该商品进行修改规格、下架、删除等操作，否则影响下单购买，活动结束可正常编辑该商品！<br/>
    3、每一种规格代表不同型号，例如颜色为一种规格，尺寸为一种规格，如果设置多规格，手机用户必须每一种规格都选择一个规格项，才能添加购物车或购买。<br/>
    </div>
    <el-form label-width="100px" v-if="has_option">
      <el-form-item label="商品规格">
        <div class="goods-spec_info-forms">
          <div class="goods-spec_info-form" v-for="(specItem,specItemIndex) in tableColumnList.goodsSpecs" :key="specItemIndex">
            <div class="goods-spec_info_form-item">
              <div class="goods-sped_info_form-name">
                规格名：
              </div>
              <div class="goods-spec_info_form-content">
                <el-input v-model.trim="specItem.attr" ref="specValueInput" placeholder="（比如颜色）" @focus="attrFocus(specItem.attr)" @blur="attrBlur(specItem.attr)"></el-input>
                <!-- <div>
                  <el-checkbox>添加规格图片</el-checkbox>
                  <el-checkbox>规格图片展示在商详页和规格选择页</el-checkbox>
                </div> -->
              </div>
            </div>
            <div class="goods-spec_info_form-item">
              <div class="goods-sped_info_form-name">
                规格值：
              </div>
              <div class="goods-spec_info_form-content">
                <div class="goods-spec_info-values">
                  <div class="goods-spec_info_value-item" v-for="(valueItem,valueItemIndex) in specItem.valueList" :key="valueItemIndex">
                    <el-input v-model.trim="specItem.valueList[valueItemIndex].title" ref="attrValueInput" @focus="attrValueFocus(specItem.valueList[valueItemIndex].title)" @blur="newAttrValueBlur(specItem.attr, specItem.valueList[valueItemIndex].title,specItemIndex,valueItemIndex)"></el-input>
                    <!-- <div
                      class="upload-boxed"
                      @click="selectedingImageSpecIndex=specItemIndex;selectedingImageSpecValueIndex=valueItemIndex;showSelectSpecImageDialog=!showSelectSpecImageDialog"
                    >
                      <img :src="valueItem.thumb" v-show="valueItem.thumb" />
                      <div class="el-icon-plus" v-show="!valueItem.thumb"></div>
                    </div> -->
                    <div class="el-icon-close goods-spec_info_value-remove" v-if="specItem.valueList.length > 1" @click="removeSpecValue(specItemIndex,valueItemIndex, specItem.attr, specItem.valueList[valueItemIndex].title)"></div>
                  </div>
                  <div class="goods-spec_info_value-item">
                    <el-button @click="addSpecValue(specItemIndex)" :disabled="specItem.valueList.filter(value => value.title === '').length > 0">添加规格值</el-button>
                  </div>
                </div>
                <!-- <div style="line-height:20px;color:#ccc;">
                  仅支持为第一组规格设置规格图片（最多40张图），买家选择不同规格会看到对应规格图片，建议尺寸：800 x 800像素
                </div> -->
              </div>
            </div>
            <div class="el-icon-close goods-spec_info-remove" @click="removeSpec(specItemIndex)"></div>
          </div>
          <div class="goods-spec_info-form" style="margin-bottom: 0;">
            <div class="goods-spec_info_form-item">

              <el-popover placement="top" width="240" v-model="add_popover_bool" @after-enter="$refs.addValueInput.focus()">
                <div style="display: flex; grid-gap: 10px;">
                  <el-input ref="addValueInput" v-model.trim="add_value" @keyup.enter.native="addSpec()" />
                  <el-button type="primary" @click="addSpec()">确定</el-button>
                </div>
                <el-button slot="reference" type="text" style="margin-right: 75px;">添加规格项</el-button>
              </el-popover>

              <el-button type="text" @click="clearALL">清空全部规格项</el-button>
            </div>
          </div>
        </div>
      </el-form-item>
      <el-form-item label="批量设置">
        <div class="goods-spec_info-forms">
          <el-popover placement="top" width="240" trigger="click">
            <div style="display: flex; grid-gap: 10px;">
              <el-input placeholder="请输入库存" v-model.trim="batch_input" @keyup.enter.native="batchSet('stock')" />
              <el-button type="primary" @click="batchSet('stock')">确定</el-button>
            </div>
            <el-button slot="reference" type="text" style="margin-right: 25px;">设置库存</el-button>
          </el-popover>
          <el-popover placement="top" width="240" trigger="click">
            <div style="display: flex; grid-gap: 10px;">
              <el-input placeholder="请输入市场价格" v-model.trim="batch_input" @keyup.enter.native="batchSet('market_price')" />
              <el-button type="primary" @click="batchSet('market_price')">确定</el-button>
            </div>
            <el-button slot="reference" type="text" style="margin-right: 25px;">设置市场价格</el-button>
          </el-popover>
          <el-popover placement="top" width="240" trigger="click">
            <div style="display: flex; grid-gap: 10px;">
              <el-input placeholder="请输入现价" v-model.trim="batch_input" @keyup.enter.native="batchSet('product_price')" />
              <el-button type="primary" @click="batchSet('product_price')">确定</el-button>
            </div>
            <el-button slot="reference" type="text" style="margin-right: 25px;">设置现价</el-button>
          </el-popover>
          <el-popover placement="top" width="240" trigger="click">
            <div style="display: flex; grid-gap: 10px;">
              <el-input placeholder="请输入成本价格" v-model.trim="batch_input" @keyup.enter.native="batchSet('cost_price')" />
              <el-button type="primary" @click="batchSet('cost_price')">确定</el-button>
            </div>
            <el-button slot="reference" type="text" style="margin-right: 25px;">设置成本价格</el-button>
          </el-popover>
          <el-popover placement="top" width="240" trigger="click">
            <div style="display: flex; grid-gap: 10px;">
              <el-input placeholder="请输入商品编码" v-model.trim="batch_input" @keyup.enter.native="batchSet('goods_sn')" />
              <el-button type="primary" @click="batchSet('goods_sn')">确定</el-button>
            </div>
            <el-button slot="reference" type="text" style="margin-right: 25px;">设置商品编码</el-button>
          </el-popover>
          <el-popover placement="top" width="240" trigger="click">
            <div style="display: flex; grid-gap: 10px;">
              <el-input placeholder="请输入商品条码" v-model.trim="batch_input" @keyup.enter.native="batchSet('product_sn')" />
              <el-button type="primary" @click="batchSet('product_sn')">确定</el-button>
            </div>
            <el-button slot="reference" type="text" style="margin-right: 25px;">设置商品条码</el-button>
          </el-popover>
          <el-popover placement="top" width="240" trigger="click">
            <div style="display: flex; grid-gap: 10px;">
              <el-input placeholder="请输入重量" v-model.trim="batch_input" @keyup.enter.native="batchSet('weight')" />
              <el-button type="primary" @click="batchSet('weight')">确定</el-button>
            </div>
            <el-button slot="reference" type="text" style="margin-right: 25px;">设置重量</el-button>
          </el-popover>
          <el-popover placement="top" width="240" trigger="click">
            <div style="display: flex; grid-gap: 10px;">
              <el-input placeholder="请输入体积" v-model.trim="batch_input" @keyup.enter.native="batchSet('volume')" />
              <el-button type="primary" @click="batchSet('volume')">确定</el-button>
            </div>
            <el-button slot="reference" type="text" style="margin-right: 25px;">设置体积</el-button>
          </el-popover>
          <el-button type="text" @click="openSortDialog" >自定义规格排序</el-button>
        </div>
      </el-form-item>
      <el-form-item label="规格明细">
        <el-table :data="tableColumnList.table_data" border :span-method="mergeRowComputed">
          <el-table-column :label="specItem.attr" v-for="(specItem,itemIndex) in tableColumnList.goodsSpecs" :key="itemIndex" :prop="specItem.attr">
            <template slot-scope="scope">
              <div>{{scope.row[specItem.attr]}}</div>
            </template>
          </el-table-column>
          <el-table-column label="库存" prop="stock">
            <template slot-scope="scope">
              <el-input v-model="scope.row.stock" :ref="'stockValueInput' + [[scope.$index]]" placeholder="0"></el-input>
            </template>
          </el-table-column>
          <el-table-column label="预扣库存" prop="withhold_stock">
            <template slot-scope="scope">
              <el-input v-model="scope.row.withhold_stock" disabled placeholder="0"></el-input>
            </template>
          </el-table-column>
          <el-table-column label="市场价格" prop="market_price">
            <template slot-scope="scope">
              <el-input v-model="scope.row.market_price" :disabled="readonly" placeholder="0.00"></el-input>
            </template>
          </el-table-column>
          <el-table-column label="现价" prop="product_price">
            <template slot-scope="scope">
              <el-input v-model="scope.row.product_price" :disabled="readonly" :ref="'priceValueInput' + [[scope.$index]]" placeholder="0.00"></el-input>
            </template>
          </el-table-column>
          <el-table-column label="成本价" prop="cost_price">
            <template slot-scope="scope">
              <el-input v-model="scope.row.cost_price" :disabled="readonly" placeholder="0.00"></el-input>
            </template>
          </el-table-column>
          <el-table-column label="商品编码" prop="goods_sn">
            <template slot-scope="scope">
              <el-input v-model="scope.row.goods_sn" ></el-input>
            </template>
          </el-table-column>
          <el-table-column label="商品条码" prop="product_sn">
            <template slot-scope="scope">
              <el-input v-model="scope.row.product_sn" ></el-input>
            </template>
          </el-table-column>
          <el-table-column label="重量（克）" prop="weight">
            <template slot-scope="scope">
              <el-input v-model="scope.row.weight" placeholder="0.000"></el-input>
            </template>
          </el-table-column>
          <el-table-column label="体积（m³）" prop="volume">
            <template slot-scope="scope">
              <el-input v-model="scope.row.volume" placeholder="0.000" ></el-input>
            </template>
          </el-table-column>
          <el-table-column label="图片（300*300）" prop="thumb">
            <template slot-scope="scope">
              <i class="el-icon-circle-close" v-show="scope.row.thumb" style="top: -3px;left: 70px;position: relative;z-index: 10;" @click="delUploadImg(scope.$index)"></i>
              <div class="upload-boxed spec-image" @click="selectedingImageSpecIndex=scope.$index;showSelectSpecImageDialog=!showSelectSpecImageDialog;materialType = '1'">
                <img
                  :src="scope.row.thumb"
                  style="width: 60px; height: 60px; border-radius: 5px; cursor: pointer;object-fit:cover;"
                  v-show="scope.row.thumb"
                />
                <div class="el-icon-plus" v-show="!scope.row.thumb"></div>
              </div>
            </template>
          </el-table-column>
        </el-table>
        <p>数量：{{ specCounts }}条（建议不超过100条）</p>
      </el-form-item>
    </el-form>
    <el-dialog :visible.sync="sortDialogVisible" width="40%">
      <template slot="title">
        <div style="font-size: 14px;">
          拖动规格项或规格值进行排序
        </div>
      </template>
      <div>
        <draggable v-model="sortGoodsSpecs">
          <ul class="attribute-item" v-for="(specItem,itemIndex) in sortGoodsSpecs" :key="itemIndex">
            <li class="attribute-title">
              <div>{{specItem.attr}}</div>
            </li>
            
              <li class="attribute-spec">
                <draggable v-model="specItem.valueList">
                  <span class="specItem-span" v-for="(valueItem,valueItemIndex) in specItem.valueList" :key="valueItemIndex">{{valueItem.title}}</span>
                </draggable>
              </li>
            
          </ul>
        </draggable>
      </div>
      <span slot="footer" class="dialog-footer">
        <el-button @click="sortDialogVisible = false">取 消</el-button>
        <el-button type="primary" @click="sureSortDialog">确 定</el-button>
      </span>
    </el-dialog>

    <upload-multimedia-img
      :upload-show="showSelectSpecImageDialog"
      :type="materialType"
      name="spec"
      selNum="one"
      @replace="showSelectSpecImageDialog = !showSelectSpecImageDialog"
      @sure="selectSpecImage"
    ></upload-multimedia-img>
  </div>
  `,
  style: `
  .attribute-item {
    display: flex;
    flex-direction: column;
    padding: 0 0 18px 18px;
    font-size: 14px;
    color: #101010;
    border-bottom: 1px solid #e8e8e8;
  }
  .attribute-title {
    font-weight: bold;
  }
  .specItem-span {
    display: inline-block;
    color: #FFF;
    background-color: #29BA9C;
    padding: 4px 10px;
    cursor: move;
    margin: 10px 0 0 10px;
  }
  .option-box {
    font-weight: bold;
    margin: 20px 0 20px 28px;
  }
  .tips-box {
    font-size: 12px;
    color: #737373;
    margin: 20px 0 25px 28px;
  }
  .goods-spec_info-forms {
    padding:15px 10px;
    border:1px solid #eee;
    border-radius:2px;
  }
  .goods-spec_info-form {
    position:relative;
  }
  .goods-spec_info-remove {
    visibility:hidden;
    position:absolute;
    top:0;
    right:0;
    margin:3px;
    padding:5px;
    color:white;
    border-radius:50%;
    background:rgba(0,0,0,0.5);
    cursor:pointer;
  }
  .goods-spec_info-form:hover .goods-spec_info-remove {
    visibility:visible;
  }
  .goods-spec_info_form-item:first-child {
    background-color:#fafafa;
  }
  .goods-spec_info_form-item {
    display:flex;
    column-gap:15px;
    margin-bottom:15px;
    padding:10px;
  }
  .goods-sped_info_form-name {
    flex-shrink:0;
  }
  .goods-spec_info_form-content {
    flex-shrink:0;
    width:80%;
  }
  .goods-spec_info_form-content .upload-boxed {
    margin:10px 0;
    width:100%;
    height:unset;
    text-align:center;
  }
  .goods-spec_info_form-content .upload-boxed .el-icon-plus {
    width:60px;
    height:60px;
    line-height:60px;
    font-size:14px;
    border-bottom:1px dashed #ccc;
  }
  .goods-spec_info_form-content .upload-boxed img {
    width:60px;
    height:60px;
  }
  .goods-spec_info_value-item {
    display:inline-block;
    position:relative;
    vertical-align:top;
    margin:0 10px 10px 0;
    width:150px;
  }
  .goods-spec_info_value-remove {
    position:absolute;
    right:-5px;
    top:-5px;
    font-size:16px;
    background-color:white;
    border-radius:50%;
    cursor:pointer;
    visibility: hidden;
  }
  .goods-spec_info_value-item:hover .goods-spec_info_value-remove {
    visibility:visible;
  }

  .spec-image.upload-boxed {
    width:60px;
    height:62px;
  }
  .spec-image.upload-boxed .el-icon-plus {
    width:60px;
    line-height:60px;
    border-bottom:1px dashed #ccc;
    box-sizing:border-box;
    background-color:white;
  } 
  `,
  props: ["form"],
  mounted() {
    // console.log(this.form)
    let test_data = {
      "has_option": 1,
      "specs": [
          {
              "id": 31,
              "title": "电池",
              "goods_id": 222,
              "spec_item": [
                  {
                      "id": 61,
                      "specid": 31,
                      "title": "一号"
                  },
                  {
                      "id": 62,
                      "specid": 31,
                      "title": "二号"
                  }
              ]
          },
          {
              "id": 32,
              "title": "类型",
              "goods_id": 222,
              "spec_item": [
                  {
                      "id": 63,
                      "specid": 32,
                      "title": "大功率"
                  },
                  {
                      "id": 64,
                      "specid": 32,
                      "title": "小功率"
                  }
              ]
          }
      ],
      "option": [
          {
              "id": 84,
              "uniacid": 6,
              "goods_id": 222,
              "title": "一号+大功率",
              "thumb": "",
              "product_price": "750.00",
              "market_price": "300.00",
              "cost_price": "100.00",
              "stock": 10,
              "weight": "0.00",
              "display_order": 0,
              "specs": "61_63",
              "skuId": "",
              "goods_sn": "",
              "product_sn": "",
              "virtual": 0,
              "red_price": "",
              "volume": "0.000",
              "withhold_stock": 0,
              "电池": "一号",
              "类型": "大功率"
          },
          {
              "id": 85,
              "uniacid": 6,
              "goods_id": 222,
              "title": "一号+小功率",
              "thumb": "",
              "product_price": "750.00",
              "market_price": "300.00",
              "cost_price": "100.00",
              "stock": 10,
              "weight": "0.00",
              "display_order": 0,
              "specs": "61_64",
              "skuId": "",
              "goods_sn": "",
              "product_sn": "",
              "virtual": 0,
              "red_price": "",
              "volume": "0.000",
              "withhold_stock": 0,
              "电池": "一号",
              "类型": "小功率"
          },
          {
              "id": 86,
              "uniacid": 6,
              "goods_id": 222,
              "title": "二号+大功率",
              "thumb": "",
              "product_price": "750.00",
              "market_price": "300.00",
              "cost_price": "100.00",
              "stock": 10,
              "weight": "0.00",
              "display_order": 0,
              "specs": "62_63",
              "skuId": "",
              "goods_sn": "",
              "product_sn": "",
              "virtual": 0,
              "red_price": "",
              "volume": "0.000",
              "withhold_stock": 0,
              "电池": "二号",
              "类型": "大功率"
          },
          {
              "id": 87,
              "uniacid": 6,
              "goods_id": 222,
              "title": "二号+小功率",
              "thumb": "",
              "product_price": "750.00",
              "market_price": "300.00",
              "cost_price": "100.00",
              "stock": 10,
              "weight": "0.00",
              "display_order": 0,
              "specs": "62_64",
              "skuId": "",
              "goods_sn": "",
              "product_sn": "",
              "virtual": 0,
              "red_price": "",
              "volume": "0.000",
              "withhold_stock": 0,
              "电池": "二号",
              "类型": "小功率"
          }
      ]
    }
    this.editHandleData(this.form); // test_data测试数据
  },
  data() {
    return {
      readonly:readonly,
      materialType:"",
      has_option: false,
      // 表单
      tableHeaderList: ['stock', 'withhold_stock', 'market_price', 'product_price', 'cost_price', 'goods_sn', 'product_sn', 'weight', 'volume', 'thumb', ], // 表格列
      tableColumnList: {
        goodsSpecs: [],
        table_data: [], // 表格中的数据
      },
      add_value: "", // 添加属性的input
      add_popover_bool: false, // 添加属性的小弹窗

      showSelectSpecImageDialog: false,
      selectedingImageSpecIndex: null, // 获取上传图片的表格索引
      // selectedingImageSpecValueIndex: null,
      sp_id: -1, // 新增table_data的id
      sc_id: -1, // 新增规格名的id
      specCounts: 0,
      batch_input: "",
      sortDialogVisible: false,
      sortGoodsSpecs: []
    };
  },
  methods: {
    editHandleData(data) {
      // 处理回显的数据
      this.has_option = Number(data.has_option) == 0 ? false : true;
      for (let i = 0; i < data.specs.length; i++) {
        this.tableColumnList.goodsSpecs.push({
          id: data.specs[i].id,
          attr: data.specs[i].title,
          valueList: data.specs[i].spec_item
        })
        // 循环旧数据  规格项都对应的数据应该赋给新数据
        this.changeOption(data.option)
      }
    },
    addSpec() {
      if (!this.add_value) return
      if (this.selectedAttr.includes(this.add_value)){
        this.$message({
          message: '不能添加相同规格名',
          type: 'warning'
        });
        return
      }
      this.sc_id++;
      this.tableColumnList.goodsSpecs.push({
        id: 'SC'+this.sc_id,
        attr: this.add_value,
        valueList: [{
          "id": 'SV0&' + 'SC'+this.sc_id,
          "specid": 'SC'+this.sc_id,
          "title": ""
        }],
      });

      // 生成处理表头数据和表格数据
      this.generateTableColumn()
      this.traverseSku()

      this.add_popover_bool = false
      this.add_value = ''
    },
    // 属性获得焦点时 得到旧的值 存一下
    attrFocus(oldVal) {
      old_attr = oldVal
    },
    // 属性失去焦点时
    attrBlur(newVal) {
      console.log('attrBlur')
      // 如果 '新值等于旧值' 或者 '空' 什么也不做
      if (newVal === old_attr || newVal === '') return

      // 生成处理表头数据和表格数据
      this.generateTableColumn()
      this.traverseSku('old_attr')
    },
    // 删除属性
    removeSpec(specItemIndex) {
      this.tableColumnList.goodsSpecs.splice(specItemIndex, 1);

      // 生成处理表头数据和表格数据
      this.generateTableColumn()
      this.traverseSku()
    },

    async addSpecValue(specItemIndex) {
      this.tableColumnList.goodsSpecs[specItemIndex].valueList.push({
        "id": 'SV'+ this.tableColumnList.goodsSpecs[specItemIndex].valueList.length + '&' + this.tableColumnList.goodsSpecs[specItemIndex].id,
        "specid": this.tableColumnList.goodsSpecs[specItemIndex].id,
        "title": ""
      });
      // 让新增的输入框获得焦点
      await this.$nextTick()
      this.$refs.attrValueInput[this.$refs.attrValueInput.length - 1].focus()
    },
    // 属性值获得焦点时 得到旧的值 在输入框失去焦点的时候, 如果值没有变化, 则什么也不做
    attrValueFocus(oldVal) {
      old_attr_value = oldVal
    },
    // 属性值失去焦点时, 操作表格数据 (新版本 可以实现无限个规格)
    newAttrValueBlur(curr_attr, newVal, specItemIndex, valueItemIndex) {
      if (curr_attr === '') return
      if (newVal === old_attr_value) return
      let value_num = this.tableColumnList.goodsSpecs[specItemIndex].valueList.filter(item => item.title == newVal)
      if ( value_num.length > 1 ) {
        this.$message({
          message: `规格值不能重复`,
          type: 'warning'
        });
        this.tableColumnList.goodsSpecs[specItemIndex].valueList[valueItemIndex].title = "";
        return
      }

      //  这里根据规格生成的笛卡尔积计算出table中需要改变的行索引 ( 包含新增和修改 )
      let cartesian_arr = this.generateBaseData(this.tableColumnList.goodsSpecs)
      // console.log(cartesian_arr)
      let change_idx_arr = [] // 需要被改变的行的索引
      for (let i in cartesian_arr) {
        if (cartesian_arr[i][curr_attr] === newVal) change_idx_arr.push(Number(i))
      }
      console.log('change_idx_arr', change_idx_arr)

      // 新的表格应该有的长度与现有的表格长度比较, 区分新增还是修改
      let length_arr = this.tableColumnList.goodsSpecs.map(x => x.valueList.length)
      let new_table_length = length_arr.reduce((acc, curr_item) => acc * curr_item) // 新的表格数据长度 求乘积
      let old_table_length = this.tableColumnList.table_data.length // 旧的表格数据长度

      this.specCounts = new_table_length;
      // 如果是修改
      if (new_table_length === old_table_length) {
        this.tableColumnList.table_data.forEach((item, index) => {
          if (change_idx_arr.includes(index)) this.tableColumnList.table_data[index][curr_attr] = newVal
        })
        return
      }
      // 如果是新增
      if (new_table_length > old_table_length) {
        // 得到当前属性的当前值和其他规格的 goodsSpecs, 构造新的表格数据
        let other_sku_arr = this.tableColumnList.goodsSpecs.map(item => {
          if (item.attr !== curr_attr) return item
          else {
            return { id: this.tableColumnList.goodsSpecs[specItemIndex].id, attr: item.attr, valueList: [{
              "id": 'SV'+ this.tableColumnList.goodsSpecs[specItemIndex].valueList.length + '&' + this.tableColumnList.goodsSpecs[specItemIndex].id,
              "specid": this.tableColumnList.goodsSpecs[specItemIndex].id,
              "title": newVal
            }] }
          }
        })
        // 得到新增的表格数据
        let ready_map = this.generateBaseData(other_sku_arr)
        let new_table_data = this.mergeTableData(ready_map)

        change_idx_arr.forEach((item_idx, index) => {
          this.tableColumnList.table_data.splice(item_idx, 0, new_table_data[index])
        })
        
        this.tableColumnList.table_data.reverse().reverse()
      }
    },
    removeSpecValue(specItemIndex, valueItemIndex, attr_name, attr_val) {
      this.tableColumnList.goodsSpecs[specItemIndex]["valueList"].splice(valueItemIndex, 1);

      // 删除table行
      let data_length = this.tableColumnList.table_data.length
      for (let i = 0; i < data_length; i++) {
        if (this.tableColumnList.table_data[i][attr_name] == attr_val) {
          this.tableColumnList.table_data.splice(i, 1)
          i = i - 1
          data_length = data_length - 1
        }
      }

      this.specCounts = this.tableColumnList.table_data.length;
    },
    // 根据 `this.tableColumnList.goodsSpecs` 生成表格列, `tableHeaderList` 用于 el-table-column 的 v-for
    generateTableColumn() {
      this.tableHeaderList = this.tableColumnList.goodsSpecs.map(x => x.attr).concat(['stock', 'withhold_stock', 'market_price', 'product_price', 'cost_price', 'goods_sn', 'product_sn', 'weight', 'volume', 'thumb', ])
    },
    // 合并 goodsSpecs 与 '现价', '库存', '市场价格' , 返回整个表格数据数组
    mergeTableData(arr) {
      return arr.map((item) => {
        this.sp_id++;
        return { ...item, id: 'SP'+ this.sp_id, 'stock': '', 'withhold_stock': '', 'market_price': '', 'product_price': '', 'cost_price': '', 'goods_sn': '', 'product_sn': '', 'weight': '', 'volume': '', 'thumb': '', }
      })
    },
    // 已有数据的修改后，合并 goodsSpecs 与 '现价', '库存', '市场价格' , 返回整个表格数据数组，(已有数据的字段值不置为空 '')
    mergeSaveTableData(arr,list) {
      return arr.map((item,index) => {
        this.sp_id++;
        return { ...item, id: 'SP'+ this.sp_id, 'stock': list[index].stock, 'withhold_stock': list[index].withhold_stock, 'market_price': list[index].market_price, 'product_price': list[index].product_price, 'cost_price': list[index].cost_price, 'goods_sn': list[index].goods_sn, 'product_sn': list[index].product_sn, 'weight': list[index].weight, 'volume': list[index].volume, 'thumb': list[index].thumb, }
      })
    },
    // 遍历 `goodsSpecs` 生成表格数据
    traverseSku(type) {
      let ready_map = this.generateBaseData(this.tableColumnList.goodsSpecs)
      this.tableColumnList.table_data = type == 'old_attr' ? this.mergeSaveTableData(ready_map,this.tableColumnList.table_data) : this.mergeTableData(ready_map)
      this.specCounts = this.tableColumnList.table_data.length;
    },
    // 重新实现笛卡尔积  入参是: this.tableColumnList.goodsSpecs 传入的数组 '为空', '长度为1', '长度大于1' 三种情况 分别处理
    generateBaseData(arr) {
      if (arr.length === 0) return []
      if (arr.length === 1) {
        let [item_spec] = arr
        return item_spec.valueList.map(x => {
          return {
            [item_spec.attr]: x.title
          }
        })
      }
      if (arr.length >= 1) {
        return arr.reduce((accumulator, spec_item) => {
          // accumulator判断是之前整合的规格数组还是单个规格项
          let acc_value_list = Array.isArray(accumulator.valueList) ? accumulator.valueList : accumulator
          let item_value_list = spec_item.valueList
          let result = []
          for (let i in acc_value_list) {
            for (let j in item_value_list) {
              let temp_data = {}
              if (!acc_value_list[i].title) {
                // accumulator不是Array的情况
                temp_data = {
                  ...acc_value_list[i],
                  [spec_item.attr]: item_value_list[j].title
                }

                // 否则如果是单个规格项
              } else {
                temp_data[accumulator.attr] = acc_value_list[i].title
                temp_data[spec_item.attr] = item_value_list[j].title
              }
              result.push(temp_data)
            }
          }
          return result
        })
      }
    },

    // 合并单元格
    mergeRowComputed({ row, column, rowIndex, columnIndex }) {
      if (columnIndex == 0) {
        let key_0 = column.label
        let first_idx = this.tableColumnList.table_data.findIndex(x => x[key_0] == row[key_0])
        const calcSameLength = () => this.tableColumnList.table_data.filter(x => x[key_0] == row[key_0]).length
        first_column_rule = rowIndex == first_idx ? [calcSameLength(), 1] : [0, 0]
        return first_column_rule

      }else {
        // 表格数据的每一项, 
        const callBacks = (table_item, start_idx = 0) => {
          if (columnIndex < start_idx) return true
          let curr_key = this.tableHeaderList[start_idx]
          return table_item[curr_key] === row[curr_key] && callBacks(table_item, ++start_idx)
        }
        let first_idx = this.tableColumnList.table_data.findIndex(x => callBacks(x))
        const calcSameLength = () => this.tableColumnList.table_data.filter(x => callBacks(x)).length
        return rowIndex == first_idx ? [calcSameLength(), 1] : [0, 0]
      }
    },

    // 拖拽排序
    openSortDialog() {
      this.sortDialogVisible = true;
      this.sortGoodsSpecs = JSON.parse(JSON.stringify(this.tableColumnList.goodsSpecs));
    },
    sureSortDialog() {
      this.tableColumnList.goodsSpecs = this.sortGoodsSpecs;

      //循环旧数据  规格项都对应的数据应该赋给新数据
      this.changeOption(this.tableColumnList.table_data);
      this.sortDialogVisible = false;
    },
    changeOption(data) {
      let oldData = [];
      // 先保存旧的表格数据
      let old_table_data = JSON.parse(JSON.stringify(data));
      let old_data_length = old_table_data.length
      for (let i = 0; i < old_data_length; i++) {
        let title = "";
        this.tableColumnList.goodsSpecs.forEach((item)=> {
          title = title + old_table_data[i][item.attr] + '+'
        })
        oldData.push({
          title: title.slice(0,title.length-1),
          index: i
        })
      }
      // 重新生成处理表头数据和表格数据
      this.generateTableColumn();
      this.traverseSku();

      let data_length = this.tableColumnList.table_data.length
      for (let i = 0; i < data_length; i++) {
        // 循环新的表格数据进行替换
        let title = "";
        this.tableColumnList.goodsSpecs.forEach((item)=> {
          title = title + this.tableColumnList.table_data[i][item.attr] + '+'
        })
        oldData.forEach((item)=> {
          if(item.title == title.slice(0,title.length-1)) {
            // this.tableColumnList.table_data[i] = old_table_data[item.index]  这样赋值会导致输入框有问题  只能像下面这样
            this.tableColumnList.table_data[i].stock = old_table_data[item.index].stock;
            this.tableColumnList.table_data[i].withhold_stock = old_table_data[item.index].withhold_stock;
            this.tableColumnList.table_data[i].market_price = old_table_data[item.index].market_price;
            this.tableColumnList.table_data[i].product_price = old_table_data[item.index].product_price;
            this.tableColumnList.table_data[i].cost_price = old_table_data[item.index].cost_price;
            this.tableColumnList.table_data[i].goods_sn = old_table_data[item.index].goods_sn;
            this.tableColumnList.table_data[i].product_sn = old_table_data[item.index].product_sn;
            this.tableColumnList.table_data[i].weight = old_table_data[item.index].weight;
            this.tableColumnList.table_data[i].volume = old_table_data[item.index].volume;
            this.tableColumnList.table_data[i].thumb = old_table_data[item.index].thumb;
            this.tableColumnList.table_data[i].id = old_table_data[item.index].id;
          }
        })
      }
    },
    clearALL() {
      this.tableColumnList.goodsSpecs = [];
      // 生成处理表头数据和表格数据
      this.generateTableColumn()
      this.traverseSku()
    },
    batchSet(value) {
      // 批量设置
      let data_length = this.tableColumnList.table_data.length
      for (let i = 0; i < data_length; i++) {
        this.tableColumnList.table_data[i][value] = this.batch_input;
      }

      this.batch_input = "";
    },
    selectSpecImage(attr, show, images) {
      this.tableColumnList.table_data[this.selectedingImageSpecIndex].thumb = images[0]["url"];
      this.$forceUpdate()
    },
    handleSpecs() {
      let specsList = [];
      let specs_length = this.tableColumnList.goodsSpecs.length;
      if(this.has_option) {
        // 启用商品规格再校验
        for (let i = 0; i < specs_length; i++) {
          // 第一步校验是否有空的规格名
          if(!this.tableColumnList.goodsSpecs[i].attr) {
            this.$message({
              message: '规格名不能为空',
              type: 'warning'
            });
            this.$refs.specValueInput[i].focus()
            return [];
          }
        }
      }
      
      for (let i = 0; i < specs_length; i++) {
        if(this.has_option) {
          // 启用商品规格再校验
          let valueList = this.tableColumnList.goodsSpecs[i].valueList.map(value => value.title);
          if(valueList.includes('')) {
            // 第二步校验是否有空的规格值
            this.$message({
              message: `${this.tableColumnList.goodsSpecs[i].attr}规格值不能为空`,
              type: 'warning'
            });
            this.$refs.specValueInput[i].focus();
            return [];
          }
        }
        
        specsList.push({
          id: this.tableColumnList.goodsSpecs[i].id,
          title: this.tableColumnList.goodsSpecs[i].attr,
          spec_item: this.tableColumnList.goodsSpecs[i].valueList
        })
      }
      return specsList;
    },
    handleOption(specs) {
      let option = [];
      let data_length = this.tableColumnList.table_data.length
      for (let i = 0; i < data_length; i++) {
        let title = "";
        let specs_id = "";

        if(this.has_option) {
          // 启用商品规格再校验
          if(this.tableColumnList.table_data[i].stock === '') {
            this.$message({
              message: `库存不能为空`,
              type: 'warning'
            });
            this.$refs[`stockValueInput${i}`].focus()
            return [];
          }
          if(this.tableColumnList.table_data[i].product_price === '') {
            this.$message({
              message: `现价不能为空`,
              type: 'warning'
            });
            this.$refs[`priceValueInput${i}`].focus()
            return [];
          }
        }
        
        specs.forEach((item)=> {
          title = title + this.tableColumnList.table_data[i][item.title] + '+'
          item.spec_item.forEach((spec)=> {
            if(spec.title === this.tableColumnList.table_data[i][item.title]) {
              specs_id = specs_id + spec.id + '_'
            }
          })
          
        })
        option.push({
          ...this.tableColumnList.table_data[i],
          title: title.slice(0,title.length-1),
          specs: specs_id.slice(0,specs_id.length-1),
        })
      }
      return option;
    },
    validate() {
      console.log(this.tableColumnList.goodsSpecs);
      console.log(this.tableColumnList.table_data)
      let data = {};
      // 注意：option是SP   规格名SC  和规格值SV新增 前缀分开来 保证id唯一
      if(this.has_option) {
        let specs = this.handleSpecs();
        if(specs.length == 0) {
          // 校验规格是否有空值
          return false;
        }

        let option = this.handleOption(specs);
        if(option.length == 0) {
          // 校验库存和价格是否有空值
          return false;
        }

        data = {
          has_option: 1,
          specs: specs,
          option: option
        };
      }else {
        // 不启用商品规格
        let specs = this.handleSpecs();
        let option = this.handleOption(specs);
        data = {
          has_option: 0,
          specs: specs,
          option: option
        };
      }
      console.log(data)
      return data;
      
    },
    // 删除图片
    delUploadImg(index){
      this.tableColumnList.table_data[index].thumb = ""
    }
  },
  computed: {
    // 已添加的属性(字符串数组)
    selectedAttr() {
      return this.tableColumnList.goodsSpecs.map(x => x.attr)
    },
  },
});
