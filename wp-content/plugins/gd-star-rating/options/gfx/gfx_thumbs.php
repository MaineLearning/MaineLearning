<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Thumbs Sizes", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tbody>
                <tr>
                    <td style="text-align: center; width: 60px;"><strong>12 px</strong></td>
                    <td style="text-align: center; width: 60px;"><strong>16 px</strong></td>
                    <td style="text-align: center; width: 60px;"><strong>20 px</strong></td>
                    <td style="text-align: center; width: 60px;"><strong>24 px</strong></td>
                    <td style="text-align: center; width: 60px;"><strong>32 px</strong></td>
                    <td style="text-align: center; width: 60px;"><strong>40 px</strong></td>
                </tr>
                <tr>
                    <td style="text-align: center;"><input type="checkbox" name="gdsr_inc_size_thumb[]" value="12" <?php echo $ginc_sizes_thumb["12"] == 0 ? "" : " checked"; ?> /></td>
                    <td style="text-align: center;"><input type="checkbox" name="gdsr_inc_size_thumb[]" value="16" <?php echo $ginc_sizes_thumb["16"] == 0 ? "" : " checked"; ?> /></td>
                    <td style="text-align: center;"><input type="checkbox" name="gdsr_inc_size_thumb[]" value="20" <?php echo $ginc_sizes_thumb["20"] == 0 ? "" : " checked"; ?> /></td>
                    <td style="text-align: center;"><input type="checkbox" name="gdsr_inc_size_thumb[]" value="24" <?php echo $ginc_sizes_thumb["24"] == 0 ? "" : " checked"; ?> /></td>
                    <td style="text-align: center;"><input type="checkbox" name="gdsr_inc_size_thumb[]" value="32" <?php echo $ginc_sizes_thumb["32"] == 0 ? "" : " checked"; ?> /></td>
                    <td style="text-align: center;"><input type="checkbox" name="gdsr_inc_size_thumb[]" value="40" <?php echo $ginc_sizes_thumb["40"] == 0 ? "" : " checked"; ?> /></td>
                </tr>
            </tbody>
        </table>
        <div class="gdsr-table-split"></div>
        <?php _e("Only sizes checked here will be included in the css file for the active rating blocks.", "gd-star-rating"); ?>
    </td>
</tr>
<tr><th scope="row"><?php _e("Stars Sets", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0" class="previewtable">
            <tbody>
            <?php
                for ($i = 0; $i < count($gdsr_gfx->thumbs); $i++) {
                    echo '<tr>';
                        $star = $gdsr_gfx->thumbs[$i];
                        echo '<td style="text-align: left; width: 28px; height: 24px;"><div style="height: 20px; width: 20px; background: url('.$star->get_url('20').') 0px -20px"></div></td>';
                        echo '<td style="width: 200px;">'.$star->name.' '.$star->version.'</td>';
                        echo '<td>'.strtoupper($star->type).'</td>';
                        echo '<td style="text-align: right; width: 32px;"><input'.(in_array($star->folder, $ginc_stars_thumb) ? " checked" : "").' type="checkbox" name="gdsr_inc_thumb[]" value="'.$star->folder.'" /></td>';
                        echo '<td style="width: 32px;"></td>';
                        if ($i < count($gdsr_gfx->thumbs) - 1) {
                            $i++;
                            $star = $gdsr_gfx->thumbs[$i];
                            echo '<td style="text-align: left; width: 28px; height: 24px;"><div style="height: 20px; width: 20px; background: url('.$star->get_url('20').') 0px -20px"></div></td>';
                            echo '<td style="width: 200px;">'.$star->name.' '.$star->version.'</td>';
                            echo '<td>'.strtoupper($star->type).'</td>';
                            echo '<td style="text-align: right; width: 32px;"><input'.(in_array($star->folder, $ginc_stars_thumb) ? " checked" : "").' type="checkbox" name="gdsr_inc_thumb[]" value="'.$star->folder.'" /></td>';
                        } else '<td colspan="3"></td>';
                    echo '</tr>';
                }
            ?>
            </tbody>
        </table>
        <div class="gdsr-table-split"></div>
        <?php _e("Only thumbs set checked here will be included in the css file for the active rating blocks.", "gd-star-rating"); ?>
    </td>
</tr>
</tbody></table>