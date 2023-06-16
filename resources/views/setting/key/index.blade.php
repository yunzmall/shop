@extends('layouts.base')

@section('title','注册商城')

@section('content')
    <script src="{{resource_get('static/yunshop/js/industry.js', 1)}}"></script>
    <div class="w1200 m0a">
        <div class="rightlist">
            <!-- 新增加右侧顶部三级菜单 -->
            <div class="right-titpos">
                <ul class="add-snav">
                    <li class="active"><a href="#">注册商城</a></li>
                </ul>
            </div>
            <div class="form-group message-box" style="display: none">
                <div class="span4">
                    <div class="alert alert-block">
                        <a class="close" data-dismiss="alert">×</a>
                        <span id="message"></span>
                    </div>
                </div>
            </div>
            <!-- 新增加右侧顶部三级菜单结束 -->
            <div class="panel panel-default" style="width:996px;">
                <div class='panel-body'>

                    <div id="register">
                        <template>
                            <el-form ref="form" :model="form" label-width="100px" class="demo-ruleForm" v-show="page=='auth'">
                                <el-form-item label="当前域名" prop="domain">
                                    <span>[[ domain ]]</span>
                                </el-form-item>
                                <el-form-item label="授权状态">
                                    <span style="color:coral">已授权</span>
                                </el-form-item>
                                <el-form-item label="key" prop="key">
                                    <el-input v-model="key" placeholder="请输入key" autocomplete="off"></el-input>
                                </el-form-item>

                                <el-form-item label="密钥">
                                    <el-input value="**************" placeholder="请输入密钥" autocomplete="off"></el-input>
                                </el-form-item>

                                <el-form-item prop="plugins" v-if="plugins != ''">
                                    <span slot="label">
                                        <span>已授权插件:</span>
                                    </span>
                                    <div v-for="(item, index, key) in plugins" :key="item.id">
                                        [[ index +1  ]]、[[ item.name]] <span style="color: #0b1eee">(已授权 [[ item.count ]] 个): </span> [[ item.text]]
                                </div>
                                </el-form-item>

                                <el-form-item prop="unauthorized" v-if="unauthorized != ''">
                                    <span slot="label">
                                        <span style="color:coral">未授权插件:</span>
                                    </span>
                                    <div style="color:coral">[[ unauthorized ]]</div>
                                    <div style="color: #ff0000; font-weight: bold">
                                        商城系统授权包含应用授权、插件授权两部分，使用未授权的商城系统应用或者插件都不具备合法性！我司保留对其使用系统停止升级、关闭、甚至对其媒体曝光和追究法律责任的起诉权利。
                                        <br>
                                        我国《中华人民共和国刑法》第二百一十七条规定： 以营利为目的，有下列侵犯著作权情形之一，违法所得 数额较大或者有其他严重情节的，处三年以下有期徒刑 或者拘役，并处或者单处罚金；违法所得数额巨大或者 有其他特别严重情节的，处三年以上七年以下有期徒刑， 并处罚金： （一）未经著作权人许可，复制发行其文字作品、音乐、 电影、电视、录像作品、计算机软件及其他作品的； （二）出版他人享有专有出版权的图书的； （三）未经录音录像制作者许可，复制发行其制作的录 音录像的； （四）制作、出售假冒他人署名的美术作品的。
                                    </div>
                                </el-form-item>
                            </el-form><!--auth end-->

                            <el-form ref="form" :model="form" :rules="rules" label-width="100px" class="demo-ruleForm" v-show="page=='free'">
                                <el-form-item label="当前域名" prop="domain">
                                    <span>[[ domain ]]</span>
                                </el-form-item>
                                <el-form-item label="授权状态">
                                    <span style="color:coral">未授权，请填写下方信息，提交后自动完成商城授权!</span>
                                </el-form-item>
                                <el-form-item label="公司名称" prop="name">
                                    <el-input v-model="form.name" placeholder="请输入公司名称" autocomplete="off"></el-input>
                                </el-form-item>
                                <el-form-item label="行业" prop="trades">
                                    <el-select v-model="form.trades" value-key="id" style="width:100%" placeholder="请选择行业">
                                        <el-option v-for="item in opt_trades.data"
                                                   :key="item.id"
                                                   :label="item.name"
                                                   :value="item.name">
                                        </el-option>
                                    </el-select>
                                </el-form-item>
                                <el-form-item label="所在区域" required>
                                    <el-col :span="4">
                                        <el-form-item prop="province">
                                            <el-select v-model="form.province" value-key="id" placeholder="省" @change="change_province">
                                                <el-option v-for="item in opt_province"
                                                           :key="item.id"
                                                           :label="item.areaname"
                                                           :value="item">
                                                </el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                    <el-col style="text-align: center" :span="1">-</el-col>
                                    <el-col :span="4">
                                        <el-form-item prop="city">
                                            <el-select v-model="form.city" value-key="id" placeholder="市" @change="change_city">
                                                <el-option v-for="item in opt_city"
                                                           :key="item.id"
                                                           :label="item.areaname"
                                                           :value="item">
                                                </el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                    <el-col style="text-align: center" :span="1">-</el-col>
                                    <el-col :span="4">
                                        <el-form-item prop="area">
                                            <el-select v-model="form.area" value-key="id" placeholder="区">
                                                <el-option v-for="item in opt_area"
                                                           :key="item.id"
                                                           :label="item.areaname"
                                                           :value="item">
                                                </el-option>
                                            </el-select>
                                        </el-form-item>
                                    </el-col>
                                </el-form-item>
                                <el-form-item label="详细地址" prop="address">
                                    <el-input v-model="form.address" placeholder="请输入详细地址" autocomplete="off"></el-input>
                                </el-form-item>
                                <el-form-item label="验证码" prop="captcha">
                                    <el-input v-model="form.captcha" style="width:150px" placeholder="请输入验证码"></el-input>
                                </el-form-item>
                                <el-form-item label="手机号" prop="mobile">
                                    <el-input placeholder="请输入手机号" v-model="form.mobile" style="width:200px" autocomplete="off"></el-input>
                                    <el-button type="info" @click="sendSms()" style="width:150px; margin-left: 50px" plain :disabled="isDisabled">[[captcha_text]]</el-button>
                                </el-form-item>

                                <el-form-item>
                                    <el-button type="primary" @click.native.prevent="onSubmit" :disabled="formLoading">提交</el-button>
                                </el-form-item>
                            </el-form><!--free end-->
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var app = new Vue({
            el: '#register',
            delimiters: ['[[', ']]'],
            data() {
                // 默认数据
                let domain = ({!!  $domain !!});
                let page = ({!!  $page !!});
                let province = ({!! $province !!});
                let set = ({!! $set !!});
                let unauthorized = ({!! $unauthorized !!});
                let plugins = ({!! $plugins !!});

                var validateMobile = (rule, value, callback) => {
                    if (!(/^1\d{10}$/.test(value))) {
                        callback(new Error('手机号格式不正确'));
                    } else {
                        callback();
                    }
                };

                return {
                    page: page.type,
                    form: {
                        name: '',
                        trades: '',
                        province: '',
                        city: '',
                        area: '',
                        address: '',
                        mobile: '',
                        captcha: ''
                    },
                    domain: domain.data,
                    unauthorized: unauthorized.data,
                    plugins: plugins.data,
                    key: set.key,
                    opt_trades: industry,
                    opt_province: province.data,
                    opt_city:'',
                    opt_area:'',
                    t: 60,
                    captcha_text: '获取验证码',
                    isDisabled: false,
                    formLoading: false,
                    rules: {
                        name: [
                            { required: true, message: '请输入公司名称', trigger: 'blur' }
                        ],
                        trades: [
                            { required: true, message: '请选择行业', trigger: 'change' }
                        ],
                        province: [
                            { required: true, message: '请选择省', trigger: 'change' }
                        ],
                        city: [
                            { required: true, message: '请选择市', trigger: 'change' }
                        ],
                        area: [
                            { required: true, message: '请选择区', trigger: 'change' }
                        ],
                        address: [
                            { required: true, message: '请输入详情地址', trigger: 'blur' }
                        ],
                        mobile: [
                            { required: true, message: '请输入手机号', trigger: 'blur' },
                            { validator: validateMobile, trigger: 'blur' }
                        ],
                        captcha: [
                            { required: true, message: '请输入验证码', trigger: 'blur' }
                        ]
                    }
                }
            },
            mounted: function () {
            },
            methods: {
                sendSms:function () {
                    let that = this;
                    let rTime = that.t;

                    if (!(/^1\d{10}$/.test(this.form.mobile))) {
                        this.$refs.form.validateField('mobile');
                        return false;
                    }

                    // 倒计时
                    let interval = window.setInterval(() => {
                        if (--that.t <= 0) {
                        that.t = rTime;
                        that.isDisabled = false;
                        that.captcha_text = '获取验证码';

                        window.clearInterval(interval);
                    } else {
                        that.isDisabled = true;
                        that.captcha_text = '(' + that.t + 's)后重新获取';
                    }
                }, 1000);

                    that.$http.post("{!! yzWebUrl('setting.key.sendSms') !!}", {'mobile': this.form.mobile}).then(response => {

                        if (response.data.result) {
                        this.$message({
                            message: response.data.data.msg,
                            type: 'success'
                        });
                    } else {
                        this.$message({
                            message: '未获取到数据',
                            type: 'error'
                        });
                    }

                }, response => {
                        console.log(response);
                    });
                },
                change_province: function (item) {
                    let that = this;
                    that.$http.post("{!! yzWebUrl('setting.key.getcity') !!}", {'data': item}).then(response => {

                        if (response.data.result) {
                        that.opt_city = response.data.data;
                    } else {
                        this.$message({
                            message: '未获取到数据',
                            type: 'error'
                        });
                    }
                }, response => {
                        console.log(response);
                    });
                },
                change_city: function (item) {
                    let that = this;
                    that.$http.post("{!! yzWebUrl('setting.key.getarea') !!}", {'data': item}).then(response => {
                        if (response.data.result) {
                        that.opt_area = response.data.data;
                    } else {
                        this.$message({
                            message: '未获取到数据',
                            type: 'error'
                        });
                    }
                }, response => {
                        console.log(response);
                    });
                },
                reg_shop: function (type) {
                    const loading = this.$loading({
                        lock: true,
                        text: '努力注册中',
                        spinner: 'el-icon-loading',
                        background: 'rgba(0, 0, 0, 0.7)'
                    });

                    this.$http.post("{!! yzWebUrl('setting.key.index') !!}", {'upgrade': {'key':this.key, 'secret': this.secret}, 'type': type}).then(response => {
                        loading.close();

                        if (response.data.result) {

                        this.$message({
                            message: response.data.msg,
                            type: 'success'
                        });
                        window.location = response.data.data.url;
                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    }
                }, response => {
                        loading.close();
                        console.log(response);
                    });
                },
                onSubmit: function () {
                    this.$refs.form.validate((valid) => {
                        if (valid) {
                            const loading = this.$loading({
                                lock: true,
                                text: '努力注册中',
                                spinner: 'el-icon-loading',
                                background: 'rgba(0, 0, 0, 0.7)'
                            });

                            this.$http.post("{!! yzWebUrl('setting.key.register') !!}", {'data': this.form}).then(response => {
                                loading.close();

                                if (response.data.result) {
                                this.$message({
                                    message: response.data.msg,
                                    type: 'success'
                                });
                                window.location = response.data.data.url;
                            } else {
                                this.$message({
                                    message: response.data.msg,
                                    type: 'error'
                                });
                            }
                        }, response => {
                                loading.close();
                                console.log(response);
                            });
                        } else {
                            return false;
                }
                });
                },
                tapclickPas(){
                      let data={
                        key:this.key,
                        secret:this.secret
                      }

                    this.$http.post("{!! yzWebUrl('setting.key.reset') !!}", {'data': data}).then(res => {
                                res=res.body
                        if (res.result==1) {
                        this.key = res.data.key;
                        this.secret = res.data.secret
                        this.$message({
                            message: res.msg,
                            type: 'success'
                        });
                        }
                    })
                }
            },
            watch: {
                'form.province': function (newValue, oldValue) {
                    this.form.city = null
                    this.opt_city = [{id:0,areaname:'请选择'}];
                    this.form.area = null
                    this.opt_area = [{id:0,areaname:'请选择'}];
                },
                'form.city': function (newValue, oldValue) {
                    this.form.area = null
                    this.opt_area = [{id:0,areaname:'请选择'}];
                }
            }
        });
    </script>
@endsection
