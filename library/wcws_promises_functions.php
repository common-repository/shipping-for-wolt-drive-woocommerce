<?php
/**
 * Wolt Drive Library. Promises stages.
 *
 * Helpers function to performon that final shipment promises when checking out
 *
 * @package Wolt_Drive\Functions
 */

/** Validate Shipment promise before ordering */
function wolt_validate_order( $posted )   {

    if ( !empty($posted) and is_array($posted) ) {
        $packages = WC()->shipping->get_packages();

        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

        if( is_array( $chosen_methods ) && in_array( 'wcws', $chosen_methods ) ) {

            if ( class_exists( 'Wolt_Drive_Api' ) ) {

                if ( $time = wolt_shop_is_open() ) {
                    $destination  = array();

                    // echo '<pre>';
                    // print_r($posted);
                    // echo '</pre>';

                    if ( $posted->ship_to_different_address ) {
                        $destination['destination'] = array(
                            'city' => $posted['shipping_city'],
                            'postcode' => $posted['shipping_postcode'],
                            'address_1' => $posted['shipping_address_1'],
                        );
                    } else {
                        $destination['destination'] = array(
                            'city' => $posted['billing_city'],
                            'postcode' => $posted['billing_postcode'],
                            'address_1' => $posted['billing_address_1'],
                        );
                    }

                    $Wolt_Drive = new Wolt_Drive_Api();
                    $shipment_promise = $Wolt_Drive->shipment_promise($destination, $time);

                    if ( $shipment_promise ) {
                        if ( !$shipment_promise['error_code'] ) {
                            if ( !$shipment_promise['price']['amount'] and !$shipment_promise['is_binding'] ) {
                                if( ! wc_has_notice( $message, $messageType ) ) {
                                    wc_add_notice( __("Wolt didn't accept the shipment, try again or contact us",'shipping-for-wolt-drive-woocommerce'), 'error' );
                                }
                            }                            
                        } else {
                            if( ! wc_has_notice( $message, $messageType ) ) {
                                wc_add_notice( __('Dammit something went wrong, try again or contact us.','shipping-for-wolt-drive-woocommerce'), 'error' );
                            }
                        }
                    } else {
                        if( ! wc_has_notice( $message, $messageType ) ) {
                            wc_add_notice( __('Shippping was not approved, try again or contact us.','shipping-for-wolt-drive-woocommerce'), 'error' );
                        }
                    }
                }

            } else {
                if( ! wc_has_notice( $message, $messageType ) ) {
                    wc_add_notice( __('The wolt shiping api seems to be missing','shipping-for-wolt-drive-woocommerce'), 'error' );
                }
            }
            
        }
    }

}
add_action( 'woocommerce_review_order_before_cart_contents', 'wolt_validate_order' , 10 );
add_action( 'woocommerce_after_checkout_validation', 'wolt_validate_order' , 10 );

/** Gets final shipment promise and saves it. */
function wolt_final_shipment_promise( $order_id ) {

    $order = wc_get_order( $order_id );

    foreach ($order->get_items('shipping') as $item_id => $item) {
        if ( $item->get_method_id() == 'wcws' ) {

            if ( class_exists( 'Wolt_Drive_Api' ) ) {

                if ( $time = wolt_shop_is_open() ) {

                    $destination  = array();
                    $destination['destination'] = array(
                        'city' => $order->get_shipping_city(),
                        'postcode' => $order->get_shipping_postcode(),
                        'address_1' => $order->get_shipping_address_1(),
                    );

                    $Wolt_Drive = new Wolt_Drive_Api();
                    $shipment_promise = $Wolt_Drive->shipment_promise($destination, $time);

                    if ( $shipment_promise ) {
                        if ( !$shipment_promise['error_code'] ) {
                            if ( $shipment_promise['price']['amount'] and $shipment_promise['is_binding'] ) {
                                update_post_meta( $order_id, 'wolt_shipment_promise', $shipment_promise );
                            }                            
                        } else {
                            if( ! wc_has_notice( $message, $messageType ) ) {
                                wc_add_notice( __('Dammit something went wrong, try again or contact us.','shipping-for-wolt-drive-woocommerce'), 'error' );
                            }
                        }
                    } else {
                        if( ! wc_has_notice( $message, $messageType ) ) {
                            wc_add_notice( __('Shippping was not approved, try again or contact us.','shipping-for-wolt-drive-woocommerce'), 'error' );
                        }
                    }
                }

            } else {
                if( ! wc_has_notice( $message, $messageType ) ) {
                    wc_add_notice( __('The wolt shiping api seems to be missing','shipping-for-wolt-drive-woocommerce'), 'error' );
                }
            }

        }
    }

}
add_action( 'woocommerce_checkout_order_processed', 'wolt_final_shipment_promise' );

/** Orders the pickup at order confirmation */
function wolt_order_pickup( $order_id ){
    $order = wc_get_order( $order_id );

    if ( $order->get_status() == 'processing' ) {
        foreach ($order->get_items('shipping') as $item_id => $item) {
            if ( $item->get_method_id() == 'wcws' ) {
                if ( class_exists( 'Wolt_Drive_Api' ) ) {
                    $shipment_promise = get_post_meta($order_id, 'wolt_shipment_promise', true);

                    if ( strtotime($shipment_promise['valid_until']) > time() ) {
                        wolt_final_shipment_promise( $order_id );
                    }

                    $time = wolt_shop_is_open();

                    $Wolt_Drive = new Wolt_Drive_Api();
                    $delivery_info = $Wolt_Drive->create_delivery($order_id, $order, $time);

                    if ( isset($delivery_info['error_code']) ) {
                        if( ! wc_has_notice( $message, $messageType ) ) {
                            wc_add_notice( __('Sorry something went wrong please contact us','shipping-for-wolt-drive-woocommerce'), 'error' );
                        }
                    } else {
                        if ( $delivery_info['status'] == 'INFO_RECEIVED' ) {
                            update_post_meta( $order_id, 'wolt_delivery_info', $delivery_info );
                        } else {
                            if( ! wc_has_notice( $message, $messageType ) ) {
                                wc_add_notice( __('Sorry something went wrong please contact us','shipping-for-wolt-drive-woocommerce'), 'error' );
                            }
                        }
                        
                    }

                } else {
                    if( ! wc_has_notice( $message, $messageType ) ) {
                        wc_add_notice( __('The wolt shiping api seems to be missing','shipping-for-wolt-drive-woocommerce'), 'error' );
                    }
                }                
            }
        }
    }

}
add_action( 'woocommerce_payment_complete', 'wolt_order_pickup' );