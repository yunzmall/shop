define({
  template: `
  <div>
    <el-form ref="auth">
      <div class="vue-main-title">
        <div class="vue-main-title-left"></div>
        <div class="vue-main-title-content">规格信息</div>
      </div>
      <div style="margin:0 auto;width:90%;">  
        <el-form-item label=" ">
          <el-table :data="spec_info"  style="width: 100%">
            <el-table-column prop="sort" label="排序" width='150' >
              <template slot-scope="scope">
                   <el-input v-model="scope.row.sort" placeholder=""></el-input>
              </template>
            </el-table-column>
            <el-table-column prop="info_img" label="图片"  width='250' >
              <template slot-scope="scope">
                <div class="upload-boxed" @click="displaySelectMaterialPopup('info_img',scope.$index)">
                  <img
                    :src="scope.row.info_img_src"
                    style="width: 150px; height: 150px; border-radius: 5px; cursor: pointer;object-fit:cover;"
                    v-show="scope.row.info_img_src"
                  />
                  <div class="el-icon-plus" v-show="!scope.row.info_img_src"></div>
                  <div class="upload-boxed-text">点击重新上传</div>
                </div>
              </template>
            </el-table-column>
            <el-table-column prop="desc" label="内容"  width='350' >
              <template slot-scope="scope">
                 <el-input v-for="(item,index) in scope.row.content" v-model="item.content" maxlength="12" show-word-limit></el-input>
              </template>
            </el-table-column>
            <el-table-column label="规格">
               <template slot-scope="scope">
                    <el-select v-model="scope.row.goods_option_id" placeholder="请选择">
                      <el-option
                        v-for="item in goods_options"
                        :key="item.id"
                        :label="item.title"
                        :value="item.id">
                      </el-option>
                    </el-select>
                  </template>
            </el-table-column>
            <el-table-column label="">
                 <template slot-scope="scope">
                    <el-button @click="delSpecInfo(scope.$index)"><i class="el-icon-delete"></i></el-button>
                 </template>
            </el-table-column>
          </el-table>
          <el-button icon="el-icon-plus" @click="addSpecInfo">添加</el-button>
          <div class="form-item_tips">
              排序大的显示在前面，图片建议尺寸：请上传，或正方形图片，文字最多显示12个<br>
              注意：如是发布商品，请先保存商品后才能进行关联规格，关联规格时注意规格有大的整体变动(例：删除某些规格项)，如有也请先保存商品信息后再进行关联，否则可能关联到失效规格
          </div>
        </el-form-item>
      </div>
      
    </el-form>
    <upload-multimedia-img
      :upload-show="showSelectMaterialPopup"
      :type="materialType"
      :name="formFieldName"
      selNum="one"
      @replace="showSelectMaterialPopup = !showSelectMaterialPopup"
      @sure="selectedMaterial"
    ></upload-multimedia-img>
  </div>
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
      showSelectMaterialPopup: false,
      formFieldName: "",
      materialType: "",
      selNum: "one",
      select:"open",
      goods_options:[],
      spec_info:[],
      img_index:"",
    };
  },
  created() {
    this.goods_options = this.form.options;
    this.spec_info = this.form.specs_info;
    // console.log(this.goods_options,this.spec_info,'规格信息123')
  },
  methods: {
    extraDate(){
      return {
        'extraContent':"商品描述"
      }
    },
    displaySelectMaterialPopup(fieldName = "thumb",index,type = 1) {
      if(fieldName == 'other'){
        this.selNum = 'more'
      }else{
        this.selNum = 'one'
      }
      this.img_index = index;
      this.formFieldName = fieldName;
      this.showSelectMaterialPopup = !this.showSelectMaterialPopup;
      this.materialType = String(type);
    },
    selectedMaterial(name, image, imageUrl) {
      let originalImageUrl = JSON.parse(JSON.stringify(imageUrl))
      if (Array.isArray(imageUrl)) {
        imageUrl = imageUrl.map((item) => {
          return item.url;
        });
      }
      console.log(originalImageUrl[0],this.img_index,this.formFieldName);
      this.spec_info[this.img_index][this.formFieldName] = originalImageUrl[0].attachment;
      this.spec_info[this.img_index][this.formFieldName+'_src'] = originalImageUrl[0].url;
      this.spec_info.push({});
      this.spec_info.splice(this.spec_info.length-1,1)
      this.$forceUpdate();
    },
    changeGoodsImage(index,val) {
      this.goodsImagesChangeIndex = index;
      this.displayUploadImagePopup("images");
    },
    addSpecInfo() {
      this.spec_info.push({
          goods_option_id:"",
          info_img:"",
          info_img_src:"",
          sort:"",
          content: [{content:""},{content:""},{content:""},{content:""},{content:""},{content:""}]
      });
    },
    delSpecInfo(index) {
      this.spec_info.splice(index,1);
    },
    validate() {
      return {
        spec_info:this.spec_info ? this.spec_info : []
      }
    },
  },
});
