/**
 * LMB Core Frontend Scripts
 *
 * Handles AJAX interactions for user widgets.
 */

jQuery(document).ready(function($) {
    // User Stats Widget
    $('.lmb-user-stats').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        $.ajax({
            url: lmbAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lmb_get_user_stats',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $widget.find('[data-type="balance"]').text(response.data.balance);
                    $widget.find('[data-type="drafts"]').text(response.data.drafts);
                    $widget.find('[data-type="published"]').text(response.data.published);
                }
            }
        });
    });

    // Invoices Widget
    $('.lmb-invoices').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');

        function loadTab(tab, status) {
            $.ajax({
                url: lmbAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_get_invoices',
                    nonce: nonce,
                    tab: tab,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        if (tab === 'accuse') {
                            $widget.find('.lmb-accuse-list').html(response.data.accuse.map(item => `
                                <li><a href="${item.url}" target="_blank">${item.title}</a> (${item.date})</li>
                            `).join(''));
                        } else {
                            $widget.find('.lmb-invoices-list').html(response.data.invoices.map(item => `
                                <li>${item.number} (${item.status}, ${item.price}, ${item.package}, Ref: ${item.reference}, ${item.date})</li>
                            `).join(''));
                        }
                    }
                }
            });
        }

        $widget.find('.lmb-tab').click(function() {
            $widget.find('.lmb-tab').removeClass('active');
            $widget.find('.lmb-tab-content').hide();
            $(this).addClass('active');
            $widget.find('#lmb-tab-' + $(this).data('tab')).show();
            loadTab($(this).data('tab'), $widget.find('#lmb-tab-' + $(this).data('tab')).find('[name="status"]').val());
        });

        $widget.find('.lmb-invoices-filter').submit(function(e) {
            e.preventDefault();
            var tab = $widget.find('.lmb-tab.active').data('tab');
            var status = $(this).find('[name="status"]').val();
            loadTab(tab, status);
        });

        loadTab('accuse', 'all');
    });

    // Upload Bank Proof Widget
    $('.lmb-upload-bank-proof').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        $.ajax({
            url: lmbAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lmb_get_invoices',
                nonce: nonce,
                tab: 'invoices',
                status: 'unpaid'
            },
            success: function(response) {
                if (response.success && response.data.invoices) {
                    $widget.find('[name="invoice_id"]').append(response.data.invoices.map(item => `
                        <option value="${item.id}">${item.number} (${item.package}, ${item.price})</option>
                    `));
                }
            }
        });

        $widget.find('.lmb-bank-proof-form').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('action', 'lmb_upload_bank_proof');
            formData.append('nonce', nonce);
            $.ajax({
                url: lmbAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $widget.find('.lmb-bank-proof-message').text(response.success ? response.data.message : response.data.message);
                }
            });
        });
    });

    // Subscribe Package Widget
    $('.lmb-subscribe-package').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        $.ajax({
            url: lmbAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lmb_update_packages',
                nonce: nonce
            },
            success: function(response) {
                if (response.success && response.data.packages) {
                    $widget.find('[name="package_id"]').append(response.data.packages.map((p, i) => `
                        <option value="${i + 1}">${p.name} (${p.price} MAD, ${p.points} points)</option>
                    `));
                }
            }
        });

        $widget.find('.lmb-package-form').submit(function(e) {
            e.preventDefault();
            var package_id = $widget.find('[name="package_id"]').val();
            $.ajax({
                url: lmbAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_subscribe_package',
                    nonce: nonce,
                    package_id: package_id
                },
                success: function(response) {
                    $widget.find('.lmb-package-message').text(response.success ? response.data.message : response.data.message);
                }
            });
        });
    });

    // Notifications Widget (shared with admin)
    $('.lmb-notifications').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        $.ajax({
            url: lmbAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'lmb_get_notifications',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $widget.find('.lmb-notifications-list').html(response.data.map(item => `
                        <li data-id="${item.id}">${item.message} (${item.time}) <button class="lmb-mark-read">Mark Read</button></li>
                    `).join(''));
                }
            }
        });
        $widget.on('click', '.lmb-mark-read', function() {
            var notification_id = $(this).parent().data('id');
            $.ajax({
                url: lmbAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_mark_notification_read',
                    nonce: nonce,
                    notification_id: notification_id
                },
                success: function(response) {
                    if (response.success) {
                        $(`li[data-id="${notification_id}"]`).remove();
                    }
                }
            });
        });
    });
});