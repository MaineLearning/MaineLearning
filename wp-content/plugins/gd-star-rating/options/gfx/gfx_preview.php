<table class="form-table"><tbody>
<tr><th scope="row"><?php _e("Stars Preview", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td width="100" style="padding: 0; border: 0; height: 28px; vertical-align: top;"><?php _e("Stars", "gd-star-rating"); ?>:</td>
                <td width="200" align="left" style="padding: 0; border: 0; vertical-align: top; text-align: right;">
                    <select style="width: 180px;" name="gdsr_style_preview" id="gdsr_style_preview" onchange="gdsrStyleSelection('stars')">
                        <?php GDSRHelper::render_styles_select($gdsr_gfx->stars, "", true); ?>
                    </select>
                </td>
                <td width="20" style="padding: 0; border: 0; vertical-align: top;" rowspan="2"></td>
                <td style="padding: 0; border: 0; vertical-align: top;" rowspan="3">
                    <table cellpadding="0" width="400" cellspacing="0" class="previewtable">
                        <tr>
                            <td class="gdsr-preview" style="background-color: black;"><img src="#" id="gdsr_preview_black" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview" style="background-color: red;"><img src="#" id="gdsr_preview_red" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview" style="background-color: green;"><img src="#" id="gdsr_preview_green" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview" style="background-color: white;"><img src="#" id="gdsr_preview_white" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview" style="background-color: blue;"><img src="#" id="gdsr_preview_blue" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview" style="background-color: yellow;"><img src="#" id="gdsr_preview_yellow" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview" style="background-color: gray;"><img src="#" id="gdsr_preview_gray" /></td>
                            <td class="gdsr-preview-space" ></td>
                            <td class="gdsr-preview gdsr-preview-pic"><img src="#" id="gdsr_preview_picture" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr valign="top">
                <td width="100" style="padding: 0; border: 0; height: 28px; vertical-align: top;"><?php _e("Size", "gd-star-rating"); ?>:</td>
                <td width="250" align="left" style="padding: 0; border: 0; vertical-align: top; text-align: right;">
                    <?php GDSRHelper::render_star_sizes("gdsr_size_preview", 30, 180, ' onchange="gdsrStyleSelection(\'stars\')"'); ?>
                </td>
            </tr>
            <tr valign="top">
                <td width="100" style="padding: 0; border: 0; vertical-align: top;"><?php _e("Author", "gd-star-rating"); ?>:</td>
                <td width="250" align="left" style="padding: 0; border: 0; vertical-align: top;">
                    <div id="gdsrauthorname" style="text-align: right;"></div>
                    <div id="gdsrauthoremail" style="text-align: right;"></div>
                    <div id="gdsrauthorurl" style="text-align: right;"></div>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Thumbs Preview", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td width="100" style="padding: 0; border: 0; height: 28px; vertical-align: top;"><?php _e("Thumbs", "gd-star-rating"); ?>:</td>
                <td width="200" align="left" style="padding: 0; border: 0; vertical-align: top; text-align: right;">
                    <select style="width: 180px;" name="gdsr_style_preview_thumbs" id="gdsr_style_preview_thumbs" onchange="gdsrStyleSelection('thumbs')">
                        <?php GDSRHelper::render_styles_select($gdsr_gfx->thumbs, "", true); ?>
                    </select>
                </td>
                <td width="20" style="padding: 0; border: 0; vertical-align: top;" rowspan="2"></td>
                <td style="padding: 0; border: 0; vertical-align: top;" rowspan="3">
                    <table cellpadding="0" width="400" cellspacing="0" class="previewtable">
                        <tr>
                            <td class="gdsr-preview-thumbs" style="background-color: black;"><img src="#" id="gdsr_preview_thumbs_black" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview-thumbs" style="background-color: red;"><img src="#" id="gdsr_preview_thumbs_red" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview-thumbs" style="background-color: green;"><img src="#" id="gdsr_preview_thumbs_green" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview-thumbs" style="background-color: white;"><img src="#" id="gdsr_preview_thumbs_white" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview-thumbs" style="background-color: blue;"><img src="#" id="gdsr_preview_thumbs_blue" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview-thumbs" style="background-color: yellow;"><img src="#" id="gdsr_preview_thumbs_yellow" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview-thumbs" style="background-color: gray;"><img src="#" id="gdsr_preview_thumbs_gray" /></td>
                            <td class="gdsr-preview-space" ></td>
                            <td class="gdsr-preview-thumbs gdsr-preview-pic"><img src="#" id="gdsr_preview_thumbs_picture" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr valign="top">
                <td width="100" style="padding: 0; border: 0; height: 28px; vertical-align: top;"><?php _e("Size", "gd-star-rating"); ?>:</td>
                <td width="250" align="left" style="padding: 0; border: 0; vertical-align: top; text-align: right;">
                    <?php GDSRHelper::render_thumbs_sizes("gdsr_size_preview_thumbs", 32, 180, ' onchange="gdsrStyleSelection(\'thumbs\')"'); ?>
                </td>
            </tr>
            <tr valign="top">
                <td width="100" style="padding: 0; border: 0; vertical-align: top;"><?php _e("Author", "gd-star-rating"); ?>:</td>
                <td width="250" align="left" style="padding: 0; border: 0; vertical-align: top;">
                    <div id="gdsrauthornamethumbs" style="text-align: right;"></div>
                    <div id="gdsrauthoremailthumbs" style="text-align: right;"></div>
                    <div id="gdsrauthorurlthumbs" style="text-align: right;"></div>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr><th scope="row"><?php _e("Trends Preview", "gd-star-rating"); ?></th>
    <td>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <td width="150" style="padding: 0; border: 0; height: 28px; vertical-align: top;"><?php _e("Trends", "gd-star-rating"); ?>:</td>
                <td width="200" align="left" style="padding: 0; border: 0; vertical-align: top; text-align: right;">
                    <select style="width: 180px;" name="gdsr_style_preview_trends" id="gdsr_style_preview_trends" onchange="gdsrStyleSelection('trends')">
                        <?php GDSRHelper::render_styles_select($gdsr_gfx->trend); ?>
                    </select>
                </td>
                <td width="20" style="padding: 0; border: 0; vertical-align: top;" rowspan="2"></td>
                <td style="padding: 0; border: 0; vertical-align: top;">
                    <table cellpadding="0" width="400" cellspacing="0" class="previewtable">
                        <tr>
                            <td class="gdsr-preview-trends" style="background-color: black;"><img src="#" id="gdsr_preview_trends_black" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview-trends" style="background-color: red;"><img src="#" id="gdsr_preview_trends_red" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview-trends" style="background-color: green;"><img src="#" id="gdsr_preview_trends_green" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview-trends" style="background-color: white;"><img src="#" id="gdsr_preview_trends_white" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview-trends" style="background-color: blue;"><img src="#" id="gdsr_preview_trends_blue" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview-trends" style="background-color: yellow;"><img src="#" id="gdsr_preview_trends_yellow" /></td>
                            <td class="gdsr-preview-space"></td>
                            <td class="gdsr-preview-trends" style="background-color: gray;"><img src="#" id="gdsr_preview_trends_gray" /></td>
                            <td class="gdsr-preview-space" ></td>
                            <td class="gdsr-preview-trends gdsr-preview-pic"><img src="#" id="gdsr_preview_trends_picture" /></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>
</tbody></table>
