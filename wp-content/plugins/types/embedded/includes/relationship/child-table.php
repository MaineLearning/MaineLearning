<?php
/*
 * Child table
 */

?>

<!--WRAPPER-->
<div class="wpcf-pr-has-entries wpcf-pr-pagination-update wpcf-relationship-save-all-update">

    <!--TITLE-->
    <div class="wpcf-pr-has-title"><?php echo $this->child_post_type_object->label; ?></div>

    <!--BUTTONS-->

    <!--SAVE ALL-->
    <a class="button-primary wpcf-pr-save-all-link" href="<?php
echo admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_save_all'
        . '&amp;post_id=' . $this->parent->ID . '&amp;post_type='
        . $this->child_post_type . '&amp;_wpnonce='
        . wp_create_nonce( 'pr_save_all' )
);

?>"><?php
       printf( __( 'Save All %s', 'wpcf' ), $this->child_post_type_object->label );

?></a>&nbsp;

    <!--ADD NEW-->
    <a href="<?php
        echo admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;'
                . 'wpcf_action=pr_add_child_post&amp;post_type_parent='
                . $this->parent_post_type
                . '&amp;post_id=' . $this->parent->ID
                . '&amp;post_type_child='
                . $this->child_post_type . '&_wpnonce=' . wp_create_nonce( 'pr_add_child_post' )
        );

?>" style="line-height:40px;" class="wpcf-pr-ajax-link button-secondary"><?php echo $this->child_post_type_object->labels->add_new_item; ?></a>

    <!--REPETITIVE WARNING-->
    <?php
    if ( !empty( $this->repetitive_warning ) ):

        ?>
        <div class="wpcf-message wpcf-error"><p><?php
    echo __( 'Repeating fields should not be used in child posts. Types will update all field values.',
            'wpcf' );

        ?></p></div>
        <?php
    endif;

    ?>

    <!--PAGINATION TOP-->
    <?php echo $this->pagination_top; ?>

    <!--TABLE-->
    <div class="wpcf-pr-pagination-update--old">
        <div class="wpcf-pr-table-wrapper">
            <table id="wpcf_pr_table_sortable_<?php echo md5( $this->child_post_type ); ?>" class="tablesorter wpcf_pr_table_sortable" cellpadding="0" cellspacing="0" style="width:100%;">
                <thead>
                    <tr>
                        <?php
                        foreach ( $headers as $header ):

                            ?>
                            <th class="wpcf-sortable">&nbsp;&nbsp;&nbsp;<?php echo $header; ?></th>
                            <?php
                        endforeach;

                        ?>
                        <th>
                            <?php
                            echo __( 'Action', 'wpcf' );

                            ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ( $rows as $child_id => $row ):
                        include dirname( __FILE__ ) . '/child-table-row.php';
                    endforeach;

                    ?>
                </tbody>
            </table>
            <?php
            // Trigger date
            // TODO Move to date
            if ( !empty( $this->trigger_date ) ):

                ?>
                <script type="text/javascript">
                    //<![CDATA[
                    jQuery(document).ready(function(){
                        wpcfFieldsDateInit("#wpcf-post-relationship");
                    });
                    //]]>
                </script>
                <?php
            endif;

            // Trigger Conditional script 
            // TODO Move to conditional
            if ( defined( 'DOING_AJAX' ) ):

                ?>
                <script type="text/javascript">wpcfPrVerifyInit();</script>
                <?php
            endif;

            ?>
        </div>
    </div>
    <!--PAGINATION BOTTOM-->
    <div class="wpcf-pagination-boottom"><?php echo $this->pagination_bottom; ?></div>
    <hr />
</div>