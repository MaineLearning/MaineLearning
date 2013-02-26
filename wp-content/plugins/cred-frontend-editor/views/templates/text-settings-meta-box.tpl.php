<?php if (!defined('ABSPATH'))  die('Security check'); ?>
<p class='cred-label-holder'>
	<?php _e('These are texts that display for different form operations. If you are using WPML, you will be able to translate them via WPML->String Translation.','wp-cred'); ?>
</p>
<table class="cred-form-texts">
<tbody>
<?php
foreach ($messages as $msgid=>$msg)
{
    ?>
    <tr>
        <td class="cred-form-texts-desc">
            <?php echo $msg['desc']; ?>
        </td>
        <td class="cred-form-texts-msg">
            <input name='<?php echo $msgid; ?>' type='text' value='<?php echo $msg['msg']; ?>' />
        </td>
    </tr>
    <?php
}
?>
</tbody>
</table>