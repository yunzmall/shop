@extends('layouts.base')
@section('content')
@section('title', '操作日志')
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
        min-height:100vh;
        background-color:#fff;
    }
    .con .setting .block{
        padding:10px;
        background-color:#fff;
        border-radius: 8px;
        margin-bottom:10px;
    }
    .con .setting .block .title{
        font-size:18px;
        margin-bottom:32px;
        display:flex;
        align-items:center;
        justify-content:space-between;
    }
    b{
        font-size:14px;
    }
    .el-table--border::after, .el-table--group::after, .el-table::before{
        background-color:#fff;
    }
</style>
<div id='re_content' >
    @include('layouts.newTabs')
    <div class="con">
        <div class="setting">
            <el-form ref="form" :model="search_form" >
                <div class="block">
                    <div class="title"><div style="display:flex;align-items:center;"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>操作日志</b><span style="color: #999999;font-size:12px;display:inline-block;margin-left:16px;">操作日志只包含部分系统操作日志，并非系统所有操作都记录日志，请悉知！</span></div><span></div></div>
                    <el-form-item label="" style="position:relative;">
                        <el-input v-model="search_form.user_name" style="width:15%;margin-right:16px;" placeholder="操作员"></el-input>
                        <el-input v-model="search_form.mark" style="width:15%;margin-right:16px;" placeholder="模块id"></el-input>
                        <el-date-picker v-model="search_form.activity_time"  value-format="timestamp" type="datetimerange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"></el-date-picker>
                        <el-button type="primary" @click="search" style="margin-left:16px;">搜索</el-button>
                        <div style="position:absolute;right:0;top:0;">
                            <el-button type="primary" @click="Del" style="margin-right:16px;">删除</el-button>
                            <el-date-picker v-model="Del_time.del"  value-format="timestamp" type="datetimerange" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"></el-date-picker>
                        </div>
                    </el-form-item>
                </div>
                <div style="background: #eff3f6;width:100%;height:15px;"></div>
                <div class="block">
                    <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>操作日志列表</b><span style="color: #999999;font-size:12px;display:inline-block;margin-left:16px;"></div></div>

                    <template >
                        <el-table
                                v-loading="loading"
                                element-loading-text="加载中"
                                :data="tableData"

                                style="width: 100%">
                            <el-table-column
                                    prop="id"
                                    align="center"
                                    label="日志ID"
                                    width="100">
                            </el-table-column>
                            <el-table-column
                                    prop="user_name"
                                    align="center"
                                    label="操作人	"
                                    label="100">
                            </el-table-column>
                            <el-table-column
                                    prop="modules_name"
                                    align="center"
                                    label="模块名	"
                                    label="100">
                            </el-table-column>
                            <el-table-column
                                    prop="mark"
                                    align="center"
                                    label="模块ID	"
                                    label="100">
                            </el-table-column>
                            <el-table-column
                                    prop="type_name"
                                    align="center"
                                    label="类别"
                                    label="100">
                            </el-table-column>
                            <el-table-column
                                    prop="field_name"
                                    align="center"
                                    label="名称"
                                    label="100">
                            </el-table-column>
                            <el-table-column
                                    prop="old_content"
                                    align="center"
                                    label="修改前内容"
                                    label="100">
                            </el-table-column>
                            <el-table-column
                                    prop="new_content"
                                    align="center"
                                    label="修改后内容"
                                    label="100">
                            </el-table-column>
                            <el-table-column
                                    prop="ip"
                                    align="center"
                                    label="操作IP"
                                    label="100">
                            </el-table-column>
                            <el-table-column
                                    prop="created_at"
                                    align="center"
                                    label="操作时间"
                                    label="100">
                            </el-table-column>
                        </el-table>
                    </template>
                    <el-row style="background-color:#fff;">
                        <el-col :span="24" align="center" migra style="padding:15px 5% 15px 0">
                            <el-pagination background  @current-change="currentChange"
                                           :page-size="page_size"
                                           layout="prev, pager, next"
                                           :current-page="current_page" :total="page_total"></el-pagination>
                        </el-col>
                    </el-row>
                </div>
        </div>
        </el-form>
    </div>
</div>
<script>
    var vm = new Vue({
        el: "#re_content",
        delimiters: ['[[', ']]'],
        data() {
            let data = {!! $data ?: '[]' !!}
                return {
                    Del_time:{},
                    activeName: 'one',
                    loading:false,
                    search_loading:false,
                    all_loading:false,
                    page_total:0,
                    page_size:0,
                    current_page:0,
                    search_form:{

                    },
                    real_search_form:{},
                    loading:false,
                    search_loading:false,
                    search_form:{},
                    real_search_form:{},
                    value:'',
                    tableData: [],
                    data: data,
                }
        },
        created(){
            this.getData();
        },
        mounted () {
        },
        methods: {
            currentChange(val) {
                this.loading = true;
                this.$http.post('{!! yzWebFullUrl('setting.operation-log.index') !!}',{page:val,search:this.real_search_form}).then(function (response){
                        if (response.data.result){
                            let datas = response.data.data.list;
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
                this.$http.post('{!! yzWebFullUrl('setting.operation-log.index') !!}').then(function (response){
                    if (response.data.result) {
                        let datas = response.data.data.list;
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
            search() {
                this.search_loading = true;
                if(this.search_form.is_time != 0 && this.search_form.activity_time){
                    this.search_form.start = Math.round(this.search_form.activity_time[0]/1000).valueOf();
                    this.search_form.end= Math.round(this.search_form.activity_time[1]/1000).valueOf();
                }
                this.$http.post('{!! yzWebFullUrl('setting.operation-log.index') !!}',{search:this.search_form}
                ).then(function (response) {
                        if (response.data.result){
                            let data = response.data.data.list;
                            this.page_total = data.total;
                            this.tableData = data.data;
                            this.page_size = data.per_page;
                            this.current_page = data.current_page;
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
                this.$http.post('{!! yzWebFullUrl('setting.operation-log.del') !!}',{start:this.Del_time.start,end:this.Del_time.end}
                ).then(function (response) {
                        if (response.data.result){
                            this.$message({message: response.data.msg,type: 'error'});
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
        },
    });
</script>
@endsection