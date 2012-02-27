<?php
if (!class_exists('Editor_addon')) {

    if (!defined('ICL_COMMON_FUNCTIONS')) {
        require_once dirname(dirname(__FILE__)) . '/functions.php';
    }

    define('EDITOR_ADDON_ABSPATH', dirname(__FILE__));
    if (!defined('EDITOR_ADDON_RELPATH')) {
        define('EDITOR_ADDON_RELPATH', icl_get_file_relpath(__FILE__));
    }
    add_action('admin_print_styles', 'add_menu_css');

    function add_menu_css() {
        global $pagenow;

        if ($pagenow == 'post.php' || $pagenow == 'post-new.php') {
            //@todo REMOVE code and associated files
//            wp_enqueue_script('editor_addon_menu_mousewheel',
//                    EDITOR_ADDON_RELPATH . '/res/js/mousewheel.js',
//                    array('jquery'));
//            wp_enqueue_script('editor_addon_menu_mousewheelintent',
//                    EDITOR_ADDON_RELPATH . '/res/js/mwheelIntent.js',
//                    array('editor_addon_menu_mousewheel'));
//            wp_enqueue_script('editor_addon_menu_scrollbar',
//                    EDITOR_ADDON_RELPATH . '/res/js/scrollbar.js',
//                    array('editor_addon_menu_mousewheelintent'));
            
            wp_enqueue_style('editor_addon_menu',
                    EDITOR_ADDON_RELPATH . '/res/css/pro_dropdown_2.css');
            wp_enqueue_style('editor_addon_menu_scroll',
                    EDITOR_ADDON_RELPATH . '/res/css/scroll.css');
        }
    }

    if (is_admin()) {
        add_action('admin_print_scripts', 'editor_add_js');
    }

    class Editor_addon
    {

        function __construct($name, $button_text, $plugin_js_url,
                $media_button_image = '') {

            $this->name = $name;
            $this->plugin_js_url = $plugin_js_url;
            $this->button_text = $button_text;
            $this->media_button_image = $media_button_image;
            $this->initialized = false;

            $this->items = array();

            if ($media_button_image != '') {
                // Media buttons
                //Adding "embed form" button
                // WP 3.3 changes
                global $wp_version;
                if (version_compare($wp_version, '3.2.1', '>')) {
                    add_action('media_buttons', array($this, 'add_form_button'),
                            10, 2);
                } else {
                    add_action('media_buttons_context',
                            array($this, 'add_form_button'), 10, 2);
                }
            }

//            add_action('media_buttons', array($this, 'media_buttons'), 11);
//            wp_enqueue_style('editor_addon', plugins_url() . '/' . basename(dirname(dirname(dirname(__FILE__)))) . '/common/' . basename(dirname(__FILE__)) . '/res/css/style.css');
        }

        function __destruct() {
            
        }

        /*

          Add a menu item that will insert the shortcode.

          To use sub menus, add a '-!-' separator between levels in
          the $menu parameter.
          eg.  Field-!-image
          This will create/use a menu "Field" and add a sub menu "image"

          $function_name is the javascript function to call for the on-click
          If it's left blank then a function will be created that just
          inserts the shortcode.

         */

        function add_insert_shortcode_menu($text, $shortcode, $menu,
                $function_name = '') {
            $this->items[] = array($text, $shortcode, $menu, $function_name);
        }

        function add_form_button($context, $text_area = 'textarea#content') {
            global $wp_version;
            // WP 3.3 changes ($context arg is actually a editor ID now)
            if (version_compare($wp_version, '3.2.1', '>') && !empty($context)) {
                $text_area = $context;
            }
            // Apply filters
            $this->items = apply_filters('editor_addon_items_' . $this->name,
                    $this->items);

            // sort the items into menu levels.

            $menus = array();
            $sub_menus = array();

            foreach ($this->items as $item) {
                $parts = explode('-!-', $item[2]);
                $menu_level = &$menus;
                foreach ($parts as $part) {
                    if ($part != '') {
                        if (!array_key_exists($part, $menu_level)) {
                            $menu_level[$part] = array();
                        }
                        $menu_level = &$menu_level[$part];
                    }
                }
                $menu_level[$item[0]] = $item;
            }

            // Apply filters
            $menus = apply_filters('editor_addon_menus_' . $this->name, $menus);

            $this->_media_menu_direct_links = array();
            $menus_output = $this->_output_media_menu($menus, $text_area);
            $direct_links = implode(' ', $this->_media_menu_direct_links);
            $out = '
<ul class="editor_addon_wrapper"><li><img src="'
            . $this->media_button_image
                    . '"><ul class="editor_addon_dropdown"><li><div class="title">'
                    . $this->button_text
                    . '</div><div class="close">&nbsp;</div></li><li><div>'
                    . apply_filters('editor_addon_dropdown_top_message_' . $this->name, '')
                    . '</div><div class="direct-links">'
                    . $direct_links . '</div><div class="scroll"><div class="wrapper">'
                    . $menus_output . '</div><div></div>'
                    . apply_filters('editor_addon_dropdown_bottom_message' . $this->name, '')
                    . '</div></li></ul></li></ul>';

            // WP 3.3 changes
            if (version_compare($wp_version, '3.2.1', '>')) {
                echo apply_filters('wpv_add_media_buttons', $out);
            } else {
                return apply_filters('wpv_add_media_buttons', $context . $out);
            }
        }

        function _output_media_menu($menu, $text_area) {

            $out = '';
            if (is_array($menu)) {
                foreach ($menu as $key => $menu_item) {
                    if (isset($menu_item[0]) && !is_array($menu_item[0])) {
                        if ($menu_item[3] != '') {
                            $out .= '<a href="javascript:void(0);" class="item" onclick="' . $menu_item[3] . '">' . $menu_item[0] . "</a>\n";
                        } else {
                            $short_code = '[' . $menu_item[1] . ']';
                            $short_code = base64_encode($short_code);
                            $out .= '<a href="javascript:void(0);" class="item" onclick="insert_b64_shortcode_to_editor(\'' . $short_code . '\', \'' . $text_area . '\')">' . $menu_item[0] . "</a>\n";
                        }
                    } else {
                        // a sum menu.
                        $this->_media_menu_direct_links[] = '<a href="javascript:void(0);" class="editor-addon-top-link" id="editor-addon-link-' . md5($key) . '">' . $key . ' </a>';
                        $out .= '<div class="group"><div class="group-title" id="editor-addon-link-' . md5($key) . '-target">' . $key . "&nbsp;&nbsp;\n</div>\n";
                        $out .= $this->_output_media_menu($menu_item, $text_area);
                        $out .= "</div>\n";
                    }
                }
            }

            return $out;
        }

        /*

          Render the javascript code to define the menus
          The views_editor_plugin.js will use the created javascript
          variables to create the menu.

         */

        function render_js() {
            if (sizeof($this->items) > 0) {
                $name = str_replace('-', '_', $this->name);

                ?>    
                <script type="text/javascript">
                    var wp_editor_addon_<?php echo $name; ?> = new Array();
                    var button_title = '<?php echo $this->button_text; ?>';
                <?php
                $index = 0;
                foreach ($this->items as $item) {
                    $function_name = $name . base64_encode($item[0]) . '_' . $index;
                    $function_name = str_replace(array('+', '/', '='), '_',
                            $function_name);
                    if ($item[3] != '') {
                        // we need to create an on-click function that calls the function passed
                        echo 'wp_editor_addon_' . $name . '[' . $index . '] = new Array("' . $item[0] . '", "' . $function_name . '", "' . $item[2] . '");' . "\n";

                        // create a js function to be called for the on_click
                        echo 'function ' . $function_name . "() { " . $item[3] . "};\n";
                    } else {
                        // we need to create an on-click function that just inserts the shortcode.
                        echo 'wp_editor_addon_' . $name . '[' . $index . '] = new Array("' . $item[0] . '", "' . $function_name . '", "' . $item[2] . '");' . "\n";

                        // create a js function to be called for the on_click
                        echo 'function ' . $function_name . "() { tinyMCE.activeEditor.execCommand('mceInsertContent', false, '[" . $item[1] . "]')};\n";
                    }

                    $index++;
                }

                ?>
                </script>
                <?php
                add_filter('mce_external_plugins',
                        array($this, 'wpv_mce_register'));
                add_filter('mce_buttons', array($this, 'wpv_mce_add_button'), 0);
            }
        }

        /*

          Add the wpv_views button to the toolbar.

         */

        function wpv_mce_add_button($buttons)
        {
            array_push($buttons, "separator", str_replace('-', '_', $this->name));
            return $buttons;
        }

        /*

          Register this plugin as a mce 'addon'
          Tell the mce editor the url of the javascript file.
         */

        function wpv_mce_register($plugin_array)
        {
            $plugin_array[str_replace('-', '_', $this->name)] = $this->plugin_js_url;
            return $plugin_array;
        }

    }

    function editor_add_js() {
        global $pagenow;

        if ($pagenow == 'post.php' || $pagenow == 'post-new.php') {

            wp_enqueue_script('icl_editor-script',
                    EDITOR_ADDON_RELPATH . '/res/js/icl_editor_addon_plugin.js',
                    array());
        }
    }

}

