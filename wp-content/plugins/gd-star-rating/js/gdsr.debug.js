/*jslint regexp: true, nomen: true, undef: true, sloppy: true, eqeq: true, vars: true, white: true, plusplus: true, maxerr: 50, indent: 4 */

function gdsrWait(rater, loader) {
    jQuery("#"+rater).css("display", "none");
    jQuery("#"+loader).css("display", "block");
}

function jquery_escape_id(myid) {
    return '#'+myid.replace(/:/g,"\\:").replace(/\./g,"\\.");
}

function gdsrEmpty() { }

function multi_rating_vote(block) {
    var el = block.split("_");
    var post_id = el[0];
    var set_id = el[1];
    var tpl_id = el[2];
    var size = el[3];
    var values = jQuery("#gdsr_multi_" + post_id + "_" + set_id).val();
    gdsrWait("gdsr_mur_text_" + post_id + "_" + set_id, "gdsr_mur_loader_" + post_id + "_" + set_id);
    jQuery.getJSON(gdsr_cnst_ajax, {_ajax_nonce: gdsr_cnst_nonce, vote_id: post_id, vote_set: set_id, vote_value: values, vote_tpl: tpl_id, vote_type: 'm', vote_size: size }, function(json) {
        jQuery("#gdsr_mur_block_" + post_id + "_" + set_id + " .gdsr_multis_as").remove();
        jQuery("#gdsr_mur_block_" + post_id + "_" + set_id + " .gdcurrent").remove();
        jQuery("#gdsr_mur_block_" + post_id + "_" + set_id + " input").remove();
        jQuery("#gdsr_mur_block_" + post_id + "_" + set_id + " .ratingbutton").remove();
        var height = jQuery("#gdsr_mur_avgstars_" + post_id + "_" + set_id + " div").css("height");
        var i;
        if (height > 0) {
            jQuery("#gdsr_mur_avgstars_" + post_id + "_" + set_id + " div").css("width", json.average * height.substring(0, 2));
        }
        for (i = 0; i < json.values.length; i++) {
            jQuery("#gdsr_mur_stars_rated_" + post_id + "_" + set_id + "_" + i).css("width", parseInt(json.values[i]));
        }
        jQuery("#gdsr_mur_text_" + post_id + "_" + set_id).html(json.rater).addClass("voted");
        gdsrWait("gdsr_mur_loader_" + post_id + "_" + set_id, "gdsr_mur_text_" + post_id + "_" + set_id);
    });
}

function gdsr_rating_multi_button(elm) {
    if (jQuery(elm).hasClass("active")) {
        block = jQuery(elm).parent().attr("id").substring(12);
        multi_rating_vote(block);
    }
}

function gdsr_rating_multi_stars(elm) {
    var el = jQuery(elm).attr("id").split("X");
    var vote = el[4];
    var size = el[5];
    var new_width = vote * size;
    var current_id = '#gdsr_mur_stars_current_' + el[1] + '_' + el[2] + '_' + el[3];
    var input_id = '#gdsr_multi_' + el[1] + '_' + el[2];
    jQuery(current_id).css("width", new_width + "px");
    var rating_values = jQuery(input_id).val().split("X");
    rating_values[el[3]] = vote;
    var active = true;
    var i;
    for (i = 0; i < rating_values.length; i++) {
        if (parseInt(rating_values[i]) === 0) {
            active = false;
            break;
        }
    }

    jQuery(input_id).val(rating_values.join("X"));
    var button_block = el[1] + '_' + el[2] + '_' + el[6] + '_' + size;
    if (typeof(gdsr_cnst_button) !== 'undefined' && gdsr_cnst_button == 1) {
        var button_id = '#gdsr_button_' + button_block;
        if (active) {
            jQuery(button_id).removeClass('gdinactive');
            jQuery(button_id).addClass('gdactive');
            jQuery(button_id + " a").addClass('active');
        } else {
            jQuery(button_id).removeClass('gdactive');
            jQuery(button_id).addClass('gdinactive');
            jQuery(button_id + " a").removeClass('active');
        }
    } else {
        if (active) {
            multi_rating_vote(button_block);
        }
    }
}

function gdsr_rating_standard(elm) {
    var el = jQuery(elm).attr("id").split("X");
    gdsrWait(el[5], el[6]);
    jQuery.getJSON(gdsr_cnst_ajax, {_ajax_nonce: gdsr_cnst_nonce, vote_id: el[1], vote_value: el[2], vote_type: el[4], vote_tpl: el[7], vote_size: el[8] }, function(json) {
        gdsrWait(el[6], el[5]);
        if (json.status == 'ok') {
            jQuery("#gdr_stars_" + el[4] + el[1]).html("");
            jQuery("#gdr_vote_" + el[4] + el[1]).css("width", parseInt(json.value));
            jQuery("#gdr_text_" + el[4] + el[1]).addClass("voted");
            jQuery("#gdr_text_" + el[4] + el[1]).html(json.rater);
        }
    });
}

function gdsr_rating_thumb(elm) {
    var cls = jQuery(elm).attr('class');
    var el = jQuery(elm).attr("id").split("X");
    if (el[6] == 'Y') {
        gdsrWait("gdsr_thumb_" + el[1] + "_" + el[3] + "_" + el[2], "gdsr_thumb_" + el[1] + "_" + el[3] + "_" + "loader_" + el[2]);
    }
    jQuery("#gdsr_thumb_" + el[1] + "_" + el[3] + "_" + "up a").replaceWith('<div class="' + cls + '"></div>');
    jQuery("#gdsr_thumb_" + el[1] + "_" + el[3] + "_" + "dw a").replaceWith('<div class="' + cls + '"></div>');
    jQuery.getJSON(gdsr_cnst_ajax, {_ajax_nonce: gdsr_cnst_nonce, vote_id: el[1], vote_value: el[2], vote_type: 'r'+el[3], vote_tpl: el[4], vote_size: el[5] }, function(json) {
        if (el[6] == 'Y') {
            gdsrWait("gdsr_thumb_" + el[1] + "_" + el[3] + "_" + "loader_" + el[2], "gdsr_thumb_" + el[1] + "_" + el[3] + "_" + el[2]);
        }
        if (json.status == 'ok') {
            jQuery("#gdsr_thumb_text_" + el[1] + "_" + el[3]).addClass("voted");
            jQuery("#gdsr_thumb_text_" + el[1] + "_" + el[3]).html(json.rater);
        }
    });
}

var gdsrCanceled = false;
function hideshowCmmInt() {
    var value = jQuery("#comment_parent").val();
    if (parseInt(value) === 0) {
        jQuery("#gdsr-cmm-integration-block-review").removeClass("cmminthide");
        jQuery("#gdsr-cmm-integration-block-standard").removeClass("cmminthide");
        jQuery("#gdsr-cmm-integration-block-multis").removeClass("cmminthide");
    } else {
        jQuery("#gdsr-cmm-integration-block-review").addClass("cmminthide");
        jQuery("#gdsr-cmm-integration-block-standard").addClass("cmminthide");
        jQuery("#gdsr-cmm-integration-block-multis").addClass("cmminthide");
    }

    if (!gdsrCanceled) {
        jQuery("#cancel-comment-reply-link").click(function() {
            hideshowCmmInt();
        });
        gdsrCanceled = true;
    } else {
        jQuery("#cancel-comment-reply-link").unbind("click");
        gdsrCanceled = false;
    }
}

function value_cmm_rated_multis() {
    var value = jQuery(".gdsr-mur-cls-rt").val();
    return value.split("X");
}

function is_cmm_rated_multis() {
    var value = value_cmm_rated_multis();
    var rated = true;
    var i;
    for (i = 0; i < value.length; i++) {
        if (parseInt(value[i]) === 0) {
            rated = false;
        }
    }
    return rated;
}

function value_cmm_rated_standard() {
    return jQuery(".gdsr-int-cls-rt").val();
}

function is_cmm_rated_standard() {
    return value_cmm_rated_standard() > 0;
}

function value_cmm_rated_review() {
    return jQuery(".gdsr-cmm-cls-rt").val();
}

function is_cmm_rated_review() {
    return value_cmm_rated_review() > 0;
}

function gdsr_ie() {
    return jQuery.browser.msie && jQuery.browser.version < '8.0';
}

function gdsr_random_seed() {
    var start = new Date().getTime();
    start += Math.floor(Math.random() * 1024);
    return start;
}

jQuery(document).ready(function() {
    if (typeof(gdsr_cnst_cache) !== 'undefined' && gdsr_cnst_cache == 1) {
        var ela = "";
        var elc = "";
        jQuery(".gdsrcacheloader").each(function(i) {
            el = jQuery(this).attr("id").substring(6);
            if (el.substring(0, 1) == "a") {
                ela+= el + ":";
            } else {
                elc+= el + ":";
            }
        });

        if (ela.length > 0) {
            ela = ela.substring(0, ela.length - 1);
            jQuery.getJSON(gdsr_cnst_ajax, {_ajax_nonce: gdsr_cnst_nonce, vote_type: 'cache', vote_domain: 'a', votes: ela}, function(json) {
                var i;
                for (i = 0; i < json.items.length; i++) {
                    var item = json.items[i];
                    jQuery(jquery_escape_id(item.id)).replaceWith(item.html);
                }

                if (jQuery.browser.msie) { jQuery(".gdsr_rating_as > a").attr("href", "javascript:gdsrEmpty()"); }
                if (jQuery.browser.msie) { jQuery(".gdthumb > a").attr("href", "javascript:gdsrEmpty()"); }
                if (jQuery.browser.msie) { jQuery(".gdsr_multisbutton_as > a").attr("href", "javascript:gdsrEmpty()"); }
                if (jQuery.browser.msie) { jQuery(".gdsr_multis_as > a").attr("href", "javascript:gdsrEmpty()"); }

                jQuery(".gdsr_rating_as > a").unbind("click");
                jQuery(".gdsr_rating_as > a").click(function() { gdsr_rating_standard(this); });
                jQuery(".gdthumb > a").unbind("click");
                jQuery(".gdthumb > a").click(function() { gdsr_rating_thumb(this); });

                jQuery(".gdsr_multisbutton_as > a").unbind("click");
                jQuery(".gdsr_multisbutton_as > a").click(function() { gdsr_rating_multi_button(this); });
                jQuery(".gdsr_multis_as > a").unbind("click");
                jQuery(".gdsr_multis_as > a").click(function() { gdsr_rating_multi_stars(this); });
            });
        }

        if (elc.length > 0) {
            elc = elc.substring(0, elc.length - 1);
            jQuery.getJSON(gdsr_cnst_ajax, {_ajax_nonce: gdsr_cnst_nonce, vote_type: 'cache', vote_domain: 'c', votes: elc}, function(json) {
                var i;
                for (i = 0; i < json.items.length; i++) {
                    var item = json.items[i];
                    jQuery(jquery_escape_id(item.id)).replaceWith(item.html);
                }

                if (jQuery.browser.msie) { jQuery(".gdsr_rating_as > a").attr("href", "javascript:gdsrEmpty()"); }
                if (jQuery.browser.msie) { jQuery(".gdthumb > a").attr("href", "javascript:gdsrEmpty()"); }

                jQuery(".gdsr_rating_as > a").unbind("click");
                jQuery(".gdsr_rating_as > a").click(function() { gdsr_rating_standard(this); });
                jQuery(".gdthumb > a").unbind("click");
                jQuery(".gdthumb > a").click(function() { gdsr_rating_thumb(this); });
            });
        }
    }

    if (gdsr_ie()) { jQuery(".gdsr_rating_as > a").attr("href", "javascript:gdsrEmpty()"); }
    jQuery(".gdsr_rating_as > a").click(function() { gdsr_rating_standard(this); });

    if (gdsr_ie()) { jQuery(".gdthumb > a").attr("href", "javascript:gdsrEmpty()"); }
    jQuery(".gdthumb > a").click(function() { gdsr_rating_thumb(this); });

    if (gdsr_ie()) { jQuery(".gdsr_integration > a").attr("href", "javascript:gdsrEmpty()"); }
    jQuery(".gdsr_integration > a").click(function() {
        var el = jQuery(this).attr("id").split("X");
        var pid = "#" + jQuery(this).parent().attr("id");
        var new_width = el[1] * el[2];
        jQuery(pid + "_stars_rated").css("width", new_width + "px");
        jQuery(pid + "_value").val(el[1]);
    });

    if (gdsr_ie()) { jQuery(".gdsr_multisbutton_as > a").attr("href", "javascript:gdsrEmpty()"); }
    jQuery(".gdsr_multisbutton_as > a").click(function() { gdsr_rating_multi_button(this); });

    if (gdsr_ie()) { jQuery(".gdsr_multis_as > a").attr("href", "javascript:gdsrEmpty()"); }
    jQuery(".gdsr_multis_as > a").click(function() { gdsr_rating_multi_stars(this); });

    if (gdsr_ie()) { jQuery(".gdsr_mur_static > a").attr("href", "javascript:gdsrEmpty()"); }
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
        var i;
        for (i = 0; i < rating_values.length; i++) { if (parseInt(rating_values[i]) === 0) { break; } }
        jQuery(input_id).val(rating_values.join("X"));
    });
});
