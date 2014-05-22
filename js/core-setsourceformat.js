/**预览数据源
 * 
 * @param line_det 行分隔符
 * @param row_det 列分隔符
 * @param enclosure_det 字段环绕符
 * @param skip_head 是否跳过第1行
 */
function preview(line_det, row_det, enclosure_det, skip_head) {
	if (line_det=='' || row_det=='' || enclosure_det=='') {
		alert("请填充分隔符");
		return;
	}
	var encode = $("#encode").val();
	$("#preview_box").html("<div style='text-align:center;'><img src='images/gif/loading.gif' /><br />LOADING...</div>");
	$("#next_btn").hide();
	
	$.ajax({
		url: '?action=preview&method=previewFormat',
		type : "post",
		data : {
			"line_det" : line_det,
			"row_det" : row_det,
			"enclosure_det" : enclosure_det,
			"skip_head" : skip_head,
			"encode"	: encode
		},
		error : function (exception){
			alert('与服务器断开连接');
			$("#next_btn").show();
		},
		success : function (data){
			$("#preview_box").html(data);
			$("#next_btn").show();
		}
	});
	
}

$(document).ready(function (){
	
	// 行分隔符选择
	$('#line_det_sel').change(function (){
		var val = $(this).val();
		if (val=='o') {
			$("#line_det_val").show();
			$("#line_det_val").val('请输入分隔符');
			$(this).hide();
			return;
		}
		$("#line_det_val").val(val);
	});

	// 列分隔符选择
	$('#row_det_sel').change(function (){
		if ($("#line_det_val").val()=='') {
			$('#row_det_sel').val('');
			$("#line_det_sel").val('');
			alert('请先选择行分隔符');
			return;
		}
		var val = $(this).val();
		if (val=='o') {
			$("#row_det_val").show();
			$("#row_det_val").val('请输入分隔符');
			$(this).hide();
			return;
		}
		$("#row_det_val").val(val);
	});

	// 字段环绕符选择
	$('#enclosure_det_sel').change(function (){
		if ($("#row_det_val").val()=='') {
			$('#enclosure_det_sel').val('');
			$('#row_det_sel').val('');
			alert('请先选择字段分界符');
			return;
		}
		var val = $(this).val();
		if (val=='o') {
			$("#enclosure_det_val").show();
			$("#enclosure_det_val").val('请输入分隔符');
			$(this).hide();
			return;
		}
		$("#enclosure_det_val").val(val);
	});
	
	// 绑定预览按钮
	$("#preview_btn").click(function (){
		var line_det = $("#line_det_val").val();
		var row_det = $("#row_det_val").val();
		var enclosure_det_val = $("#enclosure_det_val").val();
		var skip_head = $("#skip_head").attr("checked") ? 1 : 0;
		if (line_det=='' || row_det=='' || line_det=='请输入分隔符' || row_det=='请输入分隔符') {
			alert('请填写您文件的行分隔符和列分隔符');
			return;
		}
		preview(line_det, row_det, enclosure_det_val, skip_head);
	});
});

