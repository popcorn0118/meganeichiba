jQuery(function ($) {

    function checkEmailMatch($form, showError = false) {

        const $email = $form.find('input[name="your-email"]');
        const $confirm = $form.find('input[name="your-email-confirm"]');

        const email = $.trim($email.val());
        const confirm = $.trim($confirm.val());

        // 清除原本提示
        $confirm.removeClass('is-error');
        $form.find('.wpcf7-not-valid-tip').remove();

        // 其中一個沒填
        if (!email || !confirm) {
            return false;
        }

        // Email 不一致
        if (email !== confirm) {

            if (showError) {
                $confirm.addClass('is-error');
                $confirm.after('<div class="wpcf7-not-valid-tip">電子郵件與確認電子郵件不一致。</div>');
            }

            return false;
        }

        return true;
    }

    function checkConfirmButton($form) {

        let valid = true;

        // 必填 input、textarea
        $form.find('input[aria-required="true"], textarea[aria-required="true"]').each(function () {
            if ($.trim($(this).val()) === '') {
                valid = false;
                return false;
            }
        });

        // 必填 select
        if (valid) {
            $form.find('select[aria-required="true"]').each(function () {
                if (!$(this).val()) {
                    valid = false;
                    return false;
                }
            });
        }

        // Email 一致
        if (valid) {
            valid = checkEmailMatch($form);
        }

        // 同意條款
        if (valid) {
            valid = $form.find('input[name="privacy-agree"]').prop('checked');
        }

        $form.find('.confirm-btn').prop('disabled', !valid);
    }

    // textarea 依內容高度
    function fitTextareaContent(el) {
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
    }

    // 初始化：固定高度
    $('textarea').each(function () {
        this.style.height = '300px';
    });

    // 初始化檢查
    $('.contact-form').each(function () {
        checkConfirmButton($(this));
    });

    // 欄位變更
    $(document).on(
        'input change',
        '.contact-form input, .contact-form textarea, .contact-form select',
        function () {

            const $form = $(this).closest('.contact-form');

            checkEmailMatch($form);
            checkConfirmButton($form);

        }
    );

    // Email 離開欄位提示
    $(document).on(
        'blur',
        'input[name="your-email"], input[name="your-email-confirm"]',
        function () {
            checkEmailMatch($(this).closest('.contact-form'), true);
        }
    );

    // 確認
    $(document).on('click', '.confirm-btn', function () {

        const $form = $(this).closest('.contact-form');

        $form.addClass('is-confirm');

        $form.find('input:not([type="submit"]):not([type="hidden"]):not([type="checkbox"]), textarea')
            .prop('readonly', true);

        // textarea 縮成內容高度
        $form.find('textarea').each(function () {
            fitTextareaContent(this);
        });

        $form.find('select').css('pointer-events', 'none');
        $form.find('.agree-wrap').css('pointer-events', 'none');

    });

    // 修正
    $(document).on('click', '.correction-btn', function () {

        const $form = $(this).closest('.contact-form');

        $form.removeClass('is-confirm');

        $form.find('input:not([type="submit"]):not([type="hidden"]):not([type="checkbox"]), textarea')
            .prop('readonly', false);

        // 恢復固定高度
        $form.find('textarea').each(function () {
            this.style.height = '300px';
        });

        $form.find('select').css('pointer-events', '');
        $form.find('.agree-wrap').css('pointer-events', '');

    });

});