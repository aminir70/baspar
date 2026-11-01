<?php
/**
 * Price board — weekly petrochemical base prices (all groups and grades)
 * with search, filters, sorting, units, bookmarks, comparison, history
 * charts, a purchase calculator and price-inquiry buttons.
 *
 * The full table is rendered on the server (search engines and visitors
 * without JavaScript see every price); assets/js/prices.js adds the
 * interactive layer on top of the same data.
 *
 * @package BasparElements
 */

namespace BasparElements\Widgets;

use Elementor\Controls_Manager;
use function BasparElements\icon_svg;
use function BasparElements\fa_num;
use BasparElements\Prices;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Baspar_Price_Board extends Baspar_Widget_Base {
	public function get_name() { return 'baspar-price-board'; }
	public function get_title() { return __( 'بسپار — قیمت پایه محصولات پتروشیمی (پویا)', 'baspar-elements' ); }
	public function get_icon() { return 'eicon-table'; }
	public function get_keywords() { return array( 'price', 'قیمت', 'بورس', 'پتروشیمی', 'پلیمر', 'baspar' ); }

	public function get_script_depends() {
		return array( 'baspar-elements', 'baspar-prices' );
	}

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'محتوا', 'baspar-elements' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'برچسب لاتین', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'PETROCHEMICAL BASE PRICES' ) );
		$this->add_control( 'title', array( 'label' => __( 'عنوان', 'baspar-elements' ), 'type' => Controls_Manager::TEXT, 'default' => 'قیمت پایه محصولات پتروشیمی' ) );
		$this->add_control( 'title_tag', array( 'label' => __( 'تگ عنوان', 'baspar-elements' ), 'type' => Controls_Manager::SELECT, 'default' => 'h1', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'div' => 'div' ) ) );
		$this->add_control( 'subtitle', array( 'label' => __( 'توضیح ({count} = تعداد گرید، {groups} = تعداد گروه)', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => 'قیمت پایه هفتگی {count} گرید از {groups} گروه محصولات پلیمری در بورس کالا؛ با تغییرات هفته، نمودار سابقه قیمت و ماشین‌حساب خرید.' ) );
		$this->add_control( 'unit', array(
			'label'   => __( 'واحد پیش‌فرض', 'baspar-elements' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'toman',
			'options' => array( 'toman' => 'تومان / کیلوگرم', 'rial' => 'ریال / کیلوگرم', 'mton' => 'میلیون تومان / تن', 'usd' => 'دلار / تن' ),
		) );
		$this->add_control( 'show_pulse', array( 'label' => __( 'نبض بازار (خلاصه هفته)', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_tiles', array( 'label' => __( 'کاشی گروه‌ها', 'baspar-elements' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'note', array( 'label' => __( 'یادداشت زیر جدول', 'baspar-elements' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3, 'default' => 'قیمت‌ها «قیمت پایه» عرضه در بورس کالا (ریال بر کیلوگرم، بدون ارزش افزوده و حمل) هستند و با قیمت فروش در بازار فرق دارند. برای قیمت روز، موجودی و ارسال با کارشناسان بسپارمارکت تماس بگیرید.' ) );
		$this->end_controls_section();
	}

	/**
	 * Formatted price for the server-rendered table (toman / kg default).
	 *
	 * @param int    $rial Rial per kg.
	 * @param string $unit Unit.
	 * @param int    $usd  Dollar rate (rial).
	 * @return string
	 */
	private function fmt( $rial, $unit, $usd ) {
		switch ( $unit ) {
			case 'rial':
				return self::num( $rial );
			case 'mton':
				return self::num( $rial / 10000, 1 );
			case 'usd':
				return $usd ? self::num( $rial * 1000 / $usd ) : '—';
			default:
				return self::num( round( $rial / 10 ) );
		}
	}

	/**
	 * Persian number with Persian separators (same as the script's fa-IR).
	 *
	 * @param float $n        Number.
	 * @param int   $decimals Decimals.
	 * @return string
	 */
	private static function num( $n, $decimals = 0 ) {
		return strtr( fa_num( number_format( (float) $n, $decimals, '.', ',' ) ), array( ',' => '٬', '.' => '٫' ) );
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( ! function_exists( '\\BasparElements\\Prices\\board' ) ) {
			return;
		}
		$week_id = isset( $_GET['pweek'] ) ? absint( $_GET['pweek'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$b       = Prices\board( $week_id );
		if ( ! $b ) {
			if ( $this->is_editor() || current_user_can( 'manage_options' ) ) {
				echo '<div class="baspar-scope"><div class="prb-empty">' . esc_html__( 'هنوز قیمتی وارد نشده است. از «بسپار ← قیمت‌های پایه» به‌روزرسانی کنید.', 'baspar-elements' ) . '</div></div>';
			}
			return;
		}
		$unit     = in_array( $s['unit'], array( 'toman', 'rial', 'mton', 'usd' ), true ) ? $s['unit'] : 'toman';
		$tag      = in_array( $s['title_tag'], array( 'h1', 'h2', 'div' ), true ) ? $s['title_tag'] : 'h1';
		$w        = $b['week'];
		$groups   = array();
		foreach ( $b['groups'] as $g ) {
			$groups[ $g['id'] ] = $g;
		}
		$prods    = array();
		foreach ( $b['producers'] as $p ) {
			$prods[ $p['id'] ] = $p['name'];
		}
		$dollar_t = $w['dollar'] ? round( $w['dollar'] / 10 ) : 0;
		$dollar_c = ( $w['dollar'] && $w['dollar_prev'] ) ? round( ( $w['dollar'] - $w['dollar_prev'] ) * 100 / $w['dollar_prev'], 2 ) : null;
		$sub      = strtr( (string) $s['subtitle'], array( '{count}' => fa_num( $w['count'] ), '{groups}' => fa_num( count( $b['groups'] ) ) ) );
		$units    = array( 'toman' => 'تومان/کیلو', 'rial' => 'ریال/کیلو', 'mton' => 'م.تومان/تن', 'usd' => 'دلار/تن' );
		$set      = Prices\settings();
		$contact  = Prices\inquiry_contacts();
		$cfg      = array(
			'unit'  => $unit,
			'vat'   => (float) $set['vat'],
			'rest'  => esc_url_raw( rest_url( 'baspar/v1/prices/history' ) ),
			'wa'    => $contact['wa'],
			'bale'  => $contact['bale'],
			'tel'   => $contact['tel'],
			'page'  => get_permalink(),
			'week'  => $w['id'],
		);
		$sign = static function ( $v ) {
			if ( null === $v ) {
				return '';
			}
			return ( $v > 0 ? '+' : ( $v < 0 ? '−' : '' ) ) . self::num( abs( $v ), abs( $v ) < 10 ? 2 : 1 ) . '٪';
		};
		?>
		<div class="baspar-scope">
			<section class="prb" data-prb data-unit="<?php echo esc_attr( $unit ); ?>">
				<header class="prb-hero">
					<div class="prb-hero-t">
						<?php if ( $s['kicker'] ) : ?><span class="prb-kicker mono"><i aria-hidden="true"></i><?php echo esc_html( $s['kicker'] ); ?></span><?php endif; ?>
						<<?php echo $tag; // phpcs:ignore ?> class="prb-title"><?php echo esc_html( $s['title'] ); ?></<?php echo $tag; // phpcs:ignore ?>>
						<?php if ( $sub ) : ?><p class="prb-sub"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
						<ul class="prb-meta">
							<li><?php echo icon_svg( 'clock', 15 ); // phpcs:ignore ?><span><?php esc_html_e( 'هفته', 'baspar-elements' ); ?> <b><?php echo esc_html( $w['date'] ); ?></b></span></li>
							<?php if ( $w['valid_until'] ) : ?><li><?php echo icon_svg( 'check', 15 ); // phpcs:ignore ?><span><?php esc_html_e( 'معتبر تا', 'baspar-elements' ); ?> <b><?php echo esc_html( $w['valid_until'] ); ?></b></span></li><?php endif; ?>
							<li><?php echo icon_svg( 'layers', 15 ); // phpcs:ignore ?><span><b><?php echo esc_html( fa_num( $w['count'] ) ); ?></b> <?php esc_html_e( 'گرید', 'baspar-elements' ); ?></span></li>
							<?php if ( $w['pdf'] ) : ?><li><a href="<?php echo esc_url( $w['pdf'] ); ?>" target="_blank" rel="noopener nofollow"><?php echo icon_svg( 'doc', 15 ); // phpcs:ignore ?><span><?php esc_html_e( 'فایل رسمی (PDF)', 'baspar-elements' ); ?></span></a></li><?php endif; ?>
						</ul>
					</div>
					<?php if ( $dollar_t ) : ?>
						<div class="prb-dollar">
							<span class="prb-dollar-k"><?php esc_html_e( 'دلار پایه محاسبه (حواله مرکز مبادله)', 'baspar-elements' ); ?></span>
							<strong><?php echo esc_html( self::num( $dollar_t ) ); ?> <small><?php esc_html_e( 'تومان', 'baspar-elements' ); ?></small></strong>
							<?php if ( null !== $dollar_c ) : ?><span class="prb-chg <?php echo $dollar_c > 0 ? 'is-up' : ( $dollar_c < 0 ? 'is-down' : '' ); ?>"><?php echo esc_html( $sign( $dollar_c ) ); ?> <?php esc_html_e( 'نسبت به هفته قبل', 'baspar-elements' ); ?></span><?php endif; ?>
							<span class="prb-dollar-spark" data-prb-dollar aria-hidden="true"></span>
						</div>
					<?php endif; ?>
				</header>

				<?php if ( 'yes' === $s['show_pulse'] && null !== $b['stats']['avg'] ) :
					$st    = $b['stats'];
					$total = max( 1, $st['up'] + $st['down'] + $st['flat'] );
					$by_k  = array();
					foreach ( $b['rows'] as $r ) {
						$by_k[ $r['k'] ] = $r;
					}
					?>
					<div class="prb-pulse">
						<div class="prb-pulse-card">
							<span class="prb-pulse-k"><?php esc_html_e( 'میانگین تغییر هفته', 'baspar-elements' ); ?></span>
							<strong class="<?php echo $st['avg'] > 0 ? 'is-up' : ( $st['avg'] < 0 ? 'is-down' : '' ); ?>"><?php echo esc_html( $sign( $st['avg'] ) ); ?></strong>
							<div class="prb-bar" role="img" aria-label="<?php echo esc_attr( sprintf( '%s افزایشی، %s کاهشی، %s بدون تغییر', fa_num( $st['up'] ), fa_num( $st['down'] ), fa_num( $st['flat'] ) ) ); ?>">
								<i class="up" style="width:<?php echo esc_attr( round( $st['up'] * 100 / $total, 2 ) ); ?>%"></i><i class="flat" style="width:<?php echo esc_attr( round( $st['flat'] * 100 / $total, 2 ) ); ?>%"></i><i class="down" style="width:<?php echo esc_attr( round( $st['down'] * 100 / $total, 2 ) ); ?>%"></i>
							</div>
							<span class="prb-legend"><b class="up"><?php echo esc_html( fa_num( $st['up'] ) ); ?></b> <?php esc_html_e( 'افزایشی', 'baspar-elements' ); ?> · <b><?php echo esc_html( fa_num( $st['flat'] ) ); ?></b> <?php esc_html_e( 'ثابت', 'baspar-elements' ); ?> · <b class="down"><?php echo esc_html( fa_num( $st['down'] ) ); ?></b> <?php esc_html_e( 'کاهشی', 'baspar-elements' ); ?></span>
						</div>
						<?php foreach ( array( 'top_up' => __( 'بیشترین افزایش', 'baspar-elements' ), 'top_down' => __( 'بیشترین کاهش', 'baspar-elements' ) ) as $key => $label ) :
							if ( empty( $st[ $key ] ) ) {
								continue;
							}
							?>
							<div class="prb-pulse-card is-list">
								<span class="prb-pulse-k"><?php echo esc_html( $label ); ?></span>
								<ol>
									<?php foreach ( array_slice( $st[ $key ], 0, 3 ) as $k ) :
										$r = $by_k[ $k ];
										?>
										<li><button type="button" data-prb-open="<?php echo esc_attr( $k ); ?>"><span class="nm"><?php echo esc_html( $r['gr'] ); ?><small><?php echo esc_html( $prods[ $r['p'] ] ?? '' ); ?></small></span><span class="prb-chg <?php echo 'top_up' === $key ? 'is-up' : 'is-down'; ?>"><?php echo esc_html( $sign( $r['c'] ) ); ?></span></button></li>
									<?php endforeach; ?>
								</ol>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( 'yes' === $s['show_tiles'] ) : ?>
					<div class="prb-tiles" role="group" aria-label="<?php esc_attr_e( 'گروه‌های محصول', 'baspar-elements' ); ?>">
						<?php foreach ( $b['groups'] as $g ) : ?>
							<button type="button" class="prb-tile<?php echo null === $g['avg'] ? '' : ( $g['avg'] > 0 ? ' is-up' : ( $g['avg'] < 0 ? ' is-down' : '' ) ); ?>" data-prb-group="<?php echo esc_attr( $g['id'] ); ?>" aria-pressed="false" style="--heat:<?php echo esc_attr( null === $g['avg'] ? 0 : min( 1, abs( $g['avg'] ) / 8 ) ); ?>">
								<span class="t"><?php echo esc_html( $g['short'] ); ?></span>
								<span class="n"><?php echo esc_html( $g['name'] === $g['short'] ? fa_num( $g['n'] ) . ' گرید' : $g['name'] ); ?></span>
								<span class="c"><?php echo esc_html( null === $g['avg'] ? '—' : $sign( $g['avg'] ) ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="prb-tools" data-prb-tools hidden>
					<label class="prb-search"><?php echo icon_svg( 'search', 18 ); // phpcs:ignore ?><input type="search" data-prb-q placeholder="<?php esc_attr_e( 'جستجوی گرید یا پتروشیمی… (مثلاً S65، 0209، جم)', 'baspar-elements' ); ?>" aria-label="<?php esc_attr_e( 'جستجو', 'baspar-elements' ); ?>"></label>
					<div class="prb-tools-row">
						<div class="prb-pop" data-prb-pop>
							<button type="button" class="prb-btn" data-prb-pop-btn aria-expanded="false"><?php echo icon_svg( 'factory', 16 ); // phpcs:ignore ?><span data-prb-prod-label><?php esc_html_e( 'همه پتروشیمی‌ها', 'baspar-elements' ); ?></span><?php echo icon_svg( 'arrow-down', 14 ); // phpcs:ignore ?></button>
							<div class="prb-pop-panel" data-prb-pop-panel hidden>
								<input type="search" class="prb-pop-q" data-prb-pop-q placeholder="<?php esc_attr_e( 'جستجوی پتروشیمی', 'baspar-elements' ); ?>">
								<div class="prb-pop-list">
									<?php foreach ( $b['producers'] as $p ) : ?>
										<label><input type="checkbox" value="<?php echo esc_attr( $p['id'] ); ?>" data-prb-prod><span><?php echo esc_html( $p['name'] ); ?></span><small><?php echo esc_html( fa_num( $p['n'] ) ); ?></small></label>
									<?php endforeach; ?>
								</div>
								<button type="button" class="prb-link" data-prb-prod-clear><?php esc_html_e( 'پاک کردن انتخاب', 'baspar-elements' ); ?></button>
							</div>
						</div>
						<div class="prb-seg" role="group" aria-label="<?php esc_attr_e( 'جهت تغییر', 'baspar-elements' ); ?>">
							<button type="button" data-prb-dir="all" class="is-on"><?php esc_html_e( 'همه', 'baspar-elements' ); ?></button>
							<button type="button" data-prb-dir="up"><?php esc_html_e( 'افزایشی', 'baspar-elements' ); ?></button>
							<button type="button" data-prb-dir="down"><?php esc_html_e( 'کاهشی', 'baspar-elements' ); ?></button>
						</div>
						<label class="prb-select"><span class="screen-reader-text"><?php esc_html_e( 'مرتب‌سازی', 'baspar-elements' ); ?></span>
							<select data-prb-sort>
								<option value="group"><?php esc_html_e( 'به ترتیب گروه', 'baspar-elements' ); ?></option>
								<option value="pct-desc"><?php esc_html_e( 'بیشترین افزایش', 'baspar-elements' ); ?></option>
								<option value="pct-asc"><?php esc_html_e( 'بیشترین کاهش', 'baspar-elements' ); ?></option>
								<option value="price-asc"><?php esc_html_e( 'ارزان‌ترین', 'baspar-elements' ); ?></option>
								<option value="price-desc"><?php esc_html_e( 'گران‌ترین', 'baspar-elements' ); ?></option>
								<option value="name"><?php esc_html_e( 'نام گرید', 'baspar-elements' ); ?></option>
							</select>
						</label>
						<div class="prb-seg is-unit" role="group" aria-label="<?php esc_attr_e( 'واحد', 'baspar-elements' ); ?>">
							<?php foreach ( $units as $u => $label ) : ?><button type="button" data-prb-unit="<?php echo esc_attr( $u ); ?>"<?php echo $u === $unit ? ' class="is-on"' : ''; ?>><?php echo esc_html( $label ); ?></button><?php endforeach; ?>
						</div>
						<button type="button" class="prb-btn is-icon" data-prb-stars aria-pressed="false" title="<?php esc_attr_e( 'فقط نشان‌شده‌ها', 'baspar-elements' ); ?>"><?php echo icon_svg( 'star', 17 ); // phpcs:ignore ?><span class="prb-stars-n" data-prb-stars-n></span></button>
						<button type="button" class="prb-btn is-icon" data-prb-csv title="<?php esc_attr_e( 'دانلود اکسل (CSV) همین فهرست', 'baspar-elements' ); ?>"><?php echo icon_svg( 'arrow-down', 17 ); // phpcs:ignore ?><span><?php esc_html_e( 'CSV', 'baspar-elements' ); ?></span></button>
						<button type="button" class="prb-btn is-icon" data-prb-share title="<?php esc_attr_e( 'کپی لینک همین فیلترها', 'baspar-elements' ); ?>"><?php echo icon_svg( 'link', 17 ); // phpcs:ignore ?></button>
						<?php if ( count( $b['weeks'] ) > 1 ) : ?>
							<label class="prb-select is-week"><span class="screen-reader-text"><?php esc_html_e( 'هفته', 'baspar-elements' ); ?></span>
								<select data-prb-week>
									<?php foreach ( $b['weeks'] as $wk ) : ?><option value="<?php echo esc_attr( $wk['id'] ); ?>"<?php selected( $wk['id'], $w['id'] ); ?>><?php echo esc_html( 'هفته ' . $wk['date'] ); ?></option><?php endforeach; ?>
								</select>
							</label>
						<?php endif; ?>
					</div>
				</div>

				<p class="prb-count" data-prb-count aria-live="polite"></p>

				<div class="prb-tablewrap">
					<table class="prb-table">
						<caption class="screen-reader-text"><?php echo esc_html( $s['title'] . ' — ' . $w['date'] ); ?></caption>
						<thead><tr>
							<th class="c-star"><span class="screen-reader-text"><?php esc_html_e( 'نشان', 'baspar-elements' ); ?></span></th>
							<th class="c-grade"><?php esc_html_e( 'گرید', 'baspar-elements' ); ?></th>
							<th class="c-prod"><?php esc_html_e( 'تولیدکننده', 'baspar-elements' ); ?></th>
							<th class="c-price"><?php esc_html_e( 'قیمت پایه', 'baspar-elements' ); ?> <small data-prb-unit-label><?php echo esc_html( $units[ $unit ] ); ?></small></th>
							<th class="c-chg"><?php esc_html_e( 'تغییر هفته', 'baspar-elements' ); ?></th>
							<th class="c-spark"><?php esc_html_e( 'روند', 'baspar-elements' ); ?></th>
							<th class="c-act"><span class="screen-reader-text"><?php esc_html_e( 'عملیات', 'baspar-elements' ); ?></span></th>
						</tr></thead>
						<tbody data-prb-body>
							<?php
							$cur = null;
							foreach ( $b['rows'] as $r ) :
								if ( $r['g'] !== $cur ) :
									$cur = $r['g'];
									$g   = $groups[ $cur ];
									?>
									<tr class="prb-grp"><th colspan="7" scope="rowgroup"><span><?php echo esc_html( $g['name'] ); ?></span><small><?php echo esc_html( fa_num( $g['n'] ) ); ?> <?php esc_html_e( 'گرید', 'baspar-elements' ); ?><?php echo null !== $g['avg'] ? ' · ' . esc_html( $sign( $g['avg'] ) ) : ''; ?></small></th></tr>
								<?php endif;
								$chg = null !== $r['pv'] ? $r['v'] - $r['pv'] : null;
								?>
								<tr class="prb-row" data-k="<?php echo esc_attr( $r['k'] ); ?>">
									<td class="c-star"></td>
									<th class="c-grade" scope="row"><?php echo esc_html( $r['gr'] ); ?></th>
									<td class="c-prod"><?php echo esc_html( $prods[ $r['p'] ] ?? '' ); ?></td>
									<td class="c-price"><?php echo esc_html( $this->fmt( $r['v'], $unit, $w['dollar'] ) ); ?></td>
									<td class="c-chg"><?php if ( null !== $r['c'] ) : ?><span class="prb-chg <?php echo $chg > 0 ? 'is-up' : ( $chg < 0 ? 'is-down' : '' ); ?>"><?php echo esc_html( $sign( $r['c'] ) ); ?></span><?php endif; ?></td>
									<td class="c-spark"></td>
									<td class="c-act"></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<div class="prb-empty-q" data-prb-none hidden><?php echo icon_svg( 'search', 28 ); // phpcs:ignore ?><p><?php esc_html_e( 'گریدی با این فیلترها پیدا نشد.', 'baspar-elements' ); ?></p><button type="button" class="prb-link" data-prb-reset><?php esc_html_e( 'حذف همه فیلترها', 'baspar-elements' ); ?></button></div>

				<footer class="prb-foot">
					<?php if ( $s['note'] ) : ?><p class="prb-note"><?php echo icon_svg( 'info', 16 ); // phpcs:ignore ?><span><?php echo esc_html( $s['note'] ); ?></span></p><?php endif; ?>
					<?php if ( $set['attribution'] ) : ?><p class="prb-src"><?php echo esc_html( $set['attribution'] ); ?><?php if ( $w['url'] ) : ?> — <a href="<?php echo esc_url( $w['url'] ); ?>" target="_blank" rel="noopener nofollow"><?php esc_html_e( 'خبر این هفته', 'baspar-elements' ); ?></a><?php endif; ?></p><?php endif; ?>
				</footer>

				<div class="prb-tray" data-prb-tray hidden>
					<div class="prb-tray-in"><span class="prb-tray-k"><?php echo icon_svg( 'layers', 16 ); // phpcs:ignore ?><?php esc_html_e( 'مقایسه', 'baspar-elements' ); ?></span><div class="prb-tray-list" data-prb-tray-list></div><button type="button" class="prb-btn is-primary" data-prb-compare><?php esc_html_e( 'مقایسه نمودار', 'baspar-elements' ); ?></button><button type="button" class="prb-link" data-prb-tray-clear><?php esc_html_e( 'پاک کردن', 'baspar-elements' ); ?></button></div>
				</div>

				<script type="application/json" data-prb-data><?php echo wp_json_encode( array( 'cfg' => $cfg, 'b' => $b, 'prod' => $prods ), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP ); ?></script>
			</section>
			<?php $this->schema( $s, $b ); ?>
		</div>
		<?php
	}

	/**
	 * schema.org Dataset for the weekly table.
	 *
	 * @param array $s Settings.
	 * @param array $b Board.
	 */
	private function schema( $s, $b ) {
		$data = array(
			'@context'           => 'https://schema.org',
			'@type'              => 'Dataset',
			'name'               => $s['title'] . ' — ' . $b['week']['date'],
			'description'        => 'قیمت پایه هفتگی ' . $b['week']['count'] . ' گرید محصولات پتروشیمی (پلی‌اتیلن، پلی‌پروپیلن، پی‌وی‌سی، پلی‌استایرن، PET، ABS، SBR و…) برای عرضه در بورس کالای ایران، به ریال بر کیلوگرم.',
			'url'                => get_permalink(),
			'inLanguage'         => 'fa-IR',
			'dateModified'       => $b['week']['iso'],
			'temporalCoverage'   => $b['week']['iso'],
			'creator'            => array( '@type' => 'Organization', 'name' => 'مدیریت توسعه صنایع پایین‌دستی پتروشیمی' ),
			'publisher'          => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ),
			'variableMeasured'   => 'قیمت پایه (ریال بر کیلوگرم)',
			'keywords'           => array_values( array_map( static function ( $g ) { return $g['name']; }, $b['groups'] ) ),
		);
		if ( $b['week']['url'] ) {
			$data['isBasedOn'] = $b['week']['url'];
		}
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>';
	}
}
