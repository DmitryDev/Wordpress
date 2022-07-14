<?php
/*
 Plugin Name: Search By Slug
 License: GPLv3 (license.txt)
 Description: WP plugin for searching post/pages by slug in admin area only
 Author: Dmitry Nazarenko
 Version: 1.0.0
*/

if(!defined('WP_Search_Slug')) {
    class WP_Search_Slug {
        function __construct() {

            add_filter('posts_where' , array('WP_Search_Slug', 'posts_search_slug'), 10, 2);
            add_action('pre_get_posts',array('WP_Search_Slug', 'search_filter'),10,2);
        }

        /**
         * Modify search term and where clause for request in admin panel
         *
         * @param string $where
         * @param WP_Query $wp_query
         *
         * @return string
         */
        public static function posts_search_slug(string $where, WP_Query $wp_query) {
            global $pagenow;

            $post_type = '';
            if(isset($_GET['post_type'])) {
                $post_type = htmlspecialchars(strip_tags($_GET['post_type']));
            }

            $all_post_types = get_post_types();
            $search_term = '';
            if( isset($_GET['s']) && !empty(trim($_GET['s']))) {
                $search_term_arr = explode('slug:',trim($_GET['s']));
                if (count($search_term_arr) > 1) {
                    $search_term = $wp_query->query_vars['s'];
                }
            }
            if('admin-ajax.php' === $pagenow) {
                $search_term = $wp_query->query_vars['s'];
            }
            if(is_admin() && $pagenow === 'edit.php'  && !empty($search_term) && is_string($search_term)
                && strlen($search_term) && (in_array($post_type, $all_post_types)) || $pagenow === 'admin-ajax.php')
            {
                global $wpdb;

                $like_esc = '%' . $wpdb->esc_like($search_term) . '%';
                $like_term = $wpdb->prepare("({$wpdb->posts}.post_name LIKE %s)", $like_esc);
                $like_search_pattern = $wpdb->prepare("({$wpdb->posts}.post_title LIKE %s)", $like_esc);
                $like_search_replace = " " . $like_search_pattern . " OR " . $like_term . " ";

                $where = str_replace($like_search_pattern, $like_search_replace, $where);
            }

            return $where;
        }

        /**
         * @param WP_Query $query
         *
         * @return void
         */
        public static function search_filter( WP_Query $query) {
            global $pagenow;
            if ( (is_admin() && $query->is_main_query()) || $pagenow ==='admin-ajax.php' ) {
                if ($query->is_search) {
                    $replacement = str_replace( 'slug:', '', $query->query_vars['s'] );
                    $query->set( 's', $replacement );
                }
            }
        }
    }

    new WP_Search_Slug();
}
?>