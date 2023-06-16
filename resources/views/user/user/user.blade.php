@extends('layouts.base')
@section('title', '操作员管理')
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
            margin-bottom:10px;
        }
        .con .setting .block .title{
            font-size:18px;
            margin-bottom:32px;
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
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <div class="block">
                    <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>操作员</b></div></div>
                    <template>
                        <el-select v-model="search_form.role_id" placeholder="请选择" style="margin-right:16px;">
                            <el-option
                                    v-for="item in roleList"
                                    :key="item.value"
                                    :label="item.name"
                                    :value="item.id">
                            </el-option>
                        </el-select>
                    </template>
                    <template>
                        <el-select v-model="search_form.status" placeholder="请选择" style="margin-right:16px;">
                            <el-option
                                    v-for="item in options"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.status">
                            </el-option>
                        </el-select>
                    </template>
                    <el-input v-model="search_form.keyword"  style="width:15%;margin-right:16px;" placeholder="可搜索操作员帐号/姓名/手机号"></el-input><el-button type="primary" @click="search">搜索</el-button>
                </div>
                <div style="background: #eff3f6;width:100%;height:15px;"></div>
                <div class="block">
                    <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>操作员列表</b><span><a href="{{ yzWebFullUrl('user.user.store') }}"><el-button style="margin-left:30px;" type="primary" size="mini">添加操作员</el-button></a></div></div>
                </div>
                <el-table
                        v-loading="loading"
                        element-loading-text="加载中"
                        :data="tableData"
                        style="padding:0 10px"
                        style="width: 100%">
                    <el-table-column
                            prop="username"
                            align="center"
                            label="操作员账号"
                    >
                    </el-table-column>
                    <el-table-column
                            align="center"
                            label="角色"
                    >
                        <template slot-scope="scope" >
                            [[scope.row.user_role&&scope.row.user_role.role?scope.row.user_role.role.name:'无']]
                        </template>
                    </el-table-column>
                    <el-table-column
                            align="center"
                            label="	姓名"
                    >
                        <template slot-scope="scope" >
                            [[scope.row.user_profile?scope.row.user_profile.realname:'']]
                        </template>
                    </el-table-column>
                    <el-table-column
                            align="center"
                            label="	手机"
                    >
                        <template slot-scope="scope" >
                            [[scope.row.user_profile?scope.row.user_profile.mobile:'']]
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
                                    :inactive-value="3"
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
                            <a style="color:#999" :href="'{{ yzWebFullUrl('user.user.update', array('id' => '')) }}'+[[scope.row.uid]]"><i class="iconfont icon-ht_operation_edit" style="font-size:16px;margin-right:35px;"></i></a>
                            <a style="color:#999"><i class="iconfont icon-ht_operation_delete" style="font-size:16px;" @click="del(scope, tableData)"></i></a>
                        </template>
                    </el-table-column>
                </el-table>
                </template>
                <el-row style="background-color:#fff;">
                    <el-col :span="24" align="center" migra style="padding:15px 5% 15px 0">
                        <el-pagination background  @current-change="currentChange"
                                       layout="prev, pager, next"
                                       :page-size="page_size" :current-page="current_page" :total="page_total"></el-pagination>
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
                    roleList:[],
                    loading:false,
                    search_loading:false,
                    all_loading:false,
                    page_total:0,
                    page_size:0,
                    current_page:0,
                    search_form:{
                        status:'',
                        role_id:''
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
                    tableData: [],
                }
            },
            mounted () {
                this.getData();
            },
            methods: {
                changeStatus(item){

                    this.$http.post('{!! yzWebFullUrl('user.user.switchUser') !!}',{uid:item.uid}).then(function (response){
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
                    this.$http.post('{!! yzWebFullUrl('user.user.index') !!}',json
                    ).then(function (response) {
                            if (response.data.result){
                                let datas = response.data.data.userList;
                                this.roleList=response.data.data.roleList
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
                    this.$http.post('{!! yzWebFullUrl('user.user.index') !!}',{page:val,search:this.real_search_form}).then(function (response){
                            if (response.data.result){
                                let datas = response.data.data.userList;
                                this.roleList=response.data.data.roleList
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
                            id:scope.row.uid
                        }
                        this.$http.post('{!! yzWebFullUrl('user.user.destroy') !!}',json).then(function (response){
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
                    this.$http.post('{!! yzWebFullUrl('user.user.index') !!}').then(function (response){
                        if (response.data.result) {
                            let datas = response.data.data.userList;
                            this.roleList=response.data.data.roleList
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