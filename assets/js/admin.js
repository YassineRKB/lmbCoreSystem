/**
 * LMB Admin Scripts
 *
 * Handles AJAX interactions for admin widgets.
 */

jQuery(document).ready(function($) {
    // Admin Stats Widget
    $('.lmb-admin-stats').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        $.ajax({
            url: lmbAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'lmb_get_admin_stats',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    $widget.find('[data-type="new_clients"]').text(response.data.new_clients);
                    $widget.find('[data-type="published_ads"]').text(response.data.published_ads);
                    $widget.find('[data-type="draft_ads"]').text(response.data.draft_ads);
                    $widget.find('[data-type="profits"]').text(response.data.profits);
                }
            }
        });
    });

    // Admin Actions Widget
    $('.lmb-admin-actions').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');

        function loadTab(tab) {
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_get_admin_actions',
                    nonce: nonce,
                    tab: tab
                },
                success: function(response) {
                    if (response.success) {
                        if (tab === 'feed') {
                            $widget.find('.lmb-feed-list').html(response.data.feed.map(item => `<li>${item.message} (${item.time})</li>`).join(''));
                        } else if (tab === 'pending-ads') {
                            $widget.find('.lmb-pending-ads-list').html(response.data.pending_ads.map(item => `<li>${item.title} <button data-action="approve" data-id="${item.id}">Approve</button> <button data-action="deny" data-id="${item.id}">Deny</button></li>`).join(''));
                        } else if (tab === 'pending-payments') {
                            $widget.find('.lmb-pending-payments-list').html(response.data.pending_payments.map(item => `<li>${item.user} (${item.points} points) <button data-action="approve-payment" data-id="${item.id}">Approve</button> <button data-action="deny-payment" data-id="${item.id}">Deny</button></li>`).join(''));
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
            loadTab($(this).data('tab'));
        });

        $widget.on('click', '.lmb-action-btn', function() {
            var action = $(this).data('action');
            // Handle bulk actions (to be implemented)
        });

        $widget.on('click', '[data-action="approve"]', function() {
            var ad_id = $(this).data('id');
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_approve_ad',
                    nonce: nonce,
                    ad_id: ad_id
                },
                success: function(response) {
                    if (response.success) {
                        loadTab('pending-ads');
                    }
                }
            });
        });

        $widget.on('click', '[data-action="deny"]', function() {
            var ad_id = $(this).data('id');
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_deny_ad',
                    nonce: nonce,
                    ad_id: ad_id
                },
                success: function(response) {
                    if (response.success) {
                        loadTab('pending-ads');
                    }
                }
            });
        });

        $widget.on('click', '[data-action="approve-payment"]', function() {
            var payment_id = $(this).data('id');
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_approve_payment',
                    nonce: nonce,
                    payment_id: payment_id
                },
                success: function(response) {
                    if (response.success) {
                        loadTab('pending-payments');
                    }
                }
            });
        });

        $widget.on('click', '[data-action="deny-payment"]', function() {
            var payment_id = $(this).data('id');
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_deny_payment',
                    nonce: nonce,
                    payment_id: payment_id
                },
                success: function(response) {
                    if (response.success) {
                        loadTab('pending-payments');
                    }
                }
            });
        });

        loadTab('feed');
    });

    // Balance Manipulation Widget
    $('.lmb-balance-manipulation').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        $widget.find('.lmb-balance-form').submit(function(e) {
            e.preventDefault();
            var user_id = $widget.find('[name="user_id"]').val();
            var points = $widget.find('[name="points"]').val();
            var action = $(e.originalEvent.submitter).data('action');
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_update_balance',
                    nonce: nonce,
                    user_id: user_id,
                    points: points,
                    action_type: action
                },
                success: function(response) {
                    $widget.find('.lmb-balance-message').text(response.success ? response.data.message : response.data.message);
                }
            });
        });
    });

    // Legal Ads List Widget
    $('.lmb-legal-ads-list').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        function loadAds() {
            var data = {
                action: 'lmb_get_legal_ads',
                nonce: nonce,
                user_id: $widget.find('[name="user_id"]').val(),
                ad_type: $widget.find('[name="ad_type"]').val(),
                company_name: $widget.find('[name="company_name"]').val()
            };
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $widget.find('.lmb-ads-table-body').html(response.data.map(item => `
                            <tr>
                                <td>${item.id}</td>
                                <td>${item.content}</td>
                                <td>${item.status}</td>
                                <td>${item.approved_by}</td>
                                <td>${item.timestamp}</td>
                            </tr>
                        `).join(''));
                    }
                }
            });
        }
        $widget.find('.lmb-ads-filter').submit(function(e) {
            e.preventDefault();
            loadAds();
        });
        loadAds();
    });

    // Notifications Widget
    $('.lmb-notifications').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        $.ajax({
            url: lmbAdmin.ajaxurl,
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
                url: lmbAdmin.ajaxurl,
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

    // Packages Editor Widget
    $('.lmb-packages-editor').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        function loadPackages() {
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_update_packages',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success && response.data.packages) {
                        $widget.find('.lmb-packages-list').html(response.data.packages.map((p, i) => `
                            <li>${p.name} (${p.price} MAD, ${p.points} points) <button data-id="${i + 1}">Edit</button></li>
                        `).join(''));
                    }
                }
            });
        }
        $widget.find('.lmb-package-form').submit(function(e) {
            e.preventDefault();
            var data = {
                action: 'lmb_update_packages',
                nonce: nonce,
                package_id: $widget.find('[name="package_id"]').val(),
                package_name: $widget.find('[name="package_name"]').val(),
                package_price: $widget.find('[name="package_price"]').val(),
                package_points: $widget.find('[name="package_points"]').val()
            };
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        loadPackages();
                        $widget.find('.lmb-package-form')[0].reset();
                        $widget.find('[name="package_id"]').val(0);
                    }
                }
            });
        });
        $widget.on('click', '[data-id]', function() {
            var id = $(this).data('id');
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_update_packages',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success && response.data.packages[id - 1]) {
                        $widget.find('[name="package_id"]').val(id);
                        $widget.find('[name="package_name"]').val(response.data.packages[id - 1].name);
                        $widget.find('[name="package_price"]').val(response.data.packages[id - 1].price);
                        $widget.find('[name="package_points"]').val(response.data.packages[id - 1].points);
                    }
                }
            });
        });
        loadPackages();
    });

    // Upload Accuse Widget
    $('.lmb-upload-accuse').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        $widget.find('.lmb-accuse-form').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('action', 'lmb_upload_accuse');
            formData.append('nonce', nonce);
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $widget.find('.lmb-accuse-message').text(response.success ? response.data.message : response.data.message);
                }
            });
        });
    });

    // Upload Newspaper Widget
    $('.lmb-upload-newspaper').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        function loadNewspapers() {
            var data = {
                action: 'lmb_get_newspapers',
                nonce: nonce,
                search: $widget.find('[name="search"]').val(),
                start_date: $widget.find('[name="start_date"]').val(),
                end_date: $widget.find('[name="end_date"]').val()
            };
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $widget.find('.lmb-newspapers-list').html(response.data.map(item => `
                            <li><a href="${item.url}" target="_blank">${item.title}</a> (${item.date})</li>
                        `).join(''));
                    }
                }
            });
        }
        $widget.find('.lmb-newspaper-form').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('action', 'lmb_upload_newspaper');
            formData.append('nonce', nonce);
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        loadNewspapers();
                        $widget.find('.lmb-newspaper-form')[0].reset();
                    }
                }
            });
        });
        $widget.find('.lmb-newspaper-filter').submit(function(e) {
            e.preventDefault();
            loadNewspapers();
        });
        loadNewspapers();
    });

    // User List Widget
    $('.lmb-user-list').each(function() {
        var $widget = $(this);
        var nonce = $widget.data('nonce');
        $widget.find('.lmb-user-search').submit(function(e) {
            e.preventDefault();
            var search = $widget.find('[name="search"]').val();
            $.ajax({
                url: lmbAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'lmb_search_users',
                    nonce: nonce,
                    search: search
                },
                success: function(response) {
                    if (response.success) {
                        $widget.find('.lmb-users-table-body').html(response.data.map(item => `
                            <tr>
                                <td>${item.id}</td>
                                <td>${item.name}</td>
                                <td>${item.email}</td>
                                <td>${item.company}</td>
                            </tr>
                        `).join(''));
                    }
                }
            });
        });
    });
});