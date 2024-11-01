<?php

// Required PHP files
require_once STRIPE_PAYMENTS_PLUGIN_DIR . '/admin.php';
require_once STRIPE_PAYMENTS_PLUGIN_DIR . '/form.php';
require_once STRIPE_PAYMENTS_PLUGIN_DIR . '/ajax-payment.php';
require_once STRIPE_PAYMENTS_PLUGIN_DIR . '/short-codes.php';

// Add javascript and css to the page
add_action('init', 'enqueueHeaderCode', 0);
add_action('wp_head', 'addHeaderCode', 0);

function enqueueHeaderCode() {
    // Use the global defined in the stripe.php file.
    global $publicKey;
    global $isLiveKeys;
    global $isPolitical;
    global $isLive;
    
    wp_enqueue_style( 'stripe', STRIPE_PAYMENTS_PLUGIN_URL . '/stripe.css', 0);
    
    // add our scripts and their dependencies
    // We're going to manually add jQuery
    // wp_enqueue_script('stripe_payments_plugin', STRIPE_PAYMENTS_PLUGIN_URL . '/stripe.js', array('jquery'));
    wp_enqueue_script('stripe', 'https://js.stripe.com/v1/', array('jquery'));
    wp_enqueue_script('stripe_payments_plugin', STRIPE_PAYMENTS_PLUGIN_URL . '/stripe.js', array('jquery'));
}

function addHeaderCode() {
    // Use the global defined in the stripe.php file.
    global $publicKey, $isPolitical, $isLive;
    // emit the javascript variable that holds our public stripe key
    $isLiveKeys = ($isLive===true) ? 'true' : 'false';
    $isPolitical = ($isPolitical===true) ? 'true' : 'false';
    echo "<script>var stripePublishable='".$publicKey."';var isPolitical=".$isPolitical.";var isLiveKeys=".$isLiveKeys.";</script>\n";
}