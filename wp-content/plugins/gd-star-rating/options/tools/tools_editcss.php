<?php

    $rating_css = STARRATING_XTRA_PATH."css/rating.css";
    if (file_exists($rating_css)) {
        $css_file = htmlspecialchars(file_get_contents($rating_css));
        if (!is_writable($rating_css)) {
            $msg = __("File is not writeable and can't be saved through this form.", "gd-star-rating");
            $button = ' disabled="disabled"';
        }
    }
    else {
        $msg = __("Additional CSS file not found", "gd-star-rating");
        $button = ' disabled="disabled"';
    }

?>
<table class="form-table"><tbody>
<tr><th scope="row">rating.css</th>
    <td>
        <form method="post">
        <textarea class="gdsr-editcss-area" name="gdsr_editcss_contents"><?php echo $css_file; ?></textarea><br />
        <input<?php echo $button; ?> type="submit" class="inputbutton" value="<?php _e("Save", "gd-star-rating"); ?>" name="gdsr_editcss_rating" id="gdsr_editcss_rating" />
        <div class="gdsr-table-split"></div>
        <strong><?php echo $msg; ?></strong>
        </form>
    </td>
</tr>
</tbody></table>
