<p><?php _e('You can assign specific sidebars to this post, just select a sidebar and the default one will be replaced, if it is available on your template.','custom-sidebars')?></p>
<?php if(!empty($sidebars)): foreach($sidebars as $s): $sb_name = $available[$s]['name']; ?>
	<p><b><?php echo $sb_name;?></b>: 
	<select name="cs_replacement_<?php echo $s ?>">
		<option value=""></option>
		<?php foreach($available as $a):?>
		<option value="<?php echo $a['id']; ?>" <?php echo ($selected[$s]==$a['id']) ? 'selected="selected"' : ''; ?>>
			<?php echo $a['name']; ?>
		</option>
		<?php endforeach;?>
	</select>
	</p>
<?php endforeach; else: ?>
	<p id="message" class="updated"><?php _e('There are not replaceable sidebars selected. You can define what sidebar will be able for replacement in the <a href="themes.php?page=customsidebars">Custom Sidebars config page</a>.','custom-sidebars')?></p>
<?php endif;?> 