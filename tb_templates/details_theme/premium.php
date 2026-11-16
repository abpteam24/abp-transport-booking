<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}
add_action('abptb_details_premium_template', function ($post_id, $form_data = [], $full_content = true) {
	if (!empty($post_id) && $post_id > 0 && get_post_type($post_id) == ABPTB_Function::get_cpt() && (get_post_status($post_id) == 'publish' || is_admin())) {
		$post_infos = ABPTB_Function::get_all_meta($post_id);
		$form_data['form'] = 'inline';
		$bp_dp = $form_data['bp_dp'] ?? '';
		$display_return = $post_infos['display_return'] ?? 'off';
		$display_return = ABPTB_Function::on_off('return') ? $display_return : 'off';
		?>
        <div class="abptb_area premium details_page">
            <div class="abp_container">
                <div class="details_head">
                    <div class="details_head_left">
                        <h1 class="abp_color_theme_fs_h3_gap_xs"><?php ABPTB_Layout::title($post_infos); ?></h1>
						<?php ABPTB_Layout::sub_title($post_infos); ?>
                        <div class="details_route">
                            <div class="details_route_go"><?php ABPTB_Layout::route_direction($post_infos, $bp_dp); ?></div>
							<?php if ($display_return == 'on') { ?>
                                <div class="details_route_return"><?php ABPTB_Layout::route_direction($post_infos, '', true); ?></div>
							<?php } ?>
                        </div>
                        <div class="details_meta">
							<?php ABPTB_Layout::brand($post_infos);
							ABPTB_Layout::organizer($post_infos, 'publish'); ?>
                        </div>
						<?php ABPTB_Layout::item_feature($post_infos['post_feature'] ?? '');
						ABPTB_Layout::description($post_infos); ?>
                    </div>
                    <div class="details_head_right">
						<?php do_action('abptb_search_form', $post_infos, $form_data); ?>
                    </div>
                </div>
                <div class="abptb_booking">
					<?php do_action('abptb_registration', $post_infos, $form_data); ?>
                </div>
				<?php if ($full_content) { ?>
					<?php ABPTB_Layout::the_content($post_id); ?>
                    <div class="_grid_500">
                        <div class="_fd_column_gap">
							<?php do_action('abptb_faq', $post_infos); ?>
							<?php do_action('abptb_slider', ($post_infos['abptb_slider'] ?? [])); ?>
                        </div>
						<?php do_action('abptb_term_condition', $post_infos); ?>
                    </div>
					<?php do_action('abptb_related_item', ($post_infos['related_item'] ?? '')); ?>
				<?php } ?>
            </div>
        </div>
		<?php
	}
}, 10, 3);