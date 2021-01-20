<script>
Vue.component('tinymceee', {
    props: ['value'],
    data(){
        let self2 = this;
        return{
            id_name:'vue-tinymce-' + +new Date() + ((Math.random() * 1000).toFixed(0) + ''),
            flag:true,
            hasInit: false,
            hasChange: false,

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
            centerDialogVisible: false,
            pageSize: 0,
            current_page: 0,
            total: 0,
        }
    },
    watch:{
        value(val){
            if(!this.hasChange && this.hasInit){
                // tinyMCE.activeEditor.setContent(val);
                this.$nextTick(() =>
                    window.tinymce.get(this.id_name).setContent(val || ''))
            }
            this.flag=true;
        },
        // img(){
        //   if(this.img){
        //       tinyMCE.activeEditor.insertContent(`<img src="${this.img}" >`)
        //     }
        // }
    },
    created(){
      window.addEventListener('beforeunload', e => {
        window.onbeforeunload =null
      });
    },
    mounted: function(){
        var component = this;
        tinymce.init({
            selector: "#"+component.id_name,
            language: "zh_CN",
            hasChange: false,
            hasInit: false,
            menubar: false,
            body_class: 'panel-body ',
            object_resizing: false,//调整尺寸
            end_container_on_empty_block: true,
            powerpaste_word_import: 'merge',
            powerpaste_html_import: 'merge',
            powerpaste_allow_local_images: true,
            code_dialog_height: 450,
            code_dialog_width: 1000,
            advlist_bullet_styles: 'square',
            advlist_number_styles: 'default',
            imagetools_cors_hosts: ['www.tinymce.com', 'codepen.io'],
            fontsize_formats: "8px 10px 12px 14px 18px 24px 36px",
            default_link_target: '_blank',
            link_title: false,
            nonbreaking_force_tab: true, // inserting nonbreaking space &nbsp; need Nonbreaking Space Plugin
            plugins: ['advlist anchor autolink autosave code codesample colorpicker colorpicker contextmenu directionality emoticons fullscreen hr image imagetools insertdatetime link lists media nonbreaking noneditable pagebreak powerpaste preview print save searchreplace spellchecker tabfocus table template textcolor textpattern visualblocks visualchars wordcount'],
            toolbar: ['searchreplace bold italic underline strikethrough fontsizeselect alignleft aligncenter alignright outdent indent  blockquote undo redo removeformat subscript superscript code codesample', 'hr bullist numlist link image charmap preview anchor pagebreak insertdatetime media table emoticons forecolor backcolor fullscreen'],
            image_advtab:true,
            relative_urls: false,
            remove_script_host: false,
            // image_dimensions:false,
            // object_resizing: true,
            // paste_webkit_styles: true ,
            // inline_styles: true, 
            // schema: 'html5',
            // valid_elements: 'img[]',
            // extended_valid_elements: 'img[style|class|src|border|alt|title|hspace|vspace|width|height|align|name|loading]',

            init_instance_callback: editor => {
                if (this.value) {
                editor.setContent(this.value)
                }
                this.hasInit = true
                editor.on('NodeChange Change KeyUp SetContent', () => {
                    this.hasChange = true
                    this.$emit('input', editor.getContent())
                })
            },
            // setup: function(editor) {
            //     editor.on('input undo redo execCommand blur', function(e) {
            //         console.log("111111111111111111")
            //         component.flag=false;
            //         component.$emit('input', editor.getContent());
            //     }) 
            // }
        });
    },
    methods:{
        setContent(value) {
          window.tinymce.get(this.tinymceId).setContent(value)
        },
        getContent() {
          window.tinymce.get(this.tinymceId).getContent()
        },
        destroyTinymce() {
          const tinymce = window.tinymce.get(this.tinymceId)
          if (this.fullscreen) {
            tinymce.execCommand('mceFullScreen')
          }

          if (tinymce) {
            tinymce.destroy()
          }
        },
        

        chooseTheImg(img,img_url) {
            if(img_url){
                tinyMCE.activeEditor.insertContent(`<img src="${img_url}" data-mce-src="${img_url}">`)
            }
            this.centerDialogVisible = false;
        },
        
        chooseYear(year) {
            this.currentChange(1);
        },
        chooseMonth(month) {
            this.currentChange(1);
        },
        openUpload() {
            this.uploadImgUrl = "";
            this.netImgUrl = "";
            this.centerDialogVisible = true;
            this.initData();
            this.currentChange(1);
        },
        sureImg() {
            if(this.uploadImgUrl){
                tinyMCE.activeEditor.insertContent(`<img src="${this.uploadImgUrl}" >`)
            }
            this.centerDialogVisible = false;
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
                        if(response.data.data.img_url){
                            tinyMCE.activeEditor.insertContent(`<img src="${response.data.data.img_url}" >`)
                        }
                        this.centerDialogVisible = false;
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
        clearImg(str) {
            this.form[str] = "";
            this.form[str+'_url'] = "";
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
            console.log(res)
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
        initData() {
            this.imgLoading= false;
            this.ImgList= [];
            //弹框上传图片的路径
            this.uploadImg= "";
            this.uploadImgUrl= "";
            this.netImgUrl= "";

            this.chooseImg= "";

            this.radio1= "", //年
            this.radio2= "", //月
            this.activeName2= "first";
            //是否显示弹框
            // this.centerDialogVisible= false;
            this.pageSize= 0;
            this.current_page= 0;
            this.total= 0;
        }  
    },
  template: `<div style="position: relative;">
              <div style="text-align: right;position: absolute;z-index: 999;right: 0;">
                <el-button size="small" type="primary" @click="openUpload()">上传图片</el-button>
              </div>
              <textarea :id="id_name" style="height:300px" v-model="value"></textarea>
              <!--弹框上传图片-->
              <el-dialog :visible.sync="centerDialogVisible" width="60%" center>
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
                      <el-button @click="centerDialogVisible = false">取 消</el-button>
                      <el-button type="primary" @click="sureImg">确 定 </el-button>
                  </span>
              </el-dialog>
      <!--end-->
            </div>`
});
</script>
