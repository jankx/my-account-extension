/**
 * Nobitour My Account - Frontend JavaScript
 */
(function($) {
    'use strict';

    var $document = $(document);
    var config = window.jankxMyAccount || {};

    var MyAccount = {
        init: function() {
            this.bindEvents();
            this.initAvatarUpload();
        },

        bindEvents: function() {
            $document.on('submit', '#jankx-profile-form', this.handleProfileUpdate.bind(this));
            $document.on('submit', '#jankx-password-form', this.handlePasswordChange.bind(this));
            $document.on('click', '[data-action="change-avatar"]', this.triggerAvatarUpload.bind(this));
            $document.on('change', '#jankx-avatar-input', this.handleAvatarUpload.bind(this));
            $document.on('click', '#jankx-delete-account', this.handleDeleteAccount.bind(this));
            $document.on('change', '.jankx-checkbox input[type="checkbox"]', this.handleSettingsChange.bind(this));
        },

        handleProfileUpdate: function(e) {
            e.preventDefault();

            var $form = $(e.currentTarget);
            var $btn = $form.find('button[type="submit"]');
            var $status = $('#jankx-profile-status');

            var data = {
                action: 'jankx_update_profile',
                nonce: config.nonce,
                display_name: $form.find('[name="display_name"]').val(),
                email: $form.find('[name="email"]').val(),
                phone: $form.find('[name="phone"]').val()
            };

            $btn.prop('disabled', true);
            this.showStatus($status, 'loading', config.i18n.saving);

            $.post(config.ajaxUrl, data)
                .done(function(response) {
                    if (response.success) {
                        MyAccount.showStatus($status, 'success', response.data.message);
                        MyAccount.updateHeaderInfo(data.display_name, data.email);
                    } else {
                        MyAccount.showStatus($status, 'error', response.data.message);
                    }
                })
                .fail(function() {
                    MyAccount.showStatus($status, 'error', config.i18n.error);
                })
                .always(function() {
                    $btn.prop('disabled', false);
                });
        },

        handlePasswordChange: function(e) {
            e.preventDefault();

            var $form = $(e.currentTarget);
            var $btn = $form.find('button[type="submit"]');
            var $status = $('#jankx-password-status');

            var newPass = $form.find('[name="new_password"]').val();
            var confirmPass = $form.find('[name="confirm_password"]').val();

            if (newPass !== confirmPass) {
                this.showStatus($status, 'error', 'Mật khẩu xác nhận không khớp.');
                return;
            }

            if (newPass.length < 8) {
                this.showStatus($status, 'error', 'Mật khẩu mới phải có ít nhất 8 ký tự.');
                return;
            }

            var data = {
                action: 'jankx_change_password',
                nonce: config.nonce,
                current_password: $form.find('[name="current_password"]').val(),
                new_password: newPass,
                confirm_password: confirmPass
            };

            $btn.prop('disabled', true);
            this.showStatus($status, 'loading', config.i18n.saving);

            $.post(config.ajaxUrl, data)
                .done(function(response) {
                    if (response.success) {
                        MyAccount.showStatus($status, 'success', response.data.message);
                        $form[0].reset();
                    } else {
                        MyAccount.showStatus($status, 'error', response.data.message);
                    }
                })
                .fail(function() {
                    MyAccount.showStatus($status, 'error', config.i18n.error);
                })
                .always(function() {
                    $btn.prop('disabled', false);
                });
        },

        initAvatarUpload: function() {
            if ($('#jankx-avatar-input').length === 0) {
                $('body').append('<input type="file" id="jankx-avatar-input" class="jankx-avatar-upload-input" accept="image/jpeg,image/png,image/gif,image/webp">');
            }
        },

        triggerAvatarUpload: function(e) {
            e.preventDefault();
            $('#jankx-avatar-input').trigger('click');
        },

        handleAvatarUpload: function(e) {
            var file = e.target.files[0];
            if (!file) return;

            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (allowedTypes.indexOf(file.type) === -1) {
                alert('Chỉ chấp nhận file JPG, PNG, GIF hoặc WebP.');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('Kích thước file không được vượt quá 5MB.');
                return;
            }

            var formData = new FormData();
            formData.append('action', 'jankx_upload_avatar');
            formData.append('nonce', config.nonce);
            formData.append('avatar', file);

            var $avatar = $('.jankx-avatar-img');
            var originalSrc = $avatar.attr('src');

            $.ajax({
                url: config.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    var reader = new FileReader();
                    reader.onload = function(ev) {
                        $avatar.attr('src', ev.target.result);
                    };
                    reader.readAsDataURL(file);
                },
                success: function(response) {
                    if (response.success) {
                        $avatar.attr('src', response.data.url);
                        alert(response.data.message);
                    } else {
                        $avatar.attr('src', originalSrc);
                        alert(response.data.message);
                    }
                },
                error: function() {
                    $avatar.attr('src', originalSrc);
                    alert(config.i18n.error);
                }
            });

            $(e.target).val('');
        },

        handleSettingsChange: function(e) {
            var $checkbox = $(e.currentTarget);
            var settings = {};

            $('.jankx-checkbox input[type="checkbox"]').each(function() {
                settings[$(this).attr('name')] = $(this).is(':checked') ? 1 : 0;
            });

            settings.action = 'jankx_save_settings';
            settings.nonce = config.nonce;

            $.post(config.ajaxUrl, settings)
                .done(function(response) {
                    if (!response.success) {
                        alert(response.data.message);
                    }
                })
                .fail(function() {
                    alert(config.i18n.error);
                });
        },

        handleDeleteAccount: function(e) {
            e.preventDefault();

            if (!confirm(config.i18n.confirmDelete)) {
                return;
            }

            alert('Tính năng này sẽ được cập nhật trong phiên bản tiếp theo.');
        },

        showStatus: function($el, type, message) {
            $el.removeClass('jankx-status-success jankx-status-error jankx-status-loading')
               .addClass('jankx-status-' + type)
               .text(message);

            if (type === 'success') {
                setTimeout(function() {
                    $el.fadeOut(300, function() {
                        $(this).text('').show();
                    });
                }, 3000);
            }
        },

        updateHeaderInfo: function(name, email) {
            $('.jankx-account-name').text(name);
            $('.jankx-account-email').text(email);
        }
    };

    $(document).ready(function() {
        MyAccount.init();
    });

})(jQuery);
