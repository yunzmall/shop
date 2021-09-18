<script>
Vue.component('addressList', {
    props: {
        province_id:{
            type:Number|String,
            default:'',
        },
        city_id:{
            type:Number|String,
            default:'',
        },
        district_id:{
            type:Number|String,
            default:'',
        },
        street_id:{
            type:Number|String,
            default:'',
        },
        //是否开启街道
        street:{
            type:Number|String,
            default:2,
        },
        // 宽度
        widthStyle:{
            type:String,
            default:'',
        },
        
    },
    delimiters: ['[[', ']]'],
    data(){
        return{
            form:{
                province_id:this.province_id,
                city_id:this.city_id,
                district_id:this.district_id,
                street_id:this.street_id,
            },
            province_list:[],
            city_list:[],
            district_list:[],
            street_list:[],
        }
    },
    watch:{
        'form.province_id':{
            handler(val) {
                this.$emit('change-province','province_id',val);
            },
        },
        'form.city_id':{
            handler(val) {
                this.$emit('change-province','city_id',val);
            },
        },
        'form.district_id':{
            handler(val) {
                this.$emit('change-province','district_id',val);
            },
        },
        'form.street_id':{
            handler(val) {
                this.$emit('change-province','street_id',val);
            },
        },
        province_id(val) {
            if(val&&this.province_id!=this.form.province_id) {
                this.form.province_id = this.province_id
                this.changeProvince(val,1)
            }
        },
        city_id(val) {
            if(val&&this.city_id!=this.form.city_id) {
                this.form.city_id = this.city_id
                this.changeCity(val,1)
            }
        },
        district_id(val) {
            if(val&&this.district_id!=this.form.district_id) {
                this.form.district_id = this.district_id
                this.changeDistrict(val,1)
            }
        },
        street_id(val) {
            if(val&&this.street_id!=this.form.street_id) {
                this.form.street_id = this.street_id
            }
        },
    },
    mounted: function(){
        this.initProvince();
        this.getStreet();
        if(this.form.province_id) {
            this.changeProvince(this.form.province_id,1)
        }
        if(this.form.city_id) {
            this.changeCity(this.form.city_id,1)
        }
        if(this.form.district_id) {
            this.changeDistrict(this.form.district_id,1)
        }
    },
    methods:{
        initProvince(val) {
            this.$http.get("{!! yzWebUrl('area.list.init', ['area_ids'=>'']) !!}"+val).then(response => {
                this.province_list = response.data.data;
            }, response => {
            });
        },
        // 区改变
        getStreet() {
            let url = "<?php echo yzWebUrl('area.list.open-street', []); ?>";
            this.$http.get(url).then(response => {
                if (response.data.result) {
                    if(this.street==2) {
                        this.street = response.data.data.is_street
                    }
                } else {
                    this.street_list = null;
                }
            }, response => {
            });
        },
        changeProvince(val,type) {
            if(!type) {
                this.city_list = [];
                this.district_list = [];
                this.street_list = [];
                // this.form.province_id = "";
                this.form.city_id = "";
                this.form.district_id = "";
                this.form.street_id = "";
            }
            let url = "<?php echo yzWebUrl('area.list', ['parent_id'=> '']); ?>" + val;
            this.$http.get(url).then(response => {
                if (response.data.data.length) {
                    this.city_list = response.data.data;
                } else {
                    this.city_list = null;
                }
            }, response => {
            });
        },
        // 市改变
        changeCity(val,type) {
            if(!type) {
                this.district_list = [];
                this.street_list = [];
                this.form.district_id = "";
                this.form.street_id = "";
            }
            let url = "<?php echo yzWebUrl('area.list', ['parent_id'=> '']); ?>" + val;
            this.$http.get(url).then(response => {
                if (response.data.data.length) {
                    this.district_list = response.data.data;
                } else {
                    this.district_list = null;
                }
            }, response => {
            });
        },
        // 区改变
        changeDistrict(val,type) {
            if(!type) {
                this.street_list = [];
                this.form.street_id = "";
            }
            let url = "<?php echo yzWebUrl('area.list', ['parent_id'=> '']); ?>" + val;
            this.$http.get(url).then(response => {
                if (response.data.data.length) {
                    this.street_list = response.data.data;
                } else {
                    this.street_list = null;
                }
            }, response => {
            });
        },
        
    
    },
    template: `
            <div>
                <el-select v-model="form.province_id" clearable placeholder="省" @change="changeProvince(form.province_id)" :style="{width:widthStyle=='search'?'150px':street==1?'17.5%':'23.33%'}">
                    <el-option v-for="(item,index) in province_list" :key="index" :label="item.areaname" :value="item.id"></el-option>
                </el-select>
                <el-select v-model="form.city_id" clearable placeholder="市" @change="changeCity(form.city_id)" :style="{width:widthStyle=='search'?'150px':street==1?'17.5%':'23.33%'}">
                    <el-option v-for="(item,index) in city_list" :key="index" :label="item.areaname" :value="item.id"></el-option>
                </el-select>
                <el-select v-model="form.district_id" clearable placeholder="区/县" @change="changeDistrict(form.district_id)" :style="{width:widthStyle=='search'?'150px':street==1?'17.5%':'23.33%'}">
                    <el-option v-for="(item,index) in district_list" :key="index" :label="item.areaname" :value="item.id"></el-option>
                </el-select>
                <el-select v-if="street==1" v-model="form.street_id" clearable placeholder="街道/乡镇" :style="{width:widthStyle=='search'?'150px':street==1?'17.5%':'23.33%'}">
                    <el-option v-for="(item,index) in street_list" :key="index" :label="item.areaname" :value="item.id"></el-option>
                </el-select>
            </div>
    `
    
});
</script>
