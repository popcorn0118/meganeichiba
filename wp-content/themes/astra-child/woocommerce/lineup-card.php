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

// ACF 重複器 product-color：color-value（色票色）+ color-image（商品列表主圖）+ color-code（色號文字，如 BK / AG）
$color_rows = get_field( 'product-color' ) ?: [];


foreach ( $color_rows as $row ) {

    $image = $row['color-image'] ?? null;

    if ( ! $image ) {
        continue;
    }

    $slides[] = [
        'image_id' => $image['ID'],
        'color'    => $row['color-value'] ?: '',
        'code'     => $row['color-code'] ?: '',
    ];
}

// fallback：無 ACF 資料時使用商品特色圖
if ( empty( $slides ) && $product->get_image_id() ) {
    $slides[] = [
        'image_id' => $product->get_image_id(),
        'color'    => '',
        'code'     => '',
    ];
}

?>

<?php
$first_color = ltrim( $slides[0]['color'] ?? '', '#' );
$permalink   = get_permalink();
?>

<a
    class="lineup-card"
    href="<?= esc_url( $first_color ? $permalink . '?color=' . $first_color : $permalink ); ?>"
    data-permalink="<?= esc_url( $permalink ); ?>"
>

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

    </div>

    <dl class="lineup-card-model-info">

        <div class="lineup-card-model-info-row">
            <dt>型號</dt>
            <dd class="lineup-card-link"><?= esc_html( get_the_title() ); ?></dd>
        </div>

        <?php if ( count( $slides ) > 1 ) : ?>

            <div class="lineup-card-model-info-row">
                <dt>色號</dt>
                <dd>

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
                            ><span class="lineup-card-dot-label"><?= esc_html( $slide['code'] ); ?></span></button>

                        <?php endforeach; ?>

                    </div>

                </dd>
            </div>

        <?php endif; ?>

    </dl>

    <span class="lineup-card-viewmore brand-card-button">
        <i aria-hidden="true" class="fas fa-caret-right"></i> View More
    </span>

    <div class="lineup-card-price">
        <?= $product->get_price_html(); ?>
    </div>

</a>
