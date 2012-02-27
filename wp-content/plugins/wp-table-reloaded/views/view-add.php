<?php if ( !defined( 'WP_TABLE_RELOADED_ABSPATH' ) ) exit; // no direct loading of this file ?>

        <div style="clear:both;">
        <p><?php _e( 'To add a new table, enter its name, a description (optional) and the number of rows and columns.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?><br/><?php _e( 'You may also add, insert or delete rows and columns later.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></p>
        </div>

		<div style="clear:both;">
        <form method="post" action="<?php echo $this->get_action_url(); ?>">
        <?php wp_nonce_field( $this->get_nonce( 'add' ) ); ?>

        <table class="wp-table-reloaded-options wp-table-reloaded-newtable">
        <tr>
            <th scope="row"><label for="table_name"><?php _e( 'Table Name', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><input type="text" name="table[name]" id="table_name" class="focus-blur-change" value="<?php _e( 'Enter Table Name', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>" style="width:100%;" title="<?php _e( 'Enter Table Name', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>" /></td>
        </tr>
        <tr>
            <th scope="row"><label for="table_description"><?php _e( 'Description', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><textarea name="table[description]" id="table_description" class="focus-blur-change" rows="15" cols="40" style="width:100%;height:85px;" title="<?php _e( 'Enter Description', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>"><?php _e( 'Enter Description', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></textarea></td>
        </tr>
        <tr>
            <th scope="row"><label for="table_rows"><?php _e( 'Number of Rows', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><input type="text" name="table[rows]" id="table_rows" value="5" /></td>
        </tr>
        <tr>
            <th scope="row"><label for="table_cols"><?php _e( 'Number of Columns', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>:</label></th>
            <td><input type="text" name="table[cols]" id="table_cols" value="5" /></td>
        </tr>
        </table>

        <input type="hidden" name="action" value="add" />
        <p class="submit">
        <input type="submit" name="submit" class="button-primary" value="<?php _e( 'Add Table', WP_TABLE_RELOADED_TEXTDOMAIN ); ?>" />
        </p>

        </form>
        </div>