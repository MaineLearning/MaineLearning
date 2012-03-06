<div class='wrap'>
<?php
	if ( empty( $this->_options[ 'repairbuddy_password' ] ) ) die( 'Access Denied' );
?>
</div><!--/.wrap-->

<br><br>

<a href="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=backupbuddy_repairbuddy_beta" class="button-primary">Download RepairBuddy</a>
&nbsp;&nbsp;
<a href="<?php echo admin_url( 'admin-ajax.php' ); ?>?action=backupbuddy_repairbuddy_reset" class="button-secondary">Reset RepairBuddy Password</a>