<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Disciple_Tools_Data_Top_Off_Endpoints
{
    /**
     * @todo Set the permissions your endpoint needs
     * @link https://github.com/DiscipleTools/Documentation/blob/master/theme-core/capabilities.md
     * @var string[]
     */
    public $permissions = [ 'access_contacts', 'dt_all_access_contacts', 'view_project_metrics' ];


    /**
     * @todo define the name of the $namespace
     * @todo define the name of the rest route
     * @todo defne method (CREATABLE, READABLE)
     * @todo apply permission strategy. '__return_true' essentially skips the permission check.
     */
    //See https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
    public function add_api_routes() {
        $namespace = 'disciple-tools-data-top-off/v1';

        register_rest_route(
            $namespace, '/update_gender/(?P<id>\d+)/(?P<gender>\w+)', [
                'methods'  => 'POST',
                'callback' => [ $this, 'update_gender' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );

        register_rest_route(
            $namespace, '/update_location/(?P<id>\d+)/(?P<location>\w+)', [
                'methods' => 'POST',
                'callback' => [ $this, 'update_location' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );

        register_rest_route(
            $namespace, '/update_name/(?P<id>\d+)/(?P<name>.+)', [
                'methods' => 'POST',
                'callback' => [ $this, 'update_name' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );

        register_rest_route(
            $namespace, '/get_auto_tags/(?P<id>\d+)', [
                'methods' => 'GET',
                'callback' => [ $this, 'get_auto_tags' ],
                'permission_callback' => function( WP_REST_Request $request ) {
                    return $this->has_permission();
                },
            ]
        );
    }

    public function update_gender( WP_REST_Request $request ) {
        $params = $request->get_params();
        $id = esc_sql( $params['id'] );
        $gender = esc_sql( $params['gender'] );
        $genders_list = [ 'male', 'female' ];
        if ( ! in_array( $gender, $genders_list ) ) {
            return false;
        }
        update_post_meta( $id, 'gender', $gender );
        return true;
    }

    public function update_location( WP_REST_Request $request ) {
        $params = $request->get_params();
        $id = esc_sql( $params['id'] );
        $location = esc_sql( $params['location'] );
        update_post_meta( $id, 'location_grid', $location );
        return true;
    }

    public function update_name( WP_REST_Request $request ) {
        $params = $request->get_params();
        $id = esc_sql( $params['id'] );
        $name = esc_sql( urldecode( $params['name'] ) );
        $post = [
            'ID' => $id,
            'post_title' => $name,
        ];
        wp_update_post( $post );
        return true;
    }

    public function get_auto_tags( WP_REST_Request $request ) {
        $params = $request->get_params();
        $id = esc_sql( $params['id'] );
        $all_comments = self::get_comments_by_id( $id );
        $auto_tags = self::get_tags_from_comments( $all_comments );
        $auto_tags = array_filter( $auto_tags, function( $item ) {
            if ( $item >= 2 ) {
                return $item;
            }
        });
        return $auto_tags;
    }

    public function get_comments_by_id( $id ) {
        global $wpdb;
        $comments = $wpdb->get_col(
            $wpdb->prepare( "SELECT `comment_content` FROM {$wpdb->prefix}comments WHERE comment_post_id = %d;", $id )
        );
        return $comments;
    }

    public function get_tags_from_comments( $comments ) {
        $auto_tags = [];
        foreach ( $comments as $comment ) {
            $exploded_comment = explode( ' ', $comment );
            foreach ( $exploded_comment as $ec ) {
                $ec = self::clean_auto_tag( $ec );
                $auto_tags[] = $ec;
            }
        }
        $auto_tags = array_count_values( $auto_tags );
        array_multisort( $auto_tags, SORT_DESC );
        return $auto_tags;
    }

    private function clean_auto_tag( $tag ) {
        $tag = strtolower( $tag );
        $tag = trim( $tag, ',.!?/\\*()[]{}' );
        return $tag;
    }

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }
    public function has_permission(){
        $pass = false;
        foreach ( $this->permissions as $permission ){
            if ( current_user_can( $permission ) ){
                $pass = true;
            }
        }
        return $pass;
    }
}
Disciple_Tools_Data_Top_Off_Endpoints::instance();
