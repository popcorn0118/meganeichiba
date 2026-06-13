<?php

/* =================================
   首頁 - 商品品牌列表
   "<?php require get_theme_file_path( 'inc/custom-products-brand-list.php' ); ?>"
   "[astra_custom_layout id=647]"
================================== */

$brands = get_terms([
    'taxonomy'   => 'product_brand',
    'hide_empty' => false,
    'orderby'    => 'term_order',
    'order'      => 'ASC',
]);

if ( empty( $brands ) || is_wp_error( $brands ) ) {
    return;
}

?>

<div class="products-brand-list">

    <?php foreach ( $brands as $brand ) :

        $logo = get_field( 'logo', 'product_brand_' . $brand->term_id );
        $status = get_field( 'state', 'product_brand_' . $brand->term_id );

        $thumbnail_id = get_term_meta(
            $brand->term_id,
            'thumbnail_id',
            true
        );

        $image = wp_get_attachment_image_url(
            $thumbnail_id,
            'full'
        );

        if ( $status === 'disabled' ) {
            continue;
        }

    ?>

        <article class="brand-card">

            <div class="brand-card-image">

                <?php if ( $image ) : ?>

                    <img
                        src="<?= esc_url( $image ); ?>"
                        alt="<?= esc_attr( $brand->name ); ?>"
                    >

                <?php else : ?>

                    <div class="brand-card-coming-soon">
                        COMING SOON
                    </div>

                <?php endif; ?>

            </div>

            <?php if ( ! empty( $logo ) ) : ?>

                <div class="brand-card-logo">
                    <img
                        src="<?= esc_url( $logo['url'] ); ?>"
                        alt="<?= esc_attr( $brand->name ); ?>"
                    >
                </div>

            <?php else : ?>

                <h3 class="brand-card-title">
                    <?= esc_html( $brand->name ); ?>
                </h3>

            <?php endif; ?>

            <?php if ( ! empty( $brand->description ) ) : ?>

                <div class="brand-card-desc">
                    <?= nl2br( esc_html( $brand->description ) ); ?>
                </div>

            <?php endif; ?>

            <?php if ( in_array( $status, ['public'], true ) ) : ?>

                <a
                    href="<?= esc_url( get_term_link( $brand ) ); ?>"
                    class="brand-card-button"
                >
                    <i aria-hidden="true" class="fas fa-caret-right"></i> View More
                </a>

                

            <?php endif; ?>

        </article>

    <?php endforeach; ?>

</div>