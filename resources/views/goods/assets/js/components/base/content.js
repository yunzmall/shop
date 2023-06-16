define({
  template: `
  <div>
    <div class="vue-main-title">
      <div class="vue-main-title-left"></div>
      <div class="vue-main-title-content">商品描述</div>
    </div>
    <tinymceee v-model="form.content" v-if="!isShowContent"></tinymceee>
    <tinymceee v-model="content" v-if="isShowContent"></tinymceee>
  </div>
  `,
  style: `
  .tox-tinymce {
    min-height:600px;
  }
  `,
  props: {
    form: {
      default() {
        return {};
      },
    },
    formKey: {
      type: String,
    },
  },
  data() {
    return {
      isShowContent:false,
      content:""
    };
  },
  created() {
    // console.log(this.form,'商品描述')
    if(this.form){
      if(this.form === null){
        this.isShowContent = true
        return
      }
      this.isShowContent = false
    }else{
      this.isShowContent = true
    }

  },
  methods: {
    extraDate(){
      return {
        'extraContent':"商品描述"
      }
    },
    validate() {
      if(this.isShowContent){
        return {
          content:this.content ? this.content : ""
        }
      }else{
        return {
          content:this.form.content ? this.form.content : ""
        }
      }
    },
  },
});
