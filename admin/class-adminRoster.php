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
    private $list_type;

	public function prepare_items( $type ) {

        $this->list_type = $type;

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
        switch ( $this->list_type ){
        case VATROC::$ATC:
            $columns = array(
                'display_name'  => __( 'Name', 'vatroc' ),
                "{$this->meta_prefix}vatsim_uid"   =>  __( "VATSIM UID", "vatroc" ),
                "{$this->meta_prefix}vatsim_rating"   =>  __( "Rating", "vatroc" ),
                "{$this->meta_prefix}position"   =>  __( "Position", "vatroc" )
            ); break;
        case VATROC::$STAFF:
            $columns = array(
                'display_name'  => __( 'Name', 'vatroc' ),
                "{$this->meta_prefix}vatsim_uid"   =>  __( "VATSIM UID", "vatroc" ),
                "{$this->meta_prefix}staff_number"   =>  __( "Number", "vatroc" ),
                "{$this->meta_prefix}staff_role"   =>  __( "Role", "vatroc" )
            ); break;
        }

        return $columns;
    }

    
    public function get_hidden_columns() {
        return array();
    }


    public function get_sortable_columns() {
        switch ( $this->list_type ) {
        case VATROC::$ATC:
            return array(
                'display_name' => array( 'display_name', false ),
                "{$this->meta_prefix}position" => array( 'vatroc_position', false ),
                "{$this->meta_prefix}vatsim_uid" => array( 'vatroc_vatsim_uid', false ),
                "{$this->meta_prefix}vatsim_rating" => array( 'vatroc_vatsim_rating', false )
            );
        case VATROC::$STAFF:
            return array(
                "{$this->meta_prefix}staff_number" => array( 'Number', false ),
                'display_name' => array( 'Name', false )
            );
        }
    }


    public function column_default( $item, $column_name ) {
        switch( $column_name ) {
            case 'display_name':
            case "{$this->meta_prefix}staff_number":
            case "{$this->meta_prefix}staff_role":
            case 'vatroc_vatsim_uid':
                return isset( $item[ $column_name ] ) ? $item[ $column_name ] : NULL;
            case 'vatroc_vatsim_rating':
                return VATROC::$vatsim_rating[ $item[ $column_name ] ]; break;
            case 'vatroc_position':
                return VATROC::$atc_position[ $item[ $column_name ] ]; break;
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

            $data[ $i ][ $val[ "meta_key" ] ] = $val[ "meta_value" ];
        }

        $rosters = array();
        switch ( $this->list_type ) {
        case VATROC::$ATC:
            foreach ( $data as $idx=>$val ) {
                if ( isset( $data[ $idx ][ "{$this->meta_prefix}position" ] ) &&
                    $data[ $idx ][ "{$this->meta_prefix}position" ] > 0 ) {
                    array_push( $rosters, $data[ $idx ] );
                }
            } break;
        case VATROC::$STAFF:
            foreach ( $data as $idx=>$val ) {
                if ( isset( $data[ $idx ][ "{$this->meta_prefix}staff_role" ] ) ) {
                    array_push( $rosters, $data[ $idx ] );
                }
            } break;
        }

        return $rosters;
    }


    protected function column_display_name( $item ) {
        if ( current_user_can( 'manage_options' ) ) {
            $actions = array(
                'edit'      => sprintf('<a href="' . get_edit_user_link( $item[ "ID" ] ) . '">Edit</a>',$_REQUEST['page'],'edit',$item['ID']),
            );
        }
       return $item[ 'display_name' ] . $this->row_actions($actions);
    }


    private function sort_data( $a, $b ) {
        // Set defaults
        $orderby = 'vatroc_vatsim_rating';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }

        if ( isset( $_GET[ 'orderby' ] ) ) {
           if ( $_GET[ 'orderby' ] == "display_name" ) {
                $result = strcmp( $a[$orderby], $b[$orderby] );
            } else {
                $result = intval( $a[$orderby] ) < intval( $b[$orderby] );
            }
        }

        if($order === 'asc')
        {
            return $result;
        }
        return -$result;
    }
};

class VATROC_AdminRoster {
    public function __construct() {
    }


    public static function output( $type ) {
        self::manage_atc( $type );
    }


    public static function manage_atc( $type ) {
        $roster_list = new VATROC_RosterList();
        $roster_list->prepare_items( $type );
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Manage 
<?php
        switch ( $type ) {
        case VATROC::$ATC:
            echo "ATC";
            break;
        case VATROC::$STAFF:
            echo "Staff";
            break;
        default: break;
        }
?>
</h1>
    <hr class="wp-header-end">

<?php
    $roster_list->display( $type );
?>
    
</div>
<?php
    }
};

return new VATROC_AdminRoster();
