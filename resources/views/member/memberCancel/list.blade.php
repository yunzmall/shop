@extends('layouts.base')
@section('title', '账号注册管理审核')
@section('content')
    <style>
        .panel-info {
            margin: 6px 0;
        }
        .add-shopnav {
            padding: 5px;
        }
        .add-shopnav li.active a{
            padding: 4px 12px;
            letter-spacing: 2px;
            font-size: 14px;
            border-radius: 5px!important;
        }
        .tips {
            color: red;
            margin-bottom:20px;
        }
        .panel{
            margin-bottom: 20px!important;
            border-radius: 10px;
            padding-left: 5px;
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
            padding: 15px!important;
        }
        .con{
            padding-bottom:20px;
            position:relative;
            border-radius: 8px;

            min-height: 100vh;
            background: #fff;
        }
        .con .setting .block{
            padding:10px;
            background-color:#fff;
            border-radius: 8px;
            margin-bottom:10px;
        }
        .con .setting .block .title{
            font-size:18px;
            margin-bottom:20px;
            display:flex;
            align-items:center;
            justify-content:space-between;
        }
        .confirm-btn{
            width: 100%;
            position:absolute;
            bottom:0;
            left:0;
            line-height:63px;
            background-color: #ffffff;
            box-shadow: 0px 8px 23px 1px
            rgba(51, 51, 51, 0.3);
            background-color:#fff;
            text-align:center;
        }
        .add{
            width: 154px;
            height: 36px;
            border-radius: 4px;
            border: solid 1px #29ba9c;
            color:#29ba9c;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .el-table--fit{
            margin-top:-10px;
        }
        b{
            font-size:14px;
        }
        .el-table--border::after, .el-table--group::after, .el-table::before{
            background-color:#fff;
        }
    </style>
    <div id='re_content' >
    <div class="panel panel-info">
            <ul class="add-shopnav">
                <li>
                    <a href="{{ yzWebFullUrl('member.member-cancel.index') }}">
                        账号注销设置
                    </a>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </li>
                <li class="active">
                    <a href="{{ yzWebFullUrl('member.member-cancel.verify') }}">
                        账号注销申请审核
                    </a>
                </li>
            </ul>
        </div>

        <div class="con">
            <div class="setting">
                <div class="block">
                    <div class="title">
                        <div style="display:flex;align-items:center;">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>账号注销申请审核</b>
                        </div>
                    </div>
                    <div class="tips">
                        <span>注：账号注销后，会员个人信息（包含昵称，姓名，手机号，收获信息，会员资料等），商城等级，个人资产（包含金额，积分等），优惠券，插件分红等将失效，且不能恢复，若会员存在交易中订单，暂不能进行审核，交易完成后可进行审核。</span>
                    </div>
                    <el-input v-model="search_form.member_id" style="width:15%;margin-right:16px;" placeholder="会员ID"></el-input>
                    <el-input v-model="search_form.member" style="width:15%;margin-right:16px;" placeholder="昵称/姓名/手机号"></el-input>
                    <template>
                        <el-select v-model="search_form.status" placeholder="状态" style="width:15%;margin-right:16px;">
                            <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.status"></el-option>
                        </el-select>
                    </template>
                    <template>
                        <span class="demonstration"></span>
                        <el-date-picker
                                v-model="search_form.create_time"
                                type="datetimerange"
                                range-separator="至"
                                start-placeholder="开始日期"
                                end-placeholder="结束日期"
                                align="right">
                        </el-date-picker>
                    </template>
                    <el-button type="primary" @click="search">搜索</el-button>
                </div>
                <div style="background: #eff3f6;width:100%;height:20px;"></div>
                <div class="block">
                    <div class="title">
                        <div style="display:flex;align-items:center;">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;">
                            </span><b>申请列表</b>
                        </div>
                    </div>
                </div>
                <el-table :data="tableData" style="padding:0 10px" style="width: 100%">
                    <el-table-column prop="id" align="center" label="ID"></el-table-column>
                    <el-table-column prop="created_at" align="center" label="申请时间"></el-table-column>
                    <el-table-column prop="member_id" align="center" label="会员id"></el-table-column>
                    <el-table-column align="center" label="会员">
                        <template slot-scope="scope">
                            <img style='width:40px;height:40px' v-if="scope.row.has_one_member" :src=scope.row.has_one_member.avatar_image style="max-width:100px">
                            <div>[[scope.row.has_one_member?scope.row.has_one_member.nickname:'未更新']]</div>
                        </template>
                    </el-table-column>
                    <el-table-column align="center" label="状态">
                        <template slot-scope="scope" >
                            <div v-if="scope.row.status==1" style="color: #FFBF00">待审核</div>
                            <div v-if="scope.row.status==2" style="color: #0ad76d">审核通过</div>
                            <div v-if="scope.row.status==3" style="color: #A4A4A4">审核驳回</div>
                            <div v-if="scope.row.status==4" style="color: #A4A4A4">申请取消</div>
                        </template>
                    </el-table-column>
                    <el-table-column align="center" label="操作">
                        <template slot-scope="scope" >
                            <a style="color:#999">
                                <i class="el-icon-circle-check" style="font-size:20px;margin-right:35px;" v-if="scope.row.status==1" @click="pass(scope.row.id)"></i>
                            </a>
                            <a style="color:#999">
                                <i class="el-icon-circle-close" style="font-size:20px;" v-if="scope.row.status==1" @click="reject(scope.row.id)"></i>
                            </a>
                        </template>
                    </el-table-column>
                </el-table>
                <el-row style="background-color:#fff;">
                    <el-col :span="24" align="center" migra style="padding:15px 5% 15px 0" v-loading="loading">
                        <el-pagination background  @current-change="currentChange"
                                       layout="prev, pager, next"
                                       :page-size="page_size" :current-page="current_page" :total="page_total">
                        </el-pagination>
                    </el-col>
                </el-row>
            </div>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    dataList:[],
                    loading:false,
                    search_loading:false,
                    all_loading:false,
                    page_total:0,
                    page_size:0,
                    current_page:0,
                    search_form:{
                        member_id:'{!! $_REQUEST['member_id'] ? :"" !!}'
                    },
                    real_search_form:{},
                    options: [
                        {
                            status: '1',
                            label: '待审核'
                        },
                        {
                            status: '2',
                            label: '审核通过'
                        },
                        {
                            status: '3',
                            label: '审核驳回'
                        },
                        {
                            status: '4',
                            label: '申请取消'
                        }
                    ],
                    tableData: []
                }
            },
            mounted () {
                this.getData();
            },
            methods: {
                search() {
                    this.search_loading = true;
                    if(this.search_form.create_time){
                        this.search_form.create_time[0] = Math.round(this.search_form.create_time[0]/1000).valueOf();
                        this.search_form.create_time[1] = Math.round(this.search_form.create_time[1]/1000).valueOf();
                    }
                    let json={
                        search:this.search_form,
                    }
                    this.$http.post('{!! yzWebFullUrl('member.member-cancel.search') !!}',json
                    ).then(function (response) {
                            if (response.data.result){
                                let datas = response.data.data.list;
                                this.dataList=datas.data
                                this.tableData=datas.data
                                this.page_total = datas.total;
                                this.page_size = datas.per_page;
                                this.current_page = datas.current_page;
                                this.loading = false;
                                this.real_search_form=Object.assign({},this.search_form);
                            }
                            else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                            this.search_loading = false;
                        },function (response) {
                            this.search_loading = false;
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    );
                },
                currentChange(val) {
                    this.loading = true;
                    this.$http.post('{!! yzWebFullUrl('member.member-cancel.search') !!}',{page:val,search:this.real_search_form}).then(function (response){
                            if (response.data.result){
                                let datas = response.data.data.list;
                                this.tableData = datas.data
                                this.page_total = datas.total;
                                this.page_size = datas.per_page;
                                this.current_page = datas.current_page;
                                this.loading = false;
                            } else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                        },function (response) {
                            console.log(response);
                            this.loading = false;
                        }
                    );
                },
                pass(id){
                    this.$confirm('是否通过审核?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let json={
                            id:id
                        };
                        this.$http.post('{!! yzWebFullUrl('member.member-cancel.pass') !!}',json).then(function (response){
                                if (response.data.result) {
                                    this.$message({message:"审核通过！",type:"success"});
                                    this.loading = false;
                                    location.reload();
                                }else {
                                    this.$message({message: response.data.msg,type: 'error'});
                                }
                            },function (response) {
                                this.loading = false;
                            }
                        );
                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '已取消审核'
                        });
                    });
                },
                reject(id){
                    this.$confirm('是否驳回审核?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let json={
                            id:id
                        };
                        this.$http.post('{!! yzWebFullUrl('member.member-cancel.reject') !!}',json).then(function (response){
                                if (response.data.result) {
                                    this.$message({message:"驳回成功！",type:"success"});
                                    this.loading = false;
                                    location.reload();
                                }else {
                                    this.$message({message: response.data.msg,type: 'error'});
                                }
                            },function (response) {
                                this.loading = false;
                            }
                        );
                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '已取消驳回'
                        });
                    });
                },
                getData(){
                    this.$http.post('{!! yzWebFullUrl('member.member-cancel.search') !!}').then(function (response){
                        if (response.data.result) {
                            let datas = response.data.data.list;
                            this.tableData = datas.data;
                            this.page_total = datas.total;
                            this.page_size = datas.per_page;
                            this.current_page = datas.current_page;
                            this.loading = false;
                        }else{
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
            },
        });
    </script>
@endsection