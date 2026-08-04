<?php
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	if (!class_exists('ABPTB_Dependencies')) {
		class ABPTB_Dependencies {
			public function __construct() {
				add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'), 90);
				add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue'), 90);
				$this->load_file();
				add_action('init', [$this, 'register_cpt']);
				add_filter('use_block_editor_for_post_type', [$this, 'disable_gutenberg'], 10, 2);
				add_filter('plugin_action_links', array($this, 'plugin_settings_link'), 10, 2);
				add_action('upgrader_process_complete', [$this, 'flush_rewrite']);
				add_action('admin_init', array($this, 'activation_redirect'));
			}
			public function admin_enqueue($hook): void {
				$screen = get_current_screen();
				$post_type = $screen ? $screen->post_type : '';
				if (!str_contains($hook, 'transport-forge') && $post_type !== 'abptb_post') {
					return;
				}
				$label = ABPTB_Function::label();
				$post_id = get_the_ID();
				$this->global_enqueue();
				wp_enqueue_editor();
				wp_enqueue_media();
				//admin script
				wp_enqueue_script('jquery-ui-sortable');
				wp_enqueue_style('wp-color-picker');
				wp_enqueue_script('wp-color-picker');
				wp_enqueue_style('wp-codemirror');
				wp_enqueue_script('wp-codemirror');
				//=============================//
				wp_enqueue_script('abptb_admin', ABPTB_URL . 'assets/js/abptb_admin.js', array('jquery'), time(), true);
				wp_localize_script('abptb_admin', 'abptb_admin_data', [
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('abptb_admin_ajax_nonce'),
					'icon_url' => ABPTB_URL . 'assets/js/abptb_icons.json',
					'related_info' => wp_json_encode(ABPTB_Function::related_info_js($post_id)),
					'feature_data' => wp_json_encode(ABPTB_Feature::get_feature_js($post_id)),
					'sp_data' => wp_json_encode(ABPTB_Seat_Plan::get_sp_js($post_id)),
					'msg' => [
						'confirm_delete' => __('Are you sure you want to delete this item?', 'abp-transport-booking'),
						'confirm_ok' => __('1. Ok : To Remove Item .', 'abp-transport-booking'),
						'confirm_cancel' => __('2. Cancel : To Cancel .', 'abp-transport-booking'),
						'saving' => __('Saving.............!', 'abp-transport-booking'),
						'saved' => __('Saved...............!', 'abp-transport-booking'),
						'date_content' => __('Importing Global Date Configuration........', 'abp-transport-booking'),
						'additional_content' => __('Importing Global Additional Service Configuration........', 'abp-transport-booking'),
						'client_form_content' => __('Importing Global Attendee Form Configuration........', 'abp-transport-booking'),
						'faq_content' => __('Importing Global FAQ Configuration........', 'abp-transport-booking'),
						'tc_content' => __('Importing Global term And Condition Configuration........', 'abp-transport-booking'),
						'importing' => __('Importing........', 'abp-transport-booking'),
						'imported' => __('Imported Successfully............. !', 'abp-transport-booking'),
						'loading' => __('Loading........', 'abp-transport-booking'),
						'price_loading' => __('Price Configuration Loading........', 'abp-transport-booking'),
						'type_switch' => __('Ticket Type Switching... Please Wait.......', 'abp-transport-booking'),
						'loaded' => __('Loaded Successfully............. !', 'abp-transport-booking'),
						'order_loading' => __('Order Loading........ !', 'abp-transport-booking'),
						'error' => __('An error occurred. Please try again.', 'abp-transport-booking'),
						'deleting' => __('Deleting.............', 'abp-transport-booking'),
						'delete_success' => __('Item Deleted Successfully............. !', 'abp-transport-booking'),
						'select_stops' => __('Select Stops..', 'abp-transport-booking'),
						'select_ticket' => __('Select Ticket Type..', 'abp-transport-booking'),
						'post_loading' => $label . ' ' . __('List Loading.............', 'abp-transport-booking'),
						'permanent_remove' => $label . ' ' . __('Permanent Deleting.........!', 'abp-transport-booking'),
						'move_trash' => $label . ' ' . __('move to Trashing.........!', 'abp-transport-booking'),
						'restore' => $label . ' ' . __('Restoring.........!', 'abp-transport-booking'),
						'wc_install_active' => __('WooCommerce Downloading And Installing.........Please Wait...............!!', 'abp-transport-booking'),
						'wc_active' => __('WooCommerce  Installing.........Please Wait...............!!', 'abp-transport-booking'),
						'create_post_page' => __('Page Creating ........!', 'abp-transport-booking'),
						'no_item' => __('No More Item Found !', 'abp-transport-booking'),
						'no_item_selected' => __('No Item selected !', 'abp-transport-booking'),
					],
				]);
				wp_enqueue_style('abptb_admin', ABPTB_URL . 'assets/css/abptb_admin.css', array(), time());
				wp_enqueue_script('abptb_sp', ABPTB_URL . 'assets/js/abptb_sp.js', array('jquery'), time(), true);
				wp_localize_script('abptb_sp', 'abptb_sp_config', [
					'seat_type' => wp_json_encode(ABPTB_Seat_Plan::get_ticket_type_js()),
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('abptb_admin_ajax_nonce'),
					'decor_item' => wp_json_encode(ABPTB_Seat_Plan::get_decor_js()),
					'msg' => [
						'sp_delete_confirm' => __('Are you sure you want to delete this Seat Plan?', 'abp-transport-booking'),
						'sp_clear_confirm' => __('Are you absolutely sure you want to clear the full layout?', 'abp-transport-booking'),
						'sp_clear' => __('Layout clear successfully.............!', 'abp-transport-booking'),
						'sp_deleting' => __('Seat Plan Deleting.............!', 'abp-transport-booking'),
						'sp_saving' => __('Seat Plan Saving.............!', 'abp-transport-booking'),
						'sp_loading' => __('Seat Plan Loading.............!', 'abp-transport-booking'),
					],
				]);
				//=============================//
				do_action('abptb_admin_enqueue');
			}
			public function frontend_enqueue(): void {
				if (in_array('woocommerce/woocommerce.php', get_option('active_plugins'))) {
					wp_enqueue_script('wc-checkout');
					wp_enqueue_style('select2');
					wp_enqueue_script('select2');
				}
				wp_enqueue_script('abptb_frontend', ABPTB_URL . 'assets/js/abptb_frontend.js', array('jquery'), time(), true);
				wp_enqueue_script('abptb_slick', ABPTB_URL . 'assets/js/slick.min.js', array('jquery'), ABPTB_VERSION, true);
				$this->global_enqueue();
				do_action('abptb_frontend_enqueue');
			}
			public function global_enqueue(): void {
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_style('abptb_jquery_ui', ABPTB_URL . 'assets/css/jquery-ui.min.css', array(), '1.13.2');
				wp_enqueue_style('abptb_font_awesome', ABPTB_URL . 'assets/css/font_awesome.min.css', array(), '5.15.4');
				wp_enqueue_style('abptb_lib', ABPTB_URL . 'assets/css/abptb_lib.css', array(), time());
				wp_enqueue_script('abptb_lib', ABPTB_URL . 'assets/js/abptb_lib.js', array('jquery'), time(), true);
				if (in_array('woocommerce/woocommerce.php', get_option('active_plugins'))) {
					wp_localize_script('abptb_lib', 'abptb_var', [
						'currency_symbol' => get_woocommerce_currency_symbol(),
						'currency_position' => get_option('woocommerce_currency_pos'),
						'currency_decimal' => wc_get_price_decimal_separator(),
						'thousands_separator' => wc_get_price_thousand_separator(),
						'decimal_num' => ABPTB_Function::get_option('woocommerce_price_num_decimals', 2),
						'currency_suffix' => ABPTB_Function::get_option('woocommerce_price_display_suffix', ''),
						'blank_image' => ABPTB_BLANK_IMG_URL,
						'date_format' => ABPTB_JS_Date_Format,
					]);
				} else {
					wp_localize_script('abptb_lib', 'abptb_var', [
						'currency_symbol' => '',
						'currency_position' => '',
						'currency_decimal' => '',
						'thousands_separator' => '',
						'decimal_num' => '',
						'wc_suffix' => '',
						'blank_image' => ABPTB_BLANK_IMG_URL,
						'date_format' => ABPTB_JS_Date_Format,
					]);
				}
				$colors = ABPTB_Function::get_option('abptb_color');
				//echo '<pre>';                print_r($colors);                echo '</pre>';
				$available = !empty($colors['available']) ? $colors['available'] : "#D4EDDA";
				$sold = !empty($colors['sold']) ? $colors['sold'] : "#F8D7DA";
				$booked = !empty($colors['booked']) ? $colors['booked'] : "#6C757D";
				$selected = !empty($colors['selected']) ? $colors['selected'] : "#007BFF";
				$abptb_css_var = ABPTB_Function::get_option('abptb_css_var');
				$default_color = ($abptb_css_var['color_default'] ?? null) ?: '#303030';
				$color_theme = ($abptb_css_var['color_theme'] ?? null) ?: '#95951c';
				$alternate_color = ($abptb_css_var['color_theme_alternate'] ?? null) ?: '#fff';
				$color_warning = ($abptb_css_var['color_warning'] ?? null) ?: '#E67C30';
				$bg_section = ($abptb_css_var['bg_section'] ?? null) ?: '#FAFCFE';
				$bg_button = ($abptb_css_var['bg_button'] ?? null) ?: '#222';
				$color_button = ($abptb_css_var['color_button'] ?? null) ?: $alternate_color;
				$color_theme_ee = $color_theme . 'ee';
				$color_theme_cc = $color_theme . 'cc';
				$color_theme_aa = $color_theme . 'aa';
				$color_theme_88 = $color_theme . '88';
				$color_theme_77 = $color_theme . '77';
				$default_br = !empty($abptb_css_var['br_default']) ? $abptb_css_var['br_default'] . 'px' : '5px';
				$br_xl = !empty($abptb_css_var['br_default']) ? $abptb_css_var['br_default'] * 2 . 'px' : '10px';
				$fs_h1 = !empty($abptb_css_var['fs_h1']) ? $abptb_css_var['fs_h1'] . 'px' : '30px';
				$fs_h2 = !empty($abptb_css_var['fs_h2']) ? $abptb_css_var['fs_h2'] . 'px' : '26px';
				$fs_h3 = !empty($abptb_css_var['fs_h3']) ? $abptb_css_var['fs_h3'] . 'px' : '24px';
				$fs_h4 = !empty($abptb_css_var['fs_h4']) ? $abptb_css_var['fs_h4'] . 'px' : '20px';
				$fs_h5 = !empty($abptb_css_var['fs_h5']) ? $abptb_css_var['fs_h5'] . 'px' : '17px';
				$fs_h6 = !empty($abptb_css_var['fs_h6']) ? $abptb_css_var['fs_h6'] . 'px' : '15px';
				$fs_label = !empty($abptb_css_var['fs_label']) ? $abptb_css_var['fs_label'] . 'px' : '14px';
				$default_fs = !empty($abptb_css_var['fs_default']) ? $abptb_css_var['fs_default'] . 'px' : '12px';
				$button_fs = !empty($abptb_css_var['fs_button']) ? $abptb_css_var['fs_button'] . 'px' : '14px';
				$off = esc_html__('OFF', 'abp-transport-booking');
				$on = esc_html__('ON', 'abp-transport-booking');
				$abptb_var =
					":root {
						--tb_br: {$default_br};						
						--tb_br_xl: {$br_xl};						
						--tb_text_off:'{$off}';
						--tb_text_on: '{$on}';
						--tb_fs: {$default_fs};				
						--tb_fs_label: {$fs_label};
						--tb_fs_h6: {$fs_h6};
						--tb_fs_h5: {$fs_h5};
						--tb_fs_h4: {$fs_h4};
						--tb_fs_h3: {$fs_h3};
						--tb_fs_h2: {$fs_h2};
						--tb_fs_h1: {$fs_h1};						
						--tb_button_bg: {$bg_button};
						--tb_button_color: {$color_button};
						--tb_button_fs: {$button_fs};						
						--tb_color_default: {$default_color};						
						--tb_color_section: {$bg_section};
						--tb_color_theme: {$color_theme};
						--tb_color_theme_ee: {$color_theme_ee};
						--tb_color_theme_cc: {$color_theme_cc};
						--tb_color_theme_aa: {$color_theme_aa};
						--tb_color_theme_88: {$color_theme_88};
						--tb_color_theme_77: {$color_theme_77};
						--tb_color_theme_alter: {$alternate_color};
						--tb_color_warning:{$color_warning};						
						--tb_color_available:{$available};						
						--tb_color_sold:{$sold};						
						--tb_color_booked:{$booked};						
						--tb_color_seclected:{$selected};						
					}";
				wp_add_inline_style('abptb_lib', wp_kses_post($abptb_var));
				wp_enqueue_style('abptb', ABPTB_URL . 'assets/css/abptb.css', array(), time());
				wp_enqueue_script('abptb_infos', ABPTB_URL . 'assets/js/abptb.js', array('jquery'), time(), true);
				$rental_data = array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('abptb_ajax_nonce'),
					'route_info' => wp_json_encode(ABPTB_Function::get_route_info()),
					'location_info' => wp_json_encode(ABPTB_Function::location_info_js()),
					'now' => current_time('Y-m-d H:i'),
					'msg' => [
						'date_loading' => __('Date  Loading.............', 'abp-transport-booking'),
						'end_date_loading' => __('Return Date  Loading.............', 'abp-transport-booking'),
						'bp_select' => __('Please select boarding point......!', 'abp-transport-booking'),
						'dp_select' => __('Please select dropping point......!', 'abp-transport-booking'),
						'select_post' => __('Please Select', 'abp-transport-booking') . ' ' . ABPTB_Function::label(),
						'select_journey_date' => __('Please Select Journey Date', 'abp-transport-booking'),
						'select_journey_time' => __('Please Select Journey Time', 'abp-transport-booking'),
						'free' => __('FREE', 'abp-transport-booking'),
						'loading' => __('Loading..............!', 'abp-transport-booking'),
					],
				);
				wp_localize_script('abptb_infos', 'abptb_infos', $rental_data);
				do_action('abptb_global_script');
			}
			private function load_file(): void {
				require_once ABPTB_DIR . 'includes/abptb_function.php';
				require_once ABPTB_DIR . 'includes/abptb_query.php';
				require_once ABPTB_DIR . 'includes/abptb_layout.php';
				if (is_admin()) {
					require_once ABPTB_DIR . 'admin/abptb_admin.php';
					require_once ABPTB_DIR . 'admin/abptb_post.php';
					require_once ABPTB_DIR . 'admin/abptb_routing.php';
					require_once ABPTB_DIR . 'admin/abptb_ticket.php';
					require_once ABPTB_DIR . 'admin/abptb_price.php';
					require_once ABPTB_DIR . 'admin/abptb_dashboard.php';
					require_once ABPTB_DIR . 'admin/abptb_orders.php';
					require_once ABPTB_DIR . 'admin/abptb_dates.php';
					require_once ABPTB_DIR . 'admin/abptb_additional.php';
					require_once ABPTB_DIR . 'admin/abptb_form.php';
					require_once ABPTB_DIR . 'admin/abptb_seat_plan.php';
					require_once ABPTB_DIR . 'admin/abptb_resource.php';
					require_once ABPTB_DIR . 'admin/abptb_configuration.php';
					require_once ABPTB_DIR . 'admin/abptb_status.php';
					require_once ABPTB_DIR . 'admin/abptb_category.php';
					require_once ABPTB_DIR . 'admin/abptb_organizer.php';
					require_once ABPTB_DIR . 'admin/abptb_location.php';
					require_once ABPTB_DIR . 'admin/abptb_brand.php';
					require_once ABPTB_DIR . 'admin/abptb_feature.php';
				}
				if (in_array('woocommerce/woocommerce.php', get_option('active_plugins'))) {
					require_once ABPTB_DIR . 'includes/abptb_hooks.php';
					require_once ABPTB_DIR . 'includes/abptb_ajax.php';
					require_once ABPTB_DIR . 'includes/abptb_frontend.php';
					require_once ABPTB_DIR . 'includes/abptb_shortcodes.php';
					require_once ABPTB_DIR . 'includes/abptb_woocommerce.php';
					require_once ABPTB_DIR . 'admin/abptb_hidden_post.php';
				}
			}
			public function register_cpt(): void {
				$cpt = ABPTB_Function::get_cpt();
				$label = ABPTB_Function::label();
				register_post_type($cpt, [
					'public' => true,
					'labels' => [
						'name' => esc_html($label),
						'singular_name' => esc_html($label),
						'menu_name' => esc_html($label),
						'name_admin_bar' => esc_html($label),
						'archives' => __('Post List', 'abp-transport-booking'),
						'attributes' => __('Post List', 'abp-transport-booking'),
						'parent_item_colon' => __('Post Item:', 'abp-transport-booking'),
						'all_items' => __('Post', 'abp-transport-booking'),
						'add_new_item' => __('Add Post', 'abp-transport-booking'),
						'add_new' => __('Add Post', 'abp-transport-booking'),
						'new_item' => __('Add Post', 'abp-transport-booking'),
						'edit_item' => __('Edit Post', 'abp-transport-booking'),
						'update_item' => __('Update Post', 'abp-transport-booking'),
						'view_item' => __('View Post', 'abp-transport-booking'),
						'view_items' => __('View Post', 'abp-transport-booking'),
						'search_items' => __('Search Post', 'abp-transport-booking'),
						'not_found' => __('Post Not Found', 'abp-transport-booking'),
						'not_found_in_trash' => __('Post Not found in Trash', 'abp-transport-booking'),
						'featured_image' => __('Post Image', 'abp-transport-booking'),
						'set_featured_image' => __('Post Image', 'abp-transport-booking'),
						'remove_featured_image' => __('Remove Post Image', 'abp-transport-booking'),
						'use_featured_image' => __('Use image Post as featured image', 'abp-transport-booking'),
						'insert_into_item' => __('Insert  Post', 'abp-transport-booking'),
						'uploaded_to_this_item' => __('Uploaded  Post', 'abp-transport-booking'),
						'items_list' => __('Post List', 'abp-transport-booking'),
						'items_list_navigation' => __('Category list navigation', 'abp-transport-booking'),
						'filter_items_list' => __('Filter Post List', 'abp-transport-booking')
					],
					'menu_icon' => ABPTB_Function::icon_wp(),
					'supports' => ['title', 'editor', 'thumbnail'],
					'rewrite' => ['slug' => ABPTB_Function::slug(), 'with_front' => true, 'pages' => true, 'feeds' => true,],
					'show_in_rest' => true,
					'rest_base' => 'abptb_post',
					'capability_type' => 'post',
					'publicly_queryable' => true,  // you should be able to query it
					'show_ui' => true,  // you should be able to edit it in wp-admin
					'show_in_menu' => false,
					'exclude_from_search' => true,  // you should exclude it from search results
					'show_in_nav_menus' => true,  // you should be able to add it to menus
					'has_archive' => true,  // it should have archive page
				]);
				register_taxonomy('abptb_location', $cpt, [
					'hierarchical' => true,
					"public" => true,
					'labels' => [
						'name' => $label . ' ' . ABPTB_Function::location_label(),
						'singular_name' => $label . ' ' . ABPTB_Function::location_label(),
					],
					'show_ui' => true,
					'show_admin_column' => false,
					'show_in_menu' => false,
					'query_var' => true,
					'rewrite' => ['slug' => ABPTB_Function::location_slug()],
					'show_in_rest' => true,
					'rest_base' => 'abptb_location',
					'meta_box_cb' => false,
				]);
				if (ABPTB_Function::on_off('category')) {
					register_taxonomy('abptb_category', $cpt, [
						'hierarchical' => true,
						"public" => true,
						'labels' => [
							'name' => $label . ' ' . ABPTB_Function::category_label(),
							'singular_name' => $label . ' ' . ABPTB_Function::category_label(),
						],
						'show_ui' => true,
						'show_admin_column' => false,
						'show_in_menu' => false,
						'query_var' => true,
						'rewrite' => ['slug' => ABPTB_Function::category_slug()],
						'show_in_rest' => true,
						'rest_base' => 'abptb_category',
						'meta_box_cb' => false,
					]);
				}
				if (ABPTB_Function::on_off('organizer')) {
					register_taxonomy('abptb_organizer', $cpt, [
						'hierarchical' => true,
						"public" => true,
						'labels' => [
							'name' => $label . ' ' . ABPTB_Function::organizer_label(),
							'singular_name' => $label . ' ' . ABPTB_Function::organizer_label(),
						],
						'show_ui' => true,
						'show_admin_column' => false,
						'show_in_menu' => false,
						'query_var' => true,
						'rewrite' => ['slug' => ABPTB_Function::organizer_slug()],
						'show_in_rest' => true,
						'rest_base' => 'abptb_organizer',
						'meta_box_cb' => false,
					]);
				}
				if (ABPTB_Function::on_off('brand')) {
					register_taxonomy('abptb_brand', $cpt, [
						'hierarchical' => true,
						"public" => true,
						'labels' => [
							'name' => $label . ' ' . ABPTB_Function::brand_label(),
							'singular_name' => $label . ' ' . ABPTB_Function::brand_label(),
						],
						'show_ui' => true,
						'show_admin_column' => false,
						'show_in_menu' => false,
						'query_var' => true,
						'rewrite' => ['slug' => ABPTB_Function::brand_slug()],
						'show_in_rest' => true,
						'rest_base' => 'abptb_brand',
						'meta_box_cb' => false,
					]);
				}
				flush_rewrite_rules();
			}
			public static function activation(): void {
				self::create_table();
				flush_rewrite_rules();
			}
			public static function deactivate(): void {
				flush_rewrite_rules();
			}
			public static function create_table(): void {
				global $wpdb;
				$order_table = $wpdb->prefix . 'abptb_orders';
				$sp_table = $wpdb->prefix . 'abptb_sp';
				$collate = $wpdb->get_charset_collate();
				$abptb_orders = "CREATE TABLE $order_table (
					        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					        order_id bigint(20) unsigned NOT NULL,
					        item_id bigint(20) unsigned NOT NULL,
					        post_id bigint(20) unsigned NOT NULL,
					        user_id bigint(20) unsigned NOT NULL,
					        start_point bigint(20) DEFAULT NULL,
					        start_time datetime DEFAULT NULL,
					        bp_dp varchar(100) DEFAULT NULL,
					        bp bigint(20) DEFAULT NULL,
					        dp bigint(20) DEFAULT NULL,
					        bp_time datetime DEFAULT NULL,
					        dp_time datetime DEFAULT NULL,        
					        pick_up varchar(100) DEFAULT NULL,
					        pick_up_time datetime DEFAULT NULL,
					        drop_off varchar(100) DEFAULT NULL,      
					        drop_off_time datetime DEFAULT NULL, 
					        ticket_info text NOT NULL,
					        ticket_id varchar(255) NOT NULL,
					        sp_id bigint(20) NOT NULL,
					        qty int(5) NOT NULL DEFAULT 1,
					        price varchar(100) DEFAULT NULL,					        
					        ex_info text NOT NULL,				        					        
					        ex_id varchar(255) NOT NULL,
					        ex_price varchar(100) DEFAULT NULL,
					        total varchar(100) DEFAULT NULL,					        
					        pass_info text NOT NULL,					        
					        checkin tinyint(1) NOT NULL DEFAULT 0,					        
					        female tinyint(1) NOT NULL DEFAULT 0,					        
					        book_type int(5) NOT NULL DEFAULT 0,
					        order_status varchar(20) NOT NULL,
					        payment_method varchar(100) DEFAULT NULL,
					        billing_name varchar(100) DEFAULT NULL,
					        billing_email varchar(100) DEFAULT NULL,
					        billing_phone varchar(20) DEFAULT NULL,
					        billing_address varchar(255) DEFAULT NULL,
					        others text DEFAULT NULL,
					        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
					        updated_at datetime DEFAULT NULL,
					        PRIMARY KEY  (id),
					        KEY order_id  (order_id),
					        KEY user_id  (user_id),
					        KEY item_id  (item_id)
					    ) $collate;";
				// Seat Plan Table
				$sp = "CREATE TABLE $sp_table (
					        id mediumint unsigned NOT NULL AUTO_INCREMENT,
					        name varchar(100) DEFAULT NULL,
					        total_seats mediumint NOT NULL DEFAULT 0,
					        layout_data longtext DEFAULT NULL,
					        seat_info longtext DEFAULT NULL,
					        others longtext DEFAULT NULL,
					        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
					        updated_at datetime DEFAULT NULL,
					        PRIMARY KEY  (id)
					    ) $collate;";
				if (!function_exists('dbDelta')) {
					require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				}
				dbDelta($abptb_orders);
				dbDelta($sp);
			}
			public function plugin_settings_link($links_array, $plugin_file_name) {
				if (strpos($plugin_file_name, ABPTB_BASE)) {
					array_unshift($links_array, '<a class="_abp" href="' . esc_url(ABPTB_Function::build_url('configuration')) . '">' . __('Configuration', 'abp-transport-booking') . '</a>');
				}
				return $links_array;
			}
			public function flush_rewrite(): void {
				flush_rewrite_rules();
			}
			public function disable_gutenberg($current_status, $post_type) {
				if ($post_type === ABPTB_Function::get_cpt()) {
					return false;
				}
				return $current_status;
			}
			public function activation_redirect(): void {
				$active_tab = '';
				$page = '';
				if (isset($_GET['_abptb_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_abptb_nonce'])), 'abptb_url_action')) {
					$active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'status';
					$page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
				}
				if ($page === ABPTB_Function::slug() && ABPTB_WC < 2 && $active_tab != 'status') {
					wp_safe_redirect(ABPTB_Function::build_url('status'));
					exit;
				}
			}
		}
		new ABPTB_Dependencies();
	}