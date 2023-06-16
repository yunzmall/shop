define({
    name: "contact_tel",
    template: `
    <div>
      <el-form label-width="20%">
        <div id="vue_head">
          <div class="base_set">
            <div class="vue-main-title">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">
                    联系电话
                </div>
            </div>
            <div class="vue-main-form">
                <el-form-item label="联系电话">
                  <el-input v-model="form.contact_tel" style="width: 35%"></el-input>
                </el-form-item>
            </div>
          </div>
        </div>
      </el-form>
    </div>
  `,
    data(){
        return {
            contact_tel: '',
        }
    },
    style: ``,
    mounted () {
        this.contact_tel = this.form.contact_tel ? this.form.contact_tel : ''
    },
    methods: {
        validate(){
            return {
                contact_tel: this.form.contact_tel,
            }
        }
    },
    props: {
        form: {
            default() {
                return {}
            }
        }
    }
})