jQuery( function ( $ ) {

    // 確認
    $(document).on('click', '.confirm-btn', function () {

        const $form = $(this).closest('.contact-form');

        $form.addClass('is-confirm');

        $form.find('input:not([type="submit"]):not([type="hidden"]):not([type="checkbox"]), textarea, select')
            .prop('disabled', true);

        $form.find('input[type="checkbox"]')
            .prop('disabled', true);

    });

    // 修正
    $(document).on('click', '.correction-btn', function () {

        const $form = $(this).closest('.contact-form');

        $form.removeClass('is-confirm');

        $form.find('input:not([type="submit"]):not([type="hidden"]), textarea, select')
            .prop('disabled', false);

    });

});