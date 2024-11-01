<?php
/**
 * Wolt Drive Library. Output Functions.
 *
 * Helpers function to output informations
 *
 * @package Wolt_Drive\Functions
 */

 /** Display predicted pickup and arrivals times */
add_action( 'woocommerce_after_shipping_rate', 'wolt_drive_shipment_promist_fields', 20, 2 );
function wolt_drive_shipment_promist_fields( $method, $index ) {
    if( ! is_checkout()) return; // Only on checkout page

    $customer_carrier_method = 'wcws';

    if( $method->id != $customer_carrier_method ) return; 

    $wolt_response = WC()->session->get('wolt_response');

    echo '<div class="wolt-info" style="font-weight: normal; font-size: .9em; color: rgba(0,0,0,0.4);">';

    echo '<img width="12px" src="'.esc_url(WOLTPURL).'images/timer-start.svg" alt="" /> <span style="color: rgba(0,0,0,0.9);">'.__('We can be there in', 'shipping-for-wolt-drive-woocommerce').' '.esc_html($wolt_response['time_estimate_minutes']).__(' min.','shipping-for-wolt-drive-woocommerce').'</span><br />';
    echo '<img width="12px" src="'.esc_url(WOLTPURL).'images/location.svg" alt="" /> '.esc_html($wolt_response['pickup']['location']['formatted_address']).'<br />';
    echo '<img width="12px" src="'.esc_url(WOLTPURL).'images/gps.svg" alt="" /> '.esc_html($wolt_response['dropoff']['location']['formatted_address']).'<br />';

    woocommerce_form_field( 'wolt_promise_id' , array(
        'type'          => 'hidden',
        'required'      => true,
        'placeholder'   => '',
    ), $wolt_response['id']);

    echo '</div>';

}

/** Display closed box if shop is closed at the moment of checkout */
add_action( 'woocommerce_review_order_before_payment', 'wolt_drive_notice_when_closed' );
function wolt_drive_notice_when_closed() {

    $Wolt_Shipping_Method = new Wolt_Shipping_Method();
    if ( !$Wolt_Shipping_Method->is_open() and $Wolt_Shipping_Method->settings['enabled'] == 'yes' ) {
        
        $next_open = $Wolt_Shipping_Method->next_open();  

        echo '<div class="wolt-drive-notice">';

        echo '<img src="'.esc_url(WOLTPURL).'images/closed.svg" alt="" />';

        echo '<span class="text">';
        echo __('Sorry but our Wolt delivery service are closed for today. We will be open again: <br />','shipping-for-wolt-drive-woocommerce');
        echo '<span class="date">';
        echo esc_html(wp_date( get_option( 'date_format' ), strtotime( $next_open['date'] ) ) );
        echo ' ';
        echo esc_html(date( get_option( 'time_format' ), strtotime( $next_open['open'] ) ) );
        echo ' - ';
        echo esc_html(date( get_option( 'time_format' ), strtotime( $next_open['close'] ) ) );
        echo '</span>';       
        echo '</span>';
        
        echo '</div>';
        
    }

}

/** Display delivery information, on the thank you page */
add_action( 'woocommerce_thankyou', 'wolt_thank_you_page' );
function wolt_thank_you_page( $order_id ) {
    $order = wc_get_order( $order_id );
    foreach ($order->get_items('shipping') as $item_id => $item) {
        if ( $item->get_method_id() == 'wcws' ) {
            $delivery_info = get_post_meta($order_id, 'wolt_delivery_info', true);

            if ( isset($delivery_info['status']) ) {

                $minutes = round( ((strtotime($delivery_info['dropoff']['eta']) - time()) / 60 ), 0);
                if ( $minutes < 0 ) {
                    $minutes = 0;
                }

                echo '<div style="text-align: center;" class="wolt-delivery-info">';

                echo '<span style="position: relative; z-index: 1;">'.__("You're delivery is coming with wolt",'shipping-for-wolt-drive-woocommerce').'</span>';
                echo '<br />';
                echo '<span style="position: relative; z-index: 1; font-weight: bold;">'.esc_html(sprintf(__("The delivery will be here in %s mins",'shipping-for-wolt-drive-woocommerce'), $minutes )).'</span>';
                echo '<br />';
                echo '<div style="';
                    echo 'background-image: url('.esc_url(WOLTPURL).'images/wolt-becycle.svg);'; 
                    echo 'background-repeat: no-repeat;'; 
                    echo 'background-position: top center;'; 
                    echo 'background-size: contain;';
                    echo 'padding: 20% 0 10% 0;"';
                echo '">';
                echo '<a target="_blank" style="position: relative; z-index: 1; padding: 15px 35px; background-color: #00C2E8; color: #FFF; border-radius: 25px; font-weight: bold; box-shadow: 0px 5px 10px 0px rgba(0,0,0,0.3);" href="'.esc_url($delivery_info['tracking']['url']).'">'.__('Track your delivery here','shipping-for-wolt-drive-woocommerce').'</a>';
                echo'</div>';
                

                echo '</div>';

            } else if ( isset($delivery_info['error']) ) {

                echo '<div style="text-align: center;" class="wolt-delivery-info">';

                
                
                echo '<span style="position: relative; z-index: 1; font-weight: bold;">'.__("Ups, something went really wrong, please contact us to get your delivery",'shipping-for-wolt-drive-woocommerce').'</span>';
                echo '<br />';
                echo '<div style="';
                    echo 'background-image: url('.esc_url(WOLTPURL).'images/wolt-becycle-error.svg);'; 
                    echo 'background-repeat: no-repeat;'; 
                    echo 'background-position: top center;'; 
                    echo 'background-size: contain;';
                    echo 'padding: 20% 0 10% 0;"';
                echo '">';
                echo'</div>';
                

                echo '</div>';

            } else {
                echo '<div style="text-align: center;" class="wolt-delivery-info">';
                echo '<span style="position: relative; z-index: 1;">'.__("You need to personally contact the us to place an order for delivery through Wolt.",'shipping-for-wolt-drive-woocommerce').'</span>';

                echo '</div>';
            }
        }
    }
}

/**
 * TODO:
 * Get delivery time into the new order mail
 */
// add_action( 'woocommerce_email_order_details', 'wolt_email_details' );
// function wolt_email_details( $order ) {

//     foreach ($order->get_items('shipping') as $item_id => $item) {
//         if ( $item->get_method_id() == 'wcws' ) {
//             $delivery_info = get_post_meta($order->id, 'wolt_delivery_info', true);

//             if ( isset($delivery_info['status']) ) {

//                 $minutes = round( ((strtotime($delivery_info['dropoff']['eta']) - time()) / 60 ), 0);
//                 if ( $minutes < 0 ) {
//                     $minutes = 0;
//                 }

//                 echo '<div style="text-align: center;" class="wolt-delivery-info">';

//                 echo '<span style="position: relative; z-index: 1;">'.__("You're delivery is coming with wolt",'shipping-for-wolt-drive-woocommerce').'</span>';
//                 echo '<br />';
//                 echo '<span style="position: relative; z-index: 1; font-weight: bold;">'.esc_html(sprintf(__("The devlivery will be here in %s mins",'shipping-for-wolt-drive-woocommerce'), $minutes )).'</span>';
//                 echo '<br />';
//                 echo '<div style="';
//                     echo 'background-image: url('.esc_url(WOLTPURL).'images/wolt-becycle.svg);'; 
//                     echo 'background-repeat: no-repeat;'; 
//                     echo 'background-position: top center;'; 
//                     echo 'background-size: contain;';
//                     echo 'padding: 20% 0 10% 0;"';
//                 echo '">';
//                 echo '<a target="_blank" style="position: relative; z-index: 1; padding: 15px 35px; background-color: #00C2E8; color: #FFF; border-radius: 25px; font-weight: bold; box-shadow: 0px 5px 10px 0px rgba(0,0,0,0.3);" href="'.esc_url($delivery_info['tracking']['url']).'">'.__('Track your delivery here','shipping-for-wolt-drive-woocommerce').'</a>';
//                 echo'</div>';
                

//                 echo '</div>';

//             }
//         }
//     }

//     echo esc_attr($order_id);
// }