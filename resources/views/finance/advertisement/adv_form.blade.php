@extends('layouts.base')

@section('content')
@section('title', trans('收益广告'))

<link rel="stylesheet" href="{{resource_get('plugins/store-cashier/src/common/static/index.css')}}">
<script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak=QXSZyPZk26shrYzAXjTkDLx5LbRCHECz"></script>
<style>
    .dialog-cover {
        z-index: 999;
    }
</style>
<div class="all">
    <div id="app" v-cloak>
        <el-form ref="form" :model="adv" :rules="rules" label-width="15%">
            <div class="vue-main">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">收益广告</div>
                </div>
                <div class="vue-main-form">
                    <el-form-item label="排序" prop="sort_by">
                        <el-input v-model="adv.sort_by" style="width:70%;"></el-input>
                    </el-form-item>
                    <el-form-item label="标题" prop="name">
                        <el-input v-model="adv.name" style="width:70%;"></el-input>
                    </el-form-item>
                    <el-form-item label="图片" prop="thumb" ref="thumb">
                        <div class="upload-box" @click="openUpload('thumb')" v-if="!adv.thumb">
                            <i class="el-icon-plus" style="font-size:32px"></i>
                        </div>
                        <div @click="openUpload('thumb')" class="upload-boxed" v-if="adv.thumb">
                            <img :src="adv.thumb" alt=""
                                 style="width:150px;height:150px;border-radius: 5px;cursor: pointer;">
                            <div class="upload-boxed-text">点击重新上传</div>
                        </div>
                        <div class="tip">提示: 100*100px或正方型图片</div>
                    </el-form-item>
                    <el-form-item label="链接" prop="status">
                        <el-input v-model="adv.adv_url" style="width:70%;"></el-input>
                    </el-form-item>
                    <el-form-item label="是否显示" prop="status">
                        <el-switch
                                v-model="adv.status"
                                :active-value="1"
                                :inactive-value="0"
                                active-color="#13ce66"
                                inactive-color="#ff4949">
                        </el-switch>
                    </el-form-item>
                    <el-form-item label="投放区域" prop="area_open">
                        <el-switch
                                v-model="adv.area_open"
                                :active-value="1"
                                :inactive-value="0"
                                active-color="#13ce66"
                                inactive-color="#ff4949">
                        </el-switch>
                        <span class='help-block'>投放区域开启后，前端根据会员定位显示最近设置的广告</span>
                    </el-form-item>
                    <el-form-item v-if="adv.area_open==1" label="定位" prop="">
                        <el-input v-model="markersParam[0]" style="width:35%;"></el-input>
                        <el-input v-model="markersParam[1]" style="width:35%;"></el-input>
                        <el-button @click="openMap">选择坐标</el-button>
                    </el-form-item>

                </div>
            </div>
        </el-form>
        <!-- 分页 -->
        <div class="vue-page">
            <div class="vue-center">
                <el-button type="primary" @click="submitForm()">保存设置</el-button>
            </div>
        </div>
        <el-dialog :visible.sync="map_show" width="60%" center title="选择坐标">
            <div>
                <div>
                    <input v-model="map_keyword" style="width:70%" @keyup.enter="searchMap" class="el-input__inner">
                    <el-button type="primary" @click="searchMap()">搜索</el-button>
                </div>
                <div ref="ditucontent" style="width:100%;height:450px;margin:20px 0"></div>
            </div>
            <span slot="footer" class="dialog-footer">
                    <el-button @click="sureMap">确 定</el-button>
                    <el-button @click="map_show = false">取 消</el-button>
                </span>
        </el-dialog>


        <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img>
        <upload-img-list :upload-list-show="uploadListShow" :name="chooseImgListName" @replace="changeListProp"
                         @sure="sureImgList"></upload-img-list>
    </div>
</div>
<script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script>
@include('public.admin.uploadImg')
@include('public.admin.uploadImgList')

<script>
    var top_left_control = new BMap.ScaleControl({anchor: BMAP_ANCHOR_TOP_LEFT});// 左上角，添加比例尺
    var top_left_navigation = new BMap.NavigationControl();  //左上角，添加默认缩放平移控件
    var top_right_navigation = new BMap.NavigationControl({
        anchor: BMAP_ANCHOR_TOP_RIGHT,
        type: BMAP_NAVIGATION_CONTROL_SMALL
    }); //右上角，仅包含平移和缩放按钮

    /*缩放控件type有四种类型:
    BMAP_NAVIGATION_CONTROL_SMALL：仅包含平移和缩放按钮；BMAP_NAVIGATION_CONTROL_PAN:仅包含平移按钮；BMAP_NAVIGATION_CONTROL_ZOOM：仅包含缩放按钮*/
    var app = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        name: 'test',
        data() {
            let id = {!! $id?:0 !!};
            console.log(id);
            return {
                id: id,
                selectLinkPopup: false,
                adv: {
                    sort_by: '',
                    name: '',
                    thumb: '',
                    adv_url: '',
                    status: 0,
                    area_open: 0,
                },
                uploadShow: false,
                chooseImgName: '',
                submit_url: '',
                showVisible: false,

                uploadListShow: false,
                chooseImgListName: '',

                loading: false,
                uploadImg1: '',

                areaLoading: false,
                street: 1,
                category_list: [],

                map: "",
                marker: "",
                centerParam: [116.413384, 39.910925],
                zoomParamzoomParam: "",
                markersParam: [116.413384, 39.910925],
                pointNew: "",

                choose_center: [],
                choose_marker: [],

                map_show: false,
                map_keyword: '',

                // 会员
                member_keyword: '',
                member_show: false,
                member_list: [],
                choose_member_type: '',
                choosed_boss: {},
                choosed_store: {},

                rules: {
                    name: {required: true, message: '请输入广告标题'},
                    thumb: {required: true, message: '请选择图片'},
                    // banner_thumb:{ required: true, message: '请选择门店banner图'},
                    sort_by: {required: true, message: '请输入排序'},
                    province_id: {required: true, message: '请选择地址'},

                }
            }
        },
        created() {


        },
        mounted() {
            // this.initMap();
            this.id = this.getParam("id");
            this.initProvince();
            this.getData();
            // this.show = true
        },
        methods: {
            hideSelectedLinkPopup() {
                this.selectLinkPopup = false;
            },
            //当前链接的增加
            parHref(child, confirm) {
                this.form.payment_jump_h5 = child;
                this.hideSelectedLinkPopup();
            },
            changeprogram(item) {
                this.pro = item;
            },
            parpro(child, confirm) {
                this.pro = confirm;
                this.form.payment_jump_minapp = child;
            },
            openMap() {
                this.map_show = true;
                setTimeout(() => {
                    this.initMap();
                }, 100)
                this.map_keyword = "";
            },

            getData() {
                let loading = this.$loading({
                    target: document.querySelector(".content"),
                    background: 'rgba(0, 0, 0, 0)'
                });
                this.$http.post('{!! yzWebFullUrl('finance.advertisement.edit') !!}', {id: this.id}).then(function (response) {
                        if (response.data.result === 1) {
                            this.adv.sort_by = response.data.data.adv.sort_by
                            this.adv.name = response.data.data.adv.name
                            this.adv.thumb = response.data.data.adv.thumb
                            this.adv.adv_url = response.data.data.adv.adv_url
                            this.adv.status = response.data.data.adv.status
                            this.adv.area_open = response.data.data.adv.area_open
                            this.markersParam[0] = response.data.data.adv.longitude
                            this.markersParam[1] = response.data.data.adv.latitude
                        }

                        loading.close();
                    }, function (response) {
                        this.$message({message: response.data.msg, type: 'error'});
                        loading.close();
                    }
                );
            },

            submitForm() {
                let loading = this.$loading({
                    target: document.querySelector(".content"),
                    background: 'rgba(0, 0, 0, 0)'
                });
                let url = ''
                if (this.id) {
                    url = "{!! yzWebUrl('finance.advertisement.edit') !!}"
                } else {
                    url = "{!! yzWebUrl('finance.advertisement.add') !!}"
                }
                this.$set(this.adv,'lng',this.markersParam[0])
                this.$set(this.adv,'lat',this.markersParam[1])
                let json = this.adv

                this.$http.post(url, {id: this.id,adv:json}).then(function (response) {
                    if (response.data.result === 1) {
                        this.$message({message: response.data.msg, type: 'success'});
                        loading.close();
                        location.href = response.data.data.url
                    } else {
                        this.$message({message: response.data.msg, type: 'error'});
                        loading.close();
                    }
                    }, function (response) {
                        this.$message({message: response.data.msg, type: 'error'});
                        loading.close();
                    }
                );
            },
            initProvince(val) {
                console.log(val);
                this.areaLoading = true;
                this.$http.post("{!! yzWebUrl('finance.advertisement.edit', ['area_ids'=>'']) !!}" + val).then(response => {
                    this.province_list = response.data.data;
                    this.areaLoading = false;
                }, response => {
                    this.areaLoading = false;
                });
            },
            goBack() {
                history.go(-1)
            },
            openUpload(str) {
                this.chooseImgName = str;
                this.uploadShow = true;
            },
            sureImg(name, image, image_url) {
                this.adv[name] = image_url;
                console.log(this.adv)
            },
            clearImg(str, type, index) {
                if (!type) {
                    this.adv[str] = "";
                    this.adv[str + '_url'] = "";
                } else {
                    this.adv[str].splice(index, 1);
                    this.adv[str + '_url'].splice(index, 1);
                }
                this.$forceUpdate();
            },
            changeProp(val) {
                if(val == true) {
                    this.uploadShow = false;
                }
                else {
                    this.uploadShow = true;
                }
            },
            openListUpload(str) {
                this.chooseImgListName = str;
                this.uploadListShow = true;
            },
            changeListProp(val) {
                if (val == true) {
                    this.uploadListShow = false;
                } else {
                    this.uploadListShow = true;
                }
            },
            sureImgList(name, image, image_url) {
                console.log(name)
                console.log(image)
                console.log(image_url)
                if (!this.adv[name] || !this.adv[name + '_url']) {
                    this.adv[name] = [];
                    this.adv[name + '_url'] = [];
                }
                image.forEach((item, index) => {
                    this.adv[name].push(item);
                    this.adv[name + '_url'].push(image_url[index]);
                })
                console.log(this.adv)
            },
            getParam(name) {
                return location.href.match(new RegExp("[?#&]" + name + "=([^?#&]+)", "i"))
                    ? RegExp.$1
                    : "";
            },
            gotoDelivery() {
                let link = `{!! yzWebFullUrl('plugin.store-cashier.admin.delivery.index') !!}` + `&store_id=` + this.id;
                window.location.href = link;
            },
            searchMap() {
                console.log(this.marker);
                let that = this;
                geo.getPoint(this.map_keyword, function (point) {

                    that.choose_marker = [point.lng, point.lat];
                    that.choose_center = [point.lng, point.lat];
                    console.log(point)
                    that.map.panTo(point);
                    that.marker.setPosition(point);
                    that.marker.setAnimation(BMAP_ANIMATION_BOUNCE);
                    setTimeout(function () {
                        that.marker.setAnimation(null)
                    }, 3600);
                });

            },

            sureMap() {
                let that = this;
                this.markersParam = [];
                this.centerParam = [];
                this.markersParam = this.choose_marker.length <= 0 ? [116.413384, 39.910925] : this.choose_marker;
                this.centerParam = this.choose_center.length <= 0 ? [116.413384, 39.910925] : this.choose_center;
                ;
                console.log(this.centerParam);
                console.log(this.markersParam);
                that.map_show = false;

            },
            //创建和初始化地图函数：
            initMap() {
                let that = this;
                // [FF]切换模式后报错
                if (!window.BMap) {
                    return;
                }

                console.log(this.$refs['ditucontent']);
                for (let i in this.$refs) {
                    console.log(i)
                }
                this.createMap(); //创建地图
                this.setMapEvent(); //设置地图事件
                this.addMapControl(); //向地图添加控件
                geo = new BMap.Geocoder();


                // 创建标注

                var point = new BMap.Point(this.markersParam[0], this.markersParam[1]);
                this.marker = new BMap.Marker(point);
                this.marker.enableDragging();

                this.map.addOverlay(this.marker); // 将标注添加到地图中
                this.marker.addEventListener('dragend', function (e) {//拖动标注结束
                    that.pointNew = e.point;
                    var point = that.marker.getPosition();
                    geo.getLocation(point, function (address) {
                        console.log(address.address);
                        that.map_keyword = address.address;
                    });
                    console.log(e);
                    console.log("使用拖拽获取的百度坐标" + that.pointNew.lng + "," + that.pointNew.lat);
                    that.choose_marker = [that.pointNew.lng, that.pointNew.lat];
                    that.choose_center = [that.pointNew.lng, that.pointNew.lat];
                });
                this.marker.setLabel(new BMap.Label('请您移动此标记，选择您的坐标！', {'offset': new BMap.Size(10, -20)}));
                if (parent.editor && parent.document.body.contentEditable == "true") {
                    //在编辑状态下
                    setMapListener(); //地图改变修改外层的iframe标签src属性
                }
            },

            //创建地图函数：
            createMap() {
                this.map = new BMap.Map(this.$refs['ditucontent']); //在百度地图容器中创建一个地图

                var point = new BMap.Point(
                    this.centerParam[0],
                    this.centerParam[1]
                ); //

                this.zoomParam = 12;
                this.map.centerAndZoom(point, parseInt(this.zoomParam)); //设定地图的中心点和坐标并将地图显示在地图容器中
            },

            //地图事件设置函数：
            setMapEvent() {
                // this.map.disableDragging(); //启用地图拖拽事件，默认启用(可不写)
                this.map.enableScrollWheelZoom(); //启用地图滚轮放大缩小
                this.map.enableDoubleClickZoom(); //启用鼠标双击放大，默认启用(可不写)
                this.map.enableKeyboard(); //启用键盘上下左右键移动地图
            },

            //地图控件添加函数：
            addMapControl() {
                this.map.addControl(new BMap.NavigationControl());
                this.map.addControl(top_left_control);
                this.map.addControl(top_left_navigation);
                this.map.addControl(top_right_navigation);
            },

            setMapListener() {
                var editor = parent.editor,
                    containerIframe,
                    iframes = parent.document.getElementsByTagName("iframe");
                for (var key in iframes) {
                    if (iframes[key].contentWindow == window) {
                        containerIframe = iframes[key];
                        break;
                    }
                }
                if (containerIframe) {
                    this.map.addEventListener("moveend", mapListenerHandler);
                    this.map.addEventListener("zoomend", mapListenerHandler);
                    this.marker.addEventListener("dragend", mapListenerHandler);
                }

                function mapListenerHandler() {
                    var zoom = this.map.getZoom();
                    this.center = this.map.getCenter();
                    this.marker = window.marker.getPoint();
                    containerIframe.src = containerIframe.src
                        .replace(
                            new RegExp("([?#&])center=([^?#&]+)", "i"),
                            "$1center=" + center.lng + "," + center.lat
                        )
                        .replace(
                            new RegExp("([?#&])markers=([^?#&]+)", "i"),
                            "$1markers=" + this.marker.lng + "," + this.marker.lat
                        )
                        .replace(new RegExp("([?#&])zoom=([^?#&]+)", "i"), "$1zoom=" + zoom);
                    editor.fireEvent("saveScene");
                }
            },
        },
    })

</script>
@endsection

