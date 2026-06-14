<?php
/**
 * LINEUP 商品卡
 * 由 taxonomy-product_brand.php 與 AJAX 分類切換共用
 */

defined( 'ABSPATH' ) || exit;

$product = wc_get_product();

if ( ! $product ) {
    return;
}

$image_ids = array_filter( array_merge(
    [ $product->get_image_id() ],
    $product->get_gallery_image_ids()
) );

?>

<article class="lineup-card">

    <div class="lineup-card-images">

        <?php if ( ! empty( $image_ids ) ) : ?>

            <?php foreach ( $image_ids as $i => $image_id ) : ?>

                <div class="lineup-card-image<?= 0 === $i ? ' active' : ''; ?>">
                    <?= wp_get_attachment_image( $image_id, 'large' ); ?>
                </div>

            <?php endforeach; ?>

        <?php else : ?>

            <div class="lineup-card-image active lineup-card-image-placeholder"></div>

        <?php endif; ?>

        <?php if ( count( $image_ids ) > 1 ) : ?>

            <div class="lineup-card-dots">

                <?php foreach ( $image_ids as $i => $image_id ) : ?>

                    <button
                        type="button"
                        class="lineup-card-dot<?= 0 === $i ? ' active' : ''; ?>"
                        data-index="<?= esc_attr( $i ); ?>"
                    ></button>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

    <h3 class="lineup-card-title">
        <a href="<?= esc_url( get_permalink() ); ?>"><?= esc_html( get_the_title() ); ?></a>
    </h3>

    <?php if ( $product->get_short_description() ) : ?>

        <div class="lineup-card-desc">
            <?= wp_kses_post( wpautop( $product->get_short_description() ) ); ?>
        </div>

    <?php endif; ?>

    <div class="lineup-card-price">
        <?= $product->get_price_html(); ?>
    </div>

</article>
