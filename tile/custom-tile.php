<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class Disciple_Tools_Data_Top_Off_Template_Tile
{
    private static $_instance = null;
    public static function instance(){
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct(){
        add_filter( 'dt_details_additional_tiles', [ $this, 'dt_details_additional_tiles' ], 100, 2 );
        add_action( 'dt_details_additional_section', [ $this, 'dt_add_section' ], 30, 2 );
    }

    /**
     * This function registers a new tile to a specific post type
     *
     * @todo Set the post-type to the target post-type (i.e. contacts, groups, trainings, etc.)
     * @todo Change the tile key and tile label
     *
     * @param $tiles
     * @param string $post_type
     * @return mixed
     */
    public function dt_details_additional_tiles( $tiles, $post_type = '' ) {
        if ( $post_type === 'contacts' || $post_type === 'starter_post_type' ){
            $tiles['Disciple_Tools_Data_Top_Off_template'] = [ 'label' => __( 'Disciple Tools Data Top-Off', 'disciple-tools-data-top-off-template' ) ];
        }
        return $tiles;
    }

    public function dt_add_section( $section, $post_type ) {
        if ( ( $post_type === 'contacts' || $post_type === 'starter_post_type' ) && $section === 'Disciple_Tools_Data_Top_Off_template' ){
            ?>
            <div class="cell small-12 medium-4">
                <strong><?php esc_html_e( 'Suggested Tags from Comments', 'disciple_tools_data_top_off' ); ?></strong><br><br>
            </div>
            <div class="cell small-12 medium-4" id="autotag-tile-content">
            </div>
            <script>
                $.ajax( {
                type: "GET",
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                url: window.location.origin + '/wp-json/disciple-tools-data-top-off/v1/get_auto_tags/' + window.detailsSettings.post_id,
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ) ?>' );
                    },
                } ).done(
                    $.each( function(tags){
                        console.log(tags);
                        $.each(tags, function(index,tag){
                            $('#autotag-tile-content').append(`
                            <li style="margin-right:5px;">${tag['tag']} (${tag['count']})
                                <a data-tag="${tag['tag']}" class="autotag_suggestion">add tag</a>
                            </li>`);
                        })
                    })
                );
            </script>
            <script>
               $(document).on('click', '.autotag_suggestion', function(){
                var post_id = window.detailsSettings.post_id;
                var tag = $(this).data('tag');
                $(this).parent().remove();
                   $.ajax({
                       type: "POST",
                       contentType: "application/json; charset=utf-8",
                       dataType: "json",
                       url: window.location.origin + '/wp-json/disciple-tools-data-top-off/v1/add_tag/' + post_id + '/' + tag,
                       beforeSend: function(xhr) {
                           xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                        },
                   }) 
               }); 
            </script>
        <?php }
    }
}
Disciple_Tools_Data_Top_Off_Template_Tile::instance();