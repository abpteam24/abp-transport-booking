<?php
	if (!defined('ABSPATH')) {
		exit; // Exit if accessed directly
	}
	if (!class_exists('ABPTB_Frontend')) {
		class ABPTB_Frontend {
			public function __construct() {
				add_filter('single_template', [$this, 'load_single_page']);
				add_filter('template_include', array($this, 'load_taxonomy_page'));
			}
			public function load_single_page($template): string {
				if (is_singular(ABPTB_Function::get_cpt())) {
					$custom_template = ABPTB_Function::template_path('page/details_page.php');
					if (!empty($custom_template)) {
						return $custom_template;
					}
				}
				return (string)$template;
			}
			public function load_taxonomy_page($template): string {
				if (is_tax('abptb_category')) {
					return ABPTB_Function::template_path('page/category.php');
				}
				if (is_tax('abptb_location')) {
					return ABPTB_Function::template_path('page/location.php');
				}
				if (is_tax('abptb_brand')) {
					return ABPTB_Function::template_path('page/brand.php');
				}
				if (is_tax('abptb_organizer')) {
					return ABPTB_Function::template_path('page/organizer.php');
				}
				return (string)$template;
			}
		}
		new ABPTB_Frontend();
	}