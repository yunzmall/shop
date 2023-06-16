<div class="yz-menu">
    <i class="fa fa-bars" style="color:#333"></i>
</div>
<div class="yz-menu-header">
    <nav class="navbar navbar-transparent navbar-absolute" style="display: flex;flex-direction: row-reverse;height: 52px;">
        <!-- <div class="container-fluid"> -->
            <div class="navbar-minimize" style="margin:0;padding:0;margin-left:20px;">
            {{--<h4>{{YunShop::app()->account['name']}}</h4>--}}
{{--                <img src="{{\Setting::get('shop.shop.logo') ?request()->getSchemeAndHttpHost().'/attachment/'.\Setting::get('shop.shop.logo'): '/addons/yun_shop/static/resource/images/nopic.jpg'}}" --}}
                {{--style="width:80px;height:50px;margin:0;padding:0">--}}
{{--                <h4>{{YunShop::app()->account['name']}}</h4>--}}
            </div>
            <div class="collapse navbar-collapse" style="float:right">
                <ul class="nav navbar-nav navbar-right" style="display: flex;">
                    <li>
{{--                        @if (!YunShop::isRole())--}}
                            <a href="#pablo" class="dropdown-toggle" data-toggle="dropdown" style="display: flex;">
                                <i class="material-icons" style="float: left;">dashboard</i>
                                <p class="" style="float: left;">{{YunShop::app()->account['name']}}</p>
                            </a>

                            @if(YunShop::app()->role)
                                <ul class="dropdown-menu">
                                    @if (config('app.framework') != 'platform')
                                        @if(YunShop::app()->role !='operator')
                                            <li class="about"> <i></i><a href="?c=account&a=post&uniacid={{YunShop::app()->uniacid}}&acid={{YunShop::app()->uniacid}}"> <span class="fa fa-wechat"></span>编辑当前账号资料</a> </li>
                                        @endif
                                        <li> <a href="?c=account&a=display&"><span class="fa fa-cogs fa-fw"></span>管理其他公众号</a> </li>
                                        <li> <a target="_blank" href="?c=utility&a=emulator&"><span class="fa fa-mobile fa-fw"></span>模拟测试</a> </li>
                                    @else
                                        @if(YunShop::app()->role != 'operator' && YunShop::app()->role != 'clerk')
                                            <li> <a href="/admin.html#/manage/index"><span class="fa fa-cogs fa-fw"></span>管理其他公众号</a> </li>
                                        @endif
                                        <li onclick="openPwd()"> <a ><span class="fa fa-edit fa-fw"></span>个人信息</a> </li>
                                    @endif
                                    @if(config('app.env') != 'production')
                                        <li> <a target="_blank" href="{{yzWebUrl('menu.index')}}"><span class="fa fa-align-justify fa-fw"></span>菜单管理</a></li>
                                    @endif
                                    {{--<li> <a target="_blank" href="{{yzWebUrl('member.member.updateWechatOpenData')}}"><span class="fa fa-cube fa-fw"></span>微信开放平台数据同步</a></li>--}}
                                    <li> <a target="_self" href="{{yzWebUrl('cache.update')}}" onclick="return confirm('确认更新缓存？');return false;"><span class="fa fa-refresh fa-fw"></span>更新缓存</a></li>
                                    <!-- <li> <a href="{{yzWebUrl('setting.shop.entry')}}"> <span class="fa fa-camera-retro fa-fw"></span>商城入口 </a>  </li> -->
                                </ul>
                            @endif
{{--                        @endif--}}
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" style="display: flex;">
                            <i class="material-icons" style="float:left">person</i>
                            {{--<span class="notification">5</span>--}}
                            <p class="" style="float:left">
                                {{YunShop::app()->username}}(@if(YunShop::app()->role == 'founder')系统管理员@elseif(YunShop::app()->role =='manager')公众号管理员@else公众号操作员@endif)
                                <b class="caret"></b>
                            </p>
                        </a>
                        <ul class="dropdown-menu">
                            @if (config('app.framework') != 'platform')
                                <li class="about"> <i></i> <a href="?c=user&a=profile&do=profile&"> <span class="fa fa-wechat fa-fw"></span>我的账号</a> </li>
                                @if(YunShop::app()->role == 'founder')
                                    <li class="system one"> <a href="{{yzWebFullUrl('setting.key.index')}}"><span class="fa fa-key fa-fw"></span>商城授权</a> </li>
                                    <li class="system one"> <a href="?c=system&a=welcome&"><span class="fa fa-sitemap fa-fw"></span>系统选项</a> </li>
                                    <li class="system"> <a href="?c=system&a=welcome" target="_blank"><span class="fa fa-cloud-download fa-fw"></span>自动更新</a> </li>
                                    <li class="system three"> <a href="?c=system&a=updatecache&" target="_blank"><span class="fa fa-refresh fa-fw"></span>更新缓存</a> </li>
                                @endif
                            @endif

                            @if (config('app.framework') == 'platform')
                                @if(YunShop::app()->role == 'founder')
                                    <li class="system one"> <a href="{{yzWebFullUrl('setting.key.index')}}"><span class="fa fa-key fa-fw"></span>商城授权</a> </li>
                                @endif
                                <li class="drop_out"> <a href="javascript:void(0)" id="sys_logout"><span class="fa fa-sign-out fa-fw"></span>退出系统</a> </li>
                            @else
                                <li class="drop_out"> <a href="?c=user&a=logout"><span class="fa fa-sign-out fa-fw"></span>退出系统</a> </li>
                            @endif
                        </ul>

                    </li>
                    {{--<li>
                        <a href="#pablo" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="material-icons">person</i>
                            <p class="hidden-lg hidden-md">Profile</p>
                        </a>
                    </li>--}}
                    <li class="separator hidden-lg hidden-md"></li>
                </ul>
                {{--<form class="navbar-form navbar-right" role="search">
                    <div class="form-group form-search is-empty">
                        <input type="text" class="form-control" placeholder="Search">
                        <span class="material-input"></span>
                    </div>
                    <button type="submit" class="btn btn-white btn-round btn-just-icon">
                        <i class="material-icons">search</i>
                        <div class="ripple-container"></div>
                    </button>
                </form>--}}
            </div>
            <div class="navbar-header" style="width:100%">
                {{--<button type="button" class="navbar-toggle" data-toggle="collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>--}}
                <ul class="clearfix pull-left" style="margin-bottom: 0em;">
                    {{--<li class=" active" style="">
                        <a ui-sref="shop.dashboard" href="/shop">商城</a>
                    </li>--}}
                    @foreach(\app\backend\modules\menu\Menu::current()->getItems() as $key=>$value)

                        @if(isset($value['menu']) && $value['menu'] == 1 && can($key) && ($value['top_show'] == 1 || app('plugins')->isTopShow($key)))

                            @if(isset($value['child']) && array_child_kv_exists($value['child'],'menu',1))

                                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getItems()) ? 'active' : ''}}">
                                    <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                        <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                        {{--<span class="pull-right-container">--}}
                                        {{--<i class="fa fa-angle-left pull-right"></i>--}}
                                        {{--</span>--}}
                                        {{$value['name']}}
                                    </a>
                                    {{--@include('layouts.childMenu',['childs'=>$value['child'],'item'=>$key])--}}
                                </li>
                            @elseif($value['menu'] == 1)
                                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getItems()) ? 'active' : ''}}">
                                    <a href="{{isset($value['url']) ? yzWebFullUrl($value['url']):''}}{{$value['url_params'] ?? ''}}">
                                        <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                        {{$value['name'] ?? ''}}
                                    </a>
                                </li>
                            @endif
                        @endif
                    @endforeach


                </ul>
            </div>

        <!-- </div> -->
    </nav>
</div>
<div id="change_pwd_all_diag">
    <el-dialog title="修改个人信息" :visible.sync="change_pwd_all_show">
        <el-form ref="form" :model="form" :rules="rules" label-width="20%">
            <el-form-item label="用户名" prop="username">
            <el-input
                style="width: 80%"
                type="text"
                v-model="form.username"
                disabled
            ></el-input>
            </el-form-item>
            <el-form-item label="手机号" prop="mobile">
            <el-input
                style="width: 80%"
                type="text"
                v-model="mobile"
                disabled
            ></el-input>
            </el-form-item>
            <el-form-item label="原密码" prop="old_password">
                <el-input
                    style="width: 80%"
                    type="text"
                    v-model="form.old_password"
                ></el-input>
                <!-- <div class="tip">不填则不修改密码</div> -->
            </el-form-item>
            <el-form-item label="新密码" prop="new_pass">
                <el-input
                    style="width: 80%"
                    type="text"
                    v-model="form.new_pass"
                ></el-input>
            </el-form-item>
            <el-form-item
                label="确认新密码"
                prop="re_password"
            >
                <el-input
                    style="width: 80%"
                    type="text"
                    v-model="form.re_password"
                ></el-input>
            </el-form-item>
            <!-- <el-form-item >
                密码至少为8-16个字符,至少1个大写字母，1个小写字母和一个数字，其他可以任意字符
            </el-form-item> -->
        </el-form>
        <span slot="footer" class="dialog-footer">
            <el-button @click="change_pwd_all_show = false">取 消</el-button>
            <el-button type="primary" @click="changePwd()">确 定</el-button>
        </span>
    </el-dialog>
</div>
<script>
    $(function () {
        $("#sys_logout").click(function () {
            $.get("/admin/logout",function(data,status){
                location.href = '/';
            });
        });
    });
    function openPwd() {
        change_pwd_all_diag.change_pwd_all_show = true
    }
    var change_pwd_all_diag = new Vue({
        el: "#change_pwd_all_diag",
        delimiters: ['[[', ']]'],
        data() {
            let validatePass2 = (rule, value, callback) => {
                if (value === "") {
                    callback(new Error("请再次输入密码"));
                } else if (value !== this.form.new_pass) {
                    callback(new Error("两次输入密码不一致!"));
                } else {
                    callback();
                }
            };
            return {
                mobile:'',
                change_pwd_all_show:false,
                form:{

                },
                rules:{
                    old_password: [{ required: true, trigger: "blur", message: "请输入原密码" }],
                    new_pass: [{ required: true, trigger: "blur", message: "请输入密码" }],
                    re_password: [
                        { validator: validatePass2, required: true, trigger: "blur" }
                    ]
                }
            }
        },
        watch:{
            change_pwd_all_show(value) {
                if(value) {
                    this.getInfo();
                }
            }
        },
        created () {



        },
        methods: {
            getInfo() {
                this.$http.post('{!! yzWebFullUrl('user.user.getAdminUserInfo') !!}', {}).then(response => {
                    console.log( response.data)

                    if (response.data.result === 1) {
                        this.form.username = response.data.data.username;
                        this.mobile = response.data.data.mobile;
                        console.log( response.data)

                    } else {

                        this.$message.error(response.data.msg);
                    }
                })
            },
            changePwd() {
                let that = this;
                this.$refs["form"].validate(valid => {
                    if (valid) {
                        this.$http.post('{!! yzWebFullUrl('user.user.resetPassword') !!}', { old_password:this.form.old_password,username: this.form.username,new_pass:this.form.new_pass }).then(response => {
                            if (response.data.result === 1) {
                                this.$message.success("修改成功!");
                                that.change_pwd_all_show = false;
                                setTimeout(()=> {
                                    that.logout();
                                },2000)
                            } else {
                                this.$message.error(response.data.msg);
                            }
                            }).catch(err => {
                                console.error(err);
                        });
                    }
                });
            },
            logout() {
                this.$http.get("/admin/logout", {}).then(response => {
                    location.href = '/';
                })
            }

        },
    });
</script>
