<?php
/**
 * Petrochemical base prices — admin page (بسپار ← قیمت‌های پایه).
 *
 * @package BasparElements
 */

namespace BasparElements\Prices;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	static function () {
		add_submenu_page( 'baspar-experts', __( 'قیمت‌های پایه', 'baspar-elements' ), __( 'قیمت‌های پایه', 'baspar-elements' ), 'manage_options', 'baspar-prices', __NAMESPACE__ . '\\render_admin' );
	},
	20
);

add_action( 'admin_post_baspar_prices', __NAMESPACE__ . '\\handle_admin' );

/**
 * Redirect back to the page with a notice.
 *
 * @param string $msg  Message.
 * @param bool   $ok   Success.
 */
function back( $msg, $ok = true ) {
	set_transient( 'baspar_prices_notice_' . get_current_user_id(), array( 'msg' => $msg, 'ok' => $ok ), 60 );
	wp_safe_redirect( admin_url( 'admin.php?page=baspar-prices' ) );
	exit;
}

/**
 * Manual meta (date + dollar) from the form.
 *
 * @return array
 */
function manual_meta() {
	$date = jalali_to_mysql( sanitize_text_field( wp_unslash( $_POST['jdate'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	return array(
		'src_id'       => 0,
		'source'       => 'manual',
		'url'          => '',
		'title'        => __( 'ورود دستی', 'baspar-elements' ),
		'published_at' => $date ? $date : current_time( 'mysql' ),
		'valid_until'  => '',
		'dollar'       => (int) to_int( wp_unslash( $_POST['dollar'] ?? '' ) ) * 10, // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- digits only.
		'pdf_url'      => '',
	);
}

/**
 * Form handler.
 */
function handle_admin() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی ندارید.', 'baspar-elements' ) );
	}
	check_admin_referer( 'baspar_prices' );
	maybe_install();
	$do = sanitize_key( $_POST['do'] ?? '' );
	switch ( $do ) {
		case 'check':
			$r = check_latest();
			back( $r['message'], $r['ok'] );
			break;
		case 'backfill':
			$r = queue_backfill( (int) ( $_POST['weeks'] ?? 52 ) );
			if ( $r['ok'] ) {
				spawn_cron();
			}
			back( $r['message'], $r['ok'] );
			break;
		case 'url':
			$id = import_url( wp_unslash( $_POST['url'] ?? '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in import_url().
			back( is_wp_error( $id ) ? $id->get_error_message() : __( 'قیمت‌های این خبر وارد شد.', 'baspar-elements' ), ! is_wp_error( $id ) );
			break;
		case 'html':
			if ( empty( $_FILES['html']['tmp_name'] ) || ! is_uploaded_file( $_FILES['html']['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				back( __( 'فایلی انتخاب نشده است.', 'baspar-elements' ), false );
			}
			$html   = (string) file_get_contents( $_FILES['html']['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.Security.ValidatedSanitizedInput
			$parsed = parse_post( $html );
			if ( is_wp_error( $parsed ) ) {
				back( $parsed->get_error_message(), false );
			}
			list( $meta, $rows ) = $parsed;
			if ( empty( $meta['published_at'] ) ) {
				$meta['published_at'] = manual_meta()['published_at'];
			}
			$id = store_week( $meta, $rows );
			back( is_wp_error( $id ) ? $id->get_error_message() : sprintf( '%d گرید از فایل وارد شد.', count( $rows ) ), ! is_wp_error( $id ) );
			break;
		case 'text':
		case 'csv':
			$text = 'csv' === $do && ! empty( $_FILES['csv']['tmp_name'] ) && is_uploaded_file( $_FILES['csv']['tmp_name'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				? (string) file_get_contents( $_FILES['csv']['tmp_name'] ) // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.Security.ValidatedSanitizedInput
				: (string) wp_unslash( $_POST['text'] ?? '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- parsed as plain text, every cell cleaned.
			$text = preg_replace( '/^\xEF\xBB\xBF/', '', $text );
			$rows = 'csv' === $do ? parse_csv( $text ) : parse_text( $text );
			if ( ! $rows ) {
				back( __( 'هیچ ردیف قیمتی شناخته نشد.', 'baspar-elements' ), false );
			}
			$id = store_week( manual_meta(), $rows );
			back( is_wp_error( $id ) ? $id->get_error_message() : sprintf( '%d گرید وارد شد.', count( $rows ) ), ! is_wp_error( $id ) );
			break;
		case 'delete':
			global $wpdb;
			$t  = tables();
			$id = (int) ( $_POST['week'] ?? 0 );
			$wpdb->delete( $t['rows'], array( 'week_id' => $id ) );
			$wpdb->delete( $t['weeks'], array( 'id' => $id ) );
			flush_cache();
			back( __( 'هفته حذف شد.', 'baspar-elements' ) );
			break;
		case 'settings':
			$in = wp_unslash( $_POST['s'] ?? array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each field sanitized below.
			update_option(
				OPTION,
				array(
					'auto'        => empty( $in['auto'] ) ? 0 : 1,
					'reader'      => empty( $in['reader'] ) ? 0 : 1,
					'archive_url' => esc_url_raw( $in['archive_url'] ?? '' ) ? esc_url_raw( $in['archive_url'] ) : settings()['archive_url'],
					'attribution' => sanitize_text_field( $in['attribution'] ?? '' ),
					'whatsapp'    => sanitize_text_field( $in['whatsapp'] ?? '' ),
					'vat'         => max( 0, min( 30, (float) ( $in['vat'] ?? 10 ) ) ),
					'sidebar'     => empty( $in['sidebar'] ) ? 0 : 1,
					'page_url'    => esc_url_raw( $in['page_url'] ?? '' ),
				),
				false
			);
			schedule();
			flush_cache();
			back( __( 'تنظیمات ذخیره شد.', 'baspar-elements' ) );
			break;
	}
	back( __( 'درخواست نامعتبر.', 'baspar-elements' ), false );
}

/**
 * The page.
 */
function render_admin() {
	maybe_install();
	$s      = settings();
	$st     = state();
	$weeks  = weeks( 120 );
	$latest = $weeks ? $weeks[0] : null;
	$notice = get_transient( 'baspar_prices_notice_' . get_current_user_id() );
	delete_transient( 'baspar_prices_notice_' . get_current_user_id() );
	$next   = wp_next_scheduled( 'baspar_prices_check' );
	$queue  = isset( $st['queue'] ) ? count( (array) $st['queue'] ) : 0;
	$form   = static function ( $do, $inner, $multipart = false ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"' . ( $multipart ? ' enctype="multipart/form-data"' : '' ) . '>';
		wp_nonce_field( 'baspar_prices' );
		echo '<input type="hidden" name="action" value="baspar_prices"><input type="hidden" name="do" value="' . esc_attr( $do ) . '">';
		echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup built below.
		echo '</form>';
	};
	$manual = '<p class="bp-row"><label>' . esc_html__( 'تاریخ اعلام (شمسی)', 'baspar-elements' ) . ' <input type="text" name="jdate" placeholder="1405/08/10" dir="ltr" size="11"></label> <label>' . esc_html__( 'دلار پایه (تومان)', 'baspar-elements' ) . ' <input type="text" name="dollar" placeholder="185863" dir="ltr" size="9"></label></p>';
	?>
	<div class="wrap baspar-prices-admin" dir="rtl">
		<h1><?php esc_html_e( 'قیمت پایه محصولات پتروشیمی', 'baspar-elements' ); ?></h1>
		<?php if ( $notice ) : ?><div class="notice notice-<?php echo $notice['ok'] ? 'success' : 'error'; ?> is-dismissible"><p><?php echo esc_html( $notice['msg'] ); ?></p></div><?php endif; ?>
		<style>
			.baspar-prices-admin .bp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin-top:16px}
			.baspar-prices-admin .bp-card{background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:16px 18px}
			.baspar-prices-admin .bp-card h2{margin:0 0 10px;font-size:15px}
			.baspar-prices-admin .bp-kv{display:grid;grid-template-columns:auto 1fr;gap:6px 14px;margin:0}
			.baspar-prices-admin .bp-kv dt{color:#646970}.baspar-prices-admin .bp-kv dd{margin:0;font-weight:600}
			.baspar-prices-admin .bp-err{color:#b32d2e;font-weight:600}
			.baspar-prices-admin .bp-row{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
			.baspar-prices-admin textarea{width:100%;min-height:120px;direction:rtl}
			.baspar-prices-admin .bp-hint{color:#646970;font-size:12px;margin:6px 0}
			.baspar-prices-admin table.widefat td,.baspar-prices-admin table.widefat th{vertical-align:middle}
		</style>
		<div class="bp-grid">
			<div class="bp-card">
				<h2><?php esc_html_e( 'وضعیت', 'baspar-elements' ); ?></h2>
				<dl class="bp-kv">
					<dt><?php esc_html_e( 'آخرین هفته', 'baspar-elements' ); ?></dt><dd><?php echo $latest ? esc_html( jdate( $latest->published_at ) . ' — ' . $latest->row_count . ' گرید' ) : '—'; ?></dd>
					<dt><?php esc_html_e( 'هفته‌های ذخیره‌شده', 'baspar-elements' ); ?></dt><dd><?php echo esc_html( count( $weeks ) ); ?></dd>
					<dt><?php esc_html_e( 'آخرین بررسی', 'baspar-elements' ); ?></dt><dd><?php echo esc_html( ( $st['last_check'] ?? '—' ) . ( ! empty( $st['last_via'] ) ? ' (' . ( 'reader' === $st['last_via'] ? 'از طریق سرویس واسط' : 'مستقیم' ) . ')' : '' ) ); ?></dd>
					<dt><?php esc_html_e( 'بررسی خودکار بعدی', 'baspar-elements' ); ?></dt><dd><?php echo $next ? esc_html( get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $next ), 'Y-m-d H:i' ) ) : esc_html__( 'خاموش', 'baspar-elements' ); ?></dd>
					<?php if ( $queue ) : ?><dt><?php esc_html_e( 'ورود سابقه', 'baspar-elements' ); ?></dt><dd><?php echo esc_html( sprintf( '%d از %d هفته باقی مانده', $queue, (int) ( $st['queue_total'] ?? $queue ) ) ); ?></dd><?php endif; ?>
					<?php if ( ! empty( $st['last_error'] ) ) : ?><dt><?php esc_html_e( 'آخرین خطا', 'baspar-elements' ); ?></dt><dd class="bp-err"><?php echo esc_html( $st['last_error'] ); ?></dd><?php endif; ?>
				</dl>
				<div class="bp-row" style="margin-top:12px">
					<?php $form( 'check', '<button class="button button-primary">' . esc_html__( 'بررسی و به‌روزرسانی الان', 'baspar-elements' ) . '</button>' ); ?>
					<?php $form( 'backfill', '<select name="weeks"><option value="13">۳ ماه</option><option value="26">۶ ماه</option><option value="52" selected>۱ سال</option><option value="104">۲ سال</option></select> <button class="button">' . esc_html__( 'ورود سابقه قیمت', 'baspar-elements' ) . '</button>' ); ?>
				</div>
				<p class="bp-hint"><?php esc_html_e( 'سابقه در پس‌زمینه و چند هفته در هر نوبت وارد می‌شود؛ برای دیدن پیشرفت صفحه را تازه کنید.', 'baspar-elements' ); ?></p>
				<?php if ( ! empty( $st['queue_errors'] ) ) : ?><details><summary><?php esc_html_e( 'خطاهای ورود سابقه', 'baspar-elements' ); ?></summary><ul><?php foreach ( (array) $st['queue_errors'] as $e ) : ?><li><?php echo esc_html( $e ); ?></li><?php endforeach; ?></ul></details><?php endif; ?>
			</div>

			<div class="bp-card">
				<h2><?php esc_html_e( 'ورود دستی (وقتی خودکار کار نکرد)', 'baspar-elements' ); ?></h2>
				<?php $form( 'url', '<p class="bp-row"><input type="url" name="url" dir="ltr" style="flex:1;min-width:220px" placeholder="https://wikiplast.ir/news/…"> <button class="button">' . esc_html__( 'ورود از لینک خبر', 'baspar-elements' ) . '</button></p>' ); ?>
				<?php $form( 'html', '<p class="bp-row"><input type="file" name="html" accept=".html,.htm,text/html"> <button class="button">' . esc_html__( 'ورود از فایل HTML ذخیره‌شده', 'baspar-elements' ) . '</button></p><p class="bp-hint">' . esc_html__( 'صفحه خبر را در مرورگر باز کنید و با Ctrl+S ذخیره کنید.', 'baspar-elements' ) . '</p>', true ); ?>
				<details><summary><?php esc_html_e( 'چسباندن جدول یا فایل CSV', 'baspar-elements' ); ?></summary>
					<?php $form( 'text', $manual . '<textarea name="text" placeholder="' . esc_attr__( 'جدول قیمت را از صفحه خبر کپی کنید و اینجا بچسبانید (نام گروه در یک خط، سپس ردیف‌ها: گرید، قیمت، قیمت قبل، اختلاف، تولیدکننده).', 'baspar-elements' ) . '"></textarea><p><button class="button">' . esc_html__( 'ورود متن', 'baspar-elements' ) . '</button></p>' ); ?>
					<?php $form( 'csv', $manual . '<p class="bp-row"><input type="file" name="csv" accept=".csv,.txt,text/csv"> <button class="button">' . esc_html__( 'ورود CSV', 'baspar-elements' ) . '</button></p><p class="bp-hint">' . esc_html__( 'ستون‌ها: گروه، گرید، تولیدکننده، قیمت، قیمت قبل (ریال بر کیلوگرم).', 'baspar-elements' ) . '</p>', true ); ?>
				</details>
			</div>

			<div class="bp-card">
				<h2><?php esc_html_e( 'تنظیمات', 'baspar-elements' ); ?></h2>
				<?php
				ob_start();
				?>
				<p><label><input type="checkbox" name="s[auto]" value="1" <?php checked( $s['auto'] ); ?>> <?php esc_html_e( 'بررسی خودکار روزی دو بار', 'baspar-elements' ); ?></label></p>
				<p><label><input type="checkbox" name="s[reader]" value="1" <?php checked( $s['reader'] ); ?>> <?php esc_html_e( 'اگر سرور به ویکی‌پلاست دسترسی نداشت، از سرویس واسط r.jina.ai بخوان', 'baspar-elements' ); ?></label></p>
				<p><label><?php esc_html_e( 'آدرس آرشیو', 'baspar-elements' ); ?><br><input type="url" name="s[archive_url]" value="<?php echo esc_attr( $s['archive_url'] ); ?>" dir="ltr" class="regular-text"></label></p>
				<p><label><?php esc_html_e( 'متن منبع زیر جدول', 'baspar-elements' ); ?><br><input type="text" name="s[attribution]" value="<?php echo esc_attr( $s['attribution'] ); ?>" class="large-text"></label></p>
				<p><label><?php esc_html_e( 'شماره واتس‌اپ استعلام (خالی = کارشناس پیش‌فرض)', 'baspar-elements' ); ?><br><input type="text" name="s[whatsapp]" value="<?php echo esc_attr( $s['whatsapp'] ); ?>" dir="ltr"></label></p>
				<p><label><input type="checkbox" name="s[sidebar]" value="1" <?php checked( $s['sidebar'] ); ?>> <?php esc_html_e( 'بنر قیمت پایه در سایدبار نوشته‌ها و محصولات (خودکار)', 'baspar-elements' ); ?></label></p>
				<p><label><?php esc_html_e( 'آدرس صفحه قیمت‌ها (خالی = پیدا کردن خودکار)', 'baspar-elements' ); ?><br><input type="url" name="s[page_url]" value="<?php echo esc_attr( $s['page_url'] ); ?>" dir="ltr" class="regular-text" placeholder="<?php echo esc_attr( page_url() ); ?>"></label></p>
				<p><label><?php esc_html_e( 'ارزش افزوده در ماشین‌حساب (٪)', 'baspar-elements' ); ?> <input type="number" name="s[vat]" value="<?php echo esc_attr( $s['vat'] ); ?>" min="0" max="30" step="0.5" style="width:70px"></label></p>
				<p><button class="button button-primary"><?php esc_html_e( 'ذخیره', 'baspar-elements' ); ?></button></p>
				<?php
				$form( 'settings', ob_get_clean() );
				?>
			</div>
		</div>

		<h2 style="margin-top:24px"><?php esc_html_e( 'هفته‌ها', 'baspar-elements' ); ?></h2>
		<table class="widefat striped">
			<thead><tr><th><?php esc_html_e( 'تاریخ', 'baspar-elements' ); ?></th><th><?php esc_html_e( 'عنوان', 'baspar-elements' ); ?></th><th><?php esc_html_e( 'گرید', 'baspar-elements' ); ?></th><th><?php esc_html_e( 'دلار (تومان)', 'baspar-elements' ); ?></th><th><?php esc_html_e( 'منبع', 'baspar-elements' ); ?></th><th></th></tr></thead>
			<tbody>
				<?php if ( ! $weeks ) : ?><tr><td colspan="6"><?php esc_html_e( 'هنوز چیزی وارد نشده است. «بررسی و به‌روزرسانی الان» را بزنید.', 'baspar-elements' ); ?></td></tr><?php endif; ?>
				<?php foreach ( $weeks as $w ) : ?>
					<tr>
						<td><?php echo esc_html( jdate( $w->published_at ) ); ?></td>
						<td><?php echo $w->url ? '<a href="' . esc_url( $w->url ) . '" target="_blank" rel="noopener">' . esc_html( $w->title ) . '</a>' : esc_html( $w->title ); ?></td>
						<td><?php echo esc_html( $w->row_count ); ?></td>
						<td><?php echo $w->dollar ? esc_html( number_format( $w->dollar / 10 ) ) : '—'; ?></td>
						<td><?php echo esc_html( 'manual' === $w->source ? 'دستی' : 'ویکی‌پلاست' ); ?></td>
						<td><?php $form( 'delete', '<input type="hidden" name="week" value="' . (int) $w->id . '"><button class="button-link-delete" onclick="return confirm(\'حذف این هفته؟\')">' . esc_html__( 'حذف', 'baspar-elements' ) . '</button>' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
