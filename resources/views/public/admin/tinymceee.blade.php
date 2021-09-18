<template id="tinymceee">
    <div style="position: relative;">
        <div style="text-align: right;position: absolute;z-index: 999;right: 0;">
            <el-button size="small" type="primary" @click="openUploadOnlyTextArea()">上传图片</el-button>
        </div>
        <textarea :id="id_name" style="height:600px" v-model="value"></textarea>
        <!--弹框上传图片-->
    <!-- <el-dialog :visible.sync="centerDialogVisible" width="60%" center>
            <el-tabs v-model="activeName2" type="card">
                <el-tab-pane label="上传图片" name="first">
                    <div style="text-align: center" class="submit_Img" v-loading="imgLoading" element-loading-background="rgba(0, 0, 0, 0)">
                        <el-upload class="avatar-uploader" action="{!!yzWebFullUrl('upload.uploadV2.upload',['upload_type'=>'image'])!!}" accept="image/*" :show-file-list="false" :on-success="uploadSuccess" :before-upload="beforeUpload">
                            <div class="avatar_box" v-if="uploadImgUrl">
                                <img :src="uploadImgUrl" class="avatar" />
                            </div>
                            <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                        </el-upload>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="提取网络图片" name="second">
                    <el-input v-model="netImgUrl" placeholder="请输入网络图片地址" style="width:90%"></el-input>
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
                            <img @click="chooseTheImg(img.attachment,img.url)" :src="img.url" class="avatar" />
                            <i class="el-icon-circle-close" @click="deleteImg(img.id)" title="点击清除图片"></i>
                        </div>
                    </div>

                    <el-pagination style="margin-top: 10px;text-align: right" background @current-change="currentChange" :page-size="pageSize" :current-page.sync="current_page" :total="total" layout="prev, pager, next">
                    </el-pagination>
                </el-tab-pane>
            </el-tabs>
            <span slot="footer" class="dialog-footer">
                <el-button @click="centerDialogVisible = false">取 消</el-button>
                <el-button type="primary" @click="sureImg">确 定 </el-button>
            </span>
        </el-dialog> -->
        <el-dialog :visible.sync="uploadShow" width="800px" :before-close="beforeClose" v-if="imgLoading">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="选取图片" name="first">
                    <div class="clearfix con-box">
                        <div class="scroll-box fl">
                            <div class="left-group fl" :class="groupList.length > 17?'bor-right':''">
                                <!-- <p class="TD-gro" v-for="(item,index) in groupList" :class="item.id == groupId && index == groupIndex ? 'text-bg' : ''" @click="handleGroup(item.id,index)">[[item.title]]([[item.source_count]])</p> -->
                                <div class="TD-gro" v-for="(item,index) in groupList" :class="item.id == groupId && index == groupIndex ? 'text-bg' : ''" @click="handleGroup(item.id,index)">[[item.title]]([[item.source_count]])</div>
                            </div>
                        </div>
                        <div class="right-img" :class="groupList.length <= 17?'bor-left':''">
                            <div v-if="resourceList.length <= 0" style="text-align:center;margin-top:20px">
                                暂没有数据~
                            </div>
                            <div v-if="type == 1" class='img-source fl' v-for="(item,index) in resourceList">
                                <img :src="item.url" alt="">
                                <p>[[item.filename]]</p>
                                <div class="img-mark" :style="{ display: item.is_choose ? 'block' : '' }">
                                    <el-checkbox v-model="item.is_choose" class="sle-img" @change="handChecked($event,item.id,item)"></el-checkbox>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="handel  clearfix">
                        <el-popover placement="top" width="300" trigger="click">
                            <span style="color:#409eff;cursor: pointer;" class="fl" slot="reference">新建分组</span>
                            <p class="gro-inp">请输入分组名称</p>
                            <el-input v-model="newGroupName" placeholder="" style="margin-bottom:15px" maxlength="6" show-word-limit></el-input>
                            <el-button type="primary" class="gro-sure" @click="handAddGroup()" style="margin-left:30px">确定</el-button>
                            <el-button class="gro-cancel" @click="handCancelGroup()" style="margin-left:50px">取消</el-button>
                        </el-popover>
                        <div class="fr">
                            <el-pagination background @current-change="currentChange" layout="prev, pager, next" :current-page="current_page" :page-size.sync="Number(page_size)" :total="page_total">
                            </el-pagination>
                        </div>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="提取网络图片" name="second" class="getNet" v-if="type == 1 || type == 3">
                    <div class="getnetimg" v-if="type == 1">
                        <el-image class="defaultImg" style="" :src="previewUrl">
                            <div slot="error" class="image-slot">
                                <i class="el-icon-picture-outline" style="font-size:40px"></i>
                            </div>
                        </el-image>

                        <div>
                            <p>输入图片链接</p>
                            <el-input v-model="imgLink" placeholder="图片链接" style="width:60%;margin:20px"></el-input>
                            <p>
                                <el-button style="margin-bottom:20px;width:100px" @click="conversion()">转化</el-button>
                            </p>
                        </div>
                    </div>
                </el-tab-pane>
            </el-tabs>
            <div class="img-hint">
                <el-upload :action="uploadLink" ref="upload" :multiple="multipleTag" :on-success="handleSucesss" :on-exceed="handleExceed" :on-preview="handlePreview" :before-upload="beforeUpload">
                    <el-button size="small" type="primary">点击上传</el-button>
                </el-upload>
            </div>
            <div class="uploading-btn">
                <span>已选择</span>
                <span v-if="type == 1" style="color:#409eff;">[[selectCount]]/[[resourceTotal]]个图片</span>
                <span slot="footer" class="dialog-footer">
                    <el-button type="primary" @click="sureImg" style="margin-left:20%;width:100px">确 定</el-button>
                    <el-button @click="beforeClose" style="margin-left:5%;width:100px;">取 消</el-button>
                </span>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    const upload_url = '{!! yzWebFullUrl('upload.uploadV2.upload-vue') !!}';
    Vue.component('tinymceee', {
        props: ['value'],
        delimiters: ['[[', ']]'],
        data() {
            let self2 = this;
            return {
                id_name: 'vue-tinymce-' + +new Date() + ((Math.random() * 1000).toFixed(0) + ''),
                flag: true,
                hasInit: false,
                hasChange: false,

                // imgLoading: false,
                // ImgList: [],
                //弹框上传图片的路径
                uploadImg: "",
                uploadImgUrl: "",
                netImgUrl: "",

                // chooseImg: "",
                // radio1: "", //年
                // radio2: "", //月
                // activeName2: "first",
                //是否显示弹框
                // centerDialogVisible: false,
                // pageSize: 0,
                // current_page: 0,
                // total: 0,

                // 新增
                uploadShow: false,
                imgLoading: false,
                activeName: 'first',
                imgChecked: '',
                newGroupName: '',
                currentPage3: 0,
                netUrl: '',
                imgLink: '',
                groupList: [],
                groupId: '',
                groupIndex: '',
                imgList: [],
                page_size: 12, //
                page: 1, //默认请求第一页
                current_page: 0,
                page_total: 0,
                file: {},
                resourceTotal: 0,
                tabPanStatus: '',
                selectArr: [],
                netImg: {},
                previewUrl: '',
                fileList: [],
                resourceList: [],
                uploadType: '',
                uploadLink: '',
                selectCount: 0,
                videoLink: '',
                viewVideoLink: '',
                currentPage: 1,
                multipleTag: true,
                type: 1,
                selNum: 'list'

            }
        },
        watch: {
            value(val) {
                if (!this.hasChange && this.hasInit) {
                    // tinyMCE.activeEditor.setContent(val);
                    this.$nextTick(() =>
                        window.tinymce.get(this.id_name).setContent(val || ''))
                }
                this.flag = true;
            },
            // img(){
            //   if(this.img){
            //       tinyMCE.activeEditor.insertContent(`<img src="${this.img}" >`)
            //     }
            // }
        },



        created() {
            window.addEventListener('beforeunload', e => {
                window.onbeforeunload = null
            });
            this.getGroupList(this.type)
            this.uploadLink = '{!! yzWebFullUrl('upload.uploadV3.upload') !!}'+'&upload_type='+'image'+'&tag_id='+ ''
        },
        mounted: function() {
            var component = this;
            tinymce.init({
                selector: "#" + component.id_name,
                language: "zh_CN",
                hasChange: false,
                hasInit: false,
                menubar: false,
                body_class: 'panel-body ',
                object_resizing: false, //调整尺寸
                end_container_on_empty_block: true,
                powerpaste_word_import: 'merge',
                powerpaste_html_import: 'merge',
                powerpaste_allow_local_images: true,
                code_dialog_height: 450,
                code_dialog_width: 1000,
                max_height: 1000,
                advlist_bullet_styles: 'square',
                advlist_number_styles: 'default',
                imagetools_cors_hosts: ['www.tinymce.com', 'codepen.io'],
                fontsize_formats: "8px 10px 12px 14px 18px 24px 36px",
                default_link_target: '_blank',
                link_title: false,
                nonbreaking_force_tab: true, // inserting nonbreaking space &nbsp; need Nonbreaking Space Plugin
                // plugins: ['advlist anchor autolink autosave code codesample colorpicker colorpicker contextmenu directionality emoticons fullscreen hr image imagetools insertdatetime link lists media nonbreaking noneditable pagebreak powerpaste preview print save searchreplace spellchecker tabfocus table template textcolor textpattern visualblocks visualchars wordcount'],
                // toolbar: ['searchreplace bold italic underline strikethrough fontsizeselect alignleft aligncenter alignright outdent indent  blockquote undo redo removeformat subscript superscript code codesample', 'hr bullist numlist link image charmap preview anchor pagebreak insertdatetime media table emoticons forecolor backcolor fullscreen'],
                plugins: ['advlist anchor autolink autoresize code codesample colorpicker contextmenu directionality emoticons fullscreen hr image imagetools insertdatetime link charmap lists media nonbreaking noneditable pagebreak preview print save searchreplace spellchecker tabfocus table template textcolor textpattern visualblocks visualchars wordcount bdmap powerpaste'],
                toolbar: ['forecolor backcolor searchreplace bold italic underline strikethrough fontsizeselect', ' alignleft aligncenter alignright outdent indent  ltr rtl blockquote undo redo removeformat subscript superscript code codesample', 'hr bullist numlist link image charmap preview anchor pagebreak print insertdatetime media table emoticons fullscreen bdmap'],
                image_advtab: true,
                relative_urls: false,
                remove_script_host: false,
                images_upload_url: upload_url + '&upload_type=image',
                // image_dimensions:false,
                // object_resizing: true,
                // paste_webkit_styles: true ,
                // inline_styles: true, 
                // schema: 'html5',
                // valid_elements: 'img[]',
                // extended_valid_elements: 'img[style|class|src|border|alt|title|hspace|vspace|width|height|align|name|loading]',
                file_picker_callback: function(callback, value, meta) {
                    //文件分类
                    var filetype = '.pdf, .jpg, .jpeg, .png, .gif, .mp3, .mp4';
                    //后端接收上传文件的地址
                    var upurl = upload_url +'&upload_type=video';
                    //为不同插件指定文件类型及后端地址
                    switch (meta.filetype) {
                        case 'image':
                            upurl = upload_url +'&upload_type=image';
                            filetype = '.jpg, .jpeg, .png, .gif';
                            break;
                        case 'media':
                            filetype = '.mp3, .mp4';
                            break;
                        case 'file':
                        default:
                    }
                    //模拟出一个input用于添加本地文件
                    var input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', filetype);
                    input.click();
                    input.onchange = function() {
                        var file = this.files[0];
                        const loading = component.$loading({
                            lock: true,
                            text: '正在上传',
                            spinner: 'el-icon-loading',
                            background: 'rgba(0, 0, 0, 0.7)'
                        });
                        var xhr, formData;
                        // console.log(file, file.name);
                        xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;
                        xhr.open('POST', upurl);
                        xhr.onload = function() {
                            var json;
                            if (xhr.status != 200) {
                                loading.close();
                                // failure('HTTP Error: ' + xhr.status);
                                return;
                            }
                            json = JSON.parse(xhr.responseText);
                            if (!json || typeof json.location != 'string') {
                                // failure('Invalid JSON: ' + xhr.responseText);
                                return;
                            }
                            loading.close();
                            callback(encodeURI(json.location), {
                                title: file.name
                            });
                        };
                        formData = new FormData();
                        formData.append('file', file, file.name);
                        xhr.send(formData);
                    };
                },
                media_url_resolver: (data, resolve)=> {
                    try {
                        let videoUri = encodeURI(data.url);
                        if(data.url.indexOf('.mp4')>-1) {
                            // 判断是否mp4  否则用ifarme嵌套
                            let embedHtml = `<p>
                                    <video src=${ data.url } width="100%" height="auto" style="max-width: 100%;" allowfullscreen="false" controls="controls" controlslist="nodownload">
                                    </video>
                                </p>`;
                            resolve({ html: embedHtml });
                        }else {
                            let embedHtml = `<p>
                                    <iframe frameborder="0" src=${ data.url } width="100%" height="100%" style="max-width: 100%;">
                                    </iframe>
                                </p>`;
                            resolve({ html: embedHtml });
                        }

                    } catch (e) {
                        resolve({ html: "" });
                    }
                },
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
        methods: {
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

            // chooseTheImg(img, img_url) {
            //     if (img_url) {
            //         tinyMCE.activeEditor.insertContent(`<img src="${img_url}" data-mce-src="${img_url}">`)
            //     }
            //     this.centerDialogVisible = false;
            // },

            // chooseYear(year) {
            //     this.currentChange(1);
            // },
            // chooseMonth(month) {
            //     this.currentChange(1);
            // },
            openUploadOnlyTextArea() {
                this.uploadShow = true
                this.uploadImgUrl = "";
                this.netImgUrl = "";
                this.centerDialogVisible = true;
                this.initData();
                this.currentChange(1);
            },
            sureImg() {
                // if (this.uploadImgUrl) {
                //     tinyMCE.activeEditor.insertContent(`<img src="${this.uploadImgUrl}" >`)
                // }
                this.fileList.forEach((item, index) => {
                    tinyMCE.activeEditor.insertContent(`<img src="${item.url}" >`)
                })
                this.centerDialogVisible = false;
                this.uploadShow = false //新增
            },
            // 转化网络地址
            // transform() {
            //     if (!this.netImgUrl) {
            //         this.$message.error("请输入网络地址")
            //         return;
            //     }
            //     this.imgLoading = true;
            //     this.$http
            //         .post("{!! yzWebFullUrl('upload.uploadV2.fetch') !!}", {
            //             url: this.netImgUrl
            //         }).then(response => {
            //             if (response.data.result === 1) {
            //                 if (response.data.data.img_url) {
            //                     tinyMCE.activeEditor.insertContent(`<img src="${response.data.data.img_url}" >`)
            //                 }
            //                 this.centerDialogVisible = false;
            //             } else {
            //                 this.$message.error(response.data.msg);
            //             }
            //             this.imgLoading = false;
            //         })
            //         .catch(err => {
            //             console.error(err);
            //             this.imgLoading = false;

            //         });
            // },
            // currentChange(val) {
            //     this.imgLoading = true;
            //     this.$http.post("{!! yzWebFullUrl('upload.uploadV2.get-image') !!}" + '&group_id=-999&local=local', {
            //             page: val,
            //             year: this.radio1,
            //             month: this.radio2
            //         })
            //         .then(response => {
            //             console.log(response)

            //             if (response.data.result == 1) {
            //                 this.total = response.data.data.total;
            //                 this.ImgList = response.data.data.data;
            //                 this.current_page = response.data.data.current_page;
            //                 this.pageSize = response.data.data.per_page;
            //             } else {
            //                 this.$message.error(response.data.msg);
            //             }
            //             this.imgLoading = false;
            //         })
            //         .catch(err => {
            //             console.error(err);
            //             this.imgLoading = false;
            //         });
            // },
            // clearImg(str) {
            //     this.form[str] = "";
            //     this.form[str + '_url'] = "";
            // },
            // deleteImg(id) {
            //     this.imgLoading = true;
            //     this.$http
            //         .post("{!! yzWebFullUrl('upload.uploadV2.delete') !!}", {
            //             id: id
            //         })
            //         .then(response => {
            //             if (response.data.result == 1) {
            //                 this.$message.success("系统删除成功");
            //                 this.currentChange(1);
            //             } else {
            //                 this.$message.error(response.data.msg);
            //             }
            //             this.imgLoading = false;
            //         })
            //         .catch(err => {
            //             console.error(err);
            //             this.imgLoading = false;
            //         });
            // },
            // uploadSuccess(res, file) {
            //     console.log(res)
            //     if (res.result == 1) {
            //         if (res.data.state == 'SUCCESS') {
            //             this.uploadImg = res.data.attachment; //传相对地址
            //             this.uploadImgUrl = res.data.url;
            //             this.$message.success("上传成功！");
            //         } else {
            //             this.$message.error(res.msg);
            //         }
            //     } else {
            //         this.$message.error(res.msg);
            //     }
            //     this.imgLoading = false;
            // },
            // beforeUpload(file) {
            //     this.imgLoading = true;
            //     const isLt2M = file.size / 1024 / 1024 < 4;
            //     if (!isLt2M) {
            //         this.$message.error("上传图片大小不能超过 4MB!");
            //         this.imgLoading = false;
            //     }
            //     return isLt2M;
            // },
            initData() {
                this.imgLoading = false;
                this.ImgList = [];
                //弹框上传图片的路径
                this.uploadImg = "";
                this.uploadImgUrl = "";
                this.netImgUrl = "";

                this.chooseImg = "";

                this.radio1 = "", //年
                    this.radio2 = "", //月
                    this.activeName2 = "first";
                //是否显示弹框
                // this.centerDialogVisible= false;
                this.pageSize = 0;
                this.current_page = 0;
                this.total = 0;
            },

            // 新增
            // 获取分组列表
            getGroupList(type) {
                this.$http.post('{!! yzWebFullUrl('setting.media.tags') !!}', {
                    source_type: type
                }).then(response => {
                    if (response.data.result) {
                        this.groupList = response.data.data
                        this.imgLoading = true
                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    }
                }, response => {
                    this.$message({
                        message: response.data.msg,
                        type: 'error'
                    });
                });
            },

            getMultimediaList(id, pageSize, page) {
                let url = ''
                if (this.type == 1) { //图片
                    url = '{!! yzWebFullUrl('upload.uploadV3.getImage') !!}'
                    this.page_size = 12
                }
                this.$http.post(url, {
                    tag_id: id,
                    pageSize: this.page_size,
                    page: page
                }).then(response => {
                    if (response.data.result) {
                        this.page_total = response.data.data.total
                        // this.page_size = response.data.per_page;
                        this.current_page = response.data.data.current_page;
                        this.resourceTotal = response.data.data.total
                        this.resourceList = response.data.data.data
                        this.imgLoading = true
                        this.resourceList.forEach((item, index) => {
                            item['is_choose'] = 0
                        });
                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    }
                }, response => {
                    this.$message({
                        message: response.data.msg,
                        type: 'error'
                    });
                });
            },

            // 添加分组
            addGroup(sourceType, groupName) {
                this.$http.post('{!! yzWebFullUrl('setting.media.addTag') !!}', {
                    source_type: sourceType,
                    title: groupName
                }).then(response => {
                    if (response.data.result) {
                        this.closePopover()
                        this.$message({
                            message: '添加分组成功',
                            type: 'success'
                        });
                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    }
                }, response => {
                    this.$message({
                        message: response.data.msg,
                        type: 'error'
                    });
                });
            },
            // 提取网络图片
            getNetImg(url, tag_id) {
                this.$http.post('{!! yzWebFullUrl('upload.uploadV3.fetch') !!}', {
                    url,
                    tag_id
                }).then(response => {
                    if (response.data.result) {
                        this.netImg = response.data.data
                        console.log(this.netImg, '网络图片')
                        this.imgLink = '',
                            this.$message({
                                message: '图片已提取到未分组',
                                type: 'success'
                            });
                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    }
                }, response => {
                    this.$message({
                        message: response.data.msg,
                        type: 'error'
                    });
                });
            },

            handleGroup(id, ind) {
                this.groupIndex = ind;
                this.groupId = id
                this.getMultimediaList(id, this.page_size, this.page) //请求每一个分组的资源
                this.selectArr = []
                if (this.type == 1) {
                    this.uploadLink = '{!! yzWebFullUrl('upload.uploadV3.upload') !!}'+'&upload_type='+'image'+'&tag_id='+ this.groupId
                } else if (this.type == 3) {
                    this.uploadLink = '{!! yzWebFullUrl('upload.uploadV3.upload') !!}'+'&upload_type='+'video'+'&tag_id='+ this.groupId
                } else {
                    this.uploadLink = '{!! yzWebFullUrl('upload.uploadV3.upload') !!}'+'&upload_type='+'audio'+'&tag_id='+ this.groupId
                }
                this.selectCount = 0
            },

            // 新建分组
            handAddGroup() {
                if (this.newGroupName == '') {
                    this.$message({
                        message: '分组名不能为空',
                        type: 'error'
                    });
                    return
                }
                console.log(this.type);
                this.addGroup(this.type, this.newGroupName)
                this.newGroupName = ''
                // 新建完分组后重新请求分组列表接口
                setTimeout(() => {
                    this.getGroupList(this.type)
                }, 800);
                this.closePopover()

            },

            handCancelGroup() {
                this.newGroupName = ''
                this.closePopover()
            },

            closePopover() {
                if (document.all) {
                    document.getElementById('ImgMul').click();
                } else { // 其它浏览器
                    var e = document.createEvent('MouseEvents')
                    e.initEvent('click', true, true)
                    document.getElementById('ImgMul').dispatchEvent(e)
                }
            },

            // 弹窗的关闭事件
            handleClose(done) {

            },

            handleClick(tab, event) {
                this.tabPanStatus = tab.paneName
                this.imgLink = ''
                this.previewUrl = ''

            },
            handleSizeChange() {

            },
            currentChange(val) {
                this.currentPage = val
                this.getMultimediaList(this.groupId, this.page_size, val)
                this.selectCount = 0
            },

            // 取消按钮
            beforeClose() {
                this.activeName == 'first'
                this.$emit('replace', this.uploadShow);
                this.fileList = []
                this.resourceList = [] //弹窗退出清空数据，取消勾选状态
                this.groupId = ''
                this.groupIndex = ''
                this.activeName = 'first'
                this.uploadShow = false
            },

            beforeUpload(file) {
                this.resourceList.forEach(item => {
                    return item.is_choose = false
                })
                // this.imgLoading = true;
                // const isLt2M = file.size / 1024 / 1024 < 4;
                // if (!isLt2M) {
                //     this.$message.error("上传文件大小不能超过 4MB!");
                //     // this.imgLoading = false;
                // }
                // return isLt2M;
            },

            //上传成功的回调
            handleSucesss(response, file, fileList) {
                if (response.result == 1) {
                    this.uploadImg = response.data.attachment //存储相对地址，供后期使用
                    response.data.is_choose = true
                    this.resourceList.unshift(response.data)
                    let arr = this.resourceList.slice(0, this.page_size)
                    this.resourceList = arr
                    console.log(this.resourceList, '12435435')
                    console.log(this, 'oooooooooooo')
                    this.handChecked(null, response.data.id, response.data)
                    // this.$refs.upload.clearFiles()
                    // if(this.resourceList.length >= 8) {
                    //     this.page_total += fileList.length
                    // }
                    let url = ''
                    if (this.type == 1) { //图片
                        url = '{!! yzWebFullUrl('upload.uploadV3.getImage') !!}'
                    }
                    this.$http.post(url, {
                        tag_id: this.groupId,
                        pageSize: this.page_size,
                        page: this.current_page
                    }).then(response => {
                        if (response.data.result) {
                            this.page_total = response.data.data.total
                            // this.page_size = response.data.per_page;
                            this.current_page = response.data.data.current_page;
                            this.resourceTotal = response.data.data.total
                            // this.resourceList = response.data.data.data
                            // this.imgLoading = true
                            // this.resourceList.forEach((item, index) => {
                            //         item['is_choose'] = 0
                            // });
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }
                    }, response => {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    });

                    this.getSelect()
                    this.getGroupList(this.type)
                    this.$message({
                        message: response.msg,
                        type: 'success'
                    });
                    // this.resourceTotal += fileList.length
                } else {
                    this.$message.error(response.msg);
                }
                console.log(this.$refs);
            },

            // // 文件超出个数限制时的钩子
            handleExceed(files) {
                if (files.length > 5) {
                    this.$message.error("不能一次上传超过5个文件！");
                    return
                }
            },

            // 点击文件列表中已上传的文件时的钩子
            handlePreview(file) {

            },
            handChecked(val, id, item) {
                this.uploadImgUrl = item.url;
                let arr = []
                let data = []
                let list = []
                this.resourceList.forEach(item => {
                    if (this.selNum == 'one') {
                        if (item.id !== id) {
                            item.is_choose = false
                        }
                    }
                    if (item.is_choose == true) {
                        arr.push(item.id)
                        list.push(item)
                    }
                })
                this.selectArr = arr
                this.fileList = list
                this.$forceUpdate()
                this.getSelect()

            },

            getSelect() {
                let count = 0
                this.resourceList.forEach(item => {
                    if (item.is_choose) {
                        count++
                    }
                })
                this.selectCount = count;
            },

            //转换网络图片事件
            conversion() {
                this.previewUrl = this.imgLink;
                console.log(this.groupId);
                this.getNetImg(this.imgLink, this.groupId)
                console.log(this.imgLink);
            },

            playClic() {

            },

            getNetVideo() {
                this.viewVideoLink = this.videoLink
                this.$emit('videoclik', this.viewVideoLink);
            }
        },
        template: '#tinymceee'
    });
</script>

<style scoped>
    .ellipsis {
        width: 100px;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
        overflow: hidden
    }

    /* 上传 */
    .aduio-box {
        margin-top: 20px
    }

    .aduio-item {
        padding: 10px;
        margin: 0 15px 15px 0;
        width: 280px;
        height: 130px;
        border: 1px solid #c8cede;
    }

    .audio-title {
        display: inline-block;
        width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle
    }

    .aduio-right {
        width: 60px;
        height: 60px;

    }

    .aduio-right {
        margin-top: 30px;
        text-align: center;
    }

    .aduio-right img {
        width: 40px;
        height: 40px;
    }

    .play-box {
        position: relative;
        margin: 15px 15px 0 0;
        text-align: center;
        z-index: 100;
    }

    .play-box img {
        width: 40px;
        height: 40px;
    }

    .uploading-btn {
        margin-top: 10px;
        /* text-align:center; */
    }

    .uploading-btn span {
        /* text-align:left */
    }

    .video-box {
        margin: 20px 0 30px 0;
        width: 100%;
    }

    .video-box .video-item {
        position: relative;
        padding: 10px;
        margin-right: 15px;
        width: 40%;
        height: 130px;
        border: 1px solid #c8cede;
    }

    .checked-pos {
        position: absolute;
        top: 10px;
        left: 10px;
    }

    .vedio-file {
        width: 150px;
        height: 100%
    }

    .vedio-file video {
        width: 150px;
        height: 100%
    }

    .vedio-right {
        text-align: left
    }

    .vedio-right p {
        margin: 20px 0 0 15px;
    }

    .getNetWork {
        margin: 30px 0;
        text-align: center
    }

    .con-box {
        position: relative;

    }

    .left-group {
        /* position: relative; */
        /* left:-20px; */
        /* position: absolute; */
        /* height: 500px; */
        /* overflow: scroll; */
        padding: 10px 0;
        left: 0;
        top: 0;
        width: 180px;
        max-width: 150px;
        min-width: 110px;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
        overflow: hidden;
    }

    .TD-gro {
        /* margin: 0; */
        padding: 3px 0 3px 0px;
        cursor: pointer;
    }

    .left-group .gro-bg {
        background: rgb(41, 186, 156);
        color: #fff;
    }

    .right-img {
        /* min-width: 350px; */
        margin-left: 150px;
        /* width:80%; */
        min-height: 475px;
        /* border-left:1px solid #c8cede */
    }

    .handel {
        margin-top: 10px;

    }

    .img-hint {
        position: absolute;
        width: 200px;
        top: 20px;
        right: 21px;
        margin-bottom: 15px;
        height: 40px;
        line-height: 40px;
        text-align: right;
        z-index: 9999;
    }

    .img-hint>div {
        float: right
    }

    /* 隐藏上传组件的默认样式 */
    .img-hint input {
        display: none;
    }

    .img-hint .el-upload-list {
        display: none
    }

    .img-source {
        position: relative;
        margin: 10px 0 0 10px;
        width: 140px;
        height: 150px;
    }

    .img-source img {
        width: 100%;
        height: 110px;
    }

    .img-source p {
        padding: 0 5px;
        width: 100%;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
        overflow: hidden;
    }

    .img-source .sle-img {
        position: absolute;
        /* display:none; */
        top: 5px;
        left: 5px;
    }

    .img-source p {
        margin-top: 15px;
        text-align: center;
    }

    .img-mark {
        position: absolute;
        display: none;
        width: 100%;
        height: 110px;
        top: 0px;
        left: 0px;
        background: rgba(41, 186, 156, 0.3);
        border: 1px solid rgb(41, 186, 156);
    }

    .img-source:hover .img-mark {
        display: block
    }

    .vedio-source,
    .audio-source {
        position: relative;
        margin: 10px 0 0px 23px;
        width: 280px;
        height: 102px;
        border: 1px solid #c8cede;
    }

    .video-mark {
        position: absolute;
        padding: 5px;
        display: none;
        width: 100%;
        height: 100%;
        top: 0px;
        left: 0px;
        background: rgba(41, 186, 156, 0.3);
        border: 1px solid rgb(41, 186, 156);
    }

    .vedio-upload {
        width: 150px;
        height: 100%;
    }

    .vedio-upload video {
        width: 150px;
        height: 100%;
        vertical-align: top;
    }

    .vedio-source:hover .video-mark {
        display: block
    }

    .audio-source:hover .video-mark {
        display: block
    }

    .audio-source {}

    .defaultImg {
        margin-top: 20px;
        width: 150px;
        height: 150px;
        line-height: 150px;
        border: 1px solid #c8cede;
        text-align: center;
    }

    .getNet {
        text-align: center;
    }

    .getnetvideo,
    .getnetaudio {
        min-height: 300px
    }

    #multimedia-material .el-tabs__content {
        overflow: initial;
    }

    .video-time {
        position: absolute;
        padding-right: 8px;
        bottom: 10px;
        left: 10px;
        color: #fff;
        width: 50px;
        height: 20px;
        font-size: 12px;
        line-height: 20px;
        border-radius: 5px;
        text-align: right;
        background: rgba(0, 0, 0, 0.5)
    }

    .play-triangle {
        position: absolute;
        top: 3px;
        left: 6px;
        /* padding-left:5px; */
        height: 0;
        width: 0;
        overflow: hidden;
        /* 这里设置overflow, font-size, line-height */
        font-size: 0;
        /*是因为, 虽然宽高度为0, 但在IE6下会具有默认的 */
        line-height: 0;
        /* 字体大小和行高, 导致盒子呈现被撑开的长矩形 */
        border-color: transparent transparent transparent #fff;
        border-style: solid;
        border-width: 7px;
    }

    .bor-left {
        /* border-left: 1px solid #c8cede; */
    }

    .bor-right {
        border-right: 1px solid #c8cede;
    }

    .text-bg {
        background: #29BA9C;
        color: #fff;
    }

    .scroll-box {
        width: 152px;
        height: 475px;
        overflow-y: scroll;
    }

    /*滚动条整体样式*/
    .scroll-box::-webkit-scrollbar {
        width: 2px;
    }

    /*滚动条滑块*/
    .scroll-box::-webkit-scrollbar-thumb {
        border-radius: 30px;
        /* -webkit-box-shadow: inset 0 0 5px rgba(0,0,0,0.2); */
        background: #29BA9C;
    }

    /*滚动条轨道*/
    .scroll-box::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 1px rgba(0, 0, 0, 0);
        border-radius: 30px;
        background: #ccc;
    }

    .el-tabs__header {
        margin-bottom: 0;
    }

    .el-dialog__body {
        position: relative;
    }

    .image-slot {
        margin-top: 20px;
    }

    /* .el-form-item__content{
        line-height: 22px;
    } */

    .tox-dialog__body .tox-dialog__body-nav-item:nth-child(3) {
        display: none;
    }
</style>