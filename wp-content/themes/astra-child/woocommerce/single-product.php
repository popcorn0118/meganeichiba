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

    // 麵包屑：分類
    $cats = get_the_terms( get_the_ID(), 'product_cat' );
    $cat  = ( ! empty( $cats ) && ! is_wp_error( $cats ) ) ? $cats[0] : null;

    // 商品規格（ACF group）
    $product_info = get_field( 'product-info' ) ?: [];

    // 品牌 logo（從品牌 taxonomy term 的 ACF 欄位取得）
    $brand_logo = $brand ? get_field( 'logo', 'product_brand_' . $brand->term_id ) : null;

endwhile;

?>

<div class="product-single">

    <!-- a: 麵包屑 -->
    <nav class="product-breadcrumb">
        <span>PRODUCT</span>
        <span class="product-breadcrumb-sep">/</span>

        <?php if ( $brand ) : ?>
            <a href="<?= esc_url( get_term_link( $brand ) ); ?>"><?= esc_html( $brand->name ); ?></a>
            <span class="product-breadcrumb-sep">/</span>
        <?php endif; ?>

        <?php if ( $cat && $brand && 'uncategorized' !== $cat->slug ) : ?>
            <a href="<?= esc_url( get_term_link( $brand ) . '#cat=' . rawurlencode( $cat->slug ) ); ?>"><?= esc_html( $cat->name ); ?></a>
            <span class="product-breadcrumb-sep">/</span>
        <?php endif; ?>

        <span><?= esc_html( get_the_title() ); ?></span>
    </nav>

    <div class="product-single-inner">

        <!-- 左欄：商品資訊 -->
        <div class="product-single-info">

            <!-- a: 品牌 logo -->
            <?php if ( $brand_logo ) : ?>
                <div class="product-brand-logo">
                    <?= wp_get_attachment_image( $brand_logo['ID'], 'full' ); ?>
                </div>
            <?php endif; ?>

            <!-- b: 型號 / 色號 / 框型 + 顏色切換 -->
            <div class="product-color-switcher">
                <?php if ( ! empty( $color_rows ) ) : ?>
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
                <?php endif; ?>

                <dl class="product-model-info">
                    <div class="product-model-info-row">
                        <dt>型號</dt>
                        <dd><?= esc_html( get_the_title() ); ?></dd>
                    </div>
                    <div class="product-model-info-row">
                        <dt>色號</dt>
                        <dd><?= esc_html( $current_row['color-code'] ?? '—' ); ?></dd>
                    </div>
                    <div class="product-model-info-row">
                        <dt>框型</dt>
                        <dd><?= esc_html( $product_info['frame-shape'] ?? '—' ); ?></dd>
                    </div>
                </dl>

            </div>

            


            <!-- d: 規格 -->
            <dl class="product-specs">
                <div class="product-dd">
                    <div class="product-specs-label">尺寸：</div>
                    <div class="product-specs-row"><dt>鏡片寬度</dt><dd><?= esc_html( $product_info['lens-width'] ?? '—' ); ?></dd></div>
                    <div class="product-specs-row"><dt>鼻樑寬度</dt><dd><?= esc_html( $product_info['bridge-width'] ?? '—' ); ?></dd></div>
                    <div class="product-specs-row"><dt>鏡腳長度</dt><dd><?= esc_html( $product_info['temple-length'] ?? '—' ); ?></dd></div>
                    <div class="product-specs-row"><dt>鏡片高度</dt><dd><?= esc_html( $product_info['lens-height'] ?? '—' ); ?></dd></div>
                </div>
                
                <div class="product-dd">
                    <div class="product-specs-label">材質：</div>
                    <div class="product-specs-row"><dt>鏡框材質</dt><dd><?= esc_html( $product_info['frame-material'] ?? '—' ); ?></dd></div>
                    <div class="product-specs-row"><dt>鏡腳材質</dt><dd><?= esc_html( $product_info['temple-material'] ?? '—' ); ?></dd></div>
                </div>
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
            <p></p>
            <?php $desc = $product->get_description(); ?>
            <?php if ( $desc ) : ?>
                <div class="product-desc">
                    <?= wp_kses_post( wpautop( $desc ) ); ?>
                </div>
            <?php endif; ?>

        </div>

    </div>

</div>

<?php get_footer(); ?>
