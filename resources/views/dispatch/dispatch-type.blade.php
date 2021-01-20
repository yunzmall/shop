@extends('layouts.base')

@section('content')
@section('title', trans('配送方式'))

<style>

    .el-icon-edit{font-size:16px;padding:0 15px;color:#409EFF;cursor: pointer;}
    .edit-i{display:none;}
    .el-table_1_column_2:hover .edit-i{font-weight:900;padding:0;margin:0;display:inline-block;}

    .con{
        padding-bottom:40px;
        border-radius: 8px;
    }

    .con .block{
        padding:10px;
        background-color:#fff;
        border-radius: 8px;
        height:30vh;
    }
</style>

<div id="app"  class="w1200 ">
    <div class=" rightlist ">
        <div class="right-titpos">
            <ul class="add-snav">
                <li class="active"><a href="#">配送方式列表</a></li>
                <a class='btn btn-primary' href="#" style="margin-bottom:5px;"  @click="showBulkUpdateGoods()"><i class='fa fa-hand-o-up'></i> 批量更新商品</a>
            </ul>
        </div>
        <div class="right-addbox">
            <div>
                <div class="panel panel-info">
                    <div class="panel-body">
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-body table-responsive">
                        <div v-loading="loading">
                            <!-- 表格start -->
                            {{--<el-table ref="multipleTable" :data="tableData" tooltip-effect="dark" style="width: 100%" @selection-change="handleSelectionChange">--}}
                            <el-table :data="list" style="width: 100%" max-height="600" v-loading="table_loading">
                                <el-table-column prop="id" label="ID" width="80" align="center"></el-table-column>
                                <el-table-column prop="sort" label="排序" max-width="80" align="center">
                                    <template slot-scope="scope">
                                        <el-popover class="item" placement="top" effect="light">
                                            <div style="text-align:center;">
                                                <el-input v-model="change_sort" size="small"
                                                          style="width:100px;"></el-input>
                                                <el-button size="small"
                                                           @click="confirmChangeSort(scope.row.id)">确定
                                                </el-button>
                                            </div>
                                            <a slot="reference">
                                                <i class="el-icon-edit edit-i" title="点击编辑排序"
                                                   @click="editSort(scope.row.sort)"></i>
                                            </a>
                                        </el-popover>
                                        [[scope.row.sort]]
                                    </template>
                                </el-table-column>
                                <el-table-column prop="name" label="配送名称" max-width="100" align="center"></el-table-column>
                                <el-table-column label="是否开启"  max-width="100" align="center">
                                    <template slot-scope="scope">
                                        <el-switch v-model="scope.row.enable" :active-value="1" :inactive-value="0" @change="editEnable(scope.row.id, scope.row.enable)"></el-switch>
                                        {{--<el-switch v-model="scope.row.enable" @change="editEnable(scope.row.id)" active-color="#13ce66" inactive-color="#ff4949"></el-switch>--}}
                                    </template>
                                </el-table-column>
                            </el-table>

                            <!-- 表格end -->
                        </div>
                    </div>
                </div>

                <el-dialog :visible.sync="dispatchTypeShow" width="60%" center title="批量更新">
                    <div class="con">
                        <div class="block">
                            <div style="text-align: center">
                                <span>更新商品的配送方式，只更新配送方式为空的商品</span>
                            </div>
                            <div style="width:100%;height:30px;"></div>
                            <div style="text-align: center">
                                <el-checkbox-group v-model="dispatch_type_ids">
                                    <el-checkbox v-for="dispatchType in list" v-if="dispatchType.enable == 1" :label="dispatchType.id">[[ dispatchType.name]]</el-checkbox>
                                </el-checkbox-group>

                            </div>
                        </div>

                        <div style="text-align: center">
                            <el-button class="btn btn-success" type="button" size="small" @click="bulkUpdateGoods()">提交更新</el-button>
                        </div>
                    </div>
                </el-dialog>
            </div>
        </div>
    </div>
</div>


<script>
    var app = new Vue({
        el:"#app",
        delimiters: ['[[', ']]'],
        data() {

            return{
                loading:false,
                table_loading:false,
                dispatchTypeShow: false,

                list: [],
                change_sort: "",//修改排序弹框赋值
                dispatch_type_ids: [],
            }

        },
        created() {
            this.getList();
        },
        methods: {
            setData(data) {
                this.list = data;
                console.log(this.list);
            },
            getList() {
                var that = this;
                that.table_loading = true;
                that.$http.post("{!! yzWebFullUrl('dispatch.dispatch-type.get-data') !!}").then(response => {
                    console.log(response);
                    if (response.data.result == 1) {
                        this.setData(response.data.data);
                    } else {
                        that.$message.error(response.data.msg);
                    }
                    that.table_loading = false;
                }), function (res) {
                    console.log(res);
                    that.table_loading = false;
                };
            },
            //开启关闭
            editEnable(id, enable) {
                var that = this;
                that.table_loading = true;
                let json = {id: id, enable:enable};
                that.$http.post("{!! yzWebFullUrl('dispatch.dispatch-type.edit-enable') !!}", json).then(response => {
                    console.log(response);
                    if (response.data.result == 1) {
                        that.$message.success('操作成功！');
                    } else {
                        that.$message.error(response.data.msg);
                    }
                    that.table_loading = false;
                }), function (res) {
                    console.log(res);
                    that.table_loading = false;
                };
            },
            //编辑排序
            editSort(sort) {
                let that = this;
                that.change_sort = sort;
            },
            // 确认修改排序
            confirmChangeSort(id) {
                let that = this;
                if (!(/^\d+$/.test(that.change_sort))) {
                    that.$message.error('请输入正确数字');
                    return false;
                }
                that.table_loading = true;
                let json = {id: id, value: that.change_sort};
                that.$http.post("{!! yzWebFullUrl('dispatch.dispatch-type.edit-sort') !!}", json).then(response => {
                    console.log(response);
                    if (response.data.result == 1) {
                        that.$message.success('操作成功！');
                        that.getList();
                    } else {
                        that.$message.error(response.data.msg);
                    }
                    that.table_loading = false;
                }), function (res) {
                    console.log(res);
                    that.table_loading = false;
                };
            },
            showBulkUpdateGoods() {
                this.dispatchTypeShow = true;
            },

            bulkUpdateGoods() {
                var that = this;

                if (this.dispatch_type_ids.length < 1) {
                    this.$message.error('请至少选择一种配送方式');
                    return false;
                }

                that.$http.post("{!! yzWebFullUrl('dispatch.dispatch-type.bulk-update-goods') !!}", {dispatch_type_ids:this.dispatch_type_ids}).then(response => {
                    console.log(response.data);
                    if (response.data.result == 1) {
                        that.$message.success('批量操作更新成功！');
                        //location.reload();
                    } else {
                        that.$message.error(response.data.msg);
                    }
                }), function (res) {
                    console.log(res);
                };

            },
        },
    })

</script>
@endsection('content')