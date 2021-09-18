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
        z-index: 9999;
        display: none;
    }

    .el-dialog__wrapper {
        padding: 20px;
        margin: 100px auto;
        width: 800px;
        height: 700px;
        background-color: #fff;
        border-radius: 5px;
    }

    @media (max-width: 991px) {
        .el-dialog__wrapper {
            width: 820px;
            overflow-y: auto;
        }
    }

    .eixt {
        padding-bottom: 10px;
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
        height: 100%;
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
        overflow-y: auto;
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
        width: 7.3vw;
        height: 7.3vw;
    }

    .img-source img {
        width: 100%;
        height:100%;
        object-fit: contain;
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
        height: 100%;
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
        object-fit: cover;
    }
    .new-img img {
        width: 100%;
        height: 100%;
        display: block;
        border: none;
    }

    .newlink-box {
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

        /* 多图上传 */
        .multi-img-details{margin-top:.5em;position: relative;}
        .multi-img-details .multi-item{max-width: 150px!important; position: absolute!important;left: 0!important;top: 0!important; margin-right: 18px;}
        .multi-img-details .multi-item .img_box{width: 150px!important;height: 150px!important;}
        .multi-img-details .multi-item img{width: 100%!important; height: 100%!important;}
        .multi-img-details .multi-item em{position:absolute; top: 0; right: -14px;}
        #img-dialog.el-dialog__wrapper {
            padding: 20px!important;
            margin: 100px auto!important;
            width: 820px!important;
            height: 77.8vh!important;
            background-color: #fff!important;
            border-radius: 5px!important;
            box-sizing: border-box;
        }
</style>

<div class="mark" id="mark-box">
    <div class="el-dialog__wrapper" id="img-dialog" style="display: block;">
        <!-- 右上角的X -->
        <div class="eixt" onclick="closeImgDialog()"><i class="el-icon-close"></i></div>
        <!-- 顶部切换项 -->
        <div class="top-tab">
            <span class="tab-title is-active" onclick="handlSelect()" id="sel-img-btn">选取图片</span>
            <span class="tab-title" onclick="newWorkImg()" id="net-img-btn">提取网络图片</span>
            <div class="upload-btn">点击上传<input type="file" accept="image/*" title="" id="uploadfle" /></div>
        </div>

        <!-- 选取图片start -->
        <div id="sel-img">
            <div style="display:flex">
                <div class="scroll-box" style="flex-shrink: 0;">
                    <div class="left-group fl bor-right" id="group-item">
                    </div>
                </div>
                <div>
                    <div class="el-date-editor el-input el-input--prefix el-input--suffix el-date-editor--month" style="margin:20px 0 0 10px;">
                        <input type="month" autocomplete="off" name="imageFilterTime" placeholder="选择月" class="el-input__inner" onchange="getImgList(1,'')">
                        <!-- <span class="el-input__prefix"><i class="el-input__icon el-icon-date"></i></span> -->
                        <span class="el-input__suffix">
                            <span class="el-input__suffix-inner">
                                <i class="el-input__icon"></i>
                            </span>
                        </span>
                    </div>
                    <div id="imgList"></div>
                </div>
            </div>
        </div>
        <!-- 选取图片end -->

        <div>
            <div class="foot clearfix" id="foot-box">
                <span class="newgroup fl" onclick="newgroup()">新建分组</span>
                <!-- 新建分组弹窗 -->
                <div class="newdialog" id="new-dialog">
                    <p class="please-inp">请输入分组名</p>
                    <input class="group-inp" id="new-group" type="text" >
                    <div class="dio-btn">
                        <button class="new-group new-group-sure" onclick="srueGroup()" type="button">确定</button>
                        <button class="new-group new-group-cancel" onclick="cancleGroup()" type="button">取消</button>
                    </div>
                    <div class="triangle"></div>
                </div>
                <div class="fr">
                    <div class="page-box">

                    </div>
                </div>
            </div>
        </div>

        <!-- 提取网络图片start -->
        <div id="collect-img">
            <div class="new-img" style="display:none">
                <img id="show-net-img" >
            </div>

            <p style="margin-top:5px;text-align: center; color:#666">输入图片链接</p>

            <div class="newlink-box">
                <input type="text" class="netlink" id="net-img" placeholder="图片链接" onfocus="onfocusClick()" onblur="onblurClick()">
            </div>

            <div class="conversion">
                <button class="conversion-btn" onclick="conversion()" type="button">转化</button>
            </div>
        </div>

        <div class="foot-btn clearfix">
            <div class="sel-num fl" id="selNum">
                <span>已选择</span>
                <span class="bule-text" id="startNum">0</span>
                <span class="bule-text">/</span>
                <span class="bule-text" id="totalNum">100</span>
                <span class="bule-text">个图片</span>
            </div>
            <div class="sure-cancel fl">
                <button class="footbtn srue" onclick="determine()" type="button">确定</button>
                <button class="footbtn cancel" onclick="cancle()" type="button">取消</button>
            </div>
        </div>
    </div>
</div>

<script>
    var input_image_name = ""
    var byte_type = ""
    var imgDialog = document.getElementById('img-dialog') //获取整个弹窗的id
    var selImg = document.getElementById('sel-img')
    var collectImg = document.getElementById('collect-img')
    var selImgBtn = document.getElementById('sel-img-btn')
    var netImgBtn = document.getElementById('net-img-btn')
    var newDialog = document.getElementById('new-dialog')
    var netlink = document.getElementsByClassName('netlink')[0]
    var footBox = document.getElementById('foot-box')
    var startNum = document.getElementById('startNum')
    var totalNum = document.getElementById('totalNum')
    var checkbox = document.getElementsByClassName('sel-checkbox')
    var imgMark = document.getElementsByClassName('img-mark')

    var imgObj = {}
    var imgArr = [] //选中的图片
    var tag = 'single';//单个选择的标识
    // var tag = 'more';//多个选择的标识
    var id = null;
    var ids = null;

    var groupData = [];//分组列表的数据
    var newName = '';//新建分组的名字
    var netIMgLink = '';//网络的图片链接
    var groupId = '';//分组id
    var imgList = [];
    var total = null;
    var last_page = 0;
    var more_img_arr = [];//当前显示的图片链接（不包括删除）

    //拖放变量初始化配置
    var item_w,item_h,col_len,few_len,box_w,difNum;
    var box_h = [];
    var delete_num = [];//删除数量
    var arrIndex = []; //展示的图片下标
    var srcArr =[];//存所有图片链接（包括删除）
    var current_element_num = 0;

    //已保存的图片加拖放属性
    $(document).ready(function() {
        for(let i=0;i<$('.multi-img-details').length;i++) {
            // 循环每个多图上传
            let arr = [];
            let num_arr = [];
            let count = $('.multi-img-details').eq(i).children('.multi-item').length;//item数量
            if($('.multi-img-details').eq(i).children('.multi-item').length > 0) {

                $('.multi-img-details').eq(i).children('.multi-item').each(function(index){
                    $(this).addClass("items");
                    $(this).addClass(`${i}-element${index}`);
                    let val = $(this).find("input").val();
                    arr.push(val);
                    num_arr.push(index);
                })
            }

            delete_num.push(0);
            more_img_arr.push(arr);
            srcArr.push(JSON.parse(JSON.stringify(arr)));  // 深拷贝
            arrIndex.push(num_arr);
            info(count,i);
            redraw(0,i);
            tuofang(i);
        }
    })

    function selcetImg(str) {
        imgDialog.css("display", "block");
    }

    function getGroupList() {
        var str = ''
        $.ajax({
            url: '{!! yzWebUrl("setting.media.tags") !!}',   //请求接口的地址
            type: "POST",                                   //请求的方法GET/POST
            data: {                                        //需要传递的参数
                source_type: 1,
            },
            async : false,                                  //将ajax请求设置为同步请求
            success: function (res) {                      //请求成功后的操作
               if(res.result == 1) {
                    groupData = res.data
                    groupData.forEach((item,index) => {
                        str += `<p class="D-gro" ids=${item.id === ''?'all':item.id}>${item.title}(<span id=${item.id === ''?'all':item.id}>${item.source_count}</span>)</p>`
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

    getGroupList()//调用 不可去掉

    function handlSelect() {
        selImg.style.display = 'block';
        collectImg.style.display = 'none';
        selImgBtn.classList.add("is-active")
        netImgBtn.classList.remove("is-active");
        imgDialog.style.height = "700px"
        footBox.style.display="block"

    }

    function newWorkImg() {
        selImg.style.display = 'none';
        collectImg.style.display = 'block';
        netImgBtn.classList.add("is-active")
        selImgBtn.classList.remove("is-active");
        imgDialog.style.height = "560px"
        footBox.style.display="none"

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
                source_type:1,
                title:newName
            },
            async : false,                                  //将ajax请求设置为同步请求
            success: function (res) {                      //请求成功后的操作
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
        netlink.style.border = '1px solid #29ba9c'
    }

    function onblurClick() {
        netlink.style.border = '1px solid #DCDFE6'
    }

    function getImgList(page,tag_id) {//默认请求全部图片
        let filterTime=$("input[name='imageFilterTime']").val();
        console.log(filterTime);
        if(filterTime){
            filterTime={
                year:parseInt(filterTime.split("-")[0]),
                month:parseInt(filterTime.split("-")[1])
            }
        }else {
            filterTime={
                year:null,
                month:null
            };
        }
        $.ajax({
            url: '{!! yzWebUrl("upload.uploadV3.getImage") !!}',
            type: "POST",
            data: {
                page:page,
                pageSize: 12,
                tag_id:tag_id,
                date:filterTime
            },
            async : false,
            success: function (res) {
                if(res.result == 1) {
                   imgList = res.data.data;
                   // console.log(imgList,'11111111111111')
                   total = res.data.total;
                   last_page = res.data.last_page || 1;
                   let str = ''
                   imgList.forEach((item,index) => {
                        str += ` <div class="img-source fl" id="sel-input"><img src="${item.url}" alt="">
                        <!--<p>${item.filename}</p>-->
                        <div class="img-mark" id=${item.id}>
                            <input type="checkbox" class="sel-checkbox"  srcs=${item.url}  id=${item.id} attachments=${item.attachment}>
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

    (function() {
        getImgList(1,'');
    })();

    var file = document.getElementById('uploadfle')
    // 点击上传
    file.onchange = function () {
      console.log(this.files,"this.files")
        // var fileData = this.files[0]; //获取到一个FileList对象中的第一个文件( File 对象),是我们上传的文件
        uploadImage(this.files, 0);

     }

     function uploadImage(files, index) {
      let fileData = files[index];
      let fd = new FormData();
      fd.append("file", fileData);
      $.ajax({
            url: "{!! yzWebUrl("upload.uploadV3.upload") !!}"+'&upload_type=image'+"&tag_id=" + groupId,
            type: "POST",
            contentType: false,
            // 告诉jQuery不要去设置Content-Type请求头
            processData: false,
            // 告诉jQuery不要去处理发送的数据
            data: fd ,
            async : false,
            success: function (res) {
                if(res.result == 1) {
                    // alert('图片上传成功!')
                    if(index < files.length-1) {
                      // 多图采用递归上传
                      index++;
                      uploadImage(files, index);
                      return
                    }
                    getImgList(1, groupId)
                    imgArr = []
                    imgObj ={}
                  // 已经在getImgList方法里面执行；没必要再执行一次
                  //   let str = ''
                  //  imgList.forEach((item,index) => {
                  //       str += ` <div class="img-source fl" id="sel-input"><img src="${item.url}" alt="">
                  //       <!--<p>${item.filename}</p>-->
                  //       <div class="img-mark" id=${item.id}>
                  //           <input type="checkbox" class="sel-checkbox" srcs=${item.url}  id=${item.id} attachments=${item.attachment}>
                  //       </div>
                  //   </div>`
                  //  })

                  //  $('#imgList').html(str)

                  // if(total == 0) {
                  //   totalPage = 1;
                  // }
                    var  slp = new SimplePagination(last_page)
                    slp.init({
                        container: '.page-box',
                        // maxShowBtnCount: 3,
                        onPageChange: state => {
                            page = state.pageNumber
                            // console.log(page)
                            getImgList(page,groupId)
                            slectImg()
                            startNum.innerHTML = '0'
                            imgObj = {}
                            imgArr =[]
                        }
                    })
                    totalNum.innerHTML = total

                    slectImg()
                    // 上传成功默认选中
                    // console.log(document.getElementsByClassName('sel-checkbox')[0])

                    // 可以勾选多个的时候执行
                    if(tag== "more") {
                        let defaultSels = document.getElementsByClassName('sel-checkbox')

                        for(let i = 0; i < files.length; i++) {
                          defaultSels[i].checked = true;
                          defaultSels[i].parentNode.classList.add('shows');
                          ids = defaultSels[i].attributes["id"].value;
                          let path = defaultSels[i].attributes["attachments"].value;
                          let paths = defaultSels[i].attributes["srcs"].value;
                          imgArr.push({"id":ids,"url":paths,"attachment":path});
                        }

                        startNum.innerHTML = imgArr.length
                    }

                    // 只能勾选单个的时候执行
                    if(tag== "single") {
                        let defaultSel = document.getElementsByClassName('sel-checkbox')[0]
                        defaultSel.checked = true
                        defaultSel.parentNode.classList.add('shows')
                        id = defaultSel.attributes["id"].value
                        let path = defaultSel.attributes["attachments"].value
                        let paths = defaultSel.attributes["srcs"].value
                        imgObj.id = id
                        imgObj.url = paths
                        imgObj.attachment = path
                        startNum.innerHTML = '1'
                    }

                    let gid = getEle()
                    // console.log(gid)
                    if(gid == '') {
                        gid ='all'
                    }

                    var newNum = document.getElementById(gid)
                    let num = Number(newNum.innerHTML)
                    num += 1
                    newNum.innerHTML = num
                    file.value = ''

                } else {
                    alert(res.msg)
                }
            },
            error: function (err) {
                console.log(err);
            }
        })
     }

     function getEle() {
         return groupId
     }

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
                if(res.result == 1) {
                }
            },
            error: function (err) {                       //请求失败后的操作
                console.log(err);                          //请求失败在控制台输出22
            }
        })
    }

    function conversion() {
        document.querySelector(".new-img").style.display="block";
        netIMgLink = document.getElementById("net-img").value;
        // console.log(netIMgLink)
        var showNetImg = document.getElementById('show-net-img')
        showNetImg.src = netIMgLink
        getNteImg(netIMgLink,0)//转入未分组，未分组的id是0
        document.getElementById("net-img").value = '';
        alert('图片提取到未分组')
    }


    function slectImg() {
        imgObj ={}
        if(tag == 'single' ) {
            for (let index = 0; index < imgMark.length; index++) {
                    const imgMarkEl = imgMark[index];
                    imgMarkEl.onclick=function(){
                        id=imgMarkEl.id;
                        const checkEl= this.querySelector("input[type=checkbox]");
                        let checkedStatus=checkEl.checked= !checkEl.checked;
                        if(checkedStatus == true) {
                            id = checkEl.attributes["id"].value
                            var img = document.getElementById(id);
                            img.classList.add("shows");
                            let path = checkEl.attributes["attachments"].value
                            let paths = checkEl.attributes["srcs"].value
                            imgObj.id = id
                            imgObj.url = paths
                            imgObj.attachment = path
                            startNum.innerHTML = '1'
                            imgArr.push({"id":id,"url":paths,"attachment":path})
                        } else {
                            imgObj ={}
                            imgCount = 0
                            var img = document.getElementById(id)
                            img.classList.remove("shows");
                            startNum.innerHTML = '0'
                        }
                        imgArr.forEach((item,index) => {
                            if(item.id == id) {
                                imgArr.splice(index,1)
                            }
                        })
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
            for(var i = 0;i < checkbox.length;i++) {
                checkbox[i].change = function(e) {
                let checkedStatus = this.checked;
                    if(checkedStatus == true) {
                        id = this.attributes["id"].value
                        var img = document.getElementById(id);
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
                        img.classList.add("shows");
                    }
                }
            }
            }
        }

        if(tag == 'more') {
            for (let index = 0; index < imgMark.length; index++) {
                    const imgMarkEl = imgMark[index];
                    imgMarkEl.onclick=function(){
                        id=imgMarkEl.id;
                        const checkEl= this.querySelector("input[type=checkbox]");
                        let checkedStatus= checkEl.checked= !checkEl.checked;
                        if(checkedStatus == true) {
                            id = checkEl.attributes["id"].value
                            var img = document.getElementById(id)
                            img.classList.add("shows");
                            let path = checkEl.attributes["attachments"].value
                            let paths = checkEl.attributes["srcs"].value
                            imgArr.push({"id":id,"url":paths,"attachment":path})
                            // console.log(imgArr,'执行了吗');
                            startNum.innerHTML = imgArr.length;
                        } else {
                            ids = checkEl.attributes["id"].value
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
            // console.log(imgArr,22);
            for(var i = 0;i < checkbox.length;i++) {
                checkbox[i].onchange = function(e) {
                     e.stopPropagation();
                    // console.log(i);
                    let checkedStatus = this.checked;
                    if(checkedStatus == true) {
                        id = this.attributes["id"].value
                        var img = document.getElementById(id)
                        img.classList.add("shows");
                        let path = this.attributes["attachments"].value
                        let paths = this.attributes["srcs"].value
                        imgArr.push({"id":id,"url":paths,"attachment":path})
                        // console.log(imgArr,'执行了吗');
                        startNum.innerHTML = imgArr.length;
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
        slectImg() //调用 不可去掉
    // })();


    class SimplePagination {
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

        var  slp = new SimplePagination(last_page)
        slp.init({
            container: '.page-box',
            onPageChange: state => {
            page = state.pageNumber
            // console.log(page)
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
        // if(total == 0) {
        //     totalPage = 1;
        // }
        startNum.innerHTML = '0'
        imgObj = {}
        imgArr =[]
        var  slp = new SimplePagination(last_page)
        slp.init({
            container: '.page-box',
            onPageChange: state => {
                page = state.pageNumber
                // console.log(page)
                getImgList(page,groupId)
                slectImg()
                startNum.innerHTML = '0'
                imgObj = {}
                imgArr =[]
            }
        })
        totalNum.innerHTML = total
        slectImg()
    });

    // 点击确定函数，获取勾选图片的数据  单个选择获取的是imgObj 多个选择获取到的是imgArr 添加图片
    function determine() {
        // var markBox = document.getElementById('mark-box')
        // var imgDialog = document.getElementById('img-dialog')
        // markBox.style.display = "none"
        // imgDialog.style.display = "none"
        $("#imgList .img-mark").each(function() {
            $(this).removeClass("shows");
            $(this).children(".sel-checkbox").prop("checked",false);
        })
        $("#mark-box").hide();

        if(tag == 'more') {
            difNum = more_img_arr[current_element_num].length;
            for(let i=0;i<imgArr.length;i++) {
                let html = `
                    <div class="multi-item items ${current_element_num}-element${difNum+i+delete_num[current_element_num]}">
                        <div class="img_box">
                            <img onerror="this.src='{{static_url('./resource/images/nopic.jpg')}}'; this.title='图片未找到'" src="${imgArr[i].url}" class="img-responsive img-thumbnail">
                        </div>
                        <input type="hidden" name="${byte_type}[]" value="${imgArr[i].attachment}">
                        <em class="close" title="删除这张图片" onclick="deleteMultiImage2(this,${current_element_num})">×</em>
                    </div>

                `
                $(input_image_name).parent().parent().next().append(html);
                arrIndex[current_element_num].push(difNum+i+delete_num[current_element_num]);
                srcArr[current_element_num].push(imgArr[i].attachment);
                more_img_arr[current_element_num].push(imgArr[i].attachment);
            }

            let count = arrIndex[current_element_num].length;
            info(count, current_element_num);
            redraw(0, current_element_num);
            tuofang(current_element_num);
        }else {
            var ipt = $(input_image_name).parent().prev();
            ipt.val(imgObj.attachment);
            ipt.attr("filename",imgObj.filename);
            ipt.attr("url",imgObj.url);
            var img = ipt.parent().next().children();
            img.get(0).src = imgObj.url
        }
        imgArr=[];
    }

    // 初始值
    function info(count, index){
        let curr = -1;
        if(index !== '' && index !== undefined && index !== null) {
            curr = index;
        }else {
            curr = current_element_num;
        }
        box_w = $(`.input-group`).width();//取总宽度
        if(item_w===undefined || item_w===null){
            item_w = $(".multi-item").outerWidth(true);//每个item占的横向位置
            item_h = $(".img_box").outerHeight(true);//每个item占的横向位置
		};
		col_len = Math.floor(box_w/item_w);//共分多少列
		few_len = Math.ceil(count/col_len);//共分多少行
        box_h[index] = item_h*few_len;

		$(`.multi-img-details`).eq(curr).height(box_h[index]+"px");
    }

    //调用绘制
    function redraw(slidetime,index){
        let curr = -1;
        if(index !== '' && index !== undefined && index !== null) {
            curr = index;
        }else {
            curr = current_element_num;
        }
        for(var i=0;i < more_img_arr[curr].length;i++){
            this.computat(i,`${curr}-element${arrIndex[curr][i]}`,slidetime);
        }
    }

    //绘制/移动
    function draw(dom,col,few,slidetime){
        dom.css({
			"transition-duration": slidetime+"ms",
			"transform":"translate("+col+"px,"+few+"px)",
		});
    }

    //计算位置
    function computat(index,domid,slidetime){
        var item = $("."+domid);
		item.attr({
			"item":index,
		});
		var col_aliquot=index%col_len;
		var row_aliquot=Math.floor(index/col_len);
		var index_col = item_w*(col_aliquot);
		var index_few = item_h*row_aliquot;
		draw(item,index_col,index_few,slidetime);
		item.attr({
			"col":index_col,
			"few":index_few
		})
    }

    // 排序图片值
    function sortImg(curr){
        for(let i = 0;i<arrIndex[curr].length;i++){
            $('.multi-img-details').eq(curr).children('.items').eq(i).find("input").val(srcArr[curr][arrIndex[curr][i]]);
        }
    }

    //取消
    function cancle() {
        var markBox = document.getElementById('mark-box');
        markBox.style.display = "none";
        $("#imgList .img-mark").each(function() {
            $(this).removeClass("shows");
            $(this).children(".sel-checkbox").prop("checked",false);
        })
        imgArr=[];
    }

    //关闭选择图片
    function closeImgDialog() {
        $("#imgList .img-mark").each(function() {
            $(this).removeClass("shows");
            $(this).children(".sel-checkbox").prop("checked",false);
        })
        $("#mark-box").hide();
        imgArr=[];
    }

    //打开选择图片
    function showImageDialog2(el,type,b_type,num) {
        $("#mark-box").show();
        input_image_name = el
        tag = type || 'single'
        byte_type = b_type
        current_element_num = num || 0;
        var file = document.getElementById('uploadfle')
        if(tag== "more") {
          // 多图上传
          file.setAttribute('multiple', true);
        }else {
          file.removeAttribute('multiple');
        }
        slectImg();
    }

    //删除图片
    function deleteMultiImage2(elm,index){
        let curr = -1;
        if(index !== '' && index !== undefined && index !== null) {
            curr = index;
        }else {
            curr = current_element_num;
        }
        delete_num[current_element_num]++;//已经删除的个数
        let itemi = parseInt($(elm).parent().attr("item"));
        let arr2 = [];
        for(var i=0;i<arrIndex[curr].length;i++){
			if(i==itemi){
				arrIndex[curr][i]=null;
			}else if(arrIndex[curr][i]!=null){
				arr2.push(arrIndex[curr][i]);
			};
		}

        new Promise(function(resolve) {
            $(elm).parent().remove();
            more_img_arr[curr].splice(itemi,1);
            arrIndex[curr] = arr2;
            redraw(200,curr);
            resolve();
        }).then(function() {
            // sortImg()
        });
        //调整高度
        let count = arr2.length;//item数量
		info(count, curr);
    }

    //拖动排序
    function tuofang(index){
        let curr = -1;
        if(index !== '' && index !== undefined && index !== null) {
            curr = index;
        }else {
            curr = current_element_num;
        }
        var stsrtcol,stsrtfew,//初始位置
		mobiexident,mobieyident,//移动标识
		startindex;//当前点击元素的item值
	    var isdrag=false;//是否可以拖动
	    var ischange=false;//是否有改动
        var dom;//按下后移动的对象
	    $(".multi-img-details").eq(curr).on({
		    mousedown:function(e){
                e.preventDefault();
			    startindex=parseInt($(this).attr("item"));
			    $(this).css({"opacity":"0.8","z-index":"10"});
			    var startx=e.pageX;
			    var starty=e.pageY;
                dom =e.currentTarget;
			    isdrag=true;
			    stsrtcol=parseInt($(this).attr("col"));
			    stsrtfew=parseInt($(this).attr("few"));
			    $(this).off("mousemove").off("mouseup").off("mouseleave").on({
				    mousemove:function(e){
				        if(isdrag){
					        var movex=e.pageX;
					        var movey=e.pageY;
					        var mobiex=stsrtcol+movex-startx;
					        var mobiey=stsrtfew+movey-starty;
					        if(mobiex>box_w-item_w){
						        mobiex=box_w-item_w;
					        }else if(mobiex < 0){
						        mobiex=0;
					        }
					        if(mobiey>box_h[curr]-item_h){
						        mobiey=box_h[curr]-item_h;
					        }else if(mobiey < 0){
						        mobiey=0;
					        }

					        if(Math.abs(movex-startx)>10||Math.abs(movey-starty)>10){
						        ischange=true;
                                mobiexident=Math.abs(Math.ceil((mobiex-item_w/2)/item_w));
						        mobieyident=Math.abs(Math.ceil((mobiey-item_h/2)/item_h));
                                draw($(this),mobiex,mobiey,0);
					        }

				        }
				    },
				    mouseup:function(e){
				        if(isdrag){
					        isdrag=false;
					        $(this).css({"opacity":"1","z-index":""});
					        draw($(this),stsrtcol,stsrtfew,0);
					        if(ischange){
						        ischange=false;
						        let toposion = mobieyident*col_len+mobiexident;
						        let difference=toposion-startindex;
						        if(difference > 1){//往后
							        let changesitem=arrIndex[curr].splice(startindex,1)[0];
							        arrIndex[curr].splice(toposion-1,0,changesitem);
                                    redraw(200, curr);
                                    sortImg(curr);
						        }else if(difference < 0){//往前
							        var changesitem=arrIndex[curr].splice(startindex,1)[0];
							        arrIndex[curr].splice(toposion,0,changesitem);
                                    redraw(200, curr);
                                    sortImg(curr);
						        }
					        }
				        }
				    },
				    mouseleave:function(e){
				        if(isdrag){
				 	        isdrag=false;
                            $(this).css({"opacity":"1","z-index":""});
					        draw($(this),stsrtcol,stsrtfew,0);
				        }
				    },
			    })
		    },
	    },".items")
    }

    function deleteImage2(elm){
        $(elm).prev().attr("src", '{{static_url("resource/images/nopic.jpg")}}');
        $(elm).parent().prev().find("input").val("");
    }
</script>