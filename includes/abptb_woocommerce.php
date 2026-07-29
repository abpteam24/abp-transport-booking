<?php
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	if (!class_exists('ABPTB_Woocommerce')) {
		class ABPTB_Woocommerce {
			public function __construct() {
				add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 90, 3);
				add_action('woocommerce_before_calculate_totals', array($this, 'before_calculate_totals'), 90);
				add_filter('woocommerce_cart_item_thumbnail', array($this, 'cart_item_thumbnail'), 90, 3);
				add_filter('woocommerce_get_item_data', array($this, 'get_item_data'), 90, 2);
				//=============================//
				add_action('woocommerce_after_checkout_validation', array($this, 'after_checkout_validation'));
				add_action('woocommerce_checkout_create_order_line_item', array($this, 'checkout_create_order_line_item'), 90, 4);
				add_action('woocommerce_checkout_order_processed', array($this, 'checkout_order_processed'));
				add_action('woocommerce_store_api_checkout_order_processed', array($this, 'api_checkout_order_processed'));
				add_filter('woocommerce_order_status_changed', array($this, 'order_status_changed'), 90, 4);
			}
			public function add_cart_item_data($cart_item, $product_id) {
				$linked_id = ABPTB_Function::get_post_info($product_id, 'abptb_link_id', $product_id);
				$post_id = is_string(get_post_status($linked_id)) ? $linked_id : $product_id;
				if (get_post_type($post_id) == ABPTB_Function::get_cpt() && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'abptb_registration_nonce')) {
					$post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
					$post_infos = ABPTB_Function::get_all_meta($post_id);
					$bp_dp = $post_val('bp_dp');
					$booking_infos = [];
					if (!empty($bp_dp)) {
						$route_info = self::get_booking_info($post_infos, $bp_dp);
						if (!empty($route_info)) {
							$booking_infos[$bp_dp] = $route_info;
						}
					}
					$return_bp_dp = $post_val('return_bp_dp');
					if (!empty($return_bp_dp)) {
						$route_info = self::get_booking_info($post_infos, $return_bp_dp, 'return_');
						if (!empty($route_info)) {
							$booking_infos[$return_bp_dp] = $route_info;
						}
					}
					$total_price = 0;
					if (!empty($booking_infos)) {
						foreach ($booking_infos as $booking_info) {
							$total_price += ($booking_info['total'] ?? 0);
						}
					}
					$cart_item['post_id'] = $post_id;
					$cart_item['booking_infos'] = $booking_infos;
					$cart_item['total_price'] = $total_price;
					$cart_item['line_total'] = $total_price;
					$cart_item['line_subtotal'] = $total_price;
					$cart_item = apply_filters('abptb_add_cart_item_data', $cart_item, $post_id);
					$_SESSION['abptb_cart_success'] = get_the_title($post_id) . ' ' . __('Add to cart successfully!', 'abp-transport-booking');
				}
				echo '<pre>';				print_r($cart_item);				echo '</pre>';				die();
				return $cart_item;
			}
			public function before_calculate_totals($cart_object): void {
				foreach ($cart_object->cart_contents as $value) {
					$post_id = $value['post_id'] ?? 0;
					if (get_post_type($post_id) == ABPTB_Function::get_cpt()) {
						$total_price = $value['total_price'] ?? 0;
						$value['data']->set_price($total_price);
						$value['data']->set_regular_price($total_price);
						$value['data']->set_sale_price($total_price);
						$value['data']->set_sold_individually('yes');
						$value['data']->get_price();
					}
				}
			}
			public function cart_item_thumbnail($thumbnail, $cart_item, $item_key) {
				$post_id = $cart_item['post_id'] ?? 0;
				if (get_post_type($post_id) == ABPTB_Function::get_cpt()) {
					$url = ABPTB_Function::get_image_url($post_id) ?: ABPTB_BLANK_IMG_URL;
					if (!empty($url)) {
						$thumbnail = '<div class="abptb_area"><img class="_img_control" src="' . $url . '" data-href="' . get_the_permalink($post_id) . '" alt="#"></div>';
					}
				}
				return $thumbnail;
			}
			public function get_item_data($item_data, $cart_item) {
				$post_id = $cart_item['post_id'] ?? 0;
				if (get_post_type($post_id) == ABPTB_Function::get_cpt()) {
					global $post;
					$is_block_cart = false;
					$is_block_checkout = false;
					if (is_a($post, 'WP_Post')) {
						$is_block_cart = has_block('woocommerce/cart', $post->ID);
						$is_block_checkout = has_block('woocommerce/checkout', $post->ID);
					}
					if (is_checkout() && $is_block_checkout) {
						$item_data = $this->display_cart_item_block($cart_item);
					} elseif (is_cart() && $is_block_cart) {
						$item_data = $this->display_cart_item_block($cart_item);
					} else {
						ob_start();
						do_action('abptb_display_cart_item', $cart_item);
						$content = ob_get_clean();
						if (!empty($content)) {
							$item_data[] = array(
								'name' => __('Booking Details', 'abp-transport-booking'),
								'value' => $content
							);
						}
					}
				}
				return $item_data;
			}
			public static function get_booking_info($post_infos = [], $bp_dp = '', $prefix = '') {
				$booking_info = [];
				if (isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'abptb_registration_nonce')) {
					$post_int_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('absint', wp_unslash($_POST[$key])) : [];
					$post_array = fn($key) => (isset($_POST[$key]) && is_array($_POST[$key])) ? array_map('sanitize_text_field', wp_unslash($_POST[$key])) : [];
					$post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
					$post_id = $post_infos['post_id'] ?? '';
					$journey_time = $post_val($prefix . 'journey_time');
					$start_time = $post_val($prefix . 'start_time');
					$ticket_types = $post_array($prefix . 'item_check');
					$item_qty = $post_int_array($prefix . 'item_qty');
					$ticket_price = 0;
					if (!empty($journey_time) && !empty($bp_dp) && !empty($ticket_types) && !empty($item_qty) && !empty($post_id) && !empty($start_time)) {
						if (sizeof($ticket_types) > 0) {
							foreach ($ticket_types as $key => $ticket_type) {
								$qty = absint($item_qty[$key] ?? '');
								if (!empty($ticket_type) && $qty > 0) {
									$price = ABPTB_Function::get_price($post_infos, $bp_dp, $ticket_type, $journey_time);
									$booking_info['info'][$ticket_type]['price'] = $price;
									$booking_info['info'][$ticket_type]['qty'] = $qty;
									$ticket_price = $ticket_price + $price * $qty;
								}
							}
							$additional_info = self::get_additional_info($post_infos, $prefix);
							$additional_price = self::get_additional_price($additional_info);
							$booking_info['journey_time'] = $journey_time;
							$booking_info['start_time'] = $start_time;
							$booking_info['pass_info'] = self::get_passenger_info($post_infos, $prefix);
							$booking_info['additional_info'] = $additional_info;
							$booking_info['price'] = $ticket_price;
							$booking_info['ex_price'] = $additional_price;
							$booking_info['total'] = $ticket_price + $additional_price;
						}
					}
				}
				return apply_filters('abptb_cart_booking_info_filter', $booking_info);
			}
			public static function get_additional_price($services) {
				$price = 0;
				if (is_array($services) && sizeof($services) > 0) {
					foreach ($services as $service) {
						$qty = $service['qty'] ?? '';
						if (!empty($qty) && $qty > 0) {
							$ticket_price = $service['price'] ?? 0;
							$price = $price + $ticket_price * $qty;
						}
					}
				}
				return $price;
			}
			public static function get_additional_info($post_infos = [], $prefix = ''): array {
				$infos = array();
				if (isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'abptb_registration_nonce')) {
					$services = ABPTB_Function::additional_data($post_infos);
					if (!empty($services) && is_array($services)) {
						foreach ($services as $id => $service) {
							$name = isset($_POST[$prefix . 'name_' . $id]) ? sanitize_text_field(wp_unslash($_POST[$prefix . 'name_' . $id])) : '';
							$quantity = isset($_POST[$prefix . 'qty_' . $id]) ? sanitize_text_field(wp_unslash($_POST[$prefix . 'qty_' . $id])) : '';
							if (!empty($name) && !empty($quantity) && $quantity > 0 && !empty($id)) {
								$infos[$id]['name'] = $name;
								$infos[$id]['qty'] = $quantity;
								$infos[$id]['price'] = ABPTB_Function::get_additional_price($post_infos, $id);
								$infos[$id]['icon'] = $service['icon'] ?? '';
								$infos[$id]['returnable'] = $service['returnable'] ?? 'no';
							}
						}
					}
				}
				return $infos;
			}
			public static function get_passenger_info($post_infos = [], $prefix = ''): array {
				$pass_info = [];
				if (ABPTB_Function::on_off('client_info') && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'abptb_registration_nonce')) {
					$forms = ABPTB_Function::client_data($post_infos);
					if (!empty($forms) && is_array($forms)) {
						foreach ($forms as $id => $form) {
							$infos = isset($_POST[$prefix . $id]) ? array_map('sanitize_text_field', wp_unslash($_POST[$prefix . $id])) : [];
							if (!empty($infos)) {
								foreach ($infos as $key => $info) {
									if (!empty($info)) {
										$pass_info[$key][$id]['label'] = $form['label'] ?? '';
										$pass_info[$key][$id]['value'] = $info;
									}
								}
							}
						}
					}
				}
				return $pass_info;
			}
			public function display_cart_item_block($cart_item): array {
				$start_time = $cart_item['start_time'] ?? '';
				$end_time = $cart_item['end_time'] ?? '';
				$duration = $cart_item['duration'] ?? '';
				$location = $cart_item['location'] ?? '';
				$ticket_infos = $cart_item['ticket_info'] ?? [];
				$additional_info = $cart_item['additional_info'] ?? [];
				$attendee_infos = $cart_item['pass_info'] ?? [];
				$item_data = [];
				if (!empty($ticket_infos) && sizeof($ticket_infos) > 0) {
					$item_data[] = array('name' => __('Booking Information', 'abp-transport-booking'), 'value' => '<br />');
					$item_data[] = array('name' => __('Rent Start', 'abp-transport-booking'), 'value' => ABPTB_Function::date_format($start_time) . '<br />');
					$item_data[] = array('name' => __('Rent End', 'abp-transport-booking'), 'value' => ABPTB_Function::date_format($end_time) . '<br />');
					$item_data[] = array('name' => __('Duration', 'abp-transport-booking'), 'value' => $duration . '<br />');
					if (!empty($location)) {
						$item_data[] = array('name' => __('Location', 'abp-transport-booking'), 'value' => ABPTB_Function::location_value($location) . '<br />');
					}
					$item_data[] = array('name' => __('Property Information', 'abp-transport-booking'), 'value' => '<br />');
					foreach ($ticket_infos as $key => $ticket_info) {
						$item_data[] = array('name' => __('Name', 'abp-transport-booking'), 'value' => $ticket_info['name'] . '<br />');
						$item_data[] = array('name' => __('Quantity', 'abp-transport-booking'), 'value' => $ticket_info['qty'] . '<br />');
						$price = $ticket_info['price'] ?? 0;
						$price = $price > 0 ? wc_price($price) : __('FREE', 'abp-transport-booking');
						$item_data[] = array('name' => __('Rent', 'abp-transport-booking'), 'value' => $price . '<br />');
						$deposit = $ticket_info['deposit'] ?? '';
						if (ABPTB_Function::on_off('deposit') && !empty($deposit)) {
							$item_data[] = array('name' => __('Deposit', 'abp-transport-booking'), 'value' => wc_price($deposit) . '<br />');
						}
						$brand = $ticket_info['brand'] ?? '';
						if (!empty($brand) && ABPTB_Function::on_off('brand')) {
							$item_data[] = array('name' => ABPTB_Function::brand_label(), 'value' => ABPTB_Function::brand_value($brand) . '<br />');
						}
						$item_data = apply_filters('abptb_cart_property_info_block', $item_data, $cart_item, $key);
					}
					if (ABPTB_Function::on_off('additional_info') && !empty($additional_info) && sizeof($additional_info) > 0) {
						$item_data[] = array('name' => __('Additional Information', 'abp-transport-booking'), 'value' => '<br />');
						foreach ($additional_info as $additional) {
							if (is_array($additional)) {
								$qty = $additional['qty'] ?? 1;
								$price = $additional['price'] ?? 0;
								$price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-transport-booking');
								$ex_price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-transport-booking');
								$item_data[] = array('name' => $additional['name'] ?? '', 'value' => $price_text . ' X ' . $qty . '  = ' . $ex_price . '<br />');
							}
						}
					}
					if (ABPTB_Function::on_off('client_info') && !empty($attendee_infos) && sizeof($attendee_infos) > 0) {
						$item_data[] = array('name' => __('Client Information', 'abp-transport-booking'), 'value' => '<br />');
						foreach ($attendee_infos as $attendee_info) {
							$label = $attendee_info['label'] ?? '';
							$value = $attendee_info['value'] ?? '';
							if ($label && $value) {
								$item_data[] = array('name' => $label, 'value' => $value . '<br />');
							}
						}
					}
				}
				return $item_data;
			}
			//=============================//
			public function after_checkout_validation(): void {
				global $woocommerce;
				$cart_items = $woocommerce->cart->get_cart();
				foreach ($cart_items as $cart_item) {
					$post_id = $cart_item['post_id'] ?? 0;
					if (get_post_type($post_id) == ABPTB_Function::get_cpt()) {
						$location = $cart_item['location'] ?? '';
						$rent_rule = $cart_item['rent_rule'] ?? '';
						$post_infos['post_id'] = $cart_item['post_id'] ?? '';
						$post_infos['rent_rule'] = $rent_rule;
						$post_infos['start_time'] = $cart_item['start_time'] ?? '';
						$post_infos['end_time'] = $cart_item['end_time'] ?? '';
						$post_infos['location'] = $location;
						$ticket_infos = $cart_item['ticket_info'] ?? [];
						if (sizeof($ticket_infos) > 0 && ABPTB_Function::check_date_exit($post_infos)) {
							foreach ($ticket_infos as $id => $ticket_info) {
								$qty = $ticket_info['qty'] ?? '';
								if (!empty($qty) && $qty > 0) {
									$post_infos['property_id'] = $id;
									$sold_qty = ABPTB_Query::get_sold_qty($post_infos);
									//$property                   = current( ABPTB_Query::get_property( [ 'property_id' => $id ] ) );
									$price_qty_info = json_decode($property['price_qty_info'] ?? '', true) ?: [];
									$price_qty_info = (!empty($location) && isset($price_qty_info[$location])) ? $price_qty_info[$location] : $price_qty_info;
									$price_info = $price_qty_info[$rent_rule] ?? [];
									$total_qty = $price_info['qty'] ?? 0;
									$reserve_qty = $price_info['reserve'] ?? 0;
									$min_qty = (($price_info['min_qty'] ?? 0) > 0) ? $price_info['min_qty'] : 1;
									$max_qty = (($price_info['max_qty'] ?? 0) > 0) ? $price_info['max_qty'] : 0;
									$available_qty = $total_qty - $reserve_qty - $sold_qty;
									$available_qty = $max_qty > 0 ? min($max_qty, $available_qty) : $available_qty;
									if ($qty < $min_qty || $qty > $available_qty) {
										$woocommerce->cart->empty_cart();
										wc_add_notice(__("Oh ! We are Sorry, Your Selected Item Already Booked by another . please Try another Item.", 'abp-transport-booking'), 'error');
									}
								}
							}
						} else {
							$woocommerce->cart->empty_cart();
							wc_add_notice(__("Oh ! We are Sorry, Something Wrong. please Try another Time.", 'abp-transport-booking'), 'error');
						}
					}
				}
			}
			public function checkout_create_order_line_item($item, $key, $cart_item): void {
				$post_id = $cart_item['post_id'] ?? 0;
				if (get_post_type($post_id) == ABPTB_Function::get_cpt()) {
					$rent_rule = $cart_item['rent_rule'] ?? '';
					$start_time = $cart_item['start_time'] ?? '';
					$end_time = $cart_item['end_time'] ?? '';
					$book_from = ($rent_rule == 'daily' || $rent_rule == 'monthly') ? $start_time : ABPTB_Function::booking_buffer($start_time);
					$book_to = ($rent_rule == 'daily' || $rent_rule == 'monthly') ? $end_time : ABPTB_Function::booking_buffer($end_time, true);
					$start_time = $cart_item['start_time'] ?? '';
					$end_time = $cart_item['end_time'] ?? '';
					$duration = $cart_item['duration'] ?? '';
					$location = $cart_item['location'] ?? '';
					$ticket_infos = $cart_item['ticket_info'] ?? [];
					$additional_infos = $cart_item['additional_info'] ?? [];
					$attendee_infos = $cart_item['pass_info'] ?? [];
					if (!empty($ticket_infos) && sizeof($ticket_infos) > 0) {
						$item->add_meta_data(__('Booking Information', 'abp-transport-booking'), '');
						$item->add_meta_data(__('Rent Start', 'abp-transport-booking'), ABPTB_Function::date_format($start_time));
						$item->add_meta_data(__('Rent End', 'abp-transport-booking'), ABPTB_Function::date_format($end_time));
						$item->add_meta_data(__('Duration', 'abp-transport-booking'), $duration);
						if (!empty($location)) {
							$item->add_meta_data(ABPTB_Function::location_label(), ABPTB_Function::location_value($location));
						}
						$item->add_meta_data(__('Property Information', 'abp-transport-booking'), '');
						$all_brand = '';
						foreach ($ticket_infos as $ticket_info) {
							$item->add_meta_data(__('Property Name', 'abp-transport-booking'), $ticket_info['name']);
							$item->add_meta_data(__('Quantity', 'abp-transport-booking'), $ticket_info['qty']);
							$price = $ticket_info['price'] ?? 0;
							$price = $price > 0 ? wc_price($price) : __('FREE', 'abp-transport-booking');
							$item->add_meta_data(__('Rent', 'abp-transport-booking'), $price);
							$deposit = $ticket_info['deposit'] ?? '';
							if (ABPTB_Function::on_off('deposit') && !empty($deposit)) {
								$item->add_meta_data(__('Deposit', 'abp-transport-booking'), wc_price($deposit));
							}
							$brand = $ticket_info['brand'] ?? '';
							$all_brand = !empty($all_brand) ? $all_brand . ',' . $brand : $brand;
							if (!empty($brand) && ABPTB_Function::on_off('brand')) {
								$item->add_meta_data(ABPTB_Function::brand_label(), ABPTB_Function::brand_value($brand));
							}
						}
						if (ABPTB_Function::on_off('additional_info') && !empty($additional_infos) && sizeof($additional_infos) > 0) {
							$item->add_meta_data(__('Additional Information', 'abp-transport-booking'), '');
							foreach ($additional_infos as $additional) {
								$name = $additional['name'] ?? '';
								$qty = $additional['qty'] ?? 1;
								$price = $additional['price'] ?? 0;
								$price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-transport-booking');
								if (!empty($name) && $qty > 0) {
									$ex_price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-transport-booking');
									$item->add_meta_data($name, '  ( ' . $price_text . ' X ' . $qty . ') = ' . $ex_price);
								}
							}
						}
						if (ABPTB_Function::on_off('client_info') && !empty($attendee_infos) && sizeof($attendee_infos) > 0) {
							$item->add_meta_data(__('Client Information', 'abp-transport-booking'), '');
							foreach ($attendee_infos as $attendee_info) {
								$label = $attendee_info['label'] ?? '';
								$value = $attendee_info['value'] ?? '';
								if (!empty($label) && !empty($value)) {
									$item->add_meta_data($label, $value);
								}
							}
						}
						//=============================//
						$item_info = [
							'post_id' => $post_id,
							'user_id' => get_current_user_id(),
							'start_time' => $start_time,
							'end_time' => $end_time,
							'book_from' => $book_from,
							'book_to' => $book_to,
							'duration' => $duration,
							'location' => $location,
							'brand' => $all_brand,
							'rent_rule' => $rent_rule,
							'ticket_info' => $ticket_infos,
							'additional_info' => $additional_infos,
							'pass_info' => $attendee_infos,
							'rent' => $cart_item['rent'] ?? '',
							'ex_price' => $cart_item['ex_price'] ?? '',
							'deposit' => $cart_item['deposit'] ?? '',
							'item_total' => $cart_item['total_price'] ?? '',
						];
						$item_info = apply_filters('abptb_checkout_create_order_line_item', $item_info, $cart_item);
						$item->add_meta_data('_abptb_items', $item_info, true);
					}
				}
			}
			public static function save_custom_data($order_id): void {
				if ($order_id) {
					$order = wc_get_order($order_id);
					$order_status = $order->get_status();
					$order_meta = get_post_meta($order_id);
					$payment_method = $order_meta['_payment_method_title'][0] ?? '';
					$user_id = $order_meta['_customer_user'][0] ?? '';
					$_billing_first_name = $order_meta['_billing_first_name'][0] ?? '';
					$_billing_last_name = $order_meta['_billing_last_name'][0] ?? '';
					$billing_email = $order_meta['_billing_email'][0] ?? '';
					$billing_phone = $order_meta['_billing_phone'][0] ?? '';
					$_billing_address_1 = $order_meta['_billing_address_1'][0] ?? '';
					$_billing_address_2 = $order_meta['_billing_address_2'][0] ?? '';
					$billing_name = $_billing_first_name . ' ' . $_billing_last_name;
					$billing_address = $_billing_address_1 . ' ' . $_billing_address_2;
					$booked_status = ABPTB_Function::booking_status();
					$booked_status = $booked_status ? explode(',', $booked_status) : [];
					if ($order_status != 'failed') {
						$total_order = ABPTB_Query::get_booking_query(['order_id' => $order_id], 0, 0, true);
						if ($total_order == 0) {
							foreach ($order->get_items() as $item_id => $item) {
								$item_info = wc_get_order_item_meta($item_id, '_abptb_items');
								if (!empty($item_info) && is_array($item_info) && sizeof($item_info) > 0) {
									$post_id = $item_info['post_id'] ?? '';
									if (!empty($post_id) && get_post_type($post_id) == ABPTB_Function::get_cpt()) {
										$ticket_infos = $item_info['ticket_info'] ?? [];
										$start_time = $item_info['start_time'] ?? '';
										$end_time = $item_info['end_time'] ?? '';
										$book_from = $item_info['book_from'] ?? '';
										$book_to = $item_info['book_to'] ?? '';
										$additional_info = $item_info['additional_info'] ?? '';
										global $wpdb;
										$table_name = $wpdb->prefix . 'abptb_orders';
										if (!empty($ticket_infos) && sizeof($ticket_infos) > 0) {
											$property_id = $ex_id = [];
											foreach ($ticket_infos as $key => $ticket_info) {
												$property_id[] = $key;
											}
											if (!empty($additional_info) && sizeof($additional_info) > 0) {
												foreach ($additional_info as $key => $additional) {
													$ex_id[] = $key;
												}
											}
											$price_info['rent'] = $item_info['rent'] ?? '';
											$price_info['ex_price'] = $item_info['ex_price'] ?? '';
											$price_info['deposit'] = $item_info['deposit'] ?? '';
											$price_info['item_total'] = $item_info['item_total'] ?? '';
											$others['rent_rule'] = $item_info['rent_rule'] ?? '';
											$others['duration'] = $item_info['duration'] ?? '';
											$_order_status = 'wc-' . $order_status;
											$data = [
												'order_id' => intval($order_id),
												'item_id' => intval($item_id),
												'post_id' => intval($post_id),
												'user_id' => intval($user_id),
												'property_id' => wp_json_encode($property_id),
												'ex_id' => wp_json_encode($ex_id),
												'pick_up' => sanitize_text_field($item_info['pick_up'] ?? ''),
												'start_time' => sanitize_text_field($start_time),
												'drop_off' => sanitize_text_field($item_info['drop_off'] ?? ''),
												'end_time' => sanitize_text_field($end_time),
												'book_from' => sanitize_text_field($book_from),
												'book_to' => sanitize_text_field($book_to),
												'category' => sanitize_text_field(get_post_meta($post_id, 'category', true)),
												'location' => sanitize_text_field($item_info['location'] ?? ''),
												'brand' => sanitize_text_field($item_info['brand'] ?? ''),
												'price_info' => wp_json_encode($price_info),
												'property_info' => wp_json_encode($ticket_infos),
												'ex_info' => wp_json_encode($additional_info),
												'pass_info' => wp_json_encode($item_info['pass_info'] ?? []),
												'delivery_option' => 0,
												'book_status' => in_array($_order_status, $booked_status) ? 1 : 0,
												'order_status' => sanitize_text_field($_order_status),
												'payment_method' => sanitize_text_field($payment_method),
												'billing_name' => sanitize_text_field($billing_name),
												'billing_email' => sanitize_text_field($billing_email),
												'billing_phone' => sanitize_text_field($billing_phone),
												'billing_address' => sanitize_text_field($billing_address),
												'others' => wp_json_encode($others),
												'created_at' => current_time('Y-m-d H:i'),
												'updated_at' => current_time('Y-m-d H:i')
											];
											// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
											$wpdb->insert($table_name, $data);
										}
									}
								}
							}
						}
					}
				}
			}
			public function checkout_order_processed($order_id): void {
				self::save_custom_data($order_id);
			}
			public function api_checkout_order_processed($order): void {
				$this->checkout_order_processed($order->get_id());
			}
			public function order_status_changed($order_id): void {
				if (!empty($order_id) && $order_id > 0) {
					global $wpdb;
					$table_name = $wpdb->prefix . 'abptb_orders';
					$order = wc_get_order($order_id);
					$order_status = $order->get_status();
					foreach ($order->get_items() as $item_id => $item_values) {
						if ($item_id) {
							$order_infos = ABPTB_Query::get_booking_query(['item_id' => $item_id]);
							if (!empty($order_infos) && sizeof($order_infos) > 0) {
								$order_info = current($order_infos);
								$others = $order_info['others'] ?? '';
								if (!empty($others)) {
									$others = json_decode($others, true);
									$user_id = get_current_user_id();
									$others['updated_by'] = $user_id;
									$data = [
										'others' => wp_json_encode($others),
										'order_status' => 'wc-' . $order_status,
										'updated_at' => current_time('Y-m-d H:i')
									];
									$where = ['item_id' => $item_id];
									// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
									$wpdb->update($table_name, $data, $where, ['%s', '%s', '%s'], ['%d']);
									$mail_send = apply_filters('abptb_send_mail', false, $item_id);
								}
							}
						}
					}
				}
			}
		}
		new ABPTB_Woocommerce();
	}
