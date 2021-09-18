<style>
    html,
    body {
        width: 100%;
        height: 100%;
    }

    .mark {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .el-dialog__wrapper {
        padding: 20px;
        margin: 100px auto;
        width: 800px;
        height: 700px;
        background-color: #fff;
        border-radius: 5px;
    }

    .eixt {
        margin-bottom: 20px;
        width: 100%;
        text-align: right;
        cursor: pointer;
        font-size: 16px;
        color: #909399;
    }

    .top-tab {
        height: 40px;
        border-bottom: 2px solid #e4e7ed;
    }

    .tab-title {
        position: relative;
        bottom: -2px;
        display: inline-block;
        height: 100%;
        line-height: 40px;
        font-size: 14px;
        font-weight: 500;
        margin-right: 20px;
        cursor: pointer;
    }

    .is-active {
        color: #29BA9C;
        border-bottom: 2px solid #29BA9C;
    }

    .upload-btn {
        position: relative;
        float: right;
        padding: 7px 15px;
        background-color: #29BA9C;
        font-size: 12px;
        border-radius: 3px;
        color: #fff;
        cursor: pointer;
    }
    .upload-btn input {
        position: absolute;
        right: 0;
        top: 0;
        opacity: 0;
        cursor: pointer;
    }


    .fl {
        float: left;
    }

    .scroll-box {
        width: 150px;
        height: 475px;
        overflow-y: scroll;
    }

    .left-group {
        padding: 10px 0;
    }

    .D-gro {
        margin: 0;
        width: 148px;
        color: #666;
        font-size: 14px;
        white-space: nowrap;
        padding: 3px 0 3px 0px;
        cursor: pointer;
    }

    /*滚动条整体样式*/
    .scroll-box::-webkit-scrollbar {
        width: 2px;
    }

    /*滚动条滑块*/
    .scroll-box::-webkit-scrollbar-thumb {
        border-radius: 30px;
        background: #29BA9C;
    }

    /*滚动条轨道*/
    .scroll-box::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 1px rgba(0, 0, 0, 0);
        border-radius: 30px;
        background: #f4f4f5;
    }

    .text-bg {
        background: #29BA9C;
        color: #fff;
    }

    .right {
        width: 610px;
        height: 475px;
    }

    .fr {
        float: right;
    }

    .img-source {
        position: relative;
        margin: 10px 0 0 10px;
        width: 290px;
        height: 110px;
        border: 1px solid #ebebeb;
        /* background-color: pink; */
    }

    .img-source img {
        width: 100%;
        height: 100%;;
    }

    img {
        border: 0;
    }

    .img-source p {
        margin-top: 15px;
        text-align: center;
        padding: 0 5px;
        width: 100%;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
        overflow: hidden;
    }

    .img-mark {
        position: absolute;
        /* display: none; */
        visibility:hidden;
        width: 100%;
        height: 110px;
        top: 0px;
        left: 0px;
        background: rgba(41, 186, 156, 0.3);
        border: 1px solid rgb(41, 186, 156);
    }

    .img-source:hover .img-mark {
        
        visibility: visible;
    }

    .img-mark input {
        margin-left: 5px;
       
    }

    input[type="checkbox"] {
        zoom: 120%;
    }

    .img-source .sle-img {
        position: absolute;
        
        top: 5px;
        left: 5px;
    }

    .sel-checkbox {
        margin-left: 5px;
    }

    .foot {
        position: relative;
        margin-top: 15px;
    }

    .newdialog {
        padding: 10px;
        position: absolute;
        display: none;
        background-color: #fff;
        z-index: 2000;
        width: 230px;
        left: -10px;
        top: -136px;
        border-radius: 4px;
        border: 1px solid #ebebeb;

    }

    .clearfix::after {
        content: '';
        display: block;
        clear: both;
    }

    .newgroup {
        color: rgb(64, 158, 255);
        cursor: pointer;
        font-size: 14px;
    }

    .number {
        margin: 0 5px;
        background-color: #f4f4f5;
        color: #666;
        min-width: 30px;
        border-radius: 2px;
    }

    button {
        padding: 0;
        border: none;
    }

    .page,
    .btn-prev,
    .btn-next {
        display: inline-block;
        margin: 0 3px;
        width: 30px;
        height: 30px;
        line-height: 30px;
        font-size: 13px;
        text-align: center;
        color: #666;
        border-radius: 4px;
        background-color: #f4f4f5;
        cursor: pointer;
    }

    .btn-prev,
    .btn-next {
        font-weight: bold;
        border-radius: 4px;
        font-size: 16px;
        cursor: pointer!important;
    }

    .page-active {
        background-color: #29BA9C;
        color: #FFF;
    }

    .foot-btn {
        position: absolute;
        margin-top: 15px;
        font-size: 14px;
    }

    .bule-text {
        color: rgb(64, 158, 255);
    }


    .sure-cancel {
        margin-left: 180px;
    }

    .footbtn {
        width: 100px;
        height: 40px;
        border-radius: 4px;
    }

    .srue {
        margin-right: 20px;
        color: #fff;
        background-color: #29ba9c;
    }

    .triangle {
        position: absolute;
        left: 15%;
        bottom: -16px;
        width: 0;
        height: 0;
        border-width: 8px;
        border-style: solid;
        border-color: #fff transparent transparent transparent;
    }

    .please-inp {
        margin-bottom: 10px;
        color: #666;
        font-size: 14px;
    }

    .group-inp {
        width: 100%;
        height: 30px;
        border: 1px solid #DCDFE6;

    }

    .dio-btn {
        margin-top: 15px;
        text-align: center;
    }

    .new-group {
        width: 50px;
        height: 30px;
        margin: 0 10px;
        border-radius: 4px;
    }

    .new-group-sure {
        background-color: #54c8b0;
        color: #fff;
    }

    .new-group-cancel {
        border: 1px solid #DCDFE6;
        color: #666;
    }

    #sel-img {
        display: block;
    }

    #collect-img {
        display: none;
    }

    .new-img {
        margin-top: 20px;
        width: 150px;
        height: 150px;
        line-height: 150px;
        text-align: center;
        color: #999;
        line-height: 150px;
        text-align: center;
        margin: 20px auto;
    }
    .new-img img {
        width: 100%;
        height: 100%;
        display: block;
        border: none;
    }

    .newlink-box {
        margin-top: 50px;
        margin-bottom: 270px;
        text-align: center;
    }

    .netlink {
        padding: 0 10px;
        width: 450px;
        height: 40px;
        border-radius: 4px;
        border: 1px solid #DCDFE6;
    }

    .conversion {
        text-align: center;
    }

    .conversion-btn {
        margin: 30px auto;
        width: 100px;
        display: inline-block;
        cursor: pointer;
        background: #FFF;
        border: 1px solid #DCDFE6;
        color: #666;
        text-align: center;
        box-sizing: border-box;
        padding: 12px 20px;
        font-size: 14px;
        border-radius: 4px;
    }

    /* 提示信息 */
    .success-tip {
        position: fixed;
        left: 50%;
        top: 60px;
        transform: translate(-50%);
        padding: 10px;
        background-color: #f0f9eb;
        border-radius: 10px;
        color: #67c23a;
        font-size: 14px;
        display: none;
    }

    #none-data {
        margin-top: 10px;
        width: 100%;
        text-align: center;
    }
    #pageNum {
        display: inline-block;
    }

    .shows {
        visibility: visible;
    }

    .nones {
        visibility: hidden;
    }

    .paginations {
          
            font-size: 0;
            text-align: center;
        }

        .paginations .page-li {
            display: inline-block;
            font-size: 15px;
            line-height: 1;
            -ms-user-select: none;
            -moz-user-select: none;
            -webkit-user-select: none;
            user-select: none;
        }
        .paginations .page-li.page-active {
            cursor: default;
            color: #fff;
            border-color: #29BA9C;
            background-color: #29BA9C;
        }

        .paginations .page-li.number-ellipsis {
            border: none;
            cursor: default;
        }
        .paginations .page-number {
            margin: 0 5px;
            padding: 5px 12px;
            text-align: center;
            border-radius: 2px;
            background-color: #f4f4f5;
            cursor: pointer;
        }

        .paginations .page-prev {
            padding: 5px 12px;
            margin-right: 8px;
            background-color: #f4f4f5;
            border-radius: 2px;
            cursor: pointer;
        }

        .paginations .page-next {
            padding:  5px 12px;
            margin-left: 8px;
            background-color: #f4f4f5;
            border-radius: 2px;
            cursor: pointer;
        }

        .paginations .number-ellipsis {
            display: inline-block;
            font-size: 15px;
            padding: 8px 14px;
        }

        .paginations .number-ellipsis.page-hidden {
            display: none;
        }

        #page-go {
            margin-top: 10px;
            text-align: center;
        }
        .video-box {
            position: absolute;
            top: 0;
            left: 0;
            width: 160px;
            height: 110px;
        }
        .right-text {
            position: absolute;
            top: 17px;
            right: 0;
            color: #666;
        }
        .time {
            position: absolute;
            padding: 2px 5px;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            bottom: 10px;
            left: 10px;
            color: #fff;
        }
</style>

<div class="mark" id="mark-box3">
    <div class="el-dialog__wrapper" id="img-dialog3" style="display: block;">
        <!-- 右上角的X -->
        <div class="eixt">X</div>
        <!-- 顶部切换项 -->
        <div class="top-tab">
            <span class="tab-title is-active" onclick="handlSelect()" id="sel-img-btn">选取视频</span>
            <span class="tab-title" onclick="newWorkImg()" id="net-img3-btn">提取网络视频</span>
            <div class="upload-btn">点击上传<input type="file" title="" id="uploadfle" /></div>
        </div>

        <!-- 选取视频start -->
        <div id="sel-img">
            <div style="height: 475px;" class="clearfix">
                <div class="left fl  scroll-box">
                    <div class="left-group fl bor-right" id="group-item">
                    </div>
                </div>
                <div class="right fr"  id="imgList">
                    
                </div>
            </div>
        </div>
        <!-- 选取视频end -->

        <div>
            <div class="foot clearfix" id="foot-box">
                <span class="newgroup fl" onclick="newgroup()">新建分组</span>
                <!-- 新建分组弹窗 -->
                <div class="newdialog" id="new-dialog">
                    <p class="please-inp">请输入分组名</p>
                    <input class="group-inp" id="new-group" type="text" >
                    <div class="dio-btn">
                        <button class="new-group new-group-sure" onclick="srueGroup()">确定</button>
                        <button class="new-group new-group-cancel" onclick="cancleGroup()">取消</button>
                    </div>
                    <div class="triangle"></div>
                </div>
                <div class="fr">
                    <div class="page-box">
                        <!-- <ul class="clearfix paginations">
                        <li class="page-li page-prev no-prev">&lt;</li>
                        <li class="page-li page-number page-active" data-number="1">1</li>
                        
                        <li class="page-li number-ellipsis ellipsis-head" style="display: none;">...</li><li class="page-li page-number " data-number="2">2</li><li class="page-li page-number " data-number="3">3</li><li class="page-li page-number " data-number="4">4</li><li class="page-li page-number " data-number="5">5</li><li class="page-li page-number " data-number="6">6</li>
                        <li class="page-li number-ellipsis ellipsis-tail">...</li>
                        <li class="page-li page-number" data-number="100">100</li>
                        <li class="page-li page-next">&gt;</li>
                        </ul> -->
                    </div>
                </div>
            </div>
            </div>

        <!-- 提取网络视频start -->
        <div id="collect-img">
            <!-- <div class="new-img">
                <img src="" alt=""  title="" id="show-net-img3">
            </div> -->

            <!-- <p style="text-align: center; color:#666">输入视频链接</p> -->

            <div class="newlink-box">
                <input type="text" class="netlink" id="net-img3" placeholder="视频链接"  onfocus="onfocusClick()" onblur="onblurClick()">
            </div>

            <!-- <div class="conversion">
                <button class="conversion-btn" onclick="conversion()">转化</button>
            </div> -->
        </div>

        <div class="foot-btn clearfix">
            <div class="sel-num fl" id="selNum">
                <span>已选择</span>
                <span class="bule-text" id="startNum">0</span>
                <span class="bule-text">/</span>
                <span class="bule-text" id="totalNum">100</span>
                <span class="bule-text">个视频</span>
            </div>
            <div class="sure-cancel fl">
                <button class="footbtn srue" onclick="determine()">确定</button>
                <button class="footbtn cancel" onclick="cancle()">取消</button>
            </div>
        </div>
    </div>

</div>

<script>
    var imgDialog = document.getElementById('img-dialog3') //获取整个弹窗的id
    var selImg = document.getElementById('sel-img')
    var collectImg = document.getElementById('collect-img')
    var selImgBtn = document.getElementById('sel-img-btn')
    var netImgBtn = document.getElementById('net-img3-btn')
    var newDialog = document.getElementById('new-dialog')
    var netlink = document.getElementsByClassName('netlink')[0]
    var footBox = document.getElementById('foot-box')
    var startNum = document.getElementById('startNum')
    var totalNum = document.getElementById('totalNum')


    var groupData = [];//分组列表的数据
    var newName = '';//新建分组的名字
    var netIMgLink = '';//网络的视频链接
    var groupId = '';//分组id
    var imgList = []
    var total = null
    var tabTag = ''
    var videoLink = ''


    function selcetImg(str) {
        imgDialog.css("display", "block");
    }

    function getGroupList() {
        var str = ''
        $.ajax({
            url: "{!! yzWebUrl("setting.media.tags") !!}",   //请求接口的地址
            type: "POST",                                   //请求的方法GET/POST
            data: {                                        //需要传递的参数
                source_type: 3,
            },
            async : false,                                  //将ajax请求设置为同步请求
            success: function (res) {                      //请求成功后的操作
               if(res.result == 1) {
                    groupData = res.data
                    groupData.forEach((item,index) => {
                        str += `<p class="D-gro"  ids=${item.id === ''?'all':item.id}>${item.title}(<span id=${item.id === ''?'all':item.id}>${item.source_count}</span>)</p>`
                    })
                    $('#group-item').html(str);
                    $('#group-item').find('p:first').addClass('text-bg');//选取第一个默认元素添加背景
               }                                           
            },
            error: function (err) {                       //请求失败后的操作
                console.log(err);                          //请求失败在控制台输出22
            }
        })
    }

    getGroupList()

    // 获取新建分组的输入框的内容
    function groupInp(val) {
        console.log(val)
    }

    function handChecked(checkbox) {
        console.log(checkbox.checked)
    }

    function handleGroup() {
        console.log(this);
    }

    function handlSelect() {
        selImg.style.display = 'block';
        collectImg.style.display = 'none';
        selImgBtn.classList.add("is-active")
        netImgBtn.classList.remove("is-active");
        imgDialog.style.height = "700px"
        footBox.style.display="block"
        tabTag = ''

    }

    function newWorkImg() {
        selImg.style.display = 'none';
        collectImg.style.display = 'block';
        netImgBtn.classList.add("is-active")
        selImgBtn.classList.remove("is-active");
        imgDialog.style.height = "560px"
        footBox.style.display="none"
        tabTag = '1'

    }

    function newgroup() {
        newDialog.style.display = "block"
    }

    //确定新建分组
    function srueGroup() {
        newName=document.getElementById("new-group").value;
        if(newName == '') {
            alert('分组名不能为空!')
            return
        }
        $.ajax({
            url: "{!! yzWebUrl("setting.media.addTag") !!}",   //请求接口的地址
            type: "POST",                                   //请求的方法GET/POST
            data: {                                        //需要传递的参数
                source_type:3,
                title:newName
            },
            async : false,                                  //将ajax请求设置为同步请求
            success: function (res) {                      //请求成功后的操作
                console.log(res) 
                if(res.result == 1) {
                    alert('新建分组成功')
                    getGroupList()
                    getImgList(1,'')
                    newDialog.style.display = "none"
                    document.getElementById("new-group").value="";
                    startNum.innerHTML = '0'
                    imgObj = {}
                    imgArr =[]
                }
            },
            error: function (err) {                       //请求失败后的操作
                console.log(err);                          //请求失败在控制台输出22
            }
        })
    }

    function cancleGroup() {
        newDialog.style.display = "none"
        document.getElementById("new-group").value="";
    }

    function onfocusClick() {
        console.log('聚焦了')
        netlink.style.border = '1px solid #29ba9c'
        tabTag = '1'
    }

    function onblurClick() {
        netlink.style.border = '1px solid #DCDFE6'
    }

    function getImgList(page,tag_id) {//默认请求全部视频
        $.ajax({
            url: "{!! yzWebUrl("upload.uploadV3.getVideo") !!}",  
            type: "POST",                                       
            data: {
                page:page,
                pageSize: 8,                                  
                tag_id:tag_id,
            },
            async : false,                                 
            success: function (res) {                     
                if(res.result == 1) {
                   imgList = res.data.data
                   total = res.data.total
                   let str = ''
                   imgList.forEach((item,index) => {
                        str += ` <div class="img-source fl" id="sel-input">
                        <video src=${item.url} class="video-box" />
                        <div class="right-text">
                            <p>${item.filename}</p>
                            <p>${item.created_at}</p>
                        </div>
                        <span class="time">${Math.floor(item.timeline / 60)}:${Math.floor(item.timeline % 60) >= 10?Math.floor(item.timeline % 60):'0' + Math.floor(item.timeline % 60)}</span>
                        <div class="img-mark" id=${item.id}>
                            <input type="checkbox" class="sel-checkbox" srcs=${item.url}  id=${item.id} attachments=${item.attachment} />
                        </div>
                        </div>`
                   })

                   if(imgList.length <= 0) {
                        str = `<div id="none-data">暂无数据</div>`
                   }
                   $('#imgList').html(str)
                }
            },
            error: function (err) {                     
                console.log(err);                         
            }
        })
    };


    // 点击上传
    var file = document.getElementById('uploadfle')
    file.onchange = function () {
        var fileData = this.files[0]; //获取到一个FileList对象中的第一个文件( File 对象),是我们上传的文件
        let fd = new FormData();
        fd.append("file", fileData);
        $.ajax({
            url: "{!! yzWebUrl("upload.uploadV3.upload") !!}"+'&upload_type=video'+"&tag_id=" + groupId, 
            type: "POST", 
            contentType: false,
            // 告诉jQuery不要去设置Content-Type请求头
            processData: false,
            // 告诉jQuery不要去处理发送的数据                                     
            data:fd ,                                          
            async : false,                                 
            success: function (res) {                     
                console.log(res) 
                if(res.result == 1) {
                    alert('视频上传成功!')
                    getImgList(1,groupId)
                    console.log(imgList,'11111111111111')
                    imgArr = []
                    imgObj ={}
                    let str = ''
                   imgList.forEach((item,index) => {
                        str += `<div class="img-source fl" id="sel-input">
                        <video src=${item.url} class="video-box" />
                        <div class="right-text">
                            <p>${item.filename}</p>
                            <p>${item.created_at}</p>
                        </div>
                        <span class="time">${Math.floor(item.timeline / 60)}:${Math.floor(item.timeline % 60) >= 10?Math.floor(item.timeline % 60):'0' + Math.floor(item.timeline % 60)}</span>
                        <div class="img-mark" id=${item.id}>
                            <input type="checkbox" class="sel-checkbox" srcs=${item.url}  id=${item.id} attachments=${item.attachment} />
                        </div></div>`
                   })


                   $('#imgList').html(str)

                   if(total == 0) {
                    totalPage = 1;
                    } else {
                        var totalPage =  Math.ceil(total/12)
                    }
                    var  slp = new SimplePagination3(totalPage)
                    slp.init({
                        container: '.page-box',
                        // maxShowBtnCount: 3,
                        onPageChange: state => { 
                            page = state.pageNumber
                            console.log(page)
                            getImgList(page,groupId)
                            startNum.innerHTML = '0'
                            imgObj = {}
                            imgArr =[]
                        }
                    })
                    totalNum.innerHTML = total

                     slectImg()
                    // 上传成功默认选中
                    console.log(document.getElementsByClassName('sel-checkbox')[0])
                    var defaultSel = document.getElementsByClassName('sel-checkbox')[0]
                    defaultSel.checked = true
                    defaultSel.parentNode.classList.add('shows')

                    // 可以勾选多个的时候执行
                    if(tag== "more") {
                        ids = defaultSel.attributes["id"].value
                        let path = defaultSel.attributes["attachments"].value
                        let paths = defaultSel.attributes["srcs"].value
                        imgArr.push({"id":ids,"url":paths,"attachment":path})
                        startNum.innerHTML = imgArr.length  
                    }

                    // 只能勾选单个的时候执行
                    if(tag== "single") {
                        id = defaultSel.attributes["id"].value
                        let path = defaultSel.attributes["attachments"].value
                        let paths = defaultSel.attributes["srcs"].value
                        imgObj.id = id
                        imgObj.url = paths
                        imgObj.attachment = path
                        startNum.innerHTML = '1'  
                    }

                    // let gid = groupId
                   
                    // var newNum = document.getElementById(gid)
                    // console.log(document.getElementById(gid),'000000000000000000')
                   
                    let gid = getEle()
                    console.log(gid)
                    if(gid == '') {
                        gid ='all'
                    }

                    var newNum = document.getElementById(gid)
                    let num = Number(newNum.innerHTML)
                    num += 1
                    newNum.innerHTML = num

                    file.value = ''
                   
                    
                }else {
                    alert(res.msg)
                }
            },
            error: function (err) {                     
                console.log(err); 
                                         
            }
        })

     }

    function getEle() {
        //  console.log(groupId,'ididiidididididi')
         return groupId
     }
    
    function prevClick() {
        console.log(page)
    }

    function nextClick() {
        console.log(page)
    }
        
    (function() {
        getImgList(1,'');
    })();

    function getNteImg(url,tag_id) {
        $.ajax({
            url: "{!! yzWebUrl("upload.uploadV3.fetch") !!}",   //请求接口的地址
            type: "POST",                                       //请求的方法GET/POST
            data: {                                            //需要传递的参数
                url:url,
                tag_id:tag_id
            },
            async : false,                                  //将ajax请求设置为同步请求
            success: function (res) {                      //请求成功后的操作
                console.log(res) 
                if(res.result == 1) {
                }
            },
            error: function (err) {                       //请求失败后的操作
                console.log(err);                          //请求失败在控制台输出22
            }
        })
    }

    function conversion() {
        netIMgLink = document.getElementById("net-img3").value;
        console.log(netIMgLink)
        var showNetImg = document.getElementById('show-net-img3')
        showNetImg.src = netIMgLink
        getNteImg(netIMgLink,0)//转入未分组，未分组的id是0
        document.getElementById("net-img3").value = '';
        alert('视频提取到未分组')
    }

    var selCheckbox = document.getElementsByClassName('sel-checkbox')
    
    

   function  checkboxOnclick() {
       
    }


    var checkbox = document.getElementsByClassName('sel-checkbox')
    var imgMark = document.getElementsByClassName('img-mark')

    var imgObj = {}
    var imgArr = []

    var tag = 'single';//单个选择的标识
    // var tag = 'more';//多个选择的标识
    var id = null
    var ids = null


    function slectImg() {
        imgObj ={}
        if(tag == 'single' ) {
            for(var i = 0;i < checkbox.length;i++) {
                checkbox[i].onchange = function(e) {
                let checkedStatus = this.checked;
                    if(checkedStatus == true) {
                        id = this.attributes["id"].value
                        var img = document.getElementById(id)
                        img.classList.add("shows");
                        let path = this.attributes["attachments"].value
                        let paths = this.attributes["srcs"].value
                        imgObj.id = id
                        imgObj.url = paths
                        imgObj.attachment = path
                        startNum.innerHTML = '1'  
                    } else {
                        imgObj ={}
                        imgCount = 0
                        var img = document.getElementById(id)
                        img.classList.remove("shows");
                        startNum.innerHTML = '0'
                    }
                for(var i = 0;i < checkbox.length;i++) {
                    checkbox[i].checked = false
                    checkbox[i].parentNode.classList.remove("shows")
                    if(id == checkbox[i].id && checkedStatus == true) {
                        checkbox[i].checked = true
                        var img = document.getElementById(id)
                        img.classList.add("shows");
                    }
                }
            }
            }
        }
        
        if(tag == 'more') {
            for(var i = 0;i < checkbox.length;i++) {
                checkbox[i].onchange = function(e) {
                    let checkedStatus = this.checked;
                    if(checkedStatus == true) {
                        id = this.attributes["id"].value
                        var img = document.getElementById(id)
                        img.classList.add("shows");
                        let path = this.attributes["attachments"].value
                        let paths = this.attributes["srcs"].value
                        imgArr.push({"id":id,"url":paths,"attachment":path})
                        console.log(imgArr,'执行了吗')
                        startNum.innerHTML = imgArr.length  
                    } else {
                        ids = this.attributes["id"].value
                        imgArr.forEach((item,index) => {
                            if(item.id == ids) {
                                imgArr.splice(index,1)
                            }
                        })
                        var img = document.getElementById(ids)
                        img.classList.remove("shows");
                        startNum.innerHTML = imgArr.length
                    }
                }
            }
        }
    }

    // (function() {
        slectImg()
    // })();


    class SimplePagination3 {
            constructor(totalPageCount) {
                if (!totalPageCount) return
                this.state = {
                    pageNumber: 1,
                    totalPageCount
                }
            }

            init(paramsObj) {
                let state = this.state
                state.container = paramsObj.container || 'body'
                state.maxShowBtnCount = paramsObj.maxShowBtnCount || 5
                state.pCName = paramsObj.pCName || 'page-li',
                state.activeCName = paramsObj.activeCName || 'page-active',
                state.dataNumberAttr = paramsObj.dataNumberAttr || 'data-number',
                state.prevCName = paramsObj.prevCName || 'page-prev',
                state.nextCName = paramsObj.nextCName || 'page-next',
                state.disbalePrevCName = paramsObj.disbalePrevCName || 'no-prev',
                state.disbaleNextCName = paramsObj.disbaleNextCName || 'no-next',
                state.pageNumberCName = paramsObj.pageNumberCName || 'page-number'
                state.swEvent = paramsObj.swEvent || 'click'
                state.onPageChange = paramsObj.onPageChange
                state.totalPageCount > state.maxShowBtnCount + 2 && (state.activePosition = Math.ceil(state.maxShowBtnCount / 2))
                this.renderPageDOM()
            }

            switchPage() {
                let state = this.state
                let pCNameList = this.selectorEle('.' + state.pCName, true)
                let pageNumber
                pCNameList.forEach(item => {
                    item.addEventListener(state.swEvent, e => {
                        const currentPageEle = e.target
                        if (this.hasClass(currentPageEle, state.activeCName)) return
                        let dataNumberAttr = currentPageEle.getAttribute(state.dataNumberAttr)
                        if (dataNumberAttr) {
                            // 点击 数字 按钮
                            pageNumber = +dataNumberAttr
                        } else if (this.hasClass(currentPageEle, state.prevCName)) {
                            // 点击 上一页 按钮
                            state.pageNumber > 1 && (pageNumber = state.pageNumber - 1)
                        } else if (this.hasClass(currentPageEle, state.nextCName)) {
                            // 点击 下一页 按钮
                            state.pageNumber < state.totalPageCount && (pageNumber = state.pageNumber + 1)
                        }
                        pageNumber && this.gotoPage(pageNumber)
                    })
                })
            }
            gotoPage(pageNumber) {
                let state = this.state
                let evaNumberLi = this.selectorEle('.' + state.pageNumberCName, true)
                let len = evaNumberLi.length
                if (!len || this.isIllegal(pageNumber)) return
                // 清除 active 样式
                this.removeClass(this.selectorEle(`.${state.pCName}.${state.activeCName}`), state.activeCName)
                if (state.activePosition) {
                    let rEllipseSign = state.totalPageCount - (state.maxShowBtnCount - state.activePosition) - 1
                    // 左边不需要出现省略符号占位
                    if (pageNumber <= state.maxShowBtnCount && (pageNumber < rEllipseSign)) {
                        if (+evaNumberLi[1].getAttribute(state.dataNumberAttr) > 2) {
                            for (let i = 1; i < state.maxShowBtnCount + 1; i++) {
                                evaNumberLi[i].innerText = i + 1
                                evaNumberLi[i].setAttribute(state.dataNumberAttr, i + 1)
                            }
                        }
                        this.hiddenEllipse('.ellipsis-head')
                        this.hiddenEllipse('.ellipsis-tail', false)
                        this.addClass(evaNumberLi[pageNumber - 1], state.activeCName)
                    }
                    // 两边都需要出现省略符号占位
                    if (pageNumber > state.maxShowBtnCount && pageNumber < rEllipseSign) {
                        this.hiddenEllipse('.ellipsis-head', pageNumber === 2 && state.maxShowBtnCount === 1)
                        this.hiddenEllipse('.ellipsis-tail', false)
                        for (let i = 1; i < state.maxShowBtnCount + 1; i++) {
                            evaNumberLi[i].innerText = pageNumber + (i - state.activePosition)
                            evaNumberLi[i].setAttribute(state.dataNumberAttr, pageNumber + (i - state.activePosition))
                        }
                        this.addClass(evaNumberLi[state.activePosition], state.activeCName)
                    }
                    // 右边不需要出现省略符号占位
                    if (pageNumber >= rEllipseSign) {
                        this.hiddenEllipse('.ellipsis-tail')
                        this.hiddenEllipse('.ellipsis-head', false)
                        if (+evaNumberLi[len - 2].getAttribute(state.dataNumberAttr) < state.totalPageCount - 1) {
                            for (let i = 1; i < state.maxShowBtnCount + 1; i++) {
                                evaNumberLi[i].innerText = state.totalPageCount - (state.maxShowBtnCount - i) - 1
                                evaNumberLi[i].setAttribute(state.dataNumberAttr, state.totalPageCount - (state.maxShowBtnCount - i) - 1)
                            }
                        }
                        this.addClass(evaNumberLi[(state.maxShowBtnCount + 1) - (state.totalPageCount - pageNumber)], state.activeCName)
                    }
                } else {
                    // 不需要省略符号占位
                    this.addClass(evaNumberLi[pageNumber - 1], state.activeCName)
                }
                state.pageNumber = pageNumber
                state.onPageChange && state.onPageChange(state)
                // 判断 上一页 下一页 是否可使用
                this.switchPrevNextAble()
            }

            switchPrevNextAble() {
                let state = this.state
                let prevBtn = this.selectorEle('.' + state.prevCName)
                let nextBtn = this.selectorEle('.' + state.nextCName)
                // 当前页已经是第一页，则禁止 上一页 按钮的可用性
                state.pageNumber > 1
                    ? (this.hasClass(prevBtn, state.disbalePrevCName) && this.removeClass(prevBtn, state.disbalePrevCName))
                    : (!this.hasClass(prevBtn, state.disbalePrevCName) && this.addClass(prevBtn, state.disbalePrevCName))
                // 当前页已经是最后一页，则禁止 下一页 按钮的可用性
                state.pageNumber >= state.totalPageCount
                    ? (!this.hasClass(nextBtn, state.disbaleNextCName) && this.addClass(nextBtn, state.disbaleNextCName))
                    : (this.hasClass(nextBtn, state.disbaleNextCName) && this.removeClass(nextBtn, state.disbaleNextCName))
            }

            renderPageDOM() {
                // 渲染页码DOM
                let state = this.state
                let pageContainer = this.selectorEle(state.container)
                if (!pageContainer) return
                let { totalPageCount, pCName, prevCName, disbalePrevCName, pageNumberCName,
                    activeCName, dataNumberAttr, maxShowBtnCount, nextCName, disbaleNextCName } = state
                let paginationStr = `
    <ul class="clearfix paginations">
    <li class="${pCName} ${prevCName} ${disbalePrevCName}"><</li>
    <li class="${pCName} ${pageNumberCName} ${activeCName}" ${dataNumberAttr}='1'>1</li>
    `
                if (totalPageCount - 2 > maxShowBtnCount) {
                    paginationStr += `
      <li class="${pCName} number-ellipsis ellipsis-head" style="display: none;">...</li>`
                    for (let i = 2; i < maxShowBtnCount + 2; i++) {
                        paginationStr += `<li class="${pCName} ${pageNumberCName} ${i === 1 ? activeCName : ''}" ${dataNumberAttr}='${i}'>${i}</li>`
                    }
                    paginationStr += `
      <li class="${pCName} number-ellipsis ellipsis-tail">...</li>
      <li class="${pCName} ${pageNumberCName}" ${dataNumberAttr}='${totalPageCount}'>${totalPageCount}</li>
      `
                } else {
                    for (let i = 2; i <= totalPageCount; i++) {
                        paginationStr += `<li class="${pCName} ${pageNumberCName}" ${dataNumberAttr}='${i}'>${i}</li>`
                    }
                }
                paginationStr += `<li class="${pCName} ${nextCName}${totalPageCount === 1 ? ' ' + disbaleNextCName : ''}">></li></ul>`
                pageContainer.innerHTML = paginationStr
                // 切换页码
                this.switchPage()
            }

            isIllegal(pageNumber) {
                let state = this.state
                return (
                    state.pageNumber === pageNumber || Math.ceil(pageNumber) !== pageNumber ||
                    pageNumber > state.totalPageCount || pageNumber < 1 ||
                    typeof pageNumber !== 'number' || pageNumber !== pageNumber
                )
            }

            hiddenEllipse(selector, shouldHidden = true) {
                this.selectorEle(selector).style.display = shouldHidden ? 'none' : ''
            }

            selectorEle(selector, all = false) {
                return all ? document.querySelectorAll(selector) : document.querySelector(selector)
            }

            hasClass(eleObj, className) {
                return eleObj.classList.contains(className);
            }

            addClass(eleObj, className) {
                eleObj.classList.add(className);
            }

            removeClass(eleObj, className) {
                if (this.hasClass(eleObj, className)) {
                    eleObj.classList.remove(className);
                }
            }
        }
        totalNum.innerHTML = total
        var totalPage =  Math.ceil(total/8)
        var  slp = new SimplePagination3(totalPage)
        slp.init({
            container: '.page-box',
            onPageChange: state => { 
                page = state.pageNumber
                console.log(page)
            getImgList(page,groupId)
            slectImg()
            startNum.innerHTML = '0'
            imgObj = {}
            imgArr =[]
           

            }
        })
    
    $('#group-item').on('click','.D-gro', function() {
        groupId = $(this).attr('ids')
        $(this).addClass('text-bg');
        $(this).siblings('p').removeClass('text-bg');
        getImgList(1,groupId)
        if(total == 0) {
            totalPage = 1;
        } else {
            var totalPage =  Math.ceil(total/8)
        }
        startNum.innerHTML = '0'
        imgObj = {}
        imgArr =[]
        var  slp = new SimplePagination3(totalPage)
        slp.init({
            container: '.page-box',
            onPageChange: state => { 
                page = state.pageNumber
                console.log(page)
                getImgList(page,groupId)
                slectImg()
                startNum.innerHTML = '0'
                imgObj = {}
                imgArr =[]

            }
        })
        totalNum.innerHTML = total
        slectImg()
        console.log(groupId,'aaaaaaaaa')
    });

    // 点击确定函数，获取勾选视频的数据  单个选择获取的是imgObj 多个选择获取到的是imgArr tabTag='1'是提取网络视频
    function determine() {
        var markBox = document.getElementById('mark-box3')
        // var imgDialog = document.getElementById('img-dialog3')
        var netvideo = document.getElementById('net-img3')
        // markBox.style.display = "none"
        // imgDialog.style.display = "none"
        console.log(imgObj)
        console.log(imgArr)
        if(tabTag == '1') {
            videoLink = netvideo.value//获取输入的网络视频的绝对地址
        }
        tabTag = ''

    }

    function cancle() {
        var markBox = document.getElementById('mark-box3')
        // var imgDialog = document.getElementById('img-dialog3')
        markBox.style.display = "none"
        // imgDialog.style.display = "none"
        tabTag = ''

    }
    function showVideoDialog2() {
        $("#mark-box3").show()
    }

    // document.onclick = function() {
    //     newDialog.style.display = "none"
    // }

</script>
