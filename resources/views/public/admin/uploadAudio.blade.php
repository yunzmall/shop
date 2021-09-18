            <!-- 音频上传 -->
             <!-- <el-button type="text" @click="aduioDialogVisible = true">音频上传</el-button>
                <el-dialog
                    :visible.sync="aduioDialogVisible"
                    width="50%"
                    :before-close="handleClose">
                    <el-tabs v-model="activeName" @tab-click="handleClick">
                        <el-tab-pane label="素材库" name="first">
                            <div class="clearfix">
                                <el-button class="fr">上传音频</el-button>
                            </div>
                            <ul class="aduio-box clearfix">
                                <li class="aduio-item fl">
                                     <div class="fl">
                                        <el-checkbox v-model="aduioChecked"></el-checkbox>
                                        <span class="audio-title">我一天一个时间只做一件事情</span>
                                        <p style="margin-top:60px">2017-12-415</p>
                                     </div>
                                     <div class="fr aduio-right">
                                        <img src="../../../../static/images/play.png" alt="" v-if="1">
                                        <img src="../../../../static/images/puse.png" alt="" v-if="0">
                                        <p>02:25</p>
                                     </div>
                                </li>
                                <li class="aduio-item fl">
                                     
                                </li>
                                <li class="aduio-item fl">
                                     
                                     </li>
                                     <li class="aduio-item fl">
                                          
                                     </li>
                                     <li class="aduio-item fl">
                                     
                                     </li>
                                     <li class="aduio-item fl">
                                          
                                     </li>
                                     <li class="aduio-item fl">
                                     
                                     </li>
                                     <li class="aduio-item fl">
                                          
                                     </li>
                                  
                            </ul>
                        </el-tab-pane>
                    </el-tabs> 
  
                    <div class="uploading-btn">
                        <span>已选择</span>
                        <span>0/10个音频视频项</span>
                         <span slot="footer" class="dialog-footer">
                            <el-button type="primary" @click="aduioDialogVisible = false" style="margin-left:30%">确 定</el-button>
                            <el-button @click="aduioDialogVisible = false">取 消</el-button>
                        </span>
                    </div>
                </el-dialog> -->


<style>
    /* 上传 */
.aduio-box {
    margin-top:20px
}

.aduio-item {
    padding:10px;
    margin:0 15px 15px 0;
    width:280px;
    height:130px;
    border:1px solid #c8cede;
}

.audio-title {
    display:inline-block;
    width:150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align:middle
}

.aduio-right {
    width:60px;
    height:60px;

}

.aduio-right {
    margin-top:30px;
    text-align:center;
}

.aduio-right img {
    width:40px;
    height:40px;
}

.uploading-btn {
    /* text-align:center; */
}

.uploading-btn span {
    /* text-align:left */
}
.video-box {
    margin:20px 0 30px 0;
    width:100%;
}

.video-box .video-item {
    position: relative;
    padding:10px;
    margin-right:15px;
    width:40%;
    height:130px;
    border:1px solid #c8cede;
}
.checked-pos {
    position: absolute;
    top:10px;
    left:10px;
}



.vedio-file {
    width:150px;
    height:100%
}

.vedio-file video {
    width:150px;
    height:100%
}

.vedio-right {
    text-align:left
}

.vedio-right p{
    margin:20px 0 0 15px;
}
.getNetWork {
    margin:30px 0;
    text-align:center
}
.left-group {
    width:150px;
    
}
.right-img {
    width:80%;
    border-left:1px solid #c8cede
}

.handel {
    margin-top:30px;
   
}
.img-hint {
    height:40px;
    line-height:40px;
    text-align:right;
}

.img-source {
    position: relative;
    margin-left:15px;
    width:140px;
    height:150px;
}

.img-source  img {
    width:100%;
    height:110px;
}
.img-source p {
    padding:0 5px;
    width:100%;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align:middle
}

.img-source .sle-img {
    position:absolute;
    /* display:none; */
    top:5px;
    left:5px;
}

.img-source p {
    margin-top:15px;
    text-align:center;
}

.img-source:hover .img-mark {
    display:block
}

.img-mark {
    position:absolute;
    display:none;
    width:100%;
    height:110px;
    top:0px;
    left:0px;
    background: rgba(41, 186, 156, 0.3);
    border:1px solid rgb(41, 186, 156);
}

.defaultImg {
    width: 150px; 
    height: 150px;
    line-height:150px;
    border:1px solid #c8cede;
    text-align:center;

}

.getNet {
    text-align:center;
}

</style>