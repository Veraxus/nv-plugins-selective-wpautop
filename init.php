<?php
/*
Plugin Name: Selective Autoformatting (wpautop)
Plugin URI: http://nouveauframework.com/plugins/
Description: Allows WordPress's built-in auto-formatting "feature" to be disabled on a post-specific case-by-case basis.
Author: Matt Van Andel
Version: 1.1
Author URI: http://mattstoolbox.com/
License: GPLv2 or later
*/

//Initialize the plugin
NV_Selective_WPAutoP::init();

/**
 * This controls the Selective Autop
 */
class NV_Selective_WPAutoP {

    /**
     * Hook everything...
     */
    public static function init() {
        // Enable the built-in toggle-auto-formatting feature
        add_action('add_meta_boxes' , array( __CLASS__, 'metaBoxInit' ));
        add_action('save_post'      , array( __CLASS__, 'metaBoxSave' ));

        //Enable filtering
        add_filter('the_content'    , array( __CLASS__, 'postFilter' ), -1);
        add_filter('the_excerpt'    , array( __CLASS__, 'postFilter' ), -1);

        //Enable help text
        add_action('admin_head'     , array( __CLASS__,'help') );
    }

    /**
     * This is one of two functions that drive Nouveau's "Auto-formatting" toggle option for post content. This function
     * generates the visible meta-box on post content's edit pages. The box presents a single check box. When checked,
     * that post content will not use wpautop()
     *
     * @see self::meta_wpautop_save()
     * @see add_action('add_meta_boxes',$func)
     * @since Nouveau 1.0
     */
    public static function metaBoxInit() {

        //Get all registered post types
        $post_types = get_post_types();

        //Ensure the meta box is added to ALL content types
        foreach ( $post_types as $type ) {

            //WPAUTOP TOGGLE META BOX
            add_meta_box(
                'wpautop_toggle', //Unique id for this meta box
                __( 'Auto-formatting', 'nouveau' ), //Visible title of the widget
                array(
                    'NV_SelectiveAutoP', //Use the CallFile class to fetch files instead of a function...
                    'require_meta_box', //Load nouveau/admin/metaboxes/toggleautop (code.php + view.php)
                ),
                $type, //The name of each type screen
                'side', //Where should this appear by default
                'high' //Should this show high or low on the sidebar?
            );

        }
        unset( $type );

    }


    /**
     * This verifies the submission of the "Auto-formatting" toggle box for post content, and saves the meta field data
     * every time the post type's edit data is saved.
     *
     * @see self::meta_wpautop_box()
     * @see add_action('save_post',$func)
     *
     * @param int $post_id Post id is passed automatically by the save_post hook.
     * @return bool
     */
    public static function metaBoxSave( $post_id ) {

        //Don't save meta boxes if the page is merely autosaving
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return false;
        }

        //Don't save meta boxes if user doesn't have adequate rights
        if ( ! current_user_can( 'edit_posts' ) ) {
            return false;
        }

        //SAVE TOGGLEAUTOP META
        if ( ! empty( $_REQUEST[ 'toggle_wpautop_nonce' ] ) && wp_verify_nonce( $_REQUEST[ 'toggle_wpautop_nonce' ], 'toggle_wpautop' ) ) {
            //save plugin fields using update_post_meta()
            update_post_meta(
                $post_id, //post id
                'toggle_wpautop', //meta key
                isset( $_REQUEST[ 'toggle_wpautop' ] ) //meta value ( isset() for bool )
            );
        }


    }

    /**
     * Checks if the current post has auto-formatting disabled immediately before filtering takes place. If so, the
     * wpautop filter is disabled for this post. To ensure consistent behavior, we are using a filter as an action.
     *
     * @see add_action('add_meta_boxes',$func)
     * @since Nouveau 1.0
     */
    public static function postFilter( $content ) {
        global $post;

        if ( get_post_meta( $post->ID, 'toggle_wpautop', true ) ) {
            remove_filter( 'the_content', 'wpautop' );
            remove_filter( 'the_excerpt', 'wpautop' );
        }

        return $content;
    }

    /**
     * This loads the meta box template file.
     */
    public static function requireMetaBox(){
        require('templates/metabox.php');
    }

    /**
     * Customizes help text for the admin.
     *
     * Used by hook: admin_head
     *
     * @see add_action('admin_head',$func)
     * @global WP_Screen $current_screen Information about the current admin screen
     * @since Nouveau 1.0
     */
    public static function help() {

        global $wp_meta_boxes;
        $current_screen = get_current_screen();

        //Add new help text
        switch ( $current_screen->base ) {

            case 'post':
            case 'edit':
            case 'add':
                get_current_screen()->add_help_tab( array(
                    'id'      => 'selectiveautop',
                    'title'   => __( 'Auto-formatting', 'nouveau' ),
                    'content' => '<p>' . __( "To prevent WordPress from automatically adding formatting to this post, locate the <b>Auto-formatting</b> panel and check the box next to <em>Disable auto-formatting</em>.", 'nouveau' ) . '</p>' .
                        '<p>' . sprintf(__( 'You can also set Auto-formatting to be disabled by default (for new posts only) under <a href="%s">Settings > Writing</a> in your WordPress admin.', 'nouveau' ), admin_url('options-writing.php') ) . '</p>',
                ) );
                break;

            default:
                break;
        }

    }

}