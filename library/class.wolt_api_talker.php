<?php
/**
 * Wolt Drive Library. Wolt Shipping method.
 *
 * @class Wolt_shipping_Method
 * @package Wolt_Drive\Library
 */

defined( 'WPINC' ) || exit;

class Wolt_Drive_Api {
    /**
     * Contructor for Wolt Drive
     * 
     * @access public
     * @return void
     */
    public function __construct() {

        $settings = wolt_drive_get_settings();

        $this->mechant      = $settings['merchant_id'];
        $this->venue        = $settings['venue_id'];
        $this->token        = $settings['token'];
        $this->test_mode    = $settings['test_mode'];

        $this->phone        = $settings['support_phone'];
        $this->email        = $settings['support_email'];
        
        $this->area_code    = $settings['sms_area_code'];
        $this->sms_received = $settings['sms_received'];
        $this->sms_picked_up= $settings['sms_picked_up'];

        if ( $settings['test_mode'] ) {
            $this->url  = 'daas-public-api.development.dev.woltapi.com';  
        } else {
            $this->url  = 'daas-public-api.wolt.com';
        }

    }
    public function shipment_promise( $package, $time ) {

        if ( !$this->mechant or !$this->venue or !$this->token ) {
            return array('error_code' => 'wolt_settings_missing');
        }

        $response = array();

        if ( !empty($package) ) {
            if ( !empty($package['destination']) ) {
                if ( !empty($package['destination']['city']) or !empty($package['destination']['postcode']) or !empty($package['destination']['address_1']) ) {
                    $post_raw = array(
                        'city'      => $package['destination']['city'],
                        'post_code' => $package['destination']['postcode'],
                        'street'    => $package['destination']['address_1'],
                        'min_preparation_time_minutes' => $time,
                    );

                    return $this->talk_with_wolt($post_raw, 'shipment-promises');
                } else {
                    return array('error_code' => 'destination_info_empty');
                }                
            }
        } else {
            return 'package empty';
        }

        return 'end true';

    }

    public function create_delivery($order_id, $order, $time) {

        if ( !is_array($order) and !empty($order) ) {
            if ( $shipment_promise = get_post_meta($order_id, 'wolt_shipment_promise', true) ) {

                $parcels = array();

                foreach ($order->get_items() as $item_id => $item) {
                    $product = $item->get_data();
                    $p = wc_get_product( $product['product_id'] );
                    $parcels[] = array(
                        'description' => $product['name'],
                        'dimensions' => array(
                            'depth_cm'      => (($value = $p->get_length()) ? explode('.', $value)[0] : 0),
                            'height_cm'     => (($value = $p->get_height()) ? explode('.', $value)[0] : 0),
                            'width_cm'      => (($value = $p->get_width()) ? explode('.', $value)[0] : 0),
                            'weight_gram'   => (($value = $p->get_weight()) ? $value * 100 : 0),
                        ),
                    );
                };
                /**
                 * TODO:
                 * Make support URL in settings
                 * Possibility to set age restrictions and verification
                 * Possibility to set age restrictions and verification
                 */

                if (strpos($order->get_billing_phone(), '+') !== false) {
                    $customer_phone = $order->get_billing_phone();
                } else {
                    $customer_phone = $this->area_code.$order->get_billing_phone();
                }

                $name = $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name();

                $payload = array(
                    'customer_support' => array(
                        'email' => $this->email,
                        'phone_number' => $this->phone,
                        'url' => get_site_url(),
                    ),
                    'dropoff' => array(
                        'comment' => '',
                        'location' => array(
                            'coordinates' => array(
                                'lat' => $shipment_promise['dropoff']['location']['coordinates']['lat'],
                                'lon' => $shipment_promise['dropoff']['location']['coordinates']['lon']
                            ),
                        ),
                    ),
                    'merchant_order_reference_id' => $order_id,
                    'parcels' => $parcels,
                    'pickup'   => array(
                        'comment' => $order->get_customer_note(),
                        'options' => array(
                            'min_preparation_time_minutes' => $time,
                        ),
                    ),
                    'price' => array(
                        'amount' => $shipment_promise['price']['amount'],
                        'currency' => $shipment_promise['price']['currency'],
                    ),
                    'recipient' => array(
                        'email' => $order->get_billing_email(),
                        'name' => $name,
                        'phone_number' => $customer_phone,
                    ),
                    'shipment_promise_id' => $shipment_promise['id'],
                );

                $sms_settings = array();

                if ( $this->sms_received != '' ) {
                    $sms_settings['picked_up'] = str_replace('CUSTOMER_NAME', $name, $this->sms_picked_up);
                }

                if ( $this->sms_received != '' ) {
                    $sms_settings['received'] = str_replace('CUSTOMER_NAME', $name, $this->sms_received);
                }

                if ( !empty($sms_settings) ) {
                    $payload['sms_notifications'] = $sms_settings;
                }

                return $this->talk_with_wolt($payload, 'deliveries');
            }            
        } else {
            return array('error_code' => 'order_is_invalid');
        }
    }

    private function talk_with_wolt($payload, $action) {

        $post = json_encode($payload);

        $authorization  = "Bearer ".$this->token;
        $url            = 'https://'.$this->url.'/v1/venues/'.$this->venue.'/'.$action;

        $args = array(
            'headers'     => array(
                'Content-Type' => 'application/json',
                'Authorization' => $authorization,
            ),
            'body'        => $post,
            'method'      => 'POST',
            'data_format' => 'body',
        );

        $response   = wp_remote_post( $url, $args );

        $retcode    = wp_remote_retrieve_response_code( $response );
        $result     = wp_remote_retrieve_body( $response );

        switch ($retcode) {
            case 502:
                return array('error_code' => 'bad_gateway');
                break;

            case 400:
                return array('error_code' => json_decode($result, true));
                break;

            case 401:
                return array('error_code' => 'wrong_token');
                break;

            case 404:
                $result = json_decode($result, true);
                if ( isset( $result['error_code'] ) ) {
                    return $result;
                } else {
                    return array('error_code' => 'wrong_venue_id');
                }
                break;

            default:
                return json_decode($result, true);
                break;
        }

    }
}
