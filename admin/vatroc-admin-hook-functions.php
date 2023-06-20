<?php
// https://wordpress.stackexchange.com/questions/160422/add-custom-column-to-users-admin-panel
function account_linked_user_table( $column ) {
    $column[ 'fb' ] = 'Facebook';
    $column[ 'vatsim' ] = 'VATSIM';

    return $column;
}
add_filter( 'manage_users_columns', 'account_linked_user_table' );
add_filter( 'manage_users_sortable_columns', 'account_linked_user_table' );


function account_linked_user_table_row( $val, $column_name, $user_id ) {
    // need optimize
    global $wpdb;
    $table_name = $wpdb->prefix . 'social_users';

    switch ( $column_name ) {
        case 'fb':
            $nextend_provider = new NextendSocialProviderFacebook();
            $fblink = get_user_meta( $user_id, 'fblink', true );
            return $nextend_provider->isUserConnected( $user_id ) ? $fblink == NULL ? "Linked" : sprintf( "<a href='%s' target='_blank'>Linked*</a>", $fblink ) : "";
        case 'vatsim':
            return VATROC_My::get_vatsim_uid( $user_id ) ? VATROC_My::get_vatsim_uid( $user_id ) : null;
        default:  
    }
    return $val;
}

add_filter( 'manage_users_custom_column', 'account_linked_user_table_row', 10, 3 );
function account_linked_sort_column_query( $query ){
    $orderby = $query->get( 'orderby' );
    if ( 'Facebook' == $orderby ){
        $meta_query = array(
            'relation' => 'OR',
            array(
                'key' => 'fb_user_access_token',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key' => 'fb_user_access_token',
            ),
        );
        $query->set( 'meta_query', $meta_query );
        $query->set( 'orderby', 'meta_value' );
    }
    if ( 'VATSIM' == $orderby ){
        $meta_query = array(
            'relation' => 'OR',
            array(
                'key' => 'vatroc_vatsim_uid',
                'type' => 'NUMERIC'
            ),
        );
        $query->set( 'meta_query', $meta_query );
        $query->set( 'orderby', 'meta_value' );
    }
}
add_action( 'pre_get_users', 'account_linked_sort_column_query' );
// https://wordpress.stackexchange.com/questions/293318/make-custom-column-sortable

add_action('show_user_profile', 'my_user_profile_edit_action');
add_action('edit_user_profile', 'my_user_profile_edit_action');
function my_user_profile_edit_action( $user ) {
    $meta_prefix = VATROC::$meta_prefix;
    $vatsim_rating = get_user_meta( $user->ID, "{$meta_prefix}vatsim_rating", true );
    $position = get_user_meta( $user->ID, "{$meta_prefix}position", true );
    $can_edit = current_user_can( VATROC::$admin_options );

    echo VATROC::get_template( "admin/templates/user_profile.php", [ 
        "user" => $user,
        "meta_prefix" => $meta_prefix,
        "can_edit" => $can_edit,
        "vatsim_rating" => $vatsim_rating,
        "position" => $position,
    ] );
}

function my_user_profile_maybe_can_edit( $user, $name, $type ) {

    $meta_prefix = VATROC::$meta_prefix;
    $can_edit = current_user_can( VATROC::$admin_options );

if ( $can_edit ) : ?>
    <input name="<?php echo $name; ?>" type="<?php echo $type; ?>" id="<?php echo $name; ?>" value="<?php echo get_user_meta( $user->ID, "{$meta_prefix}{$name}", true ); ?>">
<?php else: echo get_user_meta( $user->ID, "{$meta_prefix}{$name}", true ); ?>
<?php endif;

}

add_action('personal_options_update', 'my_user_profile_update_action');
add_action('edit_user_profile_update', 'my_user_profile_update_action');
function my_user_profile_update_action($user) {
    $editables = array(
        // postname     =>  metakey
        'vatsim_uid'    =>  'vatroc_vatsim_uid',
        'vatsim_rating' =>  'vatroc_vatsim_rating',
        'position'      =>  'vatroc_position',
        'staff_number'  =>  'vatroc_staff_number',
        'staff_role'    =>  'vatroc_staff_role',
        'home_division' =>  'vatroc_home_division',
        'solo_valid_until' =>  'vatroc_solo_valid_until',
        'date_application' =>  'vatroc_date_application',
        'date_exam' =>  'vatroc_date_exam',
        'date_del_sim' =>  'vatroc_date_del_sim',
        'date_del_ojt' =>  'vatroc_date_del_ojt',
        'date_del_cpt' =>  'vatroc_date_del_cpt',
        'date_gnd_sim' =>  'vatroc_date_gnd_sim',
        'date_gnd_ojt' =>  'vatroc_date_gnd_ojt',
        'date_gnd_cpt' =>  'vatroc_date_gnd_cpt',
        'date_twr_sim' =>  'vatroc_date_twr_sim',
        'date_twr_ojt' =>  'vatroc_date_twr_ojt',
        'date_twr_cpt' =>  'vatroc_date_twr_cpt',
        'date_app_sim' =>  'vatroc_date_app_sim',
        'date_app_ojt' =>  'vatroc_date_app_ojt',
        'date_app_cpt' =>  'vatroc_date_app_cpt',
        'date_ctr_sim' =>  'vatroc_date_ctr_sim',
        'date_ctr_ojt' =>  'vatroc_date_ctr_ojt',
        'date_ctr_cpt' =>  'vatroc_date_ctr_cpt',
    );

    foreach( $editables as $postname=>$metakey ) {
        if ( isset($_POST[ $postname ]) ) {
            if ( $postname == 'vatsim_uid' || current_user_can( VATROC::$admin_options ) ) {
                if ( $_POST[ $postname ] != get_user_meta( $user, $metakey, true ) )
                    VATROC::actionLog( get_current_user_id(), $metakey, $_POST[ $postname ] );
                if ( strlen( $_POST[ $postname ] ) == 0 ) delete_user_meta( $user, $metakey );
                else update_user_meta($user, $metakey, $_POST[ $postname ] );
            }
        }
    }
}

add_action( 'admin_bar_menu', 'add_vatroctool_barmenu', 100  );
function add_vatroctool_barmenu ( $admin_bar ) {
    $admin_bar->add_menu( array ( 
        'id'    => 'vatroc-tool',
        'title' =>  '<span class="ab-icon dashicons dashicons-superhero"></span>VATROC Tools',
        'href'  =>  admin_url( '?page=vatroc' ),
        'meta'  =>  array(
            'title' =>  __( 'VATROC Tools' )
        )
    ) );
}

add_filter( 'magic_login_mail_create_link_button', 'create_login_link', 10, 1);
function create_login_link( $html ) {
    if(VATROC::debug_section()){
        return $html;
    }
    return null;
}
