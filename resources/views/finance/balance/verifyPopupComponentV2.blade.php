<el-dialog
        title="二次校验"
        :visible.sync="dialog_visible_verify"
        width="27%">
    <el-form ref="loginForm" :model="check_data">
        <el-form-item label="手机号" prop="phone">
            <span>[[verify_phone]]</span>
            <el-button :disabled="disabled" @click="getCode" style="margin-left: 30px">[[time_butont]]</el-button>
        </el-form-item>
        <el-form-item label="验证码" prop="code">
            <el-input style="width: 30%" type="text" maxlength="6" placeholder="验证码" v-model="check_data.code" style="width: 250px">
            </el-input>
            <div class="verify-form-row">
                通过验证后[[verify_expire]]分钟内无需再次验证
            </div>

        </el-form-item>

    </el-form>
    <div style="text-align: center">
        <el-button @click="dialog_visible_verify = false">取 消</el-button>
        <el-button type="primary" @click="check()">确 定</el-button>
    </div>

</el-dialog>