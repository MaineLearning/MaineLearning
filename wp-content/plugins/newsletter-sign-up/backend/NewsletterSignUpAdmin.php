<?php
if (!class_exists('NewsletterSignUpAdmin')) {

    class NewsletterSignUpAdmin {

        private $hook = 'newsletter-sign-up';
        private $longname = 'Newsletter Sign-Up';
        private $shortname = 'Newsletter Sign-Up';
        private $plugin_url = 'http://dannyvankooten.com/wordpress-plugins/newsletter-sign-up/';
        private $filename = 'newsletter-sign-up/newsletter-sign-up.php';
        private $accesslvl = 'manage_options';
        private $icon_url = '';
        private $bp_active = FALSE;
        private $actions = array();

        function __construct() {
            
            // If coming from older version of NSU, transfer settings to work with new settings architecture.
            $this->transfer_settings();

            $this->icon_url = plugins_url('/backend/img/icon.png', dirname(__FILE__));

            add_filter("plugin_action_links_{$this->filename}", array(&$this, 'add_settings_link'));
            add_action('admin_menu', array(&$this, 'add_option_page'));
            add_action('admin_init', array(&$this, 'settings_init'));

            // add dashboard widget hook
            add_action('wp_dashboard_setup', array(&$this, 'widget_setup'));

            // register function to remove options upon deactivation
            register_deactivation_hook($this->filename, array(&$this, 'remove_options'));


            /* Only do stuff on admin page of this plugin */
            if (isset($_GET['page']) && stripos($_GET['page'], $this->hook) !== FALSE) {
                add_action("admin_print_styles", array(&$this, 'add_admin_styles'));
                add_action("admin_print_scripts", array(&$this, 'add_admin_scripts'));
            }


            add_action('bp_include', array(&$this, 'set_bp_active'));
        }

        /**
         * If buddypress is loaded, set buddypress_active to TRUE
         */
        function set_bp_active() {
            $this->bp_active = TRUE;
        }

        /**
         * Enqueue the necessary stylesheets
         */
        function add_admin_styles() {
            wp_enqueue_style($this->hook . '_css', plugins_url('/backend/css/backend.css', dirname(__FILE__)));
        }

        /**
         * Enqueue the necessary admin scripts
         */
        function add_admin_scripts() {
            wp_enqueue_script(array('jquery', 'dashboard', 'postbox'));
            wp_enqueue_script('ns_admin_js', plugins_url('/backend/js/backend.js', dirname(__FILE__)));
        }

        /**
         * The default settings page
         */
        function options_page_default() {
            $opts = get_option('nsu_mailinglist');

            $viewed_mp = NULL;
            if (!empty($_GET['mp']))
                $viewed_mp = $_GET['mp'];
            elseif (empty($_GET['mp']) && isset($opts['provider']))
                $viewed_mp = $opts['provider'];
            if (!in_array($viewed_mp, array('mailchimp', 'icontact', 'aweber', 'phplist', 'ymlp', 'other')))
                $viewed_mp = NULL;

            // Fill in some predefined values if options not set or set for other newsletter service
            if (!isset($opts['provider']) || $opts['provider'] != $viewed_mp) {
                switch ($viewed_mp) {

                    case 'mailchimp':
                        if (empty($opts['email_id']))
                            $opts['email_id'] = 'EMAIL';
                        if (empty($opts['name_id']))
                            $opts['name_id'] = 'NAME';
                        break;

                    case 'ymlp':
                        if (empty($opts['email_id']))
                            $opts['email_id'] = 'YMP0';
                        break;

                    case 'aweber':
                        if (empty($opts['form_action']))
                            $opts['form_action'] = 'http://www.aweber.com/scripts/addlead.pl';
                        if (empty($opts['email_id']))
                            $opts['email_id'] = 'email';
                        if (empty($opts['name_id']))
                            $opts['name_id'] = 'name';
                        break;

                    case 'icontact':
                        if (empty($opts['email_id']))
                            $opts['email_id'] = 'fields_email';
                        break;
                }
            }

            require 'views/dashboard.php';
        }
        
        /**
         * The admin page for managing checkbox settings
         */
        function options_page_checkbox_settings() {
            $opts = get_option('nsu_checkbox');
            require 'views/checkbox_settings.php';
        }

        /**
         * The admin page for managing form settings
         */
        function options_page_form_settings() {
            $opts = get_option('nsu_form');
            $opts['mailinglist'] = get_option('nsu_mailinglist');
            require 'views/form_settings.php';
        }

        /**
         * The page for the configuration extractor
         */
        function options_page_config_helper() {

            if (isset($_POST['form'])) {
                $error = true;

                $form = $_POST['form'];

                // strip unneccessary tags
                $form = strip_tags($form, '<form><input><button>');


                preg_match_all("'<(.*?)>'si", $form, $matches);

                if (is_array($matches) && isset($matches[0])) {
                    $matches = $matches[0];
                    $html = stripslashes(join('', $matches));

                    $clean_form = htmlspecialchars(str_replace(array('><', '<input'), array(">\n<", "\t<input"), $html), ENT_NOQUOTES);

                    $doc = new DOMDocument();
                    $doc->strictErrorChecking = FALSE;
                    $doc->loadHTML($html);
                    $xml = simplexml_import_dom($doc);

                    if ($xml) {
                        $result = true;
                        $form = $xml->body->form;

                        if ($form) {
                            unset($error);
                            $form_action = (isset($form['action'])) ? $form['action'] : 'Can\'t help you on this one..';

                            if ($form->input) {

                                $additional_data = array();

                                /* Loop trough input fields */
                                foreach ($form->input as $input) {

                                    // Check if this is a hidden field
                                    if ($input['type'] == 'hidden') {
                                        $additional_data[] = array($input['name'], $input['value']);
                                        // Check if this is the input field that is supposed to hold the EMAIL data
                                    } elseif (stripos($input['id'], 'email') !== FALSE || stripos($input['name'], 'email') !== FALSE) {
                                        $email_identifier = $input['name'];

                                        // Check if this is the input field that is supposed to hold the NAME data
                                    } elseif (stripos($input['id'], 'name') !== FALSE || stripos($input['name'], 'name') !== FALSE) {
                                        $name_identifier = $input['name'];
                                    }
                                }
                            }
                        }



                        // Correct value's
                        if (!isset($email_identifier))
                            $email_identifier = 'Can\'t help you on this one..';
                        if (!isset($name_identifier))
                            $name_identifier = 'Can\'t help you on this one. Not using name data?';
                    }
                }
            }

            require 'views/config_helper.php';
        }

        /**
         * Renders a donate box
         */
        function donate_box() {
            $content = '
            <p>I spent countless hours developing this plugin for <b>FREE</b>. If you like it, consider donating a token of your appreciation.</p>
					
			<form id="dvk_donate" action="https://www.paypal.com/cgi-bin/webscr" method="post">
                <input type="hidden" name="cmd" value="_s-xclick">
                <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
                <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
                <img alt="" border="0" src="https://www.paypalobjects.com/nl_NL/i/scr/pixel.gif" width="1" height="1">
            </form>

			<p>Or you can: </p>
            <ul>
                <li><a href="http://wordpress.org/extend/plugins/newsletter-sign-up/">Give a 5&#9733; rating on WordPress.org</a></li>
                <li><a href="'.$this->plugin_url.'">Blog about it and link to the plugin page</a></li>
                <li style="vertical-align:bottom;"><a href="http://twitter.com/share" class="twitter-share-button" data-url="'.$this->plugin_url.'" data-text="Showing my appreciation to @DannyvanKooten for his #WordPress plugin: '.$this->shortname.'" data-count="none">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script></li>
            </ul>';
            $this->postbox($this->hook . '-donatebox', 'Donate $10, $20 or $50!', $content);
        }

        /**
         * Renders a box with the latests posts from DannyvanKooten.com
         */
        function latest_posts() {
            require_once(ABSPATH . WPINC . '/rss.php');
            if ($rss = fetch_rss('http://feeds.feedburner.com/dannyvankooten')) {
                $content = '<ul>';
                $rss->items = array_slice($rss->items, 0, 5);

                foreach ((array) $rss->items as $item) {
                    $content .= '<li class="dvk-rss-item">';
                    $content .= '<a target="_blank" href="' . clean_url($item['link'], $protocolls = null, 'display') . '">' . $item['title'] . '</a> ';
                    $content .= '</li>';
                }
                $content .= '<li class="dvk-rss"><a href="http://dannyvankooten.com/feed/">Subscribe to my RSS feed</a></li>';
                $content .= '<li class="dvk-email"><a href="http://dannyvankooten.com/newsletter/">Subscribe by email</a></li>';
                $content .= '<li class="dvk-twitter">You should follow me on twitter <a href="http://twitter.com/dannyvankooten">here</a></li>';
                $content .= '</ul><br style="clear:both;" />';
            } else {
                $content = '<p>No updates..</p>';
            }
            $this->postbox($this->hook . '-latestpostbox', 'Latest blog posts..', $content);
        }


        /**
         * Renders a box with a link to the support forums for NSU
         */
        function support_box() {
            $content = '<p>Are you having trouble setting-up ' . $this->shortname . ', experiencing an error or got a great idea on how to improve it?</p><p>Please, post
				your question or tip in the <a target="_blank" href="http://wordpress.org/tags/' . $this->hook . '">support forums</a> on WordPress.org. This is so that others can benefit from this too.</p>';
            $this->postbox($this->hook . '-support-box', "Looking for support?", $content);
        }

        /**
         * Output's the necessary HTML formatting for a postbox
         * 
         * @param string $id
         * @param string $title
         * @param string $content 
         */
        function postbox($id, $title, $content) {
            ?>
            <div id="<?php echo $id; ?>" class="dvk-box">		
               
                <h3 class="hndle"><?php echo $title; ?></h3>
                <div class="inside">
                    <?php echo $content; ?>			
                </div>
            </div>
            <?php
        }

        /**
         * Renders the DvK.com dashboard widget on the admin homepage.
         */
        function dashboard_widget() {
            $options = get_option('dvkdbwidget');
            if (isset($_POST['dvk_removedbwidget'])) {
                $options['dontshow'] = true;
                update_option('dvkdbwidget', $options);
            }

            if (isset($options['dontshow']) && $options['dontshow']) {
                echo "If you reload, this widget will be gone and never appear again, unless you decide to delete the database option 'dvkdbwidget'.";
                return;
            }

            require_once(ABSPATH . WPINC . '/rss.php');
            if ($rss = fetch_rss('http://feeds.feedburner.com/dannyvankooten')) {
                echo '<div class="rss-widget">';
                echo '<a href="http://dannyvankooten.com/" title="Go to DannyvanKooten.com"><img src="http://static.dannyvankooten.com/images/dvk-64x64.png" class="alignright" alt="DannyvanKooten.com"/></a>';
                echo '<ul>';
                $rss->items = array_slice($rss->items, 0, 3);
                foreach ((array) $rss->items as $item) {
                    echo '<li>';
                    echo '<a target="_blank" class="rsswidget" href="' . clean_url($item['link'], $protocolls = null, 'display') . '">' . $item['title'] . '</a> ';
                    echo '<span class="rss-date">' . date('F j, Y', strtotime($item['pubdate'])) . '</span>';
                    echo '<div class="rssSummary">' . $this->text_limit($item['summary'], 250) . '</div>';
                    echo '</li>';
                }
                echo '</ul>';
                echo '<div style="border-top: 1px solid #ddd; padding-top: 10px; text-align:center;">';
                echo '<a target="_blank" style="margin-right:10px;" href="http://feeds.feedburner.com/dannyvankooten"><img src="' . get_bloginfo('wpurl') . '/wp-includes/images/rss.png" alt=""/> Subscribe by RSS</a>';
                echo '<a target="_blank" href="http://dannyvankooten.com/newsletter/"><img src="http://static.dannyvankooten.com/images/email-icon.png" alt=""/> Subscribe by email</a>';
                echo '<form class="alignright" method="post"><input type="hidden" name="dvk_removedbwidget" value="true"/><input title="Remove this widget" type="submit" value=" X "/></form>';
                echo '</div>';
                echo '</div>';
            }
        }

        /**
         * Function that is hooked, adds the DvK.com dashboard widget.
         */
        function widget_setup() {
            $options = get_option('dvkdbwidget');
            if (!$options['dontshow'])
                wp_add_dashboard_widget('dvk_db_widget', 'Latest posts on DannyvanKooten.com', array(&$this, 'dashboard_widget'));
        }

        function text_limit($text, $limit, $finish = '...') {
            if (strlen($text) > $limit) {
                $text = substr($text, 0, $limit);
                $text = substr($text, 0, - ( strlen(strrchr($text, ' ')) ));
                $text .= $finish;
            }
            return $text;
        }

        /**
         * Adds the different menu pages
         */
        function add_option_page() {
            add_menu_page($this->longname, "Newsl. Sign-up", $this->accesslvl, $this->hook, array(&$this, 'options_page_default'), $this->icon_url);
            add_submenu_page($this->hook, "Newsletter Sign-Up :: Mailinglist Settings", "List Settings", $this->accesslvl, $this->hook, array($this, 'options_page_default'));
            add_submenu_page($this->hook, "Newsletter Sign-Up :: Checkbox Settings", "Checkbox Settings", $this->accesslvl, $this->hook . '/checkbox-settings', array($this, 'options_page_checkbox_settings'));
            add_submenu_page($this->hook, "Newsletter Sign-Up :: Form Settings", "Form Settings", $this->accesslvl, $this->hook . '/form-settings', array($this, 'options_page_form_settings'));
            add_submenu_page($this->hook, "Newsletter Sign-Up :: Configuration Extractor", "Config Extractor", $this->accesslvl, $this->hook . '/config-helper', array($this, 'options_page_config_helper'));
        }

        /**
         * Adds the settings link on the plugin's overview page
         * @param array $links Array containing all the settings links for the various plugins.
         * @return array The new array containing all the settings links
         */
        function add_settings_link($links) {
            $settings_link = '<a href="admin.php?page=' . $this->hook . '">Settings</a>';
            array_unshift($links, $settings_link);
            return $links;
        }

        /**
         * Check how long the plugin has been used
         * If used for over 30 days, show a pop-up asking for a tweet or donation.
         */
        function check_usage_time() {
            $opts = get_option('nsu');
            if (isset($_GET['dontshowpopup']) && $_GET['dontshowpopup'] == 1) {
                $opts['dontshowpopup'] = 1;
                update_option('nsu', $opts);
            }
            if (!isset($opts['date_installed'])) {
                // set installed_time to now, so we can show pop-up in 30 days
                $opts['date_installed'] = strtotime('now');
                update_option('nsu', $opts);
            } elseif ((!isset($opts['dontshowpopup']) || $opts['dontshowpopup'] != 1) && $opts['date_installed'] < strtotime('-30 days')) {
                // plugin has been installed for over 30 days
                $this->actions['show_donate_box'] = true;
                wp_enqueue_style('dvk_donate', plugins_url('/backend/css/donate.css', dirname(__FILE__)));
                wp_enqueue_script('dvk_donate', plugins_url('/backend/js/donate.js', dirname(__FILE__)));
            }
        }

        /**
         * Registers the settings using WP Settings API.
         */
        function settings_init() {
            register_setting('nsu_group', 'nsu', array(&$this, 'validate_options'));
            register_setting('nsu_form_group', 'nsu_form', array(&$this, 'validate_form_options'));
            register_setting('nsu_mailinglist_group', 'nsu_mailinglist', array(&$this, 'validate_mailinglist_options'));
            register_setting('nsu_checkbox_group', 'nsu_checkbox', array(&$this, 'validate_checkbox_options'));
        }

        /**
         * Transfer settings from old optionname for backwards compatibility
         */
        private function transfer_settings() {
            if (($old_options = get_option('ns_options')) != false && get_option('nsu') == false) {

                $nsu = array(
                    'date_installed' => (isset($old_options['date_installed'])) ? $old_options['date_installed'] : strtotime('now'),
                    'dontshowpopup' => (isset($old_options['dontshowpopup'])) ? $old_options['dontshowpopup'] : 0,
                    'load_widget_styles' => (isset($old_options['load_widget_styles'])) ? $old_options['load_widget_styles'] : 0
                );

                // new form option holder
                $nsu_form = $old_options['form'];

                // new checkbox option holder
                $nsu_checkbox = array(
                    'text' => (isset($old_options['checkbox_text'])) ? $old_options['checkbox_text'] : 'Sign me up for the newsletter.',
                    'precheck' => (isset($old_options['precheck_checkbox'])) ? $old_options['precheck_checkbox'] : 0,
                    'add_to_comment_form' => (isset($old_options['add_to_comment_form'])) ? $old_options['add_to_comment_form'] : 0,
                    'add_to_registration_form' => (isset($old_options['add_to_reg_form'])) ? $old_options['add_to_reg_form'] : 0,
                    'add_to_buddypress_form' => (isset($old_options['add_to_bp_form'])) ? $old_options['add_to_bp_form'] : 0,
                    'add_to_multisite_form' => (isset($old_options['add_to_ms_form'])) ? $old_options['add_to_ms_form'] : 0,
                    'css_reset' => (isset($old_options['do_css_reset'])) ? $old_options['do_css_reset'] : 0,
                    'cookie_hide' => (isset($old_options['cookie_hide'])) ? $old_options['cookie_hide'] : 0,
                );

                $nsu_mailinglist = array(
                    'provider' => (isset($old_options['email_service'])) ? $old_options['email_service'] : '',
                    'use_api' => (isset($old_options['use_api'])) ? $old_options['use_api'] : 0,
                    'mc_api_key' => (isset($old_options['api_key'])) ? $old_options['api_key'] : '',
                    'mc_list_id' => (isset($old_options['list_id'])) ? $old_options['list_id'] : '',
                    'ymlp_api_key' => (isset($old_options['ymlp_api_key'])) ? $old_options['ymlp_api_key'] : '',
                    'ymlp_username' => (isset($old_options['ymlp_username'])) ? $old_options['ymlp_username'] : '',
                    'ymlp_groupid' => (isset($old_options['ymlp_groupid'])) ? $old_options['ymlp_groupid'] : '',
                    'aweber_list_name' => (isset($old_options['aweber_list_name'])) ? $old_options['aweber_list_name'] : '',
                    'phplist_list_id' => (isset($old_options['phplist_list_id'])) ? $old_options['phplist_list_id'] : '',
                    'form_action' => (isset($old_options['form_action'])) ? $old_options['form_action'] : '',
                    'email_id' => (isset($old_options['email_id'])) ? $old_options['email_id'] : '',
                    'subscribe_with_name' => (isset($old_options['subscribe_with_name'])) ? $old_options['subscribe_with_name'] : 0,
                    'name_id' => (isset($old_options['name_id'])) ? $old_options['name_id'] : '',
                    'extra_data' => $old_options['extra_data']
                );

                delete_option('nsu');
                delete_option('nsu_form');
                delete_option('nsu_checkbox');
                delete_option('nsu_mailinglist');

                add_option('nsu', $nsu);
                add_option('nsu_form', $nsu_form);
                add_option('nsu_checkbox', $nsu_checkbox);
                add_option('nsu_mailinglist', $nsu_mailinglist);

                //delete_option('ns_options');
            }
        }

        /**
         * Removes the options from database, this function is hooked to deactivation of NSU.
         */
        function remove_options() {
            // old option name
            delete_option('ns_options');
            
            // new option names
            delete_option('nsu');
            delete_option('nsu_form');
            delete_option('nsu_checkbox');
            delete_option('nsu_mailinglist');
        }

        /**
         * Validate the submitted options
         * @param array $options The submitted options
         */
        public function validate_options($options) {
            return $options;
        }

        public function validate_form_options($options) {
            $options['text_after_signup'] = strip_tags($options['text_after_signup'], '<a><b><strong><i><img><em><br><p><ul><li><ol>');
            
            // redirect to url should start with http
            if(isset($options['redirect_to']) && substr($options['redirect_to'],0,4) != 'http') {
                $options['redirect_to'] = '';
            }
            
            return $options;
        }

        public function validate_mailinglist_options($options) {
            if (is_array($options['extra_data'])) {
                foreach ($options['extra_data'] as $key => $value) {
                    if (empty($value['name']))
                        unset($options['extra_data'][$key]);
                }
            }

            return $options;
        }

        public function validate_checkbox_options($options) {
            return $options;
        }

    }

}