/* ============================================================
   Baspar Elements — petrochemical base price board.
   Interactive layer for widgets/class-baspar-price-board.php:
   search, filters, sort, units, bookmarks, shareable filters (#hash),
   comparison, history charts (SVG), purchase calculator, CSV export.
   No dependencies.
   ============================================================ */
( function () {
	'use strict';

	var STAR_KEY = 'bspr_price_stars';
	var COLORS = [ '#6D28D9', '#DB2777', '#0EA5E9', '#F59E0B' ];
	var UNIT_LABEL = { toman: 'تومان/کیلو', rial: 'ریال/کیلو', mton: 'م.تومان/تن', usd: 'دلار/تن' };

	function fa( n, digits ) {
		if ( null === n || undefined === n || isNaN( n ) ) { return '—'; }
		return Number( n ).toLocaleString( 'fa-IR', { maximumFractionDigits: digits || 0, minimumFractionDigits: digits || 0 } );
	}
	function esc( s ) {
		return String( s ).replace( /[&<>"']/g, function ( c ) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ]; } );
	}
	function norm( s ) {
		return String( s || '' ).toLowerCase()
			.replace( /[۰-۹]/g, function ( d ) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf( d ); } )
			.replace( /[٠-٩]/g, function ( d ) { return '٠١٢٣٤٥٦٧٨٩'.indexOf( d ); } )
			.replace( /ي/g, 'ی' ).replace( /ك/g, 'ک' ).replace( /[\s\-_().‌]/g, '' );
	}
	function load( key, fb ) { try { var v = JSON.parse( localStorage.getItem( key ) ); return v || fb; } catch ( e ) { return fb; } }
	function save( key, v ) { try { localStorage.setItem( key, JSON.stringify( v ) ); } catch ( e ) {} }
	function pctText( c ) {
		if ( null === c || undefined === c ) { return ''; }
		var a = Math.abs( c );
		return ( c > 0 ? '+' : ( c < 0 ? '−' : '' ) ) + fa( a, a < 10 ? 2 : 1 ) + '٪';
	}
	// "1405/08/10" → "۱۴۰۵/۰۸/۱۰"
	function faDate( s ) { return esc( s ).replace( /\d/g, function ( d ) { return '۰۱۲۳۴۵۶۷۸۹'[ d ]; } ); }
	function cls( c ) { return c > 0 ? 'is-up' : ( c < 0 ? 'is-down' : '' ); }
	var ICON = {
		star: '<svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"><path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8-4.3-4.1 5.9-.9z"/></svg>',
		chart: '<svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20h18M5 16l4-5 4 3 6-8"/></svg>',
		cmp: '<svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M8 4v16M16 4v16M4 8h8M12 16h8"/></svg>',
		wa: '<svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M.06 24l1.69-6.16A11.87 11.87 0 0 1 .16 11.9C.16 5.34 5.5 0 12.05 0a11.82 11.82 0 0 1 8.41 3.49 11.82 11.82 0 0 1 3.48 8.41c0 6.56-5.34 11.9-11.9 11.9a11.9 11.9 0 0 1-5.69-1.45L.06 24zm6.6-3.81c1.67 1 3.27 1.59 5.39 1.59 5.45 0 9.89-4.43 9.89-9.88 0-5.47-4.42-9.89-9.88-9.89-5.45 0-9.89 4.43-9.89 9.88 0 2.23.65 3.9 1.75 5.65l-1 3.65 3.74-.99z"/></svg>',
		x: '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>'
	};

	function PriceBoard( root ) {
		var raw = root.querySelector( '[data-prb-data]' );
		if ( ! raw ) { return; }
		var D = JSON.parse( raw.textContent );
		this.root = root;
		this.cfg = D.cfg;
		this.b = D.b;
		this.prod = D.prod;
		this.groups = {};
		this.byKey = {};
		var self = this;
		this.b.groups.forEach( function ( g ) { self.groups[ g.id ] = g; } );
		this.b.rows.forEach( function ( r, i ) {
			r.i = i;
			r.pn = self.prod[ r.p ] || '';
			r.gn = ( self.groups[ r.g ] || {} ).name || '';
			r.idx = norm( r.gr + ' ' + r.pn + ' ' + r.gn + ' ' + ( self.groups[ r.g ] || {} ).short );
			self.byKey[ r.k ] = r;
		} );
		this.st = { q: '', groups: [], prods: [], dir: 'all', sort: 'group', unit: this.cfg.unit, stars: false };
		this.stars = load( STAR_KEY, [] );
		this.cmp = [];
		this.hist = {};
		this.readHash();
		this.bind();
		this.syncControls();
		this.render();
		this.drawDollar();
		var tools = root.querySelector( '[data-prb-tools]' );
		if ( tools ) { tools.hidden = false; }
		// Keep the sticky toolbar below the site's sticky header.
		var place = function () {
			var h = document.querySelector( '.baspar-scope .header, .elementor-location-header .elementor-sticky' );
			var px = h ? Math.round( h.getBoundingClientRect().height ) + 8 : 8;
			root.style.setProperty( '--prb-sticky', Math.min( px, 140 ) + 'px' );
		};
		if ( window.requestAnimationFrame ) { requestAnimationFrame( function () { setTimeout( place, 0 ); } ); }
		window.addEventListener( 'resize', place );
	}

	PriceBoard.prototype = {
		/* ---- state <-> URL hash (shareable filters) -------------------- */
		readHash: function () {
			var h = ( location.hash || '' ).replace( /^#/, '' );
			if ( ! /(^|&)(q|g|p|d|s|u|f)=/.test( h ) ) { return; }
			var st = this.st;
			h.split( '&' ).forEach( function ( kv ) {
				var p = kv.split( '=' ), k = p[ 0 ], v = decodeURIComponent( p[ 1 ] || '' );
				if ( 'q' === k ) { st.q = v; }
				if ( 'g' === k && v ) { st.groups = v.split( ',' ).map( Number ); }
				if ( 'p' === k && v ) { st.prods = v.split( ',' ).map( Number ); }
				if ( 'd' === k ) { st.dir = v; }
				if ( 's' === k ) { st.sort = v; }
				if ( 'u' === k && UNIT_LABEL[ v ] ) { st.unit = v; }
				if ( 'f' === k ) { st.stars = '1' === v; }
			} );
		},
		hash: function () {
			var st = this.st, parts = [];
			if ( st.q ) { parts.push( 'q=' + encodeURIComponent( st.q ) ); }
			if ( st.groups.length ) { parts.push( 'g=' + st.groups.join( ',' ) ); }
			if ( st.prods.length ) { parts.push( 'p=' + st.prods.join( ',' ) ); }
			if ( 'all' !== st.dir ) { parts.push( 'd=' + st.dir ); }
			if ( 'group' !== st.sort ) { parts.push( 's=' + st.sort ); }
			if ( st.unit !== this.cfg.unit ) { parts.push( 'u=' + st.unit ); }
			if ( st.stars ) { parts.push( 'f=1' ); }
			return parts.join( '&' );
		},
		writeHash: function () {
			var h = this.hash();
			var url = location.pathname + location.search + ( h ? '#' + h : '' );
			try { history.replaceState( null, '', url ); } catch ( e ) {}
		},

		/* ---- controls ---------------------------------------------------- */
		bind: function () {
			var self = this, r = this.root, t;
			var q = r.querySelector( '[data-prb-q]' );
			if ( q ) {
				q.value = this.st.q;
				q.addEventListener( 'input', function () {
					clearTimeout( t );
					t = setTimeout( function () { self.st.q = q.value.trim(); self.update(); }, 120 );
				} );
			}
			r.addEventListener( 'click', function ( e ) {
				var el;
				if ( ( el = e.target.closest( '[data-prb-group]' ) ) ) {
					var g = Number( el.getAttribute( 'data-prb-group' ) ), i = self.st.groups.indexOf( g );
					if ( i > -1 ) { self.st.groups.splice( i, 1 ); } else { self.st.groups.push( g ); }
					self.update();
					return;
				}
				if ( ( el = e.target.closest( '[data-prb-dir]' ) ) ) { self.st.dir = el.getAttribute( 'data-prb-dir' ); self.update(); return; }
				if ( ( el = e.target.closest( '[data-prb-unit]' ) ) ) { self.st.unit = el.getAttribute( 'data-prb-unit' ); self.update(); return; }
				if ( ( el = e.target.closest( '[data-prb-stars]' ) ) ) { self.st.stars = ! self.st.stars; self.update(); return; }
				if ( ( el = e.target.closest( '[data-prb-star]' ) ) ) { self.toggleStar( el.getAttribute( 'data-prb-star' ) ); return; }
				if ( ( el = e.target.closest( '[data-prb-cmp]' ) ) ) { self.toggleCmp( el.getAttribute( 'data-prb-cmp' ) ); return; }
				if ( ( el = e.target.closest( '[data-prb-open]' ) ) ) { self.openDetail( el.getAttribute( 'data-prb-open' ) ); return; }
				if ( e.target.closest( '[data-prb-csv]' ) ) { self.csv(); return; }
				if ( e.target.closest( '[data-prb-share]' ) ) { self.share( e.target.closest( '[data-prb-share]' ) ); return; }
				if ( e.target.closest( '[data-prb-reset]' ) ) { self.reset(); return; }
				if ( e.target.closest( '[data-prb-compare]' ) ) { self.openCompare(); return; }
				if ( e.target.closest( '[data-prb-tray-clear]' ) ) { self.cmp = []; self.renderTray(); self.paintRowState(); return; }
				if ( ( el = e.target.closest( '[data-prb-tray-x]' ) ) ) { self.toggleCmp( el.getAttribute( 'data-prb-tray-x' ) ); return; }
				if ( e.target.closest( '[data-prb-pop-btn]' ) ) { self.togglePop(); return; }
				if ( e.target.closest( '[data-prb-prod-clear]' ) ) { self.st.prods = []; self.syncControls(); self.update(); return; }
			} );
			r.addEventListener( 'change', function ( e ) {
				var el = e.target;
				if ( el.matches( '[data-prb-sort]' ) ) { self.st.sort = el.value; self.update(); }
				if ( el.matches( '[data-prb-prod]' ) ) {
					self.st.prods = Array.prototype.map.call( r.querySelectorAll( '[data-prb-prod]:checked' ), function ( c ) { return Number( c.value ); } );
					self.update();
				}
				if ( el.matches( '[data-prb-week]' ) ) {
					var u = new URL( location.href );
					if ( Number( el.value ) === self.b.weeks[ 0 ].id ) { u.searchParams.delete( 'pweek' ); } else { u.searchParams.set( 'pweek', el.value ); }
					location.href = u.toString();
				}
			} );
			var pq = r.querySelector( '[data-prb-pop-q]' );
			if ( pq ) {
				pq.addEventListener( 'input', function () {
					var v = norm( pq.value );
					r.querySelectorAll( '.prb-pop-list label' ).forEach( function ( l ) { l.hidden = v && norm( l.textContent ).indexOf( v ) < 0; } );
				} );
			}
			document.addEventListener( 'click', function ( e ) {
				if ( ! e.target.closest( '[data-prb-pop]' ) ) { self.togglePop( false ); }
			} );
			document.addEventListener( 'keydown', function ( e ) {
				if ( 'Escape' === e.key ) { self.togglePop( false ); self.closeModal(); }
			} );
			// Opening a shared link / back-forward within the page.
			window.addEventListener( 'hashchange', function () {
				if ( ( location.hash || '' ).replace( /^#/, '' ) === self.hash() ) { return; }
				self.st = { q: '', groups: [], prods: [], dir: 'all', sort: 'group', unit: self.cfg.unit, stars: false };
				self.readHash();
				if ( q ) { q.value = self.st.q; }
				self.syncControls();
				self.render();
			} );
		},
		togglePop: function ( force ) {
			var p = this.root.querySelector( '[data-prb-pop-panel]' ), b = this.root.querySelector( '[data-prb-pop-btn]' );
			if ( ! p ) { return; }
			var open = undefined === force ? p.hidden : force;
			p.hidden = ! open;
			b.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		},
		syncControls: function () {
			var st = this.st, r = this.root, self = this;
			r.querySelectorAll( '[data-prb-group]' ).forEach( function ( b ) {
				var on = st.groups.indexOf( Number( b.getAttribute( 'data-prb-group' ) ) ) > -1;
				b.classList.toggle( 'is-on', on );
				b.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
			} );
			r.querySelectorAll( '[data-prb-dir]' ).forEach( function ( b ) { b.classList.toggle( 'is-on', b.getAttribute( 'data-prb-dir' ) === st.dir ); } );
			r.querySelectorAll( '[data-prb-unit]' ).forEach( function ( b ) { b.classList.toggle( 'is-on', b.getAttribute( 'data-prb-unit' ) === st.unit ); } );
			var sort = r.querySelector( '[data-prb-sort]' );
			if ( sort ) { sort.value = st.sort; }
			r.querySelectorAll( '[data-prb-prod]' ).forEach( function ( c ) { c.checked = st.prods.indexOf( Number( c.value ) ) > -1; } );
			var lab = r.querySelector( '[data-prb-prod-label]' );
			if ( lab ) {
				lab.textContent = ! st.prods.length ? 'همه پتروشیمی‌ها' : ( 1 === st.prods.length ? ( self.prod[ st.prods[ 0 ] ] || '' ) : fa( st.prods.length ) + ' پتروشیمی' );
			}
			var sb = r.querySelector( '[data-prb-stars]' );
			if ( sb ) { sb.classList.toggle( 'is-on', st.stars ); sb.setAttribute( 'aria-pressed', st.stars ? 'true' : 'false' ); }
			var sn = r.querySelector( '[data-prb-stars-n]' );
			if ( sn ) { sn.textContent = this.stars.length ? fa( this.stars.length ) : ''; }
			var ul = r.querySelector( '[data-prb-unit-label]' );
			if ( ul ) { ul.textContent = UNIT_LABEL[ st.unit ]; }
		},
		update: function () {
			this.syncControls();
			this.render();
			this.writeHash();
		},
		reset: function () {
			this.st = { q: '', groups: [], prods: [], dir: 'all', sort: 'group', unit: this.st.unit, stars: false };
			var q = this.root.querySelector( '[data-prb-q]' );
			if ( q ) { q.value = ''; }
			this.update();
		},

		/* ---- values ------------------------------------------------------ */
		val: function ( rial, unit ) {
			unit = unit || this.st.unit;
			if ( null === rial || undefined === rial ) { return null; }
			if ( 'rial' === unit ) { return rial; }
			if ( 'mton' === unit ) { return rial / 10000; }
			if ( 'usd' === unit ) { return this.b.week.dollar ? rial * 1000 / this.b.week.dollar : null; }
			return Math.round( rial / 10 );
		},
		fmt: function ( rial, unit ) {
			unit = unit || this.st.unit;
			var v = this.val( rial, unit );
			return fa( v, 'mton' === unit ? 1 : 0 );
		},

		/* ---- filtering + table ------------------------------------------- */
		visible: function () {
			var st = this.st, q = norm( st.q ), stars = this.stars, out = this.b.rows.filter( function ( r ) {
				if ( st.groups.length && st.groups.indexOf( r.g ) < 0 ) { return false; }
				if ( st.prods.length && st.prods.indexOf( r.p ) < 0 ) { return false; }
				if ( 'up' === st.dir && ! ( r.c > 0 ) ) { return false; }
				if ( 'down' === st.dir && ! ( r.c < 0 ) ) { return false; }
				if ( st.stars && stars.indexOf( r.k ) < 0 ) { return false; }
				if ( q && r.idx.indexOf( q ) < 0 ) { return false; }
				return true;
			} );
			var by = {
				'pct-desc': function ( a, b ) { return ( b.c === null ? -1e9 : b.c ) - ( a.c === null ? -1e9 : a.c ); },
				'pct-asc': function ( a, b ) { return ( a.c === null ? 1e9 : a.c ) - ( b.c === null ? 1e9 : b.c ); },
				'price-asc': function ( a, b ) { return a.v - b.v; },
				'price-desc': function ( a, b ) { return b.v - a.v; },
				'name': function ( a, b ) { return a.gr.localeCompare( b.gr, 'fa' ); }
			}[ st.sort ];
			if ( by ) { out.sort( by ); }
			return out;
		},
		spark: function ( s, c ) {
			var pts = ( s || [] ).filter( function ( v ) { return null !== v; } );
			if ( pts.length < 2 ) { return '<span class="prb-nospark">—</span>'; }
			var min = Math.min.apply( null, pts ), max = Math.max.apply( null, pts ), w = 84, h = 26, n = pts.length;
			var d = pts.map( function ( v, i ) {
				var x = 2 + ( w - 4 ) * i / ( n - 1 ), y = max === min ? h / 2 : 2 + ( h - 4 ) * ( 1 - ( v - min ) / ( max - min ) );
				return ( i ? 'L' : 'M' ) + x.toFixed( 1 ) + ' ' + y.toFixed( 1 );
			} ).join( ' ' );
			// RTL page, time runs left→right as in every chart.
			return '<svg class="prb-spark ' + cls( c ) + '" viewBox="0 0 ' + w + ' ' + h + '" width="' + w + '" height="' + h + '" aria-hidden="true"><path d="' + d + ' L' + ( w - 2 ) + ' ' + h + ' L2 ' + h + 'Z" class="a"/><path d="' + d + '" class="l"/></svg>';
		},
		rowHtml: function ( r ) {
			var star = this.stars.indexOf( r.k ) > -1, inCmp = this.cmp.indexOf( r.k ) > -1, chg = null === r.pv ? null : r.v - r.pv;
			return '<tr class="prb-row' + ( star ? ' is-star' : '' ) + ( inCmp ? ' is-cmp' : '' ) + '" data-k="' + esc( r.k ) + '">' +
				'<td class="c-star"><button type="button" class="prb-ic is-star' + ( star ? ' is-on' : '' ) + '" data-prb-star="' + esc( r.k ) + '" aria-pressed="' + ( star ? 'true' : 'false' ) + '" aria-label="نشان کردن ' + esc( r.gr ) + '">' + ICON.star + '</button></td>' +
				'<th class="c-grade" scope="row"><button type="button" class="prb-gr" data-prb-open="' + esc( r.k ) + '">' + esc( r.gr ) + '</button><span class="prb-gr-g">' + esc( ( this.groups[ r.g ] || {} ).short || '' ) + '</span></th>' +
				'<td class="c-prod" data-l="تولیدکننده">' + esc( r.pn ) + '</td>' +
				'<td class="c-price" data-l="قیمت">' + this.fmt( r.v ) + '</td>' +
				'<td class="c-chg" data-l="تغییر">' + ( null === r.c ? '<span class="prb-chg">جدید</span>' : '<span class="prb-chg ' + cls( chg ) + '" title="' + ( chg > 0 ? '+' : '' ) + this.fmt( Math.abs( chg ) ) + '">' + pctText( r.c ) + '</span>' ) + '</td>' +
				'<td class="c-spark">' + this.spark( r.s, r.c ) + '</td>' +
				'<td class="c-act"><button type="button" class="prb-ic" data-prb-open="' + esc( r.k ) + '" aria-label="نمودار و جزئیات ' + esc( r.gr ) + '" title="نمودار و ماشین‌حساب">' + ICON.chart + '</button>' +
				'<button type="button" class="prb-ic' + ( inCmp ? ' is-on' : '' ) + '" data-prb-cmp="' + esc( r.k ) + '" aria-pressed="' + ( inCmp ? 'true' : 'false' ) + '" aria-label="افزودن به مقایسه" title="مقایسه">' + ICON.cmp + '</button>' +
				( this.cfg.wa ? '<a class="prb-ic is-wa" href="' + esc( this.waLink( r ) ) + '" target="_blank" rel="noopener nofollow" aria-label="استعلام قیمت ' + esc( r.gr ) + ' در واتس‌اپ" title="استعلام قیمت">' + ICON.wa + '</a>' : '' ) +
				'</td></tr>';
		},
		render: function () {
			var rows = this.visible(), html = [], cur = null, self = this, grouped = 'group' === this.st.sort;
			rows.forEach( function ( r ) {
				if ( grouped && r.g !== cur ) {
					cur = r.g;
					var g = self.groups[ cur ] || {};
					html.push( '<tr class="prb-grp"><th colspan="7" scope="rowgroup"><span>' + esc( g.name || '' ) + '</span><small>' + fa( g.n ) + ' گرید' + ( null !== g.avg && undefined !== g.avg ? ' · <b class="' + cls( g.avg ) + '">' + pctText( g.avg ) + '</b>' : '' ) + '</small></th></tr>' );
				}
				html.push( self.rowHtml( r ) );
			} );
			var body = this.root.querySelector( '[data-prb-body]' );
			body.innerHTML = html.join( '' );
			this.root.querySelector( '[data-prb-none]' ).hidden = rows.length > 0;
			var cnt = this.root.querySelector( '[data-prb-count]' );
			var filtered = rows.length !== this.b.rows.length;
			cnt.innerHTML = filtered
				? 'نمایش <b>' + fa( rows.length ) + '</b> از ' + fa( this.b.rows.length ) + ' گرید · <button type="button" class="prb-link" data-prb-reset>حذف فیلترها</button>'
				: '<b>' + fa( rows.length ) + '</b> گرید · قیمت‌ها به ' + UNIT_LABEL[ this.st.unit ];
			this.root.setAttribute( 'data-unit', this.st.unit );
		},
		paintRowState: function () {
			var self = this;
			this.root.querySelectorAll( '.prb-row' ).forEach( function ( tr ) {
				var k = tr.getAttribute( 'data-k' ), on = self.cmp.indexOf( k ) > -1;
				tr.classList.toggle( 'is-cmp', on );
				var b = tr.querySelector( '[data-prb-cmp]' );
				if ( b ) { b.classList.toggle( 'is-on', on ); b.setAttribute( 'aria-pressed', on ? 'true' : 'false' ); }
			} );
		},

		/* ---- bookmarks + compare ----------------------------------------- */
		toggleStar: function ( k ) {
			var i = this.stars.indexOf( k );
			if ( i > -1 ) { this.stars.splice( i, 1 ); } else { this.stars.push( k ); }
			save( STAR_KEY, this.stars );
			var tr = this.root.querySelector( '.prb-row[data-k="' + k + '"]' );
			if ( tr ) {
				var on = i < 0, b = tr.querySelector( '[data-prb-star]' );
				tr.classList.toggle( 'is-star', on );
				b.classList.toggle( 'is-on', on );
				b.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
			}
			this.syncControls();
			if ( this.st.stars ) { this.render(); }
		},
		toggleCmp: function ( k ) {
			var i = this.cmp.indexOf( k );
			if ( i > -1 ) { this.cmp.splice( i, 1 ); } else {
				if ( this.cmp.length >= 4 ) { this.cmp.shift(); }
				this.cmp.push( k );
			}
			this.renderTray();
			this.paintRowState();
		},
		renderTray: function () {
			var tray = this.root.querySelector( '[data-prb-tray]' ), self = this;
			if ( ! tray ) { return; }
			tray.hidden = ! this.cmp.length;
			this.root.classList.toggle( 'has-tray', this.cmp.length > 0 );
			tray.querySelector( '[data-prb-tray-list]' ).innerHTML = this.cmp.map( function ( k, i ) {
				var r = self.byKey[ k ];
				return '<span class="prb-chip" style="--c:' + COLORS[ i ] + '"><i></i>' + esc( r.gr ) + '<button type="button" data-prb-tray-x="' + esc( k ) + '" aria-label="حذف">' + ICON.x + '</button></span>';
			} ).join( '' );
			tray.querySelector( '[data-prb-compare]' ).disabled = this.cmp.length < 2;
		},

		/* ---- history + charts -------------------------------------------- */
		fetchHistory: function ( keys, weeks ) {
			var self = this, need = keys.filter( function ( k ) { return ! self.hist[ k + ':' + weeks ]; } );
			if ( ! need.length ) {
				return Promise.resolve( keys.map( function ( k ) { return self.hist[ k + ':' + weeks ]; } ) );
			}
			var url = this.cfg.rest + ( this.cfg.rest.indexOf( '?' ) > -1 ? '&' : '?' ) + 'keys=' + encodeURIComponent( need.join( ',' ) ) + '&weeks=' + weeks;
			return fetch( url, { credentials: 'same-origin' } ).then( function ( r ) { return r.json(); } ).then( function ( d ) {
				need.forEach( function ( k ) { self.hist[ k + ':' + weeks ] = ( d.series && d.series[ k ] ) || []; } );
				return keys.map( function ( k ) { return self.hist[ k + ':' + weeks ]; } );
			} );
		},
		/**
		 * Line chart (SVG). series: [{name, color, pts: [[label, rial], …]}].
		 */
		chart: function ( box, series, unit ) {
			var self = this;
			var W = Math.max( 280, box.clientWidth || 600 ), H = W < 480 ? 210 : 260, P = { t: 14, r: 12, b: 26, l: 62 };
			var labels = [];
			series.forEach( function ( s ) { s.pts.forEach( function ( p ) { if ( labels.indexOf( p[ 0 ] ) < 0 ) { labels.push( p[ 0 ] ); } } ); } );
			labels.sort();
			if ( labels.length < 2 ) { box.innerHTML = '<p class="prb-chart-empty">برای نمودار حداقل دو هفته داده لازم است.</p>'; return; }
			var vals = [];
			series.forEach( function ( s ) { s.pts.forEach( function ( p ) { vals.push( self.val( p[ 1 ], unit ) ); } ); } );
			var min = Math.min.apply( null, vals ), max = Math.max.apply( null, vals );
			if ( min === max ) { min *= 0.95; max *= 1.05; }
			var pad = ( max - min ) * 0.08; min -= pad; max += pad;
			var X = function ( i ) { return P.l + ( W - P.l - P.r ) * i / ( labels.length - 1 ); };
			var Y = function ( v ) { return P.t + ( H - P.t - P.b ) * ( 1 - ( v - min ) / ( max - min ) ); };
			var svg = '<svg viewBox="0 0 ' + W + ' ' + H + '" width="100%" height="' + H + '" role="img" aria-label="نمودار قیمت">';
			for ( var t = 0; t <= 4; t++ ) {
				var v = min + ( max - min ) * t / 4, y = Y( v );
				svg += '<line x1="' + P.l + '" x2="' + ( W - P.r ) + '" y1="' + y + '" y2="' + y + '" class="g"/><text x="' + ( P.l - 8 ) + '" y="' + ( y + 4 ) + '" class="ya">' + fa( v, 'mton' === unit ? 1 : 0 ) + '</text>';
			}
			var step = Math.ceil( labels.length / ( W < 480 ? 4 : 7 ) );
			labels.forEach( function ( l, i ) {
				var last = labels.length - 1;
				if ( i === last || ( i % step === 0 && last - i >= step * 0.6 ) ) {
					svg += '<text x="' + X( i ) + '" y="' + ( H - 6 ) + '" class="xa">' + faDate( l.slice( 5 ) ) + '</text>';
				}
			} );
			series.forEach( function ( s ) {
				var d = '', area = '';
				s.pts.forEach( function ( p, j ) {
					var x = X( labels.indexOf( p[ 0 ] ) ), y = Y( self.val( p[ 1 ], unit ) );
					d += ( j ? 'L' : 'M' ) + x.toFixed( 1 ) + ' ' + y.toFixed( 1 );
				} );
				if ( 1 === series.length && s.pts.length ) {
					area = d + 'L' + X( labels.indexOf( s.pts[ s.pts.length - 1 ][ 0 ] ) ) + ' ' + ( H - P.b ) + 'L' + X( labels.indexOf( s.pts[ 0 ][ 0 ] ) ) + ' ' + ( H - P.b ) + 'Z';
					svg += '<path d="' + area + '" fill="' + s.color + '" opacity=".09"/>';
				}
				svg += '<path d="' + d + '" fill="none" stroke="' + s.color + '" stroke-width="2.4" stroke-linejoin="round" stroke-linecap="round"/>';
			} );
			svg += '<line class="cur" x1="0" x2="0" y1="' + P.t + '" y2="' + ( H - P.b ) + '" style="display:none"/><g class="dots"></g><rect class="hit" x="' + P.l + '" y="0" width="' + ( W - P.l - P.r ) + '" height="' + H + '" fill="transparent"/></svg><div class="prb-tip" hidden></div>';
			box.innerHTML = svg;
			var hit = box.querySelector( '.hit' ), cur = box.querySelector( '.cur' ), dots = box.querySelector( '.dots' ), tip = box.querySelector( '.prb-tip' ), el = box.querySelector( 'svg' );
			function move( e ) {
				var rect = el.getBoundingClientRect(), px = ( ( e.touches ? e.touches[ 0 ].clientX : e.clientX ) - rect.left ) * W / rect.width;
				var i = Math.round( ( px - P.l ) / ( W - P.l - P.r ) * ( labels.length - 1 ) );
				i = Math.max( 0, Math.min( labels.length - 1, i ) );
				var x = X( i ), lab = labels[ i ], html = '<b>' + faDate( lab ) + '</b>', dd = '';
				series.forEach( function ( s ) {
					var p = s.pts.filter( function ( q ) { return q[ 0 ] === lab; } )[ 0 ];
					if ( p ) {
						var y = Y( self.val( p[ 1 ], unit ) );
						dd += '<circle cx="' + x + '" cy="' + y + '" r="4.5" fill="#fff" stroke="' + s.color + '" stroke-width="2.5"/>';
						html += '<span><i style="background:' + s.color + '"></i>' + ( series.length > 1 ? esc( s.name ) + ': ' : '' ) + self.fmt( p[ 1 ], unit ) + '</span>';
					}
				} );
				cur.setAttribute( 'x1', x ); cur.setAttribute( 'x2', x ); cur.style.display = '';
				dots.innerHTML = dd;
				tip.innerHTML = html; tip.hidden = false;
				var left = x / W * rect.width;
				tip.style.left = Math.min( Math.max( left, 70 ), rect.width - 70 ) + 'px';
			}
			hit.addEventListener( 'mousemove', move );
			hit.addEventListener( 'touchstart', move, { passive: true } );
			hit.addEventListener( 'touchmove', move, { passive: true } );
			hit.addEventListener( 'mouseleave', function () { cur.style.display = 'none'; dots.innerHTML = ''; tip.hidden = true; } );
		},
		drawDollar: function () {
			var box = this.root.querySelector( '[data-prb-dollar]' ), ds = this.b.dollars || [];
			if ( ! box || ds.length < 2 ) { return; }
			var vals = ds.map( function ( d ) { return d[ 1 ]; } );
			box.innerHTML = this.spark( vals, vals[ vals.length - 1 ] - vals[ vals.length - 2 ] ) ;
		},

		/* ---- modals -------------------------------------------------------- */
		modal: function ( html, wide ) {
			this.closeModal();
			var m = document.createElement( 'div' );
			m.className = 'baspar-scope prb-modal' + ( wide ? ' is-wide' : '' );
			m.setAttribute( 'role', 'dialog' );
			m.setAttribute( 'aria-modal', 'true' );
			m.innerHTML = '<div class="prb-modal-ov" data-prb-x></div><div class="prb-modal-box"><button type="button" class="prb-modal-x" data-prb-x aria-label="بستن">' + ICON.x + '</button>' + html + '</div>';
			document.body.appendChild( m );
			document.documentElement.classList.add( 'prb-lock' );
			var self = this;
			m.addEventListener( 'click', function ( e ) { if ( e.target.closest( '[data-prb-x]' ) ) { self.closeModal(); } } );
			this.lastFocus = document.activeElement;
			setTimeout( function () { var x = m.querySelector( '.prb-modal-x' ); if ( x ) { x.focus(); } }, 30 );
			this.m = m;
			return m;
		},
		closeModal: function () {
			if ( this.m ) {
				this.m.remove();
				this.m = null;
				document.documentElement.classList.remove( 'prb-lock' );
				if ( this.lastFocus && this.lastFocus.focus ) { this.lastFocus.focus(); }
			}
		},
		waText: function ( r ) {
			return 'سلام، وقت بخیر.\nبرای خرید «' + r.gr + '» تولید ' + r.pn + ' استعلام قیمت و موجودی می‌خواستم.\n(قیمت پایه هفته ' + this.b.week.date + ': ' + this.fmt( r.v, 'toman' ) + ' تومان/کیلو)\n' + this.cfg.page;
		},
		waLink: function ( r ) {
			return this.cfg.wa + ( this.cfg.wa.indexOf( '?' ) > -1 ? '&' : '?' ) + 'text=' + encodeURIComponent( this.waText( r ) );
		},
		openDetail: function ( k ) {
			var r = this.byKey[ k ], self = this, g = this.groups[ r.g ] || {};
			if ( ! r ) { return; }
			var chg = null === r.pv ? null : r.v - r.pv;
			var m = this.modal(
				'<div class="prb-d-head"><span class="prb-d-g">' + esc( g.name || '' ) + '</span><h2>' + esc( r.gr ) + '</h2><span class="prb-d-p">' + esc( r.pn ) + '</span></div>' +
				'<div class="prb-d-kpis">' +
					'<div><span>قیمت پایه این هفته</span><strong>' + this.fmt( r.v, 'toman' ) + ' <small>تومان/کیلو</small></strong><em>' + this.fmt( r.v, 'mton' ) + ' میلیون تومان/تن · ' + this.fmt( r.v, 'rial' ) + ' ریال</em></div>' +
					'<div><span>تغییر نسبت به هفته قبل</span><strong class="' + cls( chg ) + '">' + ( null === r.c ? '—' : pctText( r.c ) ) + '</strong><em>' + ( null === chg ? '' : ( chg > 0 ? '+' : ( chg < 0 ? '−' : '' ) ) + this.fmt( Math.abs( chg ), 'toman' ) + ' تومان' ) + '</em></div>' +
					'<div><span>معادل دلاری</span><strong>' + this.fmt( r.v, 'usd' ) + ' <small>$/تن</small></strong><em>با دلار ' + fa( Math.round( this.b.week.dollar / 10 ) ) + ' تومان</em></div>' +
				'</div>' +
				'<div class="prb-d-chart"><div class="prb-d-range" role="group" aria-label="بازه"><button type="button" data-w="13">۳ ماه</button><button type="button" data-w="26" class="is-on">۶ ماه</button><button type="button" data-w="52">۱ سال</button></div><div class="prb-chart" data-chart><div class="prb-skel"></div></div><div class="prb-d-stats" data-stats></div></div>' +
				'<div class="prb-calc"><h3>ماشین‌حساب خرید</h3><div class="prb-calc-in"><label>مقدار<span><input type="number" min="0.025" step="0.025" value="1" data-ton inputmode="decimal"> تن</span></label><label class="prb-check"><input type="checkbox" data-vat checked> ارزش افزوده ' + fa( this.cfg.vat ) + '٪</label></div><div class="prb-calc-out" data-out></div><p class="prb-calc-n">بر اساس قیمت پایه بورس؛ قیمت فروش، حمل و موجودی را از کارشناس بپرسید.</p></div>' +
				'<div class="prb-d-acts">' +
					( this.cfg.wa ? '<a class="prb-btn is-wa" href="' + esc( this.waLink( r ) ) + '" target="_blank" rel="noopener nofollow">' + ICON.wa + 'استعلام قیمت و موجودی در واتس‌اپ</a>' : '' ) +
					( this.cfg.bale ? '<a class="prb-btn is-bale" href="' + esc( this.cfg.bale ) + '" target="_blank" rel="noopener nofollow">پیام در بله</a>' : '' ) +
					'<button type="button" class="prb-btn" data-prb-cmp-m="' + esc( r.k ) + '">' + ICON.cmp + ( this.cmp.indexOf( r.k ) > -1 ? 'در فهرست مقایسه' : 'افزودن به مقایسه' ) + '</button>' +
				'</div>'
			);
			var weeks = 26;
			function draw() {
				var box = m.querySelector( '[data-chart]' );
				self.fetchHistory( [ r.k ], weeks ).then( function ( res ) {
					var pts = res[ 0 ] || [];
					self.chart( box, [ { name: r.gr, color: COLORS[ 0 ], pts: pts } ], 'toman' );
					if ( pts.length ) {
						var v = pts.map( function ( p ) { return p[ 1 ]; } ), mn = Math.min.apply( null, v ), mx = Math.max.apply( null, v ), first = v[ 0 ], ch = ( r.v - first ) * 100 / first;
						m.querySelector( '[data-stats]' ).innerHTML = '<span>کمترین <b>' + self.fmt( mn, 'toman' ) + '</b></span><span>بیشترین <b>' + self.fmt( mx, 'toman' ) + '</b></span><span>تغییر در بازه <b class="' + cls( ch ) + '">' + pctText( Math.round( ch * 100 ) / 100 ) + '</b></span><span>' + fa( pts.length ) + ' هفته</span>';
					}
				} ).catch( function () { box.innerHTML = '<p class="prb-chart-empty">دریافت سابقه قیمت ممکن نشد.</p>'; } );
			}
			draw();
			m.querySelector( '.prb-d-range' ).addEventListener( 'click', function ( e ) {
				var b = e.target.closest( '[data-w]' );
				if ( ! b ) { return; }
				weeks = Number( b.getAttribute( 'data-w' ) );
				m.querySelectorAll( '.prb-d-range button' ).forEach( function ( x ) { x.classList.toggle( 'is-on', x === b ); } );
				draw();
			} );
			function calc() {
				var t = parseFloat( String( m.querySelector( '[data-ton]' ).value ).replace( /[۰-۹]/g, function ( d ) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf( d ); } ) ) || 0;
				var vat = m.querySelector( '[data-vat]' ).checked ? self.cfg.vat : 0;
				var base = r.v / 10 * 1000 * t, tax = base * vat / 100;
				m.querySelector( '[data-out]' ).innerHTML = '<div><span>مبلغ پایه</span><b>' + fa( Math.round( base ) ) + ' تومان</b></div>' + ( vat ? '<div><span>ارزش افزوده</span><b>' + fa( Math.round( tax ) ) + ' تومان</b></div>' : '' ) + '<div class="sum"><span>جمع (تقریبی)</span><b>' + fa( Math.round( base + tax ) ) + ' تومان</b></div>';
			}
			m.querySelector( '[data-ton]' ).addEventListener( 'input', calc );
			m.querySelector( '[data-vat]' ).addEventListener( 'change', calc );
			calc();
			m.querySelector( '[data-prb-cmp-m]' ).addEventListener( 'click', function ( e ) {
				self.toggleCmp( r.k );
				e.currentTarget.lastChild.textContent = self.cmp.indexOf( r.k ) > -1 ? 'در فهرست مقایسه' : 'افزودن به مقایسه';
			} );
		},
		openCompare: function () {
			var self = this, keys = this.cmp.slice();
			if ( keys.length < 2 ) { return; }
			var rows = keys.map( function ( k ) { return self.byKey[ k ]; } );
			var m = this.modal(
				'<div class="prb-d-head"><span class="prb-d-g">مقایسه گریدها</span><h2>' + rows.map( function ( r ) { return esc( r.gr ); } ).join( ' · ' ) + '</h2></div>' +
				'<div class="prb-d-chart"><div class="prb-d-range" role="group" aria-label="بازه"><button type="button" data-w="13">۳ ماه</button><button type="button" data-w="26" class="is-on">۶ ماه</button><button type="button" data-w="52">۱ سال</button></div><div class="prb-chart" data-chart><div class="prb-skel"></div></div><div class="prb-legend2">' +
					rows.map( function ( r, i ) { return '<span><i style="background:' + COLORS[ i ] + '"></i>' + esc( r.gr ) + ' <small>' + esc( r.pn ) + '</small></span>'; } ).join( '' ) + '</div></div>' +
				'<div class="prb-cmp-table"><table><thead><tr><th>گرید</th><th>تولیدکننده</th><th>قیمت (تومان/کیلو)</th><th>تغییر هفته</th><th>اختلاف با ارزان‌ترین</th></tr></thead><tbody>' +
					( function () {
						var min = Math.min.apply( null, rows.map( function ( r ) { return r.v; } ) );
						return rows.map( function ( r, i ) {
							var d = r.v - min;
							return '<tr><th><i style="background:' + COLORS[ i ] + '"></i>' + esc( r.gr ) + '</th><td>' + esc( r.pn ) + '</td><td>' + self.fmt( r.v, 'toman' ) + '</td><td><span class="prb-chg ' + cls( r.c ) + '">' + pctText( r.c ) + '</span></td><td>' + ( d ? '+' + self.fmt( d, 'toman' ) + ' (' + fa( d * 100 / min, 1 ) + '٪)' : '<b class="is-up-best">ارزان‌ترین</b>' ) + '</td></tr>';
						} ).join( '' );
					} )() +
				'</tbody></table></div>', true );
			var weeks = 26;
			function draw() {
				var box = m.querySelector( '[data-chart]' );
				self.fetchHistory( keys, weeks ).then( function ( res ) {
					self.chart( box, rows.map( function ( r, i ) { return { name: r.gr, color: COLORS[ i ], pts: res[ i ] || [] }; } ), 'toman' );
				} ).catch( function () { box.innerHTML = '<p class="prb-chart-empty">دریافت سابقه قیمت ممکن نشد.</p>'; } );
			}
			draw();
			m.querySelector( '.prb-d-range' ).addEventListener( 'click', function ( e ) {
				var b = e.target.closest( '[data-w]' );
				if ( ! b ) { return; }
				weeks = Number( b.getAttribute( 'data-w' ) );
				m.querySelectorAll( '.prb-d-range button' ).forEach( function ( x ) { x.classList.toggle( 'is-on', x === b ); } );
				draw();
			} );
		},

		/* ---- export / share ------------------------------------------------ */
		csv: function () {
			var self = this, unit = this.st.unit, lines = [ [ 'گروه', 'گرید', 'تولیدکننده', 'قیمت (' + UNIT_LABEL[ unit ] + ')', 'قیمت هفته قبل', 'تغییر ٪' ].join( ',' ) ];
			this.visible().forEach( function ( r ) {
				var q = function ( s ) { return '"' + String( s ).replace( /"/g, '""' ) + '"'; };
				var v = self.val( r.v, unit ), pv = null === r.pv ? '' : self.val( r.pv, unit );
				lines.push( [ q( r.gn ), q( r.gr ), q( r.pn ), 'mton' === unit ? v.toFixed( 1 ) : Math.round( v ), '' === pv ? '' : ( 'mton' === unit ? pv.toFixed( 1 ) : Math.round( pv ) ), null === r.c ? '' : r.c ].join( ',' ) );
			} );
			var blob = new Blob( [ '﻿' + lines.join( '\r\n' ) ], { type: 'text/csv;charset=utf-8' } );
			var a = document.createElement( 'a' );
			a.href = URL.createObjectURL( blob );
			a.download = 'base-prices-' + this.b.week.date_short.replace( /\//g, '-' ) + '.csv';
			document.body.appendChild( a );
			a.click();
			setTimeout( function () { URL.revokeObjectURL( a.href ); a.remove(); }, 500 );
		},
		share: function ( btn ) {
			var url = location.href;
			var done = function () { btn.classList.add( 'is-copied' ); setTimeout( function () { btn.classList.remove( 'is-copied' ); }, 1500 ); };
			if ( navigator.share && /Mobi|Android/i.test( navigator.userAgent ) ) {
				navigator.share( { title: document.title, url: url } ).catch( function () {} );
			} else if ( navigator.clipboard ) {
				navigator.clipboard.writeText( url ).then( done, function () { window.prompt( 'لینک', url ); } );
			} else {
				window.prompt( 'لینک', url );
			}
		}
	};

	function boot( ctx ) {
		( ctx || document ).querySelectorAll( '.baspar-scope [data-prb]' ).forEach( function ( el ) {
			if ( el.getAttribute( 'data-prb-ready' ) ) { return; }
			el.setAttribute( 'data-prb-ready', '1' );
			try { el.prb = new PriceBoard( el ); } catch ( e ) { if ( window.console ) { console.error( e ); } }
		} );
	}
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () { boot(); } );
	} else {
		boot();
	}
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction( 'frontend/element_ready/baspar-price-board.default', function ( $s ) { boot( $s[ 0 ] ); } );
			}
		} );
	}
} )();
