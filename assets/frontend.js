/**
 * Jankx My Account - Frontend Scripts
 */
(function($) {
    'use strict';

    const MyAccount = {
        init: function() {
            this.bindEvents();
            this.initTabs();
        },

        bindEvents: function() {
            // Profile form
            $(document).on('submit', '#jankx-profile-form', this.handleProfileSubmit.bind(this));
            
            // Password form
            $(document).on('submit', '#jankx-password-form', this.handlePasswordSubmit.bind(this));
            
            // Avatar change
            $(document).on('click', '.jankx-avatar-change', this.handleAvatarChange.bind(this));
            
            // Avatar upload
            $(document).on('change', '#jankx-avatar-input', this.handleAvatarUpload.bind(this));
        },

        initTabs: function() {
            const $layout = $('.jankx-account-layout');
            if (!$layout.length) return;

            // Highlight active nav item
            const activeTab = new URLSearchParams(window.location.search).get('tab') || 'profile';
            $(`.jankx-nav-item[data-tab="${activeTab}"]`).addClass('jankx-nav-active');
        },

        handleProfileSubmit: function(e) {
            e.preventDefault();
            
            const $form = $(e.target);
            const $status = $('#jankx-profile-status');
            const $btn = $('#jankx-save-profile');
            
            $btn.prop('disabled', true).text(jankxMyAccount.i18n.saving);
            $status.removeClass('success error').text('');
            
            $.ajax({
                url: jankxMyAccount.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'jankx_update_profile',
                    nonce: jankxMyAccount.nonce,
                    display_name: $form.find('[name="display_name"]').val(),
                    email: $form.find('[name="email"]').val(),
                    phone: $form.find('[name="phone"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        $status.addClass('success').text(response.data.message);
                        // Update display name in sidebar
                        $('.jankx-user-name').text($form.find('[name="display_name"]').val());
                    } else {
                        $status.addClass('error').text(response.data.message);
                    }
                },
                error: function() {
                    $status.addClass('error').text(jankxMyAccount.i18n.error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Lưu thay đổi');
                }
            });
        },

        handlePasswordSubmit: function(e) {
            e.preventDefault();
            
            const $form = $(e.target);
            const $status = $('#jankx-password-status');
            const $btn = $('#jankx-change-password');
            
            $btn.prop('disabled', true).text(jankxMyAccount.i18n.saving);
            $status.removeClass('success error').text('');
            
            $.ajax({
                url: jankxMyAccount.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'jankx_change_password',
                    nonce: jankxMyAccount.nonce,
                    current_password: $form.find('[name="current_password"]').val(),
                    new_password: $form.find('[name="new_password"]').val(),
                    confirm_password: $form.find('[name="confirm_password"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        $status.addClass('success').text(response.data.message);
                        $form[0].reset();
                    } else {
                        $status.addClass('error').text(response.data.message);
                    }
                },
                error: function() {
                    $status.addClass('error').text(jankxMyAccount.i18n.error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Đổi mật khẩu');
                }
            });
        },

        handleAvatarChange: function(e) {
            e.preventDefault();
            
            // Create hidden file input
            if (!$('#jankx-avatar-input').length) {
                $('body').append('<input type="file" id="jankx-avatar-input" accept="image/*" style="display:none">');
            }
            $('#jankx-avatar-input').trigger('click');
        },

        handleAvatarUpload: function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const $btn = $('.jankx-avatar-change');
            const formData = new FormData();
            
            formData.append('action', 'jankx_upload_avatar');
            formData.append('nonce', jankxMyAccount.nonce);
            formData.append('avatar', file);
            
            $btn.prop('disabled', true).text(jankxMyAccount.i18n.uploading);
            
            $.ajax({
                url: jankxMyAccount.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('.jankx-avatar-img').attr('src', response.data.url);
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert(jankxMyAccount.i18n.error);
                },
                complete: function() {
                    $btn.prop('disabled', false).html(`
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                        Đổi ảnh
                    `);
                }
            });
        }
    };

    $(document).ready(function() {
        MyAccount.init();
    });

})(jQuery);
