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

// ACF 重複器 product-color：color-value（色票色）+ color-image（商品列表主圖）
$color_rows = get_field( 'product-color' ) ?: [];


foreach ( $color_rows as $row ) {

    $image = $row['color-image'] ?? null;

    if ( ! $image ) {
        continue;
    }

    $slides[] = [
        'image_id' => $image['ID'],
        'color'    => $row['color-value'] ?: '',
    ];
}

// fallback：無 ACF 資料時使用商品特色圖
if ( empty( $slides ) && $product->get_image_id() ) {
    $slides[] = [
        'image_id' => $product->get_image_id(),
        'color'    => '',
    ];
}

?>

<?php
$first_color = ltrim( $slides[0]['color'] ?? '', '#' );
$permalink   = get_permalink();
?>

<article class="lineup-card" data-permalink="<?= esc_url( $permalink ); ?>">

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
                        data-color="<?= esc_attr( ltrim( $slide['color'], '#' ) ); ?>"
                        <?php if ( $slide['color'] ) : ?>
                            style="--dot-color: <?= esc_attr( $slide['color'] ); ?>;"
                        <?php endif; ?>
                    ></button>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

    <h3 class="lineup-card-title">
        <!-- <?= esc_html( get_the_title() ); ?> -->
        <a
            class="lineup-card-link"
            href="<?= esc_url( $first_color ? $permalink . '?color=' . $first_color : $permalink ); ?>"
        ><?= esc_html( get_the_title() ); ?></a>
    </h3>

    <?php if ( $product->get_description() ) : ?>

        <div class="lineup-card-desc">
            <?= wp_kses_post( wpautop( $product->get_description() ) ); ?>
        </div>

    <?php endif; ?>

    <div class="lineup-card-price">
        <?= $product->get_price_html(); ?>
    </div>

</article>
