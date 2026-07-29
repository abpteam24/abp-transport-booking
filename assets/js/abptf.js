let abptf_route_info = JSON.parse(abptf_infos.route_info);
let abptf_location_info = JSON.parse(abptf_infos.location_info);
(function ($) {
    "use strict";
    let abptf_booking = abptf_parent.find('div.abptf_booking');
    $(document).ready(function () {
        abptf_parent.find('#abptf_search_area').each(function () {
            load_bp($(this));
        })
    });
    //console.log(abptf_route_info);
    function load_bp(parent) {
        let post_id = $.trim(parent.find('[name="post_id"]').val());
        if (!abptf_route_info) return;
        parent.find('.abptf_bp ul').html('');
        parent.find('.abptf_dp ul').html('');
        parent.find('.abptf_bp input').val('');
        parent.find('.abptf_dp input').val('');
        let rawData;
        if (post_id) {
            if (abptf_route_info[post_id]) {
                let postData = abptf_route_info[post_id];
                rawData = postData['all_bp'] || rawData;
            }
        } else {
            rawData = abptf_route_info['all_bp'];
        }
        if (!rawData) return;
        let routes = Array.isArray(rawData) ? rawData : Object.values(rawData).flat();
        if (!Array.isArray(routes)) return;
        let locationMap = {};
        if (Array.isArray(abptf_location_info)) {
            abptf_location_info.forEach(loc => {
                locationMap[loc.id.toString()] = loc.label;
            });
            let optionsHtml = routes.map(slot => {
                let label = locationMap[slot.toString()] || '';
                return `<li data-value="${slot}" data-text="${label}"><span class="fas fa-map-marker-alt _mar_r_xxs"></span>${label}</li>`;
            }).join('');
            parent.find('.abptf_bp ul').html(optionsHtml);
        }
    }
    function load_dp(parent) {
        if (typeof abptf_route_info === 'undefined' || !abptf_route_info) return;
        let post_id = $.trim(parent.find('[name="post_id"]').val());
        let bp = $.trim(parent.find('[name="_bp"]').val());
        parent.find('.abptf_dp input').val('');
        if (!bp) {
            parent.find('.abptf_dp ul').html('');
            return;
        }
        let validDpList = new Set();
        let extractDpFromObject = (routeObj) => {
            if (routeObj && typeof routeObj === 'object' && routeObj[bp]) {
                let dpArray = routeObj[bp]; // BP key-er bhetorer DP array
                if (Array.isArray(dpArray)) {
                    dpArray.forEach(dpId => validDpList.add(dpId.toString()));
                }
            }
        };
        if (post_id) {
            if (abptf_route_info[post_id]) {
                extractDpFromObject(abptf_route_info[post_id]);
            }
        } else {
            Object.keys(abptf_route_info).forEach(key => {
                if (key !== 'all_bp') {
                    if (abptf_route_info[key]) {
                        extractDpFromObject(abptf_route_info[key]);
                    }
                }
            });
        }
        let locationMap = {};
        if (typeof abptf_location_info !== 'undefined' && Array.isArray(abptf_location_info)) {
            abptf_location_info.forEach(loc => {
                locationMap[loc.id.toString()] = loc.label;
            });
        }
        let optionsHtml = Array.from(validDpList).map(slot => {
            let label = locationMap[slot] || '';
            return `<li data-value="${slot}" data-text="${label}"><span class="fas fa-map-marker-alt _mar_r_xxs"></span>${label}</li>`;
        }).join('');
        parent.find('.abptf_dp ul').html(optionsHtml);
    }
    abptf_parent.on('click', '.pagination_item .select_post', function (e) {
        e.preventDefault();
        let post_id = parseInt($(this).attr('data-post_id'));
        let target = $(this).closest('div.abptf_area').find('#abptf_search_area .post_selection .dropdown_list li');
        target.each(function () {
            if (parseInt($(this).attr('data-value')) === post_id) {
                $(this).trigger('click');
                return true;
            }
        });
    });
    abptf_parent.on('abp_trigger', '#abptf_search_area [name="post_id"]', function () {
        let parent = $(this).closest("#abptf_search_area");
        let target_return = parent.find('.return_date');
        let target_journey = parent.find('.journey_date');
        load_bp(parent);
        let post_id = parent.find('[name="post_id"]').val();
        let formData = new FormData();
        formData.append('post_id', post_id);
        formData.append('action', 'abptf_load_date');
        formData.append('nonce', abptf_infos.nonce);
        $.ajax({
            type: 'POST', url: abptf_infos.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(target_return);
                abptf_spinner(target_journey);
                abptf_toast_msg(abptf_infos.msg.date_loading);
            },
            success: function (response) {
                abptf_spinner_remove(target_journey);
                abptf_spinner_remove(target_return);
                if (response.data && response.data.hasOwnProperty('html_journey')) {
                    target_journey.html(response.data.html_journey).promise().done(function () {
                        if (response.data.hasOwnProperty('picker_config') && response.data.picker_config) {
                            abptf_init_dynamic_date_pickers(response.data.journey, response.data.picker_config);
                        } else {
                            abptf_load_datepicker(target_journey);
                        }
                    });
                    if (target_return.length > 0 && response.data.hasOwnProperty('html_return') && response.data.html_return) {
                        target_return.slideDown('fast');
                        target_return.html(response.data.html_return).promise().done(function () {
                            if (response.data.hasOwnProperty('picker_config') && response.data.picker_config) {
                                abptf_init_dynamic_date_pickers(response.data.return, response.data.picker_config);
                            } else {
                                abptf_load_datepicker(target_return);
                            }
                        });
                    } else {
                        target_return.slideUp('fast');
                    }
                    abptf_toast_msg(response.data.msg, response.data.type);
                } else {
                    abptf_toast_msg(response.data.msg, response.data.type);
                }
            }, error: function (xhr) {
                abptf_ajx_error(xhr, target_journey);
            }
        });
    });
    abptf_parent.on('abp_trigger', '#abptf_search_area [name="_bp"]', function () {
        load_dp($(this).closest('#abptf_search_area'));
    });
    abptf_parent.on('change', "#abptf_search_area [name='journey_date']", function (e) {
        e.preventDefault();
        let parent = $(this).closest("#abptf_search_area");
        let target = parent.find('.return_date');
        if (target.length > 0) {
            let date = parent.find('[name="journey_date"]').val();
            let post_id = parent.find('[name="post_id"]').val();
            let formData = new FormData();
            formData.append('post_id', post_id);
            formData.append('journey_date', date);
            formData.append('action', 'abptf_load_return_date');
            formData.append('nonce', abptf_infos.nonce);
            $.ajax({
                type: 'POST', url: abptf_infos.ajax_url, contentType: false, processData: false, data: formData,
                beforeSend: function () {
                    abptf_spinner(target);
                    abptf_toast_msg(abptf_infos.msg.end_date_loading);
                },
                success: function (response) {
                    abptf_spinner_remove(target);
                    if (response.data && response.data.hasOwnProperty('html')) {
                        target.html(response.data.html).promise().done(function () {
                            if (response.data.hasOwnProperty('picker_config') && response.data.picker_config) {
                                abptf_init_dynamic_date_pickers(response.data.selector, response.data.picker_config);
                            } else {
                                abptf_load_datepicker(target);
                            }
                        });
                        abptf_toast_msg(response.data.msg, response.data.type);
                    } else {
                        abptf_toast_msg(response.data.msg, response.data.type);
                    }
                }, error: function (xhr) {
                    abptf_ajx_error(xhr, target);
                }
            });
        }
    });
    abptf_parent.on('submit', '#abptf_search_area form.search_form', function (e) {
        e.preventDefault();
        let form_area = $(this).closest('#abptf_search_area');
        if ($.trim(form_area.find('[name="_bp"]').val()).length === 0) {
            setTimeout(function () {
                abptf_toast_msg(abptf_infos.msg.bp_select);
                form_area.find('[name="_bp"]').siblings('input').click();
            }, 100);
            return;
        }
        if ($.trim(form_area.find('[name="_dp"]').val()).length === 0) {
            setTimeout(function () {
                abptf_toast_msg(abptf_infos.msg.dp_select);
                form_area.find('[name="_dp"]').siblings('input').click();
            }, 100);
            return;
        }
        let post_id = parseInt(form_area.find('[name="post_id"]').val());
        if (post_id && post_id > 0) {
            if ($.trim(form_area.find('[name="journey_date"]').val()).length === 0) {
                setTimeout(function () {
                    abptf_toast_msg(abptf_infos.msg.select_journey_date);
                    form_area.find('#journey_date').focus();
                }, 100);
                return;
            }
        }
        let target = abptf_parent.find('.abptf_booking');
        let formData = new FormData(this);
        formData.append('action', 'abptf_global_booking');
        formData.append('nonce', abptf_infos.nonce);
        $.ajax({
            type: 'POST', url: abptf_infos.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(target);
                abptf_spinner(form_area);
                abptf_toast_msg(abptf_infos.msg.loading);
            },
            success: function (response) {
                abptf_spinner_remove(target);
                abptf_spinner_remove(form_area);
                abptf_toast_msg(response.data.msg, response.data.type);
                if (response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html).promise().done(function () {
                        abptf_init(target);
                    });
                } else {
                    window.location.reload();
                }
            }, error: function (xhr) {
                abptf_ajx_error(xhr, target);
            }
        });
    });
    //==============//
    abptf_booking.on('change', "[name='journey_time']", function (e) {
        e.preventDefault();
        let target = $(this).closest(".booking_area");
        let parent = $(this).closest("form");
        let formData = abptf_get_form_data(target);
        formData.append('post_id', parent.find('[name="post_id"]').val());
        formData.append('double_route', parent.find('[name="double_route"]').val());
        formData.append('action', 'abptf_load_transport_data');
        formData.append('nonce', abptf_infos.nonce);
        $.ajax({
            type: 'POST', url: abptf_infos.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg(abptf_infos.msg.loading);
            },
            success: function (response) {
                abptf_spinner_remove(target);
                abptf_toast_msg(response.data.msg, response.data.type);
                if (response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html).promise().done(function () {
                        abptf_init(target);
                    });
                }
            }, error: function (xhr) {
                abptf_ajx_error(xhr, target);
            }
        });
    });
    abptf_booking.on('abp_trigger', "[name='item_check[]'],[name='return_item_check[]']", function (e) {
        e.preventDefault();
        let $this = $(this);
        let parent = $this.closest(".booking_area");
        let form = $this.closest("form");
        let max_qty = parseInt(form.find('[name="max_qty"]').val());
        let r = $this.closest('.booking_area').find('[name="return_item_qty[]"]').length > 0;
        let data_id = $this.attr('data-id');
        let target = parent.find('[data-collapse="' + data_id + '"]');
        let item_parent = $this.closest('.ticket_item');
        if (!item_parent.hasClass('abp_active')) {
            item_parent.find('[name="return_item_qty[]"]').val(parseInt(item_parent.find('[name="return_item_qty[]"]').attr('data-min')));
            item_parent.find('[name="item_qty[]"]').val(parseInt(item_parent.find('[name="item_qty[]"]').attr('data-min')));
        }
        if (r) {
            let qty = get_quantity_return(parent);
            if (max_qty > 0 && qty > max_qty) {
                item_parent.find('[data-checked]').trigger('abp_role_back');
                abptf_toast_msg(form.find('[name="max_qty"]').attr('data-msg'), 'warn');
            } else {
                if (target.length > 0) {
                    target.slideToggle('fast');
                    item_parent.toggleClass('abp_active');
                }
                item_parent.find('[name="return_item_qty[]"]').trigger('change');
            }
        } else {
            let qty = get_quantity(parent);
            if (max_qty > 0 && qty > max_qty) {
                item_parent.find('[data-checked]').trigger('abp_role_back');
                abptf_toast_msg(form.find('[name="max_qty"]').attr('data-msg'), 'warn');
            } else {
                if (target.length > 0) {
                    target.slideToggle('fast');
                    item_parent.toggleClass('abp_active');
                }
                item_parent.find('[name="item_qty[]"]').trigger('change');
            }
        }
    });
    abptf_booking.on('change', '[name="item_qty[]"],[name="return_item_qty[]"]', function (e) {
        e.preventDefault();
        let $this = $(this);
        let parent = $(this).closest('div.abptf_booking');
        let max_qty = parseInt(parent.find('[name="max_qty"]').val());
        let r = $this.closest('.booking_area').find('[name="return_item_qty[]"]').length > 0;
        if (r) {
            let qty = get_quantity_return(parent);
            if (max_qty > 0 && qty > max_qty) {
                $this.val(parseInt($this.val()) - 1);
                abptf_toast_msg(parent.find('[name="max_qty"]').attr('data-msg'), 'warn');
            } else {
                all_management(parent);
            }
        } else {
            let qty = get_quantity(parent);
            if (max_qty > 0 && qty > max_qty) {
                $this.val(parseInt($this.val()) - 1);
                abptf_toast_msg(parent.find('[name="max_qty"]').attr('data-msg'), 'warn');
            } else {
                all_management(parent);
            }
        }
    })
    abptf_booking.on('change', '.ex_price_calculate', function () {
        let parent = $(this).closest('div.abptf_booking');
        all_management(parent);
    });
    abptf_booking.on('click', '.book_continue', function (e) {
        e.preventDefault();
        let current = $(this);
        let parent = current.closest('div.abptf_booking');
        if (get_quantity(parent) > 0) {
            if (submit_validation(current) < 1) {
                parent.find("[name='add-to-cart']").trigger('click');
                parent.find("[name='add-admin-order']").trigger('click');
            }
        } else {
            abptf_alert(current);
        }
    });
    function all_management(parent) {
        let total = 0;
        let qty = get_quantity(parent);
        let return_qty = get_quantity_return(parent);
        let total_qty = qty + return_qty;
        let price = 0;
        let return_price = 0;
        let ex_price = 0;
        let return_ex_price = 0;
        if (total_qty > 0) {
            if (qty > 0) {
                price = get_price(parent);
                ex_price = get_additional_price(parent);
                parent.find('.total_continue_area .price_up').slideDown('fast');
                parent.find('.additional_service_area:not(.booking_area.return .additional_service_area)').slideDown('fast');
            }else{
                parent.find('.total_continue_area .price_up').slideUp('fast');
                parent.find('.total_continue_area .ex_price_up').slideUp('fast')
                parent.find('.additional_service_area:not(.booking_area.return .additional_service_area)').slideUp('fast');
            }
            if (return_qty > 0) {
                return_price = get_price_return(parent);
                return_ex_price = get_additional_price_return(parent);
                parent.find('.total_continue_area .price_down').slideDown('fast');
                parent.find('.booking_area.return .additional_service_area').slideDown('fast');
            }else{
                parent.find('.total_continue_area .price_down').slideUp('fast');
                parent.find('.total_continue_area .ex_price_down').slideUp('fast')
                parent.find('.booking_area.return .additional_service_area').slideUp('fast');
            }
            total = price + ex_price + return_price + return_ex_price;
            parent.find('.total_continue_area').slideDown('fast');
        } else {
            parent.find('.additional_service_area').slideUp('fast');
            parent.find('.total_continue_area').slideUp('fast');
        }
        total = total > 0 ? abptf_wc_price_format(total) : abptf_infos.msg.free;
        parent.find('.abptf_total').html(total);
        // abptf_load_image();
    }
    function get_quantity(parent) {
        let qty = 0;
        parent.find('.item_select:not(.booking_area.return .item_select)').each(function () {
            let current = $(this);
            let active_property = $.trim(current.find('[name="item_check[]"]').val());
            if (active_property) {
                qty = qty + parseInt($.trim(current.find('[name="item_qty[]"]').val()));
            }
        });
        return qty;
    }
    function get_quantity_return(parent) {
        let qty = 0;
        if (parent.find('.booking_area.return').length > 0) {
            parent.find('.return .item_select').each(function () {
                let current = $(this);
                let active_property = $.trim(current.find('[name="return_item_check[]"]').val());
                if (active_property ) {
                    qty = qty + parseInt($.trim(current.find('[name="return_item_qty[]"]').val()));
                }
            });
        }
        return qty;
    }
    function get_price(parent) {
        let total = 0;
        parent.find('.item_select:not(.booking_area.return .item_select)').each(function () {
            let current = $(this);
            let active_property =$.trim(current.find('[name="item_check[]"]').val());
            if (active_property ) {
                let target = current.find('[name="item_qty[]"]');
                let price = parseFloat($.trim(target.attr('data-price')));
                price = price && price >= 0 ? price : 0;
                total = total + price * parseInt($.trim(target.val()));
            }
        });
        let total_price = total > 0 ? abptf_wc_price_format(total) : abptf_infos.msg.free;
        parent.find('.price_up .item_total').html(total_price);
        return total;
    }
    function get_price_return(parent) {
        let total = 0;
        if (parent.find('.booking_area.return').length > 0) {
            parent.find('.return .item_select').each(function () {
                let current = $(this);
                let active_property = $.trim(current.find('[name="return_item_check[]"]').val());
                if (active_property) {
                    let target = current.find('[name="return_item_qty[]"]');
                    let price = parseFloat($.trim(target.attr('data-price')));
                    price = price && price >= 0 ? price : 0;
                    total = total + price * parseInt($.trim(target.val()));
                }
            });
            let total_price = total > 0 ? abptf_wc_price_format(total) : abptf_infos.msg.free;
            parent.find('.price_down .item_total').html(total_price);
        }
        return total;
    }
    function get_additional_price(parent) {
        let target = parent.find('.ex_price_calculate:not(.booking_area.return .ex_price_calculate)');
        let total = 0;
        if (target.length > 0) {
            let ex_qty = 0;
            target.each(function () {
                let qty =  parseInt($(this).val());
                ex_qty+=qty;
                let ex_price = $(this).attr('data-price');
                ex_price = ex_price && ex_price >= 0 ? ex_price : 0;
                total = total + parseFloat(ex_price) * qty;
            });
            let total_price = total > 0 ? abptf_wc_price_format(total) : abptf_infos.msg.free;
            if (ex_qty > 0) {
                parent.find('.total_continue_area .ex_price_up').slideDown('fast').find('.additional_total').html(total_price);
            } else {
                parent.find('.total_continue_area .ex_price_up').slideUp('fast').find('.additional_total').html(total_price);
            }
        }
        return total;
    }
    function get_additional_price_return(parent) {
        let target = parent.find('.booking_area.return .ex_price_calculate');
        let total = 0
        let ex_qty = 0;
        if (target.length > 0) {
            target.each(function () {
                let qty =  parseInt($(this).val());
                ex_qty+=qty;
                let ex_price = $(this).attr('data-price');
                ex_price = ex_price && ex_price >= 0 ? ex_price : 0;
                total = total + parseFloat(ex_price) * qty;
            });
            let total_price = total > 0 ? abptf_wc_price_format(total) : abptf_infos.msg.free;
            if (ex_qty > 0) {
                parent.find('.total_continue_area .ex_price_down').slideDown('fast').find('.additional_total').html(total_price);
            } else {
                parent.find('.total_continue_area .ex_price_down').slideUp('fast').find('.additional_total').html(total_price);
            }
        }
        return total;
    }
    function submit_validation(current) {
        let exit = 0;
        current.closest('form').find("[required]").each(function () {
            let value = $(this).val();
            if (!value || value === ' ' || value === 'undefined' || value === '') {
                $(this).trigger('focus').addClass('abp_required');
                exit++;
            }
        });
        return exit;
    }
}(jQuery));