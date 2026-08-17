<?php
    if (!defined('ABSPATH')) {
        exit;
    }
    add_action('abptb_term_condition_template', function ($post_infos = [], $type = '') {
        if (ABPTB_Function::on_off('tc')) {
            $infos = '';
            if (!empty($post_infos) && is_array($post_infos)) {
                $display = $post_infos['display_tc'] ?? 'on';
                $active_global_tc = $post_infos['active_global_tc'] ?? 'on';
                if ($display === 'on') {
                    $infos = ($active_global_tc === 'on') ? ABPTB_Function::get_option('abptb_tc', '') : ($post_infos['abptb_tc'] ?? '');
                }
            } elseif ($type === 'global') {
                $infos = ABPTB_Function::get_option('abptb_tc', '');
            }
            if (!empty($infos) && is_string($infos)) {
                ?>
                <div class="_section_card_xs_w_full term_condition">
                    <h4 class="abp_gap_xs">🤝 <?php esc_html_e('Term & Conditions', 'abp-transport-booking'); ?></h4>
                    <div class="_divider_xs"></div>
                    <?php
                        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
                        echo wp_kses_post(apply_filters('the_content', $infos));
                    ?>
                </div>
                <?php
            }
        }
    }, 10, 2);