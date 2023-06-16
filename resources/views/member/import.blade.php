<script src="{!!resource_absolute('static/js/xlsx.full.min.js')!!}"></script>
@extends('layouts.base')
@section('content')
<link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />

<style scoped>
    .rightlists {
        width: 600px;
        height: 50px;
        color: crimson;
        line-height: 48px;
        text-align: center;
        border-radius: 5px;
        border: 1px solid crimson;
        background: rgb(250, 220, 225);
    }

    .usage {
        width: 100%;
        height: 100px;
        font-size: 15px;
        font-weight: 600;
        color: black;
        display: flex;
        line-height: 60px;
        border-radius: 5px;
        overflow: hidden;
        background: #eff3f6;
    }

    .method_application {
        margin-left: 50px;
    }

    .method_info p:nth-child(2) {
        line-height: 0px;
    }

    .EXCEL_Box {
        /* border:1px solid red; */
        display: flex;
        justify-content: space-between;
    }

    .EXCEL_Box label {
        font-weight: 600;
        line-height: 40px;
        margin: 0 10px 0 10px;
        /* border:1px solid red; */

    }

    .put_box {
        /* border:1px solid red; */
        width: calc(100% - 500px);
        margin-left: 150px;
    }
</style>

<div class="all">
    <div id="app">
        <div class="total-head">
            <el-form>
                <el-form-item>
                    <div class="vue-title">
                        <div class="vue-title-left"></div>
                        <div class="vue-title-content">会员excel上传</div>
                    </div>
                </el-form-item>
                <div class="put_box">
                    <el-form-item>
                        <div class="rightlists">尽量在服务器空闲时间来操作，会占用大量内存与带宽，在获取过程中，请不要进行任何操作!</div>
                    </el-form-item>
                    <el-form-item>
                        <div class="usage">
                            <div class="method_application">使用方法：</div>
                            <div class="method_info">
                                <p>1. 目前支持xls和xlsx两种格式</p>
                                <p>2. 下载模板填写信息</p>
                            </div>
                        </div>
                    </el-form-item>
                    <el-form-item>
                        <div class="EXCEL_Box">
                            <div style="display: flex;">
                                <label class="excel-label">EXCEL</label>
                                <input type="file" @change="importf($event)" />
                            </div>
                            <div>
                                <el-button @click="ExcelEvent" type="primary">Excel示例文件下载</el-button>
                            </div>
                        </div>
                    </el-form-item>
                </div>
            </el-form>
        </div>
    </div>
</div>
<script>
    let vm = new Vue({
        el: "#app",
        name: "import",
        delimiters: ['[[', ']]'],
        data() {
            return {
                message: "girl",
                wb: "", //读取完成的数据
                rABS: false //是否将文件读取为二进制字符串
            }
        },
        methods: {
            importf(obj) { //导入
                console.log(obj, 456465465);
                console.log(obj.target.files);
                if (!obj.target.files) {
                    return;
                }
                var f = obj.target.files[0];
                var reader = new FileReader();
                console.log(reader);
                reader.onload = function(e) {
                    var data = e.target.result;
                    if (this.rABS) {
                        this.wb = XLSX.read(btoa(this.fixdata(data)), { //手动转化
                            type: 'base64'
                        });
                    } else {
                        this.wb = XLSX.read(data, {
                            type: 'binary'
                        });
                    }
                    //wb.SheetNames[0]是获取Sheets中第一个Sheet的名字
                    //wb.Sheets[Sheet名]获取第一个Sheet的数据
                    var data = XLSX.utils.sheet_to_row_object_array(this.wb.Sheets[this.wb.SheetNames[0]]);
                    console.log(data, 44564);
                    $.ajax({
                        url: "{!! yzWebUrl('member.member.member-excel') !!}",
                        type: "post",
                        data: {
                            data: data
                        },
                        cache: false,
                        success: function(result) {
                            alert(result.msg);
                            window.location.reload();
                        }
                    })
                };
                // this.$message.success("小屁孩")
                if (this.rABS) {
                    reader.readAsArrayBuffer(f);
                } else {
                    reader.readAsBinaryString(f);
                }
            },
            fixdata(data) { //文件流转BinaryString
                var o = "",
                    l = 0,
                    w = 10240;
                for (; l < data.byteLength / w; ++l) o += String.fromCharCode.apply(null, new Uint8Array(data.slice(l * w, l * w + w)));
                o += String.fromCharCode.apply(null, new Uint8Array(data.slice(l * w)));
                return o;
            },
            ExcelEvent() {
                let url = `{!! yzWebFullUrl('member.member.memberExcelDemo') !!}`;
                window.location.href = url;
            }
        }
    })
</script>@endsection('content')