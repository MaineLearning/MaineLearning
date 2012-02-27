jQuery(document).ready(function() {
    var htmlColor = jQuery("html").css("background-color");
    jQuery(".form-table td").css("border-bottom-color", htmlColor);
    jQuery(".form-table th").css("border-bottom-color", htmlColor);
});
