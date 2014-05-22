function del_uploaded(filename,obj) {
	$.ajax({
		url : "?action=file&method=delUploaded&t=" + Math.random(),
		type : "post",
		dataType: 'json',
		data : {"filename" : filename},
		error : function (data){
			alert(data);
		},
		success : function (json){
			if (json.status==1) {
				$(obj).parent().parent().remove();
			}
			alert(json.msg);
		}
	});
}