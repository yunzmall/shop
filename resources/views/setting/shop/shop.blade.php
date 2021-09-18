@extends('layouts.base')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .main-panel{
            margin-top:50px;
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
    <div id='re_content' >
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <el-form ref="form" :model="form" label-width="15%">
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>基础设置
                            </b></div>
                        <el-form-item label="关闭站点">
                            <template>
                                <el-switch
                                        v-model="form.close"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="商城名称"  >
                            <el-input v-model="form.name" placeholder="请输入商城名称" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="商城LOGO" prop="head_img_url">
                            <div class="upload-box" @click="openUpload('logo',1,'one')" v-if="!form.logo_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('logo',1,'one')" class="upload-boxed" v-if="form.logo_url">
                                <img :src="form.logo_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('logo')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">正方型图片</div>
                        </el-form-item>
                        <!-- <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img> -->
                        <upload-multimedia-img :upload-show="uploadShow" :type="type" :name="chooseImgName" :sel-Num="selNum" @replace="changeProp" @sure="sureImg"></upload-multimedia-img>
                        <el-form-item label="商城海报" prop="head_img_url">
                            <div class="upload-box" @click="openUpload('signimg',1,'one')" v-if="!form.signimg_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('signimg',1,'one')" class="upload-boxed" v-if="form.signimg_url">
                                <img :src="form.signimg_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('signimg')" title="点击清除图片"></i>
                            </div>
                            <div class="tip">推广海报，建议尺寸640*640</div>
                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>版权设置</b><span style="margin-left:15px;color:#999;font-size:12px;">显示在平台会员中心底部</span></div>

                        <el-form-item label="版权信息"  >
                            <el-input v-model="form.copyright" placeholder="请输入版权信息" style="width:70%;"></el-input>
                            <div style="font-size:12px;">版权所有 © 后面文字字样</div>
                        </el-form-item>
                        @if(YunShop::app()->role == 'founder')  {{--判断是不是超级管理员--}}
                        <el-form-item label="版权图片"  >
                            <div class="upload-box" @click="openUpload('copyrightImg',1,'one')" v-if="!form.copyrightImg_url">
                                <i class="el-icon-plus" style="font-size:32px"></i>
                            </div>
                            <div @click="openUpload('copyrightImg',1,'one')" class="upload-boxed" v-if="form.copyrightImg_url">
                                <img :src="form.copyrightImg_url" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                <div class="upload-boxed-text">点击重新上传</div>
                                <i class="el-icon-close" @click.stop="clearImg('copyrightImg')" title="点击清除图片"></i>
                            </div>
                        </el-form-item>
                            @endif
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>商城业绩</b><span style="margin-left:15px;color:#999;font-size:12px;">商城自营部分营业额在推广中心显示，指定会员等级可见！</span></div>
                        <el-form-item label="商城业绩显示" >
                            <template>
                                <el-switch
                                        v-model="form.achievement"

                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                        </el-form-item>
                        <el-form-item label="会员等级查看权限" >
                            <template>
                                <el-checkbox-group v-model="form.member_level" text-color="#29ba9c" @change="getInfo">
                                    <el-checkbox label="-1">全部会员等级</el-checkbox>
                                    <el-checkbox :label="String(item.id)" v-for="(item,index,key) in level">[[item.level_name]]</el-checkbox>
                                </el-checkbox-group>
                            </template>
                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>其他设置</b></div>

                        <el-form-item label="客服链接"  >
                            <el-input v-model="form.cservice" placeholder="请输入客服链接" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">
                                支持任何客服系统的聊天链接,例如芸客服,QQ,企点,53客服,百度商桥等；建议使用系统自带的芸客服插件！
                            </div>
                        </el-form-item>
                        <el-form-item label=""  >
                            <el-input v-model="form.cservice_mini" placeholder="请输入小程序客服路径，仅支持芸客服插件!" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">
                                只支持芸客服插件小程序客服聊天路径，如留空则使用小程序官方客服功能！
                            </div>
                        </el-form-item>
                        <el-form-item label="百度统计">
                            <el-input v-model="form.baidu" placeholder="请输入百度统计站点ID" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">请填入百度统计的站点ID(百度统计只能统计域名下的流量统计,链接#号后面的百度统计可能会被默认截取掉)</div>
                        </el-form-item>
                        <el-form-item label="余额字样">
                            <el-input v-model="form.credit" placeholder="请输入余额字样" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">前端余额字样的自定义功能</div>
                        </el-form-item>
                        <el-form-item label="积分字样">
                            <el-input v-model="form.credit1" placeholder="请输入积分字样" style="width:70%;"></el-input>
                            <div style="font-size:12px;color:#ccc;">前端积分字样的自定义功能</div>
                        </el-form-item>
                    </div>


                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>平台协议</b></div>

                        <el-form-item label="是否开启" prop="is_agreement">
                            <el-switch v-model="form.is_agreement" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>

                        <el-form-item label="平台协议自定义名称">
                            <el-input v-model="form.agreement_name" placeholder="请输入平台协议自定义名称" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="平台协议" prop="agreement">
                            <tinymceee v-model="form.agreement" style="width:70%" v-if="displayTinymceEditor"></tinymceee>
                        </el-form-item>

                    </div>
                    <div class="confirm-btn">
                        <el-button type="primary" @click="submit">提交</el-button>
                    </div>
            </div>
            </el-form>
        </div>
    </div>
    <script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script>
    <!-- @include('public.admin.uploadImg') -->
    @include('public.admin.tinymceee')
    @include('public.admin.uploadMultimediaImg')

    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    activeName: 'one',
                    level:[],
                    uploadShow:false,
                    chooseImgName:'',
                    uploadListShow:false,
                    chooseImgListName:'',
                    form:{
                        close:'0',
                        https:'0',
                        name:'',
                        logo:'',
                        signimg:'',
                        copyright:'',
                        copyrightImg:'',
                        achievement:'0',
                        member_level:['-1'],
                        cservice:'',
                        baidu:'',
                        credit:'',
                        credit1:'',
                        agreement:'',
                        is_agreement:'0',
                        agreement_name:''
                    },
                    type:'',
                    selNum:'',
                    displayTinymceEditor:false
                }
            },
            mounted () {
                this.getData();
            },
            methods: {
                openUpload(str,type,sel) {
                    this.chooseImgName = str;
                    this.uploadShow = true;
                    this.type = type;
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

                getInfo(){
                    this.$forceUpdate()
                },
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.shop.shop-set-info') !!}').then(function (response){
                        if (response.data.result) {
                            console.log(response.data.data.shop)
                            if(response.data.data.shop){
                                for(let i in response.data.data.shop){
                                    this.form[i]=response.data.data.shop[i]
                                }
                            }
                            this.level=response.data.data.level
                            this.agreement = response.data.data.shop.agreement
                            if(!this.form.hasOwnProperty('member_level')){
                                this.form.member_level=['-1']
                            }
                            this.displayTinymceEditor=true;
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
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
                submit() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('setting.shop.shop-set-info') !!}',{'shop':this.form}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                        location.reload();
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                        loading.close();
                    })
                },
            },
        });
    </script>
@endsection
