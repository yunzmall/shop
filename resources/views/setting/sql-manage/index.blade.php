@extends('layouts.base')
@section('title', trans('数据库管理'))
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
<div id='re_content'>
    @include('layouts.newTabs')
    <div class="con">
        <div class="setting">
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block" style="padding-top:20px;">
                <div class="title">
                    <div style="display:flex;align-items:center;">
                        <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                        <b>数据库管理</b>
                    </div>
                </div>
            </div>
            <template style="margin-top:-10px;">
                <el-table :data="list" style="padding:0 10px" style="width: 100%">
                    <el-table-column prop="" align="center" label="访问路径" label="100">
                        <template slot-scope="scope">
                            [[scope.row.access_url]]
                            <el-input v-model="scope.row.access_url" style="position: relative;opacity: 0" ref="access_url"></el-input>
                            <span v-if="scope.row.access_url">
                                <el-button icon="el-icon-document-copy" @click="copyLink('access_url')" size="small" round>复制链接</el-button>
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="" align="center" label="安装状态" label="100">
                        <template slot-scope="scope">
                            <span v-if="scope.row.is_install">已安装</span>
                            <span v-else="scope.row.is_install">未安装</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="" align="center" label="操作" label="100">
                        <template slot-scope="scope">
                            <div v-if="!scope.row.is_install">
                                <el-button type="primary" @click="download_(scope.row.download_url)">下载安装</el-button>
                            </div>
                            <div v-else>
                                <el-button type="danger" @click="uninstall_()">卸载</el-button>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
        </div>
    </div>
</div>
<script>
    var vm = new Vue({
        el: "#re_content",
        delimiters: ['[[', ']]'],
        data() {
            let list = {!! $list ?: '[]' !!};
            return {
                loading:false,
                list: list,
            }
        },
        created(){
        },
        mounted () {
        },
        methods: {
            download_(url){
                let loading = this.$loading({target:document.querySelector(".content"),background:'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('setting.sql-manage.download') !!}', {url:url}).then(function (res) {
                    if (res.data.result) {
                        this.$message({message:res.data.msg,type:'success'});
                        this.loading = false;
                    }
                    loading.close();
                    location.reload();
                }, function (res) {
                    console.log(res)
                    this.loading = false;
                });
            },
            uninstall_(){
                this.$confirm('是否确定要卸载', '温馨提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    this.$http.get('{!! yzWebFullUrl('setting.sql-manage.uninstall') !!}').then(function (res) {
                        if (res.data.result) {
                            this.$message({message:res.data.msg,type:'success'});
                        } else {
                            this.$message({message:res.data.msg,type:'error'});
                        }
                        window.location = location;
                    }, function (res) {
                        console.log(res)
                    });
                }).catch(() => {
                    console.log()
                });
            },
            copyLink(type) {
                this.$refs[type].select();
                document.execCommand("Copy")
                this.$message.success("复制成功!");
            },
        },
    });
</script>
@endsection

