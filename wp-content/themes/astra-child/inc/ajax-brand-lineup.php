<?php
/**
 * 品牌列表頁 - LINEUP 分類切換 AJAX
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_brand_lineup_filter', 'astra_child_brand_lineup_filter' );
add_action( 'wp_ajax_nopriv_brand_lineup_filter', 'astra_child_brand_lineup_filter' );

function astra_child_brand_lineup_filter() {

    check_ajax_referer( 'brand_lineup_nonce', 'nonce' );

    $brand_id = isset( $_POST['brand_id'] ) ? absint( $_POST['brand_id'] ) : 0;
    $cat_slug = isset( $_POST['cat'] ) ? sanitize_title( wp_unslash( $_POST['cat'] ) ) : '';

    if ( ! $brand_id ) {
        wp_send_json_error();
    }

    $tax_query = [
        'relation' => 'AND',
        [
            'taxonomy' => 'product_brand',
            'field'    => 'term_id',
            'terms'    => $brand_id,
        ],
    ];

    if ( $cat_slug ) {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $cat_slug,
        ];
    }

    $query = new WP_Query( [
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'tax_query'      => $tax_query,
    ] );

    ob_start();

    if ( $query->have_posts() ) {

        while ( $query->have_posts() ) {
            $query->the_post();
            get_template_part( 'woocommerce/lineup-card' );
        }

        wp_reset_postdata();
    }

    $html = ob_get_clean();

    wp_send_json_success( [ 'html' => $html ] );
}
