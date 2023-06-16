@extends('layouts.base')
@section('title', '会员详情')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>

        .content{
            background: #eff3f6;

        }
        .con{
            padding:20px 0;
            border-radius: 8px;
            position:relative;
        }
        .con  .block{
            padding: 10px;
            background-color:#fff;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .con  .block:last-child{
            margin-bottom: 60px;
        }
        .con  .block .title{
            font-size:18px;
            margin-bottom:15px;
            display:flex;
            align-items:center;
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
        .upload-boxed .el-icon-close {
            position: absolute;
            top: -5px;
            right: -5px;
            color: #fff;
            background: #333;
            border-radius: 50%;
            cursor: pointer;
        }
        b{
            font-size:14px;
        }
    </style>
    <!-- tab -->
    <div id='re_content'>
        <div class="con" >
            <el-form ref="form" :model="form" label-width="15%">
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基本信息</b></div>
                        <el-form-item label="粉丝">
                                <div class="upload-box" @click="openUpload('avatar','1','one')" v-if="!info.avatar" style="padding-left: 32px">
                                    <i class="el-icon-plus" style="font-size:32px"></i>
                                </div>
                                <div @click="openUpload('avatar','1','one')" class="upload-boxed" v-if="info.avatar" style="height:150px;padding-left: 32px">
                                    <img :src="info.avatar" alt="" style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                                    <div class="upload-boxed-text">点击重新上传</div>
                                </div>
                        </el-form-item>
                        <upload-multimedia-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" :type="type" @sure="sureImg"></upload-multimedia-img>
                        <el-form-item label="会员昵称">
                                <el-input v-model="form.nickname" style="margin-left:30px;width:60%;"></el-input>
                            <div style="margin-left:30px;">若开启微信授权登录，修改后会被微信的头像昵称替换</div>
                        </el-form-item>
                    <el-form-item label="会员ID">
                        <div style="margin-left:30px;">
                            [[info.uid]]
                        </div>
                    </el-form-item>
                    <el-form-item label="注册时间">
                        <div style="margin-left:30px;">
                            [[info.createtime]]
                        </div>
                    </el-form-item>

                    <el-form-item label="真实姓名">
                        <el-input v-model="form.realname" style="margin-left:30px;width:60%;"></el-input>
                    </el-form-item>
                    <el-form-item label="绑定手机">
                        <div style="margin-left:30px;display:inline-block">
                            [[info.mobile]]
                        </div>
                        <el-button @click="mobileShow">修改</el-button>
                        <el-button @click="getRecord">修改记录</el-button>
                    </el-form-item>
                    <el-form-item label="性别">
                        <div style="margin-left:30px;" v-if="info.gender=='1'">
                            男
                        </div>
                        <div style="margin-left:30px;" v-if="info.gender=='2'">
                            女
                        </div>
                        <div style="margin-left:30px;" v-if="info.gender=='0'">
                            未定义
                        </div>
                    </el-form-item>
                    <el-form-item label="生日">
                        <div style="margin-left:30px;">
                            {{--[[info.birthyear-info.birthmonth-info.birthday]]--}}
                            [[info.birthyear]]-[[info.birthmonth]]-[[info.birthday]]
                        </div>
                    </el-form-item>
                    <el-form-item label="所在地信息">
                        <div style="margin-left:30px;">
                            [[info.yz_member?info.yz_member.province_name:'']][[info.yz_member?info.yz_member.city_name:'']][[info.yz_member?info.yz_member.area_name:'']][[info.yz_member?info.yz_member.address:'']]
                        </div>
                    </el-form-item>
                </div>

                @foreach(\app\common\modules\widget\Widget::current()->getItem('member') as $key=>$value)
                    {!! widget($value['class'], ['id'=> $member['uid']])!!}
                @endforeach
                <div class="block" v-show="myform && myform.length">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>自定义会员资料信息</b></div>
                    <el-form-item :label="item.name" v-for="(item,index) in myform" :key="index">
                            <el-input v-model="item.value" style="margin-left:30px;width:60%;"></el-input>
                            {{--[[item.value]]--}}
                    </el-form-item>
                </div>
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>自定义字段信息</b></div>

                    <el-form-item :label="set.custom_title" >
                        <el-input v-model="form.custom_value" style="margin-left:30px;width:60%;"></el-input>
                    </el-form-item>

                </div>
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>微信信息</b></div>

                    <el-form-item label="微信号">
                        <el-input v-model="form.wechat" style="margin-left:30px;width:60%;"></el-input>
                    </el-form-item>
                    <el-form-item label="公众号关注状态">
                        <div style="margin-left:30px;" v-if="info.has_one_fans&&info.has_one_fans.followed==1">
                            已关注
                        </div>
                        <div style="margin-left:30px;" v-else>
                            未关注
                        </div>
                    </el-form-item>
                    <el-form-item label="公众号openid">
                        <div style="margin-left:30px;">
                            [[info.has_one_fans?info.has_one_fans.openid:'']]
                        </div>
                    </el-form-item>
                    <el-form-item label="小程序openid">
                        <div style="margin-left:30px;">
                            [[info.has_one_mini_app?info.has_one_mini_app.openid:'']]
                        </div>
                    </el-form-item>

                </div>
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>支付宝信息</b></div>

                    <el-form-item label="支付宝姓名">
                        <el-input v-model="form.alipayname" style="margin-left:30px;width:60%;"></el-input>
                    </el-form-item>
                    <el-form-item label="支付宝账号">
                        <el-input v-model="form.alipay" style="margin-left:30px;width:60%;"></el-input>
                    </el-form-item>

                </div>
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>会员等级/分组信息</b></div>

                    <el-form-item label="会员等级">
                        <template>
                            <el-select v-model="form.level_id" placeholder="请选择" style="margin-left:30px;">
                                <el-option
                                        v-for="(item,i) in levels"
                                        :label="item.level_name"
                                        :value="item.id" 
                                        :key="i">
                                        
                                </el-option>
                            </el-select>
                        </template>
                    </el-form-item>
                    <el-form-item label="会员等级期限">
                        <el-date-picker
                                v-model="validity_time"
                                type="date"
                                @change="changeDay"
                                placeholder="选择日期"
                                value-format="yyyy-MM-dd 23:59:59"
                                style="margin-left:30px;"
                        >
                        </el-date-picker>
                        <el-popover placement="bottom" width="200" trigger="click" v-model="changeDayNumShow">
                            <div style="text-align:center;">
                                <el-input v-model="validity_day" size="small" style="width:180px;" @input="changeDayNum"></el-input>
                                <!-- <el-button size="small" @click="changeDayNum">确定</el-button> -->
                            </div>
                            <i slot="reference" class="iconfont icon-ht_operation_edit" title="输入天数" style="cursor: pointer;"></i>
                        </el-popover>
                        <el-input v-model="validity_day" style="margin-left:30px;width:30%;" disabled>
                            <template slot="append">天</template>
                        </el-input>

                    </el-form-item>
                    <el-form-item label="会员分组">
                        <template>
                            <el-select v-model="form.group_id" placeholder="请选择" style="margin-left:30px;">
                                <el-option
                                        v-for="(item,i) in groups"
                                        :label="item.group_name"
                                        :value="item.id"
                                        :key="i">
                                </el-option>
                            </el-select>
                        </template>
                    </el-form-item>
                </div>
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>推广信息</b></div>

                    <el-form-item label="推广员">
                        <template >
                            <el-switch
                                    style="margin-left:30px;"
                                    v-model="form.agent"
                                    active-value="1"
                                    inactive-value="0"
                            >
                            </el-switch>
                        </template>
                    </el-form-item>
                    <el-form-item label="会员上线">
                        <div style="margin-left:30px;display:inline-block">
                            [ [[info.yz_member?info.yz_member.parent_id:'']] ] [[parent_name]]
                        </div>
                        <el-button @click="memberShow">修改</el-button>
                        <el-button @click="getParent">修改记录</el-button>
                    </el-form-item>
                    <el-form-item label="会员邀请码">
                        <el-input v-model="form.invite_code" style="margin-left:30px;width:30%;"></el-input>
                        <div style="margin-left:30px;">会员邀请码须8个字符</div>
                    </el-form-item>
                    <el-form-item label="提现手机">
                        <div style="margin-left:30px;width:60%;">[[info.yz_member?info.yz_member.withdraw_mobile:'']]</div>
                    </el-form-item>
                    <div class="confirm-btn">
                        <el-button type="primary" @click="submit">提交</el-button>
                    </div>
                </div>
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>其他信息</b></div>

                    <el-form-item label="积分">
                        <el-input v-model="info.credit1" style="margin-left:30px;width:30%;" disabled></el-input><a :href="'{{ yzWebFullUrl('point.recharge.index', array('id' => '')) }}'+[[info.uid]]"><el-button style="margin-left:10px;">充值</el-button></a>
                    </el-form-item>
                    <el-form-item label="余额">
                        <el-input v-model="info.credit2" style="margin-left:30px;width:30%;" disabled></el-input><a :href="'{{ yzWebFullUrl('balance.recharge.index', array('member_id' => '')) }}'+[[info.uid]]"><el-button style="margin-left:10px;">充值</el-button></a>
                    </el-form-item>
                    <el-form-item label="成交订单数">
                        <div style="margin-left:30px;">[[info.has_one_order?info.has_one_order.total:0]]</div>
                    </el-form-item>
                    <el-form-item label="成交金额" >
                        <div style="margin-left:30px;">[[info.has_one_order?info.has_one_order.sum:0]]</div>
                    </el-form-item>
                    <el-form-item label="黑名单">
                        <template>
                            <el-switch
                                    style="margin-left:30px;"
                                    v-model="form.is_black"
                                    active-value="1"
                                    inactive-value="0"
                            >
                            </el-switch>
                        </template>
                    </el-form-item>
                    <el-form-item label="备注" >
                        <el-input v-model="form.content" type="textarea" style="margin-left:30px;width:30%;"></el-input>
                    </el-form-item>
                </div>
            </el-form>
        </div>
        <el-dialog title="修改手机" :visible.sync="mobile_show" @close="closeMoblie">
            <el-input v-model="mobile"></el-input>
            <span slot="footer" class="dialog-footer">
            <el-button @click="mobile_show = false">取 消</el-button>
            <el-button type="primary" @click="mobileChoose()">确 定</el-button>
        </span>
        </el-dialog>
        <el-dialog title="修改记录" :visible.sync="mobile_record" @close="closeRecord">
            <el-table :data="record" style="width: 100%" >
                <el-table-column prop="member_id" label="会员id" align="center"></el-table-column>
                <el-table-column prop="mobile_before" label="修改前手机号" align="center"></el-table-column>
                <el-table-column prop="mobile_after" label="修改后手机号" align="center"></el-table-column>
                <el-table-column prop="created_at" label="修改时间" align="center"></el-table-column>
            </el-table>
        </el-dialog>
        <el-dialog title="修改记录" :visible.sync="parent_record" @close="closeParent">
            <el-table :data="parentRecord" style="width: 100%" >
                <el-table-column prop="parent_id" label="原上线ID" align="center"></el-table-column>
                <el-table-column prop="created_at" label="修改时间" align="center"></el-table-column>
            </el-table>
        </el-dialog>
        <el-dialog title="选择会员" :visible.sync="member_show" @close="closeMember">
            <div style="display:flex;justify-content:center;"><el-input v-model="member" style="width:80%;margin-right:20px;" placeholder="请输入会员ID搜索"></el-input><el-button @click="getMember" >搜索</el-button></div>
            <template>
                <el-table
                        :data="list"
                        style="width: 100%">
                    <el-table-column
                            prop="nickname"
                            align="center"
                            label="头像"
                    >
                        <template slot-scope="scope" v-if="scope.row.avatar_image">
                            <img :src="scope.row.avatar_image" style="width:20px;height:20px;">
                        </template>
                    </el-table-column>
                    <el-table-column
                            prop="nickname"
                            align="center"
                            label="用户名"
                    >
                    </el-table-column>
                    <el-table-column
                            prop="mobile"
                            align="center"
                            label="手机"
                    >
                    </el-table-column>
                    <el-table-column
                            prop="mobile"
                            align="center"
                            label="操作"
                    >
                        <template slot-scope="scope">
                            <el-button @click="memberChoose" size="mini">选择</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </template>
        </el-dialog>
    </div>
    </div>
    @include('public.admin.uploadMultimediaImg')
    <script>

        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                let member = {!! json_encode($member)?:'{}' !!}
                    let levels = {!! json_encode($levels)?:'{}' !!}
                    let groups={!! json_encode($groups)?:'{}' !!}
                    let parent_name={!! json_encode($parent_name)?:'{}' !!}
                    let myform={!! json_encode($myform)?:'{}' !!}
                    let set={!! json_encode($set)?:'{}' !!}

                    return {
                        uploadShow:false,
                        chooseImgName:'',
                        record:[],
                        parentRecord:[],
                        parent_record:false,
                        mobile_record:false,
                        set:set,
                        myform:myform,
                        parent_name:parent_name,
                        list:[],
                        mobile_show:false,
                        member_show:false,
                        mobile:'',
                        member:'',
                        form:{
                            id:member.uid,
                            level_id:member.yz_member?member.yz_member.level_id:0,
                            group_id:member.yz_member?member.yz_member.group_id:0,
                            validity:member.yz_member?member.yz_member.validity:'',
                            realname:member.realname,
                            nickname:member.nickname,
                            avatar:member.avatar,
                            wechat:member.yz_member?member.yz_member.wechat:'',
                            alipayname:member.yz_member?member.yz_member.alipayname:'',
                            alipay:member.yz_member?member.yz_member.alipay:'',
                            is_black:member.yz_member?String(member.yz_member.is_black):'0',
                            agent:String(member.agent),
                            invite_code:member.yz_member?member.yz_member.invite_code:'',
                            content:member.yz_member?member.yz_member.content:'',
                            custom_value:member.yz_member?member.yz_member.custom_value:'',
                        },
                        info:member,
                        levels:levels,
                        groups:groups,
                        validity_time:'',//有限期
                        validity_day:'',
                        changeDayNumShow:false,
                        type:'',
                        selNum:'',
                    }
            },
            created () {
                this.levels.unshift({
                    id:0,
                    level:0,
                    level_name:this.set.level_name
                })
                this.groups.unshift({
                    id:0,
                    group_name:'无分组'
                })
                //   会员有效期
                this.validity_day = this.form.validity;
                if(this.form.validity==0) {
                    this.validity_time = this.timeStyle(new Date().getTime()-24*60*60*1000)
                }
                else if(this.form.validity==1) {
                    this.validity_time = this.timeStyle(new Date().getTime())
                }
                else {
                    this.validity_time = this.timeStyle(new Date().getTime()+((Number(this.form.validity)-1)*24*60*60*1000))
                }

            },
            methods: {
                openUpload(str,type,sel) {
                    this.chooseImgName = str;
                    this.uploadShow = true;
                    this.type = type;
                    this.selNum = sel;
                },
                changeProp(val) {
                    if(val == true) {
                        this.uploadShow = false;
                    }
                    else {
                        this.uploadShow = true;
                    }
                },
                sureImg(name,uploadShow,fileList) {
                    if(fileList.length <= 0) {
                        return
                    }
                    this.form[name] = fileList[0].url;
                    this.info[name] = fileList[0].url;
                    this.info[name+'_image'] = fileList[0].url;
                },
                getParent(){
                    this.parent_record=true;
                    this.$http.post("{!! yzWebUrl('member.member.member_record') !!}",{uid:this.info.uid}).then(response => {
                        if (response.data.result) {
                            this.parentRecord=response.data.data.records
                        }else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    }, response => {
                        this.$message({type: 'error',message: response.data.msg});
                        console.log(response);
                    });
                },
                getRecord(){
                    this.mobile_record=true;
                    this.$http.post("{!! yzWebUrl('member.member.changeMobileLog') !!}",{uid:this.info.uid}).then(response => {
                        if (response.data.result) {
                            this.record=response.data.data.list
                        }else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    }, response => {
                        this.$message({type: 'error',message: response.data.msg});
                        console.log(response);
                    });
                },
                getInfo(){
                    this.$http.get("{!! yzWebUrl('member.member.detail') !!}"+'&id='+this.info.uid+'&type='+1).then(response => {
                        if (response.data.result) {
                            this.info=response.data.data.member
                            this.parent_name=response.data.data.parent_name
                        }else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    }, response => {
                        this.$message({type: 'error',message: response.data.msg});
                        console.log(response);
                    });
                },
                getMember(){
                    this.list=[];
                    this.$http.post("{!! yzWebUrl('member.member.search-member') !!}",{parent:this.member}).then(response => {
                        if (response.data.result) {
                            this.list=response.data.data.members
                        }else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    }, response => {
                        this.$message({type: 'error',message: response.data.msg});
                        console.log(response);
                    });
                },
                memberChoose(){
                    this.$http.post("{!! yzWebUrl('member.member.change_relation') !!}",{member:this.info.uid,parent:this.list[0].uid}).then(response => {
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                            this.getInfo()
                            this.member_show=false;
                        }else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    }, response => {
                        this.$message({type: 'error',message: response.data.msg});
                        console.log(response);
                    });
                },
                memberShow(){
                    this.member_show=true;
                },
                closeMember(){
                    this.member_show=false;

                },
                mobileChoose(){
                    this.$http.post("{!! yzWebUrl('member.member.changeMobile') !!}",{mobile:this.mobile,uid:this.info.uid}).then(response => {
                        if (response.data.result) {
                            this.$message({message: "修改成功",type: 'success'});
                            this.getInfo()
                            this.mobile_show=false;
                        }else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    }, response => {
                        this.$message({type: 'error',message: response.data.msg});
                        console.log(response);
                    });
                },

                mobileShow(){
                    this.mobile_show=true;
                },
                closeMoblie(){
                    this.mobile_show=false;
                },
                closeRecord(){
                    this.mobile_record=false;
                },
                closeParent(){
                    this.parent_record=false;
                },
                submit(){
                    if(this.validity_time) {
                        let times = new Date(this.validity_time).getTime();
                        let today = new Date().getTime();
                        let day = '';
                        if(times>today) {
                            day = times-today;
                            console.log(day)
                            if(day>=0) {
                                day = Math.ceil(day/1000/60/60/24)
                            }
                        }
                        this.form.validity = day;
                    }
                    this.$http.post("{!! yzWebUrl('member.member.update') !!}",{id:this.form.id,member:this.form,myform:this.myform}).then(response => {
                        if (response.data.result) {
                            this.$message({message: "提交成功",type: 'success'});
                        }else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    }, response => {
                        this.$message({type: 'error',message: response.data.msg});
                        console.log(response);
                    });
                },
                changeDay(val) {
                    console.log(val)
                    let times = new Date(this.validity_time).getTime();
                    let today = new Date().getTime();
                    let day = '';
                    if(times>today) {
                        day = times-today;
                        console.log(day)
                        if(day>=0) {
                            day = Math.ceil(day/1000/60/60/24)
                        }
                    }

                },
                changeDayNum() {
                    if(!(/(^[0-9]\d*$)/.test(this.validity_day))) {
                        this.$message.error('请输入正确数字')
                        return
                    }
                    this.validity_time = this.timeStyle(new Date().getTime()+((Number(this.validity_day)-1)*24*60*60*1000))
                },
                add0(m) {
                    return m<10?'0'+m:m
                },
                timeStyle(time) {
                    console.log(time)
                    let time1 = new Date(time);
                    let y = time1.getFullYear();
                    let m = time1.getMonth()+1;
                    let d = time1.getDate();
                    let h = time1.getHours();
                    let mm = time1.getMinutes();
                    let s = time1.getSeconds();
                    // return y+'-'+this.add0(m)+'-'+this.add0(d)+' '+this.add0(h)+':'+this.add0(mm)+':'+this.add0(s);
                    return y+'-'+this.add0(m)+'-'+this.add0(d)+' '+'23:59:59';
                },
            },
        });
    </script>

@endsection
