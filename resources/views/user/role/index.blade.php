@extends('layouts.base')
@section('title', '角色管理')
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
    <div id='re_content' >
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <div class="block">
                    <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>角色管理</b><span></div></div>
                    <template>
                        <el-select v-model="search_form.status" placeholder="请选择">
                            <el-option
                                    v-for="item in options"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.status">
                            </el-option>
                        </el-select>
                    </template>
                    <el-input  v-model="search_form.keyword" style="width:15%;margin-left:16px;margin-right:16px;" placeholder="可搜索角色名称"></el-input><el-button type="primary" @click="search">搜索</el-button>
                </div>
                <div style="background: #eff3f6;width:100%;height:15px;"></div>
                <div class="block">
                    <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>角色列表</b><a href="{{ yzWebFullUrl('user.role.store') }}"><el-button type="primary" style="margin-left:30px;" size="mini">添加角色</el-button></a></div></div>
                </div>
                <template style="margin-top:-10px;">
                    <el-table
                            :data="tableData"
                            style="padding:0 10px"
                            v-loading="loading"
                            element-loading-text="加载中"
                    >
                        <el-table-column
                                prop="name"
                                align="center"
                                label="角色名称"
                        >
                        </el-table-column>
                        <el-table-column
                                align="center"
                                label="操作员数量"
                        >
                            <template slot-scope="scope" >
                                <div>
                                    [[scope.row.role_user.length]]
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column
                                align="center"
                                label="状态"
                        >
                            <template slot-scope="scope" >
                                <el-switch
                                        @change="changeStatus(scope.row)"
                                        v-model="scope.row.status"
                                        :active-value="2"
                                        :inactive-value="1"
                                        active-color="#54c8b0"
                                >
                                </el-switch>
                            </template>
                        </el-table-column>
                        <el-table-column
                                align="center"
                                label="操作"
                        >
                            <template slot-scope="scope" >
                                <a :href="'{{ yzWebFullUrl('user.role.update', array('id' => '')) }}'+[[scope.row.id]]" style="color:#999"><i class="iconfont icon-ht_operation_edit" style="font-size:16px;margin-right:35px;"></i></a>
                                <a style="color:#999"><i class="iconfont icon-ht_operation_delete" style="font-size:16px;" @click="del(scope, tableData)"></i></a>
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
            el: "#re_content",
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
                        status:''
                    },
                    real_search_form:{},
                    activeName: 'one',
                    options: [{
                        status: '2',
                        label: '启用'
                    },
                        {
                            status: '1',
                            label: '禁用'
                        }
                    ],
                    tableData: []
                }
            },
            mounted () {
                this.getData();
            },
            methods: {
                changeStatus(item){
                    this.$http.post('{!! yzWebFullUrl('user.role.switchRole') !!}',{id:item.id}).then(function (response){
                            if (response.data.result) {
                                this.$message({message:"操作成功！",type:"success"});
                                this.loading = false;
                            }else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                        },function (response) {
                            console.log(response);
                            this.loading = false;
                        }
                    );
                },
                search() {
                    this.search_loading = true;
                    let json={
                        search:this.search_form,
                    }
                    this.$http.post('{!! yzWebFullUrl('user.role.index') !!}',json
                    ).then(function (response) {
                            if (response.data.result){

                                let datas = response.data.data.roleList;
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
                    this.$http.post('{!! yzWebFullUrl('user.role.index') !!}',{page:val,search:this.real_search_form}).then(function (response){
                            if (response.data.result){
                                let datas = response.data.data.roleList;
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
                del(scope,rows){
                    this.$confirm('是否删除?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        rows.splice(scope.$index, 1);
                        let json={
                            id:scope.row.id
                        }
                        this.$http.post('{!! yzWebFullUrl('user.role.destory') !!}',json).then(function (response){
                                if (response.data.result) {
                                    this.$message({message:"删除成功！",type:"success"});
                                    this.loading = false;
                                }else {
                                    this.$message({message: response.data.msg,type: 'error'});
                                }
                            },function (response) {
                                console.log(response);
                                this.loading = false;
                            }
                        );
                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '已取消删除'
                        });
                    });

                },

                getData(){
                    this.loading = true
                    this.$http.post('{!! yzWebFullUrl('user.role.index') !!}').then(function (response){
                        if (response.data.result) {
                            let datas = response.data.data.roleList;
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
            },
        });
    </script>
@endsection