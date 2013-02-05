<?php if ( !defined( 'WP_TABLE_RELOADED_ABSPATH' ) ) exit; // no direct loading of this file ?>
        <div style="clear:both;">
        <p><?php _e( 'WP-Table Reloaded can import tables from existing data.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?> <?php _e( 'This may be a CSV, XML or HTML file, each with a certain structure.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?><br/><br/><?php _e( 'To import an existing table, please select its format and the source for the import.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?> <?php if ( 0 < count( $this->tables ) ) _e( 'You can also decide, if you want to import it as a new table or replace an existing table.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></p>
        </div>
        <div style="clear:both;">
        <form method="post" enctype="multipart/form-data" action="<?php echo $this->get_action_url(); ?>">
        <?php wp_nonce_field( $this->get_nonce( 'import' ) ); ?>
        <table class="wp-table-reloaded-options">
        <tr>
            <th scope="row"><label for="import_format"><?php _e( 'Select Import Format', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><select id="import_format" name="import_format">
        <?php
            $import_formats = $this->import_instance->import_formats;
            foreach ( $import_formats as $import_format => $longname )
                echo "<option" . ( isset( $_POST['import_format'] ) && ( $import_format == $_POST['import_format'] ) ? ' selected="selected"': '' ) . " value=\"{$import_format}\">{$longname}</option>\n";
        ?>
        </select></td>
        </tr>
        <?php if ( 0 < count( $this->tables ) ) { ?>
        <tr class="tr-import-addreplace">
            <th scope="row"><?php _e( 'Add or Replace Table?', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</th>
            <td>
            <input name="import_addreplace" id="import_addreplace_add" type="radio" value="add" <?php echo ( isset( $_POST['import_addreplace'] ) && 'add' != $_POST['import_addreplace'] ) ? '' : 'checked="checked" ' ; ?>/> <label for="import_addreplace_add"><?php _e( 'Add as new Table', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></label>
            <input name="import_addreplace" id="import_addreplace_replace" type="radio" value="replace" <?php echo ( isset( $_POST['import_addreplace'] ) && 'replace' == $_POST['import_addreplace'] ) ? 'checked="checked" ': '' ; ?>/> <label for="import_addreplace_replace"><?php _e( 'Replace existing Table', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></label>
            </td>
        </tr>
        <tr class="tr-import-addreplace-table">
            <th scope="row"><label for="import_addreplace_table"><?php _e( 'Select existing Table to Replace', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><select id="import_addreplace_table" name="import_addreplace_table">
            <option value="-1">&nbsp;</option>
        <?php
            foreach ( $this->tables as $id => $tableoptionname ) {
                // get name and description to show in list
                $table = $this->load_table( $id );
                    $name = $this->helper->safe_output( $table['name'] );
                    //$description = $this->helper->safe_output( $table['description'] );
                unset( $table );
                echo "<option" . ( isset( $_POST['import_addreplace_table'] ) && ( $id == $_POST['import_addreplace_table'] ) ? ' selected="selected"': '' ) . " value=\"{$id}\">{$name} (ID {$id})</option>";
            }
        ?>
        </select></td>
        </tr>
        <?php } ?>
        <tr class="tr-import-from">
            <th scope="row"><?php _e( 'Select source for Import', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</th>
            <td>
            <input name="import_from" id="import_from_file" type="radio" value="file-upload" <?php echo ( isset( $_POST['import_from'] ) && 'file-upload' != $_POST['import_from'] ) ? '' : 'checked="checked" ' ; ?>/> <label for="import_from_file"><?php _e( 'File upload', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></label>
            <input name="import_from" id="import_from_url" type="radio" value="url" <?php echo ( isset( $_POST['import_from'] ) && 'url' == $_POST['import_from'] ) ? 'checked="checked" ': '' ; ?>/> <label for="import_from_url"><?php _e( 'URL', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></label>
            <input name="import_from" id="import_from_field" type="radio" value="form-field" <?php echo ( isset( $_POST['import_from'] ) && 'form-field' == $_POST['import_from'] ) ? 'checked="checked" ': '' ; ?>/> <label for="import_from_field"><?php _e( 'Manual input', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></label>
            <input name="import_from" id="import_from_server" type="radio" value="server" <?php echo ( isset( $_POST['import_from'] ) && 'server' == $_POST['import_from'] ) ? 'checked="checked" ': '' ; ?>/> <label for="import_from_server"><?php _e( 'File on server', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></label>
            </td>
        </tr>
        <tr class="tr-import-file-upload">
            <th scope="row"><label for="import_file"><?php _e( 'Select File with Table to Import', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><input name="import_file" id="import_file" type="file" /></td>
        </tr>
        <tr class="tr-import-url">
            <th scope="row"><label for="import_url"><?php _e( 'URL to Import Table from', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><input type="text" name="import_url" id="import_url" style="width:400px;" value="<?php echo ( isset( $_POST['import_url'] ) ) ? $_POST['import_url'] : 'http://' ; ?>" /></td>
        </tr>
        <tr class="tr-import-server">
            <th scope="row"><label for="import_server"><?php _e( 'Path to file on server', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><input type="text" name="import_server" id="import_server" style="width:400px;" value="<?php echo ( isset( $_POST['import_server'] ) ) ? $_POST['import_server'] : '' ; ?>" /></td>
        </tr>
        <tr class="tr-import-form-field">
            <th scope="row" style="vertical-align:top;"><label for="import_data"><?php _e( 'Paste data with Table to Import', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><textarea  name="import_data" id="import_data" rows="15" cols="40" style="width:600px;height:300px;"></textarea></td>
        </tr>
        </table>
        <input type="hidden" name="action" value="import" />
        <p class="submit">
        <input type="submit" name="submit" class="button-primary" value="<?php _e( 'Import Table', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>" />
        </p>
        </form>
        </div>

        <?php // check if plugin is installed at all / if tables in db exist
        global $wpdb;
        $wpdb->golftable  = $wpdb->prefix . 'golftable';
        $wpdb->golfresult = $wpdb->prefix . 'golfresult';

        if ( $wpdb->golftable == $wpdb->get_var( "show tables like '{$wpdb->golftable}'" ) && $wpdb->golfresult == $wpdb->get_var( "show tables like '{$wpdb->golfresult}'" ) ) {
        // wp-Table tables exist -> the plugin might be installed, so we output all found tables

        ?>
        <h2><?php _e( 'Import from original wp-Table plugin', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></h2>
        <div style="clear:both;">
        <?php
        $tables = $wpdb->get_results("SELECT * FROM $wpdb->golftable ORDER BY 'table_aid' ASC ");
        if ( 0 < count( $tables ) ) {
            // Tables found in db
        ?>
            <form method="post" action="<?php echo $this->get_action_url(); ?>">
            <?php wp_nonce_field( $this->get_nonce( 'bulk_edit' ) ); ?>
            <table class="widefat">
            <thead>
                <tr>
                    <th class="check-column" scope="col"><input type="checkbox" /></th>
                    <th scope="col"><?php _e( 'ID', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Table Name', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Description', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Action', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="check-column" scope="col"><input type="checkbox" /></th>
                    <th scope="col"><?php _e( 'ID', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Table Name', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Description', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Action', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                </tr>
            </tfoot>
            <?php
            echo "<tbody>\n";
            $bg_style_index = 0;
            foreach ( $tables as $table ) {
                $bg_style_index++;
                $bg_style = ( 1 == ($bg_style_index % 2) ) ? ' class="alternate"' : '';

                $table_id = $table->table_aid;
                $name = $table->table_name;
                $description = $table->description;

                $import_url = $this->get_action_url( array( 'action' => 'import', 'import_format' => 'wp_table', 'wp_table_id' => $table_id ), true );

                echo "<tr{$bg_style}>\n";
                echo "\t<th class=\"check-column\" scope=\"row\"><input type=\"checkbox\" name=\"tables[]\" value=\"{$table_id}\" /></th>";
                echo "<th scope=\"row\">{$table_id}</th>";
                echo "<td>{$name}</td>";
                echo "<td>{$description}</td>";
                echo "<td><a class=\"import_wptable_link\" href=\"{$import_url}\">" . __( 'Import', WP_TABLE_RELOADED_TEXTDOMAIN ) . "</a></td>\n";
                echo "</tr>\n";

            }
            echo "</tbody>\n";
            echo "</table>\n";
        ?>
        <input type="hidden" name="action" value="bulk_edit" />
        <p class="submit"><?php _e( 'Bulk actions:', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>  <input type="submit" name="submit[wp_table_import]" class="button-primary bulk_wp_table_import_tables" value="<?php _e( 'Import Tables', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>" />
        </p>

        </form>
        <?php
        } else { // end if $tables
            echo "<div style=\"clear:both;\"><p>" . __( 'wp-Table by Alex Rabe seems to be installed, but no tables were found.', WP_TABLE_RELOADED_TEXTDOMAIN ) . "</p></div>";
        }
            ?>
        </div>
        <?php
        } else {
            // at least one of the wp-Table tables was *not* found in database, so nothing to show here
        }
        ?>