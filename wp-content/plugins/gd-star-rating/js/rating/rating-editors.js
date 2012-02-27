function gdsrEmpty() { }

function gdsrTimerChange(id) {
    var timer = jQuery("#gdsr_timer_type"+id).val();
    jQuery("#gdsr_timer_date"+id).css("display", "none");
    jQuery("#gdsr_timer_countdown"+id).css("display", "none");
    if (timer == "D") jQuery("#gdsr_timer_date"+id).css("display", "block");
    if (timer == "T") jQuery("#gdsr_timer_countdown"+id).css("display", "block");
}

function gdsrPostEditSection(id) {
    jQuery("#gdsr-bullet-"+id).toggleClass("gdsr-bullet-opened");
    jQuery("#gdsr-section-"+id).toggle("fast");
}

function gdsrRepeat(str, i) {
   if (isNaN(i) || i <= 0) return "";
   return str + gdsrRepeat(str, i-1);
}

function gdsrMultiRevert(sid, pid, slc) {
    for (var i = 0; i < slc; i++) {
        var val = jQuery('#gdsr_mur_stars_rated_' + pid + '_' + sid + '_' + i).css("width");
        jQuery('#gdsr_murvw_stars_current_' + pid + '_' + sid + '_' + i).css("width", val);
    }
    jQuery('.gdsr_int_multi_' + pid + '_' + sid).val(jQuery('#gdsr_mur_review_' + pid + '_' + sid).val());
}

function gdsrMultiClear(sid, pid, slc) {
    jQuery(".gdcurrent").css("width", "0px");
    var empty = gdsrRepeat("0X", slc);
    empty = empty.substr(empty, empty.length - 1);
    jQuery('.gdsr_int_multi_' + pid + '_' + sid).val(empty);
}

jQuery(document).ready(function() {
    if (jQuery.browser.msie) jQuery(".gdsr_mur_static > a").attr("href", "javascript:gdsrEmpty()");
    jQuery(".gdsr_mur_static > a").click(function() {
        var el = jQuery(this).attr("id").split("X");
        var vote = el[4];
        var size = el[5];
        var new_width = vote * size;
        var current_id = '#gdsr_murvw_stars_current_' + el[1] + '_' + el[2] + '_' + el[3];
        var input_id = '.gdsr_int_multi_' + el[1] + '_' + el[2];
        jQuery(current_id).css("width", new_width + "px");
        var rating_values = jQuery(input_id).val().split("X");
        rating_values[el[3]] = vote;
        for (var i = 0; i < rating_values.length; i++) {
            if (rating_values[i] == 0) break;
        }
        jQuery(input_id).val(rating_values.join("X"));
    });
});
