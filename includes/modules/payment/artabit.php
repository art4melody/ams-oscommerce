<?php

/**
 * ©2014 Artabit
 * 
 * Permission is hereby granted to any person obtaining a copy of this software
 * and associated documentation for use and/or modification in association with
 * the artabit.com service.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * 
 * AMS osCommerce payment plugin using the artabit.com service.
 * 
 */
 
  if(!(function_exists('tep_remove_order'))) {
    function tep_remove_order($order_id, $restock = false) {
      if ($restock == 'on') {
        $order_query = tep_db_query("select products_id, products_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");
        while ($order = tep_db_fetch_array($order_query)) {
          tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity + " . $order['products_quantity'] . ", products_ordered = products_ordered - " . $order['products_quantity'] . " where products_id = '" . (int)$order['products_id'] . "'");
        }
      }

      tep_db_query("delete from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
      tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int)$order_id . "'");
      tep_db_query("delete from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int)$order_id . "'");
      tep_db_query("delete from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$order_id . "'");
      tep_db_query("delete from " . TABLE_ORDERS_TOTAL . " where orders_id = '" . (int)$order_id . "'");
    }
  }

  class artabit {
    var $code, $title, $description, $enabled;

    // Class Constructor
    function artabit () {
      global $order;

      $this->code = 'artabit';
      $this->title = MODULE_PAYMENT_AMS_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_AMS_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_AMS_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_AMS_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_AMS_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_AMS_ORDER_STATUS_ID;
      }

      if (is_object($order)) {
        $this->update_status();
      }
    }

    // Class Methods
    function update_status () {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_AMS_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . intval(MODULE_PAYMENT_AMS_ZONE) . "' and zone_country_id = '" . intval($order->billing['country']['id']) . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          }
          elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }

     
      if ($this->enabled) {
        // check currency
        $currencies = array('IDR');

        if (array_search($order->info['currency'], $currencies) === false) {
          //print 'artabit: currency is not supported';
          $this->enabled = false;
        } else {
          $total = $order->info['total'] * $order->info['currency_value'];
          if ($total < 5000) {
            //print 'artabit: invoice total < IDR 5000';
            $this->enabled = false;
          }
        }

         // check that api key is not blank
        if (!MODULE_PAYMENT_AMS_APITOKEN OR !strlen(MODULE_PAYMENT_AMS_APITOKEN) OR
            !MODULE_PAYMENT_AMS_APISECRET OR !strlen(MODULE_PAYMENT_AMS_APISECRET)) {
          //print 'no API access data in configuration';
          $this->enabled = false;
        }
      }
    }

    function javascript_validation () {
      return false;
    }

    function selection () {
      return array('id' => $this->code, 'module' => $this->title);
    }

    function pre_confirmation_check () {
      return false;
    }

    function confirmation () {
      return false;
    }

    function process_button () {
      return false;
    }

    function before_process () {
      return false;
    }

    function after_process () {
      global $insert_id, $order;
      require_once 'artabit/ams_lib.php';

      // change order status to value selected by merchant
      tep_db_query("update ". TABLE_ORDERS. " set orders_status = " . intval(MODULE_PAYMENT_AMS_UNPAID_STATUS_ID) . " where orders_id = ". intval($insert_id));   
      $total = $order->info['total'] * $order->info['currency_value'];

      $options = array(
        'amount_local' => $total,
        'transaction_id' => strval($insert_id),
        'return_url' => tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'),
        'cancel_url' => tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'),
        'callback_url' => tep_href_link('ams_callback.php', '', 'SSL', true, true),
        'apiToken' => MODULE_PAYMENT_AMS_APITOKEN,
        'apiSecret' => MODULE_PAYMENT_AMS_APISECRET
      );
    
      $invoice = amsCreateInvoice($insert_id, $total, $options);

      if (!is_array($invoice) or array_key_exists('error', $invoice)) {
        tep_remove_order($insert_id, $restock = true);
        tep_redirect(FILENAME_CHECKOUT_PAYMENT);
      }
      else {
        $_SESSION['cart']->reset(true);
        tep_redirect($invoice['invoicePage']);
      }
      return false;
    }

    function get_error () {
      return false;
    }

    function check () {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_AMS_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install () {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) "
        ."values ('Enable Artabit Module', 'MODULE_PAYMENT_AMS_STATUS', 'False', 'Do you want to accept bitcoin payments via artabit.com?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) "
        ."values ('API Token', 'MODULE_PAYMENT_AMS_APITOKEN', '', 'Enter you API Token which you generated at ams.artabit.com', '6', '0', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) "
        ."values ('API Secret', 'MODULE_PAYMENT_AMS_APISECRET', '', 'Enter you API Secret which you generated at ams.artabit.com', '6', '0', now());");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) "
        ."values ('Unpaid Order Status', 'MODULE_PAYMENT_AMS_UNPAID_STATUS_ID', '" . intval(DEFAULT_ORDERS_STATUS_ID) .  "', 'Automatically set the status of unpaid orders to this value.', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) "
        ."values ('Paid Order Status', 'MODULE_PAYMENT_AMS_PAID_STATUS_ID', '2', 'Automatically set the status of paid orders to this value.', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) "
        ."values ('Payment Zone', 'MODULE_PAYMENT_AMS_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) "
        ."values ('Sort Order of Display.', 'MODULE_PAYMENT_AMS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '2', now())");
    }

    function remove () {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array(
        'MODULE_PAYMENT_AMS_STATUS',
        'MODULE_PAYMENT_AMS_APITOKEN',
        'MODULE_PAYMENT_AMS_APISECRET',
        'MODULE_PAYMENT_AMS_UNPAID_STATUS_ID',
        'MODULE_PAYMENT_AMS_PAID_STATUS_ID',
        'MODULE_PAYMENT_AMS_ZONE',
        'MODULE_PAYMENT_AMS_SORT_ORDER');
    }
  }
?>
