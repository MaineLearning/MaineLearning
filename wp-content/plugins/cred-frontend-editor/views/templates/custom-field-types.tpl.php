<?php if (!defined('ABSPATH'))  die('Security check'); ?>
<!DOCTYPE html>
<html>
<head>
<?php
// include jquery from wp-admin an styles
wp_enqueue_script('jquery-ui-sortable');
wp_print_scripts('jquery-ui-sortable');
wp_enqueue_style('wp-admin');
wp_enqueue_style('colors-fresh');
wp_print_styles('wp-admin');
wp_print_styles('colors-fresh');
wp_enqueue_style('cred_cred_style', CRED_ASSETS_URL.'/css/cred.css');
wp_print_styles('cred_cred_style');
?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body class="cred-box">
<h2 style='margin:0;padding-top:10px;'><?php _e('Set Field Type','wp-cred'); ?></h2>
    <div>
        <div class="cred-box-inner">
        <?php
            foreach ($fields as $type=>$field)
            {
                echo "<a target='_self' href='".$url.'&field='.$type."' class='button cred_field_add' title='".sprintf(__('Set Field type "%s"','wp-cred'),$field['title'])."'>".$field['title']."</a>";
            }
        ?>
        </div>
    </div>
</body>
</html>