<?php
// https://wordpress.stackexchange.com/questions/160422/add-custom-column-to-users-admin-panel
function account_linked_user_table( $column ) {
    $column[ 'fb' ] = 'Facebook';
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
}
add_action( 'pre_get_users', 'account_linked_sort_column_query' );
// https://wordpress.stackexchange.com/questions/293318/make-custom-column-sortable

add_action('show_user_profile', 'my_user_profile_edit_action');
add_action('edit_user_profile', 'my_user_profile_edit_action');
function my_user_profile_edit_action($user) {
    $meta_prefix = VATROC::$meta_prefix;
    $vatsim_rating = get_user_meta( $user->ID, "{$meta_prefix}vatsim_rating", true );
    $position = get_user_meta( $user->ID, "{$meta_prefix}position", true );
    $can_edit = current_user_can( VATROC::$admin_options );
?>
  <h3 id="profile-vatroc-tool">VATROC Tool</h3>
  <table class="form-table">
    <tr>
      <th>
            <label for="vatsim_uid">VATSIM UID</label>
      </th>
      <td>
        <input name="vatsim_uid" type="text" id="vatsim_uid" value="<?php echo get_user_meta( $user->ID, "{$meta_prefix}vatsim_uid", true ); ?>">
        </td>
    </tr>
    <tr>
      <th>
            <label for="vatsim_rating">VATSIM Rating</label>
      </th>
      <td>
<?php if ( $can_edit ) : ?>
        <select name="vatsim_rating" id="vatsim_rating" >
            <option disabled val=""></option>
<?php
            foreach( VATROC::$vatsim_rating as $key=>$val ) {
                $selected = $vatsim_rating == $key ? "selected" : "";
                echo "<option value='{$key}' {$selected} >{$val}</option>";
            }
?>
        </select>
        </td>
<?php else: echo VATROC::$vatsim_rating[ $vatsim_rating ]; ?>
<?php endif; ?>
    </tr>
    <tr>
      <th>
            <label for="position">Position</label>
      </th>
      <td>
<?php if ( $can_edit ) : ?>
        <select name="position" id="position" value="<?php echo get_user_meta( $user->ID, "vatroc_position", true ); ?>" <?php if ( !$can_edit ) echo "disabled"; ?>>
            <option disabled val=""></option>
<?php
            foreach( VATROC::$atc_position as $key=>$val ) {
                $selected = $position == $key ? "selected" : "";
                echo "<option value='{$key}' {$selected} >{$val}</option>";
            }
?>
        </select>
<?php else: echo VATROC::$atc_position[ $position ]; ?>
<?php endif; ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="staff_number">VATROC STAFF NUMBER</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "staff_number", "number" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="staff_role">VATROC STAFF ROLE</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "staff_role", "text" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="solo_valid_until">Solo Valid Until</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "solo_valid_until", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="home_division">Visiting Home Division</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "home_division", "text" ); ?>
        </td>
    </tr>
  </table>
  <h3 id="profile-vatroc-progress-dates">VATROC Progress Dates</h3>
  <table class="form-table">
    <tr>
      <th>
            <label for="date_application">Application Date</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_application", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_exam">Exam Date</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_exam", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_del_sim">Date DEL SIM</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_del_sim", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_del_ojt">Date DEL OJT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_del_ojt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_del_cpt">Date DEL CPT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit($user,  "date_del_cpt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_gnd_sim">Date GND SIM</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_gnd_sim", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_gnd_ojt">Date GND OJT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_gnd_ojt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_gnd_cpt">Date GND CPT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_gnd_cpt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_twr_sim">Date TWR SIM</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_twr_sim", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_twr_ojt">Date TWR OJT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_twr_ojt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_twr_cpt">Date TWR CPT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_twr_cpt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_app_sim">Date APP SIM</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_app_sim", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_app_ojt">Date APP OJT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_app_ojt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_app_cpt">Date APP CPT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_app_cpt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_ctr_sim">Date CTR SIM</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_ctr_sim", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_ctr_ojt">Date CTR OJT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_ctr_ojt", "date" ); ?>
        </td>
    </tr>
    <tr>
      <th>
            <label for="date_ctr_cpt">Date CTR CPT</label>
      </th>
      <td>
<?php my_user_profile_maybe_can_edit( $user, "date_ctr_cpt", "date" ); ?>
        </td>
    </tr>
<?php 
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
