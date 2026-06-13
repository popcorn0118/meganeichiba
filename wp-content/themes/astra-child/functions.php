<?php
/**
 * astra-child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package astra-child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );


// 將 ACF「品牌狀態」顯示於 WooCommerce Brands 後台列表
add_filter( 'manage_edit-product_brand_columns', function( $columns ) {

    $new = [];

    foreach ( $columns as $key => $label ) {

        $new[ $key ] = $label;

        if ( $key === 'description' ) {
            $new['brand_status'] = '品牌狀態';
        }
    }

    return $new;

} );

add_filter( 'manage_product_brand_custom_column', function( $content, $column_name, $term_id ) {

    if ( $column_name !== 'brand_status' ) {
        return $content;
    }

    $status = get_field( 'state', 'product_brand_' . $term_id );

    $labels = [
		'public'       => '<strong style="color:#00a32a;">公開</strong>',
		'preview'      => '<strong style="color:#dba617;">展示中</strong>',
		'coming_soon'  => '<strong style="color:#646970;">即將推出</strong>',
		'disabled'     => '<strong style="color:#d63638;">停用</strong>',
	];

    return $labels[ $status ] ?? '-';

}, 10, 3 );

