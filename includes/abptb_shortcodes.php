<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Shortcodes')) {
        class ABPTB_Shortcodes {
            public function __construct() {
                add_shortcode('abptb-booking', array($this, 'booking'));
                add_shortcode('abptb-post', array($this, 'post_list'));
                add_shortcode('abptb-gallery', array($this, 'gallery'));
            }
            public function booking($attribute): bool|string {
                $defaults = self::default_attribute();
                $params = shortcode_atts($defaults, $attribute);
                $post_id = $params['post_id'] ?? '';
                ob_start();
                if (!empty($post_id)) {
                    do_action('abptb_load_details_template', $post_id);
                } else {
                    $params['all_post'] = ABPTB_Query::get_post_id($params);
                    $params['global_order'] = 'yes';
                    $style = sanitize_key(($params['style'] ?? 'grid') ?: 'grid');
                    $templates = ['grid' => 'list/grid.php', 'missionary' => 'list/missionary.php',];
                    $file = ABPTB_Function::template_path($templates[$style] ?? $templates['grid']);
                    ?>
                    <div class="abptb_area">
                        <div class="abp_container">
                            <div class="global_form"><?php do_action('abptb_search_form', $params); ?></div>
                            <div class="abptb_booking  _gap_fd_column">
                                <div class="abp_pagination _gap_fd_column">
                                    <?php
                                        do_action('abptb_post_filter', $params);
                                        if (is_file($file)) {
                                            include_once $file;
                                            do_action('abptb_' . $style . '_template', $params);
                                        } else {
                                            include_once ABPTB_Function::template_path('list/default.php');
                                            do_action('abptb_default_template', $params);
                                        } ?>
                                    <div class="not_found">
                                        <?php ABPTB_Layout::layout_warning_info('not_match'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                return ob_get_clean();
            }
            public function post_list($attribute): bool|string {
                $defaults = self::default_attribute();
                $params = shortcode_atts($defaults, $attribute);
                $post_id = $params['post_id'] ?? '';
                //echo '<pre>';print_r($params);echo '</pre>';
                ob_start();
                if (!empty($post_id)) {
                    do_action('abptb_load_details_template', $post_id);
                } else {
                    $params['all_post'] = ABPTB_Query::get_post_id($params);
                    $style = sanitize_key(($params['style'] ?? 'grid') ?: 'grid');
                    $templates = ['grid' => 'list/grid.php', 'missionary' => 'list/missionary.php',];
                    $file = ABPTB_Function::template_path($templates[$style] ?? $templates['grid']);
                    ?>
                    <div class="abptb_area">
                        <div class="abp_container abptb_booking">
                            <div class="abp_pagination _gap_fd_column">
                                <?php
                                    do_action('abptb_post_filter', $params);
                                    if (is_file($file)) {
                                        include_once $file;
                                        do_action('abptb_' . $style . '_template', $params);
                                    } else {
                                        include_once ABPTB_Function::template_path('list/default.php');
                                        do_action('abptb_default_template', $params);
                                    } ?>
                                <div class="not_found">
                                    <?php ABPTB_Layout::layout_warning_info('not_match'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                return ob_get_clean();
            }
            public function gallery($attribute): bool|string {
                $defaults = self::default_attribute();
                $params = shortcode_atts($defaults, $attribute);
                $post_id = $params['post_id'] ?? '';
                ob_start();
                ?>
                <div class="abptb_area">
                    <div class="abp_container global_slider abp_pagination">
                        <?php
                            if (!empty($post_id)) {
                                $img_infos = ABPTB_Function::get_post_info($post_id, 'abptb_slider', []);
                                do_action('abptb_slider', $img_infos, $params);
                            } else {
                                $post_ids = ABPTB_Query::get_post_id($params);
                                $img_infos = '';
                                if (!empty($post_ids) && sizeof($post_ids) > 0) {
                                    foreach ($post_ids as $post_id) {
                                        $info = ABPTB_Function::get_post_info($post_id, 'abptb_slider', []);
                                        if (!empty($info)) {
                                            $img_infos = $img_infos ? $img_infos . ',' . $info : $info;
                                        }
                                    }
                                    do_action('abptb_slider', $img_infos, $params);
                                }
                            }
                        ?>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }
            public static function default_attribute(): array {
                return array(
                    "post_id" => '',
                    "cat_id" => '',
                    "loc_id" => '',
                    "brand_id" => '',
                    "org_id" => '',
                    "style" => 'grid',
                    "slider_style" => 'gallery',
                    "show" => '',
                    "column" => 3,
                    'sort' => 'ASC',
                    "pagination" => "yes",
                    "pagination-style" => "live",
                    'form' => 'inline',
                );
            }
        }
        new ABPTB_Shortcodes();
    }