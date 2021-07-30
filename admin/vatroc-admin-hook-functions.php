<?php
// https://wordpress.stackexchange.com/questions/160422/add-custom-column-to-users-admin-panel
function account_linked_user_table( $column ) {
    $column[ 'fb' ] = 'Facebook';
    return $column;
}
add_filter( 'manage_users_columns', 'account_linked_user_table' );


function account_linked_user_table_row( $val, $column_name, $user_id ) {
    // need optimize
    global $wpdb;
    $table_name = $wpdb->prefix . 'social_users';

    switch ( $column_name ) {
        case 'fb':
            $fb_id = $wpdb->get_var( sprintf( 'SELECT identifier FROM `%s` WHERE ID="%s";', $table_name, $user_id ) );
            return $fb_id != NULL ? sprintf( "<a href='%s' target='_blank'>Linked</a>", get_user_meta( $user_id, 'fblink', true ) ) : "";
        default:  
    }
    return $val;
}
add_filter( 'manage_users_custom_column', 'account_linked_user_table_row', 10, 3 );


add_action('show_user_profile', 'my_user_profile_edit_action');
add_action('edit_user_profile', 'my_user_profile_edit_action');
function my_user_profile_edit_action($user) {
    $meta_prefix = VATROC::$meta_prefix;
	$vatsim_rating = get_user_meta( $user->ID, "{$meta_prefix}vatsim_rating", true );
	$position = get_user_meta( $user->ID, "{$meta_prefix}position", true );
    $can_edit = current_user_can( 'manage_options' );
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
<?php if ( $can_edit ) : ?>
        <input name="staff_number" type="number" id="staff_number" value="<?php echo get_user_meta( $user->ID, "{$meta_prefix}staff_number", true ); ?>">
<?php else: echo get_user_meta( $user->ID, "{$meta_prefix}staff_number", true ); ?>
<?php endif; ?>
  	  </td>
	</tr>
	<tr>
	  <th>
  	  	<label for="staff_role">VATROC STAFF ROLE</label>
      </th>
	  <td>
<?php if ( $can_edit ) : ?>
        <input name="staff_role" type="text" id="staff_role" value="<?php echo get_user_meta( $user->ID, "{$meta_prefix}staff_role", true ); ?>">
<?php else: echo get_user_meta( $user->ID, "{$meta_prefix}staff_role", true ); ?>
<?php endif; ?>
  	  </td>
	</tr>
	<tr>
	  <th>
  	  	<label for="solo_valid_until">Solo Valid Until</label>
      </th>
	  <td>
<?php if ( $can_edit ) : ?>
        <input name="solo_valid_until" type="date" id="solo_valid_until" value="<?php echo get_user_meta( $user->ID, "{$meta_prefix}solo_valid_until", true ); ?>">
<?php else: echo get_user_meta( $user->ID, "{$meta_prefix}solo_valid_until", true ); ?>
<?php endif; ?>
  	  </td>
	</tr>
	<tr>
	  <th>
  	  	<label for="home_division">Visiting Home Division</label>
      </th>
	  <td>
<?php if ( $can_edit ) : ?>
        <input name="home_division" type="home_division" id="home_division" value="<?php echo get_user_meta( $user->ID, "{$meta_prefix}home_division", true ); ?>">
<?php else: echo get_user_meta( $user->ID, "{$meta_prefix}home_division", true ); ?>
<?php endif; ?>
  	  </td>
	</tr>
  </table>
<?php 
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
        'solo_valid_until' =>  'vatroc_solo_valid_until'
    );

    foreach( $editables as $postname=>$metakey ) {
        if ( isset($_POST[ $postname ]) ) {
            if ( $postname == 'vatsim_uid' || current_user_can( 'manage_options' ) ) {
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
