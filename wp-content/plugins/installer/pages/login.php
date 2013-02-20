<?php 
if( !defined('ABSPATH') ) die('Security check');
/*if(isset($_GET['buyurl'])){    ;
    ?><iframe src="<?php echo $_GET['buyurl']?>" width="640" height="480" frameborder="0" scrolling="no"></iframe>
    <?php
    exit;
}*/
?>

<script type='text/javascript'>
wprc.login.ajaxloginurl="<?php echo admin_url('admin-ajax.php'); ?>";
//wprc.login.reqErrorMessage="<?php //_e('Connection Error Occurred! Please try again later.','installer'); ?>";
wprc.login.init()
</script>

<div id="login_success_messages_wrap">
    <div id="wprc_repository_login_success" class="updated below-h2">
        <p><?php printf(__('Credentials for %s have been saved', 'installer'), $this->repository->repository_name) ?></p>
        <p class="submit alignright" ><input id="wprc_repository_login_close" type="button" class="button-primary" value="<?php _e('Close', 'installer') ?>" / ></p>
        <br clear="all" />
    </div>
    <div id="wprc_repository_login_fail" class="error below-h2">
        <p></p>
    </div>
</div>

<div id="wprc_repository_login_wrap">
<?php if(empty($this->repository->repository_name)): ?>

    <p class="error login-text"><?php printf(__('The repository %s was not found. Perhaps deleted?', 'installer'), $this->repository->repository_name) ?></p>
    
<?php else: ?>


    <form id="wprc_repository_login_form" action="<?php echo admin_url('admin.php?wprc_c=repository-login&amp;wprc_action=RepositoryLogin') ?>" method="post">

        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('installer-login-form'); ?>" />
        <input type="hidden" name="wprc_c" value="repository-login" />
        <input type="hidden" name="action" value="repository-login" />
        <input type="hidden" name="submit" value="1" />
        <input type="hidden" name="wprc_action" value="RepositoryLogin" />
        <input type="hidden" name="repository_id" value="<?php echo $this->repository->id ?>" />
        
        <table align="center">

            <tr>
                <th colspan="2"><?php printf(__('Please enter your <span class="repository-name">%s</span> login details', 'installer'), $this->repository->repository_name); ?></th>
            </tr>
            <tr>
                <td>
                <label><?php _e('Username', 'installer')?></label>
                </td>
                <td>
                    <input type="text" name="username" <?php if($this->repository->repository_username!='') echo "disabled"; ?>/>
                </td>
            </tr>
            <tr>
                <td>
                <label><?php _e('Password', 'installer')?></label>
                </td>
                <td>
                    <input type="hidden" name="_plain" value="true" />
                    <input type="password" name="password" <?php if($this->repository->repository_password!='') echo "disabled"; ?> />
                </td>
            </tr>    
            <tr>
                <td align="left">
    				<div id="wprc-loader"><img src="<?php echo WPRC_ASSETS_URL . '/css/290.gif'; ?>" /></div>
                </td>
                <td>
                    <input name="submit" class="button-primary" id='wprc-login-submit' type="submit" value="<?php _e('Login', 'installer') ?>" />
                    <input name="cancel" class="button-secondary" id='wprc-login-cancel' type="button" value="<?php _e('Cancel', 'installer') ?>" />
                </td>
            </tr>    
        </table>
    </form>
    
    
<?php endif; ?>
</div>


