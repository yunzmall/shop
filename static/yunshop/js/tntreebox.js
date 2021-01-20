
// var data = [
	
// ];
var data = [];

var _tnTreebox = function(){};
var _tnTreebox_id = 0;
//_tnTreebox原型上增加方法
_tnTreebox.prototype = {
	//初始化数据
	data:null,//三联级所有对象
	dom_id:null,
	name:null,
	old_selected:null,//默认选中状态
	width:0,
	//初始化方法
	//调用： tnTreeBox('treebox','checkboxname',data,['o_1','o_2']);
	init:function(dom_id,name,data,selected){
		this.dom_id = dom_id;
		this.data = data;
		this.name = name;
		this.old_selected = selected;
	},
	//动态创建初始页面
	makeHtml:function(){
		var _data = {};
		for(var i in this.data){
			//for..in循环遍历data的每一个对象，并赋值给d
			var d = this.data[i];
			//hasOwnProperty()判断对象是否包含特定的自身（非继承）属性。
			if(!d.hasOwnProperty('parent_id')){
				//第一级的数据，没有父节点
				d.parent_id = 0;
			}
			//区分每一级每一组的方法是parent_id，第一级为0

			//每一级一开始if内创建一个数组，并将第一个值放入，再走else将同一级的全部对象放入
			if(typeof(_data[d.parent_id]) != 'object'){
				//将第一个每一级的数据赋值给_data
				_data[d.parent_id] = [d];
			}else{
				// 将data[i]放入_data的d.parent_id属性中
				_data[d.parent_id].push(d);
			}

		}
		//console.log(_data)  //输出按父节点分类的数组

		var html= "";

		for(var parent_id in _data){
			var list = _data[parent_id];
			// console.log(list)//按上面data顺序打印数组

			var html_list = "";//装子类html
			for(var j in list){
				var item = list[j];
				//$.inArray() 函数用于在数组中查找指定值，并返回它的索引值（如果没有找到，则返回-1）
				//被选中且在数组中可以找到，则默认为选中态
				if(this.old_selected&&$.inArray(item.id,this.old_selected)!=-1){
					var checked = " checked='checked'";
				}else{
					var checked = "";
				}
				//如果item的属性有is_select为true，那么默认也为选中态，此处有bug，即使选中态，后面也会被清空
				if(checked==''){
					try{
						if(item.is_select){
							checked = " checked='checked'";
						}
					}catch(err){
					}
				}

				var _class = "";
				// console.log(_data[item.id])//打印子类，为什么？
				//_data[item.id]是对象即为子类
				if(typeof(_data[item.id]) == 'object'){
					//增加一个类属性children，注意空格隔开
					_class+=" children";
				}

				try{
					if(item.is_hidden){
						_class+=" hide2";
					}
				}catch(err){
				}

				//补充class用来装上面的类
				if(_class){
					_class = " class='"+_class+"'";
				}
				//_tnTreebox_id
				_tnTreebox_id++;
				var id = 'treebox_'+_tnTreebox_id;
				//多选框
				var box = "<input type='checkbox' id='"+id+"' data-herf='"+item.href+"' data-id='11' name='"+this.name+"' value='"+item.id+"' />";
				try{
					//没有多选框，自行在data中定义，只有最后一项才有多选框
					if(item.no_box){
						box = '';
					}
				}catch(err){

				}
				//三级的box叠加
				html_list+="<li"+_class+" v="+item.id+"><em>"+box+"</em><label>"+item.name+"</label><span></span></li>";
			}


			var _class = 'box';
			//0是一级，其他的是子集
			if(parent_id==='0'){
				_class += " root";
			}else{
				_class += " hide";
			}


			if(_class){
				_class = " class='"+_class+"'";
			}
			//<li class=' children' v=o_1><em></em><label>基础数据管理</label><span></span></li>叠加
			html_list = "<div parent_id="+parent_id+_class+"><ul>"+html_list+"</ul></div>";
			// console.log(html_list)

			html+=html_list;//全部html_list叠加
		}


		//header存放选中的多选框内容，list存放动态生成的全部
		html = "<div class='header'></div><div class='list'>"+html+"</div>";

		//给id为treebox的div加上类tntreebox
		$("#"+this.dom_id).addClass('tntreebox').html(html);
		//获取宽度
		this.width = $("#"+this.dom_id).width();
		var that = this;
		$("#"+this.dom_id+" .children").on('click',function(){
			that.showChildren(this);
		});
		//当元素的值发生改变时，会发生 change 事件。
		$("#"+this.dom_id+" :checkbox").on('change',function(dom){
			console.log(dom.currentTarget)
			var event = dom.currentTarget;
			that.showChecked(event);
		});
		//？
		that.showChecked();
	},


	//点击触发事件
	showChildren:function(e){
		// console.log(e)//获取被点击元素
		var li = $(e);
		//cur，当前选中的元素
		li.parent().find('.cur').removeClass('cur');
		li.addClass('cur');
		//attr：找到每一个匹配元素的一个或多个属性。
		//找到一级盒子
		if(li.parent().parent().attr('class')=="box root"){
			var liV = li.attr("v");
			// 子元素取消颜色
			//让子类全部失效,注释掉之后，点击一级父类，子类还能继续保持选中状态
			// li.siblings().removeClass('selected');
			// $(".list").find(':checkbox').prop('checked',false);
			// li.parent().parent().parent().prev().html('');
			// $(".list li[v^=" + liV + "]").removeClass('selected');
		}

		//获得点击元素的id，id存储在v里面
		var id = li.attr('v');
		var col = li.parent().parent().attr('col');
		// console.log(col) 1/undefined
		//如果col==undefined，即一级col=0
		if(!col){
			col = 0;
		}
		var _col = col;
		// console.log(_col)  0/1
		//1代表着第二级
		while(1){
			_col++;
			//一级被点击后给其二级增加col=1，二级被点击后给其三级增加col=2
			var o = $("#"+this.dom_id+" div[col="+_col+"]");
			// console.log(o)
			if(o.length>0){
				//点击一级后隐藏其两级子类
				o.hide();
			}else{
				break;
			}
		}
		//点击后清除隐藏，并展示
		$("#"+this.dom_id+" div[parent_id="+id+"]").attr('col',col*1+1).removeClass('hide').show();
	},
	//多选框每次被点击触发
	showChecked:function(event){
		
		var html = '';
		//not：去除所有与给定选择器匹配的元素
		$("#"+this.dom_id+" input:checked").not('.hide2 input:checked').each(function(i,e){
			// console.log(e) //勾选的那个多选框,e等同于this
			//next：找到每个段落的后面紧邻的同辈元素中类名为label的元素。
			var text = $(this).parent().next('label').html();
			var id = $(this).val();

			//动态的为选中元素创建div，显示选中元素
			html += "<div>"+text+"<span v="+id+">x</span></div>";
		});
		if(html){
			html+=" <a>清空</a>";
		}
		//将创建好的div放入类名header的div里面
		// $("#"+this.dom_id+" .header").html(html);
		var thats = this
		//为x添加点击事件
		$("#"+this.dom_id+" .header span").on('click',function(){
			var obj = $(this);//$(this)是被点击的span的对象
			var id = obj.attr('v');
			//obj.parent().parent() 是header, length<2,因为是header也是div，其目的是清除header这个div内部的div
			if(obj.parent().parent().find('div').length<2){
				//移除“清空”按钮
				obj.parent().parent().find('a').remove();
			}
			//prop：获取匹配的元素集中第一个元素的属性（property）值或设置每一个匹配元素的一个或多个属性。

			//将三级的选中状态取消
			var checked = obj.parent().parent().next(".list").find(':checkbox[value='+id+']')
			checked.parent().parent().removeClass('selected');


			//将三级的多选框的对应的元素checked移除
			var p = checked.prop('checked',false);
			//移除被点击x的div
			obj.parent().remove();
		});

		var that = this;
		//“清空”按钮被点击后，全部移除
		// $("#"+this.dom_id+" .header a").on('click',function(){
		// 	var obj = $(this);
		// 	obj.parent().next('.list').find(':checkbox').prop('checked',false);
		// 	obj.parent().html('');
		// 	$("#"+that.dom_id+" .selected").removeClass('selected');
		// });


		//将原来的选中态，现在不是选中态的清除，下面会为真正被选中的重新添加selected样式
		$("#"+this.dom_id+" .selected").removeClass('selected');

		//选中背景加深
		$("#"+this.dom_id+" input:checked").each(function(index,item){
			//为三级li添加selected事件
			console.log(item);
			if(item == event)
			that.selected($(this).parent().parent());
		});

	},
	//selected事件
	selected:function(obj){
		$(obj).parent().parent().parent().find(':checkbox').prop('checked',false);

		$(obj).children().children().prop('checked',true);

		var href = $(obj).children().children().attr("data-herf");
		if(this.dom_id == 'treebox') {
			var id = $("#modal-mylink").attr("data-id")
		}
		else if(this.dom_id == 'treebox1') {
			var id = $("#modal-myApplink").attr("data-id");
		}
        // var id = $("#modal-mylink").attr("data-id") || $("#modal-myApplink").attr("data-id");
		console.log(id);
		// return;
        if(id){
			$("input[data-id="+id+"]").val('');
            $("input[data-id="+id+"]").val(href);
            $("#modal-mylink").attr("data-id","");
            $("#modal-myApplink").attr("data-id","");
        }else{
            ue.execCommand('link', {href:href});
        }
		$("#modal-mylink .close").click();
        $("#modal-myApplink .close").click();
		

	}
};
//最后归纳到tnTreeBox方法
function tnTreeBox(id,name,data,selected){
	var obj = new _tnTreebox();//new一个_tnTreebox的实例
	obj.init(id,name,data,selected);//初始化
	obj.makeHtml();//绘制
}
