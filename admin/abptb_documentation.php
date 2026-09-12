<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABPTB_Documentation')) {
        class ABPTB_Documentation {
            public function __construct() {
                add_action('abptb_load_documentation', array($this, 'load_documentation'));
            }
            private function gq_tag(string $type, string $text): void {
                echo '<span class="gq_tag gq_tag_' . esc_attr($type) . '">' . esc_html($text) . '</span>';
            }
            private function gq_btn(string $url, string $icon, string $text, string $blank = ''): void {
                echo '<a class="_btn_light_green_pale_xs" href="' . esc_url($url) . '"' . ($blank ? ' target="_blank" rel="noopener"' : '') . '><i class="' . esc_attr($icon) . '"></i> ' . esc_html($text) . '</a>';
            }
            private function gq_sw(string $label): void {
                echo '<span class="gq_sw"><span class="gq_sw_i"><i class="fas fa-check"></i></span>' . esc_html($label) . '</span>';
            }
            public function load_documentation($abptb_info): void {
                $label = ABPTB_Function::label();
                $pro_active = defined('ABPTB_DIR_PRO');
                ?>
                <div class="_max_1200_mar_auto">
                    <div class="dash_hero" style="margin-bottom: var(--tb_gap)">
                        <div class="dash_hero_main">
                            <div class="dash_hero_text">
                                <span class="dash_hero_date"><i class="fas fa-book-open"></i> <?php esc_html_e('Documentation & Quick Start Guide', 'abp-transport-booking'); ?></span>
                                <h2>
                                    <?php
                                        /* translators: %s: transport label. */
                                        printf(esc_html__('Learn how to use %s step by step', 'abp-transport-booking'), esc_html($label));
                                    ?>
                                </h2>
                                <p>
                                    <?php esc_html_e('Follow the steps below in order. Cards marked MUST are required, OPTIONAL cards improve the experience, and the purple cards are PRO tools.', 'abp-transport-booking'); ?>
                                </p>
                            </div>
                        </div>
                        <div class="dash_hero_side">
                            <div class="dash_hero_actions">
                                <?php $this->gq_btn(admin_url('post-new.php?post_type=' . ABPTB_Function::get_cpt()), 'fas fa-plus', $label . ' (Add New)'); ?>
                                <?php $this->gq_btn(ABPTB_Function::build_url('configuration'), 'fas fa-gear', __('Configuration', 'abp-transport-booking')); ?>
                            </div>
                        </div>
                    </div>
                    <div class="documentation">
                        <div class="gq_wrap">
                            <div class="gq_roadmap">
                                <span class="gq_rm_chip"><i class="fas fa-heart" style="color:#ef4444"></i> <?php esc_html_e('Free Setup', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_arrow">→</span>
                                <span class="gq_rm_chip">1 <?php esc_html_e('Install', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_chip">2 <?php esc_html_e('Configure', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_chip">3 <?php esc_html_e('Global Data', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_chip">4 <?php esc_html_e('Tickets / Seats', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_chip">5 <?php esc_html_e('Transport', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_chip">6 <?php esc_html_e('Shortcode', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_chip">7 <?php esc_html_e('Test', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_chip">8 <?php esc_html_e('Design', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_chip">9 <?php esc_html_e('Manage', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_arrow">→</span>
                                <span class="gq_rm_chip"><i class="fas fa-crown" style="color:#8b5cf6"></i> <?php esc_html_e('PRO', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_arrow">→</span>
                                <span class="gq_rm_chip">P1 <?php esc_html_e('License', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_chip">P2 <?php esc_html_e('PDF', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_chip">P3 <?php esc_html_e('Mail / Export', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_chip">P4 <?php esc_html_e('Discount / Payment', 'abp-transport-booking'); ?></span>
                                <span class="gq_rm_chip">P5 <?php esc_html_e('Admin Tools', 'abp-transport-booking'); ?></span>
                            </div>
                            <?php if (ABPTB_WC < 2) { ?>
                                <div class="_section_1_xs abp_notice">
                                    <h6 class="abp_color_warning_gap_xs"><span class="fas fa-exclamation-triangle"></span><?php esc_html_e('WooCommerce is required before you can start booking. Please install and activate WooCommerce first.', 'abp-transport-booking'); ?></h6>
                                    <?php if (ABPTB_WC == 1) { ?>
                                        <button class="_btn_warning_xs" onclick="abptb_wc_config('wc_active')" type="button"><span class="fas fa-tasks"></span><?php esc_html_e('Activate Now', 'abp-transport-booking'); ?></button>
                                    <?php } else { ?>
                                        <button class="_btn_warning_xs" onclick="abptb_wc_config('wc_install_active')" type="button"><span class="fas fa-file-download"></span><?php esc_html_e('Install & Activate Now', 'abp-transport-booking'); ?></button>
                                    <?php } ?>
                                </div>
                                <div class="_divider_xs"></div>
                            <?php } ?>
                            <div class="gq_phase gq_phase_free"><i class="fas fa-heart"></i> <?php esc_html_e('FREE SETUP — follow steps 1 to 9 in order', 'abp-transport-booking'); ?></div>
                            <div class="gq_step gq_step_must">
                                <span class="gq_num">1</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-download"></i> <?php esc_html_e('Install & Activate', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('must', __('Must', 'abp-transport-booking')); ?>
                                    </div>
                                    <p><?php esc_html_e('The plugin needs WooCommerce because all payments and orders run through it.', 'abp-transport-booking'); ?></p>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><strong>WooCommerce</strong> — <?php esc_html_e('install and activate first. If you see the warning above, click the green button.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><strong><?php echo esc_html($label); ?></strong> — <?php esc_html_e('Plugins → Add New → search "ABP Transport Booking" (or upload the ZIP) → Install → Activate.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><strong>Next</strong> — <?php esc_html_e('a new menu appears in the left sidebar with your transport name. Open it — you are now inside the plugin.', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                    <div class="gq_btn_row">
                                        <?php $this->gq_btn(ABPTB_Function::build_url('dashboard'), 'fas fa-gauge-high', __('Open Plugin Dashboard', 'abp-transport-booking')); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="gq_step gq_step_must">
                                <span class="gq_num">2</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-gear"></i> <?php esc_html_e('Configuration Basics — Labels & Settings', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('must', __('Must', 'abp-transport-booking')); ?>
                                    </div>
                                    <p><?php esc_html_e('Open the Configuration tab. Here you control the name and behavior of your booking system.', 'abp-transport-booking'); ?></p>
                                    <div class="gq_grid2">
                                        <div class="gq_mini"><b><i class="fas fa-font"></i> <?php esc_html_e('Transport label & slug', 'abp-transport-booking'); ?></b><span><?php esc_html_e('The name customers see (default "Transport") and the URL slug of the listing page.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-ticket-alt"></i> <?php esc_html_e('Booked order statuses', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Which WooCommerce statuses count as a real booking (default Processing + Completed).', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-rocket"></i> <?php esc_html_e('Menu & transport icon', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Dashboard menu icon and the emoji/icon shown next to transports.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-lightbulb"></i> <?php esc_html_e('Labels for category / organizer / brand', 'abp-transport-booking'); ?></b><span><?php esc_html_e('If those features are ON, you can rename their labels and slugs here.', 'abp-transport-booking'); ?></span></div>
                                    </div>
                                    <p class="gq_sub"><i class="fas fa-power-off"></i> <strong><?php esc_html_e('Feature ON/OFF control:', 'abp-transport-booking'); ?></strong> <?php esc_html_e('everything is ON by default. Turn OFF the features your business does not use — their menu tabs, form fields and options disappear, keeping the dashboard clean.', 'abp-transport-booking'); ?></p>
                                    <div class="gq_sw_grid">
                                        <span class="gq_sw_i"><strong><?php esc_html_e('Transport content', 'abp-transport-booking'); ?>:</strong></span>
                                        <?php
                                            $this->gq_sw(__('Transport icon', 'abp-transport-booking'));
                                            $this->gq_sw(__('Sub title', 'abp-transport-booking'));
                                            $this->gq_sw(__('Short description', 'abp-transport-booking'));
                                            $this->gq_sw(__('Transport ID/SKU', 'abp-transport-booking'));
                                            $this->gq_sw(__('Category', 'abp-transport-booking'));
                                            $this->gq_sw(__('Organizer', 'abp-transport-booking'));
                                            $this->gq_sw(__('Brand', 'abp-transport-booking'));
                                            $this->gq_sw(__('Transport features', 'abp-transport-booking'));
                                            $this->gq_sw(__('Capacity badge', 'abp-transport-booking'));
                                        ?>
                                    </div>
                                    <div class="gq_sw_grid">
                                        <span class="gq_sw_i"><strong><?php esc_html_e('Booking & tickets', 'abp-transport-booking'); ?>:</strong></span>
                                        <?php
                                            $this->gq_sw(__('Multiple ticket types', 'abp-transport-booking'));
                                            $this->gq_sw(__('Seat plan', 'abp-transport-booking'));
                                            $this->gq_sw(__('Ticket capacity', 'abp-transport-booking'));
                                            $this->gq_sw(__('Return trip', 'abp-transport-booking'));
                                            $this->gq_sw(__('Min & max quantity', 'abp-transport-booking'));
                                            $this->gq_sw(__('Related transport', 'abp-transport-booking'));
                                        ?>
                                    </div>
                                    <div class="gq_sw_grid">
                                        <span class="gq_sw_i"><strong><?php esc_html_e('Forms & services', 'abp-transport-booking'); ?>:</strong></span>
                                        <?php
                                            $this->gq_sw(__('Client info form', 'abp-transport-booking'));
                                            $this->gq_sw(__('Attendee form', 'abp-transport-booking'));
                                            $this->gq_sw(__('Same attendee reuse', 'abp-transport-booking'));
                                            $this->gq_sw(__('Multiple pickup / drop-off', 'abp-transport-booking'));
                                            $this->gq_sw(__('Additional services', 'abp-transport-booking'));
                                            $this->gq_sw(__('Route direction', 'abp-transport-booking'));
                                        ?>
                                    </div>
                                    <div class="gq_sw_grid">
                                        <span class="gq_sw_i"><strong><?php esc_html_e('Customer info', 'abp-transport-booking'); ?>:</strong></span>
                                        <?php
                                            $this->gq_sw(__('FAQ', 'abp-transport-booking'));
                                            $this->gq_sw(__('Terms & Conditions', 'abp-transport-booking'));
                                        ?>
                                    </div>
                                    <div class="gq_btn_row">
                                        <?php $this->gq_btn(ABPTB_Function::build_url('configuration'), 'fas fa-gear', __('Open Configuration', 'abp-transport-booking')); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="gq_step gq_step_must">
                                <span class="gq_num">3</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-globe"></i> <?php esc_html_e('Global Configuration (Global Data)', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('must', __('Must: Dates + Stops', 'abp-transport-booking')); ?>
                                    </div>
                                    <p><?php esc_html_e('The Global Data tab stores reusable settings — create them once and every transport can use them.', 'abp-transport-booking'); ?></p>
                                    <div class="gq_grid2">
                                        <div class="gq_mini"><b><i class="fas fa-calendar-day"></i> <?php esc_html_e('Dates — MUST', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Date format, booking buffer (cut-off time before a journey) and the advance-booking window (how far ahead customers can book).', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-route"></i> <?php esc_html_e('Stops / Locations — MUST', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Boarding and drop-off places. Every transport route uses these stops, so add them before creating transports.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-boxes-stacked"></i> <?php esc_html_e('Category — optional', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Transport types for filtering and SEO pages (bus, ferry, coach …).', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-building"></i> <?php esc_html_e('Organizer — optional', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Company or operator shown on transports.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-tags"></i> <?php esc_html_e('Brands — optional', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Transport brands for your fleet.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-link"></i> <?php esc_html_e('Features Library — optional', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Amenities like AC, Wi-Fi, toilet — reusable and shown on transports.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b>💰 <?php esc_html_e('Additional services — optional', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Extras customers can add at booking time (baggage, meals, insurance).', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-id-card"></i> 📋 <?php esc_html_e('Client form — optional', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Reusable passenger information fields.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-book"></i> 📚 <?php esc_html_e('Resources (FAQ & Terms) — optional', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Global FAQ and Terms & Conditions content.', 'abp-transport-booking'); ?></span></div>
                                    </div>
                                    <div class="gq_btn_row">
                                        <?php $this->gq_btn(ABPTB_Function::build_url('global', ['global' => 'dates']), 'fas fa-calendar-day', __('Open Global Dates', 'abp-transport-booking')); ?>
                                        <?php $this->gq_btn(ABPTB_Function::build_url('global', ['global' => 'location']), 'fas fa-route', esc_html(ABPTB_Function::location_label())); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="gq_step gq_step_must">
                                <span class="gq_num">4</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-chair"></i> <?php esc_html_e('Ticket Types & Seat Plans (Ticket/Seat Plan tab)', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('must', __('Must: choose at least tickets or seat plan', 'abp-transport-booking')); ?>
                                    </div>
                                    <p><?php esc_html_e('Build your reusable library first. You need ticket types, and a seat plan only if customers pick individual seats.', 'abp-transport-booking'); ?></p>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick">1</span><span><strong><?php esc_html_e('Add ticket types', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('name, icon, color and prefix (e.g. Adult, Child, Concession).', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick">2</span><span><strong><?php esc_html_e('Create a seat plan', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('drag-and-drop designer: seats, rows, automatic numbering, background images and colors.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick">3</span><span><strong><?php esc_html_e('Assign tickets to seats', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('each seat or seat area can use a different ticket type.', 'abp-transport-booking'); ?></li>
                                        <li><span class="gq_tick">✓</span><span><?php esc_html_e('Seat plans are reusable — one plan can serve many transports, even multiple times in the same transport (multi-layer seating).', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                    <div class="gq_btn_row">
                                        <?php $this->gq_btn(ABPTB_Function::build_url('sp'), 'fas fa-chair', __('Open Ticket/Seat Plan', 'abp-transport-booking')); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="gq_step gq_step_must">
                                <span class="gq_num">5</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-bus"></i> <?php esc_html_e('Create Your First Transport (Lists → Add New)', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('must', __('Must', 'abp-transport-booking')); ?>
                                    </div>
                                    <p><?php esc_html_e('Fill the editor tabs one by one, left to right:', 'abp-transport-booking'); ?></p>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick">1</span><span><strong><?php esc_html_e('General', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('template (Default/Light/Premium), transport icon, sub-title, short description, category, organizer, brand, related transports.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick">2</span><span><strong><?php esc_html_e('Route', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('boarding and drop-off stops + forward/return direction.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick">3</span><span><strong><?php esc_html_e('Price', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('fare for the forward and return journey.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick">4</span><span><strong><?php esc_html_e('Date', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('which dates the transport runs: schedule, date range, weekends only, or off-dates.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick">5</span><span><strong><?php esc_html_e('Time', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('departure time(s) for each journey day.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick">6</span><span><strong><?php esc_html_e('Ticket', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('attach your seat plan or ticket types, set quantities and min/max per order.', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                    <p><?php esc_html_e('When everything is filled, click Publish. The transport is now bookable on the frontend.', 'abp-transport-booking'); ?></p>
                                    <div class="gq_btn_row">
                                        <?php $this->gq_btn(ABPTB_Function::build_url('posts'), 'fas fa-bus', __('Open Lists', 'abp-transport-booking')); ?>
                                        <?php $this->gq_btn(admin_url('post-new.php?post_type=' . ABPTB_Function::get_cpt()), 'fas fa-plus', __('Add New Transport', 'abp-transport-booking')); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="gq_step gq_step_must">
                                <span class="gq_num">6</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-code"></i> <?php esc_html_e('Put the Booking Form on a Page', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('must', __('Must', 'abp-transport-booking')); ?>
                                    </div>
                                    <p><?php esc_html_e('Add one of these shortcodes to any WordPress page:', 'abp-transport-booking'); ?></p>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick">✓</span><span><code>[abptb-booking]</code> — <?php esc_html_e('transport list with the booking search form (recommended).', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick">✓</span><span><code>[abptb-post]</code> — <?php esc_html_e('transport list only.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick">✓</span><span><code>[abptb-gallery]</code> — <?php esc_html_e('transport image gallery / slider.', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                    <p><?php esc_html_e('Optional shortcode attributes:', 'abp-transport-booking'); ?> <code>post_id</code>, <code>cat_id</code>, <code>loc_id</code>, <code>style</code> (<code>grid</code>/<code>missionary</code>), <code>column</code>, <code>sort</code>, <code>pagination</code>, <code>form</code>.</p>
                                    <div class="gq_btn_row">
                                        <?php $this->gq_btn(admin_url('post-new.php?post_type=page'), 'fas fa-plus', __('Create a Page', 'abp-transport-booking'), 'blank'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="gq_step gq_step_must">
                                <span class="gq_num">7</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-shopping-cart"></i> <?php esc_html_e('Place a Test Booking', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('must', __('Must', 'abp-transport-booking')); ?>
                                    </div>
                                    <p><?php esc_html_e('Open the page on the frontend and run through the full flow once:', 'abp-transport-booking'); ?></p>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('Search a route, pick a journey date and press Check Availability.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('Select seats (if seat plan) and ticket types, set the passenger count.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('Fill the passenger/client form and optional additional services.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('Complete payment through WooCommerce checkout.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('Check the seat legend (available/selected/sold), then confirm the booking appears in My Account → Transport Bookings.', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="gq_step gq_step_opt">
                                <span class="gq_num">8</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-palette"></i> <?php esc_html_e('Branding & Design', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('opt', __('Optional', 'abp-transport-booking')); ?>
                                    </div>
                                    <div class="gq_grid2">
                                        <div class="gq_mini"><b><i class="fas fa-pen-ruler"></i> <?php esc_html_e('Frontend templates', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Each transport can use the Default, Light or Premium detail template.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-photo-video"></i> <?php esc_html_e('Slider / gallery', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Carousel or gallery/masonry theme, images per row, indicator position and lightbox popup.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-drafting-compass"></i> <?php esc_html_e('CSS property', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Brand colors, fonts, button styles and border radius without touching code.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-id-card-alt"></i> <?php esc_html_e('Contact information', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Company name, address, phone and e-mail shown on bookings.', 'abp-transport-booking'); ?></span></div>
                                    </div>
                                    <div class="gq_btn_row">
                                        <?php $this->gq_btn(ABPTB_Function::build_url('configuration'), 'fas fa-gear', __('Open Configuration', 'abp-transport-booking')); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="gq_step gq_step_must">
                                <span class="gq_num">9</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-file-invoice"></i> <?php esc_html_e('Manage Orders & Watch the Dashboard', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('must', __('Ongoing', 'abp-transport-booking')); ?>
                                    </div>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><strong><?php esc_html_e('Orders tab', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('filter by transport, date, route, order number or customer; paginate and cancel line items.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><strong><?php esc_html_e('Dashboard tab', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('total transports, orders, tickets sold, revenue, booked today, today\'s trips and system health.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><strong><?php esc_html_e('My Account', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('customers see their own bookings under "Transport Bookings".', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                    <div class="gq_btn_row">
                                        <?php $this->gq_btn(ABPTB_Function::build_url('orders'), 'fas fa-file-invoice', __('Open Orders', 'abp-transport-booking')); ?>
                                        <?php $this->gq_btn(ABPTB_Function::build_url('dashboard'), 'fas fa-gauge-high', __('Open Dashboard', 'abp-transport-booking')); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="gq_phase gq_phase_pro"><i class="fas fa-crown"></i> <?php esc_html_e('PRO POWER-UP — these tools work only when the PRO plugin + license are active', 'abp-transport-booking'); ?></div>
                            <div class="gq_step gq_step_pro">
                                <span class="gq_num">P1</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-key"></i> <?php esc_html_e('Install PRO & Activate the License', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('pro', __('PRO — Must', 'abp-transport-booking')); ?>
                                        <?php if ($pro_active) {
                                            $this->gq_tag('ok', __('Installed', 'abp-transport-booking'));
                                        } ?>
                                    </div>
                                    <p><?php esc_html_e('The pro plugin needs WooCommerce and the free plugin to be active. Do these in order:', 'abp-transport-booking'); ?></p>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick">1</span><span><?php esc_html_e('Upload <code>abp-transport-booking-pro.zip</code> under Plugins → Add New → Upload Plugin → Activate.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick">2</span><span><?php esc_html_e('Open the Dashboard — the License card appears there. Paste your license key and click Activate License.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick">3</span><span><?php esc_html_e('If asked, install the ABP PDF Tools (needed for PDF tickets) and wait for it to finish.', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="gq_step gq_step_pro">
                                <span class="gq_num">P2</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-ticket-alt"></i> <?php esc_html_e('PDF Tickets & QR Codes', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('pro', __('PRO', 'abp-transport-booking')); ?>
                                    </div>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('Configuration → PDF: set background image, logo, colors and who can download tickets.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('Customers download the PDF (with QR code) on the thank-you page and from My Account.', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="gq_step gq_step_pro">
                                <span class="gq_num">P3</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="far fa-envelope"></i> <?php esc_html_e('E-Mail Notifications & Exports', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('pro', __('PRO', 'abp-transport-booking')); ?>
                                    </div>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('Configuration → E-Mail: branded booking e-mails with PDF attachments; resend any e-mail from the order list.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('Export the order list as PDF or CSV from the Configuration → Order Lists tabs.', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="gq_step gq_step_pro">
                                <span class="gq_num">P4</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-percent"></i> <?php esc_html_e('Discounts', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('pro', __('PRO', 'abp-transport-booking')); ?>
                                    </div>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><strong><?php esc_html_e('Seasonal & early-bird discounts', 'abp-transport-booking'); ?></strong> — <?php esc_html_e('site-wide (Global Data → Global Discount) or per transport (Lists → Discount).', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('Partial payment is explained in detail in the next step (P5).', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="gq_step gq_step_pro">
                                <span class="gq_num">P5</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-hand-holding-usd"></i> <?php esc_html_e('Partial Payment — Setup & How It Works', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('pro', __('PRO', 'abp-transport-booking')); ?>
                                    </div>
                                    <div class="gq_sub"><?php esc_html_e('1. Site-wide (default) settings', 'abp-transport-booking'); ?></div>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick">1</span><span><?php esc_html_e('Turn on the Partial Payment switch in Global Data.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick">2</span><span><?php esc_html_e('Global Data → Partial Payment: choose the method — Fixed Amount or Percentage — and enter the deposit value/percentage.', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                    <div class="gq_sub"><?php esc_html_e('2. Per transport override (optional)', 'abp-transport-booking'); ?></div>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick">3</span><span><?php esc_html_e('Lists → edit a transport → Partial Payment tab: enable “Pay Partial Amount at Checkout?” and optionally set its own deposit value (leave empty to use the site-wide default).', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                    <div class="gq_sub"><?php esc_html_e('3. At checkout (customer)', 'abp-transport-booking'); ?></div>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('A Payment Option box appears above the payment methods on both the Classic and the Block checkout.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('The customer chooses Pay Full Amount Now or Pay Partial Amount (Deposit) Now. The deposit is charged immediately; the rest is recorded as due.', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('Only transports with Partial Payment enabled are split; other items in the same cart are always paid in full.', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                    <div class="gq_sub"><?php esc_html_e('4. Paid & due shown everywhere', 'abp-transport-booking'); ?></div>
                                    <ul class="gq_list">
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('PDF ticket, booking e-mail, admin order list and My Account show Paid (Deposit) and Due (Balance).', 'abp-transport-booking'); ?></span></li>
                                        <li><span class="gq_tick"><i class="fas fa-check"></i></span><span><?php esc_html_e('Once the balance is paid, a Balance Paid Date appears instead of the Pay Due button.', 'abp-transport-booking'); ?></span></li>
                                    </ul>
                                    <div class="gq_sub"><?php esc_html_e('5. Paying the balance', 'abp-transport-booking'); ?></div>
                                    <div class="gq_grid2">
                                        <div class="gq_mini"><b><i class="fas fa-user"></i> <?php esc_html_e('Customer (online)', 'abp-transport-booking'); ?></b><span><?php esc_html_e('My Account → Transport Bookings → click Pay Due Payment. A WooCommerce order for the balance is created and paid with the store’s payment methods; the record updates itself.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-user-shield"></i> <?php esc_html_e('Admin (cash)', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Admin Order List → under Total, click Paid Now to mark the due amount collected; the database updates immediately through AJAX.', 'abp-transport-booking'); ?></span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="gq_step gq_step_pro">
                                <span class="gq_num">P6</span>
                                <div class="gq_body">
                                    <div class="gq_head">
                                        <h3><i class="fas fa-wrench"></i> <?php esc_html_e('Admin Power Tools', 'abp-transport-booking'); ?></h3>
                                        <?php $this->gq_tag('pro', __('PRO', 'abp-transport-booking')); ?>
                                    </div>
                                    <div class="gq_grid2">
                                        <div class="gq_mini"><b><i class="fas fa-cart-plus"></i> <?php esc_html_e('Add Order', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Create bookings directly from the dashboard, without checkout.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-ban"></i> <?php esc_html_e('Cancel Requests', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Customers request cancellation (with time limit); you approve or reject. Approving releases the seats.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-clock"></i> <?php esc_html_e('Multiple trips / day', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Day-wise and date-wise departure times for up and return journeys.', 'abp-transport-booking'); ?></span></div>
                                        <div class="gq_mini"><b><i class="fas fa-clipboard-check"></i> <?php esc_html_e('Check-in', 'abp-transport-booking'); ?></b><span><?php esc_html_e('Mark bookings as checked-in from the order list.', 'abp-transport-booking'); ?></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        new ABPTB_Documentation();
    }