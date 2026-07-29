<?php
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	if (!class_exists('ABPTF_Hooks')) {
		class ABPTF_Hooks {
			public function __construct() {
				add_action('abptf_load_details_template', [$this, 'details_template']);
				add_action('abptf_search_form', [$this, 'search_form'], 10, 2);
				add_action('abptf_post_filter', [$this, 'post_filter'], 10, 2);
				add_action('abptf_type_head', [$this, 'type_head'], 10, 3);
				add_action('abptf_ticket_type', [$this, 'ticket_type'], 10, 3);
				add_action('abptf_registration', [$this, 'registration'], 10, 2);
				add_action('abptf_additional', [$this, 'additional'], 10, 2);
				add_action('abptf_client_form', [$this, 'client_form'], 10, 2);
				add_action('abptf_total_price', [$this, 'total_price'],10,2);
				add_action('abptf_content', [$this, 'the_content']);
				add_action('abptf_pagination', [$this, 'pagination']);
				add_action('abptf_display_cart_item', [$this, 'display_cart_item']);
				add_action('abptf_faq', [$this, 'faq'], 10, 2);
				add_action('abptf_term_condition', [$this, 'term_condition'], 10, 2);
				add_action('abptf_related_item', [$this, 'related_item'], 10, 2);
				add_action('abptf_slider', [$this, 'slider'], 10, 3);
				add_action('abptf_slider_popup', [$this, 'slider_popup'], 10, 3);
			}
			public function details_template($post_id): void {
				require_once ABPTF_Function::details_template_path($post_id);
				$template_name = ABPTF_Function::get_post_info($post_id, 'abptf_template', 'default');
				do_action('abptf_details_' . $template_name . '_template', $post_id);
			}
			public function search_form($post_infos = []): void {
				include_once ABPTF_Function::template_path('layout/search_form.php');
				do_action('search_form_template', $post_infos);
			}
			public function post_filter($params = []): void {
				include_once ABPTF_Function::template_path('layout/post_filter.php');
				do_action('abptf_post_filter_template', $params);
			}
			public function type_head($post_infos, $form_data = [], $prefix = ''): void {
				include_once ABPTF_Function::template_path('layout/type_head.php');
				do_action('abptf_type_head_template', $post_infos, $form_data, $prefix);
			}
			public function ticket_type($post_infos, $form_data = [], $prefix = ''): void {
				include_once ABPTF_Function::template_path('layout/ticket_type.php');
				do_action('abptf_ticket_type_template', $post_infos, $form_data, $prefix);
			}
			public function registration($post_infos = [], $form_data = []): void {
				include_once ABPTF_Function::template_path('layout/registration.php');
				do_action('abptf_registration_template', $post_infos, $form_data);
			}
			public function additional($post_infos = [], $prefix = ''): void {
				include_once ABPTF_Function::template_path('layout/additional_services.php');
				do_action('abptf_additional_template', $post_infos, $prefix);
			}
			public function client_form($post_infos = [], $prefix = ''): void {
				include_once ABPTF_Function::template_path('layout/client_form.php');
				do_action('abptf_client_form_template', $post_infos, $prefix);
			}
			public function total_price($post_infos = [], $form_data = []): void {
				include_once ABPTF_Function::template_path('layout/total_price.php');
				do_action('abptf_total_price_template', $post_infos,$form_data);
			}
			public function the_content($post_id): void {
				include_once ABPTF_Function::template_path('layout/the_content.php');
				do_action('abptf_content_template', $post_id);
			}
			public function pagination($args): void {
				include_once ABPTF_Function::template_path('layout/pagination.php');
				do_action('abptf_pagination_template', $args);
			}
			public function display_cart_item($cart_item = []): void {
				include_once ABPTF_Function::template_path('layout/display_cart_item.php');
				do_action('abptf_display_cart_item_template', $cart_item);
			}
			public function faq($post_infos = [], $type = ''): void {
				include_once ABPTF_Function::template_path('layout/faq.php');
				do_action('abptf_faq_template', $post_infos, $type);
			}
			public function term_condition($post_infos = [], $type = ''): void {
				include_once ABPTF_Function::template_path('layout/term_condition.php');
				do_action('abptf_term_condition_template', $post_infos, $type);
			}
			public function related_item($related_item = ''): void {
				include_once ABPTF_Function::template_path('layout/related_item.php');
				do_action('abptf_related_item_template', $related_item);
			}
			public function slider($img_ids = '', $params = []): void {
				if (!empty($img_ids)) {
					$img_ids = explode(',', $img_ids);
					$style = $params['slider_style'] ?? '';
					$image_column = $params['column'] ?? '';
					$abptf_slider = ABPTF_Function::get_option('abptf_slider');
					//echo '<pre>';print_r($abptf_slider);echo '</pre>';
					if (!empty($image_column)) {
						$abptf_slider['image_column'] = $image_column;
						$abptf_slider['show_item'] = ($params['show'] ?? null) ?: $image_column * 3;
					}
					if (!empty($style)) {
						$slider_style = $style == 'gallery' ? 'gallery' : 'slider';
					} else {
						$slider_style = ($abptf_slider['slider_style'] ?? null) ?: 'slider';
					}
					include_once ABPTF_Function::template_path('layout/' . $slider_style . '.php');
					do_action('abptf_' . $slider_style . '_template', $img_ids, $abptf_slider);
				}
			}
			public function slider_popup($abptf_slider, $img_ids, $popup_id = '#abptf_slider_'): void {
				include_once ABPTF_Function::template_path('layout/slider_popup.php');
				do_action('abptf_slider_popup_template', $abptf_slider, $img_ids, $popup_id);
			}
		}
		new ABPTF_Hooks();
	}