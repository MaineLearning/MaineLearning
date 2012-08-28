<div class="defaultsSelector">

<h3 class="csh3title" title="<?php _e('Click to toogle', 'custom-sidebars'); ?>"><?php _e('Tag archives','custom-sidebars'); ?></h3>
<div class="defaultsContainer"><?php if(!empty($modifiable)): foreach($modifiable as $m): $sb_name = $allsidebars[$m]['name'];?>
                <p><?php echo $sb_name; ?>: 
                    <select name="tag_page_<?php echo $m;?>">
                        <option value=""></option>
                    <?php foreach($allsidebars as $key => $sb):?>
                        <option value="<?php echo $key; ?>" <?php echo (isset($defaults['tags'][$m]) && $defaults['tags'][$m]==$key) ? 'selected="selected"' : ''; ?>>
                            <?php echo $sb['name']; ?>
                        </option>
                    <?php endforeach;?>
                    </select>
                    <?php if(!isset($cs_is_defaults)): ?>
                        <a href="#" class="selectSidebar"><?php printf(__('<- Set %s here.', 'custom-sidebars'), $current_sidebar['name']); ?></a>
                    <?php endif; ?>
                </p>
            <?php endforeach;else:?>
                <p><?php _e('There are no replaceable sidebars selected. You must select some of them in the form above to be able for replacing them in all the post type entries.','custom-sidebars'); ?></p>
            <?php endif;?></div>
</div>
