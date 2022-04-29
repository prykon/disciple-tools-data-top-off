<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class Disciple_Tools_Data_Top_Off_Menu
 */
class Disciple_Tools_Data_Top_Off_Menu {

    public $token = 'Disciple_Tools_Data_Top_Off';
    public $page_title = 'Data Top-Off';

    private static $_instance = null;

    /**
     * Disciple_Tools_Data_Top_Off_Menu Instance
     *
     * Ensures only one instance of Disciple_Tools_Data_Top_Off_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return Disciple_Tools_Data_Top_Off_Menu instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()


    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct() {

        add_action( "admin_menu", array( $this, "register_menu" ) );

    } // End __construct()


    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        add_submenu_page( 'dt_extensions', $this->page_title, $this->page_title, 'manage_dt', $this->token, [ $this, 'content' ] );
    }

    /**
     * Menu stub. Replaced when Disciple.Tools Theme fully loads.
     */
    public function extensions_menu() {}

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        if ( !current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple.Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        if ( isset( $_GET["tab"] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET["tab"] ) );
        } else {
            $tab = 'gender';
        }

        $link = 'admin.php?page='.$this->token.'&tab=';

        ?>
        <div class="wrap">
            <h2>Data Top-Off</h2>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'gender' ?>"
                   class="nav-tab <?php echo esc_html( ( $tab == 'gender' || !isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>">Gender</a>
                <a href="<?php echo esc_attr( $link ) . 'location' ?>" class="nav-tab <?php echo esc_html( ( $tab == 'location' || !isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>">Location</a>
            </h2>

            <?php
            switch ( $tab ) {
                case "gender":
                    $object = new Disciple_Tools_Data_Top_Off_Tab_Gender();
                    $object->content();
                    break;
                case "location":
                    $object = new Disciple_Tools_Data_Top_Off_Tab_Location();
                    $object->content();
                    break;
                default:
                    break;
            }
            ?>
        </div><!-- End wrap -->
        <?php
    }

    /**
     * Display admin notice
     * @param $notice string
     * @param $type string error|success|warning
     */
    public static function admin_notice( string $notice, string $type ) {
        ?>
        <div class="notice notice-<?php echo esc_attr( $type ) ?> is-dismissible">
            <p><?php echo esc_html( $notice ) ?></p>
        </div>
        <?php
    }
}
Disciple_Tools_Data_Top_Off_Menu::instance();

/**
 * Class Disciple_Tools_Data_Top_Off_Tab_Gender
 */
class Disciple_Tools_Data_Top_Off_Tab_Gender {
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <?php self::show_genderless_count(); ?>
                        <!-- Start Dictionary Table -->
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th>Genders from Name Dictionary</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <?php self::show_dictionary_table(); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- End Dictionary Table -->
                        <br>
                        <!-- Start Namesake Table -->
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th>Genders from Namesakes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <?php self::show_namesake_table(); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- End Namesake Table -->
                        <br>
                        <!-- Start E-mail Inference Table -->
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th>Name Inference from E-mails</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <?php self::show_email_inference_table(); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- End E-mail Inference Table -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Information</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    In order to easily top-off your contact gender data, you can select from two autocomplete methods:
                    <br>
                    <br>
                    <b>1. Name Dictionary:</b>
                    <br>
                    Autocomplete popular names with their known gender.
                    <br>
                    <br>
                    <b>2. Namesakes</b>
                    <br>
                    Use less popular contact names that have their gender set in your Disciple.Tools instance to autocomplete other contacts with the same names.
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    // Get contacts that don't have a gender set
    private function get_genderless_contacts() {
        global $wpdb;
        $result = $wpdb->get_col(
            "SELECT DISTINCT ( pm.post_id )
                FROM $wpdb->postmeta pm
                LEFT JOIN $wpdb->posts p
                ON pm.post_id = p.ID
                WHERE pm.post_id NOT IN (
                    SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'gender' AND meta_value IN ( 'male', 'female' )
                )
                AND p.post_type = 'contacts'
                ORDER BY p.post_title ASC;"
        );
        return $result;
    }

    // Get contact names that already have at least one gender set
    private function get_names_with_gender() {
        global $wpdb;
        $contact_ids = $wpdb->get_col( "
            SELECT DISTINCT( post_id )
            FROM $wpdb->postmeta
            WHERE meta_key = 'gender'
            AND meta_value IN ('male', 'female');
            "
        );

        foreach ( $contact_ids as $id ) {
            $first_name = self::get_first_name( $id );
            $gender = get_post_meta( $id, 'gender' );
            $name_genders[ $first_name ] = $gender;
        }

        foreach ( $name_genders as $name => $genders ) {
            // If there's only one gender for the current name
            if ( count( array_unique( $genders ) ) === 1 ) {
                $output[ $name ] = $genders[0];
            }
        }
        return $output;
    }



    public function show_genderless_count() {
        $genderless = self::get_genderless_contacts();
        ?>
        <div >There are currently <b><?php echo count( $genderless ); ?> contacts</b> that need their gender set.</div>
        <br>
        <?php
    }

    public function get_name_email_data() {
        global $wpdb;
        return $wpdb->get_results( "SELECT p.ID AS contact_id, LOWER( p.post_title ) AS name, LOWER( pm.meta_value ) AS email, 'foo' AS inference
            FROM $wpdb->posts p
            RIGHT JOIN $wpdb->postmeta pm
            ON p.ID = pm.post_id
            WHERE pm.meta_key LIKE 'contact_email%'
            AND pm.meta_key NOT LIKE '%_details'
            AND pm.meta_value != '';
        ", ARRAY_A );
    }

    public function get_email_inferences( $data ) {
        $output = [];
        foreach ( $data as $d ) {
            $name = trim( $d['name'] );
            $email = $d['email'];
            $email = preg_replace( '/\d+|_|\-|\./u', '', $email ); // Remove numbers and special characters from email username
            preg_match( '/^(.*?)@.*$/', $email, $email_username );

            // If there's an email username we can work with...
            if ( isset( $email_username[1] ) ) {
                $inference = $email_username[1];
                $inference = str_replace( $name, $name . ' ', $inference );
                $inference = trim( $inference );

                $email_without_name = trim( str_replace( $name, '', $inference ) );
                if ( strlen( $inference ) > strlen( $email_without_name ) ) {
                    // Check for emails with initials, such as jdoe@email.com
                    $inference = $name . ' ' . $email_without_name;

                    if ( strlen( $email_without_name ) === 1 ) {
                        $inference = $email_without_name . '. ' . $name;
                    }

                    $inference = trim( $inference );

                    if ( $name === $inference ) {
                        continue;
                    }


                    $inference = ucwords( $inference );
                    $output[] = [
                        'contact_id' => $d['contact_id'],
                        'email' => $d['email'],
                        'name' => get_the_title( $d['contact_id'] ),
                        'inference' => $inference,
                    ];
                }
            }
        }
        return $output;
    }

    public function show_email_inference_table() {
        $name_email_data = self::get_name_email_data();
        $email_inferences = self::get_email_inferences( $name_email_data );

        // Accept all email name inferences was clicked
        if ( isset( $_POST['accept_email_inference_nonce'], $_POST['accept_email_inference_nonce'] ) ) {
            if ( ! wp_verify_nonce( sanitize_key( $_POST['accept_email_inference_nonce'] ), 'email_inference_add_all' ) ) {
                return;
            }
            foreach ( $email_inferences as $email_inference ) {
                $post = [
                    'ID' => $email_inference['contact_id'],
                    'post_title' => $email_inference['inference'],
                ];
                wp_update_post( $post );
            }
            Disciple_Tools_Data_Top_Off_Menu::admin_notice( count( $email_inferences ) . __( ' contacts updated.', 'disciple_tools' ), "success" );
            $name_email_data = self::get_name_email_data();
            $email_inferences = self::get_email_inferences( $name_email_data );
        }
        ?>
        <form method="post">
            <input type="hidden" name="accept_email_inference_nonce" value="<?php echo esc_attr( wp_create_nonce( 'email_inference_add_all' ) ) ?>" />
            <?php
            if ( count( $email_inferences ) === 0 ) {
                ?>
                    <div>No contacts can have their name set automatically from email username inferences.</div>
                <?php
                return;
            }
            ?>
            <div>
                <b><?php echo esc_html( count( $email_inferences ) ); ?> names</b> can be infered from their email address.
                <button name="email_inference_add_all">Accept all</button>
            </div>
            <br>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>E-Mail</th>
                        <th>Autofill to</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $email_inferences as $email_inference ) : ?>
                    <tr id="contact-name-inference-<?php echo esc_attr( $email_inference['contact_id'] ); ?>">
                        <td><?php echo esc_html( $email_inference['name'] ); ?></td>
                        <td><?php echo esc_html( $email_inference['email'] ); ?></td>
                        <td><?php echo esc_html( $email_inference['inference'] ); ?></td>
                        <td><?php if ( $email_inference['inference'] ) { echo '<a href="javascript:void(0);" class="accept_inference" data-id="' . esc_attr( $email_inference['contact_id'] ) .'" data-inference="' . esc_attr( $email_inference['inference'] ) . '">accept</a>'; } ?> | <a href="<?php echo esc_attr( '/contacts/' .$email_inference['contact_id'] ); ?>" target="_blank">view</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
        <script>
            // Assign name to a contact
            jQuery( '.accept_inference' ).on( 'click', function () {
                var id = jQuery( this ).data( 'id' );
                var name = jQuery( this ).data( 'inference' );
                jQuery.ajax( {
                    type: 'POST',
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'json',
                    url: window.location.origin + '/wp-json/disciple-tools-data-top-off/v1/update_name/' + id + '/' + name,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                    },
                } );
                jQuery( '#contact-name-inference-' + id ).remove();
            } );
        </script>
        <?php
    }


    public function show_dictionary_table() {
        $genderless = self::get_genderless_contacts();
        $genderable = [];

        $updates_count = 0;
        foreach ( $genderless as $gid ) {
            $first_name = self::get_first_name( $gid );
            if ( $first_name ) {
                $gender = self::get_gender( $first_name );
                if ( $gender ) {
                    $genderable[$gid] = $gender;
                    $updates_count ++;
                }
            }
        }

        // Accept all dictionary gender suggestions was clicked
        if ( isset( $_POST['accept_dictionary_nonce'], $_POST['accept_dictionary_nonce'] ) ) {
            if ( ! wp_verify_nonce( sanitize_key( $_POST['accept_dictionary_nonce'] ), 'dictionary_add_all' ) ) {
                return;
            }
            foreach ( $genderable as $id => $gender ) {
                update_post_meta( $id, 'gender', $gender );
            }
            $genderless = self::get_genderless_contacts(); // Refresh genderless ids for table
            Disciple_Tools_Data_Top_Off_Menu::admin_notice( $updates_count . __( ' contacts updated.', 'disciple_tools' ), "success" );
        }
        ?>
        <form method="post">
            <input type="hidden" name="accept_dictionary_nonce" id="accept_dictionary_nonce" value="<?php echo esc_attr( wp_create_nonce( 'dictionary_add_all' ) ) ?>" />
            <?php
            if ( empty( $genderable ) || ( isset( $_POST['accept_dictionary_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['accept_dictionary_nonce'] ), 'dictionary_add_all' ) ) ) {
                ?>
                <div>
                    No contact names can be filled automatically from the name dictionary.
                </div>
                <?php
                return;
            }
            ?>
            <div>
                <b><?php echo count( $genderable ); ?></b> contact genders can be filled automatically from a name dictionary.
                <button name="dictionary_add_all">Accept all</button>
            </div>
            <br>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Autofill to</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ( $genderless as $genderless_id ) :
                        $name = get_the_title( $genderless_id );
                        $gender = self::get_gender( $name );
                        if ( ! $gender ) {
                            continue;
                        }
                        ?>
                    <tr id="contact-<?php echo esc_attr( $genderless_id ); ?>">
                        <td><?php echo esc_html( $name ); ?></td>
                        <td><?php echo esc_html( $gender ); ?></td>
                        <td><?php if ( $gender ) { echo '<a href="javascript:void(0);" class="accept_gender" data-id="' . esc_attr( $genderless_id ) .'" data-gender="' . esc_attr( $gender ) . '">accept</a>'; } ?> | <a href="<?php echo esc_attr( "/contacts/$genderless_id" ); ?>" target="_blank">view</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
        <script>
            // Assign gender to a contact
            jQuery( '.accept_gender' ).on( 'click', function () {
                var id = jQuery( this ).data( 'id' );
                var gender = jQuery( this ).data( 'gender' );
                jQuery.ajax( {
                    type: 'POST',
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'json',
                    url: window.location.origin + '/wp-json/disciple-tools-data-top-off/v1/update_gender/' + id + '/' + gender,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>' );
                    },
                } );
                jQuery( '#contact-' + id ).remove();
            } );
        </script>
        <?php
    }

    private function get_namesake_table_data( $genderable_contacts, $name_genders ) {
        $namesake_table = [];
        foreach ( $genderable_contacts as $id ) {
            $first_name = self::get_first_name( $id );
            if ( array_key_exists( $first_name, $name_genders ) && $first_name !== '' ) {
                $namesake_table[ $first_name ] = $name_genders[ $first_name ];
            }
        }
        return $namesake_table;
    }

    // If a name has a gender set and another contact has the same name, suggest the same gender
    public function show_namesake_table() {
        $genderable_contacts = self::get_genderless_contacts();
        $name_genders = self::get_names_with_gender();
        $namesake_table = self::get_namesake_table_data( $genderable_contacts, $name_genders );

        $namesakable_names = [];
        foreach ( $namesake_table as $name => $gender ) {
            $namesakable_names[] = self::get_ungendered_by_name( $name );
        }


        // Accept all namesake gender suggestions was clicked
        if ( isset( $_POST['accept_namesakes_nonce'], $_POST['namesakes_add_all'] ) ) {
            if ( ! wp_verify_nonce( sanitize_key( $_POST['accept_namesakes_nonce'] ), 'namesakes_nonce' ) ) {
                return;
            }
            $updates_count = 0;
            foreach ( $namesakable_names as $contact ) {
                foreach ( $contact as $c ) {
                    $first_name = self::get_first_name( $c->ID );
                    $gender = $namesake_table[$first_name];
                    update_post_meta( $c->ID, 'gender', $gender );
                    $updates_count ++;
                }
            }
            Disciple_Tools_Data_Top_Off_Menu::admin_notice( $updates_count . __( ' contacts updated.', 'disciple_tools' ), "success" );
            $genderable_contacts = self::get_genderless_contacts();
            $namesake_table = self::get_namesake_table_data( $genderable_contacts, $name_genders ); // Refresh namesake_table ids for table
        }


        // Accept specific namesake gender suggestions was clicked
        if ( isset( $_POST['accept_namesakes_nonce'], $_POST['namesakes_specific_name'] ) ) {

            if ( ! wp_verify_nonce( sanitize_key( $_POST['accept_namesakes_nonce'] ), 'namesakes_nonce' ) ) {
                return;
            }

            $specific_name = sanitize_key( $_POST['namesakes_specific_name'] );
            $updates_count = 0;
            foreach ( $namesakable_names as $contact ) {
                foreach ( $contact as $c ) {
                    $first_name = self::get_first_name( $c->ID );
                    if ( $first_name === $specific_name ) {
                        $gender = $namesake_table[$first_name];
                        update_post_meta( $c->ID, 'gender', $gender );
                        $updates_count ++;
                    }
                }
            }
            Disciple_Tools_Data_Top_Off_Menu::admin_notice( $updates_count . __( ' contacts updated.', 'disciple_tools' ), "success" );
            $genderable_contacts = self::get_genderless_contacts();
            $namesake_table = self::get_namesake_table_data( $genderable_contacts, $name_genders ); // Refresh namesake_table ids for table
        }

        $count_sub_namesakes = count( $namesakable_names, COUNT_RECURSIVE ) - count( $namesakable_names );
        if ( count( $namesake_table ) === 0 ) {
            ?>
            <div>No contacts can have their gender set automatically from gendered contacts with the same name.</div>
            <?php
            return;
        }

        if ( count( $namesake_table ) > 0 ) {
            ?>
            <form method="post">
                <input type="hidden" name="accept_namesakes_nonce" id="accept_namesakes_nonce" value="<?php echo esc_attr( wp_create_nonce( 'namesakes_nonce' ) ) ?>" />
                <div>
                    <b><?php echo esc_html( count( $namesake_table ) ); ?></b> names (<b><?php echo esc_html( $count_sub_namesakes ); ?></b> contacts) can have their gender set automatically from gendered contacts with the same name.
                    <button name="namesakes_add_all">Accept all</button>
                </div>
                <br>
            <?php
        }
        ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th colspan="2">Name</th>
                        <th>Autofill to</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $namesake_table as $name => $gender ) : ?>
                    <tr>
                        <td colspan="3">
                            <b><?php echo esc_html( ucwords( $name ) ); ?></b>
                        </td>
                        <td>
                            <button name="namesakes_specific_name" value="<?php echo esc_attr( $name ); ?>">Accept all <?php echo esc_html( ucwords( $name ) ); ?></button>
                        </td>
                    </tr>
                        <?php
                            $ungendered_by_name = self::get_ungendered_by_name( $name );
                        foreach ( $ungendered_by_name as $ungendered_name ) : ?>
                            <tr id="contact-<?php echo esc_attr( $ungendered_name->ID );?>">
                                <td></td>
                                <td><?php echo esc_html( $ungendered_name->name ); ?></td>
                                <td><?php echo esc_html( $gender ); ?></td>
                                <td><?php if ( $gender ) { echo '<a href="javascript:void(0);" class="accept_gender" data-id="' . esc_attr( $ungendered_name->ID ) .'" data-gender="' . esc_attr( $gender ) . '">accept</a>'; } ?> | <a href="<?php echo esc_attr( '/contacts/'. $ungendered_name->ID ); ?>" target="_blank">view</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="4">
                                <hr>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
        <script>
            // Assign gender to a contact
            jQuery( '.accept_gender' ).on( 'click', function() {
                var id = jQuery( this ).data( 'id' );
                var gender = jQuery( this ).data( 'gender' );
                jQuery.ajax( {
                    type: 'POST',
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'json',
                    url: window.location.origin + '/wp-json/disciple-tools-data-top-off/v1/update_gender/' + id + '/' + gender,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ) ?>' );
                        },
                } );
                jQuery('#contact-' + id ).remove();
            } );
        </script>
        <?php
    }

    private function get_ungendered_by_name( $name ) {
        global $wpdb;
        $result = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, post_title AS name
                FROM $wpdb->posts
                WHERE LOWER( post_title ) LIKE %s
                AND ID NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'gender'
                    AND meta_value IN ('male', 'female')
                );", $name . '%'
            )
        );
        return $result;
    }

    private function get_first_name( $id ) {
        $full_name = strtolower( get_the_title( $id ) );
        $first_name = explode( ' ', $full_name )[0];
        $first_name = str_replace( ',', '', $first_name );
        return $first_name;
    }

    private function get_gender( $name ) {
        $male_names = [ 'aamir','aaron','abbey','abbie','abbot','abbott','abby','abdel','abdul','abdulkarim','abdullah','abe','abel','abelard','abner','abraham','abram','ace','adair','adam','adams','addie','adger','aditya','adlai','adnan','adolf','adolfo','adolph','adolphe','adolpho','adolphus','adrian','adrick','adrien','agamemnon','aguinaldo','aguste','agustin','aharon','ahmad','ahmed','ahmet','ajai','ajay','al','alaa','alain','alan','alasdair','alastair','albatros','albert','alberto','albrecht','alden','aldis','aldo','aldric','aldrich','aldus','aldwin','alec','aleck','alejandro','aleks','aleksandrs','alessandro','alex','alexander','alexei','alexis','alf','alfie','alfonse','alfonso','alfonzo','alford','alfred','alfredo','algernon','ali','alic','alister','alix','allah','allan','allen','alley','allie','allin','allyn','alonso','alonzo','aloysius','alphonse','alphonso','alston','alton','alvin','alwin','amadeus','ambros','ambrose','ambrosi','ambrosio','ambrosius','amery','amory','amos','anatol','anatole','anatollo','anatoly','anders','andie','andonis','andre','andrea','andreas','andrej','andres','andrew','andrey','andri','andros','andrus','andrzej','andy','angel','angelico','angelo','angie','angus','ansel','ansell','anselm','anson','anthony','antin','antoine','anton','antone','antoni','antonin','antonino','antonio','antonius','antony','anurag','apollo','apostolos','aram','archibald','archibold','archie','archon','archy','arel','ari','arie','ariel','aristotle','arlo','armand','armando','armond','armstrong','arne','arnie','arnold','arnoldo','aron','arron','art','arther','arthur','artie','artur','arturo','arvie','arvin','arvind','arvy','ash','ashby','ashish','ashley','ashton','aub','aube','aubert','aubrey','augie','august','augustin','augustine','augusto','augustus','austen','austin','ave','averell','averil','averill','avery','avi','avraham','avram','avrom','axel','aylmer','aziz','bailey','bailie','baillie','baily','baird','baldwin','bancroft','barbabas','barclay','bard','barde','barn','barnabas','barnabe','barnaby','barnard','barnebas','barnett','barney','barnie','barny','baron','barr','barret','barrett','barri','barrie','barris','barron','barry','bart','bartel','barth','barthel','bartholemy','bartholomeo','bartholomeus','bartholomew','bartie','bartlet','bartlett','bartolemo','bartolomei','bartolomeo','barton','barty','bary','basil','batholomew','baxter','bay','bayard','beale','bealle','bear','bearnard','beau','beaufort','beauregard','beck','bela','ben','benedict','bengt','benito','benjamen','benjamin','benji','benjie','benjy','benn','bennet','bennett','bennie','benny','benson','bentley','benton','beowulf','berchtold','berk','berke','berkeley','berkie','berkley','bernard','bernardo','bernd','bernhard','bernie','bert','bertie','bertram','bertrand','bharat','biff','bill','billie','billy','bing','binky','bishop','bjorn','bjorne','blaine','blair','blake','blare','blayne','bo','bob','bobbie','bobby','bogart','bogdan','boniface','boris','boyce','boyd','brad','braden','bradford','bradley','bradly','brady','brandon','brandy','brant','brendan','brent','bret','brett','brewer','brewster','brian','brice','briggs','brinkley','britt','brock','broddie','broddy','broderic','broderick','brodie','brody','bronson','brook','brooke','brooks','bruce','bruno','bryan','bryant','bryce','bryn','bryon','bubba','buck','bucky','bud','buddy','burgess','burke','burl','burnaby','burt','burton','buster','butch','butler','byram','byron','caesar','cain','cal','caldwell','caleb','calhoun','calvin','cam','cameron','cammy','carey','carl','carleigh','carlie','carlin','carlo','carlos','carlton','carlyle','carmine','carroll','carson','carsten','carter','cary','caryl','case','casey','caspar','casper','cass','cat','cecil','cesar','chad','chadd','chaddie','chaddy','chadwick','chaim','chalmers','chan','chance','chancey','chanderjit','chandler','chane','chariot','charles','charleton','charley','charlie','charlton','chas','chase','chaunce','chauncey','che','chelton','chen','chester','cheston','chet','chev','chevalier','chevy','chip','chris','chrissy','christ','christian','christiano','christie','christof','christofer','christoph','christophe','christopher','christorpher','christos','christy','chrisy','chuck','churchill','clair','claire','clancy','clarance','clare','clarence','clark','clarke','claude','claudio','claudius','claus','clay','clayborn','clayborne','claybourne','clayton','cleland','clem','clemens','clement','clemente','clemmie','cletus','cleveland','cliff','clifford','clifton','clint','clinten','clinton','clive','clyde','cob','cobb','cobbie','cobby','cody','colbert','cole','coleman','colin','collin','collins','conan','connie','connolly','connor','conrad','conroy','constantin','constantine','constantinos','conway','cooper','corbin','corby','corey','corky','cornelius','cornellis','corrie','cortese','corwin','cory','cosmo','costa','courtney','craig','crawford','creighton','cris','cristopher','curt','curtice','curtis','cy','cyril','cyrill','cyrille','cyrillus','cyrus','dabney','daffy','dale','dallas','dalton','damian', 'damián', 'damien','damon','dan','dana','dane','dani','danie','daniel','dannie','danny','dante','darby','darcy','daren','darian','darien','darin','dario','darius','darrel','darrell','darren','darrick','darrin','darryl','darth','darwin','daryl','daryle','dave','davey','david','davidde','davide','davidson','davie','davin','davis','davon','davoud','davy','dawson','dean','deane','del','delbert','dell','delmar','demetre','demetri','demetris','demetrius','demosthenis','denis','dennie','dennis','denny','derby','derek','derick','derk','derrek','derrick','derrin','derrol','derron','deryl','desmond','desmund','devin','devon','dewey','dewitt','dexter','dick','dickey','dickie','diego','dieter','dietrich','dillon','dimitri','dimitrios','dimitris','dimitrou','dimitry','dino','dion','dionis','dionysus','dirk','dmitri','dom','domenic','domenico','dominic','dominick','dominique','don','donal','donald','donn','donnie','donny','donovan','dorian','dory','doug','douggie','dougie','douglas','douglass','douglis','dov','doyle','drake','drew','dru','dryke','duane','dudley','duffie','duffy','dugan','duke','dunc','duncan','dunstan','durand','durant','durante','durward','dustin','dwain','dwaine','dwane','dwayne','dwight','dylan','dyson','earl','earle','easton','eben','ebeneser','ebenezer','eberhard','ed','eddie','eddy','edgar','edgardo','edie','edmond','edmund','edouard','edsel','eduard','eduardo','edward','edwin','efram','egbert','ehud','elbert','elden','eldon','eli','elias','elihu','elijah','eliot','eliott','elisha','elliot','elliott','ellis','ellsworth','ellwood','elmer','elmore','elnar','elric','elroy','elton','elvin','elvis','elwin','elwood','elwyn','ely','emanuel','emerson','emery','emil','emile','emilio','emmanuel','emmery','emmet','emmett','emmit','emmott','emmy','emory','ender','engelbart','engelbert','englebart','englebert','enoch','enrico','enrique','ephraim','ephram','ephrayim','ephrem','er','erasmus','erastus','erek','erhard','erhart','eric','erich','erick','erik','erin','erl','ernest','ernesto','ernie','ernst','erny','errol','ervin','erwin','esau','esme','esteban','ethan','ethelbert','ethelred','etienne','euclid','eugen','eugene','eustace','ev','evan','evelyn','everard','everett','ewan','ewart','ez','ezechiel','ezekiel','ezra','fabian','fabio','fairfax','farley','fazeel','federico','felice','felicio','felipe','felix','ferd','ferdie','ferdinand','ferdy','fergus','ferguson','ferinand','fernando','fidel','filbert','filip','filipe','filmore','finley','finn','fitz','fitzgerald','flem','fleming','flemming','fletch','fletcher','flin','flinn','flint','flipper','florian','floyd','flynn','fons','fonsie','fonz','fonzie','forbes','ford','forest','forester','forrest','forrester','forster','foster','fowler','fox','fran','francesco','francis','francisco','francois','frank','frankie','franklin','franklyn','franky','frans','franz','fraser','frazier','fred','freddie','freddy','frederic','frederich','frederick','frederico','frederik','fredric','fredrick','freeman','freemon','fremont','french','friedric','friedrich','friedrick','fritz','fulton','fyodor','gabe','gabriel','gabriele','gabriell','gabriello','gail','gale','galen','gallagher','gamaliel','garcia','garcon','gardener','gardiner','gardner','garey','garfield','garfinkel','garold','garp','garret','garrett','garrot','garrott','garry','garth','garv','garvey','garvin','garvy','garwin','garwood','gary','gaspar','gasper','gaston','gav','gaven','gavin','gavriel','gay','gayle','gearard','gene','geo','geof','geoff','geoffrey','geoffry','georg','george','georges','georgia','georgie','georgy','gerald','geraldo','gerard','gere','gerhard','gerhardt','geri','germaine','gerold','gerome','gerrard','gerri','gerrit','gerry','gershom','gershon','giacomo','gian','giancarlo','giavani','gibb','gideon','giff','giffard','giffer','giffie','gifford','giffy','gil','gilbert','gilberto','gilburt','giles','gill','gilles','ginger','gino','giordano','giorgi','giorgio','giovanne','giovanni','giraldo','giraud','giuseppe','glen','glenn','glynn','godard','godart','goddard','goddart','godfree','godfrey','godfry','godwin','gomer','gonzales','gonzalo','goober','goose','gordan','gordie','gordon','grace','grady','graehme','graeme','graham','graig','grant','granville','greg','gregg','greggory','gregor','gregorio','gregory','gretchen','griff','griffin','griffith','griswold','grove','grover','guido','guillaume','guillermo','gunner','gunter','gunther','gus','gustaf','gustav','gustave','gustavo','gustavus','guthrey','guthrie','guthry','guy','hadleigh','hadley','hadrian','hagan','hagen','hailey','hakeem','hakim','hal','hale','haleigh','haley','hall','hallam','halvard','ham','hamel','hamid','hamil','hamilton','hamish','hamlen','hamlet','hamlin','hammad','hamnet','han','hanan','hanford','hank','hannibal','hans','hans-peter','hansel','hanson','harald','harcourt','hari','harlan','harland','harley','harlin','harman','harmon','harold','harris','harrison','harrold','harry','hart','hartley','hartwell','harv','harvard','harvey','harvie','harwell','hasheem','hashim','haskel','haskell','hassan','hastings','hasty','haven','hayden','haydon','hayes','hayward','haywood','hazel','heath','heathcliff','hebert','hector','heinrich','heinz','helmuth','henderson','hendrick','hendrik','henri','henrie','henrik','henrique','henry','herb','herbert','herbie','herby','hercule','hercules','herculie','herman','hermann','hermon','hermy','hernando','herold','herrick','herrmann','hersch','herschel','hersh','hershel','herve','hervey','hew','hewe','hewet','hewett','hewie','hewitt','heywood','hezekiah','higgins','hilary','hilbert','hill','hillard','hillary','hillel','hillery','hilliard','hilton','hiralal','hiram','hiro','hirsch','hobart','hodge','hogan','hollis','holly','homer','horace','horacio','horatio','horatius','horst','howard','howie','hoyt','hubert','hudson','huey','hugh','hugo','humbert','humphrey','hunt','hunter','huntington','huntlee','huntley','hurley','husain','husein','hussein','hy','hyatt','hyman','hymie','iago','iain','ian','ibrahim','ichabod','iggie','iggy','ignace','ignacio','ignacius','ignatius','ignaz','ignazio','igor','ike','ikey','immanuel','ingamar','ingelbert','ingemar','inglebert','ingmar','ingram','inigo','ira','irvin','irvine','irving','irwin','isa','isaac','isaak','isador','isadore','isaiah','ishmael','isidore','ismail','israel','istvan','ivan','ivor','izaak','izak','izzy','jabez','jack','jackie','jackson','jacob','jacques','jae','jaime','jake','jakob','james','jameson','jamey','jamie','jan','janos','janus','jared','jarrett','jarvis','jason','jasper','javier','jay','jean','jean-christophe','jean-francois','jean-lou','jean-luc','jean-marc','jean-paul','jean-pierre','jeb','jed','jedediah','jef','jeff','jefferey','jefferson','jeffery','jeffie','jeffrey','jeffry','jefry','jehu','jennings','jens','jephthah','jerald','jeramie','jere','jereme','jeremiah','jeremias','jeremie','jeremy','jermain','jermaine','jermayne','jerold','jerome','jeromy','jerri','jerrie','jerrold','jerrome','jerry','jervis','jerzy','jess','jesse','jessee','jessey','jessie','jesus','jeth','jethro','jim','jimbo','jimmie','jimmy','jo','joab','joachim','joao','joaquin','job','jock','jodi','jodie','jody','joe','joel','joey','johan','johann','johannes','john','john-david','john-patrick','johnathan','johnathon','johnnie','johnny','johny','jon','jonah','jonas','jonathan','jonathon','jonny','jordan','jordon','jordy','jorge','jory','jose','josef','joseph','josephus','josh','joshua','joshuah','josiah','jotham','juan','juanita','jud','judah','judas','judd','jude','judith','judson','judy','juergen','jule','jules','julian','julie','julio','julius','justin','justis','kaiser','kaleb','kalil','kalle','kalman','kalvin','kam','kane','kareem','karel','karim','karl','karsten','kaspar','keefe','keenan','keene','keil','keith','kellen','kelley','kelly','kelsey','kelvin','kelwin','ken','kendal','kendall','kendrick','kenn','kennedy','kenneth','kenny','kent','kenton','kenyon','kermie','kermit','kerry','kevan','kevin','kim','kimball','kimmo','kin','kincaid','king','kingsley','kingsly','kingston','kip','kirby','kirk','kit','klaus','klee','knox','konrad','konstantin','kory','kostas','kraig','kris','krishna','kristian','kristopher','kristos','kurt','kurtis','kyle','laird','lamar','lambert','lamont','lance','lancelot','lane','langston','lanny','larry','lars','laurance','lauren','laurence','laurens','laurent','laurie','lawerence','lawrence','lawson','lawton','lay','layton','lazar','lazare','lazaro','lazarus','lazlo','lee','lefty','leif','leigh','leighton','leland','lem','lemar','lemmie','lemmy','lemuel','len','lenard','lennie','lenny','leo','leon','leonard','leonardo','leonerd','leonhard','leonid','leonidas','leopold','leroy','les','lesley','leslie','lester','lev','levi','levin','levon','levy','lew','lewis','lex','liam','lin','lincoln','lind','lindsay','lindsey','lindy','linoel','linus','lion','lionel','lionello','llewellyn','lloyd','locke','lockwood','logan','lon','lonnie','lonny','loren','lorenzo','lorne','lorrie','lothar','lou','louie','louis','lovell','lowell','lucas','luce','lucian','luciano','lucien','lucio','lucius','ludvig','ludwig','luigi','luis','lukas','luke','luther','lyle','lyn','lyndon','lynn','mac','mace','mack','mackenzie','maddie','maddy','madison','magnum','magnus','mahesh','mahmoud','mahmud','maison','major','malcolm','manfred','manish','manny','manuel','marc','marcel','marcello','marcellus','marcelo','marchall','marcio','marco','marcos','marcus','marietta','marilu','mario','marion','marius','mark','marko','markos','markus','marlin','marlo','marlon','marlow','marlowe','marmaduke','marsh','marshal','marshall','mart','martainn','marten','martie','martin','martino','marty','martyn','marv','marve','marven','marvin','marwin','mason','mateo','mathew','mathias','matias','matt','matteo','matthaeus','mattheus','matthew','matthias','matthieu','matthiew','matthus','mattias','mattie','matty','maurice','mauricio','maurie','maurise','maurits','mauritz','maury','max','maxfield','maxie','maxim','maximilian','maximilien','maxwell','mayer','maynard','maynord','mayor','mead','meade','meier','meir','mel','melvin','melvyn','menard','mendel','mendie','meredeth','meredith','merell','merill','merle','merlin','merrel','merrick','merril','merrill','merry','merv','mervin','merwin','meryl','meyer','mic','micah','michael','michail','michal','michale','micheal','micheil','michel','michele','mick','mickey','mickie','micky','miguel','mika','mikael','mike','mikel','mikey','mikhail','miles','millicent','milo','milt','milton','mischa','mitch','mitchael','mitchel','mitchell','moe','mohamad','mohamed','mohammad','mohammed','mohan','moise','moises','moishe','monroe','montague','monte','montgomery','monty','moore','mordecai','morgan','morlee','morley','morly','morrie','morris','morry','morse','mort','morten','mortie','mortimer','morton','morty','mose','moses','moshe','moss','muffin','mugsy','muhammad','munmro','munroe','murdoch','murdock','murphy','murray','mustafa','myke','myles','mylo','myron','nahum','napoleon','nat','natale','nate','nathan','nathanael','nathanial','nathaniel','nathanil','neal','neale','neall','nealon','nealson','nealy','ned','neddie','neddy','neel','neil','nels','nelsen','nelson','nero','neron','nester','nestor','nev','nevil','nevile','neville','nevin','nevins','newton','niall','niccolo','nicholas','nichole','nichols','nick','nickey','nickie','nickolas','nicky','nico','nicolas','niels','nigel','niki','nikita','nikki','nikolai','nikos','niles','nils','nilson','niven','noach','noah','noam','noble','noe','noel','nolan','noland','norbert','norm','norman','normand','normie','norris','northrop','northrup','norton','norwood','nunzio','obadiah','obadias','oberon','obie','octavius','odell','odie','odin','odysseus','olaf','olag','ole','oleg','olin','oliver','olivier','olle','ollie','omar','oral','oran','orazio','orbadiah','oren','orin','orion','orlando','orren','orrin','orson','orton','orville','osbert','osborn','osborne','osbourn','osbourne','oscar','osgood','osmond','osmund','ossie','oswald','oswell','otes','othello','otho','otis','otto','owen','ozzie','ozzy','pablo','pace','paco','paddie','paddy','padraig','page','paige','pail','palmer','paolo','park','parke','parker','parnell','parrnell','parry','parsifal','partha','pascal','pascale','pasquale','pat','pate','patel','paten','patin','paton','patric','patrice','patricio','patrick','patrik','patsy','pattie','patty','paul','paulo','pavel','pearce','pedro','peirce','pembroke','pen','penn','pennie','penny','penrod','pepe','pepillo','pepito','perceval','percival','percy','perry','pete','peter','petey','petr','peyter','peyton','phil','philbert','philip','phillip','phillipe','phillipp','phineas','phip','pierce','pierre','pierson','piet','pieter','pietro','piggy','pincas','pinchas','pincus','piotr','pip','plato','pooh','porter','poul','powell','praneetf','prasad','prasun','prent','prentice','prentiss','prescott','preston','price','prince','pryce','puff','purcell','putnam','pyotr','quent','quentin','quiggly','quigly','quigman','quill','quillan','quincey','quincy','quinlan','quinn','quint','quintin','quinton','quintus','rab','rabbi','rabi','rad','radcliffe','rafael','rafe','ragnar','raimund','rainer','raj','rajeev','raleigh','ralf','ralph','ram','ramesh','ramon','ramsay','ramsey','rand','randal','randall','randell','randi','randie','randolf','randolph','randy','ransell','ransom','raoul','raphael','raul','ravi','ravil','rawley','ray','raymond','raymund','raymundo','raynard','rayner','raynor','reagan','red','redford','redmond','reece','reed','rees','reese','reg','regan','regen','reggie','reggis','reggy','reginald','reginauld','reid','reilly','reinhard','reinhold','rem','remington','remus','renado','renaldo','renard','renato','renaud','renault','rene','reube','reuben','reuven','rex','rey','reynard','reynold','reynolds','reza','rhett','ric','ricard','ricardo','riccardo','rice','rich','richard','richardo','richie','richmond','richy','rick','rickard','rickey','ricki','rickie','ricky','rik','rikki','riley','rinaldo','ripley','ritch','ritchie','roarke','rob','robb','robbert','robbie','robert','roberto','robin','robinson','rochester','rock','rockwell','rocky','rod','rodd','roddie','roddy','roderic','roderich','roderick','roderigo','rodge','rodger','rodney','rodolfo','rodolph','rodolphe','rodrick','rodrigo','rodrique','rog','roger','rogers','roice','roland','rolando','rolf','rolfe','rolland','rollin','rollins','rollo','rolph','romain','roman','romeo','ron','ronald','ronen','roni','ronnie','ronny','roosevelt','rory','roscoe','ross','roth','rourke','rowland','roy','royal','royce','rube','ruben','rubin','ruby','rudd','ruddie','ruddy','rudie','rudiger','rudolf','rudolfo','rudolph','rudy','rudyard','rufe','rufus','rupert','ruperto','russ','russel','russell','rustie','rustin','rusty','rutger','rutherford','rutledge','rutter','ryan','sal','salem','salim','salman','salmon','salomo','salomon','salomone','salvador','salvatore','salvidor','sam','sammie','sammy','sampson','samson','samuel','samuele','sancho','sander','sanders','sanderson','sandor','sandro','sandy','sanford','sanson','sansone','sarge','sargent','sascha','sasha','saul','sauncho','saunder','saunders','saunderson','saundra','saw','sawyer','sawyere','sax','saxe','saxon','say','sayer','sayers','sayre','sayres','scarface','schroeder','schuyler','scot','scott','scotti','scottie','scotty','seamus','sean','sebastian','sebastiano','sebastien','see','selby','selig','serge','sergeant','sergei','sergent','sergio','seth','seymour','shadow','shaine','shalom','shamus','shanan','shane','shannan','shannon','shaughn','shaun','shaw','shawn','shay','shayne','shea','sheff','sheffie','sheffield','sheffy','shelby','shelden','sheldon','shell','shelley','shelton','shem','shep','shepard','shepherd','sheppard','shepperd','sheridan','sherlock','sherlocke','sherman','sherwin','sherwood','sherwynd','shimon','shlomo','sholom','shorty','shurlock','shurlocke','shurwood','si','sibyl','sid','siddhartha','sidnee','sidney','siegfried','siffre','sig','sigfrid','sigfried','sigmund','silas','silvain','silvan','silvano','silvanus','silvester','silvio','sim','simeon','simmonds','simon','simone','sinclair','sinclare','sivert','siward','skell','skelly','skip','skipp','skipper','skippie','skippy','skipton','sky','skye','skylar','skyler','slade','slim','sloan','sloane','sly','smith','smitty','socrates','sol','sollie','solly','solomon','somerset','son','sonnie','sonny','sparky','spence','spencer','spense','spenser','spike','spiro','spiros','spud','srinivas','stacy','staffard','stafford','staford','stan','standford','stanfield','stanford','stanislaw','stanleigh','stanley','stanly','stanton','stanwood','stavros','stearn','stearne','stefan','stefano','steffen','stephan','stephanus','stephen','sterling','stern','sterne','steve','steven','stevie','stevy','stew','steward','stewart','stig','stillman','stillmann','sting','stinky','stirling','stu','stuart','sturgis','sullivan','sully','sumner','sunny','sutherland','sutton','sven','swen','syd','sydney','sylvan','sylvester','tab','tabb','tabbie','tabby','taber','tabor','tad','tadd','taddeo','taddeus','tadeas','tailor','tait','taite','talbert','talbot','tallie','tally','tam','tamas','tammie','tammy','tan','tann','tanner','tanney','tannie','tanny','tarrance','tarrant','tarzan','tate','taylor','teador','ted','tedd','teddie','teddy','tedie','tedman','tedmund','tedrick','temp','temple','templeton','teodoor','teodor','teodorico','teodoro','terence','terencio','terrance','terrel','terrell','terrence','terri','terrill','terry','thacher','thad','thaddeus','thaddius','thaddus','thadeus','thain','thaine','thane','tharen','thatch','thatcher','thaxter','thayne','thebault','thedric','thedrick','theo','theobald','theodor','theodore','theodoric','theophyllus','thibaud','thibaut','thom','thomas','thor','thorn','thorndike','thornie','thornton','thorny','thorpe','thorstein','thorsten','thorvald','thurstan','thurston','tibold','tiebold','tiebout','tiler','tim','timmie','timmy','timothee','timotheus','timothy','tirrell','tito','titos','titus','tobe','tobiah','tobias','tobie','tobin','tobit','toby','tod','todd','toddie','toddy','tom','tomas','tome','tomkin','tomlin','tommie','tommy','tonnie','tony','tore','torey','torin','torr','torrance','torre','torrence','torrey','torrin','torry','town','towney','townie','townsend','towny','trace','tracey','tracie','tracy','traver','travers','travis','tray','tre','tremain','tremaine','tremayne','trent','trenton','trev','trevar','trever','trevor','trey','trip','tristan','troy','truman','tuck','tucker','tuckie','tucky','tudor','tull','tulley','tully','turner','ty','tybalt','tye','tyler','tymon','tymothy','tynan','tyrone','tyrus','tyson','udale','udall','udell','ugo','ulberto','uli','ulick','ulises','ulric','ulrich','ulrick','ulysses','umberto','upton','urbain','urban','urbano','urbanus','uri','uriah','uriel','urson','vachel','vaclav','vail','val','valdemar','vale','valentin','valentine','van','vance','vasili','vasilis','vasily','vassili','vassily','vaughan','vaughn','venkat','verge','vergil','vern','verne','vernen','verney','vernon','vernor','vic','vick','victor','vijay','vilhelm','vin','vince','vincent','vincents','vinnie','vinny','vinod','virge','virgie','virgil','virgilio','vite','vito','vlad','vladamir','vladimir','voltaire','von','wade','wadsworth','wain','waine','wainwright','wait','waite','waiter','wake','wakefield','wald','waldemar','walden','waldo','waldon','waleed','walker','wallace','wallache','wallas','wallie','wallis','wally','walsh','walt','walter','walther','walton','wang','ward','warde','warden','ware','waring','warner','warren','wash','washington','wat','waverley','waverly','way','waylan','wayland','waylen','waylin','waylon','wayne','web','webb','weber','webster','weidar','weider','welbie','welby','welch','wells','welsh','wendall','wendel','wendell','werner','wes','wesley','weslie','west','westbrook','westbrooke','westleigh','westley','weston','weylin','wheeler','whit','whitaker','whitby','whitman','whitney','whittaker','wiatt','wilber','wilbert','wilbur','wilburn','wilburt','wilden','wildon','wilek','wiley','wilfred','wilfrid','wilhelm','will','willard','willdon','willem','willey','willi','william','willie','willis','willmott','willy','wilmar','wilmer','wilson','wilt','wilton','win','windham','winfield','winford','winfred','winifield','winn','winnie','winny','winslow','winston','winthrop','winton','wit','witold','wittie','witty','wojciech','wolf','wolfgang','wolfie','wolfram','wolfy','woochang','wood','woodie','woodman','woodrow','woody','worden','worth','worthington','worthy','wright','wyatan','wyatt','wye','wylie','wyn','wyndham','wynn','wynton','xavier','xenos','xerxes','xever','ximenes','ximenez','xymenes','yaakov','yacov','yale','yanaton','yance','yancey','yancy','yank','yankee','yard','yardley','yehudi','yigal','yule','yuri','yves','zach','zacharia','zachariah','zacharias','zacharie','zachary','zacherie','zachery','zack','zackariah','zak','zalman','zane','zared','zary','zeb','zebadiah','zebedee','zebulen','zebulon','zechariah','zed','zedekiah','zeke','zelig','zerk','zeus','zippy','zollie','zolly','zorro','rahul','shumeet','vibhu' ];
        $female_names = [ 'abagael','abagail','abbe','abbey','abbi','abbie','abby','abigael','abigail','abigale','abra','acacia','ada','adah','adaline','adara','addie','addis','adel','adela','adelaide','adele','adelice','adelina','adelind','adeline','adella','adelle','adena','adey','adi','adiana','adina','adora','adore','adoree','adorne','adrea','adria','adriaens','adrian','adriana','adriane','adrianna','adrianne','adrien','adriena','adrienne','aeriel','aeriela','aeriell','ag','agace','agata','agatha','agathe','aggi','aggie','aggy','agna','agnella','agnes','agnese','agnesse','agneta','agnola','agretha','aida','aidan','aigneis','aila','aile','ailee','aileen','ailene','ailey','aili','ailina','ailyn','aime','aimee','aimil','aina','aindrea','ainslee','ainsley','ainslie','ajay','alaine','alameda','alana','alanah','alane','alanna','alayne','alberta','albertina','albertine','albina','alecia','aleda','aleece','aleecia','aleen','alejandra','alejandrina','alena','alene','alessandra','aleta','alethea','alex','alexa','alexandra','alexandrina','alexi','alexia','alexina','alexine','alexis','alfie','alfreda','ali','alia','alica','alice','alicea','alicia','alida','alidia','alina','aline','alis','alisa','alisha','alison','alissa','alisun','alix','aliza','alla','alleen','allegra','allene','alli','allianora','allie','allina','allis','allison','allissa','allsun','ally','allyce','allyn','allys','allyson','alma','almeda','almeria','almeta','almira','almire','aloise','aloisia','aloysia','alpa','alta','althea','alvera','alvina','alvinia','alvira','alyce','alyda','alys','alysa','alyse','alysia','alyson','alyss','alyssa','amabel','amabelle','amalea','amalee','amaleta','amalia','amalie','amalita','amalle','amanda','amandi','amandie','amandy','amara','amargo','amata','amber','amberly','ambrosia','ambur','ame','amelia','amelie','amelina','ameline','amelita','ami','amie','amity','ammamaria','amy','ana','anabel','anabella','anabelle','anais','analiese','analise','anallese','anallise','anastasia','anastasie','anastassia','anatola','andee','andi','andie','andra','andrea','andreana','andree','andrei','andria','andriana','andriette','andromache','andromeda','andy','anestassia','anet','anett','anetta','anette','ange','angel','angela','angele','angelia','angelica','angelika','angelina','angeline','angelique','angelita','angelle','angie','angil','angy','ania','anica','anissa','anita','anitra','anja','anjanette','anjela','ann','ann-mari','ann-marie','anna','anna-diana','anna-diane','anna-maria','annabal','annabel','annabela','annabell','annabella','annabelle','annadiana','annadiane','annalee','annalena','annaliese','annalisa','annalise','annalyse','annamari','annamaria','annamarie','anne','anne-corinne','anne-mar','anne-marie','annecorinne','anneliese','annelise','annemarie','annetta','annette','anni','annice','annie','annissa','annmaria','annmarie','annnora','annora','anny','anselma','ansley','anstice','anthe','anthea','anthia','antoinette','antonella','antonetta','antonia','antonie','antonietta','antonina','anya','aphrodite','appolonia','april','aprilette','ara','arabel','arabela','arabele','arabella','arabelle','arda','ardath','ardeen','ardelia','ardelis','ardella','ardelle','arden','ardene','ardenia','ardine','ardis','ardith','ardra','ardyce','ardys','ardyth','aretha','ariadne','ariana','arianne','aridatha','ariel','ariela','ariella','arielle','arlana','arlee','arleen','arlen','arlena','arlene','arleta','arlette','arleyne','arlie','arliene','arlina','arlinda','arline','arly','arlyn','arlyne','aryn','ashely','ashlee','ashleigh','ashlen','ashley','ashli','ashlie','ashly','asia','astra','astrid','astrix','atalanta','athena','athene','atlanta','atlante','auberta','aubine','aubree','aubrette','aubrey','aubrie','aubry','audi','audie','audra','audre','audrey','audrie','audry','audrye','audy','augusta','auguste','augustina','augustine','aura','aurea','aurel','aurelea','aurelia','aurelie','auria','aurie','aurilia','aurlie','auroora','aurora','aurore','austin','austina','austine','ava','aveline','averil','averyl','avie','avis','aviva','avivah','avril','avrit','ayn','bab','babara','babette','babita','babs','bambi','bambie','bamby','barb','barbabra','barbara','barbara-anne','barbaraanne','barbe','barbee','barbette','barbey','barbi','barbie','barbra','barby','bari','barrie','barry','basia','bathsheba','batsheva','bea','beatrice','beatrisa','beatrix','beatriz','beau','bebe','becca','becka','becki','beckie','becky','bee','beilul','beitris','bekki','bel','belia','belicia','belinda','belita','bell','bella','bellamy','bellanca','belle','bellina','belva','belvia','bendite','benedetta','benedicta','benedikta','benetta','benita','benni','bennie','benny','benoite','berenice','beret','berget','berna','bernadene','bernadette','bernadina','bernadine','bernardina','bernardine','bernelle','bernete','bernetta','bernette','berni','bernice','bernie','bernita','berny','berri','berrie','berry','bert','berta','berte','bertha','berthe','berti','bertie','bertina','bertine','berty','beryl','beryle','bess','bessie','bessy','beth','bethanne','bethany','bethena','bethina','betsey','betsy','betta','bette','bette-ann','betteann','betteanne','betti','bettie','bettina','bettine','betty','bettye','beulah','bev','beverie','beverlee','beverlie','beverly','bevvy','bianca','bianka','biddy','bidget','bill','billi','billie','billy','binni','binnie','binny','bird','birdie','birgit','birgitta','blair','blaire','blake','blakelee','blakeley','blanca','blanch','blancha','blanche','blinni','blinnie','blinny','bliss','blisse','blithe','blondell','blondelle','blondie','blondy','blythe','bo','bobbette','bobbi','bobbie','bobby','bobette','bobina','bobine','bobinette','bonita','bonnee','bonni','bonnie','bonny','brana','brandais','brande','brandea','brandi','brandice','brandie','brandise','brandy','brea','breanne','brear','bree','breena','bren','brena','brenda','brenn','brenna','brett','bria','briana','brianna','brianne','bride','bridget','bridgett','bridgette','bridie','brier','brietta','brigid','brigida','brigit','brigitta','brigitte','brina','briney','briny','brit','brita','britaney','britani','briteny','britney','britni','britt','britta','brittan','brittany','britte','brittney','brook','brooke','brooks','brunella','brunhilda','brunhilde','bryana','bryn','bryna','brynn','brynna','brynne','buffy','bunni','bunnie','bunny','burta','cabrina','cacilia','cacilie','caitlin','caitrin','cal','calida','calla','calley','calli','callida','callie','cally','calypso','cam','camala','camel','camella','camellia','cameo','cami','camila','camile','camilla','camille','cammi','cammie','cammy','canada','candace','candi','candice','candida','candide','candie','candis','candra','candy','cappella','caprice','cara','caralie','caren','carena','caresa','caressa','caresse','carey','cari','caria','carie','caril','carilyn','carin','carina','carine','cariotta','carissa','carita','caritta','carla','carlee','carleen','carlen','carlena','carlene','carley','carli','carlie','carlin','carlina','carline','carlisle','carlita','carlota','carlotta','carly','carlye','carlyn','carlynn','carlynne','carma','carmel','carmela','carmelia','carmelina','carmelita','carmella','carmelle','carmen','carmina','carmine','carmita','carmon','caro','carol','carol-jean','carola','carolan','carolann','carole','carolee','caroleen','carolie','carolin','carolina','caroline','caroljean','carolyn','carolyne','carolynn','caron','carree','carri','carrie','carrissa','carrol','carroll','carry','cary','caryl','caryn','casandra','casey','casi','casia','casie','cass','cassandra','cassandre','cassandry','cassaundra','cassey','cassi','cassie','cassondra','cassy','cat','catarina','cate','caterina','catha','catharina','catharine','cathe','cathee','catherin','catherina','catherine','cathi','cathie','cathleen','cathlene','cathrin','cathrine','cathryn','cathy','cathyleen','cati','catie','catina','catlaina','catlee','catlin','catrina','catriona','caty','cayla','cecelia','cecil','cecile','ceciley','cecilia','cecilla','cecily','ceil','cele','celene','celesta','celeste','celestia','celestina','celestine','celestyn','celestyna','celia','celie','celina','celinda','celine','celinka','celisse','celle','cesya','chad','chanda','chandal','chandra','channa','chantal','chantalle','charil','charin','charis','charissa','charisse','charita','charity','charla','charlean','charleen','charlena','charlene','charline','charlot','charlott','charlotta','charlotte','charmain','charmaine','charmane','charmian','charmine','charmion','charo','charyl','chastity','chelsae','chelsea','chelsey','chelsie','chelsy','cher','chere','cherey','cheri','cherianne','cherice','cherida','cherie','cherilyn','cherilynn','cherin','cherise','cherish','cherlyn','cherri','cherrita','cherry','chery','cherye','cheryl','cheslie','chiarra','chickie','chicky','chiquita','chloe','chloette','chloris','chris','chriss','chrissa','chrissie','chrissy','christa','christabel','christabella','christabelle','christal','christalle','christan','christean','christel','christen','christi','christian','christiana','christiane','christie','christin','christina','christine','christy','christyna','chrysa','chrysler','chrystal','chryste','chrystel','ciara','cicely','cicily','ciel','cilka','cinda','cindee','cindelyn','cinderella','cindi','cindie','cindra','cindy','cinnamon','cissie','cissy','clair','claire','clara','clarabelle','clare','claresta','clareta','claretta','clarette','clarey','clari','claribel','clarice','clarie','clarinda','clarine','clarisa','clarissa','clarisse','clarita','clary','claude','claudelle','claudetta','claudette','claudia','claudie','claudina','claudine','clea','clem','clemence','clementia','clementina','clementine','clemmie','clemmy','cleo','cleopatra','clerissa','cleva','clio','clo','cloe','cloris','clotilda','clovis','codee','codi','codie','cody','coleen','colene','coletta','colette','colleen','collete','collette','collie','colline','colly','con','concettina','conchita','concordia','conney','conni','connie','conny','consolata','constance','constancia','constancy','constanta','constantia','constantina','constantine','consuela','consuelo','cookie','cora','corabel','corabella','corabelle','coral','coralie','coraline','coralyn','cordelia','cordelie','cordey','cordie','cordula','cordy','coreen','corella','corena','corenda','corene','coretta','corette','corey','cori','corie','corilla','corina','corine','corinna','corinne','coriss','corissa','corliss','corly','cornela','cornelia','cornelle','cornie','corny','correna','correy','corri','corrianne','corrie','corrina','corrine','corrinne','corry','cortney','cory','cosetta','cosette','courtenay','courtney','cresa','cris','crissie','crissy','crista','cristabel','cristal','cristen','cristi','cristie','cristin','cristina','cristine','cristionna','cristy','crysta','crystal','crystie','cyb','cybal','cybel','cybelle','cybil','cybill','cyndi','cyndy','cynthea','cynthia','cynthie','cynthy','dacey','dacia','dacie','dacy','dael','daffi','daffie','daffy','dafna','dagmar','dahlia','daile','daisey','daisi','daisie','daisy','dale','dalenna','dalia','dalila','dallas','daloris','damara','damaris','damita','dana','danell','danella','danelle','danette','dani','dania','danica','danice','daniel','daniela','daniele','daniella','danielle','danika','danila','danit','danita','danna','danni','dannie','danny','dannye','danya','danyelle','danyette','daphene','daphna','daphne','dara','darb','darbie','darby','darcee','darcey','darci','darcie','darcy','darda','dareen','darell','darelle','dari','daria','darice','darla','darleen','darlene','darline','darryl','darsey','darsie','darya','daryl','daryn','dasha','dasi','dasie','dasya','datha','daune','daveen','daveta','davida','davina','davine','davita','dawn','dawna','dayle','dayna','dea','deana','deane','deanna','deanne','deb','debbi','debbie','debbra','debby','debee','debera','debi','debor','debora','deborah','debra','dede','dedie','dedra','dee','dee dee','deeann','deeanne','deedee','deena','deerdre','dehlia','deidre','deina','deirdre','del','dela','delaney','delcina','delcine','delia','delila','delilah','delinda','dell','della','delly','delora','delores','deloria','deloris','delphina','delphine','delphinia','demeter','demetra','demetria','demetris','dena','deni','denice','denise','denna','denni','dennie','denny','deny','denys','denyse','deonne','desaree','desdemona','desirae','desiree','desiri','deva','devan','devi','devin','devina','devinne','devon','devondra','devonna','devonne','devora','dew','di','diahann','diamond','dian','diana','diandra','diane','diane-marie','dianemarie','diann','dianna','dianne','diannne','didi','dido','diena','dierdre','dina','dinah','dinnie','dinny','dion','dione','dionis','dionne','dita','dix','dixie','dode','dodi','dodie','dody','doe','doll','dolley','dolli','dollie','dolly','dolora','dolores','dolorita','doloritas','dominica','dominique','dona','donella','donelle','donetta','donia','donica','donielle','donna','donnajean','donnamarie','donni','donnie','donny','dora','doralia','doralin','doralyn','doralynn','doralynne','dorcas','dore','doreen','dorelia','dorella','dorelle','dorena','dorene','doretta','dorette','dorey','dori','doria','dorian','dorice','dorie','dorine','doris','dorisa','dorise','dorit','dorita','doro','dorolice','dorolisa','dorotea','doroteya','dorothea','dorothee','dorothy','dorree','dorri','dorrie','dorris','dorry','dorthea','dorthy','dory','dosi','dot','doti','dotti','dottie','dotty','dove','drea','drew','dulce','dulcea','dulci','dulcia','dulciana','dulcie','dulcine','dulcinea','dulcy','dulsea','dusty','dyan','dyana','dyane','dyann','dyanna','dyanne','dyna','dynah','e\'lane','eada','eadie','eadith','ealasaid','eartha','easter','eba','ebba','ebonee','ebony','eda','eddi','eddie','eddy','ede','edee','edeline','eden','edi','edie','edin','edita','edith','editha','edithe','ediva','edna','edwina','edy','edyth','edythe','effie','eileen','eilis','eimile','eirena','ekaterina','elaina','elaine','elana','elane','elayne','elberta','elbertina','elbertine','eleanor','eleanora','eleanore','electra','elena','elene','eleni','elenore','eleonora','eleonore','elfie','elfreda','elfrida','elfrieda','elga','elianora','elianore','elicia','elie','elinor','elinore','elisa','elisabet','elisabeth','elisabetta','elise','elisha','elissa','elita','eliza','elizabet','elizabeth','elka','elke','ella','elladine','elle','ellen','ellene','ellette','elli','ellie','ellissa','elly','ellyn','ellynn','elmira','elna','elnora','elnore','eloisa','eloise','elonore','elora','elsa','elsbeth','else','elsey','elsi','elsie','elsinore','elspeth','elsy','elva','elvera','elvina','elvira','elwina','elwira','elyn','elyse','elysee','elysha','elysia','elyssa','em','ema','emalee','emalia','emanuela','emelda','emelia','emelina','emeline','emelita','emelyne','emera','emilee','emili','emilia','emilie','emiline','emily','emlyn','emlynn','emlynne','emma','emmalee','emmaline','emmalyn','emmalynn','emmalynne','emmeline','emmey','emmi','emmie','emmy','emmye','emogene','emyle','emylee','endora','engracia','enid','enrica','enrichetta','enrika','enriqueta','enya','eolanda','eolande','eran','erda','erena','erica','ericha','ericka','erika','erin','erina','erinn','erinna','erma','ermengarde','ermentrude','ermina','erminia','erminie','erna','ernaline','ernesta','ernestine','ertha','eryn','esma','esmaria','esme','esmeralda','esmerelda','essa','essie','essy','esta','estel','estele','estell','estella','estelle','ester','esther','estrella','estrellita','ethel','ethelda','ethelin','ethelind','etheline','ethelyn','ethyl','etta','etti','ettie','etty','eudora','eugenia','eugenie','eugine','eula','eulalie','eunice','euphemia','eustacia','eva','evaleen','evangelia','evangelin','evangelina','evangeline','evania','evanne','eve','eveleen','evelina','eveline','evelyn','evette','evey','evie','evita','evonne','evvie','evvy','evy','eyde','eydie','fabrianne','fabrice','fae','faina','faith','fallon','fan','fanchette','fanchon','fancie','fancy','fanechka','fania','fanni','fannie','fanny','fanya','fara','farah','farand','farica','farra','farrah','farrand','fatima','faun','faunie','faustina','faustine','fawn','fawna','fawne','fawnia','fay','faydra','faye','fayette','fayina','fayre','fayth','faythe','federica','fedora','felecia','felicdad','felice','felicia','felicity','felicle','felipa','felisha','felita','feliza','fenelia','feodora','ferdinanda','ferdinande','fern','fernanda','fernande','fernandina','ferne','fey','fiann','fianna','fidela','fidelia','fidelity','fifi','fifine','filia','filide','filippa','fina','fiona','fionna','fionnula','fiorenze','fleur','fleurette','flo','flor','flora','florance','flore','florella','florence','florencia','florentia','florenza','florette','flori','floria','florice','florida','florie','florina','florinda','floris','florri','florrie','florry','flory','flossi','flossie','flossy','flower','fortuna','fortune','fran','france','francene','frances','francesca','francesmary','francine','francis','francisca','franciska','francoise','francyne','frank','frankie','franky','franni','frannie','franny','frayda','fred','freda','freddi','freddie','freddy','fredelia','frederica','fredericka','fredi','fredia','fredra','fredrika','freida','frieda','friederike','fulvia','gabbey','gabbi','gabbie','gabey','gabi','gabie','gabriel','gabriela','gabriell','gabriella','gabrielle','gabriellia','gabrila','gaby','gae','gael','gail','gale','gale ','galina','garland','garnet','garnette','gates','gavra','gavrielle','gay','gayla','gayle','gayleen','gaylene','gaynor','gelya','gen','gena','gene','geneva','genevieve','genevra','genia','genna','genni','gennie','gennifer','genny','genovera','genvieve','george','georgeanna','georgeanne','georgena','georgeta','georgetta','georgette','georgia','georgiamay','georgiana','georgianna','georgianne','georgie','georgina','georgine','gera','geralda','geraldina','geraldine','gerda','gerhardine','geri','gerianna','gerianne','gerladina','germain','germaine','germana','gerri','gerrie','gerrilee','gerry','gert','gerta','gerti','gertie','gertrud','gertruda','gertrude','gertrudis','gerty','giacinta','giana','gianina','gianna','gigi','gilberta','gilberte','gilbertina','gilbertine','gilda','gill','gillan','gilli','gillian','gillie','gilligan','gilly','gina','ginelle','ginevra','ginger','ginni','ginnie','ginnifer','ginny','giorgia','giovanna','gipsy','giralda','gisela','gisele','gisella','giselle','gizela','glad','gladi','gladis','gladys','gleda','glen','glenda','glenine','glenn','glenna','glennie','glennis','glori','gloria','gloriana','gloriane','glorianna','glory','glyn','glynda','glynis','glynnis','godiva','golda','goldarina','goldi','goldia','goldie','goldina','goldy','grace','gracia','gracie','grata','gratia','gratiana','gray','grayce','grazia','gredel','greer','greta','gretal','gretchen','grete','gretel','grethel','gretna','gretta','grier','griselda','grissel','guendolen','guenevere','guenna','guglielma','gui','guillema','guillemette','guinevere','guinna','gunilla','gunvor','gus','gusella','gussi','gussie','gussy','gusta','gusti','gustie','gusty','gwen','gwendolen','gwendolin','gwendolyn','gweneth','gwenette','gwenn','gwenneth','gwenni','gwennie','gwenny','gwenora','gwenore','gwyn','gwyneth','gwynne','gypsy','hadria','hailee','haily','haleigh','halette','haley','hali','halie','halimeda','halley','halli','hallie','hally','hana','hanna','hannah','hanni','hannibal','hannie','hannis','hanny','happy','harlene','harley','harli','harlie','harmonia','harmonie','harmony','harri','harrie','harriet','harriett','harrietta','harriette','harriot','harriott','hatti','hattie','hatty','havivah','hayley','hazel','heath','heather','heda','hedda','heddi','heddie','hedi','hedvig','hedwig','hedy','heida','heide','heidi','heidie','helaina','helaine','helen','helen-elizabeth','helena','helene','helga','helge','helise','hellene','helli','heloise','helsa','helyn','hendrika','henka','henrie','henrieta','henrietta','henriette','henryetta','hephzibah','hermia','hermina','hermine','herminia','hermione','herta','hertha','hester','hesther','hestia','hetti','hettie','hetty','hilarie','hilary','hilda','hildagard','hildagarde','hilde','hildegaard','hildegarde','hildy','hillary','hilliary','hinda','holley','holli','hollie','holly','holly-anne','hollyanne','honey','honor','honoria','hope','horatia','hortense','hortensia','hulda','hyacinth','hyacintha','hyacinthe','hyacinthia','hyacinthie','hynda','ianthe','ibbie','ibby','ida','idalia','idalina','idaline','idell','idelle','idette','ike','ikey','ilana','ileana','ileane','ilene','ilise','ilka','illa','ilona','ilsa','ilse','ilysa','ilyse','ilyssa','imelda','imogen','imogene','imojean','ina','inci','indira','ines','inesita','inessa','inez','inga','ingaberg','ingaborg','inge','ingeberg','ingeborg','inger','ingrid','ingunna','inna','ioana','iolande','iolanthe','iona','iormina','ira','irena','irene','irina','iris','irita','irma','isa','isabeau','isabel','isabelita','isabella','isabelle','isador','isadora','isadore','isahella','iseabal','isidora','isis','isobel','issi','issie','issy','ivett','ivette','ivie','ivonne','ivory','ivy','izabel','izzi','jacenta','jacinda','jacinta','jacintha','jacinthe','jackelyn','jacki','jackie','jacklin','jacklyn','jackquelin','jackqueline','jacky','jaclin','jaclyn','jacquelin','jacqueline','jacquelyn','jacquelynn','jacquenetta','jacquenette','jacquetta','jacquette','jacqui','jacquie','jacynth','jada','jade','jaime','jaimie','jaine','jaleh','jami','jamie','jamima','jammie','jan','jana','janaya','janaye','jandy','jane','janean','janeczka','janeen','janel','janela','janella','janelle','janene','janenna','janessa','janet','janeta','janetta','janette','janeva','janey','jania','janice','janie','janifer','janina','janine','janis','janith','janka','janna','jannel','jannelle','janot','jany','jaquelin','jaquelyn','jaquenetta','jaquenette','jaquith','jasmin','jasmina','jasmine','jayme','jaymee','jayne','jaynell','jazmin','jean','jeana','jeane','jeanelle','jeanette','jeanie','jeanine','jeanna','jeanne','jeannette','jeannie','jeannine','jehanna','jelene','jemie','jemima','jemimah','jemmie','jemmy','jen','jena','jenda','jenelle','jenette','jeni','jenica','jeniece','jenifer','jeniffer','jenilee','jenine','jenn','jenna','jennee','jennette','jenni','jennica','jennie','jennifer','jennilee','jennine','jenny','jeraldine','jeralee','jere','jeri','jermaine','jerrie','jerrilee','jerrilyn','jerrine','jerry','jerrylee','jess','jessa','jessalin','jessalyn','jessamine','jessamyn','jesse','jesselyn','jessi','jessica','jessie','jessika','jessy','jewel','jewell','jewelle','jill','jillana','jillane','jillayne','jilleen','jillene','jilli','jillian','jillie','jilly','jinny','jo','jo ann','jo-ann','jo-anne','joann','joanne','joan','joana','joane','joanie','joann','joanna','joanne','joannes','jobey','jobi','jobie','jobina','joby','jobye','jobyna','jocelin','joceline','jocelyn','jocelyne','jodee','jodi','jodie','jody','joela','joelie','joell','joella','joelle','joellen','joelly','joellyn','joelynn','joete','joey','johanna','johannah','johnette','johnna','joice','jojo','jolee','joleen','jolene','joletta','joli','jolie','joline','joly','jolyn','jolynn','jonell','joni','jonie','jonis','jordain','jordan','jordana','jordanna','jorey','jori','jorie','jorrie','jorry','joscelin','josee','josefa','josefina','joselyn','josepha','josephina','josephine','josey','josi','josie','joslyn','josselyn','josy','jourdan','joy','joya','joyan','joyann','joyce','joycelin','joye','joyous','juana','juanita','jude','judi','judie','judith','juditha','judy','judye','julee','juli','julia','juliana','juliane','juliann','julianna','julianne','julie','julienne','juliet','julieta','julietta','juliette','julina','juline','julissa','julita','june','junette','junia','junie','junina','justin','justina','justine','jyoti','kacey','kacie','kacy','kai','kaia','kaila','kaile','kailey','kaitlin','kaitlyn','kaitlynn','kaja','kakalina','kala','kaleena','kali','kalie','kalila','kalina','kalinda','kalindi','kalli','kally','kameko','kamila','kamilah','kamillah','kandace','kandy','kania','kanya','kara','kara-lynn','karalee','karalynn','kare','karee','karel','karen','karena','kari','karia','karie','karil','karilynn','karin','karina','karine','kariotta','karisa','karissa','karita','karla','karlee','karleen','karlen','karlene','karlie','karlotta','karlotte','karly','karlyn','karmen','karna','karol','karola','karole','karolina','karoline','karoly','karon','karrah','karrie','karry','kary','karyl','karylin','karyn','kasey','kass','kassandra','kassey','kassi','kassia','kassie','kaster','kat','kata','katalin','kate','katee','katerina','katerine','katey','kath','katha','katharina','katharine','katharyn','kathe','katheleen','katherina','katherine','katheryn','kathi','kathie','kathleen','kathlene','kathlin','kathrine','kathryn','kathryne','kathy','kathye','kati','katie','katina','katine','katinka','katleen','katlin','katrina','katrine','katrinka','katti','kattie','katuscha','katusha','katy','katya','kay','kaycee','kaye','kayla','kayle','kaylee','kayley','kaylil','kaylyn','kee','keeley','keelia','keely','kelcey','kelci','kelcie','kelcy','kelila','kellen','kelley','kelli','kellia','kellie','kellina','kellsie','kelly','kellyann','kelsey','kelsi','kelsy','kendra','kendre','kenna','keren','keri','keriann','kerianne','kerri','kerrie','kerrill','kerrin','kerry','kerstin','kesley','keslie','kessia','kessiah','ketti','kettie','ketty','kevina','kevyn','ki','kia','kiah','kial','kiele','kiersten','kikelia','kiley','kim','kimberlee','kimberley','kimberli','kimberly','kimberlyn','kimbra','kimmi','kimmie','kimmy','kinna','kip','kipp','kippie','kippy','kira','kirbee','kirbie','kirby','kiri','kirsten','kirsteni','kirsti','kirstie','kirstin','kirstyn','kissee','kissiah','kissie','kit','kitti','kittie','kitty','kizzee','kizzie','klara','klarika','klarrisa','konstance','konstanze','koo','kora','koral','koralle','kordula','kore','korella','koren','koressa','kori','korie','korney','korrie','korry','kourtney','kris','krissie','krissy','krista','kristal','kristan','kriste','kristel','kristen','kristi','kristien','kristin','kristina','kristine','kristy','kristyn','krysta','krystal','krystalle','krystle','krystyna','kyla','kyle','kylen','kylie','kylila','kylynn','kym','kynthia','kyrstin','la','lacee','lacey','lacie','lacy','ladonna','laetitia','laila','laina','lainey','lamb','lana','lane','lanette','laney','lani','lanie','lanita','lanna','lanni','lanny','lara','laraine','lari','larina','larine','larisa','larissa','lark','laryssa','latashia','latia','latisha','latrena','latrina','laura','lauraine','laural','lauralee','laure','lauree','laureen','laurel','laurella','lauren','laurena','laurene','lauretta','laurette','lauri','laurianne','laurice','laurie','lauryn','lavena','laverna','laverne','lavina','lavinia','lavinie','layla','layne','layney','lea','leah','leandra','leann','leanna','leanne','leanor','leanora','lebbie','leda','lee','leeann','leeann','leeanne','leela','leelah','leena','leesa','leese','legra','leia','leiah','leigh','leigha','leila','leilah','leisha','lela','lelah','leland','lelia','lena','lenee','lenette','lenka','lenna','lenora','lenore','leodora','leoine','leola','leoline','leona','leonanie','leone','leonelle','leonie','leonora','leonore','leontine','leontyne','leora','leorah','leshia','lesley','lesli','leslie','lesly','lesya','leta','lethia','leticia','letisha','letitia','letta','letti','lettie','letty','leyla','lezlie','lia','lian','liana','liane','lianna','lianne','lib','libbey','libbi','libbie','libby','licha','lida','lidia','lil','lila','lilah','lilas','lilia','lilian','liliane','lilias','lilith','lilla','lilli','lillian','lillis','lilllie','lilly','lily','lilyan','lin','lina','lind','linda','lindi','lindie','lindsay','lindsey','lindsy','lindy','linea','linell','linet','linette','linn','linnea','linnell','linnet','linnie','linzy','liora','liorah','lira','lisa','lisabeth','lisandra','lisbeth','lise','lisetta','lisette','lisha','lishe','lissa','lissi','lissie','lissy','lita','liuka','livia','liz','liza','lizabeth','lizbeth','lizette','lizzie','lizzy','loella','lois','loise','lola','lolande','loleta','lolita','lolly','lona','lonee','loni','lonna','lonni','lonnie','lora','lorain','loraine','loralee','loralie','loralyn','loree','loreen','lorelei','lorelle','loren','lorena','lorene','lorenza','loretta','lorettalorna','lorette','lori','loria','lorianna','lorianne','lorie','lorilee','lorilyn','lorinda','lorine','lorita','lorna','lorne','lorraine','lorrayne','lorri','lorrie','lorrin','lorry','lory','lotta','lotte','lotti','lottie','lotty','lou','louella','louisa','louise','louisette','love','luana','luanna','luce','luci','lucia','luciana','lucie','lucienne','lucila','lucilia','lucille','lucina','lucinda','lucine','lucita','lucky','lucretia','lucy','luella','luelle','luisa','luise','lula','lulita','lulu','luna','lura','lurette','lurleen','lurlene','lurline','lusa','lust','lyda','lydia','lydie','lyn','lynda','lynde','lyndel','lyndell','lyndsay','lyndsey','lyndsie','lyndy','lynea','lynelle','lynett','lynette','lynn','lynna','lynne','lynnea','lynnell','lynnelle','lynnet','lynnett','lynnette','lynsey','lysandra','lyssa','mab','mabel','mabelle','mable','mada','madalena','madalyn','maddalena','maddi','maddie','maddy','madel','madelaine','madeleine','madelena','madelene','madelin','madelina','madeline','madella','madelle','madelon','madelyn','madge','madlen','madlin','madona','madonna','mady','mae','maegan','mag','magda','magdaia','magdalen','magdalena','magdalene','maggee','maggi','maggie','maggy','magna','mahala','mahalia','maia','maible','maiga','mair','maire','mairead','maisey','maisie','mala','malanie','malcah','malena','malia','malina','malinda','malinde','malissa','malissia','malka','malkah','mallissa','mallorie','mallory','malorie','malory','malva','malvina','malynda','mame','mamie','manda','mandi','mandie','mandy','manon','manya','mara','marabel','marcela','marcelia','marcella','marcelle','marcellina','marcelline','marchelle','marci','marcia','marcie','marcile','marcille','marcy','mareah','maren','marena','maressa','marga','margalit','margalo','margaret','margareta','margarete','margaretha','margarethe','margaretta','margarette','margarita','margaux','marge','margeaux','margery','marget','margette','margi','margie','margit','marglerite','margo','margot','margret','marguerite','margurite','margy','mari','maria','mariam','marian','mariana','mariann','marianna','marianne','maribel','maribelle','maribeth','marice','maridel','marie','marie-ann','marie-jeanne','marieann','mariejeanne','mariel','mariele','marielle','mariellen','marietta','mariette','marigold','marijo','marika','marilee','marilin','marillin','marilyn','marin','marina','marinna','marion','mariquilla','maris','marisa','mariska','marissa','marit','marita','maritsa','mariya','marj','marja','marje','marji','marjie','marjorie','marjory','marjy','marketa','marla','marlane','marleah','marlee','marleen','marlena','marlene','marley','marlie','marline','marlo','marlyn','marna','marne','marney','marni','marnia','marnie','marquita','marrilee','marris','marrissa','marry','marsha','marsiella','marta','martelle','martguerita','martha','marthe','marthena','marti','martica','martie','martina','martita','marty','martynne','mary','marya','maryangelyn','maryann','maryanna','maryanne','marybelle','marybeth','maryellen','maryjane','maryjo','maryl','marylee','marylin','marylinda','marylou','marylynne','maryrose','marys','marysa','masha','matelda','mathilda','mathilde','matilda','matilde','matti','mattie','matty','maud','maude','maudie','maura','maure','maureen','maureene','maurene','maurine','maurise','maurita','mavis','mavra','max','maxi','maxie','maxine','maxy','may','maya','maybelle','mayda','maye','mead','meade','meagan','meaghan','meara','mechelle','meg','megan','megen','meggan','meggi','meggie','meggy','meghan','meghann','mehetabel','mei','meira','mel','mela','melamie','melania','melanie','melantha','melany','melba','melesa','melessa','melicent','melina','melinda','melinde','melisa','melisande','melisandra','melisenda','melisent','melissa','melisse','melita','melitta','mella','melli','mellicent','mellie','mellisa','mellisent','mellissa','melloney','melly','melodee','melodie','melody','melonie','melony','melosa','melva','mercedes','merci','mercie','mercy','meredith','meredithe','meridel','meridith','meriel','merilee','merilyn','meris','merissa','merl','merla','merle','merlina','merline','merna','merola','merralee','merridie','merrie','merrielle','merrile','merrilee','merrili','merrill','merrily','merry','mersey','meryl','meta','mia','micaela','michaela','michaelina','michaeline','michaella','michal','michel','michele','michelina','micheline','michell','michelle','micki','mickie','micky','midge','mignon','mignonne','miguela','miguelita','mildred','mildrid','milena','milicent','milissent','milka','milli','millicent','millie','millisent','milly','milzie','mimi','min','mina','minda','mindy','minerva','minetta','minette','minna','minni','minnie','minny','minta','miquela','mira','mirabel','mirabella','mirabelle','miran','miranda','mireielle','mireille','mirella','mirelle','miriam','mirilla','mirna','misha','missie','missy','misti','misty','mitra','mitzi','mmarianne','modesta','modestia','modestine','modesty','moina','moira','moll','mollee','molli','mollie','molly','mommy','mona','monah','monica','monika','monique','mora','moreen','morena','morgan','morgana','morganica','morganne','morgen','moria','morissa','morlee','morna','moselle','moya','moyna','moyra','mozelle','muffin','mufi','mufinella','muire','mureil','murial','muriel','murielle','myna','myra','myrah','myranda','myriam','myrilla','myrle','myrlene','myrna','myrta','myrtia','myrtice','myrtie','myrtle','nada','nadean','nadeen','nadia','nadine','nadiya','nady','nadya','nalani','nan','nana','nananne','nance','nancee','nancey','nanci','nancie','nancy','nanete','nanette','nani','nanice','nanine','nannette','nanni','nannie','nanny','nanon','naoma','naomi','nara','nari','nariko','nat','nata','natala','natalee','natalia','natalie','natalina','nataline','natalya','natasha','natassia','nathalia','nathalie','natka','natty','neala','neda','nedda','nedi','neely','neila','neile','neilla','neille','nela','nelia','nelie','nell','nelle','nelli','nellie','nelly','nena','nerissa','nerita','nert','nerta','nerte','nerti','nertie','nerty','nessa','nessi','nessie','nessy','nesta','netta','netti','nettie','nettle','netty','nevsa','neysa','nichol','nichole','nicholle','nicki','nickie','nicky','nicol','nicola','nicole','nicolea','nicolette','nicoli','nicolina','nicoline','nicolle','nidia','nike','niki','nikki','nikkie','nikoletta','nikolia','nil','nina','ninetta','ninette','ninnetta','ninnette','ninon','nisa','nissa','nisse','nissie','nissy','nita','nitin','nixie','noami','noel','noelani','noell','noella','noelle','noellyn','noelyn','noemi','nola','nolana','nolie','nollie','nomi','nona','nonah','noni','nonie','nonna','nonnah','nora','norah','norean','noreen','norene','norina','norine','norma','norri','norrie','norry','nova','novelia','nydia','nyssa','octavia','odele','odelia','odelinda','odella','odelle','odessa','odetta','odette','odilia','odille','ofelia','ofella','ofilia','ola','olenka','olga','olia','olimpia','olive','olivette','olivia','olivie','oliy','ollie','olly','olva','olwen','olympe','olympia','olympie','ondrea','oneida','onida','onlea','oona','opal','opalina','opaline','ophelia','ophelie','oprah','ora','oralee','oralia','oralie','oralla','oralle','orel','orelee','orelia','orelie','orella','orelle','oreste','oriana','orly','orsa','orsola','ortensia','otha','othelia','othella','othilia','othilie','ottilie','pacifica','page','paige','paloma','pam','pamela','pamelina','pamella','pammi','pammie','pammy','pandora','pansie','pansy','paola','paolina','parwane','pat','patience','patrica','patrice','patricia','patrizia','patsy','patti','pattie','patty','paula','paula-grace','paule','pauletta','paulette','pauli','paulie','paulina','pauline','paulita','pauly','pavia','pavla','pearl','pearla','pearle','pearline','peg','pegeen','peggi','peggie','peggy','pen','penelopa','penelope','penni','pennie','penny','pepi','pepita','peri','peria','perl','perla','perle','perri','perrine','perry','persis','pet','peta','petra','petrina','petronella','petronia','petronilla','petronille','petunia','phaedra','phaidra','phebe','phedra','phelia','phil','philipa','philippa','philippe','philippine','philis','phillida','phillie','phillis','philly','philomena','phoebe','phylis','phyllida','phyllis','phyllys','phylys','pia','pier','pierette','pierrette','pietra','piper','pippa','pippy','polly','pollyanna','pooh','poppy','portia','pris','prisca','priscella','priscilla','prissie','pru','prudence','prudi','prudy','prue','prunella','queada','queenie','quentin','querida','quinn','quinta','quintana','quintilla','quintina','rachael','rachel','rachele','rachelle','rae','raf','rafa','rafaela','rafaelia','rafaelita','ragnhild','rahal','rahel','raina','raine','rakel','ralina','ramona','ramonda','rana','randa','randee','randene','randi','randie','randy','ranee','rani','rania','ranice','ranique','ranna','raphaela','raquel','raquela','rasia','rasla','raven','ray','raychel','raye','rayna','raynell','rayshell','rea','reba','rebbecca','rebe','rebeca','rebecca','rebecka','rebeka','rebekah','rebekkah','ree','reeba','reena','reeta','reeva','regan','reggi','reggie','regina','regine','reiko','reina','reine','remy','rena','renae','renata','renate','rene','renee','renel','renell','renelle','renie','rennie','reta','retha','revkah','rey','reyna','rhea','rheba','rheta','rhetta','rhiamon','rhianna','rhianon','rhoda','rhodia','rhodie','rhody','rhona','rhonda','riane','riannon','rianon','rica','ricca','rici','ricki','rickie','ricky','riki','rikki','rina','risa','rissa','rita','riva','rivalee','rivi','rivkah','rivy','roana','roanna','roanne','robbi','robbie','robbin','robby','robbyn','robena','robenia','roberta','robin','robina','robinet','robinett','robinetta','robinette','robinia','roby','robyn','roch','rochell','rochella','rochelle','rochette','roda','rodi','rodie','rodina','romola','romona','romonda','romy','rona','ronalda','ronda','ronica','ronna','ronni','ronnica','ronnie','ronny','roobbie','rora','rori','rorie','rory','ros','rosa','rosabel','rosabella','rosabelle','rosaleen','rosalia','rosalie','rosalind','rosalinda','rosalinde','rosaline','rosalyn','rosalynd','rosamond','rosamund','rosana','rosanna','rosanne','rosario','rose','roseann','roseanna','roseanne','roselia','roselin','roseline','rosella','roselle','roselyn','rosemaria','rosemarie','rosemary','rosemonde','rosene','rosetta','rosette','roshelle','rosie','rosina','rosita','roslyn','rosmunda','rosy','row','rowe','rowena','roxana','roxane','roxanna','roxanne','roxi','roxie','roxine','roxy','roz','rozalie','rozalin','rozamond','rozanna','rozanne','roze','rozele','rozella','rozelle','rozina','rubetta','rubi','rubia','rubie','rubina','ruby','ruella','ruperta','ruth','ruthann','ruthanne','ruthe','ruthi','ruthie','ruthy','ryann','rycca','saba','sabina','sabine','sabra','sabrina','sacha','sada','sadella','sadie','sal','sallee','salli','sallie','sally','sallyann','sallyanne','salome','sam','samantha','samara','samaria','sammy','samuela','samuella','sande','sandi','sandie','sandra','sandy','sandye','sapphira','sapphire','sara','sara-ann','saraann','sarah','sarajane','saree','sarena','sarene','sarette','sari','sarina','sarine','sarita','sascha','sasha','sashenka','saudra','saundra','savina','sayre','scarlet','scarlett','scotty','sean','seana','secunda','seka','sela','selena','selene','selestina','selia','selie','selina','selinda','seline','sella','selle','selma','sena','sephira','serena','serene','shaina','shaine','shalna','shalne','shamit','shana','shanda','shandee','shandie','shandra','shandy','shane','shani','shanie','shanna','shannah','shannen','shannon','shanon','shanta','shantee','shara','sharai','shari','sharia','sharie','sharity','sharl','sharla','sharleen','sharlene','sharline','sharna','sharon','sharona','sharra','sharron','sharyl','shaun','shauna','shawn','shawna','shawnee','shay','shayla','shaylah','shaylyn','shaylynn','shayna','shayne','shea','sheba','sheela','sheelagh','sheelah','sheena','sheeree','sheila','sheila-kathryn','sheilah','sheilakathryn','shel','shela','shelagh','shelba','shelbi','shelby','shelia','shell','shelley','shelli','shellie','shelly','shena','sher','sheree','sheri','sherie','sheril','sherill','sherilyn','sherline','sherri','sherrie','sherry','sherye','sheryl','shilpa','shina','shir','shira','shirah','shirl','shirlee','shirleen','shirlene','shirley','shirline','shoshana','shoshanna','shoshie','siana','sianna','sib','sibbie','sibby','sibeal','sibel','sibella','sibelle','sibilla','sibley','sibyl','sibylla','sibylle','sidoney','sidonia','sidonnie','sigrid','sile','sileas','silva','silvana','silvia','silvie','simona','simone','simonette','simonne','sindee','sinead','siobhan','sioux','siouxie','sisely','sisile','sissie','sissy','sofia','sofie','solange','sondra','sonia','sonja','sonni','sonnie','sonnnie','sonny','sonya','sophey','sophi','sophia','sophie','sophronia','sorcha','sosanna','stace','stacee','stacey','staci','stacia','stacie','stacy','stafani','star','starla','starlene','starlin','starr','stefa','stefania','stefanie','steffane','steffi','steffie','stella','stepha','stephana','stephani','stephanie','stephannie','stephenie','stephi','stephie','stephine','stesha','stevana','stevena','stoddard','storey','storm','stormi','stormie','stormy','sue','sue-elle','suellen','sukey','suki','sula','sunny','sunshine','susan','susana','susanetta','susann','susanna','susannah','susanne','susette','susi','susie','sussi','susy','suzan','suzann','suzanna','suzanne','suzetta','suzette','suzi','suzie','suzy','suzzy','sybil','sybila','sybilla','sybille','sybyl','sydel','sydelle','sydney','sylvia','sylvie','tabatha','tabbatha','tabbi','tabbie','tabbitha','tabby','tabina','tabitha','taffy','talia','tallia','tallie','tally','talya','talyah','tamar','tamara','tamarah','tamarra','tamera','tami','tamiko','tamma','tammara','tammi','tammie','tammy','tamra','tana','tandi','tandie','tandy','tani','tania','tansy','tanya','tara','tarah','tarra','tarrah','taryn','tasha','tasia','tate','tatiana','tatiania','tatum','tawnya','tawsha','teane','ted','tedda','teddi','teddie','teddy','tedi','tedra','teena','tella','teodora','tera','teresa','teresaanne','terese','teresina','teresita','teressa','teri','teriann','terina','terra','terri','terri-jo','terrianne','terrie','terry','terrye','tersina','teryl','terza','tess','tessa','tessi','tessie','tessy','thalia','thea','theada','theadora','theda','thekla','thelma','theo','theodora','theodosia','theresa','theresa-marie','therese','theresina','theresita','theressa','therine','thia','thomasa','thomasin','thomasina','thomasine','tia','tiana','tiena','tierney','tiertza','tiff','tiffani','tiffanie','tiffany','tiffi','tiffie','tiffy','tilda','tildi','tildie','tildy','tillie','tilly','tim','timi','timmi','timmie','timmy','timothea','tina','tine','tiphani','tiphanie','tiphany','tish','tisha','tobe','tobey','tobi','tobie','toby','tobye','toinette','toma','tomasina','tomasine','tomi','tomiko','tommi','tommie','tommy','toni','tonia','tonie','tony','tonya','tootsie','torey','tori','torie','torrie','tory','tova','tove','trace','tracee','tracey','traci','tracie','tracy','trenna','tresa','trescha','tressa','tricia','trina','trish','trisha','trista','trix','trixi','trixie','trixy','truda','trude','trudey','trudi','trudie','trudy','trula','tuesday','twila','twyla','tybi','tybie','tyne','ula','ulla','ulrica','ulrika','ulrike','umeko','una','ursa','ursala','ursola','ursula','ursulina','ursuline','uta','val','valaree','valaria','vale','valeda','valencia','valene','valenka','valentia','valentina','valentine','valera','valeria','valerie','valery','valerye','valida','valina','valli','vallie','vally','valma','valry','van','vanda','vanessa','vania','vanna','vanni','vannie','vanny','vanya','veda','velma','velvet','vena','venita','ventura','venus','vera','veradis','vere','verena','verene','veriee','verile','verina','verine','verla','verna','vernice','veronica','veronika','veronike','veronique','vi','vicki','vickie','vicky','victoria','vida','viki','vikki','vikkie','vikky','vilhelmina','vilma','vin','vina','vinita','vinni','vinnie','vinny','viola','violante','viole','violet','violetta','violette','virgie','virgina','virginia','virginie','vita','vitia','vitoria','vittoria','viv','viva','vivi','vivia','vivian','viviana','vivianna','vivianne','vivie','vivien','viviene','vivienne','viviyan','vivyan','vivyanne','vonni','vonnie','vonny','wallie','wallis','wally','waly','wanda','wandie','wandis','waneta','wenda','wendeline','wendi','wendie','wendy','wenona','wenonah','whitney','wileen','wilhelmina','wilhelmine','wilie','willa','willabella','willamina','willetta','willette','willi','willie','willow','willy','willyt','wilma','wilmette','wilona','wilone','wilow','windy','wini','winifred','winna','winnah','winne','winni','winnie','winnifred','winny','winona','winonah','wren','wrennie','wylma','wynn','wynne','wynnie','wynny','xaviera','xena','xenia','xylia','xylina','yalonda','yehudit','yelena','yetta','yettie','yetty','yevette','yoko','yolanda','yolande','yolane','yolanthe','yonina','yoshi','yoshiko','yovonnda','yvette','yvonne','zabrina','zahara','zandra','zaneta','zara','zarah','zaria','zarla','zea','zelda','zelma','zena','zenia','zia','zilvia','zita','zitella','zoe','zola','zonda','zondra','zonnya','zora','zorah','zorana','zorina','zorine','zsa zsa','zsazsa','zulema','zuzana','mikako','kaari','gita', 'geeta' ];

        $names = explode( ' ', $name );
        foreach ( $names as $split_name ) {
            if ( array_search( strtolower( $split_name ), $male_names ) ) {
                return 'male';
            }
            if ( array_search( strtolower( $split_name ), $female_names ) ) {
                return 'female';
            }
        }
    }
}


/**
 * Class Disciple_Tools_Data_Top_Off_Tab_Location
 */
class Disciple_Tools_Data_Top_Off_Tab_Location {
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Contact Locations from Group Assistance</th>
                </tr>
            </thead>
            <tbody>
                <br>
                <tr>
                    <td>
                        <?php self::show_location_table(); ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Information</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <b>Contact Locations from Group Assistance</b>
                    <br>
                    <br>
                    If a contact doesn't have a set location but attends a group or church that does have a set location, we can infer that that contact is also in that area.
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }

    private function get_locationless_count( $post_type ) {
        global $wpdb;
        $missing_location_count = 0;
        $result = $wpdb->get_col(
            $wpdb->prepare( "
                SELECT ID
                FROM $wpdb->posts
                WHERE post_type = %s ", $post_type )
        );
        foreach ( $result as $r ) {
            if ( ! get_post_meta( $r, 'location_grid' ) ) {
                $missing_location_count ++;
            }
        }
        return $missing_location_count;
    }

    private function get_missing_location_group_members() {
        global $wpdb;
        $result = $wpdb->get_results( "
            SELECT p2p.p2p_to as group_id, p2p.p2p_from as contact_id, pm.meta_value as group_location
            FROM wp_p2p p2p
              INNER JOIN wp_postmeta pm
                ON pm.post_id = p2p.p2p_to
            WHERE p2p.p2p_type = 'contacts_to_groups'
            AND pm.meta_key = 'location_grid'
            AND p2p.p2p_from NOT IN (
                SELECT post_id
                FROM $wpdb->postmeta
                WHERE meta_key = 'location_grid'
                )
            " );
        return $result;
    }

    private function get_locationless_group_members( $group_id ) {
        global $wpdb;
        $result = $wpdb->get_col(
            $wpdb->prepare( "
                SELECT p2p_from
                FROM $wpdb->p2p
                WHERE p2p_to = %d
                AND p2p_type = 'contacts_to_groups'
                AND p2p_from NOT IN (
                    SELECT post_id
                    FROM $wpdb->postmeta
                    WHERE meta_key = 'location_grid'
                );", $group_id )
        );
        return $result;
    }

    private function set_location_for_group_members( $group_id ) {
        $group_id = esc_sql( sanitize_key( $group_id ) );
        //Get group location
        $location = get_post_meta( $group_id, 'location_grid' )[0];

        //Get group members without location
        $locationless_members = self::get_locationless_group_members( $group_id );

        //Update location for members
        foreach ( $locationless_members as $member_id ) {
            update_post_meta( $member_id, 'location_grid', $location );
        }
    }
    private function show_location_table() {
        // Accept all location suggestions was clicked
        if ( isset( $_POST['accept_location_nonce'] ) && isset( $_POST['locations_add_all'] ) ) {
            if ( ! wp_verify_nonce( sanitize_key( $_POST['accept_location_nonce'] ), 'location_nonce' ) ) {
                return;
            }

            $result = self::get_missing_location_group_members();
            $updates_count = count( $result );
            $unique_group_ids = array_unique( array_column( $result, 'group_id' ) );
            foreach ( $unique_group_ids as $group_id ) {
                self::set_location_for_group_members( $group_id );
            }
            Disciple_Tools_Data_Top_Off_Menu::admin_notice( $updates_count . __( ' locations updated.', 'disciple_tools' ), "success" );
        }

        // Accept location suggestions for a specific group was clicked
        if ( isset( $_POST['accept_location_nonce'] ) && isset( $_POST['locations_specific_group'] ) ) {
            if ( ! wp_verify_nonce( sanitize_key( $_POST['accept_location_nonce'] ), 'location_nonce' ) ) {
                return;
            }

            $group_id = esc_sql( sanitize_key( $_POST['locations_specific_group'] ) );
            $updates_count = count( self::get_locationless_group_members( $group_id ) );
            self::set_location_for_group_members( $group_id );
            Disciple_Tools_Data_Top_Off_Menu::admin_notice( $updates_count . __( ' locations updated.', 'disciple_tools' ), "success" );
        }



        $result = self::get_missing_location_group_members();

        foreach ( $result as $r ){
            $r->contact_location = get_post_meta( $r->contact_id, 'location_grid' )[0];
        }

        $display_output = false; // Only show the output table if there are results
        ?>
        <form method="post">
            <input type="hidden" name="accept_location_nonce" id="accept_location_nonce" value="<?php echo esc_attr( wp_create_nonce( 'location_nonce' ) ) ?>" />
        <?php
        $output = "
        <div><b>" . count( self::get_missing_location_group_members() ) . "</b> contact locations can be filled automatically their attending group's location.</div>
        <div>Please note that a contact can attend more than one group. <button name=\"locations_add_all\">Accept all</button><br><br></div>
        <table class=\"widefat striped\">
            <thead>
                <tr>
                    <th colspan=\"2\">Name</th>
                    <th>Autofill to</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>";

        $unique_group_ids = array_unique( array_column( $result, 'group_id' ) );

        // Loop groups
        foreach ( $unique_group_ids as $group_id ) {
            $first_row = true;
            $count = 0;
            $display_group_output = false;
            // Loop group members
            foreach ( $result as $r ) {
                if ( $r->group_id === $group_id ) {
                    // If there's a group member without a set location
                    if ( is_null( $r->contact_location ) ) {
                        $display_output = true;
                        $display_group_output = true;
                        if ( $first_row ) {
                            $output .= "<tr><td colspan=\"3\"><a href=\"/groups/" . $group_id . "\" target=\"_blank\">" . get_the_title( $group_id ) . "</a></td>
                            <td><button name=\"locations_specific_group\" value=\"" . esc_attr( $group_id ) . "\">Accept these</button></td>
                            </tr>";
                            $first_row = false;
                        }
                        $output .=
                            "<tr id=\"contact-" . $r->contact_id . "\">
                                <td></td>
                                <td>" . esc_html( get_the_title( $r->contact_id ) ) . " (" . $r->contact_id .")</td>
                                <td>" . esc_html( Disciple_Tools_Mapping_Queries::get_by_grid_id( $r->group_location )['name'] ) . "</td>
                                <td><a href=\"#\" class=\"accept_location\" data-id=\"" . esc_attr( $r->contact_id ) . "\" data-location=\"" . esc_attr( $r->group_location ) . "\">accept</a> | <a href=\"/contacts/" . $r->contact_id ."\" target=\"_blank\">view</a></td>
                            </tr>";
                    }
                }
            }
            if ( $display_group_output ) {
                $output .= '<tr><td colspan="4"><hr></td></tr>';
            }
        }
        $output .= "</tbody></table>";
        if ( ! $display_output ) {
            $output = "<div>There are no contacts without a set location that attend a group with a set location.</div>";
        }
        echo $output;
        ?>
        </form>
        <script>
            // Assign location to a contact
            jQuery( '.accept_location' ).on( 'click', function () {
                var id = jQuery( this ).data( 'id' );
                var location = jQuery( this ).data( 'location' );
                jQuery.ajax( {
                    type: 'POST',
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'json',
                    url: window.location.origin + '/wp-json/disciple-tools-data-top-off/v1/update_location/' + id + '/' + location,
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ) ?>' );
                        },
                } );
                jQuery('#contact-' + id ).remove();
            } );
        </script>
        <?php
    }
}

