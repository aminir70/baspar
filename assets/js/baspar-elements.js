/* ============================================================
   Baspar Elements — front-end interactions.
   Self-contained, no jQuery. Re-initialises on Elementor's
   `frontend/element_ready` hooks so widgets work in the editor
   preview too. All selectors are scoped to `.baspar-scope`.
   ============================================================ */
( function () {
	'use strict';

	/* ---- Layout-safe scheduling -------------------------------------------
	   Measuring (getBoundingClientRect, offsetWidth, scrollY…) right after the
	   page changes forces the browser to lay the page out early ("forced
	   reflow"). Start-up measurements wait until the first frame is painted,
	   when layout is already up to date, and read everything before writing. */
	function afterPaint( fn ) {
		if ( window.requestAnimationFrame ) {
			window.requestAnimationFrame( function () { setTimeout( fn, 0 ); } );
		} else {
			setTimeout( fn, 0 );
		}
	}

	// Show elements that are on screen and hide the rest until they scroll in,
	// using IntersectionObserver's first report (no measuring of our own).
	// `htmlClass` turns the hidden start state on only after that first report,
	// so content already visible never flickers.
	function observeReveal( els, opts, htmlClass, show ) {
		var first = true;
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting && ( first || ! opts.threshold || e.intersectionRatio >= opts.threshold ) ) {
					show( e.target, first );
					io.unobserve( e.target );
				}
			} );
			if ( first ) {
				first = false;
				document.documentElement.classList.add( htmlClass );
			}
		}, opts );
		els.forEach( function ( el ) { io.observe( el ); } );
	}

	/* ---- Scroll reveal -------------------------------------------------- */
	// Content stays visible until this script runs (it may be delayed by a
	// cache plugin). Elements already on screen are marked shown before the
	// `baspar-js` class enables the hidden start state, so nothing flickers.
	function initReveal( root ) {
		var els = ( root || document ).querySelectorAll( '.baspar-reveal:not(.in)' );
		if ( ! els.length ) {
			return;
		}
		if ( ! ( 'IntersectionObserver' in window ) ) {
			els.forEach( function ( el ) { el.classList.add( 'in' ); } );
			return;
		}
		observeReveal( Array.prototype.slice.call( els ), { threshold: 0.15 }, 'baspar-js', function ( el, initial ) {
			var d = initial ? 0 : parseInt( el.getAttribute( 'data-reveal-delay' ) || 0, 10 );
			if ( d ) {
				setTimeout( function () { el.classList.add( 'in' ); }, d );
			} else {
				el.classList.add( 'in' );
			}
		} );
	}

	/* ---- Sticky sidebar (single post) ------------------------------------
	   The column that holds the sidebar widgets sticks while the article is
	   read. When the sidebar is taller than the screen, `top` becomes
	   negative so it scrolls until its bottom edge is visible and then stays
	   there until the end of the article (no scroll listeners: CSS sticky). */
	function initStickySidebar() {
		if ( initStickySidebar.done ) {
			return;
		}
		var marks = document.querySelectorAll( '.scta, .sln, .sbx, .sbn, .pdq-side, .bspr-side-col' );
		if ( ! marks.length ) {
			return;
		}
		var cols = [];
		marks.forEach( function ( m ) {
			var col = m.closest( '.elementor-column, .e-con' );
			if ( ! col || cols.indexOf( col ) > -1 || col.classList.contains( 'bspr-no-sticky' ) ) {
				return;
			}
			// Only a side column: the row must also hold the article.
			var row = col.parentElement;
			if ( ! row || ! row.querySelector( '.bspr-article, .ph-title' ) || col.querySelector( '.bspr-article' ) ) {
				return;
			}
			cols.push( col );
		} );
		if ( ! cols.length ) {
			return;
		}
		initStickySidebar.done = true;
		var offset = 100;
		function fit() {
			// Read everything first, then write.
			var vh = window.innerHeight || document.documentElement.clientHeight;
			var on = window.innerWidth > 1024;
			var heights = cols.map( function ( col ) { return on ? col.offsetHeight : 0; } );
			cols.forEach( function ( col, i ) {
				col.classList.toggle( 'bspr-sticky-col', on );
				if ( ! on ) {
					col.style.removeProperty( '--bspr-sticky-top' );
					return;
				}
				var h = heights[ i ];
				var top = h + offset + 24 > vh ? vh - h - 24 : offset;
				col.style.setProperty( '--bspr-sticky-top', Math.round( top ) + 'px' );
			} );
		}
		window.addEventListener( 'resize', fit );
		if ( 'ResizeObserver' in window ) {
			// Fires once after the first layout, then on every size change.
			var ro = new ResizeObserver( function () { window.requestAnimationFrame( fit ); } );
			cols.forEach( function ( c ) { ro.observe( c ); } );
		} else {
			afterPaint( fit );
		}
		window.addEventListener( 'load', fit );
	}

	/* ---- Random lists: show N random items out of a rendered pool -------- */
	function initRandomLists( root ) {
		( root || document ).querySelectorAll( '[data-random][data-show]' ).forEach( function ( ul ) {
			if ( ul.getAttribute( 'data-shuffled' ) ) {
				return;
			}
			ul.setAttribute( 'data-shuffled', '1' );
			var items = Array.prototype.slice.call( ul.children );
			var n = parseInt( ul.getAttribute( 'data-show' ), 10 ) || items.length;
			for ( var i = items.length - 1; i > 0; i-- ) {
				var j = Math.floor( Math.random() * ( i + 1 ) );
				var t = items[ i ];
				items[ i ] = items[ j ];
				items[ j ] = t;
			}
			items.forEach( function ( li, k ) {
				li.classList.toggle( 'spr-extra', k >= n );
				ul.appendChild( li );
			} );
		} );
	}

	/* ---- Author box: expand / collapse the biography --------------------- */
	function initAuthorBio( root ) {
		( root || document ).querySelectorAll( '[data-au-more]' ).forEach( function ( btn ) {
			if ( btn.getAttribute( 'data-bound' ) ) {
				return;
			}
			btn.setAttribute( 'data-bound', '1' );
			btn.addEventListener( 'click', function () {
				var bio = btn.parentNode.querySelector( '[data-au-bio]' );
				var open = bio.classList.toggle( 'is-open' );
				btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
		} );
	}


	/* ---- Mega menu (desktop) + drawer (mobile) ---------------------------
	   Desktop panels open on hover/focus with CSS alone; the script switches
	   the category tabs and closes panels with Escape. The mobile drawer is
	   moved to <body> so the sticky, blurred header cannot clip it. */
	function initMega( root ) {
		var ctx = root || document;
		ctx.querySelectorAll( '[data-mm-panel]' ).forEach( function ( panel ) {
			if ( panel.dataset.mmInit ) {
				return;
			}
			panel.dataset.mmInit = '1';
			var tabs = panel.querySelectorAll( '[data-mm-tab]' );
			var panes = panel.querySelectorAll( '[data-mm-pane]' );
			var timer = null;
			function show( i ) {
				tabs.forEach( function ( t ) { t.classList.toggle( 'is-on', t.getAttribute( 'data-mm-tab' ) === i ); } );
				panes.forEach( function ( p ) { p.classList.toggle( 'is-on', p.getAttribute( 'data-mm-pane' ) === i ); } );
			}
			tabs.forEach( function ( t ) {
				t.addEventListener( 'mouseenter', function () {
					clearTimeout( timer );
					timer = setTimeout( function () { show( t.getAttribute( 'data-mm-tab' ) ); }, 70 );
				} );
				t.addEventListener( 'mouseleave', function () { clearTimeout( timer ); } );
				t.addEventListener( 'focus', function () { show( t.getAttribute( 'data-mm-tab' ) ); } );
			} );
		} );
		if ( ! initMega.esc ) {
			initMega.esc = true;
			document.addEventListener( 'keydown', function ( e ) {
				if ( 'Escape' !== e.key ) {
					return;
				}
				var open = document.querySelector( '.mmd-root.is-open' );
				if ( open ) {
					closeDrawer( open );
					return;
				}
				var item = document.activeElement && document.activeElement.closest && document.activeElement.closest( '.mm-item' );
				if ( item ) {
					var top = item.querySelector( '.mm-top' );
					item.classList.add( 'is-closed' );
					if ( top ) {
						top.focus();
					}
				}
			} );
			document.addEventListener( 'mouseover', function ( e ) {
				var item = e.target.closest && e.target.closest( '.mm-item.is-closed' );
				if ( item ) {
					item.classList.remove( 'is-closed' );
				}
			} );
		}

		/* Drawer: portal to <body> (replace a stale copy after an editor re-render). */
		ctx.querySelectorAll( '.baspar-scope [data-mmd]' ).forEach( function ( d ) {
			if ( d.parentNode === document.body ) {
				return;
			}
			var old = document.body.querySelector( ':scope > #' + d.id );
			if ( old && old !== d ) {
				old.remove();
			}
			document.body.appendChild( d );
			bindDrawer( d );
		} );
		ctx.querySelectorAll( '[data-mmd-open]' ).forEach( function ( b ) {
			if ( b.dataset.mmdInit ) {
				return;
			}
			b.dataset.mmdInit = '1';
			b.addEventListener( 'click', function ( e ) {
				var d = document.getElementById( b.getAttribute( 'data-mmd-open' ) );
				if ( ! d ) {
					return;
				}
				e.preventDefault();
				e.stopImmediatePropagation();
				openDrawer( d, b );
			} );
		} );
	}

	function openDrawer( d, opener ) {
		d.classList.add( 'is-open' );
		d.setAttribute( 'aria-hidden', 'false' );
		d._opener = opener || null;
		if ( opener ) {
			opener.setAttribute( 'aria-expanded', 'true' );
		}
		document.body.classList.add( 'baspar-nav-lock' );
		var x = d.querySelector( '.mmd-x' );
		if ( x ) {
			setTimeout( function () { x.focus(); }, 60 );
		}
	}

	function closeDrawer( d ) {
		d.classList.remove( 'is-open' );
		d.setAttribute( 'aria-hidden', 'true' );
		document.body.classList.remove( 'baspar-nav-lock' );
		if ( d._opener ) {
			d._opener.setAttribute( 'aria-expanded', 'false' );
			d._opener.focus();
		}
	}

	function bindDrawer( d ) {
		if ( d.dataset.mmdBound ) {
			return;
		}
		d.dataset.mmdBound = '1';
		var stack = [];
		function go( id, back ) {
			var next = document.getElementById( id );
			var cur = d.querySelector( '[data-mmd-view].is-on' );
			if ( ! next || next === cur ) {
				return;
			}
			next.hidden = false;
			next.classList.remove( 'from-back', 'from-next' );
			next.classList.add( back ? 'from-back' : 'from-next' );
			void next.offsetWidth;
			if ( cur ) {
				cur.classList.remove( 'is-on' );
				cur.hidden = true;
			}
			next.classList.add( 'is-on' );
			next.scrollTop = 0;
			var f = next.querySelector( '.mmd-back, .mmd-item' );
			if ( f ) {
				f.focus( { preventScroll: true } );
			}
		}
		d.addEventListener( 'click', function ( e ) {
			var t = e.target;
			if ( t.closest( '[data-mmd-close]' ) ) {
				closeDrawer( d );
				return;
			}
			var g = t.closest( '[data-mmd-go]' );
			if ( g ) {
				stack.push( d.querySelector( '[data-mmd-view].is-on' ).id );
				go( g.getAttribute( 'data-mmd-go' ), false );
				return;
			}
			var b = t.closest( '[data-mmd-back]' );
			if ( b ) {
				go( stack.pop() || b.getAttribute( 'data-mmd-back' ), true );
				return;
			}
			if ( t.closest( 'a[href]' ) ) {
				closeDrawer( d );
			}
		} );
	}
	/* ---- Article: reading progress + table-of-contents highlight -------- */
	function initArticle( root ) {
		var scope = root || document;
		var bar = scope.querySelector( '.bspr-progress span' );
		var article = document.querySelector( '[data-article]' );
		var tocs = scope.querySelectorAll( '[data-toc]' );
		if ( ( ! bar && ! tocs.length ) || ! article || article.getAttribute( 'data-article-init' ) ) {
			return;
		}
		article.setAttribute( 'data-article-init', '1' );
		var links = [];
		document.querySelectorAll( '[data-toc] a[href^="#"]' ).forEach( function ( a ) {
			var el = document.getElementById( decodeURIComponent( a.getAttribute( 'href' ).slice( 1 ) ) );
			if ( el ) {
				links.push( { a: a, el: el } );
			}
		} );
		var ticking = false;
		function update() {
			ticking = false;
			var r = article.getBoundingClientRect();
			var vh = window.innerHeight || document.documentElement.clientHeight;
			if ( bar ) {
				var total = r.height - vh * 0.6;
				var p = total > 0 ? Math.min( 1, Math.max( 0, -r.top / total ) ) : 1;
				bar.style.transform = 'scaleX(' + p.toFixed( 4 ) + ')';
			}
			var current = null;
			links.forEach( function ( l ) {
				if ( l.el.getBoundingClientRect().top < vh * 0.3 ) {
					current = l;
				}
			} );
			links.forEach( function ( l ) {
				l.a.classList.toggle( 'is-active', l === current );
			} );
		}
		function onScroll() {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		}
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', onScroll );
		afterPaint( update );
	}

	/* ---- Animated counters ---------------------------------------------- */
	function animateCounter( el ) {
		if ( el.dataset.counted ) {
			return;
		}
		el.dataset.counted = '1';
		var target = parseFloat( el.getAttribute( 'data-target' ) || el.textContent ) || 0;
		var dur = parseInt( el.getAttribute( 'data-duration' ) || '1600', 10 );
		var faNum = 'fa' === ( el.getAttribute( 'data-locale' ) || 'fa' );
		var start = null;
		function fmt( n ) {
			var s = Math.round( n ).toLocaleString( faNum ? 'fa-IR' : 'en-US' );
			return s;
		}
		function step( t ) {
			if ( ! start ) { start = t; }
			var p = Math.min( 1, ( t - start ) / dur );
			var eased = 1 - Math.pow( 1 - p, 3 );
			el.textContent = fmt( target * eased );
			if ( p < 1 ) { requestAnimationFrame( step ); }
		}
		requestAnimationFrame( step );
	}

	function initCounters( root ) {
		var nums = ( root || document ).querySelectorAll( '.baspar-scope [data-counter]' );
		if ( ! nums.length ) {
			return;
		}
		if ( ! ( 'IntersectionObserver' in window ) ) {
			nums.forEach( animateCounter );
			return;
		}
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					animateCounter( e.target );
					io.unobserve( e.target );
				}
			} );
		}, { threshold: 0.4 } );
		nums.forEach( function ( el ) { io.observe( el ); } );
	}

	/* ---- Tabs (latest products / generic) ------------------------------- */
	function initTabs( root ) {
		var groups = ( root || document ).querySelectorAll( '.baspar-scope [data-tabs]' );
		groups.forEach( function ( group ) {
			if ( group.dataset.tabsInit ) {
				return;
			}
			group.dataset.tabsInit = '1';
			var tabs = group.querySelectorAll( '[data-tab]' );
			var panels = group.querySelectorAll( '[data-panel]' );
			tabs.forEach( function ( tab ) {
				tab.addEventListener( 'click', function () {
					var id = tab.getAttribute( 'data-tab' );
					tabs.forEach( function ( t ) { t.classList.toggle( 'is-active', t === tab ); } );
					panels.forEach( function ( p ) {
						p.classList.toggle( 'is-active', p.getAttribute( 'data-panel' ) === id );
					} );
				} );
			} );
		} );
	}

	/* ---- FAQ accordion -------------------------------------------------- */
	function initFaq( root ) {
		var items = ( root || document ).querySelectorAll( '.baspar-scope .faq-item' );
		items.forEach( function ( item ) {
			var btn = item.querySelector( '.faq-q' );
			if ( ! btn || btn.dataset.faqInit ) {
				return;
			}
			btn.dataset.faqInit = '1';
			btn.addEventListener( 'click', function () {
				var open = item.classList.contains( 'is-open' );
				var single = item.closest( '[data-faq-single]' );
				if ( single ) {
					single.querySelectorAll( '.faq-item' ).forEach( function ( i ) { i.classList.remove( 'is-open' ); } );
				}
				item.classList.toggle( 'is-open', ! open );
			} );
		} );
	}

	/* ---- Header navigation (mobile toggle + dropdowns) ------------------ */
	var CARET_SVG = '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>';

	// Direct child <a> of a list item (avoids reaching into sub-menus).
	function directLink( li ) {
		for ( var i = 0; i < li.children.length; i++ ) {
			if ( 'A' === li.children[ i ].tagName ) {
				return li.children[ i ];
			}
		}
		return null;
	}

	function closeNav( scope ) {
		if ( ! scope ) {
			return;
		}
		var menu = scope.querySelector( '.nav-mobile' );
		var burger = scope.querySelector( '.hamburger' );
		if ( menu ) {
			menu.classList.remove( 'open' );
		}
		if ( burger ) {
			burger.classList.remove( 'is-open' );
			burger.setAttribute( 'aria-expanded', 'false' );
		}
		document.body.classList.remove( 'baspar-nav-lock' );
	}

	function initNav( root ) {
		var ctx = root || document;

		/* Hamburger opens / closes the mobile panel. */
		ctx.querySelectorAll( '.baspar-scope .hamburger' ).forEach( function ( b ) {
			if ( b.dataset.navInit ) {
				return;
			}
			b.dataset.navInit = '1';
			b.addEventListener( 'click', function () {
				var scope = b.closest( '.baspar-scope' );
				var menu = scope ? scope.querySelector( '.nav-mobile' ) : null;
				if ( ! menu ) {
					return;
				}
				var open = menu.classList.toggle( 'open' );
				b.classList.toggle( 'is-open', open );
				b.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
				document.body.classList.toggle( 'baspar-nav-lock', open );
			} );
		} );

		/* Desktop: add a caret to items that have a sub-menu. */
		ctx.querySelectorAll( '.baspar-scope .nav .menu-item-has-children' ).forEach( function ( li ) {
			if ( li.dataset.ddInit ) {
				return;
			}
			li.dataset.ddInit = '1';
			var link = directLink( li );
			if ( link && ! link.querySelector( '.nav-caret' ) ) {
				var caret = document.createElement( 'span' );
				caret.className = 'nav-caret';
				caret.innerHTML = CARET_SVG;
				link.appendChild( caret );
			}
		} );

		/* Mobile: turn parent items into tap-to-expand accordions. */
		ctx.querySelectorAll( '.baspar-scope .nav-mobile .menu-item-has-children' ).forEach( function ( li ) {
			if ( li.dataset.accInit ) {
				return;
			}
			li.dataset.accInit = '1';
			var link = directLink( li );
			if ( ! link ) {
				return;
			}
			var btn = document.createElement( 'button' );
			btn.type = 'button';
			btn.className = 'submenu-toggle';
			btn.setAttribute( 'aria-label', 'زیرمنو' );
			btn.innerHTML = CARET_SVG;
			link.insertAdjacentElement( 'afterend', btn );
			btn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				e.stopPropagation();
				li.classList.toggle( 'is-open' );
			} );
		} );

		/* Mobile: tapping an actual link closes the panel. */
		ctx.querySelectorAll( '.baspar-scope .nav-mobile a' ).forEach( function ( a ) {
			if ( a.dataset.closeInit ) {
				return;
			}
			a.dataset.closeInit = '1';
			a.addEventListener( 'click', function () {
				closeNav( a.closest( '.baspar-scope' ) );
			} );
		} );
	}

	/* ---- Floating WhatsApp show-on-scroll ------------------------------- */
	function initFloatWa() {
		var fwa = document.querySelector( '.bspr-fwa' );
		if ( ! fwa || fwa.dataset.init ) {
			return;
		}
		fwa.dataset.init = '1';
		var threshold = parseInt( fwa.getAttribute( 'data-threshold' ) || '400', 10 );
		function onScroll() {
			fwa.classList.toggle( 'in', window.scrollY > threshold );
		}
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		afterPaint( onScroll );
	}

	/* ---- Home v2 motion (1.8.0) -----------------------------------------
	   [data-anim] elements start hidden only after this runs (html class
	   baspar-motion); anything already on screen is shown first, so a late
	   script never hides visible content. Groups ([data-anim-group]) reveal
	   all their children together with a CSS stagger (--i). */
	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function initMotion( root ) {
		var ctx = root || document;
		var targets = [];
		ctx.querySelectorAll( '.baspar-scope [data-anim-group]:not(.is-in)' ).forEach( function ( g ) { targets.push( g ); } );
		ctx.querySelectorAll( '.baspar-scope [data-anim]:not(.is-in)' ).forEach( function ( el ) {
			if ( ! el.closest( '[data-anim-group]' ) ) { targets.push( el ); }
		} );
		if ( ! targets.length ) {
			return;
		}
		if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) {
			targets.forEach( function ( t ) { t.classList.add( 'is-in' ); } );
			return;
		}
		observeReveal( targets, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }, 'baspar-motion', function ( t ) {
			t.classList.add( 'is-in' );
		} );
	}

	// Cursor spotlight (--mx / --my) and 3D tilt (--rx / --ry), pointer only.
	function initPointerFx( root ) {
		if ( reduceMotion || ! window.matchMedia || ! window.matchMedia( '(hover: hover)' ).matches ) {
			return;
		}
		( root || document ).querySelectorAll( '.baspar-scope [data-spot], .baspar-scope [data-tilt]' ).forEach( function ( el ) {
			if ( el.dataset.fxInit ) {
				return;
			}
			el.dataset.fxInit = '1';
			var tilt = el.hasAttribute( 'data-tilt' );
			var raf = 0;
			el.addEventListener( 'pointermove', function ( e ) {
				var r = el.getBoundingClientRect();
				var x = ( e.clientX - r.left ) / r.width;
				var y = ( e.clientY - r.top ) / r.height;
				cancelAnimationFrame( raf );
				raf = requestAnimationFrame( function () {
					el.style.setProperty( '--mx', ( x * 100 ).toFixed( 1 ) + '%' );
					el.style.setProperty( '--my', ( y * 100 ).toFixed( 1 ) + '%' );
					if ( tilt ) {
						el.style.setProperty( '--ry', ( ( x - 0.5 ) * 10 ).toFixed( 2 ) + 'deg' );
						el.style.setProperty( '--rx', ( ( 0.5 - y ) * 8 ).toFixed( 2 ) + 'deg' );
					}
				} );
			} );
			el.addEventListener( 'pointerleave', function () {
				cancelAnimationFrame( raf );
				el.style.removeProperty( '--rx' );
				el.style.removeProperty( '--ry' );
			} );
		} );
	}

	// Hero rotating word: steps a column of words; the last item repeats the
	// first, so the loop snaps back invisibly.
	function initRotator( root ) {
		( root || document ).querySelectorAll( '.baspar-scope .h2v-rot-in' ).forEach( function ( col ) {
			if ( col.dataset.rotInit || reduceMotion ) {
				return;
			}
			col.dataset.rotInit = '1';
			var n = col.children.length - 1;
			if ( n < 2 ) {
				return;
			}
			var k = 0;
			setInterval( function () {
				if ( document.hidden ) {
					return;
				}
				k++;
				col.style.transition = '';
				col.style.setProperty( '--k', k );
				if ( k === n ) {
					setTimeout( function () {
						col.style.transition = 'none';
						k = 0;
						col.style.setProperty( '--k', 0 );
					}, 750 );
				}
			}, 2600 );
		} );
	}

	// Product rails: arrows, progress bar, gentle autoplay (paused on hover,
	// focus, touch and when off screen). RTL aware.
	function initRails( root ) {
		( root || document ).querySelectorAll( '.baspar-scope [data-rail]' ).forEach( function ( rail ) {
			if ( rail.dataset.railInit ) {
				return;
			}
			rail.dataset.railInit = '1';
			var track = rail.querySelector( '[data-rail-track]' );
			var bar = rail.querySelector( '[data-rail-bar]' );
			var prev = rail.querySelector( '[data-rail-prev]' );
			var next = rail.querySelector( '[data-rail-next]' );
			if ( ! track ) {
				return;
			}
			var rtl = null; // read lazily, after the first paint
			function isRtl() {
				if ( null === rtl ) { rtl = 'rtl' === getComputedStyle( track ).direction; }
				return rtl;
			}
			function max() { return Math.max( 0, track.scrollWidth - track.clientWidth ); }
			function pos() { return Math.abs( track.scrollLeft ); }
			function step() {
				var item = track.querySelector( '.prl-item' );
				return item ? item.getBoundingClientRect().width + 16 : track.clientWidth * 0.8;
			}
			function go( dir ) {
				var m = max();
				var target = pos() + dir * step();
				if ( target > m + 4 ) { target = 0; }
				if ( target < -4 ) { target = m; }
				track.scrollTo( { left: isRtl() ? -target : target, behavior: reduceMotion ? 'auto' : 'smooth' } );
			}
			function update() {
				// Reads first …
				var sw = track.scrollWidth;
				var cw = track.clientWidth;
				var m = Math.max( 0, sw - cw );
				var x = pos();
				var p = m ? x / m : 0;
				var dir = isRtl() ? -1 : 1;
				// … then writes.
				if ( bar ) {
					var vis = sw ? cw / sw : 1;
					bar.style.width = Math.max( 12, vis * 100 ) + '%';
					bar.style.transform = 'translateX(' + dir * p * ( ( 1 / Math.max( vis, 0.0001 ) ) - 1 ) * 100 + '%)';
				}
				rail.classList.toggle( 'is-static', m < 4 );
				rail.classList.toggle( 'at-start', x < 4 );
				rail.classList.toggle( 'at-end', x > m - 4 );
			}
			if ( prev ) { prev.addEventListener( 'click', function () { go( -1 ); } ); }
			if ( next ) { next.addEventListener( 'click', function () { go( 1 ); } ); }
			track.addEventListener( 'scroll', function () { requestAnimationFrame( update ); }, { passive: true } );
			window.addEventListener( 'resize', update );
			track.addEventListener( 'keydown', function ( e ) {
				if ( 'ArrowLeft' === e.key ) { e.preventDefault(); go( isRtl() ? 1 : -1 ); }
				if ( 'ArrowRight' === e.key ) { e.preventDefault(); go( isRtl() ? -1 : 1 ); }
			} );
			rail._update = update;
			if ( 'ResizeObserver' in window ) {
				new ResizeObserver( function () { window.requestAnimationFrame( update ); } ).observe( track );
			} else {
				afterPaint( update );
			}
			if ( rail.hasAttribute( 'data-rail-auto' ) && ! reduceMotion ) {
				var hold = false;
				var seen = true;
				[ 'pointerenter', 'focusin', 'touchstart' ].forEach( function ( ev ) { rail.addEventListener( ev, function () { hold = true; }, { passive: true } ); } );
				[ 'pointerleave', 'focusout' ].forEach( function ( ev ) { rail.addEventListener( ev, function () { hold = false; } ); } );
				if ( 'IntersectionObserver' in window ) {
					new IntersectionObserver( function ( en ) { seen = en[ 0 ].isIntersecting; } ).observe( rail );
				}
				setInterval( function () {
					if ( ! hold && seen && ! document.hidden && rail.offsetParent ) {
						go( 1 );
					}
				}, 4200 );
			}
		} );
	}

	// Product rail tabs with a sliding indicator.
	function initRailTabs( root ) {
		( root || document ).querySelectorAll( '.baspar-scope [data-prl]' ).forEach( function ( box ) {
			if ( box.dataset.prlInit ) {
				return;
			}
			box.dataset.prlInit = '1';
			var tabs = box.querySelectorAll( '[data-prl-tab]' );
			var panes = box.querySelectorAll( '[data-prl-pane]' );
			var wrap = box.querySelector( '[data-prl-tabs]' );
			var ind = wrap && wrap.querySelector( '.prl-ind' );
			function place() {
				var on = wrap && wrap.querySelector( '.prl-tab.is-on' );
				if ( ! ind || ! on ) {
					return;
				}
				ind.style.width = on.offsetWidth + 'px';
				ind.style.transform = 'translateX(' + on.offsetLeft + 'px)';
				ind.style.opacity = '1';
				if ( ! wrap.classList.contains( 'is-ready' ) ) {
					requestAnimationFrame( function () { wrap.classList.add( 'is-ready' ); } );
				}
			}
			tabs.forEach( function ( t, idx ) {
				t.addEventListener( 'click', function () {
					var i = t.getAttribute( 'data-prl-tab' );
					tabs.forEach( function ( x ) {
						var on = x === t;
						x.classList.toggle( 'is-on', on );
						x.setAttribute( 'aria-selected', on ? 'true' : 'false' );
					} );
					panes.forEach( function ( p ) {
						var on = p.getAttribute( 'data-prl-pane' ) === i;
						p.classList.toggle( 'is-on', on );
						if ( on ) {
							p.removeAttribute( 'hidden' );
							p.classList.remove( 'is-enter' );
							void p.offsetWidth;
							p.classList.add( 'is-enter' );
							var r = p.querySelector( '[data-rail]' );
							if ( r && r._update ) { r._update(); }
						} else {
							p.setAttribute( 'hidden', '' );
						}
					} );
					place();
					if ( t.scrollIntoView && wrap.scrollWidth > wrap.clientWidth ) {
						t.scrollIntoView( { block: 'nearest', inline: 'center', behavior: 'smooth' } );
					}
				} );
				t.addEventListener( 'keydown', function ( e ) {
					var d = 'ArrowLeft' === e.key ? 1 : ( 'ArrowRight' === e.key ? -1 : 0 );
					if ( d ) {
						e.preventDefault();
						var n = tabs[ ( idx + d + tabs.length ) % tabs.length ];
						n.focus();
						n.click();
					}
				} );
			} );
			if ( wrap && 'ResizeObserver' in window ) {
				// First report comes after layout: no forced reflow on load.
				new ResizeObserver( function () { window.requestAnimationFrame( place ); } ).observe( wrap );
			} else {
				afterPaint( place );
				window.addEventListener( 'resize', place );
			}
			if ( document.fonts && document.fonts.ready ) { document.fonts.ready.then( function () { window.requestAnimationFrame( place ); } ); }
		} );
	}

	// Sticky header for Hello Elementor: the widget's own `position:sticky`
	// can't work inside a short Elementor section, so the whole header area
	// (theme-builder header, or the top-level section on a page) becomes the
	// sticky element. Anything above the header widget inside that area (the
	// topbar) scrolls away. Skipped when Elementor Pro's sticky is in use.
	function initStickyHeader() {
		var w = document.querySelector( '.baspar-scope[data-sticky-header]' );
		if ( ! w || w.dataset.stInit || document.body.classList.contains( 'elementor-editor-active' ) ) {
			return;
		}
		w.dataset.stInit = '1';
		if ( w.closest( '.elementor-sticky, [data-settings*="\\"sticky\\""]' ) ) {
			return;
		}
		var host = w.closest( '.elementor-location-header, [data-elementor-type="header"]' );
		if ( ! host ) {
			var doc = w.closest( '.elementor' );
			if ( ! doc ) {
				return;
			}
			host = w;
			while ( host.parentElement && host.parentElement !== doc ) {
				host = host.parentElement;
			}
		}
		host.classList.add( 'bspr-sticky-host' );
		var above = 0;
		function measure() {
			// Reads first, then writes.
			var bar = document.getElementById( 'wpadminbar' );
			var barH = bar && 'fixed' === getComputedStyle( bar ).position ? bar.offsetHeight : 0;
			above = Math.max( 0, w.getBoundingClientRect().top - host.getBoundingClientRect().top );
			var stuck = window.scrollY > above + 4;
			host.style.setProperty( '--bspr-sticky-top', ( barH - above ) + 'px' );
			w.classList.toggle( 'is-stuck', stuck );
		}
		function onScroll() {
			w.classList.toggle( 'is-stuck', window.scrollY > above + 4 );
		}
		afterPaint( measure );
		window.addEventListener( 'resize', measure );
		window.addEventListener( 'load', measure );
		window.addEventListener( 'scroll', onScroll, { passive: true } );
	}

	/* ---- Boot ----------------------------------------------------------- */
	function boot( root ) {
		initStickyHeader();
		initMotion( root );
		initPointerFx( root );
		initRotator( root );
		initRails( root );
		initRailTabs( root );
		initReveal( root );
		initCounters( root );
		initTabs( root );
		initFaq( root );
		initNav( root );
		initArticle( root );
		initMega( root );
		initAuthorBio( root );
		initRandomLists( root );
		initStickySidebar();
		initFloatWa();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', function () { boot( document ); } );
	} else {
		boot( document );
	}

	// Elementor editor / frontend hooks.
	if ( window.jQuery ) {
		window.jQuery( window ).on( 'elementor/frontend/init', function () {
			if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
				window.elementorFrontend.hooks.addAction( 'frontend/element_ready/global', function ( $scope ) {
					boot( $scope && $scope[ 0 ] ? $scope[ 0 ] : document );
				} );
			}
		} );
	}

	/* ---- Product page: gallery, lightbox, copy link ----------------------
	   Delegated on document so it works for markup rendered later (Elementor
	   editor) and when this file is loaded late by a cache plugin. Without
	   JS the main image is a plain link to the full-size file. */
	document.addEventListener( 'click', function ( e ) {
		var th = e.target.closest && e.target.closest( '[data-pdg-th]' );
		if ( th ) {
			var g = th.closest( '[data-pdg]' );
			var img = g && g.querySelector( '[data-pdg-img]' );
			var main = g && g.querySelector( '[data-pdg-main]' );
			if ( img && main ) {
				img.removeAttribute( 'srcset' );
				img.removeAttribute( 'sizes' );
				img.src = th.getAttribute( 'data-large' );
				main.href = th.getAttribute( 'data-full' );
				g.querySelectorAll( '[data-pdg-th]' ).forEach( function ( b ) { b.classList.toggle( 'is-on', b === th ); } );
			}
			return;
		}
		var mainLink = e.target.closest && e.target.closest( '[data-pdg-main]' );
		if ( mainLink && ! e.ctrlKey && ! e.metaKey ) {
			e.preventDefault();
			var box = document.createElement( 'div' );
			box.className = 'baspar-lightbox';
			box.setAttribute( 'role', 'dialog' );
			box.setAttribute( 'aria-modal', 'true' );
			box.innerHTML = '<button type="button" aria-label="بستن">×</button><img alt="">';
			box.querySelector( 'img' ).src = mainLink.href;
			document.body.appendChild( box );
			var close = function () { box.remove(); document.removeEventListener( 'keydown', esc ); };
			var esc = function ( k ) { if ( 'Escape' === k.key ) { close(); } };
			box.addEventListener( 'click', close );
			document.addEventListener( 'keydown', esc );
			box.querySelector( 'button' ).focus();
			return;
		}
		var cp = e.target.closest && e.target.closest( '[data-copy]' );
		if ( cp ) {
			var url = cp.getAttribute( 'data-copy' );
			var done = function () { cp.classList.add( 'is-copied' ); setTimeout( function () { cp.classList.remove( 'is-copied' ); }, 1600 ); };
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( url ).then( done, function () { window.prompt( 'لینک', url ); } );
			} else {
				window.prompt( 'لینک', url );
			}
		}
	} );
} )();
