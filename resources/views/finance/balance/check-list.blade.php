@extends('layouts.base')
@section('title', "审核列表")
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods.css')}}"/>
    <style>
        .a-btn {
            border-radius: 2px;
            padding: 8px 12px;
            box-sizing: border-box;
            color: #666;
            font-weight: 500;
            text-align: center;
            margin-left: 1%;
            background-color: #fff;
        }
        .a-btn:hover{
            background-color: #29BA9C;
            color: #FFF;
            border-radius: 5px;
        }
        .tabs-div {
            background: #fff;
            padding: 13px 0;
            margin-left: 10px;
            cursor: pointer;
            border-radius:10px;
        }
        .a-colour2 {
            background-color: #29BA9C;
            color: #FFF;
            border-radius: 5px;
        }

        .sort_active{
            color: #29BA9C;
        }
        .form-list {
            margin-top: -25px;
        }
    </style>
    <div id="qrcode" ref="qrcode" style="display:none;"></div>
    <div class="rightlist">
        <div id="app" v-cloak v-loading="all_loading">
            <template>
                <div class="">
                    <div class="third-list">
                        <div class="form-list">
                            <el-form :inline="true" :model="search_form" ref="search_form" style="margin-left:10px;">
                                <el-row>
                                    <el-form-item label="" prop="">
                                        <el-input v-model="search_form.member_id" placeholder="会员ID"></el-input>
                                    </el-form-item>
                                    <el-form-item label="" prop="">
                                        <el-input v-model="search_form.member" placeholder="会员昵称/姓名/手机号"></el-input>
                                    </el-form-item>

                                    <el-form-item label="" prop="">
                                        <el-select v-model="search_form.status" placeholder="审核状态" clearable>
                                            <el-option v-for="item in status_list" :key="item.id" :label="item.name"
                                                       :value="item.id"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item label="发布时间">
                                        <el-date-picker
                                                v-model="times"
                                                type="datetimerange"
                                                value-format="yyyy-MM-dd HH:mm:ss"
                                                range-separator="至"
                                                start-placeholder="开始日期"
                                                end-placeholder="结束日期"
                                                style="margin-left:5px;"
                                                align="right">
                                        </el-date-picker>
                                    </el-form-item>

                                    <a href="#">
                                        <el-button type="primary" icon="el-icon-search" @click="search(1)">搜索
                                        </el-button>
                                    </a>
                                    </el-col>
                                </el-row>
                            </el-form>
                        </div>
                        <div class="table-list">
                            <div>
                                <template>
                                    <!-- 表格start -->
                                    <el-table :data="list" style="width: 100%"
                                              :class="table_loading==true?'loading-height':''"
                                              v-loading="table_loading">
                                        <el-table-column prop="created_at" label="时间" align="center"></el-table-column>
                                        <el-table-column prop="member_id" label="会员ID" align="center"></el-table-column>
                                        <el-table-column prop="total" label="粉丝" align="center">
                                            <template slot-scope="scope" v-if="scope.row.member != null">
                                                <div class="p-text" style="padding-left: 6px;">
                                                    <p v-if="scope.row.member.avatar!==null"><img style="width:37px;height:37px;border-radius: 50%;margin-right:5px;" :src="scope.row.member.avatar" alt=""></p>
                                                    <p v-if="scope.row.member.nickname">[[scope.row.member.nickname]]</p>
                                                    <p v-else>未更新</p>
                                                </div>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="姓名/手机号" align="center">
                                            <template slot-scope="scope" v-if="scope.row.member != null">
                                                [[scope.row.member.realname]] / [[scope.row.member.mobile]]
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="充值说明" align="center">
                                            <template slot-scope="scope">
                                                <div class="table-option">&nbsp;&nbsp;
                                                    <a @click="seeExplain(scope.$index)">
                                                        查看
                                                    </a>&nbsp;&nbsp;
                                                </div>
                                            </template>
                                        </el-table-column>
                                        <el-table-column prop="money" label="充值金额" align="center"></el-table-column>
                                        <el-table-column label="操作人" align="center">
                                            <template slot-scope="scope">
                                                [[scope.row.admin_user.username]]【ID:[[scope.row.admin_user.uid]]】
                                            </template>
                                        </el-table-column>
                                        <el-table-column prop="status_name" label="审批状态" align="center"></el-table-column>
                                        <el-table-column label="操作" width="320" align="center">
                                            <template slot-scope="scope">
                                                <el-button size="mini" type="success" v-if="is_can_check&&scope.row.status==0" @click="checkLog(scope.row.id,1)" round>
                                                    通过
                                                </el-button>
                                                <el-button size="mini" type="danger" v-if="is_can_check&&scope.row.status==0" @click="checkLog(scope.row.id,2)" round>
                                                    驳回
                                                </el-button>
                                            </template>
                                        </el-table-column>
                                    </el-table>
                                    <!-- 表格end -->
                                </template>


                                <!-- 查看充值说明 -->
                                <el-dialog title="充值说明" :visible.sync="explain_show" width="20%" center>
                                    [[explain]]<br>
                                    <el-link type="primary" @click="dowonload(enclosure)" v-if="enclosure">点击查看附件</el-link>
                                </el-dialog>
                            </div>
                        </div>
                    </div>
                    <!-- 分页 -->
                    <div class="vue-page" v-show="total>1">
                        <el-row>
                            <el-col align="right">
                                <el-pagination layout="prev, pager, next,jumper" @current-change="search" :total="total"
                                               :page-size="per_size" :current-page="current_page" background
                                               v-loading="loading"></el-pagination>
                            </el-col>
                        </el-row>
                    </div>
                </div>

            </template>

        </div>
    </div>
    <script>
        var app = new Vue({
            el: "#app",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    is_can_check:0,
                    status_list:[
                        {id:1,name:"已通过"},
                        {id:2,name:"已驳回"},
                        {id:0,name:"待审核"},
                    ],
                    times:[],
                    list: [],//列表
                    search_form: {
                        member_id: '',
                        member: '',
                        status:"",
                    },
                    explain:"",
                    enclosure:"",
                    explain_show:false,
                    form: {},

                    loading: false,
                    table_loading: false,
                    all_loading: false,

                    //分页
                    total: 0,
                    per_size: 0,
                    current_page: 0,
                    this_page:0,
                    rules: {},
                }
            },
            created() {

            },
            mounted() {
                let is_can_check = {!! $is_can_check !!};
                this.is_can_check = is_can_check;
                this.search(1);
            },
            methods: {
                dowonload(url) {
                    let that = this;
                    let link = document.createElement('a')  // 创建a标签
                    link.style.display = 'none'
                    if (!/\.(jpg|jpeg|png|GIF|JPG|PNG)$/.test(url)) {
                        that.$http.post("{!! yzWebFullUrl('finance.balance-recharge-check.down-load-file') !!}", {url},{
                            responseType: 'blob',
                        }).then(res => {
                            if (res.result == 0) {
                                return this.$message.error(res.msg);
                            }
                            //文件名
                            var filename = url.split('/').pop()
                            let binaryData = [];
                            binaryData.push(res);
                            link.href = window.URL.createObjectURL(res.data);
                            link.download = filename;
                            link.setAttribute("visibility", "none");
                            document.body.appendChild(link)
                            link.click()
                            window.URL.revokeObjectURL(link.href);
                            document.body.removeChild(link);
                        }), function (res) {
                            this.$message.error("文件下载失败!")
                        };
                    } else {
                        link.href =  url // 设置下载地址
                        link.setAttribute('target', '__blank') // 浏览器打开图片
                        document.body.appendChild(link)
                        link.click();
                    }
                },
                // 搜索、分页
                search(page) {
                    this.this_page = page;
                    var that = this;
                    console.log(that.search_form);
                    // 商品类型
                    let json = {
                        page: page,
                        search: that.search_form,
                    };

                    if(this.times && this.times.length>0) {
                        json.search.start_time = this.times[0];
                        json.search.end_time = this.times[1];
                    } else {
                        json.search.start_time = '';
                        json.search.end_time = '';
                    }

                    that.table_loading = true;
                    that.$http.post("{!! yzWebFullUrl('finance.balance-recharge-check.get-list') !!}", json).then(response => {
                        console.log(response);
                        if (response.data.result == 1) {
                            that.list = response.data.data.data;
                            that.total = response.data.data.total;
                            that.current_page = response.data.data.current_page;
                            that.per_size = response.data.data.per_page;
                        } else {
                            that.$message.error(response.data.msg);
                        }
                        that.table_loading = false;
                    }), function (res) {
                        console.log(res);
                        that.table_loading = false;
                    };
                },
                seeExplain(index) {
                    this.explain_show = true;
                    this.explain = this.list[index].explain;
                    this.enclosure = this.list[index].enclosure_src;
                },
                checkLog(id,status) {
                    var that = this;
                    that.$confirm('确定该操作吗', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        that.table_loading = true;
                        that.$http.post("{!! yzWebFullUrl('finance.balance-recharge-check.check') !!}", {id: id,status:status}).then(response => {
                            if (response.data.result == 1) {
                                that.$message.success("审核成功！");
                                that.search(1);
                            } else {
                                that.$message.error(response.data.msg);
                            }
                            that.table_loading = false;
                        }), function (res) {
                            console.log(res);
                            that.table_loading = false;
                        };
                    }).catch(() => {
                        this.$message({type: 'info', message: '已取消审核'});
                    });
                },
                // 字符转义
                escapeHTML(a) {
                    a = "" + a;
                    return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");
                },
            },
        })

    </script>
@endsection
