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
        <el-form-item label="收款码名称">
          <el-input v-model="form.goods.title"></el-input>
        </el-form-item>
        <el-form-item label="商品分类">
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
        <el-form-item label="收款码图片">
          <div class="upload-boxed" @click="displaySelectMaterialPopup('thumb')">
            <img
              :src="form.goods.thumb_link"
              style="width: 150px; height: 150px; border-radius: 5px; cursor: pointer;object-fit:cover;"
              v-show="form.goods.thumb"
            />
            <div class="el-icon-plus" v-show="!form.goods.thumb"></div>
            <div class="upload-boxed-text">点击重新上传</div>
          </div>
          <div class="form-item_tips">建议尺寸: 640 * 640 ，或正方型图片</div>
        </el-form-item>
        <el-form-item label="是否上架">
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
      console.log(name,image,imageUrl,"imageUrl*-",this.formFieldName);
      let originalImageUrl = JSON.parse(JSON.stringify(imageUrl))
      if(name == "video"){
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
      console.log(val,id,item);
      // console.log(val,id,item,'点击图片选中');
      // if(this.formFieldName == "video_cover"){
      //   this.form.goods.video_image = item.url
      // }
      // if(this.formFieldName == "thumb"){
      //   this.form.goods[this.formFieldName] = item.url
      // }
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
      let obj = {
        category: filterSubmitCategoryList,
        status:this.form.goods.status,
        title:this.form.goods.title,
        thumb:this.form.goods.thumb,
        display_order:this.form.goods.display_order
      }
      console.log(obj);
      return obj
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
