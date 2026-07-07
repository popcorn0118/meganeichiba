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
function child_download_get_acf_object_id() {

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
 * 下載圖示（文件 + 下載箭頭）
 */
function child_download_icon() {
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
add_action( 'astra_footer_before', 'child_render_download_block' );
function child_render_download_block() {

	$object_id = child_download_get_acf_object_id();

	if ( ! $object_id || ! get_field( 'show', $object_id ) ) {
		return;
	}

	$download_desc          = get_field( 'download-desc', 'option' );
	$password_download_desc = get_field( 'password-download-desc', 'option' );
	?>
	<div class="child-download" data-object-id="<?php echo esc_attr( $object_id ); ?>">

		<button type="button" class="child-download__trigger">
			<span class="child-download__icon"><?php child_download_icon(); ?></span>
			<span class="child-download__label">資料下載(PDF)</span>
		</button>

		<?php if ( $download_desc ) : ?>
			<div class="child-download__notice"><?php echo wp_kses_post( $download_desc ); ?></div>
		<?php endif; ?>

		<div class="child-download-modal" aria-hidden="true">
			<div class="child-download-modal__overlay"></div>

			<div class="child-download-modal__box" role="dialog" aria-modal="true" aria-labelledby="child-download-modal-title">
				<button type="button" class="child-download-modal__close" aria-label="關閉">&times;</button>

				<h3 id="child-download-modal-title" class="child-download-modal__title">資料下載</h3>

				<form class="child-download-modal__form" autocomplete="off">
					<input type="password" name="download_password" class="child-download-modal__input" autocomplete="new-password" aria-label="下載密鑰">

					<?php if ( $password_download_desc ) : ?>
						<div class="child-download-modal__desc"><?php echo wp_kses_post( $password_download_desc ); ?></div>
					<?php endif; ?>

					<button type="submit" class="child-download-modal__submit" aria-label="下載">
						<?php child_download_icon(); ?>
					</button>

					<p class="child-download-modal__error" hidden></p>
				</form>
			</div>
		</div>
	</div>
	<?php
}

/**
 * 載入下載功能專用的 CSS / JS（僅在區塊會顯示的頁面載入）
 */
add_action( 'wp_enqueue_scripts', 'child_download_enqueue_assets' );
function child_download_enqueue_assets() {

	$object_id = child_download_get_acf_object_id();

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
		'nonce'    => wp_create_nonce( 'child_download_nonce' ),
	] );
}

/**
 * AJAX：驗證下載密鑰，成功則回傳檔案網址
 */
add_action( 'wp_ajax_child_verify_download_password', 'child_verify_download_password' );
add_action( 'wp_ajax_nopriv_child_verify_download_password', 'child_verify_download_password' );
function child_verify_download_password() {

	check_ajax_referer( 'child_download_nonce', 'nonce' );

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

	$type = get_field( 'type', $object_id );
	$url  = '';

	if ( 'file' === $type ) {
		$file = get_field( 'upload-file', $object_id );

		if ( is_array( $file ) && ! empty( $file['url'] ) ) {
			$url = $file['url'];
		}
	} elseif ( 'link' === $type ) {
		$url = get_field( 'file-link', $object_id );
	}

	if ( ! $url ) {
		wp_send_json_error( [ 'message' => '目前無可下載的檔案' ] );
	}

	wp_send_json_success( [ 'url' => $url ] );
}
