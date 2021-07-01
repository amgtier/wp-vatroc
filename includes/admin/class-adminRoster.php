<?php
/**
 * VATROC Admin Roster
 *
 * @class VATROC_AdminRoster
 * @author tzchao
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}



class VATROC_RosterList extends WP_List_Table {
    public $meta_prefix = "vatroc_";
    public $perPage = 1000;

	public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $this->perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$this->perPage),$this->perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }


    public function get_columns() {
        $columns = array(
            'display_name'  => __( 'Name', 'vatroc' ),
            "{$this->meta_prefix}vatsim_uid"   =>  __( "VATSIM UID", "vatroc" ),
            "{$this->meta_prefix}vatsim_rating"   =>  __( "Rating", "vatroc" ),
            "{$this->meta_prefix}position"   =>  __( "Position", "vatroc" )
        );

        return $columns;
    }

    
    public function get_hidden_columns() {
        return array();
    }


    public function get_sortable_columns() {
        return array(
            'display_name' => array( 'Name', false )
        );
    }


    public function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'display_name':
            case 'vatroc_vatsim_uid':
            case 'vatroc_vatsim_rating':
            case 'vatroc_position':
                return isset( $item[ $column_name ] ) ? $item[ $column_name ] : NULL;
            default:
                return print_r( $item, true );
        }
    }


    public function table_data() {
        global $wpdb;
        $sql = "SELECT ID,display_name FROM {$wpdb->prefix}users";
        $sql .= " LIMIT $this->perPage";
        // $sql .= ' OFFSET ' . ( $page_number - 1 ) * $this->per_page;

        $sql_usermeta = "SELECT user_id,meta_key,meta_value FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE '{$this->meta_prefix}%'";

        $data = $wpdb->get_results( $sql, 'ARRAY_A' );
        $usermeta = $wpdb->get_results( $sql_usermeta, 'ARRAY_A' );

        for( $i = 0; $i < count( $data ); $i += 1 ) {
            $data[ $i ][ "display_name" ] = "<a href='" . get_edit_user_link( $data[ $i ][ "ID" ] ) . "#profile-vatroc-tool' target='_balnk'>{$data[ $i ][ "display_name" ]}</a>";
        }

        foreach( $usermeta as $idx=>$val ) {
            $entry = null;
            $i = 0;
            for ( ; $i < count( $data ); $i += 1 ){
                if ( $data[ $i ][ "ID" ] == $val[ "user_id" ] ) {
                    break;
                }
            }
            if ( $i == count( $data ) ) continue;

            switch( $val[ "meta_key" ] ){
                case "{$this->meta_prefix}vatsim_rating":
                    $data[ $i ][ $val[ "meta_key" ] ] = VATROC::$vatsim_rating[ $val[ "meta_value" ] ]; break;
                case "{$this->meta_prefix}position":
                    $data[ $i ][ $val[ "meta_key" ] ] = VATROC::$atc_position[ $val[ "meta_value" ] ]; break;
                default:
                    $data[ $i ][ $val[ "meta_key" ] ] = $val[ "meta_value" ];
            }
        }

        return $data;
    }


    protected function column_display_name( $item ) {
       $actions = array(
           'edit'      => sprintf('<a href="' . get_edit_user_link( $item[ "ID" ] ) . '">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
       );

       return $item[ 'display_name' ] . $this->row_actions($actions);
    }
};

class VATROC_AdminRoster {
    public function __construct() {
    }


    public static function output() {
        self::manage_atc();
    }


    public static function manage_atc() {
        $roster_list = new VATROC_RosterList();
        $roster_list->prepare_items();
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Manage ATC</h1>
    <a href="#" class="page-title-action"  >Add New</a>
    <hr class="wp-header-end">

<?php
    $roster_list->display();
?>
    
</div>
<?php
    }
};

return new VATROC_AdminRoster();
