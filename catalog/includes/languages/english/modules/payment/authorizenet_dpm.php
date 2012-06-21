<?php
/*
  $Id: authorizenet.php 08/03/2006 23:51:00 Rhea Anthony Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License
*/

// Admin Configuration Items

  define('MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_ADMIN_TITLE', 'Authorize.net DPM'); // Payment option title as displayed in the admin
  define('MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_DESCRIPTION', '<b>Automatic Approval Credit Card Numbers:</b><br /><br />Visa#: 4007000000027<br />MC#: 5424000000000015<br />Discover#: 6011000000000012<br />AMEX#: 370000000000002<br /><br /><b>Note:</b> The credit card numbers above will return a decline in Live mode, and an
approval in Test mode.  Any future date can be used for the expiry date and any 3 digit number can be used for the CVV Code (4 digit number for AMEX)<br /><br /><b>Automatic Decline Credit Card Number:</b><br /><br />Card #: 4222222222222<br /><br />Use the number above to test declined cards.<br /><br />');

  // Catalog Items

  define('MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CATALOG_TITLE', 'Credit Card');  // Payment option title as displayed to the customer
  define('MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CREDIT_CARD_TYPE', 'Credit Card Type:');
  define('MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CREDIT_CARD_OWNER_FN', 'First Name:');
  define('MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CREDIT_CARD_OWNER_LN', 'Last Name:');
  define('MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CREDIT_CARD_NUMBER', 'Credit Card Number:');
  define('MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CREDIT_CARD_EXPIRES', 'Credit Card Expiry Date:');
  //define('MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CVV', 'CVV Number <a href="javascript:newwindow()"><u>More Info</u></a>');
  define('MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_CVV', 'CVV Number <a onClick="javascript:window.open(\'cvv_help.php\',\'jav\',\'width=500,height=550,resizable=no,toolbar=no,menubar=no,status=no\');"><u>More Info</u></a>');
  define('MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_DECLINED_MESSAGE', 'Your credit card could not be authorized for this reason. Please correct any information and try again or contact us for further assistance.');
  define('MODULE_PAYMENT_AUTHORIZENET_DPM_TEXT_ERROR', 'Credit Card Error!');
?>
