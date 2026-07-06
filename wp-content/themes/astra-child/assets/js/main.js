jQuery(function ($) {

    function checkEmailMatch($form, showError = false) {

    const $email = $form.find('input[name="your-email"]');
    const $confirm = $form.find('input[name="your-email-confirm"]');

    const email = $.trim($email.val());
    const confirm = $.trim($confirm.val());

    // 先清除原本的提示
    $confirm.removeClass('is-error');
    $form.find('.wpcf7-not-valid-tip').remove();

    // 其中一個沒填，不提示
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

    // 一致
    return true;
}

    function checkConfirmButton($form) {

        let valid = true;

        // 必填 input、email、textarea
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

        // 電子郵件一致
        if (valid) {
            valid = checkEmailMatch($form);
        }

        // acceptance
        if (valid) {
            const checked = $form.find('input[name="privacy-agree"]').prop('checked');
            if (!checked) {
                valid = false;
            }
        }

        $form.find('.confirm-btn').prop('disabled', !valid);
    }

    // 一開始檢查一次
    $('.contact-form').each(function () {
        checkConfirmButton($(this));
    });

    // 任一欄位變更重新檢查
    $(document).on(
        'input change',
        '.contact-form input, .contact-form textarea, .contact-form select',
        function () {
            checkConfirmButton($(this).closest('.contact-form'));
        }
    );

    // Email 離開欄位時檢查並顯示提示
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

        $form.find('select')
            .css('pointer-events', 'none');

        $form.find('.agree-wrap')
            .css('pointer-events', 'none');

    });

    // 修正
    $(document).on('click', '.correction-btn', function () {

        const $form = $(this).closest('.contact-form');

        $form.removeClass('is-confirm');

        $form.find('input:not([type="submit"]):not([type="hidden"]), textarea')
            .prop('readonly', false);

        $form.find('select')
            .css('pointer-events', '');

        $form.find('.agree-wrap')
            .css('pointer-events', '');

    });

});