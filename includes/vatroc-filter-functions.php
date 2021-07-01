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
	$vatsim_rating = get_user_meta( $user->ID, "vatroc_vatsim_rating", true );
	$position = get_user_meta( $user->ID, "vatroc_position", true );
?>
  <h3 id="profile-vatroc-tool">VATROC Tool</h3>
  <table class="form-table">
	<tr>
	  <th>
  	  	<label for="vatsim_uid">VATSIM UID</label>
      </th>
	  <td>
        <input name="vatsim_uid" type="text" id="vatsim_uid" value="<?php echo get_user_meta( $user->ID, "vatroc_vatsim_uid", true ); ?>">
  	  </td>
	</tr>
	<tr>
	  <th>
  	  	<label for="vatsim_rating">VATSIM Rating</label>
      </th>
	  <td>
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
	</tr>
	<tr>
	  <th>
  	  	<label for="position">Position</label>
      </th>
	  <td>
        <select name="position" id="position" value="<?php echo get_user_meta( $user->ID, "vatroc_position", true ); ?>">
            <option disabled val=""></option>
<?php
            foreach( VATROC::$atc_position as $key=>$val ) {
                $selected = $position == $key ? "selected" : "";
                echo "<option value='{$key}' {$selected} >{$val}</option>";
            }
?>
		</select>
  	  </td>
	</tr>
  </table>
<?php 
}

add_action('personal_options_update', 'my_user_profile_update_action');
add_action('edit_user_profile_update', 'my_user_profile_update_action');
function my_user_profile_update_action($user) {
    if ( isset($_POST['vatsim_uid']) ) {
        if ( strlen( $_POST[ 'vatsim_uid' ] == 0 ) ) delete_user_meta( $user, 'vatroc_vatsim_uid' );
        else update_user_meta($user, 'vatroc_vatsim_uid', $_POST[ 'vatsim_uid' ] );
    }
    if ( isset($_POST['vatsim_rating']) ) {
        if ( strlen( $_POST[ 'vatsim_rating' ] == 0 ) ) delete_user_meta( $user, 'vatroc_vatsim_rating' );
        else update_user_meta($user, 'vatroc_vatsim_rating', $_POST[ 'vatsim_rating' ] );
    }
    if ( isset($_POST['position']) ) {
        if ( strlen( $_POST[ 'position' ] == 0 ) ) delete_user_meta( $user, 'vatroc_position' );
        else update_user_meta($user, 'vatroc_position', $_POST[ 'position' ] );
    }
}
