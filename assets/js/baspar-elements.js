/* ============================================================
   Baspar Elements — front-end interactions.
   Self-contained, no jQuery. Re-initialises on Elementor's
   `frontend/element_ready` hooks so widgets work in the editor
   preview too. All selectors are scoped to `.baspar-scope`.
   ============================================================ */
( function () {
	'use strict';

	/* ---- Scroll reveal -------------------------------------------------- */
	function initReveal( root ) {
		var els = ( root || document ).querySelectorAll( '.baspar-reveal:not(.in)' );
		if ( ! els.length ) {
			return;
		}
		if ( ! ( 'IntersectionObserver' in window ) ) {
			els.forEach( function ( el ) { el.classList.add( 'in' ); } );
			return;
		}
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
		initFloatWa();
	}

	document.addEventListener( 'DOMContentLoaded', function () { boot( document ); } );

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
