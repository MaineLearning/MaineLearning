<?php
$bp_gtm_actions = get_option('bp_gtm_actions');

if ($bp_gtm_actions['role'] == 'on') {
    $actions = new BP_GTM_Actions();
    add_action('bp_actions', array($actions, 'init'));
    add_action('wp_ajax_bp_gtm_show_role_ajax', array($actions, 'show_role_ajax'));
    add_action('wp_ajax_bp_gtm_change_role_ajax', array($actions, 'change_role_ajax'));
}


class BP_GTM_Actions {

    function init(){
        global $bp;
        $bp_gtm = get_option('bp_gtm');
//        var_dump($bp_gtm);die;
        if( bp_gtm_check_access('involved_roles') &&
                ( $bp_gtm['groups'] == 'all' || array_key_exists($bp->groups->current_group->id, $bp_gtm['groups']) ) ){
            
            add_action('bp_directory_involved_actions', array($this, 'role_extra_button'));
        }
        add_action('wp_footer', array($this, 'role_popup'));
        add_action('wp_print_scripts', array($this, 'show_role_js'));
    }

    function show_role_js(){
        wp_enqueue_script('bp-gtm-actions-js', GTM_URL . '_inc/actions.js');
    }

    function show_role_ajax(){
        echo '
        <a id="popupRolesClose" title="'.__('Close this window','bp_gtm').'">x</a>
        <h1>'.sprintf(__('Change a role for %s','bp_gtm'), bp_core_get_userlink($_GET['user'])).' </h1>
        <p id="popupArea">'.__('Select new role for this user.','bp_gtm').'
            <span class="float-right">
                <select id="role_name">
                    <option value="0">'.__('select a role','bp_gtm').'</option>';
                    $roles = bp_gtm_roles_list();
                    foreach($roles as $role){
                        echo '<option '.(bp_gtm_get_role_for_user($_GET['user'])->id == $role->id ? 'selected="selected"' : '' ).' value="'.(int)$role->id.'">'.$role->role_name.'</option>';
                    }
                echo '
                </select>
            </span>
        </p>
        <p id="popupArea">
            <span class="popupReport"></span>
            <a rel="'.(int)$_GET['user'].'" href="#" class="button" id="change_role">Click to Change Role</a>
        </p>';
        die();
    }

    function change_role_ajax(){
        global $bp;
        if (bp_gtm_change_user_role($_GET['user'], $_GET['role'], $bp->groups->current_group->id)) {
            echo '1';
        }else{
            echo '0';
        }
        die();
    }

    
    // display on Group->GTM->Involved page
    function role_extra_button($user_id){
        echo ' | <a class="change_role" rel="'.$user_id.'" href="#" title="'.__('Change role','bp_gtm').'">'.__('R', 'bp_gtm').'</a>';
    }

    function role_popup(){
        global $bp;
//        if($bp->loggedin_user->gtm_access->involved_roles == '1')
            echo '<div id="popupRoles"></div><div id="backgroundPopup"></div>';
    }
}

?>
