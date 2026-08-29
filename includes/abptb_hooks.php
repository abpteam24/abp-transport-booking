<?php
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	if (!class_exists('ABPTB_Hooks')) {
		class ABPTB_Hooks {
			public function __construct() {
				add_action('abptb_load_details_template', [$this, 'details_template']);
				add_action('abptb_search_form', [$this, 'search_form'], 10, 2);
				add_action('abptb_post_filter', [$this, 'post_filter'], 10, 2);
				add_action('abptb_ticket_type', [$this, 'ticket_type'], 10, 3);
				add_action('abptb_sp_type', [$this, 'sp_type'], 10, 3);
				add_action('abptb_registration', [$this, 'registration'], 10, 2);
				add_action('abptb_registration_item', [$this, 'registration_item'], 10, 3);
				add_action('abptb_selection_details', [$this, 'selection_details'], 10, 3);
				add_action('abptb_additional', [$this, 'additional'], 10, 2);
				add_action('abptb_client_form', [$this, 'client_form'], 10, 2);
				add_action('abptb_total_price', [$this, 'total_price'], 10, 2);
				add_action('abptb_pagination', [$this, 'pagination']);
				add_action('abptb_display_cart_item', [$this, 'display_cart_item']);
				add_action('abptb_faq', [$this, 'faq'], 10, 2);
				add_action('abptb_term_condition', [$this, 'term_condition'], 10, 2);
				add_action('abptb_related_item', [$this, 'related_item'], 10, 2);
				add_action('abptb_slider', [$this, 'slider'], 10, 3);
				add_action('abptb_slider_popup', [$this, 'slider_popup'], 10, 3);
			}
			public function details_template($post_id): void {
				require_once ABPTB_Function::details_template_path($post_id);
				$template_name = ABPTB_Function::get_post_info($post_id, 'abptb_template', 'default');
				do_action('abptb_details_' . $template_name . '_template', $post_id);
			}
			public function search_form($post_infos = []): void {
				include_once ABPTB_Function::template_path('layout/search_form.php');
				do_action('abptb_search_form_template', $post_infos);
			}
			public function post_filter($params = []): void {
				include_once ABPTB_Function::template_path('layout/post_filter.php');
				do_action('abptb_post_filter_template', $params);
			}

			public function ticket_type($post_infos, $form_data = [], $prefix = ''): void {
				include_once ABPTB_Function::template_path('layout/ticket_type.php');
				do_action('abptb_ticket_type_template', $post_infos, $form_data, $prefix);
			}
			public function sp_type($post_infos, $form_data = [], $prefix = ''): void {
				include_once ABPTB_Function::template_path('layout/sp_type.php');
				do_action('abptb_sp_type_template', $post_infos, $form_data, $prefix);
			}
			public function registration($post_infos = [], $form_data = []): void {
				if (!empty($post_infos)) {
					$sale_continue = $post_infos['sale_continue'] ?? 'on';
					$seat_type = $post_infos['seat_type'] ?? 'sp';
					$seat_type = ABPTB_Function::on_off('sp') ? $seat_type : 'ticket';
					$post_id = absint($post_infos['post_id'] ?? 0);
					$form_data_down = $form_data['down'] ??[];
					$form_data_up = $form_data['up'] ?? [];
                    $double_route = $form_data_down['double_route'] ?? '';
					if ($sale_continue == 'on') { ?>
                        <form action="" method="post" class="_grid_600 <?php echo esc_attr($double_route); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="double_route" value="<?php echo esc_attr($double_route); ?>">
                            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                            <input type="hidden" name="seat_type" value="<?php echo esc_attr($seat_type); ?>">
                            <input type="hidden" name="same_attendee" value="<?php echo esc_attr($post_infos['display_single_form'] ?? 'on'); ?>">
                            <input type="hidden" name="min_qty" value="<?php echo esc_attr($post_infos['min_qty'] ?? 1); ?>">
                            <input type="hidden" name="max_qty" value="<?php echo esc_attr($post_infos['max_qty'] ?? ''); ?>" data-msg="<?php echo esc_attr__('You can buy max ticket :', 'abp-transport-booking') . ' ' . esc_attr(($post_infos['max_qty'] ?? '')); ?>">
							<?php wp_nonce_field('abptb_registration_nonce');
								do_action('abptb_admin_order', $post_id); ?>
                            <div class="booking_area">
								<?php do_action('abptb_registration_item', $post_infos, $form_data_down); ?>
                            </div>
							<?php if (!empty($form_data_up) && !empty($double_route)) { ?>
                                <div class="booking_area return">
									<?php do_action('abptb_registration_item', $post_infos, $form_data_up,'return_'); ?>
                                </div>
							<?php }
								if (!empty($double_route)) {
									do_action('abptb_total_price', $post_infos, $form_data_up);
								} ?>
                        </form>
						<?php
					} else {
						ABPTB_Layout::layout_warning_info('sale_close_msg');
					}
				}
			}
			public function registration_item($post_infos = [], $form_data = [], $prefix = ''): void {
				include_once ABPTB_Function::template_path('layout/registration_item.php');
				do_action('abptb_registration_item_template', $post_infos, $form_data, $prefix);
			}public function selection_details($post_infos = [], $form_data = [], $prefix = ''): void {
				include_once ABPTB_Function::template_path('layout/selection_details.php');
				do_action('abptb_selection_details_template', $post_infos, $form_data, $prefix);
			}
			public function additional($post_infos = [], $prefix = ''): void {
				include_once ABPTB_Function::template_path('layout/additional_services.php');
				do_action('abptb_additional_template', $post_infos, $prefix);
			}
			public function client_form($post_infos = [], $prefix = ''): void {
				include_once ABPTB_Function::template_path('layout/client_form.php');
				do_action('abptb_client_form_template', $post_infos, $prefix);
			}
			public function total_price($post_infos = [], $form_data = []): void {
				include_once ABPTB_Function::template_path('layout/total_price.php');
				do_action('abptb_total_price_template', $post_infos, $form_data);
			}

			public function pagination($args): void {
				include_once ABPTB_Function::template_path('layout/pagination.php');
				do_action('abptb_pagination_template', $args);
			}
			public function display_cart_item($cart_item = []): void {
				include_once ABPTB_Function::template_path('layout/display_cart_item.php');
				do_action('abptb_display_cart_item_template', $cart_item);
			}
			public function faq($post_infos = [], $type = ''): void {
				include_once ABPTB_Function::template_path('layout/faq.php');
				do_action('abptb_faq_template', $post_infos, $type);
			}
			public function term_condition($post_infos = [], $type = ''): void {
				include_once ABPTB_Function::template_path('layout/term_condition.php');
				do_action('abptb_term_condition_template', $post_infos, $type);
			}
			public function related_item($related_item = ''): void {
				include_once ABPTB_Function::template_path('layout/related_item.php');
				do_action('abptb_related_item_template', $related_item);
			}
			public function slider($img_ids = '', $params = []): void {
				if (!empty($img_ids)) {
					$img_ids = explode(',', $img_ids);
					$style = $params['slider_style'] ?? '';
					$image_column = $params['column'] ?? '';
					$abptb_slider = ABPTB_Function::get_option('abptb_slider');
					//echo '<pre>';print_r($abptb_slider);echo '</pre>';
					if (!empty($image_column)) {
						$abptb_slider['image_column'] = $image_column;
						$abptb_slider['show_item'] = ($params['show'] ?? null) ?: $image_column * 3;
					}
					if (!empty($style)) {
						$slider_style = $style == 'gallery' ? 'gallery' : 'slider';
					} else {
						$slider_style = ($abptb_slider['slider_style'] ?? null) ?: 'slider';
					}
					include_once ABPTB_Function::template_path('layout/' . $slider_style . '.php');
					do_action('abptb_' . $slider_style . '_template', $img_ids, $abptb_slider);
				}
			}
			public function slider_popup($abptb_slider, $img_ids, $popup_id = '#abptb_slider_'): void {
				include_once ABPTB_Function::template_path('layout/slider_popup.php');
				do_action('abptb_slider_popup_template', $abptb_slider, $img_ids, $popup_id);
			}
		}
		new ABPTB_Hooks();
	}