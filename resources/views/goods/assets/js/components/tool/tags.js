define({
  name: "tags",
  template: `
    <div id="tags">
      <el-form ref="form" label-width="15%">
        <div id="vue_head">
            <div class="base_set">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">商品标签</div>
                </div>
                <div class="vue-main-form">
                <el-form-item v-if="tagItem.value !== undefined && tagItem.value.length > 0 " :label="tagItem.name" v-for="(tagItem,tagIndex) in form.filtering" :key="tagIndex">
                    <div style="display:flex;padding-top: 12px;flex-wrap: wrap;">
                      <el-checkbox  v-model="tagItem.checkAll" @change="handleCheckAllChange($event,tagItem)" >全选</el-checkbox>
                      <el-checkbox-group v-model="form.goods_filter" style="display: contents;">
                        <el-checkbox v-for="(item,index) in tagItem.value" :label="item.id" :key="item.id" @change="handleCheckChange($event,tagIndex,tagItem)">{{item.name}}</el-checkbox>
                      </el-checkbox-group>
                    </div>
                  </el-form-item>
                </div>
            </div>
        </div>
      </el-form>
    </div>
  `,
  props: {
    form: {
      default() {
        return {}
      }
    }
  },
  data(){
    return {}
  },
  mounted() {
    this.form.filtering.forEach(element => {
      this.$set(element,'checkAll',false)
    });
    this.form.filtering.forEach((item,index) => {
      let checkAll = true;
      item.value.forEach((tagItem,tagIndex) => {
        if(this.form.goods_filter.indexOf(tagItem.id) === -1){
          checkAll = false ;
          return;
        }
      })
      this.form.filtering[index].checkAll = checkAll;
    })
  },
  methods: {
    handleCheckAllChange(status,tagItem){
      if(status){
        for(let item of tagItem.value){
          this.form.goods_filter.push(item.id)
        }
      }else{
        for(let item of tagItem.value){
          let key = this.form.goods_filter.indexOf(item.id)
          if(key !== -1){
            this.form.goods_filter.splice(key,1)
          }
        }
      }
      this.form.goods_filter = [...new Set(this.form.goods_filter)]
    },
    handleCheckChange(data,tagIndex,tagItem){
      if(!data) {
        // 有一个是false肯定不是全选
        tagItem.checkAll = false
        return;
      }

      let checkAll = true;
      for(let item of tagItem.value){
        if(this.form.goods_filter.indexOf(item.id) === -1){
          checkAll = false;
          return;
        }
      }
      tagItem.checkAll = checkAll
    },
    validate(){
      return {
        goods_filter:this.form.goods_filter 
      }
    }
  },
  style: `
    #tags .el-checkbox:last-of-type{
      margin-right:30px
    }
  `,
})