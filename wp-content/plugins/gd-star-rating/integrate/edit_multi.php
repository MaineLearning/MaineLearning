<?php
    if ($multi_id == 0) _e("You don't have any multi rating sets defined. Create at least one set.", "gd-star-rating");
    else {
        require_once(STARRATING_PATH."code/t2/render.php");
        $gdsr_multis = GDSRDBMulti::get_multis_tinymce();
?>
    <div class="gdsr-mur-sets-background">
    <input type="hidden" name="gdsrmultiactive" value="<?php echo $multi_id; ?>" />
    <input type="hidden" id="gdsr_post_edit_mur" name="gdsr_post_edit_mur" value="edit" />
    <table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr>
        <td style="padding-top: 3px;"><?php _e("Set", "gd-star-rating"); ?>:</td>
        <td>
            <label><select id="srMultiRatingSet" name="gdsrmultiset" style="width: 222px">
                <?php GDSRHelper::render_styles_select($gdsr_multis, $multi_id); ?>
            </select></label>
        </td>
        <td>
            <?php _e("To change the active set, you need to select the set you want from the list on the left, and save this article to accept the change.", "gd-star-rating");
            echo " ";
            _e("All values for all sets will be saved in the database. This allows different multi rating sets reviews.", "gd-star-rating"); ?>
        </td>
      </tr>
    </table>
    </div>
<?php
        echo '<table width="100%" cellspacing="0" cellpadding="0"><tr><td width="35%" class="gdsr-mur-review-info">';
        echo __("Set ID", "gd-star-rating").": <strong>".$multi_id."</strong><br />";
        echo __("Set Name", "gd-star-rating").": <strong>".$set->name."</strong>";
        echo '<div class="gdsr-table-split-edit"></div>';
?>
        <input onclick="gdsrMultiClear(<?php echo $multi_id; ?>, <?php echo $post_id; ?>, <?php echo count($set->object); ?>)" type="button" class="gdsr-input-button" value="<?php _e("Clear", "gd-star-rating"); ?>" />
        <input onclick="gdsrMultiRevert(<?php echo $multi_id; ?>, <?php echo $post_id; ?>, <?php echo count($set->object); ?>)" type="button" class="gdsr-input-button" value="<?php _e("Revert", "gd-star-rating"); ?>" />
<?php
        echo '</td><td width="65%" class="gdsr-mur-review-stars">';
        echo GDSRRenderT2::render_mre(0, array("post_id" => $post_id, "votes" => $votes, "style" => "oxygen", "set" => $set, "height" => 20, "allow_vote" => true));
        echo '</td></tr></table>';
    }
?>