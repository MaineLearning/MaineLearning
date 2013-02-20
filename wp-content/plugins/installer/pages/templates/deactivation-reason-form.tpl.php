<?php if( !defined('ABSPATH') ) die('Security check'); ?>
<script language="javascript">
jQuery(document).ready(function()
{
    wprc.uninstallReport.ajaxurl="<?php echo admin_url('admin.php').'?wprc_c=repository-reporter&wprc_action=sendUninstallReport'; ?>";
	wprc.uninstallReport.skipajaxurl="<?php echo admin_url('admin.php').'?wprc_c=repository-reporter&wprc_action=skipUninstallReport'; ?>";
	<?php if ($bulk_deactivate==1) { ?>
		wprc.uninstallReport.action="bulk_action";
	<?php } else { ?>
		wprc.uninstallReport.action="single_action";
		wprc.uninstallReport.gotourl="<?php echo $url.'&reported=true'; ?>";
	<?php } ?>
    wprc.uninstallReport.init();
});
</script>
<div class="wrap">

<div id="icon-repositories-installer" class="icon32"><br></div>
<h2>
    <?php 
        if ( 'theme' === $extension_type )
            echo __('Are you replacing the theme because of a problem?','installer'); 
        elseif ( 'plugin' === $extension_type )
            echo __('Is there a problem with this plugin?','installer'); 
    ?>
</h2>

<p><?php _e( 'Installer plugin lets you report possible problems with themes and plugins. Your report will be sent anonymously and will be shown in wp-compatibility.com', 'installer' ); ?></p>

<?php if ($bulk_deactivate==1) { ?>
<!-- normal deactivation form -->
<form method="post" action="<?php echo $url; ?>" id="deactivation_form">
<input type="hidden" name="reported" value="true">
<input type="hidden" name="_wpnonce" value="<?php echo esc_attr($bulk_data['_wpnonce']); ?>">
<input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr($bulk_data['_wp_http_referer']); ?>">
<input type="hidden" name="action" value="<?php echo esc_attr($bulk_data['action']); ?>">
<input type="hidden" name="action2" value="<?php echo esc_attr($bulk_data['action2']); ?>">
<?php foreach ($bulk_data['checked'] as $bulk_one) { ?>
<input type="hidden" name="checked[]" value="<?php echo esc_attr($bulk_one); ?>">
<?php } ?>
</form>
<?php } ?>

<!-- our deactivation report form -->
<form method="post" action="<?php echo admin_url('admin.php').'?wprc_c=repository-reporter&wprc_action=sendUninstallReport'; ?>" id="deactivation_report_form">
<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('installer-deactivation-form'); ?>">
<input type="hidden" name="site_id" value="<?php echo esc_attr($site_id); ?>">

<?php
    for($i=0; $i<count($extension_keys); $i++)
    {
        echo '<input type="hidden" name="extension_keys[]" value="'.$extension_keys[$i].'">';
    }
?>
<br />
<input type="hidden" name="extension_type" value="<?php echo esc_attr($extension_type); ?>">
<!--input type="hidden" name="bulk_deactivate" value="<?php echo esc_attr($bulk_deactivate); ?>">-->
<table cellpadding="3px">
<tr>
<td>
    <div id="urc1-wrap" class="wprc option-wrap">
        <label>
            <input type="radio" id="urc1" name="uninstall_reason_code" value="1" onchange="wprc.uninstallReport.showDetails('#urc1')"> 
            <span><?php echo $language['i_dont_need_what_this_extension_do']; ?></span>
        </label>
    </div>
</td>
</tr>

<tr>
<td>
    <div id="urc2-wrap" class="wprc option-wrap">
        <label>
            <input type="radio" id="urc2" name="uninstall_reason_code" value="2" onchange="wprc.uninstallReport.showDetails('#urc2')"> 
            <span><?php echo $language['i_dont_like_how_the_extension_performs_its_tasks']; ?></span>
        </label>
    </div>
</td>
</tr>

<tr>
<td>
   <div id="urc3-wrap" class="wprc option-wrap">
   <label>
        <input type="radio" id="urc3" name="uninstall_reason_code" value="3" onchange="wprc.uninstallReport.showDetails('#urc3')"> 
        <span><?php echo $language['extension_is_not_working_right_on_my_site']; ?></span>
   </label> 
    <div class="wprc validation-errors" id="urc3-errors"></div>
    <div id="urc3-box" class="wprc details-boxes">
        <table>
        <?php 
        if($show_themes_list) 
        {
        ?>
        <tr>
            <td>
                <label>
                    <input id="urc31" name="uninstall_reason_code_child" value="1" type="radio" onchange="wprc.uninstallReport.showSubdetails('#urc31')"> 
                    <span><?php echo __('There is a problem with themes','installer'); ?></span>
                </label>
                <div class="wprc subdetails-boxes" id="urc31-subbox">
                    <select id="theme-select" size="5" name="problem_themes[]" multiple="multiple" style="display: none;">
                	<?php 
                    foreach($themes AS $key => $theme)
                    {
                        echo '<option value="'.$key.'"> '.$theme['Name'].'</option>';
                    }
                    ?>
                	</select>
                </div>
                <div class="wprc validation-errors" id="urc31-errors"></div>
            </td>
        </tr>
        <?php
        }
        ?>
        <tr>
            <td>
                <label>
                    <input id="urc32" name="uninstall_reason_code_child" value="2" type="radio" onchange="wprc.uninstallReport.showSubdetails('#urc32')">
                    <span><?php echo __('There is a problem with plugins','installer'); ?></span>
                </label>
                <div class="wprc subdetails-boxes" id="urc32-subbox" >
                    <select id="plugin-select" size="5" name="problem_plugins[]" multiple="multiple" style="display: none;">
              		<?php 
                    foreach($plugins AS $key => $plugin)
                    {
                        echo '<option value="'.$key.'"> '.$plugin['Name'].'</option>';
                    }
                    ?>
                	</select>
                </div>
                <div class="wprc validation-errors" id="urc32-errors"></div>
            </td>
        </tr>
        
        <tr>
            <td>
                <label>
                    <input id="urc30" name="uninstall_reason_code_child" value="0" type="radio" onchange="wprc.uninstallReport.showSubdetails('#urc30')">
                    <span><?php echo __('I\'m not sure what\'s causing the problem','installer'); ?></span>
                </label>
            </td>
        </tr>
        </table>
    </div>
    </div>
</td>
</tr>

<tr>
<td>
   <div id="urc0-wrap" class="wprc option-wrap">
   <label>
        <input type="radio" id="urc0" name="uninstall_reason_code" value="0">
        <span><?php echo __('Something else','installer'); ?></span>
    </label>
    </div>
</td>
</tr>

<tr>
<td>
    <div id="urc0-box" style="display:none">
        <p><?php echo $language['describe_uninstalling_reason']; ?></p>
        <textarea class="wprc box" name="uninstall_reason_description"></textarea>
    </div>
</td>
</tr>

<tr>
<td>
    <div id="urc0-box">
        <p><input type="checkbox" id="uninstall_no_more_reports" name="uninstall_no_more_reports" value="1"> 
            <label for="uninstall_no_more_reports"><?php _e( 'Do not offer to send any more feedback reports', 'installer' ); ?></label></p>
        
    </div>
</td>
</tr>

<tr>
<td>
	
    <input type="button" class="button-primary" onclick="wprc.uninstallReport.submitDeactivationForm('deactivation_report_form','deactivation_form')" value="<?php echo $language['report_and_uninstall']; ?>">
    <input type="button" class="button-secondary" onclick="wprc.uninstallReport.skipUninstallReport('deactivation_report_form','deactivation_form')" value="<?php echo __('No problem to report','installer'); ?>">
    <div id="wprc-loader"></div>
</td>
</tr>


<script>
    jQuery(document).ready(function() {
        jQuery('input[type=radio]').change(function() {
            wprc.uninstallReport.toggleTellUsTextarea();
        });
    });
</script>
<tr>
<td><div class="wprc validation-errors" id="below-submit-errors"></div></td>
</tr>
</table>
</form>
<?php if ( ! empty( $extension_name ) ): ?>
    <h3><?php printf( __( 'See what others have said about this %s', 'installer' ), $extension_type ); ?></h3>
    <?php $compatibility_url = 'http://wp-compatibility.com/compatibility-result'; ?>
    <?php $compatibility_url = add_query_arg( 's1', urlencode($extension_name), $compatibility_url);?>

    <?php 
        if ( $extension_type == 'theme' )
            echo sprintf( __( 'Visit the %scompatibility report%s for %s in wp-compatibility.com, to see how it works with different plugins.', 'installer' ), '<a href="' . $compatibility_url . '" target="_blank">', '</a>', $extension_name );
        else 
            echo sprintf( __( 'Visit the %scompatibility report%s for %s in wp-compatibility.com, to see how it works with different themes and plugins.', 'installer' ), '<a href="' . $compatibility_url . '" target="_blank">', '</a>', $extension_name );
    ?>
<?php endif; ?>

</div>