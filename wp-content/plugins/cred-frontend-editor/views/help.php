<?php
if (!defined('ABSPATH'))  die('Security check');
?>

<div class="wrap cred-wrap">
    <h2 class="cred-h2"><?php _e('CRED Help','wp-cred') ?>&nbsp;&nbsp;(<?php printf(__('Version %s','wp-cred'),CRED_FE_VERSION); ?>)</h2><br />

	<?php
	// Installer plugin active?
    $installer_on = defined('WPRC_VERSION') && WPRC_VERSION;

    if(!$installer_on){
    ?>
    <div class="cred_cyan_box">
        <p> <?php printf(__('The recommended way to install and update CRED plugin is by using the %s.', 'wp-cred'),'<a target="_blank" href="http://wp-compatibility.com/installer-plugin/">'.  __('Installer Plugin', 'wp-cred'). '</a>'); ?> </p>
        <br />
        <p>
            <a target="_blank" class="button-primary" href="http://wp-compatibility.com/installer-plugin/"><?php  _e('Download Installer', 'wp-cred'); ?> </a>&nbsp;
            <a target="_blank" href="http://wp-types.com/faq/how-to-install/"><?php  _e('Instructions', 'wp-cred'); ?> </a>
        </p>
    </div>
    <?php
    }
	?>


<h3 style="margin-top:3em;"><?php _e('Documentation and Support', 'wp-cred'); ?></h3>
<ul>
    <li><?php printf('<a target="_blank" href="http://wp-types.com/documentation/user-guides/#CRED"><strong>%s</strong></a>'.__(' - everything you need to know about using CRED', 'wp-cred'),__('User Guides', 'wp-cred')); ?></li>
	<li><?php printf('<a target="_blank" href="http://discover-wp.com/"><strong>%s</strong></a>'.__(' - learn to use CRED by experimenting with fully-functional learning sites','wp-cred'),__('Discover WP','wp-cred') ); ?></li>
    <li><?php printf('<a target="_blank" href="http://wp-types.com/forums/forum/support-2/"><strong>%s</strong></a>'.__(' - online help by support staff', 'wp-cred'),__('Support forum', 'wp-cred') ); ?></li>
</ul>

</div>
