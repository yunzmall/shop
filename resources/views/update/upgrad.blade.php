@extends('layouts.base')

@section('title', trans('系统升级'))

@section('css')
<style>
    .main-panel {
        background: #f0f2f4;
    }
    .main-panel > .content {
        padding: 0px 10px;
    }
    .bg-purple {
        background: #fff;
        margin: 10px 0;
        padding: 10px;
    }
    .bg-purple-light {
        background: #fff;
        margin: 10px 0;
        padding: 10px;
    }
    .top-title {
        display: flex;
        align-items: center;
    }
    .systen-new {
        line-height: 25px;
        margin-left: 13px;
    }
    .line {
        display: block;
        width: 2px;
        height: 18px;
        background: #29ba9c;
        margin-right: 10px;
    }
    .version-card {
        margin: 20px 40px;
        line-height: 33px;
    }
    .version-card .new-version {
        font-size: 15px;
        color: #65b687;
        font-weight: 600;
    }
    .version-card .el-button--primary {
        color: #FFF;
        background-color: #65b687;
        border-color: #29BA9C;
        margin-top: 20px;
    }
    .version-card .el-button--primary:focus {
        color: #FFF;
        background-color: #57c988;
        border-color: #57c988;
    }
    .version-card .el-button--primary:hover {
        color: #FFF;
        background-color: #57c988;
        border-color: #57c988;
    }
    .update-tip {
        margin: 0 13px;
    }
    .update-tip .el-checkbox{
        white-space: normal !important;
        display: flex !important;
        font-weight: bold;
    }
    .update-file {
        margin: 0 13px;
        line-height: 30px;
    }
    .title-tip {
        font-size: 16px;
        color: #e65652;
        font-weight: 500;
        margin-top: 20px;
    }
    .check-box {
        display: flex;
        flex-direction: column;
        height: 637px;
        overflow-y: auto;
    }
    .active-color {
        color: #e15652;
        font-weight: 400;
    }
    .origin-state {
        margin-left: 20px;
    }
    .service-time {
        margin-bottom: 10px;
    }
    .active-weith-color {
        color: #e15652;
        font-weight: 600;
    }
    .el-dialog {
        display: flex;
        flex-direction: column;
        margin:0 !important;
        position:absolute;
        top:50%;
        left:50%;
        transform:translate(-50%,-50%);
    }
    .el-dialog__body {
        padding: 0 20px !important;
        font-weight: 600;
    }
    .text-tip {
        margin: 10px 0;
    }
    .el-dialog .el-button--primary {
        color: #FFF;
        background-color: #65b687;
        border-color: #29BA9C;
    }
    .el-dialog .el-button--primary:focus {
        color: #FFF;
        background-color: #57c988;
        border-color: #57c988;
    }
    .el-dialog .el-button--primary:hover {
        color: #FFF;
        background-color: #57c988;
        border-color: #57c988;
    }
    .version-box {
        border-left: 1px solid #e9e9e9;
        margin: 0;
    }
    .version-radius {
        background: #409eff;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        float: left;
        position: relative;
        left: -5px;
    }
    .version-margin-left {
        margin-left: 30px;
    }
    .version-num {
        font-weight: 900;
        font-size: 22px;
        line-height: 24px;
        margin-bottom: 20px;
    }
    .version-margin-bottom {
        margin-bottom: 20px;
    }
    .version-time {
        background: #f9f9f9;
        border: 1px solid #e9e9e9;
        padding: 5px 10px;
        font-weight: 500;
    }
    .version-content {
        font-weight: 500;
        line-height: 28px;
    }
    .no-update-log {
        display: flex;
        justify-content: center;
        height: 790px;
        margin: 20px;
    }
    .update-log {
        padding:30px 0 10px 15px;
        color:#333;
        font-size:15px;
        line-height:24px;
        height: 550px;
        overflow-y: auto;
    }

    .vue-page {
        border-radius: 5px;
        width: calc(100% - 0.5%);
        float: right;
        /* margin-right: 35px;
        position: fixed;
        bottom: 0;
        right: 0; */
        padding: 15px 5% 15px 0;
        background: #fff;
        height: 60px;
        z-index: 999;
        margin-top: 0;
        box-shadow: 0 2px 9px rgba(51, 51, 51, 0.1);
    }

    .active-disabled {
        color: #c0c4cc !important;
        background-image: none !important;
        background-color: #eeeeee !important;
        border-color: #eeeeee !important;
    }

    .grid-content {
        height: 1160px;
    }

    .right-system {
        background: #fff;
        padding: 20px 20px 0 20px; 
        margin-top: 10px;
    }
</style>
@endsection

@section('content')

    <div id="upgrad"  v-loading="init_loading">
        <el-row class="box" :gutter="10">
        <el-col :span="12">
            <div class="grid-content bg-purple">
            <div class="top-title">
                <span class="line"></span>
                <span>系统更新</span>
            </div>
            <div class="version-card">
                <div>本地后端版本: [[search.local_backend_version !== '' ? search.local_backend_version : '未知']]</div>
                <div>本地前端 (H5) 版本: [[search.local_frontend_version !== '' ? search.local_frontend_version : '未知']]</div>
                <div class="new-version">云端后台最新版本: [[search.origin_backend_version !== '' ? search.origin_backend_version : '未知']]</div>
                <div class="new-version">云端前端最新版本: [[search.origin_frontend_version !== '' ? search.origin_frontend_version : '未知']]</div>
                <el-button :class="search.upgrade_status == 1 ? '' : 'active-disabled'" type="primary" size="medium" :loading="init_loading" :disabled="search.upgrade_status == 1 ? false : true" @click="checkUpdateFile" v-if="is_update == ''">检查最新文件</el-button>
                <div v-if="is_update == 1">
                    <el-button
                        :class="agreeCheckList.length == 2 ? '' : 'active-disabled'"
                        type="success"
                        icon="el-icon-download"
                        v-if="is_update_type === 1"
                        :disabled="agreeCheckList.length == 2 ? false : true"
                        @click="upgrade()"
                        :loading="is_show_progress === 1"
                        >立即更新</el-button
                    >
                    <el-button
                        :class="agreeCheckList.length == 2 ? '' : 'active-disabled'"
                        type="success"
                        icon="el-icon-download"
                        v-if="is_update_type === 2"
                        :disabled="agreeCheckList.length == 2 ? false : true"
                        @click="upgradeFront()"
                        :loading="is_show_progress === 1"
                        >立即更新</el-button
                    >
                </div>
            </div>
            <div v-if="is_update == 1">
                <!-- <div v-if="is_update == 99" style="color:green;fonr-weight:800;line-height:50px;">
                恭喜您,当前版本为最新版!
                </div> -->
                <div v-if="is_update == -1" style="fonr-weight:800;line-height:50px;">
                暂未授权!
                </div>
                <div
                v-if="is_update == 3"
                style="color:green;fonr-weight:800;line-height:50px;"
                >
                检查更新中...
                </div>
                <div
                v-if="is_update === -2"
                style="color:red;fonr-weight:800;line-height:50px;"
                >
                网络请求超时!
                </div>
                <div
                v-if="is_update == 98"
                style="color:red;fonr-weight:800;line-height:50px;"
                >
                [[ error_msg ]]
                </div>
                <div class="update-tip" v-if="is_update == 1 && (is_update_type == 1 || is_update_type == 2)">
                    <el-checkbox-group v-model="agreeCheckList">
                        <el-checkbox label="file">我已知晓,点击立即更新将会请求云端文件覆盖本地文件,并执行数据库迁移文件以更新数据库表、字段等信息;</el-checkbox>
                        <el-checkbox label="update">我已知晓,在线更新不能保证100%成功,可能会因更新失败导致程序运行异常,异常技术处理需要时间;如果我的剩余售后服务时长为0,官方将不会处理我的任何问题,包括更新导致的程序异常等.</el-checkbox>
                    </el-checkbox-group>
                </div>
                <div class="update-file">
                    <div style="display: flex;align-items: center;">
                        <div class="title-tip" v-if="is_update == 1 && is_update_type == 1">更新之前请注意数据备份</div>
                        <div style="display:inline-block;padding-left:10px;color:#999;margin-top: 20px;" v-if="is_show_progress === 1">
                        <div v-if="is_show_progress_type === 1">
                            后台文件已更新
                            <span style="color:red;font-weight:900">
                            [[ updated_file ]]
                            </span>
                            个文件 / 共
                            <span style="color:red;font-weight:900">
                            [[ total_file ]] </span
                            >个文件！
                        </div>
                        <div v-if="is_show_progress_type === 2">
                            前端文件正在下载更新
                        </div>
                        <div v-if="is_show_progress_type === 3">
                            更新完成
                        </div>
                        <div v-if="is_show_progress_type === 4">
                            网络请求超时
                        </div>
                        </div>
                    </div>
                <div v-if="is_update == 1 && is_update_type == 1">更新文件(选中则不更新)</div>
                <div class="check-box"><el-checkbox :true-label="1" :false-label="0" :label="item.path" v-for="(item,index) in files" :key="index" v-model.number="item.is_choose" v-if="is_update == 1 && is_update_type == 1"></el-checkbox></div>
                </div>

                <!-- <div v-if="is_update == 1 && is_update_type == 2">
                当前版本号：<span
                    style="color:red;fonr-weight:900;line-height:50px;"
                    >[[ version ]]</span
                >
                </div> -->
            </div>
            </div>
        </el-col>
        <el-col :span="12">
            <div class="grid-content">
            <div class="right-system">
                <div class="top-title">
                    <span class="line"></span>
                    <span>系统信息</span>
                </div>
                <div class="systen-new">
                <br>
                <div>当前域名: [[search.current_domain !== '' ? search.current_domain : '未知']]  <span class="active-color origin-state" v-if="search.current_domain">[[search.domain_status_txt]]</span></div>
                    <div>客户端授权域名: [[search.origin_domain !== '' ? search.origin_domain : '未知']]</div>
                    <br>
                    <div>已安装插件数量: [[search.local_plugins_count !== '' ? `${search.local_plugins_count}个` : '未知']]</div>
                    <div>已授权插件数量: [[search.origin_plugins_count !== '' ? `${search.origin_plugins_count}个` : '未知']]</div>
                    <div  class="active-color" v-if="search.no_auth_plugins > 0">未授权安装插件数量: [[search.no_auth_plugins !== '' ? `${search.no_auth_plugins}个` : '未知']]</div>
                    <div class="active-color" v-else>&nbsp;</div>
                    <br v-if="search.upgrade_status === 0">
                    <div  class="active-color" v-if="search.upgrade_status === 0">涉嫌使用盗版插件，为了保证您的权益，请及时联系客服处理!</div>
                    <div  class="active-color" v-if="search.upgrade_status === 0">我国《中华人民共和国刑法》第二百一十七条规定: 以营利为目的,有下列侵犯著作权情形之一，违法所得数额较大或者有其他严重情节的，处三年以下有期徒刑或者拘役，并处或者单处罚金；违法所得数额巨大或者有其他特别严重情节的，处三年以上七年以下有期徒刑，并处罚金：
                    (一)未经著作权人许可，复制发行其文字作品、音乐、电影、电视、录像作品、计算软件及其他作品的; (二)出版他人享有专有出版权的图书的；(三)
                    未经录音录像制作者许可,复制发行其制作的录音录像的; (四)制作、出售假冒他人著名的美术作品的.</div>
                    <br v-if="search.upgrade_status === 0">
                    <div class="service-time">剩余售后服务时长: <span class="active-color">[[search.service_time !== '' ? `${search.service_time}天` : '未知']]</span></div>
                    <div class="active-weith-color" style="height: 50px">
                    <div v-if="search.service_time === 0" >
                        温馨提示: 当前售后服务时长为0,我们不会限制您更新系统,系统更新为所有客户统一推送,不管您是否付费,都不存在差别对待;但是因您售后服务
                        时长为0,我们将不会处理您的任何问题,包括因为更新出错导致系统异常等,如果您不具备技术维护能力,建议您续费后再进行系统更新!详情请联系客服!!!
                    </div>
                    </div>
                </div>
            </div>
            <div  class="right-system">
                <br>
                <div class="top-title">
                    <span class="line"></span>
                    <span>更新日志</span>
                </div>
                <div class="no-update-log"  v-show="log.length == 0">未知</div>
                <div class="update-log" v-show="log.length > 0"  :style="{height: search.upgrade_status === 0 ? '613px' :  '787px'}">
                    <div class="version-box" v-for="(item, index) in log" :key="index">
                        <div class="version-radius"></div>
                        <div class="version-margin-left">
                            <div class="version-num">[[item.title]]</div>
                            <div class="version-margin-bottom">
                                <span class="version-time">[[item.created_at]]</span>
                            </div>
                            <div style="padding-bottom:30px">
                                <div v-for="(item1, index1) in item.content" :key="index1" v-html="item1"></div>
                            </div>
                        </div>
                    </div>
                    <div class="vue-page">
                        <el-row>
                            <el-col align="center">
                                <el-pagination layout="prev, pager, next,jumper" @current-change="search1" :total="total" :page-size="per_page" :current-page="current_page" background></el-pagination>
                            </el-col>
                        </el-row>
                    </div>
                </div>
            </div>
            </div>
        </el-col>
        </el-row>
        <el-dialog
            title="温馨提示"
            center
            :visible.sync="dialogVisible"
            :show-close="false"
            :close-on-click-modal="false"
            :close-on-press-escape="false"
            width="25%">
            <div>进入系统更新页面,您的程序将会请求我们的云端系统,检验您当前程序的版本和信息,用于检测是否存在新的更新文件.</div>
            <div class="text-tip">该请求不会修改您本地的任何文件和数据!</div>
            <span slot="footer" class="dialog-footer">
                <el-button  type="info" @click="cancel">取消</el-button>
                <el-button type="primary" @click="agreeCheck(1)" :loading="agreeLoading">我已知晓并同意!</el-button>
            </span>
        </el-dialog>
    </div>
    <script>
        let upgrad = new Vue({
            el: "#upgrad",
            delimiters: ['[[', ']]'],
            name: 'upgrad',
            data() {
                return {
                    file:'',
                    update:'',
                    dialogVisible: true,
                    search:{
                        current_domain: "",
                        domain_status_txt: "",
                        local_backend_version: "",
                        local_frontend_version: "",
                        local_plugins_count: "",
                        no_auth_plugins: "",
                        origin_backend_version: "",
                        origin_domain: "",
                        origin_frontend_version: "",
                        origin_plugins_count: "",
                        service_time: "",
                        upgrade_status: "",
                        hasUpgradeFile:0, //判断需不需要弹出层
                    },
                    twoSearch:{},
                    log:[], //日志列表
                    agreeCheckList:[],
                    files:[], //更新文件
                    not_files: [],
                    agreeLoading:false,
                    logLoading:false,
                    init_loading: false,
                    filecount: "", //后台文件数
                    frontendUpgrad: "", //前端文件数
                    is_update: '',
                    is_update_type: 1, //显示哪个升级按钮
                    error_msg:'',//错误提示
                    is_show_progress: 0,
                    is_show_progress_type: 0,
                    total_file: 0,
                    updated_file: 0,
                    current_page:1,
                    total:1,
                    per_page:1,
                }
            },
            // mounted() {
            //     this.agreeCheck('')
            // },
            watch:{
                logLoading(newVal,oldVal){
                    if(newVal){
                        document.getElementsByClassName('update-log')[0].scrollTop = 0;
                    }
                }
            },
            methods:{
                cancel(){
                    window.history.go(-1);
                },
                search1(page){
                    this.getDataLog(page);
                },
                agreeCheck(val){
                    this.init_loading = true;
                    this.agreeLoading = true;
                    // if(val){
                    //     this.search = this.twoSearch;
                    //     this.init_loading = false; 
                    //     this.dialogVisible = false;
                    //     this.getDataLog(1);
                    //     return
                    // } 
                    this.$http.post('{!! yzWebUrl('update.system-info') !!}').then(response => {
                        return response.json();
                    }).then(({data,msg,result}) => {
                        if(result){
                            if(data.hasUpgradeFile == undefined){
                                data.hasUpgradeFile = 0;
                            }
                            // if(val || data.hasUpgradeFile == 1){
                                this.search = data;
                                this.getDataLog(1);
                                this.dialogVisible = false;
                                this.agreeLoading = false; 
                            // }
                            // if(data.hasUpgradeFile === 0 && val == ''){
                            //     this.dialogVisible = true;
                            //     this.twoSearch = data;
                            // }
                            this.init_loading = false;
                        }else{
                            this.$message.success(msg);
                            this.agreeLoading = false;
                            this.init_loading = false;
                        }
                    }).catch(err => {
                        console.log(err);
                    })
                },
                // 获取更新日志
                getDataLog(page){
                    this.logLoading = false;
                    this.$http.post('{!! yzWebUrl('update.log') !!}',{page}).then(response => {
                        return response.json();
                    }).then(({data,msg,result}) => {
                        if(result){
                            this.log = data.data;
                            this.total = data.total;
                            this.per_page = data.per_page;
                            this.current_page = data.current_page;
                            this.logFormat();
                            this.logLoading = true;
                        }else{
                            this.$message.success(msg);
                            this.logLoading = true;
                        }
                    }).catch(err => {
                        console.log(err);
                    })
                },
                // 处理日志格式
                logFormat(){
                    if (this.log && this.log.length >= 0) {
                    let content = [];
                    for (let i = 0; i < this.log.length; i++) {
                        this.log[i].created_at = this.log[i].created_at.split(" ")[0];
                        this.log[i].content = this.log[i].content.split("\n");
                        }
                    }
                },
                // 检查更新文件
                checkUpdateFile(){
                    this.is_update = 3
                    this.init_loading = true;
                    this.$http.get('{!! yzWebUrl('update.verifyheck') !!}', {}).then(res => {
                        return res.json();
                    })
                        .then(response => {
                        if (response.result == 1) {
                            if (response.filecount > 0) {
                                this.files = response.files;
                                // this.version = response.version; //后台版本号
                                // if (response.list && response.list.length > 0) {
                                //   this.new_version =
                                //     response.list[response.frontendUpgrad - 1].version; //前端版本号
                                // }
                                this.filecount = response.filecount; //后台文件数
                                this.frontendUpgrad = response.frontendUpgrad; //前端文件数
                                // this.log = response.log;
                                // // 升级说明数据处理
                                // if (this.log && this.log.length >= 0) {
                                //   let content = [];
                                //   for (let i = 0; i < this.log.length; i++) {
                                //     this.log[i].created_at = this.log[i].created_at.split(" ")[0];
                                //     // console.log(this.log[i].content.split("\n"))
                                //     this.log[i].content = this.log[i].content.split("\n");
                                //   }
                                //   // this.content_list.push(content)
                                // }
                                this.is_update = 1;
                                this.is_update_type = 1;
                            }
                            //后台没有更新文件
                            else if (response.filecount <= 0) {
                                // 前后端都没有更新文件
                                if (response.frontendUpgrad == 0) {
                                    this.is_update = 99;
                                }
                                // 后台没有更新文件,前端有更新文件
                                else {
                                    this.filecount = response.filecount; //后台文件数
                                    this.frontendUpgrad = response.frontendUpgrad; //前端文件数
                                    this.new_version =
                                    response.list[response.frontendUpgrad - 1].version; //前端版本号
                                    this.version = response.version; //后台版本号
                                    this.is_update = 1;
                                    this.is_update_type = 2;
                                }
                            }
                                this.init_loading = false;
                            } else if (response.result == -1) {
                                this.$message.error("暂未授权!");
                                this.is_update = -1;
                                this.init_loading = false;
                            } else if (response.result == -2) {
                                this.$message.error("网络请求超时!");
                                this.is_update = -2;
                                this.init_loading = false;
                            } else if (response.result == 99) {
                                this.$message.success("当前版本为最新版!");
                                this.is_update = 99;
                                this.init_loading = false;
                            } else if (response.result == 98) {
                                this.$message.error(response.msg);
                                this.error_msg = response.msg;
                                this.is_update = 98;
                                this.init_loading = false;
                            } else if (response.result === 0) {
                                // this.$router.push({ path: "/login" });
                            }
                        })
                        .catch(() => {
                            this.init_loading = false;
                        });
                },
                upgrade() {
                    var that = this;
                    that.is_show_progress = 1;
                    for (let i = 0; i < that.files.length; i++) {
                        if (that.files[i].is_choose === 1) {
                        that.not_files.push(that.files[i].path);
                        }
                    }
                    this.$http.post('{!! yzWebUrl('update.fileDownload') !!}', { 
                        protocol:{
                            file:1,
                            update:1
                        },
                        nofiles: that.not_files 
                        }).then(res => {
                            return res.json();
                        })
                        .then(response => {
                        if (response.result == 1) {
                            that.is_show_progress_type = 1;
                            that.updated_file = response.success;
                            that.total_file = response.total;
                            that.upgrade();
                        }
                        // 更新前端
                        else if (response.result == 2) {
                            if (that.frontendUpgrad > 0) {
                            that.upgradeFront();
                            } else {
                            that.is_show_progress_type = 3;
                            that.is_show_progress = 0;
                            location.reload();
                            }
                        }
                        //不更新的文件
                        else if (response.result == 3) {
                            that.upgrade();
                        }
                        })
                        .catch(() => {
                        // this.loading = false;
                        });
                    console.log(that.not_files);
                },
                upgradeFront() {
                    var that = this;
                    that.is_show_progress = 1;
                    that.is_show_progress_type = 2;
                    this.$http.post('{!! yzWebUrl('update.startDownload') !!}', {}).then(res => {
                            return res.json();
                        })
                        .then(response => {
                        if (response.status == 1) {
                            that.is_show_progress_type = 3;
                            that.is_show_progress = 0;
                            location.reload();
                        } else {
                            that.is_show_progress_type = 4;
                            that.is_show_progress = 0;
                        }
                        })
                        .catch(() => {
                        // this.loading = false;
                        });
                },
            }
        })
    </script>
@endsection

