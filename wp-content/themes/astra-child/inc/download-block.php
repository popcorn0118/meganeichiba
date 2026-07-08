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
	<svg xmlns="http://www.w3.org/2000/svg" width="29" height="36" viewBox="0 0 29 36" fill="none">
		<path d="M14.5 0L14.7121 0.0125999C15.1166 0.0599829 15.4934 0.241343 15.7814 0.52742C16.0695 0.813498 16.2521 1.18761 16.2998 1.5894L16.3125 1.8V9L16.3216 9.27C16.3864 10.1271 16.7579 10.9329 17.3689 11.5417C17.9799 12.1504 18.7901 12.522 19.6529 12.5892L19.9375 12.6H27.1875L27.3996 12.6126C27.8041 12.66 28.1809 12.8413 28.4689 13.1274C28.757 13.4135 28.9396 13.7876 28.9873 14.1894L29 14.4V30.6C29.0001 31.9774 28.4702 33.3027 27.5187 34.3049C26.5672 35.307 25.2661 35.9102 23.8815 35.991L23.5625 36H5.4375C4.05056 36.0001 2.716 35.4738 1.7069 34.5289C0.6978 33.584 0.0904301 32.2918 0.00906272 30.9168L8.44706e-09 30.6V5.4C-7.72519e-05 4.02262 0.529839 2.69727 1.48133 1.69513C2.43281 0.692988 3.73394 0.0898065 5.1185 0.00900021L5.4375 0H14.5ZM14.5 14.4C14.0193 14.4 13.5583 14.5896 13.2184 14.9272C12.8785 15.2648 12.6875 15.7226 12.6875 16.2V22.653L11.2502 21.2274C10.9381 20.9175 10.5228 20.7313 10.0824 20.7038C9.64188 20.6763 9.20642 20.8093 8.85769 21.078L8.68731 21.2274C8.34752 21.5649 8.15664 22.0227 8.15664 22.5C8.15664 22.9773 8.34752 23.435 8.68731 23.7726L13.2186 28.2726L13.2983 28.3482L13.4216 28.4472L13.6209 28.575L13.8276 28.6722L14.0179 28.7352L14.2898 28.7892L14.5 28.8L14.7121 28.7874L14.9241 28.7514L15.1199 28.692L15.2667 28.6308L15.4443 28.5372L15.6111 28.422L15.7814 28.2726L20.3127 23.7726C20.6525 23.435 20.8434 22.9773 20.8434 22.5C20.8434 22.0227 20.6525 21.5649 20.3127 21.2274L20.1423 21.078C19.7936 20.8093 19.3581 20.6763 18.9176 20.7038C18.4772 20.7313 18.0619 20.9175 17.7498 21.2274L16.3125 22.6512V16.2C16.3124 15.7591 16.1495 15.3336 15.8545 15.0041C15.5595 14.6747 15.153 14.4642 14.7121 14.4126L14.5 14.4ZM19.9357 1.7982L27.1875 9H19.9375L19.9357 1.7982Z" fill="white"/>
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
			<span class="download__label">資料下載<?php if ( $extension ) : ?><span class="download__ext">(<?php echo esc_html( $extension ); ?>)</span><?php endif; ?></span>
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
						<?php download_icon(); ?> 下載
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
