@extends('layouts.base')

@section('content')
    <style>
        .panel {
            margin-bottom: 10px !important;
            padding-left: 20px;
            border-radius: 10px;
        }

        .panel .active a {
            background-color: #29ba9c !important;
            border-radius: 18px !important;
            color: #fff;
        }

        .panel a {
            border: none !important;
            background-color: #fff !important;
        }

        .content {
            background: #eff3f6;
            padding: 10px !important;
        }

        .con {
            padding-bottom: 20px;
            position: relative;
            border-radius: 8px;
            min-height: 100vh;
            background-color: #fff;
        }

        .con .setting .block {
            padding: 10px;
            background-color: #fff;
            border-radius: 8px;
        }

        .con .setting .block .title {
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .confirm-btn {
            width: calc(100% - 266px);
            position: fixed;
            bottom: 0;
            right: 0;
            margin-right: 10px;
            line-height: 63px;
            background-color: #ffffff;
            box-shadow: 0px 8px 23px 1px rgba(51, 51, 51, 0.3);
            background-color: #fff;
            text-align: center;
        }

        b {
            font-size: 14px;
        }
    </style>
    <div id='re_content'>
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <el-form ref="form" label-width="15%">
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>支付密码</b>
                        </div>
                        <el-form-item label="支付密码">
                            <el-switch v-model="payPasswordTurnedon" active-value="1" inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="多位数密码">
                            <el-switch v-model="payPasswordMultiple" active-value="1" inactive-value="0">
                            </el-switch>
                            <div class="tip">
                                系统默认6位数字+安全键盘。开启后可填多位(字母+数字+字符)且不使用安全键盘。<br>由于不使用安全键盘可能产生的密码安全问题，强烈建议不开启
                            </div>
                        </el-form-item>
                        <el-form-item v-for="(conditionGroup,groupKey) in condition" :key="conditionGroup.code" :label="conditionGroup.name">
                            <template>
                                <el-checkbox-group v-model="selectedCondition[groupKey]" text-color="#29ba9c">
                                    <el-checkbox :label="item.key" v-for="(item,index,key) in conditionGroup.condition">[[item.name]]</el-checkbox>
                                </el-checkbox-group>
                            </template>
                        </el-form-item>
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>二次校验</b>
                        </div>
                        <el-form-item label="二次校验场景">
                            <el-checkbox v-model="is_phone_verify" :true-label="1" :false-label="0" @change="changeSwitch('is_phone_verify')">提现打款</el-checkbox>
                            <el-checkbox v-model="is_member_export_verify" :true-label="1" :false-label="0" @change="changeSwitch('is_member_export_verify')">会员导出（含全部、团队、直推会员）</el-checkbox>
                            <el-checkbox v-model="is_commission_export_verify" :true-label="1" :false-label="0" @change="changeSwitch('is_commission_export_verify')">分销商导出（含全部、推广粉丝导出）</el-checkbox>
                        </el-form-item>
                        <el-form-item label="验证手机号">
                            <span>[[phone]]</span>
                            <el-button size="mini" type="primary" @click="setPhone('phone_show')" v-if="is_set_phone==0">设置手机号</el-button>
                            <el-button size="mini" type="primary" @click="setPhone('phone_edit')" v-if="is_set_phone==1">更改手机号</el-button>
                            <div class="tip">
                                设置后修改需要原手机号验证码，请勿使用临时手机号!
                            </div>
                        </el-form-item>
                        <el-form-item label="验证有效期">
                            <el-input v-model="verify_expire" style="width:20%;"></el-input><span style="margin-left:10px;">分钟</span>
                            <div class="tip">
                                验证成功后，在验证有效期内，无需重复验证，默认10分钟，最长可设置不超过120分钟。
                            </div>
                        </el-form-item>
                        <div class="confirm-btn">
                            <el-button type="primary" @click="submit">提交</el-button>
                        </div>
                    </div>
                </el-form>

                <el-dialog :visible.sync="phone_show" width="450px" center title="设置验证手机号">
                    <div>
                        <el-form ref="form1" label-width="15%">
                            <el-form-item label="手机号">
                                <el-input v-model="form1.phone" style="width:60%" placeholder="请输入手机号"></el-input>
                                <el-button size="small" type="primary" @click="getVerifyCode('set_phone')" :disabled="form1.verify_code_disabled">
                                    获取验证码[[form1.last_time]]
                                </el-button>
                            </el-form-item>
                            <el-form-item label="验证码">
                                <el-input v-model="form1.verify_code" style="width:60%" placeholder="请输入短信验证码"></el-input>
                            </el-form-item>
                            <div style="text-align: center">
                                <el-button type="primary" @click="submitVerify('phone_show')">提交</el-button>
                                <el-button type="danger" @click="cancel('phone_show')">取消</el-button>
                            </div>
                        </el-form>
                    </div>
                </el-dialog>

                <el-dialog :visible.sync="phone_edit" width="450px" center title="修改验证手机号">
                    <div>
                        <el-form ref="form1" label-width="20%">
                            <el-form-item label="手机号">
                                <span style="display:inline-block;width:60%">[[phone]]</span>
                                <el-button size="small" type="primary" @click="getVerifyCode('edit_phone')" :disabled="form2.verify_code_disabled">
                                    获取验证码[[form2.last_time]]
                                </el-button>
                            </el-form-item>
                            <el-form-item label="验证码">
                                <el-input v-model="form2.verify_code" style="width:60%" placeholder="请输入短信验证码"></el-input>
                            </el-form-item>
                            <el-form-item label="新手机号">
                                <el-input v-model="form2.phone" style="width:60%" placeholder="请输入手机号"></el-input>
                                <el-button size="small" type="primary" @click="getVerifyCode('edit_phone_new')" :disabled="form2.verify_code_disabled_new">
                                    获取验证码[[form2.last_time_new]]
                                </el-button>
                            </el-form-item>
                            <el-form-item label="验证码">
                                <el-input v-model="form2.verify_code_new" style="width:60%" placeholder="请输入短信验证码"></el-input>
                            </el-form-item>
                            <div style="text-align: center">
                                <el-button type="primary" @click="submitVerify('phone_edit')">提交</el-button>
                                <el-button type="danger" @click="cancel('phone_edit')">取消</el-button>
                            </div>
                        </el-form>
                    </div>
                </el-dialog>

                <el-dialog :visible.sync="phone_close" width="450px" center title="关闭场景时二次校验短信验证">
                    <div>
                        <el-form ref="form1" label-width="20%">
                            <el-form-item label="手机号">
                                <span style="display:inline-block;width:60%">[[phone]]</span>
                                <el-button size="small" type="primary" @click="getVerifyCode('close_phone')" :disabled="form3.verify_code_disabled">
                                    获取验证码[[form3.last_time]]
                                </el-button>
                            </el-form-item>
                            <el-form-item label="验证码">
                                <el-input v-model="form3.verify_code" style="width:60%" placeholder="请输入短信验证码"></el-input>
                            </el-form-item>
                            <div style="text-align: center">
                                <el-button type="primary" @click="submitVerify('phone_close')">提交</el-button>
                                <el-button type="danger" @click="cancel('phone_close')">取消</el-button>
                            </div>
                        </el-form>
                    </div>
                </el-dialog>
            </div>
        </div>
        {!! json_encode($setting) !!}
    </div>
    <script>
        new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                let condition = JSON.parse(`{!! json_encode($condition) !!}`);
                const setting = JSON.parse(`{!! json_encode($setting) !!}`);
                const withdraw = JSON.parse(`{!! json_encode($withdraw_verify) !!}`);
                const selectedCondition = {};
                let payPasswordTurnedon=0;
                let payPasswordMultiple=0;
                // if (Array.isArray(setting)) {
                //     for (const key in condition) {
                //         if (Object.hasOwnProperty.call(condition, key)) {
                //             const element = condition[key];
                //             if (!selectedCondition[element['code']]) {
                //                 selectedCondition[element['code']] = [];
                //             }
                //         }
                //     }
                // } else {
                //     payPasswordTurnedon = setting['pay_state'];
                //     payPasswordMultiple = setting['pay_multiple'];
                //     delete setting['pay_state'];
                //     Object.assign(selectedCondition, setting);
                // }

                for (const key in condition) {
                    if (Object.hasOwnProperty.call(condition, key)) {
                        const element = condition[key];
                        if (!selectedCondition[element['code']]) {
                            selectedCondition[element['code']] = [];
                        }
                    }
                }

                payPasswordTurnedon = setting['pay_state'];
                payPasswordMultiple = setting['pay_multiple'];
                delete setting['pay_state'];
                Object.assign(selectedCondition, setting);

                return {
                    condition,
                    selectedCondition,
                    payPasswordTurnedon,
                    payPasswordMultiple,

                    phone_show:false,
                    phone_edit:false,
                    phone_close:false,
                    is_set_phone:withdraw&&withdraw.is_set_phone?withdraw.is_set_phone:0,
                    is_phone_verify:withdraw&&withdraw.is_phone_verify?withdraw.is_phone_verify:0,
                    is_member_export_verify:withdraw&&withdraw.is_member_export_verify?withdraw.is_member_export_verify:0,
                    is_commission_export_verify:withdraw&&withdraw.is_commission_export_verify?withdraw.is_commission_export_verify:0,
                    phone:withdraw&&withdraw.phone?withdraw.phone:"",
                    verify_expire:withdraw&&withdraw.verify_expire?withdraw.verify_expire:"",
                    is_verify:0,//判断此次验证过手机没有
                    change_item:"",
                    timer1:null,
                    timer2:null,
                    timer3:null,
                    timer4:null,
                    form1:{
                        phone:'',
                        verify_code:'',
                        verify_code_disabled:false,
                        last_time:"",
                    },
                    form2:{
                        phone:'',
                        verify_code:'',
                        verify_code_disabled:false,
                        last_time:"",
                        verify_code_new:'',
                        verify_code_disabled_new:false,
                        last_time_new:"",
                    },
                    form3:{
                        verify_code:'',
                        verify_code_disabled:false,
                        last_time:"",
                    },
                }
            },
            methods: {
                submit() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    let formData = JSON.parse(JSON.stringify(this.selectedCondition));
                    formData['pay_state'] = this.payPasswordTurnedon
                    formData['pay_multiple'] = this.payPasswordMultiple
                    let withdraw_verify = {
                        is_phone_verify : this.is_phone_verify,
                        is_member_export_verify : this.is_member_export_verify,
                        is_commission_export_verify : this.is_commission_export_verify,
                        phone : this.phone,
                        verify_expire : this.verify_expire,
                        form1 : this.form1,
                        form2 : this.form2,
                        form3 : this.form3,
                    }
                    this.$http.post("{!! yzWebFullUrl('password.setting.index') !!}", {
                        pay_password: formData,
                        withdraw_verify: withdraw_verify,
                    }).then(function(response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }
                        loading.close();
                    }, function(response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    })
                },
                setPhone(type) {
                    if (type == 'phone_show') {
                        this.phone_show = true;
                    } else if (type == 'phone_edit') {
                        this.phone_edit = true;
                    } else if (type == 'phone_close') {
                        this.phone_close = true;
                    }
                },
                cancel(type) {
                    if (type == 'phone_show') {
                        this.phone_show = false;
                    } else if (type == 'phone_edit') {
                        this.phone_edit = false;
                    } else if (type == 'phone_close') {
                        this.phone_close = false;
                        this[this.change_item] = 1;
                    }
                },
                submitVerify(type) {
                    let loading = this.$loading({target: document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    let json;
                    if (type == 'phone_show') {
                        json = {
                            type : 1,
                            phone : this.form1.phone,
                            code : this.form1.verify_code,
                        };
                    } else if (type == 'phone_edit') {
                        json = {
                            type : 2,
                            oldCode : this.form2.verify_code,
                            phone : this.form2.phone,
                            code : this.form2.verify_code_new,
                        };
                    } else if (type == 'phone_close') {
                        json = {
                            type : 3,
                            code : this.form3.verify_code,
                        };
                    } else {
                        this.$message({message: '类型错误',type: 'error'});
                        return;
                    }
                    this.$http.post("{!! yzWebFullUrl('password.setting.verifyWithdrawCode') !!}",json).then(function(response) {
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                            if (type == 'phone_show') {
                                this.phone = this.form1.phone;
                                this.phone_show = false;
                            } else if (type == 'phone_edit') {
                                this.phone = this.form2.phone;
                                this.phone_edit = false;
                            } else if (type == 'phone_close') {
                                this.phone_close = false;
                            }
                            this.is_verify = 1;
                        } else {
                            if (type == 'phone_close') {
                                this.phone_close = false;
                                console.log(this.change_item);
                                this[this.change_item] = 1;
                            }
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                    }, function(response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    })
                },
                getVerifyCode(type) {
                    let json;
                    if (type == 'set_phone') {
                        if (!this.form1.phone) {
                            this.$message({message: '请填写手机号',type: 'error'});
                            return;
                        }
                        json = {
                            type : 1,
                            phone : this.form1.phone,
                        };
                        if (this.timer1) {
                            return ;
                        } else {
                            this.form1.verify_code_disabled = true;
                            this.form1.last_time = 60;
                            this.timer1 = setInterval(() => {
                                if (this.form1.last_time > 0 && this.form1.last_time <= 60) {
                                    this.form1.last_time--;
                                } else {
                                    this.form1.verify_code_disabled  = false;
                                    this.form1.last_time = "";
                                    clearInterval(this.timer1);
                                    this.timer1 = null;
                                }
                            }, 1000)
                        }
                    } else if (type == 'edit_phone') {
                        json = {type : 2};
                        if (this.timer2) {
                            return ;
                        } else {
                            this.form2.verify_code_disabled = true;
                            this.form2.last_time = 60;
                            this.timer2 = setInterval(() => {
                                if (this.form2.last_time > 0 && this.form2.last_time <= 60) {
                                    this.form2.last_time--;
                                } else {
                                    this.form2.verify_code_disabled  = false;
                                    this.form2.last_time = "";
                                    clearInterval(this.timer2);
                                    this.timer2 = null;
                                }
                            }, 1000)
                        }
                    } else if (type == 'edit_phone_new') {
                        if (!this.form2.phone) {
                            this.$message({message: '请填写新手机号',type: 'error'});
                            return;
                        }
                        if (this.timer3) {
                            return ;
                        } else {
                            this.form2.verify_code_disabled_new = true;
                            this.form2.last_time_new = 60;
                            this.timer3 = setInterval(() => {
                                if (this.form2.last_time_new > 0 && this.form2.last_time_new <= 60) {
                                    this.form2.last_time_new--;
                                } else {
                                    this.form2.verify_code_disabled_new  = false;
                                    this.form2.last_time_new = "";
                                    clearInterval(this.timer3);
                                    this.timer3 = null;
                                }
                            }, 1000)
                        }
                        json = {
                            type : 3,
                            phone : this.form2.phone,
                        };
                    } else if (type == 'close_phone') {
                        if (this.timer4) {
                            return ;
                        } else {
                            this.form3.verify_code_disabled = true;
                            this.form3.last_time = 60;
                            this.timer4 = setInterval(() => {
                                if (this.form3.last_time > 0 && this.form3.last_time <= 60) {
                                    this.form3.last_time--;
                                } else {
                                    this.form3.verify_code_disabled  = false;
                                    this.form3.last_time = "";
                                    clearInterval(this.timer4);
                                    this.timer4 = null;
                                }
                            }, 1000)
                        }
                        json = {type : 4};
                    } else {
                        this.$message({message: '类型错误',type: 'error'});
                        return;
                    }
                    let loading = this.$loading({target: document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post("{!! yzWebFullUrl('password.setting.sendVerifyCode') !!}",json).then(function(response) {
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                    }, function(response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    })
                },
                changeSwitch(item) {
                    // console.log(item);
                    // console.log(this[item]);
                    if (this.is_set_phone != 1) {//并未设置手机号
                        return;
                    }
                    if (this[item] == 0 && this.is_verify == 0) {
                        this.change_item = item;
                        this.setPhone('phone_close');
                    }
                },
            },
        });
    </script>
@endsection