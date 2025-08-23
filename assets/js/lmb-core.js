/**
 * LMB Core Frontend Scripts
 *
 * Handles AJAX interactions for user and public widgets.
 */

jQuery(document).ready(function($) {
    // Elementor Form Submission for Legal Ads
    $(document).on('submit', '.elementor-form', function(e) {
        e.preventDefault();
        var $form = $(this);
        var formData = new FormData(this);
        formData.append('action', 'lmb_submit_legal_ad');
        formData.append('nonce', lmbAjax.submit_legal_ad_nonce);

        $.ajax({
            url: lmbAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $form.find('.elementor-message').remove();
                if (response.success) {
                    $form.append('<div class="elementor-message elementor-message-success">' + response.data.message + '</div>');
                } else {
                    $form.append('<div class="elementor-message elementor-message-danger">' + response.data.message + '</div>');
                }
            },
            error: function() {
                $form.find('.elementor-message').remove();
                $form.append('<div class="elementor-message elementor-message-danger">' + __('An error occurred. Please try again.', 'lmb-core') + '</div>');
            }
        });
    });

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
                            $widget.find('.lmb-accuse-list').html(response.data.accuse && response.data.accuse.length ? response.data.accuse.map(item => `
                                <li><a href="${item.url}" target="_blank">${item.title}</a> (${item.date})</li>
                            `).join('') : '<li>' + __('No accuse files available.', 'lmb-core') + '</li>');
                        } else {
                            $widget.find('.lmb-invoices-list').html(response.data.invoices && response.data.invoices.length ? response.data.invoices.map(item => `
                                <li>
                                    <span>${item.number}</span>
                                    <span>${item.package}</span>
                                    <span>${item.price}</span>
                                    <span>${item.status}</span>
                                    <span>${item.date}</span>
                                    ${item.status === 'unpaid' ? `<button class="lmb-upload-bank-proof" data-invoice-id="${item.id}">${__('Upload Bank Proof', 'lmb-core')}</button>` : ''}
                                </li>
                            `).join('') : '<li>' + __('No invoices available.', 'lmb-core') + '</li>');
                        }
                    }
                }
            });
        }

        // Initial load
        loadTab('invoices', $widget.find('.lmb-status-filter').val());

        // Tab switch
        $widget.find('.lmb-tab').on('click', function() {
            $widget.find('.lmb-tab').removeClass('active');
            $(this).addClass('active');
            var tab = $(this).data('tab');
            loadTab(tab, $widget.find('.lmb-status-filter').val());
        });

        // Status filter change
        $widget.find('.lmb-status-filter').on('change', function() {
            var tab = $widget.find('.lmb-tab.active').data('tab');
            loadTab(tab, $(this).val());
        });
    });

    // Upload Bank Proof
    $(document).on('click', '.lmb-upload-bank-proof', function() {
        var $button = $(this);
        var invoiceId = $button.data('invoice-id');
        var $input = $('<input type="file" accept=".pdf,.jpg,.png">');
        $input.on('change', function() {
            var formData = new FormData();
            formData.append('action', 'lmb_upload_bank_proof');
            formData.append('nonce', lmbAjax.lmb_upload_bank_proof_nonce);
            formData.append('invoice_id', invoiceId);
            formData.append('bank_proof_file', $input[0].files[0]);

            $.ajax({
                url: lmbAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        $button.remove();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert(__('An error occurred. Please try again.', 'lmb-core'));
                }
            });
        });
        $input.click();
    });

    // Subscribe Package Widget
    $('.lmb-subscribe-package').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        $widget.find('.lmb-package-select').on('change', function() {
            var packageId = $(this).val();
            if (packageId) {
                $.ajax({
                    url: lmbAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lmb_subscribe_package',
                        nonce: nonce,
                        package_id: packageId
                    },
                    success: function(response) {
                        if (response.success) {
                            $widget.find('.lmb-message').remove();
                            $widget.append('<div class="lmb-message lmb-message-success">' + response.data.message + '</div>');
                        } else {
                            $widget.find('.lmb-message').remove();
                            $widget.append('<div class="lmb-message lmb-message-error">' + response.data.message + '</div>');
                        }
                    }
                });
            }
        });
    });

    // Public Newspaper Widget
    $('.lmb-newspaper').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');

        function loadNewspapers() {
            var search = $widget.find('.lmb-search').val();
            var startDate = $widget.find('.lmb-start-date').val();
            var endDate = $widget.find('.lmb-end-date').val();
            var sort = $widget.find('.lmb-sort').val();

            $.ajax({
                url: lmbAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_get_public_newspapers',
                    nonce: nonce,
                    search: search,
                    start_date: startDate,
                    end_date: endDate,
                    sort: sort
                },
                success: function(response) {
                    if (response.success) {
                        $widget.find('.lmb-newspaper-list').html(response.data.length ? response.data.map(item => `
                            <li>
                                <a href="${item.url}" target="_blank" class="lmb-download-newspaper" data-id="${item.id}">${item.title}</a> (${item.date})
                            </li>
                        `).join('') : '<li>' + __('No newspapers found.', 'lmb-core') + '</li>');
                    }
                }
            });
        }

        // Initial load
        loadNewspapers();

        // Filters
        $widget.find('.lmb-search, .lmb-start-date, .lmb-end-date, .lmb-sort').on('change keyup', loadNewspapers);

        // Track downloads
        $widget.on('click', '.lmb-download-newspaper', function() {
            var downloadId = $(this).data('id');
            $.ajax({
                url: lmbAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_get_public_newspapers',
                    nonce: nonce,
                    download_id: downloadId
                }
            });
        });
    });

    // Notifications Widget
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
                    $widget.find('.lmb-notification-list').html(response.data.length ? response.data.map(item => `
                        <li class="lmb-notification" data-id="${item.id}">
                            <span>${item.message}</span> (${item.time})
                            <button class="lmb-mark-read">${__('Mark as Read', 'lmb-core')}</button>
                        </li>
                    `).join('') : '<li>' + __('No notifications.', 'lmb-core') + '</li>');
                }
            }
        });

        // Mark notification as read
        $widget.on('click', '.lmb-mark-read', function() {
            var $li = $(this).closest('.lmb-notification');
            var notificationId = $li.data('id');
            $.ajax({
                url: lmbAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_mark_notification_read',
                    nonce: nonce,
                    notification_id: notificationId
                },
                success: function(response) {
                    if (response.success) {
                        $li.remove();
                    }
                }
            });
        });
    });
});