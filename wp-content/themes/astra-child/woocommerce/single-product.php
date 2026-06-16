<?php
/**
 * 商品內頁 (single-product.php)
 *
 * URL 參數 ?color=xxxxxx（hex 不含 #）決定顯示哪個顏色的 gallery。
 * 顏色資料來自 ACF 重複器 product-color：
 *   color-code  — 顏色代碼文字（BK / AG…）
 *   color-value — 色彩選擇器（hex）
 *   color-image — 列表主圖
 *   color-gallery — 內頁輪播圖庫（圖片陣列）
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
    the_post();

    $product = wc_get_product();

    if ( ! $product ) {
        continue;
    }

    // ACF 重複器
    $color_rows = get_field( 'product-color' ) ?: [];

    // 從 URL 參數找目前顏色 index
    $url_color     = sanitize_text_field( $_GET['color'] ?? '' );
    $current_index = 0;

    foreach ( $color_rows as $i => $row ) {
        if ( ltrim( $row['color-value'] ?? '', '#' ) === $url_color ) {
            $current_index = $i;
            break;
        }
    }

    $current_row  = $color_rows[ $current_index ] ?? null;

    // color-image 當第一張，後面接 color-gallery
    $gallery = [];
    if ( ! empty( $current_row['color-image'] ) ) {
        $gallery[] = $current_row['color-image'];
    }
    if ( ! empty( $current_row['color-gallery'] ) ) {
        $gallery = array_merge( $gallery, $current_row['color-gallery'] );
    }

    // 麵包屑：品牌
    $brands = get_the_terms( get_the_ID(), 'product_brand' );
    $brand  = ( ! empty( $brands ) && ! is_wp_error( $brands ) ) ? $brands[0] : null;

endwhile;

?>

<div class="product-single">

    <!-- a: 麵包屑 -->
    <nav class="product-breadcrumb">
        <a href="<?= esc_url( wc_get_page_permalink( 'shop' ) ); ?>">全部商品</a>
        <span class="product-breadcrumb-sep">/</span>

        <?php if ( $brand ) : ?>
            <a href="<?= esc_url( get_term_link( $brand ) ); ?>"><?= esc_html( $brand->name ); ?></a>
            <span class="product-breadcrumb-sep">/</span>
        <?php endif; ?>

        <span><?= esc_html( get_the_title() ); ?></span>
    </nav>

    <div class="product-single-inner">

        <!-- 左欄：商品資訊 -->
        <div class="product-single-info">

            <!-- b: 標題 -->
            <h1 class="product-single-title"><?= esc_html( get_the_title() ); ?></h1>

            <!-- c: 顏色切換 -->
            <?php if ( ! empty( $color_rows ) ) : ?>

                <div class="product-color-switcher">

                    <p class="product-color-label">
                        顏色 / <span><?= esc_html( $current_row['color-code'] ?? '' ); ?></span>
                    </p>

                    <div class="product-color-dots">

                        <?php foreach ( $color_rows as $i => $row ) : ?>
                            <?php $hex = ltrim( $row['color-value'] ?? '', '#' ); ?>

                            <a
                                href="?color=<?= esc_attr( $hex ); ?>"
                                class="product-color-dot<?= $i === $current_index ? ' active' : ''; ?>"
                                style="--dot-color: <?= esc_attr( $row['color-value'] ?? '#ccc' ); ?>;"
                                title="<?= esc_attr( $row['color-code'] ?? '' ); ?>"
                            ></a>

                        <?php endforeach; ?>

                    </div>

                </div>

            <?php endif; ?>

            <!-- d: 規格（暫用假字） -->
            <dl class="product-specs">
                <div class="product-specs-row"><dt>產地</dt><dd>日本</dd></div>
                <div class="product-specs-row"><dt>材質</dt><dd>鈦合金、鈦</dd></div>
                <div class="product-specs-row"><dt>鏡片寬度</dt><dd>—</dd></div>
                <div class="product-specs-row"><dt>鼻樑寬度</dt><dd>—</dd></div>
                <div class="product-specs-row"><dt>鏡腳長度</dt><dd>—</dd></div>
                <div class="product-specs-row"><dt>鏡片高度</dt><dd>—</dd></div>
            </dl>

        </div>

        <!-- 右欄：e: color-gallery -->
        <div class="product-single-gallery">

            <?php if ( ! empty( $gallery ) ) : ?>

                <!-- 主圖 -->
                <div class="product-gallery-main">
                    <?php foreach ( $gallery as $j => $img ) : ?>
                        <div class="product-gallery-main-item<?= 0 === $j ? ' active' : ''; ?>" data-index="<?= $j; ?>">
                            <?= wp_get_attachment_image( $img['ID'], 'large' ); ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- 縮圖列 -->
                <?php if ( count( $gallery ) > 1 ) : ?>
                    <div class="product-gallery-thumbs">
                        <?php foreach ( $gallery as $j => $img ) : ?>
                            <button
                                type="button"
                                class="product-gallery-thumb<?= 0 === $j ? ' active' : ''; ?>"
                                data-index="<?= $j; ?>"
                            >
                                <?= wp_get_attachment_image( $img['ID'], 'medium' ); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php get_footer(); ?>
