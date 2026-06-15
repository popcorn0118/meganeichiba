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

$slides = [];

// 可變商品：依各 color 規格（變體）的圖片輪播
if ( $product->is_type( 'variable' ) ) {

    foreach ( $product->get_children() as $variation_id ) {

        $variation = wc_get_product( $variation_id );

        if ( ! $variation || ! $variation->exists() ) {
            continue;
        }

        $image_id = $variation->get_image_id();

        if ( ! $image_id ) {
            continue;
        }

        $color_slug = $variation->get_attributes()['pa_color'] ?? '';
        $color_hex  = '';

        if ( $color_slug ) {
            $color_term = get_term_by( 'slug', $color_slug, 'pa_color' );

            if ( $color_term ) {
                $color_hex = get_field( 'color', 'pa_color_' . $color_term->term_id ) ?: '';
            }
        }

        $slides[] = [
            'image_id' => $image_id,
            'color'    => $color_hex,
        ];
    }
}

// 一般商品（或無變體圖片）：使用特色圖 + 圖庫
if ( empty( $slides ) ) {

    $image_ids = array_values( array_filter( array_merge(
        [ $product->get_image_id() ],
        $product->get_gallery_image_ids()
    ) ) );

    foreach ( $image_ids as $image_id ) {
        $slides[] = [
            'image_id' => $image_id,
            'color'    => '',
        ];
    }
}

?>

<article class="lineup-card">

    <div class="lineup-card-images">

        <?php if ( ! empty( $slides ) ) : ?>

            <?php foreach ( $slides as $i => $slide ) : ?>

                <div class="lineup-card-image<?= 0 === $i ? ' active' : ''; ?>">
                    <?= wp_get_attachment_image( $slide['image_id'], 'large' ); ?>
                </div>

            <?php endforeach; ?>

        <?php else : ?>

            <div class="lineup-card-image active lineup-card-image-placeholder"></div>

        <?php endif; ?>

        <?php if ( count( $slides ) > 1 ) : ?>

            <div class="lineup-card-dots">

                <?php foreach ( $slides as $i => $slide ) : ?>

                    <button
                        type="button"
                        class="lineup-card-dot<?= 0 === $i ? ' active' : ''; ?>"
                        data-index="<?= esc_attr( $i ); ?>"
                        <?php if ( $slide['color'] ) : ?>
                            style="--dot-color: <?= esc_attr( $slide['color'] ); ?>;"
                        <?php endif; ?>
                    ></button>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

    <h3 class="lineup-card-title">
        <?= esc_html( get_the_title() ); ?>
        <!-- <a href="<?= esc_url( get_permalink() ); ?>"><?= esc_html( get_the_title() ); ?></a> -->
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
