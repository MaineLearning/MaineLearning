jQuery(document).ready(function() {
    jQuery(".jqloaderarticle").change(function() {
        var loadr = jQuery("#gdsr_wait_loader_article").val();
        var texts = jQuery("#gdsr_wait_text_article").val();
        var usetx = jQuery("#gdsr_wait_show_article").is(':checked');
        var clssx = jQuery("#gdsr_wait_class_article").val();
        if (usetx) {
            texts = '';
            loadr = loadr + ' width';
        }
        jQuery("#gdsrwaitpreviewarticle").removeClass();
        jQuery("#gdsrwaitpreviewarticle").addClass("wait-preview-holder-article loader "+loadr+" "+clssx);
        jQuery("#gdsrwaitpreviewarticle").html(texts);
    });

    jQuery(".jqloadercomment").change(function() {
        var loadr = jQuery("#gdsr_wait_loader_comment").val();
        var texts = jQuery("#gdsr_wait_text_comment").val();
        var usetx = jQuery("#gdsr_wait_show_comment").is(':checked');
        var clssx = jQuery("#gdsr_wait_class_comment").val();
        if (usetx) {
            texts = '';
            loadr = loadr + ' width';
        }
        jQuery("#gdsrwaitpreviewcomment").removeClass();
        jQuery("#gdsrwaitpreviewcomment").addClass("wait-preview-holder-comment loader "+loadr+" "+clssx);
        jQuery("#gdsrwaitpreviewcomment").html(texts);
    });

    jQuery(".jqloadermultis").change(function() {
        var loadr = jQuery("#gdsr_wait_loader_multis").val();
        var texts = jQuery("#gdsr_wait_text_multis").val();
        var usetx = jQuery("#gdsr_wait_show_multis").is(':checked');
        var clssx = jQuery("#gdsr_wait_class_multis").val();
        if (usetx) {
            texts = '';
            loadr = loadr + ' width';
        }
        jQuery("#gdsrwaitpreviewmultis").removeClass();
        jQuery("#gdsrwaitpreviewmultis").addClass("wait-preview-holder-multis loader "+loadr+" "+clssx);
        jQuery("#gdsrwaitpreviewmultis").html(texts);
    });
});
