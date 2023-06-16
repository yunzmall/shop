@extends('layouts.base')
@section('content')
@section('title', trans('失败队列重试'))
<style>
    .panel{
        margin-bottom:10px!important;
        padding-left: 20px;
        border-radius: 10px;
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
        border-radius: 8px;
        min-height:100vh;
        background-color:#fff;
        position:relative;
    }
    .con .setting .block{
        padding:10px;
        background-color:#fff;
        border-radius: 8px;
        margin-bottom:10px;
    }
    .con .setting .block .title{
        font-size:18px;
        margin-bottom:15px;
        display:flex;
        align-items:center;
    }
    .el-form-item{
        padding-left:300px;
        margin-bottom:10px!important;
    }
    .el-form-item__label{
        margin-right:30px;
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
    b{
        font-size:14px;
    }
</style>
<div id='re_content' >
    @include('layouts.newTabs')
    <template>
        <div class="workOrder" >
            <div class="table_box" style="padding-left:10px;padding-top:10px;">
                <el-button size="mini" style=" float:right"  @click="catchInfo(0,1)">一键重试</el-button>

                <el-table :data="list"  style="width: 100%">
                    <el-table-column prop="queue_id" label="ID(ID号唯一，重试后仍存在请联系客服)" width="350">
                    </el-table-column>
                    <el-table-column prop="payload"  label="任务" width="450":show-overflow-tooltip="true" >
                    </el-table-column>
                    <el-table-column prop="exception" label="错误信息"  width="450" :show-overflow-tooltip="true">
                    </el-table-column>
                    <el-table-column prop="failed_at" label="失败时间">
                    </el-table-column>
                    <el-table-column prop="address" label="操作">
                        <template slot-scope="scope">
                            <el-button size="mini" @click="catchInfo(scope.row.id,1)">重试</el-button>
                            <el-button size="mini" @click="catchInfo(scope.row.id,2)">忽略</el-button>
                        </template>
                    </el-table-column>

                </el-table>
            </div>
            <div class="pageclass" style="margin-top: 20px;">
                <el-pagination
                        align="center"
                        @current-change="handleCurrentChange"
                        :current-page.sync="currentPage"
                        :page-size="PageSize"
                        layout="total, prev, pager, next,jumper"
                        :total="total">
                </el-pagination>
            </div>
        </div>
    </template>
</div>
<script>
    var vm = new Vue({
        el: "#re_content",
        delimiters: ['[[', ']]'],
        data() {
            return {
                list: [],
                currentPage:1,//当前页
                total:0,//总条数
                PageSize:10,//每页显示多少条
            }
        },
        mounted() {
            this.getData(1);
        },
        methods: {
            getData(page) {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('setting.cron_log.index') !!}', {page:page}).then(function(response) {
                    if (response.data.result) {
                        console.log(response.data.data.data)
                        this.list = response.data.data.data;
                        this.current_page=response.data.data.current_page;
                        this.total=response.data.data.total;
                        this.per_page=response.data.data.per_page;
                        loading.close();
                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    }
                    loading.close();

                }, function(response) {
                    this.$message({
                        message: response.data.msg,
                        type: 'error'
                    });
                    loading.close();
                });
            },
            handleCurrentChange(val) {
                this.getData(val)
            },
            catchInfo(id,type) {
                let info = '重试';
                if (type == 2) {
                    info = '忽略';
                }
                this.$confirm('是否确定'+info+'？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {

                    this.$http.post('{!! yzWebFullUrl('setting.cron_log.submit') !!}', {id:id,type:type}).then(function(response) {
                    if (response.data.result) {
                        this.$message({message: response.data.msg, type: 'success'});
                    } else {
                        this.$message({message: response.data.msg, type: 'error'});
                    }
                    this.getData(1);
                },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                })
            })
            }
        },

    });
</script>
@endsection
