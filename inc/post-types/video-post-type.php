<?php
// Register Video Post Type
function create_video_post_type() {
    $labels = array(
        'name' => 'Videos',
        'singular_name' => 'Video',
        'menu_name' => 'Videos',
        'all_items' => 'All Videos',
        'add_new_item' => 'Add New Video',
        'edit_item' => 'Edit Video',
        'new_item' => 'New Video',
        'view_item' => 'View Video',
        'search_items' => 'Search Videos',
        'not_found' => 'No videos found',
        'not_found_in_trash' => 'No videos found in trash',
        'featured_image' => 'Video Thumbnail',
        'set_featured_image' => 'Set video thumbnail',
    );

    $args = array(
        'label' => 'Video',
        'description' => 'Custom post type for videos',
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail'),
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,  // Enables REST API
        'has_archive' => true,
        'menu_icon' => 'dashicons-video-alt3',
        'capability_type' => 'post',
    );

    register_post_type('video', $args);
}
add_action('init', 'create_video_post_type');

// Add custom field (Video URL) to Video Post Type
function video_meta_boxes() {
    add_meta_box(
        'video_url',
        'Video URL',
        'video_url_meta_box_callback',
        'video',
        'normal',
        'high'  // Place it at the top
    );
}
add_action('add_meta_boxes', 'video_meta_boxes');

// Callback function for the video URL field
function video_url_meta_box_callback($post) {
    $value = get_post_meta($post->ID, '_video_url', true);
    ?>
    <label for="video_url">YouTube Video URL</label>
    <input type="text" name="video_url" id="video_url" value="<?php echo esc_attr($value); ?>" style="width:100%; height:40px; font-size:16px; padding:10px; border:1px solid #999999; border-radius:5px;" />
    <?php
}

// Save Video URL field data
function save_video_url_meta_box_data($post_id) {
    // Verify nonce, check for autosave, and user permissions (optional)
    if (array_key_exists('video_url', $_POST)) {
        $video_url = sanitize_text_field($_POST['video_url']);
        update_post_meta($post_id, '_video_url', $video_url);
        
        // Automatically set the YouTube thumbnail as the Featured Image
        set_youtube_thumbnail_as_featured($post_id, $video_url);
    }
}
add_action('save_post', 'save_video_url_meta_box_data');

// Function to set YouTube thumbnail as Featured Image
function set_youtube_thumbnail_as_featured($post_id, $youtube_url) {
    // Extract YouTube video ID
    $video_id = extract_youtube_id($youtube_url);

    if ($video_id) {
        $thumbnail_url = 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg';
        
        // Download and set the thumbnail as the Featured Image
        $image_id = insert_image_from_url($thumbnail_url);
        set_post_thumbnail($post_id, $image_id);
    }
}

// Helper function to extract YouTube video ID
function extract_youtube_id($url) {
    preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $matches);
    return isset($matches[1]) ? $matches[1] : null;
}

// Helper function to insert image from URL
function insert_image_from_url($image_url) {
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    $file = $upload_dir['path'] . '/' . $filename;

    // Save the image to the uploads directory
    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null);
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    // Insert the attachment
    $attach_id = wp_insert_attachment($attachment, $file);
    
    // Include image.php for image metadata functions
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}
?>
