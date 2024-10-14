<?php
// Register AI Models Post Type
function create_ai_models_post_type() {
    $labels = array(
        'name' => 'AI Models',
        'singular_name' => 'AI Model',
        'menu_name' => 'AI Models',
        'all_items' => 'All AI Models',
        'add_new_item' => 'Add New AI Model',
        'edit_item' => 'Edit AI Model',
        'new_item' => 'New AI Model',
        'view_item' => 'View AI Model',
        'search_items' => 'Search AI Models',
        'not_found' => 'No AI models found',
        'not_found_in_trash' => 'No AI models found in trash',
        'featured_image' => 'Model Image',
        'set_featured_image' => 'Set model image',
    );

    $args = array(
        'label' => 'AI Model',
        'description' => 'Custom post type for AI Models',
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail'),
        'public' => true,
        'show_ui' => true,
        'show_in_rest' => true,  // Enables REST API
        'has_archive' => true,
        'menu_icon' => 'dashicons-admin-generic', // You can use a different dashicon if needed
        'capability_type' => 'post',
    );

    register_post_type('ai_model', $args);
}
add_action('init', 'create_ai_models_post_type');

// Add custom field (Model Type) to AI Model Post Type
function ai_model_meta_boxes() {
    add_meta_box(
        'ai_model_type',
        'AI Model Type',
        'ai_model_type_meta_box_callback',
        'ai_model',
        'normal',
        'high'  // Position it at the top
    );
}
add_action('add_meta_boxes', 'ai_model_meta_boxes');

// Callback function for the AI Model Type field
function ai_model_type_meta_box_callback($post) {
    $value = get_post_meta($post->ID, '_ai_model_type', true);
    ?>
    <label for="ai_model_type">Model Type</label>
    <input type="text" name="ai_model_type" id="ai_model_type" value="<?php echo esc_attr($value); ?>" style="width:100%; height:40px; font-size:16px; padding:10px; border:2px solid #0073aa; border-radius:5px;" />
    <?php
}

// Save AI Model Type field data
function save_ai_model_type_meta_box_data($post_id) {
    if (array_key_exists('ai_model_type', $_POST)) {
        update_post_meta($post_id, '_ai_model_type', sanitize_text_field($_POST['ai_model_type']));
    }
}
add_action('save_post', 'save_ai_model_type_meta_box_data');
?>
