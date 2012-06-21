<?php



/* $Id: authorizenet_dpm.php 23rd August, 2006 18:50:00 Brent O'Keeffe $

   Released under the GNU General Public License

   osCommerce, Open Source E-Commerce Solutions

   http://www.oscommerce.com

   Original portions copyright 2003 osCommerce

   Updated portions copyright 2004 Jason LeBaron (jason@networkdad.com)

   Restoration of original portions and addition of new portions Copyright (c) 2006 osCommerce

   Updated portions and additions copyright 2006 Brent O'Keeffe - JK Consulting. (brent@jkconsulting.net)

   Updated portions and additions copyright 2012 Joseph Persie - Supraliminal Solutions . (persie.joseph@gmail.com)
*/



  class authorizenet_dpm {

    var $code, $title, $description, $enabled, $response;


    const LIVE_URL = 'https://secure.authorize.net/gateway/transact.dll';
    const SANDBOX_URL = 'https://test.authorize.net/gateway/transact.dll';



// class constructor

    function authorizenet_dpm() {



      $this->code = 'authorizenet_dpm';



      if ($_GET['main_page'] != '') {

        $this->title = MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CATALOG_TITLE; // Module title in Catalog

      } else {

        $this->title = MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_ADMIN_TITLE; // Module title it Admin

      }

     

      $this->description = MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_DESCRIPTION; // Description of Module in Admin

      $this->enabled = ((MODULE_PAYMENT_AUTHORIZENET_DPM_STATUS == 'True') ? true : false); // If the module is installed or not

      $this->sort_order = MODULE_PAYMENT_AUTHORIZENET_DPM_SORT_ORDER; // Sort Order of this payment option on the checkout_payment.php page

      $this->form_action_url = (MODULE_PAYMENT_AUTHORIZENET_DPM_TESTMODE == "Live") ? self::LIVE_URL : self::SANDBOX_URL;


      if ((int)MODULE_PAYMENT_AUTHORIZENET_DPM_ORDER_STATUS_ID > 0) {

        $this->order_status = MODULE_PAYMENT_AUTHORIZENET_DPM_ORDER_STATUS_ID;

      }


      if (is_object($order)) $this->update_status();

	  

	    $this->cc_types = array('VISA' => 'Visa',

                              'MASTERCARD' => 'MasterCard',

                              'DISCOVER' => 'Discover Card',

                              'AMEX' => 'American Express');                         



    }



    function update_status() {

      global $order, $db;



      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_AUTHORIZENET_DPM_ZONE > 0) ) {

        $check_flag = false;

        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_AUTHORIZENET_DPM_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");

        while ($check = tep_db_fetch_array($check_query)) {

          if ($check['zone_id'] < 1) {

            $check_flag = true;

            break;

          } elseif ($check['zone_id'] == $order->billing['zone_id']) {

            $check_flag = true;

            break;

          }

        }



        if ($check_flag == false) {

          $this->enabled = false;

        }

      }

    }



    // Validate the credit card information via javascript (Number, Owner, and CVV Lengths)

    function javascript_validation() {

    }



    // Display Credit Card information on the checkout_payment.php page

    function selection() {

      global $order;

      $selection = array('id' => $this->code,

                         'module' => MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CATALOG_TITLE,

                         'fields' => array(
                                           array('title' => MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CREDIT_CARD_OWNER_FN,

                                                 'field' => tep_draw_input_field('x_first_name', $order->billing['firstname'])),

                                           array('title' => MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CREDIT_CARD_OWNER_LN,

                                                 'field' => tep_draw_input_field('x_last_name', $order->billing['lastname']))
                                          ));

      return $selection;

    }





    // Evaluates the Credit Card Type for acceptance and validity of the Credit Card Number and Expiry Date

    function pre_confirmation_check() {

    }



    // Display Credit Card Information on the Checkout Confirmation Page

    function confirmation() {

      global $order;

      $types_array = array();

      while (list($key, $value) = each($this->cc_types)) {

          $types_array[] = array('id' => $key,

                                 'text' => $value);

      }

      $confirmation = array('fields' => array(

                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CREDIT_CARD_OWNER_FN,

                                                    'field' => $_POST['x_first_name']),

                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CREDIT_CARD_OWNER_LN,

                                                    'field' => $_POST['x_last_name']),

                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CREDIT_CARD_TYPE,
  
                                                    'field' => tep_draw_pull_down_menu('x_card_type', $types_array)),

                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CREDIT_CARD_NUMBER,

                                                    'field' => tep_draw_input_field('x_card_num')),

                                              array('title' => MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CREDIT_CARD_EXPIRES,

                                                    'field' => tep_draw_input_field('x_exp_date','','size=4, maxlength=4'))));



      if (MODULE_PAYMENT_AUTHORIZENET_DPM_USE_CVV == 'True') {

          $confirmation['fields'][] = array('title' => MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CVV,

                                         'field' => tep_draw_input_field('x_card_code','',"size=4, maxlength=4"));

      }


      return $confirmation;

    }



    function process_button() {

        global $order;


            $hidden_data = array(

        'x_login'               => MODULE_PAYMENT_AUTHORIZENET_DPM_LOGIN, // The login name as assigned to you by authorize.net

        //'x_tran_key'            => MODULE_PAYMENT_AUTHORIZENET_DPM_TXNKEY,  // The Transaction Key (16 digits) is generated through the merchant interface

        'x_relay_response'      => 'TRUE', // DPM uses relay response

        'x_relay_url'           => tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL', true),

        'x_delim_char'          => ',',

        'x_delim_data'          => 'TRUE', // The default delimiter is a comma

        'x_version'             => '3.1',  // 3.1 is required to use CVV codes

        'x_type'                => MODULE_PAYMENT_AUTHORIZENET_DPM_AUTHORIZATION_TYPE == 'Authorize' ? 'AUTH_ONLY': 'AUTH_CAPTURE',

        'x_method'              => 'CC',

        'x_amount'              => number_format($order->info['total'], 2),

        'x_fp_hash'             => self::getFingerprint(MODULE_PAYMENT_AUTHORIZENET_DPM_LOGIN, MODULE_PAYMENT_AUTHORIZENET_DPM_TXNKEY, number_format($order->info['total'], 2), time(), time()),

        'x_fp_sequence'         => time(),

        'x_fp_timestamp'        => time(),

        'x_email_customer'      => MODULE_PAYMENT_AUTHORIZENET_DPM_EMAIL_CUSTOMER == 'True' ? 'TRUE': 'FALSE',

        'x_email_merchant'      => MODULE_PAYMENT_AUTHORIZENET_DPM_EMAIL_MERCHANT == 'True' ? 'TRUE': 'FALSE',

        'x_cust_id'             => $_SESSION['customer_id'],

        'x_first_name'          => $_POST['x_first_name'],

        'x_last_name'           => $_POST['x_last_name'],

        'x_invoice_num'         => $new_order_id,

        'x_company'             => $order->billing['company'],

        'x_address'             => $order->billing['street_address'],

        'x_city'                => $order->billing['city'],

        'x_state'               => $order->billing['state'],

        'x_zip'                 => $order->billing['postcode'],

        'x_country'             => $order->billing['country']['title'],

        'x_phone'               => $order->customer['telephone'],

        'x_email'               => $order->customer['email_address'],

        'x_ship_to_first_name'  => $order->delivery['firstname'],

        'x_ship_to_last_name'   => $order->delivery['lastname'],

        'x_ship_to_address'     => $order->delivery['street_address'],

        'x_ship_to_city'        => $order->delivery['city'],

        'x_ship_to_state'       => $order->delivery['state'],

        'x_ship_to_zip'         => $order->delivery['postcode'],

        'x_ship_to_country'     => $order->delivery['country']['title'],

        'x_description'         => $description,

        'x_test_request'        => (MODULE_PAYMENT_AUTHORIZENET_DPM_TESTMODE == 'Test' ? 'TRUE' : 'FALSE'),
        'Date'                  => time(),

        tep_session_name()      => tep_session_id(),

        'x_customer_ip' => $_SERVER['REMOTE_ADDR']);

      foreach($hidden_data as $key=>$value) {
          $process_button_string .= tep_draw_hidden_field($key, $value);
      }

      return $process_button_string;
    }



    function before_process() {

      global $order;

      $this->response = $_POST;
      
  // If the response code is not 1 (approved) then redirect back to the payment page with the appropriate error message

      if ($this->response['x_response_code'] != '1') {

/*
        echo "<pre>";

            print_r($this->response);

        echo "</pre>";

*/
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode($this->response['x_response_reason_text']) . ' - ' . urlencode(MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_DECLINED_MESSAGE), 'SSL', true, false));

      }

    }



    function after_process() {

      global $insert_id;

      

      if ((int)$insert_id < 1 || !is_array($this->response)) return false;

      

      $avs_codes = array('A' => 'Address (Street) matches, ZIP does not',

                         'B' => 'Address information not provided for AVS check',

                         'E' => 'AVS error',

                         'G' => 'Non-U.S. Card Issuing Bank',

                         'N' => 'No Match on Address (Street) or ZIP',

                         'P' => 'AVS not applicable for this transaction',

                         'R' => 'Retry System unavailable or timed out',

                         'S' => 'Service not supported by issuer',

                         'U' => 'Address information is unavailable',

                         'W' => 'Nine digit ZIP matches, Address (Street) does not',

                         'X' => 'Address (Street) and nine digit ZIP match',

                         'Y' => 'Address (Street) and five digit ZIP match',

                         'Z' => 'Five digit ZIP matches, Address (Street) does not');

            

      $card_codes = array('M' => 'Match',

                          'N' => 'No Match',

                          'P' => 'Not Processed',

                          'S' => 'Should have been present',

                          'U' => 'Issuer unable to process request');

            

      $details  = 'Transaction ID|' . $this->response['x_trans_id'] . ';';

      $details .= 'AVS Response|' . (array_key_exists($this->response['x_avs_code'], $avs_codes) ? $avs_codes[$this->response['x_avs_code']] : $this->response['x_avs_code']) . ';';


      $details .= 'Card Code Response|' . (array_key_exists($this->response['x_cvv2_resp_code'], $card_codes) ? $card_codes[$this->response["x_cvv2_resp_code"]] : $this->response["x_cvv2_resp_code"]);

                       

      tep_db_query("UPDATE " . TABLE_ORDERS . " 

                    SET cc_type = '" . $this->response['x_card_type'] . "',

                        cc_owner = '" . $this->response['x_first_name']. " " . $this->response['x_last_name'] . "',

                        cc_number = '" . $this->response['x_account_number'] . "'

                    WHERE orders_id = " . (int)$insert_id . " LIMIT 1");


      return false;

    }



    function get_error() {

      global $_GET;



      $error = array('title' => MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_ERROR,

                     'error' => stripslashes(urldecode($_GET['error'])));



      return $error;

    }



    function check() {

      

      if (!isset($this->_check)) {

        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_AUTHORIZENET_DPM_STATUS'");

        $this->_check = tep_db_num_rows($check_query);

      }

      return $this->_check;

    }



    function install() {

      

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Authorize.net DPM Module', 'MODULE_PAYMENT_AUTHORIZENET_DPM_STATUS', 'True', 'Do you want to accept Authorize.net payments via the DPM Method?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Login Username', 'MODULE_PAYMENT_AUTHORIZENET_DPM_LOGIN', 'Your User Name', 'The login username used for the Authorize.net service', '6', '0', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Transaction Key', 'MODULE_PAYMENT_AUTHORIZENET_DPM_TXNKEY', '16 digit key', 'Transaction Key used for encrypting TP data', '6', '0', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_AUTHORIZENET_DPM_TESTMODE', 'Test', 'Transaction mode used for processing orders', '6', '0', 'tep_cfg_select_option(array(\'Test\', \'Live\'), ', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Authorization Type', 'MODULE_PAYMENT_AUTHORIZENET_DPM_AUTHORIZATION_TYPE', 'Authorize/Capture', 'Do you want submitted credit card transactions to be authorized only, or authorized and captured?', '6', '0', 'tep_cfg_select_option(array(\'Authorize\', \'Authorize/Capture\'), ', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Customer Notifications', 'MODULE_PAYMENT_AUTHORIZENET_DPM_EMAIL_CUSTOMER', 'False', 'Should Authorize.Net e-mail a receipt to the customer?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Merchant Notifications', 'MODULE_PAYMENT_AUTHORIZENET_DPM_EMAIL_MERCHANT', 'True', 'Should Authorize.Net e-mail a receipt to the merchant?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Request CVV Number', 'MODULE_PAYMENT_AUTHORIZENET_DPM_USE_CVV', 'True', 'Do you want to ask the customer for the card\'s CVV number', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_AUTHORIZENET_DPM_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_AUTHORIZENET_DPM_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");

      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_AUTHORIZENET_DPM_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");

    }



    function remove() {

      

      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");

    }



    function keys() {

      return array('MODULE_PAYMENT_AUTHORIZENET_DPM_STATUS', 'MODULE_PAYMENT_AUTHORIZENET_DPM_LOGIN', 'MODULE_PAYMENT_AUTHORIZENET_DPM_TXNKEY', 'MODULE_PAYMENT_AUTHORIZENET_DPM_TESTMODE', 'MODULE_PAYMENT_AUTHORIZENET_DPM_AUTHORIZATION_TYPE', 'MODULE_PAYMENT_AUTHORIZENET_DPM_EMAIL_CUSTOMER', 'MODULE_PAYMENT_AUTHORIZENET_DPM_EMAIL_MERCHANT', 'MODULE_PAYMENT_AUTHORIZENET_DPM_USE_CVV', 'MODULE_PAYMENT_AUTHORIZENET_DPM_SORT_ORDER', 'MODULE_PAYMENT_AUTHORIZENET_DPM_ZONE', 'MODULE_PAYMENT_AUTHORIZENET_DPM_ORDER_STATUS_ID'); //'MODULE_PAYMENT_AUTHORIZENET_DPM_METHOD'
    }

    public static function getFingerprint($api_login_id, $transaction_key, $amount, $fp_sequence, $fp_timestamp) {
        if (function_exists('hash_hmac')) {
            return hash_hmac("md5", $api_login_id . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . $amount . "^", $transaction_key);
        }
        return bin2hex(mhash(MHASH_MD5, $api_login_id . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . $amount . "^", $transaction_key));
    }

  }
