<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	add_action( 'abptb_related_item_template', function ( $related_item = '' ) {
		if ( ABPTB_Function::on_off( 'related' ) && ! empty( $related_item ) ) {
			$post_ids = explode( ',', $related_item );
			if ( empty( $post_ids ) || ! is_array( $post_ids ) ) {
				return;
			}
			$params['all_post'] = $post_ids;
			$params['related']  = 'yes';
            $brand_icon=ABPTB_Function::icon();
			?>
            <div class="_abp_panel related_item_area">
                <div class="_panel_head _fj_between">
                    <h4 class="_abp"><?php ABPTB_Layout::image_icon($brand_icon);  ?><?php esc_html_e( 'Related', 'abp-transport-booking' ); ?></h4>
                    <div class="_group_content">
                        <h3 class="related_prev">🔙</h3>
                        <h3 class="related_next">🔜</h3>
                    </div>
                </div>
                <div class="_panel_body_xs ">
					<?php
						include_once ABPTB_Function::template_path( 'list/default.php' );
						do_action( 'abptb_default_template', $params );
					?>
                </div>
            </div>
			<?php
		}
	}, 10, 2 );