<?php
/**
 * Plugin Name: Aurpay
 * Plugin URI: https://dashboard.aurpay.net
 * Description: Pay with Crypto For WooCommerce, Let your customer pay with ETH, USDC, USDT, DAI, lowest fees, non-custodail & no fraud/chargeback, 50+ cryptos. Invoice, payment link, payment button.
 * Version: 1.0.1
 * Author: Aurtech01
 * Author URI: https://www.linkedin.com/company/aurpay/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: Expand customer base with crypto payment, non-custodail & no fraud/chargeback, low fees, 50+ cryptos. Invoice, payment link, payment button.
 * Tags: Crypto, cryptocurrency, crypto payment, erc20, cryptocurrency, e-commerce, bitcoin, bitcoin lighting network, ethereum, crypto pay, smooth withdrawals, cryptocurrency payments, low commission, pay with meta mask, payment button, invoice, crypto woocommerce，bitcoin woocommerce，ethereum，pay crypto，virtual currency，bitcoin wordpress plugin，free crypto plugin
 * Requires at least: 5.8
 * Requires PHP: 7.2
 */

defined( 'ABSPATH' ) || exit;

if ( !defined('WC_AURPAY')) {
    define('WC_AURPAY', 'WC_AURPAY');
}

if ( !defined( 'WC_Aurpay_VERSION' ) ) {
    define('WC_Aurpay_VERSION', '1.0');
}

if ( !defined( 'WC_Aurpay_ID' ) ) {
    define('WC_Aurpay_ID', 'aurpay' /*'aurpay'*/);
}

if ( !defined( 'WC_Aurpay_DIR' ) ) {
    define('WC_Aurpay_DIR', rtrim(plugin_dir_path(__FILE__), '/'));
}

if ( ! defined( 'WC_Aurpay_FILE' ) ) {
    define( 'WC_Aurpay_FILE', __FILE__ );
}

if ( !defined( 'WC_Aurpay_URL' ) ) {
    define('WC_Aurpay_URL', rtrim(plugin_dir_url(__FILE__), '/'));
}

add_action('plugins_loaded', 'aurpay_init');
add_action('wp_enqueue_scripts','aurpay_add_styles');

add_filter( 'allowed_redirect_hosts', 'aurpay_allowed_redirect_hosts' );
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'aurpay_plugin_edit_link' );


if ( !function_exists( 'aurpay_allowed_redirect_hosts' )) {
    function aurpay_allowed_redirect_hosts($allowed_host)
    {
        $allowed_host[] = 'dashboard.aurpay.net';
        return $allowed_host;
    }
}

/**
 * Runs on Plugin's activation
 * @since 1.0
 * @version 1.0
 */
if ( !function_exists('aurpay_init')) {
    function aurpay_init()
    {
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }
        require_once WC_Aurpay_DIR . '/includes/class-checkout.php';
        require_once WC_Aurpay_DIR . '/includes/class-rest-api.php';

        $appgwc = new Aurpay_Payment_Gateway();

        $apapi = new Aurpay_Webhoot($appgwc->apikey);

        if ( isset ($appgwc->public_key) and $appgwc->public_key != '' ) {
            return false;
        }
        else {
            wp_enqueue_style('aurpay-banner-style' , plugin_dir_url( __FILE__ ) . '/assets/css/aurpay-usage-notice.css');
            add_action('admin_notices', 'aurpay_render_usage_notice');
        }

        add_filter('woocommerce_payment_gateways', array($appgwc, 'woocommerce_aurpay_add_gateway'), 10, 1);
        add_action('woocommerce_receipt_' . $appgwc->id, array($appgwc, 'receipt_page'));
        add_action('woocommerce_update_options_payment_gateways_' . $appgwc->id, array($appgwc, 'process_admin_options')); // WC >= 2.0
        add_action('woocommerce_update_options_payment_gateways', array($appgwc, 'process_admin_options'));
    }
}


/**
 * Runs on Plugin's activation
 * @since 1.0
 * @version 1.0
 */
if ( !function_exists( 'aurpay_om_woocommerce_requirements' ) ) {
    function aurpay_om_woocommerce_requirements() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_attr_e( 'Please activate', 'woocommerce' );?> <a href='https://wordpress.org/plugins/woocommerce/'><?php esc_attr_e( 'Woocommerce', 'woocommerce' ); ?></a> <?php esc_attr_e( 'to use this plugin.', 'woocommerce' ); ?></p>
        </div>
        <?php
    }
}


/**
 * Plug-in edit link
 * @since 1.0
 * @version 1.0
 */
if ( !function_exists( 'aurpay_plugin_edit_link' )) {
    function aurpay_plugin_edit_link($links)
    {
        return array_merge(
            array(
                'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . WC_Aurpay_ID ) . '">' . __( 'Settings', 'aurpay' ) . '</a>',
            ),
            $links
        );
    }
}


/**
 * Add style to aurpay payment
 * @since 1.0
 * @version 1.0
 */
if ( !function_exists( 'aurpay_add_styles' )) {
    function aurpay_add_styles()
    {
        wp_enqueue_style('style_file' , plugin_dir_url( __FILE__ ) . 'assets/css/aurpay.css');
    }
}

/**
 * Renders the usage notice.  Only shown once and on plugin activation.
 */
if ( !function_exists( 'aurpay_render_usage_notice' )) {
    function aurpay_render_usage_notice() {
        wp_enqueue_style('aurpay-banner-style' , WC_Aurpay_FILE . 'assets/css/aurpay-usage-notice.css');

        ?>
        <div class="ap-connection-banner aurpay-usage-notice">
            <span class="notice-dismiss aurpay-usage-notice__dismiss" title="Dismiss this notice"></span>
            <div class="ap-connection-banner__container-top-text">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <rect x="0" fill="none" width="24" height="24" />
                <g><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm1 15h-2v-2h2v2zm0-4h-2l-.5-6h3l-.5 6z" /></g>
            </svg>
            <span>We're thrilled to announce we will reward 10-200 Satoshi for successfully activated merchants!</span>
            </div>
            <span class="notice-dismiss aurpay-usage-notice__dismiss" title="Dismiss this notice"></span>
            <div class="ap-connection-banner__inner">
                <div class="ap-connection-banner__content">
                    <div class="ap-connection-banner__logo">
                        <img src="<?php echo esc_url( plugins_url( 'assets/images/logo_aurpay.svg', WC_Aurpay_FILE ) ); ?>" alt="logo">
                    </div>
                    <h2 class="ap-connection-banner__title">Empower Your Business with Aurpay Crypto Payment</h2>
                    <div class="ap-connection-banner__columns">
                        <div class="ap-connection-banner__text">Aurpay crypto payment allows you easily accept 50+ tokens. Increase Boost your sales with lifetime commission.</div>
                        <div class="ap-connection-banner__text">Aurpay crypto payment gives your customers best checkout experience. Save your time and cost significantly.</div>
                    </div>
                    <div class="ap-connection-banner__rows">
                        <div class="ap-connection-banner__text">By setting up Aurpay, get a merchant account and save your key in WooCommerce Payment settings.</div>
                    </div>
                    <div class="ap-connection-banner__rows">
                        <div class="ap-connection-banner__text ap-connection-banner__step"><b>Step 1:</b> Click Integration tab on the Aurpay dashboard left sidebar. Get Aurpay PublicKey for WooCommerce.</div>
                        <a id="ap-connect-button--alt" rel="external" target="_blank" href="https://dashboard.aurpay.net" class="ap-banner-cta-button">Setup Aurpay</a>
                    </div>
                    <div class="ap-connection-banner__rows">
                        <div class="ap-connection-banner__text ap-connection-banner__step"><b>Step 2:</b> Save your PublicKey in WooCommerce Payment settings.</div>
                        <a id="ap-connect-button--alt" target="_self" href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . WC_Aurpay_ID ) ?>" class="ap-banner-cta-button">Settings</a>
                    </div>
                </div>
                <div class="ap-connection-banner__image-container">
                    <img class="ap-connection-banner__image-background" src="<?php echo esc_url( plugins_url( 'assets/images/background.svg', WC_Aurpay_FILE ) ); ?>" />
                    <picture>
                        <source type="image/webp" srcset="<?php echo esc_url( plugins_url( 'assets/images/img_aurpay.webp', WC_Aurpay_FILE ) ); ?> 1x, <?php echo esc_url( plugins_url( 'assets/images/img_aurpay-2x.webp', WC_Aurpay_FILE ) ); ?> 2x">
                        <img class="ap-connection-banner__image" srcset="<?php echo esc_url( plugins_url( 'assets/images/img_aurpay.png', WC_Aurpay_FILE ) ); ?> 1x, <?php echo esc_url( plugins_url( 'assets/images/img_aurpay-2x.png', WC_Aurpay_FILE ) ); ?> 2x" src="<?php echo esc_url( plugins_url( 'assets/images/img_aurpay.png', WC_Aurpay_FILE ) ); ?>" alt="">
                    </picture>
                </div>
            </div>
        </div>
        <?php

        wp_enqueue_script(
            'aurpay-notice-banner-js' ,
            WC_Aurpay_FILE . 'assets/js/aurpay-usage-notice.js',
            array( 'jquery' )
        );
    }
}
