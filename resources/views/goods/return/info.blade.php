@extends('layouts.base')
@section('content')
@section('title', trans('退货地址模板详情'))
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
<div class="all">
    <div id="app" v-cloak>
        <div class="vue-crumbs">
            <a @click="goParent">退货地址列表</a> > 退货地址模板详情
        </div>
        <div class="vue-main">

            <div class="vue-main-title">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">退货地址模板详情</div>
            </div>
            <div class="vue-main-form">
                <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                    <el-form-item label="退货地址名称" prop="address_name">
                        <el-input v-model="form.address_name" style="width:70%;"></el-input>
                    </el-form-item>
                    <el-form-item label="联系人" prop="alias">
                        <el-input v-model="form.contact" style="width:70%;"></el-input>
                    </el-form-item>
                    <el-form-item label="联系人手机" prop="alias">
                        <el-input v-model="form.mobile" style="width:70%;"></el-input>
                    </el-form-item>
                    <el-form-item label="联系人电话" prop="alias">
                        <el-input v-model="form.telephone" style="width:70%;"></el-input>
                    </el-form-item>


                    <el-form-item label="注册地址" prop="" v-loading="areaLoading">
                        <el-select v-model="p.province" placeholder="请选择省" clearable @change="changeProvince" :style="street==1?'width:17.5%':'width:23.3%'">
                            <el-option v-for="item in province_list" :key="item.id" :label="item.areaname" :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="p.city" placeholder="请选择市" clearable @change="changeCity" :style="street==1?'width:17.5%':'width:23.3%'">
                            <el-option v-for="item in city_list" :key="item.id" :label="item.areaname" :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="p.district" placeholder="请选择区" clearable @change="changeDistrict" :style="street==1?'width:17.5%':'width:23.3%'">
                            <el-option v-for="item in district_list" :key="item.id" :label="item.areaname" :value="item.id"></el-option>
                        </el-select>
                        <el-select v-model="p.street" placeholder="请选择街道" clearable :style="street==1?'width:17.5%':'width:23.3%'">
                            <el-option v-for="item in street_list" :key="item.id" :label="item.areaname" :value="item.id"></el-option>
                        </el-select>
                    </el-form-item>


                    <el-form-item label="详细地址" prop="alias">
                        <el-input v-model="form.address" style="width:70%;"></el-input>
                    </el-form-item>
                    <el-form-item label="是否默认" prop="is_recommend">
                        <el-switch v-model="form.is_default" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>

                </el-form>
            </div>
        </div>
        <!-- 分页 -->
        <div class="vue-page">
            <div class="vue-center">
                <el-button type="primary" @click="submitForm('form')">保存设置</el-button>
                <el-button @click="goBack">返回</el-button>
            </div>
        </div>
    </div>
</div>

<script>
    var app = new Vue({
        el:"#app",
        delimiters: ['[[', ']]'],
        name: 'test',
        data() {
            let id = {!! $id?:0 !!};
            console.log(id);
            return{
                id:id,
                form:{

                },
                centerDialogVisible:false,
                showVisible:false,

                loading: false,
                rules:{
                    name:{ required: true, message: '请输入退货地址名称名称'}
                },
                p:{
                    province:'',
                    city:'',
                    district:'',
                    street:'',
                },
                province_list:[],
                city_list:[],
                district_list:[],
                street_list:[],
                areaLoading:false,
                street:1,
                submit_url:''

            }
        },
        created() {


        },
        mounted() {
            // this.getData();
            this.initProvince();
            if(this.id) {
                this.getData();
                this.submit_url = '{!! yzWebFullUrl('goods.return-address.edit') !!}';
            }
            else {
                this.submit_url = '{!! yzWebFullUrl('goods.return-address.add') !!}';
            }
        },
        methods: {
            getData() {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post('{!! yzWebFullUrl('goods.return-address.edit') !!}',{id:this.id}).then(function (response) {
                    console.log(response.data,33333)
                        if (response.data.result){
                            this.form = {
                                ...response.data.data,
                            };
                            console.log(this.form.province_id,this.form.city_id,this.form.district_id,888888)
                            this.changeProvince(this.form.province_id);
                            this.changeCity(this.form.city_id);
                            this.changeDistrict(this.form.district_id)
                            this.p.province = this.form.province_id;
                            this.p.city = this.form.city_id;
                            this.p.district = this.form.district_id;
                            this.p.street = this.form.street_id;

                        }
                        else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                        loading.close();
                    }
                );
            },


            initProvince(val) {
                console.log(val);
                this.areaLoading = true;
                this.$http.get("{!! yzWebUrl('area.list.init', ['area_ids'=>'']) !!}"+val).then(response => {
                    this.province_list = response.data.data;
                    this.areaLoading = false;
                }, response => {
                    this.areaLoading = false;
                });
            },
            changeProvince(val) {
                this.city_list = [];
                this.district_list = [];
                this.street_list = [];
                this.p.city = "";
                this.p.district = "";
                this.p.street = "";
                this.areaLoading = true;
                let url = "<?php echo yzWebUrl('area.list', ['parent_id'=> '']); ?>" + val;
                this.$http.get(url).then(response => {
                    if (response.data.data.length) {
                        this.city_list = response.data.data;
                    } else {
                        this.city_list = null;
                    }
                    this.areaLoading = false;
                }, response => {
                    this.areaLoading = false;
                });
            },
            // 市改变
            changeCity(val) {
                this.district_list = [];
                this.street_list = [];
                this.p.district = "";
                this.p.street = "";
                this.areaLoading = true;
                let url = "<?php echo yzWebUrl('area.list', ['parent_id'=> '']); ?>" + val;
                this.$http.get(url).then(response => {
                    if (response.data.data.length) {
                        this.district_list = response.data.data;
                    } else {
                        this.district_list = null;
                    }
                    this.areaLoading = false;
                }, response => {
                    this.areaLoading = false;
                });
            },
            // 区改变
            changeDistrict(val) {
                console.log(val)
                this.street_list = [];
                this.p.street = "";
                this.areaLoading = true;
                let url = "<?php echo yzWebUrl('area.list', ['parent_id'=> '']); ?>" + val;
                this.$http.get(url).then(response => {
                    if (response.data.data.length) {
                        this.street_list = response.data.data;
                    } else {
                        this.street_list = null;
                    }
                    this.areaLoading = false;
                }, response => {
                    this.areaLoading = false;
                });
            },

            submitForm(formName) {
                let that = this;
                let json = this.form;

                json.province_id = this.p.province;
                json.city_id = this.p.city;
                json.district_id = this.p.district;
                json.street_id = this.p.street;

                let json1 = {
                    address:json
                }
                if(this.id) {
                    json1.id = this.id
                }
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                        this.$http.post(this.submit_url,json1).then(response => {
                            if (response.data.result) {
                                this.$message({type: 'success',message: '操作成功!'});
                                this.goBack();
                            } else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                            loading.close();
                        },response => {
                            loading.close();
                        });
                    }
                    else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },

            goBack() {
                history.go(-1)
            },
            goParent() {
                window.location.href = `{!! yzWebFullUrl('goods.return-address.index') !!}`;
            }, 
        },
    })

</script>


@endsection