<?php

function create_logging_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . "vatroc_log";
    $user_table_name = $wpdb->prefix . "users";
    $vatroc_db_version = "1.0.0";
    $charset_collate = $wpdb->get_charset_collate();

    if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) != $table_name ) {
        $sql = "CREATE TABLE $table_name (
            ID        BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user`    BIGINT(20) UNSIGNED NOT NULL,
            `key`     VARCHAR(256) NOT NULL,
            `value`   VARCHAR(256),
            `fragment` BOOLEAN DEFAULT FALSE,
            `date`    DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (ID),
            FOREIGN KEY (`user`) REFERENCES wp_users (ID)
            ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    add_option('vatroc_db_version', $vatroc_db_version);
}


create_logging_table();
