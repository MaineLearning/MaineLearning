<table cellpadding="0" cellspacing="0" class="previewtable" style="width: 100%">
    <tr>
        <td><?php _e("Select code:", "gd-star-rating"); ?></td>
        <td style="text-align: right">
            <select onchange="gdsrChangeShortcode('admin')" id="srShortcode" name="srShortcode" style="width: 200px">
                <option value="starrating"><?php _e("Results", "gd-star-rating"); ?>: StarRating</option>
                <option value="blograting"><?php _e("Results", "gd-star-rating"); ?>: BlogRating</option>
                <option value="starrating">--------------------</option>
                <option value="starratingmulti"><?php _e("Multi", "gd-star-rating"); ?>: StarRatingMulti</option>
                <option value="starreviewmulti"><?php _e("Multi", "gd-star-rating"); ?>: StarReviewMulti</option>
                <option value="starrating">--------------------</option>
                <option value="starreview"><?php _e("Articles", "gd-star-rating"); ?>: StarReview</option>
                <option value="starrater"><?php _e("Articles", "gd-star-rating"); ?>: StarRater</option>
                <option value="starthumbsblock"><?php _e("Articles", "gd-star-rating"); ?>: StarThumbsBlock</option>
                <option value="starrating">--------------------</option>
                <option value="starcomments"><?php _e("Comments", "gd-star-rating"); ?>: StarComments</option>
            </select>
        </td>
    </tr>
</table>
<div class="gdsr-table-split"></div>
<table cellpadding="0" cellspacing="0" class="previewtable" style="width: 100%">
    <tr>
        <td><?php _e("Shortcode:", "gd-star-rating"); ?></td>
    </tr>
    <tr>
        <td><textarea id="gdsr-builder-shortcode" class="gdsr-builder-area"></textarea></td>
    </tr>
</table>
<div class="gdsr-table-split"></div>
<table cellpadding="0" cellspacing="0" class="previewtable" style="width: 100%; margin-bottom: 10px;">
    <tr>
        <td><?php _e("Function:", "gd-star-rating"); ?></td>
    </tr>
    <tr>
        <td><textarea id="gdsr-builder-function" class="gdsr-builder-area"></textarea></td>
    </tr>
</table>
<input type="button" onclick="gdsrAdminGetShortcode()" class="inputbutton" value="<?php _e("Rebuild", "gd-star-rating"); ?>" />
<div class="gdsr-table-split"></div>
<?php _e("List of all functions with additional parameters", "gd-star-rating"); ?>:<br />
<a target="_blank" href="<?php echo STARRATING_URL; ?>info/functions.html"><?php _e("Functions", "gd-star-rating") ?></a>
