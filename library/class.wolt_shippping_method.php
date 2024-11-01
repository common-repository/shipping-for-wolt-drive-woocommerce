<?php
/**
 * Wolt Drive Library. Wolt Shipping method.
 *
 * @class Wolt_shipping_Method
 * @package Wolt_Drive\Classes
 */

defined( 'WPINC' ) || exit;

function wolt_shipping_method() {
    if ( ! class_exists( 'Wolt_Shipping_Method' ) ) {
        class Wolt_Shipping_Method extends WC_Shipping_Method {
            /**
              * Constructor for your shipping class
              *
              * @access public
              * @return void
              */
            public function __construct() {
                $this->id                 = 'wcws';
                $this->method_title       = __( 'Wolt Drive','shipping-for-wolt-drive-woocommerce');
                $this->method_description = __( 'Wolt Drive shipping method','shipping-for-wolt-drive-woocommerce');

                $this->init();

                $this->enabled      = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : '';
                $this->title        = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Wolt Drive shipping', 'shipping-for-wolt-drive-woocommerce' );
                
                $this->fees         = isset( $this->settings['fees'] ) ? $this->settings['fees'] : '';
                $this->price        = isset( $this->settings['override_price'] ) ? $this->settings['override_price'] : '';
                
                $this->support_phone= isset( $this->settings['support_phone'] ) ? $this->settings['support_phone'] : '';
                $this->support_email= isset( $this->settings['support_email'] ) ? $this->settings['support_email'] : '';
                
                $this->sms_area_code= isset( $this->settings['sms_area_code'] ) ? $this->settings['sms_area_code'] : '+45';
                $this->sms_received = isset( $this->settings['sms_received'] ) ? $this->settings['sms_received'] : '';
                $this->sms_picked_up= isset( $this->settings['sms_picked_up'] ) ? $this->settings['sms_picked_up'] : '';
                
                $this->mon          = isset( $this->settings['mon_normal'] ) ? $this->settings['mon_normal'] : '3';
                $this->tue          = isset( $this->settings['tue_normal'] ) ? $this->settings['tue_normal'] : '';
                $this->wed          = isset( $this->settings['wed_normal'] ) ? $this->settings['wed_normal'] : '';
                $this->thu          = isset( $this->settings['thu_normal'] ) ? $this->settings['thu_normal'] : '';
                $this->fri          = isset( $this->settings['fri_normal'] ) ? $this->settings['fri_normal'] : '';
                $this->sat          = isset( $this->settings['sat_normal'] ) ? $this->settings['sat_normal'] : '';
                $this->sun          = isset( $this->settings['sun_normal'] ) ? $this->settings['sun_normal'] : '';
                
                $this->special      = isset( $this->settings['hours_sepcial'] ) ? $this->settings['hours_sepcial'] : '';
                
                $this->test_mode    = (isset( $this->settings['test_mode'] ) and $this->settings['test_mode'] == 'yes' ) ? true : false;
                
                $this->merchant_id  = (isset( $this->settings['test_mode'] ) and $this->settings['test_mode'] == 'yes' ) ? $this->settings['test_merchant_id'] : $this->settings['live_merchant_id'];
                $this->venue_id     = (isset( $this->settings['test_mode'] ) and $this->settings['test_mode'] == 'yes' ) ? $this->settings['test_venue_id'] : $this->settings['live_venue_id'];
                $this->token        = (isset( $this->settings['test_mode'] ) and $this->settings['test_mode'] == 'yes' ) ? $this->settings['test_token'] : $this->settings['live_token'];
                

            }

            /**
              * Init your settings
              *
              * @access public
              * @return void
              */
            function init() {
                 // Load the settings API
                $this->init_form_fields();
                $this->init_settings();

                 // Save settings in admin if you have any defined
                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }

            /**
              * Define settings field for this shipping
              * @return void
              */
            function init_form_fields() {

                $this->form_fields = array(

                    'enabled' => array(
                        'title'        => __( 'Enable method', 'shipping-for-wolt-drive-woocommerce' ),
                        'type'         => 'checkbox',
                        'description'  => __( 'Enable this shipping method.', 'shipping-for-wolt-drive-woocommerce' ),
                        'desc_tip'     => true,
                    ),

                    'title' => array(
                        'title'        => __( 'Title', 'shipping-for-wolt-drive-woocommercee' ),
                        'type'         => 'text',
                        'description'  => __( 'Title to be displayed on site', 'shipping-for-wolt-drive-woocommerce' ),
                        'default'      => __( 'Wolt drive shipping', 'shipping-for-wolt-drive-woocommerce' ),
                        'desc_tip'     => true,
                    ),

                    'fees' => array(
                        'title'        => __( 'Fee', 'shipping-for-wolt-drive-woocommerce' ),
                        'type'         => 'text',
                        'required'     => true,
                        'description'  => __( 'Fees to be added on top of the ones recived from wolt', 'shipping-for-wolt-drive-woocommerce' ),
                        'desc_tip'     => true,
                    ),

                    'override_price' => array(
                        'title'        => __( 'Override price', 'shipping-for-wolt-drive-woocommerce' ),
                        'type'         => 'text',
                        'description'  => __( 'Override the price for shipping', 'shipping-for-wolt-drive-woocommerce' ),
                        'desc_tip'     => true,
                    ),

                    'support_phone' => array(
                        'title'        => __( 'Support Phone', 'shipping-for-wolt-drive-woocommerce' ),
                        'type'         => 'text',
                        'description'  => __( 'A number customers can contact in case of problem with there deliveries.', 'shipping-for-wolt-drive-woocommerce' ),
                        'default'      => __( '', 'shipping-for-wolt-drive-woocommerce' ),
                        'desc_tip'     => true,
                    ),

                    'support_email' => array(
                        'title'        => __( 'Support Email', 'shipping-for-wolt-drive-woocommerce' ),
                        'type'         => 'text',
                        'description'  => __( 'A Email customers can contact in case of problem with there deliveries.', 'shipping-for-wolt-drive-woocommerce' ),
                        'default'      => __( '', 'shipping-for-wolt-drive-woocommerce' ),
                        'desc_tip'     => true,
                    ),

                    'sms_notifications_title' => array(
                        'title'        => __( 'SMS Notifications', 'shipping-for-wolt-drive-woocommerce' ),
                        'type'         => 'title',
                        'description'  => '<hr />'.__('Text for the SMS notifications that customers gets when wolt receives the order, and have picked up the package.<br />Use CUSTOMER_NAME to input customer name, and use TRACKING_LINK to get the tracking link.<br />Leave blank for no SMS notifications.','shipping-for-wolt-drive-woocommerce'),
                        'default'      => 100
                    ),

                    'sms_area_code' => array(
                        'title'        => __( 'Phone area code', 'shipping-for-wolt-drive-woocommerce' ),
                        'type'         => 'text',
                        'description'  => __( 'Area code of deliver, eks. +45', 'shipping-for-wolt-drive-woocommerce' ),
                        'default'      => __( '+45', 'shipping-for-wolt-drive-woocommerce' ),
                        'desc_tip'     => true,
                    ),

                    'sms_received' => array(
                        'title'        => __( 'SMS received', 'shipping-for-wolt-drive-woocommerce' ),
                        'type'         => 'textarea',
                        'description'  => __( 'Content to be sent as sms when order is received..', 'shipping-for-wolt-drive-woocommerce' ),
                        'default'      => __( 'Hello CUSTOMER_NAME! Your order from Amazing Store will be delivered soon. You can follow it here: TRACKING_LINK', 'shipping-for-wolt-drive-woocommerce' ),
                        'desc_tip'     => true,
                        'css'          => 'width: 400px; height: 125px;',
                    ),

                    'sms_picked_up' => array(
                        'title'        => __( 'SMS pickup', 'shipping-for-wolt-drive-woocommerce' ),
                        'type'         => 'textarea',
                        'description'  => __( 'Content to be sent as sms when order is picked up.', 'shipping-for-wolt-drive-woocommerce' ),
                        'default'      => __( 'Hello CUSTOMER_NAME! Your order from Amazing Store has been picked up and will be delivered soon. You can follow it here: TRACKING_LINK', 'shipping-for-wolt-drive-woocommerce' ),
                        'desc_tip'     => true,
                        'css'          => 'width: 400px; height: 125px;',
                    ),

                    'openening_hours_title' => array(
                        'title'        => __( 'Opening hours settings', 'shipping-for-wolt-drive-woocommerce' ),
                        'type'         => 'title',
                        'description'  => '<hr />'.__('Set normal weekly opening hours through the fields, leave blank if closed.<br/>Separate the time with ";" - last option is minium preparation time in minutes which is optional, default is 30 min ','shipping-for-wolt-drive-woocommerce').'<br />'.__('[opening];[closing];[minimum preparation]','shipping-for-wolt-drive-woocommerce'),
                        'default'      => 100
                    ),

                    'mon_normal' => array(
                        'title'        => __( 'Monday', 'shipping-for-wolt-drive-woocommerce' ),
                        'type'         => 'text',
                        'default'      => __( '', 'shipping-for-wolt-drive-woocommerce' ),
                        'desc_tip'     => true,
                    ),

                    'tue_normal' => array(
                        'title'        => __( 'Tuesday','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'text',
                        'default'      => __( '','shipping-for-wolt-drive-woocommerce'),
                    ),

                    'wed_normal' => array(
                        'title'        => __( 'Wednesday','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'text',
                        'default'      => __( '','shipping-for-wolt-drive-woocommerce')
                    ),

                    'thu_normal' => array(
                        'title'        => __( 'Thursday','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'text',
                        'description'  => __( '','shipping-for-wolt-drive-woocommerce'),
                        'default'      => __( '','shipping-for-wolt-drive-woocommerce')
                    ),

                    'fri_normal' => array(
                        'title'        => __( 'Friday','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'text',
                        'description'  => __( '','shipping-for-wolt-drive-woocommerce'),
                        'default'      => __( '','shipping-for-wolt-drive-woocommerce')
                    ),

                    'sat_normal' => array(
                        'title'        => __( 'Saturday','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'text',
                        'description'  => __( '','shipping-for-wolt-drive-woocommerce'),
                        'default'      => __( '','shipping-for-wolt-drive-woocommerce')
                    ),

                    'sun_normal' => array(
                        'title'        => __( 'Sunday','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'text',
                        'description'  => __( '','shipping-for-wolt-drive-woocommerce'),
                        'default'      => __( '','shipping-for-wolt-drive-woocommerce')
                    ),

                    'hours_sepcial' => array(
                        'title'        => __( 'Special opening hours','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'textarea',
                        'description'  => __( 'Special opening hours one date per line, leave only date if closed.<br />Use ";" to separate opening and closing, and last can be minium delivery preparation time e.g.:<br /><i>[date MM/DD/YYYY];[opening];[closing];[preparation time]</i>','shipping-for-wolt-drive-woocommerce'),
                        'default'      => __( '9:00;16:00;30','shipping-for-wolt-drive-woocommerce'),
                        'css'          => 'width: 400px; height: 125px;',
                    ),

                    'live_title' => array(
                        'title'        => __( 'Production settings','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'title',
                        'description'  => '<hr />',
                        'default'      => 100
                    ),

                    'live_merchant_id' => array(
                        'title'        => __( 'Merchant ID','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'text',
                        'description'  => __( 'Your production merchant id','shipping-for-wolt-drive-woocommerce'),
                    ),

                    'live_venue_id' => array(
                        'title'        => __( 'Venue ID','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'text',
                        'description'  => __( 'Your production venue id','shipping-for-wolt-drive-woocommerce'),
                    ),

                    'live_token' => array(
                        'title'        => __( 'Token','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'text',
                        'description'  => __( 'Your production token','shipping-for-wolt-drive-woocommerce'),
                    ),

                    'test_title' => array(
                        'title'        => __( 'Test settings','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'title',
                        'description'  => '<hr />',
                        'default'      => 100
                    ),

                    'test_mode' => array(
                        'title'        => __( 'Test mode','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'checkbox',
                        'description'  => __( 'Enable test mode.','shipping-for-wolt-drive-woocommerce'),
                        'default'      => 'yes'
                    ),

                    'test_merchant_id' => array(
                        'title'        => __( 'Merchant ID','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'text',
                        'description'  => __( 'Your test merchant id','shipping-for-wolt-drive-woocommerce'),
                    ),

                    'test_venue_id' => array(
                        'title'        => __( 'Venue ID','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'text',
                        'description'  => __( 'Your test venue id','shipping-for-wolt-drive-woocommerce'),
                    ),

                    'test_token' => array(
                        'title'        => __( 'Token','shipping-for-wolt-drive-woocommerce'),
                        'type'         => 'text',
                        'description'  => __( 'Your test token','shipping-for-wolt-drive-woocommerce'),
                    ),

                );

            }

            /**
              * Calcualte Wolt shipping cost
              *
              * @access public
              * @param mixed $package
              * @return void
              */
            public function calculate_shipping( $package = array() ) {
                
                if ( $time = $this->is_open() ) {
                    if ( class_exists( 'Wolt_Drive_Api' ) ) {


                        $Wolt_Drive = new Wolt_Drive_Api($this->merchant_id, $this->venue_id, $this->token, $this->test_mode);
                        $shipment_promise = $Wolt_Drive->shipment_promise($package, $time);                        

                        if ( $shipment_promise ) {
                            if ( !$shipment_promise['error_code'] ) {
                                if ( $shipment_promise['price']['amount'] ) {

                                    $shipment_promise['package'] = $package;

                                    WC()->session->set('wolt_response',  $shipment_promise);

                                    if ( !empty($this->price) ) {
                                        $amount = $this->price;
                                    } else {
                                        $fees   = !empty($this->fees) ? $this->fees : 0;
                                        $amount = ($shipment_promise['price']['amount'] / 100 ) + $fees ;
                                    }

                                    $rate = array(
                                        'id' => $this->id,
                                        'label' => $this->title,
                                        'cost' => $amount
                                    );

                                    $this->add_rate( $rate );

                                }                            
                            }
                        }

                    } else {

                        WC()->session->set('wolt_response',  array('error_code' => 'no_wolt_class'));

                    }
                }

            }

            public function is_open($debug = false) {

                return wolt_shop_is_open();

            }

            public function next_open() {

                return wolt_shop_next_open();

            }
            
        }
    }
}
add_action( 'woocommerce_shipping_init', 'wolt_shipping_method' );

function add_wolt_shipping_method( $methods ) {
    $methods[] = 'Wolt_Shipping_Method';
    return $methods;
}

add_filter( 'woocommerce_shipping_methods', 'add_wolt_shipping_method' );


function wolt_drive_checkout_css_file() {
    if ( wolt_drive_is_woocommerce_active() &&  is_checkout() ) {
        wp_enqueue_style( 'wolt-drive-css', WOLTPURL . 'css/wolt-drive.css' );
    }
}
add_action('wp_head', 'wolt_drive_checkout_css_file');

