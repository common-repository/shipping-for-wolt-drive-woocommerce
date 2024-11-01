<?php
/**
 * Wolt Drive Library. Core Functions.
 *
 * Core functionality
 *
 * @package Wolt_Drive\Functions
 */

 /** Display predicted pickup and arrivals times */
function wolt_drive_get_settings() {
    $settings = get_option( 'woocommerce_wcws_settings', array() );
    $return = array();
    if ( !empty($settings)  ) {

        $return['merchant_id']      = $settings['test_mode'] == 'yes' ? $settings['test_merchant_id'] : $settings['live_merchant_id'];
        $return['venue_id']         = $settings['test_mode'] == 'yes' ? $settings['test_venue_id'] : $settings['live_venue_id'];
        $return['token']            = $settings['test_mode'] == 'yes' ? $settings['test_token'] : $settings['live_token'];

        $return['test_mode']        = $settings['test_mode'] == 'yes' ? true : false;

        $return['support_phone']    = isset($settings['support_phone'])  ? $settings['support_phone'] : '';
        $return['support_email']    = isset($settings['support_email'])  ? $settings['support_email'] : '';

        $return['mon']              = isset($settings['mon_normal'])  ? $settings['mon_normal'] : '';
        $return['tue']              = isset($settings['tue_normal'])  ? $settings['tue_normal'] : '';
        $return['wed']              = isset($settings['wed_normal'])  ? $settings['wed_normal'] : '';
        $return['thu']              = isset($settings['thu_normal'])  ? $settings['thu_normal'] : '';
        $return['fri']              = isset($settings['fri_normal'])  ? $settings['fri_normal'] : '';
        $return['sat']              = isset($settings['sat_normal'])  ? $settings['sat_normal'] : '';
        $return['sun']              = isset($settings['sun_normal'])  ? $settings['sun_normal'] : '';
        $return['special']          = isset($settings['hours_sepcial'])  ? $settings['hours_sepcial'] : '';

        $return['sms_area_code']    = isset($settings['sms_area_code'])  ? $settings['sms_area_code'] : '+45';
        $return['sms_received']     = isset($settings['sms_received'])  ? $settings['sms_received'] : '';
        $return['sms_picked_up']    = isset($settings['sms_picked_up'])  ? $settings['sms_picked_up'] : '';

        $return['settings']         = $settings;

        
    }
    return $return;    
}

function wolt_log($message, $code = 500)
{
    $log = "USER: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a") . PHP_EOL .
        "Attempt: " . ($code == 200 ? 'Success' : 'Failed: ' . $code) . PHP_EOL .
        "Message: " . $message . PHP_EOL .
        "-------------------------" . PHP_EOL;

    file_put_contents(WOLTDIR . '/log.txt', $log, FILE_APPEND);
}