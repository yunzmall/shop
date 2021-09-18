<template id="ImgMul">
    <div styl="width:800px">
    <el-dialog
        :visible.sync="uploadShow"
        width="800px"
        :before-close="beforeClose"
        v-if="imgLoading"
        >
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane  :label="'选取' + tabPan" name="first">
            <div class="clearfix con-box">
            <div class="scroll-box fl">
                <div class="left-group fl" :class="groupList.length > 17?'bor-right':''" >
                    <p class="D-gro" v-for="(item,index) in groupList"  :class="item.id == groupId && index == groupIndex ? 'text-bg' : ''" @click="handleGroup(item.id,index)">[[item.title]]([[item.source_count]])</p>
                </div>
            </div>

            <div class="right-img" :class="groupList.length <= 17?'bor-left':''">
                <div style="margin:10px 0 10px 10px">
                    <el-date-picker
                        v-model="filterDate"
                        type="month"
                        placeholder="选择月"
                        @change="getMultimediaList('',page_size,1)"
                        >
                    </el-date-picker>
                </div>
                <!-- <div class="img-hint"> -->
                    <!-- <span style="margin-right:20px">[[type == 1?'大小不超过4M，已关闭图片水印':'']]<i v-if="type == 1" class="el-icon-question"></i></span> -->
                    <!-- <el-upload
                        :action="uploadLink"
                        ref="upload"
                        multiple
                        :on-success="handleSucesss"
                        :on-exceed="handleExceed"
                        :on-preview="handlePreview"
                        :before-upload="beforeUpload"
                        >
                        <el-button size="small" type="primary">点击上传</el-button>
                    </el-upload>
                </div> -->

                <!-- 没有数据 -->
                <div v-if="resourceList.length <= 0" style="text-align:center;margin-top:20px">
                    暂没有数据~
                </div>

                <!-- 图片 type：1 -->
                <div v-if="type == 1" class='img-source fl' v-for="(item,index) in resourceList" >
                    <img :src="item.url" alt="">
                    <p>[[item.filename]]</p>
                    <div class="img-mark" :style="{ display: item.is_choose ? 'block' : '' }">
                        <el-checkbox v-model="item.is_choose" class="sle-img"  @change="handChecked($event,item.id,item)"></el-checkbox>
                    </div>
                </div>

                <!-- 视频 type：3 -->
                <div class="vedio-source fl" v-for="(item,index) in resourceList" v-if="type == 3" >
                    <div class="vedio-upload fl">
                        <video :src="item.url"></video>
                    </div>
                    <p class="fl ellipsis" style="margin:20px 0 0 15px;width:110px;">[[item.filename]]</p>
                    <p class="fl ellipsis" style="margin:20px 0 0 15px">[[item.created_at]]</p>
                    <div class="video-mark" :style="{ display: item.is_choose ? 'block' : '' }">
                        <el-checkbox  v-model="item.is_choose" @change="handChecked($event,item.id,item)" class="sle-img"></el-checkbox>
                    </div>
                    <span class="video-time"><i class="play-triangle"></i>[[Math.floor(item.timeline / 60)]]:[[Math.floor(item.timeline % 60) >= 10?Math.floor(item.timeline % 60):'0' + Math.floor(item.timeline % 60)]]</span>
                </div>

                <!-- 音频 type: 2 -->
                <div class="audio-source fl" v-for="(item,index) in resourceList" v-if="type == 2" >
                    <div class="fl">
                        <p style="margin:8px 0 0 30px;width:180px;" class="ellipsis">[[item.filename]]</p>
                        <p style="margin:20px 0 0 30px;"  >[[item.created_at]]</p>
                    </div>
                    <div class="fr play-box" @click="playClic()">
                        <img src="../../../../static/images/play.png" alt="">
                        <!-- <img src="../../../../static/images/puse.png" alt=""> -->
                        <p>[[Math.floor(Number(item.timeline) / 60)]]:[[Math.floor(Number(item.timeline) % 60)]]</p>
                    </div>
                    <div class="video-mark" :style="{ display: item.is_choose ? 'block' : '' }">
                        <el-checkbox v-model="item.is_choose" @change="handChecked($event,item.id,item)" class="sle-img"></el-checkbox>
                    </div>
                </div>
            </div>
            </div>
                <div class="handel  clearfix">
                <el-popover placement="top" width="300" trigger="click">
                    <span style="color:#409eff;cursor: pointer;" class="fl" slot="reference" >新建分组</span>
                    <p class="gro-inp">请输入分组名称</p>
                    <el-input v-model="newGroupName" placeholder="" style="margin-bottom:15px" maxlength="6"show-word-limit></el-input>
                    <el-button type="primary" class="gro-sure" @click="handAddGroup()" style="margin-left:30px">确定</el-button>
                    <el-button class="gro-cancel"  @click="handCancelGroup()" style="margin-left:50px">取消</el-button>
                </el-popover>
                        <div class="fr">
                        <el-pagination
                            background
                            @current-change="currentChange"
                            layout="prev, pager, next"
                            :current-page="current_page"
                            :page-size.sync="Number(page_size)"
                            :total="page_total">
                        </el-pagination>
                    </div>
                </div>

            </el-tab-pane>
            <el-tab-pane :label="tanPanNet" name="second" class="getNet" v-if="type == 1 || type == 3">
            <div class="getnetimg" v-if="type == 1">
                <el-image
                        class="defaultImg"
                        style=""
                        :src="previewUrl"
                    >
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

            <div class="getnetvideo" v-if="type == 3">
                    <span>视频链接</span>
                    <el-input v-model="videoLink" placeholder="视频链接" style="width:60%;margin:20px"></el-input>
                    <el-button type="primary" style="width:80px" @click="getNetVideo()">确定</el-button>
                    <!-- <div v-if="viewVideoLink != ''">
                        <video :src="viewVideoLink" controls></video>
                    </div> -->
            </div>

            <!-- <div class="getnetaudio" v-if="type == 3">
                    <span>音频链接</span>
                    <el-input v-model="imgLink" placeholder="音频链接" style="width:60%;margin:20px"></el-input>
                    <el-button type="primary" style="width:80px">确定</el-button>
            </div> -->
            </el-tab-pane>

        </el-tabs>

        <div class="img-hint">
                    <!-- <span style="margin-right:20px">[[type == 1?'大小不超过4M，已关闭图片水印':'']]<i v-if="type == 1" class="el-icon-question"></i></span> -->
                    <el-upload
                        :action="uploadLink"
                        ref="upload"
                        :multiple = "multipleTag"
                        :on-success="handleSucesss"
                        :on-exceed="handleExceed"
                        :on-preview="handlePreview"
                        :before-upload="beforeUpload"
                        >
                        <el-button size="small" type="primary">点击上传</el-button>
                    </el-upload>
                </div>
            <div class="uploading-btn">
                <span>已选择</span>
                <span v-if="type == 1" style="color:#409eff;">[[selectCount]]/[[resourceTotal]]个图片</span>
                <span v-if="type == 3" style="color:#409eff;">[[selectCount]]/[[resourceTotal]]个视频</span>
                <span v-if="type == 2" style="color:#409eff;">[[selectCount]]/[[resourceTotal]]个音频</span>
                <span slot="footer" class="dialog-footer">
                    <el-button type="primary" @click="sureImg" style="margin-left:20%;width:100px">确 定</el-button>
                    <el-button @click="beforeClose" style="margin-left:5%;width:100px;">取 消</el-button>
                </span>
            </div>
    </el-dialog>
    </div>
</template>

<script>
Vue.component('uploadMultimediaImg', {
    props: ['uploadShow','type','name','sourceType','selNum'],
    delimiters: ['[[', ']]'],
    data(){
      return{
        imgLoading: false,
        activeName:'first',
        imgChecked:'',
        newGroupName:'',
        currentPage3:0,
        netUrl:'',
        imgLink:'',
        groupList:[],
        groupId:'',
        groupIndex:'',
        imgList:[],
        page_size:12,//
        page:1,//默认请求第一页
        current_page:0,
        page_total:0,
        file:{},
        vedioList:[],
        audioList:[],
        tanPanNet:'',
        tabPan:'',
        resourceTotal:0,
        tabPanStatus:'',
        selectArr:[],
        netImg:{},
        previewUrl:'',
        fileList:[],
        resourceList:[],
        uploadType:'',
        uploadLink:'',
        selectCount:0,
        videoLink:'',
        viewVideoLink:'',
        currentPage:1,
        multipleTag:null,
        filterDate:null
      }
    },
    watch:{
        uploadShow() {
            if(this.uploadShow) {
                this.getGroupList(this.type)
                this.getMultimediaList('',this.page_size,this.page)
            }
       },

      type() {
          if(this.type ==1) {
              this.tabPan = '图片'
              this.tanPanNet = '提取网络图片'
              this.uploadLink = '{!! yzWebFullUrl('upload.uploadV3.upload') !!}'+'&upload_type='+'image'+'&tag_id='+ ''
              this.page_size = 12
          } else if(this.type == 3) {
              this.tabPan = '视频'
              this.tanPanNet ='提取网络视频'
              this.uploadLink = '{!! yzWebFullUrl('upload.uploadV3.upload') !!}'+'&upload_type='+'video'+'&tag_id='+ ''
              this.page_size = 8
          } else {
              this.tabPan = '音频'
              this.tanPanNet = '提取网络音频'
              this.uploadLink = '{!! yzWebFullUrl('upload.uploadV3.upload') !!}'+'&upload_type='+'audio'+'&tag_id='+ ''
              this.page_size = 8
          }
        },
        name() {
            console.log(this.name,'34567890-')
        },
        selNum() {
            console.log(this.selNum,'uhguahguhagio')
            if(this.selNum == 'one') {
                this.multipleTag = false//单个选择的不支持多个同时上传
            }
            if(this.selNum == 'more') {
                this.multipleTag = true//单个选择的不支持多个同时上传
            }
        }
    },

    methods:{
        // 获取分组列表
        getGroupList(type) {
        this.type = type;//存储父组件传过来的资源类型，方便新建分组的时候使用
        this.$http.post('{!! yzWebFullUrl('setting.media.tags') !!}', {
                    source_type: type
                }).then(response => {
                if (response.data.result) {
                    this.groupList  = response.data.data
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

        getMultimediaList(id,pageSize,page) {
            let url = ''
            if(this.type == 1) {//图片
                url = '{!! yzWebFullUrl('upload.uploadV3.getImage') !!}'
              this.page_size = 12
            } else if(this.type == 3) {//视频
                url = '{!! yzWebFullUrl('upload.uploadV3.getVideo') !!}'
              this.page_size = 8
              console.log(this.page_size,'34567890')
            } else {//音频
                url = '{!! yzWebFullUrl('upload.uploadV3.getAudio') !!}'
              this.page_size = 8
            }
            let filterDate={
                year:null,
                month:null
            }
            if(this.filterDate){
                let d=new Date(this.filterDate);
                filterDate.year=d.getFullYear();
                filterDate.month=d.getMonth()+1;
            }

            this.$http.post(url, {
                tag_id: id,
                pageSize:this.page_size,
                page:page,
                date:filterDate
                }).then(response => {
                if (response.data.result) {
                    this.page_total= response.data.data.total
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
        getNetImg(url,tag_id) {
            this.$http.post('{!! yzWebFullUrl('upload.uploadV3.fetch') !!}', {
                    url,
                    tag_id
                }).then(response => {
                if (response.data.result) {
                    this.netImg = response.data.data
                    console.log(this.netImg,'网络图片')
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

        handleGroup(id,ind) {
            this.groupIndex = ind;
            this.groupId = id
            this.getMultimediaList(id,this.page_size,this.page)//请求每一个分组的资源
            this.selectArr = []
            if(this.type == 1) {
                this.uploadLink = '{!! yzWebFullUrl('upload.uploadV3.upload') !!}'+'&upload_type='+'image'+'&tag_id='+ this.groupId
            } else if(this.type == 3) {
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
            } else {// 其它浏览器
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
        currentChange(val){
            this.currentPage = val
            this.getMultimediaList(this.groupId,this.page_size,val)
            this.selectCount = 0
        },

        // 取消按钮
        beforeClose() {
            this.activeName == 'first'
            this.$emit('replace', this.uploadShow);
            this.fileList = []
            this.resourceList=[]//弹窗退出清空数据，取消勾选状态
            this.groupId = ''
            this.groupIndex=''
            this.activeName = 'first'
        },

        // 确定按钮
        sureImg(){
            if( this.activeName == 'first') {
                let list = []
                    this.resourceList.forEach(item => {
                        if(item.is_choose == true) {
                            list.push(item)
                        }
                    })
                this.fileList = list
                this.$emit('sure',this.name, this.uploadShow,this.fileList);
                this.beforeClose();
                this.fileList = []
                this.resourceList=[]//弹窗退出清空数据，取消勾选状态
            } else if(this.fileList.length <= 0) {
                this.beforeClose();
            } else {
                this.$emit('sure',this.name, this.uploadShow)
                this.beforeClose();
                this.fileList = []
            }
            this.activeName = 'first'
        },

        beforeUpload(file) {
            console.log(file,'12456783456789')
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
            console.log(response,'aaaaaaaaaaaaaaaaaa')
            if(response.result  == 1) {
                response.data.is_choose = true
                this.resourceList.unshift(response.data)
                let arr = this.resourceList.slice(0,this.page_size)
                this.resourceList = arr
                console.log(this.resourceList,'12435435')
                // this.$refs.upload.clearFiles()
                // if(this.resourceList.length >= 8) {
                //     this.page_total += fileList.length
                // }
                let url = ''
                if(this.type == 1) {//图片
                    url = '{!! yzWebFullUrl('upload.uploadV3.getImage') !!}'
                } else if(this.type == 3) {//视频
                    url = '{!! yzWebFullUrl('upload.uploadV3.getVideo') !!}'
                } else {//音频
                    url = '{!! yzWebFullUrl('upload.uploadV3.getAudio') !!}'
                }
                this.$http.post(url, {
                    tag_id: this.groupId,
                    pageSize:this.page_size,
                    page:this.current_page
                    }).then(response => {
                    if (response.data.result) {
                        this.page_total= response.data.data.total
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
            }else {
                this.$message.error(response.msg);
            }
            console.log(this.$refs);
        },

        // // 文件超出个数限制时的钩子
        handleExceed(files) {
           if(files.length > 5) {
            this.$message.error("不能一次上传超过5个文件！");
            return
           }
        },

        // 点击文件列表中已上传的文件时的钩子
        handlePreview(file){

        },
        handChecked(val,id,item) {
            let arr = []
            let data = []
            let list = []
            this.resourceList.forEach(item => {
                if(this.selNum=='one') {
                    if(item.id!==id) {
                        item.is_choose = false
                    }
                }
                if(item.is_choose == true) {
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
                if(item.is_choose)  {
                    count++
                }
            })
            this.selectCount = count;
        },

        //转换网络图片事件
        conversion(){
            this.previewUrl = this.imgLink;
            console.log(this.groupId);
            this.getNetImg(this.imgLink,this.groupId)
            console.log(this.imgLink);
        },

        playClic() {

        },

        getNetVideo() {
            this.viewVideoLink = this.videoLink
            this.$emit('videoclik', this.viewVideoLink);
        }
    },
  template: '#ImgMul'
});
</script>

<style scoped>
    .ellipsis  {
        width: 100px;
        text-overflow: ellipsis;
         white-space: nowrap;
         vertical-align:middle;
         overflow:hidden
    }
        /* 上传 */
    .aduio-box {
        margin-top:20px
    }
    .aduio-item {
        padding:10px;
        margin:0 15px 15px 0;
        width:280px;
        height:130px;
        border:1px solid #c8cede;
    }
    .audio-title {
        display:inline-block;
        width:150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align:middle
    }
    .aduio-right {
        width:60px;
        height:60px;

    }
    .aduio-right {
        margin-top:30px;
        text-align:center;
    }
    .aduio-right img {
        width:40px;
        height:40px;
    }
    .play-box {
        position:relative;
        margin:15px 15px 0 0;
        text-align:center;
        z-index:100;
    }
    .play-box img {
        width:40px;
        height:40px;
    }
    .uploading-btn {
        margin-top: 10px;
        /* text-align:center; */
    }
    .uploading-btn span {
        /* text-align:left */
    }
    .video-box {
        margin:20px 0 30px 0;
        width:100%;
    }
    .video-box .video-item {
        position: relative;
        padding:10px;
        margin-right:15px;
        width:40%;
        height:130px;
        border:1px solid #c8cede;
    }
    .checked-pos {
        position: absolute;
        top:10px;
        left:10px;
    }
    .vedio-file {
        width:150px;
        height:100%
    }
    .vedio-file video {
        width:150px;
        height:100%
    }
    .vedio-right {
        text-align:left
    }

    .vedio-right p{
        margin:20px 0 0 15px;
    }
    .getNetWork {
        margin:30px 0;
        text-align:center
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
        width:180px;
        max-width:150px;
        min-width:110px;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align:middle;
        overflow:hidden;
    }
    .D-gro {
        margin:0;
        padding:3px 0 3px 0px;
        cursor: pointer;
    }
    .left-group .gro-bg {
        background: rgb(41, 186, 156);
        color:#fff;
    }
    .right-img {
        /* min-width: 350px; */
        margin-left: 150px;
        /* width:80%; */
        min-height:475px;
        /* border-left:1px solid #c8cede */
    }
    .handel {
        margin-top:10px;

    }
    .img-hint {
        position: absolute;
        width: 200px;
        top: 10px;
        right: 21px;
        margin-bottom:15px;
        height:40px;
        line-height:40px;
        text-align:right;
        z-index: 9999;
    }
    .img-hint>div {
        float:right
    }
    /* 隐藏上传组件的默认样式 */
    .img-hint input{
        display:none;
    }
    .img-hint .el-upload-list  {
        display:none
    }
    .img-source {
        position: relative;
        margin: 10px 0 0 10px;
        width:140px;
        height:150px;
    }
    .img-source  img {
        width:100%;
        height:110px;
    }
    .img-source p {
        padding:0 5px;
        width:100%;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align:middle;
        overflow:hidden;
    }
    .img-source .sle-img {
        position:absolute;
        /* display:none; */
        top:5px;
        left:5px;
    }
    .img-source p {
        margin-top:15px;
        text-align:center;
    }
    .img-mark {
        position:absolute;
        display:none;
        width:100%;
        height:110px;
        top:0px;
        left:0px;
        background: rgba(41, 186, 156, 0.3);
        border:1px solid rgb(41, 186, 156);
    }
    .img-source:hover .img-mark {
        display:block
    }
    .vedio-source,.audio-source{
        position: relative;
        margin:10px 0 0px 23px;
        width:280px;
        height:102px;
        border:1px solid #c8cede;
    }
    .video-mark {
        position:absolute;
        padding:5px;
        display:none;
        width:100%;
        height:100%;
        top:0px;
        left:0px;
        background: rgba(41, 186, 156, 0.3);
        border:1px solid rgb(41, 186, 156);
    }
    .vedio-upload {
        width:150px;
        height:100%;
    }
    .vedio-upload video {
        width:150px;
        height:100%;
        vertical-align: top;
    }
    .vedio-source:hover .video-mark {
        display:block
    }
    .audio-source:hover .video-mark {
        display:block
    }
    .audio-source {

    }
    .defaultImg {
        margin-top: 20px;
        width: 150px;
        height: 150px;
        line-height:150px;
        border:1px solid #c8cede;
        text-align:center;
    }
    .getNet {
        text-align:center;
    }
    .getnetvideo, .getnetaudio {
          min-height:300px
    }
    #multimedia-material .el-tabs__content {
        overflow:initial;
    }
    .video-time {
    position:absolute;
    padding-right:8px;
    bottom:10px;
    left:10px;
    color:#fff;
    width:50px;
    height:20px;
    font-size:12px;
    line-height:20px;
    border-radius:5px;
    text-align:right;
    background:rgba(0,0,0,0.5)
    }
    .play-triangle {
        position:absolute;
        top:3px;
        left:6px;
        /* padding-left:5px; */
        height:0;
        width:0;
        overflow: hidden; /* 这里设置overflow, font-size, line-height */
        font-size: 0;     /*是因为, 虽然宽高度为0, 但在IE6下会具有默认的 */
        line-height: 0;  /* 字体大小和行高, 导致盒子呈现被撑开的长矩形 */
        border-color:transparent transparent transparent #fff;
        border-style:solid;
        border-width:7px;
    }
    .bor-left {
        /* border-left: 1px solid #c8cede; */
    }
    .bor-right {
        border-right: 1px solid #c8cede;
    }
    .text-bg  {
        background:#29BA9C;
        color:#fff;
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
        -webkit-box-shadow: inset 0 0 1px rgba(0,0,0,0);
        border-radius: 30px;
        background: #ccc;
    }
    .el-tabs__header {
        margin-bottom: 0;
    }

    .el-dialog__header {
        padding: 25px;
    }

    .el-dialog__body {
        position: relative;
        padding: 10px 20px;
    }
    .image-slot {
        margin-top: 20px;
    }
</style>
