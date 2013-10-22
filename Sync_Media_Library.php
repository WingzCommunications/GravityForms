add_action('gform_after_submission', 'gform_synchronise_media_library', 10, 2);
function gform_synchronise_media_library($entry, $form) {
    if(!function_exists('wp_generate_attachment_metadata')){require_once(ABSPATH . 'wp-admin/includes/image.php');}
    $post_id = $entry['post_id'];
    $fileupload_fields = GFCommon::get_fields_by_type($form, array("fileupload"));
    error_log('before loop syncro lib');
    foreach($fileupload_fields as $field){
        $field_id = $field["id"];
        if (isset($_FILES['input_'.$field_id])){
            $file_url = $entry["$field_id"];
            $upload_to_url = wp_upload_dir();
            $file_data = file_get_contents($file_url);
            $filename = basename($file_url);
            if (wp_mkdir_p($upload_to_url['path'] . '/resumes/' . $post_id)) // can we put it there
                $file = $upload_to_url['path'] . '/resumes/' . $post_id . '/' . $filename;
            else
                $file = $upload_to_url['basedir'] . '/resumes/' . $post_id . '/' . $filename; //get the whole location
            file_put_contents($file, $file_data);
        }
        $wp_filetype = wp_check_filetype($filename, null );
        $attachment = array(
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit',
            'post_mime_type' => $wp_filetype['type']

        );
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata( $attach_id, $attach_data);
    }
}
