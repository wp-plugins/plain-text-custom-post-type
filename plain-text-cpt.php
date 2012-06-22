<?php
/*
Plugin Name:    Plain Text Custom Post Type 
Plugin URI:     http://www.nutt.net/tag/plain-text-cpt/
Version:        0.1
Author:         Ryan Nutt   
Author URI:     http://www.nutt.net
License:        GPL2
Description:    Adds a new custom post type for plain text pages. A plain text page has no formatting and can be used for JavaScript or CSS files that are editable through the WordPress admin. 
 */

new Plain_Text_CPT(); 

class Plain_Text_CPT {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_filter('user_can_richedit', array($this, 'disable_wysiwyg')); 
        add_action('media_buttons_context', array($this, 'media_buttons'));
        add_action('load-post-new.php', array($this, 'edit_page'));
        add_action('load-post.php', array($this, 'edit_page')); 
        add_action('post_submitbox_misc_actions', array($this, 'content_type_fields'));
        add_action('save_post', array($this, 'save_post'));
        add_filter('the_content', array($this, 'the_content'), 9999); 
        
        register_activation_hook(__FILE__, array($this, 'flush_rewrite'));
        register_deactivation_hook(__FILE__, array($this, 'flush_rewrite'));
        
        add_action('get_header', array($this, 'start_buffer')); 
        add_action('get_header', array($this, 'add_to_head')); 
        
        add_action('manage_posts_custom_column', array($this, 'custom_columns'), 10, 2); 
        add_action('manage_edit-plain_text_post_columns', array($this, 'manage_columns')); 
        
        wp_register_script('jquery-tabby', plugins_url('jquery.textarea.js', __FILE__), array('jquery')); 

    }
    
    public function init() { 
        $labels = array(
            'name' => __('Plain Text Files'),
            'singular_name' => __('Plain Text File'),
            'add_new' => __('Add New Text File'),
            'add_new_item' => __('Add New Text File'),
            'edit' => __('Edit'),
            'edit_item' => __('Edit Text File'),
            'new_item' => __('New Text File'),
            'view' => __('View Text File'),
            'view_item' => __('View Text File'),
            'search_items' => __('Search Text Files'),
            'not_found' => __('No Text Files Found'),
            'not_found_in_trash' => __('No Text Files Found'),
            'parent' => __('Parent Text File')
        );
        
        register_post_type('plain_text_post',
                array(
                    'labels' => $labels,
                    'description' => __('Text Files'),
                    'public' => true,
                    'publically_queryable' => false,
                    'supports' => array('title', 'editor'),
                    'rewrite' => true,
                    'capability_type' => 'post',
                    'feeds' => false,
                    'query_var' => 'txt',
                    'rewrite' => array(
                        'slug' => 'txt'
                    ),
                    'show_in_menu' => 'edit.php?post_type=page'
                ));
        flush_rewrite_rules();
    }
        
    /**
     * Remove the quick tag buttons. Setting it to '' doesn't appear to work, 
     * so we're setting it to ' ' with a space which does look to work. 
     * 
     * @todo    Find a way to get the gray bar to disappear. For now empty is
     *          good enough, but not ideal. 
     */
    public function quick_tag_settings($q) {
        $q['buttons'] = ' ';
        return $q; 
    }
    
    /** Add filter for removal of quick tag buttons */
    public function edit_page() {
        global $typenow; 
        if ('plain_text_post' == $typenow) {
            add_filter('quicktags_settings', array($this, 'quick_tag_settings'), 10, 1); 
            
            wp_enqueue_script('jquery-tabby'); 
            add_action('admin_footer', array($this, 'textarea_footer'));
        }
        
    }
    
    /** Add JS to footer to put tabby on the text area */
    public function textarea_footer() {
        ?><script type="text/javascript">
            jQuery(document).ready(function() { 
                jQuery('textarea.wp-editor-area').tabby();
                console.info(jQuery('textarea').css('-moz-tab-size')); 
            });
        </script><?php
    }
    
    /** Turn off rich text editor for this post type */
    public function disable_wysiwyg($c) {
        global $post_type;
        if ($post_type == 'plain_text_post') {
            return false; 
        }
        return $c;
    }
    
    /** Remove media button */
    public function media_buttons($c) {
        global $post_type;
        if ($post_type == 'plain_text_post') {
            return '';
        }
        return $c;
    }
    
    /** Add the content type field to the post meta box */
    public function content_type_fields() {
        global $typenow, $post;
        if ('plain_text_post' != $typenow)
            return; 
        ?>
        <div class="misc-pub-section misc-pub-section-last" style="border-top: 1px solid #eee;">
        <strong>
        <?php _e('Content Type:'); ?>
        </strong><br>
        <select name="plain_text_cpt_content_type" style="width:100%;">
            <option value="text" <?php selected(get_post_meta($post->ID, '_plain_text_cpt_content_type', true), 'text'); ?>>Plain Text</option>
            <option value="css" <?php selected(get_post_meta($post->ID, '_plain_text_cpt_content_type', true), 'css'); ?>>CSS</option>
            <option value="javascript" <?php selected(get_post_meta($post->ID, '_plain_text_cpt_content_type', true), 'javascript'); ?>>JavaScript</option>
        </select>
        <br>
        <?php _e('If left blank, content type will be <code>text/plain</code>'); ?>
        <br><br>
        <input type="checkbox" name="plain_text_insert" <?php checked(get_post_meta($post->ID, '_plain_text_insert', true)); ?>>&nbsp;
        <strong>
        <?php _e('Automatically insert into head'); ?>
        </strong>
        </div>
        <?php
    }
    
    /** Save post handler */
    public function save_post($postID) {
        global $post;
        if ($post->post_type == 'plain_text_post') {
            if (isset($_POST['plain_text_cpt_content_type'])) {
                update_post_meta($postID, '_plain_text_cpt_content_type', $_POST['plain_text_cpt_content_type'] );
            }
            $insertVal = isset($_POST['plain_text_insert']) && $_POST['plain_text_insert'] == 'on';
            update_post_meta($postID, '_plain_text_insert', $insertVal); 
        }
        
    }
    
    public function flush_rewrite() {
        flush_rewrite_rules(); 
    }
    
    /**
     * Send the output.
     * 
     * This function clears the output buffer added in {@link Plain_Text_CPT::start_buffer}
     * before outputting the unfiltered content using get_the_content. 
     */
    public function the_content($content) {
        global $post; 
        if ($post->post_type == 'plain_text_post') {
            ob_end_clean(); 
            $postType = get_post_meta($post->ID, '_plain_text_cpt_content_type', true);
            if ($postType == 'css') {
                header('Content-type: text/css');
            }
            else if ($postType == 'javascript') {
                header('Content-type: text/javascript');
            }
            else {
                header('Content-type: text/plain'); 
            }
            
            header('Last-Modified: '.date('r', strtotime($post->post_modified))); 
            
            // Needs to be unfiltered, so can't use $content
            die(get_the_content());             
        }
        return $content; 
    }
    
    /** 
     * Start a buffer if the post type is plain_text_post. When the post is output
     * the buffer will be thrown out so that there's nothing other than just the
     * plain text.
     */
    public function start_buffer() {
        global $post; 
        if ($post->post_type == 'plain_text_post') {
            ob_start(array($this, 'buffer_callback'));
            //echo 'Starting buffer'; die(); 
        }
        
    }
    
    public function buffer_callback($data) {
        return '';
    }
    
    /** Check to see if we need to enqueue any scripts or styles */
    public function add_to_head() {
        $myPosts = get_posts(array(
            'numberposts' => -1,
            'post_type' => 'plain_text_post',
            'meta_query' => array(
                array(
                    'key' => '_plain_text_insert',
                    'value' => 1,
                    'compare' => '=='
                )),
            'post_status' => 'publish'
            
        ));
        
        if (!empty($myPosts)) {
            foreach ($myPosts as $p) {
                $type = get_post_meta($p->ID, '_plain_text_cpt_content_type', true);
                if ($type == 'css') {
                    $link = get_post_permalink($p->ID);
                    wp_register_style('plain-'.$p->ID, $link);
                    wp_enqueue_style('plain-'.$p->ID); 
                }
                else if ($type == 'javascript') {
                    $link = get_post_permalink($p->ID);
                    wp_register_script('plain-'.$p->ID, $link);
                    wp_enqueue_script('plain-'.$p->ID); 
                }
            }
        }
        
    }
    
    /** Callback for the custom columns on the post list */
    public function custom_columns($column, $postID) {
        if ($column == 'type') {
            $type = get_post_meta($postID, '_plain_text_cpt_content_type', true);
            switch ($type) {
                case 'text':
                    _e('Plain Text');
                    break;
                case 'javascript':
                    _e('JavaScript');
                    break;
                case 'css':
                    _e('CSS');
                    break;
                default:
                    _e('Unknown');
                    break; 
            }
        }
        else if ($column == 'auto_insert') {
            echo (get_post_meta($postID, '_plain_text_insert', true) == 1) ? 'Yes': ''; 
        }
    }
    
    /** Add File Type column to post list for this file type */
    public function manage_columns($cols) {
        return array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Title'),
            'type' => __('File Type'),
            'auto_insert' => __('Auto Insert'),
            'date' => __('Date')
        );       
    }
    
}
?>