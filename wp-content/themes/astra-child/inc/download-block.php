<?php
/**
 * 資料下載（密碼保護）區塊
 *
 * ACF 欄位：
 * - 頁面 / product_brand：show（是否顯示）、type（file / link）、upload-file（檔案，array）、file-link（網址文字）
 * - 全站設定（option）：password-list（重複器，子欄位 password）、password-download-desc（WYSIWYG）、download-desc（WYSIWYG）
 *
 * @package astra-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 取得目前頁面對應的 ACF post_id（頁面用文章 ID，品牌用 taxonomy_term_id）
 *
 * @return string|int|false
 */
function download_get_acf_object_id() {

	if ( is_page() ) {
		return get_the_ID();
	}

	if ( is_tax( 'product_brand' ) ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			return $term->taxonomy . '_' . $term->term_id;
		}
	}

	return false;
}

/**
 * 依 type 欄位解析實際下載網址與檔名
 *
 * @param string|int $object_id
 * @return array{url: string, filename: string}
 */
function download_resolve_file( $object_id ) {

	$type = get_field( 'type', $object_id );
	$file = get_field( 'upload-file', $object_id );
	$link = get_field( 'file-link', $object_id );

	$file_url = '';

	if ( is_array( $file ) && ! empty( $file['url'] ) ) {
		$file_url = $file['url'];
	} elseif ( is_numeric( $file ) ) {
		$file_url = wp_get_attachment_url( (int) $file );
	} elseif ( is_string( $file ) && '' !== $file ) {
		$file_url = $file;
	}

	// type 若為「link」優先用檔案連結，其餘情況（含 type 回傳格式為 Label 時）以實際有值的欄位為準
	$url = ( 'link' === $type ) ? ( $link ?: $file_url ) : ( $file_url ?: $link );

	if ( ! $url ) {
		return [
			'url'      => '',
			'filename' => '',
		];
	}

	$filename = ( is_array( $file ) && ! empty( $file['filename'] ) )
		? $file['filename']
		: basename( wp_parse_url( $url, PHP_URL_PATH ) );

	return [
		'url'      => $url,
		'filename' => $filename,
	];
}

/**
 * 下載圖示（文件 + 下載箭頭）
 */
function download_icon() {
	?>
	<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path d="M6 2h8l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" fill="currentColor" opacity="0.15" />
		<path d="M6 2h8l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.5" />
		<path d="M12 8v7m0 0-2.5-2.5M12 15l2.5-2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
	</svg>
	<?php
}

/**
 * 輸出資料下載區塊（掛在 footer 上方）
 */
add_action( 'astra_footer_before', 'render_download_block' );
function render_download_block() {

	$object_id = download_get_acf_object_id();

	if ( ! $object_id || ! get_field( 'show', $object_id ) ) {
		return;
	}

	$download_desc          = get_field( 'download-desc', 'option' );
	$password_download_desc = get_field( 'password-download-desc', 'option' );

	$file_info = download_resolve_file( $object_id );
	$extension = $file_info['filename'] ? strtoupper( pathinfo( $file_info['filename'], PATHINFO_EXTENSION ) ) : '';
	?>
	<div class="download" data-object-id="<?php echo esc_attr( $object_id ); ?>">

		<button type="button" class="download__trigger">
			<span class="download__icon"><?php download_icon(); ?></span>
			<span class="download__label">資料下載</span><?php if ( $extension ) : ?><span class="download__ext">(<?php echo esc_html( $extension ); ?>)</span><?php endif; ?>
		</button>

		<?php if ( $download_desc ) : ?>
			<div class="download__notice"><?php echo wp_kses_post( $download_desc ); ?></div>
		<?php endif; ?>

		<div class="download-modal" aria-hidden="true">
			<div class="download-modal__overlay"></div>

			<div class="download-modal__box" role="dialog" aria-modal="true" aria-labelledby="download-modal-title">
				<button type="button" class="download-modal__close" aria-label="關閉">&times;</button>

				<h3 id="download-modal-title" class="download-modal__title">資料下載</h3>

				<form class="download-modal__form" autocomplete="off">
					<input type="password" name="download_password" class="download-modal__input" autocomplete="new-password" aria-label="下載密鑰">

					<?php if ( $password_download_desc ) : ?>
						<div class="download-modal__desc"><?php echo wp_kses_post( $password_download_desc ); ?></div>
					<?php endif; ?>

					<button type="submit" class="download-modal__submit" aria-label="下載">
						<?php download_icon(); ?>
					</button>

					<p class="download-modal__error" hidden></p>
				</form>
			</div>
		</div>
	</div>
	<?php
}

/**
 * 載入下載功能專用的 CSS / JS（僅在區塊會顯示的頁面載入）
 */
add_action( 'wp_enqueue_scripts', 'download_enqueue_assets' );
function download_enqueue_assets() {

	$object_id = download_get_acf_object_id();

	if ( ! $object_id || ! get_field( 'show', $object_id ) ) {
		return;
	}

	wp_enqueue_script(
		'astra-child-download-modal',
		get_stylesheet_directory_uri() . '/assets/js/download-modal.js',
		[],
		CHILD_THEME_ASTRA_CHILD_VERSION,
		true
	);

	wp_localize_script( 'astra-child-download-modal', 'ChildDownload', [
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'download_nonce' ),
	] );
}

/**
 * AJAX：驗證下載密鑰，成功則回傳檔案網址
 */
add_action( 'wp_ajax_verify_download_password', 'verify_download_password' );
add_action( 'wp_ajax_nopriv_verify_download_password', 'verify_download_password' );
function verify_download_password() {

	check_ajax_referer( 'download_nonce', 'nonce' );

	$object_id = isset( $_POST['object_id'] ) ? sanitize_text_field( wp_unslash( $_POST['object_id'] ) ) : '';
	$password  = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';

	if ( ! $object_id || '' === $password || ! get_field( 'show', $object_id ) ) {
		wp_send_json_error( [ 'message' => '密鑰錯誤，請重新輸入' ] );
	}

	$valid = false;

	if ( have_rows( 'password-list', 'option' ) ) {
		while ( have_rows( 'password-list', 'option' ) ) {
			the_row();

			$stored_password = (string) get_sub_field( 'password' );

			if ( '' !== $stored_password && hash_equals( $stored_password, $password ) ) {
				$valid = true;
				break;
			}
		}
	}

	if ( ! $valid ) {
		wp_send_json_error( [ 'message' => '密鑰錯誤，請重新輸入' ] );
	}

	$file_info = download_resolve_file( $object_id );

	if ( ! $file_info['url'] ) {
		wp_send_json_error( [ 'message' => '目前無可下載的檔案' ] );
	}

	wp_send_json_success( $file_info );
}
