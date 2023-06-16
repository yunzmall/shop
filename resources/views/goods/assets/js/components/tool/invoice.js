define({
  name: "invoice",
  template: `
  <div>
    <div class="vue-main-title">
      <div class="vue-main-title-left"></div>
      <div class="vue-main-title-content">发票设置</div>
    </div>
    <div style="margin:0 auto;width:100%;">
      <el-form label-width="130px">
        <el-form-item label="赋码方式" >
          <el-radio v-model="search.coding_type" :label="0">按商品赋码</el-radio>
          <el-radio v-model="search.coding_type" :label="1">按规格赋码</el-radio>
        </el-form-item>
        <el-form-item label=" "  v-if="search.coding_type == 0">
          <div style="display: flex;">
            <el-input v-model="keyword" style="margin-right: 20px;width:25%;"></el-input>
            <el-button type="primary" @click="serachContent">搜索</el-button>
          </div>
        </el-form-item>
        <el-form-item label=" "  v-if="search.coding_type == 0">
          <div style="display: flex;">
            <div style="flex:1;height: 360px;overflow: auto;margin-right: 20px;">
              <el-tree
                class="filter-tree"
                :data="dataList"
                :props="defaultProps"
                :filter-node-method="filterNode"
                @node-click="handleNodeClick"
                ref="tree">
              </el-tree>
            </div>
            <div style="flex:2">
              <div style="display: flex;flex-wrap: wrap;margin-bottom:20px;">
                <div style="display: flex;width:50%;margin-right: 30px;"><span>税收分类编码: </span><el-input v-model="search.code" style="max-width: 80%;"></el-input></div>
                <div style="display: flex;width:45%;"><span>商品名称: </span><el-input v-model="search.goods_name" style="max-width: 80%;"></el-input></div>
              </div>
              <div style="display: flex;flex-wrap: wrap;margin-bottom:20px;">
                <div style="display: flex;width:50%;margin-right: 30px;"><span>税收分类简称: </span><el-input v-model="search.name" style="max-width: 80%;"></el-input></div>
                <!-- <div style="display: flex;width:45%;"><span>商品简码: </span><el-input v-model="search.commodity_code" style="max-width: 80%;"></el-input></div> -->
              </div>
              <div style="display: flex;flex-wrap: wrap;margin-bottom:20px;">
                <div style="flex:1;">
                  <span style="margin: 0 5px 5px 0;">使用优惠政策: </span>
                    <el-radio v-model="search.use_discount" :label="1">是</el-radio>
                    <el-radio v-model="search.use_discount" :label="0">否</el-radio>
                </div>

                <div style="flex:1;">
                  <span>优惠政策类型: </span>
                  <el-select v-model="search.discount" clearable placeholder="请选择" style="width: 72%;">
                    <el-option
                    v-for="item in use_discountType"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                    </el-option>
                  </el-select>
                </div>
              </div>
              <div style="display: flex;flex-wrap: wrap;margin-bottom:20px;">
                <div style="width:48%;margin-left: 40px;">
                  <i class="el-icon-star-on" style="color: #EE3939;font-size: 12px;margin-top: 10px;"></i>
                  <span>税率: </span>
                  <el-select v-model="search.tax_rate" clearable placeholder="请选择" style="width: 42%;">
                    <el-option
                    v-for="item in taxRate"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                    </el-option>
                  </el-select>
                </div>
                <div style="display: flex;width:45%;">
                  <i class="el-icon-star-on" style="color: #EE3939;font-size: 12px;margin-top: 10px;"></i>
                  <span>免税类型: </span>
                  <el-select v-model="search.tax_type" clearable placeholder="请选择" style="width: 80%;">
                    <el-option
                    v-for="item in dutyFree"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                    </el-option>
                  </el-select>
                </div>
              </div>
              <div style="display: flex;margin-left: 30px;">
                  <span style="margin: 0 5px 5px 0;">含税标志: </span>
                  <div>
                    <el-radio v-model="search.tax_flag" :label="1">含税</el-radio>
                    <el-radio v-model="search.tax_flag" :label="0">不含税</el-radio>
                  </div>
              </div>
            </div>
          </div>
        </el-form-item>

        <!-- 规格 -->
        <el-form-item label="批量操作"  v-if="search.coding_type == 1">
          <span class="code-color" @click="codingDialog('')">赋码</span>
        </el-form-item>
        <el-form-item label=" "  v-if="search.coding_type == 1">
          <el-table :data="tableColumnList.table_data" border :span-method="mergeRowComputed" style="width:30%">
            <el-table-column :label="specItem.attr" v-for="(specItem,itemIndex) in tableColumnList.goodsSpecs" :key="itemIndex" :prop="specItem.attr">
                <template slot-scope="scope">
                <div >{{scope.row[specItem.attr]}}</div>
                </template>
            </el-table-column>
            <el-table-column label="赋码" prop="stock">
                <template slot-scope="scope">
                  <span @click="codingDialog(scope.$index)" class="code-color">赋码</span>
                <!-- <el-input v-model="scope.row.stock" :ref="'stockValueInput' + [[scope.$index]]" placeholder="0"></el-input> -->
                </template>
            </el-table-column>
          </el-table>
        </el-form-item>
      </el-form>
    </div>
    <!-- 弹窗 -->
    <el-dialog
        title=" "
        :visible.sync="dialogVisible"
        :show-close="false"
        center
        width="80%">
        <div style="border-bottom: 2px solid #b4bccc;margin-bottom: 20px;"><span style="margin:0 20px;">发票赋码</span></div>
        <div style="display: flex;padding:20px;">
            <el-input v-model="keyword" style="margin-right: 20px;width:25%;"></el-input>
            <el-button type="primary" @click="serachContent">搜索</el-button>
        </div>
        <div style="display: flex;padding:20px;">
            <div style="flex:1;height: 360px;overflow: auto;margin-right: 20px;">
              <el-tree
                class="filter-tree"
                :data="dataList"
                :props="defaultProps"
                :filter-node-method="filterNode"
                @node-click="handleNodeClick"
                ref="tree">
              </el-tree>
            </div>
            <div style="flex:2">
              <div style="display: flex;flex-wrap: wrap;margin-bottom:20px;">
                <div style="display: flex;width:50%;margin-right: 30px;"><span style="line-height: 40px;">税收分类编码: </span><el-input v-model="search.code" style="max-width: 80%;"></el-input></div>
                <div style="display: flex;width:45%;"><span style="line-height: 40px;">商品名称: </span><el-input v-model="search.goods_name" style="max-width: 80%;"></el-input></div>
              </div>
              <div style="display: flex;flex-wrap: wrap;margin-bottom:20px;">
                <div style="display: flex;width:50%;margin-right: 30px;"><span style="line-height: 40px;">税收分类简称: </span><el-input v-model="search.name" style="max-width: 80%;"></el-input></div>
                <!-- <div style="display: flex;width:45%;"><span style="line-height: 40px;">商品简码: </span><el-input v-model="search.commodity_code" style="max-width: 80%;"></el-input></div> -->
              </div>
              <div style="display: flex;flex-wrap: wrap;margin-bottom:20px;">
              <div style="flex:1;">
                  <span style="margin: 0 5px 5px 0;line-height: 40px;" >使用优惠政策: </span>
                    <el-radio v-model="search.use_discount" :label="1">是</el-radio>
                    <el-radio v-model="search.use_discount" :label="0">否</el-radio>
                </div>

                <div style="flex:1;">
                  <span style="line-height: 40px;">优惠政策类型: </span>
                  <el-select v-model="search.discount" clearable placeholder="请选择" style="width: 80%;">
                    <el-option
                    v-for="item in use_discountType"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                    </el-option>
                  </el-select>
                </div>
              </div>
              <div style="display: flex;flex-wrap: wrap;margin-bottom:20px;">
                <div style="width:48%;margin-left: 40px;">
                  <i class="el-icon-star-on" style="color: #EE3939;font-size: 12px;margin-top: 10px;"></i>
                  <span style="line-height: 40px;">税率: </span>
                  <el-select v-model="search.tax_rate" clearable placeholder="请选择" style="width: 80%;">
                    <el-option
                    v-for="item in taxRate"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                    </el-option>
                  </el-select>
                </div>

                <div style="display: flex;width:45%;">
                  <i class="el-icon-star-on" style="color: #EE3939;font-size: 12px;margin-top: 10px;"></i>
                  <span style="line-height: 40px;">免税类型: </span>
                  <el-select v-model="search.tax_type" clearable placeholder="请选择" style="width: 80%;">
                    <el-option
                    v-for="item in dutyFree"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
                    </el-option>
                  </el-select>
                </div>
              </div>
              <div style="display: flex;margin-left: 30px;">
                <span style="margin: 0 5px 5px 0;">含税标志: </span>
                <div>
                  <el-radio v-model="search.tax_flag" :label="1">含税</el-radio>
                  <el-radio v-model="search.tax_flag" :label="0">不含税</el-radio>
                </div>
              </div>
            </div>
        </div>
        <span slot="footer" class="dialog-footer">
            <el-button type="primary" @click="save">保 存</el-button>
            <el-button @click="dialogVisible = false">取 消</el-button>
        </span>
    </el-dialog>
  </div>
  `,
  style: `
  .tox-tinymce {
    min-height:600px;
  }
  .code-color {
    color:#29BA9C;
    font-weight: bold;
  }
  `,
  props: {
    form: {
      default() {
        return {};
      },
    },
    formKey: {
      type: String,
    },
  },
  data() {
    return {
      search:{
        coding_type: 0 ,
        code:'',
        goods_name:'',
        use_discount:1,
        name:'',
        // commodity_code:'',
        tax_rate:'',
        tax_type:'0',
        tax_flag:1,
        discount:''
      },
      dialogVisible: false,
    //   记录点击的小标
      columnIndex:'',
      use_discountType: [],
      taxRate: [],
      dutyFree:[],
      keyword:'',
      dataList: [],
      defaultProps: {
        children: 'children',
        label: 'mc'
      },
      tableColumnList: {
        goodsSpecs: [],
        table_data: [], // 表格中的数据
      },
      save_num:0,
    };
  },
  mounted() {
    console.log(this.form,'invoice');
    for(let key in this.form.taxRate){
        this.taxRate.push({
            value:key,
            label:this.form.taxRate[key]+'%'
        })
    }
    for(let key in this.form.discount){
        this.use_discountType.push({
            value:this.form.discount[key],
            label:this.form.discount[key]
        })
    }
    for(let key in this.form.exemptionType){
        this.dutyFree.push({
            value:key,
            label:this.form.exemptionType[key]
        })
    }
    this.editHandleData(this.form); // test_data测试数据
    this.dataList = this.form.treeCode;
    this.search.coding_type = this.form.invoice.coding_type;
    // this.form.invoice.options = this.form.invoice.options.reverse()
    if(this.form.invoice.coding_type == 1){
        this.tableColumnList.table_data.forEach((item,index) => {
            item.coding_type = this.form.invoice.coding_type;
            item.code = this.form.invoice.options[index].code  ;
            item.goods_name = this.form.invoice.options[index].goods_name ;
            item.use_discount = this.form.invoice.options[index].use_discount;
            item.name =  this.form.invoice.options[index].name;
            item.tax_rate = this.form.invoice.options[index].tax_rate;
            item.tax_type = this.form.invoice.options[index].tax_type ;
            item.tax_flag =  this.form.invoice.options[index].tax_flag ;
            item.discount = this.form.invoice.options[index].discount
        })
    }else{
        this.search.code = this.form.invoice.code  ;
        this.search.goods_name = this.form.invoice.goods_name ;
        this.search.use_discount = this.form.invoice.use_discount;
        this.search.name =  this.form.invoice.name;
        this.search.tax_rate = this.form.invoice.tax_rate === 0 ? '0' : this.form.invoice.tax_rate;
        this.search.tax_type = this.form.invoice.tax_type ;
        this.search.tax_flag =  this.form.invoice.tax_flag ;
        this.search.discount = this.form.invoice.discount;
        this.tableColumnList.table_data.forEach((item,index) => {
            item.coding_type = 1;
            item.code =  '' ;
            item.goods_name = '' ;
            item.use_discount = 1;
            item.name =  '';
            item.tax_rate = '0';
            item.tax_type = '0';
            item.tax_flag =  1;
            item.discount = ''
        })
    }
  },
  watch:{
    'search.coding_type':{
        handler(newVal, oldVal){
            this.search.code = '';
            this.search.goods_name = '';
            this.search.use_discount = 1;
            this.search.name = '';
            this.search.discount = '';
            this.search.tax_rate = '0';
            this.search.tax_type = '0';
            this.search.tax_flag = 1;
        },
    }
  },
  methods: {
    editHandleData(data) {
      // 处理回显的数据
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
            this.tableColumnList.table_data[i].id = old_table_data[item.index].id;
          }
        })
      }
    },
    // 遍历 `goodsSpecs` 生成表格数据
    traverseSku() {
      let ready_map = this.generateBaseData(this.tableColumnList.goodsSpecs)
      this.tableColumnList.table_data = this.mergeTableData(ready_map)
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
                  [spec_item.attr]: item_value_list[j].title,
                //   id:item_value_list[j].id
                }

                // 否则如果是单个规格项
              } else {
                temp_data[accumulator.attr] = acc_value_list[i].title
                temp_data[spec_item.attr] = item_value_list[j].title
                // temp_data['id'] = item_value_list[j].id
              }
              result.push(temp_data)
            }
          }
          return result
        })
      }
    },
    generateTableColumn() {
    //   this.tableHeaderList = this.tableColumnList.goodsSpecs.map(x => x.attr).concat(['stock', 'withhold_stock', 'tax_flaget_price', 'product_price', 'cost_price', 'goods_sn', 'product_sn', 'weight', 'volume', 'thumb', ])
      this.tableHeaderList = this.tableColumnList.goodsSpecs.map(x => x.attr).concat([])
    },
    // 合并 goodsSpecs 与 '现价', '库存', '市场价格' , 返回整个表格数据数组
    mergeTableData(arr) {
      return arr.map((item) => {
        this.sp_id++;
        // return { ...item, id: 'SP'+ this.sp_id, 'stock': '', 'withhold_stock': '', 'tax_flaget_price': '', 'product_price': '', 'cost_price': '', 'goods_sn': '', 'product_sn': '', 'weight': '', 'volume': '', 'thumb': '', }
         return { ...item,id:item.id}
      })
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
    // 弹窗
    codingDialog(index){
        if(!this.form.has_option){ return }
        this.dialogVisible = true;
        this.columnIndex = index;
        if(index === ''){
            this.search.code = '' ;
            this.search.goods_name = '' ;
            this.search.use_discount = 1;
            this.search.name =  '';
            this.search.tax_rate = '0';
            this.search.tax_type = '0';
            this.search.tax_flag = 1;
            this.search.discount = '';
        }else{

            this.search.coding_type = this.tableColumnList.table_data[this.columnIndex].coding_type;
            this.search.code = this.tableColumnList.table_data[this.columnIndex].code   ;
            this.search.goods_name = this.tableColumnList.table_data[this.columnIndex].goods_name ;
            this.search.use_discount = this.tableColumnList.table_data[this.columnIndex].use_discount ;
            this.search.name  =  this.tableColumnList.table_data[this.columnIndex].name ;
            this.search.tax_rate = this.tableColumnList.table_data[this.columnIndex].tax_rate;
            this.search.tax_type = this.tableColumnList.table_data[this.columnIndex].tax_type  ;
            this.search.tax_flag =  this.tableColumnList.table_data[this.columnIndex].tax_flag  ;
            this.search.discount = this.tableColumnList.table_data[this.columnIndex].discount ;
            console.log(this.tableColumnList.table_data ,this.columnIndex,'this.search.discount ');
            // }
        }
    },
    filterNode(value, data) {
      if (!value) return true;
      return data.mc.indexOf(value) !== -1;
    },
    // 搜索
    serachContent(){
        this.$refs.tree.filter(this.keyword);
    },
    // 获取点击节点数据
    handleNodeClick(data){
        if(data.children.length === 0){
            this.search.code = data.bm;
            this.search.goods_name = '' ;
            this.search.name = data.spbmjc;
            // this.search.commodity_code = data.mc;
        }
    },
    validate(){
        if(this.search.tax_type === ''){
            this.$message.success('免税类型选项不能为空')
            return false
        }
        if(this.search.tax_rate === ''){
                this.$message.success('税率选项不能为空')
                return false
        }
        return this.save()
    },
    // 保存
    save(){
        this.save_num += 1;
        if(this.columnIndex === ''){
            for(let item of this.tableColumnList.table_data){
                item.coding_type = this.search.coding_type;
                item.code = this.search.code;
                item.goods_name = this.search.goods_name;
                item.use_discount = this.search.use_discount;
                item.name = this.search.name;
                // item.commodity_code = this.search.commodity_code;
                item.tax_rate = this.search.tax_rate;
                item.tax_type = this.search.tax_type;
                item.tax_flag = this.search.tax_flag;
                item.discount = this.search.discount;
            }

        }else{
            this.tableColumnList.table_data[this.columnIndex].coding_type = this.search.coding_type;
            this.tableColumnList.table_data[this.columnIndex].code = this.search.code;
            this.tableColumnList.table_data[this.columnIndex].goods_name = this.search.goods_name;
            this.tableColumnList.table_data[this.columnIndex].use_discount = this.search.use_discount;
            this.tableColumnList.table_data[this.columnIndex].name = this.search.name;
            this.tableColumnList.table_data[this.columnIndex].tax_rate = this.search.tax_rate;
            this.tableColumnList.table_data[this.columnIndex].tax_type = this.search.tax_type;
            this.tableColumnList.table_data[this.columnIndex].tax_flag = this.search.tax_flag;
            this.tableColumnList.table_data[this.columnIndex].discount = this.search.discount;
        }
        this.dialogVisible = false;
        let options = []
        for(let item of this.tableColumnList.table_data){
            options.push({
                // coding_type : this.search.coding_type,
                code : item.code,
                name : item.name,
                use_discount : item.use_discount,
                discount : item.discount,
                tax_type : item.tax_type,
                tax_rate : item.tax_rate,
                goods_name : item.goods_name,
                // goods_code : item.commodity_code,
                tax_flag : item.tax_flag,
                goods_option_id : item.id
            })
        }
        let invoice = {
            coding_type : this.search.coding_type,
            code : this.search.coding_type ? '' : this.search.code,
            name : this.search.coding_type ? '' : this.search.name,
            use_discount : this.search.coding_type ? 1 : this.search.use_discount,
            discount : this.search.coding_type ? '' : this.search.discount,
            tax_type : this.search.coding_type ? '' : this.search.tax_type,
            tax_rate : this.search.coding_type ? '' : this.search.tax_rate,
            goods_name : this.search.coding_type ? '' : this.search.goods_name,
            // goods_code : this.search.commodity_code,
            tax_flag : this.search.coding_type ? 1 : this.search.tax_flag,
            options : this.search.coding_type && this.form.has_option == 1 ? options : []
        }
        console.log(invoice,'options',this.tableColumnList.table_data,this.search.goods_name);
        return invoice
    }
  },
});
