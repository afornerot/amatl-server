function ModalLoad(idmodal,title,path) {
	$("#"+idmodal+" .modal-header h4").text(title);
	$("#"+idmodal+" #framemodal").attr("src",path);
}

$(document).ready(function(){

	$("#selectproject").on("change", function() {
		url=$(this).data("change");

		$.ajax({
			type: "POST",
			url: url,
			data: {
				id: $(this).val()
			},
			success: function (result) {
				location.reload();
			}
	   });

    });
});
