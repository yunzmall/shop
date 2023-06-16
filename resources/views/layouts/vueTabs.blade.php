<div class="vue-head">
    <el-tabs v-model="activeName" @tab-click="handleClick()">
        <el-tab-pane v-for="(item,index) in tab_list" :key="index" :label="item.title" :name="item.value"></el-tab-pane>
    </el-tabs>
</div>