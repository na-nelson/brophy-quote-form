<?php
/**
 * Plugin Name: Brophy Quote Form
 * Plugin URI: https://www.nelsonnelson.llc/
 * Description: Quote form shortcode and settings for Brophy Box.
 * Version: 0.1.0
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * Author: Nelson & Nelson, LLC
 * Author URI: https://www.nelsonnelson.llc/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: brophy-quote-form
 *
 * @package BrophyQuoteForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BQF_VERSION', '0.1.0' );
define( 'BQF_FILE', __FILE__ );
define( 'BQF_PATH', plugin_dir_path( __FILE__ ) );
define( 'BQF_URL', plugin_dir_url( __FILE__ ) );

const BB_TABLES = [
    'container_prices'   		=> 'tblMJAta4DETQrK3l',
    'delivery_pricing_zones'    => 'tblsyDLEGIbvYqTpW',
    'zip_codes'     			=> 'tblH8gIq8T09n985p',
    'discounts'					=> 'tblTL8BvqzNOKd3uv',
];

const BB_TABLE_PRIMARY_FIELDS = [
	'zip_codes'        => 'zip-code',
	'container_prices' => 'id',
	'delivery_pricing_zones' => 'id',
    'discounts' => 'id',
];


require_once BQF_PATH . 'includes/brophy-box-airtable.php';
require_once BQF_PATH . 'includes/admin.php';
require_once BQF_PATH . 'includes/brophy-box-ajax.php';
require_once BQF_PATH . 'includes/brophy-box-shortcode.php';
