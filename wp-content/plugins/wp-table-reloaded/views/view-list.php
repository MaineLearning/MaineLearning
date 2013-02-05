<?php if ( !defined( 'WP_TABLE_RELOADED_ABSPATH' ) ) exit; // no direct loading of this file ?>
        <div style="clear:both;"><p><?php _e( 'This is a list of all available tables.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?> <?php _e( 'You may add, edit, copy, delete or preview tables here.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?><br /><br/><?php printf( __( 'To insert the table into a page, post or text-widget, copy the shortcode <strong>[table id=%s /]</strong> and paste it into the corresponding place in the editor.', WP_TABLE_RELOADED_TEXTDOMAIN ), '&lt;ID&gt;' ); ?> <?php _e( 'Each table has a unique ID that needs to be adjusted in that shortcode.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?> <?php printf( __( 'You can also click the button &quot;%s&quot; in the editor toolbar to select and insert a table.', WP_TABLE_RELOADED_TEXTDOMAIN ), __( 'Table', WP_TABLE_RELOADED_TEXTDOMAIN ) ); ?></p></div>
		<?php
        if ( 0 < count( $this->tables ) ) {
            ?>
        <div style="clear:both;">
            <form method="post" action="<?php echo $this->get_action_url(); ?>">
            <?php wp_nonce_field( $this->get_nonce( 'bulk_edit' ) ); ?>
            <table class="widefat" id="wp-table-reloaded-list">
            <thead>
                <tr>
                    <th class="check-column" scope="col"><input type="checkbox" /></th>
                    <th scope="col"><?php _e( 'ID', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col" style="display:none;"></th>
                    <th scope="col"><?php _e( 'Table Name', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Description', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Last Modified', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="check-column" scope="col"><input type="checkbox" /></th>
                    <th scope="col"><?php _e( 'ID', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col" style="display:none;"></th>
                    <th scope="col"><?php _e( 'Table Name', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Description', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Last Modified', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                </tr>
            </tfoot>
            <tbody>
            <?php
            $bg_style_index = 0;
            foreach ( $this->tables as $id => $tableoptionname ) {
                $bg_style_index++;
                $bg_style = ( 1 == ($bg_style_index % 2) ) ? ' class="alternate"' : '';

                // get name and description to show in list
                $table = $this->load_table( $id );
                    $name = ( !empty( $table['name'] ) ) ? $this->helper->safe_output( $table['name'] ) : __( '(no name)', WP_TABLE_RELOADED_TEXTDOMAIN );
                    $description = ( !empty( $table['description'] ) ) ? $this->helper->safe_output( $table['description'] ) : __( '(no description)', WP_TABLE_RELOADED_TEXTDOMAIN );
                    $last_modified = $this->format_datetime( $table['last_modified'] );
                    $last_editor = $this->get_last_editor( $table['last_editor_id'] );
                    if ( !empty( $last_editor ) )
                        $last_editor = __( 'by', WP_TABLE_RELOADED_TEXTDOMAIN ) . ' ' . $last_editor;
                unset( $table );

                $edit_url = $this->get_action_url( array( 'action' => 'edit', 'table_id' => $id ), false );
                $copy_url = $this->get_action_url( array( 'action' => 'copy', 'table_id' => $id ), true );
                $export_url = $this->get_action_url( array( 'action' => 'export', 'table_id' => $id ), false );
                $delete_url = $this->get_action_url( array( 'action' => 'delete', 'table_id' => $id, 'item' => 'table' ), true );
                $preview_url = $this->get_action_url( array( 'action' => 'ajax_preview', 'table_id' => $id ), true );

                echo "<tr id=\"wp-table-reloaded-table-{$id}\" {$bg_style}>\n";
                echo "\t<td class=\"check-column no-wrap\"><input type=\"checkbox\" name=\"tables[]\" value=\"{$id}\" /></td>\n";
                echo "\t<td class=\"no-wrap table-id\">{$id}</td>\n";
                echo "\t<td style=\"display:none;\">{$name}</td>\n";
                echo "\t<td>\n";
                echo "\t\t<a title=\"" . sprintf( __( 'Edit %s', WP_TABLE_RELOADED_TEXTDOMAIN ), "&quot;{$name}&quot;" ) . "\" class=\"row-title\" href=\"{$edit_url}\">{$name}</a>\n";
                echo "\t\t<div class=\"row-actions no-wrap\">";
                echo "<a href=\"{$edit_url}\">" . __( 'Edit', WP_TABLE_RELOADED_TEXTDOMAIN ) . "</a>" . " | ";
                $shortcode = "[table id={$id} /]";
                echo "<a href=\"javascript:void(0);\" class=\"table_shortcode_link\" title=\"{$shortcode}\">" . __( 'Shortcode', WP_TABLE_RELOADED_TEXTDOMAIN ) . "</a>" . " | ";
                echo "<a class=\"copy_table_link\" href=\"{$copy_url}\">" . __( 'Copy', WP_TABLE_RELOADED_TEXTDOMAIN ) . "</a>" . " | ";
                echo "<a href=\"{$export_url}\">" . __( 'Export', WP_TABLE_RELOADED_TEXTDOMAIN ) . "</a>" . " | ";
                echo "<span class=\"delete\"><a class=\"delete_table_link\" href=\"{$delete_url}\">" . __( 'Delete', WP_TABLE_RELOADED_TEXTDOMAIN ) . "</a></span>" . " | ";
                $preview_title = sprintf( __( 'Preview of Table %s', WP_TABLE_RELOADED_TEXTDOMAIN ), $id );
                echo "<a class=\"preview-link\" href=\"{$preview_url}\" title=\"{$preview_title}\">" . __( 'Preview', WP_TABLE_RELOADED_TEXTDOMAIN ) . "</a>";
                echo "</div>\n";
                echo "\t</td>\n";
                echo "\t<td>{$description}</td>\n";
                echo "\t<td class=\"no-wrap\">{$last_modified}<br/>{$last_editor}</td>\n";
                echo "</tr>\n";
            }
            ?>
            </tbody>
            </table>
        <input type="hidden" name="action" value="bulk_edit" />
        <p class="submit" style="clear:both;"><?php _e( 'Bulk actions:', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>  <input type="submit" name="submit[copy]" class="button-primary bulk_copy_tables" value="<?php _e( 'Copy Tables', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>" /> <input type="submit" name="submit[delete]" class="button-primary bulk_delete_tables" value="<?php _e( 'Delete Tables', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>" />
        </p>

        </form>
        </div>
        <?php
        } else { // end if $tables
            $add_url = $this->get_action_url( array( 'action' => 'add' ), false );
            $import_url = $this->get_action_url( array( 'action' => 'import' ), false );
            echo "<div style=\"clear:both;\"><p>" . __( 'No tables were found.', WP_TABLE_RELOADED_TEXTDOMAIN ) . '<br/><br/><strong>' . sprintf( __( 'You should <a href="%s">add</a> or <a href="%s">import</a> a table to get started!', WP_TABLE_RELOADED_TEXTDOMAIN ), $add_url, $import_url ) . "</strong></p></div>";
        }

        // add tablesorter script
        if ( 0 < count( $this->tables ) )
            add_action( 'admin_footer', array( &$this, 'output_tablesorter_js' ) );
        ?>