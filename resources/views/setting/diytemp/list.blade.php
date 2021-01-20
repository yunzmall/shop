@extends('layouts.base')
@section('title', '自定义模板管理')
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
            position:relative;
            border-radius: 8px;
            min-height:100vh;

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
                    <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;" ></span><b>搜索</b><span></div></div>
                    <el-input  style="width:15%;margin-right:16px;" v-model="keyword" placeholder="请输入模板名称" ></el-input><el-button type="primary"  @click="search">搜索</el-button>
                </div>
                <div style="background: #eff3f6;width:100%;height:15px;"></div>
                <div class="block">
                    <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>自定义模板列表([[page_total]])</b></div><a  @click="toAdd"><el-button type="primary" size="mini" >添加新模板</el-button></a></div>

                </div>
                <el-table
                        :data="tableData"
                        style="width: 100%;padding:0 10px;"
                >
                    <el-table-column
                            prop="title"
                            align="center"
                            label="模板名称"
                    >
                    </el-table-column>
                    <el-table-column
                            align="center"
                            label="操作"
                    >
                        <template slot-scope="scope" >
                            <a style="color:#999;margin-right:20px;" :href="'{{ yzWebFullUrl('setting.diy-temp.edit', array('id' => '')) }}'+[[scope.row.id]]"><i class="iconfont icon-ht_operation_edit" style="font-size:16px;margin-right:35px;"></i></a>
                            <a style="color:#999"><i class="iconfont icon-ht_operation_delete" style="font-size:16px;" @click="del(scope, tableData)"></i></a>
                        </template>
                    </el-table-column>
                </el-table>
                </template>
                <el-row style="background-color:#fff;">
                    <el-col :span="24" align="center" migra style="padding:15px 5% 15px 0" v-loading="loading">
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
                    keyword:'',
                    list:[],
                    tableData:[],
                    loading:false,
                    search_loading:false,
                    all_loading:false,
                    page_total:0,
                    page_size:0,
                    current_page:0,
                    keyword:'',
                    real_search_form:{},
                    activeName: 'one',
                    multipleSelection:[]
                }
            },
            mounted () {
                this.search()
            },
            methods: {
                toAdd(){
                    window.location.href='{!! yzWebFullUrl('setting.diy-temp.add') !!}'
                },
                search() {
                    this.search_loading = true;
                    this.$http.post('{!! yzWebFullUrl('setting.diy-temp.index') !!}',{keyword:this.keyword}
                    ).then(function (response) {
                            if (response.data.result){
                                let datas = response.data.data.list;
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
                    this.$http.post('{!! yzWebFullUrl('setting.diy-temp.index') !!}',{page:val,keyword:this.keyword}).then(function (response){
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
                del(scope,rows){
                    rows.splice(scope.$index, 1);
                    let json={
                        id:scope.row.id
                    }
                    this.$http.post('{!! yzWebFullUrl('setting.diy-temp.del') !!}',json).then(function (response){
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
                },

            },
        });
    </script>
@endsection

