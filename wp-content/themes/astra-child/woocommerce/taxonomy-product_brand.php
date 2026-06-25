<?php
/**
 * Brand archive (taxonomy-product_brand.php)
 *
 * 上方品牌介紹區塊資料來自 ACF 欄位群組「品牌列表頁 (brand-archive)」
 * 下方 LINEUP 依品牌商品實際使用到的 product_cat 自動產生分類頁籤，
 * 無分類則直接列出該品牌全部商品。
 */

defined( 'ABSPATH' ) || exit;

get_header();

$term = get_queried_object();
$acf_term_id = 'product_brand_' . $term->term_id;

$brand_logo = get_field( 'logo', $acf_term_id );
$brand_archive = get_field( 'brand-archive', $acf_term_id );

$banner_img   = $brand_archive['banner-img'] ?? null;
$banner_img_m = $brand_archive['banner-img-m'] ?? null;
$title       = $brand_archive['title'] ?? '';
$title_sub   = $brand_archive['title-sub'] ?? '';
$detail_show = $brand_archive['detail-show'] ?? false;
$detail      = $brand_archive['detail'] ?? [];

// 取得此品牌商品實際使用到的商品分類（排除預設「未分類」）
$cat_terms   = [];
$product_ids = get_objects_in_term( $term->term_id, 'product_brand' );

if ( ! is_wp_error( $product_ids ) && ! empty( $product_ids ) ) {

    $cats = [];

    foreach ( $product_ids as $product_id ) {

        $terms = get_the_terms( $product_id, 'product_cat' );

        if ( ! $terms || is_wp_error( $terms ) ) {
            continue;
        }

        foreach ( $terms as $cat ) {
            if ( 'uncategorized' !== $cat->slug ) {
                $cats[ $cat->term_id ] = $cat;
            }
        }
    }

    // 依後台「商品分類」拖曳排序（termmeta: order）排序
    $cat_terms = array_values( $cats );

    usort( $cat_terms, function ( $a, $b ) {
        $order_a = (int) get_term_meta( $a->term_id, 'order', true );
        $order_b = (int) get_term_meta( $b->term_id, 'order', true );
        return $order_a <=> $order_b;
    } );
}

$has_categories = ! empty( $cat_terms );
$default_cat    = $has_categories ? reset( $cat_terms )->slug : '';

?>

<div class="brand-archive">

    <?php if ( ! empty( $banner_img['url'] ) ) : ?>

        <section class="brand-hero">
            <div class="brand-hero-img<?= ! empty( $banner_img_m['url'] ) ? ' has-img-m' : ''; ?>">
                <img class="brand-hero-img__pc"
                    src="<?= esc_url( $banner_img['url'] ); ?>"
                    alt="<?= esc_attr( $term->name ); ?>"
                >
                <?php if ( ! empty( $banner_img_m['url'] ) ) : ?>
                <img class="brand-hero-img__m"
                    src="<?= esc_url( $banner_img_m['url'] ); ?>"
                    alt="<?= esc_attr( $term->name ); ?>"
                >
                <?php endif; ?>
            </div>
        </section>

    <?php endif; ?>

    <div class="brand-cont">
        <?php if ( $title || $title_sub ) : ?>

            <section class="brand-intro">

                <?php if ( $title ) : ?>
                    <h1 class="brand-intro-title Noto-Serif"><?= $title; ?></h1>
                <?php endif; ?>

                <?php if ( $title_sub ) : ?>
                    <div class="brand-intro-sub"><?= nl2br( esc_html( $title_sub ) ); ?></div>
                <?php endif; ?>

            </section>

        <?php endif; ?>

        <?php if ( $detail_show && ! empty( $detail ) ) : ?>

            <section class="brand-detail">

                <h2 class="brand-detail-title">DETAIL</h2>

                <div class="brand-detail-list">

                    <?php foreach ( $detail as $row ) : ?>

                        <div class="brand-detail-item">

                            <?php if ( ! empty( $row['img']['url'] ) ) : ?>
                                <div class="brand-detail-img">
                                    <img src="<?= esc_url( $row['img']['url'] ); ?>" alt="">
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $row['desc'] ) ) : ?>
                                <div class="brand-detail-desc"><?= nl2br( esc_html( $row['desc'] ) ); ?></div>
                            <?php endif; ?>

                        </div>

                    <?php endforeach; ?>

                </div>

            </section>

        <?php endif; ?>

        <section class="brand-lineup" data-brand-id="<?= esc_attr( $term->term_id ); ?>">

            <?php if ( !empty($has_categories) ) : ?>
                <div class="brand-lineup-tabs">

                    <?php foreach ( $cat_terms as $i => $cat ) : ?>

                        <button
                            type="button"
                            class="brand-lineup-tab<?= 0 === $i ? ' active' : ''; ?> IBM-Plex-Mono"
                            data-cat="<?= esc_attr( $cat->slug ); ?>"
                        >
                            <?= esc_html( $cat->name ); ?>
                        </button>

                    <?php endforeach; ?>

                </div>
            <?php else: ?>
                <h2 class="brand-lineup-title IBM-Plex-Mono">LINEUP</h2>
            <?php endif; ?>

            <div class="brand-lineup-grid">

                <?php

                $lineup_tax_query = [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'product_brand',
                        'field'    => 'term_id',
                        'terms'    => $term->term_id,
                    ],
                ];

                if ( $has_categories ) {
                    $lineup_tax_query[] = [
                        'taxonomy' => 'product_cat',
                        'field'    => 'slug',
                        'terms'    => $default_cat,
                    ];
                }

                $lineup_query = new WP_Query( [
                    'post_type'      => 'product',
                    'posts_per_page' => -1,
                    'tax_query'      => $lineup_tax_query,
                    'orderby'   => 'menu_order',
	                'order'     => 'ASC',
                ] );

                if ( $lineup_query->have_posts() ) :

                    while ( $lineup_query->have_posts() ) :
                        $lineup_query->the_post();
                        get_template_part( 'woocommerce/lineup-card' );
                    endwhile;

                    wp_reset_postdata();

                endif;

                ?>

            </div>

        </section>
    </div>
</div>

<?php get_footer(); ?>
