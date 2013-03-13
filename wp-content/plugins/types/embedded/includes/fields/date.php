<?php
/*
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * Set date formats
 * TODO Document this
 */
global $supported_date_formats, $supported_date_formats_text;
$supported_date_formats = array('F j, Y', //December 23, 2011
    'Y/m/d', // 2011/12/23
    'm/d/Y', // 12/23/2011
    'd/m/Y' // 23/22/2011
);

$supported_date_formats_text = array('F j, Y' => 'Month dd, yyyy',
    'Y/m/d' => 'yyyy/mm/dd',
    'm/d/Y' => 'mm/dd/yyyy',
    'd/m/Y' => 'dd/mm/yyyy'
);

function wpcf_get_date_format() {
    global $supported_date_formats;

    $date_format = get_option( 'date_format' );
    if ( !in_array( $date_format, $supported_date_formats ) ) {
        // Choose the Month day, Year fromat
        $date_format = 'F j, Y';
    }

    return $date_format;
}

function wpcf_get_date_format_text() {
    global $supported_date_formats, $supported_date_formats_text;

    $date_format = get_option( 'date_format' );
    if ( !in_array( $date_format, $supported_date_formats ) ) {
        // Choose the Month day, Year fromat
        $date_format = 'F j, Y';
    }

    return $supported_date_formats_text[$date_format];
}

/*
 * 
 * 
 * 
 * 
 * Filters
 */
// 
add_filter( 'wpcf_fields_type_date_value_get',
        'wpcf_fields_date_value_get_filter', 10, 4 );

add_filter( 'wpcf_fields_type_date_value_save',
        'wpcf_fields_date_value_save_filter', 10, 4 );

add_filter( 'wpcf_conditional_display_compare_condition_value',
        'wpcf_fields_date_conditional_display_value_filter', 10, 5 );

add_filter( 'wpcf_repetitive_field_old_value',
        'wpcf_repetitive_date_old_value_filter', 10, 4 );

add_action( 'wpcf_fields_type_date_save',
        'wpcf_fields_date_collect_hour_and_minute', 10, 5 );

add_action( 'wpcf_post_field_saved', 'wpcf_fields_date_save_hour_and_minute',
        10, 2 );

add_filter( 'wpcf_fields_type_date_value_display',
        'wpcf_fields_date_get_hour_and_minute_by_meta_key', 10, 5 );

// Enqueue later
add_filter( 'types_field_get_submitted_data', 'wpcf_fields_date_to_time', 15, 2 );


if ( defined( 'DOING_AJAX' ) ) {
    add_filter( 'wpcf_conditional_display_compare_meta_value',
            'wpcf_fields_date_conditional_display_value_filter', 10, 5 );
}
if ( !function_exists( 'wpv_filter_parse_date' ) ) {
    require_once WPCF_EMBEDDED_ABSPATH . '/common/wpv-filter-date-embedded.php';
}

/*
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * Main functions
 */

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_date() {
    return array(
        'id' => 'wpcf-date',
        'title' => __( 'Date', 'wpcf' ),
        'description' => __( 'Date', 'wpcf' ),
        'validate' => array('required', 'date'),
        'meta_box_js' => array(
            'wpcf-jquery-fields-date' => array(
                'src' => WPCF_EMBEDDED_RES_RELPATH . '/js/jquery.ui.datepicker.min.js',
                'deps' => array('jquery-ui-core'),
            ),
            'wpcf-jquery-fields-date-inline' => array(
                'inline' => 'wpcf_fields_date_meta_box_js_inline',
            ),
        ),
        'meta_box_css' => array(
            'wpcf-jquery-fields-date' => array(
                'src' => WPCF_EMBEDDED_RES_RELPATH . '/css/jquery-ui/datepicker.css',
            ),
        ),
        /*
         * 
         * 
         * Since Types 1.2
         * We do not use inherited field type anymore
         */
//        'inherited_field_tydpe' => 'textfield',
        'meta_key_type' => 'TIME',
        'version' => '1.2',
    );
}

/**
 * From data for post edit page.
 * 
 * @param type $field 
 * @param type $data
 * @param type $field_object Field instance 
 */
function wpcf_fields_date_meta_box_form( $field, $field_object = null ) {

    /*
     * 
     * @since Types 1.2
     * 
     * Added extra fields 'hour' and 'minute'.
     * 
     * If value is not array it is assumed that DB entry is timestamp()
     * and data is converted to array.
     */
    $value = $field['value'] = wpcf_fields_date_value_get_filter( $field['value'],
            $field_object );

    // TODO WPML
    if ( isset( $field['wpml_action'] ) && $field['wpml_action'] == 'copy' ) {
        $attributes = array('style' => 'width:150px;');
    } else {
        $attributes = array('class' => 'wpcf-datepicker', 'style' => 'width:150px;');
    }

    /*
     * 
     * Do not forget to trigger datepicker script
     * 
     * Only trigger on AJAX call (inserting new)
     */
    $js_trigger = defined( 'DOING_AJAX' ) ? '<script type="text/javascript">wpcfFieldsDateInit(\'\');</script>' : '';

    /*
     * 
     * 
     * Set Form
     */
    $unique_id = wpcf_unique_id( serialize( $field ) );
    $form = array();
    $form[$unique_id . '-datepicker'] = array(
        '#type' => 'textfield',
        '#title' => '&nbsp;' . $field['name'],
        '#attributes' => $attributes,
        '#name' => 'wpcf[' . $field['slug'] . '][datepicker]',
        '#id' => 'wpcf-date-' . $field['slug'] . '-datepicker-' . $unique_id,
        '#value' => $value['datepicker'],
        '#inline' => true,
        '#after' => '' . $js_trigger, // Append JS trigger
        '#_validate_this' => true, // Important when H and M are used too
    );

    /*
     * 
     * If set 'date_and_time' add time
     */
    if ( !empty( $field['data']['date_and_time'] ) && $field['data']['date_and_time'] == 'and_time' ) {
        $hours = 24;
        $minutes = 60;
        $options = array();

        // Hour
        for ( $index = 0; $index < $hours; $index++ ) {
            $prefix = $index < 10 ? '0' : '';
            $options[$index] = array(
                '#title' => $prefix . strval( $index ),
                '#value' => $index,
            );
        }
        $form[$unique_id . 'time_hour'] = array(
            '#type' => 'select',
            '#title' => __( 'Hour', 'wpcf' ),
            '#inline' => true,
            '#before' => '<br />',
            '#after' => '&nbsp;&nbsp;',
            '#options' => $options,
            '#default_value' => $value['hour'],
            '#name' => 'wpcf[' . $field['slug'] . '][hour]',
            '#id' => 'wpcf-date-' . $field['slug'] . '-select-hour-'
            . $unique_id,
            '#inline' => true,
        );

        // Minutes
        for ( $index = 1; $index < $minutes; $index++ ) {
            $prefix = $index < 10 ? '0' : '';
            $options[$index] = array(
                '#title' => $prefix . strval( $index ),
                '#value' => $index,
            );
        }
        $form[$unique_id . 'time_minute'] = array(
            '#type' => 'select',
            '#title' => __( 'Minute', 'wpcf' ),
            '#after' => '<br /><br />',
            '#inline' => true,
            '#options' => $options,
            '#default_value' => $value['minute'],
            '#name' => 'wpcf[' . $field['slug'] . '][minute]',
            '#id' => 'wpcf-date-' . $field['slug'] . '-minute-'
            . $unique_id,
        );
    }

    return $form;
}

function _wpcf_date_convert_wp_to_js( $date_format ) {
    $date_format = str_replace( 'd', 'dd', $date_format );
    $date_format = str_replace( 'j', 'd', $date_format );
    $date_format = str_replace( 'l', 'DD', $date_format );
    $date_format = str_replace( 'm', 'mm', $date_format );
    $date_format = str_replace( 'n', 'm', $date_format );
    $date_format = str_replace( 'F', 'MM', $date_format );
    $date_format = str_replace( 'Y', 'yy', $date_format );

    return $date_format;
}

/**
 * Renders inline JS.
 */
function wpcf_fields_date_meta_box_js_inline() {

    $date_format = wpcf_get_date_format();
    $date_format = _wpcf_date_convert_wp_to_js( $date_format );

    $date_format_note = '<span style="margin-left:10px"><i>' . esc_js( sprintf( __( 'Input format: %s',
                                    'wpcf' ), wpcf_get_date_format_text() ) ) . '</i></span>';

    ?>
    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready(function(){
            wpcfFieldsDateInit('');
        });
                                                                                                                                                                                                                                                                                                                                                                                                                                                               
        function wpcfFieldsDateInit(div) {
            if (jQuery.isFunction(jQuery.fn.datepicker)) {
                jQuery(div+' .wpcf-datepicker').each(function(index) {
                    if (!jQuery(this).is(':disabled') && !jQuery(this).hasClass('hasDatepicker')) {
                        jQuery(this).datepicker({
                            showOn: "button",
                            buttonImage: "<?php echo WPCF_EMBEDDED_RES_RELPATH; ?>/images/calendar.gif",
                            buttonImageOnly: true,
                            buttonText: "<?php
    _e( 'Select date', 'wpcf' );

    ?>",
                            dateFormat: "<?php echo $date_format; ?>",
                            altFormat: "<?php echo $date_format; ?>",
                            onSelect: function(dateText, inst) {
                                jQuery(this).trigger('wpcfDateBlur');
                            }
                        });
                        jQuery(this).next().after('<?php echo $date_format_note; ?>');
                    }
                });
            }
        }
        function wpcfFieldsDateEditorCallback(field_id) {
            var url = "<?php echo admin_url( 'admin-ajax.php' ); ?>?action=wpcf_ajax&wpcf_action=editor_insert_date&_wpnonce=<?php echo wp_create_nonce( 'fields_insert' ); ?>&field_id="+field_id+"&keepThis=true&TB_iframe=true&width=400&height=400";
            tb_show("<?php
    _e( 'Insert date', 'wpcf' );

    ?>", url);
        }
        //]]>
    </script>
    <?php
}

/**
 * Parses date meta.
 * 
 * @param type $value
 * @param type $field Field data
 * @return type 
 */
function wpcf_fields_date_value_get_filter( $value, $field ) {

    global $wpcf;

    if ( is_array( $value ) ) {
        /*
         * Since Types 1.2
         */
        $value = wp_parse_args( $value,
                array(
            'datepicker' => null,
            'hour' => 8,
            'minute' => 0,
                )
        );
    } else {
        /*
         * We assume it is timestamp from earlier versions
         */
        $value = array(
            'datepicker' => $value,
            'hour' => 8,
            'minute' => 0,
        );
    }

    /*
     * Since Types 1.2 we require $cf field object
     */
    if ( $field instanceof WPCF_Field ) {
        $post = $field->post;
    } else {
        // Remove for moment
        remove_filter( 'wpcf_fields_type_date_value_get',
                'wpcf_fields_date_value_get_filter', 10, 4 );

        // Hide on frontpage where things will go fine because of loop
        if ( is_admin() ) {
            _deprecated_argument( 'date_obsolete_parameter', '1.2',
                    '<br /><br /><div class="wpcf-error">'
                    . 'Since Types 1.2 $cf field object is required' . '</div><br /><br />' );
        }
        /*
         * Set default objects
         */
        $_field = $field;
        $field = new WPCF_Field();
        $field->context = is_admin() ? 'frontend' : 'group';
        $post_id = wpcf_get_post_id( $field->context );
        $post = get_post( $post_id );
        if ( empty( $post ) ) {
            return $value;
        }
        $field->set( $post, $_field );

        // Back to filter
        add_filter( 'wpcf_fields_type_date_value_get',
                'wpcf_fields_date_value_get_filter', 10, 4 );
    }

    /*
     * 
     * Get hour and minute
     * CHECKPOINT
     * 
     * We need meta_id here.
     */
    if ( !empty( $post->ID ) ) {
        $_meta_id = isset( $_field['__meta_id'] ) ? $_field['__meta_id'] : $field->meta_object->meta_id;
        $_hm = get_post_meta( $post->ID,
                '_wpcf_' . $field->cf['id']
                . '_hour_and_minute', true );
        $hm = isset( $_hm[$_meta_id] ) ? $_hm[$_meta_id] : array();
    } else {
        /*
         * If $post is not set.
         * We need to record this
         */
        $wpcf->errors['missing_post'][] = func_get_args();
    }

    /*
     * Setup hour and minute.
     */
    if ( !empty( $hm ) && is_array( $hm )
            && (isset( $hm['hour'] ) && isset( $hm['minute'] ) ) ) {
        $value['hour'] = $hm['hour'];
        $value['minute'] = $hm['minute'];
    }

    // Calculate time
    $value['timestamp'] = wpcf_fields_date_calculate_time( $value );

    // Set datepicker to use formatted date
    if ( !empty( $value['datepicker'] ) ) {
        // Test if already formatted
        if ( strtotime( $value['datepicker'] ) === false ) {
            $value['datepicker'] = date( wpcf_get_date_format(),
                    intval( $value['datepicker'] ) );
        }
    }

    return $value;
}

/**
 * Calculate time
 * @param type $value 
 */
function wpcf_fields_date_calculate_time( $value ) {

    extract( $value );
    $timestamp = strtotime( strval( $datepicker ) );
    /*
     * 
     * TODO What if fails
     * temprary set to return NOW  - time()
     */
    if ( !$timestamp ) {
        // try if it is timestamp
        $timestamp = intval( $datepicker );
    }
    // TODO check if $datepicker hour is set to 00:00
    // fix hour and minute
    if ( strval( $hour ) == '00' ) {
        $hour = 0;
    }
    if ( strval( $minute ) == '00' ) {
        $minute = 0;
    }

    $timestamp = intval( $timestamp ) + (60 * 60 * intval( $hour )) + (60 * intval( $minute ));

    return $timestamp;
}

/**
 * Converts date to time on post saving.
 * 
 * @param type $value
 * @return type 
 */
function wpcf_fields_date_value_save_filter( $value ) {

    global $wpcf;

    if ( empty( $value ) ) {
        return $value;
    }

//    $date_format = wpcf_get_date_format();
//    if ($date_format == 'd/m/Y') {
//        // strtotime requires a dash or dot separator to determine dd/mm/yyyy format
//        $value = str_replace('/', '-', $value);
//    }
//    return strtotime(strval($value));
    /*
     * 
     * TODO Review
     * @since Types 1.2
     * 
     * Now we save timestamp, hour and minute
     * This is place where we ensure that right data is saved.
     * 
     */

    $data = wpcf_fields_date_set_hour_and_minute( $value );

    // Return timestamp
    return $data['datepicker'];
}

/**
 *
 * Convert a format from date() to strftime() format
 *
 */
function wpcf_date_to_strftime( $format ) {

    $format = str_replace( 'd', '%d', $format );
    $format = str_replace( 'D', '%a', $format );
    $format = str_replace( 'j', '%e', $format );
    $format = str_replace( 'l', '%A', $format );
    $format = str_replace( 'N', '%u', $format );
    $format = str_replace( 'w', '%w', $format );

    $format = str_replace( 'W', '%W', $format );

    $format = str_replace( 'F', '%B', $format );
    $format = str_replace( 'm', '%m', $format );
    $format = str_replace( 'M', '%b', $format );
    $format = str_replace( 'n', '%m', $format );

    $format = str_replace( 'o', '%g', $format );
    $format = str_replace( 'Y', '%Y', $format );
    $format = str_replace( 'y', '%y', $format );

    return $format;
}

/**
 * View function.
 * 
 * @param type $params 
 */
function wpcf_fields_date_view( $params ) {

    global $wp_locale;

    // Append hour and minute if necessary
    $meta = wpcf_fields_date_value_get_filter( $params['field_value'],
            $params['field'] );
    $params['field_value'] = intval( $meta['timestamp'] );


    $defaults = array(
        'format' => get_option( 'date_format' ),
        'style' => '' // add default value
    );
    $params = wp_parse_args( $params, $defaults );
    $output = '';

    switch ( $params['style'] ) {
        case 'calendar':
            $output .= wpcf_fields_date_get_calendar( $params, true, false );
            break;

        default:
            $field_name = '';


            // Extract the Full month and Short month from the format.
            // We'll replace with the translated months if possible.
            $format = $params['format'];
            $format = str_replace( 'F', '#111111#', $format );
            $format = str_replace( 'M', '#222222#', $format );

            // Same for the Days
            $format = str_replace( 'D', '#333333#', $format );
            $format = str_replace( 'l', '#444444#', $format );

            $date_out = date( $format, intval( $params['field_value'] ) );

            $month = date( 'm', intval( $params['field_value'] ) );
            $month_full = $wp_locale->get_month( $month );
            $date_out = str_replace( '#111111#', $month_full, $date_out );
            $month_short = $wp_locale->get_month_abbrev( $month_full );
            $date_out = str_replace( '#222222#', $month_short, $date_out );

            $day = date( 'w', intval( $params['field_value'] ) );
            $day_full = $wp_locale->get_weekday( $day );
            $date_out = str_replace( '#444444#', $day_full, $date_out );
            $day_short = $wp_locale->get_weekday_abbrev( $day_full );
            $date_out = str_replace( '#333333#', $day_short, $date_out );

            $output = $date_out;
            break;
    }

    return $output;
}

/**
 * Calendar view.
 * 
 * @global type $wpdb
 * @global type $m
 * @global type $wp_locale
 * @global type $posts
 * @param type $params
 * @param type $initial
 * @param type $echo
 * @return type 
 */
function wpcf_fields_date_get_calendar( $params, $initial = true, $echo = true ) {

    global $wpdb, $m, $wp_locale, $posts;

    // wpcf Set our own date
    $monthnum = date( 'n', $params['field_value'] );
    $year = date( 'Y', $params['field_value'] );
    $wpcf_date = date( 'j', $params['field_value'] );

    $cache = array();
    $key = md5( $params['field']['slug'] . $wpcf_date );
    if ( $cache = wp_cache_get( 'get_calendar', 'calendar' ) ) {
        if ( is_array( $cache ) && isset( $cache[$key] ) ) {
            if ( $echo ) {
                echo apply_filters( 'get_calendar', $cache[$key] );
                return;
            } else {
                return apply_filters( 'get_calendar', $cache[$key] );
            }
        }
    }

    if ( !is_array( $cache ) )
        $cache = array();

    if ( isset( $_GET['w'] ) )
        $w = '' . intval( $_GET['w'] );

    // week_begins = 0 stands for Sunday
    $week_begins = intval( get_option( 'start_of_week' ) );

    // Let's figure out when we are
    if ( !empty( $monthnum ) && !empty( $year ) ) {
        $thismonth = '' . zeroise( intval( $monthnum ), 2 );
        $thisyear = '' . intval( $year );
    } elseif ( !empty( $w ) ) {
        // We need to get the month from MySQL
        $thisyear = '' . intval( substr( $m, 0, 4 ) );
        $d = (($w - 1) * 7) + 6; //it seems MySQL's weeks disagree with PHP's
        $thismonth = $wpdb->get_var( "SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')" );
    } elseif ( !empty( $m ) ) {
        $thisyear = '' . intval( substr( $m, 0, 4 ) );
        if ( strlen( $m ) < 6 )
            $thismonth = '01';
        else
            $thismonth = '' . zeroise( intval( substr( $m, 4, 2 ) ), 2 );
    } else {
        $thisyear = gmdate( 'Y', current_time( 'timestamp' ) );
        $thismonth = gmdate( 'm', current_time( 'timestamp' ) );
    }

    $unixmonth = mktime( 0, 0, 0, $thismonth, 1, $thisyear );
    $last_day = date( 't', $unixmonth );

    /* translators: Calendar caption: 1: month name, 2: 4-digit year */
    $calendar_caption = _x( '%1$s %2$s', 'calendar caption' );
    $calendar_output = '<table id="wp-calendar" summary="' . esc_attr__( 'Calendar' ) . '">
	<caption>' . sprintf( $calendar_caption,
                    $wp_locale->get_month( $thismonth ), date( 'Y', $unixmonth ) ) . '</caption>
	<thead>
	<tr>';

    $myweek = array();

    for ( $wdcount = 0; $wdcount <= 6; $wdcount++ ) {
        $myweek[] = $wp_locale->get_weekday( ($wdcount + $week_begins) % 7 );
    }

    foreach ( $myweek as $wd ) {
        $day_name = (true == $initial) ? $wp_locale->get_weekday_initial( $wd ) : $wp_locale->get_weekday_abbrev( $wd );
        $wd = esc_attr( $wd );
        $calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
    }

    $calendar_output .= '
	</tr>
	</thead>

	<tfoot>
	<tr>';

    $calendar_output .= '
	</tr>
	</tfoot>

	<tbody>
	<tr>';

    // See how much we should pad in the beginning
    $pad = calendar_week_mod( date( 'w', $unixmonth ) - $week_begins );
    if ( 0 != $pad )
        $calendar_output .= "\n\t\t" . '<td colspan="' . esc_attr( $pad ) . '" class="pad">&nbsp;</td>';

    $daysinmonth = intval( date( 't', $unixmonth ) );
    for ( $day = 1; $day <= $daysinmonth; ++$day ) {
        if ( isset( $newrow ) && $newrow )
            $calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
        $newrow = false;

        if ( $day == gmdate( 'j', current_time( 'timestamp' ) ) && $thismonth == gmdate( 'm',
                        current_time( 'timestamp' ) ) && $thisyear == gmdate( 'Y',
                        current_time( 'timestamp' ) ) )
            $calendar_output .= '<td id="today">';
        else
            $calendar_output .= '<td>';

        // wpcf
        if ( $wpcf_date == $day ) {
            $calendar_output .= '<a href="javascript:void(0);">' . $day . '</a>';
        } else {
            $calendar_output .= $day;
        }

        $calendar_output .= '</td>';

        if ( 6 == calendar_week_mod( date( 'w',
                                mktime( 0, 0, 0, $thismonth, $day, $thisyear ) ) - $week_begins ) )
            $newrow = true;
    }

    $pad = 7 - calendar_week_mod( date( 'w',
                            mktime( 0, 0, 0, $thismonth, $day, $thisyear ) ) - $week_begins );
    if ( $pad != 0 && $pad != 7 )
        $calendar_output .= "\n\t\t" . '<td class="pad" colspan="' . esc_attr( $pad ) . '">&nbsp;</td>';

    $calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

    $cache[$key] = $calendar_output;
    wp_cache_set( 'get_calendar', $cache, 'calendar' );

    if ( $echo )
        echo apply_filters( 'get_calendar', $calendar_output );
    else
        return apply_filters( 'get_calendar', $calendar_output );
}

/**
 * TinyMCE editor form.
 */
function wpcf_fields_date_editor_callback() {
    $last_settings = wpcf_admin_fields_get_field_last_settings( $_GET['field_id'] );
    wp_enqueue_script( 'jquery' );
    $form = array();
    $form['#form']['callback'] = 'wpcf_fields_date_editor_form_submit';
    $form['style'] = array(
        '#type' => 'radios',
        '#name' => 'wpcf[style]',
        '#options' => array(
            __( 'Show as calendar', 'wpcf' ) => 'calendar',
            __( 'Show as text', 'wpcf' ) => 'text',
        ),
        '#default_value' => isset( $last_settings['style'] ) ? $last_settings['style'] : 'text',
        '#after' => '<br />',
    );
    $date_formats = apply_filters( 'date_formats',
            array(
        __( 'F j, Y' ),
        'Y/m/d',
        'm/d/Y',
        'd/m/Y',
            )
    );
    $options = array();
    foreach ( $date_formats as $format ) {
        $title = date( $format, time() );
        $field['#title'] = $title;
        $field['#value'] = $format;
        $options[] = $field;
    }
    $custom_format = isset( $last_settings['format-custom'] ) ? $last_settings['format-custom'] : get_option( 'date_format' );
    $options[] = array(
        '#title' => __( 'Custom', 'wpcf' ),
        '#value' => 'custom',
        '#suffix' => wpcf_form_simple( array('custom' => array(
                '#name' => 'wpcf[format-custom]',
                '#type' => 'textfield',
                '#value' => $custom_format,
                '#suffix' => '&nbsp;' . date( $custom_format, time() ),
                '#inline' => true,
                ))
        ),
    );
    $form['toggle-open'] = array(
        '#type' => 'markup',
        '#markup' => '<div id="wpcf-toggle" style="display:none;">',
    );
    $form['format'] = array(
        '#type' => 'radios',
        '#name' => 'wpcf[format]',
        '#options' => $options,
        '#default_value' => isset( $last_settings['format'] ) ? $last_settings['format'] : get_option( 'date_format' ),
        '#after' => '<a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank">'
        . __( 'Documentation on date and time formatting', 'wpcf' ) . '</a>',
    );
    $form['toggle-close'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );
    $form['field_id'] = array(
        '#type' => 'hidden',
        '#name' => 'wpcf[field_id]',
        '#value' => $_GET['field_id'],
    );
    $form['submit'] = array(
        '#type' => 'submit',
        '#name' => 'submit',
        '#value' => __( 'Insert date', 'wpcf' ),
        '#attributes' => array('class' => 'button-primary'),
    );
    $f = wpcf_form( 'wpcf-fields-date-editor', $form );
    add_action( 'admin_head_wpcf_ajax', 'wpcf_fields_date_editor_form_script' );
    wpcf_admin_ajax_head( __( 'Insert date', 'wpcf' ) );
    echo '<form id="wpcf-form" method="post" action="">';
    echo $f->renderForm();
    echo '</form>';
    wpcf_admin_ajax_footer();
}

/**
 * AJAX window JS.
 */
function wpcf_fields_date_editor_form_script() {

    ?>
    <script type="text/javascript">
        // <![CDATA[
        jQuery(document).ready(function(){
            jQuery('input[name|="wpcf[style]"]').change(function(){
                if (jQuery(this).val() == 'text') {
                    jQuery('#wpcf-toggle').slideDown();
                } else {
                    jQuery('#wpcf-toggle').slideUp();
                }
            });
            if (jQuery('input[name="wpcf[style]"]:checked').val() == 'text') {
                jQuery('#wpcf-toggle').show();
            }
        });
        // ]]>
    </script>
    <?php
}

/**
 * Inserts shortcode in editor.
 * 
 * @return type 
 */
function wpcf_fields_date_editor_form_submit() {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    if ( !isset( $_POST['wpcf']['field_id'] ) ) {
        return false;
    }
    $field = wpcf_admin_fields_get_field( $_POST['wpcf']['field_id'] );
    if ( empty( $field ) ) {
        return false;
    }
    $add = ' ';
    $style = isset( $_POST['wpcf']['style'] ) ? $_POST['wpcf']['style'] : 'text';
    $add .= 'style="' . $style . '"';
    $format = '';
    if ( $style == 'text' ) {
        if ( $_POST['wpcf']['format'] == 'custom' ) {
            $format = $_POST['wpcf']['format-custom'];
        } else {
            $format = $_POST['wpcf']['format'];
        }
        if ( empty( $format ) ) {
            $format = get_option( 'date_format' );
        }
        $add .= ' format="' . $format . '"';
    }
    $shortcode = wpcf_fields_get_shortcode( $field, $add );
    wpcf_admin_fields_save_field_last_settings( $_POST['wpcf']['field_id'],
            array(
        'style' => $style,
        'format' => $_POST['wpcf']['format'],
        'format-custom' => $_POST['wpcf']['format-custom'],
            )
    );
    echo editor_admin_popup_insert_shortcode_js( $shortcode );
    die();
}

/**
 * Filters conditional display value.
 * 
 * @param type $value
 * @param type $field
 * @param type $operation
 * @param type $conditional_field
 * @param type $post
 * @return type 
 */
function wpcf_fields_date_conditional_display_value_filter( $value, $field,
        $operation, $field_compared, $post ) {
    $field = wpcf_admin_fields_get_field( $field );
    if ( !empty( $field ) && $field['type'] == 'date' ) {
        $value = wpcf_fields_date_value_get_filter( $value, $field );
        $time = intval( $value['timestamp'] );

        // TODO Revise this
//        $time = strtotime($value);
        if ( $time ) {
            return $time;
        } else {
            // Check dates
            $value = wpv_filter_parse_date( $value );
        }
    }
    return $value;
}

/**
 * Set repetitive old value back to time.
 * 
 * @param type $value
 * @param type $field
 * @param type $post
 * @param type $element
 * @return type 
 */
function wpcf_repetitive_date_old_value_filter( $value, $field, $post, $element ) {
    if ( $field['type'] == 'date' ) {
        $value = wpcf_fields_date_value_get_filter( $value, $field );
        $time = intval( $value['timestamp'] );

        // TODO Revise this
//        $time = strtotime($value);
        if ( $time ) {
            return $time;
        }
    }
    return $value;
}

/**
 * Collects after meta is created.
 * 
 * We'll be holding Hour and Minute in separate meta.
 * 
 * @param type $value
 * @param type $field
 * @param type $this
 * @param type $meta_id
 */
function wpcf_fields_date_collect_hour_and_minute( $value, $field,
        $field_object, $meta_id, $meta_value_original ) {
    global $wpcf;

    $wpcf->field->__date->additional_meta[$field['id']][$meta_id] = $meta_value_original;
}

/**
 * Saves Hour and Minute after meta is created.
 * 
 * We'll be holding Hour and Minute in separate meta.
 * 
 * @param type $value
 * @param type $field
 * @param type $this
 * @param type $meta_id
 */
function wpcf_fields_date_save_hour_and_minute( $post_id, $field ) {
    global $wpcf;

    if ( !empty( $wpcf->field->__date->additional_meta[$field['id']] ) ) {
        update_post_meta(
                $post_id, '_wpcf_' . $field['id'] . '_hour_and_minute',
                $wpcf->field->__date->additional_meta[$field['id']] );
    }
}

/**
 * Sets data for Hour and Minute.
 * 
 * @param type $value
 * @param type $date_format
 * @return int
 */
function wpcf_fields_date_set_hour_and_minute( $value ) {
    $date_format = wpcf_get_date_format();
    $data = array();
    if ( is_array( $value ) ) {
        if ( $date_format == 'd/m/Y' ) {
            // strtotime requires a dash or dot separator to determine dd/mm/yyyy format
            $value['datepicker'] = str_replace( '/', '-',
                    strval( $value['datepicker'] ) );
        }
        $data['datepicker'] = strtotime( $value['datepicker'] );
        $data['hour'] = isset( $value['hour'] ) ? intval( $value['hour'] ) : 8;
        $data['minute'] = isset( $value['minute'] ) ? intval( $value['minute'] ) : 0;
    } else {
        if ( $date_format == 'd/m/Y' ) {
            // strtotime requires a dash or dot separator to determine dd/mm/yyyy format
            $value = str_replace( '/', '-', strval( $value ) );
        }
        // Check if date string
        $_v = strtotime( $value );
        $data['datepicker'] = $_v == false || $_v == -1 ? $value : $_v;
        $data['hour'] = 8;
        $data['minute'] = 0;
    }

    return $data;
}

function wpcf_fields_date_get_hour_and_minute( $post_id, $field_id ) {
    return get_post_meta(
                    $post_id, '_wpcf_' . $field_id . '_hour_and_minute', true );
}

/**
 * 
 * @param type $meta_value
 * @param type $params
 * @param type $post_id
 * @param type $field_id
 * @param type $meta_id
 * @return type
 */
function wpcf_fields_date_get_hour_and_minute_by_meta_key( $meta_value, $params,
        $post_id, $field_id, $meta_id ) {
    $meta = get_post_meta(
            $post_id, '_wpcf_' . $field_id . '_hour_and_minute', true );
    return isset( $meta[$meta_id] ) ? $meta[$meta_id] : $meta_value;
}

/**
 * String to time.
 * 
 * @param type $posted
 * @param type $field
 * @return type
 */
function wpcf_fields_date_to_time( $str, $field ) {
    if ( $field->cf['type'] == 'date' ) {
        $time = strtotime( strval( $str ) );
        if ( $time ) {
            return $time;
        }
    }
    return $str;
}