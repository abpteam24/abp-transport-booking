window.abptf_parent = window.abptf_parent || jQuery('div.abptf_admin');
let abptf_feature_data = JSON.parse(abptf_admin_data.feature_data);
let abptf_related_info = JSON.parse(abptf_admin_data.related_info);
let abptf_sp_info = JSON.parse(abptf_admin_data.sp_data);
function abptf_admin_init(target) {
    abptf_sortable(target);
    abptf_color_picker_init(target);
    //abptf_wp_editor_init(target);
    abptf_location_selection(target);
    abptf_ticket_type_selection(target);
}
function abptf_sortable(target = abptf_parent) {
    let $sortable = target.find('.sortable_area:not(.abp_hidden *)');
    if ($sortable.length === 0) {
        $sortable = target.closest('.sortable_area');
    }
    if ($sortable.length > 0) {
        $sortable.sortable({
            handle: '.sortable_handle',
            stop: function (event, ui) {
                ui.item.trigger('abp_trigger');
            }
        });
    }
}
function abptf_color_picker_init(target = abptf_parent) {
    let $pickers = target.find('.abp_color_picker:not(.abp_hidden *)');
    if ($pickers.length > 0) {
        $pickers.wpColorPicker({
            change: function (event, ui) {
                setTimeout(function () {
                    jQuery(event.target).trigger('abp_trigger');
                }, 50);
            },
            clear: function (event) {
                setTimeout(function () {
                    jQuery(event.target).trigger('abp_trigger');
                }, 50);
            }
        });
    }
}
function abptf_wp_editor_init(target = abptf_parent) {
    let textArea = target.find('textarea.wp-editor-area:not(.abp_hidden *)');
    if (textArea.length > 0) {
        let existingId = textArea.attr('id');
        if (existingId && typeof tinymce !== 'undefined' && tinymce.get(existingId)) {
            return;
        }
        let uniqueId = existingId || ('editor_' + Math.random().toString(36).substring(2, 11));
        if (target.find('.wp-editor-wrap').length > 0) {
            target.find('.wp-editor-wrap').replaceWith(textArea);
        }
        textArea.attr('id', uniqueId).show();
        setTimeout(function () {
            if (typeof wp !== 'undefined' && wp.editor) {
                wp.editor.remove(uniqueId);
                wp.editor.initialize(uniqueId, {
                    tinymce: {
                        wpautop: true,
                        cleanup: false,
                        verify_html: false,
                        entity_encoding: 'raw',
                        forced_root_block: false,
                        valid_elements: '*[*]',
                        setup: function (editor) {
                            editor.on('change', function () {
                                editor.save();
                            });
                        }
                    },
                    quicktags: true,
                    mediaButtons: true
                });
            }
        }, 100);
    }
}
function abptf_location_selection(target = abptf_parent) {
    const $selects = target.find('.route_configuration [name="stop_name[]"]');
    if ($selects.length > 0) {
        const selectedValues = $selects.map(function () {
            return jQuery(this).val();
        }).get().filter(value => value !== "");
        $selects.each(function () {
            const $currentSelect = jQuery(this);
            const currentValue = $currentSelect.val();
            $currentSelect.html('<option value="" selected>' + abptf_admin_data.msg.select_stops + '</option>');
            abptf_location_info.forEach(function (location) {
                if (!selectedValues.includes(location.id.toString()) || location.id.toString() === currentValue) {
                    const $option = jQuery('<option></option>').val(location.id).text(location.label);
                    if (location.id.toString() === currentValue) {
                        $option.prop('selected', true);
                    }
                    $currentSelect.append($option);
                }
            });
        });
    }
    const $selects_return = target.find('.return_route_configuration [name="return_stop_name[]"]');
    if ($selects_return.length > 0) {
        const selectedValues = $selects_return.map(function () {
            return jQuery(this).val();
        }).get().filter(value => value !== "");
        $selects_return.each(function () {
            const $currentSelect = jQuery(this);
            const currentValue = $currentSelect.val();
            $currentSelect.html('<option value="" selected>' + abptf_admin_data.msg.select_stops + '</option>');
            abptf_location_info.forEach(function (location) {
                if (!selectedValues.includes(location.id.toString()) || location.id.toString() === currentValue) {
                    const $option = jQuery('<option></option>').val(location.id).text(location.label);
                    if (location.id.toString() === currentValue) {
                        $option.prop('selected', true);
                    }
                    $currentSelect.append($option);
                }
            });
        });
    }
}
function abptf_ticket_type_selection(target = abptf_parent) {
    const $selects = target.find('.ticket_configuration [name="ticket_name[]"]');
    if ($selects.length > 0) {
        const selectedValues = $selects.map(function () {
            return jQuery(this).val();
        }).get().filter(value => value !== "");
        $selects.each(function () {
            const $currentSelect = jQuery(this);
            const currentValue = $currentSelect.val();
            $currentSelect.html('<option value="" selected>' + abptf_admin_data.msg.select_ticket + '</option>');
            abptf_ticket_type.forEach(function (ticket) {
                if (!selectedValues.includes(ticket.id.toString()) || ticket.id.toString() === currentValue) {
                    const $option = jQuery('<option></option>').val(ticket.id).text(ticket.label);
                    if (ticket.id.toString() === currentValue) {
                        $option.prop('selected', true);
                    }
                    $currentSelect.append($option);
                }
            });
        });
    }
}
function abptf_load_post_list(parent, filter_args) {
    let target = parent.find('.post_list');
    if (target.length > 0) {
        jQuery.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_reload_post_list", "filter_args": filter_args, 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.post_loading);
            }, success: function (response) {
                target.html(response.data.html);
                abptf_spinner_remove(parent);
                abptf_toast_msg(response.data.html, 'success');
            }
        });
    } else {
        parent.find('.post_tab').trigger('click');
    }
}
function abptf_emoji_check(str) {
    return !(/^fa[bsrld]\s/.test(str));
}
//========== Global Function =================//
window.abptf_popup_open_global = function (action, id = '') {
    if (action) {
        jQuery('body').addClass('_stop_scroll').find('[data-popup="#abptf_global_popup"]').addClass('in').promise().done(function () {
            let parent = abptf_parent.find('[data-popup="#abptf_global_popup"]').find('.popup_area').addClass(action);
            id = id !== '' ? id : '';
            let target = parent.find('.popup_body');
            let post_id = abptf_parent.find("[name='abptf_post_id']").val() || '';
            jQuery.ajax({
                type: 'POST', url: abptf_admin_data.ajax_url, data: {
                    "action": 'abptf_add_' + action, 'id': id, 'post_id': post_id, 'nonce': abptf_admin_data.nonce
                }, beforeSend: function () {
                    abptf_spinner(parent);
                    abptf_toast_msg(abptf_admin_data.msg.loading);
                }, success: function (response) {
                    abptf_spinner_remove(parent);
                    if (response.data && response.data.hasOwnProperty('html')) {
                        target.html(response.data.html).promise().done(function () {
                            abptf_toast_msg(response.data.msg, response.data.type);
                            abptf_init(target);
                        });
                    }
                }, error: function (xhr) {
                    abptf_ajx_error(xhr, parent);
                }
            })
        });
    }
};
window.abptf_popup_close_global = function () {
    let deferred = jQuery.Deferred();
    let target = abptf_parent.find('[data-popup="#abptf_global_popup"]');
    if (target.length > 0) {
        target.removeClass('in').promise().done(function () {
            jQuery('body').removeClass('_stop_scroll');
            target.find('.popup_area').removeClass().addClass('popup_area').find('.popup_body').html('').promise().done(function () {
                deferred.resolve(true);
            });
        });
    } else {
        deferred.resolve(true);
    }
    return deferred.promise();
};
window.abptf_save_global = function (action, $_this) {
    if (action) {
        let $this = jQuery($_this);
        let parent = $this.closest('.abp_form');
        let formData = abptf_get_form_data(parent);
        let post_page = abptf_parent.find("[name='abptf_post_id']");
        if (post_page.length > 0) {
            formData.append('post_id', post_page.val());
        }
        formData.append('nonce', abptf_admin_data.nonce);
        formData.append('action', 'abptf_save_' + action);
        jQuery.when(abptf_popup_close_global()).done(function (isClosed) {
            if (isClosed) {
                let target = abptf_parent.find('.' + action);
                jQuery.ajax({
                    type: 'POST',
                    url: abptf_admin_data.ajax_url,
                    contentType: false,
                    processData: false,
                    data: formData,
                    beforeSend: function () {
                        abptf_spinner(target);
                        abptf_toast_msg(abptf_admin_data.msg.saving);
                    },
                    success: function (response) {
                        if (target && target.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                            target.html(response.data.html).promise().done(function () {
                                abptf_init(target);
                            });
                        }
                        if (response.data.hasOwnProperty('js')) {
                            if (action === 'ticket_type') {
                                abptf_ticket_type = response.data.js;
                                abptf_sp_init();
                                abptf_ticket_type_selection();
                            }
                            if (action === 'decor_item') {
                                abptf_decor_item = response.data.js;
                                abptf_sp_init();
                            }
                            if (action === 'tax_location') {
                                abptf_location_info = response.data.js;
                                abptf_location_selection();
                            }
                            if (action === 'option_feature') {
                                abptf_feature_data = response.data.js;
                                new ABPTF_Multi_Selection('div.abptf_admin .post_feature', abptf_feature_data);
                            }
                        }
                        abptf_spinner_remove(target);
                        abptf_toast_msg(response.data.msg, response.data.type);
                    },
                    error: function (xhr) {
                        abptf_ajx_error(xhr, target);
                    }
                });
            }
        });
    }
};
window.abptf_delete_global = function (action, id = '') {
    if (confirm(abptf_admin_data.msg.confirm_delete + ' \n\n' + abptf_admin_data.msg.confirm_ok + ' \n ' + abptf_admin_data.msg.confirm_cancel)) {
        if (action && id) {
            let target = abptf_parent.find('.' + action);
            jQuery.ajax({
                type: 'POST', url: abptf_admin_data.ajax_url, data: {
                    "action": 'abptf_delete_' + action, 'id': id, 'nonce': abptf_admin_data.nonce
                }, beforeSend: function () {
                    abptf_spinner(target);
                    abptf_toast_msg(abptf_admin_data.msg.deleting, 'error');
                }, success: function (response) {
                    if (response.data && response.data.hasOwnProperty('html')) {
                        target.html(response.data.html).promise().done(function () {
                            abptf_init(target);
                        });
                    }
                    if (response.data.hasOwnProperty('js')) {
                        if (action === 'ticket_type') {
                            abptf_ticket_type = response.data.js;
                            abptf_sp_init();
                        }
                        if (action === 'decor_item') {
                            abptf_decor_item = response.data.js;
                            abptf_sp_init();
                        }
                    }
                    abptf_toast_msg(response.data.msg, response.data.type);
                    abptf_spinner_remove(target);
                }, error: function (xhr) {
                    abptf_ajx_error(xhr, target);
                }
            })
        }
    }
};
window.abptf_post_action = function (action, id) {
    id = id !== '' ? parseInt(id) : '';
    if (action && !isNaN(id) && id !== '') {
        let parent = abptf_parent.find('.abptf_posts')
        jQuery.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": 'abptf_post_' + action, 'post_id': id, 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg((abptf_admin_data.msg[action] ? abptf_admin_data.msg[action] : abptf_admin_data.msg.loading), 'warn');
            }, success: function (response) {
                abptf_spinner_remove(parent);
                abptf_toast_msg(response.data.msg, response.data.type);
                window.location.reload();
            }, error: function (xhr) {
                abptf_ajx_error(xhr, parent);
            }
        });
    }
};
window.abptf_import_global = function (action) {
    if (action) {
        let target = abptf_parent.find('.' + action);
        if (action === 'dummy') {
            target = abptf_parent.find('.abp_status');
        }
        jQuery.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": 'abptf_import_' + action, 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg((abptf_admin_data.msg[action] ? abptf_admin_data.msg[action] : abptf_admin_data.msg.loading));
            }, success: function (response) {
                abptf_spinner_remove(target);
                abptf_toast_msg(response.data.msg, response.data.type);
                if (action === 'dummy') {
                    window.location.reload();
                } else {
                    if (target && target.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                        target.html(response.data.html).promise().done(function () {
                            abptf_init(target);
                            abptf_wp_editor_init(target);
                        });
                    }
                }
            }, error: function (xhr) {
                abptf_ajx_error(xhr, target);
            }
        });
    }
};
window.abptf_create_page = function (page_type) {
    if (page_type) {
        let parent = abptf_parent.find('.abp_status');
        jQuery.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_create_page", 'nonce': abptf_admin_data.nonce, 'type': page_type
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.create_post_page);
            }, success: function (response) {
                abptf_toast_msg(response.data.msg, response.data.type);
                window.location.reload();
            }, error: function (xhr) {
                abptf_ajx_error(xhr, target);
            }
        });
    }
};
window.abptf_wc_config = function (page_type) {
    if (page_type) {
        let parent = abptf_parent.find('.abp_status');
        jQuery.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": "abptf_wc_config", 'nonce': abptf_admin_data.nonce, 'type': page_type
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg((abptf_admin_data.msg[page_type] ? abptf_admin_data.msg[page_type] : abptf_admin_data.msg.loading));
            }, success: function (response) {
                abptf_toast_msg(response.data.msg, response.data.type);
                window.location.reload();
            }, error: function (xhr) {
                abptf_ajx_error(xhr, parent);
            }
        });
    }
};
//==================image selection========================//
let abptf_media_uploader;
window.abptf_image_remove = function ($this) {
    $this = jQuery($this);
    let parent = $this.closest('.image_selection');
    if (parent && parent.length > 0) {
        parent.find('input').val('');
        parent.find('img').attr('src', '');
        parent.find('button').slideDown('fast');
        parent.find('.image_item').slideUp('fast');
        let id = parent.find('input').attr('data-target');
        let target = jQuery(id);
        if (target && target.length > 0) {
            jQuery(target).css('background-image', '');
        }
    }
};
window.abptf_image_selection = function ($this) {
    $this = jQuery($this);
    if (!abptf_media_uploader) {
        abptf_media_uploader = wp.media({
            multiple: false
        });
        abptf_media_uploader.on('select', function () {
            let attachment = abptf_media_uploader.state().get('selection').first().toJSON();
            let parent = abptf_media_uploader.current_target;
            if (parent && parent.length > 0) {
                parent.find('input').val(attachment.id);
                parent.find('img').attr('src', attachment.url);
                parent.find('button').slideUp('fast');
                parent.find('.image_item').slideDown('fast');
                let id = parent.find('input').attr('data-target');
                let target = jQuery(id);
                if (target && target.length > 0) {
                    jQuery(target).css('background-image', `url(${attachment.url})`);
                }
            }
        });
    }
    abptf_media_uploader.current_target = $this.closest('.image_selection');
    abptf_media_uploader.open();
};
(function ($) {
    "use strict";
    //==========Post Action=================//
    $(document).on('click', 'div.abptf_admin .post_list .pagination_area button[data-page]', function () {
        let $this = $(this);
        if (!$this.hasClass('abp_active')) {
            let parent = $(this).closest('.abptf_posts');
            let filter_args = {};
            if (parent.find("[name='select_hidden_post_status']").length > 0) {
                filter_args['status'] = parent.find("[name='select_hidden_post_status']").val();
            }
            filter_args['page_number'] = parseInt($this.attr('data-page'));
            if (parent.find("[name='page_item']").length > 0) {
                filter_args['page_item'] = parseInt(parent.find("[name='page_item']").val());
            }
            abptf_load_post_list(parent, filter_args);
        }
    });
    //==========Route , ticket config=================//
    abptf_parent.on('abp_trigger', '.abptf_routing .add_new_hook', function () {
        abptf_location_selection();
    });
    abptf_parent.on('change', '.abptf_routing [name="stop_name[]"] , .abptf_routing [name="return_stop_name[]"]', function () {
        abptf_location_selection();
    });
    abptf_parent.on('abp_trigger', '.ticket_configuration .add_new_hook', function () {
        abptf_ticket_type_selection();
    });
    abptf_parent.on('change', '.ticket_configuration [name="ticket_name[]"] ', function () {
        abptf_ticket_type_selection();
    });
    abptf_parent.on('change', '.abptf_ticket [name="seat_type"]', function () {
        load_ticket_type($(this));
    });
    abptf_parent.on('abp_trigger', '.abptf_ticket [name="display_ticket_type"]', function () {
        load_ticket_type($(this));
    });
    abptf_parent.on('change', '.sp_selection_area [name="sp_id[]"]', function () {
        let sp_id = $(this).val();
        let parent = $(this).closest('tr');
        let grand_parent = $(this).closest('.ticket_configuration');
        let $targetContainer = parent.find('.ticket_type_details');
        $targetContainer.empty();
        parent.find('.row_total').html('');
        if (!sp_id || !abptf_sp_info[sp_id]) {
            return;
        }
        let total_seat = 0;
        let ticketTypes = abptf_sp_info[sp_id];
        let html = '<div class="_group_list">';
        Object.keys(ticketTypes).forEach(function (key) {
            let item = ticketTypes[key];
            let label = item.label || '';
            let color = item.color || '#333';
            let icon = item.icon || '';
            let img = item.img || '';
            let icon_emoji = '';
            if (img && $.isNumeric(icon) && icon > 0) {
                icon_emoji = '<div class="abp_image"><img class="_img_control"  src="' + img + '" alt="#"></div>';
            } else {
                if (abptf_emoji_check(icon)) {
                    icon_emoji = '<span>' + icon + '</span>';
                } else {
                    icon_emoji = '<span class="' + icon + '"></span>';
                }
            }
            let seatCount = parseInt(item.seat, 10) || 0;
            total_seat = total_seat + seatCount;
            if (label !== '') {
                html += `<div class="_list_item">`;
                html += `    <h6 class="_abp" style="color: ${color}">`;
                if (icon_emoji !== '') {
                    html += ` ${icon_emoji}`;
                }
                html += `        ${label}`;
                html += `    </h6>`;
                html += `    <span class="_mar_l_xs_circle_icon_xs">${seatCount}</span>`;
                html += `</div>`;
            }
        });
        html += '</div>';
        parent.find('.row_total').html(total_seat);
        let total_seat_count = 0;
        grand_parent.find('.row_total').each(function () {
            total_seat_count = total_seat_count + parseInt($(this).html(), 0);
        }).promise().done(function () {
            grand_parent.find('.transport_total').html(total_seat_count);
        });
        parent.find('.sp_id_change').attr('onclick', "abptf_popup_open_global('view_sp','" + sp_id + "')");
        $targetContainer.html(html);
    });
    window.abptf_price_load = function () {
        let target = abptf_parent.find('div.abptf_price');
        let formData = new FormData();
        let display_return = abptf_parent.find("[name='display_return']").val();
        let seat_type = abptf_parent.find("[name='seat_type']").val();
        let display_ticket_type = abptf_parent.find("[name='display_ticket_type']").val();
        formData.append('post_id', abptf_parent.find("[name='abptf_post_id']").val());
        formData.append('display_return', display_return);
        formData.append('seat_type', seat_type);
        formData.append('display_ticket_type', display_ticket_type);
        let route_infos = [];
        abptf_parent.find('.route_configuration .delete_area').each(function () {
            let place = $(this).find('[name="stop_name[]"]').val();
            let type = $(this).find('[name="stop_type[]"]').val();
            if (place && type) {
                route_infos.push({
                    stop: place,
                    type: type
                });
            }
        });
        formData.append('routing_infos', JSON.stringify(route_infos));
        if (display_return === 'on') {
            let return_route_infos = [];
            abptf_parent.find('.return_route_configuration .delete_area').each(function () {
                let place = $(this).find('[name="return_stop_name[]"]').val();
                let type = $(this).find('[name="return_stop_type[]"]').val();
                if (place && type) {
                    return_route_infos.push({
                        stop: place,
                        type: type
                    });
                }
            });
            formData.append('return_routing_infos', JSON.stringify(return_route_infos));
        }
        let ticket_infos = {};
        let price_infos = {};
        let return_price_infos = {};
        let tr_target = '';
        if (display_ticket_type === 'on') {
            if (seat_type === 'ticket') {
                abptf_parent.find('.ticket_selection_area tr').each(function () {
                    let ticket_name = $(this).find('[name="ticket_name[]"]').val();
                    if (ticket_name) {
                        ticket_infos[ticket_name] = ticket_name;
                    }
                });
            } else {
                tr_target = abptf_parent.find('.sp_selection_area tr');
                tr_target.each(function () {
                    let sp_id = $(this).find('[name="sp_id[]"]').val();
                    if (sp_id && abptf_sp_info[sp_id]) {
                        let ticketTypes = abptf_sp_info[sp_id];
                        Object.keys(ticketTypes).forEach(function (key) {
                            let ticket_name = key;
                            if (ticket_name) {
                                ticket_infos[ticket_name] = ticket_name;
                            }
                        });
                    }
                });
            }
        } else {
            ticket_infos['price'] = 'price';
        }
        Object.keys(ticket_infos).forEach(function (ticket_name) {
            let price_name = display_ticket_type === 'on' ? ticket_name + '_price[]' : 'route_price[]';
            abptf_parent.find('.price_infos [name="' + price_name + '"]').each(function () {
                let bp_dp = $(this).closest('tr').find('[name="route_id[]"]').val();
                if (bp_dp) {
                    if (!price_infos[bp_dp]) price_infos[bp_dp] = {};
                    price_infos[bp_dp][ticket_name] = $(this).val();
                }
            });
            if (display_return === 'on') {
                let return_price_name = display_ticket_type === 'on' ? 'return_' + ticket_name + '_price[]' : 'return_route_price[]';
                abptf_parent.find('.return_price_infos [name="' + return_price_name + '"]').each(function () {
                    let bp_dp = $(this).closest('tr').find('[name="return_route_id[]"]').val();
                    if (bp_dp) {
                        if (!return_price_infos[bp_dp]) return_price_infos[bp_dp] = {};
                        return_price_infos[bp_dp][ticket_name] = $(this).val();
                    }
                });
            }
        });
        formData.append('all_ticket_type', JSON.stringify(ticket_infos));
        formData.append('price_infos', JSON.stringify(price_infos));
        formData.append('return_price_infos', JSON.stringify(return_price_infos));
        formData.append('action', 'abptf_reload_pricing');
        formData.append('nonce', abptf_admin_data.nonce);
        get_price_info(formData, target);
    };
    function get_price_info(formData, target) {
        jQuery.ajax({
            type: 'POST',
            url: abptf_admin_data.ajax_url,
            contentType: false,
            processData: false,
            data: formData,
            beforeSend: function () {
                abptf_spinner(target);
                abptf_toast_msg(abptf_admin_data.msg.price_loading);
            },
            success: function (response) {
                if (target && target.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html);
                }
                abptf_spinner_remove(target);
                abptf_toast_msg(response.data.msg, response.data.type);
            },
            error: function (xhr) {
                abptf_spinner_remove(target);
                if (xhr.response && xhr.response.data) {
                    abptf_toast_msg(xhr.response.data.msg, xhr.response.data.type);
                }
            }
        });
    }
    function load_ticket_type($this) {
        let parent = $this.closest('.abptf_ticket');
        let type = abptf_parent.find("[name='seat_type']").val();
        let display_ticket_type = abptf_parent.find("[name='display_ticket_type']").val();
        let target = parent.find('.ticket_configuration');
        let post_id = abptf_parent.find("[name='abptf_post_id']").val();
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, data: {
                "action": 'abptf_type_switch', 'type': type, 'display_ticket_type': display_ticket_type, 'post_id': post_id, 'nonce': abptf_admin_data.nonce
            }, beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.type_switch);
            }, success: function (response) {
                if (target && target.length > 0 && response.data && response.data.hasOwnProperty('html')) {
                    target.html(response.data.html);
                }
                abptf_spinner_remove(parent);
                abptf_toast_msg(response.data.msg, response.data.type);
            }, error: function (xhr) {
                abptf_spinner_remove(parent);
                if (xhr.response && xhr.response.data) {
                    abptf_toast_msg(xhr.response.data.msg, xhr.response.data.type);
                }
            }
        });
    }
    //==========Orders list=================//
    $(document).on('submit', 'div.abptf_admin form.load_order_list', function (e) {
        e.preventDefault();
        let parent = $(this).closest('.abptf_orders');
        let target = parent.find('.order_list');
        let formData = new FormData(this);
        if (parent.find('[data-page].abp_active').length > 0) {
            formData.append('page_number', parseInt(parent.find('[data-page].abp_active').attr('data-page')));
        }
        formData.append('page_item', parseInt(parent.find("[name='page_item']").val()));
        formData.append('status', parent.find('.order_status_menu [data-status].abp_active').attr('data-status'));
        formData.append('action', 'abptf_load_order_list');
        formData.append('nonce', abptf_admin_data.nonce);
        $.ajax({
            type: 'POST', url: abptf_admin_data.ajax_url, contentType: false, processData: false, data: formData,
            beforeSend: function () {
                abptf_spinner(parent);
                abptf_toast_msg(abptf_admin_data.msg.order_loading);
            },
            success: function (response) {
                abptf_spinner_remove(parent);
                if (response.data) {
                    if (response.data.hasOwnProperty('html')) {
                        target.html(response.data.html).promise().done(function () {
                            abptf_init(target);
                        });
                    }
                    abptf_toast_msg(response.data.msg, response.data.type);
                }
            }
        });
    });
    $(document).on('click', 'div.abptf_admin .order_status_menu button[data-status]', function () {
        let $this = $(this);
        if (!$this.hasClass('abp_active')) {
            $this.closest('.order_status_menu').find('[data-status].abp_active').removeClass('abp_active').promise().done(function () {
                $this.addClass('abp_active').promise().done(function () {
                    $this.closest('.abptf_orders').find('form.load_order_list').submit();
                });
            });
        }
    });
    $(document).on('click', 'div.abptf_admin button.abptf_item_cancel', function () {
        let $this = $(this);
        let parent = $(this).closest('.abptf_orders');
        let item_id = $this.attr('data-item_id');
        if (confirm(abptf_admin_data.msg.confirm_delete + ' \n\n' + abptf_admin_data.msg.confirm_ok + ' \n ' + abptf_admin_data.msg.confirm_cancel)) {
            $.ajax({
                type: 'POST', url: abptf_admin_data.ajax_url, data: {
                    "action": "abptf_item_cancel", 'item_id': item_id, 'nonce': abptf_admin_data.nonce
                }, beforeSend: function () {
                    abptf_spinner(parent);
                    abptf_toast_msg(abptf_admin_data.msg.deleting, 'error');
                }, success: function (response) {
                    abptf_spinner_remove(parent);
                    abptf_toast_msg(response.data.msg);
                    $this.closest('.abptf_orders').find('form.load_order_list').submit();
                }
            });
        }
    });
    $(document).on('click', 'div.abptf_admin .order_list .pagination_area button[data-page]', function () {
        let $this = $(this);
        if (!$this.hasClass('abp_active')) {
            let parent = $(this).closest('.order_list');
            parent.find('[data-page].abp_active').removeClass('abp_active').promise().done(function () {
                $this.addClass('abp_active').promise().done(function () {
                    $this.closest('.abptf_orders').find('form.load_order_list').submit();
                });
            });
        }
    });
}(jQuery));
//==============Empty title check /image selection/add_new_delete============================//
(function ($) {
    'use strict';
    $(document).ready(function () {
        //=========== Feature  selection=================//
        new ABPTF_Multi_Selection('div.abptf_admin .post_feature', abptf_feature_data);
        //=========== Related post  selection=================//
        new ABPTF_Multi_Selection('div.abptf_admin .related_item', abptf_related_info);
        //=========Color Picker==============//
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.wp-picker-container').length) {
                $('.wp-picker-container.wp-picker-active').find('.wp-color-result').trigger('click');
            }
        });
    });
    //========= Empty title check==============//
    $(document).on('click', '#publish, .editor-post-publish-button', function (e) {
        let hasPostIdInput = $('input[name="abptf_post_id"]').length > 0;
        if (hasPostIdInput) {
            let title = $('#title').val() || $('.editor-post-title__input').val();
            if (!title || title.trim().length === 0) {
                alert('Title empty! Please enter a title before updating.');
                e.preventDefault();
                return false;
            }
        }
    });
    //==================image selection========================//
    $(document).on('click', 'div.abptf_admin .add_image_multi', function () {
        let parent = $(this).closest('.multiple_image_area');
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            let html = '<div class="multiple_image_item" data-image-id="' + attachment_id + '"><span class="fas fa-times _circle_icon_xs remove_image_multi"></span>';
            html += '<img class="_img_control" src="' + attachment_url + '" alt="' + attachment_id + '"/>';
            html += '</div>';
            parent.find('.multiple_image').append(html);
            let value = parent.find('.multiple_image_ids').val();
            value = value ? value + ',' + attachment_id : attachment_id;
            parent.find('.multiple_image_ids').val(value);
        }
        wp.media.editor.open($(this));
        return false;
    });
    $(document).on('click', 'div.abptf_admin .remove_image_multi', function () {
        let parent = $(this).closest('.multiple_image_area');
        let current_parent = $(this).closest('.multiple_image_item');
        let img_id = current_parent.data('image-id');
        current_parent.remove();
        let all_img_ids = parent.find('.multiple_image_ids').val();
        all_img_ids = all_img_ids.replace(',' + img_id, '')
        all_img_ids = all_img_ids.replace(img_id + ',', '')
        all_img_ids = all_img_ids.replace(img_id, '')
        parent.find('.multiple_image_ids').val(all_img_ids);
    });
    $(document).on('click', 'div.abptf_admin .icon_image_selection_area .icon_delete', function () {
        let parent = $(this).closest('.icon_image_selection_area');
        parent.find('input[type="hidden"]').val('');
        parent.find('[data-add-icon]').removeAttr('class');
        parent.find('.icon_item').slideUp('fast');
        parent.find('.image_icon_select_area').slideDown('fast');
    });
    $(document).on('click', 'div.abptf_admin button.image_select', function () {
        let $this = $(this);
        let parent = $this.closest('.icon_image_selection_area');
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            parent.find('input[type="hidden"]').val(attachment_id);
            parent.find('.icon_item').slideUp('fast');
            parent.find('img').attr('src', attachment_url);
            parent.find('.image_item').slideDown('fast');
            parent.find('.image_icon_select_area').slideUp('fast');
        }
        wp.media.editor.open($this);
        return false;
    });
    $(document).on('click', 'div.abptf_admin .icon_image_selection_area .image_delete', function () {
        let parent = $(this).closest('.icon_image_selection_area');
        parent.find('input[type="hidden"]').val('');
        parent.find('img').attr('src', '');
        parent.find('.image_item').slideUp('fast');
        parent.find('.image_icon_select_area').slideDown('fast');
    });
    //=========add_new_delete ==============//
    abptf_parent.on('click', '.delete_hook', function () {
        if (confirm(abptf_admin_data.msg.confirm_delete + ' \n\n' + abptf_admin_data.msg.confirm_ok + ' \n ' + abptf_admin_data.msg.confirm_cancel)) {
            let deleteArea = $(this).closest('.delete_area');
            let parent = $(this).closest('.configuration_content');
            deleteArea.slideUp(250, function () {
                $(this).remove();
                if (parent.find('.insertable_area .delete_area').length === 0) {
                    parent.find('.hide_on_load').slideUp(250);
                }
            });
            abptf_toast_msg(abptf_admin_data.msg.delete_success);
        }
    });
    abptf_parent.on('click', '.add_new_hook', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        let parent = $(this).closest('.configuration_content');
        let target_element = $(this).next('.abp_hidden');
        if (target_element.length === 0) {
            target_element = parent.children('.abp_hidden');
        }
        if (target_element.length === 0) {
            target_element = parent.find('.abp_hidden').first();
        }
        let item_html = target_element.find('.hidden_content').html();
        if (!item_html || item_html === "undefined" || item_html === " ") {
            target_element = parent.find('.abp_hidden').first();
            item_html = target_element.find('.hidden_content').html();
        }
        let $item = $(item_html);
        if (target_element.attr('data-hidden_id') !== undefined) {
            if (item_html && item_html !== "undefined" && item_html.trim() !== "") {
                let current_id = $item.find('.hidden_id').val();
                let unique_id = 'abp_' + Date.now();
                $item.find('.hidden_id').val(unique_id);
                $item.find('input, select, textarea').each(function () {
                    let current_name = $(this).attr('name');
                    if (current_name && current_id) {
                        let regex = new RegExp(current_id, 'g');
                        let new_name = current_name.replace(regex, unique_id);
                        $(this).attr('name', new_name);
                    }
                });
            }
        }
        let $insertable_area = parent.find('.insertable_area').first();
        $insertable_area.append($item);
        let target = $item.hasClass('delete_area') ? $item : $item.find('.delete_area');
        if (target.length > 0) {
            target.find('.edit_area').slideDown('fast');
            parent.find('.hide_on_load').slideDown('fast');
            abptf_init(target);
            abptf_wp_editor_init(target);
        }
        $(this).trigger('abp_trigger');
    });
    abptf_parent.on('click', '.edit_hook', function () {
        $(this).closest('.delete_area').toggleClass('active').find('.edit_area').slideToggle('fast');
        //$(this).closest('.delete_area').find('.edit_area').slideToggle('fast');
    });
    abptf_parent.on('keyup change', '[data-pass]', function () {
        let input_value = $(this).val();
        let input_id = $(this).attr('data-pass');
        $(this).closest('.delete_area').find("[data-paste='" + input_id + "']").each(function () {
            $(this).html(input_value);
        });
    });
})(jQuery);
//=================select icon=========================//
(function ($) {
    'use strict';
    let abptf_target_popup = $(document).find('div.abptf_admin .popup_icon');
    let abptf_category_list = abptf_target_popup.find('.dropdown_list');
    let abptf_search_field = abptf_target_popup.find('.abp_dropdown .abp_icon_search');
    let abptf_icon_title = abptf_target_popup.find('.item_icon_title');
    let abptf_icon_area = abptf_target_popup.find('.item_icon_area');
    let abptf_item_loader = abptf_target_popup.find('.item_loader');
    let search_result_icon = [];
    let total_icon = 0;
    let abptf_json_icon = [];
    $.getJSON(abptf_admin_data.icon_url, function (data) {
        abptf_json_icon = data;
        load_icon_category_list();
    }).fail(function () {
        abptf_icon_area.html('Nothing Found !');
    });
    $(document).on('click', 'div.abptf_admin .icon_image_selection_area button.icon_add', function () {
        load_icon_list();
    });
    $(document).on('abp_trigger', 'div.abptf_admin .abp_dropdown .abp_icon_search_hidden', function () {
        let search_value = $(this).val().toLowerCase().trim();
        if (search_value === '' || search_value.length > 2) {
            load_icon_list();
        }
    });
    abptf_search_field.keyup(function () {
        let search_value = $(this).val().toLowerCase().trim();
        if (search_value === '' || search_value.length > 2) {
            load_icon_list();
        }
    });
    abptf_search_field.change(function () {
        let search_value = $(this).val().toLowerCase().trim();
        if (search_value === '' || search_value.length > 2) {
            load_icon_list();
        }
    });
    abptf_target_popup.find('.popup_close').click(function () {
        abptf_search_field.val('').trigger('change');
        abptf_target_popup.find('.icon_item').removeClass('abp_active');
    });
    abptf_target_popup.on('click', '.icon_item', function () {
        let parent = $('[data-active-popup]').closest('.icon_image_selection_area');
        let icon_class = $(this).data('icon-class');
        if (icon_class) {
            parent.find('input[type="hidden"]').val(icon_class);
            parent.find('.image_icon_select_area').slideUp('fast');
            parent.find('.image_item').slideUp('fast');
            parent.find('.icon_item').slideDown('fast');
            if (abptf_emoji_check(icon_class)) {
                parent.find('[data-add-icon]').removeAttr('class').html(icon_class);
            } else {
                parent.find('[data-add-icon]').removeAttr('class').addClass(icon_class).html('');
            }
            abptf_target_popup.find('.icon_item').removeClass('abp_active');
            abptf_target_popup.find('.popup_close').trigger('click');
        }
    });
    // ─── get search icon array / initial array───────────
    function get_icon_array() {
        let pool = [];
        let search_value = abptf_search_field.val().toLowerCase().trim();
        if (search_value) {
            $.each(abptf_json_icon, function (i, group) {
                if (group.category.toLowerCase().includes(search_value)) {
                    $.each(group.icons, function (iconKey, iconLabel) {
                        let match = iconLabel.match(/#(.*?)#/);
                        let finalLabel = match ? match[1] : iconLabel;
                        pool.push({key: iconKey, label: finalLabel});
                    });
                    return pool;
                } else {
                    if (i !== 0) {
                        $.each(group.icons, function (iconKey, iconLabel) {
                            if (iconLabel.toLowerCase().includes(search_value)) {
                                let match = iconLabel.match(/#(.*?)#/);
                                let finalLabel = match ? match[1] : iconLabel;
                                pool.push({key: iconKey, label: finalLabel});
                            }
                        });
                    }
                }
            });
        } else {
            let group = abptf_json_icon[0];
            if (!group) return [];
            $.each(group.icons, function (iconKey, iconLabel) {
                pool.push({key: iconKey, label: iconLabel});
            });
        }
        return pool;
    }
    // ─── load input category ───────────
    function load_icon_category_list() {
        let category_list = $('<ul>').addClass('_abp');
        $.each(abptf_json_icon, function (i, group) {
            let current_count = Object.keys(group.icons).length;
            if (i !== 0) {
                total_icon += current_count;
            }
            let text = group.category;
            let category_li = $('<li>').attr('data-value', text).attr('data-text', text);
            $('<span>').addClass('_mar_r_xxs').text(group.emoji).appendTo(category_li);
            $('<span>').text(text).appendTo(category_li);
            $('<span>').text('( ' + current_count + ' )').appendTo(category_li);
            category_li.appendTo(category_list);
        });
        category_list.appendTo(abptf_category_list);
        abptf_spinner(abptf_item_loader);
    }
    function load_icon_list() {
        abptf_icon_area.empty();
        search_result_icon = get_icon_array();
        if (search_result_icon.length === 0) {
            abptf_icon_area.html('Nothing Found !');
            updateCount();
            return;
        }
        $.each(search_result_icon, function (i, item) {
            let $item = $('<div>').addClass('icon_item').attr('title', item.label).attr('data-icon-class', item.key);
            let $preview;
            if (abptf_emoji_check(item.key)) {
                $preview = $('<span>').text(item.key);
            } else {
                $preview = $('<span>').addClass(item.key);
            }
            $item.append($preview);
            $item.append($('<i>').text(item.label));
            $item.appendTo(abptf_icon_area);
        });
        updateCount();
    }
    function updateCount() {
        let search_value = abptf_search_field.val();
        search_value = search_value ? search_value : 'Selected Icon'
        abptf_icon_title.text(search_value + ' : ' + search_result_icon.length + ' / ' + total_icon + ' icons');
    }
})(jQuery);
//=========== Multi selection start=================//
class ABPTF_Multi_Selection {
    constructor(parentSelector, dataSource) {
        this.parent = document.querySelector(parentSelector);
        if (!this.parent) return;
        this.dataSource = dataSource;
        this.hiddenInput = this.parent.querySelector('input[type="hidden"]');
        this.selectedList = this.parent.querySelector('.selected_list');
        this.init();
    }
    init() {
        this.searchEl = this.parent.querySelector('.item_search');
        this.featureListEl = this.parent.querySelector('.selection_list');
        this.loadPreSelected();
        this.bindEvents();
        this.render();
    }
    bindEvents() {
        if (this.searchEl) {
            ['focusin', 'click'].forEach(eventType => {
                this.searchEl.addEventListener(eventType, (e) => {
                    e.stopPropagation();
                    this.featureListEl?.classList.add('active');
                });
            });
            this.searchEl.addEventListener('input', () => this.render());
        }
        document.addEventListener('click', (e) => {
            if (!e.target.closest(this.parent.className.split(' ').map(c => '.' + c).join(''))) {
                this.featureListEl?.classList.remove('active');
            }
        });
    }
    loadPreSelected() {
        if (!this.hiddenInput || !this.selectedList) return;
        let hiddenVal = this.hiddenInput.value;
        let preIds = hiddenVal ? hiddenVal.split(',').map(s => s.trim()).filter(Boolean) : [];
        if (preIds.length > 0) {
            this.selectedList.innerHTML = '';
            preIds.forEach(id => {
                let f = this.dataSource.find(x => String(x.id) === String(id));
                if (!f) return;
                this.appendSelectedItem(f);
            });
        }
    }
    getSelectedIds() {
        let ids = [];
        this.parent.querySelectorAll('.selected_item').forEach(el => {
            let id = el.getAttribute('data-id');
            if (id) ids.push(id);
        });
        return ids;
    }
    render() {
        if (!this.featureListEl) return;
        let q = this.searchEl ? this.searchEl.value.toLowerCase() : '';
        let selectedIds = this.getSelectedIds();
        let available = this.dataSource.filter(f => {
            return selectedIds.indexOf(String(f.id)) === -1 && f.label.toLowerCase().indexOf(q) !== -1;
        });
        if (available.length === 0) {
            this.featureListEl.innerHTML = `<div class="item_empty">${abptf_admin_data.msg.no_item}</div>`;
            return;
        }
        this.featureListEl.innerHTML = available.map(f => {
            let icon_text = abptf_emoji_check(f.icon) ? `<span class="_mar_r_xxs">${f.icon}</span>` : `<span class="${f.icon} _mar_r_xxs"></span>`;
            let label = f.value ? f.label + '-' + f.value : f.label;
            return `
                <div class="selection_item" data-id="${f.id}">
                    <div>${icon_text}${label}</div>
                    <span class="fa-solid fa-plus fs-add"></span>
                </div>
            `;
        }).join('');
        this.featureListEl.querySelectorAll('.selection_item').forEach(item => {
            item.addEventListener('click', () => {
                this.selectItem(item.getAttribute('data-id'));
            });
        });
    }
    selectItem(id) {
        let f = this.dataSource.find(x => String(x.id) === String(id));
        if (!f) return;
        let placeholder = this.selectedList.querySelector('.item_empty');
        if (placeholder) placeholder.remove();
        this.appendSelectedItem(f);
        this.updateHiddenField();
        this.render();
        setTimeout(() => {
            this.featureListEl?.classList.add('active');
        }, 0);
    }
    appendSelectedItem(f) {
        let div = document.createElement('div');
        div.className = 'selected_item';
        div.setAttribute('data-id', f.id);
        let icon_text = abptf_emoji_check(f.icon) ? `<span class="_mar_r_xxs">${f.icon}</span>` : `<i class="${f.icon} _mar_r_xxs"></i>`;
        let label = f.value ? f.label + '-' + f.value : f.label;
        div.innerHTML = `
            <div class="_fa_center">${icon_text}${label}</div>
            <span class="item_remove">❌</span>
        `;
        div.querySelector('.item_remove').addEventListener('click', (e) => {
            e.stopPropagation();
            this.removeItem(f.id);
        });
        this.selectedList.appendChild(div);
    }
    removeItem(id) {
        let item = this.selectedList.querySelector(`.selected_item[data-id="${id}"]`);
        if (item) item.remove();
        if (!this.selectedList.querySelector('.selected_item')) {
            this.selectedList.innerHTML = `<div class="item_empty">${abptf_admin_data.msg.no_item_selected}</div>`;
        }
        this.updateHiddenField();
        this.render();
    }
    updateHiddenField() {
        if (this.hiddenInput) {
            this.hiddenInput.value = this.getSelectedIds().join(',');
        }
    }
}
