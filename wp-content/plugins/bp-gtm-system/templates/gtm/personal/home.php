<?php
get_header();
global $bp;
$bp_gtm_p_tasks_pp = get_user_meta($bp->loggedin_user->id, 'bp_gtm_tasks_pp', true);
if(!$bp_gtm_p_tasks_pp)
   $bp_gtm_p_tasks_pp = 5;

$gtm_profile_link = $bp->loggedin_user->domain . $bp->gtm->slug . '/';
?>

    <div id="content">
        <div class="padder">

            <?php do_action( 'bp_before_member_home_content' ) ?>

            <div id="item-header">
                <?php locate_template( array( 'members/single/member-header.php' ), true ) ?>
            </div><!-- #item-header -->

            <div id="item-nav">
                <div class="item-list-tabs no-ajax" id="object-nav">
                    <ul>
                        <?php bp_get_displayed_user_nav() ?>
                        <?php do_action( 'bp_member_options_nav' ) ?>
                    </ul>
                </div>
            </div><!-- #item-nav -->

            <div id="item-body">
            <div class="item-list-tabs no-ajax" id="subnav">
               <ul><?php bp_gtm_personal_tabs(); ?></ul>
            </div>
                <?php do_action( 'bp_before_member_body' ) ?>

            <?php
            if ($bp->current_action == 'tasks' || !$bp->current_action){
               include(dirname(__File__).'/tasks.php');
            }elseif($bp->current_action == 'projects'){
               include(dirname(__File__).'/projects.php');
            }else{
               include(dirname(__File__).'/settings.php');
            }
            ?>

            <?php do_action( 'bp_after_member_body' ) ?>

            </div><!-- #item-body -->

            <?php do_action( 'bp_after_member_home_content' ) ?>

        </div><!-- .padder -->
    </div><!-- #content -->

    <?php locate_template( array( 'sidebar.php' ), true ) ?>

<?php get_footer() ?>