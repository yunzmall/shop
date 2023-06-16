define({
  name:"advertising",
  template:`
    <div id="advertising">
      <el-form>
      <div style="margin:0 auto;width:80%;">
        <el-form-item label="广告宣传语" label-width="155px" prop="is_open">
          <el-radio-group v-model="forms.is_open">
            <el-radio :label="1">开启</el-radio>
            <el-radio :label="0">关闭</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="文案" label-width="155px" prop="copywriting">
            <el-input
              type="textarea"
              :rows="2"
              v-model="forms.copywriting">
            </el-input>
            <div class="form-item_tips">商品详情显示</div>
            <div class="form-item_tips">最多可输入100个字</div>
        </el-form-item>

        <el-form-item label="文案字体大小" label-width="155px" prop="font_size">
            <el-input-number v-model="forms.font_size" controls-position="right" :min="0" :max="50">
            </el-input-number>
            <span class="pxSpan">px</span>
            <div class="form-item_tips">最大50px</div>
        </el-form-item>

        <el-form-item label="文案颜色" label-width="155px" prop="font_color">
            <div style="display:flex">
                <el-input placeholder="请选择颜色" style="width: 110px;" v-model="forms.font_color"></el-input>
                <el-color-picker v-model="forms.font_color"></el-color-picker>
            </div>
        </el-form-item>

        <el-form-item label="H5链接" label-width="155px" prop="link">
          <el-input  v-model="forms.link" placeholder="请填写指向的链接 (请以https://开头)">
            <div  style="cursor: pointer;" slot="append"  @click="showLink('link','link_one')">选择链接</div>
          </el-input>
        </el-form-item>

        <el-form-item label="小程序链接" label-width="155px" prop="min_link">
        <el-input v-model="forms.min_link" placeholder="请填写指向的链接 ">
            <div  style="cursor: pointer;" slot="append" @click="showLink('mini','min_link_one')">选择链接</div>
          </el-input>
        </el-form-item>
      </div>
      </el-form>
        <pop :show="show" @replace="changeLink" @add="parHref"></pop>
        <program :pro="pro" @replacepro="changeprogram" @addpro="parpro"></program>
    </div>
  `,
  style:`
  .pxSpan {
    display: inline-block;
    height: 41px;
    background-color:#eee;
    width: 40px;
    text-align: center;
    border: 1px solid #DCDFE6;
    border-radius: 0 4px 4px 0;
    margin-left: -5px;
  }
  `,
  props: {
    form: {
      default() {
        return {}
      }
    }
  },
  data(){
    return{
        show:false,//是否开启公众号弹窗
        pro:false ,//是否开启小程序弹窗 
        chooseLink:'',
        chooseMiniLink:'',
        forms:{
            "is_open": 0,
            "copywriting": "",
            "font_size": "",
            "font_color": "",
            "link": "",
            "min_link": ""
        }
    }
  },
  created() {
    if(this.form !== null){
        this.forms = this.form
    }
  },
  methods: {
    //弹窗显示与隐藏的控制
    changeLink(item){
        this.show=item;
    },
    //当前链接的增加
    parHref(child,confirm){
        this.show=confirm;
        this.forms.link = child
        console.log(child,confirm,'parHref');
    },
    changeprogram(item){
        this.pro=item;
    },
    parpro(child,confirm){
        this.pro=confirm;
        this.forms.min_link = child
        console.log(child,confirm,'parpro');
    },
    showLink(type,name) {
        if(type=="link") {
            this.chooseLink = name;
            this.show = true;
        }else {
            this.chooseMiniLink = name;
            this.pro = true;
        }
        console.log( this.chooseLink,this.chooseMiniLink,'123this.show');
    },
    validate(){
        return {
            copywriting:this.forms.copywriting,
            is_open:this.forms.is_open,
            font_size:this.forms.font_size,
            font_color:this.forms.font_color,
            link:this.forms.link,
            min_link:this.forms.min_link
        }
    },
  },
})