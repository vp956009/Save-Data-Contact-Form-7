<?php


class Expoerts_CSV{

    public function download_send_headers( $filename ) {
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");
    }


    public function array2csv(array &$array, $df){

        if (count($array) == 0) {
            return null;
        }

        $array_keys = array_keys($array);
        $heading    = array();
        $unwanted   = array('cfdb7_', 'your-');

        foreach ( $array_keys as $aKeys ) {
            $tmp       = str_replace( $unwanted, '', $aKeys );
            $heading[] = ucfirst( $tmp );
        }
        fputcsv( $df, $heading );

        foreach ( $array['form_id'] as $line => $form_id ) {
            $line_values = array();
            foreach($array_keys as $array_key ) {
                $val = isset( $array[ $array_key ][ $line ] ) ? $array[ $array_key ][ $line ] : '';
                $line_values[ $array_key ] = $val;
            }
            fputcsv($df, $line_values);
        }
    }


    public function download_csv_file(){

        global $wpdb;
        $table_name  = $wpdb->prefix.'cf7sd_forms';

        if( isset($_REQUEST['csv']) && isset( $_REQUEST['nonce'] ) ){

            $nonce =  $_REQUEST['nonce'];
            if ( ! wp_verify_nonce( $nonce, 'dnonce')) {
                wp_die( 'Not Valid.. Download nonce..!! ' );
            }
            $ocpage_id         = (int)$_REQUEST['cf7sd_formid'];
            $heading_row = $wpdb->get_results("SELECT form_id, form_value, form_date FROM $table_name
                WHERE form_post_id = '$ocpage_id' ORDER BY form_id DESC LIMIT 1",OBJECT);

            $heading_row    = reset( $heading_row );
            $heading_row    = unserialize( $heading_row->form_value );
            $heading_key    = array_keys( $heading_row );


            $total_rows  = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE form_post_id = '$ocpage_id' "); 
            $per_query    = 1000;
            $total_query  = ( $total_rows / $per_query );

            $this->download_send_headers( "cfdb7-" . date("Y-m-d") . ".csv" );
            $df = fopen("php://output", 'w');
            file_put_contents('./log_'.date("j.n.Y").'.log', $df, FILE_APPEND);
                //exit();
            ob_start();

            for( $p = 0; $p <= $total_query; $p++ ){

                $offset  = $p * $per_query;
                $results = $wpdb->get_results("SELECT form_id, form_value, form_date FROM $table_name
                WHERE form_post_id = '$ocpage_id' ORDER BY form_id DESC  LIMIT $offset, $per_query",OBJECT);
                
                $data  = array();
                $i     = 0;
                foreach ($results as $result) :
                    
                    $i++;
                    $data['form_id'][$i]    = $result->form_id;
                    $data['form_date'][$i]  = $result->form_date;
                    $resultTmp              = unserialize( $result->form_value );
                    $upload_dir             = wp_upload_dir();
                    $cfdb7_dir_url          = $upload_dir['baseurl'].'/cf7sd_uploads';

                    foreach ($resultTmp as $key => $value):
                        $matches = array();

                        if ( ! in_array( $key, $heading_key ) ) continue;
                        if( ! empty($matches[0]) ) continue;

                        if (strpos($key, 'cfdb7_file') !== false ){
                            $data[$key][$i] = $cfdb7_dir_url.'/'.$value;
                            continue;
                        }
                        if ( is_array($value) ){

                            $data[$key][$i] = implode(', ', $value);
                            continue;
                        }

                        $data[$key][$i] = str_replace( array('&quot;','&#039;','&#047;','&#092;')
                        , array('"',"'",'/','\\'), $value );

                    endforeach;

                endforeach;

                echo $this->array2csv( $data, $df );

            }
            echo ob_get_clean();
            fclose( $df );
            die();
        }
    }
}


add_action( 'init', 'cf7sd_export_csv' );
function cf7sd_export_csv() {
    $csv = new Expoerts_CSV();
    if( isset($_REQUEST['csv']) && ( $_REQUEST['csv'] == true ) && isset( $_REQUEST['nonce'] ) ) {

        $nonce  = filter_input( INPUT_GET, 'nonce', FILTER_SANITIZE_STRING );

        if ( ! wp_verify_nonce( $nonce, 'dnonce' ) ) wp_die('Invalid nonce..!!');

        $csv->download_csv_file();
    }
}
