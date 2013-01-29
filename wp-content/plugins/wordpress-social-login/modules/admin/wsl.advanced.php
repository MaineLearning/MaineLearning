<div>  
	<form method="post" id="wsl_setup_form" action="options.php"> 
	<?php settings_fields( 'wsl-settings-group-advanced-settings' ); ?>

	<div class="metabox-holder columns-2" id="post-body">
	<div  id="post-body-content"> 

	<div id="namediv" class="stuffbox">
		<h3>
			<label for="name">Advanced Settings</label>
		</h3>
		<div class="inside"> 
			<br />
			<h3 style="text-align:center;font-size: 17px;">
				<b>Please</b> for the love of <b>God</b>, stay out of Advanced.. unless you are Advanced and you know what you are doing.
			</h3> 
			<br />
			<table width="100%" border="0" cellpadding="5" cellspacing="2" style="border-top:1px solid #ccc;">  
			  <tbody>
			  <tr>
				<td width="200" align="right" valign="top"><strong>WSL Base URL :</strong></td>
				<td> 
				<input type="text" name="wsl_settings_base_url" value="<?php echo get_option( 'wsl_settings_base_url' ); ?>" class="inputgnrc"> 
				<br />
				eg: <code>http://www.example.com/wp-content/plugins/wordpress-social-login</code> without '/',. Keep it empty to rest to default.
				</td>
			  </tr> 
			</tbody>
			</table>
 
		</div>
	</div>

	<br style="clear:both;" />
	<div style="margin-left:5px;margin-top:-20px;"> 
		<input type="submit" class="button-primary" value="Save Settings" /> 
	</div>

	</div> 
	</div> 
	</form> 
</div> 