define({
  name:"share",
  template:`
    <div class="share">
      <el-form>
      <div class="vue-main-title">
        <div class="vue-main-title-left"></div>
        <div class="vue-main-title-content">分享关注</div>
      </div>
      
      <div style="margin:0 auto;width:80%;">

        <el-form-item label="分享标题">
          <el-input style="width:500px;" v-model="json.share_title" ></el-input>
          <p>如果不填写，默认为商品名称</p>
        </el-form-item>

        <el-form-item label="分享图标">
            <div class="upload-boxed" @click="displayUploadImagePopup('goods_image')">
              <div v-if="json.share_thumb && json.share_thumb != ' '">
                <img :src="json.share_thumb" alt="" style="width: 150px; height: 150px; border-radius: 5px; cursor: pointer;">
              </div>
              <div v-else>
                <div class="el-icon-plus" style="font-size: 60px;cursor: pointer;"></div>
                <div class="upload-boxed-text">点击上传图片</div>
              </div>
            </div>
            
          <p style="margin-left: 70px;">如果不选择，默认为商品缩略图片</p>
        </el-form-item>

        <el-form-item label="分享描述">
          <el-input style="width:500px;" v-model="json.share_desc" ></el-input>
          <p>如果不填写，默认为商品名称</p>
        </el-form-item>

      </div>
      </el-form>
      <upload-multimedia-img 
        :upload-show="showUploadImagePopup" 
        :type="type" :name="chooseImgName" 
        selNum="one" 
        @replace="displayUploadImagePopup" 
        @sure="selectedImage"
      ></upload-multimedia-img>
    </div>
  `,
  style:`

  `,
  props: {
    form: {
      default() {
        return {}
      }
    }
  },
  data() {
    return {
      showUploadImagePopup: false,
      chooseImgName: "",
      type:"",
      json: {
        share_title: '',
        share_thumb: '',
        share_desc: '',
      }
    }
  },
  mounted() {
    if(this.form && JSON.stringify(this.form) !== '[]') {
      this.json.share_title = this.form.share_title;
      this.json.share_thumb = this.form.share_thumb;
      this.json.share_desc = this.form.share_desc;
    }
  },
  methods:{
    displayUploadImagePopup(type = "goods_image") {
      this.type = "1";
      this.chooseImageName = type;
      this.showUploadImagePopup = !this.showUploadImagePopup;
    },
    selectedImage(name, show, images) {
      this.json.share_thumb = images[0]["url"];
      this.$forceUpdate()
    },
    extraDate(){
      
    },
    validate(){
      return this.json;
    }
  }
})