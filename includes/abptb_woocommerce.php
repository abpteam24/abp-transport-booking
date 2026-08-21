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
				add_action('woocommerce_checkout_create_order_line_item', array($this, 'checkout_create_order_line_item'), 90, 4);
				add_action('woocommerce_checkout_order_processed', array($this, 'checkout_order_processed'));
				add_action('woocommerce_store_api_checkout_order_processed', array($this, 'api_checkout_order_processed'));
				add_filter('woocommerce_order_status_changed', array($this, 'order_status_changed'), 90, 4);
				add_action('woocommerce_checkout_process', [$this, 'checkout_process_validation']);
				add_action('woocommerce_after_checkout_validation', [$this, 'checkout_process_validation']);
				add_action('woocommerce_check_cart_items', [$this, 'checkout_process_validation']);
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
				//echo '<pre>';				print_r($cart_item);				echo '</pre>';				die();
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
			public function cart_item_thumbnail($thumbnail, $cart_item) {
				$post_id = $cart_item['post_id'] ?? 0;
				if (get_post_type($post_id) == ABPTB_Function::get_cpt()) {
					$url = ABPTB_Function::get_image_url($post_id) ?: ABPTB_BLANK_IMG_URL;
					if (!empty($url)) {
						$thumbnail = '<div class="abptb_area"><img class="_img_control" src="' . $url . '" data-href="' . get_the_permalink($post_id) . '" alt="#"></div>';
					}
				}
				return $thumbnail;
			}
			public function get_item_data($item_data, $booking_items) {
				$post_id = $booking_items['post_id'] ?? 0;
				if (get_post_type($post_id) == ABPTB_Function::get_cpt()) {
					global $post;
					$is_block_cart = false;
					$is_block_checkout = false;
					if (is_a($post, 'WP_Post')) {
						$is_block_cart = has_block('woocommerce/cart', $post->ID);
						$is_block_checkout = has_block('woocommerce/checkout', $post->ID);
					}
					if (is_checkout() && $is_block_checkout) {
						$item_data = $this->display_cart_item_block($booking_items);
					} elseif (is_cart() && $is_block_cart) {
						$item_data = $this->display_cart_item_block($booking_items);
					} else {
						ob_start();
						do_action('abptb_display_cart_item', $booking_items);
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
					$post_int = fn($key, $default = '') => isset($_POST[$key]) ? absint($_POST[$key]) : $default;
					$post_val = fn($key, $default = '') => isset($_POST[$key]) ? sanitize_text_field(wp_unslash($_POST[$key])) : $default;
					$post_id = $post_infos['post_id'] ?? '';
					$seat_type = $post_infos['seat_type'] ?? 'sp';
					$seat_type = ABPTB_Function::on_off('sp') ? $seat_type : 'ticket';
					$bp_time = $post_val($prefix . 'bp_time');
					$start_time = $post_val($prefix . 'start_time');
					$start_point = ABPTB_Function::start_point($post_infos,$bp_dp);
					$end_point = ABPTB_Function::start_point($post_infos,$bp_dp,true);
					$ticket_price = 0;
					if (!empty($bp_time) && !empty($bp_dp) && !empty($post_id) && !empty($start_time)) {
						if ($seat_type == 'ticket') {
							$ticket_types = $post_array($prefix . 'item_check');
							$item_qty = $post_int_array($prefix . 'item_qty');
							if (!empty($ticket_types) && !empty($item_qty) && sizeof($ticket_types) > 0) {
								foreach ($ticket_types as $key => $ticket_type) {
									$qty = absint($item_qty[$key] ?? '');
									if (!empty($ticket_type) && $qty > 0) {
										$price = ABPTB_Function::get_price($post_infos, $bp_dp, $ticket_type, $start_time);
										$booking_info['info'][$ticket_type]['id'] = $ticket_type;
										$booking_info['info'][$ticket_type]['name'] = ABPTB_Function::ticket_name($ticket_type);
										$booking_info['info'][$ticket_type]['price'] = $price;
										$booking_info['info'][$ticket_type]['qty'] = $qty;
										$ticket_price = $ticket_price + $price * $qty;
									}
								}
							}
						} else {
							$seats = $post_val($prefix . 'sp_selected_seat');
							$seats = $seats ? explode(',', $seats) : [];
							$types = $post_val($prefix . 'sp_selected_seat_id');
							$types = $types ? explode(',', $types) : [];
							$sp_id = $post_int($prefix . 'sp_id');
							if (!empty($sp_id) && !empty($seats) && !empty($types)) {
								foreach ($types as $index => $type) {
									$seat = $seats[$index] ?? '';
									if (!empty($seat) && !empty($type)) {
										$price = ABPTB_Function::get_price($post_infos, $bp_dp, $type, $start_time);
										$booking_info['info'][$index]['id'] = $type;
										$booking_info['info'][$index]['name'] = $seat;
										$booking_info['info'][$index]['price'] = $price;
										$booking_info['info'][$index]['qty'] = 1;
										$ticket_price = $ticket_price + $price * 1;
									}
								}
								$booking_info['sp_id'] = $sp_id;
							}
						}
						if (!empty($booking_info['info'])) {
							[$bp, $dp] = array_map('intval', explode('_', $bp_dp));
							$pick_up = $post_val($prefix . 'pick_up');
							$drop_off = $post_val($prefix . 'drop_off');
							$dp_time = $post_val($prefix . 'dp_time');
							$additional_info = self::get_additional_info($post_infos, $prefix);
							$additional_price = self::get_additional_price($additional_info);
							$booking_info['seat_type'] = $seat_type;
							$booking_info['post_id'] = $post_id;
							$booking_info['bp_time'] = $bp_time;
							$booking_info['dp_time'] = $dp_time;
							$booking_info['pick_up'] = $pick_up;
							$booking_info['drop_off'] = $drop_off;
							$booking_info['pick_up_time'] = ABPTB_Function::get_pd_time($bp, $bp_time, $pick_up);
							$booking_info['drop_off_time'] = ABPTB_Function::get_pd_time($dp, $dp_time, $drop_off);
							$booking_info['start_time'] = $start_time;
							$booking_info['start_point'] = $start_point;
							$booking_info['bp_dp'] = $start_point.'_'.$end_point;
							$booking_info['duration'] = ABPTB_Function::date_time_difference($bp_time, $dp_time);
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
			public function display_cart_item_block($booking_infos): array {
				$item_data = [];
				$booking_info = $booking_infos['booking_infos'] ?? [];
				$post_id = $booking_infos['post_id'] ?? '';
				if (!empty($booking_info) && sizeof($booking_info) > 0 && !empty($post_id) && get_post_type($post_id) == ABPTB_Function::get_cpt()) {
					$return = '';
					foreach ($booking_info as $bp_dp => $booking_item) {
						if (!empty($booking_item)) {
							$ticket_infos = $booking_item['info'] ?? [];
							if (!empty($ticket_infos) && sizeof($ticket_infos) > 0) {
								$bp_time = $booking_item['bp_time'] ?? '';
								$dp_time = $booking_item['dp_time'] ?? '';
								[$bp, $dp] = array_map('intval', explode('_', $bp_dp));
								$pick_up = $booking_item['pick_up'] ?? '';
								$drop_off = $booking_item['drop_off'] ?? '';
								$start_point = $booking_item['start_point'] ?? '';
								$item_data[] = array('name' => __('Booking Information', 'abp-transport-booking') . ' ' . $return, 'value' => '<br />');
								if (intval($start_point) !== intval($bp)) {
									$item_data[] = array('name' => __('Starting Point', 'abp-transport-booking'), 'value' => ABPTB_Function::location_value($start_point) . ' - ' . ABPTB_Function::date_format($booking_item['start_time'] ?? '') . '<br />');
								}
								$item_data[] = array('name' => __('Boarding Point', 'abp-transport-booking'), 'value' => ABPTB_Function::location_value($bp) . ' -  ' . ABPTB_Function::date_format($bp_time) . '<br />');
								$item_data[] = array('name' => __('Arrival', 'abp-transport-booking'), 'value' => ABPTB_Function::location_value($dp) . ' -  ' . ABPTB_Function::date_format($dp_time) . '<br />');
								if (intval($pick_up) !== intval($bp)) {
									$item_data[] = array('name' => __('Pick Up', 'abp-transport-booking'), 'value' => ABPTB_Function::pd_value($pick_up) . ' - ' . ABPTB_Function::date_format($booking_item['pick_up_time'] ?? '') . '<br />');
								}
								if (intval($drop_off) !== intval($dp)) {
									$item_data[] = array('name' => __('Drop-Off', 'abp-transport-booking'), 'value' => ABPTB_Function::pd_value($drop_off) . ' - ' . ABPTB_Function::date_format($booking_item['drop_off_time'] ?? '') . '<br />');
								}
								$item_data[] = array('name' => __('Approximate Time ', 'abp-transport-booking'), 'value' => ($booking_item['duration'] ?? '') . '<br />');
								$item_data[] = array('name' => __('Ticket Information', 'abp-transport-booking'), 'value' => '<br />');
								foreach ($ticket_infos as $ticket_info) {
									$price = $ticket_info['price'] ?? 0;
									$qty = $ticket_info['qty'] ?? 1;
									$price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-transport-booking');
									$price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-transport-booking');
									$name = ABPTB_Function::ticket_label($ticket_info, $booking_item);
									$item_data[] = array('name' => __('Ticket', 'abp-transport-booking'), 'value' => '(' . $name . ')' . $price_text . ' X ' . $qty . '  = ' . $price . '<br />');
								}
								$additional_info = $booking_item['additional_info'] ?? [];
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
								$attendee_infos = $booking_item['pass_info'] ?? [];
								if (ABPTB_Function::on_off('client_info') && !empty($attendee_infos) && sizeof($attendee_infos) > 0) {
									$item_data[] = array('name' => __('Client Information', 'abp-transport-booking'), 'value' => '<br />');
									foreach ($attendee_infos as $attendee_info) {
										if (!empty($attendee_info)) {
											foreach ($attendee_info as $attendee) {
												$label = $attendee['label'] ?? '';
												$value = $attendee['value'] ?? '';
												if ($label && $value) {
													$item_data[] = array('name' => $label, 'value' => $value . '<br />');
												}
											}
										}
									}
								}
								$return = __('( Return )', 'abp-transport-booking');
							}
						}
					}
				}
				return $item_data;
			}
			//=============================//
			public function checkout_process_validation(): void {
				$booking_items = WC()->cart->get_cart();
				if (!empty($booking_items)) {
					foreach ($booking_items as $booking_item) {
						$post_id = $booking_item['post_id'] ?? '';
						if (!empty($booking_item) && !empty($post_id) && get_post_type($post_id) == ABPTB_Function::get_cpt()) {
							if (!ABPTB_Function::checkout_validation($booking_item)) {
								WC()->cart->empty_cart();
								wc_add_notice(
									__('Oh! We are sorry, something went wrong. Please try again later.', 'abp-transport-booking'),
									'error'
								);
								wp_safe_redirect(wc_get_page_permalink('cart'));
								exit;
							}
						}
					}
				}
			}
			public function checkout_create_order_line_item($item, $_key, $booking_infos): void {
				$booking_info = $booking_infos['booking_infos'] ?? [];
				$post_id = $booking_infos['post_id'] ?? 0;
				if (!empty($booking_info) && sizeof($booking_info) > 0 && !empty($post_id) && get_post_type($post_id) == ABPTB_Function::get_cpt()) {
					$return = '';
					foreach ($booking_info as $bp_dp => $booking_item) {
						if (!empty($booking_item)) {
							$ticket_infos = $booking_item['info'] ?? [];
							if (!empty($ticket_infos) && sizeof($ticket_infos) > 0) {
								[$bp, $dp] = array_map('intval', explode('_', $bp_dp));
								$additional_infos = $booking_item['additional_info'] ?? [];
								$attendee_infos = $booking_item['pass_info'] ?? [];
								$pick_up = $booking_item['pick_up'] ?? '';
								$drop_off = $booking_item['drop_off'] ?? '';
								$start_point = $booking_item['start_point'] ?? '';
								$item->add_meta_data(__('Booking Information', 'abp-transport-booking') . ' ' . $return, '');
								if (intval($start_point) !== intval($bp)) {
									$item->add_meta_data(__('Starting Point', 'abp-transport-booking'), ABPTB_Function::location_value($start_point) . ' - ' . ABPTB_Function::date_format($booking_item['start_time'] ?? ''));
								}
								$item->add_meta_data(__('Boarding Point', 'abp-transport-booking'), ABPTB_Function::location_value($bp) . ' - ' . ABPTB_Function::date_format($booking_item['bp_time'] ?? ''));
								$item->add_meta_data(__('Arrival', 'abp-transport-booking'), ABPTB_Function::location_value($dp) . ' - ' . ABPTB_Function::date_format($booking_item['bp_time'] ?? ''));
								if (intval($pick_up) !== intval($bp)) {
									$item->add_meta_data(__('Pick Up', 'abp-transport-booking'), ABPTB_Function::pd_value($pick_up) . ' - ' . ABPTB_Function::date_format($booking_item['pick_up_time'] ?? ''));
								}
								if (intval($drop_off) !== intval($dp)) {
									$item->add_meta_data(__('Drop-Off', 'abp-transport-booking'), ABPTB_Function::pd_value($drop_off) . ' - ' . ABPTB_Function::date_format($booking_item['drop_off_time'] ?? ''));
								}
								$item->add_meta_data(__('Approximate Time ', 'abp-transport-booking'), ($booking_item['duration'] ?? ''));
								$item->add_meta_data(__('Ticket Information', 'abp-transport-booking'), '');
								foreach ($ticket_infos as $ticket_info) {
									$price = $ticket_info['price'] ?? 0;
									$qty = $ticket_info['qty'] ?? 1;
									$price_text = $price > 0 ? wc_price($price) : __('FREE', 'abp-transport-booking');
									$price = $price > 0 ? wc_price($price * $qty) : __('FREE', 'abp-transport-booking');
									$name = ABPTB_Function::ticket_label($ticket_info, $booking_item);
									$item->add_meta_data($name, ($price_text . ' X ' . $qty . '  = ' . $price));
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
										if (!empty($attendee_info)) {
											foreach ($attendee_info as $attendee) {
												$label = $attendee['label'] ?? '';
												$value = $attendee['value'] ?? '';
												if (!empty($label) && !empty($value)) {
													$item->add_meta_data($label, $value);
												}
											}
										}
									}
								}
								//=============================//
							}
						}
					}
					$item_info = [
						'post_id' => $post_id,
						'user_id' => get_current_user_id(),
						'booking_infos' => $booking_info,
						'item_total' => $booking_infos['total_price'] ?? '',
					];
					$item_info = apply_filters('abptb_checkout_create_order_line_item', $item_info, $booking_infos);
					$item->add_meta_data('_abptb_items', $item_info, true);
				}
			}
			public static function save_custom_data($order_id): void {
				if ($order_id) {
					$order = wc_get_order($order_id);
					if (!$order) {
						return;
					}
					$order_status = $order->get_status();
					$payment_method = $order->get_payment_method_title();
					$user_id = $order->get_customer_id();
					$_billing_first_name = $order->get_billing_first_name();
					$_billing_last_name = $order->get_billing_last_name();
					$billing_email = $order->get_billing_email();
					$billing_phone = $order->get_billing_phone();
					$_billing_address_1 = $order->get_billing_address_1();
					$_billing_address_2 = $order->get_billing_address_2();
					$billing_name = $_billing_first_name . ' ' . $_billing_last_name;
					$billing_address = $_billing_address_1 . ' ' . $_billing_address_2;
					if ($order_status != 'failed') {
						$total_order = ABPTB_Query::get_booking_query(['order_id' => $order_id], 0, 0, true);
						if ($total_order == 0) {
							foreach ($order->get_items() as $item_id => $item) {
								$item_infos = wc_get_order_item_meta($item_id, '_abptb_items');
								if (!empty($item_infos) && is_array($item_infos) && sizeof($item_infos) > 0) {
									$post_id = $item_infos['post_id'] ?? '';
									$booking_info = $item_infos['booking_infos'] ?? [];

									if (!empty($post_id) && get_post_type($post_id) == ABPTB_Function::get_cpt() && !empty($booking_info) && sizeof($booking_info) > 0) {
										foreach ($booking_info as $bp_dp => $item_info) {
											if (!empty($item_info)) {
												$seat_type = $item_info['seat_type'] ?? '';
												$ticket_infos = $item_info['info'] ?? [];
												[$bp, $dp] = array_map('intval', explode('_', $bp_dp));
												$additional_info = $item_info['additional_info'] ?? [];
												global $wpdb;
												$table_name = $wpdb->prefix . 'abptb_orders';
												if (!empty($ticket_infos) && sizeof($ticket_infos) > 0) {
													$ticket_id = $ex_id = [];
													$qty = 0;
													foreach ($ticket_infos as $ticket_info) {
														if ($seat_type == 'sp') {
															$ticket_id[] = $ticket_info['name'] ?? '';
														} else {
															$ticket_id[] = $ticket_info['id'] ?? '';
														}
														$qty = $qty + ($ticket_info['qty'] ?? 1);
													}
													if (!empty($additional_info) && sizeof($additional_info) > 0) {
														foreach ($additional_info as $key => $additional) {
															$ex_id[] = $key;
														}
													}
													$others['duration'] = $item_info['duration'] ?? '';
													$_order_status = 'wc-' . $order_status;
													$data = [
														'order_id' => intval($order_id),
														'item_id' => intval($item_id),
														'post_id' => intval($post_id),
														'user_id' => intval($user_id),
														'start_point' => intval($item_info['start_point'] ?? ''),
														'start_time' => sanitize_text_field($item_info['start_time'] ?? ''),
														'seat_type' => sanitize_text_field($seat_type),
														'bp_dp' => sanitize_text_field($item_info['bp_dp'] ?? ''),
														'bp' => intval($bp),
														'dp' => intval($dp),
														'bp_time' => sanitize_text_field($item_info['bp_time'] ?? ''),
														'dp_time' => sanitize_text_field($item_info['dp_time'] ?? ''),
														'pick_up' => sanitize_text_field($item_info['pick_up'] ?? ''),
														'pick_up_time' => sanitize_text_field($item_info['pick_up_time'] ?? ''),
														'drop_off' => sanitize_text_field($item_info['drop_off'] ?? ''),
														'drop_off_time' => sanitize_text_field($item_info['drop_off_time'] ?? ''),
														'sp_id' => intval($item_info['sp_id'] ?? ''),
														'ticket_info' => wp_json_encode($ticket_infos),
														'ticket_id' => wp_json_encode($ticket_id),
														'qty' => intval($qty),
														'price' => sanitize_text_field($item_info['price'] ?? ''),
														'ex_info' => wp_json_encode($additional_info),
														'ex_id' => wp_json_encode($ex_id),
														'ex_price' => sanitize_text_field($item_info['ex_price'] ?? ''),
														'total' => sanitize_text_field($item_info['total'] ?? ''),
														'pass_info' => wp_json_encode($item_info['pass_info'] ?? []),
														'checkin' => 0,
														'female' => 0,
														'book_type' => 0,
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
