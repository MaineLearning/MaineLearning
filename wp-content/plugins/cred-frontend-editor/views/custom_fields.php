<?php
if (!defined('ABSPATH'))  die('Security check');
if(!current_user_can('manage_options')) {
	die('Access Denied');
}
?>

<?php
wp_enqueue_style( 'thickbox' );
wp_print_styles( 'thickbox' );
wp_enqueue_script( 'thickbox' );
wp_print_scripts( 'thickbox' );
?>

<script type='text/javascript'>
(function($)
{
    var not_set_text="<?php _e('Not Set','wp-cred'); ?>";
    
    $(function(){
        $('.ajax-feedback').css('visibility','hidden');
        $('.cred-field-type').each(function(){
            var $_this=$(this);
            if (not_set_text==$_this.text())
            {
                $_this.closest('tr').find('._cred-field-edit').hide();
                $_this.closest('tr').find('._cred-field-set').show();
                $_this.closest('tr').find('._cred-field-remove').hide();
            }
            else
            {
                $_this.closest('tr').find('._cred-field-edit').show();
                $_this.closest('tr').find('._cred-field-set').hide();
                $_this.closest('tr').find('._cred-field-remove').show();
            }
        });
        $('._cred-field-remove').each(function(){
            var $_this=$(this);
            $_this.click(function(event){
                event.preventDefault();
                event.stopPropagation();
                
                $('.ajax-feedback').css('visibility','visible');
                $.get($_this.attr('href'), function(res){
                    console.log(res);
                    if ('true'==res)
                    {
                        $_this.closest('tr').find('.cred-field-type').text(not_set_text);
                        $_this.closest('td').find('._cred-field-edit').hide();
                        $_this.closest('td').find('._cred-field-set').show();
                        $_this.hide();
                        $('.ajax-feedback').css('visibility','hidden');
                    }
                });
            });
        });
        
        /*$('input[name="ignorechecked[]"]')
        .change(function(){
            if ($(this).is(':checked'))
            {
                $('#unignorecheckbox_'+$(this).attr('id')).removeAttr('checked');
            }
            else
            {
                $('#unignorecheckbox_'+$(this).attr('id')).attr('checked','checked');
            }
        }).trigger('change');*/
        /*$('input[name="show_private"]')
        .change(function(){
            if ($(this).is(':checked'))
            {
                $('input[name="dont_show_private"]').removeAttr('checked');
            }
            else
            {
                $('input[name="dont_show_private"]').attr('checked','checked');
            }
        }).trigger('change');*/
    });
})(jQuery);
</script>

<div class="wrap cred-wrap">
    <h2 class="cred-h2"><?php _e('Manage Other Post Types with CRED','wp-cred') ?></h2><br />
    <?php
    if (isset($_REQUEST['posttype']) && !empty($_REQUEST['posttype']))
    {
        $cfmodel=CRED_Loader::get('MODEL/Fields');
        if (isset($_REQUEST['ignorechecked']) && is_array($_REQUEST['ignorechecked']))
        {
            $cfmodel->ignoreCustomFields($_REQUEST['posttype'], $_REQUEST['ignorechecked']);
        }
        if (isset($_REQUEST['unignorechecked']) && is_array($_REQUEST['unignorechecked']))
        {
            $cfmodel->ignoreCustomFields($_REQUEST['posttype'], $_REQUEST['unignorechecked'], 'unignore');
        }
        if (isset($_REQUEST['resetchecked']) && is_array($_REQUEST['resetchecked']))
        {
            $cfmodel->ignoreCustomFields($_REQUEST['posttype'], $_REQUEST['resetchecked'], 'reset');
        }
    }
    // display custom fields table
    $wp_list_table = CRED_Loader::get('TABLE/Custom_Fields');
    //$doaction = $wp_list_table->current_action();
    $wp_list_table->prepare_items();
    ?>
    <form id="custom_fields" action="" method="post">
    <?php
    $wp_list_table->display();
    ?>
    </form>
</div>