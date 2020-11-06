<?php

if (!defined('ABSPATH'))
  exit;



if (!class_exists('cf7sd_savedata')) {
    class cf7sd_savedata {

        protected static $instance;        

        
        function save_application_form($wpcf7) {
            global $wpdb;
            $table_name    = $wpdb->prefix.'cf7sd_forms';
            $upload_dir    = wp_upload_dir();
            $cf7sd_uploads = $upload_dir['basedir'].'/cf7sd_uploads';
            $time_now      = time();


            $form = WPCF7_Submission::get_instance();
            if ( $form ) {

                $black_list   = array('_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag',
                '_wpcf7_is_ajax_call','cfdb7_name', '_wpcf7_container_post','_wpcf7cf_hidden_group_fields',
                '_wpcf7cf_hidden_groups', '_wpcf7cf_visible_groups', '_wpcf7cf_options','g-recaptcha-response');

                $data           = $form->get_posted_data();
                $files          = $form->uploaded_files();
                $uploaded_files = array();


                foreach ($files as $file_key => $file) {
                    array_push($uploaded_files, $file_key);
                    copy($file, $cf7sd_uploads.'/'.$time_now.'-'.basename($file));
                }

                $form_data   = array();
                $form_data['cf7sd_status'] = 'unread';
                foreach ($data as $key => $d) {
                   
                    $matches = array();

                    if ( !in_array($key, $black_list ) && !in_array($key, $uploaded_files ) && empty( $matches[0] ) ) {

                        $tmpD = $d;

                        if ( ! is_array($d) ){

                            $bl   = array('\"',"\'",'/','\\','"',"'");
                            $wl   = array('&quot;','&#039;','&#047;', '&#092;','&quot;','&#039;');

                            $tmpD = str_replace($bl, $wl, $tmpD );
                        }

                        $form_data[$key] = $tmpD;
                    }
                    if ( in_array($key, $uploaded_files ) ) {
                        $form_data[$key.'cfdb7_file'] = $time_now.'-'.$d;
                    }
                }

                

                $form_post_id = $wpcf7->id();
                $form_value   = serialize( $form_data );
                $form_date    = current_time('Y-m-d H:i:s');

                $wpdb->insert( $table_name, array(
                    'form_post_id' => $form_post_id,
                    'form_value'   => $form_value,
                    'form_date'    => $form_date
                ) );

                $insert_id = $wpdb->insert_id;
            }
        }


        function init() {   
            add_action( 'wpcf7_before_send_mail', array( $this, 'save_application_form'));
        }


        public static function instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
                self::$instance->init();
            }
            return self::$instance;
        }
    }
    cf7sd_savedata::instance();
}













