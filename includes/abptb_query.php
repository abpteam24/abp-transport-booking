<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly
	if (!class_exists('ABPTB_Query')) {
		class ABPTB_Query {
			public function __construct() {
			}
			public static function get_info() {
				global $wpdb;
				$cache_key = 'abptb_info';
				$abptb_info = wp_cache_get($cache_key);
				if (false !== $abptb_info) {
					return $abptb_info;
				}
				$order_table = $wpdb->prefix . 'abptb_orders';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$total_order = (int)$wpdb->get_var(
					$wpdb->prepare("SELECT COUNT(*) FROM %i", $order_table)
				);
				$abptb_info = array();
				$post_ids = self::get_post_id(['status' => ['publish', 'draft', 'private', 'trash']]);
				$post_counts = wp_count_posts(ABPTB_Function::get_cpt());
				$total_publish = $post_counts->publish ?? 0;
				$total_draft = $post_counts->draft ?? 0;
				$total_private = $post_counts->private ?? 0;
				$total_trash = $post_counts->trash ?? 0;
				$abptb_info['post_ids'] = $post_ids;
				$abptb_info['total_post'] = sizeof($post_ids);
				$abptb_info['total_publish'] = $total_publish;
				$abptb_info['total_draft'] = $total_draft;
				$abptb_info['total_private'] = $total_private;
				$abptb_info['total_trash'] = $total_trash;
				$abptb_info['total_order'] = $total_order;
				wp_cache_set($cache_key, $abptb_info);
				return $abptb_info;
			}
			public static function query_post_type($post_type, $show = -1, $page = 1): WP_Query {
				$args = array(
					'post_type' => $post_type,
					'posts_per_page' => $show,
					'paged' => $page,
					'post_status' => 'publish'
				);
				return new WP_Query($args);
			}
			public static function get_post_id($filters = []): array {
				$post_type = ($filters['cpt'] ?? null) ?: ABPTB_Function::get_cpt();
				$show = ($filters['posts_per_page'] ?? null) ?: -1;
				$page = ($filters['paged'] ?? null) ?: 1;
				$status = ($filters['status'] ?? null) ?: 'publish';
				$cat_id = $filters['cat_id'] ?? null;
				$loc_id = $filters['loc_id'] ?? null;
				$brand_id = $filters['brand_id'] ?? null;
				$org_id = $filters['org_id'] ?? null;
				$bp_dp = $filters['bp_dp'] ?? null;
				$bp = $filters['bp'] ?? null;
				$dp = $filters['dp'] ?? null;
				if(!empty($bp) && !empty($dp)) {
					$bp_dp=$bp.'_'.$dp;
				}
				$meta_query = ['relation' => 'AND'];
				// Category query
				if (!empty($cat_id)) {
					$meta_query[] = ['key' => 'abptb_category', 'value' => $cat_id, 'compare' => '='];
				}
				if (!empty($brand_id)) {
					$meta_query[] = ['key' => 'abptb_brand', 'value' => $brand_id, 'compare' => '='];
				}
				if (!empty($org_id)) {
					$meta_query[] = ['key' => 'abptb_organizer', 'value' => $org_id, 'compare' => '='];
				}
				// route query
				if (!empty($bp_dp)) {
					$meta_query[] = ['key' => 'route_data', 'value' => '"' . $bp_dp . '"', 'compare' => 'LIKE'];
				}
				if (!empty($loc_id)) {
					$meta_query[] = ['key' => 'route_direction', 'value' => '"' . $loc_id . '"', 'compare' => 'LIKE'];
				}
				$all_data = get_posts(array(
					'fields' => 'ids',
					'post_type' => $post_type,
					'posts_per_page' => $show,
					'paged' => $page,
					'post_status' => $status,
					'meta_query' => $meta_query
				));
				return array_unique($all_data);
			}
			public static function get_booking_query($filters = array(), $limit = 0, $offset = 0, $count = false) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'abptb_orders';
				$cache_key = 'abptb_bk_' . md5(wp_json_encode($filters) . $limit . $offset . (int)$count);
				$cache_group = 'abptb_orders';
				$cached = wp_cache_get($cache_key, $cache_group);
				if (false !== $cached) {
					return $cached;
				}
				$conditions = array();
				$params = array();
				// Order Status Filter
				$status = !empty($filters['status']) ? sanitize_text_field($filters['status']) : null;
				$booked_status = $status ?: ABPTB_Function::booking_status();
				$booked_status = $booked_status ? explode(',', $booked_status) : array();
				$is_all_status = (!empty($booked_status) && current($booked_status) === 'all');
				if (!empty($booked_status) && !$is_all_status) {
					$placeholders = implode(',', array_fill(0, count($booked_status), '%s'));
					$conditions[] = "order_status IN ($placeholders)";
					$params = array_merge($params, $booked_status);
				}
				// Integer ID Filters
				$int_keys = array('id', 'post_id', 'user_id', 'item_id', 'order_id', 'start_point', 'sp_id');
				foreach ($int_keys as $key) {
					if (!empty($filters[$key])) {
						$conditions[] = "{$key} = %d";
						$params[] = absint($filters[$key]);
					}
				}
				// Start Time Filter (Fixed Y-m-d format for DATE() comparison)
				if (!empty($filters['start_time'])) {
					$timestamp = strtotime($filters['start_time']);
					if (gmdate('H:i', $timestamp) !== '00:00') {
						$params[] = gmdate('Y-m-d H:i', $timestamp);
						$conditions[] = "DATE_FORMAT(start_time, '%%Y-%%m-%%d %%H:%%i') = %s";
					} else {
						$params[] = gmdate('Y-m-d', $timestamp);
						$conditions[] = 'DATE(start_time) = %s';
					}
				}
				if (!empty($filters['_start_time'])) {
					$timestamp = strtotime($filters['_start_time']);
					$params[] = gmdate('Y-m-d', $timestamp);
					$conditions[] = 'DATE(start_time) = %s';
				}
				// Route Directions (BP & DP dynamically handled)
				if (!empty($filters['bp_dp'])) {
					$post_id = !empty($filters['post_id']) ? absint($filters['post_id']) : 0;
					$start_time = !empty($filters['start_time']) ? sanitize_text_field($filters['start_time']) : '';
					$bp_dp_parts = explode('_', sanitize_text_field($filters['bp_dp']));
					if (count($bp_dp_parts) >= 2 && $post_id && $start_time) {
						$bp = intval($bp_dp_parts[0]);
						$dp = intval($bp_dp_parts[1]);
						$routes = ABPTB_Function::get_post_info($post_id, 'return_route_direction', array());
						if (!empty($routes) && is_array($routes)) {
							$sp = array_search($bp, $routes, false);
							$ep = array_search($dp, $routes, false);
							if (false !== $sp && false !== $ep) {
								$valid_bps = array_slice($routes, 0, $ep);
								$valid_dps = array_slice($routes, $sp + 1);
								if (!empty($valid_bps) && !empty($valid_dps)) {
									$bp_placeholders = implode(',', array_fill(0, count($valid_bps), '%s'));
									$dp_placeholders = implode(',', array_fill(0, count($valid_dps), '%s'));
									$conditions[] = "bp IN ({$bp_placeholders})";
									$params = array_merge($params, $valid_bps); // FIXED ARRAY MERGE
									$conditions[] = "dp IN ({$dp_placeholders})";
									$params = array_merge($params, $valid_dps); // FIXED ARRAY MERGE
								}
							}
						}
					}
				} else {
					// Single BP / DP filters fallback (if bp_dp is not present)
					if (!empty($filters['bp']) || !empty($filters['_bp'])) {
						$bp_val = !empty($filters['bp']) ? $filters['bp'] : $filters['_bp'];
						$conditions[] = 'bp = %d';
						$params[] = absint($bp_val);
					}
					if (!empty($filters['dp']) || !empty($filters['_dp'])) {
						$dp_val = !empty($filters['dp']) ? $filters['dp'] : $filters['_dp'];
						$conditions[] = 'dp = %d';
						$params[] = absint($dp_val);
					}
					if (!empty($filters['_bp_dp'])) {
						$conditions[] = "bp_dp LIKE %s";
						$params[] = '%' . $wpdb->esc_like(sanitize_text_field($filters['_bp_dp'])) . '%';
					}
				}
				// JSON Fields
				if (!empty($filters['ticket_id'])) {
					$conditions[] = 'JSON_CONTAINS(ticket_id, %s)';
					$params[] = wp_json_encode(sanitize_text_field($filters['ticket_id']));
				}
				if (!empty($filters['ex_id'])) {
					$conditions[] = 'JSON_CONTAINS(ex_id, %s)';
					$params[] = wp_json_encode(sanitize_text_field($filters['ex_id']));
				}
				// Date Range Filters
				if (!empty($filters['order_date'])) {
					$conditions[] = 'DATE(created_at) = %s';
					$params[] = gmdate('Y-m-d', strtotime($filters['order_date']));
				}
				if (!empty($filters['start_time_from']) && !empty($filters['start_time_to'])) {
					$conditions[] = 'DATE(start_time) BETWEEN %s AND %s';
					$params[] = gmdate('Y-m-d', strtotime($filters['start_time_from']));
					$params[] = gmdate('Y-m-d', strtotime($filters['start_time_to']));
				}
				if (!empty($filters['order_date_from']) && !empty($filters['order_date_to'])) {
					$conditions[] = 'DATE(created_at) BETWEEN %s AND %s';
					$params[] = gmdate('Y-m-d', strtotime($filters['order_date_from']));
					$params[] = gmdate('Y-m-d', strtotime($filters['order_date_to']));
				}
				// Billing Info (LIKE search)
				$like_keys = array('billing_name', 'billing_email', 'billing_phone');
				foreach ($like_keys as $like_key) {
					if (!empty($filters[$like_key])) {
						$conditions[] = "{$like_key} LIKE %s";
						$params[] = '%' . $wpdb->esc_like(sanitize_text_field($filters[$like_key])) . '%';
					}
				}
				// SQL Query Assembly
				$select = $count ? 'SELECT COUNT(*)' : 'SELECT *';
				$sql = "{$select} FROM {$table_name}";
				if (!empty($conditions)) {
					$sql .= ' WHERE ' . implode(' AND ', $conditions);
				}
				if (!$count) {
					$allowed_columns = array('id', 'post_id', 'order_id', 'status', 'created_at');
					$raw_order_by = !empty($filters['order_by']) ? sanitize_key($filters['order_by']) : 'order_id';
					$order_by = in_array($raw_order_by, $allowed_columns, true) ? $raw_order_by : 'order_id';
					$order_dir = (!empty($filters['order_dir']) && strtoupper($filters['order_dir']) === 'ASC') ? 'ASC' : 'DESC';
					$sql .= " ORDER BY {$order_by} {$order_dir}";
					if ($limit > 0) {
						$sql .= ' LIMIT %d OFFSET %d';
						$params[] = absint($limit);
						$params[] = absint($offset);
					}
				}
				if ($count) {
					if (!empty($params)) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						$results = $wpdb->get_var(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
							$wpdb->prepare($sql, ...$params)
						);
					} else {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$results = $wpdb->get_var($sql);
					}
				} else {
					if (!empty($params)) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						$results = $wpdb->get_results(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
							$wpdb->prepare($sql, ...$params),
							ARRAY_A
						);
					} else {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$results = $wpdb->get_results($sql, ARRAY_A);
					}
				}
				$results = $results ?: ($count ? 0 : array());
				wp_cache_set($cache_key, $results, $cache_group, 30);
				return $results;
			}
			public static function get_sold_qty_ex($filters = []) {
				$sold_qty = 0;
				$booking_items = self::get_booking_query($filters);
				if (empty($booking_items)) {
					return $sold_qty;
				}
				$id = $filters['ex_id'] ?? '';
				foreach ($booking_items as $booking_item) {
					$ex_ids = json_decode($booking_item['ex_id'] ?? '', true) ?: [];
					$additional_infos = json_decode($booking_item['ex_info'] ?? '', true) ?: [];
					if (!empty($id)) {
						if (in_array($id, $ex_ids, true) && isset($additional_infos[$id])) {
							$sold_qty += $additional_infos[$id]['qty'] ?? 1;
						}
					} else {
						foreach ($additional_infos as $additional_info) {
							$sold_qty += $additional_info['qty'] ?? 1;
						}
					}
				}
				return $sold_qty;
			}
			public static function get_sp($id = '', $count = false) {
				global $wpdb;
				$cache_key = 'abptb_sp_' . md5($id . ($count ? '_count' : '_all'));
				$abptb_sp = wp_cache_get($cache_key);
				if (false !== $abptb_sp) {
					return $abptb_sp;
				}
				$table_name = $wpdb->prefix . 'abptb_sp';
				if ($count) {
					if (!empty($id)) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe table name variable; $id is prepared.
						$results = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE id = %d", (int)$id));
					} else {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe table name variable with no user input.
						$results = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
					}
				} else {
					if (!empty($id)) {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe table name variable; $id is prepared.
						$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d ORDER BY id ASC", (int)$id), ARRAY_A);
					} else {
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Safe table name variable with no user input.
						$results = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY id ASC", ARRAY_A);
					}
				}
				wp_cache_set($cache_key, $results);
				return $results;
			}
			public static function get_sold_ticket($filters = []): array {
				$sold_qty = [];
				$booking_items = self::get_booking_query($filters);
				if (empty($booking_items)) {
					return $sold_qty;
				}
				foreach ($booking_items as $booking_item) {
					$ticket_infos = json_decode($booking_item['ticket_info'] ?? '', true) ?: [];
					if (!empty($ticket_infos)) {
						foreach ($ticket_infos as $ticket_info) {
							if (!empty($ticket_info)) {
								$qty = $ticket_info ['qty'] ?? 1;
								$id = $ticket_info ['id'] ?? 'price';
								$sold_qty [$id] = ($sold_qty [$id] ?? 0) + $qty;
								$sold_qty ['total'] = ($sold_qty ['total'] ?? 0) + $qty;
							}
						}
					}
				}
				return $sold_qty;
			}
			public static function get_sold_seat($filters = []): array {
				$sold_seats = [];
				$booking_items = self::get_booking_query($filters);
				if (empty($booking_items)) {
					return $sold_seats;
				}
				foreach ($booking_items as $booking_item) {
					$ticket_infos = json_decode($booking_item['ticket_info'] ?? '', true) ?: [];
					if (!empty($ticket_infos)) {
						foreach ($ticket_infos as $ticket_info) {
							if (!empty($ticket_info)) {
								$sold_seats [] = $ticket_info ['name'] ?? '';
							}
						}
					}
				}
				return array_values(array_unique($sold_seats));
			}
		}
		new ABPTB_Query();
	}