<?php 

if (isset($_POST['gdsr_action']) && $_POST['gdsr_action'] == 'save') {
    $gdsr_options["bot_message"] = $_POST['gdsr_bot_message'];
    $gdsr_options["no_votes_percentage"] = $_POST['gdsr_no_votes_percentage'];
    $gdsr_options["cached_loading"] = isset($_POST['gdsr_cached_loading']) ? 1 : 0;
    $gdsr_options["include_opacity"] = isset($_POST['gdsr_include_opacity']) ? 1 : 0;
    $gdsr_options["comments_integration_articles_active"] = isset($_POST['gdsr_cmmintartactive']) ? 1 : 0;
    $gdsr_options["update_report_usage"] = isset($_POST['gdsr_update_report_usage']) ? 1 : 0;
    $gdsr_options["int_comment_std_zero"] = isset($_POST['gdsr_int_comment_std_zero']) ? 1 : 0;
    $gdsr_options["int_comment_mur_zero"] = isset($_POST['gdsr_int_comment_mur_zero']) ? 1 : 0;
    $gdsr_options["thumbs_active"] = isset($_POST['gdsr_thumbs_act']) ? 1 : 0;
    $gdsr_options["wp_query_handler"] = isset($_POST['gdsr_wp_query_handler']) ? 1 : 0;
    $gdsr_options["disable_ie6_check"] = isset($_POST['gdsr_disable_ie6_check']) ? 1 : 0;
    $gdsr_options["news_feed_active"] = isset($_POST['gdsr_news_feed_active']) ? 1 : 0;
    $gdsr_options["widgets_hidempty"] = isset($_POST['gdsr_widgets_hidempty']) ? 1 : 0;
    $gdsr_options["encoding"] = $_POST['gdsr_encoding'];
    $gdsr_options["admin_rows"] = $_POST['gdsr_admin_rows'];
    $gdsr_options["gfx_generator_auto"] = isset($_POST['gdsr_gfx_generator_auto']) ? 1 : 0;
    $gdsr_options["gfx_prevent_leeching"] = isset($_POST['gdsr_gfx_prevent_leeching']) ? 1 : 0;
    $gdsr_options["external_rating_css"] = isset($_POST['gdsr_external_rating_css']) ? 1 : 0;
    $gdsr_options["css_cache_active"] = isset($_POST['gdsr_css_cache_active']) ? 1 : 0;
    $gdsr_options["external_css"] = isset($_POST['gdsr_external_css']) ? 1 : 0;
    $gdsr_options["cmm_integration_replay_hide_review"] = isset($_POST['gdsr_cmm_integration_replay_hide_review']) ? 1 : 0;
    $gdsr_options["cmm_integration_prevent_duplicates"] = isset($_POST['gdsr_cmm_integration_prevent_duplicates']) ? 1 : 0;
    $gdsr_options["admin_advanced"] = isset($_POST['gdsr_admin_advanced']) ? 1 : 0;
    $gdsr_options["admin_placement"] = isset($_POST['gdsr_admin_placement']) ? 1 : 0;
    $gdsr_options["admin_defaults"] = isset($_POST['gdsr_admin_defaults']) ? 1 : 0;
    $gdsr_options["admin_import"] = isset($_POST['gdsr_admin_import']) ? 1 : 0;
    $gdsr_options["admin_export"] = isset($_POST['gdsr_admin_export']) ? 1 : 0;
    $gdsr_options["admin_setup"] = isset($_POST['gdsr_admin_setup']) ? 1 : 0;
    $gdsr_options["admin_ips"] = isset($_POST['gdsr_admin_ips']) ? 1 : 0;
    $gdsr_options["admin_views"] = isset($_POST['gdsr_admin_views']) ? 1 : 0;
    $gdsr_options["prefetch_data"] = isset($_POST['gdsr_prefetch_data']) ? 1 : 0;

    $gdsr_options["allow_mixed_ip_votes"] = isset($_POST['gdsr_allow_mixed_ip_votes']) ? 1 : 0;
    $gdsr_options["cmm_allow_mixed_ip_votes"] = isset($_POST['gdsr_cmm_allow_mixed_ip_votes']) ? 1 : 0;
    $gdsr_options["mur_allow_mixed_ip_votes"] = isset($_POST['gdsr_mur_allow_mixed_ip_votes']) ? 1 : 0;
    $gdsr_options["cache_active"] = isset($_POST['gdsr_cache_active']) ? 1 : 0;
    $gdsr_options["cache_forced"] = isset($_POST['gdsr_cache_forced']) ? 1 : 0;
    $gdsr_options["cache_cleanup_auto"] = isset($_POST['gdsr_cache_cleanup_auto']) ? 1 : 0;
    $gdsr_options["cache_cleanup_days"] = $_POST['gdsr_cache_cleanup_days'];

    $gdsr_options["wait_show_article"] = isset($_POST['gdsr_wait_show_article']) ? 1 : 0;
    $gdsr_options["wait_show_comment"] = isset($_POST['gdsr_wait_show_comment']) ? 1 : 0;
    $gdsr_options["wait_show_multis"] = isset($_POST['gdsr_wait_show_multis']) ? 1 : 0;
    $gdsr_options["wait_loader_article"] = $_POST['gdsr_wait_loader_article'];
    $gdsr_options["wait_loader_comment"] = $_POST['gdsr_wait_loader_comment'];
    $gdsr_options["wait_loader_multis"] = $_POST['gdsr_wait_loader_multis'];
    $gdsr_options["wait_text_article"] = $_POST['gdsr_wait_text_article'];
    $gdsr_options["wait_text_comment"] = $_POST['gdsr_wait_text_comment'];
    $gdsr_options["wait_text_multis"] = $_POST['gdsr_wait_text_multis'];
    $gdsr_options["wait_class_article"] = $_POST['gdsr_wait_class_article'];
    $gdsr_options["wait_class_comment"] = $_POST['gdsr_wait_class_comment'];
    $gdsr_options["wait_class_multis"] = $_POST['gdsr_wait_class_multis'];
    $gdsr_options["wait_loader_artthumb"] = $_POST['gdsr_wait_loader_artthumb'];
    $gdsr_options["wait_loader_cmmthumb"] = $_POST['gdsr_wait_loader_cmmthumb'];

    $gdsr_options["security_showdashboard_user_level"] = $_POST['gdsr_security_showdashboard_user_level'];
    $gdsr_options["security_showip_user_level"] = $_POST['gdsr_security_showip_user_level'];

    $gdsr_options["google_rich_snippets_format"] = $_POST['gdsr_grs_format'];
    $gdsr_options["google_rich_snippets_location"] = $_POST['gdsr_grs_location'];
    $gdsr_options["google_rich_snippets_datasource"] = $_POST['gdsr_grs_datasource'];
    $gdsr_options["google_rich_snippets_active"] = isset($_POST['gdsr_grs']) ? 1 : 0;

    $gdsr_options["debug_wpquery"] = isset($_POST['gdsr_debug_wpquery']) ? 1 : 0;
    $gdsr_options["debug_active"] = isset($_POST['gdsr_debug_active']) ? 1 : 0;
    $gdsr_options["debug_inline"] = isset($_POST['gdsr_debug_inline']) ? 1 : 0;
    $gdsr_options["use_nonce"] = isset($_POST['gdsr_use_nonce']) ? 1 : 0;
    $gdsr_options["ajax_jsonp"] = isset($_POST['gdsr_ajax_jsonp']) ? 1 : 0;
    $gdsr_options["ip_filtering"] = isset($_POST['gdsr_ip_filtering']) ? 1 : 0;
    $gdsr_options["ip_filtering_restrictive"] = isset($_POST['gdsr_ip_filtering_restrictive']) ? 1 : 0;
    $gdsr_options["widget_articles"] = isset($_POST['gdsr_widget_articles']) ? 1 : 0;
    $gdsr_options["widget_top"] = isset($_POST['gdsr_widget_top']) ? 1 : 0;
    $gdsr_options["widget_comments"] = isset($_POST['gdsr_widget_comments']) ? 1 : 0;
    $gdsr_options["display_pages"] = isset($_POST['gdsr_pages']) ? 1 : 0;
    $gdsr_options["display_posts"] = isset($_POST['gdsr_posts']) ? 1 : 0;
    $gdsr_options["display_archive"] = isset($_POST['gdsr_archive']) ? 1 : 0;
    $gdsr_options["display_home"] = isset($_POST['gdsr_home']) ? 1 : 0;
    $gdsr_options["display_search"] = isset($_POST['gdsr_search']) ? 1 : 0;
    $gdsr_options["display_comment"] = isset($_POST['gdsr_dispcomment']) ? 1 : 0;
    $gdsr_options["display_comment_page"] = isset($_POST['gdsr_dispcomment_pages']) ? 1 : 0;
    $gdsr_options["moderation_active"] = isset($_POST['gdsr_modactive']) ? 1 : 0;
    $gdsr_options["multis_active"] = isset($_POST['gdsr_multis']) ? 1 : 0;
    $gdsr_options["timer_active"] = isset($_POST['gdsr_timer']) ? 1 : 0;
    $gdsr_options["rss_active"] = isset($_POST['gdsr_rss']) ? 1 : 0;
    $gdsr_options["save_user_agent"] = isset($_POST['gdsr_save_user_agent']) ? 1 : 0;
    $gdsr_options["save_cookies"] = isset($_POST['gdsr_save_cookies']) ? 1 : 0;
    $gdsr_options["ie_opacity_fix"] = isset($_POST['gdsr_ieopacityfix']) ? 1 : 0;

    $gdsr_options["override_display_comment"] = isset($_POST['gdsr_override_display_comment']) ? 1 : 0;
    $gdsr_options["override_thumb_display_comment"] = isset($_POST['gdsr_override_thumb_display_comment']) ? 1 : 0;

    $gdsr_options["integrate_dashboard"] = isset($_POST['gdsr_integrate_dashboard']) ? 1 : 0;
    $gdsr_options["integrate_dashboard_latest"] = isset($_POST['gdsr_integrate_dashboard_latest']) ? 1 : 0;
    $gdsr_options["integrate_dashboard_latest_filter_thumb_std"] = isset($_POST['gdsr_integrate_dashboard_latest_filter_thumb_std']) ? 1 : 0;
    $gdsr_options["integrate_dashboard_latest_filter_thumb_cmm"] = isset($_POST['gdsr_integrate_dashboard_latest_filter_thumb_cmm']) ? 1 : 0;
    $gdsr_options["integrate_dashboard_latest_filter_stars_std"] = isset($_POST['gdsr_integrate_dashboard_latest_filter_stars_std']) ? 1 : 0;
    $gdsr_options["integrate_dashboard_latest_filter_stars_cmm"] = isset($_POST['gdsr_integrate_dashboard_latest_filter_stars_cmm']) ? 1 : 0;
    $gdsr_options["integrate_dashboard_latest_filter_stars_mur"] = isset($_POST['gdsr_integrate_dashboard_latest_filter_stars_mur']) ? 1 : 0;
    $gdsr_options["integrate_dashboard_latest_count"] = $_POST['gdsr_integrate_dashboard_latest_count'];

    $gdsr_options["integrate_post_edit"] = isset($_POST['gdsr_integrate_post_edit']) ? 1 : 0;
    $gdsr_options["integrate_post_edit_mur"] = isset($_POST['gdsr_integrate_post_edit_mur']) ? 1 : 0;
    $gdsr_options["integrate_tinymce"] = isset($_POST['gdsr_integrate_tinymce']) ? 1 : 0;
    $gdsr_options["integrate_rss_powered"] = isset($_POST['gdsr_integrate_rss_powered']) ? 1 : 0;

    $gdsr_options["trend_last"] = $_POST['gdsr_trend_last'];
    $gdsr_options["trend_over"] = $_POST['gdsr_trend_over'];
    $gdsr_options["bayesian_minimal"] = $_POST['gdsr_bayesian_minimal'];
    $gdsr_options["bayesian_mean"] = $_POST['gdsr_bayesian_mean'];
    $gdsr_options["auto_display_position"] = $_POST['gdsr_auto_display_position'];
    $gdsr_options["auto_display_comment_position"] = $_POST['gdsr_auto_display_comment_position'];

    $gdsr_options["default_timer_type"] = isset($_POST['gdsr_default_timer_type']) ? $_POST['gdsr_default_timer_type'] : "N";
    $gdsr_options["default_timer_countdown_value"] = isset($_POST['gdsr_default_timer_countdown_value']) ? $_POST['gdsr_default_timer_countdown_value'] : 30;
    $gdsr_options["default_timer_countdown_type"] = isset($_POST['gdsr_default_timer_countdown_type']) ? $_POST['gdsr_default_timer_countdown_type'] : "D";
    $gdsr_options["default_timer_value"] = $gdsr_options["default_timer_countdown_type"].$gdsr_options["default_timer_countdown_value"];
    $gdsr_options["default_mur_timer_type"] = isset($_POST['gdsr_default_mur_timer_type']) ? $_POST['gdsr_default_mur_timer_type'] : "N";
    $gdsr_options["default_mur_timer_countdown_value"] = isset($_POST['gdsr_default_mur_timer_countdown_value']) ? $_POST['gdsr_default_mur_timer_countdown_value'] : 30;
    $gdsr_options["default_mur_timer_countdown_type"] = isset($_POST['gdsr_default_mur_timer_countdown_type']) ? $_POST['gdsr_default_mur_timer_countdown_type'] : "D";
    $gdsr_options["default_mur_timer_value"] = $gdsr_options["default_mur_timer_countdown_type"].$gdsr_options["default_mur_timer_countdown_value"];

    $gdsr_options["review_active"] = isset($_POST['gdsr_reviewactive']) ? 1 : 0;
    $gdsr_options["comments_active"] = isset($_POST['gdsr_commentsactive']) ? 1 : 0;
    $gdsr_options["comments_review_active"] = isset($_POST['gdsr_cmmreviewactive']) ? 1 : 0;
    $gdsr_options["hide_empty_rating"] = isset($_POST['gdsr_haderating']) ? 1 : 0;
    $gdsr_options["cookies"] = isset($_POST['gdsr_cookies']) ? 1 : 0;
    $gdsr_options["cmm_cookies"] = isset($_POST['gdsr_cmm_cookies']) ? 1 : 0;
    $gdsr_options["author_vote"] = isset($_POST['gdsr_authorvote']) ? 1 : 0;
    $gdsr_options["cmm_author_vote"] = isset($_POST['gdsr_cmm_authorvote']) ? 1 : 0;
    $gdsr_options["logged"] = isset($_POST['gdsr_logged']) ? 1 : 0;
    $gdsr_options["cmm_logged"] = isset($_POST['gdsr_cmm_logged']) ? 1 : 0;

    $gdsr_options["rss_datasource"] = $_POST['gdsr_rss_datasource'];
    $gdsr_options["rss_style"] = $_POST['gdsr_rss_style'];
    $gdsr_options["rss_size"] = $_POST['gdsr_rss_size'];
    $gdsr_options["thumb_rss_style"] = $_POST['gdsr_thumb_rss_style'];
    $gdsr_options["thumb_rss_size"] = $_POST['gdsr_thumb_rss_size'];
    $gdsr_options["rss_header_text"] = stripslashes(htmlentities($_POST['gdsr_rss_header_text'], ENT_QUOTES, STARRATING_ENCODING));
    $gdsr_options["style"] = $_POST['gdsr_style'];
    $gdsr_options["style_ie6"] = $_POST['gdsr_style_ie6'];
    $gdsr_options["size"] = $_POST['gdsr_size'];
    $gdsr_options["header_text"] = stripslashes(htmlentities($_POST['gdsr_header_text'], ENT_QUOTES, STARRATING_ENCODING));
    $gdsr_options["thumb_style"] = $_POST['gdsr_thumb_style'];
    $gdsr_options["thumb_style_ie6"] = $_POST['gdsr_thumb_style_ie6'];
    $gdsr_options["thumb_size"] = $_POST['gdsr_thumb_size'];
    $gdsr_options["thumb_header_text"] = stripslashes(htmlentities($_POST['gdsr_thumb_header_text'], ENT_QUOTES, STARRATING_ENCODING));
    $gdsr_options["thumb_cmm_style"] = $_POST['gdsr_thumb_cmm_style'];
    $gdsr_options["thumb_cmm_style_ie6"] = $_POST['gdsr_thumb_cmm_style_ie6'];
    $gdsr_options["thumb_cmm_size"] = $_POST['gdsr_thumb_cmm_size'];
    $gdsr_options["thumb_cmm_header_text"] = stripslashes(htmlentities($_POST['gdsr_thumb_cmm_header_text'], ENT_QUOTES, STARRATING_ENCODING));

    $gdsr_options["default_srb_template"] = $_POST['gdsr_default_srb_template'];
    $gdsr_options["default_crb_template"] = $_POST['gdsr_default_crb_template'];
    $gdsr_options["default_ssb_template"] = $_POST['gdsr_default_ssb_template'];
    $gdsr_options["default_mrb_template"] = $_POST['gdsr_default_mrb_template'];
    $gdsr_options["default_tab_template"] = $_POST['gdsr_default_tab_template'];
    $gdsr_options["default_tcb_template"] = $_POST['gdsr_default_tcb_template'];
    $gdsr_options["srb_class_block"] = $_POST['gdsr_classblock'];
    $gdsr_options["srb_class_text"] = $_POST['gdsr_classtext'];
    $gdsr_options["srb_class_header"] = $_POST['gdsr_classheader'];
    $gdsr_options["srb_class_stars"] = $_POST['gdsr_classstars'];
    $gdsr_options["cmm_class_block"] = $_POST['gdsr_cmm_classblock'];
    $gdsr_options["cmm_class_text"] = $_POST['gdsr_cmm_classtext'];
    $gdsr_options["cmm_class_header"] = $_POST['gdsr_cmm_classheader'];
    $gdsr_options["cmm_class_stars"] = $_POST['gdsr_cmm_classstars'];

    $gdsr_options["mur_style"] = $_POST['gdsr_mur_style'];
    $gdsr_options["mur_style_ie6"] = $_POST['gdsr_mur_style_ie6'];
    $gdsr_options["mur_size"] = $_POST['gdsr_mur_size'];
    $gdsr_options["mur_header_text"] = stripslashes(htmlentities($_POST['gdsr_mur_header_text'], ENT_QUOTES, STARRATING_ENCODING));
    $gdsr_options["mur_class_block"] = $_POST['gdsr_mur_classblock'];
    $gdsr_options["mur_class_text"] = $_POST['gdsr_mur_classtext'];
    $gdsr_options["mur_class_header"] = $_POST['gdsr_mur_classheader'];
    $gdsr_options["mur_class_button"] = $_POST['gdsr_mur_classbutton'];
    $gdsr_options["mur_button_text"] = $_POST['gdsr_mur_submittext'];
    $gdsr_options["mur_button_active"] = isset($_POST['gdsr_mur_submitactive']) ? 1 : 0;

    $gdsr_options["cmm_aggr_style"] = $_POST['gdsr_cmm_aggr_style'];
    $gdsr_options["cmm_aggr_style_ie6"] = $_POST['gdsr_cmm_aggr_style_ie6'];
    $gdsr_options["cmm_aggr_size"] = $_POST['gdsr_cmm_aggr_size'];
    $gdsr_options["cmm_style"] = $_POST['gdsr_cmm_style'];
    $gdsr_options["cmm_style_ie6"] = $_POST['gdsr_cmm_style_ie6'];
    $gdsr_options["cmm_size"] = $_POST['gdsr_cmm_size'];
    $gdsr_options["cmm_header_text"] = stripslashes(htmlentities($_POST['gdsr_cmm_header_text'], ENT_QUOTES, STARRATING_ENCODING));

    $gdsr_options["review_style"] = $_POST['gdsr_review_style'];
    $gdsr_options["review_style_ie6"] = $_POST['gdsr_review_style_ie6'];
    $gdsr_options["review_size"] = $_POST['gdsr_review_size'];
    $gdsr_options["review_stars"] = $_POST['gdsr_review_stars'];
    $gdsr_options["review_header_text"] = stripslashes(htmlentities($_POST['gdsr_review_header_text'], ENT_QUOTES, STARRATING_ENCODING));
    $gdsr_options["review_class_block"] = $_POST['gdsr_review_classblock'];
    $gdsr_options["cmm_review_style"] = isset($_POST['gdsr_cmm_review_style']) ? $_POST['gdsr_cmm_review_style'] : "oxyen";
    $gdsr_options["cmm_review_style_ie6"] = isset($_POST['gdsr_cmm_review_style_ie6']) ? $_POST['gdsr_cmm_review_style_ie6'] : "oxyen_gif";
    $gdsr_options["cmm_review_size"] = isset($_POST['gdsr_cmm_review_size']) ? $_POST['gdsr_cmm_review_size'] : 20;

    $gdsr_options["default_voterules_multis"] = $_POST['gdsr_default_vote_multis'];
    $gdsr_options["default_voterules_articles"] = $_POST['gdsr_default_vote_articles'];
    $gdsr_options["default_voterules_comments"] = $_POST['gdsr_default_vote_comments'];
    $gdsr_options["recc_default_voterules_articles"] = $_POST['gdsr_recc_default_vote_articles'];
    $gdsr_options["recc_default_voterules_comments"] = $_POST['gdsr_recc_default_vote_comments'];

    $gdsr_options["default_moderation_multis"] = isset($_POST['gdsr_default_mod_multis']) ? $_POST['gdsr_default_mod_multis'] : "";
    $gdsr_options["default_moderation_articles"] = isset($_POST['gdsr_default_mod_articles']) ? $_POST['gdsr_default_mod_articles'] : "";
    $gdsr_options["default_moderation_comments"] = isset($_POST['gdsr_default_mod_comments']) ? $_POST['gdsr_default_mod_comments'] : "";
    $gdsr_options["recc_default_moderation_articles"] = isset($_POST['gdsr_recc_default_mod_articles']) ? $_POST['gdsr_recc_default_mod_articles'] : "";
    $gdsr_options["recc_default_moderation_comments"] = isset($_POST['gdsr_recc_default_mod_comments']) ? $_POST['gdsr_recc_default_mod_comments'] : "";

    $gdsr_options["thumb_display_pages"] = isset($_POST['gdsr_thumb_pages']) ? 1 : 0;
    $gdsr_options["thumb_display_posts"] = isset($_POST['gdsr_thumb_posts']) ? 1 : 0;
    $gdsr_options["thumb_display_archive"] = isset($_POST['gdsr_thumb_archive']) ? 1 : 0;
    $gdsr_options["thumb_display_home"] = isset($_POST['gdsr_thumb_home']) ? 1 : 0;
    $gdsr_options["thumb_display_search"] = isset($_POST['gdsr_thumb_search']) ? 1 : 0;
    $gdsr_options["thumb_display_comment"] = isset($_POST['gdsr_thumb_dispcomment']) ? 1 : 0;
    $gdsr_options["thumb_display_comment_page"] = isset($_POST['gdsr_thumb_dispcomment_pages']) ? 1 : 0;
    $gdsr_options["thumb_auto_display_position"] = $_POST['gdsr_thumb_auto_display_position'];
    $gdsr_options["thumb_auto_display_comment_position"] = $_POST['gdsr_thumb_auto_display_comment_position'];

    $gdsr_options["css_last_changed"] = time();
    update_option("gd-star-rating", $gdsr_options);

    $bots = explode("\r\n", $_POST["gdsr_bots"]);
    foreach($bots as $key => $value) {
        if(trim($value) == "") {
            unset($bots[$key]);
        }
    }
    $bots = array_values($bots);
    update_option("gd-star-rating-bots", $bots);
}

?>