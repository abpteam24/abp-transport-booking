<?php
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	if (!class_exists('ABPTB_My_Account')) {
		class ABPTB_My_Account {
			const ENDPOINT = 'abptb-bookings';
			public function __construct() {
				add_action('init', array($this, 'register_endpoint'));
				add_filter('woocommerce_get_query_vars', array($this, 'query_vars'));
				add_filter('woocommerce_account_menu_items', array($this, 'menu_items'), 20);
				add_action('woocommerce_account_' . self::ENDPOINT . '_endpoint', array($this, 'render_bookings'));
				add_action('admin_init', array($this, 'maybe_flush_rules'));
			}
			public function register_endpoint(): void {
				add_rewrite_endpoint(self::ENDPOINT, EP_ROOT | EP_PAGES);
			}
			public function query_vars($vars) {
				$vars[self::ENDPOINT] = self::ENDPOINT;
				return $vars;
			}
			public function menu_items($items) {
				$slug = self::ENDPOINT;
				$menu = array();
				foreach ($items as $key => $value) {
					$menu[$key] = $value;
					if ('orders' === $key) {
						$menu[$slug] = __('Transport Bookings', 'abp-transport-booking');
					}
				}
				if ('dashboard' === $slug || !array_key_exists($slug, $menu)) {
					$menu[$slug] = __('Transport Bookings', 'abp-transport-booking');
				}
				return $menu;
			}
			public function maybe_flush_rules(): void {
				if (!get_option('abptb_my_account_flushed')) {
					$this->register_endpoint();
					flush_rewrite_rules(false);
					update_option('abptb_my_account_flushed', 1);
				}
			}
			public function render_bookings(): void {
				$user_id = get_current_user_id();
				?>
				<div class="abptb_area abptb_my_account">
					<style>
						.abptb_my_account ._section_card {border: 1px solid var(--tb_color_border);border-radius: var(--tb_br);padding: var(--tb_gap_xs);}
						.abptb_my_account ul.abp li {display: -webkit-flex;display: flex;-webkit-flex-wrap: wrap;flex-wrap: wrap;gap: var(--tb_gap_xxs);-webkit-align-items: baseline;align-items: baseline;}
						.abptb_my_account ul.abp li strong {min-width: 150px;display: inline-block;}
						.abptb_my_account .abp_tag {background: var(--tb_color_light);border: 1px solid var(--tb_color_border);border-radius: 50px;color: var(--tb_color_default);font-size: var(--tb_fs_small);padding: var(--tb_gap_xxs) var(--tb_gap_xs);white-space: nowrap;}
						.abptb_my_account ._pagination {display: -webkit-flex;display: flex;gap: var(--tb_gap_xxs);-webkit-flex-wrap: wrap;flex-wrap: wrap;}
						.abptb_my_account ._page_link {min-width: 36px;height: 36px;border: 1px solid var(--tb_color_border);background: var(--tb_color_white);color: var(--tb_color_default);border-radius: var(--tb_br);display: -webkit-inline-flex;display: inline-flex;-webkit-align-items: center;align-items: center;-webkit-justify-content: center;justify-content: center;text-decoration: none;padding: 0 var(--tb_gap_xs);}
						.abptb_my_account ._page_link:hover {border-color: var(--tb_color_theme);color: var(--tb_color_theme);}
						.abptb_my_account ._page_active {background: var(--tb_color_theme);border-color: var(--tb_color_theme);color: var(--tb_color_white);}
					</style>
					<h4><?php echo esc_html__('My Transport Bookings', 'abp-transport-booking'); ?></h4>
					<div class="_divider_xs"></div>
					<?php
					if (!$user_id) {
						echo '<p>' . esc_html__('Please login to see your bookings.', 'abp-transport-booking') . '</p>';
						return;
					}
					$limit = absint(ABPTB_Function::get_option('abptb_per_page_item', 20));
					if ($limit < 1) {
						$limit = 20;
					}
					$page = isset($_GET['abptb_page']) ? absint($_GET['abptb_page']) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					if ($page < 1) {
						$page = 1;
					}
					$offset = ($page - 1) * $limit;
					$filters = array(
						'user_id' => $user_id,
						'status' => 'all',
						'order_by' => 'created_at',
						'order_dir' => 'DESC',
						'cache' => false,
					);
					$total = ABPTB_Query::get_booking_query($filters, 0, 0, true);
					$bookings = ABPTB_Query::get_booking_query($filters, $limit, $offset);
					if (!empty($bookings) && is_array($bookings)) {
						foreach ($bookings as $booking_item) {
							$this->booking_card($booking_item);
						}
						$this->pagination($total, $limit, $page);
					} else {
						echo '<p>' . esc_html__('You have no transport bookings yet.', 'abp-transport-booking') . '</p>';
					}
					?>
				</div>
				<?php
			}
			private function booking_card($booking_item): void {
				$post_id = absint($booking_item['post_id'] ?? 0);
				$order_status = $booking_item['order_status'] ?? '';
				$created_at = $booking_item['created_at'] ?? '';
				$bp = $booking_item['bp'] ?? '';
				$dp = $booking_item['dp'] ?? '';
				$others = json_decode($booking_item['others'] ?? '', true) ?: array();
				$duration = $others['duration'] ?? '';
				$total = (float)($booking_item['total'] ?? 0);
				$additional_infos = json_decode($booking_item['ex_info'] ?? '', true) ?: array();
				?>
				<div class="_section_card _mar_b">
					<div class="_fj_between">
						<div>
							<h5 style="margin:0 0 4px;"><?php ABPTB_Layout::title(array('post_id' => $post_id)); ?></h5>
							<p style="margin:0;" class="abp_color_gray">#<?php echo esc_html($booking_item['order_id'] ?? ''); ?> - <?php echo esc_html(ABPTB_Function::date_format($created_at)); ?></p>
						</div>
						<span class="abp_tag"><?php echo esc_html(ABPTB_Layout::status_text($order_status)); ?></span>
					</div>
					<div class="_divider_xs"></div>
					<ul class="abp">
						<li><strong><?php esc_html_e('From - To', 'abp-transport-booking'); ?></strong> <?php ABPTB_Layout::route_direction(array('post_id' => $post_id), $bp . '_' . $dp, false, false); ?></li>
						<?php if (!empty($bp)) { ?>
							<li><strong><?php esc_html_e('Boarding Point', 'abp-transport-booking'); ?></strong> <?php echo esc_html(ABPTB_Function::location_value($bp)); ?> - <?php echo esc_html(ABPTB_Function::date_format($booking_item['bp_time'] ?? '')); ?></li>
						<?php } ?>
						<?php if (!empty($dp)) { ?>
							<li><strong><?php esc_html_e('Dropping Point', 'abp-transport-booking'); ?></strong> <?php echo esc_html(ABPTB_Function::location_value($dp)); ?> - <?php echo esc_html(ABPTB_Function::date_format($booking_item['dp_time'] ?? '')); ?></li>
						<?php } ?>
						<?php if (!empty($duration)) { ?>
							<li><strong><?php esc_html_e('Approximate Time', 'abp-transport-booking'); ?></strong> <?php echo esc_html($duration); ?></li>
						<?php } ?>
						<?php if (!empty($booking_item['seat_no'])) { ?>
							<li><strong><?php esc_html_e('Seat', 'abp-transport-booking'); ?></strong> <?php echo esc_html($booking_item['seat_no']); ?></li>
						<?php } ?>
					</ul>
					<?php if (!empty($booking_item['ticket_info'])) { ?>
						<div class="_divider_xs"></div>
						<div class="_group_content">
							<?php ABPTB_Layout::ticket_info($booking_item); ?>
						</div>
					<?php } ?>
					<?php if (ABPTB_Function::on_off('additional_info') && !empty($additional_infos)) { ?>
						<div class="_divider_xs"></div>
						<div class="_group_content">
							<?php ABPTB_Layout::additional_info($additional_infos); ?>
						</div>
					<?php } ?>
					<div class="_divider_xs"></div>
					<p style="margin:0;"><strong><?php esc_html_e('Total', 'abp-transport-booking'); ?></strong> : <?php echo $total > 0 ? wp_kses_post(wc_price($total)) : esc_html__('FREE', 'abp-transport-booking'); ?></p>
					<div class="_divider_xs"></div>
					<div class="_fj_start _f_wrap_gap_xxs">
						<?php do_action('abptb_my_account_booking_actions', $booking_item); ?>
					</div>
				</div>
				<?php
			}
			private function pagination($total, $limit, $page): void {
				if (empty($limit) || (int)$total <= (int)$limit) {
					return;
				}
				$pages = (int)ceil((int)$total / (int)$limit);
				if ($pages < 2) {
					return;
				}
				$base = wc_get_account_endpoint_url(self::ENDPOINT);
				?>
				<div class="_fj_center _mar_b">
					<div class="_pagination">
						<?php for ($i = 1; $i <= $pages; $i++) {
							$is_current = ($i === (int)$page);
							$url = (1 === $i) ? $base : add_query_arg('abptb_page', $i, $base); ?>
							<?php if ($is_current) { ?>
								<span class="_page_link _page_active"><?php echo esc_html($i); ?></span>
							<?php } else { ?>
								<a class="_page_link" href="<?php echo esc_url($url); ?>"><?php echo esc_html($i); ?></a>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
				<?php
			}
		}
		new ABPTB_My_Account();
	}