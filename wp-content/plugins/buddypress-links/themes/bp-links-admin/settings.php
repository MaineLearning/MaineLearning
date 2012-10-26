<?php
/**
 * BP Links admin manage settings
 */
?>

<?php if ( isset( $message ) ) { ?>
	<div id="message" class="<?php echo $type ?> fade">
		<p><?php echo $message ?></p>
	</div>
<?php } ?>

<div class="wrap buddypress-links-admin-settings" style="position: relative">

	<?php screen_icon( 'bp-links' ); ?>

	<h2><?php _e( 'Edit Settings', 'buddypress-links' ) ?></h2>

	<?php BP_Links_Settings::instance()->settings() ?>

	<p>
		<strong>&dagger;</strong> -
		<em><a href="http://shop.presscrew.com/shop/buddypress-links/" target="_blank"><?php _e( 'Setting applies to pro extension only', 'buddypress-links' ) ?></a></em>
	</p>
	
</div>

<script type="text/javascript">
	jQuery(document).ready(function($){
		$('div.buddypress-links-admin-settings input.disabled').attr('disabled', 'disabled');
	});
</script>

<?php include 'sidebar.php'; ?>