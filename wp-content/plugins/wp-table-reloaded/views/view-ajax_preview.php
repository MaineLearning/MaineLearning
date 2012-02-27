<?php if ( !defined( 'WP_TABLE_RELOADED_ABSPATH' ) ) exit; // no direct loading of this file ?>
            <div style="clear:both;"><p>
            <?php _e( 'This is a preview of your table.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?><br/>
            <?php _e( 'Because of CSS styling, the table might look different on your page!', WP_TABLE_RELOADED_TEXTDOMAIN ); ?> <?php _e( 'The JavaScript libraries are also not available in this preview.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?><br/>
            <?php printf( __( 'To insert the table into a page, post or text-widget, copy the shortcode <strong>[table id=%s /]</strong> and paste it into the corresponding place in the editor.', WP_TABLE_RELOADED_TEXTDOMAIN ), $this->helper->safe_output( $table['id'] ) ); ?>
            </p></div>
            <div style="clear:both;">
            <?php
                $WP_Table_Reloaded_Frontend = $this->create_class_instance( 'WP_Table_Reloaded_Controller_Frontend', 'controller-frontend.php', 'controllers' );
                $WP_Table_Reloaded_Frontend->options['frontend_edit_table_link'] = false; // set this (temporarily and locally) to false -> no link in the preview
                $atts = array( 'id' => $_GET['table_id'] );
                echo $WP_Table_Reloaded_Frontend->handle_content_shortcode_table( $atts );
            ?>
            </div>