<?php
/**
 * Plugin Name: Site Audit
 * Plugin URI: http://www.renmuell.com
 * Description:Site Audit
 * Version: 0.0.1
 * Author: Rene Müller
 * Author URI: http://www.renmuell.de
 */

if (!defined('WPINC')) {
	die;
}

define('SITE_AUDIT_VERSION', '0.0.1');
define('SITE_AUDIT_TABLE', 'site_audit');
define('SITE_AUDIT_CHECKS_TABLE', 'site_audit_checks');

include_once(__DIR__ . "/admin/admin.php");

function activate_site_audit() {
    global $wpdb;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $site_audit_db_version = '1.0';
    $charset_collate = $wpdb->get_charset_collate();

    $table_name = $wpdb->prefix . SITE_AUDIT_TABLE;
    $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            created timestamp NOT NULL default CURRENT_TIMESTAMP,
            title text NOT NULL,
            settings longtext NOt NULL,
            author bigint(20) NOT NULL,
            UNIQUE KEY id (id)
            ) $charset_collate;";
    dbDelta($sql);

    $table_name = $wpdb->prefix . SITE_AUDIT_CHECKS_TABLE;
    $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            created timestamp NOT NULL default CURRENT_TIMESTAMP,
            audit_id bigint(20) NOT NULL,
            post_id bigint(20) NOt NULL,
            settings longtext NOt NULL,
            comment text NULL,
            author bigint(20) NOT NULL,
            UNIQUE KEY id (id)
            ) $charset_collate;";
    dbDelta($sql);

    add_option('site_audit_db_version', $site_audit_db_version);
}

function deactivate_site_audit() {
}

register_activation_hook(__FILE__, 'activate_site_audit');
register_deactivation_hook(__FILE__, 'deactivate_site_audit');
