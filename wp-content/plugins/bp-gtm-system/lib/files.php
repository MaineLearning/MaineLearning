<?php

// get all allowed file types
function bp_gtm_files_types($all = false) {
    $types['archives'] = array('gz', 'zip', 'rar', '7z', 'tar');
    $types['media'] = array('bmp', 'gif', 'jpeg', 'jpg', 'png', 'mp3', 'mov', 'avi', '3gp', 'mp4', 'wav', 'ogg', 'flv');
    $types['documents'] = array('pdf', 'djvu', 'txt', 'rtf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx');
    $types['other'] = array('xml', 'json', 'css');

    if ($all)
        $types = array('xml', 'json', 'css', 'gz', 'zip', 'rar', '7z', 'tar', 'pdf', 'djvu', 'txt', 'rtf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'bmp', 'gif', 'jpeg', 'jpg', 'png', 'mp3', 'mov', 'avi', '3gp', 'mp4', 'wav', 'ogg', 'flv');

    return $types;
}

add_action('bp_gtm_discuss_new_reply_after', 'bp_gtm_files_form');
add_action('bp_after_gtm_project_create', 'bp_gtm_files_form');
add_action('bp_after_gtm_task_create', 'bp_gtm_files_form');

function bp_gtm_files_form($bp_gtm) {
    if (!empty($bp_gtm['files']) && $bp_gtm['files'] == 'on') {
        echo '<div id="gtm-files">
                      <div class="gtm_files">
                          <h5>' . __('Upload Files', 'bp_gtm') . '</h5>
                          <p>' . __('Select files you want to add to the project.', 'bp_gtm') . '</p>
                          <div class="single_file first"><input type="file" name="gtmFile_1" id="gtmFile" >
                          <textarea name="description[gtmFile_1]"></textarea></div>
                          <div class="add_file"><input type="button" name="add_new" value="' . __('Add new', 'bp_gtm') . '" /></div>
                          <div class="clear"></div>
                      </div>
                  </div>';
        echo '<div id="additional-file-info">
                    <div class="additional-file-info">
                        <span>' . __('Allowed file types', 'bp_gtm') . '</span>
                 ';
        foreach ($bp_gtm['files_types'] as $type) {
            echo '<span>.' . $type . '</span>';
        }
        echo '</div>';
        echo '<div class="additional-file-info">
                            <span>' . __('Maximum size of uploaded file', 'bp_gtm') . '</span>
                            <span>' . round($bp_gtm['files_size'] / 1024, 2) . ' Mb</span>';
        echo '</div>
                   </div>';
    }
}

add_action('bp_gtm_discuss_after_content', 'bp_gtm_files_discuss_display', 10, 3);
add_action('bp_gtm_project_after_content', 'bp_gtm_files_discuss_display', 10, 3);
add_action('bp_gtm_task_after_content', 'bp_gtm_files_discuss_display', 10, 3);

function bp_gtm_files_discuss_display($post, $type, $discuss_exist) {
    global $wpdb, $bp;
    if (!$discuss_exist) {
        $project = 'AND discuss_id=0';
    }
//    var_dump($discuss_exist);die;
    $files = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bp->gtm->table_files} WHERE {$type}_id = {$post->id} $project"));
    if (!empty($files)) {
        echo '<div class="clear-both"></div><h4>'.sprintf(__('List of %s documents.', 'bp_gtm'), $type).'</h4><div class="gtm_files_list"><ul>';
        if ($discuss_exist) {
            foreach ($files as $file) {
                echo '<li>' . bp_gtm_file_link($file) . '</li>';
            }
        } else {
            foreach ($files as $file) {
                echo '<li>' . bp_gtm_discuss_file_link($file) . '</li>';
            }
        }
        echo '</ul></div>';
    }
}

add_action('bp_gtm_save_discussion_post', 'bp_gtm_files_save', 1, 6);

function bp_gtm_files_save($elem_type, $owner_id, $post_id, $task_id, $project_id, $group_id) {
    $return = false;

    if (!empty($_FILES)) {
        global $bp, $wpdb;
        $bp_gtm = get_option('bp_gtm');

        $allowed_filetypes = $bp_gtm['files_types'];

        if ($elem_type == 'task')
            $elem_id = $task_id;
        elseif ($elem_type == 'project')
            $elem_id = $project_id;
        else
            $elem_id = $post_id;

        $upload_path = WP_CONTENT_DIR . '/uploads/gtm/files/' . $elem_type . '/'; // where to upload
        if (!file_exists($upload_path))
            wp_mkdir_p($upload_path);

        foreach ($_FILES as $form => $file) {
            $array = array();
            if (!empty($file['size'])) {
                preg_match('/\.(\S{2,4})$/i', $file['name'], $array);
                if (!in_array($array[1], $allowed_filetypes)) { //not allowed file
                    bp_core_add_message(sprintf(__('You cannot upload %s. This file type is not supported.', 'bp_gtm'), $file['name']), 'error');
                    unset($_FILES[$form]);
                } else if ($file['size'] > $bp_gtm['files_size'] * 1024) {
                    bp_core_add_message(sprintf(__('You cannot upload %s-files. File size is larger than the maximum.', 'bp_gtm'), $file['name']), 'error');
                    unset($_FILES[$form]);
                }
            }
        }
        foreach ($_FILES as $key => $file) {
            if (empty($file['name']))
                continue;
            if (move_uploaded_file($file['tmp_name'], $upload_path . str_replace(' ', '_', $elem_id . '_' . $file['name']))) {
                $wpdb->insert($bp->gtm->table_files, array(
                    'task_id' => $task_id,
                    'project_id' => $project_id,
                    'discuss_id' => $post_id,
                    'owner_id' => $owner_id,
                    'group_id' => $group_id,
                    'path' => '/uploads/gtm/files/' . $elem_type . '/' . str_replace(' ', '_', $elem_id . '_' . $file['name']),
                    'date_uploaded' => time(),
                    'description' => $_POST['description'][$key],
                        ), array('%d', '%d', '%d', '%d', '%d', '%s', '%d', '%s'));
                $return['file_id'][] = $wpdb->insert_id;
            } else {
                bp_core_add_message(sprintf(__('There was an error uploding %s. Do you have enough rights?', 'bp_gtm'), $file['name']), 'error');
            }
        }

        return $return;
    }
    return $return;
}

add_action('admin_init', 'file_meta_box');

function file_meta_box() {
    add_meta_box('bp-gtm-admin-files', __('Tasks/Projects/Discussion Posts Files Management', 'bp_gtm'), 'on_bp_gtm_admin_files', 'buddypress_page_bp-gtm-admin', 'normal', 'core');
}

function on_bp_gtm_admin_files() {
    $bp_gtm = get_option('bp_gtm');
    echo '<p>' . __('If you want your users and group members upload files into tasks, projects and discussion post, you can set all the options here.', 'bp_gtm') . '</p>';
    echo '<p>' . __('First of all you need to decide - do you need file management?', 'bp_gtm') . '</p>';
    echo '<p><input name="bp_gtm_files" id="bp_gtm_files_on" type="radio" value="on" ' . ('on' == $bp_gtm['files'] ? 'checked="checked" ' : '') . '/> <label for="bp_gtm_files_on">' . __('Enable', 'bp_gtm') . '</label></p>
            <p><input name="bp_gtm_files" id="bp_gtm_files_off" type="radio" value="off" ' . ('off' == $bp_gtm['files'] ? 'checked="checked" ' : '') . '/> <label for="bp_gtm_files_off">' . __('Disable', 'bp_gtm') . '</label></p>';

    echo '<hr />';

    echo '<p>' . __('Up to how many files would you like to allow users to upload for each task, project or discussion post?', 'bp_gtm') . '</p>';
    echo '<p><input name="bp_gtm_files_count" id="bp_gtm_files_count"  value="' . $bp_gtm['files_count'] . '" /> &rarr; ' . __('Should be numeric, otherwise will not be saved. Set 0 for unlimited number.', 'bp_gtm');

    echo '<hr />';

    echo '<p>' . __('Which file types would you like users have ability to upload?', 'bp_gtm') . '</p>';
    echo '<p>';
    $all_types = bp_gtm_files_types();
    $i = 0;
    foreach ($all_types as $slug => $types) {
        if ($slug == 'media')
            $name = __('Media', 'bp_gtm');
        elseif ($slug == 'archives')
            $name = __('Archives', 'bp_gtm');
        elseif ($slug == 'documents')
            $name = __('Documents', 'bp_gtm');
        else
            $name = __('Other', 'bp_gtm');
        echo '<p><strong>' . $name . ': </strong>';

        foreach ($types as $type) {
            $checked = '';
            if (!empty($bp_gtm['files_types']) && in_array($type, $bp_gtm['files_types'])) {
                $checked = 'checked="checked"';
            }
            echo ' <input type="checkbox" name="bp_gtm_files_types[]" id="bp_gtm_files_types_' . $i . '" ' . $checked . ' value="' . $type . '" /><label for="bp_gtm_files_types_' . $i . '">' . $type . '</label>;';
            $i++;
        }
        echo '</p>';
    }
    echo '</p>';

    echo '<hr />';

    echo '<p>' . __('What is the maximum size of uploaded file?', 'bp_gtm') . '</p>';
    $upload_size_unit = wp_max_upload_size();
    $sizes = array('KB', 'MB', 'GB');
    for ($u = -1; $upload_size_unit > 1024 && $u < count($sizes) - 1; $u++)
        $upload_size_unit /= 1024;
    if ($u < 0) {
        $upload_size_unit = 0;
        $u = 0;
    } else {
        $upload_size_unit = (int) $upload_size_unit;
    }
    echo '<p><input name="bp_gtm_files_size" id="bp_gtm_files_size"  clas="text-right" value="' . $bp_gtm['files_size'] . '" />KB &rarr; ' . sprintf(__('Please remember that 1MB = 1024KB. Should be less than WordPress limit: %d%s', 'bp_gtm'), $upload_size_unit, $sizes[$u]);
}

/**
 * Get all existing files
 * @return array of files for discuss, project and tasks
 */
function bp_gtm_get_all_files() {
    global $bp, $wpdb;
    $files = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$bp->gtm->table_files}"));
    return $files;
}

/**
 * Get GTM uploads path
 */
function bp_gtm_discuss_file_link($file) {
    $date_format = get_option('date_format');
    $name = str_replace('/', '', substr($file->path, strrpos($file->path, '/'), strlen($file->path) - 1)); // what's the name
    $path = bp_gtm_file_path($file->path);
    $user = bp_core_get_userlink($file->owner_id);
    return '<a href="' . $path . '">' . $name . '</a> Uploaded by 
        <span class="file_uploader">' . $user . '</span> at <span class="file_uploader">' . date($date_format, $file->date_uploaded) . '</span>
            <span class="edit_buttons"><span class="edit_description">' . __('Edit description', 'bp_gtm') . '</span>
            | <span class="delete_file_discussion">' . __('Delete file', 'bp_gtm') . '</span></span><br /> 
            <span class="file_description">' . $file->description . '</span>
            <span class="hidden">
                <textarea name="file_description" id="description_' . $file->id . '">' . $file->description . '</textarea><br />
                <input type="button" class="submit_description" name="submit" value="' . __('Update Description', 'bp_gtm') . '">
            </span>';
}

function bp_gtm_file_link($file) {
    if(is_object($file)){
        $file_path = $file->path;
    } else {
        $file_path = $file;
    }
    $name = str_replace('/', '', substr($file_path, strrpos($file_path, '/'), strlen($file_path) - 1)); // what's the name
    $path = bp_gtm_file_path($file_path); 
    return '<a href="' . $path . '">' . $name . '</a>';
}

/**
 * Get GTM uploads path
 */
function bp_gtm_file_path($file_path) {
    return apply_filters('bp_gtm_get_file_path', WP_CONTENT_URL . $file_path);
}

/**
 * Get GTM uploads path
 */
function bp_gtm_file_dir($file_path) {
    return apply_filters('bp_gtm_get_file_path', WP_CONTENT_DIR . $file_path);
}

/**
 * Get type upload type(discuss, project or discuss)
 */
function bp_gtm_get_upload_target($file, $gtm_link) {
    if (!empty($file->discuss_id)) {
        if (!empty($file->task_id)) {
            $id = $file->task_id;
            $type = 'tasks';
        } else {
            $id = $file->project_id;
            $type = 'projects';
        }
        $view = 'discuss';
    } else if (!empty($file->task_id)) {
        $id = $file->task_id;
        $view = $type = 'tasks';
    } else if (!empty($file->project_id)) {
        $id = $file->project_id;
        $view = $type = 'projects';
    }
    ?>
    <a class="topic-title" href="<?php echo $gtm_link . '/' . $type . '/view/' . $id ?>" title="<?php _e('Permalink', 'bp_gtm') ?>">
        <?php echo ucfirst($view); ?>
    </a>
    <?php
//    return $type;
}