<?php
/**
 * Wolt Drive Library. Time Functions.
 *
 * Helpers function checking openings times
 *
 * @package Wolt_Drive\Functions
 */

 /** Test if the shop is open */
function wolt_shop_is_open($debug = false) {

    $settings = wolt_drive_get_settings();
    date_default_timezone_set(wp_timezone_string());

    if ( !empty($settings) ) {
        if ( $debug ) {
            echo 'Current time: '.esc_html(date('H:i', time()));
        }

        if ( $settings['special'] != '' ) {
            $special_hours = explode(PHP_EOL, $settings['special']);
            if ( is_array($special_hours) ) {
                foreach ($special_hours as $date_times) {
                    $date = explode(';', $date_times);
                    if ( is_array($date) ) {
                        if ( strtotime($date[0]) == strtotime(date('m/d/Y', time())) ) {
                            $special_date = true;
                            if ( isset($date[1]) and isset($date[2]) and !empty($date[1] and !empty($date[2])) ) {
                                if ( strtotime(date('G:i')) > strtotime($date[1]) or strtotime(date('G:i')) == strtotime($date[1]) ) {
                                    if ( strtotime(date('G:i')) < strtotime($date[2]) or strtotime(date('G:i')) == strtotime($date[2]) ) {
                                        $is_open = true;                                                
                                    } else {
                                        $is_open = false;
                                    }
                                } else {
                                    $is_open = false;
                                }
                            } else {
                                $is_open = false;
                            }
                        } 
                    }
                }
            }
        }

        if ( $special_date ) {
            if ( $is_open ) {
                $date['open'] = true;
                return ( $debug ? $date : ((isset($date[3]) and !empty($date[3])) ? $date[3] : 30) );
            } else {
                $date['open'] = false; 
                return ( $debug ? $date : false );
            }
        }

        if ( $debug ) {
            echo '<br />No special date';
        }

        switch (date('D')) {
            case 'Mon':
                $time = explode(';', $settings['mon']);
                break;
            case 'Tue':
                $time = explode(';', $settings['tue']);
                break;
            case 'Wed':
                $time = explode(';', $settings['wed']);
                break;
            case 'Thu':
                $time = explode(';', $settings['thu']);
                break;
            case 'Fri':
                $time = explode(';', $settings['fri']);
                break;
            case 'Sat':
                $time = explode(';', $settings['sat']);
                break;
            case 'Sun':
                $time = explode(';', $settings['sun']);
                break;
        }

        if ( !empty($time[0]) and !empty($time[1]) ) {
            if ( strtotime(date('G:i')) > strtotime($time[0]) or strtotime(date('G:i')) == strtotime($time[0]) ) {
                if ( strtotime(date('H:i')) < strtotime($time[1]) or strtotime(date('H:i')) == strtotime($time[1]) ) {
                    $is_open = true;
                } else {
                    $is_open = false;
                }
            } else {
                $is_open = false;
            }                    
        } else {
            $is_open = false;
        }

        if ( $is_open ) {
            $time['open'] = true;
            return ( $debug ? $time : ((isset($time[2]) and !empty($time[2])) ? $time[2] : 30) );
        } else {
            $time['open'] = false; 
            return ( $debug ? $time : false );
        }
    } else {
        return false;
    }

}

/** Get next time open */
function wolt_shop_next_open() {

    $settings = wolt_drive_get_settings();

    if ( !empty($settings) ) {
        date_default_timezone_set(wp_timezone_string());

        $i = 1;
        $closed = true;

        $next_date = new DateTime('tomorrow');
        $special_hours = explode(PHP_EOL, $settings['special']);                

        while ($closed) {

            if ( is_array($special_hours) ) {
                foreach ($special_hours as $date_times) {
                    $date = explode(';', $date_times);
                    if( is_array($date) ) {
                        if ( strtotime($next_date->format('Y-m-d')) == strtotime($date[0]) ) {
                            if ( $date[1] != '' and $date[2] != '' ) {
                                $next_open = array(
                                    'date'  => $next_date->format('Y-m-d'),
                                    'open'  => $date[1],
                                    'close' => $date[2],
                                    'special' => 'yes',
                                );
                                $closed = false;
                            } else {
                                $next_date->modify('+1 day');
                                continue;
                            }                                 
                        }
                    }
                }
            }

            switch ($next_date->format('D')) {
                case 'Mon':
                    $time = explode(';', $settings['mon']);
                    break;
                case 'Tue':
                    $time = explode(';', $settings['tue']);
                    break;
                case 'Wed':
                    $time = explode(';', $settings['wed']);
                    break;
                case 'Thu':
                    $time = explode(';', $settings['thu']);
                    break;
                case 'Fri':
                    $time = explode(';', $settings['fri']);
                    break;
                case 'Sat':
                    $time = explode(';', $settings['sat']);
                    break;
                case 'Sun':
                    $time = explode(';', $settings['sun']);
                    break;
            }

            if ( !empty($time[0]) and !empty($time[1]) ) {
                $next_open = array(
                    'date'  => $next_date->format('Y-m-d'),
                    'open'  => $time[0],
                    'close' => $time[1],
                    'special' => 'no',
                );
                $closed = false;
            } else {
                $next_date->modify('+1 day');
            }

            if ( $i == 20 ) {
                $closed = false;
            } else {
                $i++;
            }

        }

        return $next_open;
    } else {
        return false;
    }

}

/** Helper function to test the openings times */
function wolt_test_opening_times() {

    $time = wolt_shop_is_open(true);
    if ( $time['open'] ) {
        echo '<br />';
        echo "The shop is open, with the prepare time: <br/>";
        esc_html(print_r($time));
    } else {
        echo '<br />';
        echo "The shop is closed<br/>";
        esc_html(print_r($time));
    }

    
    if ( $time = wolt_shop_is_open() ) {
        echo '<br />';
        echo "The shop is open, with the prepare time: ".esc_html($time)."<br/>";
    } else {
        echo '<br />';
        echo "The shop is closed<br/>";
    }

}