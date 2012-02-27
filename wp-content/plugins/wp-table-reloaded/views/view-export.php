<?php if ( !defined( 'WP_TABLE_RELOADED_ABSPATH' ) ) exit; // no direct loading of this file ?>
<?php
        // Begin Export Table Form
        $table = $this->load_table( $table_id );

        $rows = count( $table['data'] );
        $cols = (0 < $rows) ? count( $table['data'][0] ) : 0;
        ?>
        <div style="clear:both;">
        <p><?php _e( 'It is recommended to export and backup the data of important tables regularly.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?> <?php _e( 'Select the table, the desired export format and (for CSV only) a delimiter.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?> <?php _e( 'You may choose to download the export file. Otherwise it will be shown on this page.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?><br/><?php _e( 'Be aware that only the table data, but no options or settings are exported.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?><br/><?php printf( __( 'To backup all tables, including their settings, at once use the &quot;%s&quot; button in the &quot;%s&quot;.', WP_TABLE_RELOADED_TEXTDOMAIN ), __( 'Create and Download Dump File', WP_TABLE_RELOADED_TEXTDOMAIN ), __( 'Plugin Options', WP_TABLE_RELOADED_TEXTDOMAIN ) ); ?></p>
        </div>
        <?php if ( 0 < count( $this->tables ) ) { ?>
        <div style="clear:both;">
        <form method="post" action="<?php echo $this->get_action_url(); ?>">
        <?php wp_nonce_field( $this->get_nonce( 'export' ) ); ?>
        <table class="wp-table-reloaded-options">
        <tr>
            <th scope="row"><label for="table_id"><?php _e( 'Select Table to Export', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><select id="table_id" name="table_id">
        <?php
            foreach ( $this->tables as $id => $tableoptionname ) {
                // get name and description to show in list
                $table = $this->load_table( $id );
                    $name = $this->helper->safe_output( $table['name'] );
                    //$description = $this->helper->safe_output( $table['description'] );
                unset( $table );
                $selected = selected( $table_id, $id, false );
                echo "<option{$selected} value='{$id}'>{$name} (ID {$id})</option>";
            }
        ?>
        </select></td>
        </tr>
        <tr>
            <th scope="row"><label for="export_format"><?php _e( 'Select Export Format', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><select id="export_format" name="export_format">
        <?php
            $export_formats = $this->export_instance->export_formats;
            foreach ( $export_formats as $export_format => $longname )
                echo "<option" . ( ( isset( $_POST['export_format'] ) && $export_format == $_POST['export_format'] ) ? ' selected="selected"': '' ) . " value=\"{$export_format}\">{$longname}</option>";
        ?>
        </select></td>
        </tr>
        <tr class="tr-export-delimiter">
            <th scope="row"><label for="delimiter"><?php _e( 'Select Delimiter to use', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><select id="delimiter" name="delimiter">
        <?php
            $delimiters = $this->export_instance->delimiters;
            foreach ( $delimiters as $delimiter => $longname )
                echo "<option" . ( ( isset( $_POST['delimiter'] ) && $delimiter == $_POST['delimiter'] ) ? ' selected="selected"': '' ) . " value=\"{$delimiter}\">{$longname}</option>";
        ?>
        </select> <small>(<?php _e( 'Only needed for CSV export.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>)</small></td>
        </tr>
        <tr>
            <th scope="row"><?php _e( 'Download file', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</th>
            <td><input type="checkbox" name="download_export_file" id="download_export_file" value="true"<?php echo ( isset( $_POST['submit'] ) && !isset( $_POST['download_export_file'] ) ) ? '' : ' checked="checked"'; ?> /> <label for="download_export_file"><?php _e( 'Yes, I want to download the export file.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></label></td>
        </tr>
        </table>
        <input type="hidden" name="action" value="export" />
        <p class="submit">
        <input type="submit" name="submit" class="button-primary" value="<?php _e( 'Export Table', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>" />
        </p>
        <?php if ( isset( $output ) && $output ) { ?>
        <textarea rows="15" cols="40" style="width:600px;height:300px;"><?php echo htmlspecialchars( $output ); ?></textarea>
        <?php } ?>
        </form>
        </div>

        <?php
        } else { // end if $tables
            $add_url = $this->get_action_url( array( 'action' => 'add' ), false );
            $import_url = $this->get_action_url( array( 'action' => 'import' ), false );
            echo "<div style=\"clear:both;\"><p>" . __( 'No tables were found.', WP_TABLE_RELOADED_TEXTDOMAIN ) . '<br/>' . sprintf( __( 'You should <a href="%s">add</a> or <a href="%s">import</a> a table to get started!', WP_TABLE_RELOADED_TEXTDOMAIN ), $add_url, $import_url ) . "</p></div>";
        }
        ?>