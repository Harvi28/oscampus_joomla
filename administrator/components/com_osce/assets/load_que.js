jQuery(document).ready(function($){
		// event.preventDefault();
  
  let searchBoxTag = `
  <div class="control-group">
  	<div class="controls">
		<select id="tagbox" multiple class="chosen-select">
		</select>
		<button type='button' id="btnid">Click Here</button>
		
		<div id="genques">
		<ul id="ques"></ul>
		</div>
	</div>
  </div>`;

  // $(".osc-quiz-questions").html('');


 
  let apendAfter = $('#jform_content_timeLimit').closest('.control-group');

  // searchBox.insertAfter.apendAfter;

	apendAfter.after(function() {
	  return searchBoxTag;
	});

	

	// let tagbt = $('#tagbtn').closest();

	// console.log(tagbt)

// $('#btnid').on('click',function(event){
			  	
	  		
// 					$(".osc-quiz-questions").html('');
// 				  		// event.preventDefault();
// 				  // alert(event.isDefaultPrevented());

					
// 				});

$("#tagbox").chosen();
$("#tagbox").chosen().change(function(event,val){
	const tag_id = val.selected
	// event.preventDefault();
	$.ajax({
		  type: "POST",
		  url: "index.php?option=com_osce&task=tags.getQuebyTagsId",
		  data: JSON.stringify({id: tag_id}),
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

// let ploatQuetions = function(queData){

// 	var html = '';

// 	$.each(queData, function(key, value) {
// 						html +='</div id="osc-quiz-questions">';
//             html += '<ul class="osc-question" style="background:#e6e7ed;color:black;padding:5px 0px">' + value.ques;
//             html += '<div id="osc_question_option">'
//             html += '<li style="background:#cccacb;margin:10px 0px;">'  + value.opt1;
//             html += '<input type="radio" name ="options" id="options"  value="1" style="margin-left:200px">';
//             html +='</input >';
//             html += '</li>'; 
//             html += '<li style="background:#cccacb;margin-bottom:10px">'  + value.opt2;
//             html += '<input type="radio" name ="options" id="options" value="2" style="margin-left:200px">';
//             html +='</input >';
//             html += '</li>';
//             html += '<li style="background:#cccacb;margin-bottom:10px">'  + value.opt3;
//             html += '<input type="radio" name ="options" id="options" value="3" style="margin-left:200px">';
//             html +='</input >';
//             html += '</li>' ;   
//             html += '<li style="background:#cccacb;margin-bottom:10px">'  + value.opt4;
//             html += '<input type="radio" name ="options" id="options" value="4" style="margin-left:200px">';
//             html +='</input >';
//             html += '</li>';
//             html += '</div>';
//             html += '</ul ">';
//             html+='</div>'
//         });



// // let qbankHtml = `<div class="osc-quiz-questions"><ul><li class="osc-question"><input id="questions_0" type="text" name="jform[questions][0]" value="name?" size="75" placeholder="Type your question"><button type="button" class="osc-btn-warning-admin osc-quiz-delete-question" style="cursor: pointer;"><i class="fa fa-times"></i></button><ul><li class="osc-answer"><input id="questions_0_correct" name="jform[questions][0][correct]" type="radio" value="0" checked=""><input id="questions_0_0" type="text" name="jform[questions][0][answers][0]" value="a" size="75" placeholder=""><button type="button" class="osc-btn-warning-admin osc-quiz-delete-answer" style="cursor: pointer;"><i class="fa fa-times"></i></button></li><li class="osc-answer"><input id="questions_0_correct" name="jform[questions][0][correct]" type="radio" value="1"><input id="questions_0_1" type="text" name="jform[questions][0][answers][1]" value="b" size="75" placeholder=""><button type="button" class="osc-btn-warning-admin osc-quiz-delete-answer" style="cursor: pointer;"><i class="fa fa-times"></i></button></li><li class="osc-answer"><input id="questions_0_correct" name="jform[questions][0][correct]" type="radio" value="2"><input id="questions_0_2" type="text" name="jform[questions][0][answers][2]" value="c" size="75" placeholder=""><button type="button" class="osc-btn-warning-admin osc-quiz-delete-answer" style="cursor: pointer;"><i class="fa fa-times"></i></button></li><li class="osc-answer"><input id="jform_content_questions_0_correct" name="jform[questions][0][correct]" type="radio" value="3"><input id="questions_0_3" type="text" name="jform[questions][0][answers][3]" value="d" size="75" placeholder=""><button type="button" class="osc-btn-warning-admin osc-quiz-delete-answer" style="cursor: pointer;"><i class="fa fa-times"></i></button></li><li class="osc-quiz-add-answer" style="cursor: pointer;"><button type="button" class="osc-btn-main-admin"><i class="fa fa-plus"></i> Add Answer</button></li></ul></div>`;
//  $('.osc-quiz-questions').html(html);
// }

$.ajax({
	type: "GET",
	url: "index.php?option=com_osce&task=tags.getTags",
	contentType: "application/json; charset=utf-8",
	success: function(res){
	  	console.log(res)
	  	res = JSON.parse(res)
	  	$.each(res, function(key, value) {   
		     $('#tagbox')
		         .append($("<option></option>")
		                    .attr("value", value.id)
		                    .text(value.title)); 
			});
			$('#tagbox').trigger("chosen:updated");
			// $('#tagbox').chosen()
  },
  error: function(err){
  	console.log(err)
  }
})
});