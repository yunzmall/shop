@extends('layouts.base')
@section('title', '折扣设置')
@section('content')

<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>

<style>
    
    .el-tabs__item,.is-top{font-size:16px}
    .el-tabs__active-bar { height: 3px;}
</style>
<div class="all">
    <div id="app" v-cloak>
        <div class="vue-nav">
            <el-tabs v-model="activeName" @tab-click="handleClick">
                <el-tab-pane label="折扣全局设置" name="1"></el-tab-pane>
                <el-tab-pane label="折扣设置" name="2"></el-tab-pane>
                <el-tab-pane label="运费批量设置" name="3"></el-tab-pane>
            </el-tabs>
        </div>
        <div class="vue-main">
            
            <div class="vue-main-title">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">折扣设置</div>
            </div>
            <div class="vue-main-form">
                
                <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                    <el-form-item label="" prop="batch_list">
                        <template v-for="(item,index) in form.batch_list">
                            <el-input :value="item.new_name" style="width:60%;padding:10px 0;" disabled></el-input>
                            <a v-bind:href="'{{ yzWebUrl('discount.batch-discount.update-set', array('id' => '')) }}'+[[form.batch_list[index].id]]">
                                <el-button>编辑</el-button>
                            </a>
                            <el-button type="danger" icon="el-icon-close" @click="delBatch(index,form.batch_list[index].id)"></el-button>
                        </template><br>
                        <a href="{{ yzWebFullUrl('discount.batch-discount.store') }}">
                            <el-button type="primary">添加批量折扣</el-button>
                        </a>
                    </el-form-item>
                </el-form>
            </div>
        </div>
        <!-- 分页 -->
        <!-- <div class="vue-page">
            <div class="vue-center">
                <el-button type="primary" @click="submitForm('form')">保存设置</el-button>
                <el-button @click="goBack">返回</el-button>
            </div>
        </div> -->
        <!--end-->
    </div>
</div>

    <script>
        var vm = new Vue({
        el:"#app",
        delimiters: ['[[', ']]'],
            data() {
                return{
                    form:{
                        batch_list:[],
                    },
                    activeName:'2',
                    loading: false,
                    submit_loading: false,
                    rules: {

                    },
                }
            },
            methods: {
                delBatch(index,id){
                    if(!id){
                        this.$confirm('确定删除吗', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {

                            this.form.batch_list.splice(index,1);
                            this.$message({type: 'success',message: '删除成功!'});
                        }).catch(() => {
                            this.$message({type: 'info',message: '已取消删除'});
                        });
                    }
                    else{
                        this.$confirm('确定删除吗', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                            this.table_loading=true;
                            this.$http.post('{!! yzWebFullUrl('discount.batch-discount.delete-set') !!}',{id:id}).then(function (response) {
                                    console.log(response.data);
                                    if (response.data.result) {
                                        this.form.batch_list.splice(index,1);
                                        this.$message({type: 'success',message: '删除成功!'});
                                    }
                                    else{
                                        this.$message({type: 'error',message: response.data.msg});
                                    }
                                    this.table_loading=false;
                                },function (response) {
                                    this.$message({type: 'error',message: response.data.msg});
                                    this.table_loading=false;
                                }
                            );
                        }).catch(() => {
                            this.$message({type: 'info',message: '已取消删除'});
                        });

                    }
                },
                handleClick(val) {
                    console.log(val.name)
                    if(val.name == 1) {
                        window.location.href = `{!! yzWebFullUrl('discount.batch-discount.allSet') !!}`;
                    }
                    else if(val.name == 2) {
                        window.location.href = `{!! yzWebFullUrl('discount.batch-discount.index') !!}`;
                    }
                    else if(val.name == 3) {
                        window.location.href = `{!! yzWebFullUrl('discount.batch-dispatch.freight') !!}`;
                    }
                },
                // settingBatch(index,id) {
                //     console.log(index,id);
                //     window.location.href='{!! yzWebFullUrl('discount.batch-discount.store') !!}';
                // },
            },
            created(){
                this.$http.get("{!! yzWebUrl('discount.batch-discount.get-set') !!}" ).then(response => {
                    let batch_list=response.data.data
                    for(let i=0;i<batch_list.length;i++){
                        batch_list[i].new_name=[];
                        for(let j=0;j<batch_list[i].category_ids.length;j++){
                            batch_list[i].new_name[j] = "[ID:"+batch_list[i].category_ids[j].id+"][分类:"+batch_list[i].category_ids[j].name+"]";
                        }
                        batch_list[i].new_name = batch_list[i].new_name.join(",");
                    }
                    this.form.batch_list=batch_list
                }, response => {
                    console.log(response);
                });
            }
        });
    </script>
@endsection



