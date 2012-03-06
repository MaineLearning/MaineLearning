<?php
if ( !defined( 'PB_WP_LOADED' ) ) :
	global $pluginbuddy_repairbuddy;
	$pluginbuddy_repairbuddy->output_status( "WordPress is required for this functionality.  Please make sure RepairBuddy is placed at the root of your WordPress install.", true );
else:
?>
<div>
	<form method="post" action="#" id='search_user'>
		<input type="hidden" name="action" value="search_user" />
		<h3><label for='user_search'>Enter a Username or E-mail Address to Search for or Create</label></h3>
		<input type='text' size='80' id='user_search' name='user_search' value='Username or E-mail Address' />
		<p><input id='search' type="submit" name="submit" value="Search" class="button"></p>
		<input type='hidden' name='hash' id='hash' value='<?php echo htmlspecialchars( $_GET[ 'v' ] ); ?>' />
		<input type='hidden' name='page' id='page' value='<?php echo htmlspecialchars( $_GET[ 'page' ] ); ?>' />
	</form>
	<form method="post" action="#" id='create_user' style='display: none;'>
		<input type="hidden" name="action" value="create_user" />
		<h3>Enter Your User Information</h3>
		<div id='dynamic_user'>
			<input type='text' name='username' id='username' value='' /><label for='username'>Username</label><br />
			<input type='text' name='email' id='email' value='' /><label for='email'>E-mail Address</label><br />
			<input type='password' name='pass1' id='pass1' value='' /><label for='pass1'>Password</label><br />
			<input type='password' name='pass2' id='pass2' value='' /><label for='pass2'>Confirm Password</label><br />
		</div><!-- #dynamic_user-->
		<div id='save_cancel'>
			<input id='save' type='button' name='save' class='button' value="Save" />&nbsp;&nbsp;<input type='button' type='button' name='cancel' id='cancel' class='button-secondary' value="Cancel" />
		</div>
		<input type='hidden' name='hash' id='hash' value='<?php echo htmlspecialchars( $_GET[ 'v' ] ); ?>' />
		<input type='hidden' name='page' id='page' value='<?php echo htmlspecialchars( $_GET[ 'page' ] ); ?>' />
		<input type='hidden' name='user_id' id='user_id' value='0' />
		
	</form>
	<div class='updated' id='status' style='display: none;'><p><strong id='status_message'>dd</strong></p></div>
	<div id='loading' style='display: none;'><img src='<?php echo $this->get_plugin_url( 'images/working.gif', dirname( __FILE__ ) ); ?>' title="Loading" alt="Loading" /></div>
</div>
<?php 
endif;
?>