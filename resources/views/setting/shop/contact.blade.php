@extends('layouts.base')
@section('content')
    <script type="text/javascript"
            src="https://api.map.baidu.com/api?v=2.0&ak=QXSZyPZk26shrYzAXjTkDLx5LbRCHECz"></script>
    <style>
        .panel{
            margin-bottom:10px!important;
            padding-left: 20px;
            border-radius: 10px;
        }
        .panel .active a {
            background-color: #29ba9c!important;
            border-radius: 18px!important;
            color:#fff;
        }
        .panel a{
            border:none!important;
            background-color:#fff!important;
        }
        .content{
            background: #eff3f6;
            padding: 10px!important;
        }
        .con{
            padding-bottom:20px;
            position:relative;
            min-height:100vh;
            background-color:#fff;
            border-radius: 8px;
        }
        .con .setting .block{
            padding:10px;
            background-color:#fff;
            border-radius: 8px;
        }
        .con .setting .block .title{
            display:flex;
            align-items:center;
            margin-bottom:15px;
        }
        .confirm-btn{
            width: calc(100% - 266px);
            position:fixed;
            bottom:0;
            right:0;
            margin-right:10px;
            line-height:63px;
            background-color: #ffffff;
            box-shadow: 0px 8px 23px 1px
            rgba(51, 51, 51, 0.3);
            background-color:#fff;
            text-align:center;
        }

        b{
            font-size:14px;
        }
    </style>
    <div id='re_content' >
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b></div>
                    <el-form ref="form" :model="form" label-width="15%">
                        <el-form-item label="客服电话">
                            <el-input v-model="form.phone" placeholder="请输入客服电话" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="所在地址">
                            <el-input v-model="form.address" placeholder="请输入所在地址" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="商城简介">
                            <el-input v-model="form.description"  type="textarea" placeholder="请输入商城简介" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="店铺名称">
                            <el-input v-model="form.store_name" placeholder="请输入店铺名称" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="店铺位置">
                            <el-select  @change="changeProvince" v-model="form.province_id" :clearable="true"  :filterable="true">
                                <el-option :label="v.areaname" :value="v.id" v-for="(v,k) in province_list"></el-option>
                            </el-select>
                            <el-select @change="changeCity" v-model="form.city_id" :clearable="true"  :filterable="true">
                                <el-option :label="v.areaname" :value="v.id" v-for="(v,k) in city_list" v-if="v.parentid==form.province_id"></el-option>
                            </el-select>
                            <el-select @change="changeDistrict" v-model="form.district_id" :clearable="true"  :filterable="true">
                                <el-option :label="v.areaname" :value="v.id" v-for="(v,k) in district_list" v-if="v.parentid==form.city_id"></el-option>
                            </el-select>
                            <el-select v-model="form.street_id" :clearable="true"  :filterable="true">
                                <el-option :label="v.areaname" :value="v.id" v-for="(v,k) in street_list" v-if="v.parentid==form.district_id"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="店铺地址">
                            <el-input v-model="form.store_address" placeholder="请输入店铺地址" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="店铺定位">
                            <el-input v-model="form.store_longitude" placeholder="请输入店铺经度" style="width:30%;">
                                <template slot="prepend">经度</template>
                            </el-input>
                            <el-input v-model="form.store_latitude" placeholder="请输入店铺纬度" style="width:30%;">
                                <template slot="prepend">纬度</template>
                            </el-input>
                            <el-button @click="openMap">选择坐标</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </div>

            <el-dialog :visible.sync="map_show" width="60%" center title="选择坐标">
                <div>
                    <div>
                        <input v-model="map_keyword" style="width:70%" @keyup.enter="searchMap"
                               class="el-input__inner">
                        <el-button type="primary" @click="searchMap()">搜索</el-button>
                    </div>
                    <div ref="ditucontent" style="width:100%;height:450px;margin:20px 0"></div>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="sureMap">确 定</el-button>
                    <el-button @click="map_show = false">取 消</el-button>
                </span>
            </el-dialog>

            <div class="confirm-btn">
                <el-button type="primary" @click="submit">提交</el-button>
            </div>
        </div>
    </div>
    <script>

        var top_left_control = new BMap.ScaleControl({anchor: BMAP_ANCHOR_TOP_LEFT});// 左上角，添加比例尺
        var top_left_navigation = new BMap.NavigationControl();  //左上角，添加默认缩放平移控件
        var top_right_navigation = new BMap.NavigationControl({
            anchor: BMAP_ANCHOR_TOP_RIGHT,
            type: BMAP_NAVIGATION_CONTROL_SMALL
        }); //右上角，仅包含平移和缩放按钮
        /*缩放控件type有四种类型:
        BMAP_NAVIGATION_CONTROL_SMALL：仅包含平移和缩放按钮；BMAP_NAVIGATION_CONTROL_PAN:仅包含平移按钮；BMAP_NAVIGATION_CONTROL_ZOOM：仅包含缩放按钮*/

        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    activeName: 'first',
                    form:{
                        phone:'',
                        address:'',
                        description:'',
                        store_name:'',
                        store_address:'',
                        store_longitude:113.275995,
                        store_latitude:23.117055,
                        province_id: '',
                        city_id: '',
                        district_id: '',
                        street_id: '',
                    },
                    map: "",
                    marker: "",
                    centerParam: [113.275995, 23.117055],
                    zoomParam: "",
                    markersParam: [113.275995, 23.117055],
                    pointNew: "",
                    choose_center: [],
                    choose_marker: [],
                    map_show: false,
                    map_keyword: '',
                    province_list: [],
                    city_list: [],
                    district_list: [],
                    street_list: [],
                }
            },
            mounted () {
                this.getData();
                this.initProvince();
            },
            methods: {
                openMap() {
                    this.map_show = true;
                    setTimeout(() => {
                        this.initMap();
                    }, 100);
                    this.map_keyword = "";
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
                    this.markersParam = this.choose_marker.length <= 0 ? [113.275995, 23.117055] : this.choose_marker;
                    this.centerParam = this.choose_center.length <= 0 ? [113.275995, 23.117055] : this.choose_center;
                    this.form.store_longitude = this.markersParam[0];
                    this.form.store_latitude = this.markersParam[1];
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
                    // this.centerParam = '116.712617,24.778619';
                    // var centerArr = this.centerParam.split(",");
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
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.shop.contact') !!}').then( (response)=>{
                        if (response.data.result == 1) {
                            if(response.data.data.set){
                                for(let i in response.data.data.set){
                                    this.form[i]=response.data.data.set[i]
                                }
                                if (this.form.store_longitude && this.form.store_latitude) {
                                    this.centerParam = [this.form.store_longitude, this.form.store_latitude];
                                    this.markersParam = [this.form.store_longitude, this.form.store_latitude];
                                }
                                
                                let set = response.data.data.set;
                                // 省市区
                                if(set.province_id) {
                                    this.changeProvince(set.province_id);
                                    this.form.province_id = set.province_id;
                                }
                                if(set.city_id) {
                                    this.changeCity(set.city_id);
                                    this.form.city_id = set.city_id;
                                }
                                if(set.district_id) {
                                    this.changeDistrict(set.district_id);
                                    this.form.district_id = set.district_id;
                                    this.form.street_id = set.street_id;
                                }
                            }
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }

                    }, (response)=> {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
                initProvince() {
                    this.areaLoading = true;
                    this.$http.get("{!! yzWebUrl('area.list.init', ['area_ids'=>'']) !!}").then(response => {
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
                    this.form.city_id = "";
                    this.form.district_id = "";
                    this.form.street_id = "";
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
                    this.form.district_id = "";
                    this.form.street_id = "";
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
                    this.street_list = [];
                    this.form.street_id = "";
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
                submit() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('setting.shop.contact') !!}',{'contact':this.form}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                        location.reload();
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
            },
        });
    </script>
@endsection
