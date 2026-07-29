<?php
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	if (!class_exists('ABPTB_Function')) {
		class ABPTB_Function {
			public function __construct() {
			}
			public static function get_cpt(): string { return 'abptb_post'; }
			public static function get_post_info($post_id, $key, $default = '') {
				$data = get_post_meta($post_id, $key, true) ?: $default;
				return self::data_sanitize($data);
			}
			public static function data_sanitize($data) {
				$data = maybe_unserialize($data);
				if (is_string($data)) {
					$data = maybe_unserialize($data);
					if (is_array($data)) {
						$data = self::data_sanitize($data);
					} else {
						$data = sanitize_text_field(stripslashes(wp_strip_all_tags($data)));
					}
				} elseif (is_array($data)) {
					foreach ($data as &$value) {
						if (is_array($value)) {
							$value = self::data_sanitize($value);
						} else {
							$value = sanitize_text_field(stripslashes(wp_strip_all_tags($value)));
						}
					}
				}
				return $data;
			}
			public static function get_all_meta($post_id = 0): array {
				$all_data = [];
				if ($post_id > 0) {
					$all_data['post_title'] = get_the_title($post_id);
					$all_data['post_id'] = $post_id;
					$metas = get_post_meta($post_id);
					if (!empty($metas) && sizeof($metas) > 0) {
						foreach ($metas as $key => $meta) {
							$all_data[$key] = self::data_sanitize($meta[0]);
						}
					}
					$all_data['capacity'] = self::get_total_qty($post_id, $all_data);
				}
				return $all_data;
			}
			public static function get_taxonomy($name): array|WP_Error|string {
				return get_terms(array('taxonomy' => $name, 'hide_empty' => false));
			}
			public static function get_all_term_data($term_name): array {
				$all_data = [];
				$taxonomies = self::get_taxonomy($term_name);
				if (!empty($taxonomies) && is_array($taxonomies) && sizeof($taxonomies) > 0) {
					foreach ($taxonomies as $taxonomy) {
						$all_data[$taxonomy->term_id] = $taxonomy->name;
					}
				}
				return $all_data;
			}
			public static function get_option($option, $default = []) {
				$option_data = get_option(sanitize_key($option));
				return !empty($option_data) ? $option_data : $default;
			}
			public static function get_options($option, $key, $default = '') {
				$options = get_option(sanitize_key($option));
				if (isset($options[$key]) && $options[$key]) {
					$default = $options[$key];
				}
				return $default;
			}
			public static function booking_status() { return (ABPTB_Configuration['booked_status'] ?? null) ?: 'wc-processing,wc-completed'; }
			public static function label() { return (ABPTB_Configuration['label'] ?? null) ?: __('Transport', 'abp-transport-booking'); }
			public static function slug() { return (ABPTB_Configuration['slug'] ?? null) ?: 'transport-forge'; }
			public static function icon_wp() { return (ABPTB_Configuration['icon'] ?? null) ?: 'dashicons-tickets'; }
			public static function icon() { return (ABPTB_Configuration['brand_icon'] ?? null) ?: '🚐'; }
			public static function feature_label() { return (ABPTB_Configuration['feature_label'] ?? null) ?: __('Feature', 'abp-transport-booking'); }
			public static function brand_label() { return (ABPTB_Configuration['brand_label'] ?? null) ?: __('Brand', 'abp-transport-booking'); }
			public static function brand_slug() { return (ABPTB_Configuration['brand_slug'] ?? null) ?: 'brand'; }
			public static function brand_value($id) { return (ABPTB_Brand[$id]['name'] ?? null) ?: $id; }
			public static function category_label() { return (ABPTB_Configuration['category_label'] ?? null) ?: __('Transport Type', 'abp-transport-booking'); }
			public static function category_slug() { return (ABPTB_Configuration['cat_slug'] ?? null) ?: 'category'; }
			public static function category_value($id) { return (ABPTB_Category[$id]['name'] ?? null) ?: $id; }
			public static function organizer_label() { return (ABPTB_Configuration['organizer_label'] ?? null) ?: __('Organizer', 'abp-transport-booking'); }
			public static function organizer_slug() { return (ABPTB_Configuration['org_slug'] ?? null) ?: 'organizer'; }
			public static function organizer_value($id) { return (ABPTB_Organizer[$id]['name'] ?? null) ?: $id; }
			public static function location_label() { return (ABPTB_Configuration['location_label'] ?? null) ?: __('Stops', 'abp-transport-booking'); }
			public static function location_slug() { return (ABPTB_Configuration['location_slug'] ?? null) ?: 'location'; }
			public static function location_value($id) { return (ABPTB_Location[$id]['name'] ?? null) ?: $id; }
			public static function ticket_name($id) { return (ABPTB_Ticket[$id]['label'] ?? null) ?: __('Ticket/Seat', 'abp-transport-booking'); }
			public static function ticket_icon($id) { return (ABPTB_Ticket[$id]['icon'] ?? null) ?: ''; }
			public static function on_off($key): bool {
				$value = (ABPTB_On_Off[$key] ?? 'on') ?: 'on';
				return $value !== 'off';
			}
			public static function array_to_string($array) {
				$ids = '';
				if (sizeof($array) > 0) {
					foreach ($array as $data) {
						if ($data) {
							$ids = $ids ? $ids . ',' . $data : $data;
						}
					}
				}
				return $ids;
			}
			public static function build_url($value = '', $extra_args = []): string {
				$default_args = [
					'page' => ABPTB_Function::slug(),
					'tab' => $value,
					'_abptb_nonce' => wp_create_nonce('abptb_url_action'),
				];
				$final_args = array_merge($default_args, $extra_args);
				return add_query_arg($final_args, admin_url('admin.php'));
			}
			public static function get_image_url($post_id = '', $image_id = '', $size = 'full'): bool|string {
				$image_id = $post_id && $post_id > 0 ? get_post_thumbnail_id($post_id) : $image_id;
				return wp_get_attachment_image_url($image_id, $size);
			}
			public static function get_page_by_slug($slug): bool|WP_Post {
				if ($pages = get_pages()) {
					foreach ($pages as $page) {
						if ($slug === $page->post_name) {
							return $page;
						}
					}
				}
				return false;
			}
			public static function get_id_by_slug($page_slug): ?int {
				$page = get_page_by_path($page_slug);
				return $page?->ID;
			}
			public static function check_wc(): int {
				if (class_exists('WooCommerce') || is_plugin_active('woocommerce/woocommerce.php')) {
					return 2;
				}
				$wc_dir = trailingslashit(WP_PLUGIN_DIR) . 'woocommerce';
				if (is_dir($wc_dir)) {
					return 1;
				}
				return 0;
			}
			public static function already_in_cart($post_id, $bp, $dp, $bp_date, $seat_name): int {
				$count = 0;
				if (is_admin() && str_contains(wp_get_referer(), 'admin_order')) {
					return $count;
				}
				$formatted_bp_date = $bp_date ? gmdate('Y-m-d', strtotime($bp_date)) : '';
				if (!function_exists('WC') || !WC()->cart) {
					return $count;
				}
				$cart_items = WC()->cart->get_cart();
				if (!is_array($cart_items) || empty($cart_items)) {
					return $count;
				}
				$routes = self::get_post_info($post_id, 'route_direction', []);
				if (!is_array($routes) || empty($routes)) {
					return $count;
				}
				$sp = array_search($bp, $routes, true);
				$ep = array_search($dp, $routes, true);
				$target_seat = strtolower($seat_name);
				foreach ($cart_items as $cart_item) {
					$cart_post_id = absint($cart_item['post_id'] ?? 0);
					$cart_date = $cart_item['bp_time'] ?? '';
					$cart_date = $cart_date ? gmdate('Y-m-d', strtotime($cart_date)) : '';
					if ($cart_post_id !== absint($post_id) || $cart_date !== $formatted_bp_date) {
						continue;
					}
					$cart_bp = $cart_item['bp'] ?? '';
					$cart_dp = $cart_item['dp'] ?? '';
					$is_bp_valid = in_array($cart_bp, array_slice($routes, 0, $ep), true);
					$is_dp_valid = in_array($cart_dp, array_slice($routes, $sp + 1), true);
					if (!$is_bp_valid || !$is_dp_valid) {
						continue;
					}
					$seat_infos = $cart_item['ticket_info'] ?? [];
					if (!is_array($seat_infos) || empty($seat_infos)) {
						continue;
					}
					foreach ($seat_infos as $seat_info) {
						$current_seat = strtolower($seat_info['seat'] ?? '');
						if ($current_seat === $target_seat) {
							$count += absint($cart_item['qty'] ?? 1);
						}
					}
				}
				return $count;
			}
			public static function get_user_role($user_ID): string {
				global $wp_roles;
				$user_role_list = '';
				$user_data = get_userdata($user_ID);
				$user_role_slug = $user_data->roles;
				if (is_array($user_role_slug) && sizeof($user_role_slug) > 0) {
					$user_count = 0;
					foreach ($user_role_slug as $user_role) {
						$user_count++;
						if ($user_count > 1) {
							$user_role_list .= ", ";
						}
						$user_role_list .= translate_user_role($wp_roles->roles[$user_role]['name']);
					}
				}
				return $user_role_list;
			}
			//=========== Template Related==================//
			public static function details_template_path($post_id): string {
				$post_id = $post_id ?? get_the_id();
				$template_name = self::get_post_info($post_id, 'abptb_template', 'default');
				$file_name = 'details_theme/' . $template_name . '.php';
				$dir = ABPTB_DIR . 'tb_templates/' . $file_name;
				if (!file_exists($dir)) {
					$file_name = 'details_theme/default.php';
				}
				return self::template_path($file_name);
			}
			public static function template_path($file_name): string {
				$file_path = wp_normalize_path(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . '/tb_templates/' . $file_name);
				$default_dir = wp_normalize_path(ABPTB_DIR . 'tb_templates/' . $file_name);
				return file_exists($file_path) ? $file_path : $default_dir;
			}
			//============== Transport Function===============//
			public static function get_route_info(): array {
				$_post_id = get_the_ID();
				$post_ids = (!empty($_post_id) && $_post_id > 0 && get_post_type($_post_id) == ABPTB_Function::get_cpt()) ? [$_post_id] : ABPTB_ids;
				$route_infos = [];
				if (!empty($post_ids)) {
					$all_bp = [];
					foreach ($post_ids as $post_id) {
						$route_data = ABPTB_Function::get_post_info($post_id, 'route_data', []);
						if (!empty($route_data)) {
							$info = [];
							foreach ($route_data as $data) {
								[$bp, $dp] = array_map('intval', explode('_', $data));
								$info[$bp][] = $dp;
								$info['all_bp'][] = $bp;
								$all_bp[] = $bp;
							}
							$info = array_map(function ($subArray) {
								return array_values(array_unique($subArray));
							}, $info);
							$route_infos[$post_id] = $info;
						}
					}
					$route_infos['all_bp'] = array_unique($all_bp);
				}
				return $route_infos;
			}
			public static function return_check($post_infos, $bp_dp): bool {
				if (!empty($post_infos) && !empty($bp_dp)) {
					$display_return = $post_infos['display_return'] ?? 'off';
					$display_return = ABPTB_Function::on_off('return') ? $display_return : 'off';
					if ($display_return == 'on') {
						$return_price_infos = $post_infos['return_price_infos'] ?? [];
						return isset($return_price_infos[$bp_dp]);
					}
				}
				return false;
			}
			public static function start_point($post_infos, $bp_dp): int|string|null {
				$start_point = '';
				if (!empty($post_infos) && !empty($bp_dp)) {
					$key = self::return_check($post_infos, $bp_dp) ? 'return_routing_infos' : 'routing_infos';
					$route = $post_infos[$key] ?? [];
					$start_point = array_key_first($route);
				}
				return $start_point;
			}
			//============= Date function================//
			public static function check_date_exit($post_infos = []): bool {
				$post_id = absint($post_infos['post_id'] ?? 0);
				$start_date_time = $post_infos['start_time'] ?? '';
				$end_date_time = $post_infos['end_time'] ?? '';
				$rent_rule = $post_infos['rent_rule'] ?? self::get_post_info($post_id, 'rent_rule');
				if ($post_id <= 0 || empty($start_date_time) || empty($end_date_time) || empty($rent_rule)) {
					return false;
				}
				$start = gmdate('Y-m-d', strtotime($start_date_time));
				$end = gmdate('Y-m-d', strtotime($end_date_time));
				$all_dates = self::date($post_id);
				$all_end_dates = ($rent_rule === 'hourly') ? $all_dates : self::date($post_id);
				if (!in_array($start, $all_dates, true) || !in_array($end, $all_end_dates, true)) {
					return false;
				}
				if ($rent_rule !== 'hourly' && $rent_rule !== 'multi_day') {
					return true;
				}
				$time_list = self::get_time($post_id);
				if (empty($time_list)) {
					return false;
				}
				$start_time = gmdate('H:i', strtotime($start_date_time));
				$end_time = gmdate('H:i', strtotime($end_date_time));
				$start_day_name = strtolower(gmdate('l', strtotime($start)));
				$time_slots = $time_list[$start] ?? $time_list[$start_day_name] ?? $time_list['slot'] ?? '';
				if (empty($time_slots)) {
					return false;
				}
				if ($rent_rule === 'hourly') {
					return self::check_time_slot_exit($time_slots, $start_time) && self::check_time_slot_exit($time_slots, $end_time);
				}
				if (!self::check_time_slot_exit($time_slots, $start_time)) {
					return false;
				}
				$end_day_name = strtolower(gmdate('l', strtotime($end)));
				$end_time_slots = $time_list[$end] ?? $time_list[$end_day_name] ?? $time_list['slot'] ?? '';
				if (!empty($end_time_slots) && self::check_time_slot_exit($end_time_slots, $end_time)) {
					return true;
				}
				return false;
			}
			public static function date_all($post_ids = [], $_date = ''): array {
				$all_dates = [];
				$post_ids = !empty($post_ids) ? $post_ids : ABPTB_ids;
				if (!empty($post_ids)) {
					$global_complete = 0;
					foreach ($post_ids as $post_id) {
						if (!empty($post_id) && $post_id > 0) {
							$active_global_dates = self::get_post_info($post_id, 'active_global_dates', 'on');
							if ($active_global_dates == 'on') {
								$date_infos = self::get_option('abptb_dates');
								$global_complete++;
							} else {
								$date_infos = self::get_post_info($post_id, 'abptb_dates', []);
							}
							if (!empty($date_infos) && $global_complete < 2) {
								$dates = self::date($post_id, $date_infos, $_date);
								$all_dates = array_merge($all_dates, $dates);
							}
						}
					}
					$all_dates = array_unique($all_dates);
					usort($all_dates, "ABPTB_Function::sort_date");
				}
				return $all_dates;
			}
			public static function date($post_id, $date_infos = [], $_date = ''): array {
				$all_dates = [];
				if (!empty($post_id) && $post_id > 0) {
					if (empty($date_infos)) {
						$active_global_dates = self::get_post_info($post_id, 'active_global_dates', 'on');
						if ($active_global_dates == 'on') {
							$date_infos = self::get_option('abptb_dates');
						} else {
							$date_infos = self::get_post_info($post_id, 'abptb_dates', []);
						}
					}
					$date_type = $date_infos['date_type'] ?? 'periodic_date';
					$now = current_time('Y-m-d');
					if ($date_type == 'specific_date') {
						$specific_dates = $date_infos['specific_dates'] ?? [];
						if (is_array($specific_dates) && sizeof($specific_dates) > 0) {
							foreach ($specific_dates as $date_item) {
								if (!empty($date_item)) {
									$date_item = gmdate('Y-m-d', strtotime($date_item));
									if (strtotime($date_item) >= strtotime($now)) {
										$all_dates[] = $date_item;
									}
								}
							}
						}
					} else {
						$start_date = $date_infos['periodic_start_date'] ?? '';
						$start_date = !empty($_date) ? $_date : $start_date;
						$start_date = $start_date ?: $now;
						$sale_end_date = $date_infos['periodic_end_date'] ?? '';
						$sale_end_date = $sale_end_date ? gmdate('Y-m-d', strtotime($sale_end_date)) : '';
						$active_days = (ABPTB_Date_Config['advance_date_number'] ?? null) ?: 28;
						if (strtotime($now) >= strtotime($start_date)) {
							$start_date = $now;
						}
						$end_date = gmdate('Y-m-d', strtotime($start_date . ' +' . $active_days . ' day'));
						if ($sale_end_date && strtotime($sale_end_date) < strtotime($end_date)) {
							$end_date = $sale_end_date;
						}
						$all_dates = self::date_list_modify($start_date, $end_date, $date_infos);
					}
					$all_dates = sizeof($all_dates) > 1 ? array_unique($all_dates) : $all_dates;
					usort($all_dates, "ABPTB_Function::sort_date");
					$current_date = current($all_dates);
					$now = strtotime(current_time('Y-m-d'));
					if ($now == strtotime($current_date)) {
						$time_infos = self::get_post_info($post_id, 'time_infos', []);
						$time_info = self::time($time_infos, $current_date);
						if (!empty($time_info)) {
							$exit = false;
							$buffer_time = ABPTB_Dates['sale_close_after'] ?? 0;
							$now_time = strtotime(current_time('Y-m-d H:i'));
							foreach ($time_info as $_time) {
								$date = $current_date . ' ' . $_time;
								$date = $buffer_time > 0 ? strtotime($date) - $buffer_time : strtotime($date);
								if ($now_time < $date) {
									$exit = true;
								}
							}
							if (!$exit) {
								unset($all_dates[array_key_first($all_dates)]);
							}
						}
					}
				}
				return $all_dates;
			}
			public static function date_list_modify($start_date, $end_date, $date_infos): array {
				$all_dates = [];
				if (strtotime($start_date) <= strtotime($end_date)) {
					$now = current_time('Y-m-d');
					$off_dates = [];
					$date_rule = $date_infos['date_rule'] ?? '';
					$date_rule_array = $date_rule ? explode(',', $date_rule) : [];
					if (in_array('off_date_range', $date_rule_array)) {
						$off_date_range = $date_infos['off_date_range'] ?? [];
						if (is_array($off_date_range) && sizeof($off_date_range) > 0) {
							foreach ($off_date_range as $off_date) {
								if (is_array($off_date) && ($off_date['from'] ?? '') !== '' && ($off_date['to'] ?? '') !== '') {
									$from_date = gmdate('Y-m-d', strtotime($off_date['from']));
									$to_date = gmdate('Y-m-d', strtotime($off_date['to']));
									$off_date_lists = self::date_separate_period($from_date, $to_date);
									foreach ($off_date_lists as $off_date_list) {
										$off_dates[] = $off_date_list->format('Y-m-d');
									}
								}
							}
						}
					}
					if (in_array('specific_of_date', $date_rule_array)) {
						$particular_off_dates = $date_infos['specific_off_dates'] ?? [];
						if (is_array($particular_off_dates) && sizeof($particular_off_dates) > 0) {
							foreach ($particular_off_dates as $particular_off_date) {
								$particular_off_date = gmdate('Y-m-d', strtotime($particular_off_date));
								$off_dates[] = $particular_off_date;
							}
						}
					}
					$off_dates = array_unique($off_dates);
					$off_day_array = [];
					if (in_array('weekend', $date_rule_array)) {
						$off_days = $date_infos['weekend'] ?? '';
						$off_day_array = $off_days ? explode(',', $off_days) : [];
					}
					$repeat = $date_infos['periodic_after'] ?? 1;
					$dates = self::date_separate_period($start_date, $end_date, $repeat);
					foreach ($dates as $date) {
						$date = $date->format('Y-m-d');
						if (strtotime($date) >= strtotime($now)) {
							$day = strtolower(gmdate('l', strtotime($date)));
							if (!in_array($date, $off_dates) && !in_array($day, $off_day_array)) {
								$all_dates[] = $date;
							}
						}
					}
					//==================//
					if (in_array('special_on_dates', $date_rule_array)) {
						$special_on_dates = $date_infos['special_on_dates'] ?? [];
						if (is_array($special_on_dates) && sizeof($special_on_dates) > 0) {
							foreach ($special_on_dates as $date_item) {
								if (!empty($date_item)) {
									$date_item = gmdate('Y-m-d', strtotime($date_item));
									if (strtotime($date_item) >= strtotime($now)) {
										$all_dates[] = $date_item;
									}
								}
							}
						}
					}
				}
				return $all_dates;
			}
			public static function time_route($post_infos, $bp_dp, $journey_date): array {
				$time_info = [];
				if (!empty($post_infos) && !empty($bp_dp) && !empty($journey_date)) {
					$is_return = self::return_check($post_infos, $bp_dp);
					$key = $is_return ? 'return_time_infos' : 'time_infos';
					$time_infos = $post_infos[$key] ?? [];
					$time_info = self::time($time_infos, $journey_date);
				}
				return $time_info;
			}
			public static function time($time_infos, $journey_date) {
				$day_times = $time_infos['day_time'] ?? [];
				$date_times = $time_infos['date_times'] ?? [];
				$default_time = $time_infos['time'] ?? [];
				if (!empty($day_times)) {
					foreach ($day_times as $day_key => $day_time) {
						if (!empty($day_time)) {
							$time_info[strtolower($day_key)] = $day_time;
						}
					}
				}
				if (!empty($date_times)) {
					foreach ($date_times as $date_time) {
						$date = $date_time['date'] ?? '';
						$time = $date_time['time'] ?? [];
						if (!empty($date) && !empty($time)) {
							$time_info[$date] = $time;
						}
					}
				}
				if (!empty($time_info[$journey_date])) {
					return $time_info[$journey_date];
				}
				$day_name = strtolower(date('l', strtotime($journey_date)));
				if (!empty($time_info[$day_name])) {
					return $time_info[$day_name];
				}
				return $default_time;
			}
			public static function time_bp($post_infos, $bp_dp, $journey_date, $all_start_time = []): array {
				$time_info = [];
				if (!empty($post_infos) && !empty($bp_dp) && !empty($journey_date)) {
					$is_return = self::return_check($post_infos, $bp_dp);
					$key = $is_return ? 'return_routing_infos' : 'routing_infos';
					$route_infos = $post_infos[$key] ?? [];
					$all_start_time = !empty($all_start_time) ? $all_start_time : self::time_route($post_infos, $bp_dp, $journey_date);
					[$bp, $dp] = array_map('intval', explode('_', $bp_dp));
					$info = $route_infos[$bp] ?? '';
					if (!empty($bp) && !empty($dp) && !empty($all_start_time) && !empty($info)) {
						$buffer_time = ABPTB_Dates['sale_close_after'] ?? 0;
						$now = strtotime(current_time('Y-m-d H:i'));
						$min = $info['time'] ?? 0;
						foreach ($all_start_time as $_time) {
							$date = $journey_date . ' ' . $_time;
							$date = gmdate('Y-m-d H:i', strtotime("+{$min} minutes", strtotime($date)));
							$_date = $buffer_time > 0 ? gmdate('Y-m-d H:i', strtotime($date) - $buffer_time) : $date;
							if ($now < strtotime($_date)) {
								$time_info[$_time] = $date;
							}
						}
					}
				}
				return $time_info;
			}
			public static function time_difference($start_time, $end_time): string {
				$text = '';
				if (!empty($end_time)) {
					$start_time = $start_time ?? 0;
					$totalMinutes = $end_time - $start_time;
					if ($totalMinutes <= 0) {
						return __('0 Min', 'abp-transport-booking');
					}
					$days = floor($totalMinutes / 1440);
					$hours = floor(($totalMinutes % 1440) / 60);
					$minutes = $totalMinutes % 60;
					if ($days > 0) {
						$text .= sprintf(
						/* translators: %s = Days */
							_n(' %s Day', ' %s Days', $days, 'abp-transport-booking'), $days);
					}
					if ($hours > 0) {
						$text .= sprintf(
						/* translators: %s = Hours */
							_n(' %s Hour', ' %s Hours', $hours, 'abp-transport-booking'), $hours);
					}
					if ($minutes > 0) {
						$text .= sprintf(
						/* translators: %s = Minutes */
							_n(' %s Minute', ' %s Minutes', $minutes, 'abp-transport-booking'), $minutes);
					}
				}
				return $text;
			}
			public static function date_format($date, $format = ''): string {
				if (!empty($date)) {
					if (empty($format)) {
						$format = ABPTB_Function::check_time_exit_date($date) ? 'full' : 'date';
					}
					$date_format = self::date_format_php();
					$time_format = ABPTB_Time_Format;
					$wp_settings = $date_format . '  ' . $time_format;
					//$timezone = wp_timezone_string();
					$timestamp = strtotime($date);
					if ($format == 'date') {
						$date = date_i18n($date_format, $timestamp);
					} elseif ($format == 'time') {
						$date = date_i18n($time_format, $timestamp);
					} elseif ($format == 'full') {
						$date = date_i18n($wp_settings, $timestamp);
					} elseif ($format == 'day') {
						$date = date_i18n('d', $timestamp);
					} elseif ($format == 'month') {
						$date = date_i18n('M', $timestamp);
					} elseif ($format == 'year') {
						$date = date_i18n('Y', $timestamp);
					} else {
						$date = date_i18n($format, $timestamp);
					}
				}
				return $date;
			}
			public static function date_format_php(): string {
				$formats = [
					'yy/mm/dd' => 'Y/m/d',
					'yy-dd-mm' => 'Y-d-m',
					'yy/dd/mm' => 'Y/d/m',
					'dd-mm-yy' => 'd-m-Y',
					'dd/mm/yy' => 'd/m/Y',
					'mm-dd-yy' => 'm-d-Y',
					'mm/dd/yy' => 'm/d/Y',
					'd M , yy' => 'j M , Y',
					'D d M , yy' => 'D j M , Y',
					'M d , yy' => 'M  j, Y',
					'D M d , yy' => 'D M  j, Y',
				];
				return $formats[ABPTB_JS_Date_Format] ?? 'Y-m-d';
			}
			public static function date_format_js() { return (ABPTB_Date_Config['date_format'] ?? null) ?: 'D d M , yy'; }
			public static function date_separate_period($start_date, $end_date, $repeat = 1): DatePeriod {
				$repeat = max($repeat, 1);
				$_interval = "P" . $repeat . "D";
				$end_date = gmdate('Y-m-d', strtotime($end_date . ' +1 day'));
				return new DatePeriod(new DateTime($start_date), new DateInterval($_interval), new DateTime($end_date));
			}
			public static function check_time_exit_date($date): bool {
				if ($date) {
					$parse_date = date_parse($date);
					if (($parse_date['hour'] && $parse_date['hour'] > 0) || ($parse_date['minute'] && $parse_date['minute'] > 0) || ($parse_date['second'] && $parse_date['second'] > 0)) {
						return true;
					}
				}
				return false;
			}
			public static function sort_date($a, $b): int { return strtotime($a) - strtotime($b); }
			public static function sort_date_array($a, $b): int {
				$dateA = strtotime($a['time']);
				$dateB = strtotime($b['time']);
				if ($dateA == $dateB) {
					return 0;
				} elseif ($dateA > $dateB) {
					return 1;
				} else {
					return -1;
				}
			}
			public static function get_date_time_difference($start_time, $end_time) {
				$text = '';
				if (!empty($start_time) && !empty($end_time) && strtotime($start_time) <= strtotime($end_time)) {
					$date1 = date_create($start_time);
					$date2 = date_create($end_time);
					$diff = date_diff($date1, $date2);
					$years = $diff->y;
					$months = $diff->m;
					$days = $diff->d;
					$hours = $diff->h;
					$minutes = $diff->i;
					$seconds = $diff->s;
					if ($years > 0) {
						$text .= sprintf(
						/* translators: %s =Years */
							_n(' %s Year', ' %s Years', $years, 'abp-transport-booking'), $years);
					}
					if ($months > 0) {
						$text .= sprintf(
						/* translators: %s = Months */
							_n(' %s Month', ' %s Months', $months, 'abp-transport-booking'), $months);
					}
					if ($days > 0) {
						$text .= sprintf(
						/* translators: %s = Days */
							_n(' %s Day', ' %s Days', $days, 'abp-transport-booking'), $days);
					}
					if ($hours > 0) {
						$text .= sprintf(
						/* translators: %s = Hours */
							_n(' %s Hour', ' %s Hours', $hours, 'abp-transport-booking'), $hours);
					}
					if ($minutes > 0) {
						$text .= sprintf(
						/* translators: %s = Minutes */
							_n(' %s Minute', ' %s Minutes', $minutes, 'abp-transport-booking'), $minutes);
					}
					if ($seconds > 0) {
						$text .= sprintf(
						/* translators: %s = Seconds */
							_n(' %s Second', ' %s Seconds', $seconds, 'abp-transport-booking'), $seconds);
					}
				}
				return $text;
			}
			public static function check_time_slot_exit($main_slots, $input_slots): bool {
				if (!empty($main_slots) && !empty($input_slots)) {
					$main_slots = explode('-', $main_slots);
					if (isset($main_slots[0]) && isset($main_slots[1])) {
						$main_start = strtotime($main_slots[0]);
						$main_end = strtotime($main_slots[1]);
						$input_slots = strtotime($input_slots);
						if ($main_start <= $input_slots && $main_end >= $input_slots) {
							return true;
						}
					}
				}
				return false;
			}
			public static function booking_buffer($time, $end = ''): string {
				$date_infos = ABPTB_Dates;
				if (!empty($end)) {
					$buffer_time = $date_infos['sale_close_after'] ?? 0;
					$buffer_time = $buffer_time * 60;
					$time = gmdate('Y-m-d H:i', strtotime($time) + $buffer_time);
				} else {
					$buffer_time = $date_infos['sale_close_before'] ?? 0;
					$buffer_time = $buffer_time * 60;
					$time = gmdate('Y-m-d H:i', strtotime($time) - $buffer_time);
				}
				return $time;
			}
			//=============Price Function================//
			public static function tax_with_price($post_id, $price): string {
				$num_of_decimal = get_option('woocommerce_price_num_decimals', 2);
				$_product = self::get_post_info($post_id, 'link_wc_id', $post_id);
				$product = wc_get_product($_product);
				$tax_display = get_option('woocommerce_tax_display_shop');
				if ('' === $price) {
					return '';
				}
				$return_price = (float)$price;
				if ($product && $product->is_taxable()) {
					$tax_rates = WC_Tax::get_rates($product->get_tax_class());
					if (!empty($tax_rates)) {
						$taxes = WC_Tax::calc_tax($return_price, $tax_rates, false);
						$tax_total = 'yes' === get_option('woocommerce_tax_round_at_subtotal') ? array_sum($taxes) : array_sum(array_map('wc_round_tax_total', $taxes));
						if (!empty(WC()->customer) && WC()->customer->get_is_vat_exempt()) {
							$return_price = round($return_price, $num_of_decimal);
						} else {
							$return_price = $tax_display === 'excl' ? round($return_price, $num_of_decimal) : round($return_price + $tax_total, $num_of_decimal);
						}
					}
				}
				return $return_price;
			}
			public static function get_price($post_infos, $bp_dp, $type = 'price', $date = ''): bool|int|string|null {
				if (!empty($bp_dp)) {
					$price = 0;
					$post_id = absint($post_infos['post_id'] ?? 0);
					if (!empty($post_id) && $post_id > 0 && !empty($type)) {
						$display_ticket_type = $post_infos['display_ticket_type'] ?? ABPTB_Function::get_post_info($post_id, 'display_ticket_type', 'on');
						$display_ticket_type = ABPTB_Function::on_off('ticket_type') ? $display_ticket_type : 'off';
						$type = $display_ticket_type == 'on' ? $type : 'price';
						$price_infos = $post_infos['price_data'] ?? ABPTB_Function::get_post_info($post_id, 'price_data', []);
						if (!empty($price_infos) && array_key_exists($bp_dp, $price_infos)) {
							$price = $price_infos[$bp_dp][$type] ?? (current($price_infos[$bp_dp]) ?? 0);
							$price = $price > 0 ? apply_filters('abptb_filter_price', $price, $date) : $price;
						}
					}
					return $price > 0 ? self::tax_with_price($post_id, $price) : 0;
				}
				return null;
			}
			public static function get_additional_price($post_infos=[], $i_d=''): int|string {
				$services = ABPTB_Function::additional_data($post_infos);
				$price = 0;
				$post_id = $post_infos['post_id'] ?? '';
				if (!empty($services) && is_array($services) && !empty($i_d)) {
					foreach ($services as $id => $service) {
						if ($id == $i_d) {
							$price = $service['price'] ?? 0;
							break;
						}
					}
				}
				return $price > 0 ? self::tax_with_price($post_id, $price) : 0;
			}
			//=============================//
			public static function get_total_qty($post_id, $post_infos = []) {
				$total_qty = 0;
				if (!empty($post_id) && $post_id > 0) {
					if (!empty($post_infos)) {
						$seat_type = $post_infos['seat_type'] ?? 'sp';
						$ticket_infos = $post_infos['ticket_infos'] ?? [];
						$sp_infos = $post_infos['sp_infos'] ?? [];
					} else {
						$seat_type = ABPTB_Function::get_post_info($post_id, 'seat_type', 'sp');
						$ticket_infos = ABPTB_Function::get_post_info($post_id, 'ticket_infos', []);
						$sp_infos = ABPTB_Function::get_post_info($post_id, 'sp_infos', []);
					}
					$seat_type = ABPTB_Function::on_off('sp') ? $seat_type : 'ticket';
					if ($seat_type === 'ticket') {
						if (!empty($ticket_infos)) {
							foreach ($ticket_infos as $ticket_info) {
								$total_qty += $ticket_info['qty'] ?? 0;
							}
						}
					} else {
						if (!empty($sp_infos)) {
							$ids = [];
							foreach ($sp_infos as $ticket_info) {
								$id = $ticket_info['id'] ?? '';
								if (!empty($id)) {
									$ids[] = $id;
								}
							}
							$total_qty = self::get_sp_ticket($ids, true);
						}
					}
				}
				return $total_qty;
			}
			public static function get_sp_ticket(array $ids = [], bool $count = false) {
				if (empty($ids)) {
					return $count ? 0 : [];
				}
				$tickets = [];
				$total = 0;
				foreach ($ids as $id) {
					$seat_infos = ABPTB_Ticket_SP[$id] ?? null;
					if (empty($seat_infos)) {
						continue;
					}
					if ($count) {
						$total += $seat_infos['total'] ?? 0;
					} else {
						$seat_info = $seat_infos['type'] ?? [];
						if (!empty($seat_info)) {
							$tickets = array_merge($tickets, array_keys($seat_info));
						}
					}
				}
				return $count ? $total : array_values(array_unique($tickets));
			}
			//=============================//
			public static function client_data($post_infos = []) {
				$data = [];
				if (ABPTB_Function::on_off('client_info') && !empty($post_infos)) {
					$display = $post_infos['display_client_form'] ?? 'on';
					if ($display === 'on') {
						$active_global = $post_infos['active_global_form'] ?? 'on';
						if ($active_global === 'on') {
							$data = ABPTB_Function::get_option('abptb_forms');
						} else {
							$data = $post_infos['abptb_forms'] ?? [];
						}
					}
				}
				return $data;
			}
			public static function additional_data($post_infos = []) {
				$data = [];
				if (ABPTB_Function::on_off('additional_info') && !empty($post_infos)) {
					$display = $post_infos['display_additional_services'] ?? 'on';
					if ($display === 'on') {
						$active_global = $post_infos['active_global_additional'] ?? 'on';
						if ($active_global === 'on') {
							$data = ABPTB_Function::get_option('abptb_additional');
						} else {
							$data = $post_infos['additional_services'] ?? [];
						}
					}
				}
				return $data;
			}
			//=============================//
			public static function related_info_js($_post_id = ''): array {
				$all_info = [];
				if (!empty($_post_id) && $_post_id > 0) {
					if (!empty(ABPTB_ids)) {
						$active_icon = ABPTB_Function::on_off('post_icon');
						foreach (ABPTB_ids as $post_id) {
							if ($post_id !== $_post_id) {
								$icon = $active_icon ? ABPTB_Function::get_post_info($post_id, 'post_icon') : '';
								$all_info[] = ['id' => $post_id, 'icon' => $icon, 'label' => get_the_title($post_id)];
							}
						}
					}
				}
				return $all_info;
			}
			public static function location_info_js(): array {
				$all_info = [];
				$all_location = ABPTB_Function::get_option('abptb_location');
				if (!empty($all_location)) {
					foreach ($all_location as $key => $location) {
						$name = $location['name'] ?? '';
						if ($name) {
							$all_info[] = ['id' => $key, 'label' => $name];
						}
					}
				}
				return $all_info;
			}
		}
		new ABPTB_Function();
	}