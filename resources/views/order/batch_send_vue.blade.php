@extends('layouts.base')
@section('title','批量发货')

@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        body{font-weight:500;color:#333;}
        .all{background:#eff3f6}

        .el-form-item__label{margin-right:30px}
        .vue-main-form .el-form-item__content{margin-left:calc(15% + 30px) !important}
        .order-sum-li{width:16.66%;line-height:28px}
        .list-con-goods-text{min-height:70px;overflow:hidden;flex:1;display: flex;flex-direction: column;justify-content: space-between;}
        .list-con-goods-price{border-right:1px solid #e9e9e9;border-left:1px solid #e9e9e9;min-width:150px;min-height:90px;text-align: left;padding:20px;display: flex;flex-direction: column;}
        .list-con-goods-title{font-size:14px;line-height:20px;text-overflow: -o-ellipsis-lastline;overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 2;line-clamp: 2;-webkit-box-orient: vertical;}
        .list-con-goods-option{font-size:12px;color:#999}
    </style>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-crumbs">
                <a>订单管理</a> > 批量发货
            </div>
            <el-form ref="form" :model="form" label-width="15%">
                <div class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">批量发货</div>
                    </div>
                </div>

                <el-form-item label="选择快递：">
                    <el-select v-model="form.express" filterable clearable placeholder="选择快递" style="width:500px">
                        <el-option v-for="(item,index) in list"
                                   :label="item.name"
                                   :value="item"
                                   >
                        </el-option>
                    </el-select>
                </el-form-item>

                <el-form-item label="表格：">
                    <el-upload
                            style="width: 300px;"
                            class="upload-demo"
                            ref="upload"
                            action="{!! yzWebFullUrl('order.batch-send.index') !!}"
                            :on-preview="handlePreview"
                            :on-remove="handleRemove"
                            accept=".xlsx,.xls,csv"
                            :data="upData"
                            :on-success="success"
                            {{--:file-list="fileList"--}}
                            :auto-upload="false">
                        <el-button slot="trigger" size="small" type="primary">选取文件</el-button>
                    </el-upload>


                    <div style="padding-bottom: 15px;">如果遇到数据重复,则进行数据更新!</div>
                    <el-button style="margin-left: 10px;" size="small" type="success" @click="submitUpload('form')">确认导入</el-button>
                    <el-button style="margin-left: 10px;" size="small" type="success" @click="getExample">下载表格模板</el-button>

                    <p style="padding-top: 40px; font-size: 16px; font-weight: bold;">功能介绍：</p>
                    <span>使用excel快速导入进行订单发货, 文件格式[XLS]</span><br/>
                    <span>如重复导入数据将以最新导入数据为准，请谨慎使用</span><br/>
                    <span>数据导入订单状态自动修改为已发货</span><br/>
                    <span>一次导入的数据不要太多,大量数据请分批导入,建议在服务器负载低的时候进行</span>

                    <p style="padding-top: 40px; font-size: 16px; font-weight: bold;">使用方法：</p>
                    <span>1. 下载Excel模板文件并录入信息</span><br/>
                    <span>2. 选择快递公司</span><br/>
                    <span>3. 上传Excel导入</span>


                    <p style="padding-top: 40px; font-size: 16px; font-weight: bold;">格式要求：</p>
                    <span>Excel第一列必须为订单编号，第二列必须为快递单号，请确认订单编号与快递单号的备注</span>

                </el-form-item>




            </el-form>

            <!-- 导入信息 -->
            <el-dialog :visible.sync="close_order_show" width="550px"  title="导入信息">
                <div style="height:300px;overflow:auto" id="close-order">
                    <div style="color:#000;font-weight:500">导入信息</div>
                    <div v-html="close_order_con" style="text-align:center;"></div>
                </div>
                <span slot="footer" class="dialog-footer">
                <el-button @click="close_order_show = false">关闭</el-button>
            </span>
            </el-dialog>

        </div>
    </div>



    <script>
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                return{
                    form:{},
                    list:[1,2,3],
                    close_order_show:false,
                    close_order_con:'',
                }
            },
            created() {
                this.getData()
            },
            computed: {
                // 这里定义上传文件时携带的参数，即表单数据
                upData: function() {
                    return {
                        body: JSON.stringify(this.form.express)
                    }
                }
            },
            mounted() {

            },
            methods: {

                getData() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});

                    let url = "{!! yzWebFullUrl('order.batch-send.get-express') !!}";
                    this.$http.post(url).then(function (response) {
                        // console.log(response.data.data)
                            if (response.data.result){
                                this.list = response.data.data;
                                loading.close();
                            } else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                            loading.close();
                        },function (response) {
                            this.$message({message: response.data.msg,type: 'error'});
                            loading.close();
                        }
                    );
                },
                getExample(){
                    let url = "{!! yzWebFullUrl('order.batch-send.get-example') !!}";
                    this.$http.post(url).then(function (response) {
                        if (response.status == 200) {
                            window.location.href = response.url
                        } else {
                            console.log(response)
                            that.$message.error(response.data.msg);
                        }
                    });

                },



                submitUpload(form) {
                    console.log(form)
                    this.$refs[form].validate(async valid => {
                        if (valid) {
                            // 表单验证通过后使用组件自带的方法触发上传事件
                            this.$refs.upload.submit()
                        } else {
                            return false;
                        }
                    });

                    // this.$refs.upload.submit();
                },
                handleRemove(file, fileList) {
                    console.log(file, fileList);
                },
                handlePreview(file) {
                    console.log(file,5656566);
                },
                success(res){
                    if (res.result) {
                        this.close_order_show = true;
                        this.close_order_con = res.data;
                        console.log(res)
                    } else {
                        this.$refs.upload.clearFiles();
                        this.$message({type: 'error',message: res.msg});
                    }
                }

            },
        })

    </script>
@endsection('content')

