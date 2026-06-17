<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =================================
  一次性匯入 WooCommerce 商品
  1.修改 $files 要匯出的檔
  2..xlsx 另存成 -> Unicode 文字 (*.txt)
  3.執行：http://localhost:8888/?import_products=1
  跑完請立刻註解 functions.php 的 require_once
================================== */

add_action( 'init', 'popcorn_import_products_once' );

function popcorn_import_products_once() {

	if ( empty( $_GET['import_products'] ) || $_GET['import_products'] !== '1' ) {
		return;
	}

	if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
		wp_die( '沒有權限' );
	}

	if ( ! function_exists( 'update_field' ) ) {
		wp_die( 'ACF 未啟用' );
	}

	if ( ! post_type_exists( 'product' ) ) {
		wp_die( 'WooCommerce product 不存在' );
	}

	$files = [
		'zerogra' => WP_CONTENT_DIR . '/uploads/import/zerogra.txt',
		'nosefree' => WP_CONTENT_DIR . '/uploads/import/nosefree.txt',
		'megane-ichiba' => WP_CONTENT_DIR . '/uploads/import/megane-ichiba.txt',
	];

	$image_map = popcorn_build_attachment_map();

	$created = 0;
	$updated = 0;
	$failed  = 0;

	$missing_images    = [];
	$missing_galleries = [];

	foreach ( $files as $brand_slug => $file_path ) {

		if ( ! file_exists( $file_path ) ) {
			echo '<p>找不到檔案：' . esc_html( $file_path ) . '</p>';
			continue;
		}

		$rows = popcorn_read_txt_rows( $file_path );

		if ( empty( $rows ) ) {
			echo '<p>檔案無資料：' . esc_html( $file_path ) . '</p>';
			continue;
		}

		$current_product_title = '';
		$current_product_cat   = '';
		$current_description   = '';
		$current_colors        = [];
		$current_info          = [];
		$product_started       = false;

		foreach ( $rows as $index => $row ) {

			if ( $index < 3 ) {
				continue;
			}

			$row = array_pad( $row, 14, '' );

			$product_cat     = trim( $row[0] );
			$post_title      = trim( $row[1] );
			$color_code      = trim( $row[2] );
			$color_value     = trim( $row[3] );

			$temple_material = trim( $row[6] );
			$frame_material  = trim( $row[7] );
			$frame_shape     = trim( $row[8] );
			$lens_width      = trim( $row[9] );
			$bridge_width    = trim( $row[10] );
			$temple_length   = trim( $row[11] );
			$lens_height     = trim( $row[12] );
			$description     = trim( $row[13] );

			if ( $post_title !== '' ) {

				if ( $product_started && $current_product_title !== '' ) {

					$result = popcorn_save_import_product(
						$current_product_title,
						$current_description,
						$current_product_cat,
						$brand_slug,
						$current_colors,
						$current_info
					);

					if ( $result === 'created' ) {
						$created++;
					} elseif ( $result === 'updated' ) {
						$updated++;
					} else {
						$failed++;
					}
				}

				$current_product_title = $post_title;
				$current_product_cat   = $product_cat ?: 'uncategorized';
				$current_description   = $description;
				$current_colors        = [];

				$current_info = [
					'frame-shape'     => $frame_shape,
					'lens-width'      => $lens_width,
					'bridge-width'    => $bridge_width,
					'temple-length'   => $temple_length,
					'lens-height'     => $lens_height,
					'temple-material' => $temple_material,
					'frame-material'  => $frame_material,
				];

				$product_started = true;
			}

			if ( $current_product_title === '' || $color_code === '' ) {
				continue;
			}

			$image_base = strtolower( sanitize_title( $current_product_title . '-' . $color_code ) );

			$color_image = $image_map[ $image_base ] ?? '';

			if ( ! $color_image ) {
				$missing_images[] = $image_base;
			}

			$gallery_prefix = $image_base . '-';
			$color_gallery  = [];

			foreach ( $image_map as $image_key => $attachment_id ) {
				if ( strpos( $image_key, $gallery_prefix ) === 0 ) {
					$color_gallery[] = $attachment_id;
				}
			}

			if ( empty( $color_gallery ) ) {
				$missing_galleries[] = $gallery_prefix;
			}

			$current_colors[] = [
				'color-code'    => $color_code,
				'color-value'   => $color_value,
				'color-image'   => $color_image,
				'color-gallery' => $color_gallery,
			];
		}

		if ( $product_started && $current_product_title !== '' ) {

			$result = popcorn_save_import_product(
				$current_product_title,
				$current_description,
				$current_product_cat,
				$brand_slug,
				$current_colors,
				$current_info
			);

			if ( $result === 'created' ) {
				$created++;
			} elseif ( $result === 'updated' ) {
				$updated++;
			} else {
				$failed++;
			}
		}
	}

	echo '<h2>匯入完成</h2>';
	echo '<p>新增商品：' . intval( $created ) . '</p>';
	echo '<p>更新商品：' . intval( $updated ) . '</p>';
	echo '<p>失敗商品：' . intval( $failed ) . '</p>';
	echo '<p>找不到主圖：' . count( array_unique( $missing_images ) ) . '</p>';
	echo '<p>找不到圖庫：' . count( array_unique( $missing_galleries ) ) . '</p>';

	if ( ! empty( $missing_images ) ) {
		echo '<h3>找不到主圖</h3><pre>';
		echo esc_html( implode( "\n", array_unique( $missing_images ) ) );
		echo '</pre>';
	}

	if ( ! empty( $missing_galleries ) ) {
		echo '<h3>找不到圖庫</h3><pre>';
		echo esc_html( implode( "\n", array_unique( $missing_galleries ) ) );
		echo '</pre>';
	}

	exit;
}

function popcorn_read_txt_rows( $file_path ) {

	$content = file_get_contents( $file_path );

	if ( substr( $content, 0, 2 ) === "\xFF\xFE" ) {
		$content = mb_convert_encoding( $content, 'UTF-8', 'UTF-16LE' );
	} elseif ( substr( $content, 0, 2 ) === "\xFE\xFF" ) {
		$content = mb_convert_encoding( $content, 'UTF-8', 'UTF-16BE' );
	} elseif ( substr( $content, 0, 3 ) === "\xEF\xBB\xBF" ) {
		$content = substr( $content, 3 );
	}

	$lines = preg_split( "/\r\n|\n|\r/", $content );
	$rows  = [];

	foreach ( $lines as $line ) {

		if ( trim( $line ) === '' ) {
			continue;
		}

		$rows[] = explode( "\t", $line );
	}

	return $rows;
}

function popcorn_save_import_product( $title, $description, $product_cat, $brand_slug, $colors, $info ) {

	$product_id = popcorn_get_product_id_by_title( $title );
	$is_update  = $product_id ? true : false;

	if ( $product_id ) {

		$result = wp_update_post([
			'ID'           => $product_id,
			'post_title'   => $title,
			'post_content' => $description,
			'post_status'  => 'publish',
			'post_type'    => 'product',
		], true);

	} else {

		$result = wp_insert_post([
			'post_title'   => $title,
			'post_content' => $description,
			'post_status'  => 'publish',
			'post_type'    => 'product',
		], true);

		$product_id = $result;
	}

	if ( is_wp_error( $result ) || ! $product_id ) {
		return false;
	}

	wp_set_object_terms( $product_id, 'simple', 'product_type' );
	wp_set_object_terms( $product_id, $product_cat ?: 'uncategorized', 'product_cat' );
	wp_set_object_terms( $product_id, $brand_slug, 'product_brand' );

	update_field( 'product-color', $colors, $product_id );
	update_field( 'product-info', $info, $product_id );

	delete_post_thumbnail( $product_id );
	delete_post_meta( $product_id, '_thumbnail_id' );
	delete_post_meta( $product_id, '_product_image_gallery' );

	return $is_update ? 'updated' : 'created';
}

function popcorn_get_product_id_by_title( $title ) {

	global $wpdb;

	$product_id = $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT ID
			FROM {$wpdb->posts}
			WHERE post_type = 'product'
			AND post_title = %s
			ORDER BY ID ASC
			LIMIT 1
			",
			$title
		)
	);

	return $product_id ? intval( $product_id ) : 0;
}

function popcorn_build_attachment_map() {

	$attachments = get_posts([
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	]);

	$map = [];

	foreach ( $attachments as $attachment_id ) {

		$file = get_attached_file( $attachment_id );

		if ( ! $file ) {
			continue;
		}

		$name = pathinfo( $file, PATHINFO_FILENAME );
		$key  = strtolower( sanitize_title( $name ) );

		$map[ $key ] = $attachment_id;
	}

	return $map;
}