<script>
Vue.component('uploadImg', {
    props: ["uploadShow","name"],
    delimiters: ['[[', ']]'],
    data(){
      return{
        imgLoading: false,
        ImgList: [],
        //弹框上传图片的路径
        uploadImg: "",
        uploadImgUrl: "",
        netImgUrl: "",

        chooseImg: "",

        radio1: "", //年
        radio2: "", //月
        activeName2: "first",
        //是否显示弹框
        pageSize: 0,
        current_page: 0,
        total: 0,
      }
    },
    watch:{
      uploadShow() {
        if(this.uploadShow) {
          this.currentChange(1);
        }
      }
    },
    mounted: function(){
    //   this.currentChange(1);
    },
    methods:{
      chooseTheImg(img,img_url) {
        this.$emit("sure",this.name,img,img_url);
        this.beforeClose();
      },
      chooseYear(year) {
          this.currentChange(1);
      },
      chooseMonth(month) {
          this.currentChange(1);
      },
      
      sureImg() {
        this.$emit("sure",this.name,this.uploadImg,this.uploadImgUrl);
        this.beforeClose();
      },
      // 转化网络地址
      transform() {
          if(!this.netImgUrl) {
              this.$message.error("请输入网络地址")
              return;
          }
          this.imgLoading = true;
          this.$http
              .post("{!! yzWebFullUrl('upload.uploadV2.fetch') !!}", { url: this.netImgUrl }).then(response => {
                  if (response.data.result === 1) {
                    this.$emit("sure",this.name,response.data.data.img,response.data.data.img_url);
                    this.beforeClose();
                  } else {
                      this.$message.error(response.data.msg);
                  }
                  this.imgLoading = false;
              })
              .catch(err => {
                  console.error(err);
                  this.imgLoading = false;

          });
      },
      currentChange(val) {
          this.imgLoading = true;
          this.$http.post("{!! yzWebFullUrl('upload.uploadV2.get-image') !!}"+'&group_id=-999&local=local',{ page: val, year: this.radio1, month: this.radio2 })
              .then(response => {
                  console.log(response)

              if (response.data.result == 1) {
                  this.total = response.data.data.total;
                  this.ImgList = response.data.data.data;
                  this.current_page = response.data.data.current_page;
                  this.pageSize = response.data.data.per_page;
              } else {
                  this.$message.error(response.data.msg);
              }
              this.imgLoading = false;
              })
              .catch(err => {
              console.error(err);
              this.imgLoading = false;
              });
      },
      
      deleteImg(id) {
          this.imgLoading = true;
          this.$http
              .post("{!! yzWebFullUrl('upload.uploadV2.delete') !!}", { id: id })
              .then(response => {
              if (response.data.result == 1) {
                  this.$message.success("系统删除成功");
                  this.currentChange(1);
              } else {
                  this.$message.error(response.data.msg);
              }
              this.imgLoading = false;
              })
              .catch(err => {
              console.error(err);
              this.imgLoading = false;
          });
      },
      uploadSuccess(res, file) {
          if (res.result == 1) {
              if (res.data.state == 'SUCCESS') {
                  this.uploadImg = res.data.attachment;//传相对地址
                  this.uploadImgUrl = res.data.url;
                  this.$message.success("上传成功！");
              } else {
                  this.$message.error(res.msg);
              }
          } else {
              this.$message.error(res.msg);
          }
          this.imgLoading = false;
      },
      beforeUpload(file) {
          this.imgLoading = true;
          const isLt2M = file.size / 1024 / 1024 < 4;
          if (!isLt2M) {
              this.$message.error("上传图片大小不能超过 4MB!");
              this.imgLoading = false;
          }
          return isLt2M;
      },
      initData(){
        this.imgLoading= false;
        this.ImgList= [];
        this.uploadImg= "";
        this.uploadImgUrl= "";
        this.netImgUrl= "";

        this.chooseImg= "";

        this.radio1= ""; //年
        this.radio2= ""; //月
        this.activeName2= "first";
        this.pageSize= 0;
        this.current_page= 0;
        this.total= 0;
      },
      beforeClose() {
        this.initData();
        this.$emit('replace', this.uploadShow);
      },
        
    },
  template: `
      <el-dialog :visible.sync="uploadShow" width="60%" center :before-close="beforeClose">
        <el-tabs v-model="activeName2" type="card">
            <el-tab-pane label="上传图片" name="first">
            <div
                style="text-align: center"
                class="submit_Img"
                v-loading="imgLoading"
                element-loading-background="rgba(0, 0, 0, 0)"
            >
                <el-upload
                    class="avatar-uploader"
                    action="{!!yzWebFullUrl('upload.uploadV2.upload',['upload_type'=>'image'])!!}"
                    accept="image/*"
                    :show-file-list="false"
                    :on-success="uploadSuccess"
                    :before-upload="beforeUpload"
                >
                <div class="avatar_box" v-if="uploadImgUrl">
                    <img :src="uploadImgUrl" class="avatar" />
                </div>
                <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                </el-upload>
            </div>
            </el-tab-pane>
            <el-tab-pane label="提取网络图片" name="second">
                <el-input
                    v-model="netImgUrl"
                    placeholder="请输入网络图片地址"
                    style="width:90%"
                ></el-input>
                <el-button @click="transform">转化</el-button>
            </el-tab-pane>
            <el-tab-pane label="浏览图片" name="third">
            <div>
                <el-radio-group v-model="radio1" size="medium" @change="chooseYear">
                    <el-radio-button label="">不限</el-radio-button>
                    <el-radio-button label="2021">2021年</el-radio-button>
                    <el-radio-button label="2020">2020年</el-radio-button>
                    <el-radio-button label="2019">2019年</el-radio-button>
                    <el-radio-button label="2018">2018年</el-radio-button>
                    <el-radio-button label="2017">2017年</el-radio-button>
                    <el-radio-button label="2016">2016年</el-radio-button>
                </el-radio-group>
            </div>

            <div style="margin-top: 10px;">
                <el-radio-group v-model="radio2" size="small" @change="chooseMonth">
                    <el-radio-button label="">不限</el-radio-button>
                    <el-radio-button label="1">1月</el-radio-button>
                    <el-radio-button label="2">2月</el-radio-button>
                    <el-radio-button label="3">3月</el-radio-button>
                    <el-radio-button label="4">4月</el-radio-button>
                    <el-radio-button label="5">5月</el-radio-button>
                    <el-radio-button label="6">6月</el-radio-button>
                    <el-radio-button label="7">7月</el-radio-button>
                    <el-radio-button label="8">8月</el-radio-button>
                    <el-radio-button label="9">9月</el-radio-button>
                    <el-radio-button label="10">10月</el-radio-button>
                    <el-radio-button label="11">11月</el-radio-button>
                    <el-radio-button label="12">12月</el-radio-button>
                </el-radio-group>
            </div>

            <div id="upload-img" class="imgList" v-loading="imgLoading" element-loading-background="rgba(0, 0, 0, 0)">
                <div class="avatar-uploader-box" v-for="(img,index) in ImgList" :key="index">
                <img
                    @click="chooseTheImg(img.attachment,img.url)"
                    :src="img.url"
                    class="avatar"
                />
                <i
                    class="el-icon-circle-close"
                    @click="deleteImg(img.id)"
                    title="点击清除图片"
                ></i>
                </div>
            </div>

            <el-pagination
                style="margin-top: 10px;text-align: right"
                background
                @current-change="currentChange"
                :page-size="pageSize"
                :current-page.sync="current_page"
                :total="total"
                layout="prev, pager, next"
            >
            </el-pagination>
            </el-tab-pane>
        </el-tabs>
        <span slot="footer" class="dialog-footer">
            <el-button @click="beforeClose">取 消</el-button>
            <el-button type="primary" @click="sureImg">确 定 </el-button>
        </span>
    </el-dialog>
  `
});
</script>
