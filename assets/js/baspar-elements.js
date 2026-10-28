/* ============================================================
   Baspar Elements — front-end interactions.
   Self-contained, no jQuery. Re-initialises on Elementor's
   `frontend/element_ready` hooks so widgets work in the editor
   preview too. All selectors are scoped to `.baspar-scope`.
   ============================================================ */
( function () {
	'use strict';

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
		var vh = window.innerHeight || document.documentElement.clientHeight;
		els = Array.prototype.filter.call( els, function ( el ) {
			var r = el.getBoundingClientRect();
			if ( r.top < vh && r.bottom > 0 ) {
				el.classList.add( 'in' );
				return false;
			}
			return true;
		} );
		document.documentElement.classList.add( 'baspar-js' );
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					var d = e.target.getAttribute( 'data-reveal-delay' ) || 0;
					setTimeout( function () { e.target.classList.add( 'in' ); }, parseInt( d, 10 ) );
					io.unobserve( e.target );
				}
			} );
		}, { threshold: 0.15 } );
		els.forEach( function ( el ) { io.observe( el ); } );
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
		var marks = document.querySelectorAll( '.scta, .sln, .sbx, .sbn, .bspr-side-col' );
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
			var vh = window.innerHeight || document.documentElement.clientHeight;
			cols.forEach( function ( col ) {
				var on = window.innerWidth > 1024;
				col.classList.toggle( 'bspr-sticky-col', on );
				if ( ! on ) {
					col.style.removeProperty( '--bspr-sticky-top' );
					return;
				}
				var h = col.offsetHeight;
				var top = h + offset + 24 > vh ? vh - h - 24 : offset;
				col.style.setProperty( '--bspr-sticky-top', Math.round( top ) + 'px' );
			} );
		}
		fit();
		window.addEventListener( 'resize', fit );
		if ( 'ResizeObserver' in window ) {
			var ro = new ResizeObserver( fit );
			cols.forEach( function ( c ) { ro.observe( c ); } );
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
		update();
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
		onScroll();
	}

	/* ---- Boot ----------------------------------------------------------- */
	function boot( root ) {
		initReveal( root );
		initCounters( root );
		initTabs( root );
		initFaq( root );
		initNav( root );
		initArticle( root );
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
} )();
