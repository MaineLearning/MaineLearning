function checkAll(form) {
    for (i = 0, n = form.elements.length; i < n; i++) {
        if(form.elements[i].type == "checkbox" && !(form.elements[i].getAttribute('onclick', 2))) {
            if(form.elements[i].checked == true) {
                form.elements[i].checked = false;
            } else {
                form.elements[i].checked = true;
            }
        }
    }
}

function gdsrOptionsSection(id) {
    jQuery("#opt-control-"+id).toggleClass("gdsr-bullet-opened");
    jQuery("#opt-panel-"+id).toggle("fast");
}

function gdsrTimerChange(tp) {
    var timer = jQuery("#gdsr_timer_type"+tp).val();
    jQuery("#gdsr_timer_date"+tp).css("display", "none");
    jQuery("#gdsr_timer_countdown"+tp).css("display", "none");
    jQuery("#gdsr_timer_date_text"+tp).css("display", "none");
    jQuery("#gdsr_timer_countdown_text"+tp).css("display", "none");
    if (timer == "D") {
        jQuery("#gdsr_timer_date"+tp).css("display", "block");
        jQuery("#gdsr_timer_date_text"+tp).css("display", "block");
    }
    if (timer == "T") {
        jQuery("#gdsr_timer_countdown"+tp).css("display", "block");
        jQuery("#gdsr_timer_countdown_text"+tp).css("display", "block");
    }
}
