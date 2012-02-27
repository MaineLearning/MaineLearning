<?php

/*
Name:    gdTemplatesT2
Library: Classes
Version: 2.4.0
Author:  Milan Petrovic
Email:   milan@gdragon.info
Website: http://www.gdragon.info/
*/

class gdTemplateDB {
    function import_templates_full($t2) {
        global $wpdb, $table_prefix;
        $sql = sprintf("TRUNCATE TABLE %s%s", $table_prefix, STARRATING_TPLT2_TABLE);
        $wpdb->query($sql);
        $t2 = str_replace("%%T2_TABLE_NAME%%", $table_prefix.STARRATING_TPLT2_TABLE, $t2);
        $wpdb->query($t2);
    }

    function import_templates_own($t2) {
        global $wpdb, $table_prefix;
        $templates = array();
        foreach ($t2 as $tpl) {
            $parts = explode("|", $tpl, 4);
            $sql = sprintf("insert into %s%s (`section`, `name`, `description`, `elements`, `preinstalled`, `default`) values ('%s', '%s', '%s', '%s', '0', '0')",
                $table_prefix, STARRATING_TPLT2_TABLE, $parts[0], $parts[1], $parts[2], $parts[3]);
            $wpdb->query($sql);
            $tpl_id = $wpdb->insert_id;
            $templates[] = array("section" => $parts[0], "tpl_id" => sprintf("%s", $tpl_id));
        }
        if (count($templates) > 0) {
            include(STARRATING_PATH.'code/t2/templates.php');
            $depend = array();
            foreach ($tpls->tpls as $tpl) {
                $section = $tpl->code;
                $sql = sprintf("select template_id from %s%s where section = '%s' and preinstalled = '1'", $table_prefix, STARRATING_TPLT2_TABLE, $section);
                $tpl_id = intval($wpdb->get_var($sql));
                $depend[$section] = $tpl_id;
            }
            foreach ($templates as $tpl) {
                $dep = array();
                $t = $tpls->get_list($tpl["section"]);
                foreach ($t->tpls as $tag) {
                    $s = $tag->code;
                    $dep[$s] = sprintf("%s", $depend[$s]);
                }
                if (count($dep) > 0) {
                    $sql = sprintf("update %s%s set dependencies = '%s' where template_id = %s",
                        $table_prefix, STARRATING_TPLT2_TABLE, serialize($dep), $tpl["tpl_id"]);
                    $wpdb->query($sql);
                }
            }
        }
    }

    function rewrite_dependencies($section, $id) {
        global $wpdb, $table_prefix;
        include($this->plugin_path.'code/t2/templates.php');
        $sections = $tpls->find_sections_depending($section);
        $sql = sprintf("select template_id, dependencies from %s%s where section in ('%s')", $table_prefix, STARRATING_TPLT2_TABLE, join("', '", $sections));
        $rows = $wpdb->get_results($sql);
        foreach ($rows as $row) {
            $dep = unserialize($row->dependencies);
            $dep[$section] = $id;
            $sql = sprintf("update %s%s set dependencies = '%s' where template_id = %s", $table_prefix, STARRATING_TPLT2_TABLE, serialize($dep), $row->template_id);
            $wpdb->query($sql);
        }
    }

    function rewrite_defaults($code, $id) {
        global $wpdb, $table_prefix;

        $sql = sprintf("update %s%s set `default` = '0' where section = '%s'", $table_prefix, STARRATING_TPLT2_TABLE, $code);
        $wpdb->query($sql);
        $sql = sprintf("update %s%s set `default` = '1' where template_id = %s", $table_prefix, STARRATING_TPLT2_TABLE, $id);
        $wpdb->query($sql);
    }

    function get_templates($section = '', $default_sort = false, $only_default = false) {
        global $wpdb, $table_prefix;
        if ($section != '') $section = sprintf(" WHERE section = '%s'", $section);
        $default_sort = $default_sort ? "`default` desc, preinstalled desc, " : "";
        $default_limit = $only_default ? " LIMIT 0, 1" : "";

        $sql = sprintf("select * from %s%s%s order by %stemplate_id asc%s", $table_prefix, STARRATING_TPLT2_TABLE, $section, $default_sort, $default_limit);
        if ($only_default) return $wpdb->get_row($sql);
        return $wpdb->get_results($sql);
    }

    function get_template($id) {
        global $wpdb, $table_prefix;
        $sql = sprintf("SELECT * FROM %s%s WHERE `template_id` = %s",
            $table_prefix, STARRATING_TPLT2_TABLE, $id);
        return $wpdb->get_row($sql);
    }

    function get_templates_dep() {
        global $wpdb, $table_prefix;

        $sql = sprintf("select * from %s%s order by template_id", $table_prefix, STARRATING_TPLT2_TABLE);
        return $wpdb->get_results($sql);
    }

    function get_templates_paged($section = '', $start = 0, $limit = 20) {
        global $wpdb, $table_prefix;
        if ($section != '') $section = sprintf(" WHERE section = '%s'", $section);

        $sql = sprintf("select * from %s%s%s limit %s, %s", $table_prefix, STARRATING_TPLT2_TABLE, $section, $start, $limit);
        return $wpdb->get_results($sql);
    }

    function get_templates_count($section = '') {
        global $wpdb, $table_prefix;
        if ($section != '') $section = sprintf(" WHERE section = '%s'", $section);

        $sql = sprintf("select count(*) from %s%s%s", $table_prefix, STARRATING_TPLT2_TABLE, $section);
        return $wpdb->get_var($sql);
    }

    function set_templates_dependes($post) {
        global $wpdb, $table_prefix;

        foreach ($post as $id => $dep) {
            $sql = sprintf("update %s%s set dependencies = '%s' where template_id = %s", $table_prefix, STARRATING_TPLT2_TABLE, serialize($dep), $id);
            $wpdb->query($sql);
        }
    }

    function set_templates_defaults($post) {
        global $wpdb, $table_prefix;

        foreach ($post as $code => $value) {
            $sql = sprintf("update %s%s set `default` = '0' where section = '%s'", $table_prefix, STARRATING_TPLT2_TABLE, $code);
            $wpdb->query($sql);
            $sql = sprintf("update %s%s set `default` = '1' where template_id = %s", $table_prefix, STARRATING_TPLT2_TABLE, $value);
            $wpdb->query($sql);
        }
    }

    function edit_template($general, $elements) {
        global $wpdb, $table_prefix;
        $sql = sprintf("UPDATE %s%s SET `section` = '%s', `name` = '%s', `description` = '%s', `elements` = '%s', `dependencies` = '%s', `preinstalled` = '%s' WHERE `template_id` = %s",
            $table_prefix, STARRATING_TPLT2_TABLE, $general["section"], $general["name"], $general["description"], serialize($elements), serialize($general["dependencies"]), $general["preinstalled"], $general["id"]);
        $wpdb->query($sql);
        return $general["id"];
    }

    function delete_template($id) {
        global $wpdb, $table_prefix;
        $sql = sprintf("DELETE FROM %s%s WHERE `template_id` = %s",
            $table_prefix, STARRATING_TPLT2_TABLE, $id);
        return $wpdb->query($sql);
    }

    function add_template($general, $elements) {
        global $wpdb, $table_prefix;
        $sql = sprintf("INSERT INTO %s%s (`section`, `name`, `description`, `elements`, `dependencies`, `preinstalled`, `default`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '0')",
            $table_prefix, STARRATING_TPLT2_TABLE, $general["section"], $general["name"], isset($general["description"]) ? $general["description"] : "", serialize($elements), serialize($general["dependencies"]), $general["preinstalled"]);
        $wpdb->query($sql);
        return $wpdb->insert_id;
    }
}

class gdTemplateHelper {
    function render_templates_section($section, $name, $selected = "0", $width = 205) {
        $templates = gdTemplateDB::get_templates($section, true);
        ?>
<select style="width: <?php echo $width ?>px;" name="<?php echo $name; ?>" id="<?php echo $name; ?>">
        <?php
        foreach ($templates as $t) {
            if ($t->template_id == $selected) $select = ' selected="selected"';
            else $select = '';
            echo sprintf('<option value="%s"%s>%s</option>', $t->template_id, $select, $t->name);
        }
        ?>
</select>
        <?php
    }

    function render_templates_sections($name, $section, $empty = true, $selected = "") {
        ?>
<select name="<?php echo $name; ?>" id="<?php echo $name; ?>">
<?php if ($empty) { ?><option value=""<?php echo $selected == '' ? ' selected="selected"' : ''; ?>><?php _e("All Sections", "gd-star-rating"); ?></option><?php } ?>
        <?php
            foreach ($section as $s) {
                echo sprintf('<option value="%s"%s>%s</option>', $s["code"], ($selected == $s["code"] ? ' selected="selected"' : ''),  $s["name"]);
            }
        ?>
</select>
        <?php
    }

    function render_dependency($secs, $tpls, $dep, $id, $tid) {
        echo '<tr><td>'.$secs[$dep].':</td><td style="text-align: right;">';
        echo '<select style="width: 240px" name="gdsr_tpl_dep['.$tid.']['.$dep.']">';
        foreach ($tpls as $tpl) {
            if ($tpl->section == $dep) {
                $select = $tpl->template_id == $id ? ' selected="selected"' : '';
                echo sprintf('<option value="%s"%s>%s</option>', $tpl->template_id, $select, $tpl->name);
            }
        }
        echo '</select></td></tr>';
    }

    function prepare_dependencies($secs, $tpls, $dep, $tid) {
        echo '<table class="tplclean">';
        foreach ($dep as $d => $id) {
            gdTemplateHelper::render_dependency($secs, $tpls, $d, $id, $tid);
        }
        echo '</table>';
    }
}

class gdTemplateRender {
    var $tpl;
    var $dep;
    var $elm;
    var $tag;
    var $custom;

    function gdTemplateRender($id, $section) {
        $this->dep = $this->tag = array();
        $this->tpl = wp_gdtpl_get_template($id);

        if (!is_object($this->tpl) || $this->tpl->section != $section) {
            $t = gdTemplateDB::get_templates($section, true, true);
            $id = $t->template_id;
            $this->tpl = wp_gdtpl_get_template($id);
        }

        $dependencies = unserialize($this->tpl->dependencies);
        if (is_array($dependencies)) {
            foreach ($dependencies as $key => $value) $this->dep[$key] = new gdTemplateRender($value, $key);
        }

        $this->elm = unserialize($this->tpl->elements);
        if (is_array($this->elm)) {
            foreach($this->elm as $key => $value) {
                $this->tag[$key] = array();
                $this->custom[$key] = array();
                $this->custom[$key] = wp_get_custom_tags($value);
                preg_match_all('(%.+?%)', $value, $matches, PREG_PATTERN_ORDER);
                if (is_array($matches[0])) $this->tag[$key] = $matches[0];
            }
        }
    }
}

if (!class_exists("gdTemplateElement")) {
    class gdTemplateElement {
        var $tag;
        var $description;
        var $tpl;

        function gdTemplateElement($t, $d) {
            $this->tag = $t;
            $this->description = $d;
            $this->tpl = -1;
        }
    }

    class gdTemplatePart {
        var $name;
        var $code;
        var $description;
        var $elements;
        var $size;

        function gdTemplatePart($n, $c, $d, $s = "single") {
            $this->name = $n;
            $this->code = $c;
            $this->description = $d;
            $this->size = $s;
            $this->elements = array();
        }
    }

    class gdTemplateTpl {
        var $code;
        var $tag;

        function gdTemplateTpl($c, $t) {
            $this->code = $c;
            $this->tag = $t;
        }
    }

    class gdTemplate {
        var $code;
        var $section;
        var $elements;
        var $parts;
        var $tag;
        var $tpls;
        var $tpls_tags;

        function gdTemplate($c, $s, $t = "") {
            $this->code = $c;
            $this->section = $s;
            $this->tag = $t;
            $this->elements = array();
            $this->parts = array();
            $this->tpls = array();
            $this->tpls_tags = array();
        }

        function add_template($c, $t) {
            $this->tpls[] = new gdTemplateTpl($c, $t);
            $this->tpls_tags[] = $t;
        }

        function add_element($t, $d) {
            $tpl = new gdTemplateElement($t, $d);
            if (in_array($t, $this->tpls_tags)) {
                $k = array_keys($this->tpls_tags, $t);
                if (count($k) == 1) $tpl->tpl = $k[0];
            }
            $this->elements[] = $tpl;
        }

        function add_part($n, $c, $d, $parts = array(), $s = "single") {
            $part = new gdTemplatePart($n, $c, $d, $s);
            $part->elements = $parts;
            $this->parts[] = $part;
        }
    }

    class gdTemplates {
        var $tpls;

        function gdTemplates() {
            $this->tpls = array();
        }

        function add_template($t) {
            $this->tpls[] = $t;
        }

        function get_list($section) {
            foreach ($this->tpls as $t) {
                if ($t->code == $section) return $t;
            }
            return null;
        }

        function list_sections() {
            $sections = array();
            $listed = array();
            foreach ($this->tpls as $t) {
                $code = $t->code;
                $name = $t->section;
                if (!in_array($code, $listed)) {
                    $listed[] = $code;
                    $sections[] = array("code" => $code, "name" => $name);
                }
            }
            return $sections;
        }

        function find_sections_depending($code) {
            $sections = array();
            foreach ($this->tpls as $t) {
                $found = false;
                if (count($t->tpls) > 0) {
                    foreach ($t->tpls as $x) {
                        if ($x->code == $code) {
                            $found = true;
                            break;
                        }
                    }
                }
                if ($found) $sections[] = $t->code;
            }
            return $sections;
        }

        function find_template_tag($code) {
            $tag = "";
            foreach ($this->tpls as $t) {
                if ($t->code == $code) {
                    $tag = $t->tag;
                    break;
                }
            }
            return $tag;
        }

        function list_sections_assoc() {
            $sections = array();
            $listed = array();
            foreach ($this->tpls as $t) {
                $code = $t->code;
                $name = $t->section;
                if (!in_array($code, $listed)) {
                    $listed[] = $code;
                    $sections[$code] = $name;
                }
            }
            return $sections;
        }
    }
}

if (!function_exists("wp_get_custom_tags")) {
    function wp_get_custom_tags($rendering) {
        preg_match_all('(%CUSTOM_.+?%)', $rendering, $matches, PREG_PATTERN_ORDER);
        if (is_array($matches[0])) return $matches[0];
        else return array();
    }
}

function wp_gdtpl_get_template($template_id) {
    global $gdsr_cache_templates;

    $tpl = is_object($gdsr_cache_templates) ? $gdsr_cache_templates->get($template_id) : null;
    if (!is_null($tpl)) return $tpl;
    else {
        $tpl = gdTemplateDB::get_template($template_id);
        if (is_object($gdsr_cache_templates))
            $gdsr_cache_templates->set($template_id, $tpl);
        return $tpl;
    }
}

function wp_gdtpl_cache_template($template) {
    global $gdsr_cache_templates;

    if (is_object($gdsr_cache_templates))
        $gdsr_cache_templates->set($template->template_id, $template);
}

?>