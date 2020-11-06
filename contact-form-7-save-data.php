<?php
/**
 * Plugin Name: Save Data Contact Form 7
 * Description: Contact Form 7 Save Data provide to store contact form 7 data in database
 * Version: 1.0
 */

if (!defined('ABSPATH')) {
    die('-1');
}
if (!defined('CF7SD_PLUGIN_NAME')) {
    define('CF7SD_PLUGIN_NAME', 'Save Data Contact Form 7');
}
if (!defined('CF7SD_VERSION')) {
    define('CF7SD_VERSION', '1.0.0');
}
if (!defined('CF7SD_PATH')) {
    define('CF7SD_PATH', __FILE__);
}
if (!defined('CF7SD_PLUGIN_DIR')) {
    define('CF7SD_PLUGIN_DIR',plugins_url('', __FILE__));
}
if (!defined('CF7SD_DOMAIN')) {
    define('CF7SD_DOMAIN', 'CF7SD');
}
if (!defined('CF7SD_PREFIX')) {
    define('CF7SD_PREFIX', "cf7sd_");
}
if (!defined('CF7SD_PAGE_SLUG')) {
    define('CF7SD_PAGE_SLUG', "cf7sd_form_entries");
}



if (!class_exists('CF7SD')) {

    class CF7SD {

        protected static $instance;

        
        function includes() {
            include_once('admin/cf7sd-backend.php');     
            include_once('admin/cf7sd-export-csv.php'); 
            include_once('admin/cf7sd-save-data.php');
        }


        function init() {
            add_action( 'admin_enqueue_scripts', array($this, 'CF7SD_load_admin_script_style'));
            session_start();
            global $wpdb;
            $table_name = $wpdb->prefix.'cf7sd_forms';
            if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {

                $charset_collate = $wpdb->get_charset_collate();

                $sql = "CREATE TABLE $table_name (
                    form_id bigint(20) NOT NULL AUTO_INCREMENT,
                    form_post_id bigint(20) NOT NULL,
                    form_value longtext NOT NULL,
                    form_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    PRIMARY KEY  (form_id)
                ) $charset_collate;";

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $sql );
            }

            $upload_dir      = wp_upload_dir();
            $cf7sd_dirname = $upload_dir['basedir'].'/cf7sd_uploads';
            if ( ! file_exists( $cf7sd_dirname ) ) {
                wp_mkdir_p( $cf7sd_dirname );
            }
        }


        function CF7SD_load_admin_script_style() {
            wp_enqueue_style( 'CF7SD-back-style', CF7SD_PLUGIN_DIR . '/includes/css/back_style.css', false, '1.0.0' );
            wp_enqueue_script( 'CF7SD-back-script', CF7SD_PLUGIN_DIR . '/includes/js/back_script.js', false, '1.0.0' );
        }

      
        public static function instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
                self::$instance->init();
                self::$instance->includes();
            }
            return self::$instance;
        }
    }
    add_action('plugins_loaded', array('CF7SD', 'instance'));
}



