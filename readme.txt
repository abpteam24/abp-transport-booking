=== ABP Transport Booking ===
Contributors: abpteam
Tags: transport booking, bus booking, seat reservation, ticket booking, passenger transport
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

WooCommerce transport booking for bus, ferry, shuttle and coach services with seat plans, ticket types, routes, schedules and return trips.

== Description ==

**ABP Transport Booking** transforms your WooCommerce store into a complete transportation ticketing platform. Manage routes, schedules, seat plans, ticket types and online bookings for bus, ferry, launch, shuttle, coach, van and minibus services — all from one WordPress dashboard.

Customers can search available transports, select a travel date, choose ticket types and seats, fill in passenger information and complete payment through WooCommerce checkout. Both **one-way** and **return trip** bookings are supported. When return travel is configured, customers can book an outbound journey and a return journey using the same transport.

The plugin ships with a powerful **seat plan designer** — drag-and-drop controls, automatic numbering, dynamic ticket-type assignment, custom cells and multi-layer seating. A seat plan can be created once and reused across many transports.

The frontend includes a **booking search form**, **featured transport slider / gallery with lightbox**, **grid & list views**, and **selectable detail templates** (Default, Light, Premium). Everything is responsive, translation-ready and built on WooCommerce — your existing payment gateways, taxes, coupons and customer accounts keep working.

An **admin dashboard** gives you a live business overview: total transports, total orders, tickets sold, revenue, bookings made today, today's trips and a system health checklist.

Want advanced tools? **ABP Transport Booking PRO** adds PDF tickets with QR codes, CSV/PDF export, e-mail notifications, seasonal & early-bird discounts, partial payment, admin order creation, customer cancel requests, day-wise/date-wise multiple trips and more — see the Pro Features section below.

== Key Features ==

* Passenger transport booking for bus, ferry, launch, shuttle, coach, van and minibus services
* One-way and return trip booking with the same transport
* Live booking search form (transport, from/to stop, journey date, return date, passenger count)
* Featured transport slider and gallery with lightbox popup
* Grid and list views with category filter and AJAX-based live pagination
* Selectable frontend templates per transport (Default, Light, Premium)
* Powerful drag-and-drop seat plan designer with automatic numbering
* Multi-layer seat plans — reuse a plan multiple times in one transport
* Real-time seat availability with cart-level seat holds (no overselling)
* Transport management dashboard with edit, clone, view, delete and trash
* Admin business dashboard with KPIs (transport, orders, tickets sold, revenue, booked today)
* Today's trips panel with sold / available / total / reserve seat counts
* Quick actions and one-click page creation / demo-data import
* Filterable, paginated booking and order list with line-item cancellation
* Reusable global data: ticket types, seat plans, stops, categories, organizers, brands, features, forms, FAQs, terms & conditions
* Date, schedule and availability configuration (weekend, special and off dates)
* Flexible pricing with minimum, maximum and reserved quantities
* Passenger information forms with multi-passenger (attendee) support
* Additional services and optional add-ons during booking
* WooCommerce My Account page with a customer "Transport Bookings" list
* Dedicated SEO-friendly pages for categories, locations, brands and organizers
* Customizable labels, slugs, transport icon and dashboard menu icon
* Global feature ON/OFF switches
* Custom CSS variables (colors, fonts, buttons) and company contact settings
* Child-theme template overrides
* WooCommerce integration incl. High-Performance Order Storage (HPOS)
* Responsive, translation-ready frontend

== Return Trip Booking ==

ABP Transport Booking supports return journey booking for passenger transport services.

Customers can book an outbound journey and a return journey when return travel is configured for the transport.

Return booking can be used when passengers need to travel to a destination and return later using the same transport service.

This can be useful for:

* Bus return journeys
* Ferry and launch return trips
* Shuttle return services
* Tourist transportation
* Event transportation
* Scheduled passenger return journeys

== Booking Search & Availability ==

The frontend booking search form finds available transports before checkout:

* Transport selection
* Boarding (from) and drop-off (to) stop selection
* Journey date and return date selection
* Passenger count based on ticket types
* Check availability validation against sold and reserved seats

Available seat capacity is calculated automatically as **total seats minus sold tickets minus reserved seats**, so customers only see journeys with real availability. Cart-level seat holds lock selected seats during the WooCommerce session and release them automatically if the booking is not completed.

== Admin Dashboard ==

The plugin adds a business overview dashboard:

* Total transports, total orders, tickets sold and total revenue
* Bookings made today
* Today's trips with sold / available / total / reserve seat chips
* Recent orders summary
* Quick actions to jump to the most-used settings
* System status and health checklist
* Content breakdown (published, draft, private, trash)
* One-click page creation and demo-data import for testing

== Global Data & Reusable Configuration ==

ABP Transport Booking allows administrators to create common transport data globally and reuse it across multiple transports.

Global data includes:

* Ticket Types
* Seat Plans
* Stops
* Categories
* Organizers
* Brands
* Features
* Additional Services
* Passenger Forms
* FAQs
* Terms & Conditions

Global data helps reduce repetitive configuration when managing multiple transports.

Supported global configurations can be imported and customized for individual transports when required.

== Ticket / Seat Plan Management ==

The dedicated "Ticket/Seat Plan" admin screen manages your reusable seat-plan and ticket-type library:

* Seat plans — add, edit, view, clone and delete
* Drag-and-drop seat layout builder (rows, columns, cell width/height/gaps/radius)
* Automatic seat numbering and custom seat name prefixes
* Dynamic ticket-type assignment per seat
* Background image and color decoration
* Global ticket-type library with label, icon, color and prefix
* Seat color control (available, selected, sold, booked)
* Decor items for visual seat styling

== Global Ticket Types ==

Create ticket types once and reuse them across multiple transports.

Ticket types can be assigned while designing seat plans, allowing different seats or seating areas to use different ticket categories.

Global ticket types are managed independently from individual transports, making them easier to maintain and reuse.

== Global Seat Plans ==

Create a seat plan once and assign it to multiple transports.

The same seat plan can also be assigned multiple times to the same transport, allowing multi-layer seating arrangements.

Seat plan design supports:

* Drag-and-drop seat positioning
* Automatic numbering
* Dynamic ticket type assignment
* Custom seat names and prefixes
* Custom cells and text
* Flexible rows and columns
* Adjustable cell dimensions
* Adjustable spacing and gaps
* Multiple layout configurations
* Background and visual customization
* Multi-layer seat arrangements
* Clone Seat plan
* Edit Seat plan
* Delete Seat plan
* View Seat plan

== Transport Management ==

Manage transports from a dedicated WordPress dashboard.

* Create transports
* Edit transports
* Clone transports
* View transport details
* Delete transports
* Manage routes
* Manage schedules
* Configure ticket types
* Configure seat plans
* Configure pricing
* Configure dates and availability
* Per-transport sale on/off switch
* Frontend template selection (Default, Light, Premium)
* Transport ID/SKU, icon, sub-title and short description
* Category, organizer and brand assignment
* Related transports and features selector
* Image gallery for the frontend slider
* Display related transports

== Frontend Booking Experience ==

The frontend booking experience includes:

* Transport listing pages with search form
* Featured transport slider / gallery with lightbox
* Transport details pages (Default, Light, Premium templates)
* Related transports
* Route and stop information
* Schedule information
* Travel date selection
* Return date selection
* Ticket type selection
* Seat selection
* Passenger information forms (including multi-passenger attendee forms)
* Additional services
* Dynamic pricing
* FAQ and Terms & Conditions display
* WooCommerce checkout
* Responsive booking interface

== Booking and Order Management ==

Manage transport bookings and WooCommerce orders from the dashboard.

* Filter bookings and orders by transport, date, route, order number, customer and more
* Paginate order results
* Line-item booking cancellation
* View complete order details
* View passenger information
* View ticket and seat information
* View booking information
* Check-in status column
* WooCommerce order integration

== Route, Stop & Schedule Management ==

Create and manage transportation routes and their stops.

* Global stop management
* Boarding stops
* Drop-off stops
* Boarding and drop-off stops
* Multiple pickup points
* Multiple drop-off points
* Route direction configuration
* Date-wise schedules
* Travel time configuration
* Special date configuration
* Availability controls

== Seat Reservation & Ticket Types ==

Customers can select available seats during booking when a seat plan is enabled.

The seat system supports:

* Custom seat layouts
* Ticket-type based seating
* Seat name prefixes
* Automatic seat numbering
* Custom seat labels
* Multiple seat layers
* Reusable seat plans
* Real-time seat availability
* Cart-level seat holds to prevent double booking
* Flexible visual seat design

== Pricing & Quantity Control ==

Configure booking quantity and pricing according to transport requirements.

* Minimum quantity
* Maximum quantity
* Reserved quantity
* Dynamic price calculation
* Multiple pricing configurations
* Ticket and seat based pricing options

== Availability Management ==

Control when a transport can be booked.

* Date-wise availability
* Weekend availability controls
* Special date configuration
* Off-date configuration
* Availability overrides
* Schedule and time configuration
* Configured WooCommerce booked statuses (e.g. processing, completed)

== Passenger Information ==

Collect passenger information during the booking process using configurable passenger forms.

* Global passenger forms
* Transport-specific passenger forms
* Custom passenger fields
* Multi-passenger attendee forms
* Passenger information management

== Additional Services ==

Offer optional services during booking.

* Global additional services
* Transport-specific services
* Optional service selection
* Service pricing

== FAQ & Terms and Conditions ==

Create reusable customer information globally or customize it for individual transports.

* Global FAQ configuration
* Transport-specific FAQ configuration
* Global Terms & Conditions
* Transport-specific Terms & Conditions
* Import global configuration
* Customize imported configuration for a transport

== Feature Controls ==

Major plugin features can be enabled or disabled globally from the configuration panel with 23 ON/OFF switches.

This allows administrators to keep the dashboard and frontend focused on the features required for their transportation business.

== Branding & Customization ==

Make the plugin fit your brand:

* Custom transport label and URL slug
* Custom category, organizer, brand and location labels and slugs
* Custom transport booking icon/emoji and dashboard menu icon
* Company contact information (name, address, phone, e-mail)
* Custom CSS variables: colors, fonts, button styles and border radius
* Featured slider theme (carousel or gallery/masonry) with layout options
* Child-theme template overrides for full design control

== My Account Bookings ==

Customers can view all of their transport bookings directly from the WooCommerce **My Account** page.

A **Transport Bookings** menu item is added next to the WooCommerce Orders section. From there, customers can see a list of their bookings with:

* Transport name and order reference
* Route, boarding point, and dropping point
* Travel date, seat, and approximate journey time
* Ticket types and quantities
* Booking total and current status

Bookings are listed newest first with pagination, and each booking shows its current status (processing, completed, cancelled, and so on).

== SEO Friendly ==

Transport, category, location, brand and organizer pages use clean, dedicated permalinks so search engines can index your transport services individually. Reusable transport descriptions, sub-titles and galleries provide rich content for each transport page.

== WooCommerce Integration ==

ABP Transport Booking uses WooCommerce for checkout and payment processing.

Customers can complete transport bookings through WooCommerce checkout and use payment gateways supported by WooCommerce.

WooCommerce handles payment processing, taxes, coupons, customer accounts, and order management according to the site's WooCommerce configuration.

The plugin is compatible with WooCommerce High-Performance Order Storage (HPOS) and declares compatibility with the custom order tables feature.

== Recommended For ==

* Bus operators
* Ferry and launch services
* Shuttle services
* Coach operators
* Van and minibus operators
* Intercity transport services
* Local passenger transport
* Airport transfer services
* Corporate transportation
* School and college transportation
* Tourist transportation
* Event transportation
* Group travel transportation
* Passenger transport agencies
* Multi-route transport businesses

== Requirements ==

* WordPress 6.2 or later
* PHP 7.4 or later
* MySQL 5.7 or later
* WooCommerce 8.0 or later

== Shortcodes ==

Use these shortcodes to display transport content on your website:

[abptb-booking] — Display transport listings with the booking search form.

[abptb-post] — Display transport listings (without the search form).

[abptb-gallery] — Display transport images and galleries.

Each shortcode accepts optional attributes such as `post_id`, `cat_id`, `loc_id`, `brand_id`, `org_id`, `style` (`grid` or `missionary`), `column`, `sort`, `pagination`, and `form` (`inline`) to fine-tune what is displayed.

== Pro Features ==

**ABP Transport Booking PRO** extends the free plugin with advanced booking and management tools.

PRO features include:

* ✅ PDF ticket and invoice generation with QR codes and custom logo/colors
* ✅ Downloadable PDF tickets on the thank-you page and My Account
* ✅ Order Lists PDF and CSV export with download buttons
* ✅ Custom e-mail notifications with PDF attachments and resend option
* ✅ QR code support for tickets
* ✅ Admin order creation — add bookings directly from the dashboard (Add Order tab)
* ✅ Customer cancel-request workflow with configurable time limit
* ✅ Cancel-request approval/rejection with automatic seat and ticket release
* ✅ Seasonal discounts — site-wide and per transport
* ✅ Early-bird discounts — site-wide and per transport
* ✅ Partial payment and deposit booking (deposit plus balance due)
* ✅ Day-wise multiple trip times (up and return journeys)
* ✅ Date-wise multiple trip times (up and return journeys)
* ✅ Mobile check-in for tickets (QR scan on boarding)
* ✅ Enhanced admin order editing tools
* ✅ Advanced booking management tools
* ✅ Premium support
* ✅ Priority updates and new features

PRO requires the free ABP Transport Booking plugin and WooCommerce to be installed and active. Activate your license key from the plugin's License tab to unlock the PRO features.

== Installation ==

= Automatic Installation =

1. Install and activate WooCommerce.
2. Go to Plugins → Add New in your WordPress dashboard.
3. Search for "ABP Transport Booking".
4. Install and activate the plugin.
5. Open Transport Booking from the WordPress admin menu.
6. Configure the global settings.
7. Create global ticket types, seat plans, stops, and other reusable data as required.
8. Create a transport and configure its route, schedule, ticket types, seat plan, pricing, and availability.
9. Configure return trip options when required.
10. Add the booking shortcode to a page.

= Manual Installation =

1. Download the plugin ZIP file.
2. Go to Plugins → Add New → Upload Plugin.
3. Upload the plugin ZIP file.
4. Activate the plugin.
5. Install and activate WooCommerce if it is not already installed.
6. Open Transport Booking from the WordPress admin menu and complete the configuration.

== Frequently Asked Questions ==

= What types of passenger transport can I manage? =

ABP Transport Booking is designed for passenger transport services such as buses, ferries, launches, shuttles, coaches, vans, minibuses, and similar ticket-based transportation services.

= Can I create one-way and return bookings? =

Yes. The plugin supports one-way and return trip bookings when return travel is configured for the transport.

= Can customers book a return trip using the same transport? =

Yes. Customers can book an outbound journey and a return journey using the same transport when return travel is configured and available.

= Can I create reusable ticket types? =

Yes. Ticket types can be created globally and reused across multiple transports and seat plans.

= Can I reuse a seat plan on multiple transports? =

Yes. A global seat plan can be assigned to multiple transports.

= Can I use the same seat plan multiple times in one transport? =

Yes. The same seat plan can be assigned multiple times to the same transport, which can be useful for multi-layer seating arrangements.

= Can I create a custom seat layout? =

Yes. The seat plan designer provides drag-and-drop controls, automatic numbering, dynamic ticket type assignment, custom cells, and multiple layout options.

= Can I manage common transport data globally? =

Yes. Ticket types, seat plans, stops, categories, organizers, brands, features, additional services, passenger forms, FAQs, and Terms & Conditions can be created globally and reused across transports.

= Can I customize global settings for an individual transport? =

Yes. Where supported, global configurations can be imported and customized for an individual transport.

= Can I enable or disable plugin features? =

Yes. Major features can be enabled or disabled globally from the configuration settings with 23 ON/OFF switches.

= Does the plugin use WooCommerce payment gateways? =

Yes. Payments are handled through WooCommerce and its supported payment gateways. The plugin also works with WooCommerce High-Performance Order Storage (HPOS).

= Does the plugin prevent overselling seats? =

Yes. Real-time availability is calculated from sold and reserved seats, and selected seats are locked during the WooCommerce session with cart-level seat holds that expire automatically.

= Can I manage bookings and orders from the dashboard? =

Yes. The plugin provides a filterable order list with pagination, line-item cancellation, and detailed booking and order information.

= Is the plugin translation-ready? =

Yes. ABP Transport Booking is translation-ready and compatible with standard WordPress localization tools.

= Can I use the plugin in a local development environment? =

Yes. The plugin can be used in local WordPress development environments such as XAMPP and LocalWP.

== Need help or have suggestions? ==
If you need any further assistance or support, please contact us through the [🎫 support form](https://abp-team.com/support-desk/). We welcome your suggestions, so feel free to tell us anything we can improve in the plugin.

🌐 [Live Demo](https://transport-booking.abp-team.com/)
📖 [Documentation](https://transport-booking.abp-team.com/documentation/)
💬 [Support Forum](https://wordpress.org/support/plugin/abp-transport-booking/)
🐛 [Bug Reports](https://github.com/abpteam24/abp-transport-booking/issues)
📧 Email: support@abp-team.com

If you find ABP Transport Booking useful, please leave a ⭐⭐⭐⭐⭐ review on WordPress.org — it really helps!


== Screenshots ==

1. Transport List Page – Grid View
2. Transport List Page – List View
3. All Transport Bookings – Grid View
4. Transport Details – Default Template with Ticket Types
5. Transport Details – Default Template with Return Transport
6. Transport Details – Light Template with Seat Plan and Single Attendee Information
7. Transport Details – Light Template with Seat Plan, Single Attendee Information, and Return Transport
8. Related Transport
9. Transport List – Admin View
10. Order List and Filter – Admin View
11. Global Ticket Type and Seat Plan Configuration
12. Global Reusable Date Configuration
13. Global Reusable Additional Service Configuration
14. Global Reusable Passenger Form Configuration
15. Global Reusable Stops and Location Configuration
16. Transport Type / Category Configuration
17. Transport Feature Configuration
18. Transport Global Configuration
19. Global Feature Controls
20. Transport General Configuration
21. Multi-Layer Seat Plan Configuration for Transport
22. Transport Ticket Type Configuration
23. Transport Route Configuration with Return Route
24. Transport Price Configuration with Return Route
25. Transport Time Configuration with Return Route


== Changelog ==

= 1.0.4 =

* Fixed booking search results and AJAX-loaded content not displaying the full design and layout.
* Fixed the seat plan legend (Available / Selected / Sold) not showing correctly during booking.
* Fixed missing transport and grid-view icons on the frontend.
* Improved frontend styling consistency across the booking and seat plan interfaces.

Released: September 6, 2026

= 1.0.3 =

* Fixed responsive layout issues across different screen sizes and devices.
* Improved mobile and tablet compatibility for booking and transport-related interfaces.
* Fixed minor UI alignment and spacing issues on responsive layouts.
* Improved overall responsive behavior and user interface consistency.

Released: August 29, 2026

= 1.0.2 =

* Added a new filter field to the Order List for improved order management and filtering.
* Improved the Order List design and overall admin UI experience.
* Fixed various design and UI issues.
* Fixed several minor bugs and improved overall plugin stability.

Released: August 21, 2026

= 1.0.1 =

* Improved Pickup Point and Drop-off Point management.
* Updated checkout validation for better booking reliability.
* Fixed various design and UI issues.
* Improved overall user experience and interface consistency.

Released: August 17, 2026

= 1.0.0 =

* Initial release.

== Upgrade Notice ==

= 1.0.4 =
Fixed seat plan legend and booking search result styling on the frontend.
Released: September 6, 2026

= 1.0.3 =
Released: August 29, 2026

= 1.0.2 =
Released: August 21, 2026

= 1.0.1 =
Released: August 17, 2026

= 1.0.0 =
Initial release.