define({
  template: `
  <div>
    <div class="vue-main-title">
      <div class="vue-main-title-left"></div>
      <div class="vue-main-title-content">商品属性</div>
    </div>
    <ul class="attributes">
      <ul class="attribute-row">
        <li class="attribute-col">
          属性名称
        </li>
        <li class="attribute-col">
          属性值
        </li>
        <li class="attribute-col"></li>
      </ul>
      <draggable v-model="attributes">
        <ul class="attribute-row" v-for="(attrItem,itemIndex) in attributes" :key="attrItem.itemIndex">
          <li class="attribute-col">
            <el-input v-model="attrItem.title" placeholder="请输入属性名称"></el-input>
          </li>
          <li class="attribute-col">
            <el-input v-model="attrItem.value" placeholder="请输入属性值"></el-input>
          </li>
          <li class="attribute-col">
            <el-button icon="el-icon-rank"></el-button>
            <el-button icon="el-icon-delete" @click="removeAttr(itemIndex)"></el-button>
          </li>
        </ul>
      </draggable>
    </ul>
    <el-button style="margin-top:20px;" @click="addAttr">添加属性</el-button>
  </div>
  `,
  style: `
  .attribute-row {
    display:flex;
    align:center;
    justify-content:space-around;
    padding:18px 0;
    font-size:14px;
    color:#101010;
    border-bottom:1px solid #e8e8e8;
  }
  `,
  props: {
    form: {
      default() {
        return {};
      },
    },
  },
  data() {
    return {
      attributes: [],
    };
  },
  mounted() {
    // console.log(this.form,5)
    this.attributes = this.form.map(item => ({id:item.id,'title':item.title,'value':item.value}))
  },
  methods: {
    removeAttr(itemIndex) {
      this.attributes.splice(itemIndex, 1);
    },
    addAttr() {
      this.attributes.push({
        id: "",
        title: "",
        value: "",
      });
    },
    extraDate(){
      
    },
    validate() {
      let yes = true;
      // 过滤空数据
      this.attributes.forEach(element => {
        if(!element.title || !element.value){
          this.$message.error('请输入属性名和属性值');
          yes = false;
          return
        }
      });

      if (yes) {
        return this.attributes;
      } else {
        return false;
      }
    },
  },
});
