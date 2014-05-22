var inserttimes = 0;
/**
 * 新增继续插入
 * 
 * @param obj
 */
function addmore(obj) {
	var data = $(obj).parent().prev().html();
	$(obj).parent().before("<fieldset class=\"insert_box\">" + data + "</fieldset>");
	inserttimes++;
	var first_line = $($(obj).parent().prev().find(".odd").get(0));
	first_line.before('<tr class="odd"><td></td><td>第' + inserttimes
			+ '次插入后的主键</td>' + '<td><input type="text" value=""/>#' + inserttimes
			+ '</td><td><input type="checkbox" value="#@'
			+ inserttimes + '@"/></td>' + '</tr>');
	$(obj).parent().prev().find('input[type="checkbox"]:checked').attr("checked", false);
	initAutoSel();
}

function initAutoSel() {
	$('.odd input[type="text"]').each(function (i,obj){
		obj.onchange = function (){
			$(this).parent().next().find("input").attr("checked","checked");
		};
	});
}

$(document).ready(function (){
	initAutoSel();
});

function previewSetDestination() {
	// 检测是否所有的表名都填写了
	var isFilledFormName = 1;
	$('fieldset .input_box div input').each(function(i, obj) {
		if (obj.value == '') {
			isFilledFormName = 0;
			return false;
		}
	});
	if (isFilledFormName == 0) {
		alert("请填写好目标表名");
		return;
	}

	// 检测是否所有的字段名都填写了
	var isFilledColName = 1;
	$('.insert_box').each(function(i,obj) {
		$(obj).find('.odd input[type="checkbox"]:checked').each(function(i,trobj) {
			if ($(trobj).parent().prev().find('input').val() == '') {
				isFilledColName = 0;
				return false;
			}
		});
	});
	
	if (isFilledColName == 0) {
		alert("请填写好目标字段名");
		return;
	}
	//组装string
	var postRule = '';
	$('.insert_box').each(function(i,obj) {
		postRule += $(obj).find('.input_box div input').val();
		postRule += "|";
		$(obj).find('.odd input[type="checkbox"]:checked').each(function(i,trobj) {
			var type = $(trobj).val();
			var val = $(trobj).parent().prev().find('input').val();
			postRule = postRule + type + val;
			postRule += "%";
		});
		postRule += "|";
	});

	$.ajax({
		url: '?action=preview&method=previewDataMapping',
		type : "post",
		data : {
			'mapRule' : postRule
		},
		error : function (exception){
			alert('与服务器断开连接');
		},
		success : function (data){
			$("#preview_box").html(data);
			$("#next_btn").show();
		}
	});
}