@extends('layouts.base')

@section('content')
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
            position:relative;
            border-radius: 8px;
            min-height:100vh;
            background-color:#fff;
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
        .el-form-item{
            padding-left:300px;
            margin-bottom:10px!important;
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
        .el-form-item__label{
            margin-right:30px;
        }
        .add{
            width: 154px;
            font-size:16px;
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
        <template>

        </template>
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <div class="block">
                    <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height:18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>幻灯片列表</b><span></div><a><el-button type="primary"  @click="addBlock">添加幻灯片</el-button></a></div>
                </div>
                <template>
                    <el-table
                            :data="tableData"
                            style="width: 100%;padding:0 10px;">
                        <el-table-column
                                prop="id"
                                align="center"
                                label="ID"
                                width="180">
                        </el-table-column>
                        <el-table-column
                                prop="display_order"
                                align="center"
                                label="显示顺序"
                                width="180">
                        </el-table-column>
                        <el-table-column
                                prop="slide_name"
                                align="center"
                                label="标题"
                                label="180">
                        </el-table-column>
                        <el-table-column
                                prop="link"
                                align="center"
                                label="链接"
                                label="180">
                        </el-table-column>
                        <el-table-column
                                align="center"
                                label="状态"
                                label="180">
                            <template slot-scope="scope" >
                                <div v-if="scope.row.enabled==1" style="color:#139933;">
                                    显示
                                </div>
                                <div v-if="scope.row.enabled==0" style="color:#ff1532;">
                                    隐藏
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column
                                align="center"
                                label="操作"
                                label="180">
                            <template slot-scope="scope" >
                                <a style="color:#999" :href="'{{ yzWebFullUrl('setting.slide.edit', array('id' => '')) }}'+[[scope.row.id]]"><i class="iconfont icon-ht_operation_edit" style="font-size:16px;margin-right:35px;" ></i></a>
                                <a style="color:#999"><i  class="iconfont icon-ht_operation_delete" style="font-size:16px;"  @click="del(scope, tableData)"></i></a>
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
                return {
                    activeName: 'one',
                    tableData: [{
                        id: '',
                        display_order: '',
                        slide_name: '',
                        link:'',
                        enabled:'',
                    }]
                }
            },
            mounted () {
                this.getData();
            },
            methods: {
                addBlock(){
                    window.location.href = "{!! yzWebFullUrl('setting.slide.create') !!} "
                },
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.slide.index')!!}').then(function (response){
                        if (response.data.result) {
                            this.tableData=response.data.data
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
                del(scope,rows){
                    this.$confirm('是否删除幻灯片?', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        rows.splice(scope.$index, 1);
                        let json={
                            id:scope.row.id
                        }
                        this.$http.post('{!! yzWebFullUrl('setting.slide.deleted') !!}',json).then(function (response){
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
            },
        });
    </script>
@endsection()


