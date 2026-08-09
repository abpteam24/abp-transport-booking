<?php
	/**
	 * Plugin Name: ABP Transport Booking
	 * Description: WooCommerce transport booking for bus, ferry, shuttle and coach services with seat plans, ticket types, routes, schedules and return trips.
	 * Version: 1.0.0
	 * Author: abpteam
	 * Author URI: https://abp-team.com
	 * Text Domain: abp-transport-booking
	 * Domain Path: /languages
	 * WC requires at least: 8.0.0
	 *  WC tested up to: latest
	 *  Requires PHP: 7.4
	 *  Requires MySQL: 5.7+
	 *  License: GPLv3
	 *  License URI: https://www.gnu.org/licenses/gpl-3.0.html
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPTB_Plugin' ) ) {
		class ABPTB_Plugin {
			public function __construct() {
				add_action( 'admin_init', function () {
					if ( ! function_exists( 'is_plugin_active' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
				} );
				add_action(
					'before_woocommerce_init', // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
					function () {
						if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
							\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
								'custom_order_tables',
								__FILE__,
								true
							);
						}
					}
				);
				$this->load_plugin();
			}

			private function load_plugin(): void {
				if ( ! defined( 'ABPTB_PLUGIN_FILE' ) ) {
					define( 'ABPTB_PLUGIN_FILE', __FILE__ );
				}
				if ( ! defined( 'ABPTB_VERSION' ) ) {
					define( 'ABPTB_VERSION', '1.0.0' );
				}
				if ( ! defined( 'ABPTB_DIR' ) ) {
					define( 'ABPTB_DIR', plugin_dir_path( __FILE__ ) );
				}
				if ( ! defined( 'ABPTB_URL' ) ) {
					define( 'ABPTB_URL', plugin_dir_url( __FILE__ ) );
				}
				if ( ! defined( 'ABPTB_BASE' ) ) {
					define( 'ABPTB_BASE', basename( __FILE__ ) );
				}
				if ( ! defined( 'ABPTB_BLANK_IMG_URL' ) ) {
					define( 'ABPTB_BLANK_IMG_URL', ABPTB_URL . 'assets/images/blank_image.png' );
				}
				require_once ABPTB_DIR . 'includes/abptb_dependencies.php';
				if ( ! defined( 'ABPTB_WC' ) ) {
					define( 'ABPTB_WC', ABPTB_Function::check_wc() );
				}
				if ( ! defined( 'ABPTB_Configuration' ) ) {
					define( 'ABPTB_Configuration', ABPTB_Function::get_option( 'abptb_configuration' ) );
				}
				if ( ! defined( 'ABPTB_Date_Config' ) ) {
					define( 'ABPTB_Date_Config', ABPTB_Function::get_option( 'abptb_date_config' ) );
				}
				if ( ! defined( 'ABPTB_Dates' ) ) {
					define( 'ABPTB_Dates', ABPTB_Function::get_option( 'abptb_dates' ) );
				}
				if ( ! defined( 'ABPTB_Category' ) ) {
					define( 'ABPTB_Category', ABPTB_Function::get_option( 'abptb_category' ) );
				}
				if ( ! defined( 'ABPTB_Organizer' ) ) {
					define( 'ABPTB_Organizer', ABPTB_Function::get_option( 'abptb_organizer' ) );
				}
				if ( ! defined( 'ABPTB_Feature' ) ) {
					define( 'ABPTB_Feature', ABPTB_Function::get_option( 'abptb_feature' ) );
				}
				if ( ! defined( 'ABPTB_Location' ) ) {
					define( 'ABPTB_Location', ABPTB_Function::get_option( 'abptb_location' ) );
				}
				if ( ! defined( 'ABPTB_Brand' ) ) {
					define( 'ABPTB_Brand', ABPTB_Function::get_option( 'abptb_brand' ) );
				}
				if ( ! defined( 'ABPTB_ids' ) ) {
					define( 'ABPTB_ids', ABPTB_Query::get_post_id());
				}
				if ( ! defined( 'ABPTB_Ticket' ) ) {
					define( 'ABPTB_Ticket', ABPTB_Function::get_option( 'abptb_ticket' ) );
				}
				if ( ! defined( 'ABPTB_Decor' ) ) {
					define( 'ABPTB_Decor', ABPTB_Function::get_option( 'abptb_decor' ) );
				}
				if ( ! defined( 'ABPTB_Ticket_SP' ) ) {
					define( 'ABPTB_Ticket_SP', ABPTB_Function::get_option( 'abptb_ticket_sp' ) );
				}
				if ( ! defined( 'ABPTB_On_Off' ) ) {
					define( 'ABPTB_On_Off', ABPTB_Function::get_option( 'abptb_on_off' ) );
				}
				if ( ! defined( 'ABPTB_JS_Date_Format' ) ) {
					define( 'ABPTB_JS_Date_Format', ABPTB_Function::date_format_js() );
				}
				if ( ! defined( 'ABPTB_Time_Format' ) ) {
					define( 'ABPTB_Time_Format', ABPTB_Date_Config['time_format'] ?? get_option( 'time_format' ) );
				}
			}
		}
		new ABPTB_Plugin();
		register_activation_hook( __FILE__, function () {
			if ( class_exists( 'ABPTB_Dependencies' ) ) {
				ABPTB_Dependencies::activation();
			}
		} );
		register_deactivation_hook( __FILE__, function () {
			if ( class_exists( 'ABPTB_Dependencies' ) ) {
				ABPTB_Dependencies::deactivate();
			}
		} );
	}