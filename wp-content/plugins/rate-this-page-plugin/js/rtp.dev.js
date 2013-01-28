jQuery(document).ready(function($){
	$('#feedback-options-general').click(function(){
		$('.feeback-helpimprove-hidden').slideToggle('fast');
		
		$('#feedbackStudies').attr('checked', false);
		$('#feedbackProfession').attr('checked', false);
		$('#feedbackPassion').attr('checked', false);
	});

	$('#feedback-trustworthy').raty({
		scoreName:  'rtp-trustworthy-score',
		cancel: 	true,
		cancelHint: rtpL10n.cancel_hint,
		target:		'.rtp-trustworthy-hint',
		hintList: 	hintListings('trustworthy'),
		path:		rtpL10n.img_path,
		start:		$('#rtp-trustworthy-val').val(),
		readOnly:	isRated(),
		noRatedMsg:	'No Rating'
	});
	
	$('#feedback-objective').raty({
		scoreName:  'rtp-objective-score',
		cancel: 	true,
		cancelHint: rtpL10n.cancel_hint,
		target:		'.rtp-objective-hint',
		hintList: 	hintListings('objective'),
		path:		rtpL10n.img_path,
		start:		$('#rtp-objective-val').val(),
		readOnly:	isRated(),
		noRatedMsg:	'No Rating'
	});
	
	$('#feedback-complete').raty({
		scoreName:  'rtp-complete-score',
		cancel: 	true,
		cancelHint: rtpL10n.cancel_hint,
		target:		'.rtp-complete-hint',
		hintList: 	hintListings('complete'),
		path:		rtpL10n.img_path,
		start:		$('#rtp-complete-val').val(),
		readOnly:	isRated(),
		noRatedMsg:	'No Rating'
	});
	
	$('#feedback-wellwritten').raty({
		scoreName:  'rtp-wellwritten-score',
		cancel: 	true,
		cancelHint: rtpL10n.cancel_hint,
		target:		'.rtp-wellwritten-hint',
		hintList: 	hintListings('wellwritten'),
		path:		rtpL10n.img_path,
		start:		$('#rtp-wellwritten-val').val(),
		readOnly:	isRated(),
		noRatedMsg:	'No Rating'
	});
	
	function isRated() {
		var ret = false;
		
		if ( rtpL10n.is_rated ) ret = true;
		
		return ret;
	}

	function hintListings(hintArea) {
		var hint = '';
		var hint_arr = $.parseJSON(rtpL10n.hints.replace(/&quot;/g, '"'));
		
		if ( rtpL10n.custom_hints != 0 && rtpL10n.custom_hints != 3 ) {
			if ( rtpL10n.custom_hints == 1 ) {
				hint = hint_arr['chint_1'];
			} else {
				hint = hint_arr['chint_2'];
			}
		} else {
			if ( hintArea == 'trustworthy' ) {
				hint = hint_arr['trustworthy'];
			} else if ( hintArea == 'objective' ) {
				hint = hint_arr['objective'];
			} else if ( hintArea == 'complete' ) {
				hint = hint_arr['complete'];
			} else {
				hint = hint_arr['wellwritten'];
			}
		}
		
		return hint;
	}
});
