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
