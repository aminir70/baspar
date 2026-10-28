<?php
/**
 * Product page support: sales experts (who answers which product category,
 * with WhatsApp / Bale / phone), contact links, per-product bottom text and
 * small product helpers used by the product-page widgets.
 *
 * Experts are edited in wp-admin › بسپار › کارشناسان فروش and stored in the
 * `baspar_experts` option. An expert can serve many categories and a
 * category can have many experts; a product gets the experts of its own
 * categories and of their parent categories.
 *
 * @package BasparElements
 */

namespace BasparElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const EXPERTS_OPTION = 'baspar_experts';

/**
 * Stored settings with defaults filled in.
 *
 * @return array{experts:array<int,array>,message:string,hours:string}
 */
function experts_settings() {
	$opt = get_option( EXPERTS_OPTION, null );
	if ( null === $opt ) {
		// First run: the two experts that were on the old product layout,
		// both serving every category until categories are assigned.
		$opt = array(
			'experts' => array(
				array( 'id' => 'e1', 'name' => 'خانم عزیزی', 'role' => 'کارشناس فروش', 'photo' => 0, 'whatsapp' => '09120766297', 'bale' => '', 'phone' => '09120766297', 'cats' => array(), 'is_default' => 1, 'active' => 1 ),
				array( 'id' => 'e2', 'name' => 'خانم احمدی', 'role' => 'کارشناس فروش', 'photo' => 0, 'whatsapp' => '09120766279', 'bale' => '', 'phone' => '09120766279', 'cats' => array(), 'is_default' => 1, 'active' => 1 ),
			),
		);
	}
	return wp_parse_args(
		(array) $opt,
		array(
			'experts' => array(),
			'message' => "سلام، وقت بخیر.\nبرای استعلام قیمت «{product}» پیام می‌دهم.\n{url}",
			'hours'   => 'شنبه تا چهارشنبه ۹ تا ۱۸ · پنجشنبه ۹ تا ۱۳',
		)
	);
}

/**
 * Latin digits only (Persian/Arabic digits converted), for phone numbers.
 *
 * @param string $s Input.
 * @return string
 */
function latin_digits( $s ) {
	return strtr( (string) $s, array( '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9' ) );
}

/**
 * Iranian mobile → international digits without "+" (0912… → 98912…).
 *
 * @param string $num Number as typed.
 * @return string
 */
function intl_number( $num ) {
	$d = preg_replace( '/\D+/', '', latin_digits( $num ) );
	if ( '' === $d ) {
		return '';
	}
	if ( 0 === strpos( $d, '0098' ) ) {
		$d = substr( $d, 2 );
	} elseif ( 0 === strpos( $d, '0' ) ) {
		$d = '98' . substr( $d, 1 );
	} elseif ( 0 === strpos( $d, '9' ) && 10 === strlen( $d ) ) {
		$d = '98' . $d;
	}
	return $d;
}

/**
 * WhatsApp chat link with a prefilled message.
 *
 * @param string $num  Number.
 * @param string $text Message.
 * @return string
 */
function whatsapp_link( $num, $text = '' ) {
	$d = intl_number( $num );
	if ( '' === $d ) {
		return '';
	}
	return 'https://wa.me/' . $d . ( '' !== $text ? '?text=' . rawurlencode( $text ) : '' );
}

/**
 * Bale link from a username, an "@id", a ble.ir link or a mobile number.
 *
 * @param string $v Value as typed.
 * @return string
 */
function bale_link( $v ) {
	$v = trim( (string) $v );
	if ( '' === $v ) {
		return '';
	}
	if ( preg_match( '#^https?://#i', $v ) ) {
		return $v;
	}
	$v = ltrim( $v, '@' );
	if ( preg_match( '/^[0-9۰-۹+\s-]{10,}$/u', $v ) ) {
		return 'https://ble.ir/' . intl_number( $v );
	}
	return 'https://ble.ir/' . rawurlencode( $v );
}

/**
 * tel: link.
 *
 * @param string $num Number.
 * @return string
 */
function tel_link( $num ) {
	$d = preg_replace( '/[^\d+]/', '', latin_digits( $num ) );
	return '' === $d ? '' : 'tel:' . $d;
}

/**
 * Experts for a product: those assigned to any of its categories or their
 * ancestors (in the configured order); if none, the "default" experts; if
 * none of those either, every active expert.
 *
 * @param int $product_id Product ID (0 = defaults only).
 * @return array<int,array>
 */
function experts_for_product( $product_id ) {
	$all = array_values( array_filter( (array) experts_settings()['experts'], static function ( $e ) {
		return ! empty( $e['active'] ) && ( ! empty( $e['whatsapp'] ) || ! empty( $e['bale'] ) || ! empty( $e['phone'] ) );
	} ) );
	if ( ! $all ) {
		return array();
	}
	$cats = array();
	if ( $product_id && taxonomy_exists( 'product_cat' ) ) {
		$terms = get_the_terms( $product_id, 'product_cat' );
		if ( is_array( $terms ) ) {
			foreach ( $terms as $t ) {
				$cats[] = (int) $t->term_id;
				foreach ( get_ancestors( $t->term_id, 'product_cat', 'taxonomy' ) as $a ) {
					$cats[] = (int) $a;
				}
			}
		}
	}
	$cats    = array_unique( $cats );
	$matched = array_values( array_filter( $all, static function ( $e ) use ( $cats ) {
		return $cats && array_intersect( $cats, array_map( 'intval', (array) ( $e['cats'] ?? array() ) ) );
	} ) );
	if ( $matched ) {
		return $matched;
	}
	$defaults = array_values( array_filter( $all, static function ( $e ) {
		return ! empty( $e['is_default'] );
	} ) );
	return $defaults ? $defaults : $all;
}

/**
 * Prefilled WhatsApp message for a product (template placeholders).
 *
 * @param \WC_Product|null $product Product.
 * @param array            $expert  Expert.
 * @return string
 */
function expert_message( $product, $expert = array() ) {
	$tpl = (string) experts_settings()['message'];
	if ( ! $product instanceof \WC_Product ) {
		return trim( preg_replace( '/«?\{product\}»?|\{sku\}|\{url\}/u', '', str_replace( '{expert}', $expert['name'] ?? '', $tpl ) ) );
	}
	return strtr(
		$tpl,
		array(
			'{product}' => wp_strip_all_tags( $product->get_name() ),
			'{sku}'     => (string) $product->get_sku(),
			'{url}'     => get_permalink( $product->get_id() ),
			'{expert}'  => $expert['name'] ?? '',
		)
	);
}

/**
 * Expert photo URL ('' when none).
 *
 * @param array $e Expert.
 * @return string
 */
function expert_photo( $e ) {
	$id = (int) ( $e['photo'] ?? 0 );
	return $id ? (string) wp_get_attachment_image_url( $id, 'thumbnail' ) : '';
}

/**
 * Brand terms of a product (YITH / native WooCommerce brands) with logo URL.
 *
 * @param int $product_id Product ID.
 * @return array<int,array{name:string,url:string,logo:string}>
 */
function product_brand_terms( $product_id ) {
	foreach ( array( 'yith_product_brand', 'product_brand', 'pwb-brand' ) as $tax ) {
		if ( ! taxonomy_exists( $tax ) ) {
			continue;
		}
		$terms = get_the_terms( $product_id, $tax );
		if ( ! is_array( $terms ) || ! $terms ) {
			continue;
		}
		$out = array();
		foreach ( $terms as $t ) {
			$logo_id = (int) get_term_meta( $t->term_id, 'thumbnail_id', true );
			if ( ! $logo_id ) {
				$logo_id = (int) get_term_meta( $t->term_id, 'pwb_brand_image', true );
			}
			$link  = get_term_link( $t );
			$out[] = array(
				'name' => $t->name,
				'url'  => is_wp_error( $link ) ? '' : $link,
				'logo' => $logo_id ? (string) wp_get_attachment_image_url( $logo_id, 'medium' ) : '',
			);
		}
		return $out;
	}
	return array();
}

/**
 * Category trail of a product's primary category (root → leaf).
 *
 * @param int $product_id Product ID.
 * @return \WP_Term[]
 */
function product_cat_trail( $product_id ) {
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( ! is_array( $terms ) || ! $terms ) {
		return array();
	}
	$primary = (int) get_post_meta( $product_id, 'rank_math_primary_product_cat', true );
	$leaf    = null;
	foreach ( $terms as $t ) {
		if ( $primary && (int) $t->term_id === $primary ) {
			$leaf = $t;
		}
	}
	if ( ! $leaf ) {
		// Deepest category wins.
		usort( $terms, static function ( $a, $b ) {
			return count( get_ancestors( $b->term_id, 'product_cat', 'taxonomy' ) ) - count( get_ancestors( $a->term_id, 'product_cat', 'taxonomy' ) );
		} );
		$leaf = $terms[0];
	}
	$trail = array();
	foreach ( array_reverse( get_ancestors( $leaf->term_id, 'product_cat', 'taxonomy' ) ) as $a ) {
		$t = get_term( $a, 'product_cat' );
		if ( $t && ! is_wp_error( $t ) ) {
			$trail[] = $t;
		}
	}
	$trail[] = $leaf;
	return $trail;
}

/**
 * Render the expert contact list (used by the product hero and the contact
 * widget). Every link is a plain link — nothing depends on JavaScript.
 *
 * @param \WC_Product|null $product Product.
 * @param array            $o       Options: layout (full|compact), show_phone, show_bale.
 */
function render_expert_list( $product, $o = array() ) {
	$o       = wp_parse_args( $o, array( 'layout' => 'full', 'show_phone' => true, 'show_bale' => true ) );
	$experts = experts_for_product( $product instanceof \WC_Product ? $product->get_id() : 0 );
	if ( ! $experts ) {
		if ( is_admin() || ( isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ) {
			echo '<p class="pdx-empty">' . esc_html__( 'هنوز کارشناسی تعریف نشده است: پیشخوان › بسپار › کارشناسان فروش', 'baspar-elements' ) . '</p>';
		}
		return;
	}
	echo '<ul class="pdx-list pdx-' . esc_attr( $o['layout'] ) . '">';
	foreach ( $experts as $e ) {
		$photo = expert_photo( $e );
		$wa    = ! empty( $e['whatsapp'] ) ? whatsapp_link( $e['whatsapp'], expert_message( $product, $e ) ) : '';
		$bale  = ( $o['show_bale'] && ! empty( $e['bale'] ) ) ? bale_link( $e['bale'] ) : '';
		$tel   = ( $o['show_phone'] && ! empty( $e['phone'] ) ) ? tel_link( $e['phone'] ) : '';
		echo '<li class="pdx">';
		echo '<span class="pdx-av">' . ( $photo ? '<img src="' . esc_url( $photo ) . '" alt="" loading="lazy">' : esc_html( mb_substr( preg_replace( '/^(خانم|آقای|آقا)\s+/u', '', (string) $e['name'] ), 0, 1, 'UTF-8' ) ) ) . '<i class="pdx-dot" aria-hidden="true"></i></span>';
		echo '<span class="pdx-who"><strong>' . esc_html( $e['name'] ) . '</strong>';
		if ( ! empty( $e['role'] ) ) {
			echo '<small>' . esc_html( $e['role'] ) . '</small>';
		}
		if ( $tel ) {
			echo '<a class="pdx-num" href="' . esc_url( $tel ) . '" dir="ltr">' . esc_html( fa_num( latin_digits( $e['phone'] ) ) ) . '</a>';
		}
		echo '</span><span class="pdx-btns">';
		if ( $wa ) {
			echo '<a class="pdx-btn pdx-wa" href="' . esc_url( $wa ) . '" target="_blank" rel="noopener nofollow" aria-label="' . esc_attr( sprintf( 'واتس‌اپ %s', $e['name'] ) ) . '">' . icon_svg( 'whatsapp', 18 ) . '<span>' . esc_html__( 'واتس‌اپ', 'baspar-elements' ) . '</span></a>'; // phpcs:ignore
		}
		if ( $bale ) {
			echo '<a class="pdx-btn pdx-bale" href="' . esc_url( $bale ) . '" target="_blank" rel="noopener nofollow" aria-label="' . esc_attr( sprintf( 'بله %s', $e['name'] ) ) . '">' . icon_svg( 'bale', 18 ) . '<span>' . esc_html__( 'بله', 'baspar-elements' ) . '</span></a>'; // phpcs:ignore
		}
		if ( $tel ) {
			echo '<a class="pdx-btn pdx-tel" href="' . esc_url( $tel ) . '" aria-label="' . esc_attr( sprintf( 'تماس با %s', $e['name'] ) ) . '">' . icon_svg( 'phone', 18 ) . '<span>' . esc_html__( 'تماس', 'baspar-elements' ) . '</span></a>'; // phpcs:ignore
		}
		echo '</span></li>';
	}
	echo '</ul>';
}

/* --------------------------------------------------------------------------
 * Admin: experts page
 * ------------------------------------------------------------------------ */

/**
 * Sanitize the experts form.
 *
 * @param mixed $in Raw input.
 * @return array
 */
function sanitize_experts( $in ) {
	$in  = is_array( $in ) ? $in : array();
	$out = array(
		'experts' => array(),
		'message' => isset( $in['message'] ) ? sanitize_textarea_field( wp_unslash( $in['message'] ) ) : '',
		'hours'   => isset( $in['hours'] ) ? sanitize_text_field( wp_unslash( $in['hours'] ) ) : '',
	);
	foreach ( (array) ( $in['experts'] ?? array() ) as $e ) {
		if ( ! is_array( $e ) ) {
			continue;
		}
		$name = sanitize_text_field( wp_unslash( $e['name'] ?? '' ) );
		if ( '' === $name ) {
			continue;
		}
		$out['experts'][] = array(
			'id'         => sanitize_key( $e['id'] ?? '' ) ? sanitize_key( $e['id'] ) : 'e' . wp_generate_password( 6, false, false ),
			'name'       => $name,
			'role'       => sanitize_text_field( wp_unslash( $e['role'] ?? '' ) ),
			'photo'      => absint( $e['photo'] ?? 0 ),
			'whatsapp'   => sanitize_text_field( latin_digits( wp_unslash( $e['whatsapp'] ?? '' ) ) ),
			'bale'       => sanitize_text_field( wp_unslash( $e['bale'] ?? '' ) ),
			'phone'      => sanitize_text_field( latin_digits( wp_unslash( $e['phone'] ?? '' ) ) ),
			'cats'       => array_values( array_unique( array_map( 'absint', (array) ( $e['cats'] ?? array() ) ) ) ),
			'is_default' => empty( $e['is_default'] ) ? 0 : 1,
			'active'     => empty( $e['active'] ) ? 0 : 1,
		);
	}
	return $out;
}

add_action(
	'admin_init',
	static function () {
		register_setting( 'baspar_experts_group', EXPERTS_OPTION, array( 'type' => 'array', 'sanitize_callback' => __NAMESPACE__ . '\\sanitize_experts' ) );
	}
);

add_action(
	'admin_menu',
	static function () {
		add_menu_page( __( 'بسپار', 'baspar-elements' ), __( 'بسپار', 'baspar-elements' ), 'manage_options', 'baspar-experts', __NAMESPACE__ . '\\render_experts_page', 'dashicons-groups', 58 );
		add_submenu_page( 'baspar-experts', __( 'کارشناسان فروش', 'baspar-elements' ), __( 'کارشناسان فروش', 'baspar-elements' ), 'manage_options', 'baspar-experts', __NAMESPACE__ . '\\render_experts_page' );
	}
);

add_action(
	'admin_enqueue_scripts',
	static function ( $hook ) {
		if ( false !== strpos( (string) $hook, 'baspar-experts' ) ) {
			wp_enqueue_media();
		}
	}
);

/**
 * Product categories as a flat list in tree order with depth.
 *
 * @return array<int,array{id:int,name:string,depth:int,count:int}>
 */
function product_cat_tree() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}
	$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'name' ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$by = array();
	foreach ( $terms as $t ) {
		$by[ (int) $t->parent ][] = $t;
	}
	$out  = array();
	$walk = static function ( $parent, $depth ) use ( &$walk, &$out, $by ) {
		foreach ( $by[ $parent ] ?? array() as $t ) {
			$out[] = array( 'id' => (int) $t->term_id, 'name' => $t->name, 'depth' => $depth, 'count' => (int) $t->count );
			$walk( (int) $t->term_id, $depth + 1 );
		}
	};
	$walk( 0, 0 );
	return $out;
}

/**
 * One expert card in the admin form ($i = index or "__i__" for the template).
 *
 * @param string|int $i    Index.
 * @param array      $e    Expert.
 * @param array      $tree Category tree.
 */
function render_expert_admin_card( $i, $e, $tree ) {
	$n     = EXPERTS_OPTION . '[experts][' . $i . ']';
	$photo = ! empty( $e['photo'] ) ? wp_get_attachment_image_url( (int) $e['photo'], 'thumbnail' ) : '';
	$cats  = array_map( 'intval', (array) ( $e['cats'] ?? array() ) );
	?>
	<div class="bx-card" data-card>
		<div class="bx-card-head">
			<button type="button" class="bx-photo" data-photo title="<?php esc_attr_e( 'انتخاب عکس', 'baspar-elements' ); ?>">
				<?php if ( $photo ) : ?><img src="<?php echo esc_url( $photo ); ?>" alt=""><?php else : ?><span class="dashicons dashicons-format-image"></span><?php endif; ?>
			</button>
			<input type="hidden" name="<?php echo esc_attr( $n ); ?>[photo]" value="<?php echo esc_attr( $e['photo'] ?? 0 ); ?>" data-photo-id>
			<input type="hidden" name="<?php echo esc_attr( $n ); ?>[id]" value="<?php echo esc_attr( $e['id'] ?? '' ); ?>">
			<div class="bx-grid">
				<label><?php esc_html_e( 'نام', 'baspar-elements' ); ?><input type="text" name="<?php echo esc_attr( $n ); ?>[name]" value="<?php echo esc_attr( $e['name'] ?? '' ); ?>" placeholder="خانم عزیزی"></label>
				<label><?php esc_html_e( 'سمت', 'baspar-elements' ); ?><input type="text" name="<?php echo esc_attr( $n ); ?>[role]" value="<?php echo esc_attr( $e['role'] ?? '' ); ?>" placeholder="کارشناس فروش پلیمر"></label>
				<label><?php esc_html_e( 'شماره واتس‌اپ', 'baspar-elements' ); ?><input type="text" dir="ltr" name="<?php echo esc_attr( $n ); ?>[whatsapp]" value="<?php echo esc_attr( $e['whatsapp'] ?? '' ); ?>" placeholder="09120000000"></label>
				<label><?php esc_html_e( 'بله (نام کاربری، لینک یا شماره)', 'baspar-elements' ); ?><input type="text" dir="ltr" name="<?php echo esc_attr( $n ); ?>[bale]" value="<?php echo esc_attr( $e['bale'] ?? '' ); ?>" placeholder="username یا https://ble.ir/..."></label>
				<label><?php esc_html_e( 'شماره تماس', 'baspar-elements' ); ?><input type="text" dir="ltr" name="<?php echo esc_attr( $n ); ?>[phone]" value="<?php echo esc_attr( $e['phone'] ?? '' ); ?>" placeholder="09120000000"></label>
				<div class="bx-flags">
					<label class="bx-check"><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[active]" value="1" <?php checked( ! empty( $e['active'] ) || ! isset( $e['active'] ) ); ?>> <?php esc_html_e( 'فعال', 'baspar-elements' ); ?></label>
					<label class="bx-check"><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[is_default]" value="1" <?php checked( ! empty( $e['is_default'] ) ); ?>> <?php esc_html_e( 'کارشناس پیش‌فرض (برای محصولاتی که کارشناس ندارند)', 'baspar-elements' ); ?></label>
				</div>
			</div>
			<div class="bx-tools">
				<button type="button" class="button" data-up title="<?php esc_attr_e( 'بالا', 'baspar-elements' ); ?>">↑</button>
				<button type="button" class="button" data-down title="<?php esc_attr_e( 'پایین', 'baspar-elements' ); ?>">↓</button>
				<button type="button" class="button bx-del" data-del><?php esc_html_e( 'حذف', 'baspar-elements' ); ?></button>
			</div>
		</div>
		<details class="bx-cats" <?php echo $cats ? '' : 'open'; ?>>
			<summary><?php esc_html_e( 'دسته‌های این کارشناس', 'baspar-elements' ); ?> <span class="bx-count" data-count><?php echo esc_html( count( $cats ) ); ?></span> <em><?php esc_html_e( '(انتخاب دسته اصلی، زیردسته‌هایش را هم شامل می‌شود)', 'baspar-elements' ); ?></em></summary>
			<input type="search" class="bx-filter" placeholder="<?php esc_attr_e( 'جستجوی دسته…', 'baspar-elements' ); ?>" data-filter>
			<div class="bx-cat-list">
				<?php foreach ( $tree as $c ) : ?>
					<label class="bx-cat" style="--d:<?php echo (int) $c['depth']; ?>" data-name="<?php echo esc_attr( $c['name'] ); ?>"><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[cats][]" value="<?php echo esc_attr( $c['id'] ); ?>" <?php checked( in_array( $c['id'], $cats, true ) ); ?>> <?php echo esc_html( $c['name'] ); ?> <small>(<?php echo esc_html( $c['count'] ); ?>)</small></label>
				<?php endforeach; ?>
			</div>
		</details>
	</div>
	<?php
}

/**
 * The admin page.
 */
function render_experts_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$set  = experts_settings();
	$tree = product_cat_tree();
	?>
	<div class="wrap bx-wrap">
		<h1><?php esc_html_e( 'کارشناسان فروش', 'baspar-elements' ); ?></h1>
		<p class="description"><?php esc_html_e( 'برای هر کارشناس دسته‌هایی را که جواب می‌دهد تیک بزنید. یک کارشناس می‌تواند چند دسته داشته باشد و یک دسته می‌تواند چند کارشناس داشته باشد. در صفحه هر محصول، کارشناسان دسته (و دسته‌های بالاتر) آن محصول با دکمه واتس‌اپ، بله و تماس نمایش داده می‌شوند. اگر برای دسته‌ای کارشناسی تعیین نشده باشد، «کارشناسان پیش‌فرض» نمایش داده می‌شوند.', 'baspar-elements' ); ?></p>
		<form method="post" action="options.php" id="bx-form">
			<?php settings_fields( 'baspar_experts_group' ); ?>
			<div id="bx-cards">
				<?php foreach ( array_values( (array) $set['experts'] ) as $i => $e ) {
					render_expert_admin_card( $i, $e, $tree );
				} ?>
			</div>
			<p><button type="button" class="button button-secondary" id="bx-add">+ <?php esc_html_e( 'افزودن کارشناس', 'baspar-elements' ); ?></button></p>

			<h2><?php esc_html_e( 'پیام آماده واتس‌اپ', 'baspar-elements' ); ?></h2>
			<p class="description"><?php esc_html_e( 'وقتی مشتری روی دکمه واتس‌اپ می‌زند، این متن در پیام آماده می‌شود. {product} = نام محصول، {sku} = کد محصول، {url} = لینک صفحه، {expert} = نام کارشناس.', 'baspar-elements' ); ?></p>
			<textarea name="<?php echo esc_attr( EXPERTS_OPTION ); ?>[message]" rows="4" class="large-text"><?php echo esc_textarea( $set['message'] ); ?></textarea>

			<h2><?php esc_html_e( 'ساعات پاسخگویی', 'baspar-elements' ); ?></h2>
			<input type="text" class="regular-text" name="<?php echo esc_attr( EXPERTS_OPTION ); ?>[hours]" value="<?php echo esc_attr( $set['hours'] ); ?>">

			<?php submit_button( __( 'ذخیره کارشناسان', 'baspar-elements' ) ); ?>
		</form>

		<h2><?php esc_html_e( 'خلاصه: هر دسته با کدام کارشناس', 'baspar-elements' ); ?></h2>
		<table class="widefat striped bx-summary">
			<thead><tr><th><?php esc_html_e( 'دسته', 'baspar-elements' ); ?></th><th><?php esc_html_e( 'کارشناسان (با ارث‌بری از دسته بالاتر)', 'baspar-elements' ); ?></th></tr></thead>
			<tbody>
				<?php
				$experts = array_filter( (array) $set['experts'], static function ( $e ) {
					return ! empty( $e['active'] );
				} );
				$defaults = wp_list_pluck( array_filter( $experts, static function ( $e ) {
					return ! empty( $e['is_default'] );
				} ), 'name' );
				foreach ( $tree as $c ) {
					$ids   = array_merge( array( $c['id'] ), array_map( 'intval', get_ancestors( $c['id'], 'product_cat', 'taxonomy' ) ) );
					$names = array();
					foreach ( $experts as $e ) {
						if ( array_intersect( $ids, array_map( 'intval', (array) ( $e['cats'] ?? array() ) ) ) ) {
							$names[] = $e['name'];
						}
					}
					echo '<tr><td style="padding-right:' . ( 10 + 18 * (int) $c['depth'] ) . 'px">' . esc_html( $c['name'] ) . '</td><td>' . ( $names ? esc_html( implode( '، ', $names ) ) : '<span style="color:#888">' . esc_html( $defaults ? 'پیش‌فرض: ' . implode( '، ', $defaults ) : 'همه کارشناسان فعال' ) . '</span>' ) . '</td></tr>';
				}
				?>
			</tbody>
		</table>

		<script type="text/html" id="bx-tpl"><?php render_expert_admin_card( '__i__', array( 'active' => 1 ), $tree ); ?></script>
	</div>
	<style>
		.bx-wrap{max-width:1100px}
		.bx-card{background:#fff;border:1px solid #dcdcde;border-radius:10px;margin:14px 0;box-shadow:0 1px 2px rgba(0,0,0,.04)}
		.bx-card-head{display:flex;gap:16px;align-items:flex-start;padding:16px}
		.bx-photo{flex:0 0 72px;width:72px;height:72px;border-radius:50%;border:1px dashed #b5b5b5;background:#f6f7f7;cursor:pointer;overflow:hidden;padding:0;display:flex;align-items:center;justify-content:center}
		.bx-photo img{width:100%;height:100%;object-fit:cover}
		.bx-grid{flex:1;display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px 14px}
		.bx-grid label{display:flex;flex-direction:column;gap:4px;font-weight:600;font-size:12px}
		.bx-grid input[type=text]{width:100%}
		.bx-flags{grid-column:1/-1;display:flex;gap:20px;flex-wrap:wrap}
		.bx-check{flex-direction:row!important;align-items:center;font-weight:400!important}
		.bx-tools{display:flex;flex-direction:column;gap:6px}
		.bx-del{color:#b32d2e!important;border-color:#b32d2e!important}
		.bx-cats{border-top:1px solid #f0f0f1;padding:10px 16px 14px}
		.bx-cats summary{cursor:pointer;font-weight:600}
		.bx-cats em{font-weight:400;color:#777;font-style:normal;font-size:12px}
		.bx-count{display:inline-block;min-width:20px;padding:0 6px;border-radius:10px;background:#7c3aed;color:#fff;font-size:11px;text-align:center}
		.bx-filter{margin:10px 0;width:260px}
		.bx-cat-list{columns:3;column-gap:24px;max-height:320px;overflow:auto}
		.bx-cat{display:block;padding:3px 0;padding-right:calc(var(--d) * 18px);break-inside:avoid}
		.bx-cat small{color:#999}
		@media(max-width:900px){.bx-grid{grid-template-columns:1fr}.bx-cat-list{columns:1}}
	</style>
	<script>
	( function () {
		var box = document.getElementById( 'bx-cards' ), tpl = document.getElementById( 'bx-tpl' ).innerHTML, n = box.children.length + 1000;
		function reindex() {
			[].forEach.call( box.querySelectorAll( '[data-card]' ), function ( card, i ) {
				[].forEach.call( card.querySelectorAll( '[name]' ), function ( el ) {
					el.name = el.name.replace( /\[experts\]\[[^\]]+\]/, '[experts][' + i + ']' );
				} );
			} );
		}
		document.getElementById( 'bx-add' ).addEventListener( 'click', function () {
			var d = document.createElement( 'div' );
			d.innerHTML = tpl.replace( /__i__/g, String( n++ ) );
			box.appendChild( d.firstElementChild );
			reindex();
		} );
		box.addEventListener( 'click', function ( e ) {
			var card = e.target.closest( '[data-card]' );
			if ( ! card ) { return; }
			if ( e.target.closest( '[data-del]' ) ) {
				if ( window.confirm( 'این کارشناس حذف شود؟' ) ) { card.remove(); reindex(); }
			} else if ( e.target.closest( '[data-up]' ) && card.previousElementSibling ) {
				box.insertBefore( card, card.previousElementSibling ); reindex();
			} else if ( e.target.closest( '[data-down]' ) && card.nextElementSibling ) {
				box.insertBefore( card.nextElementSibling, card ); reindex();
			} else if ( e.target.closest( '[data-photo]' ) && window.wp && wp.media ) {
				e.preventDefault();
				var frame = wp.media( { title: 'عکس کارشناس', multiple: false, library: { type: 'image' } } );
				frame.on( 'select', function () {
					var a = frame.state().get( 'selection' ).first().toJSON();
					card.querySelector( '[data-photo-id]' ).value = a.id;
					var url = ( a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url );
					card.querySelector( '[data-photo]' ).innerHTML = '<img src="' + url + '" alt="">';
				} );
				frame.open();
			}
		} );
		box.addEventListener( 'change', function ( e ) {
			var card = e.target.closest( '[data-card]' );
			if ( card && e.target.matches( '.bx-cat input' ) ) {
				card.querySelector( '[data-count]' ).textContent = card.querySelectorAll( '.bx-cat input:checked' ).length;
			}
		} );
		box.addEventListener( 'input', function ( e ) {
			if ( ! e.target.matches( '[data-filter]' ) ) { return; }
			var q = e.target.value.trim();
			[].forEach.call( e.target.closest( '[data-card]' ).querySelectorAll( '.bx-cat' ), function ( l ) {
				l.style.display = ! q || l.getAttribute( 'data-name' ).indexOf( q ) > -1 ? '' : 'none';
			} );
		} );
	} )();
	</script>
	<?php
}

/* --------------------------------------------------------------------------
 * Per-product bottom text (meta box on the product edit screen)
 * ------------------------------------------------------------------------ */

add_action(
	'add_meta_boxes_product',
	static function () {
		add_meta_box(
			'baspar_bottom_text',
			__( 'بسپار — متن پایین صفحه محصول', 'baspar-elements' ),
			static function ( $post ) {
				wp_nonce_field( 'baspar_bottom_text', 'baspar_bottom_text_nonce' );
				echo '<p class="description">' . esc_html__( 'متنی که در ویجت «متن پایین صفحه» (بعد از نظرات / محصولات مرتبط) نمایش داده می‌شود. خالی بماند تا آن بخش نمایش داده نشود.', 'baspar-elements' ) . '</p>';
				wp_editor( (string) get_post_meta( $post->ID, '_baspar_bottom_text', true ), 'baspar_bottom_text', array( 'textarea_rows' => 8, 'media_buttons' => true ) );
			},
			'product',
			'normal',
			'low'
		);
	}
);

add_action(
	'save_post_product',
	static function ( $post_id ) {
		if ( ! isset( $_POST['baspar_bottom_text_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['baspar_bottom_text_nonce'] ), 'baspar_bottom_text' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$val = isset( $_POST['baspar_bottom_text'] ) ? wp_kses_post( wp_unslash( $_POST['baspar_bottom_text'] ) ) : '';
		if ( '' === trim( $val ) ) {
			delete_post_meta( $post_id, '_baspar_bottom_text' );
		} else {
			update_post_meta( $post_id, '_baspar_bottom_text', $val );
		}
	}
);
