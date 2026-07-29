<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly
	if (!class_exists('ABPTF_Query')) {
		class ABPTF_Query {
			public function __construct() {
			}
			public static function get_info() {
				global $wpdb;
				$cache_key = 'abptf_info';
				$abptf_info = wp_cache_get($cache_key);
				if (false !== $abptf_info) {
					return $abptf_info;
				}
				$order_table = $wpdb->prefix . 'abptf_orders';
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$total_order = (int)$wpdb->get_var(
					$wpdb->prepare("SELECT COUNT(*) FROM %i", $order_table)
				);
				$abptf_info = array();
				$post_ids = self::get_post_id(['status' => ['publish', 'draft', 'private', 'trash']]);
				$post_counts = wp_count_posts(ABPTF_Function::get_cpt());
				$total_publish = $post_counts->publish ?? 0;
				$total_draft = $post_counts->draft ?? 0;
				$total_private = $post_counts->private ?? 0;
				$total_trash = $post_counts->trash ?? 0;
				$abptf_info['post_ids'] = $post_ids;
				$abptf_info['total_post'] = sizeof($post_ids);
				$abptf_info['total_publish'] = $total_publish;
				$abptf_info['total_draft'] = $total_draft;
				$abptf_info['total_private'] = $total_private;
				$abptf_info['total_trash'] = $total_trash;
				$abptf_info['total_order'] = $total_order;
				wp_cache_set($cache_key, $abptf_info);
				return $abptf_info;
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
				$post_type = ($filters['cpt'] ?? null) ?: ABPTF_Function::get_cpt();
				$show = ($filters['posts_per_page'] ?? null) ?: -1;
				$page = ($filters['paged'] ?? null) ?: 1;
				$status = ($filters['status'] ?? null) ?: 'publish';
				$cat_id = $filters['cat_id'] ?? null;
				$bp_dp = $filters['bp_dp'] ?? null;
				$meta_query = ['relation' => 'AND'];
				// Category query
				if (!empty($cat_id)) {
					$meta_query[] = ['key' => 'abptf_category', 'value' => $cat_id, 'compare' => '='];
				}
				// route query
				if (!empty($bp_dp)) {
					$meta_query[] = ['key' => 'route_data', 'value' => '"' . $bp_dp . '"', 'compare' => 'LIKE'];
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
				$table_name = $wpdb->prefix . 'abptf_orders';
				$cache_key = 'abptf_bk_' . md5(wp_json_encode($filters) . $limit . $offset . $count);
				$cache_group = 'abptf_orders';
				$cached = wp_cache_get($cache_key, $cache_group);
				if (false !== $cached) {
					return $cached;
				}
				$conditions = [];
				$params = [];
				$status = !empty($filters['status']) ? sanitize_text_field($filters['status']) : null;
				$booked_status = $status ?: ABPTF_Function::booking_status();
				$booked_status = $booked_status ? explode(',', $booked_status) : [];
				$is_all_status = (!empty($booked_status) && current($booked_status) === 'all');
				if (!empty($booked_status) && !$is_all_status) {
					$placeholders = implode(',', array_fill(0, count($booked_status), '%s'));
					$conditions[] = "order_status IN ($placeholders)";
					$params = array_merge($params, $booked_status);
				}
				if (!empty($filters['post_id'])) {
					$conditions[] = "post_id = %d";
					$params[] = intval($filters['post_id']);
				}
				if (!empty($filters['user_id'])) {
					$conditions[] = "user_id = %d";
					$params[] = intval($filters['user_id']);
				}
				if (!empty($filters['item_id'])) {
					$conditions[] = "item_id = %d";
					$params[] = intval($filters['item_id']);
				}
				if (!empty($filters['order_id'])) {
					$conditions[] = "order_id = %d";
					$params[] = intval($filters['order_id']);
				}
				if (!empty($filters['property_id'])) {
					$conditions[] = "JSON_CONTAINS(property_id, %s)";
					$params[] = wp_json_encode(intval($filters['property_id']));
				}
				if (!empty($filters['ex_id'])) {
					$conditions[] = "JSON_CONTAINS(ex_id, %s)";
					$params[] = wp_json_encode(sanitize_text_field($filters['ex_id']));
				}
				if (!empty($filters['location'])) {
					$conditions[] = "JSON_CONTAINS(location, %s)";
					$params[] = wp_json_encode(intval($filters['location']));
				}
				$start_time = !empty($filters['start_time']) ? gmdate('Y-m-d H:i:s', strtotime($filters['start_time'])) : null;
				$end_time = !empty($filters['end_time']) ? gmdate('Y-m-d H:i:s', strtotime($filters['end_time'])) : null;
				$order_date = !empty($filters['order_date']) ? gmdate('Y-m-d', strtotime($filters['order_date'])) : null;
				$booking_time_from = !empty($filters['booking_time_from']) ? gmdate('Y-m-d', strtotime($filters['booking_time_from'])) : null;
				$booking_time_to = !empty($filters['booking_time_to']) ? gmdate('Y-m-d', strtotime($filters['booking_time_to'])) : null;
				$order_time_from = !empty($filters['order_date_from']) ? gmdate('Y-m-d', strtotime($filters['order_date_from'])) : null;
				$order_time_to = !empty($filters['order_date_to']) ? gmdate('Y-m-d', strtotime($filters['order_date_to'])) : null;
				$billing_name = !empty($filters['billing_name']) ? '%' . sanitize_text_field($filters['billing_name']) . '%' : null;
				$billing_email = !empty($filters['billing_email']) ? '%' . sanitize_text_field($filters['billing_email']) . '%' : null;
				$billing_phone = !empty($filters['billing_phone']) ? '%' . sanitize_text_field($filters['billing_phone']) . '%' : null;
				if (!empty($start_time) && !empty($end_time)) {
					$conditions[] = "(book_from < %s AND book_to > %s)";
					$params[] = $end_time;
					$params[] = $start_time;
				} else {
					if (!empty($start_time)) {
						$conditions[] = "DATE(start_time) = %s ";
						$params[] = $start_time;
					}
				}
				if (!empty($order_date)) {
					$conditions[] = "DATE(created_at) = %s ";
					$params[] = $order_date;
				}
				if (!empty($booking_time_from) && !empty($booking_time_to)) {
					$conditions[] = "DATE(start_time) BETWEEN %s AND %s";
					$params[] = $booking_time_from;
					$params[] = $booking_time_to;
				}
				if (!empty($order_time_from) && !empty($order_time_to)) {
					$conditions[] = "DATE(created_at) BETWEEN %s AND %s";
					$params[] = $order_time_from;
					$params[] = $order_time_to;
				}
				if (!empty($billing_name)) {
					$conditions[] = "billing_name LIKE %s";
					$params[] = $billing_name;
				}
				if (!empty($billing_email)) {
					$conditions[] = "billing_email LIKE %s";
					$params[] = $billing_email;
				}
				if (!empty($billing_phone)) {
					$conditions[] = "billing_phone LIKE %s";
					$params[] = $billing_phone;
				}
				$select = $count ? "SELECT COUNT(*)" : "SELECT *";
				$sql = "$select FROM %i";
				$query_args = [$table_name];
				if (!empty($conditions)) {
					$sql .= " WHERE " . implode(" AND ", $conditions);
					$query_args = array_merge($query_args, $params);
				}
				$allowed_columns = ['id', 'post_id', 'order_id', 'status', 'created_at'];
				$order_by = sanitize_sql_orderby($filters['order_by'] ?? 'order_id');
				$order_by = in_array($order_by, $allowed_columns, true) ? $order_by : 'order_id';
				$order_dir = strtoupper($filters['order_dir'] ?? 'ASC') === 'ASC' ? 'ASC' : 'DESC';
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$sql .= " ORDER BY $order_by $order_dir";
				if ($limit > 0) {
					$sql .= " LIMIT %d OFFSET %d";
					$query_args[] = (int)$limit;
					$query_args[] = (int)$offset;
				}
				if ($count) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$results = $wpdb->get_var(
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$wpdb->prepare($sql, ...$query_args)
					);
				} else {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$results = $wpdb->get_results(
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$wpdb->prepare($sql, ...$query_args),
						ARRAY_A
					);
				}
				$results = $results ?: ($count ? 0 : []);
				wp_cache_set($cache_key, $results, $cache_group, 30);
				return $results;
			}
			public static function get_sold_qty($filters = []) {
				$sold_qty = 0;
				$booking_lists = self::get_booking_query($filters);
				if (empty($booking_lists)) {
					return $sold_qty;
				}
				$id = $filters['property_id'] ?? '';
				foreach ($booking_lists as $booking_list) {
					$property_ids = json_decode($booking_list['property_id'] ?? '', true) ?: [];
					$ticket_infos = json_decode($booking_list['property_info'] ?? '', true) ?: [];
					if (!empty($id)) {
						if (in_array($id, $property_ids, true) && isset($ticket_infos[$id])) {
							$sold_qty += $ticket_infos[$id]['qty'] ?? 1;
						}
					} else {
						foreach ($ticket_infos as $ticket_info) {
							$sold_qty += $ticket_info['qty'] ?? 1;
						}
					}
				}
				return $sold_qty;
			}
			public static function get_sold_qty_ex($filters = []) {
				$sold_qty = 0;
				$booking_lists = self::get_booking_query($filters);
				if (empty($booking_lists)) {
					return $sold_qty;
				}
				$id = $filters['ex_id'] ?? '';
				foreach ($booking_lists as $booking_list) {
					$ex_ids = json_decode($booking_list['ex_id'] ?? '', true) ?: [];
					$additional_infos = json_decode($booking_list['ex_info'] ?? '', true) ?: [];
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
				$table_name = $wpdb->prefix . 'abptf_sp';
				$cache_key = 'abptf_sp_' . md5((string)$id . ($count ? '_count' : '_all'));
				$abptf_sp = wp_cache_get($cache_key);
				if (false !== $abptf_sp) {
					return $abptf_sp;
				}
				$conditions = [];
				$params = [];
				if (!empty($id)) {
					$conditions[] = "id = %d";
					$params[] = (int)$id;
				}
				$select = $count ? "COUNT(*)" : "*";
				$where = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$sql = "SELECT $select FROM $table_name $where ORDER BY id ASC";
				if (!empty($params)) {
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$final_query = $wpdb->prepare($sql, ...$params);
				} else {
					$final_query = $sql;
				}
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				if ($count) {
					$results = $wpdb->get_var($final_query);
				} else {
					$results = $wpdb->get_results($final_query, ARRAY_A);
				}
				wp_cache_set($cache_key, $results);
				return $results;
			}
		}
		new ABPTF_Query();
	}