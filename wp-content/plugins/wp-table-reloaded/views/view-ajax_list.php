<?php if ( !defined( 'WP_TABLE_RELOADED_ABSPATH' ) ) exit; // no direct loading of this file ?>
        <div style="clear:both;"><p>
        <?php _e( 'This is a list of all available tables.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?> <?php _e( 'You may insert a table into a post or page here.', WP_TABLE_RELOADED_TEXTDOMAIN ); ?><br />
		<?php printf( __( 'Click the &quot;%s&quot; link after the desired table and the corresponding shortcode will be inserted into the editor (<strong>[table id=&lt;ID&gt; /]</strong>).', WP_TABLE_RELOADED_TEXTDOMAIN ), __( 'Insert', WP_TABLE_RELOADED_TEXTDOMAIN ) ); ?>
        </p></div>
		<?php
        if ( 0 < count( $this->tables ) ) {
            ?>
        <div style="clear:both;">
            <table class="widefat">
            <thead>
                <tr>
                    <th scope="col"><?php _e( 'ID', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Table Name', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Description', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Action', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th scope="col"><?php _e( 'ID', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Table Name', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Description', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
                    <th scope="col"><?php _e( 'Action', WP_TABLE_RELOADED_TEXTDOMAIN ); ?></th>
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
                    $name = $this->helper->safe_output( $table['name'] );
                    $description = $this->helper->safe_output( $table['description'] );
                unset( $table );

                echo "<tr{$bg_style}>\n";
                echo "\t<th scope=\"row\">{$id}</th>";
                echo "<td style=\"vertical-align:inherit;\">{$name}</td>";
                echo "<td style=\"vertical-align:inherit;\">{$description}</td>";
                echo "<td style=\"vertical-align:inherit;\"><a class=\"send_table_to_editor\" title=\"{$id}\" href=\"#\" style=\"color:#21759B;\">" . __( 'Insert', WP_TABLE_RELOADED_TEXTDOMAIN ) . "</a></td>\n";
                echo "</tr>\n";
            }
            ?>
           </tbody>
           </table>
        </div>
        <?php
        } else { // end if $tables
            echo "<div style=\"clear:both;\"><p>" . __( 'No tables were found.', WP_TABLE_RELOADED_TEXTDOMAIN ) . "</p></div>";
        }
        ?>