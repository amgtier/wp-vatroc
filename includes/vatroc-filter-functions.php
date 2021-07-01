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
