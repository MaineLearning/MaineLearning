<?php

class gdsrMenus {
    var $g;
    var $front = false;

    function gdsrMenus($gdsr_main) {
        $this->g = $gdsr_main;
    }

    function star_multi_sets() {
        $wpv = $this->g->wp_version;
        $gdsr_page = isset($_GET["gdsr"]) ? $_GET["gdsr"] : "";

        $editor = true;
        if (isset($_POST['gdsr_action']) && $_POST['gdsr_action'] == 'save') {
            $editor = false;
            $eset = new GDMultiSingle(false);
            $eset->multi_id = $_POST["gdsr_ms_id"];
            $eset->name = stripslashes(htmlentities($_POST["gdsr_ms_name"], ENT_QUOTES, STARRATING_ENCODING));
            $eset->description = stripslashes(htmlentities($_POST["gdsr_ms_description"], ENT_QUOTES, STARRATING_ENCODING));
            if (isset($_POST["gdsr_ms_stars"])) $eset->stars = $_POST["gdsr_ms_stars"];
            $eset->auto_insert = $_POST["gdsr_ms_autoinsert"];
            $eset->auto_categories = $_POST["gdsr_ms_autocategories"];
            $eset->auto_location = $_POST["gdsr_ms_autolocation"];
            $elms = $_POST["gdsr_ms_element"];
            $elwe = $_POST["gdsr_ms_weight"];
            $i = 0;
            foreach ($elms as $el) {
                if (($el != "" && $eset->multi_id == 0) || $eset->multi_id > 0) {
                    $eset->object[] = stripslashes(htmlentities($el, ENT_QUOTES, STARRATING_ENCODING));
                    $ew = $elwe[$i];
                    if (!is_numeric($ew)) $ew = 1;
                    $eset->weight[] = $ew;
                    $i++;
                }
            }
            if ($eset->name != "") {
                if ($eset->multi_id == 0) $set_id = GDSRDBMulti::add_multi_set($eset);
                else {
                    $set_id = $eset->multi_id;
                    GDSRDBMulti::edit_multi_set($eset);
                }
            }
        }

        $options = $this->g->o;
        if (($gdsr_page == "munew" || $gdsr_page == "muedit") && $editor) {
            include(STARRATING_PATH.'options/multis/editor.php');
        } else {
            switch ($gdsr_page) {
                case "mulist":
                default:
                    include(STARRATING_PATH.'options/multis/sets.php');
                    break;
                case "murpost":
                    include(STARRATING_PATH.'options/multis/results_post.php');
                    break;
                case "murset":
                    include(STARRATING_PATH.'options/multis/results_set.php');
                    break;
            }
        }
    }

    function star_multi_results() {
        $options = $this->g->o;
        $wpv = $this->g->wp_version;
        include(STARRATING_PATH.'options/multis/results.php');
    }

    function star_menu_front() {
        if (!$this->front) {
            $this->front = true;
            $options = $this->g->o;
            $wpv = $this->g->wp_version;
            include(STARRATING_PATH.'options/front.php');
        }
    }

    function star_menu_gfx() {
        if (isset($_POST['gdsr_preview_scan'])) {
            $this->g->g = gdsrAdmFunc::gfx_scan();
            update_option('gd-star-rating-gfx', $this->g->g);
        }

        $gdsr_options = $this->g->o;
        $gdsr_bots = $this->g->bots;
        $gdsr_root_url = $this->g->plugin_url;
        $gdsr_gfx = $this->g->g;
        $gdsr_wpr8 = $this->g->wpr8_available;
        $extra_folders = $this->g->extra_folders;
        $safe_mode = $this->g->safe_mode;
        $wpv = $this->g->wp_version;
        $ginc_sizes = $this->g->ginc[0];
        $ginc_stars = $this->g->ginc[1];
        $ginc_sizes_thumb = $this->g->ginc[2];
        $ginc_stars_thumb = $this->g->ginc[3];
        $wpr8 = $this->g->wpr8;

        include(STARRATING_PATH.'options/gfx.php');
    }

    function star_menu_settings() {
        if (isset($_POST['gdsr_preview_scan'])) {
            $this->g->g = gdsrAdmFunc::gfx_scan();
            update_option('gd-star-rating-gfx', $this->g->g);
        }

        $recalculate_articles = $recalculate_comment = $recalculate_reviews = $recalculate_cmm_reviews = false;

        $gdsr_options = $this->g->o;
        $gdsr_bots = $this->g->bots;
        $gdsr_root_url = $this->g->plugin_url;
        $gdsr_gfx = $this->g->g;
        $gdsr_wpr8 = $this->g->wpr8_available;
        $extra_folders = $this->g->extra_folders;
        $safe_mode = $this->g->safe_mode;
        $wpv = $this->g->wp_version;
        $ginc_sizes = $this->g->ginc[0];
        $ginc_stars = $this->g->ginc[1];
        $wpr8 = $this->g->wpr8;

        include(STARRATING_PATH.'options/settings.php');

        if ($recalculate_articles)
            gdsrAdmDB::recalculate_articles($gdsr_oldstars, $gdsr_newstars);

        if ($recalculate_comment)
            gdsrAdmDB::recalculate_comments($gdsr_cmm_oldstars, $gdsr_cmm_newstars);

        if ($recalculate_reviews)
            gdsrAdmDB::recalculate_reviews($gdsr_review_oldstars, $gdsr_review_newstars);

        if ($recalculate_cmm_reviews)
            gdsrAdmDB::recalculate_comments_reviews($gdsr_cmm_review_oldstars, $gdsr_cmm_review_newstars);
    }

    function star_menu_t2() {
        $options = $this->g->o;
        $wpv = $this->g->wp_version;

        include(STARRATING_PATH.'code/t2/templates.php');

        if (isset($_GET["tplid"])) {
            $id = $_GET["tplid"];
            $mode = $_GET["mode"];
            include(STARRATING_PATH.'gdt2/form_editor.php');
        } else if (isset($_POST["gdsr_create"])) {
            $id = 0;
            $mode = "new";
            include(STARRATING_PATH.'gdt2/form_editor.php');
        } else {
            if (isset($_POST["gdsr_setdefaults"])) {
                gdTemplateDB::set_templates_defaults($_POST["gdsr_section"]);
            }
            if (isset($_POST["gdsr_setdepends"])) {
                gdTemplateDB::set_templates_dependes($_POST["gdsr_tpl_dep"]);
            }

            include(STARRATING_PATH.'options/templates.php');
        }
    }

    function star_menu_setup() {
        $wpv = $this->g->wp_version;
        include(STARRATING_PATH.'options/setup.php');
    }

    function star_menu_ips() {
        $options = $this->g->o;
        $wpv = $this->g->wp_version;

        include(STARRATING_PATH.'options/ips.php');
    }

    function star_menu_tools() {
        $msg = "";

        $gdsr_options = $this->g->o;
        $gdsr_gfx = $this->g->g;
        $wpv = $this->g->wp_version;

        include(STARRATING_PATH.'options/tools.php');
    }

    function star_menu_import() {
        $options = $this->g->o;
        $imports = $this->g->i;
        $wpv = $this->g->wp_version;
        include(STARRATING_PATH.'options/import.php');
    }

    function star_menu_export() {
        $options = $this->g->o;
        $wpv = $this->g->wp_version;
        include(STARRATING_PATH.'options/export.php');
    }

    function star_menu_stats() {
        $options = $this->g->o;
        $wpv = $this->g->wp_version;
        $gdsr_page = isset($_GET["gdsr"]) ? $_GET["gdsr"] : "";
        $use_nonce = $this->g->use_nonce;

        switch ($gdsr_page) {
            case "articles":
            default:
                include(STARRATING_PATH.'options/articles.php');
                break;
            case "moderation":
                include(STARRATING_PATH.'options/moderation.php');
                break;
            case "comments":
                include(STARRATING_PATH.'options/comments.php');
                break;
            case "voters":
                include(STARRATING_PATH.'options/voters.php');
                break;
        }
    }

    function star_menu_users(){
        $options = $this->g->o;
        $wpv = $this->g->wp_version;
        if (isset($_GET["gdsr"]) && $_GET["gdsr"] == "userslog")
            include(STARRATING_PATH.'options/users_log.php');
        else
            include(STARRATING_PATH.'options/users.php');
    }

    function star_menu_cats(){
        $options = $this->g->o;
        $wpv = $this->g->wp_version;
        include(STARRATING_PATH.'options/categories.php');
    }

    function star_menu_security() {
        $options = $this->g->o;
        $wpv = $this->g->wp_version;
        include(STARRATING_PATH.'options/security.php');
    }

    function star_menu_my() {
        $options = $this->g->o;
        $wpv = $this->g->wp_version;
        include(STARRATING_PATH.'options/my.php');
    }

    function star_menu_builder(){
        $options = $this->g->o;
        $wpv = $this->g->wp_version;
        $gdsr_styles = $this->g->g->stars;
        $gdsr_trends = $this->g->g->trend;
        $gdsr_thumbs = $this->g->g->thumbs;
        $gdst_multis = GDSRDBMulti::get_multis_tinymce();
        include(STARRATING_PATH.'options/builder.php');
    }

    function star_menu_gdsr2() {
        include(STARRATING_PATH.'options/gdsr2.php');
    }
}

?>