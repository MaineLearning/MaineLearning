jQuery(document).ready(function($){
	var trustworthy_score = $( 'input[name$="rtp-trustworthy-score"]' );
	var objective_score = $( 'input[name$="rtp-objective-score"]' );
	var complete_score = $( 'input[name$="rtp-complete-score"]' );
	var wellwritten_score = $( 'input[name$="rtp-wellwritten-score"]' );
	var is_page = $( '#feedback-ispage' );
	
	var chk_general = $('#feedback-options-general');
	var chk_relevant = $('#feedbackStudies');
	var chk_profession = $('#feedbackProfession');
	var chk_passion = $('#feedbackPassion');
	
	var adata_arr = $.parseJSON(rtpL10nAjax.adata_arr.replace(/&quot;/g, '"'));
	var rdata_arr = $.parseJSON(rtpL10nAjax.rdata_arr.replace(/&quot;/g, '"'));
	
	Init();

	$('#submit-feedback').click(function() {
		chk_general = ( chk_general.is(':checked') ) ? true : false;
		chk_relevant = ( chk_relevant.is(':checked') ) ? true : false;
		chk_profession = ( chk_profession.is(':checked') ) ? true : false;
		chk_passion = ( chk_passion.is(':checked') ) ? true : false;
		
		submitRatings();
	});
	
	$("#rtp-form").click(function() {
		$(".rtp-switch-report").css("display", "none");	
		$(".rtp-switch-form").css("display", "block");		
		$(".rtp-feedback .feedback-ratings").css("height", "85");
	});
	
	$("#rtp-report").click(function() {
		$(".rtp-switch-form").css("display", "none");
		$(".rtp-switch-report").css("display", "block");
		$(".rtp-feedback .feedback-ratings").css("height", "70");
		
		loadResults();
	});
	
	function submitRatings() {
		$.ajax({
			type: "POST",
			url: rtpL10nAjax.ajaxurl,
			data: { 
				action: 'submit-feedback',
				post_id: escape( $( '#feedback-postid' ).val() ),
				trustworthy_rate: escape( trustworthy_score.val() ),
				objective_rate: escape( objective_score.val() ),
				complete_rate: escape( complete_score.val() ),
				wellwritten_rate: escape( wellwritten_score.val() ),
				is_highly_knowledgable: escape( chk_general ),
				is_relevant: escape( chk_relevant ),
				is_my_profession: escape( chk_profession ),
				is_personal_passion: escape( chk_passion ),
				rate_date: $( '#feedback-datetime' ).val(),
				is_page: escape( is_page.val() )
			},
			beforeSend: function() {
				$("#rtp-loading").fadeIn('fast');
				$(".rtp-button").attr("disabled", "disabled").addClass("ui-state-disabled");
			},
			success: function(){
				$('#formstatus').html("<div id='rtp-loading'></div>");
				$('#rtp-loading').html("<span style='position: relative; top: 3px;'>" + rtpL10nAjax.success_msg + "</span>")
					.fadeIn(500)
					.delay(10000)
					.fadeOut(500);
				$('#feedback-trustworthy').raty('readOnly', true);
				$('#feedback-objective').raty('readOnly', true);
				$('#feedback-complete').raty('readOnly', true);
				$('#feedback-wellwritten').raty('readOnly', true);
			}
		}); //close jQuery.ajax
		return false;
	}
	
	function loadResults() {
		var per_trustworthy = 0;
		var per_objective = 0;
		var per_complete = 0;
		var per_wellwritten = 0;
		
		try {
			per_trustworthy = (adata_arr['trustworthy']/adata_arr['c_trustworthy'])*100/5;
		} catch (e) {
			per_trustworthy = 0;
		}
		
		try {
			per_objective = (adata_arr['objective']/adata_arr['c_objective'])*100/5;
		} catch (e) {
			per_objective = 0;
		}
		
		try {
			per_complete = (adata_arr['complete']/adata_arr['c_complete'])*100/5;
		} catch (e) {
			per_complete = 0;
		}
		
		try {
			per_wellwritten = (adata_arr['wellwritten']/adata_arr['c_wellwritten'])*100/5;
		} catch (e) {
			per_wellwritten = 0;
		}
	
		$("#rtp-trustworthy-bar").progressbar({ value: per_trustworthy });
		$("#rtp-objective-bar").progressbar({ value: per_objective });
		$("#rtp-complete-bar").progressbar({ value: per_complete });
		$("#rtp-wellwritten-bar").progressbar({ value: per_wellwritten });
	}
	
	function Init() {
		$(".rtp-switch-form").css("display", "block");
		$(".rtp-switch-report").css("display", "none");
		
		if ( rtpL10nAjax.is_rated ) {
			$(".rtp-button").attr("disabled", "disabled").addClass("ui-state-disabled");
			
			$('#formstatus').html("<div id='rtp-loading'></div>");
			$('#rtp-loading').show();
			$('#rtp-loading').html("<span style='position: relative; top: 3px;'>" + rtpL10nAjax.thanks_msg + "</span>");
		}
		
		if ( rtpL10nAjax.is_custom_labels == 'true' ) {
			$('.feedback-options').hide();
		}
		
		if ( rdata_arr['is_highly_knowledgable'] == 'true' ) {
			chk_general.attr("checked", "checked");
			$('.feeback-helpimprove-hidden').show();
		}
		if ( rdata_arr['is_relevant'] == 'true' )
			chk_relevant.attr("checked", "checked");
		if ( rdata_arr['is_my_profession'] == 'true' )
			chk_profession.attr("checked", "checked");
		if ( rdata_arr['is_personal_passion'] == 'true' )
			chk_passion.attr("checked", "checked");
	}
})