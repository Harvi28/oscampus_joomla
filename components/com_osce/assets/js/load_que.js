
jQuery(document).ready(function($){
		// event.preventDefault();

$.ajax({
		  type: "POST",
		  url: "index.php?option=com_osce&task=tags.getQuebyTagsId",
		  data: null,
		  contentType: "application/json; charset=utf-8",
		  dataType: 'json',
		  success: function(res){
		  	console.log(res)
		  	// ploatQuetions(res);

		  },
		  error: function(err){
		  	console.log(err)
		  }
		});
});