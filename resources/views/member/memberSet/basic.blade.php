    @extends('layouts.base')
    @section('title', '基础设置')
    @section('content')
    <link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style scoped>
        .main-panel{
            margin-top:50px;
        }
        .main-panel #re_content {
            padding: 10px;
        }
        .panel{
            margin-bottom:10px!important;
            border-radius: 10px;
            padding-left: 20px;
        }
        .panel .active a {
            background-color: #29ba9c!important;
            border-radius: 18px!important;
            color:#fff;
        }
        .panel a{
            border:none!important;
            background-color:#fff!important;
        }
        .content{
            background: #eff3f6;
            padding:10px!important;
        }
        .con{
            padding-bottom:40px;
            position:relative;
            border-radius: 8px;
        }
        .con .setting .block{
            padding:10px;
            background-color:#fff;
            border-radius: 8px;
        }
        .block{
            padding:10px;
            background-color:#fff;
            border-radius: 8px;
        }
        .con .setting .block .title{
            font-size:18px;
            margin-bottom:15px;
            display:flex;
            align-items:center;
        }

        .confirm-btn{
            width: calc(100% - 266px);
            position:fixed;
            bottom:0;
            right:0;
            margin-right:10px;
            line-height:63px;
            background-color: #ffffff;
            box-shadow: 0px 8px 23px 1px
            rgba(51, 51, 51, 0.3);
            background-color:#fff;
            text-align:center;
        }
        b{
            font-size:14px;
        }
        .el-checkbox__inner{
            border:solid 1px #56be69!important;
        }
        .upload-boxed .el-icon-close {
            position: absolute;
            top: -5px;
            right: -5px;
            color: #fff;
            background: #333;
            border-radius: 50%;
            cursor: pointer;
        }
    </style>
    <div style="padding: 10px">
        @include('layouts.newTabs')
        <div>
            <div id="app" v-cloak>
                <div class="total-head block" style="margin: 0 0 20px 0;padding: 0">
                    <!-- 基础设置 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">基础设置</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="Banner">
                                <div class="upload-box" @click="openUpload('banner','1','one')" v-if="!form.banner_url" style="width:250px;height:150px;">
                                    <i class="el-icon-plus" style="font-size:32px"></i>
                                </div>
                                <div @click="openUpload('banner','1','one')" class="upload-boxed" v-if="form.banner_url" style="width:250px;height:150px;">
                                    <img :src="form.banner_url" alt="" style="width:250px;height:150px;border-radius: 5px;cursor: pointer;">
                                    <div class="upload-boxed-text">点击重新上传</div>
                                    <i class="el-icon-close" @click.stop="clearImg('banner')" title="点击清除图片"></i>
                                </div>
                                <div class="tip">长方形图片</div>
                            </el-form-item>
                            <el-form-item label="会员默认头像">
                                <div class="upload-box" @click="openUpload('headimg',1,'one')" v-if="!form.headimg_url">
                                    <i class="el-icon-plus" style="font-size:32px"></i>
                                </div>
                                <div @click="openUpload('headimg',1,'one')" class="upload-boxed" v-if="form.headimg_url" style="height:150px;">
                                    <img :src="form.headimg_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                    <div class="upload-boxed-text">点击重新上传</div>
                                    <i class="el-icon-close" @click.stop="clearImg('headimg')" title="点击清除图片"></i>
                                </div>
                                <div class="tip">会员默认头像（会员自定义头像>微信头像>商城默认头像）</div>
                            </el-form-item>
                            <upload-multimedia-img :upload-show="uploadShow" :type="type" :name="chooseImgName" :sel-Num="selNum"  @replace="changeProp" @sure="sureImg"></upload-multimedia-img>
                        </el-form>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <!-- 通知设置 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">通知设置</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <!-- 推广客户 -->
                            <el-form-item style="margin-left:100px" label="获得推广权限通知">
                                <el-select clearable style="width:500px;margin-right:10px" v-model="form.member_agent" filterable placeholder="请选择">
                                    <el-option v-for="item in temp_list" :key="item.id" :label="item.title" :value="item.id">
                                    </el-option>
                                </el-select>
                                <el-switch v-model="form.member_agent_default" :active-value="1" :inactive-value="0" @change="changeModal('member_agent')"></el-switch>
                            </el-form-item>
                            <!-- 新增客户 -->
                            <el-form-item style="margin-left:100px" label="新增客户通知">
                                <el-select clearable style="width:500px;margin-right:10px" v-model="form.member_new_lower" filterable placeholder="请选择">
                                    <el-option v-for="item in temp_list" :key="item.id" :label="item.title" :value="item.id">
                                    </el-option>
                                </el-select>
                                <el-switch v-model="form.member_new_lower_default" :active-value="1" :inactive-value="0" @change="changeModal('member_new_lower')"></el-switch>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
                <div class="confirm-btn">
                    <el-button type="primary" @click="submit">提交</el-button>
                </div>
            </div>

        </div>
    </div>
    @include('public.admin.uploadMultimediaImg')

    <script>
        var vm = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data() {
                let set = {!! json_encode(($set?:[])) !!};
                let temp_list = {!! json_encode(($temp_list?:[])) !!};
                return {
                    uploadShow:false,
                    chooseImgName:'',
                    uploadListShow:false,
                    chooseImgListName:'',
                    type:'',
                    selNum:'',
                    form:{
                        ...set
                    },
                    temp_list:temp_list,
                }
            },
            created() {
            },
            mounted () {
                console.log(this.form,111);
            },
            methods: {
                openUpload(str,type,sel) {
                    this.chooseImgName = str;
                    this.uploadShow = true;
                    this.type = type
                    this.selNum = sel
                },
                changeProp(val) {
                    if(val == true) {
                        this.uploadShow = false;
                    }
                    else {
                        this.uploadShow = true;
                    }
                },
                sureImg(name,uploadShow,fileList) {
                    if(fileList.length <= 0) {
                        return
                    }
                    console.log(name)
                    console.log(fileList)
                    this.form[name] =fileList[0].attachment;
                    this.form[name+'_url'] = fileList[0].url;
                    console.log(this.form[name],'aaaaa')
                    console.log( this.form[name+'_url'],'bbbbb')
                },
                clearImg(str,type,index) {
                    if(!type) {
                        this.form[str] = "";
                        this.form[str+'_url'] = "";
                    }
                    else {
                        this.form[str].splice(index,1);
                        this.form[str+'_url'].splice(index,1);
                    }
                    this.$forceUpdate();
                },
                // 消息模板开关
                changeModal(name) {
                    let url_open = "{!! yzWebUrl('setting.default-notice.index') !!}"
                    let url_close = "{!! yzWebUrl('setting.default-notice.cancel') !!}"
                    let url = "";
                    if(this.form[name+'_default']==1) {
                        url = url_open;
                    } else {
                        url = url_close;
                    }
                    let json = {
                        notice_name: name,
                        setting_name: "relation_base",
                    }
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(url,json).then(response => {
                        if (response.data.result == 1) {
                            this.$message({type: 'success',message: '操作成功!'});
                            if(this.form[name+'_default'] == 0) {
                                this.form[name+'_default'] = 1;
                                this.form[name] = response.data.id;
                            }  else {
                                this.form[name+'_default'] = 0;
                                this.form[name] = '';
                            }
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                            this.form[name+'_default'] = 0;
                        }
                        loading.close();
                    },response => {
                        loading.close();
                    });
                },
                submit() {
                    let that = this;
                    let url = '{!! yzWebFullUrl('member.member-set.basic-store') !!}';
                    let json = {
                        set : this.form
                    };
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(url,json).then(response => {
                        if (response.data.result) {
                            this.$message({type: 'success',message: response.data.msg});

                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                        // location.reload();
                    },response => {
                        loading.close();
                    });
                },
            }
        })
    </script>
    @endsection