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

require 'artabit/ams_lib.php';
require 'includes/application_top.php';

$response = amsVerifyCallback();
if (is_string($response)) {
  amsLog('ams callback error: ' . $response);
} else {
  $order_id = intval($response['transaction_id']);
  
  switch($response['status']) {
    case 0: // Created
      break;
    case 1: // Received
    case 2: // Confirmed
      if(function_exists('tep_db_query')) {
        tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" .MODULE_PAYMENT_AMS_PAID_STATUS_ID . "', last_modified = now() where orders_id = '" . intval($order_id) . "'");
      }
      if(function_exists('tep_db_perform')) {
        $sql_data_array = array('orders_id' => $order_id,
                                'orders_status_id' => MODULE_PAYMENT_AMS_PAID_STATUS_ID,
                                'date_added' => 'now()',
                                'customer_notified' => (SEND_EMAILS == 'true') ? '1' : '0',
                                'comments' => $response['confirmation_id']);

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
      }
      break;
    case -1: // Canceled
      if(function_exists('tep_remove_order')) {
          tep_remove_order($order_id, $restock = true);
    }
      break;
    case -2: // Unknown
      break;
    case -3: // Incomplete
      break;
    case -4: // Late
      break;
  }
}
?>
