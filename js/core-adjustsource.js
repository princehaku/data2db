/**
 * 预览来源数据调整
 * 
 */
function previewAdjust(obj) {
	// 如果同列的其他两个按钮是存在的 则跳转到下一步去
	if ($(obj).prev().val() == '添加合并规则') {
		window.location.href = '?action=main&method=setDestination';
		return;
	}
	$(obj).parent().parent().parent().after(
					"<fieldset><div style='text-align:center;'><img src='images/gif/loading.gif' /><br />LOADING...</div></fieldset>");
	$(obj).hide();

	var c = '';
	$(".adjust").each(function(idx, obj) {
		if ($(obj).attr('class').indexOf('divide') != -1) {
			c = c + "`divide`";
		} else {
			c = c + "`combine`";
		}
		$(this).find("input[type=\"checkbox\"]").each(function() {
			if ($(this).attr('checked') == "checked") {
				c = c + $(this).val() + "~";
			}
		});
		var alpha = $(this).find("input[type=\"text\"]").val();
		if (alpha == "`") {
			alert("对不起,此符号是系统保留符号 无法使用");
			return;
		}
		c = c + "`" + alpha + "`";
	});

	$.ajax({
		url : '?action=preview&method=previewAdjust',
		type : "post",
		data : {
			'adjustRule' : c
		},
		error : function(exception) {
			alert('与服务器断开连接');
		},
		success : function(data) {
			$(obj).parent().parent().parent().next().html(
					'<legend>预览数据</legend>'+
					'<div class="preview_box">' + data + '</div><div class="adjust_box">' + 
					global_adjust_box + '</div><br clear="all"/>');
		}
	});

}
/**
 * 移除该条调整规则
 * 
 * @param obj
 */
function removeAdjust(obj) {
	//强制移除后面的属性
	for (i = 0 ; i < 10 ; i++) {
		$(obj).parent().parent().parent().next().remove()
	}
	// 重置到初始框
	$(obj).parent().parent().html(global_adjust_box);
	$(obj).parent().parent().parent().find(".div_btn").show();
	$(obj).parent().parent().parent().find(".com_btn").show();
}
/**
 * 添加一个拆分规则
 * 
 */
function addDivide(obj) {
	$(obj).parent().find(".div_btn").hide();
	$(obj).parent().find(".com_btn").hide();
	var bid = parseInt(Math.random() * 10000000);
	var tr_obj = $(obj).parent().parent().parent().find(".preview_box tr").get(1);
	var length = parseInt($(tr_obj).find("td").length) - 1;
	c = '<div class="adjust divide"><div class="checkbox" id="box_' + bid
			+ '">';
	for (i = 0; i < length; i++) {
		var id = parseInt(Math.random() * 10000000);
		c = c + '<input type="checkbox" id="rndq_' + id + '" value="' + i
				+ '"/><label for="rndq_' + id + '">第' + (i + 1) + '列</label>';
	}
	c = c
			+ '</div>拆分符号<input type="text" name="alpha"/><input type="button"'
			+ ' onclick="removeAdjust(this);" value="撤销本拆分"/><input class="next_btn" type="button" style="margin: 15px 15px;" onclick="previewAdjust(this);"  value="下一步" /></div>';
	$(obj).parent().parent().html(c);
	$("#box_" + bid).buttonset();
}
/**
 * 添加一个合并规则
 * 
 * @param length
 */
function addCombine(obj) {
	$(obj).parent().find(".div_btn").hide();
	$(obj).parent().find(".com_btn").hide();
	var bid = parseInt(Math.random() * 10000000);
	var tr_obj = $(obj).parent().parent().parent().find(".preview_box tr").get(1);
	var length = parseInt($(tr_obj).find("td").length) - 1;
	c = '<div class="adjust combine"><div class="checkbox" id="box_' + bid
			+ '">';
	for (i = 0; i < length; i++) {
		var id = parseInt(Math.random() * 10000000);
		c = c + '<input type="checkbox" id="rndq_' + id + '" value="' + i
				+ '"/><label for="rndq_' + id + '">第' + (i + 1) + '列</label>';
	}
	c = c
			+ '</div>合并符号<input type="text" name="alpha"/><input type="button"'
			+ ' onclick="removeAdjust(this);" value="撤销本合并"/><input  class="next_btn" type="button" style="margin: 15px 15px;" onclick="previewAdjust(this);"  value="下一步" /></div>';
	$(obj).parent().parent().html(c);
	$("#box_" + bid).buttonset();
}

var global_adjust_box = '';

$(document).ready(function() {
	$(".radios").buttonset();
	$(".checkbox").buttonset();
	if (global_adjust_box == '') {
		global_adjust_box = $('.adjust_box').html();
	}
});
