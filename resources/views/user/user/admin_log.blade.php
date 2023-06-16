@extends('layouts.base')
@section('title', '登录日志')
@section('content')
    <style>
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
            padding: 10px!important;
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
        }
        .con .setting .block .title{
            font-size:18px;
            margin-bottom:15px;
            display:flex;
            align-items:center;
            justify-content:space-between;
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
        .vue-crumbs a {
            color: #333;
        }
        .vue-crumbs a:hover {
            color: #29ba9c;
        }
        .vue-crumbs {
            margin: 0 20px;
            font-size: 14px;
            color: #333;
            font-weight: 400;
            padding-bottom: 10px;
            line-height: 32px;
        }
        .el-table--border::after, .el-table--group::after, .el-table::before{
            background-color:#fff;
        }
    </style>
    <div id='admin_log' >
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <div class="block">
                    <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>登录日志</b><span></div></div>
                    <template>
                        <el-select v-model="search_form.remark" placeholder="请选择">
                            <el-option label="账号类型" value=""> </el-option>
                            <el-option
                                    v-for="item in adminType"
                                    :key="item.key"
                                    :label="item.label"
                                    :value="item">
                            </el-option>
                        </el-select>
                    </template>
                    <el-input  v-model="search_form.username" style="width:15%;margin-left:16px;margin-right:16px;" placeholder="登录账号"></el-input>

                    <el-input  v-model="search_form.member_info" style="width:15%;margin-left:16px;margin-right:16px;" placeholder="会员ID/昵称/姓名/手机号"></el-input>

                    <el-select v-model="search_form.is_search_time" clearable placeholder="是否搜索时间" style="width:150px">
                        <el-option label="时间不限" value="0"></el-option>
                        <el-option label="搜索时间" value="1"></el-option>
                    </el-select>

                    <!-- value-format="timestamp" -->
                    <el-date-picker
                            v-show="showSelectTimeRange"
                            v-model="times"
                            type="datetimerange"
                            value-format="timestamp"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期"
                            style="margin-left:5px;"
                            align="right">
                    </el-date-picker>

                    <el-button type="primary" @click="search">搜索</el-button>

                    <div style="padding-top: 8px">
                        <el-date-picker v-model="Del_time.del"  value-format="timestamp" type="datetimerange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"></el-date-picker>
                        <el-button type="primary" @click="Del" style="margin-right:16px;">删除</el-button>
                    </div>
                </div>


                <div style="background: #eff3f6;width:100%;height:15px;"></div>
                <div class="block">
                    <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>登录列表</b></div></div>
                </div>
                <template style="margin-top:-10px;">
                    <el-table :data="tableData" v-loading="loading" element-loading-text="加载中">
                        <el-table-column prop="created_at" align="center" label="登录时间" ></el-table-column>

                        <el-table-column prop="remark" label="账号类型" align="center">
                            <template slot-scope="scope">
                                <div>[[scope.row.remark]]</div>
                            </template>
                        </el-table-column>

                        <el-table-column prop="username" align="center" label="登录账号" width="300">
                            <template slot-scope="scope" >
                                <div>
                                    [[scope.row.username]]
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column label="会员" align="center" prop="display_order">
                            <template slot-scope="scope">
                                <div v-if="scope.row.has_one_member&&scope.row.has_one_member!=null">
                                    <div>
                                        <img :src="scope.row.has_one_member.avatar" alt="" style="width:40px;height:40px;border-radius:50%">
                                    </div>
                                    <div @click="gotoMember(scope.row.has_one_member.uid)" style="line-height:32px;color:#29BA9C;cursor: pointer;" class="vue-ellipsis">[[scope.row.has_one_member.nickname]]</div>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column align="center" label="IP地址" prop="ip">
                            <template slot-scope="scope" >
                                <div>
                                    [[scope.row.ip]]
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </template>

                <el-row style="background-color:#fff;">
                    <el-col :span="24" align="center" migra style="padding:15px 5% 15px 0">
                        <el-pagination background  @current-change="currentChange"
                                       :current-page="current_page"
                                       layout="prev, pager, next"
                                       :page-size="Number(page_size)" :current-page="current_page" :total="page_total"></el-pagination>
                    </el-col>
                </el-row>
            </div>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: "#admin_log",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    loading:false,
                    search_loading:false,
                    all_loading:false,
                    page_total:0,
                    page_size:0,
                    current_page:0,
                    search_form:{
                        remark:'',
                        username:'',
                        member_info:'',
                        // is_search_time:0
                    },
                    real_search_form:{},
                    adminType:[],
                    showSelectTimeRange:false,
                    times:[],
                    tableData: [],
                    Del_time:{},

                }
            },
            mounted () {
                this.getData();
            },
            methods: {
                search() {
                    this.search_loading = true;
                    let json={
                        search:this.search_form,
                    }
                    if(this.times&&this.times!=null&&this.times.length) {
                        json.search.times = {};
                        json.search.times.start = this.times[0] / 1000
                        json.search.times.end = this.times[1] / 1000
                    }
                    this.$http.post('{!! yzWebFullUrl('user.admin-log.index') !!}',json
                    ).then(function (response) {
                        console.log(response);
                            if (response.data.result){
                                let datas = response.data.data.list;
                                this.adminType=response.data.data.adminType
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
                Del() {
                    this.Del_time.start = Math.round(this.Del_time.del[0]/1000).valueOf();
                    this.Del_time.end= Math.round(this.Del_time.del[1]/1000).valueOf();
                    this.$http.post('{!! yzWebFullUrl('user.admin-log.del') !!}',{start:this.Del_time.start,end:this.Del_time.end}
                    ).then(function (response) {
                            if (response.data.result){
                                this.$message({message: response.data.msg,type: 'success'});
                            }
                            else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                        },function (response) {
                            this.search_loading = false;
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    );
                },
                currentChange(val) {
                    this.loading = true;
                    this.$http.post('{!! yzWebFullUrl('user.admin-log.index') !!}',{page:val,search:this.real_search_form}).then(function (response){
                            if (response.data.result){
                                let datas = response.data.data.list;
                                this.adminType=response.data.data.adminType
                                this.tableData=datas.data
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
                getData(){
                    this.loading = true
                    this.$http.post('{!! yzWebFullUrl('user.admin-log.index') !!}').then(function (response){
                        if (response.data.result) {
                            let datas = response.data.data.list;
                            this.adminType=response.data.data.adminType
                            this.tableData=datas.data
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
                gotoMember(id) {
                    window.location.href = `{!! yzWebFullUrl('member.member.detail') !!}`+`&id=`+id;
                },
            },
            watch:{
                "search_form.is_search_time"(newVal){
                    if(newVal==1){
                        this.showSelectTimeRange=true;
                    }else{
                        this.showSelectTimeRange=false;
                        console.log(this.search_form);
                    }
                }
            }
        });
    </script>
@endsection