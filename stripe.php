<?php
/*
Plugin Name: Stripe Political Donations & Payments
Plugin URI: http://revolutionmessaging.com/stripe-payments
Description: This plugin turns Wordpress and Stripe.com into the best political donation website ever!
Author: Revolution Messaging, LLC
Version: 1.1.7
Author URI: http://revolutionmessaging.com/
*/

// Settings
$isLiveKeys             = get_option('stripe_payments_is_live_keys');
$isPolitical            = get_option('stripe_payments_is_political')==1?true:false;
$fullAddress            = get_option('stripe_payments_full_address')==1?true:false;
$employment             = stripslashes(get_option('stripe_payments_employment'));
$eligibility            = stripslashes(get_option('stripe_payments_eligibility'));
$isLive                 = $isLiveKeys==0?false:true;
$publicKey              = get_option('stripe_payments_test_public_key');
$secretKey              = get_option('stripe_payments_test_secret_key');
if($isLive) {
    $publicKey          = get_option('stripe_payments_live_public_key');
    $secretKey          = get_option('stripe_payments_live_secret_key');
    $ellaKey            = get_option('stripe_payments_ella_key');
    $ellaSecret         = get_option('stripe_payments_ella_secret');
}
$postmarkKey            = get_option('stripe_payments_postmark_key');
$postmarkFromAddress    = get_option('stripe_payments_postmark_address');
$postmarkFromName       = get_option('stripe_payments_postmark_name');
$postmarkSubject        = get_option('stripe_payments_postmark_subject');
$currencySymbol         = get_option('stripe_payments_currency_symbol');
$transPrefix            = get_option('stripe_payments_payment_trans_prefix');

// Define variables
define( 'STRIPE_PAYMENTS_VERSION', '1.1.6' );

if ( ! defined( 'STRIPE_PAYMENTS_PLUGIN_BASENAME' ) )
    define( 'STRIPE_PAYMENTS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'STRIPE_PAYMENTS_PLUGIN_NAME' ) )
    define( 'STRIPE_PAYMENTS_PLUGIN_NAME', trim( dirname( STRIPE_PAYMENTS_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'STRIPE_PAYMENTS_PLUGIN_DIR' ) )
    define( 'STRIPE_PAYMENTS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . STRIPE_PAYMENTS_PLUGIN_NAME );

if ( ! defined( 'STRIPE_PAYMENTS_PLUGIN_URL' ) )
    define( 'STRIPE_PAYMENTS_PLUGIN_URL', WP_PLUGIN_URL . '/' . STRIPE_PAYMENTS_PLUGIN_NAME );

if ( ! defined( 'STRIPE_PAYMENTS_PAYMENT_URL' ) )
    define( 'STRIPE_PAYMENTS_PAYMENT_URL', WP_PLUGIN_URL . '/ajax-payment.php' );

// Bootstrap this plugin
require_once STRIPE_PAYMENTS_PLUGIN_DIR . '/initialize.php';